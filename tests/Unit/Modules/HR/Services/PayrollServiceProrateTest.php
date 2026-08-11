<?php

namespace Tests\Unit\Modules\HR\Services;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceProrateTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();
    }

    /**
     * Agustus 2026: 21 hari kerja (weekday), lihat PayrollServiceTest.
     * Tanpa attendance 'absent' sama sekali -> base_salary_prorated
     * harus sama persis dengan base_salary kontraktual.
     */
    public function test_no_absent_days_results_in_full_base_salary(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $period = PayrollPeriod::factory()->create(['period_month' => 8, 'period_year' => 2026]);

        $result = $this->service->calculateProratedBaseSalary($employee, $period);

        $this->assertSame(10000000.0, $result['base_salary_prorated']);
        $this->assertSame(21, $result['working_days']);
        $this->assertSame(0, $result['absent_days']);
    }

    /**
     * 3 hari absent dari 21 hari kerja -> base_salary_prorated =
     * 10jt x (21-3)/21 = 10jt x 18/21 = 8571428.57 (dibulatkan 2 desimal).
     */
    public function test_absent_days_prorate_base_salary_proportionally(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $period = PayrollPeriod::factory()->create(['period_month' => 8, 'period_year' => 2026]);

        // 3 tanggal weekday di Agustus 2026: 3, 4, 5 Agustus (Senin-Rabu)
        Attendance::factory()->absent()->create([
            'employee_id' => $employee->id,
            'date' => '2026-08-03',
        ]);
        Attendance::factory()->absent()->create([
            'employee_id' => $employee->id,
            'date' => '2026-08-04',
        ]);
        Attendance::factory()->absent()->create([
            'employee_id' => $employee->id,
            'date' => '2026-08-05',
        ]);

        $result = $this->service->calculateProratedBaseSalary($employee, $period);

        $expected = round(10000000 * (21 - 3) / 21, 2);

        $this->assertSame($expected, $result['base_salary_prorated']);
        $this->assertSame(21, $result['working_days']);
        $this->assertSame(3, $result['absent_days']);
    }

    /**
     * Kritis: leave dan sick tidak memotong base salary, cuma absent yang
     * memotong. Ini membuktikan filter status='absent' di query spesifik,
     * bukan kebetulan cocok karena tidak pernah diuji dengan status lain.
     */
    public function test_leave_and_sick_do_not_reduce_base_salary(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $period = PayrollPeriod::factory()->create(['period_month' => 8, 'period_year' => 2026]);

        Attendance::factory()->leave()->create([
            'employee_id' => $employee->id,
            'date' => '2026-08-03',
        ]);
        Attendance::factory()->sick()->create([
            'employee_id' => $employee->id,
            'date' => '2026-08-04',
        ]);
        Attendance::factory()->create([ // present, default factory
            'employee_id' => $employee->id,
            'date' => '2026-08-05',
        ]);

        $result = $this->service->calculateProratedBaseSalary($employee, $period);

        $this->assertSame(10000000.0, $result['base_salary_prorated']);
        $this->assertSame(0, $result['absent_days']);
    }

    /**
     * Kombinasi: leave+sick+present tidak memotong, HANYA absent yang
     * dihitung. Skenario campuran membuktikan filter tidak salah hitung
     * status lain sebagai absent secara tidak sengaja.
     */
    public function test_mixed_attendance_statuses_only_absent_counts(): void
    {
        $employee = Employee::factory()->create(['base_salary' => 10000000]);
        $period = PayrollPeriod::factory()->create(['period_month' => 8, 'period_year' => 2026]);

        Attendance::factory()->absent()->create(['employee_id' => $employee->id, 'date' => '2026-08-03']);
        Attendance::factory()->leave()->create(['employee_id' => $employee->id, 'date' => '2026-08-04']);
        Attendance::factory()->sick()->create(['employee_id' => $employee->id, 'date' => '2026-08-05']);
        Attendance::factory()->create(['employee_id' => $employee->id, 'date' => '2026-08-06']); // present

        $result = $this->service->calculateProratedBaseSalary($employee, $period);

        // Cuma 1 hari absent dari 4 record yang dibuat (3 status lain tidak dihitung)
        $this->assertSame(1, $result['absent_days']);

        $expected = round(10000000 * (21 - 1) / 21, 2);
        $this->assertSame($expected, $result['base_salary_prorated']);
    }
}
