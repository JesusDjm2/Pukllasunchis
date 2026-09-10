<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraIntentoFallido extends Model
{
    protected $table = 'bitacora_intentos_fallidos';

    const UPDATED_AT = null;

    protected $fillable = [
        'email_intentado',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
