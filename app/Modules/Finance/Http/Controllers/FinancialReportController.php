<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function incomeStatement(Request $request, FinancialReportService $service)
    {
        $this->authorize('finance.report.view');

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEndExclusive = $periodStart->copy()->addMonthNoOverflow();

        $report = $service->incomeStatement($periodStart, $periodEndExclusive);

        return view('finance::reports.income-statement', compact('report', 'month', 'year'));
    }

    public function balanceSheet(Request $request, FinancialReportService $service)
    {
        $this->authorize('finance.report.view');

        $asOfDate = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))
            : Carbon::today();

        $report = $service->balanceSheet($asOfDate);

        return view('finance::reports.balance-sheet', compact('report'));
    }
}
