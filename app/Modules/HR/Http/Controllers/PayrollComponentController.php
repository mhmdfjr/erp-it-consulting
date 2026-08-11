<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StorePayrollComponentRequest;
use App\Modules\HR\Http\Requests\UpdatePayrollComponentRequest;
use App\Modules\HR\Models\PayrollComponent;

class PayrollComponentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', PayrollComponent::class);

        $components = PayrollComponent::orderBy('name')->paginate(20);

        return view('hr::payroll-components.index', compact('components'));
    }

    public function create()
    {
        $this->authorize('create', PayrollComponent::class);

        return view('hr::payroll-components.create');
    }

    public function store(StorePayrollComponentRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        PayrollComponent::create($data);

        return redirect()->route('hr.payroll-components.index')
            ->with('success', 'Payroll component berhasil dibuat.');
    }

    public function edit(PayrollComponent $payrollComponent)
    {
        $this->authorize('update', $payrollComponent);

        return view('hr::payroll-components.edit', [ 'payrollComponent' => $payrollComponent, ]);
    }

    public function update(UpdatePayrollComponentRequest $request, PayrollComponent $payrollComponent)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $payrollComponent->update($data);

        return redirect()->route('hr.payroll-components.index')
            ->with('success', 'Payroll component berhasil diperbarui.');
    }
}
