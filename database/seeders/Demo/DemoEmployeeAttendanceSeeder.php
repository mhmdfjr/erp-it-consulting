<?php
// database/seeders/Demo/DemoEmployeeAttendanceSeeder.php

namespace Database\Seeders\Demo;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Position;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder DEMO untuk Employee dan Attendance satu periode penuh (bulan lalu
 * relatif terhadap tanggal seeding dijalankan), supaya payroll run untuk
 * periode itu bisa langsung diproses di chunk berikutnya.
 *
 * Variasi yang sengaja dibuat:
 * - 1 employee dengan beberapa hari `absent` -> uji prorate base salary.
 * - 1 employee dengan `leave`/`sick` -> uji bahwa status ini TIDAK memotong.
 * - 1 employee dengan attendance sengaja tidak lengkap -> uji warning
 *   attendance completeness (TASKS.md task 3.18/3.24).
 * - Sisanya full present -> baseline tanpa prorate.
 */
class DemoEmployeeAttendanceSeeder extends Seeder
{
    /**
     * Periode attendance yang di-seed. Bulan lalu dipilih (bukan bulan berjalan),
     * supaya periode ini pasti sudah "lewat" penuh saat payroll diproses di
     * chunk berikutnya -- payroll run untuk bulan berjalan yang belum selesai
     * secara bisnis kurang masuk akal untuk demo.
     */
    private Carbon $periodStart;
    private Carbon $periodEnd;

