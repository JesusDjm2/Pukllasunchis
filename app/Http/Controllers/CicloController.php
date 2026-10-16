<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CicloController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::whereHas('programa')->with(['programa', 'tutores'])->get();

        foreach ($ciclos as $ciclo) {
            $alumnos = $ciclo->alumnos()
                ->whereHas('user', fn ($q) => $q
                    ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
                    ->where(fn ($q) => $q
                        ->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                        ->orWhere(fn ($q2) => $q2
                            ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                            ->where('perfil', '!=', 'Retirado')
                        )
                    )
                )
                ->orderBy('apellidos')
                ->get();

            $alumnosB = User::where(fn ($q) => $q
                ->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                ->orWhere(fn ($q2) => $q2
                    ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                    ->where('perfil', '!=', 'Retirado')
                )
            )
                ->where('ciclo_id', $ciclo->id)
                ->whereHas('alumnoB')
                ->orderBy('apellidos')
                ->get();

            $ciclo->alumnos_validos = $alumnos;
            $ciclo->alumnos_b_validos = $alumnosB;
            $ciclo->alumnos_validos_count = $alumnos->count();
            $ciclo->alumnos_b_validos_count = $alumnosB->count();
        }

        // FID primero y luego PPD (antes era una lista fija de ids 1–5 en la vista).
        $programas = Programa::orderBy('modalidad')->orderBy('id')->get();

        return view('admin.ciclo.index', compact('ciclos', 'programas'));
    }

    public function create()
    {
        $programas = Programa::all();

        return view('admin.ciclo.create', compact('programas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ciclos')->where('programa_id', $request->programa_id),
            ],
            'programa_id' => 'required|exists:programas,id',
        ]);
        Ciclo::create($request->all());

        return redirect()->route('ciclo.index')->with('success', 'Ciclo creado exitosamente');
    }

    /* public function show(Ciclo $ciclo)
    {
        $alumnos = $ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->where('perfil', '!=', 'Sin matrícula');
            })
            ->orderBy('apellidos')
            ->get();
        $alumnosB = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumnoB');
        })
            ->where('ciclo_id', $ciclo->id)
            ->where(function ($q) {
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                })
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'inhabilitado');
                        })->where('perfil', 'Licencia');
                    });
            })
            ->orderBy('apellidos')
            ->get();

        $ciclosDisponibles = Ciclo::where('programa_id', $ciclo->programa_id)->get();
        $cantidadAlumnos = $alumnos->count();
        $cursosConDocentes = $ciclo->cursos()->with('docentes')->get();

        return view('admin.ciclo.show', compact(
            'ciclo',
            'alumnos',
            'alumnosB',
            'cantidadAlumnos',
            'ciclosDisponibles',
            'cursosConDocentes'
        ));
    } */

    public function show(Ciclo $ciclo)
    {
        $alumnos = $ciclo->alumnos()
            ->with(['user', 'user.roles'])
            ->whereHas('user', fn ($q) => $q
                ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
                ->where(fn ($q) => $q
                    ->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                    ->orWhere(fn ($q2) => $q2
                        ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                        ->where('perfil', '!=', 'Retirado')
                    )
                )
            )
            ->orderBy('apellidos')
            ->get();

        $alumnosB = User::where(fn ($q) => $q
            ->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
            ->orWhere(fn ($q2) => $q2
                ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                ->where('perfil', '!=', 'Retirado')
            )
        )
            ->where('ciclo_id', $ciclo->id)
            ->whereHas('alumnoB')
            ->with(['roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get();

        $cantidadAlumnos = $alumnos->count() + $alumnosB->count();

        $ciclosDisponibles = Ciclo::where('programa_id', $ciclo->programa_id)->get();
        $cursosConDocentes = $ciclo->cursos()->with('docentes')->get();

        return view('admin.ciclo.show', compact(
            'ciclo',
            'alumnos',
            'alumnosB',
            'cantidadAlumnos',
            'ciclosDisponibles',
            'cursosConDocentes'
        ));
    }

    public function updateCicloAlumnos(Request $request)
    {
        $request->validate([
            'nuevo_ciclo_id' => 'required|exists:ciclos,id',
            'alumnos' => 'required|array',
            'alumnos.*' => 'exists:users,id',
        ]);

        $nuevoCicloId = $request->input('nuevo_ciclo_id');
        $usuariosIds = $request->input('alumnos');

        $nuevoCiclo = Ciclo::findOrFail($nuevoCicloId);
        $usuarios = User::whereIn('id', $usuariosIds)->get();

        $alumnosNormalesIds = [];
        $omitidosLicencia = [];
        $alumnosPpdIds = [];
        $usuariosValidos = [];

        foreach ($usuarios as $user) {
            if ($user->perfil === 'Licencia') {
                $omitidosLicencia[] = $user->apellidos;
                continue;
            }

            if ($user->alumno && $user->alumno->programa_id === $nuevoCiclo->programa_id) {
                $alumnosNormalesIds[] = $user->alumno->id;
                $usuariosValidos[] = $user->id;
            } elseif ($user->alumnoB && $user->alumnoB->programa_id === $nuevoCiclo->programa_id) {
                $alumnosPpdIds[] = $user->alumnoB->id;
                $usuariosValidos[] = $user->id;
            }
        }

        // Actualizar ciclo_id solo para los usuarios válidos
        if (! empty($usuariosValidos)) {
            $ciclosAntes = $usuarios->whereIn('id', $usuariosValidos)->pluck('ciclo_id', 'id');
            User::whereIn('id', $usuariosValidos)->update(['ciclo_id' => $nuevoCicloId]);

            // update() en bloque no dispara el observer de User: el cambio de ciclo se
            // registra en la trayectoria a mano.
            foreach ($usuarios->whereIn('id', $usuariosValidos) as $user) {
                $user->ciclo_id = $nuevoCicloId;
                \App\Services\Trayectoria::registrarCambios($user, ['ciclo_id' => $ciclosAntes[$user->id]]);
            }
        }

        if (! empty($alumnosNormalesIds)) {
            Alumno::whereIn('id', $alumnosNormalesIds)->update(['ciclo_id' => $nuevoCicloId]);
        }

        if (! empty($alumnosPpdIds)) {
            ppd::whereIn('id', $alumnosPpdIds)->update(['ciclo_id' => $nuevoCicloId]);
        }

        if (empty($usuariosValidos)) {
            return redirect()->back()->with('warning', 'Ningún alumno pertenece al programa del ciclo seleccionado.');
        }

        $omitidos = $usuarios->count() - count($usuariosValidos);
        $mensaje = count($usuariosValidos).' alumno(s) movido(s) a '.$nuevoCiclo->nombre.'.';
        if ($omitidos > 0) {
            $mensaje .= ' Se omitieron '.$omitidos.': '
                .($omitidosLicencia ? count($omitidosLicencia).' con licencia' : '')
                .($omitidos - count($omitidosLicencia) > 0 ? ($omitidosLicencia ? ' y ' : '').($omitidos - count($omitidosLicencia)).' sin ficha del programa de ese ciclo' : '').'.';
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function edit(Ciclo $ciclo)
    {
        $ciclo->load('programa');
        $programas = Programa::all();
        $proyectos = Proyecto::all();

        return view('admin.ciclo.edit', compact('ciclo', 'programas', 'proyectos'));
    }

    public function update(Request $request, Ciclo $ciclo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'programa_id' => 'required|exists:programas,id',
        ]);

        $ciclo->update([
            'nombre' => $request->nombre,
            'programa_id' => $request->programa_id,
        ]);

        /* if ($request->proyecto_id) {
            $ciclo->proyectos()->sync([$request->proyecto_id]);
        } else {
            $ciclo->proyectos()->detach();
        } */

        return redirect()->route('ciclo.index')->with('success', 'Ciclo actualizado exitosamente');
    }

    public function destroy(Ciclo $ciclo)
    {
        $cursos    = $ciclo->cursos()->count();
        $alumnos   = $ciclo->alumnos()->count();
        $alumnosB  = $ciclo->alumnosB()->count();

        if ($cursos > 0 || $alumnos > 0 || $alumnosB > 0) {
            $detalle = implode(', ', array_filter([
                $cursos   ? "{$cursos} curso(s)"  : null,
                $alumnos  ? "{$alumnos} alumno(s) FID" : null,
                $alumnosB ? "{$alumnosB} alumno(s) PPD" : null,
            ]));

            return redirect()->back()->with(
                'error',
                "No se puede eliminar el ciclo porque tiene registros asociados: {$detalle}. Reasigna o elimina esos registros primero."
            );
        }

        $ciclo->delete();

        return redirect()->route('ciclo.index')->with('success', 'Ciclo eliminado exitosamente');
    }

    /**
     * Marca a todos los alumnos (FID o PPD) de este ciclo como "Egresado"
     * (users.condicion). Pensado para ciclos tipo "Egresados 2026-I": crear
     * el ciclo y mover ahí a la promoción ya no basta por sí solo — sin este
     * paso siguen contando como alumnos activos (se les sigue pidiendo
     * matrícula, aparecen en listados de matriculados, etc., ver
     * AdminController::applyAlumnoFidUserConstraints() y el mismo criterio
     * ya usado en index() de este controlador). Antes esto se hacía a mano
     * con una migración de datos por cada promoción que egresaba; ahora es
     * una acción repetible desde el propio listado de ciclos.
     */
    public function marcarComoEgresados(Ciclo $ciclo)
    {
        $query = User::where('ciclo_id', $ciclo->id)
            ->whereHas('roles', fn ($r) => $r->whereIn('name', ['alumno', 'alumnoB', 'inhabilitado']));
        $recienEgresados = (clone $query)->where(fn ($q) => $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado'))->get();

        $actualizados = $query->update(['condicion' => 'Egresado']);

        // update() en bloque no dispara el observer de User: el egreso se registra a mano,
        // solo para quienes no eran ya egresados.
        foreach ($recienEgresados as $user) {
            $antes = $user->condicion;
            $user->condicion = 'Egresado';
            \App\Services\Trayectoria::registrarCambios($user, ['condicion' => $antes]);
        }

        if ($actualizados === 0) {
            return back()->with('error', 'No se encontró ningún alumno en el ciclo "'.$ciclo->nombre.'" para marcar como egresado.');
        }

        return back()->with('success', $actualizados.' alumno(s) del ciclo "'.$ciclo->nombre.'" marcados como Egresados. Ya no se les pedirá matrícula ni aparecerán en los listados de alumnos activos.');
    }
}
