<?php

namespace App\Shared\Support;

use App\Modules\Identity\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::recordAudit('create', $model, null, static::filterExcluded($model, $model->getAttributes()));
        });

        static::updated(function (Model $model) {
            $dirty = static::filterExcluded($model, $model->getDirty());

            if (empty($dirty)) {
                return;
            }

            $original = collect($dirty)
                ->keys()
                ->mapWithKeys(fn (string $key) => [$key => $model->getOriginal($key)])
                ->toArray();

            static::recordAudit('update', $model, $original, $dirty);
        });

        static::deleted(function (Model $model) {
            static::recordAudit('delete', $model, static::filterExcluded($model, $model->getAttributes()), null);
        });
    }

    protected static function recordAudit(string $action, Model $model, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Buang atribut yang tidak relevan dicatat: field sensitif (password, token)
     * dan timestamp housekeeping (created_at/updated_at) yang selalu berubah tiap save
     * tanpa mencerminkan perubahan bisnis aktual.
     *
     * Override lewat property $auditExcept di Model untuk exclude list custom.
     */
    protected static function filterExcluded(Model $model, array $attributes): array
    {
        $excluded = property_exists($model, 'auditExcept')
            ? $model->auditExcept
            : $model->getHidden();

        $excluded = array_merge($excluded, [
            $model->getCreatedAtColumn(),
            $model->getUpdatedAtColumn(),
        ]);

        return array_diff_key($attributes, array_flip(array_filter($excluded)));
    }
}
