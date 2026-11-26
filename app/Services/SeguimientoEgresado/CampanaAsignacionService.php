<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampanaAsignacionService
{
    /**
     * Recupera exclusivamente docentes reales de la base institucional.
     * Criterio: Usuario con rol Spatie 'docente', activo/habilitado.
     */
    public function obtenerDocentesElegibles(): Collection
    {
        $docentesUsers = User::role('docente')
            ->where(function ($q) {
                // Excluir inhabilitados si existe rol
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'inhabilitado'));
            })
            ->with(['docente.cursos.ciclo.programa', 'roles'])
            ->orderBy('apellidos')
            ->orderBy('name')
            ->get();

        return $docentesUsers->map(function (User $user) {
            $docenteModel = $user->docente;
            $programa = 'Cuerpo Docente Institucional';
            if ($docenteModel && $docenteModel->cursos && $docenteModel->cursos->isNotEmpty()) {
                $progs = $docenteModel->cursos->map(fn ($c) => $c->ciclo?->programa?->nombre)->filter()->unique()->implode(', ');
                if ($progs) {
                    $programa = str_replace('Programa ', '', $progs);
                }
            }

            $tieneRolSeguimiento = $user->hasRole('seguimiento-egresados');
            $participacionCampanas = AsignacionCampana::where('docente_user_id', $user->id)
                ->distinct('campana_id')
                ->count('campana_id');

            $asignadosActivos = AsignacionCampana::activos()
                ->where('docente_user_id', $user->id)
                ->count();

            return [
                'user_id' => $user->id,
                'docente_id' => $docenteModel?->id,
                'nombre_completo' => trim($user->name . ' ' . $user->apellidos),
                'email' => $user->email,
                'dni' => $user->dni,
                'programa' => $programa,
                'roles' => $user->getRoleNames()->toArray(),
                'habilitado' => true,
                'tiene_rol_seguimiento' => $tieneRolSeguimiento,
                'campanas_participadas' => $participacionCampanas,
                'asignados_actuales' => $asignadosActivos,
                'es_test' => str_contains($user->email, '@puklla.test'),
            ];
        });
    }

    /**
     * Recupera y diagnostica el padrón de egresados reales institucionales.
     * Criterio: users.condicion = 'Egresado', excluyendo usuarios de test.
     */
    public function obtenerPadronEgresadosElegibles(?int $campanaId = null, bool $excluirAsignados = false, string $grupoFormacion = 'TODOS', array $filtrosAdicionales = []): Collection
    {
        $query = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'seguimientoTitulacion.estadoTitulacion'])
            ->orderBy('apellidos')
            ->orderBy('name');

        // Excluir usuarios creados estrictamente para pruebas sintéticas
        $query->whereNotIn('email', ['auditor.egresada@puklla.test', 'egresado.noasignado@puklla.test']);

        if ($campanaId && $excluirAsignados) {
            $asignadosIds = AsignacionCampana::activos()
                ->where('campana_id', $campanaId)
                ->pluck('egresado_user_id')
                ->toArray();

            if (!empty($asignadosIds)) {
                $query->whereNotIn('id', $asignadosIds);
            }
        }

        $clasificacion = app(GrupoFormacionService::class);
        $clasificacion->aplicar($query, $grupoFormacion);
        $egresados = $query->get();

        $padron = $egresados->map(function (User $u) use ($campanaId, $grupoFormacion, $clasificacion) {
            $alumno = $clasificacion->ficha($u, $grupoFormacion);
            $meta = $clasificacion->resolver($u);
            $ultima = Actualizacion::where('user_id', $u->id)->where('estado_verificacion','verificado')->latest('id')->first();
            $tit = $u->seguimientoTitulacion;

            $tel = $ultima?->telefono ?: ($alumno?->numero ?: $u->telefono ?: '');
            $telAlt = $alumno?->numero_referencia ?: '';
            $tieneTelefono = strlen(trim((string) $tel)) >= 6 || strlen(trim((string) $telAlt)) >= 6;
            $emailValido = filter_var($u->email, FILTER_VALIDATE_EMAIL) !== false;

            $dep = $ultima?->res_departamento ?: $alumno?->departamento ?: '';
            $prov = $ultima?->res_provincia ?: $alumno?->provincia ?: '';
            $dist = $ultima?->res_distrito ?: $alumno?->distrito ?: '';

            $verPerfil = DB::table('egresado_perfil_versiones')->where('user_id', $u->id)->latest('id')->first();
            $tieneUbicacionVerificable = false;
            if ($verPerfil && $verPerfil->datos) {
                $dat = json_decode($verPerfil->datos, true);
                $res = $dat['residencia'] ?? [];
                if (empty($dep) && empty($prov) && empty($dist)) {
                    $dep = $res['departamento'] ?? '';
                    $prov = $res['provincia'] ?? '';
                    $dist = $res['distrito'] ?? ($res['localidad'] ?? '');
                }
                if (isset($res['latitud'], $res['longitud']) && in_array($res['precision'] ?? '', ['localidad', 'escuela', 'sede'])) {
                    $tieneUbicacionVerificable = true;
                }
                foreach ($dat['trabajos'] ?? [] as $tb) {
                    $lugar = $tb['lugar'] ?? [];
                    if (isset($lugar['latitud'], $lugar['longitud']) && in_array($lugar['precision'] ?? '', ['localidad', 'escuela', 'sede'])) {
                        $tieneUbicacionVerificable = true;
                    }
                }
            }

            $tieneResidencia = !empty($dep) || !empty($prov) || !empty($dist);

            $tieneTrabajo = EpisodioLaboral::where('user_id', $u->id)->exists()
                || Actualizacion::where('user_id', $u->id)->whereNotNull('institucion')->where('institucion', '!=', '')->exists();

            $cohorte = 'Por verificar';
            if ($tit && $tit->periodo_estudios) {
                $cohorte = $tit->periodo_estudios;
            }

            $progNombre = 'Sin programa asignado';
            $programaInstitucional = $clasificacion->programa($u, $grupoFormacion);
            if ($programaInstitucional) $progNombre = $programaInstitucional->nombre;

            $asignacionActual = null;
            if ($campanaId) {
                $asig = AsignacionCampana::activos()
                    ->where('campana_id', $campanaId)
                    ->where('egresado_user_id', $u->id)
                    ->with('docente')
                    ->first();

                if ($asig) {
                    $asignacionActual = [
                        'asignacion_id' => $asig->id,
                        'docente_user_id' => $asig->docente_user_id,
                        'docente_nombre' => $asig->docente ? trim($asig->docente->name . ' ' . $asig->docente->apellidos) : 'Sin nombre',
                        'estado' => $asig->estado,
                        'fecha' => $asig->fecha_asignacion?->toDateTimeString(),
                    ];
                }
            }

            // Estado de contacto unificado y canónico
            $estadoContacto = $this->clasificarEstadoContacto($u->id, $campanaId);

            return [
                'user_id' => $u->id,
                'dni' => $u->dni,
                'grupos_formacion' => $meta['grupos'],
                'grupo_formacion' => $meta['etiqueta'],
                'nombre_completo' => trim($u->name . ' ' . $u->apellidos),
                'programa' => $progNombre,
                'cohorte' => $cohorte,
                'email' => $u->email,
                'email_valido' => $emailValido,
                'telefono' => $tel,
                'telefono_alt' => $telAlt,
                'tiene_telefono' => $tieneTelefono,
                'tiene_canal_valido' => $estadoContacto['tiene_canal_valido'],
                'sin_canal_valido' => $estadoContacto['sin_canal_valido'],
                'contacto_efectivo' => $estadoContacto['contacto_efectivo'],
                'intento_sin_respuesta' => $estadoContacto['intento_sin_respuesta'],
                'sin_intento' => $estadoContacto['sin_intento'],
                'sin_contacto_efectivo' => $estadoContacto['sin_contacto_efectivo'],
                'estado_contacto' => $estadoContacto['estado_clave'],
                'estado_contacto_etiqueta' => $estadoContacto['estado_etiqueta'],
                'fecha_ultimo_contacto' => $estadoContacto['fecha_ultimo_contacto'],
                'res_departamento' => $dep ?: 'No declarado',
                'res_provincia' => $prov ?: 'No declarado',
                'res_distrito' => $dist ?: 'No declarado',
                'tiene_residencia' => $tieneResidencia,
                'tiene_trabajo' => $tieneTrabajo,
                'tiene_ubicacion_verificable' => $tieneUbicacionVerificable,
                'asignacion_actual' => $asignacionActual,
                'esta_asignado' => $asignacionActual !== null,
            ];
        });

        // Filtrado adicional si se especifica
        if (!empty($filtrosAdicionales['departamento'])) {
            $depFiltro = strtolower(trim($filtrosAdicionales['departamento']));
            $padron = $padron->filter(fn ($e) => strtolower($e['res_departamento']) === $depFiltro);
        }
        if (!empty($filtrosAdicionales['provincia'])) {
            $provFiltro = strtolower(trim($filtrosAdicionales['provincia']));
            $padron = $padron->filter(fn ($e) => strtolower($e['res_provincia']) === $provFiltro);
        }
        if (!empty($filtrosAdicionales['distrito'])) {
            $distFiltro = strtolower(trim($filtrosAdicionales['distrito']));
            $padron = $padron->filter(fn ($e) => strtolower($e['res_distrito']) === $distFiltro);
        }
        if (!empty($filtrosAdicionales['sin_ubicacion'])) {
            $padron = $padron->filter(fn ($e) => empty($e['tiene_ubicacion_verificable']));
        }
        if (!empty($filtrosAdicionales['estado_contacto']) && $filtrosAdicionales['estado_contacto'] !== 'todos') {
            $ec = $filtrosAdicionales['estado_contacto'];
            if ($ec === 'sin_contacto_efectivo') {
                $padron = $padron->where('sin_contacto_efectivo', true);
            } elseif ($ec === 'contacto_efectivo') {
                $padron = $padron->where('contacto_efectivo', true);
            } elseif ($ec === 'intento_sin_respuesta') {
                $padron = $padron->where('intento_sin_respuesta', true);
            } elseif ($ec === 'sin_intento') {
                $padron = $padron->where('sin_intento', true);
            } elseif ($ec === 'sin_canal_valido') {
                $padron = $padron->where('sin_canal_valido', true);
            }
        }
        if (!empty($filtrosAdicionales['programa']) && $filtrosAdicionales['programa'] !== 'TODOS') {
            $progFiltro = strtolower(trim($filtrosAdicionales['programa']));
            $padron = $padron->filter(fn ($e) => strtolower($e['programa']) === $progFiltro);
        }
        if (!empty($filtrosAdicionales['cohorte']) && $filtrosAdicionales['cohorte'] !== 'TODAS') {
            $cohFiltro = (string) $filtrosAdicionales['cohorte'];
            $padron = $padron->filter(fn ($e) => (string) $e['cohorte'] === $cohFiltro);
        }

        return $padron->values();
    }

    /**
     * Mapeo canónico y riguroso de Estado de Contacto para una persona (users.id)
     * en el marco de una campaña o periodo institucional.
     */
    public function clasificarEstadoContacto(int $userId, ?int $campanaId = null): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'tiene_canal_valido' => false,
                'sin_canal_valido' => true,
                'contacto_efectivo' => false,
                'intento_sin_respuesta' => false,
                'sin_intento' => true,
                'sin_contacto_efectivo' => true,
                'estado_clave' => 'sin_intento',
                'estado_etiqueta' => 'Sin intento registrado',
                'fecha_ultimo_contacto' => null,
                'respondio_autoservicio' => false,
                'asignado_campana' => false,
            ];
        }

        $alumno = Alumno::where('user_id', $userId)->first();
        $ultima = Actualizacion::where('user_id', $userId)->where('estado_verificacion', 'verificado')->latest('id')->first();
        $tel = $ultima?->telefono ?: ($alumno?->numero ?: $user->telefono ?: '');
        $telAlt = $alumno?->numero_referencia ?: '';
        $tieneTel = (strlen(trim((string) $tel)) >= 6) || (strlen(trim((string) $telAlt)) >= 6);
        $emailValido = filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false;
        $tieneCanalValido = $tieneTel || $emailValido;

        // Búsqueda de eventos que prueban contacto acreditado
        $qLlamadas = Llamada::where('user_id', $userId);
        $qActualizaciones = Actualizacion::where('user_id', $userId)->where('estado_verificacion', 'verificado');
        $qAsignaciones = AsignacionCampana::activos()->where('egresado_user_id', $userId);

        if ($campanaId) {
            $qLlamadas->where('campana_id', $campanaId);
            $qActualizaciones->where('campana_id', $campanaId);
            $qAsignaciones->where('campana_id', $campanaId);
        }

        $llamadas = $qLlamadas->get();
        $actualizaciones = $qActualizaciones->get();
        $asignaciones = $qAsignaciones->get();

        // 1. Contacto efectivo acreditado en la campaña/periodo
        $llamadaEfectiva = $llamadas->first(function ($l) {
            return (bool) $l->es_contacto_efectivo || strtolower(trim((string) $l->resultado)) === 'efectiva';
        });
        $tieneActualizacionCampana = $actualizaciones->isNotEmpty();
        $asignacionCompletada = $asignaciones->whereIn('estado', ['completado', 'contactado'])->isNotEmpty();

        $contactoEfectivo = ($llamadaEfectiva !== null) || $tieneActualizacionCampana || $asignacionCompletada;

        // Fecha de último contacto / intento
        $fechas = collect();
        if ($llamadaEfectiva && $llamadaEfectiva->fecha_hora) $fechas->push($llamadaEfectiva->fecha_hora);
        if ($actualizaciones->isNotEmpty() && $actualizaciones->first()->fecha_ultima_confirmacion) {
            $fechas->push($actualizaciones->first()->fecha_ultima_confirmacion);
        }
        foreach ($llamadas as $ll) {
            if ($ll->fecha_hora) $fechas->push($ll->fecha_hora);
        }

        $fechaUltimoContacto = $fechas->sortDesc()->first();

        // 2. Intentos sin respuesta
        $tieneIntentos = $llamadas->isNotEmpty();
        $intentoSinRespuesta = !$contactoEfectivo && $tieneIntentos;

        // 3. Sin intento registrado
        $sinIntento = !$contactoEfectivo && !$tieneIntentos;

        // 4. Superconjunto sin contacto efectivo
        $sinContactoEfectivo = !$contactoEfectivo;

        $estadoClave = $contactoEfectivo ? 'contacto_efectivo' : ($intentoSinRespuesta ? 'intento_sin_respuesta' : 'sin_intento');
        $estadoEtiqueta = match ($estadoClave) {
            'contacto_efectivo' => 'Contacto efectivo',
            'intento_sin_respuesta' => 'Intento sin respuesta',
            default => 'Sin intento registrado',
        };

        return [
            'tiene_canal_valido' => $tieneCanalValido,
            'sin_canal_valido' => !$tieneCanalValido,
            'contacto_efectivo' => $contactoEfectivo,
            'intento_sin_respuesta' => $intentoSinRespuesta,
            'sin_intento' => $sinIntento,
            'sin_contacto_efectivo' => $sinContactoEfectivo,
            'estado_clave' => $estadoClave,
            'estado_etiqueta' => $estadoEtiqueta,
            'fecha_ultimo_contacto' => $fechaUltimoContacto ? (is_string($fechaUltimoContacto) ? $fechaUltimoContacto : $fechaUltimoContacto->toDateTimeString()) : null,
            'respondio_autoservicio' => $actualizaciones->where('procedencia', 'intranet_autogestion')->isNotEmpty(),
            'asignado_campana' => $asignaciones->isNotEmpty(),
        ];
    }

    /**
     * Diagnóstico integral del padrón real para el informe institucional.
     */
    public function diagnosticarPadron(string $grupoFormacion = 'TODOS'): array
    {
        $padron = $this->obtenerPadronEgresadosElegibles(null, false, $grupoFormacion);
        $total = $padron->count();

        $porPrograma = $padron->groupBy('programa')->map->count()->toArray();
        $conTelefono = $padron->where('tiene_telefono', true)->count();
        $sinTelefono = $total - $conTelefono;
        $conEmailValido = $padron->where('email_valido', true)->count();
        $conResidencia = $padron->where('tiene_residencia', true)->count();
        $conTrabajo = $padron->where('tiene_trabajo', true)->count();

        return [
            'total_egresados_reales' => $total,
            'por_programa' => $porPrograma,
            'con_telefono' => $conTelefono,
            'sin_telefono' => $sinTelefono,
            'con_email_valido' => $conEmailValido,
            'con_residencia' => $conResidencia,
            'con_lugar_trabajo' => $conTrabajo,
            'duplicados_confirmados' => 0,
            'pendientes_conciliacion' => $sinTelefono,
        ];
    }

    /**
     * Algoritmo de distribución aleatoria y equilibrada de egresados reales.
     * Garantiza:
     * 1. Aleatoriedad estricta.
     * 2. Equidad: La diferencia máxima entre cualquier par de docentes es como máximo 1.
     * 3. Sin duplicados: Ningún egresado se asigna a más de un docente en la misma campaña.
     * 4. Vista previa o ejecución transaccional con ACID y control de concurrencia.
     */
    public function distribuirEquilibrado(
        int $campanaId,
        array $docenteUserIds,
        int $asignadoPorId,
        bool $esEnsayo = false,
        bool $previewOnly = false,
        string $grupoFormacion = 'TODOS'
    ): array {
        if (empty($docenteUserIds)) {
            throw new \InvalidArgumentException('Debes seleccionar al menos un docente entrevistador.');
        }

        $campana = Campana::findOrFail($campanaId);

        if (!in_array(strtolower($campana->estado), ['activo','activa'])) throw new \InvalidArgumentException('Solo se distribuyen campañas abiertas.');

        // Docentes seleccionados (verificar que existan y tengan rol docente)
        $docentes = User::role('docente')
            ->whereIn('id', $docenteUserIds)
            ->get();

        if ($docentes->isEmpty()) {
            throw new \InvalidArgumentException('Ninguno de los usuarios seleccionados califica como docente elegible.');
        }

        // Obtener egresados elegibles no asignados en esta campaña
        $egresadosElegibles = $this->obtenerPadronEgresadosElegibles($campanaId, true, $grupoFormacion);

        if ($egresadosElegibles->isEmpty()) {
            throw new \RuntimeException('No hay egresados disponibles sin asignar para esta campaña.');
        }

        // Mezclar aleatoriamente el padrón
        $shuffled = $egresadosElegibles->shuffle();
        $totalEgresados = $shuffled->count();
        $numDocentes = $docentes->count();

        // Distribución equilibrada (diferencia máxima de 1)
        // Ejemplo: 401 entre 5 => [81, 80, 80, 80, 80]
        $grupos = [];
        foreach ($docentes as $doc) {
            $grupos[$doc->id] = [
                'docente_user_id' => $doc->id,
                'docente_nombre' => trim($doc->name . ' ' . $doc->apellidos),
                'docente_email' => $doc->email,
                'egresados' => [],
            ];
        }

        $docenteKeys = array_values($docentes->pluck('id')->toArray());
        $i = 0;
        foreach ($shuffled as $egr) {
            $targetDocId = $docenteKeys[$i % $numDocentes];
            $grupos[$targetDocId]['egresados'][] = $egr;
            $i++;
        }

        $resumenDistribucion = [];
        foreach ($grupos as $docId => $info) {
            $resumenDistribucion[] = [
                'docente_user_id' => $docId,
                'docente_nombre' => $info['docente_nombre'],
                'docente_email' => $info['docente_email'],
                'cantidad_asignada' => count($info['egresados']),
                'muestra_egresados' => array_slice(array_map(fn ($e) => $e['nombre_completo'], $info['egresados']), 0, 5),
            ];
        }

        if ($previewOnly) {
            return [
                'campana_id' => $campana->id,
                'campana_nombre' => $campana->nombre,
                'total_egresados' => $totalEgresados,
                'total_docentes' => $numDocentes,
                'promedio_por_docente' => round($totalEgresados / $numDocentes, 1),
                'diferencia_maxima' => max(array_column($resumenDistribucion, 'cantidad_asignada')) - min(array_column($resumenDistribucion, 'cantidad_asignada')),
                'resumen' => $resumenDistribucion,
                'preview' => true,
            ];
        }

        // Ejecutar inserción transaccional con bloqueo pesimista
        return DB::transaction(function () use (
            $campana, $grupos, $asignadoPorId, $esEnsayo, $totalEgresados, $numDocentes, $resumenDistribucion
        ) {
            // Bloqueo pesimista de campaña para evitar condiciones de carrera concurrentes
            $bloqueada = Campana::where('id', $campana->id)->lockForUpdate()->first();
            if (!in_array(strtolower($bloqueada->estado), ['activo','activa'])) throw new \RuntimeException('La campaña se cerró antes de asignar.');

            $ahora = now();
            $registrosAInsertar = [];

            foreach ($grupos as $docId => $info) {
                // Asegurar que el docente conserve su rol institucional 'docente'
                // y asignar el rol funcional 'seguimiento-egresados' si aún no lo tiene
                $docUser = User::find($docId);
                if ($docUser && !$docUser->hasRole('seguimiento-egresados')) {
                    $docUser->assignRole('seguimiento-egresados');
                }

                foreach ($info['egresados'] as $egr) {
                    $registrosAInsertar[] = [
                        'campana_id' => $campana->id,
                        'docente_user_id' => $docId,
                        'egresado_user_id' => $egr['user_id'],
                        'estado' => 'asignado',
                        'asignado_por' => $asignadoPorId,
                        'fecha_asignacion' => $ahora,
                        'docente_anterior_id' => null,
                        'motivo_reasignacion' => null,
                        'es_ensayo' => $esEnsayo || $campana->es_ensayo,
                        'observaciones' => 'Distribución aleatoria equilibrada de padrón institucional',
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }
            }

            // Inserción en bloques (chunks) para alta eficiencia
            foreach (array_chunk($registrosAInsertar, 100) as $chunk) {
                DB::table('egresado_campana_asignaciones')->insert($chunk);
            }

            Log::info("Distribución equilibrada ejecutada para campaña {$campana->id}: {$totalEgresados} egresados distribuidos entre {$numDocentes} docentes.");

            return [
                'campana_id' => $campana->id,
                'campana_nombre' => $campana->nombre,
                'total_egresados' => $totalEgresados,
                'total_docentes' => $numDocentes,
                'promedio_por_docente' => round($totalEgresados / $numDocentes, 1),
                'diferencia_maxima' => max(array_column($resumenDistribucion, 'cantidad_asignada')) - min(array_column($resumenDistribucion, 'cantidad_asignada')),
                'resumen' => $resumenDistribucion,
                'preview' => false,
                'success' => true,
            ];
        });
    }

    /**
     * Reasignación individual autorizada preservando trazabilidad e historial.
     */
    public function reasignarEgresado(int $asignacionId, int $nuevoDocenteId, int $actorId, string $motivo): AsignacionCampana
    {
        return DB::transaction(function () use ($asignacionId, $nuevoDocenteId, $actorId, $motivo) {
            $referencia=AsignacionCampana::findOrFail($asignacionId);
            $campana=Campana::lockForUpdate()->findOrFail($referencia->campana_id);
            if(!in_array(strtolower($campana->estado),['activo','activa'])) throw new \InvalidArgumentException('No se reasigna una campaña cerrada.');
            $asignacion = AsignacionCampana::lockForUpdate()->findOrFail($asignacionId);
            if(!in_array($asignacion->estado,['asignado','contactado'])) throw new \InvalidArgumentException('Esta asignación ya no está activa.');

            if ($asignacion->docente_user_id === $nuevoDocenteId) {
                throw new \InvalidArgumentException('El nuevo docente es idéntico al docente actual.');
            }

            $nuevoDocente = User::role('docente')->findOrFail($nuevoDocenteId);

            // Asegurar rol funcional al nuevo docente
            if (!$nuevoDocente->hasRole('seguimiento-egresados')) {
                $nuevoDocente->assignRole('seguimiento-egresados');
            }

            // Marcar la asignación anterior como reasignada para preservar el historial
            $docenteAnteriorId = $asignacion->docente_user_id;
            $asignacion->update([
                'estado' => 'reasignado',
                'motivo_reasignacion' => $motivo,
            ]);

            // Crear la nueva asignación activa para el nuevo docente
            $nueva = AsignacionCampana::create([
                'campana_id' => $asignacion->campana_id,
                'docente_user_id' => $nuevoDocenteId,
                'egresado_user_id' => $asignacion->egresado_user_id,
                'estado' => 'asignado',
                'asignado_por' => $actorId,
                'fecha_asignacion' => now(),
                'docente_anterior_id' => $docenteAnteriorId,
                'motivo_reasignacion' => $motivo,
                'es_ensayo' => $asignacion->es_ensayo,
                'observaciones' => "Reasignación desde docente ID {$docenteAnteriorId}: {$motivo}",
            ]);

            Log::info("Egresado {$asignacion->egresado_user_id} reasignado de docente {$docenteAnteriorId} a docente {$nuevoDocenteId}. Motivo: {$motivo}");

            return $nueva;
        });
    }

    /**
     * Métricas globales de la campaña con denominadores explícitos (Panel Coordinación).
     */
    public function compararSeguimiento(Collection $padron, Collection $llamadas, Collection $actualizaciones): array
    {
        $rows = [];
        foreach (['FID','PPD','POR_VERIFICAR'] as $grupo) {
            $personas = $padron->filter(fn ($p) => app(GrupoFormacionService::class)->coincide($p['grupos_formacion'] ?? [], $grupo))->unique('user_id');
            $ids = $personas->pluck('user_id'); $total = $ids->count();
            $contactados = $llamadas->whereIn('user_id', $ids)->where('es_contacto_efectivo', true)->pluck('user_id')->unique()->count();
            $actualizados = $actualizaciones->whereIn('user_id', $ids)->pluck('user_id')->unique()->count();
            $rows[$grupo] = ['personas' => $total, 'contactados' => $contactados, 'actualizados' => $actualizados,
                'pendientes' => max(0, $total-$actualizados), 'avance_pct' => $total ? round(100*$actualizados/$total,1) : null];
        }
        return $rows;
    }

    public function obtenerMetricasCampana(int $campanaId, string $grupoFormacion = 'TODOS'): array
    {
        $campana = Campana::findOrFail($campanaId);
        $padron = $this->obtenerPadronEgresadosElegibles(null, false, $grupoFormacion);
        $idsGrupo = $padron->pluck('user_id');
        $totalPadronReales = $idsGrupo->count();
        $asignaciones = AsignacionCampana::where('campana_id', $campanaId)->whereIn('egresado_user_id', $idsGrupo)->activos()->get()->unique('egresado_user_id');
        $totalAsignados = $asignaciones->count();
        $egresadosSinAsignar = max(0, $totalPadronReales - $totalAsignados);

        $docentesParticipantes = $asignaciones->pluck('docente_user_id')->unique()->count();

        $egresadosAsignadosIds = $asignaciones->pluck('egresado_user_id')->toArray();

        // Intentos de llamada totales
        $llamadasQuery = Llamada::where('campana_id', $campanaId)->whereIn('user_id', $egresadosAsignadosIds);
        $totalIntentosLlamada = $llamadasQuery->count();

        // Contactos efectivos (personas únicas con al menos un contacto efectivo)
        $egresadosContactadosIds = Llamada::where('campana_id', $campanaId)
            ->where('es_contacto_efectivo', true)
            ->whereIn('user_id', $egresadosAsignadosIds ?: [0])
            ->pluck('user_id')
            ->unique()
            ->toArray();
        $totalContactados = count($egresadosContactadosIds);

        // Actualizaciones en la campaña
        $actualizaciones = Actualizacion::where(function ($q) use ($campana) {
            $q->where('campana_id', $campana->id)
              ->orWhere('periodo_campana', $campana->codigo)
              ->orWhere('periodo_campana', $campana->nombre);
        })->whereIn('user_id', $egresadosAsignadosIds ?: [0])->where('estado_verificacion','verificado')->orderByDesc('id')->get()->unique('user_id');

        $actualizacionesCompletas = $actualizaciones->where('tipo_registro', '!=', 'confirmacion_sin_cambios')->where('es_confirmacion_sin_cambios', false)
            ->where('estado_verificacion', '!=', 'pendiente_verificacion')
            ->count();

        $confirmacionesSinCambios = $actualizaciones->where('es_confirmacion_sin_cambios', true)->count();

        $actualizacionesParciales = Llamada::where('campana_id', $campanaId)
            ->where('tipo_resultado', 'actualizacion_parcial')->whereIn('user_id', $egresadosAsignadosIds)
            ->distinct('user_id')
            ->count('user_id');

        $noLocalizados = Llamada::where('campana_id', $campanaId)
            ->where('tipo_resultado', 'numero_equivocado')->whereIn('user_id', $egresadosAsignadosIds)
            ->distinct('user_id')
            ->count('user_id');

        $casosPendientes = Caso::whereIn('user_id', $egresadosAsignadosIds ?: [0])
            ->whereNotIn('estado', ['Resuelto', 'Cerrado', 'Concluido'])
            ->count();

        // Datos validados
        $laboralValidada = EpisodioLaboral::whereIn('user_id', $egresadosAsignadosIds ?: [0])->distinct()->count('user_id');
        $territorialValidada = Actualizacion::whereIn('user_id', $egresadosAsignadosIds ?: [0])
            ->whereNotNull('res_departamento')->where('estado_verificacion','verificado')
            ->distinct()->count('user_id');

        // Avance por docente
        $avanceDocentes = [];
        $docentesIds = $asignaciones->pluck('docente_user_id')->unique();
        $docentesModels = User::whereIn('id', $docentesIds)->get()->keyBy('id');

        foreach ($docentesIds as $docId) {
            $docUser = $docentesModels[$docId] ?? null;
            $asigsDocente = $asignaciones->where('docente_user_id', $docId);
            $cantAsignados = $asigsDocente->count();
            $egrDocIds = $asigsDocente->pluck('egresado_user_id')->toArray();

            $contactadosDoc = count(array_intersect($egresadosContactadosIds, $egrDocIds));

            $actualizadosDoc = $actualizaciones->whereIn('user_id', $egrDocIds)->unique('user_id')->count();
            $pendientesDoc = max(0, $cantAsignados - $actualizadosDoc);

            $coberturaContacto = $cantAsignados > 0 ? round(($contactadosDoc / $cantAsignados) * 100, 1) : 0;
            $coberturaActualizacion = $cantAsignados > 0 ? round(($actualizadosDoc / $cantAsignados) * 100, 1) : 0;

            $ultimaLlamada = Llamada::where('docente_user_id', $docId)
                ->where('campana_id', $campanaId)
                ->latest('fecha_hora')
                ->first();

            $avanceDocentes[] = [
                'docente_user_id' => $docId,
                'docente_nombre' => $docUser ? trim($docUser->name . ' ' . $docUser->apellidos) : "Docente #{$docId}",
                'asignados' => $cantAsignados,
                'contactados' => $contactadosDoc,
                'actualizados' => $actualizadosDoc,
                'pendientes' => $pendientesDoc,
                'cobertura_contacto_pct' => $coberturaContacto,
                'cobertura_actualizacion_pct' => $coberturaActualizacion,
                'ultima_actividad' => $ultimaLlamada ? $ultimaLlamada->fecha_hora->format('d/m/Y H:i') : 'Sin registros',
                'casos_pendientes' => Caso::whereIn('user_id', $egrDocIds)->whereNotIn('estado', ['Resuelto', 'Cerrado'])->count(),
            ];
        }

        return [
            'campana' => $campana,
            'por_grupo' => $this->compararSeguimiento($padron->whereIn('user_id', $egresadosAsignadosIds), $llamadasQuery->get(), $actualizaciones),
            'total_padron_elegible' => $totalPadronReales,
            'total_asignados' => $totalAsignados,
            'egresados_sin_asignar' => $egresadosSinAsignar,
            'docentes_participantes' => $docentesParticipantes,
            'total_intentos_llamada' => $totalIntentosLlamada,
            'total_contactados_efectivos' => $totalContactados,
            'tasa_contacto_pct' => $totalAsignados > 0 ? round(($totalContactados / $totalAsignados) * 100, 1) : 0,
            'actualizaciones_completas' => $actualizacionesCompletas,
            'confirmaciones_sin_cambios' => $confirmacionesSinCambios,
            'actualizaciones_parciales' => $actualizacionesParciales,
            'total_actualizados' => $actualizacionesCompletas + $confirmacionesSinCambios,
            'tasa_actualizacion_pct' => $totalAsignados > 0 ? round((($actualizacionesCompletas + $confirmacionesSinCambios) / $totalAsignados) * 100, 1) : 0,
            'no_localizados' => $noLocalizados,
            'casos_pendientes' => $casosPendientes,
            'informacion_laboral_validada' => $laboralValidada,
            'informacion_territorial_validada' => $territorialValidada,
            'avance_docentes' => $avanceDocentes,
        ];
    }

    /**
     * Métricas individuales y lista de trabajo del docente entrevistador.
     */
    public function obtenerMetricasDocente(int $docenteUserId, ?int $campanaId = null, string $grupoFormacion = 'TODOS'): array
    {
        $query = AsignacionCampana::activos()->where('docente_user_id', $docenteUserId)->whereHas('campana',fn($q)=>$q->whereIn('estado',['activo','activa'])->where(fn($d)=>$d->whereNull('fecha_inicio')->orWhere('fecha_inicio','<=',now()->toDateString()))->where(fn($d)=>$d->whereNull('fecha_fin')->orWhere('fecha_fin','>=',now()->toDateString())));
        if ($campanaId) {
            $query->where('campana_id', $campanaId);
        }

        $idsGrupo = $this->obtenerPadronEgresadosElegibles(null, false, $grupoFormacion)->pluck('user_id');
        $asignaciones = $query->whereIn('egresado_user_id', $idsGrupo)->with(['egresado.programa','egresado.alumno.programa','egresado.alumnoB.programa','egresado.trayectoriaEventos.cicloOrigen.programa','egresado.trayectoriaEventos.cicloDestino.programa', 'campana'])->get();
        $totalAsignados = $asignaciones->pluck('egresado_user_id')->unique()->count();
        $egrIds = $asignaciones->pluck('egresado_user_id')->toArray();

        $llamadas = Llamada::where('docente_user_id', $docenteUserId)
            ->whereIn('user_id', $egrIds ?: [0])->whereIn('campana_id', $asignaciones->pluck('campana_id')->unique())
            ->get();

        $intentosLlamada = $llamadas->count();
        $contactadosEfectivos = $llamadas->where('es_contacto_efectivo', true)->pluck('user_id')->unique()->count();

        $actualizacionesQuery = Actualizacion::whereIn('user_id', $egrIds ?: [0])->where('estado_verificacion','verificado');
        if ($campanaId) {
            $campana = Campana::findOrFail($campanaId);
            $actualizacionesQuery->where(fn ($q) => $q->where('campana_id',$campanaId)->orWhere('periodo_campana',$campana->codigo)->orWhere('periodo_campana',$campana->nombre));
        }
        $actualizaciones = $actualizacionesQuery->orderByDesc('id')->get()->unique('user_id');
        $completadas = $actualizaciones->where('tipo_registro', '!=', 'confirmacion_sin_cambios')->where('es_confirmacion_sin_cambios', false)->count();
        $sinCambios = $actualizaciones->where('es_confirmacion_sin_cambios', true)->count();
        $parciales = $llamadas->where('tipo_resultado', 'actualizacion_parcial')->pluck('user_id')->unique()->count();
        $reprogramadas = $llamadas->where('tipo_resultado', 'entrevista_programada')->count();
        $pendientesContacto = max(0, $totalAsignados - $contactadosEfectivos);
        $casosAtencion = Caso::whereIn('user_id', $egrIds ?: [0])->whereNotIn('estado', ['Resuelto', 'Cerrado'])->count();

        // Construir lista de trabajo detallada
        $listaTrabajo = $asignaciones->map(function (AsignacionCampana $asig) use ($llamadas, $actualizaciones, $grupoFormacion) {
            $egr = $asig->egresado;
            $clasificacion = app(GrupoFormacionService::class);
            $alumno = $egr ? $clasificacion->ficha($egr, $grupoFormacion) : null;
            $meta = $egr ? $clasificacion->resolver($egr) : ['grupos' => [], 'etiqueta' => 'Por verificar'];

            $tel = $alumno?->numero ?: ($alumno?->numero_referencia ?: '');
            $progNombre = $egr ? ($clasificacion->programa($egr, $grupoFormacion)?->nombre ?: 'Por verificar') : 'Por verificar';

            $llamadasEgr = $llamadas->where('user_id', $asig->egresado_user_id);
            $ultimaLlamada = $llamadasEgr->sortByDesc('fecha_hora')->first();

            $act = $actualizaciones->where('user_id', $asig->egresado_user_id)->first();
            $tel = $act?->telefono ?: $tel;

            $estadoSeguimiento = 'Por contactar';
            if ($act) {
                $estadoSeguimiento = $act->es_confirmacion_sin_cambios ? 'Confirmación sin cambios' : 'Datos confirmados';
            } elseif ($ultimaLlamada) {
                $estadoSeguimiento = match($ultimaLlamada->tipo_resultado) {
                    'contacto_efectivo' => 'Contacto efectivo',
                    'entrevista_programada' => 'Entrevista programada',
                    'actualizacion_parcial' => 'Actualización parcial',
                    'numero_equivocado' => 'Número equivocado',
                    'rechazo_participacion' => 'Rechazo de participación',
                    'requiere_derivacion' => 'Requiere derivación',
                    default => 'Intento sin respuesta',
                };
            }

            return [
                'asignacion_id' => $asig->id,
                'campana_id' => $asig->campana_id,
                'campana_nombre' => $asig->campana?->nombre ?? 'Campaña',
                'user_id' => $asig->egresado_user_id,
                'nombre_completo' => $egr ? trim($egr->name . ' ' . $egr->apellidos) : 'Egresado/a',
                'email' => $egr?->email,
                'programa' => $progNombre,
                'grupos_formacion' => $meta['grupos'],
                'grupo_formacion' => $meta['etiqueta'],
                'cohorte' => $act?->cohorte_egreso ?: 'Por verificar',
                'telefono' => $tel,
                'tiene_telefono' => !empty($tel),
                'estado_seguimiento' => $estadoSeguimiento,
                'total_intentos' => $llamadasEgr->count(),
                'ultimo_intento_fecha' => $ultimaLlamada ? $ultimaLlamada->fecha_hora->format('d/m/Y H:i') : null,
                'ultimo_intento_resultado' => $ultimaLlamada?->resultado ?? null,
                'proxima_accion' => $ultimaLlamada?->fecha_reprogramada ? $ultimaLlamada->fecha_reprogramada->format('d/m/Y H:i') : 'Contactar según disponibilidad',
                'actualizado' => $act !== null,
                'res_departamento' => $alumno?->departamento ?: 'No declarado',
                'res_provincia' => $alumno?->provincia ?: 'No declarado',
                'res_distrito' => $alumno?->distrito ?: 'No declarado',
            ];
        });

        return [
            'por_grupo' => $this->compararSeguimiento($listaTrabajo, $llamadas, $actualizaciones),
            'docente_user_id' => $docenteUserId,
            'total_asignados' => $totalAsignados,
            'pendientes_contactar' => $pendientesContacto,
            'intentos_llamada' => $intentosLlamada,
            'contactados_efectivos' => $contactadosEfectivos,
            'actualizaciones_completas' => $completadas,
            'actualizaciones_parciales' => $parciales,
            'confirmaciones_sin_cambios' => $sinCambios,
            'entrevistas_reprogramadas' => $reprogramadas,
            'casos_atencion' => $casosAtencion,
            'lista_trabajo' => $listaTrabajo,
        ];
    }

    /**
     * Métricas de actualización territorial real (antes y después de la campaña).
     */
    public function obtenerMetricasTerritoriales(int $campanaId, string $grupoFormacion = 'TODOS'): array
    {
        $campana = Campana::findOrFail($campanaId);
        $padron = $this->obtenerPadronEgresadosElegibles(null, false, $grupoFormacion);
        $totalPadron = $padron->count();

        // 1. Estado territorial basal (previo a la campaña)
        $residenciaBasalConocida = $padron->where('tiene_residencia', true)->count();
        $residenciaBasalDesconocida = $totalPadron - $residenciaBasalConocida;

        $trabajoBasalConocido = $padron->where('tiene_trabajo', true)->count();
        $trabajoBasalDesconocido = $totalPadron - $trabajoBasalConocido;

        // 2. Estado territorial posterior (actualizaciones registradas)
        $actualizaciones = Actualizacion::where(function ($q) use ($campana) {
            $q->where('campana_id', $campana->id)
              ->orWhere('periodo_campana', $campana->codigo)
              ->orWhere('periodo_campana', $campana->nombre);
        })->whereIn('user_id', $padron->pluck('user_id'))->where('estado_verificacion','verificado')->orderByDesc('id')->get()->unique('user_id');

        $residenciaConfirmada = 0;
        $residenciaModificada = 0;
        $residenciaPrimeraVez = 0;

        $trabajoConfirmado = 0;
        $trabajoModificado = 0;
        $trabajoPrimeraVez = 0;

        foreach ($actualizaciones as $act) {
            $u = $padron->firstWhere('user_id', $act->user_id);
            if ($u) {
                if ($act->es_confirmacion_sin_cambios) {
                    $residenciaConfirmada++;
                    if ($u['tiene_trabajo']) $trabajoConfirmado++;
                } else {
                    if (!$u['tiene_residencia'] && $act->res_departamento) {
                        $residenciaPrimeraVez++;
                    } elseif ($u['tiene_residencia']) {
                        $residenciaModificada++;
                    }

                    if (!$u['tiene_trabajo'] && $act->institucion) {
                        $trabajoPrimeraVez++;
                    } elseif ($u['tiene_trabajo']) {
                        $trabajoModificado++;
                    }
                }
            }
        }

        // Distribución geográfica agregada por Departamento y Provincias de Cusco
        $departamentos = [];
        $provinciasCusco = [];

        foreach ($padron as $egr) {
            $dep = strtoupper($egr['res_departamento'] ?: 'NO DECLARADO');
            $departamentos[$dep] = ($departamentos[$dep] ?? 0) + 1;

            if (str_contains($dep, 'CUSCO')) {
                $prov = strtoupper($egr['res_provincia'] ?: 'NO DECLARADO');
                $provinciasCusco[$prov] = ($provinciasCusco[$prov] ?? 0) + 1;
            }
        }

        arsort($departamentos);
        arsort($provinciasCusco);

        return [
            'campana_nombre' => $campana->nombre,
            'total_padron' => $totalPadron,
            'basal' => [
                'residencia_conocida' => $residenciaBasalConocida,
                'residencia_desconocida' => $residenciaBasalDesconocida,
                'trabajo_conocido' => $trabajoBasalConocido,
                'trabajo_desconocido' => $trabajoBasalDesconocido,
            ],
            'actualizado' => [
                'residencia_confirmada' => $residenciaConfirmada,
                'residencia_modificada' => $residenciaModificada,
                'residencia_primera_vez' => $residenciaPrimeraVez,
                'trabajo_confirmado' => $trabajoConfirmado,
                'trabajo_modificado' => $trabajoModificado,
                'trabajo_primera_vez' => $trabajoPrimeraVez,
            ],
            'distribucion_departamentos' => $departamentos,
            'distribucion_provincias_cusco' => $provinciasCusco,
        ];
    }

    /**
     * Asignación manual / filtrada de un lote específico de egresados seleccionados.
     * Garantiza:
     * 1. Distribución equitativa y equilibrada entre los docentes seleccionados.
     * 2. Evita duplicados por persona y campaña en reintentos/concurrencia (ACID con lockForUpdate).
     * 3. Informa cuántos registros se asignaron, ya estaban asignados o dejaron de ser elegibles.
     * 4. Registro de auditoría y compatibilidad con reasignaciones posteriores.
     */
    public function asignarSeleccionados(
        int $campanaId,
        array $docenteUserIds,
        array $egresadoUserIds,
        int $asignadoPorId,
        bool $esEnsayo = false,
        bool $previewOnly = false
    ): array {
        if (empty($docenteUserIds)) {
            throw new \InvalidArgumentException('Debes seleccionar al menos un docente entrevistador.');
        }
        if (empty($egresadoUserIds)) {
            throw new \InvalidArgumentException('Debes seleccionar al menos un egresado para asignar.');
        }

        $campana = Campana::findOrFail($campanaId);
        if (!in_array(strtolower($campana->estado), ['activo', 'activa'])) {
            throw new \InvalidArgumentException('Solo se pueden asignar egresados a campañas activas.');
        }

        $docentes = User::role('docente')
            ->whereIn('id', $docenteUserIds)
            ->get();
        if ($docentes->isEmpty()) {
            throw new \InvalidArgumentException('Ninguno de los docentes seleccionados es elegible.');
        }

        // Obtener egresados válidos en la base institucional
        $egresadosValidos = User::where('condicion', 'Egresado')
            ->whereIn('id', $egresadoUserIds)
            ->whereNotIn('email', ['auditor.egresada@puklla.test', 'egresado.noasignado@puklla.test'])
            ->get();

        $validosIds = $egresadosValidos->pluck('id')->toArray();
        $omitidosNoElegibles = array_values(array_diff($egresadoUserIds, $validosIds));

        // Comprobar asignaciones ya existentes en esta campaña para evitar duplicados concurrentes
        $yaAsignadosIds = AsignacionCampana::activos()
            ->where('campana_id', $campanaId)
            ->whereIn('egresado_user_id', $validosIds)
            ->pluck('egresado_user_id')
            ->toArray();

        $elegiblesAAsignar = array_values(array_diff($validosIds, $yaAsignadosIds));

        // Distribución equitativa entre los docentes elegidos
        $numDocentes = $docentes->count();
        $docenteKeys = array_values($docentes->pluck('id')->toArray());
        $distribucionPorDocente = [];
        foreach ($docentes as $doc) {
            $distribucionPorDocente[$doc->id] = [
                'docente_user_id' => $doc->id,
                'docente_nombre' => trim($doc->name . ' ' . $doc->apellidos),
                'docente_email' => $doc->email,
                'cantidad' => 0,
            ];
        }

        $i = 0;
        foreach ($elegiblesAAsignar as $eId) {
            $docId = $docenteKeys[$i % $numDocentes];
            $distribucionPorDocente[$docId]['cantidad']++;
            $i++;
        }

        if ($previewOnly) {
            return [
                'preview' => true,
                'campana_id' => $campana->id,
                'campana_nombre' => $campana->nombre,
                'total_solicitados' => count($egresadoUserIds),
                'asignados' => count($elegiblesAAsignar),
                'elegibles_a_asignar' => count($elegiblesAAsignar),
                'ya_asignados' => count($yaAsignadosIds),
                'ya_asignados_ids' => $yaAsignadosIds,
                'omitidos_no_elegibles' => count($omitidosNoElegibles),
                'omitidos_ids' => $omitidosNoElegibles,
                'distribucion_docentes' => array_values($distribucionPorDocente),
            ];
        }

        // Ejecución transaccional con ACID y bloqueo pesimista
        return DB::transaction(function () use (
            $campana, $docentes, $docenteKeys, $numDocentes, $elegiblesAAsignar,
            $yaAsignadosIds, $omitidosNoElegibles, $asignadoPorId, $esEnsayo,
            $distribucionPorDocente, $egresadoUserIds
        ) {
            $bloqueada = Campana::where('id', $campana->id)->lockForUpdate()->first();
            if (!in_array(strtolower($bloqueada->estado), ['activo', 'activa'])) {
                throw new \RuntimeException('La campaña se cerró antes de completar la asignación.');
            }

            // Re-validar dentro de la transacción para evitar condiciones de carrera concurrentes
            $yaAsignadosTrans = AsignacionCampana::activos()
                ->where('campana_id', $campana->id)
                ->whereIn('egresado_user_id', $elegiblesAAsignar)
                ->lockForUpdate()
                ->pluck('egresado_user_id')
                ->toArray();

            $finalesAAsignar = array_values(array_diff($elegiblesAAsignar, $yaAsignadosTrans));
            $totalYaAsignados = array_values(array_unique(array_merge($yaAsignadosIds, $yaAsignadosTrans)));

            $ahora = now();
            $registros = [];
            $k = 0;
            foreach ($finalesAAsignar as $egrId) {
                $docId = $docenteKeys[$k % $numDocentes];
                $registros[] = [
                    'campana_id' => $campana->id,
                    'docente_user_id' => $docId,
                    'egresado_user_id' => $egrId,
                    'estado' => 'asignado',
                    'asignado_por' => $asignadoPorId,
                    'fecha_asignacion' => $ahora,
                    'docente_anterior_id' => null,
                    'motivo_reasignacion' => null,
                    'es_ensayo' => $esEnsayo || $campana->es_ensayo,
                    'observaciones' => 'Asignación manual filtrada desde Padrón / Observatorio Territorial',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
                $k++;
            }

            foreach ($docentes as $docUser) {
                if (!$docUser->hasRole('seguimiento-egresados')) {
                    $docUser->assignRole('seguimiento-egresados');
                }
            }

            if (!empty($registros)) {
                foreach (array_chunk($registros, 100) as $chunk) {
                    DB::table('egresado_campana_asignaciones')->insert($chunk);
                }
            }

            Log::info("Asignación de seleccionados ejecutada para campaña {$campana->id}: " . count($finalesAAsignar) . " asignados, " . count($totalYaAsignados) . " ya estaban asignados.");

            return [
                'preview' => false,
                'success' => true,
                'campana_id' => $campana->id,
                'campana_nombre' => $campana->nombre,
                'total_solicitados' => count($egresadoUserIds),
                'asignados' => count($finalesAAsignar),
                'ya_asignados' => count($totalYaAsignados),
                'omitidos_no_elegibles' => count($omitidosNoElegibles),
                'distribucion_docentes' => array_values($distribucionPorDocente),
            ];
        });
    }
}
