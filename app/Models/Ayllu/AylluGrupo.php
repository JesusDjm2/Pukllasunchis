<?php

namespace App\Models\Ayllu;

use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluGrupo extends Model
{
    use HasFactory, \App\Models\Concerns\AmbitoRedPuklla;

    protected $table = 'ayllu_grupos';

    protected $fillable = [
        'red_egresados',
        'privacidad_red',
        'origen_publicacion_id',
        'promocion_red',

        'nombre',
        'nombre_quechua',
        'slug',
        'descripcion',
        'tipo',
        'icono',
        'color',
        'programa_id',
        'ciclo_id',
        'creado_por',
        'es_oficial',
        'activo',
    ];

    protected $casts = [
        'es_oficial' => 'boolean',
        'activo' => 'boolean',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function miembros()
    {
        return $this->belongsToMany(User::class, 'ayllu_grupo_miembros', 'ayllu_grupo_id', 'user_id')
            ->withPivot('rol')
            ->withTimestamps();
    }

    public function publicaciones()
    {
        return $this->hasMany(AylluPublicacion::class, 'ayllu_grupo_id');
    }
}
