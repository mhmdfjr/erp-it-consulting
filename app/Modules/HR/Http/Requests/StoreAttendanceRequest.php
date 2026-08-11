<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.attendance.manage');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => [
                'required',
                'date',
                Rule::unique('attendances', 'date')->where(
                    fn ($query) => $query->where('employee_id', $this->input('employee_id'))
                ),
            ],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'status' => ['required', Rule::in(['present', 'absent', 'leave', 'sick'])],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.unique' => 'Employee ini sudah punya record attendance di tanggal tersebut.',
        ];
    }
}
