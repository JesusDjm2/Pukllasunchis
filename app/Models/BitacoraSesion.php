<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BitacoraSesion extends Model
{
    protected $table = 'bitacora_sesiones';

    protected $fillable = [
        'user_id',
        'email_snapshot',
        'rol_snapshot',
        'login_at',
        'logout_at',
        'logout_tipo',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estaConectadoAhora(): bool
    {
        if ($this->logout_at) {
            return false;
        }

        return DB::table('sessions')
            ->where('user_id', $this->user_id)
            ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime'))->timestamp)
            ->exists();
    }

    public function getFinEfectivoAttribute(): ?Carbon
    {
        if ($this->logout_at) {
            return $this->logout_at;
        }

        if ($this->estaConectadoAhora()) {
            return null;
        }

        $ultimaSesion = DB::table('sessions')
            ->where('user_id', $this->user_id)
            ->orderByDesc('last_activity')
            ->first();

        return $ultimaSesion
            ? Carbon::createFromTimestamp($ultimaSesion->last_activity)
            : $this->login_at->copy()->addMinutes((int) config('session.lifetime'));
    }

    public function getDuracionAttribute(): ?string
    {
        $fin = $this->fin_efectivo;

        return $fin ? $this->login_at->diffForHumans($fin, true) : null;
    }
}
