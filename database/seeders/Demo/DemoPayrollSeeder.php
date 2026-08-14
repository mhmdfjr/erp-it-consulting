<?php
// database/seeders/Demo/DemoPayrollSeeder.php

namespace Database\Seeders\Demo;

use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder DEMO untuk Payroll Period + Payroll Run satu periode (bulan lalu,
 * HARUS match dengan periode yang dipakai DemoEmployeeAttendanceSeeder --
 * processPayrollRun() menghitung prorate dari attendance periode yang sama).
 *
 * Dipanggil dengan forceIncomplete: true, karena EMP-0011 sengaja punya
 * attendance tidak lengkap di seeder sebelumnya -- ini demo skenario nyata
 * "warning attendance completeness tapi user pilih tetap lanjut" (task 3.18/3.24),
 * bukan menyembunyikan error.
 *
 * Setelah diproses, sebagian payroll_run ditandai 'paid' (toggle status manual,
 * TIDAK generate journal entry tambahan, sesuai keputusan M3), sisanya dibiarkan
 * 'finalized' supaya tombol Mark as Paid masih bisa dites di UI.
 */
class DemoPayrollSeeder extends Seeder
{
    /** Jumlah payroll_run yang ditandai 'paid', sisanya dibiarkan 'finalized'. */
    private const MARK_PAID_COUNT = 8;

    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoPayrollSeeder tidak boleh dijalankan di production.');
        }

        // CreateJournalEntryFromPayroll (listener PayrollProcessed) ShouldQueue,
        // wajib sync selama seeding supaya journal entry agregat langsung terbentuk.
        $previousQueueDefault = config('queue.default');
        config(['queue.default' => 'sync']);

        try {
            $this->seedPayrollRun();
        } finally {
            config(['queue.default' => $previousQueueDefault]);
        }
    }

    private function seedPayrollRun(): void
    {
        // Harus konsisten dengan periode di DemoEmployeeAttendanceSeeder.
        $periodDate = Carbon::now()->subMonthNoOverflow();

        $period = PayrollPeriod::firstOrCreate(
            ['period_month' => $periodDate->month, 'period_year' => $periodDate->year],
            ['status' => 'draft']
        );

        if (in_array($period->status, ['processed', 'paid'], true)) {
            $this->command->warn(sprintf(
                'Payroll period %s/%s sudah berstatus "%s", skip processPayrollRun (idempotency).',
                $period->period_month,
                $period->period_year,
                $period->status
            ));
            return;
        }

        app(PayrollService::class)->processPayrollRun($period, forceIncomplete: true);

        $this->markSomeRunsAsPaid($period);

        $this->command->info(sprintf(
            'Demo Payroll Run periode %s/%s selesai diproses.',
            $period->period_month,
            $period->period_year
        ));
    }

    private function markSomeRunsAsPaid(PayrollPeriod $period): void
    {
        $runs = PayrollRun::where('payroll_period_id', $period->id)
            ->orderBy('id')
            ->limit(self::MARK_PAID_COUNT)
            ->get();

        foreach ($runs as $run) {
            $run->update(['status' => 'paid']);
        }
    }
}
