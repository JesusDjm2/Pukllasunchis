<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditoriaRolSeguimiento extends Model
{
    protected $table = 'egresado_auditoria_roles';

    protected $fillable = [
        'user_id',
        'admin_id',
        'rol',
        'accion',
        'motivo',
        'ip',
        'roles_anteriores',
        'roles_posteriores',
    ];

    protected $casts = [
        'roles_anteriores' => 'array',
        'roles_posteriores' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Registra un evento de auditoría de asignación o revocación de rol.
     */
    public static function registrarEvento(
        User $usuario,
        string $accion,
        string $rol = 'seguimiento-egresados',
        ?string $motivo = null,
        ?array $rolesAnteriores = null,
        ?array $rolesPosteriores = null,
        ?User $admin = null,
        ?string $ip = null
    ): self {
        $admin = $admin ?: auth()->user();

        return self::create([
            'user_id' => $usuario->id,
            'admin_id' => $admin?->id,
            'rol' => $rol,
            'accion' => $accion,
            'motivo' => $motivo ?: ($accion === 'asignado' ? 'Asignación de rol de seguimiento desde panel administrativo' : 'Revocación de rol de seguimiento'),
            'ip' => $ip ?: request()?->ip(),
            'roles_anteriores' => $rolesAnteriores ?: [],
            'roles_posteriores' => $rolesPosteriores ?: [],
        ]);
    }
}
