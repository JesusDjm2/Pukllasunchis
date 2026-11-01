<?php

namespace App\Models\Ayllu;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluEventoCalendario extends Model
{
    use HasFactory;

    protected $table = 'ayllu_eventos_calendario';

    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'hora',
        'curso_id',
        'ciclo_id',
        'enlace',
        'creado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
