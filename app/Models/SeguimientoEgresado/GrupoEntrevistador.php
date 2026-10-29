<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoEntrevistador extends Model
{
    use HasFactory;

    protected $table = 'egresado_grupos_entrevistadores';

    protected $guarded = ['id'];

    protected $casts = [
        'entrevistadores' => 'array',
        'egresados_ids' => 'array',
    ];

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }
}
