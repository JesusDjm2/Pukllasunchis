<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoEntrevistador extends Model
{
    use HasFactory;

    protected $table = 'egresado_grupos_entrevistadores';

    protected $guarded = ['id'];

    protected $casts = [
        'entrevistadores' => 'array',
        'egresados_ids' => 'array',
    ];

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    /**
     * Verifica en el servidor si un encuestador tiene asignado a un egresado
     * en al menos un grupo activo de seguimiento y dentro de una campaña institucional vigente.
     */
    public static function esEntrevistadorAsignado(int $interviewerId, int $targetUserId): bool
    {
        $grupos = self::with('campana')->where('estado', 'activo')->get();
        $hoy = now()->toDateString();

        foreach ($grupos as $grupo) {
            // Si el grupo está asociado a una campaña institucional, verificar estado y vigencia
            if ($grupo->campana_id && $grupo->campana) {
                $campana = $grupo->campana;
                if (!in_array(strtolower($campana->estado), ['activo', 'activa'], true)) {
                    continue;
                }
                if ($campana->fecha_inicio && $campana->fecha_inicio > $hoy) {
                    continue;
                }
                if ($campana->fecha_fin && $campana->fecha_fin < $hoy) {
                    continue;
                }
            }

            $entrevistadores = $grupo->entrevistadores ?? [];
            $egresados = $grupo->egresados_ids ?? [];

            $tieneEntrevistador = false;
            foreach ($entrevistadores as $ent) {
                $eId = is_array($ent) ? ($ent['id'] ?? null) : (is_object($ent) ? ($ent->id ?? null) : $ent);
                if ((int)$eId === $interviewerId) {
                    $tieneEntrevistador = true;
                    break;
                }
            }

            if (!$tieneEntrevistador) {
                continue;
            }

            $tieneEgresado = false;
            foreach ($egresados as $egr) {
                $egrId = is_string($egr) && str_starts_with($egr, 'REAL-')
                    ? (int) str_replace('REAL-', '', $egr)
                    : (int) $egr;

                if ($egrId === $targetUserId) {
                    $tieneEgresado = true;
                    break;
                }
            }

            if ($tieneEntrevistador && $tieneEgresado) {
                return true;
            }
        }

        return false;
    }
}
