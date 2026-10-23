<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\TieneAsesor;
use App\Models\Titulacion\Concerns\Auditable;
use App\Models\Titulacion\Concerns\RecalculaEstadoSeguimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tesis extends Model
{
    use Auditable;
    use RecalculaEstadoSeguimiento;
    use SoftDeletes;
    use TieneAsesor;

    protected $table = 'titulacion_tesis';

    protected $fillable = [
        'seguimiento_id', 'titulo', 'asesor_id', 'asesor_externo_id', 'fecha_exposicion', 'rd_ti', 'url_repositorio_ti',
        'bachiller_sunedu', 'fecha_bachiller_sunedu', 'rd_extension', 'fecha_extension', 'fecha_sustentacion', 'rd_tesis',
        'titulo_sunedu', 'fecha_titulo_sunedu',
        'url_repositorio', 'estado',
        'enlaces_dictamen', 'link_tesis', 'licenciatura_escuela', 'fecha_licenciatura_escuela',
    ];

    /** Situación de la tesis (columna `estado`). 'aprobado' queda solo para registros antiguos. */
    public const SITUACIONES = [
        'en_repositorio' => 'En repositorio',
        'pendiente' => 'Pendiente',
        'no_solicito' => 'No solicitó',
        'no_se_presenta' => 'No se presenta',
    ];

    protected $casts = [
        'enlaces_dictamen' => 'array',
        'licenciatura_escuela' => 'boolean',
        'fecha_licenciatura_escuela' => 'date',
        'fecha_exposicion' => 'date',
        'fecha_extension' => 'date',
        'fecha_sustentacion' => 'date',
        'fecha_bachiller_sunedu' => 'date',
        'fecha_titulo_sunedu' => 'date',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
}
