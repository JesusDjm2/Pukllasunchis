<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvitacionCampana extends Model
{
    use HasFactory;

    protected $table = 'egresado_invitaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'usado' => 'boolean',
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    /**
     * Scope para tokens vigentes y no usados
     */
    public function scopeValidos($query)
    {
        return $query->where('usado', false)
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>', now());
            });
    }

    /**
     * Generador seguro de token criptográfico
     */
    public static function crearInvitacion(User $egresado, ?int $generadoPor = null, string $campana = '2026-II', string $canal = 'whatsapp'): self
    {
        // Token criptográfico seguro sin PII en el token ni en la URL
        $token = 'INV-' . Str::random(48);

        return self::create([
            'token' => $token,
            'user_id' => $egresado->id,
            'identificador_temporal' => null,
            'campana' => $campana,
            'canal' => $canal,
            'generado_por' => $generadoPor,
            'usado' => false,
            'fecha_expiracion' => now()->addDays(30),
        ]);
    }

    /**
     * Marcar como usado
     */
    public function marcarComoUsado(?string $ip = null): void
    {
        $this->update([
            'usado' => true,
            'fecha_uso' => now(),
            'ip_uso' => $ip,
        ]);
    }
}
