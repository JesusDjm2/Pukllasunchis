<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesoContacto extends Model
{
    use HasFactory;

    protected $table = 'egresado_accesos_contacto';

    protected $guarded = ['id'];

    public function consultante()
    {
        return $this->belongsTo(User::class, 'user_id_consultante');
    }

    public function consultado()
    {
        return $this->belongsTo(User::class, 'user_id_consultado');
    }
}
