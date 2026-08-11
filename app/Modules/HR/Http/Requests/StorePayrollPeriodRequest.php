<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.payroll.process');
    }

    public function rules(): array
    {
        return [
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'between:2020,2100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = \App\Modules\HR\Models\PayrollPeriod::where('period_month', $this->input('period_month'))
                ->where('period_year', $this->input('period_year'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('period_month', 'Payroll period untuk bulan/tahun ini sudah ada.');
            }
        });
    }
}
