<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\EmployeePayrollComponent;
use App\Modules\HR\Models\BpjsRate;
use App\Modules\HR\Models\PtkpTerMapping;
use App\Modules\HR\Models\TerRate;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Models\PayrollRunItem;
use Illuminate\Support\Facades\DB;
use App\Modules\HR\Events\PayrollProcessed;
use Carbon\Carbon;

class PayrollService
{
    public function calculateWorkingDays(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $workingDays = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $workingDays++;
            }
            $cursor->addDay();
        }

        return $workingDays;
    }

    /**
     * Prorate base_salary berdasarkan hari absent tanpa keterangan.
     *
     * @return array{
     *     base_salary_prorated: float,
     *     working_days: int,
     *     absent_days: int,
     * }
     */
    public function calculateProratedBaseSalary(Employee $employee, PayrollPeriod $period): array
    {
        $periodStart = Carbon::create($period->period_year, $period->period_month, 1)->startOfMonth();
        $nextPeriodStart = $periodStart->copy()->addMonthNoOverflow()->startOfMonth();

        $workingDays = $this->calculateWorkingDays($period->period_year, $period->period_month);

        $absentDays = Attendance::where('employee_id', $employee->id)
            ->where('status', 'absent')
            ->where('date', '>=', $periodStart->toDateString())
            ->where('date', '<', $nextPeriodStart->toDateString())
            ->count();

        $absentDays = min($absentDays, $workingDays);

        $effectiveDays = $workingDays - $absentDays;
        $attendanceRatio = $workingDays > 0 ? $effectiveDays / $workingDays : 1.0;

        $baseSalaryProrated = round((float) $employee->base_salary * $attendanceRatio, 2);

        return [
            'base_salary_prorated' => $baseSalaryProrated,
            'working_days' => $workingDays,
            'absent_days' => $absentDays,
        ];
    }

    /**
     * gross_salary = base_salary_prorated + earning component aktif.
     * Earning flat, tidak ikut prorate. percentage_of_base dihitung dari
     * employee->base_salary kontraktual, bukan $baseSalaryProrated.
     *
     * @return array{
     *     gross_salary: float,
     *     earning_total: float,
     *     earning_breakdown: array<int, array{component_id: int, label: string, amount: float}>,
     * }
     */
    public function calculateGrossSalary(
        Employee $employee,
        float $baseSalaryProrated,
        Carbon $periodDate
    ): array {
        $earningComponents = EmployeePayrollComponent::with('payrollComponent')
            ->where('employee_id', $employee->id)
            ->whereHas('payrollComponent', fn ($q) => $q->where('type', 'earning')->where('is_active', true))
            ->where('effective_date', '<=', $periodDate->toDateString())
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $periodDate->toDateString());
            })
            ->get();

        $earningBreakdown = [];
        $earningTotal = 0.0;

        foreach ($earningComponents as $assignment) {
            $component = $assignment->payrollComponent;

            $amount = match ($component->calculation_type) {
                'fixed_amount' => (float) $assignment->amount,
                'percentage_of_base' => round(
                    (float) $employee->base_salary * ((float) $assignment->percentage / 100),
                    2
                ),
                default => throw new \RuntimeException(
                    "Unknown calculation_type '{$component->calculation_type}' for PayrollComponent #{$component->id}"
                ),
            };

            $earningBreakdown[] = [
                'component_id' => $component->id,
                'label' => $component->name,
                'amount' => $amount,
            ];

            $earningTotal += $amount;
        }

        $grossSalary = round($baseSalaryProrated + $earningTotal, 2);

        return [
            'gross_salary' => $grossSalary,
            'earning_total' => round($earningTotal, 2),
            'earning_breakdown' => $earningBreakdown,
        ];
    }

    /**
     * Employee portion BPJS deductions bukan company portion, dihitung
     * terpisah di listener CreateJournalEntryFromPayroll.
     * max_wage_base sebagai cap: gross > cap, basis hitung. pakai cap, bukan gross aktual.
     *
     * @return array{
     *     bpjs_kesehatan_deduction: float,
     *     bpjs_jht_deduction: float,
     *     bpjs_jp_deduction: float,
     *     total_bpjs_deduction: float,
     * }
     */
    public function calculateBpjsDeductions(float $grossSalary, Carbon $periodDate): array
    {
        $rates = BpjsRate::where('effective_date', '<=', $periodDate->toDateString())
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $periodDate->toDateString());
            })
            ->get()
            ->keyBy('bpjs_type');

        $deductions = [];

        foreach (['kesehatan', 'jht', 'jp'] as $type) {
            $rate = $rates->get($type);

            if (! $rate) {
                throw new \RuntimeException(
                    "Tidak ditemukan bpjs_rates untuk type '{$type}' yang berlaku di periode {$periodDate->toDateString()}. ".
                    'Pastikan BpjsRateSeeder sudah dijalankan dan effective_date-nya benar.'
                );
            }

            $basis = $rate->max_wage_base !== null
                ? min($grossSalary, (float) $rate->max_wage_base)
                : $grossSalary;

            $deductions[$type] = round($basis * ((float) $rate->rate_employee_percentage / 100), 2);
        }

        $totalDeduction = round(
            $deductions['kesehatan'] + $deductions['jht'] + $deductions['jp'],
            2
        );

        return [
            'bpjs_kesehatan_deduction' => $deductions['kesehatan'],
            'bpjs_jht_deduction' => $deductions['jht'],
            'bpjs_jp_deduction' => $deductions['jp'],
            'total_bpjs_deduction' => $totalDeduction,
        ];
    }

    /**
     * PPh21 monthly withholding via skema TER. Lookup kategori
     * TER dari ptkp_status employee, cari bracket TER yang sesuai gross_salary,
     * kalikan rate, BULATKAN ROUND HALF UP ke rupiah penuh
     *
     * @return array{pph21_deduction: float, ter_category_used: string}
     */
    public function calculatePph21(Employee $employee, float $grossSalary, Carbon $periodDate): array
    {
        $mapping = PtkpTerMapping::with('terCategory')
            ->where('ptkp_status', $employee->ptkp_status)
            ->first();

        if (! $mapping) {
            throw new \RuntimeException(
                "Tidak ditemukan ptkp_ter_mapping untuk ptkp_status '{$employee->ptkp_status}' ".
                "(Employee #{$employee->id}). Pastikan TerRateSeeder sudah dijalankan."
            );
        }

        $terCategoryCode = $mapping->terCategory->code;

        $bracket = TerRate::where('ter_category_id', $mapping->ter_category_id)
            ->where('income_lower_bound', '<=', $grossSalary)
            ->where('effective_date', '<=', $periodDate->toDateString())
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $periodDate->toDateString());
            })
            ->where(function ($q) use ($grossSalary) {
                $q->whereNull('income_upper_bound')->orWhere('income_upper_bound', '>=', $grossSalary);
            })
            ->first();

        if (! $bracket) {
            throw new \RuntimeException(
                "Tidak ditemukan ter_rates bracket untuk kategori '{$terCategoryCode}' ".
                "dengan gross_salary {$grossSalary} di periode {$periodDate->toDateString()}."
            );
        }

        $rawDeduction = $grossSalary * ((float) $bracket->rate_percentage / 100);

        $pph21Deduction = round($rawDeduction, 0, PHP_ROUND_HALF_UP);

        return [
            'pph21_deduction' => $pph21Deduction,
            'ter_category_used' => $terCategoryCode,
        ];
    }

    /**
     * Cek attendance completeness untuk seluruh employee aktif di periode ini.
     * Dipanggil terpisah dari processPayrollRun() agar controller bisa tampilkan warning.
     *
     * @return array<int, array{employee_id: int, employee_name: string, expected: int, actual: int}>
     *   Daftar employee dengan attendance kurang lengkap. Array kosong = semua lengkap.
     */
    public function checkAttendanceCompleteness(PayrollPeriod $period): array
    {
        $periodStart = Carbon::create($period->period_year, $period->period_month, 1)->startOfMonth();
        $nextPeriodStart = $periodStart->copy()->addMonthNoOverflow()->startOfMonth();
        $workingDays = $this->calculateWorkingDays($period->period_year, $period->period_month);

        $incomplete = [];
        $activeEmployees = Employee::where('employment_status', 'active')->get();

        foreach ($activeEmployees as $employee) {
            $actualCount = Attendance::where('employee_id', $employee->id)
                ->where('date', '>=', $periodStart->toDateString())
                ->where('date', '<', $nextPeriodStart->toDateString())
                ->count();

            if ($actualCount < $workingDays) {
                $incomplete[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'expected' => $workingDays,
                    'actual' => $actualCount,
                ];
            }
        }

        return $incomplete;
    }

    /**
     * Proses payroll run untuk satu period, seluruh employee employment_status = active.
     * Idempotent: employee yang sudah punya payroll_run di period ini di-skip
     *
     * $forceIncomplete = true, caller sudah lihat checkAttendanceCompleteness() dan tetap lanjut
     *
     * @return array{processed: int, skipped: int, failed: array<int, array{employee_id: int, error: string}>}
     */
    public function processPayrollRun(PayrollPeriod $period, bool $forceIncomplete = false): array
    {
        if (! $forceIncomplete) {
            $incomplete = $this->checkAttendanceCompleteness($period);
            if (! empty($incomplete)) {
                throw new \RuntimeException(
                    'Attendance belum lengkap untuk '.count($incomplete).' employee. '.
                    'Panggil ulang dengan forceIncomplete=true untuk lanjut (hari kosong dianggap present).'
                );
            }
        }

        $periodDate = Carbon::create($period->period_year, $period->period_month, 1);
        $activeEmployees = Employee::where('employment_status', 'active')->get();

        $processed = 0;
        $skipped = 0;
        $failed = [];

        foreach ($activeEmployees as $employee) {
            // Idempotency guard: skip kalau payroll_run untuk employee+period sudah ada
            $alreadyProcessed = PayrollRun::where('payroll_period_id', $period->id)
                ->where('employee_id', $employee->id)
                ->exists();

            if ($alreadyProcessed) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($employee, $period, $periodDate) {
                    $prorate = $this->calculateProratedBaseSalary($employee, $period);

                    $gross = $this->calculateGrossSalary(
                        $employee,
                        $prorate['base_salary_prorated'],
                        $periodDate
                    );

                    $bpjs = $this->calculateBpjsDeductions($gross['gross_salary'], $periodDate);

                    $pph21 = $this->calculatePph21($employee, $gross['gross_salary'], $periodDate);

                    $totalDeduction = round(
                        $bpjs['total_bpjs_deduction'] + $pph21['pph21_deduction'],
                        2
                    );

                    $netSalary = round($gross['gross_salary'] - $totalDeduction, 2);

                    $payrollRun = PayrollRun::create([
                        'payroll_period_id' => $period->id,
                        'employee_id' => $employee->id,
                        'working_days' => $prorate['working_days'],
                        'absent_days' => $prorate['absent_days'],
                        'base_salary' => $prorate['base_salary_prorated'],
                        'gross_salary' => $gross['gross_salary'],
                        'bpjs_kesehatan_deduction' => $bpjs['bpjs_kesehatan_deduction'],
                        'bpjs_jht_deduction' => $bpjs['bpjs_jht_deduction'],
                        'bpjs_jp_deduction' => $bpjs['bpjs_jp_deduction'],
                        'pph21_deduction' => $pph21['pph21_deduction'],
                        'ter_category_used' => $pph21['ter_category_used'],
                        'total_deduction' => $totalDeduction,
                        'net_salary' => $netSalary,
                        'status' => 'finalized',
                    ]);

                    // Breakdown item: base salary (prorated) sebagai baris earning
                    // eksplisit, supaya slip gaji bisa dijelaskan
                    PayrollRunItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'payroll_component_id' => null,
                        'label' => "Base Salary (Prorated {$prorate['working_days']}-{$prorate['absent_days']}/{$prorate['working_days']} hari kerja)",
                        'amount' => $prorate['base_salary_prorated'],
                        'type' => 'earning',
                    ]);

                    foreach ($gross['earning_breakdown'] as $earning) {
                        PayrollRunItem::create([
                            'payroll_run_id' => $payrollRun->id,
                            'payroll_component_id' => $earning['component_id'],
                            'label' => $earning['label'],
                            'amount' => $earning['amount'],
                            'type' => 'earning',
                        ]);
                    }

                    PayrollRunItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'payroll_component_id' => null,
                        'label' => 'Potongan BPJS Kesehatan',
                        'amount' => $bpjs['bpjs_kesehatan_deduction'],
                        'type' => 'deduction',
                    ]);

                    PayrollRunItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'payroll_component_id' => null,
                        'label' => 'Potongan BPJS JHT',
                        'amount' => $bpjs['bpjs_jht_deduction'],
                        'type' => 'deduction',
                    ]);

                    PayrollRunItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'payroll_component_id' => null,
                        'label' => 'Potongan BPJS JP',
                        'amount' => $bpjs['bpjs_jp_deduction'],
                        'type' => 'deduction',
                    ]);

                    PayrollRunItem::create([
                        'payroll_run_id' => $payrollRun->id,
                        'payroll_component_id' => null,
                        'label' => "Potongan PPh21 (TER Kategori {$pph21['ter_category_used']})",
                        'amount' => $pph21['pph21_deduction'],
                        'type' => 'deduction',
                    ]);
                });

                $processed++;
            } catch (\Throwable $e) {
                $failed[] = [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($processed > 0) {
        $period->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        event(new PayrollProcessed($period->id));
    }

    return [
        'processed' => $processed,
        'skipped' => $skipped,
        'failed' => $failed,
    ];
    }
}
