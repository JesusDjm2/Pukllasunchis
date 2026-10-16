<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\TieneAsesor;
use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Jurado extends Model
{
    use Auditable;
    use TieneAsesor;

    /** Columna del docente para TieneAsesor (aquí no es asesor_id). */
    protected string $columnaDocente = 'docente_id';

    protected $table = 'titulacion_jurados';

    protected $fillable = ['acta_id', 'docente_id', 'asesor_externo_id', 'rol'];

    public function acta()
    {
        return $this->belongsTo(Acta::class, 'acta_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}
