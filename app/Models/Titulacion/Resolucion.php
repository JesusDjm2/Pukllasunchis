<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resolucion extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'titulacion_resoluciones';

    protected $fillable = ['seguimiento_id', 'tipo', 'numero_rd', 'fecha', 'archivo'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }
}
