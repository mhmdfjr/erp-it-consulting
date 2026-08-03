<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    /**
     * Journal entry cuma punya created_at, tidak ada updated_at,
     * karena entry immutable setelah posted (lihat DATABASE.md Section 3.2).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
        'status',
        'void_reason',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Wajib dipakai di setiap query laporan finansial (laba rugi, neraca)
     * supaya entry yang di-void tidak ikut terhitung (ARCHITECTURE.md Section 5b)
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
