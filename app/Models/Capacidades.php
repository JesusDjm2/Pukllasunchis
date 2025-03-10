<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacidades extends Model
{
    use HasFactory;
    protected $fillable = ['competencia_id', 'descripcion'];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class);
    }
    public function texto()
    {
        return $this->hasOne(TextCapacidad::class, 'capacidad_id');
    }
}
