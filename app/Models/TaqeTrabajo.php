<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaqeTrabajo extends Model
{
    use HasFactory;

    protected $table = 'taqe_trabajos';

    protected $fillable = [
        'taqe_grupo_id',
        'nombre',
        'archivo',
        'enlace',
        'tipo_archivo',
        'categoria',
        'estado',
        'subido_por_alumno_id',
    ];

    /** evidencia = avance del proceso · final = parte del trabajo final (puede haber varios). */
    public function esFinal(): bool
    {
        return $this->categoria === 'final';
    }

    /** Un enlace externo (sin archivo en Drive). */
    public function esEnlace(): bool
    {
        return $this->tipo_archivo === 'enlace';
    }

    public function grupo()
    {
        return $this->belongsTo(TaqeGrupo::class, 'taqe_grupo_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(Alumno::class, 'subido_por_alumno_id');
    }
}
