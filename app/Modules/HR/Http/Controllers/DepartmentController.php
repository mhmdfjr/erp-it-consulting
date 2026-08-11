<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreDepartmentRequest;
use App\Modules\HR\Http\Requests\UpdateDepartmentRequest;
use App\Modules\HR\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('positions')->orderBy('name')->paginate(20);

        return view('hr::departments.index', compact('departments'));
    }

    public function create()
    {
        $this->authorize('create', Department::class);

        return view('hr::departments.create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());

        return redirect()->route('hr.departments.index')
            ->with('success', 'Department berhasil dibuat.');
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        return view('hr::departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return redirect()->route('hr.departments.index')
            ->with('success', 'Department berhasil diperbarui.');
    }
}
