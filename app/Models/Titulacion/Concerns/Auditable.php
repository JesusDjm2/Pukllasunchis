<?php

namespace App\Models\Titulacion\Concerns;

use App\Models\Titulacion\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Rellena created_by/updated_by/deleted_by automáticamente y deja un
 * registro en titulacion_auditorias (quién, cuándo, qué cambió) en cada
 * creación/actualización/borrado — sin que cada controlador tenga que
 * llamarlo a mano. Requiere que el modelo tenga las columnas
 * created_by/updated_by (deleted_by y SoftDeletes son opcionales).
 *
 * created_by/updated_by/deleted_by se dejan fuera de $fillable a propósito
 * en todos los modelos de este módulo: solo este trait debe poder escribirlos,
 * nunca un request del usuario vía mass-assignment.
 */
trait Auditable
{
    protected static array $auditableColumnsCache = [];

    protected static function auditableHasColumn(Model $model, string $column): bool
    {
        $tabla = $model->getTable();
        if (! array_key_exists($tabla, self::$auditableColumnsCache)) {
            self::$auditableColumnsCache[$tabla] = Schema::getColumnListing($tabla);
        }

        return in_array($column, self::$auditableColumnsCache[$tabla], true);
    }

    public static function bootAuditable(): void
    {
        static::creating(function (Model $model) {
            if (self::auditableHasColumn($model, 'created_by')) {
                $model->created_by = $model->created_by ?? auth()->id();
            }
        });

        static::updating(function (Model $model) {
            if (self::auditableHasColumn($model, 'updated_by')) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function (Model $model) {
            $usaSoftDeletes = method_exists($model, 'isForceDeleting');
            if ($usaSoftDeletes && $model->isForceDeleting()) {
                return;
            }
            if (self::auditableHasColumn($model, 'deleted_by')) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });

        static::created(function (Model $model) {
            self::registrarAuditoria($model, 'creado', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $cambios = $model->getChanges();
            unset($cambios['updated_at']);
            if (empty($cambios)) {
                return;
            }
            self::registrarAuditoria($model, 'actualizado', array_intersect_key($model->getOriginal(), $cambios), $cambios);
        });

        static::deleted(function (Model $model) {
            self::registrarAuditoria($model, 'eliminado', $model->getAttributes(), null);
        });
    }

    protected static function registrarAuditoria(Model $model, string $accion, ?array $anteriores, ?array $nuevos): void
    {
        // Nunca debe romper la operación principal (crear/editar/borrar un
        // registro de titulación) si por algo falla el log de auditoría.
        try {
            Auditoria::create([
                'user_id' => auth()->id(),
                'accion' => $accion,
                'tabla_afectada' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'datos_anteriores' => $anteriores,
                'datos_nuevos' => $nuevos,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar auditoría de titulación: '.$e->getMessage());
        }
    }
}
