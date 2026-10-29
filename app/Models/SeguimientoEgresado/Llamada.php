<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llamada extends Model
{
    use HasFactory;

    protected $table = 'egresado_llamadas';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
