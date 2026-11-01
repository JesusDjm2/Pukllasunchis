<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AylluPerfil extends Model
{
    use HasFactory;

    protected $table = 'ayllu_perfiles';

    protected $fillable = [
        'user_id',
        'saludo_intercultural',
        'biografia',
        'saberes_intereses',
        'lengua_preferida',
        'mostrar_portafolio_comunidad',
    ];

    protected $casts = [
        'saberes_intereses' => 'array',
        'mostrar_portafolio_comunidad' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
