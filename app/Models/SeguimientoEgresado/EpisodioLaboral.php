<?php

namespace App\Models\SeguimientoEgresado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EpisodioLaboral extends Model
{
    use HasFactory;

    protected $table = 'egresado_episodios_laborales';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_captura' => 'datetime',
        'es_actual' => 'boolean',
        'es_empleo_educativo' => 'boolean',
        'es_docencia_directa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para episodios laborales activos / vigentes.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('es_actual', true);
    }

    /**
     * Scope para episodios laborales pasados / cerrados.
     */
    public function scopeHistoricos(Builder $query): Builder
    {
        return $query->where('es_actual', false);
    }

    /**
     * Scope para empleos dentro del sector educativo.
     */
    public function scopeEducativos(Builder $query): Builder
    {
        return $query->where('es_empleo_educativo', true);
    }

    /**
     * Scope para docencia directa de aula.
     */
    public function scopeDocenciaDirecta(Builder $query): Builder
    {
        return $query->where('es_docencia_directa', true);
    }

    /**
     * Cerrar este episodio laboral registrando su fecha de finalización
     * y motivo de cambio sin sobrescribir la fecha de inicio.
     */
    public function cerrarEpisodio(?string $fechaFin = null, ?string $motivo = null): self
    {
        $this->es_actual = false;
        if ($fechaFin) {
            $this->fecha_fin = $fechaFin;
        }
        if ($motivo) {
            $this->motivo_cambio = $motivo;
        }
        $this->save();

        return $this;
    }
}
