<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AsistenciaDetalle;
use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluEventoCalendario;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPerfil;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Comunicado;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Matricula;
use App\Models\MatriculaPpd;
use App\Models\Minkarikuy;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\Proyecto;
use App\Models\TaqeGrupo;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AylluContextService
{
    /**
     * Construye el contexto integral del usuario autenticado reutilizando
     * todas las tablas y modelos existentes sin duplicar fuentes de verdad.
     */
    public function resolverContexto(User $user): array
    {
        $esDocente = $user->hasRole('docente') || $user->docente !== null;
        $esTutor = $user->hasRole('tutor');
        $esAdmin = $user->hasAnyRole(['super-admin', 'admin', 'adminB']);
        $esEgresado = ($user->condicion === 'Egresado');
        $esAlumnoFid = $user->hasRole('alumno') || ($user->alumno !== null && !$esEgresado);
        $esAlumnoPpd = $user->hasRole('alumnoB') || ($user->alumnoB !== null && !$esEgresado);

        $alumno = $user->alumno;
        $ppd = $user->alumnoB;
        $docente = $user->docente;

        $programa = $user->programa ?? $alumno?->programa ?? $ppd?->programa;
        $ciclo = $user->ciclo ?? $alumno?->ciclo ?? $ppd?->ciclo;

        $periodoActual = PeriodoActual::actual();
        $periodoActualPpd = class_exists(PeriodoActualPpd::class)
            ? PeriodoActualPpd::where('actual', true)->first()
            : null;

        // Cursos vinculados según el rol real
        $cursos = $this->obtenerCursosDeUsuario($user, $alumno, $ppd, $docente, $ciclo, $periodoActual);

        // Proyecto Anual Integrador del ciclo actual (o proyectos a cargo del docente)
        $proyectoIntegrador = null;
        $proyectosDocente = collect();
        if ($ciclo && $ciclo->proyecto_id) {
            $proyectoIntegrador = Proyecto::with(['ciclos', 'docentes'])->find($ciclo->proyecto_id);
        }
        if ($docente && Schema::hasTable('proyecto_docente')) {
            $proyectosDocente = $docente->proyectosIntegradores()->with('ciclos')->get();
            if (!$proyectoIntegrador && $proyectosDocente->isNotEmpty()) {
                $proyectoIntegrador = $proyectosDocente->first();
            }
        }
        if (!$proyectoIntegrador && $cursos->isNotEmpty()) {
            $cicloCurso = $cursos->first()->ciclo;
            if ($cicloCurso && $cicloCurso->proyecto_id) {
                $proyectoIntegrador = Proyecto::with(['ciclos', 'docentes'])->find($cicloCurso->proyecto_id);
            }
        }

        // Grupos Taq'e del estudiante o de los cursos del docente
        $taqeGrupos = collect();
        if (Schema::hasTable('taqe_grupos')) {
            if ($alumno) {
                $taqeGrupos = $alumno->taqeGrupos()->with(['curso', 'proyecto', 'trabajos', 'alumnosAceptados'])->get();
            } elseif ($docente && $cursos->isNotEmpty()) {
                $taqeGrupos = TaqeGrupo::with(['curso', 'proyecto', 'trabajos', 'alumnosAceptados'])
                    ->whereIn('curso_id', $cursos->pluck('id'))
                    ->latest()
                    ->take(10)
                    ->get();
            } elseif ($esAdmin) {
                $taqeGrupos = TaqeGrupo::with(['curso', 'proyecto', 'trabajos', 'alumnosAceptados'])
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        // Comunicados oficiales y Minkarikuy vigentes
        $comunicados = Schema::hasTable('comunicados')
            ? Comunicado::orderByDesc('fecha_publicacion')->orderByDesc('id')->take(4)->get()
            : collect();

        $minkarikuy = Schema::hasTable('minkarikuys')
            ? Minkarikuy::activo()
            : null;

        // Perfil intercultural Ayllu (1:1 con User)
        $aylluPerfil = AylluPerfil::firstOrCreate(
            ['user_id' => $user->id],
            [
                'saludo_intercultural' => 'Allillanchu · Kuska yachasunchis (Aprendamos en comunidad)',
                'lengua_preferida' => ($user->lengua_1 || $alumno?->lengua_1) ? 'castellano_quechua' : 'castellano',
                'saberes_intereses' => ['Diálogo de saberes', 'Crianza mutua', 'Educación Intercultural Bilingüe'],
            ]
        );

        // Resumen privado (Asistencia, Matrícula, Notas Oficiales) — Solo para el titular
        $resumenPrivado = $this->obtenerResumenPrivado($user, $alumno, $ppd, $periodoActual, $periodoActualPpd);

        // Puede publicar en el muro general institucional (Decisión A3 aprobada)
        $puedePublicarMuroGeneral = $user->can('ayllu.publicar-muro-general')
            || $esDocente
            || $esTutor
            || $esAdmin;

        $puedeModerar = $user->can('ayllu.moderar') || $esDocente || $esTutor || $esAdmin;

        return [
            'user' => $user,
            'esDocente' => $esDocente,
            'esTutor' => $esTutor,
            'esAdmin' => $esAdmin,
            'esEgresado' => $esEgresado,
            'esAlumnoFid' => $esAlumnoFid,
            'esAlumnoPpd' => $esAlumnoPpd,
            'esEstudiante' => ($esAlumnoFid || $esAlumnoPpd),
            'alumno' => $alumno,
            'ppd' => $ppd,
            'docente' => $docente,
            'programa' => $programa,
            'ciclo' => $ciclo,
            'etiquetaPrograma' => $programa?->nombre ?? ($esAlumnoPpd ? 'Profesionalización Docente (PPD)' : 'Formación Inicial Docente (FID)'),
            'etiquetaCiclo' => $ciclo?->nombre ?? '',
            'lengua1' => $user->lengua_1 ?? $alumno?->lengua_1 ?? $ppd?->lengua_1 ?? 'Castellano',
            'lengua2' => $user->lengua_2 ?? $alumno?->lengua_2 ?? $ppd?->lengua_2 ?? 'Quechua',
            'periodoActual' => $periodoActual,
            'periodoActualPpd' => $periodoActualPpd,
            'cursos' => $cursos,
            'proyectoIntegrador' => $proyectoIntegrador,
            'proyectosDocente' => $proyectosDocente,
            'taqeGrupos' => $taqeGrupos,
            'comunicados' => $comunicados,
            'minkarikuy' => $minkarikuy,
            'aylluPerfil' => $aylluPerfil,
            'resumenPrivado' => $resumenPrivado,
            'puedePublicarMuroGeneral' => $puedePublicarMuroGeneral,
            'puedeModerar' => $puedeModerar,
            'etiquetaRol' => $this->etiquetaRol($user, $esDocente, $esTutor, $esAdmin, $esEgresado, $esAlumnoFid, $esAlumnoPpd),
        ];
    }

    public function obtenerCursosDeUsuario(
        User $user,
        ?Alumno $alumno,
        $ppd,
        ?Docente $docente,
        $ciclo,
        ?PeriodoActual $periodoActual
    ): Collection {
        if ($docente) {
            return $docente->cursos()
                ->with(['ciclo.programa', 'relacionsilabo.unidades', 'relacionsilabo.rubricas', 'docentes'])
                ->get();
        }

        if ($alumno) {
            $cursosCiclo = $ciclo
                ? Curso::where('ciclo_id', $ciclo->id)->pluck('id')
                : collect();

            $cursosExtra = $periodoActual
                ? $alumno->cursos()
                    ->where(function ($q) use ($periodoActual) {
                        $q->whereNull('alumno_cursos.periodo_actual_id')
                          ->orWhere('alumno_cursos.periodo_actual_id', $periodoActual->id);
                    })
                    ->pluck('cursos.id')
                : $alumno->cursos()->pluck('cursos.id');

            $ids = $cursosCiclo->merge($cursosExtra)->unique()->values();

            return Curso::with(['ciclo.programa', 'relacionsilabo.unidades', 'relacionsilabo.rubricas', 'docentes'])
                ->whereIn('id', $ids)
                ->get();
        }

        if ($ppd && $ciclo) {
            return Curso::with(['ciclo.programa', 'relacionsilabo.unidades', 'relacionsilabo.rubricas', 'docentes'])
                ->where('ciclo_id', $ciclo->id)
                ->get();
        }

        // Si es admin/super-admin o tutor, muestra cursos de sus ciclos tutorados o muestra representativa con sílabo
        if ($user->tutorCiclos()->exists()) {
            $cicloIds = $user->tutorCiclos()->pluck('ciclos.id');
            return Curso::with(['ciclo.programa', 'relacionsilabo.unidades', 'relacionsilabo.rubricas', 'docentes'])
                ->whereIn('ciclo_id', $cicloIds)
                ->take(12)
                ->get();
        }

        return Curso::with(['ciclo.programa', 'relacionsilabo.unidades', 'relacionsilabo.rubricas', 'docentes'])
            ->whereHas('silabos')
            ->take(8)
            ->get();
    }

    public function obtenerResumenPrivado(
        User $user,
        ?Alumno $alumno,
        $ppd,
        ?PeriodoActual $periodoActual,
        $periodoActualPpd
    ): array {
        $asistenciasTotal = 0;
        $asistenciasEfectivas = 0;
        $porcentajeAsistencia = null;

        if (Schema::hasTable('asistencia_detalles')) {
            $detalles = AsistenciaDetalle::where('user_id', $user->id)->get();
            $asistenciasTotal = $detalles->count();
            if ($asistenciasTotal > 0) {
                $asistenciasEfectivas = $detalles->whereIn('estado', ['presente', 'tardanza'])->count();
                $porcentajeAsistencia = round(($asistenciasEfectivas / $asistenciasTotal) * 100, 1);
            }
        }

        $matriculaEstado = 'No requiere / Personal';
        if ($alumno && $periodoActual && Schema::hasTable('matriculas')) {
            $mat = Matricula::where('alumno_id', $alumno->id)
                ->where('periodo_actual_id', $periodoActual->id)
                ->first();
            $matriculaEstado = $mat
                ? ($mat->voucher_verificado ? 'Matriculado (Voucher verificado)' : 'En revisión de voucher')
                : 'Pendiente de registro en ' . $periodoActual->nombre;
        } elseif ($ppd && $periodoActualPpd && Schema::hasTable('matriculas_ppd')) {
            $matPpd = MatriculaPpd::where('ppd_id', $ppd->id)
                ->where('periodo_actual_ppd_id', $periodoActualPpd->id)
                ->first();
            $matriculaEstado = $matPpd
                ? ($matPpd->voucher_verificado ? 'Matriculado PPD (Verificado)' : 'Voucher PPD en revisión')
                : 'Pendiente de registro PPD';
        }

        $entregasFormativasCount = Schema::hasTable('ayllu_entregas_formativas')
            ? AylluEntregaFormativa::where('user_id', $user->id)->count()
            : 0;

        $portafolioCount = Schema::hasTable('ayllu_entregas_formativas')
            ? AylluEntregaFormativa::where('user_id', $user->id)->where('en_portafolio', true)->count()
            : 0;

        $calificacionesRegistradas = 0;
        if ($alumno && Schema::hasTable('calificacions')) {
            $calificacionesRegistradas = \App\Models\Calificacion::where('alumno_id', $alumno->id)->count();
        } elseif ($ppd && Schema::hasTable('calificacionesppds')) {
            $calificacionesRegistradas = \App\Models\Calificacionesppd::where('ppd_id', $ppd->id)->count();
        }

        return [
            'asistencias_total' => $asistenciasTotal,
            'asistencias_efectivas' => $asistenciasEfectivas,
            'asistencias_presentes' => $asistenciasEfectivas,
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'matricula_estado' => $matriculaEstado,
            'estado_matricula' => $matriculaEstado,
            'calificaciones_registradas' => $calificacionesRegistradas,
            'entregas_formativas_count' => $entregasFormativasCount,
            'portafolio_count' => $portafolioCount,
            'ruta_calificaciones' => $alumno
                ? route('calificaciones', $alumno->id)
                : ($ppd ? route('calificacionesppd', $ppd->id) : route('ayllu.privado')),
            'ruta_ficha' => $alumno
                ? route('alumnos.index')
                : ($ppd ? route('ppd.index') : route('ayllu.perfil')),
        ];
    }

    public function etiquetaRol(
        User $user,
        bool $esDocente,
        bool $esTutor,
        bool $esAdmin,
        bool $esEgresado,
        bool $esAlumnoFid,
        bool $esAlumnoPpd
    ): string {
        if ($esAdmin && !$esDocente) {
            return 'Gestión Institucional';
        }
        if ($esDocente && $esTutor) {
            return 'Docente Formador · Tutor(a)';
        }
        if ($esDocente) {
            return 'Docente Formador(a)';
        }
        if ($esEgresado) {
            return 'Egresado(a) · Puklla en Red';
        }
        if ($esAlumnoFid) {
            return 'Estudiante FID';
        }
        if ($esAlumnoPpd) {
            return 'Estudiante PPD';
        }
        return 'Comunidad Pukllasunchis';
    }

    /**
     * Inicializa grupos comunitarios oficiales y publicaciones pedagógicas de bienvenida
     * solo si las tablas nuevas están vacías. No crea usuarios ficticios.
     */
    public function asegurarSemillaInstitucional(): void
    {
        if (!Schema::hasTable('ayllu_grupos') || !Schema::hasTable('ayllu_publicaciones')) {
            return;
        }

        if (AylluGrupo::count() === 0) {
            $autorId = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['docente', 'super-admin', 'admin']))
                ->value('id') ?? User::value('id');

            $gruposIniciales = [
                [
                    'nombre' => 'Rimaykusun — Círculo de Quechua y Diálogo de Saberes',
                    'nombre_quechua' => 'Runasimiwan Yachaykuna',
                    'slug' => 'rimaykusun-quechua-saberes',
                    'descripcion' => 'Espacio de crianza lingüística y cultural para practicar el quechua chanka y collao, compartir relatos de nuestras comunidades y fortalecer la EIB.',
                    'tipo' => 'intercultural',
                    'icono' => 'fas fa-feather-alt',
                    'color' => '#cd9244',
                    'creado_por' => $autorId,
                    'es_oficial' => true,
                ],
                [
                    'nombre' => 'Viernes Puklla — Arte, Música, Sikuris y Comunidad',
                    'nombre_quechua' => 'Kuska Pukllasunchis',
                    'slug' => 'viernes-puklla-arte-comunidad',
                    'descripcion' => 'Talleres vivenciales de música tradicional (Ayarachi, Sikuris), teatro pedagógico, tejidos y expresión artística en la formación docente.',
                    'tipo' => 'taller',
                    'icono' => 'fas fa-music',
                    'color' => '#832128',
                    'creado_por' => $autorId,
                    'es_oficial' => true,
                ],
                [
                    'nombre' => 'Práctica Preprofesional y Crianza Mutua en Escuela',
                    'nombre_quechua' => 'Yachaywasipi Uywanakuy',
                    'slug' => 'practica-preprofesional-crianza-mutua',
                    'descripcion' => 'Comunidad de acompañamiento entre estudiantes de Inicial, Primaria y Primaria EIB para compartir diarios de campo, materiales didácticos y experiencias en aula.',
                    'tipo' => 'circulo_estudio',
                    'icono' => 'fas fa-chalkboard-teacher',
                    'color' => '#0e9467',
                    'creado_por' => $autorId,
                    'es_oficial' => true,
                ],
                [
                    'nombre' => 'Semillero Taq’e — Proyectos Anuales Integradores',
                    'nombre_quechua' => 'Taq’e Maskhaykuna',
                    'slug' => 'semillero-taqe-proyectos-integradores',
                    'descripcion' => 'Articulación de los productos de cada curso con el Proyecto Anual Integrador de ciclo y las ferias de investigación Taq’e.',
                    'tipo' => 'investigacion',
                    'icono' => 'fas fa-seedling',
                    'color' => '#2563eb',
                    'creado_por' => $autorId,
                    'es_oficial' => true,
                ],
            ];

            foreach ($gruposIniciales as $g) {
                AylluGrupo::firstOrCreate(['slug' => $g['slug']], $g);
            }
        } else {
            AylluGrupo::where('slug', 'semillero-taqe-proyectos-integradores')
                ->update(['nombre_quechua' => 'Taq’e Maskhaykuna']);
        }

        if (AylluPublicacion::count() === 0) {
            $docenteUser = User::whereHas('roles', fn ($q) => $q->where('name', 'docente'))->first()
                ?? User::first();

            if ($docenteUser) {
                $cursoEjemplo = Curso::whereHas('silabos')->first() ?? Curso::first();
                $grupoQuechua = AylluGrupo::where('slug', 'rimaykusun-quechua-saberes')->first();

                AylluPublicacion::create([
                    'user_id' => $docenteUser->id,
                    'ambito' => 'institucional',
                    'tipo' => 'anuncio',
                    'titulo' => 'Allinllachu, bienvenidos a Ayllu Puklla — Un espacio para aprender y construir comunidad',
                    'contenido' => 'Iniciamos nuestro tejido digital en la EESP Pukllasunchis. Aquí articulamos nuestros cursos como comunidades con sílabo vivo, el acompañamiento formativo continuo y los aportes de cada asignatura hacia el Proyecto Anual Integrador (Taq’e), manteniendo siempre separadas y protegidas nuestras calificaciones y trámites oficiales.',
                    'resumen_quechua' => 'Kusisqa chaskiykiku Ayllu Puklla llikanchisman. Kay ayllupi kuska yachasunchis, uywanakusunchis ima.',
                    'etiquetas' => ['Ayllu Puklla', 'Crianza Mutua', 'EIB', 'Comunidad'],
                    'fijado' => true,
                    'destacado_inicio' => true,
                    'visible' => true,
                ]);

                if ($cursoEjemplo) {
                    AylluPublicacion::create([
                        'user_id' => $docenteUser->id,
                        'ambito' => 'curso',
                        'curso_id' => $cursoEjemplo->id,
                        'ciclo_id' => $cursoEjemplo->ciclo_id,
                        'tipo' => 'recurso',
                        'titulo' => 'Sílabo Vivo y Producto del Curso hacia el Proyecto Integrador',
                        'contenido' => 'En la comunidad de este curso ya pueden consultar las unidades del Sílabo Vivo, los criterios de rúbrica y registrar sus avances formativos para recibir retroalimentación oportuna antes de la entrega al repositorio Taq’e.',
                        'resumen_quechua' => 'Kay yachachiypi llamk’ayninchiskunata qhawariychis Taq’e hatun llamk’ayman churanapaq.',
                        'etiquetas' => ['Sílabo Vivo', 'Evaluación Formativa', 'Taq’e'],
                        'fijado' => false,
                        'destacado_inicio' => true,
                        'visible' => true,
                    ]);
                }

                if ($grupoQuechua) {
                    AylluPublicacion::create([
                        'user_id' => $docenteUser->id,
                        'ambito' => 'grupo',
                        'ayllu_grupo_id' => $grupoQuechua->id,
                        'tipo' => 'practica_eib',
                        'titulo' => 'Diálogo de saberes: calendario agrofestivo y planificación curricular EIB',
                        'contenido' => 'Invitamos a las y los estudiantes de Inicial, Primaria y Primaria EIB a compartir qué señas de la naturaleza y actividades comunitarias están observando este mes en sus localidades para integrarlas en nuestras sesiones de aprendizaje.',
                        'resumen_quechua' => 'Pacha qhawaymanta rimarisunchis wawakunawan allin yachachinapaq.',
                        'etiquetas' => ['Calendario Comunal', 'Quechua', 'Práctica EIB'],
                        'fijado' => false,
                        'destacado_inicio' => true,
                        'visible' => true,
                    ]);
                }
            }
        } else {
            AylluPublicacion::where('resumen_quechua', 'like', '%Kay%pi%')
                ->update(['resumen_quechua' => 'Kusisqa chaskiykiku Ayllu Puklla llikanchisman. Kay ayllupi kuska yachasunchis, uywanakusunchis ima.']);
        }

        if (Schema::hasTable('ayllu_eventos_calendario') && AylluEventoCalendario::count() === 0) {
            AylluEventoCalendario::create([
                'titulo' => 'Encuentro de Socialización de Proyectos Integradores (Taq’e)',
                'descripcion' => 'Jornada intercultural de diálogo de saberes y presentación de productos de curso articulados al Proyecto Anual Integrador.',
                'tipo' => 'taqe',
                'fecha_inicio' => now()->addDays(12)->toDateString(),
                'hora' => '09:00',
            ]);
            AylluEventoCalendario::create([
                'titulo' => 'Viernes Puklla — Círculo de Música Andina y Quechua',
                'descripcion' => 'Espacio comunitario de convivencia, canto y sikuris en el patio central de Pukllasunchis.',
                'tipo' => 'comunitario',
                'fecha_inicio' => now()->next('Friday')->toDateString(),
                'hora' => '15:30',
            ]);
        }
    }
}
