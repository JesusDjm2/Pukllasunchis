<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class RpActa extends Model
{
    use HasFactory;

    protected $table = 'rp_actas';

    protected $fillable = [
        'periodo_id',
        'programa_id',
        'numero_semestre',
        'numero_acta',
        'seccion',
        'turno',
        'fecha_emision',
        'total_creditos',
        'archivo_escaneo_path',
        'observaciones',
    ];

    protected $casts = [
        'numero_semestre' => 'integer',
        'total_creditos' => 'integer',
        'fecha_emision' => 'date',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(RpPeriodo::class, 'periodo_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(RpPrograma::class, 'programa_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(RpCalificacion::class, 'acta_id');
    }

    /** Condición y observación de cada estudiante en esta acta. */
    public function detalles(): HasMany
    {
        return $this->hasMany(RpActaEstudiante::class, 'acta_id');
    }

    // Datos fijos del instituto que imprimen las actas físicas
    public const AUTORIZACION = 'D.S. N° 03-94-ED-04-05-94';
    public const DRE = 'CUSCO';
    public const UBICACION = ['Departamento' => 'CUSCO', 'Provincia' => 'CUSCO', 'Distrito' => 'CUSCO'];

    /**
     * Cuadro estadístico: un estudiante que desaprobó hasta este número de áreas es «subsanante»;
     * con más, «observado». Confirmar con Secretaría Académica.
     */
    public const MAX_AREAS_SUBSANACION = 3;

    /**
     * Datos para mostrar el acta (pantalla, impresión y Excel): cursos (columnas), estudiantes
     * (filas, con notas, condición, observación y resumen) y cuadro estadístico.
     */
    public function matriz(): array
    {
        $this->load(['periodo', 'programa', 'calificaciones.estudiante', 'calificaciones.curso', 'detalles']);
        $detalles = $this->detalles->keyBy('estudiante_id');

        $cursos = $this->calificaciones->pluck('curso')->filter()->unique('id')->sortBy('id')->values();

        $estudiantes = $this->calificaciones
            ->groupBy('estudiante_id')
            ->map(function ($califs, $estudianteId) use ($detalles) {
                $detalle = $detalles->get($estudianteId);

                return (object) array_merge([
                    'estudiante' => $califs->first()->estudiante,
                    'notas' => $califs->keyBy('curso_id'),
                    'observacion' => $detalle?->observacion,
                ], (array) RpCalificacion::resumen($califs, $detalle?->observacion));
            })
            ->sortBy(fn ($item) => $item->estudiante?->apellidos ?? '')
            ->values();

        return ['acta' => $this, 'cursos' => $cursos, 'estudiantes' => $estudiantes, 'cuadro' => self::cuadroEstadistico($estudiantes)];
    }

    /**
     * Cuadro estadístico del pie del acta: por fila (matriculados, invictos, subsanantes, observados,
     * retirados, licencia) cuántos hombres, mujeres y en total, con su % sobre el total de matriculados.
     */
    public static function cuadroEstadistico(Collection $estudiantes): array
    {
        $grupos = [
            'Matriculados' => fn ($e) => true,
            'Invictos' => fn ($e) => $e->situacion === 'APROBADO',
            'Subsanantes' => fn ($e) => $e->situacion === 'DESAPROBADO' && $e->cursos_desaprobados <= self::MAX_AREAS_SUBSANACION,
            'Observados' => fn ($e) => $e->situacion === 'DESAPROBADO' && $e->cursos_desaprobados > self::MAX_AREAS_SUBSANACION,
            'Retirados' => fn ($e) => $e->situacion === 'RETIRADO',
            'Licencia' => fn ($e) => $e->situacion === 'LICENCIA',
        ];
        $total = max(1, $estudiantes->count());
        $pct = fn (int $n) => (int) round($n * 100 / $total);

        $filas = [];
        foreach ($grupos as $nombre => $cumple) {
            $grupo = $estudiantes->filter($cumple);
            $h = $grupo->filter(fn ($e) => $e->estudiante?->sexo === 'M')->count();
            $m = $grupo->filter(fn ($e) => $e->estudiante?->sexo === 'F')->count();
            $t = $grupo->count();
            $filas[$nombre] = ['h' => $h, 'h_pct' => $pct($h), 'm' => $m, 'm_pct' => $pct($m), 't' => $t, 't_pct' => $pct($t)];
        }

        return $filas;
    }
}
