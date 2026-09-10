<?php

namespace App\Listeners\Auth;

use App\Models\BitacoraSesion;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(Logout $event): void
    {
        if (! $this->request->hasSession()) {
            return;
        }

        $bitacoraSesionId = $this->request->session()->get('bitacora_sesion_id');

        if (! $bitacoraSesionId) {
            return;
        }

        BitacoraSesion::whereKey($bitacoraSesionId)
            ->whereNull('logout_at')
            ->update([
                'logout_at' => now(),
                'logout_tipo' => 'manual',
            ]);
    }
}
