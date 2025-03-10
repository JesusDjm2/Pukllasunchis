<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;
    protected $table='proyectos'; 
    protected $fillable= ['nombre', 'producto', 'descripcion'];
    public function ciclos()
    {
        return $this->hasMany(Ciclo::class);
    } 
}
