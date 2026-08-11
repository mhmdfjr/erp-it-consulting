<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreEmployeeRequest;
use App\Modules\HR\Http\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::with('position.department')
            ->orderBy('full_name')
            ->paginate(20);

        return view('hr::employees.index', compact('employees'));
    }

    public function create()
    {
        $this->authorize('create', Employee::class);

        $departments = Department::with('positions')->orderBy('name')->get();

        return view('hr::employees.create', compact('departments'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        Employee::create($request->validated());

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee berhasil dibuat.');
    }

    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);

        $departments = Department::with('positions')->orderBy('name')->get();

        return view('hr::employees.edit', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee berhasil diperbarui.');
    }
}
