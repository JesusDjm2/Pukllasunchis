<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursosEspeciales\CursoEspecialResource;
use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeInscripcion;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeProgresoAlumno;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Http\Request;

class CursoEspecialController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $cursos = CursoEspecial::where('activo', true)->orderBy('orden')->get();

        $inscritoIds = CeInscripcion::where('user_id', $userId)
            ->pluck('curso_especial_id')
            ->flip();

        $cursos->each(function (CursoEspecial $curso) use ($userId, $inscritoIds) {
            $curso->inscrito = $inscritoIds->has($curso->id);
            $curso->porcentaje = $curso->inscrito ? $this->calcularPorcentaje($userId, $curso) : null;
        });

        return response()->json(['data' => CursoEspecialResource::collection($cursos)]);
    }

    public function inscribir(Request $request, CursoEspecial $curso)
    {
        CeInscripcion::firstOrCreate(
            ['user_id' => $request->user()->id, 'curso_especial_id' => $curso->id],
            ['inscrito_at' => now()]
        );

        return response()->json(['data' => ['message' => 'Inscripción realizada correctamente.']]);
    }

    public function show(Request $request, CursoEspecial $curso)
    {
        $this->verificarInscripcion($request, $curso);

        $userId = $request->user()->id;
        $curso->load('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $leccionesIds = $this->leccionesIds($curso);
        $ejerciciosIds = $this->ejerciciosIds($curso);

        $leccionesCompletadas = CeProgresoAlumno::where('user_id', $userId)
            ->whereIn('ce_leccion_id', $leccionesIds)
            ->where('completado', true)
            ->pluck('ce_leccion_id')
            ->flip();

        $ejerciciosCompletados = CeProgresoAlumno::where('user_id', $userId)
            ->whereIn('ce_ejercicio_id', $ejerciciosIds)
            ->where('completado', true)
            ->pluck('ce_ejercicio_id')
            ->flip();

        $niveles = $curso->niveles->map(function ($nivel) use ($leccionesCompletadas, $ejerciciosCompletados) {
            return [
                'id' => $nivel->id,
                'nombre' => $nivel->nombre,
                'orden' => $nivel->orden,
                'unidades' => $nivel->unidades->map(function ($unidad) use ($leccionesCompletadas, $ejerciciosCompletados) {
                    return [
                        'id' => $unidad->id,
                        'nombre' => $unidad->nombre,
                        'descripcion' => $unidad->descripcion,
                        'orden' => $unidad->orden,
                        'lecciones' => $unidad->lecciones->map(fn ($l) => [
                            'id' => $l->id,
                            'nombre' => $l->nombre,
                            'tipo' => $l->tipo,
                            'duracion_min' => $l->duracion_min,
                            'orden' => $l->orden,
                            'completada' => $leccionesCompletadas->has($l->id),
                        ]),
                        'ejercicios' => $unidad->ejercicios->map(fn ($e) => [
                            'id' => $e->id,
                            'tipo' => $e->tipo,
                            'pregunta' => $e->pregunta,
                            'opciones' => $e->opciones,
                            'puntaje_max' => $e->puntaje_max,
                            'orden' => $e->orden,
                            // respuesta_correcta NUNCA se expone al cliente.
                            'completado' => $ejerciciosCompletados->has($e->id),
                        ]),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => [
                'id' => $curso->id,
                'nombre' => $curso->nombre,
                'descripcion' => $curso->descripcion,
                'imagen_url' => $curso->imagen_url,
                'porcentaje' => $this->calcularPorcentaje($userId, $curso),
                'niveles' => $niveles,
            ],
        ]);
    }

    public function progreso(Request $request, CursoEspecial $curso)
    {
        $this->verificarInscripcion($request, $curso);

        $userId = $request->user()->id;
        $curso->load('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $leccionesIds = $this->leccionesIds($curso);
        $ejerciciosIds = $this->ejerciciosIds($curso);

        $totalLecciones = $leccionesIds->count();
        $totalEjercicios = $ejerciciosIds->count();
        $completadasL = CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_leccion_id', $leccionesIds)->where('completado', true)->count();
        $completadasE = CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_ejercicio_id', $ejerciciosIds)->where('completado', true)->count();

        return response()->json([
            'data' => [
                'curso_id' => $curso->id,
                'total_lecciones' => $totalLecciones,
                'lecciones_completadas' => $completadasL,
                'total_ejercicios' => $totalEjercicios,
                'ejercicios_completados' => $completadasE,
                'porcentaje' => $this->calcularPorcentaje($userId, $curso),
            ],
        ]);
    }

    public function completarLeccion(Request $request, CeLeccion $leccion)
    {
        $curso = $leccion->unidad->nivel->curso;
        $this->verificarInscripcion($request, $curso);

        CeProgresoAlumno::marcarLeccionCompletada($request->user()->id, $leccion->id);

        return response()->json(['data' => ['message' => 'Lección marcada como completada.']]);
    }

    public function responderEjercicio(Request $request, CeEjercicio $ejercicio)
    {
        $curso = $ejercicio->unidad->nivel->curso;
        $this->verificarInscripcion($request, $curso);

        $request->validate(['respuesta' => 'required|string']);

        $correcta = $ejercicio->esCorrecta($request->respuesta);
        $puntaje = $correcta ? $ejercicio->puntaje_max : 0;

        CeProgresoAlumno::registrarEjercicio($request->user()->id, $ejercicio->id, $puntaje, $correcta);

        return response()->json([
            'data' => [
                'correcta' => $correcta,
                'puntaje' => $puntaje,
            ],
        ]);
    }

    private function leccionesIds(CursoEspecial $curso)
    {
        return $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->lecciones))->pluck('id');
    }

    private function ejerciciosIds(CursoEspecial $curso)
    {
        return $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->ejercicios))->pluck('id');
    }

    private function calcularPorcentaje(int $userId, CursoEspecial $curso): int
    {
        $curso->loadMissing('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $leccionesIds = $this->leccionesIds($curso);
        $ejerciciosIds = $this->ejerciciosIds($curso);

        $total = $leccionesIds->count() + $ejerciciosIds->count();
        if ($total === 0) {
            return 0;
        }

        $completadasL = $leccionesIds->isNotEmpty()
            ? CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_leccion_id', $leccionesIds)->where('completado', true)->count()
            : 0;
        $completadasE = $ejerciciosIds->isNotEmpty()
            ? CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_ejercicio_id', $ejerciciosIds)->where('completado', true)->count()
            : 0;

        return (int) round(($completadasL + $completadasE) / $total * 100);
    }

    private function verificarInscripcion(Request $request, CursoEspecial $curso): void
    {
        $inscrito = CeInscripcion::where('user_id', $request->user()->id)
            ->where('curso_especial_id', $curso->id)
            ->exists();

        if (! $inscrito) {
            abort(403, 'No estás inscrito en este curso.');
        }
    }
}
