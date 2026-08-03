<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.vendorbill.create');
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'account_id' => ['required', 'exists:chart_of_accounts,id'],
            'bill_number' => ['required', 'string', 'max:100'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:bill_date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