    public function __construct()
    {
        $this->periodStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $this->periodEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();
    }

    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoEmployeeAttendanceSeeder tidak boleh dijalankan di production.');
        }

        $employees = $this->seedEmployees();
        $this->seedAttendance($employees);

        $this->command->info(sprintf(
            'Demo Employee (%d) & Attendance periode %s selesai.',
            count($employees),
            $this->periodStart->format('F Y')
        ));
    }

    /**
     * @return array<string, Employee> keyed by employee_code untuk dipakai seedAttendance()
     */
    private function seedEmployees(): array
    {
        $ptkpVariants = ['TK0', 'TK1', 'K0', 'TK2', 'K1', 'K2', 'K3'];

        $employeeSeeds = [
            ['code' => 'EMP-0001', 'name' => 'Andi Wijaya', 'dept' => 'Engineering', 'pos' => 'Technical Lead', 'ptkp' => 'K1', 'salary' => 18000000, 'gender' => 'L'],
            ['code' => 'EMP-0002', 'name' => 'Bella Putri', 'dept' => 'Engineering', 'pos' => 'Software Engineer', 'ptkp' => 'TK0', 'salary' => 11000000, 'gender' => 'P'],
            ['code' => 'EMP-0003', 'name' => 'Candra Kusuma', 'dept' => 'Engineering', 'pos' => 'Software Engineer', 'ptkp' => 'TK1', 'salary' => 10500000, 'gender' => 'L'],
            ['code' => 'EMP-0004', 'name' => 'Dewi Anjani', 'dept' => 'Engineering', 'pos' => 'IT Consultant', 'ptkp' => 'K0', 'salary' => 15000000, 'gender' => 'P'],
            ['code' => 'EMP-0005', 'name' => 'Eka Prasetya', 'dept' => 'Sales & Marketing', 'pos' => 'Sales Executive', 'ptkp' => 'TK0', 'salary' => 8500000, 'gender' => 'L'],
            ['code' => 'EMP-0006', 'name' => 'Farah Amelia', 'dept' => 'Sales & Marketing', 'pos' => 'Account Manager', 'ptkp' => 'K2', 'salary' => 12500000, 'gender' => 'P'],
            ['code' => 'EMP-0007', 'name' => 'Galih Nugroho', 'dept' => 'Finance & Accounting', 'pos' => 'Staff Finance', 'ptkp' => 'TK1', 'salary' => 9000000, 'gender' => 'L'],
            ['code' => 'EMP-0008', 'name' => 'Hana Salsabila', 'dept' => 'Finance & Accounting', 'pos' => 'Finance Manager', 'ptkp' => 'K3', 'salary' => 22000000, 'gender' => 'P'],
            ['code' => 'EMP-0009', 'name' => 'Indra Setiawan', 'dept' => 'Human Resources', 'pos' => 'Staff HR', 'ptkp' => 'TK0', 'salary' => 8800000, 'gender' => 'L'],
            ['code' => 'EMP-0010', 'name' => 'Jasmine Putri', 'dept' => 'Human Resources', 'pos' => 'HR Manager', 'ptkp' => 'K1', 'salary' => 17000000, 'gender' => 'P'],
            ['code' => 'EMP-0011', 'name' => 'Kevin Halim', 'dept' => 'Operations', 'pos' => 'Warehouse Staff', 'ptkp' => 'TK2', 'salary' => 7000000, 'gender' => 'L'],
            ['code' => 'EMP-0012', 'name' => 'Laila Rahmawati', 'dept' => 'Operations', 'pos' => 'Procurement Officer', 'ptkp' => 'TK0', 'salary' => 8000000, 'gender' => 'P'],
            // Employee resigned, untuk uji filter employment_status=active saat processPayrollRun()
            // dan Stat Card employee aktif (task 4.3) tidak ikut menghitung yang ini.
            ['code' => 'EMP-0013', 'name' => 'Muhammad Rizki', 'dept' => 'Sales & Marketing', 'pos' => 'Sales Executive', 'ptkp' => 'TK0', 'salary' => 8500000, 'gender' => 'L', 'resigned' => true],
        ];

        $employees = [];

        foreach ($employeeSeeds as $seed) {
            $department = Department::where('name', $seed['dept'])->firstOrFail();
            $position = Position::where('department_id', $department->id)
                ->where('title', $seed['pos'])
                ->firstOrFail();

            $isResigned = $seed['resigned'] ?? false;

            $employee = Employee::firstOrCreate(
                ['employee_code' => $seed['code']],
                [
                    'full_name' => $seed['name'],
                    'nik' => '32' . str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
                    'npwp' => null,
                    'gender' => $seed['gender'],
                    'birth_date' => Carbon::now()->subYears(random_int(24, 45))->subDays(random_int(0, 365)),
                    'ptkp_status' => $seed['ptkp'],
                    'position_id' => $position->id,
                    'base_salary' => $seed['salary'],
                    'hire_date' => Carbon::now()->subYears(random_int(1, 5))->subMonths(random_int(0, 11)),
                    'termination_date' => $isResigned ? Carbon::now()->subDays(10) : null,
                    'employment_status' => $isResigned ? 'resigned' : 'active',
                    'bank_name' => 'Bank Central Asia',
                    'bank_account_number' => (string) random_int(1000000000, 9999999999),
                    'phone' => '0813' . random_int(10000000, 99999999),
                    'email' => strtolower(str_replace(' ', '.', $seed['name'])) . '@erpdemo.local',
                ]
            );

            $employees[$seed['code']] = $employee;
        }

        return $employees;
    }

    private function seedAttendance(array $employees): void
    {
        $weekdays = collect();
        $cursor = $this->periodStart->copy();
        while ($cursor->lte($this->periodEnd)) {
            if (! $cursor->isWeekend()) {
                $weekdays->push($cursor->copy());
            }
            $cursor->addDay();
        }

        foreach ($employees as $code => $employee) {
            // Employee resigned tidak perlu attendance periode ini, tidak akan ikut diproses payroll.
            if ($employee->employment_status !== 'active') {
                continue;
            }

            foreach ($weekdays as $index => $date) {
                $status = 'present';

                // EMP-0002: 3 hari absent tersebar -> kandidat utama uji prorate.
                if ($code === 'EMP-0002' && in_array($index, [3, 8, 15], true)) {
                    $status = 'absent';
                }

                // EMP-0005: leave 2 hari + sick 1 hari -> bukti visual leave/sick TIDAK memotong.
                if ($code === 'EMP-0005' && in_array($index, [5, 6], true)) {
                    $status = 'leave';
                }
                if ($code === 'EMP-0005' && $index === 12) {
                    $status = 'sick';
                }

                // EMP-0011: sengaja skip beberapa hari terakhir (tidak ada record sama
                // sekali) -> trigger warning attendance completeness saat payroll diproses.
                if ($code === 'EMP-0011' && $index >= $weekdays->count() - 4) {
                    continue;
                }

                Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date->toDateString()],
                    [
                        'check_in' => '08:00:00',
                        'check_out' => '17:00:00',
                        'status' => $status,
                        'note' => $status === 'absent' ? 'Tidak ada keterangan (data demo)' : null,
                    ]
                );
            }
        }
    }
}
