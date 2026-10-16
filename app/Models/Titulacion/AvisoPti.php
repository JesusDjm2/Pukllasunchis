<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AvisoPti extends Model
{
    use Auditable;

    public const TIPOS = [
        'recordatorio' => 'Recordatorio (1 año de vigencia)',
        'urgente' => 'Urgente (PTI por vencer)',
        'excel' => 'Marcado "enviado" en el Excel',
    ];

    protected $table = 'titulacion_avisos_pti';

    protected $fillable = ['seguimiento_id', 'tipo', 'email', 'fecha_vencimiento', 'enviado', 'error'];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'enviado' => 'boolean',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
