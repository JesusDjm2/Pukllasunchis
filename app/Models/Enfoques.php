<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enfoques extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
        'observables',
        'concretas',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
