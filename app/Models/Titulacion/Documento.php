<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'titulacion_documentos';

    protected $fillable = ['seguimiento_id', 'tipo', 'url', 'descripcion'];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }
}
