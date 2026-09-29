<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tesis extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'titulacion_tesis';

    protected $fillable = [
        'seguimiento_id', 'titulo', 'asesor_id', 'fecha_exposicion', 'rd_ti',
        'bachiller_sunedu', 'rd_extension', 'fecha_sustentacion', 'rd_tesis',
        'url_repositorio', 'estado',
    ];

    protected $casts = [
        'fecha_exposicion' => 'date',
        'fecha_sustentacion' => 'date',
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
