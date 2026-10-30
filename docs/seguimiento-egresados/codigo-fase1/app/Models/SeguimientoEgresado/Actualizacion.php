<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actualizacion extends Model
{
    use HasFactory;

    protected $table = 'egresado_actualizaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_bach' => 'date',
        'fecha_lic' => 'date',
        'inicio_empleo' => 'date',
        'fin_empleo' => 'date',
        'fecha_ultima_confirmacion' => 'datetime',
        'es_confirmacion_sin_cambios' => 'boolean',
        'datos_extra' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    public function episodiosLaborales()
    {
        return $this->hasMany(EpisodioLaboral::class, 'user_id', 'user_id');
    }

    public function episodiosActivos()
    {
        return $this->hasMany(EpisodioLaboral::class, 'user_id', 'user_id')->where('es_actual', true);
    }
}
