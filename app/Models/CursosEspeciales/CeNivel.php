<?php

namespace App\Models\CursosEspeciales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeNivel extends Model
{
    use HasFactory;

    protected $table = 'ce_niveles';

    protected $fillable = ['curso_especial_id', 'nombre', 'orden'];

    public function curso()
    {
        return $this->belongsTo(CursoEspecial::class, 'curso_especial_id');
    }

    public function unidades()
    {
        return $this->hasMany(CeUnidad::class, 'ce_nivel_id')->orderBy('orden');
    }
}
