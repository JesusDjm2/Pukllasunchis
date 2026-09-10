<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AlumnoAuthController extends Controller
{
    /**
     * Login vía AJAX para los formularios públicos de tutoría/sugerencias —
     * autentica sin navegar fuera de la vista actual. Solo admite cuentas
     * con rol "alumno" (FID); es la identidad que se usará para el envío.
     */
    public function loginInline(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 422);
        }

        if (!$user->hasRole('alumno') || !$user->alumno) {
            return response()->json(['message' => 'Esta opción solo está disponible para alumnos FID.'], 403);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }
}
