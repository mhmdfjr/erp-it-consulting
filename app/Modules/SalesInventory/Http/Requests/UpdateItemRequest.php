<?php

namespace App\Modules\SalesInventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sales.item.update', $this->route('item'));
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'sku')->ignore($this->route('item')->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'item_type' => ['required', 'in:physical_good,service'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit_of_measure' => ['required', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'required_if:item_type,physical_good'],
        ];
    }
}
