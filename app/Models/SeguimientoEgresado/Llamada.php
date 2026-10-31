<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llamada extends Model
{
    use HasFactory;

    protected $table = 'egresado_llamadas';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_reprogramada' => 'datetime',
        'es_contacto_efectivo' => 'boolean',
        'requiere_derivacion' => 'boolean',
        'es_ensayo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_user_id');
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    public function scopeContactosEfectivos($query)
    {
        return $query->where('es_contacto_efectivo', true);
    }

    public function scopeEnsayo($query)
    {
        return $query->where('es_ensayo', true);
    }

    public function scopeReales($query)
    {
        return $query->where('es_ensayo', false);
    }
}
