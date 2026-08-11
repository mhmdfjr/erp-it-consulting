<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\DepartmentFactory;

class Department extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return DepartmentFactory::new();
    }

    protected $fillable = ['name'];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
