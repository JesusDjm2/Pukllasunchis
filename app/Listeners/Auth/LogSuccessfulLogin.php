<?php

namespace App\Listeners\Auth;

use App\Models\BitacoraSesion;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        $registro = BitacoraSesion::create([
            'user_id' => $user->id,
            'email_snapshot' => $user->email,
            'rol_snapshot' => $user->getRoleNames()->implode(', ') ?: null,
            'login_at' => now(),
        ]);

        if ($this->request->hasSession()) {
            $this->request->session()->put('bitacora_sesion_id', $registro->id);
        }
    }
}
