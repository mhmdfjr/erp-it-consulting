<?php

namespace Tests\Feature\Modules\HR;

use App\Modules\Finance\database\seeders\ChartOfAccountsSeeder;
use App\Modules\HR\Database\Seeders\BpjsRateSeeder;
use App\Modules\HR\Database\Seeders\TerRateSeeder;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollAttendanceCompletenessTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TerRateSeeder::class);
        $this->seed(BpjsRateSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);

        config(['queue.default' => 'sync']);

        $this->service = new PayrollService();
    }

    private function fillFullAttendance(Employee $employee, int $year, int $month): void
    {
        $workingDays = $this->service->calculateWorkingDays($year, $month);
        $cursor = Carbon::create($year, $month, 1);
        $filled = 0;

        while ($filled < $workingDays) {
            if (! $cursor->isWeekend()) {
                Attendance::factory()->create([
                    'employee_id' => $employee->id,
                    'date' => $cursor->toDateString(),
                    'status' => 'present',
                ]);
                $filled++;
            }
            $cursor->addDay();
        }
    }

    /**
     * checkAttendanceCompleteness() harus mengembalikan array kosong kalau
     * SEMUA employee aktif punya attendance lengkap.
     */
    public function test_returns_empty_array_when_all_attendance_complete(): void
    {
        $employee = Employee::factory()->create();
        $this->fillFullAttendance($employee, 2026, 8);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $incomplete = $this->service->checkAttendanceCompleteness($period);

        $this->assertEmpty($incomplete);
    }

    /**
     * checkAttendanceCompleteness() harus mendeteksi employee dengan
     * attendance kurang lengkap, dan melaporkan angka expected/actual
     * yang akurat untuk ditampilkan di UI (task 3.24).
     */
    public function test_detects_employee_with_incomplete_attendance(): void
    {
        $completeEmployee = Employee::factory()->create(['full_name' => 'Complete Employee']);
        $this->fillFullAttendance($completeEmployee, 2026, 8);

        $incompleteEmployee = Employee::factory()->create(['full_name' => 'Incomplete Employee']);
        // Cuma isi 5 hari dari 21 hari kerja yang seharusnya
        Attendance::factory()->count(5)->sequence(
            ['date' => '2026-08-03'],
            ['date' => '2026-08-04'],
            ['date' => '2026-08-05'],
            ['date' => '2026-08-06'],
            ['date' => '2026-08-07'],
        )->create(['employee_id' => $incompleteEmployee->id, 'status' => 'present']);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $incomplete = $this->service->checkAttendanceCompleteness($period);

        $this->assertCount(1, $incomplete);
        $this->assertSame($incompleteEmployee->id, $incomplete[0]['employee_id']);
        $this->assertSame(21, $incomplete[0]['expected']);
        $this->assertSame(5, $incomplete[0]['actual']);
    }

    /**
     * processPayrollRun() TANPA forceIncomplete harus menolak (throw)
     * kalau ada employee dengan attendance belum lengkap — TIDAK boleh
     * ada payroll_run yang tercipta sama sekali (all-or-nothing di titik
     * pre-check ini, beda dari kegagalan per-employee di dalam loop).
     */
    public function test_process_payroll_throws_without_force_when_incomplete(): void
    {
        $employee = Employee::factory()->create();
        // Sengaja TIDAK diisi attendance sama sekali

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Attendance belum lengkap/');

        $this->service->processPayrollRun($period);

        // Kalau exception TIDAK terlempar (test gagal di titik ini),
        // pastikan tidak ada payroll_run yang sempat terbuat.
        $this->assertSame(0, $period->payrollRuns()->count());
    }

    /**
     * KRITIS: forceIncomplete=true harus tetap memproses payroll meski
     * attendance belum lengkap, dan hari yang TIDAK punya record
     * dianggap 'present' (TIDAK menambah absent_days) — sesuai keputusan
     * eksplisit di DATABASE.md Section 2.8a.
     */
    public function test_force_incomplete_treats_missing_days_as_present(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);

        // Cuma isi 10 hari dari 21 hari kerja, SEMUA berstatus present
        // (bukan absent) — 11 hari sisanya tidak punya record sama sekali.
        $dates = [
            '2026-08-03', '2026-08-04', '2026-08-05', '2026-08-06', '2026-08-07',
            '2026-08-10', '2026-08-11', '2026-08-12', '2026-08-13', '2026-08-14',
        ];
        foreach ($dates as $date) {
            Attendance::factory()->create([
                'employee_id' => $employee->id,
                'date' => $date,
                'status' => 'present',
            ]);
        }

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $result = $this->service->processPayrollRun($period, forceIncomplete: true);

        $this->assertSame(1, $result['processed']);

        $run = $period->payrollRuns()->first();

        // KRITIS: absent_days HARUS 0, bukan 11 — hari yang tidak punya
        // record dianggap present (tidak dihitung sebagai absent),
        // BUKAN otomatis dianggap absent. base_salary harus penuh.
        $this->assertSame(0, $run->absent_days);
        $this->assertEqualsWithDelta(10000000.0, (float) $run->base_salary, 0.01);
    }

    /**
     * Kombinasi: employee dengan attendance TIDAK lengkap (11 hari tidak
     * punya record) DAN ada beberapa hari eksplisit berstatus 'absent'
     * di antara yang tercatat. forceIncomplete=true harus tetap memotong
     * base_salary HANYA berdasarkan status='absent' yang eksplisit
     * tercatat, bukan dari hari yang tidak punya record.
     */
    public function test_force_incomplete_still_counts_explicit_absent_days(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);

        // 8 hari present, 2 hari eksplisit absent, 11 hari sisanya tanpa record
        $presentDates = ['2026-08-03', '2026-08-04', '2026-08-06', '2026-08-07', '2026-08-10', '2026-08-11', '2026-08-12', '2026-08-13'];
        foreach ($presentDates as $date) {
            Attendance::factory()->create(['employee_id' => $employee->id, 'date' => $date, 'status' => 'present']);
        }

        $absentDates = ['2026-08-05', '2026-08-14'];
        foreach ($absentDates as $date) {
            Attendance::factory()->create(['employee_id' => $employee->id, 'date' => $date, 'status' => 'absent']);
        }

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $result = $this->service->processPayrollRun($period, forceIncomplete: true);
        $this->assertSame(1, $result['processed']);

        $run = $period->payrollRuns()->first();

        // HANYA 2 hari absent eksplisit yang dihitung, 11 hari tanpa
        // record TIDAK ikut menambah absent_days.
        $this->assertSame(2, $run->absent_days);

        $expectedBase = round(10000000 * (21 - 2) / 21, 2);
        $this->assertEqualsWithDelta($expectedBase, (float) $run->base_salary, 0.01);
    }
}
