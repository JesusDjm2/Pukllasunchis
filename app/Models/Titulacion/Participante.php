<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\Titulacion\Concerns\TieneAsesor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Asesor o jurado de una etapa (TI o Tesis): un docente o un asesor del catálogo, con un cargo
 * opcional (Coasesor, Presidente, Secretario, Vocal…). Un trabajo puede tener 1 o más de cada uno.
 */
class Participante extends Model
{
    use Auditable;
    use TieneAsesor;

    public const FUNCIONES = ['asesor' => 'Asesor(a)', 'jurado' => 'Jurado'];

    /** Cargos sugeridos (el campo es libre y opcional). */
    public const CARGOS = [
        'asesor' => ['Asesor(a)', 'Coasesor(a)'],
        'jurado' => ['Presidente(a)', 'Secretario(a)', 'Vocal', 'Dictaminante', 'Replicante'],
    ];

    /** Columna del docente para TieneAsesor (aquí no es asesor_id). */
    protected string $columnaDocente = 'docente_id';

    protected $table = 'titulacion_participantes';

    protected $fillable = ['seguimiento_id', 'etapa', 'funcion', 'docente_id', 'asesor_externo_id', 'cargo'];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}
