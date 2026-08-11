<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bpjs_type',
        'rate_employee_percentage',
        'rate_company_percentage',
        'max_wage_base',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
    ];
}
