<?php

namespace App\Models\CursosEspeciales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeUnidad extends Model
{
    use HasFactory;

    protected $table = 'ce_unidades';

    protected $fillable = ['ce_nivel_id', 'nombre', 'descripcion', 'orden'];

    public function nivel()
    {
        return $this->belongsTo(CeNivel::class, 'ce_nivel_id');
    }

    public function lecciones()
    {
        return $this->hasMany(CeLeccion::class, 'ce_unidad_id')->orderBy('orden');
    }

    public function ejercicios()
    {
        return $this->hasMany(CeEjercicio::class, 'ce_unidad_id')->orderBy('orden');
    }
}
