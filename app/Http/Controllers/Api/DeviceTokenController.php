<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

/**
 * Registro de tokens FCM para push notifications. El mismo token físico
 * (dispositivo + instalación de la app) es único: si se reasigna a otro
 * usuario (otro alumno inicia sesión en el mismo celular) se reescribe el
 * `user_id`, para no enviarle push de una cuenta que ya cerró sesión ahí.
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|in:android,ios',
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            ['user_id' => $request->user()->id, 'platform' => $validated['platform'] ?? 'android']
        );

        return response()->json(['message' => 'Token registrado.'], 201);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate(['token' => 'required|string|max:255']);

        $request->user()->deviceTokens()->where('token', $validated['token'])->delete();

        return response()->json(['message' => 'Token eliminado.']);
    }
}
