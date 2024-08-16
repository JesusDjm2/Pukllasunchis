<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellidos',
        'dni',
        'email',
        'programa_id',
        'edad',
        'idioma',
        'numero',
        'cv',
        'otros_estudios',
        'img',
        'facebook',
        'linkedin',
        'descripcion',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }
}
