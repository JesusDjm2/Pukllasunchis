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
        $ciclos = Ciclo::withCount(['alumnos', 'alumnosB'])
            ->whereIn('programa_id', [1, 2, 3, 4, 5])
            ->get();
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
    public function show(Ciclo $ciclo)
    {
        $alumnos = $ciclo->alumnos()->orderBy('apellidos')->get();
        $alumnosB = $ciclo->alumnosB()->orderBy('apellidos')->get();
        $ciclosDisponibles = Ciclo::where('programa_id', $ciclo->programa_id)
            ->get();
        $cantidadAlumnos = $alumnos->count();
        return view('admin.ciclo.show', compact('ciclo', 'alumnos', 'cantidadAlumnos', 'ciclosDisponibles', 'alumnosB'));
    }

    /* public function updateCicloAlumnos(Request $request)
    {
        $request->validate([
            'nuevo_ciclo_id' => 'required|exists:ciclos,id',
            'alumnos' => 'required|array',
            'alumnos.*' => 'exists:alumnos,id', 
        ]);

        $nuevoCicloId = $request->input('nuevo_ciclo_id');
        $alumnosIds = $request->input('alumnos');

        // Actualizar la tabla 'alumnos'
        Alumno::whereIn('id', $alumnosIds)->update(['ciclo_id' => $nuevoCicloId]);
        $usuariosIds = Alumno::whereIn('id', $alumnosIds)->pluck('user_id')->toArray();
        User::whereIn('id', $usuariosIds)->update(['ciclo_id' => $nuevoCicloId]);

        return redirect()->back()->with('success', 'Ciclo actualizado exitosamente para los alumnos seleccionados.');
    } */
    /* public function updateCicloAlumnos(Request $request)
    {
        $request->validate([
            'nuevo_ciclo_id' => 'required|exists:ciclos,id',
            'alumnos' => 'required|array',
            'alumnos.*' => 'exists:users,id', // ¡Ahora los IDs son de la tabla `users`!
        ]);

        $nuevoCicloId = $request->input('nuevo_ciclo_id');
        $usuariosIds = $request->input('alumnos');

        // Filtrar usuarios por tipo de rol
        $usuarios = User::whereIn('id', $usuariosIds)->get();

        $alumnosNormalesIds = [];
        $alumnosPpdIds = [];

        foreach ($usuarios as $user) {
            if ($user->hasRole('alumno')) {
                // Tiene relación con la tabla alumnos
                if ($user->alumno) {
                    $alumnosNormalesIds[] = $user->alumno->id;
                }
            } elseif ($user->hasRole('alumnoB')) {
                // Tiene relación con la tabla ppd
                if ($user->alumnoB) {
                    $alumnosPpdIds[] = $user->alumnoB->id;
                }
            }
        }

        // Actualizar ciclo_id en tabla users
        User::whereIn('id', $usuariosIds)->update(['ciclo_id' => $nuevoCicloId]);

        // Actualizar también en tabla alumnos (si aplica)
        if (!empty($alumnosNormalesIds)) {
            Alumno::whereIn('id', $alumnosNormalesIds)->update(['ciclo_id' => $nuevoCicloId]);
        }

        // Y en tabla ppd (si aplica)
        if (!empty($alumnosPpdIds)) {
            ppd::whereIn('id', $alumnosPpdIds)->update(['ciclo_id' => $nuevoCicloId]);
        }

        return redirect()->back()->with('success', 'Ciclo actualizado exitosamente para los alumnos seleccionados.');
    } */
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
            if ($user->hasRole('alumno') && $user->alumno) {
                // Validar que el ciclo pertenezca al mismo programa
                if ($user->programa_id === $nuevoCiclo->programa_id) {
                    $alumnosNormalesIds[] = $user->alumno->id;
                    $usuariosValidos[] = $user->id;
                }
            } elseif ($user->hasRole('alumnoB') && $user->alumnoB) {
                // Validar por el programa del modelo PPD
                if ($user->alumnoB->programa_id === $nuevoCiclo->programa_id) {
                    $alumnosPpdIds[] = $user->alumnoB->id;
                    $usuariosValidos[] = $user->id;
                }
            }
        }

        // Actualizar ciclo_id solo para los usuarios válidos
        if (!empty($usuariosValidos)) {
            User::whereIn('id', $usuariosValidos)->update(['ciclo_id' => $nuevoCicloId]);
        }

        if (!empty($alumnosNormalesIds)) {
            Alumno::whereIn('id', $alumnosNormalesIds)->update(['ciclo_id' => $nuevoCicloId]);
        }

        if (!empty($alumnosPpdIds)) {
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
        $ciclo->delete();

        return redirect()->route('ciclo.index')->with('success', 'Ciclo eliminado exitosamente');
    }
}
