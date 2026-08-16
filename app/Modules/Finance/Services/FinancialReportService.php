<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntryLine;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Laba rugi untuk satu rentang periode [periodStart, periodEndExclusive).
     * Revenue normal balance = credit, Expense normal balance = debit.
     */
    public function incomeStatement(Carbon $periodStart, Carbon $periodEndExclusive): array
    {
        $rows = $this->accountBalanceRows($periodStart, $periodEndExclusive, ['revenue', 'expense']);

        $revenueLines = [];
        $expenseLines = [];
        $totalRevenue = '0.00';
        $totalExpense = '0.00';

        foreach ($rows as $row) {
            if ($row->account_type === 'revenue') {
                $net = bcsub((string) $row->total_credit, (string) $row->total_debit, 2);
                $revenueLines[] = ['code' => $row->code, 'name' => $row->name, 'amount' => $net];
                $totalRevenue = bcadd($totalRevenue, $net, 2);
            } else {
                $net = bcsub((string) $row->total_debit, (string) $row->total_credit, 2);
                $expenseLines[] = ['code' => $row->code, 'name' => $row->name, 'amount' => $net];
                $totalExpense = bcadd($totalExpense, $net, 2);
            }
        }

        return [
            'period_start' => $periodStart,
            'period_end_exclusive' => $periodEndExclusive,
            'revenue_lines' => $revenueLines,
            'expense_lines' => $expenseLines,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => bcsub($totalRevenue, $totalExpense, 2),
        ];
    }

    /**
     * Neraca per satu titik waktu (asOfDate, inklusif). Saldo asset/liability/
     * equity dihitung kumulatif sejak awal (bukan windowed), sesuai sifat
     * neraca sebagai snapshot posisi, bukan laporan arus periode.
     */
    public function balanceSheet(Carbon $asOfDateInclusive): array
    {
        $sinceBeginning = Carbon::create(2000, 1, 1); // cukup jauh mundur, seluruh histori transaksi
        $asOfExclusive = $asOfDateInclusive->copy()->addDay();

        $balanceRows = $this->accountBalanceRows($sinceBeginning, $asOfExclusive, ['asset', 'liability', 'equity']);

        $groups = ['asset' => [], 'liability' => [], 'equity' => []];
        $totals = ['asset' => '0.00', 'liability' => '0.00', 'equity' => '0.00'];

        foreach ($balanceRows as $row) {
            $balance = $row->account_type === 'asset'
                ? bcsub((string) $row->total_debit, (string) $row->total_credit, 2)   // normal balance debit
                : bcsub((string) $row->total_credit, (string) $row->total_debit, 2);  // normal balance credit

            $groups[$row->account_type][] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
            $totals[$row->account_type] = bcadd($totals[$row->account_type], $balance, 2);
        }

        $incomeRows = $this->accountBalanceRows($sinceBeginning, $asOfExclusive, ['revenue', 'expense']);
        $cumulativeRevenue = '0.00';
        $cumulativeExpense = '0.00';
        foreach ($incomeRows as $row) {
            if ($row->account_type === 'revenue') {
                $cumulativeRevenue = bcadd($cumulativeRevenue, bcsub((string) $row->total_credit, (string) $row->total_debit, 2), 2);
            } else {
                $cumulativeExpense = bcadd($cumulativeExpense, bcsub((string) $row->total_debit, (string) $row->total_credit, 2), 2);
            }
        }
        $netIncomeToDate = bcsub($cumulativeRevenue, $cumulativeExpense, 2);

        $totalLiabilityAndEquity = bcadd(bcadd($totals['liability'], $totals['equity'], 2), $netIncomeToDate, 2);
        $isBalanced = bccomp($totals['asset'], $totalLiabilityAndEquity, 2) === 0;

        return [
            'as_of_date' => $asOfDateInclusive,
            'asset_lines' => $groups['asset'],
            'liability_lines' => $groups['liability'],
            'equity_lines' => $groups['equity'],
            'total_asset' => $totals['asset'],
            'total_liability' => $totals['liability'],
            'total_equity' => $totals['equity'],
            'net_income_to_date' => $netIncomeToDate,
            'total_liability_and_equity' => $totalLiabilityAndEquity,
            'is_balanced' => $isBalanced,
        ];
    }

    /**
     * @param array<string> $accountTypes
     */
    private function accountBalanceRows(Carbon $start, Carbon $endExclusive, array $accountTypes)
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '>=', $start->toDateString())
            ->where('journal_entries.entry_date', '<', $endExclusive->toDateString())
            ->whereIn('chart_of_accounts.account_type', $accountTypes)
            ->where('chart_of_accounts.is_postable', true)
            ->selectRaw('
                chart_of_accounts.code as code,
                chart_of_accounts.name as name,
                chart_of_accounts.account_type as account_type,
                COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit
            ')
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.account_type')
            ->orderBy('chart_of_accounts.code')
            ->get();
    }
}
