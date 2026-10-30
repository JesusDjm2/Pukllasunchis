<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class RpCalificacion extends Model
{
    use HasFactory;

    protected $table = 'rp_calificaciones';

    protected $fillable = [
        'acta_id',
        'estudiante_id',
        'curso_id',
        'calificacion',
        'puntaje',
        'observacion',
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
        'puntaje' => 'decimal:2',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(RpActa::class, 'acta_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(RpEstudiante::class, 'estudiante_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(RpCurso::class, 'curso_id');
    }

    /**
     * Resumen de un estudiante en un acta (vista, impresión y Excel usan este mismo cálculo).
     * Créditos, puntaje y promedio ponderado solo cuentan los cursos con nota: un curso sin nota
     * (retirado, licencia) no baja el promedio. Situación: RETIRADO o LICENCIA si la observación del
     * alumno en el acta lo dice (aunque tenga notas parciales) o si no tiene ninguna nota;
     * DESAPROBADO si desaprobó al menos un curso (nota < 11) y APROBADO en otro caso.
     */
    public static function resumen(Collection $califs, ?string $observacionAlumno = null): object
    {
        $calificadas = $califs->filter(fn ($c) => $c->calificacion !== null);
        $creditos = (int) $calificadas->sum(fn ($c) => $c->curso?->creditos ?? 0);
        $puntaje = round((float) $calificadas->sum(fn ($c) => (float) $c->puntaje), 2);
        $desaprobados = $calificadas->filter(fn ($c) => (float) $c->calificacion < 11)->count();
        $obs = mb_strtoupper(trim((string) $observacionAlumno));

        if (str_starts_with($obs, 'RETIRAD')) {
            $situacion = 'RETIRADO';
        } elseif (str_starts_with($obs, 'LICENCIA')) {
            $situacion = 'LICENCIA';
        } elseif ($calificadas->isEmpty()) {
            $situacion = $califs->contains(fn ($c) => mb_strtoupper((string) $c->observacion) === 'LICENCIA') ? 'LICENCIA' : 'RETIRADO';
        } else {
            $situacion = $desaprobados > 0 ? 'DESAPROBADO' : 'APROBADO';
        }

        return (object) [
            'puntaje_total' => $puntaje,
            'total_creditos' => $creditos,
            'promedio_ponderado' => $creditos > 0 ? round($puntaje / $creditos, 2) : null,
            'cursos_desaprobados' => $desaprobados,
            'situacion' => $situacion,
            // Retirado o con licencia: no cuenta como aprobado ni desaprobado
            'retirado' => in_array($situacion, ['RETIRADO', 'LICENCIA'], true),
        ];
    }
}
