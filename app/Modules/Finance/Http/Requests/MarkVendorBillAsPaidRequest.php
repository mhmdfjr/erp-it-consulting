<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkVendorBillAsPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.vendorbill.pay');
    }

    public function rules(): array
    {
        return [
            'payment_account_id' => ['required', 'exists:chart_of_accounts,id'],
        ];
    }
}
