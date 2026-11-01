<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluRetroalimentacion extends Model
{
    use HasFactory;

    protected $table = 'ayllu_retroalimentaciones';

    protected $fillable = [
        'ayllu_entrega_formativa_id',
        'autor_user_id',
        'tipo',
        'valoracion_formativa',
        'fortalezas',
        'sugerencias_mejora',
        'comentario_general',
        'criterios_rubrica',
    ];

    protected $casts = [
        'criterios_rubrica' => 'array',
    ];

    public function entrega()
    {
        return $this->belongsTo(AylluEntregaFormativa::class, 'ayllu_entrega_formativa_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_user_id');
    }
}
