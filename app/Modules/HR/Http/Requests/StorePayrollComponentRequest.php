<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.payrollcomponent.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'calculation_type' => ['required', Rule::in(['fixed_amount', 'percentage_of_base'])],
            'is_active' => ['boolean'],
        ];
    }
}
