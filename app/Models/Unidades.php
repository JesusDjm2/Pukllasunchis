<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidades extends Model
{
    use HasFactory;
    protected $fillable = [
        'silabo_id',
        'titulo',
        'situacion',
        'duracion',
        'desempeno',
        'ejes',
        'evidencia',
        'final',
    ];
    public function silabo()
    {
        return $this->belongsTo(Silabo::class);
    }
}
