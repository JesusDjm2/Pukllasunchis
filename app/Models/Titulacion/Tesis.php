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
        'link_ti', 'link_ti_estado', 'link_ti_enviado_en', 'link_ti_aprobado_en',
        'link_tesis_estado', 'link_tesis_enviado_en', 'link_tesis_aprobado_en',
        'tramite_sust_pagado_en', 'tramite_sust_recibo', 'correo_rd_tesis_enviado_en',
    ];

    /**
     * Estados del enlace del TI (link_ti) y de la Tesis (link_tesis): el estudiante lo envía y el
     * asesor lo aprueba. Un enlace sin estado (registros anteriores) cuenta como enviado.
     */
    public const ESTADOS_ENLACE = [
        'enviado' => 'Enviado por el estudiante',
        'aprobado' => 'Aprobado por el asesor',
    ];

    /** Estado efectivo del enlace de una etapa ('ti' | 'tesis'): null (sin enlace), 'enviado' o 'aprobado'. */
    public function estadoEnlace(string $etapa): ?string
    {
        if (blank($this->{"link_{$etapa}"})) {
            return null;
        }

        return $this->{"link_{$etapa}_estado"} === 'aprobado' ? 'aprobado' : 'enviado';
    }

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
        'link_ti_enviado_en' => 'date',
        'link_ti_aprobado_en' => 'date',
        'link_tesis_enviado_en' => 'date',
        'link_tesis_aprobado_en' => 'date',
        'tramite_sust_pagado_en' => 'date',
        'correo_rd_tesis_enviado_en' => 'datetime',
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
