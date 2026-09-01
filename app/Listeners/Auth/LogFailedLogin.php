<?php

namespace App\Listeners\Auth;

use App\Models\BitacoraIntentoFallido;
use App\Models\User;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        if (! $email) {
            return;
        }

        BitacoraIntentoFallido::create([
            'email_intentado' => $email,
            'user_id' => $event->user?->id ?? User::where('email', $email)->value('id'),
        ]);
    }
}
