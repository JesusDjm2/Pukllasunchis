<?php

namespace App\Http\Controllers;

use App\Models\BolsaTrabajoOferta;
use App\Models\Comunicado;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\TrayectoriaEvento;
use App\Services\HistorialAcademico;
use Illuminate\Http\Request;

/**
 * Intranet propia para egresados (condicion='Egresado') — separada de la intranet de alumno/alumnoB
 * (que asume matrícula activa, cursos del ciclo, sílabos…). Lo que le sirve a un egresado:
 *  - «Mi proceso de titulación» (titulacion/_avance_alumno) o, si aún no lo inicia, la guía con su primer paso.
 *  - Su historial de estudios (notas oficiales archivadas) y su trayectoria (egreso, licencias…).
 *  - Oportunidades: bolsa de trabajo y comunicados recientes.
 *  - Sus datos.
 *
 * Mismo diseño (.al-ui) y menú lateral de su intranet: egresado PPD en layouts.profesionalizacion,
 * egresado FID en layouts.alumno (los dos muestran un menú de egresado).
 */
class EgresadoIntranetController extends Controller
{
    /** Cuántas ofertas y comunicados recientes se muestran en el panel. */
    private const RECIENTES = 3;

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->condicion === 'Egresado', 403);

        $seguimiento = SeguimientoTitulacion::where('user_id', $user->id)
            ->with('estadoTitulacion', 'pti.asesor', 'tesis.asesor', 'actas', 'resoluciones')
            ->first();
        $comun = [
            'eventos' => TrayectoriaEvento::where('user_id', $user->id)->whereNotIn('tipo', ['observacion'])
                ->orderByRaw('COALESCE(fecha_inicio, created_at)')->get(),
            'ofertas' => BolsaTrabajoOferta::orderByDesc('created_at')->orderByDesc('id')->limit(self::RECIENTES)->get(),
            'comunicados' => Comunicado::orderByDesc('fecha_publicacion')->orderByDesc('id')->limit(self::RECIENTES)->get(),
            'promocion' => self::promocion($user),
        ];

        if ($alumno = $user->alumnoB) {
            // Un egresado siempre puede iniciar su titulación (puedeIniciar() mira el ciclo, y el de «Egresados» no cuenta).
            $guiaTitulacion = ! $seguimiento;
            $cursosPpd = HistorialAcademico::cursosPpdPorCiclo($user);

            return view('egresado.ppd', compact('user', 'alumno', 'seguimiento', 'guiaTitulacion', 'cursosPpd') + $comun);
        }

        // Egresado FID: un egresado siempre puede iniciar su titulación (no depende del ciclo).
        $alumno = $user->alumno;
        $historial = HistorialAcademico::de($user);
        $periodos = $historial['periodos']->where('modalidad', 'FID')->values();

        return view('egresado.intranet', compact('user', 'alumno', 'seguimiento', 'historial', 'periodos') + $comun);
    }

    /** Promoción según su ciclo de egresados («Egresados 2025-I» → «2025-I»). */
    public static function promocion(\App\Models\User $user): ?string
    {
        $nombre = (string) $user->ciclo?->nombre;

        return preg_match('/egresad[oa]s?\s+(.+)$/iu', $nombre, $m) ? trim($m[1]) : null;
    }
}
