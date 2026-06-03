<?php

namespace App\Models\CursosEspeciales;

use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CeInscripcion extends Model
{
    public $timestamps = false;

    protected $table = 'ce_inscripciones';

    protected $fillable = ['user_id', 'curso_especial_id', 'periodo_actual_id', 'inscrito_at'];

    protected $casts = ['inscrito_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(CursoEspecial::class, 'curso_especial_id');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoActual::class, 'periodo_actual_id');
    }
}
