<?php

namespace App\Modules\SalesInventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sales.inventory.adjust', $this->route('item'));
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'direction' => ['required', 'in:in,out'],
            'reason_code' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason_code.required' => 'Alasan penyesuaian stok (reason code) wajib diisi.',
        ];
    }
}
