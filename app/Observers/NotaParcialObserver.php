<?php

namespace App\Observers;

use App\Models\PeriodoDos;
use App\Models\PeriodoTres;
use App\Models\PeriodoUno;
use App\Notifications\CalificacionRegistrada;

/**
 * Observa los 3 modelos de "Período actual" (Parcial 1/2/Promedio — ver
 * CursoController::calificaciones en la API y el comentario ahí sobre por
 * qué son modelos separados de `Periodo`). Se registra para los 3 en
 * AppServiceProvider::boot() en vez de enganchar `->notify()` en cada uno
 * de los 8 sitios de CalificacionController donde se crean/actualizan —
 * así no importa cuál flujo (ingreso individual, CSV masivo, PPD) haya
 * sido, siempre se detecta aquí.
 */
class NotaParcialObserver
{
    private const LABELS = [
        PeriodoUno::class => 'Parcial 1',
        PeriodoDos::class => 'Parcial 2',
        PeriodoTres::class => 'Promedio',
    ];

    public function saved(PeriodoUno|PeriodoDos|PeriodoTres $model): void
    {
        // Evita notificar en cada guardado si no cambió ninguna nota real
        // (p.ej. el docente reabre y vuelve a enviar el mismo formulario).
        $camposRelevantes = ['valoracion_curso', 'calificacion_curso', 'calificacion_sistema'];
        if (! $model->wasRecentlyCreated && ! $model->wasChanged($camposRelevantes)) {
            return;
        }

        if (blank($model->calificacion_curso) && blank($model->calificacion_sistema)) {
            return;
        }

        $alumno = $model->alumno;
        $user = $alumno?->user;
        if (! $user) {
            return;
        }

        $curso = $model->curso;
        $label = self::LABELS[$model::class] ?? 'una nota';

        $user->notify(new CalificacionRegistrada($label, $curso?->id, $curso?->nombre ?? 'tu curso'));
    }
}
