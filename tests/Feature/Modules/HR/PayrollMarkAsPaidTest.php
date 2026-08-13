<?php

namespace Tests\Feature\Modules\HR;

use App\Models\User;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\database\seeders\ChartOfAccountsSeeder;
use App\Modules\HR\Database\Seeders\BpjsRateSeeder;
use App\Modules\HR\Database\Seeders\TerRateSeeder;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollMarkAsPaidTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TerRateSeeder::class);
        $this->seed(BpjsRateSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        config(['queue.default' => 'sync']);

        $this->service = new PayrollService();

        // Asumsi pola dari M0/M1/M2: Super Admin role sudah ter-assign
        // seluruh permission via seeder. Sesuaikan kalau pola auth test
        // project Anda berbeda (misal butuh Sanctum/session guard khusus).
        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
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
     * KRITIS: Mark as Paid harus toggle status finalized->paid untuk
     * SELURUH payroll_run di period tersebut, DAN period.status jadi
     * 'paid', TAPI TIDAK BOLEH membuat journal_entries baru — accrual
     * sudah tercatat saat processPayrollRun() (task 3.18/3.26), Mark as
     * Paid murni toggle status (mirror pola VendorBill, ARCHITECTURE.md
     * Section 4).
     */
    public function test_mark_as_paid_toggles_status_without_new_journal_entry(): void
    {
        $employeeA = Employee::factory()->create(['base_salary' => 10000000]);
        $employeeB = Employee::factory()->create(['base_salary' => 8000000]);

        $this->fillFullAttendance($employeeA, 2026, 8);
        $this->fillFullAttendance($employeeB, 2026, 8);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $this->service->processPayrollRun($period);

        // Baseline SEBELUM mark as paid: pastikan status masih finalized,
        // dan catat jumlah journal entry yang sudah ada dari accrual.
        $this->assertSame(2, PayrollRun::where('payroll_period_id', $period->id)
            ->where('status', 'finalized')->count());

        $journalEntryCountBefore = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->count();
        $this->assertSame(1, $journalEntryCountBefore, 'Harus sudah ada 1 journal entry dari accrual saat processPayrollRun.');

        // === Aksi: Mark as Paid ===
        $response = $this->actingAs($this->user)
            ->post(route('hr.payroll-runs.mark-as-paid', $period));

        $response->assertRedirect(route('hr.payroll-runs.show', $period));

        // Status seluruh payroll_run harus 'paid'
        $this->assertSame(0, PayrollRun::where('payroll_period_id', $period->id)
            ->where('status', 'finalized')->count());
        $this->assertSame(2, PayrollRun::where('payroll_period_id', $period->id)
            ->where('status', 'paid')->count());

        // Period status juga harus jadi 'paid'
        $this->assertSame('paid', $period->fresh()->status);

        // === KRITIS: TIDAK ADA journal entry tambahan ===
        $journalEntryCountAfter = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->count();
        $this->assertSame(
            $journalEntryCountBefore,
            $journalEntryCountAfter,
            'Mark as Paid TIDAK BOLEH membuat journal entry baru — accrual sudah dicatat saat processPayrollRun.'
        );
    }

    /**
     * Idempotency: Mark as Paid dipanggil dua kali, panggilan kedua tidak
     * boleh error dan tidak boleh mengubah apapun (0 row affected karena
     * sudah tidak ada payroll_run berstatus 'finalized' tersisa).
     */
    public function test_mark_as_paid_twice_is_idempotent(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $this->fillFullAttendance($employee, 2026, 8);

        $period = PayrollPeriod::factory()->create([
            'period_month' => 8,
            'period_year' => 2026,
        ]);

        $this->service->processPayrollRun($period);

        $this->actingAs($this->user)->post(route('hr.payroll-runs.mark-as-paid', $period));

        $journalEntryCountAfterFirst = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->count();

        // Panggilan kedua — tidak boleh error, tidak boleh ubah apapun
        $response = $this->actingAs($this->user)->post(route('hr.payroll-runs.mark-as-paid', $period));
        $response->assertRedirect();

        $journalEntryCountAfterSecond = JournalEntry::where('reference_type', 'PayrollPeriod')
            ->where('reference_id', $period->id)
            ->count();

        $this->assertSame($journalEntryCountAfterFirst, $journalEntryCountAfterSecond);
        $this->assertSame(1, PayrollRun::where('payroll_period_id', $period->id)
            ->where('status', 'paid')->count());
    }
}
