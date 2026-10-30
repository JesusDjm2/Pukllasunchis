<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\SeguimientoEgresado\AccesoContacto;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeguimientoEgresadoController extends Controller
{
    /**
     * Vista principal del módulo de Seguimiento de Egresados (Simulador V8 conectado a la BD institucional).
     */
    public function index(Request $request)
    {
        // 1. Obtener egresados reales de la base de datos institucional
        $egresadosReales = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'seguimientoTitulacion.estadoTitulacion'])
            ->orderBy('apellidos')
            ->orderBy('name')
            ->limit(300)
            ->get()
            ->map(function ($u) {
                $alumno = $u->alumno;
                $tit = $u->seguimientoTitulacion;

                // Inferir o limpiar año de cohorte a partir de su correo o periodo de estudios
                $cohorte = 'Por verificar';
                if ($tit && $tit->periodo_estudios) {
                    $cohorte = $tit->periodo_estudios;
                } elseif (preg_match('/(20\d{2})/', $u->email, $m)) {
                    $cohorte = $m[1];
                }

                $progNombre = 'Programa por verificar';
                if ($alumno && $alumno->programa) {
                    $progNombre = str_replace('Programa ', '', $alumno->programa->nombre);
                }

                return [
                    'id' => 'REAL-' . $u->id,
                    'user_id' => $u->id,
                    'nombre' => trim($u->name . ' ' . $u->apellidos),
                    'programa' => $progNombre,
                    'egreso' => $cohorte,
                    'periodo_referencia' => $cohorte,
                    'egreso_confirmado' => true,
                    'correo' => $u->email,
                    'telefono' => $alumno?->numero ?: '',
                    'telefono_alt' => $alumno?->numero_referencia ?: '',
                    'direccion_contacto' => $alumno?->direccion ?: '',
                    'res_departamento' => $alumno?->departamento ?: 'CUSCO',
                    'res_provincia' => $alumno?->provincia ?: 'CUSCO',
                    'res_distrito' => $alumno?->distrito ?: 'CUSCO',
                    'estado_academico' => $tit?->estadoTitulacion?->nombre ?: 'Egresado/a',
                    'origen' => 'Base institucional EESPP Pukllasunchis'
                ];
            });

        // 2. Obtener actualizaciones registradas en base de datos
        $actualizacionesDb = Actualizacion::latest()->get()->keyBy(function ($item) {
            return $item->identificador_temporal ?: ('REAL-' . $item->user_id);
        });

        // 3. Obtener casos de acción institucional
        $casosDb = Caso::latest()->get();

        // 4. Obtener llamadas registradas
        $llamadasDb = Llamada::latest()->get();

        // 5. Obtener bitácora de auditoría de contactos
        $accesosContactoDb = AccesoContacto::latest()->limit(50)->get();

        // 6. Obtener grupos de entrevistadores para seguimiento
        $gruposDb = GrupoEntrevistador::latest()->get();

        // 7. Entrevistadores disponibles (docentes, tutores, alumnos o admins)
        $entrevistadoresDisponibles = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['docente', 'tutor', 'admin', 'alumno', 'encuestador-seguimiento']))
            ->orderBy('apellidos')
            ->limit(150)
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'nombre' => trim($u->name . ' ' . $u->apellidos), 'email' => $u->email]);

        // 8. Catálogo de departamentos, provincias y distritos principales
        $departamentos = Departamento::orderBy('nombre')->pluck('nombre');
        $provinciasCusco = Provincia::whereHas('departamento', function ($q) {
            $q->where('nombre', 'like', '%Cusco%');
        })->orderBy('nombre')->pluck('nombre');
        $distritosCusco = Distrito::whereHas('provincia', function ($q) {
            $q->where('nombre', 'like', '%Cusco%');
        })->orderBy('nombre')->pluck('nombre');

        // 9. Episodios laborales persistidos para el historial longitudinal
        $episodiosDb = EpisodioLaboral::latest()->get();

        return view('seguimiento_egresados.index', compact(
            'egresadosReales',
            'actualizacionesDb',
            'casosDb',
            'llamadasDb',
            'accesosContactoDb',
            'gruposDb',
            'entrevistadoresDisponibles',
            'departamentos',
            'provinciasCusco',
            'distritosCusco',
            'episodiosDb'
        ));
    }

    /**
     * Guardar actualización de ficha con resolución estricta de identidad (R01),
     * confirmación rápida «sin cambios» (R02) y persistencia longitudinal de episodios (R03).
     */
    public function guardarActualizacion(Request $request)
    {
        // -------------------------------------------------------------
        // 1. RESOLUCIÓN DE IDENTIDAD Y CONTROL DE PERMISOS (R01 / R04)
        // -------------------------------------------------------------
        $rawId = $request->input('user_id') ?: $request->input('id');
        $dni = $request->input('dni');
        $targetUserId = null;
        $idTemporal = null;

        if (is_numeric($rawId)) {
            $targetUserId = (int) $rawId;
        } elseif (is_string($rawId) && str_starts_with($rawId, 'REAL-')) {
            $targetUserId = (int) str_replace('REAL-', '', $rawId);
        } elseif ($dni && ($userByDni = User::where('dni', $dni)->first())) {
            $targetUserId = (int) $userByDni->id;
        } else {
            $idTemporal = is_string($rawId) ? $rawId : ($dni ? ('DNI-' . $dni) : null);
        }

        // Control de permisos basado en sesión autenticada
        if (auth()->check()) {
            $authUser = auth()->user();

            // Si el usuario autenticado es egresado/alumno, solo puede modificar su propia ficha
            if ($authUser->condicion === 'Egresado' || $authUser->hasRole('egresado') || $authUser->hasRole('alumno')) {
                if ($targetUserId && (int)$targetUserId !== (int)$authUser->id) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No tienes autorización para modificar la ficha de otro egresado.'
                    ], 403);
                }
                // Forzar identidad a la sesión institucional inequívoca
                $targetUserId = (int)$authUser->id;
                $idTemporal = null;
            }
            // Si el usuario autenticado es encuestador temporal, verificar asignación obligatoria en servidor
            elseif ($authUser->hasRole('encuestador-seguimiento') || $authUser->hasRole('encuestador')) {
                if ($targetUserId) {
                    $asignado = GrupoEntrevistador::esEntrevistadorAsignado($authUser->id, $targetUserId);
                    if (!$asignado) {
                        return response()->json([
                            'success' => false,
                            'error' => 'No tienes asignado este egresado en tus grupos activos de seguimiento.'
                        ], 403);
                    }
                }
            }
        } else {
            // Solicitud pública / sin sesión: validar que si se indica user_id, realmente exista en BD
            if ($targetUserId) {
                $userExists = User::where('id', $targetUserId)->exists();
                if (!$userExists) {
                    return response()->json([
                        'success' => false,
                        'error' => 'El identificador de egresado especificado no existe en la base institucional.'
                    ], 422);
                }
            } elseif (!$idTemporal) {
                return response()->json([
                    'success' => false,
                    'error' => 'Identificador de registro no proporcionado o inválido.'
                ], 422);
            }
        }

        // Recuperar nombre oficial si existe en BD
        $nombre = $request->input('nombre', 'Egresado/a');
        if ($targetUserId && ($u = User::find($targetUserId))) {
            $nombre = trim($u->name . ' ' . $u->apellidos);
        }

        $canal = $request->input('canal', auth()->check() ? 'intranet' : 'autorespuesta');
        $registradoPor = auth()->check() ? auth()->user()->name : ($request->input('registrado_por') ?: 'Autogestión del egresado');
        $campana = $request->input('campana', '2026-II');

        // -------------------------------------------------------------
        // 2. EJECUCIÓN TRANSACCIONAL PROTEGIDA (ACID / Rollback seguro)
        // -------------------------------------------------------------
        try {
            return DB::transaction(function () use (
                $request, $targetUserId, $idTemporal, $nombre, $canal, $registradoPor, $campana
            ) {
                $isSinCambios = $request->boolean('sin_cambios')
                    || $request->input('cambio_empleo') === 'Sin cambios'
                    || $request->boolean('confirmar_vigente');

                // ---------------------------------------------------------
                // 3. CAMINO RÁPIDO: CONFIRMACIÓN SIN CAMBIOS (R02)
                // ---------------------------------------------------------
                if ($isSinCambios) {
                    $actualizacion = Actualizacion::updateOrCreate(
                        ['user_id' => $targetUserId, 'identificador_temporal' => $idTemporal],
                        [
                            'nombre_completo' => $nombre,
                            'canal' => $canal,
                            'registrado_por' => $registradoPor,
                            'fecha_ultima_confirmacion' => now(),
                            'es_confirmacion_sin_cambios' => true,
                            'periodo_campana' => $campana,
                            // Microchequeo opcional de bienestar y formación
                            'satisfaccion' => $request->input('satisfaccion') ?: null,
                            'temas_interes' => $request->input('temas') ?: $request->input('temas_interes'),
                            'necesidades_apoyo' => $request->input('necesidades') ?: $request->input('necesidades_apoyo'),
                            'puedo_compartir' => $request->input('puedo_compartir') ?: null,
                        ]
                    );

                    // Si solicitó acompañamiento prioritario, abrir caso de gestión
                    $necesidad = $request->input('necesidad_prioritaria') ?: $request->input('necesidades');
                    if ($necesidad && (str_starts_with($necesidad, 'Sí:') || $request->boolean('solicitar_apoyo'))) {
                        $tema = str_starts_with($necesidad, 'Sí:') ? trim(str_replace('Sí:', '', $necesidad)) : 'Acompañamiento prioritario';
                        Caso::create([
                            'codigo' => 'CASO-' . time() . '-' . rand(10, 99),
                            'user_id' => $targetUserId,
                            'nombre_egresado' => $nombre,
                            'tema' => $tema,
                            'responsable' => 'Coordinación de Empleabilidad',
                            'fecha_gestion' => now()->addDays(7)->toDateString(),
                            'estado' => 'Por revisar',
                            'proposito' => 'Solicitud de apoyo registrada en confirmación sin cambios.',
                            'origen' => 'Microchequeo de confirmación'
                        ]);
                    }

                    $episodiosActivos = EpisodioLaboral::where('user_id', $targetUserId)
                        ->where('es_actual', true)
                        ->count();

                    return response()->json([
                        'success' => true,
                        'tipo' => 'confirmacion_sin_cambios',
                        'message' => 'Situación actual confirmada exitosamente. No se han duplicado registros laborales.',
                        'actualizacion_id' => $actualizacion->id,
                        'fecha_confirmacion' => now()->toDateString(),
                        'episodios_activos' => $episodiosActivos
                    ]);
                }

                // ---------------------------------------------------------
                // 4. ACTUALIZACIÓN COMPLETA O CAMBIO DE EMPLEO (R03)
                // ---------------------------------------------------------
                $actualizacion = Actualizacion::updateOrCreate(
                    ['user_id' => $targetUserId, 'identificador_temporal' => $idTemporal],
                    [
                        'nombre_completo' => $nombre,
                        'programa' => $request->input('programa'),
                        'cohorte_egreso' => $request->input('egreso') ?: $request->input('cohorte_egreso'),
                        'canal' => $canal,
                        'registrado_por' => $registradoPor,
                        'fecha_ultima_confirmacion' => now(),
                        'es_confirmacion_sin_cambios' => false,
                        'periodo_campana' => $campana,
                        'telefono' => $request->input('telefono'),
                        'whatsapp' => $request->input('whatsapp') ?: $request->input('telefono'),
                        'telefono_alt' => $request->input('telefono_alt'),
                        'correo' => $request->input('correo'),
                        'direccion_contacto' => $request->input('direccion_contacto'),
                        'medio_preferido' => $request->input('medio_preferido'),
                        'res_departamento' => $request->input('res_departamento'),
                        'res_provincia' => $request->input('res_provincia'),
                        'res_distrito' => $request->input('res_distrito'),
                        'titulo_bach' => $request->input('titulo_bach'),
                        'fecha_bach' => $request->input('fecha_bach') ?: null,
                        'titulo_lic' => $request->input('titulo_lic'),
                        'fecha_lic' => $request->input('fecha_lic') ?: null,
                        'apoyo_titulacion' => $request->input('apoyo_titulacion'),
                        'trabaja' => $request->input('trabaja'),
                        'cambio_empleo' => $request->input('cambio_empleo'),
                        'campo' => $request->input('campo'),
                        'tipo_practica' => $request->input('tipo_practica'),
                        'institucion' => $request->input('institucion'),
                        'cargo' => $request->input('cargo'),
                        'nivel_modalidad' => $request->input('nivel') ?: $request->input('nivel_modalidad'),
                        'sector' => $request->input('sector'),
                        'contrato' => $request->input('contrato'),
                        'modalidad_tiempo' => $request->input('modalidad_tiempo') ?: $request->input('dedicacion'),
                        'inicio_empleo' => $request->input('inicio_empleo') ?: null,
                        'fin_empleo' => $request->input('fin_empleo') ?: null,
                        'trab_departamento' => $request->input('trab_departamento'),
                        'trab_provincia' => $request->input('trab_provincia'),
                        'trab_distrito' => $request->input('trab_distrito'),
                        'lugar_trabajo_declarado' => $request->input('lugar_trabajo') ?: $request->input('lugar_trabajo_declarado'),
                        'satisfaccion' => $request->input('satisfaccion'),
                        'temas_interes' => $request->input('temas') ?: $request->input('temas_interes'),
                        'necesidades_apoyo' => $request->input('necesidad_prioritaria') ?: $request->input('necesidades'),
                        'puedo_compartir' => $request->input('puedo_compartir'),
                        'practicas_que_permanecen' => $request->input('practicas_que_permanecen'),
                        'eib' => $request->input('eib'),
                        'quechua' => $request->input('quechua'),
                        'comunidad' => $request->input('comunidad'),
                        'dificultades_practica' => $request->input('dificultades_practica'),
                        'companeros_contacto' => $request->input('companeros_contacto'),
                        'frecuencia_vinculo' => $request->input('frecuencia_vinculo'),
                        'tipo_vinculo' => $request->input('tipo_vinculo'),
                        'encuentros_red' => $request->input('encuentros_red'),
                        'desea_red' => $request->input('red') ?: $request->input('desea_red'),
                        'compartir_perfil' => $request->input('compartir_perfil'),
                    ]
                );

                // ---------------------------------------------------------
                // 5. GESTIÓN DE EPISODIOS LABORALES LONGITUDINALES (R03)
                // ---------------------------------------------------------
                $trabaja = $request->input('trabaja', 'Trabaja');

                if ($trabaja === 'Trabaja' || str_starts_with(strtolower($trabaja), 'trabaja')) {
                    $institucion = trim($request->input('institucion', ''));
                    $cargo = trim($request->input('cargo', ''));
                    $fechaInicio = $request->input('inicio_empleo') ?: now()->toDateString();
                    $fechaFin = $request->input('fin_empleo') ?: null;

                    if ($institucion !== '') {
                        // Idempotencia: Verificar si este mismo episodio ya existe para evitar duplicar por reenvío
                        $episodioExistente = EpisodioLaboral::where('user_id', $targetUserId)
                            ->where('institucion', $institucion)
                            ->where('cargo', $cargo)
                            ->where('fecha_inicio', $fechaInicio)
                            ->first();

                        if ($episodioExistente) {
                            // Actualizar atributos sin crear fila duplicada
                            $episodioExistente->update([
                                'contrato' => $request->input('contrato') ?: $episodioExistente->contrato,
                                'sector' => $request->input('sector') ?: $episodioExistente->sector,
                                'es_actual' => is_null($fechaFin) || $fechaFin >= now()->toDateString(),
                                'fecha_fin' => $fechaFin,
                                'es_empleo_educativo' => ($request->input('campo') === 'En educación' || $request->input('campo') === 'Educativo' || $request->boolean('es_empleo_educativo')),
                                'es_docencia_directa' => ($request->input('tipo_practica') === 'Docencia directa' || $request->boolean('es_docencia_directa')),
                                'trab_departamento' => $request->input('trab_departamento') ?: $episodioExistente->trab_departamento,
                                'trab_provincia' => $request->input('trab_provincia') ?: $episodioExistente->trab_provincia,
                                'trab_distrito' => $request->input('trab_distrito') ?: $episodioExistente->trab_distrito,
                                'dedicacion' => $request->input('modalidad_tiempo') ?: $request->input('dedicacion'),
                            ]);
                        } else {
                            // Si se especificó un episodio anterior a cerrar expresamente, cerrarlo
                            $episodioIdCerrar = $request->input('episodio_id_cerrar');
                            if ($episodioIdCerrar) {
                                $epCerrar = EpisodioLaboral::find($episodioIdCerrar);
                                if ($epCerrar && (int)$epCerrar->user_id === (int)$targetUserId) {
                                    $epCerrar->cerrarEpisodio(
                                        $request->input('fecha_fin_anterior') ?: now()->toDateString(),
                                        $request->input('motivo_cambio') ?: 'Cambio de empleo'
                                    );
                                }
                            }

                            // Crear nuevo episodio laboral conservando los anteriores (soporta empleos simultáneos)
                            EpisodioLaboral::create([
                                'user_id' => $targetUserId,
                                'identificador_temporal' => $idTemporal,
                                'fecha_inicio' => $fechaInicio,
                                'fecha_fin' => $fechaFin,
                                'institucion' => $institucion,
                                'cargo' => $cargo,
                                'contrato' => $request->input('contrato'),
                                'sector' => $request->input('sector'),
                                'es_actual' => is_null($fechaFin) || $fechaFin >= now()->toDateString(),
                                'es_empleo_educativo' => ($request->input('campo') === 'En educación' || $request->input('campo') === 'Educativo' || $request->boolean('es_empleo_educativo')),
                                'es_docencia_directa' => ($request->input('tipo_practica') === 'Docencia directa' || $request->boolean('es_docencia_directa')),
                                'trab_departamento' => $request->input('trab_departamento'),
                                'trab_provincia' => $request->input('trab_provincia'),
                                'trab_distrito' => $request->input('trab_distrito'),
                                'dedicacion' => $request->input('modalidad_tiempo') ?: $request->input('dedicacion'),
                                'canal_registro' => $canal,
                                'registrado_por' => $registradoPor,
                                'fecha_captura' => now(),
                            ]);
                        }
                    }
                } elseif ($trabaja === 'No trabaja') {
                    // Si se declara término de empleo / no trabaja:
                    $episodioIdCerrar = $request->input('episodio_id_cerrar');
                    if ($episodioIdCerrar) {
                        $epCerrar = EpisodioLaboral::find($episodioIdCerrar);
                        if ($epCerrar && (int)$epCerrar->user_id === (int)$targetUserId) {
                            $epCerrar->cerrarEpisodio(
                                $request->input('fin_empleo') ?: null,
                                $request->input('motivo_cambio') ?: 'Término de empleo reportado'
                            );
                        }
                    } elseif ($request->boolean('cerrar_activos')) {
                        // Cerrar empleos activos registrando la fecha declarada (conservando NULL si es desconocida)
                        EpisodioLaboral::where('user_id', $targetUserId)
                            ->where('es_actual', true)
                            ->update([
                                'es_actual' => false,
                                'fecha_fin' => $request->input('fin_empleo') ?: null,
                                'motivo_cambio' => $request->input('motivo_cambio') ?: 'Fin de empleo reportado en actualización'
                            ]);
                    }
                    // REGLA FUNDAMENTAL: Cero empleadores ficticios "Desempleado". No se crea episodio inventado.
                }

                // Si solicitó acompañamiento prioritario, abrir caso institucional
                $necesidad = $request->input('necesidad_prioritaria');
                if ($necesidad && str_starts_with($necesidad, 'Sí:')) {
                    $tema = trim(str_replace('Sí:', '', $necesidad));
                    Caso::create([
                        'codigo' => 'CASO-' . time() . '-' . rand(10, 99),
                        'user_id' => $targetUserId,
                        'nombre_egresado' => $nombre,
                        'tema' => $tema,
                        'responsable' => 'Coordinación de Empleabilidad',
                        'fecha_gestion' => now()->addDays(7)->toDateString(),
                        'estado' => 'Por revisar',
                        'proposito' => 'Solicitud de apoyo voluntario registrada desde actualización de egresado.',
                        'origen' => 'Formulario de actualización'
                    ]);
                }

                $episodiosActivos = EpisodioLaboral::where('user_id', $targetUserId)
                    ->where('es_actual', true)
                    ->count();

                return response()->json([
                    'success' => true,
                    'tipo' => 'actualizacion_completa',
                    'message' => 'Actualización guardada correctamente en la base institucional.',
                    'actualizacion_id' => $actualizacion->id,
                    'episodios_activos' => $episodiosActivos
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Error en guardarActualizacion: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error transaccional al guardar los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint para cerrar un episodio laboral específico sin sobrescribir la fecha de inicio.
     */
    public function cerrarEpisodio(Request $request, $id)
    {
        $episodio = EpisodioLaboral::findOrFail($id);

        // Control de permisos
        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->condicion === 'Egresado' && (int)$episodio->user_id !== (int)$authUser->id) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }
        }

        $episodio->cerrarEpisodio(
            $request->input('fecha_fin') ?: null,
            $request->input('motivo_cambio') ?: 'Cierre registrado manualmente'
        );

        return response()->json([
            'success' => true,
            'message' => 'Episodio laboral cerrado correctamente.',
            'episodio' => $episodio
        ]);
    }

    /**
     * Obtener episodios laborales de un egresado (activos e históricos).
     */
    public function obtenerEpisodios(Request $request, $userId)
    {
        $episodios = EpisodioLaboral::where('user_id', $userId)
            ->orderByDesc('es_actual')
            ->orderByDesc('fecha_inicio')
            ->get();

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'episodios' => $episodios
        ]);
    }

    /**
     * Registrar nueva acción o caso de acompañamiento.
     */
    public function guardarAccion(Request $request)
    {
        $idStr = $request->input('pid');
        $userId = null;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        }

        $caso = Caso::create([
            'codigo' => 'ACT-' . time() . '-' . rand(10, 99),
            'user_id' => $userId,
            'nombre_egresado' => $request->input('nombre', 'Egresado/a'),
            'tema' => $request->input('tema', 'Acompañamiento general'),
            'responsable' => $request->input('responsable', 'Coordinación'),
            'fecha_gestion' => $request->input('fecha') ?: now()->addDays(7)->toDateString(),
            'estado' => 'Por revisar',
            'proposito' => $request->input('nota', 'Respuesta institucional programada.'),
            'origen' => 'Planificación institucional'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Acción de acompañamiento registrada en la base de datos.',
            'caso' => $caso
        ]);
    }

    /**
     * Avanzar estado de un caso (Por revisar -> En atención -> Cerrado).
     */
    public function avanzarAccion(Request $request, $id)
    {
        $caso = Caso::findOrFail($id);

        $nuevoEstado = match ($caso->estado) {
            'Por revisar' => 'En atención',
            'En atención' => 'Cerrado',
            default => 'Por revisar',
        };

        $caso->update(['estado' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'nuevo_estado' => $nuevoEstado
        ]);
    }

    /**
     * Registrar intento o entrevista telefónica.
     */
    public function guardarLlamada(Request $request)
    {
        $idStr = $request->input('id');
        $userId = null;
        $idTemporal = $idStr;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
            $idTemporal = null;
        }

        $llamada = Llamada::create([
            'user_id' => $userId,
            'identificador_temporal' => $idTemporal,
            'resultado' => $request->input('result', 'No contesta'),
            'observaciones' => $request->input('comment', ''),
            'encuestador' => auth()->check() ? auth()->user()->name : 'Entrevistador institucional',
            'fecha_hora' => now()
        ]);

        return response()->json([
            'success' => true,
            'llamada_id' => $llamada->id
        ]);
    }

    /**
     * Auditar acceso a los datos de contacto de un egresado.
     */
    public function auditarContacto(Request $request)
    {
        $idStr = $request->input('id');
        $userId = null;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        }

        $log = AccesoContacto::create([
            'user_id_consultante' => auth()->id(),
            'rol_consultante' => $request->input('role', 'coordinacion'),
            'user_id_consultado' => $userId,
            'identificador_consultado' => $idStr,
            'finalidad' => $request->input('purpose', 'Seguimiento semestral'),
            'fecha_hora' => now()
        ]);

        return response()->json([
            'success' => true,
            'log_id' => $log->id
        ]);
    }

    /**
     * Crear o actualizar un grupo de entrevistadores para seguimiento.
     */
    public function guardarGrupo(Request $request)
    {
        $grupo = GrupoEntrevistador::create([
            'nombre' => $request->input('nombre', 'Equipo de Entrevistadores'),
            'programa' => $request->input('programa'),
            'cohorte' => $request->input('cohorte'),
            'coordinador_id' => auth()->id(),
            'entrevistadores' => $request->input('entrevistadores', []),
            'egresados_ids' => $request->input('egresados_ids', []),
            'estado' => 'activo',
            'observaciones' => $request->input('observaciones', '')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de entrevistadores creado exitosamente.',
            'grupo' => $grupo
        ]);
    }

    /**
     * Eliminar un grupo de entrevistadores.
     */
    public function eliminarGrupo($id)
    {
        $grupo = GrupoEntrevistador::findOrFail($id);
        $grupo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupo eliminado correctamente.'
        ]);
    }

    /**
     * Generar datos y enlace directo para compartir por WhatsApp.
     */
    public function generarEnlaceWhatsapp(Request $request)
    {
        $campana = $request->input('campana', '2026-II');
        $urlCampana = route('seguimiento-egresados.campana-publica');
        
        $mensajeDefault = "¡Hola! 🎓 La EESP Pukllasunchis te invita a participar en la Campaña de Actualización de Egresados ({$campana}). \n\n"
            . "Te tomará solo 3 minutos confirmar tus datos y contarnos sobre tu práctica profesional y necesidades de formación continua:\n"
            . "👉 {$urlCampana}\n\n"
            . "¡Gracias por formar parte de nuestra comunidad educativa!";

        $whatsappUrl = 'https://api.whatsapp.com/send?text=' . urlencode($mensajeDefault);

        return response()->json([
            'success' => true,
            'campana' => $campana,
            'url' => $urlCampana,
            'mensaje' => $mensajeDefault,
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    /**
     * Enlace público de campaña (para WhatsApp / Magic Link).
     */
    public function campanaPublica()
    {
        return view('seguimiento_egresados.campana_publica');
    }
}
