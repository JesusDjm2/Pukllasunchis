<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionCampana extends Model
{
    use HasFactory;

    protected $table = 'egresado_campana_asignaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'es_ensayo' => 'boolean',
    ];

    public function campana()
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_user_id');
    }

    public function egresado()
    {
        return $this->belongsTo(User::class, 'egresado_user_id');
    }

    public function asignador()
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    public function docenteAnterior()
    {
        return $this->belongsTo(User::class, 'docente_anterior_id');
    }

    /**
     * Scope para asignaciones con acceso activo vigente (no revocadas ni reasignadas a otro).
     */
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['asignado', 'contactado', 'completado']);
    }

    /**
     * Scope para eventos de ensayo institucional.
     */
    public function scopeEnsayo($query)
    {
        return $query->where('es_ensayo', true);
    }

    /**
     * Scope para eventos reales.
     */
    public function scopeReales($query)
    {
        return $query->where('es_ensayo', false);
    }

    /**
     * Verifica en backend si un docente tiene asignado a un egresado con acceso vigente
     * dentro de una campaña activa.
     */
    public static function esDocenteAsignado(int $docenteUserId, int $egresadoUserId, ?int $campanaId = null): bool
    {
        $query = self::query()
            ->activos()
            ->where('docente_user_id', $docenteUserId)
            ->where('egresado_user_id', $egresadoUserId);

        if ($campanaId) {
            $query->where('campana_id', $campanaId);
        }

        $asignaciones = $query->with('campana')->get();

        if ($asignaciones->isEmpty()) {
            return false;
        }

        $hoy = now()->toDateString();
        foreach ($asignaciones as $asig) {
            if (!$asig->campana) continue;
            if ($asig->campana) {
                $c = $asig->campana;
                if (!in_array(strtolower($c->estado), ['activo', 'activa'], true)) {
                    continue;
                }
                if ($c->fecha_inicio && $c->fecha_inicio > $hoy) {
                    continue;
                }
                if ($c->fecha_fin && $c->fecha_fin < $hoy) {
                    continue;
                }
            }
            return true;
        }

        return false;
    }
}
