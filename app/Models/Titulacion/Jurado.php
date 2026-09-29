<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Jurado extends Model
{
    use Auditable;

    protected $table = 'titulacion_jurados';

    protected $fillable = ['acta_id', 'docente_id', 'rol'];

    public function acta()
    {
        return $this->belongsTo(Acta::class, 'acta_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}
