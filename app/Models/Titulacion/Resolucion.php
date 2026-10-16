<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\Titulacion\Concerns\RecalculaEstadoSeguimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resolucion extends Model
{
    use Auditable;
    use RecalculaEstadoSeguimiento;
    use SoftDeletes;

    /**
     * Tipos de RD (clave guardada => nombre visible). Las claves antiguas se
     * mantienen: 'Exposicion' (TI), 'Extension' (ampliación de la fecha de
     * sustentación) y 'CambioAsesor' (los importados pueden ser asesor o tema).
     */
    public const TIPOS = [
        'PTI' => 'RD de PTI',
        'AmpliacionPti' => 'Ampliación de PTI',
        'AnulacionPti' => 'Anulación de PTI',
        'Exposicion' => 'RD de Exposición de TI',
        'FechaSustentacion' => 'Fecha de sustentación de Tesis',
        'Tesis' => 'RD de Sustentación de Tesis',
        'Extension' => 'Ampliación (fecha de sustentación)',
        'CambioAsesor' => 'Cambio de asesor',
        'CambioGrupo' => 'Cambio de grupo',
        'CambioTema' => 'Cambio de tema',
        'Otra' => 'Otra resolución',
    ];

    /** Ícono y color (Bootstrap) por tipo, para la línea de tiempo. */
    public const ESTILOS = [
        'PTI' => ['fa-file-alt', 'primary'],
        'AmpliacionPti' => ['fa-hourglass-half', 'warning'],
        'AnulacionPti' => ['fa-ban', 'danger'],
        'Exposicion' => ['fa-chalkboard-teacher', 'info'],
        'FechaSustentacion' => ['fa-calendar-check', 'info'],
        'Tesis' => ['fa-graduation-cap', 'success'],
        'Extension' => ['fa-calendar-plus', 'warning'],
        'CambioAsesor' => ['fa-user-edit', 'secondary'],
        'CambioGrupo' => ['fa-users-cog', 'secondary'],
        'CambioTema' => ['fa-exchange-alt', 'secondary'],
        'Otra' => ['fa-gavel', 'dark'],
    ];

    public static function nombreTipo(?string $tipo): string
    {
        return self::TIPOS[$tipo] ?? (string) $tipo;
    }

    protected $table = 'titulacion_resoluciones';

    protected $fillable = ['seguimiento_id', 'tipo', 'numero_rd', 'fecha', 'archivo', 'detalle'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }
}
