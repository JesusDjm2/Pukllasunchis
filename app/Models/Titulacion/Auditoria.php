<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    public $timestamps = false;

    protected $table = 'titulacion_auditorias';

    protected $fillable = [
        'user_id', 'accion', 'tabla_afectada', 'registro_id',
        'datos_anteriores', 'datos_nuevos',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $auditoria) {
            $auditoria->created_at ??= now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
