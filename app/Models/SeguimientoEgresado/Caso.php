<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caso extends Model
{
    use HasFactory;

    protected $table = 'egresado_casos';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_gestion' => 'date',
        'es_confidencial_bienestar' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retorna el propósito respetando la segregación de notas confidenciales de bienestar.
     */
    public function getPropositoVisible(?User $consultante = null): string
    {
        $consultante = $consultante ?: auth()->user();

        if (!$this->es_confidencial_bienestar) {
            return $this->proposito ?: '';
        }

        if ($consultante && app(\App\Services\SeguimientoEgresado\SeguimientoAutorizacionService::class)->puedeVerNotasConfidencialesBienestar($consultante)) {
            return $this->proposito ?: '';
        }

        return '[Información reservada de bienestar institucional]';
    }
}
