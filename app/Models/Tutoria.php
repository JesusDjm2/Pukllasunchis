<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Tutoria extends Model
{
    /**
     * Cómo quiere conversar el alumno (se guarda la clave en `urgencia`).
     * clave => [texto completo para el alumno, etiqueta corta, color]
     * Las solicitudes antiguas guardan Alta/Media/Baja y se muestran tal cual.
     */
    public const OPCIONES = [
        'pronto'       => ['Sí, necesito conversar pronto.', 'Conversar pronto', 'red'],
        'telefono'     => ['Sí, puede ser por teléfono.', 'Por teléfono', 'cyan'],
        'temprano'     => ['Sí, puedo venir un día más temprano.', 'Venir más temprano', 'amber'],
        'descanso'     => ['Sí, un día durante el descanso.', 'Durante el descanso', 'green'],
        'otro_docente' => ['No, deseo hablar con otro docente.', 'Con otro docente', 'slate'],
    ];

    public const OTRO_DOCENTE = 'otro_docente';

    private const COLORES_ANTIGUOS = ['Baja' => 'slate', 'Media' => 'cyan', 'Alta' => 'amber', 'Urgente' => 'red'];

    protected $fillable = [
        'programa_id', 'ciclo_id', 'alumno_id', 'docente_id', 'nombre_alumno', 'motivo', 'urgencia',
        'estado', 'atendido_por', 'atendido_at', 'notas_atencion',
    ];

    protected $casts = ['atendido_at' => 'datetime'];

    public function programa()   { return $this->belongsTo(Programa::class); }
    public function ciclo()      { return $this->belongsTo(Ciclo::class); }
    public function alumno()     { return $this->belongsTo(Alumno::class); }
    public function atendidoPor() { return $this->belongsTo(User::class, 'atendido_por'); }
    /** Docente con quien el alumno pidió hablar (solo con la opción "otro docente"). */
    public function docenteSolicitado() { return $this->belongsTo(User::class, 'docente_id'); }

    /** Etiqueta corta de la opción elegida (o la prioridad antigua tal cual). */
    public function getUrgenciaTextoAttribute(): string
    {
        return self::OPCIONES[$this->urgencia][1] ?? (string) $this->urgencia;
    }

    /** Color de la pastilla: red, cyan, amber, green o slate. */
    public function getUrgenciaColorAttribute(): string
    {
        return self::OPCIONES[$this->urgencia][2] ?? self::COLORES_ANTIGUOS[$this->urgencia] ?? 'slate';
    }

    /** Nombre del docente elegido con "otro docente" (el de su ficha si la tiene). */
    public function getDocenteSolicitadoNombreAttribute(): ?string
    {
        $u = $this->docenteSolicitado;

        return $u ? (trim((string) $u->docente?->nombre) ?: trim($u->name.' '.$u->apellidos)) : null;
    }

    public function pideOtroDocente(): bool
    {
        return $this->urgencia === self::OTRO_DOCENTE;
    }

    /**
     * Docentes que el alumno puede elegir con "otro docente", en dos grupos:
     * primero los tutores y luego los docentes con cursos asignados. Cada uno es
     * ['id' => users.id, 'nombre' => ..., 'detalle' => ...]. Se excluye a los
     * tutores del ciclo indicado (el alumno ya dijo que quiere hablar con otro).
     */
    public static function docentesElegibles(?int $excluirTutoresDeCiclo = null): array
    {
        $excluir = $excluirTutoresDeCiclo
            ? Ciclo::find($excluirTutoresDeCiclo)?->tutores()->pluck('users.id')->all() ?? []
            : [];

        $nombre = fn (User $u) => trim((string) $u->docente?->nombre) ?: trim($u->name.' '.$u->apellidos);

        $tutores = User::whereHas('tutorCiclos')
            ->with(['docente', 'tutorCiclos'])
            ->whereNotIn('id', $excluir)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'nombre' => $nombre($u),
                'detalle' => 'Tutor(a) · Ciclo '.$u->tutorCiclos->pluck('nombre')->unique()->sort()->join(', '),
            ]);

        $idsTutores = $tutores->pluck('id')->merge($excluir)->all();

        $docentes = Docente::whereNotNull('user_id')
            ->whereHas('cursos')
            ->whereHas('user')
            ->whereNotIn('user_id', $idsTutores)
            ->get()
            ->map(fn (Docente $d) => [
                'id' => $d->user_id,
                'nombre' => trim((string) $d->nombre) ?: 'Docente',
                'detalle' => null,
            ]);

        $ordenar = fn (Collection $c) => $c->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        return array_filter([
            'Tutores' => $ordenar($tutores),
            'Docentes de cursos del periodo' => $ordenar($docentes),
        ]);
    }
}
