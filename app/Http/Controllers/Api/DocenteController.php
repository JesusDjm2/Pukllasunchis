<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocenteCursoResource;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Lo que ve un docente desde la app: el mismo resumen y listado de cursos del
 * panel web (DocenteCOntroller@vistaDocente), sin duplicar el criterio de
 * área FID/PPD ni el de "sílabo listo".
 */
class DocenteController extends Controller
{
    public function resumen(Request $request)
    {
        $docente = $this->docente($request);
        $cursos = $this->cursosDelDocente($docente);

        $incidencias = \DB::table('incidencias')
            ->where('docente_id', $docente->id)
            ->selectRaw('count(*) as total, coalesce(sum(estado = ?), 0) as pendientes', ['pendiente'])
            ->first();

        return response()->json([
            'data' => [
                'periodo_actual' => optional(PeriodoActual::actual())->nombre,
                'cursos' => $cursos->count(),
                'cursos_fid' => $cursos->reject(fn (Curso $c) => $this->esPpd($c))->count(),
                'cursos_ppd' => $cursos->filter(fn (Curso $c) => $this->esPpd($c))->count(),
                'cursos_asincronicos' => $docente->cursosEspeciales()->count(),
                'incidencias_total' => (int) $incidencias->total,
                'incidencias_pendientes' => (int) $incidencias->pendientes,
                'notificaciones_sin_leer' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    public function cursos(Request $request)
    {
        $docente = $this->docente($request);
        $periodoActual = PeriodoActual::actual();

        $cursos = $this->cursosDelDocente($docente)
            ->sortBy(fn (Curso $curso) => $curso->nombre)
            ->values()
            ->map(fn (Curso $curso) => new DocenteCursoResource(
                $curso,
                $this->esPpd($curso) ? 'ppd' : 'fid',
                $this->tieneSilabo($curso, $periodoActual),
            ));

        return response()->json(['data' => $cursos]);
    }

    private function docente(Request $request): Docente
    {
        $docente = $request->user()->docente;

        abort_unless(
            $docente,
            404,
            'No se encontró un perfil de docente para este usuario.'
        );

        return $docente;
    }

    /** @return Collection<int, Curso> */
    private function cursosDelDocente(Docente $docente): Collection
    {
        return $docente->cursos()
            ->with(['ciclo.programa', 'silabos', 'silabosPdf'])
            ->get();
    }

    /** Mismo criterio que el panel web: el programa del ciclo define el área. */
    private function esPpd(Curso $curso): bool
    {
        return str_contains($curso->ciclo->programa->nombre ?? '', 'PPD');
    }

    /**
     * Mismo criterio que `docentes.partials.dashboard-curso-fid` (web): solo
     * cuenta un sílabo del periodo actual (o el PDF legacy sin periodo). OJO:
     * `$curso->relacionsilabo` NO entra aquí a propósito — es "el sílabo más
     * reciente sin importar el periodo" (ver `Curso::relacionsilabo()`), y
     * usarlo marcaría como "listo" un curso cuyo único sílabo es de un
     * periodo anterior.
     */
    private function tieneSilabo(Curso $curso, ?PeriodoActual $periodoActual): bool
    {
        $silaboDelPeriodo = $curso->silabos->firstWhere('periodo_actual_id', $periodoActual?->id)
            ?? $curso->silabos->firstWhere('periodo', $periodoActual?->nombre);

        $silaboPdf = $curso->silabosPdf->firstWhere('periodo_actual_id', $periodoActual?->id);

        return (bool) ($silaboDelPeriodo || $silaboPdf || $curso->silabo);
    }
}
