<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'array',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (SystemSetting $setting) {
            $setting->updated_at = now();
        });
    }
}
