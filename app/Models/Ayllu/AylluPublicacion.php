<?php

namespace App\Models\Ayllu;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluPublicacion extends Model
{
    use HasFactory, \App\Models\Concerns\AmbitoRedPuklla;

    protected $table = 'ayllu_publicaciones';

    protected $fillable = [
        'red_egresados',
        'categoria_red',
        'intencion_red',
        'estado_red',

        'user_id',
        'ambito',
        'programa_id',
        'ciclo_id',
        'curso_id',
        'ayllu_grupo_id',
        'tipo',
        'titulo',
        'contenido',
        'resumen_quechua',
        'etiquetas',
        'enlace_externo',
        'enlace_titulo',
        'fijado',
        'destacado_inicio',
        'visible',
    ];

    protected $casts = [
        'etiquetas' => 'array',
        'fijado' => 'boolean',
        'destacado_inicio' => 'boolean',
        'visible' => 'boolean',
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function grupo()
    {
        return $this->belongsTo(AylluGrupo::class, 'ayllu_grupo_id');
    }

    public function comentarios()
    {
        return $this->hasMany(AylluComentario::class, 'ayllu_publicacion_id')->orderBy('created_at');
    }

    public function reacciones()
    {
        return $this->hasMany(AylluReaccion::class, 'ayllu_publicacion_id');
    }

    public function etiquetaTipo(): array
    {
        return match ($this->tipo) {
            'anuncio' => ['texto' => 'Willakuy · Anuncio', 'icono' => 'fas fa-bullhorn', 'color' => '#9c6b37'],
            'recurso' => ['texto' => 'Yachay · Recurso Pedagógico', 'icono' => 'fas fa-book-open', 'color' => '#0e9467'],
            'practica_eib' => ['texto' => 'Práctica EIB & Saberes', 'icono' => 'fas fa-seedling', 'color' => '#2563eb'],
            'pregunta' => ['texto' => 'Tapukuy · Diálogo / Consulta', 'icono' => 'fas fa-comments', 'color' => '#a76d00'],
            'minka' => ['texto' => 'Mink’a · Trabajo Colectivo', 'icono' => 'fas fa-hands-helping', 'color' => '#832128'],
            default => ['texto' => 'Rimay · Reflexión Comunitaria', 'icono' => 'fas fa-feather-alt', 'color' => '#33445a'],
        };
    }
}
