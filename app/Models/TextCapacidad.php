<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextCapacidad extends Model
{
    use HasFactory;
    protected $fillable = ['capacidad_id', 'descripcion'];
    
    /* public function capacidad()
    {
        return $this->belongsTo(Capacidades::class);
    } */
    public function capacidad()
    {
        return $this->belongsTo(Capacidades::class, 'capacidad_id');
    }
}
