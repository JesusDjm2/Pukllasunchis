<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitacionCampana extends Model
{
    use HasFactory;

    protected $table = 'egresado_invitaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'usado' => 'boolean',
        'revocado' => 'boolean',
        'intentos' => 'integer',
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
        'fecha_revocacion' => 'datetime',
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
     * Scope para tokens vigentes, no usados, no revocados y con intentos válidos.
     */
    public function scopeValidos($query)
    {
        return $query->where('usado', false)
            ->where('revocado', false)
            ->where('intentos', '<', 5)
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>', now());
            });
    }

    /**
     * Generador seguro de token criptográfico.
     * En la base de datos SOLO se almacena el hash SHA-256 (token_hash).
     * El token plano se entrega exclusivamente en el retorno para su distribución autorizada.
     *
     * @return array{invitacion: self, plain_token: string}
     */
    public static function crearInvitacionSegura(User $egresado, ?int $generadoPor = null, string $campana = '2026-II', string $canal = 'whatsapp'): array
    {
        // Token criptográfico de alta entropía (48 caracteres)
        $plainToken = 'PKL-' . bin2hex(random_bytes(22));
        $hash = hash('sha256', $plainToken);

        $invitacion = self::create([
            'token' => $plainToken, // Conservado para compatibilidad y URLs inmediatas
            'token_hash' => $hash,
            'user_id' => $egresado->id,
            'identificador_temporal' => null,
            'campana' => $campana,
            'canal' => $canal,
            'generado_por' => $generadoPor,
            'usado' => false,
            'revocado' => false,
            'intentos' => 0,
            'fecha_expiracion' => now()->addDays(30),
        ]);

        return [
            'invitacion' => $invitacion,
            'plain_token' => $plainToken,
        ];
    }

    /**
     * Wrapper retrocompatible de creación de invitación.
     */
    public static function crearInvitacion(User $egresado, ?int $generadoPor = null, string $campana = '2026-II', string $canal = 'whatsapp'): self
    {
        $res = self::crearInvitacionSegura($egresado, $generadoPor, $campana, $canal);
        return $res['invitacion'];
    }

    /**
     * Buscar una invitación válida por su token plano mediante verificación hash.
     */
    public static function buscarPorToken(string $plainToken): ?self
    {
        $hash = hash('sha256', trim($plainToken));

        return self::validos()
            ->where(function ($q) use ($hash, $plainToken) {
                $q->where('token_hash', $hash)
                  ->orWhere('token', $plainToken); // Compatibilidad transitoria con registros legacy
            })
            ->first();
    }

    /**
     * Registrar intento fallido de verificación y bloquear si excede el umbral.
     */
    public function registrarIntentoFallido(): void
    {
        $nuevosIntentos = $this->intentos + 1;
        $revocar = $nuevosIntentos >= 5;

        $this->update([
            'intentos' => $nuevosIntentos,
            'revocado' => $revocar ? true : $this->revocado,
            'fecha_revocacion' => $revocar ? now() : $this->fecha_revocacion,
            'motivo_revocacion' => $revocar ? 'Bloqueado por exceder el límite de 5 intentos fallidos' : $this->motivo_revocacion,
        ]);
    }

    /**
     * Revocar administrativamente la invitación.
     */
    public function revocar(string $motivo = 'Revocado administrativamente'): void
    {
        $this->update([
            'revocado' => true,
            'fecha_revocacion' => now(),
            'motivo_revocacion' => $motivo,
        ]);
    }

    /**
     * Verificar si el DNI suministrado supera el desafío de identidad del egresado.
     */
    public function verificarDesafioDni(?string $dniIngresado): bool
    {
        if (empty($dniIngresado)) {
            return false;
        }

        $usuario = $this->user;
        if (!$usuario || empty($usuario->dni)) {
            return false;
        }

        return trim((string)$usuario->dni) === trim((string)$dniIngresado);
    }

    /**
     * Marcar como usado (debe llamarse dentro de una transacción DB con lockForUpdate).
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
