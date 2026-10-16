<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\TieneAsesor;
use App\Models\Titulacion\Concerns\Auditable;
use App\Models\Titulacion\Concerns\RecalculaEstadoSeguimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acta extends Model
{
    use Auditable;
    use RecalculaEstadoSeguimiento;
    use SoftDeletes;
    use TieneAsesor;

    protected $table = 'titulacion_actas';

    protected $fillable = [
        'seguimiento_id', 'tipo', 'numero_acta', 'fecha', 'promedio',
        'titulo_trabajo', 'asesor_id', 'asesor_externo_id',
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
