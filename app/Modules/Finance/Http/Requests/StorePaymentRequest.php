<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.invoice.pay', $this->route('invoice'));
    }

    public function rules(): array
    {
        $invoice = $this->route('invoice');
        $remaining = bcsub((string) $invoice->amount, (string) $invoice->payments()->sum('amount'), 2);

        return [
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$remaining],
            'payment_method' => ['nullable', 'in:cash,transfer'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'Jumlah pembayaran melebihi sisa tagihan invoice ini.',
        ];
    }
}
