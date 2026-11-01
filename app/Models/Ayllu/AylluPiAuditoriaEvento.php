<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiAuditoriaEvento extends Model
{
    protected $table = 'ayllu_pi_auditoria_eventos';

    public $timestamps = false;

    protected $fillable = [
        'accion',
        'entidad_tipo',
        'entidad_id',
        'actor_user_id',
        'ciclo_romano',
        'programa_codigo',
        'metadatos',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadatos' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
