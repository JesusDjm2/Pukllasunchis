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
        if ($user->hasRole('admin')) {
            return redirect()->route('admin')->with('userData', $user);
        } elseif ($user->hasRole('adminB')) {
            return redirect()->route('trabajo.index')->with('userData', $user);
        } elseif ($user->hasRole('alumnoB')) {
            return redirect()->route('postulante.index')->with('userData', $user);
        } elseif ($user->hasRole('alumno')) {
            $alumno = $user->alumno;
            return redirect()->route('admin')->with('userData', $user)->with('alumno', $alumno);
        } elseif ($user->hasRole('docente')) {
            return redirect()->route('docente')->with('userData', $user);
        } else {
            return redirect('/');
        }
    }
}
