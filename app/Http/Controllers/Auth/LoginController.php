<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/Administrador';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->route('admin')->with('userData', $user);
        } elseif ($user->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => $user->docente->id]);
        } elseif ($user->hasRole('tutor')) {
            return redirect()->route('tutor.dashboard');
        } elseif ($user->hasRole('adminB')) {
            return redirect()->route('trabajo.index')->with('userData', $user);
        } elseif ($user->hasRole('gestor-titulacion')) {
            // Gestor puro (sin admin/docente): su único panel es Titulación.
            return redirect()->route('titulacion.index');
        } elseif ($user->condicion === 'Egresado') {
            // Antes de la intranet normal de alumno/alumnoB: esa asume
            // matrícula/ciclo activo (cursos, calificaciones del periodo
            // en curso), lo cual ya no aplica a alguien que se graduó.
            // No aplica si además tiene un rol "más fuerte" arriba
            // (super-admin/admin/docente/tutor/adminB) — esos siguen
            // yendo a su panel normal aunque también sean egresados.
            return redirect()->route('egresado.intranet');
        } elseif ($user->hasRole('alumnoB')) {
            return redirect()->route('ppd.index')->with('userData', $user);
        } elseif ($user->hasRole('alumno')) {
            return redirect()->route('alumnos.index')->with('userData', $user);
        } elseif ($user->hasRole('inhabilitado')) {
            return redirect()->route('inhabilitado')->with('userData', $user);
        } else {
            return redirect('/');
        }
    }
}
