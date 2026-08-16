<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\JournalEntryLine;
use App\Modules\Finance\Models\Payment;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\SalesOrder;
use App\Modules\SalesInventory\Models\SalesOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $start = Carbon::now()->subMonthsNoOverflow(5)->startOfMonth();
        $end = Carbon::now()->addMonthNoOverflow()->startOfMonth();

        $charts = [
            'sales' => $user->can('sales.order.view') ? $this->buildSalesCharts($start, $end) : null,
            'finance' => $user->can('finance.journal.view') ? $this->buildFinanceCharts($start, $end) : null,
            'hr' => $user->can('hr.employee.view') ? $this->buildHrCharts() : null,
        ];

        return view('dashboard', [
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'outstandingInvoice' => $this->getOutstandingInvoice(),
            'activeEmployeeCount' => Employee::where('employment_status', 'active')->count(),
            'lowStockItems' => $this->getLowStockItems(),
            'charts' => $charts,
        ]);
    }

    private function getMonthlyRevenue(): array
    {
        $start = Carbon::now()->startOfMonth();
        $nextMonthStart = Carbon::now()->addMonthNoOverflow()->startOfMonth();

        $revenueAccountIds = ChartOfAccount::where('account_type', 'revenue')
            ->where('is_postable', true)
            ->pluck('id');

        $totals = JournalEntryLine::whereIn('account_id', $revenueAccountIds)
            ->whereHas('journalEntry', function ($query) use ($start, $nextMonthStart) {
                $query->where('status', 'posted')
                    ->where('entry_date', '>=', $start->toDateString())
                    ->where('entry_date', '<', $nextMonthStart->toDateString());
            })
            ->selectRaw('COALESCE(SUM(credit), 0) as total_credit, COALESCE(SUM(debit), 0) as total_debit')
            ->first();

        return [
            'amount' => bcsub((string) $totals->total_credit, (string) $totals->total_debit, 2),
            'period_label' => $start->translatedFormat('F Y'),
        ];
    }

    private function getOutstandingInvoice(): array
    {
        $unpaidQuery = Invoice::where('status', 'unpaid');

        return [
            'count' => (clone $unpaidQuery)->count(),
            'total_amount' => (clone $unpaidQuery)->sum('amount'),
        ];
    }

    private function getLowStockItems()
    {
        $threshold = config('erp.low_stock_threshold', 5);

        return Item::query()
            ->leftJoin('stock_levels', 'stock_levels.item_id', '=', 'items.id')
            ->where('items.item_type', 'physical_good')
            ->where('items.is_active', true)
            ->selectRaw('
                items.*,
                COALESCE(stock_levels.quantity_on_hand, 0) as quantity_on_hand,
                COALESCE(stock_levels.quantity_reserved, 0) as quantity_reserved
            ')
            ->whereRaw('COALESCE(stock_levels.quantity_on_hand, 0) - COALESCE(stock_levels.quantity_reserved, 0) <= ?', [$threshold])
            ->orderByRaw('COALESCE(stock_levels.quantity_on_hand, 0) - COALESCE(stock_levels.quantity_reserved, 0) ASC')
            ->get();
    }

    // Sales charts (permission: sales.order.view)
    private function buildSalesCharts(Carbon $start, Carbon $end): array
    {
        return [
            'monthly_trend' => $this->salesMonthlyTrend($start, $end),
            'item_breakdown' => $this->salesItemBreakdown($start),
            'status_distribution' => $this->salesOrderStatusDistribution(),
        ];
    }

    private function salesMonthlyTrend(Carbon $start, Carbon $end): array
    {
        $currentValues = $this->monthlyRevenueSeries($start, $end);

        $previousYearStart = $start->copy()->subYear();
        $previousYearEnd = $end->copy()->subYear();
        $previousYearValues = $this->monthlyRevenueSeries($previousYearStart, $previousYearEnd);

        return [
            'labels' => array_map(
                fn ($k) => Carbon::createFromFormat('Y-m', $k)->translatedFormat('M'),
                array_keys($currentValues)
            ),
            'current_year_values' => array_values($currentValues),
            'previous_year_values' => array_values($previousYearValues),
            'current_year_label' => $start->format('Y'),
            'previous_year_label' => $previousYearStart->format('Y'),
        ];
    }

    private function monthlyRevenueSeries(Carbon $start, Carbon $end): array
    {
        $buckets = $this->monthBuckets($start, $end);

        SalesOrder::where('status', 'completed')
            ->where('order_date', '>=', $start->toDateString())
            ->where('order_date', '<', $end->toDateString())
            ->get(['order_date', 'total_amount'])
            ->each(function ($order) use (&$buckets) {
                $key = Carbon::parse($order->order_date)->format('Y-m');
                if (array_key_exists($key, $buckets)) {
                    $buckets[$key] += (float) $order->total_amount;
                }
            });

        return $buckets;
    }

    private function salesItemBreakdown(Carbon $start): array
    {
        $rows = SalesOrderItem::query()
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('items', 'items.id', '=', 'sales_order_items.item_id')
            ->where('sales_orders.status', 'completed')
            ->where('sales_orders.order_date', '>=', $start->toDateString())
            ->selectRaw('items.name as item_name, SUM(sales_order_items.subtotal) as total_subtotal')
            ->groupBy('items.name')
            ->orderByDesc('total_subtotal')
            ->get();

        $top = $rows->take(5);
        $rest = $rows->slice(5);

        $labels = $top->pluck('item_name')->all();
        $values = $top->pluck('total_subtotal')->map(fn ($v) => (float) $v)->all();

        if ($rest->isNotEmpty()) {
            $labels[] = 'Lainnya';
            $values[] = (float) $rest->sum('total_subtotal');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function salesOrderStatusDistribution(): array
    {
        $rows = SalesOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        return [
            'labels' => $rows->pluck('status')->map(fn ($s) => Str::headline($s))->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    // Finance charts (permission: finance.journal.view)
    private function buildFinanceCharts(Carbon $start, Carbon $end): array
    {
        return [
            'revenue_expense' => $this->financeMonthlyRevenueExpense($start, $end),
            'invoice_aging' => $this->financeInvoiceAging(),
            'payment_method_distribution' => $this->financePaymentMethodDistribution(),
        ];
    }

    private function financeMonthlyRevenueExpense(Carbon $start, Carbon $end): array
    {
        $buckets = [];
        $cursor = $start->copy();
        while ($cursor->lt($end)) {
            $buckets[$cursor->format('Y-m')] = ['revenue' => 0.0, 'expense' => 0.0];
            $cursor->addMonthNoOverflow();
        }

        JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', 'posted')
            ->whereIn('chart_of_accounts.account_type', ['revenue', 'expense'])
            ->where('journal_entries.entry_date', '>=', $start->toDateString())
            ->where('journal_entries.entry_date', '<', $end->toDateString())
            ->get([
                'journal_entries.entry_date',
                'chart_of_accounts.account_type',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
            ])
            ->each(function ($row) use (&$buckets) {
                $key = Carbon::parse($row->entry_date)->format('Y-m');
                if (! array_key_exists($key, $buckets)) {
                    return;
                }
                if ($row->account_type === 'revenue') {
                    $buckets[$key]['revenue'] += (float) $row->credit - (float) $row->debit;
                } else {
                    $buckets[$key]['expense'] += (float) $row->debit - (float) $row->credit;
                }
            });

        return [
            'labels' => array_map(
                fn ($k) => Carbon::createFromFormat('Y-m', $k)->translatedFormat('M Y'),
                array_keys($buckets)
            ),
            'revenue' => array_column($buckets, 'revenue'),
            'expense' => array_column($buckets, 'expense'),
        ];
    }

    private function financeInvoiceAging(): array
    {
        $today = Carbon::today();

        $buckets = [
            'Belum Jatuh Tempo' => 0.0,
            'Terlambat 1-30 Hari' => 0.0,
            'Terlambat 31-60 Hari' => 0.0,
            'Terlambat > 60 Hari' => 0.0,
        ];

        Invoice::where('status', 'unpaid')->get(['due_date', 'amount'])->each(function ($invoice) use (&$buckets, $today) {
            $dueDate = Carbon::parse($invoice->due_date);
            $daysOverdue = $dueDate->isPast() ? $dueDate->diffInDays($today) : 0;

            $key = match (true) {
                $daysOverdue === 0 => 'Belum Jatuh Tempo',
                $daysOverdue <= 30 => 'Terlambat 1-30 Hari',
                $daysOverdue <= 60 => 'Terlambat 31-60 Hari',
                default => 'Terlambat > 60 Hari',
            };

            $buckets[$key] += (float) $invoice->amount;
        });

        return ['labels' => array_keys($buckets), 'values' => array_values($buckets)];
    }

    private function financePaymentMethodDistribution(): array
    {
        $rows = Payment::selectRaw('COALESCE(payment_method, \'unknown\') as method, SUM(amount) as total_amount')
            ->groupBy('method')
            ->get();

        $labelMap = ['cash' => 'Cash', 'transfer' => 'Transfer', 'unknown' => 'Tidak Diketahui'];

        return [
            'labels' => $rows->pluck('method')->map(fn ($m) => $labelMap[$m] ?? Str::headline($m))->all(),
            'values' => $rows->pluck('total_amount')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    // HR charts (permission: hr.employee.view)
    private function buildHrCharts(): array
    {
        $period = PayrollPeriod::whereIn('status', ['processed', 'paid'])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->first();

        return [
            'headcount_by_department' => $this->hrHeadcountByDepartment(),
            'payroll_cost_breakdown' => $period ? $this->hrPayrollCostBreakdown($period) : null,
            'attendance_breakdown' => $period ? $this->hrAttendanceBreakdown($period) : null,
        ];
    }

    private function hrHeadcountByDepartment(): array
    {
        $rows = Employee::query()
            ->join('positions', 'positions.id', '=', 'employees.position_id')
            ->join('departments', 'departments.id', '=', 'positions.department_id')
            ->where('employees.employment_status', 'active')
            ->selectRaw('departments.name as department_name, COUNT(*) as headcount')
            ->groupBy('departments.name')
            ->orderByDesc('headcount')
            ->get();

        return [
            'labels' => $rows->pluck('department_name')->all(),
            'values' => $rows->pluck('headcount')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function hrPayrollCostBreakdown(PayrollPeriod $period): array
    {
        $totals = PayrollRun::where('payroll_period_id', $period->id)
            ->selectRaw('
                COALESCE(SUM(net_salary), 0) as net_salary,
                COALESCE(SUM(pph21_deduction), 0) as pph21,
                COALESCE(SUM(bpjs_kesehatan_deduction + bpjs_jht_deduction + bpjs_jp_deduction), 0) as bpjs_employee
            ')
            ->first();

        return [
            'period_label' => Carbon::create($period->period_year, $period->period_month, 1)->translatedFormat('F Y'),
            'labels' => ['Net Salary (Take Home)', 'PPh21', 'BPJS (Potongan Karyawan)'],
            'values' => [
                (float) $totals->net_salary,
                (float) $totals->pph21,
                (float) $totals->bpjs_employee,
            ],
        ];
    }

    private function hrAttendanceBreakdown(PayrollPeriod $period): array
    {
        $start = Carbon::create($period->period_year, $period->period_month, 1)->startOfMonth();
        $nextMonthStart = $start->copy()->addMonthNoOverflow();

        $rows = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->where('employees.employment_status', 'active')
            ->where('attendances.date', '>=', $start->toDateString())
            ->where('attendances.date', '<', $nextMonthStart->toDateString())
            ->selectRaw('attendances.status as status, COUNT(*) as total')
            ->groupBy('attendances.status')
            ->get()
            ->keyBy('status');

        $labelMap = ['present' => 'Hadir', 'absent' => 'Absen', 'leave' => 'Cuti', 'sick' => 'Sakit'];

        return [
            'period_label' => $start->translatedFormat('F Y'),
            'labels' => array_values($labelMap),
            'values' => array_map(
                fn ($status) => (int) ($rows[$status]->total ?? 0),
                array_keys($labelMap)
            ),
        ];
    }

    private function monthBuckets(Carbon $start, Carbon $end): array
    {
        $buckets = [];
        $cursor = $start->copy();
        while ($cursor->lt($end)) {
            $buckets[$cursor->format('Y-m')] = 0;
            $cursor->addMonthNoOverflow();
        }
        return $buckets;
    }
}
