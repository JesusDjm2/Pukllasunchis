<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPpd extends Model
{
    use HasFactory;
    protected $table = 'admin_ppds';
    protected $fillable = [
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
    ];
    public function postulantes()
    {
        return $this->hasMany(PostulantesPpd::class, 'admin_ppds_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }
}
