<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnfoqueSilabo extends Model
{
    use HasFactory;
    protected $table = 'enfoque_silabos';
    protected $fillable = ['silabo_id', 'nombre', 'descripcion', 'enfoque_observables', 'silabo_concretas'];

    public function silabo()
    {
        return $this->belongsTo(Silabo::class);
    }
}
