<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Acción de acompañamiento institucional a un estudiante (ver expediente). */
class SeguimientoAccion extends Model
{
    use SoftDeletes;

    protected $table = 'seguimiento_acciones';

    public const TIPOS = [
        'entrevista' => 'Entrevista',
        'llamada' => 'Llamada / mensaje',
        'derivacion' => 'Derivación (psicología, bienestar…)',
        'compromiso' => 'Compromiso académico',
        'reunion_familia' => 'Reunión con la familia',
        'visita' => 'Visita',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'user_id', 'tipo', 'fecha', 'responsable_id', 'resumen', 'acuerdos',
        'proxima_revision', 'estado', 'confidencial', 'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proxima_revision' => 'date',
        'confidencial' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function documentos()
    {
        return $this->hasMany(ExpedienteDocumento::class);
    }

    public function getEtiquetaAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    /** Abierta y con fecha de revisión ya pasada. */
    public function getVencidaAttribute(): bool
    {
        return $this->estado === 'abierto' && $this->proxima_revision && $this->proxima_revision->lt(now()->startOfDay());
    }

    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierto');
    }
}
