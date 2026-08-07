<?php

namespace App\Modules\SalesInventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sales.order.cancel', $this->route('order'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
        ];
    }
}
