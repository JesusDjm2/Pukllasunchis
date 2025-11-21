<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFid extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];
    public function postulantes()
    {
         return $this->hasMany(PostulantesRegular::class, 'admin_fids_id');
    }
}
