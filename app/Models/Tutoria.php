<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutoria extends Model
{
    protected $fillable = [
        'programa_id', 'ciclo_id', 'alumno_id', 'nombre_alumno', 'motivo', 'urgencia',
        'estado', 'atendido_por', 'atendido_at', 'notas_atencion',
    ];

    protected $casts = ['atendido_at' => 'datetime'];

    public function programa()   { return $this->belongsTo(Programa::class); }
    public function ciclo()      { return $this->belongsTo(Ciclo::class); }
    public function alumno()     { return $this->belongsTo(Alumno::class); }
    public function atendidoPor() { return $this->belongsTo(User::class, 'atendido_por'); }
}
