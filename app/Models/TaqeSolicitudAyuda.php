<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaqeSolicitudAyuda extends Model
{
    use HasFactory;

    protected $table = 'taqe_solicitudes_ayuda';

    protected $fillable = [
        'taqe_grupo_id',
        'grupo_nombre',
        'alumno_id',
        'motivo',
        'mensaje',
        'estado',
        'atendido_por_user_id',
        'atendido_at',
    ];

    protected $casts = [
        'atendido_at' => 'datetime',
    ];

    public const MOTIVOS = [
        'cambiar_organizador' => 'Cambiar de organizador',
        'disolver_grupo' => 'Disolver el grupo',
        'otro' => 'Otro',
    ];

    public function grupo()
    {
        return $this->belongsTo(TaqeGrupo::class, 'taqe_grupo_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por_user_id');
    }

    public function motivoLabel(): string
    {
        return self::MOTIVOS[$this->motivo] ?? $this->motivo;
    }
}
