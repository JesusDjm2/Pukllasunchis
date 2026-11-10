<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ArchivosTaqe;
use App\Models\Proyecto;
use App\Models\TaqeGrupo;
use App\Models\TaqeTrabajo;
use Illuminate\Http\Request;

/**
 * Investigaciones para los docentes encargados/organizadores de un Proyecto Integrador (tabla proyecto_docente):
 * ven TODOS los grupos del proyecto, aunque no dicten esos cursos, con sus archivos y ZIP. Solo lectura.
 * Admin y super-admin también pueden entrar.
 */
class TaqeProyectoDocenteController extends Controller
{
    use ArchivosTaqe;

    public function index(Proyecto $proyecto)
    {
        $rol = $this->autorizar($proyecto);

        $proyecto->load(['ciclos.programa', 'docentes']);
        $grupos = $proyecto->taqeGrupos()
            ->with(['curso', 'ciclo', 'programa', 'periodoActual', 'proyecto', 'alumnos', 'evidencias.subidoPor', 'finales.subidoPor'])
            ->orderByDesc('id')->get();

        return view('taqe.proyecto-docente', compact('proyecto', 'grupos', 'rol'));
    }

    public function verTrabajo(Request $request, TaqeTrabajo $trabajo): \Symfony\Component\HttpFoundation\Response
    {
        $this->autorizar($trabajo->grupo->proyecto);

        return $this->servirArchivo($trabajo, $request->boolean('descargar'));
    }

    public function descargarZip(TaqeGrupo $grupo, string $que)
    {
        $this->autorizar($grupo->proyecto);

        return $this->descargarZipGrupo($grupo, $que);
    }

    /** Devuelve el rol del docente en el proyecto ('admin' si entra un administrador); 403 si no está a cargo. */
    private function autorizar(?Proyecto $proyecto): string
    {
        abort_unless($proyecto, 403, 'Este grupo no pertenece a ningún Proyecto Integrador.');
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return 'admin';
        }

        $rol = $user->docente
            ? $proyecto->docentes()->where('docentes.id', $user->docente->id)->value('proyecto_docente.rol')
            : null;
        abort_unless($rol, 403, 'No estás a cargo de este Proyecto Integrador.');

        return $rol;
    }
}
