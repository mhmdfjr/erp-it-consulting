<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $table = 'company_profile';

    protected $fillable = [
        'name',
        'npwp',
        'address',
        'phone',
        'email',
        'logo_path',
    ];
}
