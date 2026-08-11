<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreAttendanceRequest;
use App\Modules\HR\Http\Requests\UpdateAttendanceRequest;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::with('employee')->orderByDesc('date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('month')) {
            $query->whereRaw("to_char(date, 'YYYY-MM') = ?", [$request->input('month')]);
        }

        $attendances = $query->paginate(30)->withQueryString();
        $employees = Employee::where('employment_status', 'active')->orderBy('full_name')->get();

        return view('hr::attendances.index', compact('attendances', 'employees'));
    }

    public function create()
    {
        $this->authorize('create', Attendance::class);

        $employees = Employee::where('employment_status', 'active')->orderBy('full_name')->get();

        return view('hr::attendances.create', compact('employees'));
    }

    public function store(StoreAttendanceRequest $request)
    {
        Attendance::create($request->validated());

        return redirect()->route('hr.attendances.index')
            ->with('success', 'Attendance berhasil dicatat.');
    }

    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        return view('hr::attendances.edit', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $attendance->update($request->validated());

        return redirect()->route('hr.attendances.index')
            ->with('success', 'Attendance berhasil diperbarui.');
    }
}
