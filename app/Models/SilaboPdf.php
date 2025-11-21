<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SilaboPdf extends Model
{
    use HasFactory;

    protected $table = 'silabo_pdf';

    protected $fillable = [
        'pdf',
        'curso_id',
        'periodo_actual_id',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
    /** Relación: Un sílabo pertenece a un periodo */
    /* public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class);
    } */
    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class, 'periodo_actual_id');
    }
}
