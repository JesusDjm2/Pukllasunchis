<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * A dónde mandar a un usuario ya autenticado que llega a una pantalla que
     * no le corresponde (login/registro con sesión activa, o el redirect
     * genérico del middleware "guest"). Mismo criterio por rol que usa
     * LoginController::authenticated() tras un login exitoso.
     */
    protected function redirectToDashboard(User $user): RedirectResponse
    {
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->route('admin');
        } elseif ($user->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => $user->docente->id]);
        } elseif ($user->hasRole('tutor')) {
            return redirect()->route('tutor.dashboard');
        } elseif ($user->hasRole('adminB')) {
            return redirect()->route('trabajo.index');
        } elseif ($user->hasRole('alumnoB')) {
            return redirect()->route('ppd.index');
        } elseif ($user->hasRole('alumno')) {
            return redirect()->route('alumnos.index');
        } elseif ($user->hasRole('inhabilitado')) {
            return redirect()->route('inhabilitado');
        }

        return redirect('/');
    }
}
