<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpisodioLaboral extends Model
{
    use HasFactory;

    protected $table = 'egresado_episodios_laborales';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_empleo_educativo' => 'boolean',
        'es_docencia_directa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
