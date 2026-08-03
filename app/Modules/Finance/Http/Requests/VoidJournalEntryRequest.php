<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.journal.void');
    }

    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:5'],
        ];
    }
}
