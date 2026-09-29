<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pti extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'titulacion_ptis';

    protected $fillable = [
        'seguimiento_id', 'titulo', 'asesor_id', 'fecha_pti', 'rd_pti',
        'link_iti', 'estado', 'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_pti' => 'date',
        'fecha_vencimiento' => 'date',
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
