<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\TieneAsesor;
use App\Models\Titulacion\Concerns\Auditable;
use App\Models\Titulacion\Concerns\RecalculaEstadoSeguimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pti extends Model
{
    use Auditable;
    use RecalculaEstadoSeguimiento;
    use SoftDeletes;
    use TieneAsesor;

    /** Para qué grado es el PTI: el primero lleva al Bachiller; si vence
     *  después del TI, el siguiente es para la Licenciatura. */
    public const ETAPAS = ['bachiller' => 'Bachiller', 'licenciatura' => 'Licenciatura'];

    protected $table = 'titulacion_ptis';

    protected $fillable = [
        'seguimiento_id', 'numero', 'etapa', 'titulo', 'asesor_id', 'asesor_externo_id', 'fecha_pti', 'rd_pti',
        'link_iti', 'estado', 'fecha_vencimiento', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado',
    ];

    protected $casts = [
        'fecha_pti' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_ampliacion' => 'date',
        'vencimiento_ampliado' => 'date',
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
