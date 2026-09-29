<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoTitulacion extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'seguimiento_titulacion';

    protected $fillable = [
        'user_id', 'estado_titulacion_id', 'numero_expediente', 'fecha_inicio', 'fecha_fin', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estadoTitulacion()
    {
        return $this->belongsTo(EstadoTitulacion::class, 'estado_titulacion_id');
    }

    public function pti()
    {
        return $this->hasOne(Pti::class, 'seguimiento_id');
    }

    public function tesis()
    {
        return $this->hasOne(Tesis::class, 'seguimiento_id');
    }

    public function actas()
    {
        return $this->hasMany(Acta::class, 'seguimiento_id');
    }

    public function resoluciones()
    {
        return $this->hasMany(Resolucion::class, 'seguimiento_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'seguimiento_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
