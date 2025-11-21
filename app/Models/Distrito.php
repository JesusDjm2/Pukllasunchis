<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;

    protected $table = 'distritos';

    protected $fillable = [
        'nombre',
        'provincia_id',
    ];

    // Un distrito pertenece a una provincia
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
}
