<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.employee.create');
    }

    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20'],
            'npwp' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'max:10'],
            'birth_date' => ['required', 'date'],
            'ptkp_status' => ['required', Rule::in([
                'TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3',
            ])],
            'position_id' => ['required', 'exists:positions,id'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'hire_date' => ['required', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated'])],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
