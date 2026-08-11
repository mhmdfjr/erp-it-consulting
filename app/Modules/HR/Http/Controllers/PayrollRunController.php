<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StorePayrollPeriodRequest;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(private PayrollService $payrollService)
    {
    }

    public function index()
    {
        $this->authorize('viewAny', PayrollPeriod::class);

        $periods = PayrollPeriod::withCount('payrollRuns')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(20);

        return view('hr::payroll-runs.index', compact('periods'));
    }

    public function create()
    {
        $this->authorize('create', PayrollPeriod::class);

        return view('hr::payroll-runs.create');
    }

    public function store(StorePayrollPeriodRequest $request)
    {
        $period = PayrollPeriod::create($request->validated());

        return redirect()->route('hr.payroll-runs.show', $period)
            ->with('success', 'Payroll period berhasil dibuat.');
    }

    public function show(PayrollPeriod $period)
    {
        $this->authorize('viewAny', PayrollPeriod::class);

        $runs = $period->payrollRuns()->with('employee')->orderBy('id')->get();

        $incompleteAttendance = $period->status === 'draft'
            ? $this->payrollService->checkAttendanceCompleteness($period)
            : [];

        return view('hr::payroll-runs.show', compact('period', 'runs', 'incompleteAttendance'));
    }

    public function process(Request $request, PayrollPeriod $period)
    {
        $this->authorize('process', PayrollPeriod::class);

        $force = $request->boolean('force');

        try {
            $result = $this->payrollService->processPayrollRun($period, forceIncomplete: $force);
        } catch (\RuntimeException $e) {
            // Attendance belum lengkap dan force belum di-set, forward ke show
            // biarkan view render daftar incomplete + tombol process anyway
            return redirect()->route('hr.payroll-runs.show', $period)
                ->with('warning', $e->getMessage());
        }

        $message = "Payroll run selesai: {$result['processed']} employee diproses";
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} sudah diproses sebelumnya (di-skip)";
        }
        if (! empty($result['failed'])) {
            $message .= ", ".count($result['failed'])." employee GAGAL diproses — cek detail di bawah.";
        }

        return redirect()->route('hr.payroll-runs.show', $period)
            ->with(empty($result['failed']) ? 'success' : 'warning', $message)
            ->with('failedEmployees', $result['failed']);
    }

    public function markAsPaid(PayrollPeriod $period)
    {
        $this->authorize('markAsPaid', PayrollPeriod::class);

        // Idempotent: cuma update run yang 'finalized', skip yang 'paid'
        // tidak memanggil JournalEntryService, accrual sudah tercatat processPayrollRun
        $updated = PayrollRun::where('payroll_period_id', $period->id)
            ->where('status', 'finalized')
            ->update(['status' => 'paid']);

        if ($updated > 0 && $period->status !== 'paid') {
            $period->update(['status' => 'paid']);
        }

        return redirect()->route('hr.payroll-runs.show', $period)
            ->with('success', "{$updated} payroll run ditandai sebagai paid.");
    }

    public function slip(PayrollRun $run)
    {
        $this->authorize('viewAny', PayrollPeriod::class);

        $run->load('employee.position.department', 'payrollPeriod', 'items');

        $earnings = $run->items->where('type', 'earning');
        $deductions = $run->items->where('type', 'deduction');

        return view('hr::payroll-runs.slip', compact('run', 'earnings', 'deductions'));
    }

    public function cancel(PayrollPeriod $period)
    {
        $this->authorize('cancel', PayrollPeriod::class);

        if ($period->status !== 'draft') {
            return redirect()->route('hr.payroll-runs.show', $period)
                ->with('warning', 'Payroll period yang sudah diproses tidak bisa dibatalkan lewat sini.');
        }

        if ($period->payrollRuns()->exists()) {
            return redirect()->route('hr.payroll-runs.show', $period)
                ->with('warning', 'Period ini ternyata sudah punya payroll run tersimpan, tidak bisa dibatalkan lewat sini.');
        }

        $period->delete();

        return redirect()->route('hr.payroll-runs.index')
            ->with('success', 'Payroll period dibatalkan.');
    }
}
