<?php

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Models\PayrollComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeePayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.payrollcomponent.manage');
    }

    public function rules(): array
    {
        return [
            'payroll_component_id' => ['required', 'exists:payroll_components,id'],
            'amount' => ['nullable', 'numeric', 'min:0', 'required_if:calculation_type,fixed_amount'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:calculation_type,percentage_of_base'],
            'effective_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:effective_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $component = PayrollComponent::find($this->input('payroll_component_id'));

            if (! $component) {
                return;
            }

            if ($component->calculation_type === 'fixed_amount' && $this->filled('percentage')) {
                $validator->errors()->add('percentage', 'Component ini bertipe nominal tetap, jangan isi percentage.');
            }

            if ($component->calculation_type === 'percentage_of_base' && $this->filled('amount')) {
                $validator->errors()->add('amount', 'Component ini bertipe persentase, jangan isi amount.');
            }
        });
    }
}
