<?php

namespace App\Models\Ayllu;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluMensajeChat extends Model
{
    use HasFactory;

    protected $table = 'ayllu_mensajes_chat';

    protected $fillable = [
        'canal_tipo',
        'curso_id',
        'ayllu_grupo_id',
        'emisor_user_id',
        'receptor_user_id',
        'mensaje',
        'leido_at',
    ];

    protected $casts = [
        'leido_at' => 'datetime',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function grupo()
    {
        return $this->belongsTo(AylluGrupo::class, 'ayllu_grupo_id');
    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_user_id');
    }

    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_user_id');
    }
}
