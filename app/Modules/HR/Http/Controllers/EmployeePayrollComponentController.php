<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreEmployeePayrollComponentRequest;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeePayrollComponent;
use App\Modules\HR\Models\PayrollComponent;

class EmployeePayrollComponentController extends Controller
{
    public function index(Employee $employee)
    {
        $this->authorize('update', $employee);

        $assignments = $employee->payrollComponents()->with('payrollComponent')->get();
        $components = PayrollComponent::where('is_active', true)->orderBy('name')->get();

        return view('hr::employees.payroll-components', compact('employee', 'assignments', 'components'));
    }

    public function store(StoreEmployeePayrollComponentRequest $request, Employee $employee)
    {
        EmployeePayrollComponent::create([
            'employee_id' => $employee->id,
            ...$request->validated(),
        ]);

        return redirect()->route('hr.employees.payroll-components.index', $employee)
            ->with('success', 'Component berhasil ditambahkan ke employee.');
    }

    public function destroy(Employee $employee, EmployeePayrollComponent $employeePayrollComponent)
    {
        $this->authorize('update', $employee);

        // Hapus assignment, bukan payroll_run historis yang sudah dihitung dengan component ini.
        // payroll_run_items sudah jadi snapshot independen, tidak berubah retroaktif.
        $employeePayrollComponent->delete();

        return redirect()->route('hr.employees.payroll-components.index', $employee)
            ->with('success', 'Component berhasil dihapus dari employee.');
    }
}
