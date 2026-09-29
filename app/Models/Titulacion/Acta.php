<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acta extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'titulacion_actas';

    protected $fillable = [
        'seguimiento_id', 'tipo', 'numero_acta', 'fecha', 'promedio',
        'titulo_trabajo', 'asesor_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'promedio' => 'decimal:2',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function jurados()
    {
        return $this->hasMany(Jurado::class, 'acta_id');
    }
}
