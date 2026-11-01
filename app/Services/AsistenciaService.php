<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\TrayectoriaEvento;
use App\Models\User;
use App\Models\ppd;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AsistenciaService
{
    /**
     * Clasifica la condición de beca de un estudiante.
     * Detecta con precisión: Beca Amantani, PRONABEC (Beca 18 / Continuidad) y otras becas.
     *
     * @return array{es_beca: bool, tipo: ?string, detalle: ?string, badge_class: string, badge_label: string, icono: string}
     */
    public static function clasificarBeca(?User $user, $ficha = null): array
    {
        $ficha = $ficha ?? $user?->alumno ?? $user?->alumnoB;
        $origen = trim((string) ($ficha?->origen_beca ?? ''));
        $esBecaUser = (bool) ($user?->beca == 1);
        $estudioBeca = trim((string) ($ficha?->estudio_beca ?? ''));
        $esBecaFicha = (stripos($estudioBeca, 'si') !== false || $estudioBeca === '1');

        $descartes = [
            'no', 'ninguno', 'ninguna', '0', '-', '.', 'nd', 'nada', '_',
            'no tengo', 'no tengo beca', 'no tuve beca', 'no estuve con beca',
            'no cuentocon beca', 'no estudie con beca', 'ni uno', 'no hay',
            'no tengo ningúna beca', 'no tengo ninguna beca', 'ninguna beca',
            'ningún', 'ningun', 'ordinario', 'fuga consequatur te',
            'beatae voluptatem di', 'officia id reprehend', 'delectus velit des'
        ];

        $esOrigenVacio = empty($origen) || in_array(mb_strtolower($origen), $descartes, true);

        if ($esOrigenVacio) {
            if ($esBecaUser || $esBecaFicha) {
                return [
                    'es_beca' => true,
                    'tipo' => 'Becario',
                    'detalle' => 'Beca Institucional',
                    'badge_class' => 'badge-success',
                    'badge_label' => 'Beca',
                    'icono' => 'fa-graduation-cap',
                ];
            }
            return [
                'es_beca' => false,
                'tipo' => null,
                'detalle' => null,
                'badge_class' => 'badge-light text-muted',
                'badge_label' => 'Regular',
                'icono' => '',
            ];
        }

        // Beca Amantani (convenio ONG Amantani / Ccorca)
        if (stripos($origen, 'amantani') !== false) {
            return [
                'es_beca' => true,
                'tipo' => 'Beca Amantani',
                'detalle' => $origen,
                'badge_class' => 'badge-amantani',
                'badge_label' => 'Beca Amantani',
                'icono' => 'fa-hand-holding-heart',
            ];
        }

        // PRONABEC (Beca 18, Continuidad, EIB, etc.)
        if (stripos($origen, 'pronabec') !== false || stripos($origen, 'beca 18') !== false ||
            stripos($origen, 'continuidad') !== false || stripos($origen, 'pronaveb') !== false) {
            $etiqueta = stripos($origen, '18') !== false ? 'Beca 18' : 'PRONABEC';
            return [
                'es_beca' => true,
                'tipo' => 'PRONABEC',
                'detalle' => $origen,
                'badge_class' => 'badge-pronabec',
                'badge_label' => $etiqueta,
                'icono' => 'fa-award',
            ];
        }

        // Otra beca registrada
        return [
            'es_beca' => true,
            'tipo' => 'Beca ' . $origen,
            'detalle' => $origen,
            'badge_class' => 'badge-info',
            'badge_label' => mb_strimwidth($origen, 0, 18, '…'),
            'icono' => 'fa-certificate',
        ];
    }

    /**
     * Verifica si un estudiante está en condición de Licencia.
     */
    public static function verificarLicencia(?User $user, $alumno = null): array
    {
        $esLicencia = false;
        $motivo = null;

        if ($user) {
            if ($user->perfil === 'Licencia' || $user->condicion === 'Licencia') {
                $esLicencia = true;
                $motivo = 'Perfil en Licencia';
            } elseif ($user->relationLoaded('trayectoriaEventos')) {
                $eventoActivo = $user->trayectoriaEventos->first(fn ($e) => $e->tipo === 'licencia' && is_null($e->fecha_fin));
                if ($eventoActivo) {
                    $esLicencia = true;
                    $motivo = $eventoActivo->descripcion ?: 'Licencia vigente';
                }
            } else {
                $eventoActivo = TrayectoriaEvento::where('user_id', $user->id)->licenciasActivas()->first();
                if ($eventoActivo) {
                    $esLicencia = true;
                    $motivo = $eventoActivo->descripcion ?: 'Licencia vigente';
                }
            }
        }

        if (! $esLicencia && $alumno && isset($alumno->condicion) && $alumno->condicion === 'Licencia') {
            $esLicencia = true;
            $motivo = 'Ficha con condición Licencia';
        }

        return [
            'en_licencia' => $esLicencia,
            'motivo' => $motivo,
            'badge_class' => $esLicencia ? 'badge-warning font-weight-bold text-dark' : '',
            'badge_label' => $esLicencia ? 'En Licencia' : '',
            'icono' => $esLicencia ? 'fa-user-clock' : '',
        ];
    }

    /**
     * Obtiene la lista unificada de alumnos del curso con sus banderas de Beca y Licencia.
     * Compatible con cursos FID y cursos PPD.
     *
     * @return Collection<int, object>
     */
    public static function alumnosDelCurso(Curso $curso, ?PeriodoActual $periodoActual = null): Collection
    {
        $periodoActual = $periodoActual ?? PeriodoActual::actual();

        if ($curso->esPpd()) {
            return self::alumnosPpdDelCurso($curso);
        }

        return self::alumnosFidDelCurso($curso, $periodoActual);
    }

    /**
     * Lista de alumnos para cursos FID (misma lógica que calificar y panel de alumnos).
     */
    private static function alumnosFidDelCurso(Curso $curso, ?PeriodoActual $periodoActual): Collection
    {
        $filtroFidActivo = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'alumno'));
        };

        $filtroFidInhabilitado = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
              ->where('perfil', '!=', 'Retirado');
        };

        $alumnosRelacionadosActivos = $curso->alumnosDelPeriodo($periodoActual)
            ->with(['user.roles', 'ciclo'])
            ->whereHas('user', $filtroFidActivo)
            ->get();

        $alumnosRelacionadosInhabilitados = collect();
        if ($periodoActual) {
            $alumnosRelacionadosInhabilitados = $curso->alumnos()
                ->with(['user.roles', 'ciclo'])
                ->whereHas('user', $filtroFidInhabilitado)
                ->wherePivot('periodo_actual_id', $periodoActual->id)
                ->get();
        }

        $alumnosRelacionados = $alumnosRelacionadosActivos
            ->merge($alumnosRelacionadosInhabilitados)
            ->sortBy('apellidos')
            ->values();

        $alumnosCiclo = $curso->ciclo ? $curso->ciclo->alumnos()
            ->with(['user.roles', 'ciclo'])
            ->whereHas('user', $filtroFidActivo)
            ->orderBy('apellidos')
            ->get() : collect();

        $todos = $alumnosCiclo
            ->merge($alumnosRelacionados)
            ->unique('id')
            ->sortBy('apellidos')
            ->values();

        return $todos->map(function (Alumno $a) use ($curso) {
            $u = $a->user;
            $licencia = self::verificarLicencia($u, $a);
            $beca = self::clasificarBeca($u, $a);

            $foto = $u?->foto ? asset('img/estudiantes/' . $u->foto) : null;
            $esInhabilitado = $u ? $u->hasRole('inhabilitado') : false;

            return (object) [
                'clave' => 'fid-' . $a->id,
                'user_id' => $u?->id,
                'alumno_id' => $a->id,
                'apellidos' => $a->apellidos,
                'nombres' => $a->nombres,
                'nombre_completo' => trim($a->apellidos . ', ' . $a->nombres),
                'dni' => $a->dni ?: ($u?->dni ?? '—'),
                'email' => $u?->email ?: $a->email,
                'telefono' => $a->numero,
                'foto' => $foto,
                'ciclo_nombre' => $a->ciclo?->nombre ?? ($curso->ciclo?->nombre ?? '—'),
                'es_curso_ciclo' => (int) ($a->ciclo_id ?? 0) === (int) $curso->ciclo_id,
                'es_inhabilitado' => $esInhabilitado,
                'en_licencia' => $licencia['en_licencia'],
                'motivo_licencia' => $licencia['motivo'],
                'licencia_badge' => $licencia['badge_class'],
                'licencia_label' => $licencia['badge_label'],
                'licencia_icono' => $licencia['icono'],
                'es_beca' => $beca['es_beca'],
                'tipo_beca' => $beca['tipo'],
                'origen_beca' => $beca['detalle'],
                'beca_badge' => $beca['badge_class'],
                'beca_label' => $beca['badge_label'],
                'beca_icono' => $beca['icono'],
            ];
        });
    }

    /**
     * Lista de alumnos para cursos PPD.
     */
    private static function alumnosPpdDelCurso(Curso $curso): Collection
    {
        $alumnos = User::with(['ciclo.programa', 'alumnoB', 'roles'])
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            ->where('programa_id', $curso->ciclo?->programa_id)
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->orderBy('apellidos')
            ->get();

        return $alumnos->map(function (User $u) use ($curso) {
            $ficha = $u->alumnoB;
            $licencia = self::verificarLicencia($u, $ficha);
            $beca = self::clasificarBeca($u, $ficha);

            $foto = $u->foto
                ? asset(str_contains($u->foto, '/') ? $u->foto : 'img/estudiantes/' . $u->foto)
                : null;

            $esInhabilitado = $u->roles->contains('name', 'inhabilitado');

            return (object) [
                'clave' => 'ppd-' . $u->id,
                'user_id' => $u->id,
                'alumno_id' => $ficha?->id,
                'apellidos' => $u->apellidos,
                'nombres' => $u->name,
                'nombre_completo' => trim($u->apellidos . ', ' . $u->name),
                'dni' => $u->dni ?: ($ficha?->dni ?? '—'),
                'email' => $u->email ?: ($ficha?->email ?? '—'),
                'telefono' => $ficha?->numero,
                'foto' => $foto,
                'ciclo_nombre' => $u->ciclo?->nombre ?? 'PPD',
                'es_curso_ciclo' => true,
                'es_inhabilitado' => $esInhabilitado,
                'en_licencia' => $licencia['en_licencia'],
                'motivo_licencia' => $licencia['motivo'],
                'licencia_badge' => $licencia['badge_class'],
                'licencia_label' => $licencia['badge_label'],
                'licencia_icono' => $licencia['icono'],
                'es_beca' => $beca['es_beca'],
                'tipo_beca' => $beca['tipo'],
                'origen_beca' => $beca['detalle'],
                'beca_badge' => $beca['badge_class'],
                'beca_label' => $beca['badge_label'],
                'beca_icono' => $beca['icono'],
            ];
        });
    }

    /**
     * Guarda o actualiza la asistencia de una fecha para un curso.
     */
    public static function guardarAsistencia(Curso $curso, Docente $docente, array $datos, ?int $creadorId = null): Asistencia
    {
        return DB::transaction(function () use ($curso, $docente, $datos, $creadorId) {
            $fecha = $datos['fecha'] ?? now()->toDateString();
            $periodoActual = PeriodoActual::actual();

            $asistencia = Asistencia::updateOrCreate(
                [
                    'curso_id' => $curso->id,
                    'fecha' => $fecha,
                ],
                [
                    'docente_id' => $docente->id,
                    'periodo_actual_id' => $periodoActual?->id,
                    'tema' => $datos['tema'] ?? null,
                    'observaciones' => $datos['observaciones'] ?? null,
                    'created_by' => $creadorId,
                ]
            );

            if (isset($datos['alumnos']) && is_array($datos['alumnos'])) {
                foreach ($datos['alumnos'] as $userId => $item) {
                    $userId = (int) $userId;
                    if ($userId <= 0) {
                        continue;
                    }

                    $estado = in_array($item['estado'] ?? '', ['presente', 'tardanza', 'falta', 'justificado', 'licencia'], true)
                        ? $item['estado']
                        : 'presente';

                    AsistenciaDetalle::updateOrCreate(
                        [
                            'asistencia_id' => $asistencia->id,
                            'user_id' => $userId,
                        ],
                        [
                            'alumno_id' => ! empty($item['alumno_id']) ? (int) $item['alumno_id'] : null,
                            'estado' => $estado,
                            'en_licencia' => ! empty($item['en_licencia']),
                            'es_beca' => ! empty($item['es_beca']),
                            'tipo_beca' => $item['tipo_beca'] ?? null,
                            'origen_beca' => $item['origen_beca'] ?? null,
                            'observacion' => ! empty($item['observacion']) ? trim($item['observacion']) : null,
                        ]
                    );
                }
            }

            return $asistencia->fresh(['detalles.user', 'detalles.alumno', 'curso.ciclo.programa', 'docente']);
        });
    }

    /**
     * Resumen de asistencia para un ciclo (panel de tutoría).
     */
    public static function resumenCiclo(Ciclo $ciclo, ?PeriodoActual $periodo = null): array
    {
        $periodo = $periodo ?? PeriodoActual::actual();

        // Cursos del ciclo
        $cursos = $ciclo->cursos()->with('docentes')->get();
        $cursoIds = $cursos->pluck('id');

        $sesiones = Asistencia::with(['detalles', 'curso', 'docente'])
            ->whereIn('curso_id', $cursoIds)
            ->when($periodo, fn ($q) => $q->where('periodo_actual_id', $periodo->id))
            ->orderByDesc('fecha')
            ->get();

        $alumnos = $ciclo->alumnos()->with(['user.roles', 'ciclo'])->get();

        // Métricas por alumno
        $estadisticasAlumnos = $alumnos->map(function ($alumno) use ($sesiones) {
            $user = $alumno->user;
            $userId = $user?->id;
            $licencia = self::verificarLicencia($user, $alumno);
            $beca = self::clasificarBeca($user, $alumno);

            $detallesAlumno = $sesiones->flatMap->detalles->where('user_id', $userId);
            $totalSesiones = $detallesAlumno->count();
            $presentes = $detallesAlumno->where('estado', 'presente')->count();
            $tardanzas = $detallesAlumno->where('estado', 'tardanza')->count();
            $faltas = $detallesAlumno->where('estado', 'falta')->count();
            $justificados = $detallesAlumno->where('estado', 'justificado')->count();
            $licencias = $detallesAlumno->where('estado', 'licencia')->count();

            $porcentaje = $totalSesiones > 0 ? round((($presentes + $tardanzas) / $totalSesiones) * 100, 1) : null;
            $alertaFaltas = ($faltas >= 3) || ($porcentaje !== null && $porcentaje < 75);

            return [
                'alumno' => $alumno,
                'user' => $user,
                'nombre' => trim($alumno->apellidos . ', ' . $alumno->nombres),
                'dni' => $alumno->dni,
                'en_licencia' => $licencia['en_licencia'],
                'licencia_badge' => $licencia['badge_class'],
                'licencia_label' => $licencia['badge_label'],
                'es_beca' => $beca['es_beca'],
                'tipo_beca' => $beca['tipo'],
                'beca_badge' => $beca['badge_class'],
                'beca_label' => $beca['badge_label'],
                'total_sesiones' => $totalSesiones,
                'presentes' => $presentes,
                'tardanzas' => $tardanzas,
                'faltas' => $faltas,
                'justificados' => $justificados,
                'licencias' => $licencias,
                'porcentaje' => $porcentaje,
                'alerta' => $alertaFaltas,
            ];
        })->sortByDesc('faltas')->values();

        $totalSesiones = $sesiones->count();
        $totalAlumnos = $alumnos->count();
        $totalBecarios = $estadisticasAlumnos->where('es_beca', true)->count();
        $totalAmantani = $estadisticasAlumnos->filter(fn ($a) => stripos($a['tipo_beca'] ?? '', 'amantani') !== false)->count();
        $totalPronabec = $estadisticasAlumnos->filter(fn ($a) => stripos($a['tipo_beca'] ?? '', 'pronabec') !== false)->count();
        $totalLicencias = $estadisticasAlumnos->where('en_licencia', true)->count();
        $totalEnAlerta = $estadisticasAlumnos->where('alerta', true)->count();

        return [
            'ciclo' => $ciclo,
            'cursos' => $cursos,
            'sesiones' => $sesiones,
            'alumnos' => $estadisticasAlumnos,
            'total_sesiones' => $totalSesiones,
            'total_alumnos' => $totalAlumnos,
            'total_becarios' => $totalBecarios,
            'total_amantani' => $totalAmantani,
            'total_pronabec' => $totalPronabec,
            'total_licencias' => $totalLicencias,
            'total_en_alerta' => $totalEnAlerta,
        ];
    }
}
