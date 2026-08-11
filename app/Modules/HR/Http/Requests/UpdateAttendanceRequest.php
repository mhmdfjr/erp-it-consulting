<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.attendance.manage');
    }

    public function rules(): array
    {
        return [
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'status' => ['required', Rule::in(['present', 'absent', 'leave', 'sick'])],
            'note' => ['nullable', 'string'],
        ];
    }
}
