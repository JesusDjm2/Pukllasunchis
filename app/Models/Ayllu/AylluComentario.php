<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluComentario extends Model
{
    use HasFactory;

    protected $table = 'ayllu_comentarios';

    protected $fillable = [
        'ayllu_publicacion_id',
        'user_id',
        'parent_id',
        'contenido',
        'es_aporte_docente',
    ];

    protected $casts = [
        'es_aporte_docente' => 'boolean',
    ];

    public function publicacion()
    {
        return $this->belongsTo(AylluPublicacion::class, 'ayllu_publicacion_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
