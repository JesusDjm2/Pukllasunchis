<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\SeguimientoEgresado\AccesoContacto;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\SeguimientoEgresado\InvitacionCampana;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeguimientoEgresadoController extends Controller
{
    protected SeguimientoAutorizacionService $autorizacionService;

    public function __construct(?SeguimientoAutorizacionService $autorizacionService = null)
    {
        $this->autorizacionService = $autorizacionService ?: app(SeguimientoAutorizacionService::class);
    }

    /**
     * Verifica si el usuario tiene rol directivo o de coordinación de empleabilidad.
     */
    protected function esPersonalGestion(?User $user = null): bool
    {
        return $this->autorizacionService->esPersonalGestion($user);
    }

    /**
     * Verifica si el usuario pertenece al cuerpo docente o formativo.
     */
    protected function esEquipoDocente(?User $user = null): bool
    {
        return $this->autorizacionService->esDocenteOEntrevistador($user) || $this->autorizacionService->esBienestarSocial($user);
    }

    /**
     * Verifica si el usuario es encuestador de seguimiento.
     */
    protected function esEncuestador(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;
        return $user->hasAnyRole(['encuestador-seguimiento', 'encuestador']);
    }

    /**
     * Verifica si el usuario es egresado o alumno.
     */
    protected function esEgresadoOAlumno(?User $user = null): bool
    {
        return $this->autorizacionService->esEgresadoOAlumno($user);
    }

    /**
     * Comprueba autorización para consultar o mutar datos de un egresado específico.
     */
    protected function puedeAccederAEgresado(int $targetUserId, ?User $user = null): bool
    {
        return $this->autorizacionService->puedeConsultarEgresado($targetUserId, $user);
    }

    /**
     * Vista principal del módulo de Seguimiento de Egresados con alcances protegidos.
     */
    public function index(Request $request)
    {
        if (!auth()->check()) return redirect()->guest(route('login'));
        $request->validate(['grupo_formacion'=>'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $u=auth()->user();
        $filtro=$request->only('grupo_formacion');
        if ($this->autorizacionService->esPersonalGestion($u)) return redirect()->route('seguimiento.panel.coordinacion',$filtro);
        if ($this->autorizacionService->esDocenteOEntrevistador($u)) return redirect()->route('seguimiento-egresados.mis-asignaciones',$filtro);
        if ($u->condicion==='Egresado' || $u->hasRole('egresado')) return redirect()->route('seguimiento.mi-espacio');
        if ($this->autorizacionService->puedeAccederLaboratorio($u)) return redirect()->route('seguimiento.laboratorio',$filtro);
        abort(403,'Sin función autorizada de seguimiento.');
    }

    public function guardarActualizacion(Request $request)
    {
        // -------------------------------------------------------------
        // 1. RESOLUCIÓN DE IDENTIDAD, TOKENS Y PERMISOS (R01 / R04 / Fase 1C)
        // -------------------------------------------------------------
        $rawId = $request->input('user_id') ?: $request->input('id');
        $dni = $request->input('dni');
        $tokenInput = $request->input('token_invitacion') ?: $request->input('token');
        $targetUserId = null;
        $idTemporal = null;
        $invitacionValida = null;

        if (is_numeric($rawId)) {
            $targetUserId = (int) $rawId;
        } elseif (is_string($rawId) && str_starts_with($rawId, 'REAL-')) {
            $targetUserId = (int) str_replace('REAL-', '', $rawId);
        } elseif ($dni && ($userByDni = User::where('dni', $dni)->first())) {
            $targetUserId = (int) $userByDni->id;
        } else {
            $idTemporal = is_string($rawId) ? $rawId : ($dni ? ('DNI-' . $dni) : null);
        }

        $estadoVerificacion = 'verificado';
        $procedencia = 'intranet';
        $registradoPor = 'Sistema institucional';
        $canal = $request->input('canal', 'intranet');
        $esRegistroAislado = false;

        // A. Si se proporciona un token criptográfico de invitación, resolverlo con máxima prioridad
        if ($tokenInput) {
            $invitacion = InvitacionCampana::validos()->where('token', $tokenInput)->first();
            if (!$invitacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'El enlace de invitación no es válido o ha expirado.'
                ], 422);
            }
            $targetUserId = (int)$invitacion->user_id;
            $idTemporal = null;
            $invitacionValida = $invitacion;
            $estadoVerificacion = 'verificado';
            $procedencia = 'publico_whatsapp_invitacion';
            $registradoPor = auth()->check() ? auth()->user()->name : 'Autogestión por invitación WhatsApp';
            $canal = 'campana_whatsapp_invitacion';
        }
        // B. Evaluación para usuarios autenticados sin token
        elseif (auth()->check()) {
            $authUser = auth()->user();

            if ($this->esEgresadoOAlumno($authUser)) {
                if ($targetUserId && (int)$targetUserId !== (int)$authUser->id) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No tienes autorización para modificar la ficha de otro egresado.'
                    ], 403);
                }
                $targetUserId = (int)$authUser->id;
                $idTemporal = null;
                $procedencia = 'intranet_autogestion';
                $registradoPor = $authUser->name;
                $canal = $request->input('canal', 'intranet');
            } elseif ($this->esEncuestador($authUser) || $this->esEquipoDocente($authUser)) {
                if ($targetUserId) {
                    $asignado = $this->autorizacionService->puedeModificarEgresado($targetUserId, $authUser);
                    if (!$asignado) {
                        return response()->json([
                            'success' => false,
                            'error' => 'No tienes asignado este egresado en tus grupos o campañas activas de seguimiento.'
                        ], 403);
                    }
                }
                $procedencia = 'intranet_entrevistador';
                $registradoPor = $authUser->name;
            } else {
                abort_unless($targetUserId && $this->autorizacionService->puedeModificarEgresado($targetUserId, $authUser), 403);
                $procedencia = 'intranet_gestion';
                $registradoPor = $authUser->name;
            }
        }
        // C. Evaluación para solicitudes públicas / sin sesión y sin token (Prevenir suplantación IDOR)
        else {
            if ($targetUserId) {
                $userExists = User::where('id', $targetUserId)->exists();
                if (!$userExists) {
                    return response()->json([
                        'success' => false,
                        'error' => 'El identificador de egresado especificado no existe en la base institucional.'
                    ], 422);
                }
                // REGLA ANTI-SUPLANTACIÓN (Fase 1C):
                // Un acceso público sin token no puede modificar el perfil verificado ni cerrar episodios
                $esRegistroAislado = true;
                $estadoVerificacion = 'pendiente_verificacion';
                $procedencia = 'publico_whatsapp_no_verificado';
                $registradoPor = 'Público sin credencial';
                $canal = 'publico_abierto';
            } elseif (!$idTemporal) {
                return response()->json([
                    'success' => false,
                    'error' => 'Identificador de registro no proporcionado o inválido.'
                ], 422);
            } else {
                $esRegistroAislado = true;
                $estadoVerificacion = 'pendiente_verificacion';
                $procedencia = 'publico_whatsapp_no_verificado';
                $registradoPor = 'Público sin credencial';
                $canal = 'publico_abierto';
            }
        }

        $campana = $request->input('campana', '2026-II');

        // -------------------------------------------------------------
        // 2. AISLAMIENTO SEGURO DE REGISTROS NO VERIFICADOS (Fase 1C)
        // -------------------------------------------------------------
        if ($esRegistroAislado) {
            $tempKey = 'QUARANTINE-' . ($targetUserId ?: 'ANON') . '-' . time() . '-' . rand(10, 99);

            $actualizacion = Actualizacion::create([
                'user_id' => null, // Protegemos el perfil oficial: se aísla de la ficha del egresado
                'identificador_temporal' => $tempKey,
                'nombre_completo' => $request->input('nombre', 'Egresado/a'),
                'periodo_campana' => $campana,
                'tipo_registro' => 'solicitud_sin_token',
                'estado_verificacion' => 'pendiente_verificacion',
                'procedencia' => $procedencia,
                'token_invitacion' => null,
                'ip_registro' => $request->ip(),
                'canal' => $canal,
                'registrado_por' => $registradoPor,
                'fecha_ultima_confirmacion' => now(),
                'es_confirmacion_sin_cambios' => false,
                'telefono' => $request->input('telefono'),
                'whatsapp' => $request->input('whatsapp') ?: $request->input('telefono'),
                'correo' => $request->input('correo'),
                'direccion_contacto' => $request->input('direccion_contacto'),
                'trabaja' => $request->input('trabaja'),
                'institucion' => $request->input('institucion'),
                'cargo' => $request->input('cargo'),
                'datos_extra' => [
                    'candidato_user_id' => $targetUserId,
                    'dni_declarado' => $dni,
                    'ip' => $request->ip(),
                    'observacion' => 'Registro público recibido sin token criptográfico. Pendiente de verificación por llamada.'
                ]
            ]);

            // Abrir caso de verificación para que el coordinador contacte y valide
            Caso::create([
                'codigo' => 'VERIF-' . time() . '-' . rand(10, 99),
                'user_id' => $targetUserId,
                'nombre_egresado' => $request->input('nombre', 'Egresado/a'),
                'tema' => 'Verificación de actualización pública sin credencial',
                'responsable' => 'Coordinación de Empleabilidad',
                'fecha_gestion' => now()->addDays(3)->toDateString(),
                'estado' => 'Por revisar',
                'proposito' => 'Un usuario actualizó datos sin token criptográfico. Validar telefónicamente antes de sincronizar expediente.',
                'origen' => 'Campaña pública abierta'
            ]);

            return response()->json([
                'success' => true,
                'tipo' => 'pendiente_verificacion',
                'message' => 'Actualización recibida. Al ser un registro abierto, el equipo de seguimiento verificará tus datos antes de consolidarlos en tu expediente.',
                'actualizacion_id' => $actualizacion->id
            ]);
        }

        // Recuperar nombre oficial si existe en BD
        $nombre = $request->input('nombre', 'Egresado/a');
        if ($targetUserId && ($u = User::find($targetUserId))) {
            $nombre = trim($u->name . ' ' . $u->apellidos);
        }

        // -------------------------------------------------------------
        // 3. EJECUCIÓN TRANSACCIONAL PROTEGIDA CON BLOQUEO PESIMISTA (ACID)
        // -------------------------------------------------------------
        try {
            return DB::transaction(function () use (
                $request, $targetUserId, $idTemporal, $nombre, $canal, $registradoPor,
                $campana, $estadoVerificacion, $procedencia, $tokenInput, $invitacionValida
            ) {
                // Marcar token como consumido de manera atómica dentro de la transacción
                if ($invitacionValida) {
                    $invLock = InvitacionCampana::where('id', $invitacionValida->id)->lockForUpdate()->first();
                    if ($invLock) {
                        $invLock->marcarComoUsado($request->ip());
                    }
                }

                // Control de concurrencia: serializar escrituras sobre el mismo egresado
                if ($targetUserId) {
                    Actualizacion::where('user_id', $targetUserId)->lockForUpdate()->get();
                    EpisodioLaboral::where('user_id', $targetUserId)->lockForUpdate()->get();
                }

                $isSinCambios = $request->boolean('sin_cambios')
                    || $request->input('cambio_empleo') === 'Sin cambios'
                    || $request->boolean('confirmar_vigente');

                // ---------------------------------------------------------
                // 3.1. CAMINO RÁPIDO: CONFIRMACIÓN SIN CAMBIOS (R02)
                // ---------------------------------------------------------
                if ($isSinCambios) {
                    // Historial longitudinal por campaña: la clave de búsqueda incluye periodo_campana
                    $actualizacion = Actualizacion::updateOrCreate(
                        [
                            'user_id' => $targetUserId,
                            'periodo_campana' => $campana,
                        ],
                        [
                            'nombre_completo' => $nombre,
                            'tipo_registro' => 'confirmacion_sin_cambios',
                            'estado_verificacion' => $estadoVerificacion,
                            'procedencia' => $procedencia,
                            'token_invitacion' => $tokenInput,
                            'ip_registro' => $request->ip(),
                            'canal' => $canal,
                            'registrado_por' => $registradoPor,
                            'fecha_ultima_confirmacion' => now(),
                            'es_confirmacion_sin_cambios' => true,
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

                    if ($targetUserId) {
                        AsignacionCampana::where('egresado_user_id', $targetUserId)
                            ->whereIn('estado', ['asignado', 'contactado'])
                            ->update(['estado' => 'completado']);
                    }

                    if ($targetUserId) {
                    AsignacionCampana::where('egresado_user_id', $targetUserId)
                        ->whereIn('estado', ['asignado', 'contactado'])
                        ->update(['estado' => 'completado']);
                }

                $episodiosActivos = EpisodioLaboral::where('user_id', $targetUserId)
                        ->where('es_actual', true)
                        ->count();

                    app(\App\Services\SeguimientoEgresado\PerfilTerritorialService::class)->desdeActualizacion($actualizacion);
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
                // 3.2. ACTUALIZACIÓN COMPLETA O CAMBIO DE EMPLEO (R03)
                // ---------------------------------------------------------
                $actualizacion = Actualizacion::updateOrCreate(
                    [
                        'user_id' => $targetUserId,
                        'periodo_campana' => $campana,
                    ],
                    [
                        'nombre_completo' => $nombre,
                        'tipo_registro' => 'respuesta_campana',
                        'estado_verificacion' => $estadoVerificacion,
                        'procedencia' => $procedencia,
                        'token_invitacion' => $tokenInput,
                        'ip_registro' => $request->ip(),
                        'programa' => $request->input('programa'),
                        'cohorte_egreso' => $request->input('egreso') ?: $request->input('cohorte_egreso'),
                        'canal' => $canal,
                        'registrado_por' => $registradoPor,
                        'fecha_ultima_confirmacion' => now(),
                        'es_confirmacion_sin_cambios' => false,
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
                // 3.3. GESTIÓN DE EPISODIOS LABORALES LONGITUDINALES (R03 / Fase 1C)
                // ---------------------------------------------------------
                $trabaja = $request->input('trabaja', 'Trabaja');

                if ($trabaja === 'Trabaja' || str_starts_with(strtolower($trabaja), 'trabaja')) {
                    $institucion = trim($request->input('institucion', ''));
                    $cargo = trim($request->input('cargo', ''));

                    // INTEGRIDAD DE FECHA (Fase 1C):
                    // Cero asignación automática de now() a fechas de inicio desconocidas
                    $fechaInicio = $request->input('inicio_empleo') ?: null;
                    $fechaFin = $request->input('fin_empleo') ?: null;

                    // INTEGRIDAD DE CAMPOS Y NO INFERENCIA (Fase 1C):
                    // Solo marcar como educativo o docencia directa si los datos lo señalan expresamente
                    $campo = $request->input('campo');
                    $esEducativo = false;
                    if ($campo && in_array(trim($campo), ['En educación', 'Educativo', 'Sector educativo'])) {
                        $esEducativo = true;
                    } elseif ($request->has('es_empleo_educativo')) {
                        $esEducativo = $request->boolean('es_empleo_educativo');
                    }

                    $tipoPractica = $request->input('tipo_practica');
                    $esDocencia = false;
                    if ($tipoPractica && in_array(trim($tipoPractica), ['Docencia directa', 'Docente de aula', 'Docente'])) {
                        $esDocencia = true;
                    } elseif ($request->has('es_docencia_directa')) {
                        $esDocencia = $request->boolean('es_docencia_directa');
                    }

                    if ($institucion !== '') {
                        // Idempotencia: Verificar si este mismo episodio ya existe para evitar duplicar por reenvío
                        $episodioExistente = EpisodioLaboral::where('user_id', $targetUserId)
                            ->where('institucion', $institucion)
                            ->where('cargo', $cargo)
                            ->where(function ($q) use ($fechaInicio) {
                                if ($fechaInicio) {
                                    $q->where('fecha_inicio', $fechaInicio);
                                } else {
                                    $q->whereNull('fecha_inicio');
                                }
                            })
                            ->first();

                        if ($episodioExistente) {
                            // Actualizar atributos sin crear fila duplicada
                            $episodioExistente->update([
                                'contrato' => $request->input('contrato') ?: $episodioExistente->contrato,
                                'sector' => $request->input('sector') ?: $episodioExistente->sector,
                                'es_actual' => is_null($fechaFin) || $fechaFin >= now()->toDateString(),
                                'fecha_fin' => $fechaFin,
                                'es_empleo_educativo' => $esEducativo,
                                'es_docencia_directa' => $esDocencia,
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

                            // Crear nuevo episodio laboral conservando los existentes (soporta empleos simultáneos)
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
                                'es_empleo_educativo' => $esEducativo,
                                'es_docencia_directa' => $esDocencia,
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
                        // Cerrar selectivamente los episodios activos registrando la fecha fin declarada
                        EpisodioLaboral::where('user_id', $targetUserId)
                            ->where('es_actual', true)
                            ->update([
                                'es_actual' => false,
                                'fecha_fin' => $request->input('fin_empleo') ?: null,
                                'motivo_cambio' => $request->input('motivo_cambio') ?: 'Fin de empleo reportado en actualización'
                            ]);
                    }
                    // REGLA FUNDAMENTAL: Cero empleadores ficticios "Desempleado".
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

                if ($targetUserId) {
                    AsignacionCampana::where('egresado_user_id', $targetUserId)
                        ->whereIn('estado', ['asignado', 'contactado'])
                        ->update(['estado' => 'completado']);
                }

                $episodiosActivos = EpisodioLaboral::where('user_id', $targetUserId)
                    ->where('es_actual', true)
                    ->count();

                app(\App\Services\SeguimientoEgresado\PerfilTerritorialService::class)->desdeActualizacion($actualizacion);
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
     * Endpoint para cerrar un episodio laboral específico con verificación de propiedad/asignación.
     */
    public function cerrarEpisodio(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        $episodio = EpisodioLaboral::findOrFail($id);

        if (!$this->puedeAccederAEgresado((int)$episodio->user_id)) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para cerrar este episodio laboral.'
            ], 403);
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
     * Obtener episodios laborales de un egresado (activos e históricos) con control de acceso.
     */
    public function obtenerEpisodios(Request $request, $userId)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if (!$this->puedeAccederAEgresado((int)$userId)) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para consultar los episodios de este egresado.'
            ], 403);
        }

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
     * Registrar nueva acción o caso de acompañamiento institucional.
     */
    public function guardarAccion(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if (!$this->esPersonalGestion() && !$this->esEquipoDocente()) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para crear casos de acompañamiento institucional.'
            ], 403);
        }

        $idStr = $request->input('pid');
        $userId = null;
        if ($idStr && str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        } elseif ($idStr && is_numeric($idStr)) {
            $userId = (int) $idStr;
        }

        $caso = Caso::create([
            'codigo' => 'ACT-' . time() . '-' . rand(10, 99),
            'user_id' => $userId,
            'nombre_egresado' => $request->input('nombre', 'Egresado/a'),
            'tema' => $request->input('tema', 'Acompañamiento general'),
            'responsable' => $request->input('responsable', auth()->user()->name),
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
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if (!$this->esPersonalGestion()) {
            return response()->json([
                'success' => false,
                'error' => 'Solo la coordinación y administración pueden avanzar el estado de un caso.'
            ], 403);
        }

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
     * Registrar intento o entrevista telefónica con control de autorización.
     */
    public function guardarLlamada(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if ($this->esEgresadoOAlumno() && !$this->autorizacionService->esPersonalGestion() && !$this->autorizacionService->esDocenteOEntrevistador()) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para registrar llamadas.'
            ], 403);
        }

        $idStr = $request->input('id');
        $userId = null;
        $idTemporal = $idStr;
        if ($idStr && str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
            $idTemporal = null;
        } elseif ($idStr && is_numeric($idStr)) {
            $userId = (int) $idStr;
            $idTemporal = null;
        }

        if ($userId && !$this->puedeAccederAEgresado($userId)) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para registrar llamadas a este egresado.'
            ], 403);
        }

        $request->validate(['campana_id'=>'required|integer|exists:egresado_campanas,id','tipo_resultado'=>'nullable|in:intento_sin_respuesta,numero_equivocado,tercero_conocido,entrevista_programada,contacto_efectivo,actualizacion_completa,actualizacion_parcial,confirmacion_sin_cambios,rechazo_participacion,requiere_verificacion,requiere_derivacion','comment'=>'nullable|string|max:4000','fecha_reprogramada'=>'required_if:tipo_resultado,entrevista_programada|nullable|date|after:now','motivo_derivacion'=>'nullable|string|max:2000']);
        abort_unless($userId && $this->autorizacionService->puedeRegistrarLlamada($userId),403);
        abort_unless(User::whereKey($userId)->where('condicion','Egresado')->exists(),404);
        return DB::transaction(function () use ($request,$userId,$idTemporal) {
        $campanaLlamada=Campana::lockForUpdate()->findOrFail($request->campana_id);
        abort_unless(in_array(strtolower($campanaLlamada->estado),['activo','activa']) && (!$campanaLlamada->fecha_inicio || $campanaLlamada->fecha_inicio<=now()->toDateString()) && (!$campanaLlamada->fecha_fin || $campanaLlamada->fecha_fin>=now()->toDateString()),422,'Campaña fuera de vigencia.');
        abort_unless($this->autorizacionService->esPersonalGestion() || AsignacionCampana::esDocenteAsignado(auth()->id(),$userId,(int)$request->campana_id),403);
        $resultado = $request->input('result', 'No contesta');
        $tipoResultado = $request->input('tipo_resultado');
        if (!$tipoResultado) {
            $tipoResultado = match(strtolower(trim($resultado))) {
                'contacto efectivo', 'contactado' => 'contacto_efectivo',
                'entrevista programada', 'reprogramado' => 'entrevista_programada',
                'actualización completa', 'actualizacion completa' => 'actualizacion_completa',
                'actualización parcial', 'actualizacion parcial' => 'actualizacion_parcial',
                'confirmación sin cambios', 'confirmacion sin cambios' => 'confirmacion_sin_cambios',
                'número equivocado', 'numero equivocado', 'numero invalido' => 'numero_equivocado',
                'rechazo', 'rechazo de participación' => 'rechazo_participacion',
                'requiere verificación', 'requiere verificacion' => 'requiere_verificacion',
                'requiere derivación', 'requiere derivacion' => 'requiere_derivacion',
                default => 'intento_sin_respuesta'
            };
        }

        $contactosEfectivosTipos = [
            'contacto_efectivo', 'entrevista_programada', 'actualizacion_completa',
            'actualizacion_parcial', 'confirmacion_sin_cambios', 'rechazo_participacion'
        ];
        $esEfectivo = in_array($tipoResultado, $contactosEfectivosTipos, true);

        $campanaId = $request->input('campana_id');
        $esEnsayo = (bool)$campanaLlamada->es_ensayo;

        $requiereDerivacion = $request->boolean('requiere_derivacion') || $tipoResultado === 'requiere_derivacion';
        $motivoDerivacion = $request->input('motivo_derivacion');

        $llamada = Llamada::create([
            'user_id' => $userId,
            'identificador_temporal' => $idTemporal,
            'campana_id' => $campanaId,
            'docente_user_id' => auth()->id(),
            'resultado' => $resultado,
            'tipo_resultado' => $tipoResultado,
            'es_contacto_efectivo' => $esEfectivo,
            'fecha_reprogramada' => $request->input('fecha_reprogramada'),
            'requiere_derivacion' => $requiereDerivacion,
            'motivo_derivacion' => $motivoDerivacion,
            'es_ensayo' => $esEnsayo,
            'observaciones' => $request->input('comment', ''),
            'encuestador' => auth()->user()->name,
            'fecha_hora' => now()
        ]);

        // Si el egresado tiene asignación en la campaña y hubo contacto, actualizar estado de asignación
        if ($userId && $campanaId) {
            $asig = AsignacionCampana::where('campana_id', $campanaId)
                ->where('egresado_user_id', $userId)
                ->where('docente_user_id', auth()->id())
                ->whereIn('estado', ['asignado', 'contactado'])
                ->first();

            if ($asig && $esEfectivo && $asig->estado === 'asignado') {
                $asig->update(['estado' => 'contactado']);
            }
        }

        // Si requiere derivación, abrir caso institucional automáticamente
        if ($requiereDerivacion && $userId) {
            Caso::create([
                'codigo' => 'DERIV-' . time() . '-' . rand(10, 99),
                'user_id' => $userId,
                'nombre_egresado' => User::find($userId)?->name ?? 'Egresado/a',
                'tema' => 'Derivación desde llamada de seguimiento',
                'responsable' => 'Coordinación / Bienestar',
                'fecha_gestion' => now()->addDays(2)->toDateString(),
                'estado' => 'Pendiente',
                'proposito' => $motivoDerivacion ?: 'Docente entrevistador reportó necesidad de derivación o atención especial.',
                'origen' => 'Llamada telefónica docente'
            ]);
        }

        return response()->json([
            'success' => true,
            'llamada_id' => $llamada->id
        ]);
        });
    }

    /**
     * Auditar acceso a los datos de contacto de un egresado.
     */
    public function auditarContacto(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if ($this->esEgresadoOAlumno()) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para auditar contactos.'
            ], 403);
        }

        $idStr = $request->input('id');
        $userId = null;
        if ($idStr && str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        } elseif ($idStr && is_numeric($idStr)) {
            $userId = (int) $idStr;
        }

        if ($userId && !$this->puedeAccederAEgresado($userId)) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes autorización para consultar los contactos de este egresado.'
            ], 403);
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
     * Crear un grupo de entrevistadores (exclusivo para coordinación y dirección).
     */
    public function guardarGrupo(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if (!$this->esPersonalGestion()) {
            return response()->json([
                'success' => false,
                'error' => 'Solo la Coordinación de Empleabilidad y la Dirección pueden gestionar grupos de entrevistadores.'
            ], 403);
        }

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
     * Eliminar un grupo de entrevistadores (exclusivo para coordinación y dirección).
     */
    public function eliminarGrupo($id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        if (!$this->esPersonalGestion()) {
            return response()->json([
                'success' => false,
                'error' => 'Solo la Coordinación de Empleabilidad y la Dirección pueden eliminar grupos de entrevistadores.'
            ], 403);
        }

        $grupo = GrupoEntrevistador::findOrFail($id);
        $grupo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupo eliminado correctamente.'
        ]);
    }

    /**
     * Generar invitación segura sin PII en la URL para compartir por WhatsApp (Fase 1C).
     */
    public function generarEnlaceWhatsapp(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'No autenticado.'], 401);
        }

        $campana = $request->input('campana', '2026-II');
        $rawId = $request->input('user_id') ?: $request->input('id');
        $userId = null;

        if (is_numeric($rawId)) {
            $userId = (int) $rawId;
        } elseif (is_string($rawId) && str_starts_with($rawId, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $rawId);
        }

        if ($userId) {
            if (!$this->puedeAccederAEgresado($userId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes autorización para generar enlaces de este egresado.'
                ], 403);
            }

            $egresado = User::findOrFail($userId);
            $invitacion = InvitacionCampana::crearInvitacion($egresado, auth()->id(), $campana, 'whatsapp');
            $urlCampana = route('seguimiento-egresados.campana-publica', ['token' => $invitacion->token]);
            $nombreCorto = explode(' ', trim($egresado->name))[0];

            $mensajeDefault = "¡Hola {$nombreCorto}! 🎓 La EESP Pukllasunchis te invita a participar en la Campaña de Actualización de Egresados ({$campana}).\n\n"
                . "Te tomará solo 2 minutos confirmar tus datos y contarnos sobre tu práctica pedagógica:\n"
                . "👉 {$urlCampana}\n\n"
                . "¡Gracias por formar parte de nuestra comunidad educativa!";
        } else {
            $urlCampana = route('seguimiento-egresados.campana-publica');
            $mensajeDefault = "¡Hola! 🎓 La EESP Pukllasunchis te invita a participar en la Campaña de Actualización de Egresados ({$campana}).\n\n"
                . "Te tomará solo 3 minutos confirmar tus datos:\n"
                . "👉 {$urlCampana}\n\n"
                . "¡Gracias por formar parte de nuestra comunidad!";
        }

        $whatsappUrl = 'https://api.whatsapp.com/send?text=' . urlencode($mensajeDefault);

        return response()->json([
            'success' => true,
            'campana' => $campana,
            'url' => $urlCampana,
            'token' => isset($invitacion) ? $invitacion->token : null,
            'mensaje' => $mensajeDefault,
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    /**
     * Enlace público de campaña (para WhatsApp / Magic Link).
     */
    public function campanaPublica(Request $request)
    {
        $token = $request->route('token') ?? $request->query('token');
        $invitacion = null;
        $egresado = null;
        $episodioActivo = null;

        if ($token) {
            $invitacion = InvitacionCampana::validos()->where('token', $token)->first();
            if ($invitacion && $invitacion->user) {
                $egresado = $invitacion->user;
                $episodioActivo = EpisodioLaboral::where('user_id', $egresado->id)
                    ->where('es_actual', true)
                    ->latest()
                    ->first();
            }
        }

        return view('seguimiento_egresados.campana_publica', compact('token', 'invitacion', 'egresado', 'episodioActivo'));
    }
}
