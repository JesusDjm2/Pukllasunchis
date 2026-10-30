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

    /**
     * Verifica en el servidor si un encuestador tiene asignado a un egresado
     * en al menos un grupo activo de seguimiento.
     */
    public static function esEntrevistadorAsignado(int $interviewerId, int $targetUserId): bool
    {
        $grupos = self::where('estado', 'activo')->get();

        foreach ($grupos as $grupo) {
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
