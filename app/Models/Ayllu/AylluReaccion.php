<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluReaccion extends Model
{
    use HasFactory;

    protected $table = 'ayllu_reacciones';

    protected $fillable = [
        'ayllu_publicacion_id',
        'user_id',
        'tipo',
    ];

    public function publicacion()
    {
        return $this->belongsTo(AylluPublicacion::class, 'ayllu_publicacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function catalogo(): array
    {
        return [
            'allinmi' => ['label' => 'Allinmi', 'sub' => 'Qué bien', 'icono' => 'fas fa-heart'],
            'ayni'    => ['label' => 'Ayni',    'sub' => 'Reciprocidad', 'icono' => 'fas fa-hands-helping'],
            'yachay'  => ['label' => 'Yachay',  'sub' => 'Gran saber', 'icono' => 'fas fa-lightbulb'],
            'kallpa'  => ['label' => 'Kallpa',  'sub' => 'Ánimo', 'icono' => 'fas fa-fire'],
        ];
    }
}
