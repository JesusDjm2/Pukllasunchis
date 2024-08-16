<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class BolsaController extends Controller
{
    public function index()
    {
        $admins = User::all();
        $adminsB = $admins->filter(function ($user) {
            return $user->hasRole('adminB');
        });
        $alumnos = $admins->filter(function ($user) {
            return $user->hasRole('alumno') || $user->hasRole('alumnoB');
        });
        $cant = $alumnos->count();
        return view('bolsa.index', compact('adminsB', 'alumnos', 'cant', 'admins'));
    }
    public function create()
    {
        $programas = Programa::all(); 
        return view('bolsa.create', compact('programas'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'perfil' => 'nullable|string|max:40', 
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:7|confirmed',
            'role' => 'required|string|in:adminB,alumno',
            'programa_id' => 'required_if:role,alumno|exists:programas,id',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'apellidos' => $request->input('apellidos'),
            'dni' => $request->input('dni'),
            'perfil' => $request->input('perfil'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->assignRole($role);
            if ($roleName === 'alumno') {
                $user->programa()->associate($request->input('programa_id'));
                $user->save();
            }
        }
        return redirect()->route('trabajo.index')->with('success', 'Nuevo usuario creado exitosamente');
    }
    public function edit($id, Request $request)
    {
        $admin = User::findOrFail($id);
        $programas = Programa::all();
        $roles = $admin->roles;
        if ($request->has('origin')) {
            $request->session()->put('origin_url', $request->input('origin'));
        }
        return view('bolsa.edit', compact('admin', 'programas', 'roles'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'perfil' => 'nullable|string|max:40',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string|in:adminB,alumno',
            'programa_id' => 'required_if:role,alumno|exists:programas,id',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->apellidos = $request->input('apellidos');
        $user->dni = $request->input('dni');
        $user->perfil = $request->input('perfil');
        $user->email = $request->input('email');
        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->syncRoles([$role]);
            $user->programa()->associate($request->input('programa_id'));
        }
        $user->save();
        $originUrl = $request->session()->pull('origin_url', 'trabajo.index');

    if ($originUrl == 'listaPostulantes') {
        return redirect()->route('listaPostulantes')->with('success', 'Usuario actualizado correctamente');
    } else {
        return redirect()->route('trabajo.index')->with('success', 'Usuario actualizado correctamente');
    }
        /* return redirect()->route('trabajo.index')->with('success', 'Usuario actualizado correctamente'); */
    }
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $userCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count();

        if ($userCount > 1) {
            $admin->delete();
            return redirect()->route('trabajo.index')->with('success', 'Usuario eliminado exitosamente');
        } else {
            return redirect()->route('trabajo.index')->with('error', 'No puedes eliminar al último usuario registrado con el rol de admin');
        }
    }
}
