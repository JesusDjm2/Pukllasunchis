<?php

namespace App\Http\Controllers;

use App\Models\Titulacion\SeguimientoTitulacion;
use App\Services\HistorialAcademico;
use Illuminate\Http\Request;

/**
 * Intranet propia para egresados (condicion='Egresado') — deliberadamente
 * separada de la intranet normal de alumno/alumnoB (que asume matrícula
 * activa, cursos del ciclo, etc., cosas que ya no aplican a alguien
 * graduado). Muestra sus datos básicos y "Mi proceso de titulación"
 * (titulacion/_avance_alumno: línea de tiempo, siguiente paso, vigencia del
 * PTI, grupo y aviso de datos incorrectos).
 *
 * Egresado PPD: misma intranet PPD (menú lateral, diseño .al-ui) con su historial
 * de estudios por ciclo y su titulación, sin nada de matrícula ni del año en curso.
 */
class EgresadoIntranetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->condicion === 'Egresado', 403);

        $seguimiento = SeguimientoTitulacion::where('user_id', $user->id)
            ->with('estadoTitulacion', 'pti.asesor', 'tesis.asesor', 'actas', 'resoluciones')
            ->first();

        if ($alumno = $user->alumnoB) {
            $guiaTitulacion = ! $seguimiento && Titulacion\TitulacionController::puedeIniciar($user);
            $cursosPpd = HistorialAcademico::cursosPpdPorCiclo($user);

            return view('egresado.ppd', compact('user', 'alumno', 'seguimiento', 'guiaTitulacion', 'cursosPpd'));
        }

        return view('egresado.intranet', compact('user', 'seguimiento'));
    }
}
