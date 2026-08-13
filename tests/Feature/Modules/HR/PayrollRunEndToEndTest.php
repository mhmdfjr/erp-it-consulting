<?php

namespace Tests\Feature\Modules\HR;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntry;
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

class PayrollRunEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TerRateSeeder::class);
        $this->seed(BpjsRateSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);

        // Listener CreateJournalEntryFromPayroll adalah ShouldQueue —
        // paksa sync di test supaya benar-benar tereksekusi, bukan cuma
        // masuk tabel jobs (gotcha yang sama seperti M2, lihat
        // SESSION_SUMMARY_M2.md soal queue.default di environment testing).
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
     * KRITIS: proses payroll untuk 2 employee dengan attendance lengkap,
     * assert HANYA SATU journal entry agregat terbentuk (bukan 2, satu
     * per employee), assert total debit = total credit, dan assert
     * NILAI PER AKUN INDIVIDUAL benar — khususnya 204/205 harus
     * mengandung employee+company portion, bukan cuma employee portion.
     * Balance-only check tidak cukup (lihat gotcha grouping item_type M2).
     */
    public function test_process_payroll_creates_single_aggregate_balanced_journal_entry(): void
    {
        $employeeA = Employee::factory()->create([
            'ptkp_status' => 'TK0',
            'base_salary' => 10000000,
        ]);
        $employeeB = Employee::factory()->create([
            'ptkp_status' => 'K2',
            'base_salary' => 8000000,
        ]);

        $this->fillFullAttendance($employeeA, 2026, 8);
        $this->fillFullAttendance($employeeB, 2026, 8);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $result = $this->service->processPayrollRun($period);

        $this->assertSame(2, $result['processed']);
        $this->assertSame(0, $result['skipped']);
        $this->assertEmpty($result['failed']);

        // === Assert HANYA SATU journal entry agregat, bukan per employee ===
        $entries = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->get();

        $this->assertCount(1, $entries, 'Harus cuma ada 1 journal entry agregat untuk period ini, bukan 1 per employee.');

        $entry = $entries->first()->load('lines.account');

        // === Balance check: total debit = total credit ===
        $totalDebit = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');
        $this->assertEqualsWithDelta((float) $totalDebit, (float) $totalCredit, 0.01);

        // === Assert nilai PER AKUN, bukan cuma total balance ===
        $runs = $period->payrollRuns()->get();
        $expectedTotalGross = (float) $runs->sum('gross_salary');
        $expectedTotalNet = (float) $runs->sum('net_salary');
        $expectedTotalPph21 = (float) $runs->sum('pph21_deduction');
        $expectedBpjsKesehatanEmployee = (float) $runs->sum('bpjs_kesehatan_deduction');
        $expectedBpjsJhtJpEmployee = (float) ($runs->sum('bpjs_jht_deduction') + $runs->sum('bpjs_jp_deduction'));

        $lineByCode = $entry->lines->keyBy(fn ($line) => $line->account->code);

        // Debit 511 = total gross seluruh employee
        $this->assertEqualsWithDelta($expectedTotalGross, (float) $lineByCode['511']->debit, 0.01);

        // Kredit 202 = total net seluruh employee
        $this->assertEqualsWithDelta($expectedTotalNet, (float) $lineByCode['202']->credit, 0.01);

        // Kredit 203 = total PPh21
        $this->assertEqualsWithDelta($expectedTotalPph21, (float) $lineByCode['203']->credit, 0.01);

        // KRITIS: Kredit 204 (BPJS Kesehatan) HARUS lebih besar dari
        // employee portion saja — membuktikan company portion benar-benar
        // ditambahkan, bukan lupa (bug yang eksplisit diwanti-wanti di
        // DATABASE.md Appendix C "Peringatan implementasi").
        $creditKesehatan = (float) $lineByCode['204']->credit;
        $this->assertGreaterThan(
            $expectedBpjsKesehatanEmployee,
            $creditKesehatan,
            'Kredit 204 harus > employee portion saja — company portion wajib ditambahkan.'
        );

        // KRITIS: sama untuk Kredit 205 (BPJS Ketenagakerjaan gabungan JHT+JKK+JKM+JP)
        $creditKetenagakerjaan = (float) $lineByCode['205']->credit;
        $this->assertGreaterThan(
            $expectedBpjsJhtJpEmployee,
            $creditKetenagakerjaan,
            'Kredit 205 harus > employee portion (JHT+JP) saja — company portion (termasuk JKK/JKM) wajib ditambahkan.'
        );

        // Debit 512/513 (beban BPJS perusahaan) harus > 0
        // company portion benar-benar dihitung sebagai beban, bukan 0.
        $this->assertGreaterThan(0, (float) $lineByCode['512']->debit);
        $this->assertGreaterThan(0, (float) $lineByCode['513']->debit);
    }

    /**
     * Skenario prorate: satu employee absent 3 hari, assert base_salary
     * yang tersimpan di payroll_run SUDAH terpotong, dan gross_salary
     * yang masuk journal entry (via debit 511) mencerminkan angka yang
     * SUDAH prorated, bukan base_salary kontraktual penuh.
     */
    public function test_process_payroll_with_prorate_reflects_in_journal_entry(): void
    {
        $employee = Employee::factory()->create([
            'ptkp_status' => 'TK0',
            'base_salary' => 10000000,
        ]);

        $this->fillFullAttendance($employee, 2026, 8);

        foreach (['2026-08-03', '2026-08-04', '2026-08-05'] as $date) {
            Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->update(['status' => 'absent']);
        }

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $this->service->processPayrollRun($period);

        $run = $period->payrollRuns()->first();

        $expectedProratedBase = round(10000000 * (21 - 3) / 21, 2);
        $this->assertEqualsWithDelta($expectedProratedBase, (float) $run->base_salary, 0.01);
        $this->assertSame(3, $run->absent_days);

        $entry = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->first()
            ->load('lines.account');

        $debit511 = $entry->lines->firstWhere('account.code', '511');

        $this->assertEqualsWithDelta((float) $run->gross_salary, (float) $debit511->debit, 0.01);
        $this->assertLessThan(10000000, (float) $run->gross_salary);
    }

    /**
     * Idempotency: panggil processPayrollRun() dua kali untuk period yang
     * sama, assert employee yang sudah diproses di-skip di panggilan
     * kedua (bukan dobel dihitung), dan HANYA ada 1 journal entry (bukan
     * 2, karena event kedua tidak pernah di-fire kalau processed=0 di
     * panggilan kedua).
     */
    public function test_calling_process_payroll_twice_is_idempotent(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $this->fillFullAttendance($employee, 2026, 8);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $firstResult = $this->service->processPayrollRun($period);
        $this->assertSame(1, $firstResult['processed']);

        $secondResult = $this->service->processPayrollRun($period->fresh());
        $this->assertSame(0, $secondResult['processed']);
        $this->assertSame(1, $secondResult['skipped']);

        $entries = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->get();

        $this->assertCount(1, $entries, 'Panggilan kedua tidak boleh membuat journal entry tambahan.');
    }

    /**
     * Attendance TIDAK lengkap (cuma 18 dari 21 hari kerja terisi) -> harus
     * throw RuntimeException, TIDAK ada payroll_run yang terbuat sama sekali
     * (bukan partial), TIDAK ada journal entry.
     */
    public function test_process_payroll_throws_when_attendance_incomplete(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);

        // Cuma isi 18 dari 21 hari kerja Agustus 2026 (sengaja kurang 3)
        $workingDays = $this->service->calculateWorkingDays(2026, 8);
        $cursor = Carbon::create(2026, 8, 1);
        $filled = 0;

        while ($filled < $workingDays - 3) {
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

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Attendance belum lengkap/');

        $this->service->processPayrollRun($period);

        // Baris di bawah ini TIDAK akan pernah dieksekusi kalau exception
        // benar-benar dilempar (PHPUnit stop di titik exception), tapi
        // ditulis eksplisit sebagai dokumentasi ekspektasi: kalau ada yang
        // mengubah behavior jadi "partial process lalu throw", test ini
        // WAJIB direvisi, bukan diam-diam lolos.
    }

    /**
     * Assert eksplisit TIDAK ada efek samping tersisa setelah exception —
     * dipisah dari test di atas karena assertion setelah expectException()
     * tidak pernah tereksekusi (PHPUnit stop di titik exception dilempar).
     */
    public function test_process_payroll_creates_no_side_effects_when_attendance_incomplete(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);

        $workingDays = $this->service->calculateWorkingDays(2026, 8);
        $cursor = Carbon::create(2026, 8, 1);
        $filled = 0;

        while ($filled < $workingDays - 3) {
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

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        try {
            $this->service->processPayrollRun($period);
            $this->fail('processPayrollRun() seharusnya throw RuntimeException, tapi tidak.');
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertSame(0, $period->payrollRuns()->count());
        $this->assertSame('draft', $period->fresh()->status);

        $entries = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->count();
        $this->assertSame(0, $entries);
    }

    /**
     * forceIncomplete=true -> proses tetap jalan meski attendance kurang.
     * Hari yang tidak punya record dianggap 'present' (TIDAK menambah
     * absent_days), sesuai keputusan M3 planning.
     */
    public function test_process_payroll_with_force_incomplete_treats_missing_days_as_present(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);

        // Cuma isi 18 dari 21 hari kerja, TIDAK ada satupun yang absent
        // secara eksplisit — cuma kurang lengkap datanya.
        $workingDays = $this->service->calculateWorkingDays(2026, 8);
        $cursor = Carbon::create(2026, 8, 1);
        $filled = 0;

        while ($filled < $workingDays - 3) {
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

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $result = $this->service->processPayrollRun($period, forceIncomplete: true);

        $this->assertSame(1, $result['processed']);
        $this->assertEmpty($result['failed']);

        $run = $period->payrollRuns()->first();

        // KRITIS: hari kosong dianggap PRESENT, bukan absent. absent_days
        // harus 0 (bukan 3), karena tidak ada satupun row berstatus 'absent'
        // secara eksplisit — cuma data yang tidak lengkap.
        $this->assertSame(0, $run->absent_days);
        $this->assertEqualsWithDelta(10000000.0, (float) $run->base_salary, 0.01);
    }
}
