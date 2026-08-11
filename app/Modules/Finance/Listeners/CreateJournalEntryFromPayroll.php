<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Services\JournalEntryService;
use App\Modules\HR\Events\PayrollProcessed;
use App\Modules\HR\Models\BpjsRate;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateJournalEntryFromPayroll implements ShouldQueue
{
    public function __construct(private JournalEntryService $journalEntryService)
    {
    }

    public function handle(PayrollProcessed $event): void
    {
        $period = PayrollPeriod::findOrFail($event->payrollPeriodId);
        $periodDate = Carbon::create($period->period_year, $period->period_month, 1);

        $runs = PayrollRun::where('payroll_period_id', $period->id)->get();

        if ($runs->isEmpty()) {
            // Seharusnya tidak pernah terjadi, event cuma di-fire jika processed > 0 di PayrollService
            // tapi dijaga eksplisit, tidak ada gunanya membuat journal entry kosong.
            return;
        }

        // Sum dari payroll_runs
        $totalGross = (float) $runs->sum('gross_salary');
        $totalNet = (float) $runs->sum('net_salary');
        $totalPph21 = (float) $runs->sum('pph21_deduction');
        $totalBpjsKesehatanEmployee = (float) $runs->sum('bpjs_kesehatan_deduction');
        $totalBpjsJhtEmployee = (float) $runs->sum('bpjs_jht_deduction');
        $totalBpjsJpEmployee = (float) $runs->sum('bpjs_jp_deduction');

        // Hitung ulang company portion (TIDAK tersimpan di payroll_runs
        $bpjsRates = BpjsRate::where('effective_date', '<=', $periodDate->toDateString())
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $periodDate->toDateString());
            })
            ->get()
            ->keyBy('bpjs_type');

        $totalBpjsKesehatanCompany = 0.0;
        $totalBpjsKetenagakerjaanCompany = 0.0; // gabungan JHT+JKK+JKM+JP

        foreach ($runs as $run) {
            $grossSalary = (float) $run->gross_salary;

            $kesehatanRate = $bpjsRates->get('kesehatan');
            if ($kesehatanRate) {
                $basis = $kesehatanRate->max_wage_base !== null
                    ? min($grossSalary, (float) $kesehatanRate->max_wage_base)
                    : $grossSalary;
                $totalBpjsKesehatanCompany += round($basis * ((float) $kesehatanRate->rate_company_percentage / 100), 2);
            }

            foreach (['jht', 'jkk', 'jkm', 'jp'] as $type) {
                $rate = $bpjsRates->get($type);
                if (! $rate) {
                    continue;
                }
                $basis = $rate->max_wage_base !== null
                    ? min($grossSalary, (float) $rate->max_wage_base)
                    : $grossSalary;
                $totalBpjsKetenagakerjaanCompany += round($basis * ((float) $rate->rate_company_percentage / 100), 2);
            }
        }

        $totalBpjsKesehatanCompany = round($totalBpjsKesehatanCompany, 2);
        $totalBpjsKetenagakerjaanCompany = round($totalBpjsKetenagakerjaanCompany, 2);
        $totalBpjsKetenagakerjaanEmployee = round($totalBpjsJhtEmployee + $totalBpjsJpEmployee, 2);

        // Kredit 204/205 = employee portion + company portion digabung
        $creditBpjsKesehatan = round($totalBpjsKesehatanEmployee + $totalBpjsKesehatanCompany, 2);
        $creditBpjsKetenagakerjaan = round($totalBpjsKetenagakerjaanEmployee + $totalBpjsKetenagakerjaanCompany, 2);

        $accountIds = $this->resolveAccountIds([
            '511', '512', '513', '202', '203', '204', '205',
        ]);

        $monthLabel = $periodDate->translatedFormat('F Y');

        $this->journalEntryService->createEntry([
            'entry_date' => $periodDate,
            'reference_type' => 'PayrollPeriod',
            'reference_id' => $period->id,
            'description' => "Payroll {$monthLabel} — {$runs->count()} employee",
            'created_by' => null,
            'lines' => [
                [
                    'account_id' => $accountIds['511'],
                    'debit' => $totalGross,
                    'credit' => 0,
                    'description' => "Beban Gaji dan Tunjangan {$monthLabel}",
                ],
                [
                    'account_id' => $accountIds['512'],
                    'debit' => $totalBpjsKesehatanCompany,
                    'credit' => 0,
                    'description' => "Beban BPJS Kesehatan (Perusahaan) {$monthLabel}",
                ],
                [
                    'account_id' => $accountIds['513'],
                    'debit' => $totalBpjsKetenagakerjaanCompany,
                    'credit' => 0,
                    'description' => "Beban BPJS Ketenagakerjaan (Perusahaan) {$monthLabel}",
                ],
                [
                    'account_id' => $accountIds['202'],
                    'debit' => 0,
                    'credit' => $totalNet,
                    'description' => "Utang Gaji {$monthLabel} (belum dibayar)",
                ],
                [
                    'account_id' => $accountIds['203'],
                    'debit' => 0,
                    'credit' => $totalPph21,
                    'description' => "Utang PPh21 {$monthLabel}",
                ],
                [
                    'account_id' => $accountIds['204'],
                    'debit' => 0,
                    'credit' => $creditBpjsKesehatan,
                    'description' => "Utang BPJS Kesehatan {$monthLabel} (employee+company)",
                ],
                [
                    'account_id' => $accountIds['205'],
                    'debit' => 0,
                    'credit' => $creditBpjsKetenagakerjaan,
                    'description' => "Utang BPJS Ketenagakerjaan {$monthLabel} (employee+company)",
                ],
            ],
        ]);
    }

    /**
     * @param array<int, string> $codes
     * @return array<string, int>
     */
    private function resolveAccountIds(array $codes): array
    {
        $accounts = ChartOfAccount::whereIn('code', $codes)->get()->keyBy('code');

        $missing = array_diff($codes, $accounts->keys()->toArray());
        if (! empty($missing)) {
            throw new \RuntimeException(
                'Akun Chart of Accounts berikut tidak ditemukan: '.implode(', ', $missing).
                '. Pastikan ChartOfAccountsSeeder sudah dijalankan (DATABASE.md Appendix C).'
            );
        }

        return $accounts->map(fn ($account) => $account->id)->toArray();
    }
}
