<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mencatat setiap create/update/delete/restore model ke tabel `audit_logs`.
 *
 * Atribut tersembunyi (misalnya password) tidak pernah disimpan nilainya;
 * perubahannya hanya ditandai sebagai `[disamarkan]`.
 *
 * @mixin Model
 */
trait Auditable
{
    /**
     * Atribut yang tidak dicatat sama sekali karena berubah secara rutin.
     *
     * @var array<int, string>
     */
    protected static array $auditExcludedAttributes = ['created_at', 'updated_at', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            AuditLog::record('created', $model, newValues: $model->getAuditableValues($model->getAttributes()));
        });

        static::updated(function (Model $model): void {
            $newValues = $model->getAuditableValues($model->getChanges());

            if ($newValues === []) {
                return;
            }

            $oldValues = $model->getAuditableValues(array_intersect_key($model->getRawOriginal(), $newValues));

            AuditLog::record('updated', $model, $oldValues, $newValues);
        });

        static::deleted(function (Model $model): void {
            $isForceDeleting = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();

            AuditLog::record(
                $isForceDeleting ? 'force_deleted' : 'deleted',
                $model,
                oldValues: $model->getAuditableValues($model->getRawOriginal()),
            );
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (Model $model): void {
                AuditLog::record('restored', $model);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function getAuditableValues(array $attributes): array
    {
        $values = array_diff_key($attributes, array_flip(static::$auditExcludedAttributes));

        foreach (array_intersect_key($values, array_flip($this->getHidden())) as $key => $value) {
            $values[$key] = '[disamarkan]';
        }

        return $values;
    }
}
