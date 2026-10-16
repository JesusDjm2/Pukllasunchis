<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Docente;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD de Proyectos Integradores (solo admin y super-admin, ver routes/admin.php). Cada ciclo tiene a
 * lo más un proyecto (ciclos.proyecto_id): asignar un ciclo aquí lo saca del proyecto que tuviera.
 * El sílabo y Taq'e leen esta misma relación.
 */
class ProyectoController extends Controller
{
    private const MENSAJES = [
        'nombre.required' => 'Escribe el nombre del Proyecto Integrador.',
        'nombre.max' => 'El nombre no puede pasar de 255 caracteres.',
        'producto.max' => 'El producto no puede pasar de 255 caracteres.',
        'descripcion.required' => 'Escribe la descripción del Proyecto Integrador.',
        'ciclos.*.exists' => 'Uno de los ciclos elegidos no existe.',
        'docentes.*.in' => 'El rol de un docente debe ser Encargado u Organizador.',
    ];

    public function index()
    {
        $proyectos = Proyecto::with(['ciclos.programa', 'docentes'])
            ->when(Proyecto::taqeActivo(), fn ($q) => $q->withCount('taqeGrupos'))
            ->orderBy('id')->get();

        return view('admin.curso.silabos.proyectos.index', compact('proyectos'));
    }

    public function create()
    {
        return view('admin.curso.silabos.proyectos.create', ['ciclos' => $this->ciclosPorPrograma(), 'docentes' => $this->docentesDisponibles(), 'proyecto' => new Proyecto]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, &$proyecto) {
            $proyecto = Proyecto::create($datos);
            $this->asignarCiclos($proyecto, $datos['ciclos']);
            $proyecto->docentes()->sync($datos['docentes']);
        });

        return redirect()->route('proyectos.index')
            ->with('success', 'Proyecto Integrador "'.$proyecto->nombre.'" creado'.$this->resumen($datos).'.');
    }

    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['ciclos.programa', 'docentes']);
        if (Proyecto::taqeActivo()) {
            $proyecto->loadCount('taqeGrupos');
        }

        return view('admin.curso.silabos.proyectos.show', compact('proyecto'));
    }

    public function edit(Proyecto $proyecto)
    {
        return view('admin.curso.silabos.proyectos.edit', ['ciclos' => $this->ciclosPorPrograma(), 'docentes' => $this->docentesDisponibles($proyecto), 'proyecto' => $proyecto]);
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $proyecto) {
            $proyecto->update($datos);
            $this->asignarCiclos($proyecto, $datos['ciclos']);
            $proyecto->docentes()->sync($datos['docentes']);
        });

        return redirect()->route('proyectos.index')
            ->with('success', 'Proyecto Integrador "'.$proyecto->nombre.'" actualizado'.$this->resumen($datos).'.');
    }

    public function destroy(Proyecto $proyecto)
    {
        $nombre = $proyecto->nombre;
        $ciclos = $proyecto->ciclos()->count();

        DB::transaction(function () use ($proyecto) {
            // ciclos.proyecto_id no tiene clave foránea: sin esto quedaban apuntando a un proyecto borrado.
            $proyecto->ciclos()->update(['proyecto_id' => null]);
            $proyecto->delete(); // los grupos Taq'e quedan sin proyecto (FK SET NULL), no se borran
        });

        return redirect()->route('proyectos.index')->with('warning',
            'Proyecto Integrador "'.$nombre.'" eliminado.'.($ciclos ? ' '.$ciclos.' ciclo(s) quedaron sin proyecto: asígnales otro para que sus alumnos puedan formar grupos Taq\'e.' : ''));
    }

    /** Valida y sanea: la descripción es HTML de CKEditor y se limpia con HTMLPurifier. */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'producto' => 'nullable|string|max:255',
            'descripcion' => 'required|string|max:20000',
            'ciclos' => 'nullable|array',
            'ciclos.*' => 'exists:ciclos,id',
            'docentes' => 'nullable|array',
            'docentes.*' => 'nullable|in:encargado,organizador',
        ], self::MENSAJES);

        $datos['descripcion'] = Proyecto::limpiarHtml($datos['descripcion']);
        if (trim(strip_tags($datos['descripcion'])) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['descripcion' => self::MENSAJES['descripcion.required']]);
        }
        $datos['ciclos'] = array_map('intval', $datos['ciclos'] ?? []);

        // docentes[docente_id] = encargado|organizador; vacío = no participa. Ids inexistentes se ignoran.
        $roles = array_filter($datos['docentes'] ?? []);
        $validos = Docente::whereIn('id', array_keys($roles))->pluck('id')->all();
        $datos['docentes'] = collect($roles)->only($validos)->map(fn ($rol) => ['rol' => $rol])->all();

        return $datos;
    }

    /** Deja asignados EXACTAMENTE estos ciclos (antes, desmarcar todos no quitaba ninguno). */
    private function asignarCiclos(Proyecto $proyecto, array $ciclos): void
    {
        Ciclo::where('proyecto_id', $proyecto->id)->whereNotIn('id', $ciclos)->update(['proyecto_id' => null]);
        if ($ciclos) {
            Ciclo::whereIn('id', $ciclos)->update(['proyecto_id' => $proyecto->id]);
        }
    }

    private function resumen(array $datos): string
    {
        $ciclos = count($datos['ciclos']);
        $docentes = count($datos['docentes']);

        return ($ciclos ? ' con '.$ciclos.' ciclo(s)' : ' (sin ciclos asignados)')
            .($docentes ? ' y '.$docentes.' docente(s) a cargo' : '');
    }

    /**
     * Docentes con usuario activo (los inhabilitados no se ofrecen), más los que ya están en el proyecto:
     * si no se listaran, guardar el formulario los quitaría sin que nadie lo decida.
     */
    private function docentesDisponibles(?Proyecto $proyecto = null)
    {
        $asignados = $proyecto?->docentes()->pluck('docentes.id')->all() ?? [];

        return Docente::where(fn ($q) => $q->whereIn('id', $asignados)
                ->orWhereHas('user', fn ($u) => $u->whereDoesntHave('roles', fn ($r) => $r->where('name', 'inhabilitado'))))
            ->orderBy('nombre')->get(['id', 'nombre', 'email']);
    }

    /** Ciclos activos por programa (sin los de egresados, que no forman grupos). */
    private function ciclosPorPrograma()
    {
        return Ciclo::with(['programa', 'proyecto'])
            ->where('nombre', 'not like', 'Egresados%')
            ->orderBy('programa_id')->orderBy('id')
            ->get()
            ->groupBy(fn ($c) => $c->programa->nombre ?? 'Sin programa');
    }
}
