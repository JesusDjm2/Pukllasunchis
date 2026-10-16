<?php

namespace App\Http\Controllers;

use App\Models\Titulacion\SeguimientoTitulacion;
use Illuminate\Http\Request;

/**
 * Intranet propia para egresados (condicion='Egresado') — deliberadamente
 * separada de la intranet normal de alumno/alumnoB (que asume matrícula
 * activa, cursos del ciclo, etc., cosas que ya no aplican a alguien
 * graduado). Muestra sus datos básicos y "Mi proceso de titulación"
 * (titulacion/_avance_alumno: línea de tiempo, siguiente paso, vigencia del
 * PTI, grupo y aviso de datos incorrectos).
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

        return view('egresado.intranet', compact('user', 'seguimiento'));
    }
}
