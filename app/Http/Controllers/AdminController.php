<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\User;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function login()
    {
        /* if (auth()->check()) {
            if (auth()->user()->hasRole('alumno')) {
                $alumno = auth()->user()->alumno;
                if ($alumno) {
                    return view('alumnos.vistasAlumnos.index', compact('alumno'));
                }
            }
        } */
        return view('admin.login');
    }
    public function index()
    {
        $alumno = auth()->user()->alumno;
        $admins = User::all();
        $totalAlumnos = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumno');
        })->count();
        // Filtrar los usuarios que tengan el rol "adminB"
        /* $adminsB = $admins->filter(function ($user) {
            return $user->hasRole('adminB');
        }); */
        $totalRecords = Alumno::count();
        return view('admin.index', compact('alumno', 'admins', 'totalAlumnos', 'totalRecords'));
    }
    public function alumnos(Request $request)
    {
        $query = Alumno::query();
        $alumno = null;
        $withUser = $request->get('with_user');
        if ($withUser === '1') {
            $query->has('user');
        } elseif ($withUser === '0') {
            $query->doesntHave('user');
        }

        if ($request->has('search')) {
            $searchTerms = explode(' ', $request->input('search'));
            $query->where(function ($subquery) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $subquery->where(function ($nameOrApellidoQuery) use ($term) {
                        $nameOrApellidoQuery->where('nombres', 'like', '%' . $term . '%')
                            ->orWhere('apellidos', 'like', '%' . $term . '%');
                    })
                        ->orWhere('dni', 'like', '%' . $term . '%')
                        ->orWhereHas('programa', function ($programaQuery) use ($term) {
                            $programaQuery->where('nombre', 'like', '%' . $term . '%');
                        });
                }
            });
            $alumno = $query->first();
        }

        $perPage = $request->input('perPage', 10);
        $alumnos = $query->paginate($perPage);
        $alumnos->appends($request->all());
        $totalRecords = Alumno::count();


        if ($alumnos->isEmpty() && !$request->has('search_page')) {
            session()->flash('error', 'No se han encontrado resultados. Se ha buscado un total de ' . $totalRecords . ' registros.');
        }

        return view('alumnos.index', compact('alumno', 'alumnos', 'totalRecords'));
    }
    public function relacionarUsuario($alumnoId)
    {
        $alumno = Alumno::find($alumnoId);
        // Crear un nuevo usuario con los datos del alumno
        $user = new User([
            'name' => $alumno->nombres,
            'apellidos' => $alumno->apellidos,
            'dni' => $alumno->dni,
            'email' => $alumno->email,
            'password' => Hash::make($alumno->dni), // Puedes ajustar esto según tus necesidades
        ]);

        $user->save();

        // Asignar el usuario al alumno
        $alumno->user()->associate($user);
        $alumno->save();

        return redirect()->route('login')->with('success', 'Relación con usuario establecida correctamente.');
    }
    public function asignarRolAlumno($alumnoId)
    {
        $user = User::find($alumnoId);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado.'], 404);
        }

        $user->assignRole('alumno');

        return response()->json(['success' => 'Rol asignado correctamente.']);
    }
    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();
        return view('admin.create', compact('programas', 'ciclos', 'cursos'));
    }
    public function edit($id)
    {
        $admin = User::findOrFail($id);
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $currentProgramId = $admin->programa_id;
        $currentRole = $admin->getRoleNames()->first(); 
        $currentCicloId = $admin->ciclo_id;
        return view('admin.edit', compact('admin', 'programas', 'ciclos', 'currentRole','currentCicloId', 'currentProgramId'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'condicion' => 'nullable|string|max:40',
            'pendiente' => 'nullable|string',
            'perfil'=>'nullable|string',
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:7|confirmed',
            'role' => 'required|string|in:admin,docente,alumno,adminB,alumnoB',
            'programa_id' => 'required_if:role,alumno|exists:programas,id',
            'ciclo_id' => 'required_if:role,alumno|exists:ciclos,id',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'apellidos' => $request->input('apellidos'),
            'dni' => $request->input('dni'),
            'condicion' => $request->input('condicion'),
            'pendiente' => $request->input('pendiente'),
            'perfil' => $request->input('perfil'),
            'beca' => $request->input('beca'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->assignRole($role);
            // Asociar Programa y Ciclo solo si el rol es 'alumno'
            if ($roleName === 'alumno') {
                $user->programa()->associate($request->input('programa_id'));
                $user->ciclo()->associate($request->input('ciclo_id'));
                $user->save();

                // Asignar cursos al usuario
                if ($request->has('cursos')) {
                    $user->cursos()->attach($request->input('cursos'));
                }
            }
        }
        return redirect()->route('admin')->with('success', 'Nuevo usuario creado exitosamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'condicion' => 'nullable|string',
            'perfil' => 'nullable|string',
            'pendiente' => 'nullable|string',
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:7|confirmed',
            'role' => 'required|string|in:admin,docente,alumno',
            'programa_id' => 'required_if:role,alumno|exists:programas,id',
            'ciclo_id' => 'required_if:role,alumno|exists:ciclos,id',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->apellidos = $request->input('apellidos');
        $user->dni = $request->input('dni');
        $user->condicion = $request->input('condicion');
        $user->perfil = $request->input('perfil');
        $user->pendiente = $request->input('pendiente');
        $user->beca = $request->input('beca');
        $user->email = $request->input('email');
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->syncRoles([$role]);
            // Asociar Programa y Ciclo solo si el rol es 'alumno'
            if ($roleName === 'alumno') {
                $user->programa()->associate($request->input('programa_id'));
                $user->ciclo()->associate($request->input('ciclo_id'));
            }
        }
        $user->save();

        return redirect()->route('admin')->with('success', 'Usuario actualizado correctamente');
    }
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $userCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count();

        if ($userCount > 1) {
            $admin->delete();
            return redirect()->route('admin')->with('success', 'Usuario eliminado exitosamente');
        } else {
            return redirect()->route('admin')->with('error', 'No puedes eliminar al último usuario registrado con el rol de admin');
        }
    }
}
