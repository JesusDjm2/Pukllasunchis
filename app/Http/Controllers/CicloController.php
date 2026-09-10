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
        $ciclos = Ciclo::whereIn('programa_id', [1, 2, 3, 4, 5])->get();

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

        return view('admin.ciclo.index', compact('ciclos'));
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
        $alumnosPpdIds = [];
        $usuariosValidos = [];

        foreach ($usuarios as $user) {
            if ($user->perfil === 'Licencia') {
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
            User::whereIn('id', $usuariosValidos)->update(['ciclo_id' => $nuevoCicloId]);
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

        return redirect()->back()->with('success', 'Ciclo actualizado exitosamente para los alumnos válidos.');
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
}
