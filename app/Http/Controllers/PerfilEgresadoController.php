<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\User;
use App\Services\SeguimientoEgresado\PerfilTerritorialService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerfilEgresadoController extends Controller
{
    public function __construct(private PerfilTerritorialService $perfil, private SeguimientoAutorizacionService $autorizacion)
    {
        $this->middleware('auth');
    }

    private function titular(): int
    {
        abort_unless(auth()->user()->condicion === 'Egresado' || auth()->user()->hasRole('egresado'), 403);
        abort_unless(Schema::hasTable('egresado_perfil_versiones'), 503, 'El perfil requiere ejecutar las migraciones pendientes.');
        return (int) auth()->id();
    }

    public function index()
    {
        return $this->mostrar($this->titular(), false);
    }

    /**
     * Enlace común de acceso al Piloto de la Red Puklla de Egresados (PUKLLA-BETA-PILOTO).
     * Conserva el destino al iniciar sesión y valida en el servidor la elegibilidad y titularidad.
     */
    public function piloto(Request $request, string $codigo)
    {
        if (!auth()->check()) {
            session()->put('url.intended', $request->fullUrl());
            return redirect()->guest(route('login'))
                ->with('info', 'Inicia sesión con tu cuenta institucional para participar en el piloto de la Red de Egresados.');
        }

        $user = auth()->user();
        if ($user->condicion !== 'Egresado' && !$user->hasRole('egresado')) {
            abort(403, 'Se requiere una cuenta de egresado habilitada para acceder a la participación del piloto de la Red Puklla.');
        }

        $codigoCampana = strtoupper(trim($codigo));
        $campana = Campana::where('codigo', $codigoCampana)->first();
        if (!$campana && $codigoCampana === 'PUKLLA-BETA-PILOTO') {
            $campana = Campana::firstOrCreate(
                ['codigo' => 'PUKLLA-BETA-PILOTO'],
                [
                    'nombre' => 'Piloto Beta Red Puklla de Egresados',
                    'anio' => 2026,
                    'semestre' => 'II',
                    'fecha_inicio' => '2026-08-01',
                    'fecha_fin' => '2026-12-31',
                    'estado' => 'activa',
                    'descripcion' => 'Campaña piloto para validación beta de la Red Puklla de Egresados y actualización de perfiles territoriales.',
                    'es_ensayo' => false,
                ]
            );
        }

        if (!$campana) {
            abort(404, "Campaña de piloto '{$codigo}' no encontrada.");
        }

        if (!in_array(strtolower($campana->estado), ['activo', 'activa']) || ($campana->fecha_fin && $campana->fecha_fin < now()->toDateString())) {
            abort(422, 'La campaña del piloto ha concluido o no está activa.');
        }

        session(['es_piloto_beta' => true, 'codigo_piloto' => $codigoCampana]);

        return $this->mostrar($user->id, false, $campana->id, 'TODOS', true, $codigoCampana);
    }

    public function entrevista(Request $request, int $egresado)
    {
        abort_unless($this->autorizacion->puedeModificarEgresado($egresado),403);
        abort_unless(\App\Models\User::whereKey($egresado)->where('condicion','Egresado')->exists(),404);
        $campanaId=$this->campanaEntrevista($request,$egresado);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
        return $this->mostrar($egresado, true, $campanaId, $grupoFormacion);
    }

    private function mostrar(int $id, bool $esEntrevista, ?int $campanaId=null, string $grupoFormacion='TODOS', bool $esPiloto=false, ?string $codigoPiloto=null)
    {
        $titular = \App\Models\User::findOrFail($id);
        $titular->loadMissing(\App\Services\SeguimientoEgresado\GrupoFormacionService::RELACIONES);
        $grupoSvc = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class);
        $clasificacion = $grupoSvc->resolver($titular);
        $datos = $this->perfil->actual($id);
        $a = Actualizacion::where('user_id', $id)->where('estado_verificacion', 'verificado')->latest('id')->first();
        $ficha = $grupoSvc->ficha($titular);
        $progModel = $grupoSvc->programa($titular);
        $anioEgreso = $a?->cohorte_egreso
            ?: ($titular->trayectoriaEventos->firstWhere('tipo', 'egreso')?->fecha_inicio?->format('Y'))
            ?: ($titular->ciclo?->nombre && preg_match_all('/(20\d{2})/', $titular->ciclo->nombre, $mCic) ? end($mCic[1]) : null)
            ?: ($titular->seguimientoTitulacion?->periodo_estudios && preg_match_all('/(20\d{2})/', (string) $titular->seguimientoTitulacion->periodo_estudios, $mPer) ? end($mPer[1]) : null)
            ?: $titular->seguimientoTitulacion?->anio_egreso;

        $trabFicha = strtolower(trim((string) ($ficha?->trabajas ?? '')));
        $trabDefault = $a?->trabaja === 'No trabaja' ? 'no' : ($a?->trabaja === 'Trabaja' ? 'si' : (in_array($trabFicha, ['sí', 'si', '1'], true) ? 'si' : (in_array($trabFicha, ['no', 'todavía no', 'todavia no'], true) ? 'no' : null)));

        $defaults = [
            'contacto' => [
                'correo' => $a?->correo ?: ($ficha?->email ?: ($ficha?->correo ?: $titular->email)),
                'telefono' => $a?->telefono ?: ($ficha?->numero ?: ($ficha?->numero_referencia ?: $titular->telefono)),
                'programa' => $a?->programa ?: $progModel?->nombre,
                'egreso' => $anioEgreso && preg_match('/(19\d{2}|20\d{2})/', (string) $anioEgreso, $m) ? (int) $m[1] : null,
            ],
            'residencia' => [
                'departamento' => $a?->res_departamento ?: $ficha?->departamento,
                'provincia' => $a?->res_provincia ?: $ficha?->provincia,
                'distrito' => $a?->res_distrito ?: $ficha?->distrito,
            ],
            'trabaja' => $trabDefault,
        ];

        if (!$datos) {
            $datos = $defaults;
        } else {
            // Completar campos vacíos de una versión previa con la mejor fuente institucional disponible sin pisar lo confirmado
            foreach (['correo', 'telefono', 'programa', 'egreso'] as $ck) {
                if (empty($datos['contacto'][$ck]) && !empty($defaults['contacto'][$ck])) {
                    $datos['contacto'][$ck] = $defaults['contacto'][$ck];
                }
            }
            foreach (['departamento', 'provincia', 'distrito'] as $rk) {
                if (empty($datos['residencia'][$rk]) && !empty($defaults['residencia'][$rk])) {
                    $datos['residencia'][$rk] = $defaults['residencia'][$rk];
                }
            }
            if (empty($datos['trabaja']) && !empty($defaults['trabaja'])) {
                $datos['trabaja'] = $defaults['trabaja'];
            }
        }
        // Los episodios son la fuente de vigencia laboral, incluso si fueron cerrados por otra pantalla.
        $extras = collect($datos['trabajos'] ?? [])->keyBy('episodio_id');
        $datos['trabajos'] = EpisodioLaboral::where('user_id', $id)->orderByDesc('id')->get()->map(function ($e) use ($extras) {
            return array_replace($extras->get($e->id, []), [
                'episodio_id' => $e->id, 'institucion' => $e->institucion, 'cargo' => $e->cargo,
                'inicio' => $e->fecha_inicio?->format('Y-m-d'), 'fin' => $e->fecha_fin?->format('Y-m-d'),
                'educativo' => $e->es_empleo_educativo, 'docencia' => $e->es_docencia_directa,
                'lugar' => $extras->get($e->id)['lugar'] ?? ['departamento' => $e->trab_departamento, 'provincia' => $e->trab_provincia, 'distrito' => $e->trab_distrito],
            ]);
        })->all();
        $draft = DB::table('egresado_perfil_borradores')->where('user_id', $id)->first();
        $borrador = !$esEntrevista && $draft ? json_decode($draft->datos, true) : null;
        $ultimaRow = DB::table('egresado_perfil_versiones')->where('user_id', $id)->latest('id')->first(['id','confirmado_at']);
        $ultima = $ultimaRow?->confirmado_at;
        $versionActualId = $ultimaRow?->id;
        $campana = $campanaId ? \App\Models\SeguimientoEgresado\Campana::find($campanaId) : null;
        $casos = Caso::where('user_id', $id)->latest('id')->get(['tema','estado','fecha_gestion']);
        $conciliacionSvc = app(\App\Services\SeguimientoEgresado\ConciliacionHistoricoService::class);
        $hitosOficiales = $conciliacionSvc->obtenerHitosOficialesSeparados($titular);
        $antecedentesHistoricos = $conciliacionSvc->obtenerAntecedentesPersona($id, auth()->user());
        if ($esEntrevista) { unset($datos['apoyo'],$datos['contacto_alterno']); $casos=collect(); }
        return view('seguimiento_egresados.mi_espacio', compact('datos','borrador','ultima','versionActualId','casos','clasificacion','esEntrevista','titular','campanaId','campana','grupoFormacion','hitosOficiales','antecedentesHistoricos','esPiloto','codigoPiloto'));
    }

    public function guardar(Request $request)
    {
        $id = $this->titular();
        $this->validarConfirmacion($request);
        $campanaId = $request->input('campana_id') ? (int) $request->input('campana_id') : null;
        // Nunca aceptar user_id del cliente: este espacio es exclusivamente del titular.
        $datos = $this->perfil->guardar($id, $request->all(), $campanaId);
        return response()->json([
            'success' => true,
            'datos' => $datos,
            'avance' => $this->perfil->avance($datos),
            'confirmado_at' => now()->toIso8601String(),
            'continuar_red_url' => route('red.inicio'),
        ]);
    }

    /**
     * Confirmar la ficha sin cambios de forma inmediata e idempotente.
     * Atribuye la participación a la campaña activa o piloto.
     */
    public function confirmarSinCambios(Request $request)
    {
        $id = $this->titular();
        $codigoCampana = strtoupper(trim((string) ($request->input('codigo_piloto') ?: session('codigo_piloto', 'PUKLLA-BETA-PILOTO'))));
        $campanaId = $request->input('campana_id') ? (int) $request->input('campana_id') : null;

        if (!$campanaId && $codigoCampana) {
            $campanaPiloto = Campana::where('codigo', $codigoCampana)->first();
            $campanaId = $campanaPiloto?->id;
        }

        if ($campanaId) {
            $c = Campana::lockForUpdate()->findOrFail($campanaId);
            abort_unless(
                in_array(strtolower($c->estado), ['activo', 'activa']) &&
                (!$c->fecha_inicio || $c->fecha_inicio <= now()->toDateString()) &&
                (!$c->fecha_fin || $c->fecha_fin >= now()->toDateString()),
                422,
                'La campaña ya no está vigente.'
            );
        }

        $datosActuales = $this->perfil->actual($id);
        $user = User::findOrFail($id);
        $grupoSvc = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class);
        $ficha = $grupoSvc->ficha($user);
        $progModel = $grupoSvc->programa($user);

        if (empty($datosActuales)) {
            $datosActuales = [
                'contacto' => [
                    'correo' => $user->email,
                    'telefono' => $user->telefono ?: ($ficha?->numero ?: ''),
                    'programa' => $progModel?->nombre ?: 'Por verificar',
                    'egreso' => 2024,
                ],
                'residencia' => [
                    'departamento' => $ficha?->departamento ?: 'Cusco',
                    'provincia' => $ficha?->provincia ?: 'Cusco',
                    'distrito' => $ficha?->distrito ?: 'Cusco',
                ],
                'trabaja' => 'si',
            ];
        }

        // Registrar confirmación sin cambios en egresado_actualizaciones
        $last = Actualizacion::where('user_id', $id)->where('estado_verificacion', 'verificado')->latest('id')->first();
        $legacy = $last ? $last->replicate() : new Actualizacion(['user_id' => $id]);
        $legacy->fill([
            'user_id' => $id,
            'nombre_completo' => trim($user->name . ' ' . $user->apellidos),
            'campana_id' => $campanaId,
            'es_ensayo' => $campanaId ? (bool) Campana::find($campanaId)?->es_ensayo : false,
            'periodo_campana' => now()->year . '-' . (now()->month <= 6 ? 'I' : 'II'),
            'tipo_registro' => 'respuesta_campana',
            'estado_verificacion' => 'verificado',
            'procedencia' => 'intranet_autogestion',
            'canal' => 'mi_espacio',
            'fecha_ultima_confirmacion' => now(),
            'es_confirmacion_sin_cambios' => true,
            'programa' => $datosActuales['contacto']['programa'] ?? ($last?->programa ?: 'Por verificar'),
            'cohorte_egreso' => $datosActuales['contacto']['egreso'] ?? ($last?->cohorte_egreso ?: 2024),
            'correo' => $datosActuales['contacto']['correo'] ?? $user->email,
            'telefono' => $datosActuales['contacto']['telefono'] ?? $user->telefono,
            'res_departamento' => $datosActuales['residencia']['departamento'] ?? null,
            'res_provincia' => $datosActuales['residencia']['provincia'] ?? null,
            'res_distrito' => $datosActuales['residencia']['distrito'] ?? null,
            'trabaja' => match ($datosActuales['trabaja'] ?? null) { 'si' => 'Trabaja', 'no' => 'No trabaja', default => 'Prefiere no responder' },
            'datos_extra' => ['codigo_piloto' => $codigoCampana, 'autoservicio_piloto_beta' => true],
        ])->save();

        // Registrar versión de perfil
        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $id,
            'registrado_por' => $id,
            'datos' => json_encode($datosActuales, JSON_UNESCAPED_UNICODE),
            'confirmado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($campanaId) {
            AsignacionCampana::activos()
                ->where('campana_id', $campanaId)
                ->where('egresado_user_id', $id)
                ->whereIn('estado', ['asignado', 'contactado'])
                ->update(['estado' => 'completado']);
        }

        DB::table('egresado_perfil_borradores')->where('user_id', $id)->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Tus datos fueron confirmados correctamente y tu participación en el piloto ha sido registrada.',
            'confirmado_at' => now()->toIso8601String(),
            'continuar_red_url' => route('red.inicio'),
        ]);
    }

    private function validarConfirmacion(Request $request): void
    {
        $request->validate(['contacto.programa'=>'required|string|max:100','contacto.egreso'=>'required|integer|min:1950|max:'.date('Y'),'contacto.correo'=>'required|email|max:150']);
    }

    private function campanaEntrevista(Request $request,int $egresado): int
    {
        $request->validate(['campana_id'=>'required|integer|exists:egresado_campanas,id']);
        $id=(int)$request->input('campana_id');
        abort_unless($this->autorizacion->esPersonalGestion() || \App\Models\SeguimientoEgresado\AsignacionCampana::esDocenteAsignado(auth()->id(),$egresado,$id),403);
        $c=\App\Models\SeguimientoEgresado\Campana::findOrFail($id);
        abort_unless(in_array(strtolower($c->estado),['activo','activa']) && (!$c->fecha_inicio || $c->fecha_inicio<=now()->toDateString()) && (!$c->fecha_fin || $c->fecha_fin>=now()->toDateString()),422,'Campaña fuera de vigencia.');
        return $id;
    }

    public function guardarEntrevista(Request $request, int $egresado)
    {
        abort_unless($this->autorizacion->puedeModificarEgresado($egresado),403);
        abort_unless(\App\Models\User::whereKey($egresado)->where('condicion','Egresado')->exists(),404);
        $this->validarConfirmacion($request);
        $request->validate(['confirmacion_verbal'=>'accepted']);
        $campanaId=$this->campanaEntrevista($request,$egresado);
        $input=$request->except(['apoyo','contacto_alterno','confirmacion_verbal','campana_id']);
        $datos=$this->perfil->guardar($egresado,$input,$campanaId);
        unset($datos['apoyo'],$datos['contacto_alterno']);
        return response()->json(['success'=>true,'datos'=>$datos,'avance'=>$this->perfil->avance($datos),'confirmado_at'=>now()->toIso8601String()]);
    }

    public function borrador(Request $request)
    {
        $id = $this->titular();
        $data = $this->perfil->validar($request->all(), true);
        DB::table('egresado_perfil_borradores')->updateOrInsert(['user_id' => $id], ['datos' => json_encode($data), 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Borrador guardado en tu cuenta. El observatorio solo usa actualizaciones confirmadas.']);
    }

    public function lugares(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:150', 'tipo' => 'nullable|in:localidad,comunidad,escuela,sede']);
        abort_unless(Schema::hasTable('seguimiento_lugares'), 503);
        $query = DB::table('seguimiento_lugares');
        foreach (['departamento','provincia','distrito','tipo'] as $f) if ($request->filled($f)) $query->where($f, $request->input($f));
        if ($request->filled('q')) $query->where('nombre', 'like', '%'.$request->input('q').'%');
        return response()->json($query->orderBy('nombre')->limit(30)->get());
    }

    public function divisiones(Request $request)
    {
        $request->validate(['departamento' => 'nullable|string|max:200', 'provincia' => 'nullable|string|max:200']);
        if (!$request->filled('departamento')) return response()->json(\App\Models\Departamento::orderBy('nombre')->pluck('nombre'));
        if (!$request->filled('provincia')) return response()->json(\App\Models\Provincia::whereHas('departamento', fn ($q) => $q->where('nombre', $request->departamento))->orderBy('nombre')->pluck('nombre'));
        return response()->json(\App\Models\Distrito::whereHas('provincia', fn ($q) => $q->where('nombre', $request->provincia)->whereHas('departamento', fn ($q) => $q->where('nombre', $request->departamento)))->orderBy('nombre')->pluck('nombre'));
    }

    public function observatorio(Request $request)
    {
        abort_unless($this->autorizacion->puedeAccederLaboratorio(), 403);
        abort_unless(Schema::hasTable('egresado_perfil_versiones'), 503);
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
        return view('seguimiento_egresados.observatorio_conectado', compact('grupoFormacion'));
    }

    public function datosObservatorio(Request $request)
    {
        abort_unless($this->autorizacion->puedeAccederLaboratorio(), 403);
        abort_unless(Schema::hasTable('egresado_perfil_versiones'), 503);

        $clasificacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class);
        $asignacionSvc = app(\App\Services\SeguimientoEgresado\CampanaAsignacionService::class);
        $safe = app(\App\Services\SeguimientoEgresado\EstadisticaSeguraService::class);

        $request->validate([
            'grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR',
            'campana_id' => 'nullable|integer|exists:egresado_campanas,id',
            'estado_contacto' => 'nullable|string|in:todos,sin_contacto_efectivo,sin_intento,intento_sin_respuesta,contacto_efectivo,sin_canal_valido',
            'tipo_ubicacion' => 'nullable|string|in:ambos,residencia,trabajo',
        ]);

        $grupoFormacion = $clasificacion->normalizar($request->query('grupo_formacion', 'FID'));
        $campanaId = $request->filled('campana_id') ? (int) $request->input('campana_id') : null;
        if (!$campanaId) {
            $campanaActiva = \App\Models\SeguimientoEgresado\Campana::whereIn('estado', ['activa', 'activo'])->latest('id')->first();
            $campanaId = $campanaActiva?->id;
        }

        $estadoContactoFiltro = $request->query('estado_contacto', 'todos');
        $tipoUbicacionFiltro = $request->query('tipo_ubicacion', 'ambos');

        // Padrón institucional base
        $usuarios = $clasificacion->aplicar(\App\Models\User::where('condicion', 'Egresado'), $grupoFormacion)
            ->whereNotIn('email', ['auditor.egresada@puklla.test', 'egresado.noasignado@puklla.test'])
            ->get()
            ->keyBy('id');

        $resumenGrupos = $clasificacion->resumen($usuarios);

        // Pre-calcular estado de contacto para cada usuario
        $contactosPorUsuario = [];
        foreach ($usuarios as $uid => $user) {
            $contactosPorUsuario[$uid] = $asignacionSvc->clasificarEstadoContacto($uid, $campanaId);
        }

        // Filtrar usuarios según estado de contacto si se requiere
        if ($estadoContactoFiltro !== 'todos') {
            $usuarios = $usuarios->filter(function ($user, $uid) use ($contactosPorUsuario, $estadoContactoFiltro) {
                $c = $contactosPorUsuario[$uid];
                return match ($estadoContactoFiltro) {
                    'sin_contacto_efectivo' => (bool) $c['sin_contacto_efectivo'],
                    'contacto_efectivo' => (bool) $c['contacto_efectivo'],
                    'intento_sin_respuesta' => (bool) $c['intento_sin_respuesta'],
                    'sin_intento' => (bool) $c['sin_intento'],
                    'sin_canal_valido' => (bool) $c['sin_canal_valido'],
                    default => true,
                };
            });
        }

        $userIds = $usuarios->keys()->all();

        $rows = DB::table('egresado_perfil_versiones')
            ->whereIn('user_id', $userIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('egresado_perfil_versiones')->groupBy('user_id');
            })
            ->get();

        $active = EpisodioLaboral::where('es_actual', true)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $groups = [];
        $counts = ['residencia_comunidad' => 0, 'trabajo_comunidad' => 0, 'contribucion' => 0];
        $missing = ['residencia' => 0, 'trabajo' => 0];
        $usuariosConUbicacion = [];

        foreach ($rows as $row) {
            $uid = (int) $row->user_id;
            $p = json_decode($row->datos, true);
            $res = $p['residencia'] ?? [];
            if (in_array($res['comunidad_tipo'] ?? null, ['campesina', 'nativa'])) $counts['residencia_comunidad']++;
            $jobs = collect($p['trabajos'] ?? [])->keyBy('episodio_id');

            $places = [];
            if ($tipoUbicacionFiltro === 'ambos' || $tipoUbicacionFiltro === 'residencia') {
                $places[] = ['tipo' => 'residencia', 'lugar' => $res, 'job' => null];
            }

            $inCommunity = false;
            foreach ($active->get($uid, collect()) as $ep) {
                $jobData = $jobs->get($ep->id);
                $place = $jobData['lugar'] ?? [];
                if (in_array($place['comunidad_tipo'] ?? null, ['campesina', 'nativa'])) $inCommunity = true;
                if ($tipoUbicacionFiltro === 'ambos' || $tipoUbicacionFiltro === 'trabajo') {
                    $places[] = ['tipo' => 'trabajo', 'lugar' => $place, 'job' => $jobData];
                }
            }
            if ($inCommunity) $counts['trabajo_comunidad']++;
            if (in_array('colaboro', $p['comunidad']['vinculos'] ?? [])) $counts['contribucion']++;

            $tieneUbicacionVerificable = false;

            foreach ($places as $entry) {
                $place = $entry['lugar'];
                $type = $entry['tipo'];
                $job = $entry['job'];

                if (!isset($place['latitud'], $place['longitud']) || !in_array($place['precision'] ?? '', ['localidad', 'escuela', 'sede'])) {
                    $missing[$type]++;
                    continue;
                }

                $tieneUbicacionVerificable = true;
                $lat = (float) $place['latitud'];
                $lng = (float) $place['longitud'];
                $key = $type . ':' . ($place['lugar_id'] ?? '') . ':' . number_format($lat, 4) . ':' . number_format($lng, 4);

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'tipo' => $type,
                        'nombre' => $place['localidad'] ?? $place['distrito'] ?? 'Lugar',
                        'distrito' => $place['distrito'] ?? ($place['localidad'] ?? 'Sin distrito'),
                        'provincia' => $place['provincia'] ?? 'Cusco',
                        'departamento' => $place['departamento'] ?? 'Cusco',
                        'ubigeo' => $place['ubigeo'] ?? null,
                        'latitud' => $lat,
                        'longitud' => $lng,
                        'personas' => [],
                        'por_grupo' => ['FID' => 0, 'PPD' => 0],
                        'por_contacto' => [
                            'contacto_efectivo' => 0,
                            'intento_sin_respuesta' => 0,
                            'sin_intento' => 0,
                            'sin_contacto_efectivo' => 0,
                            'sin_canal_valido' => 0,
                        ],
                        'por_situacion_laboral' => [
                            'trabaja' => 0,
                            'busca_trabajo' => 0,
                            'no_trabaja' => 0,
                            'sin_dato' => 0,
                        ],
                        'por_docencia_eib' => [
                            'docencia_directa' => 0,
                            'no_docencia' => 0,
                            'eib' => 0,
                        ],
                    ];
                }

                // Evitar contar dos veces a la misma persona en la misma zona
                if (!isset($groups[$key]['personas'][$uid])) {
                    $groups[$key]['personas'][$uid] = true;

                    // Desglose por Grupo
                    foreach ($clasificacion->resolver($usuarios[$uid])['grupos'] as $g) {
                        if (isset($groups[$key]['por_grupo'][$g])) {
                            $groups[$key]['por_grupo'][$g]++;
                        }
                    }

                    // Desglose por Estado de Contacto
                    $cStat = $contactosPorUsuario[$uid] ?? [];
                    if (!empty($cStat['contacto_efectivo'])) $groups[$key]['por_contacto']['contacto_efectivo']++;
                    if (!empty($cStat['intento_sin_respuesta'])) $groups[$key]['por_contacto']['intento_sin_respuesta']++;
                    if (!empty($cStat['sin_intento'])) $groups[$key]['por_contacto']['sin_intento']++;
                    if (!empty($cStat['sin_contacto_efectivo'])) $groups[$key]['por_contacto']['sin_contacto_efectivo']++;
                    if (!empty($cStat['sin_canal_valido'])) $groups[$key]['por_contacto']['sin_canal_valido']++;

                    // Desglose por Situación Laboral
                    $trabVal = strtolower((string) ($p['trabaja'] ?? ''));
                    $busqVal = strtolower((string) ($p['busqueda'] ?? ''));
                    if ($trabVal === 'si') {
                        $groups[$key]['por_situacion_laboral']['trabaja']++;
                    } elseif ($busqVal !== '' && $busqVal !== 'no_busco' && $busqVal !== 'prefiero_no_responder') {
                        $groups[$key]['por_situacion_laboral']['busca_trabajo']++;
                    } elseif ($trabVal === 'no') {
                        $groups[$key]['por_situacion_laboral']['no_trabaja']++;
                    } else {
                        $groups[$key]['por_situacion_laboral']['sin_dato']++;
                    }

                    // Desglose Docencia Directa / EIB
                    $esDocencia = (bool) ($job['docencia'] ?? false);
                    $esEib = (bool) (($job['eib'] ?? null) === 'si');
                    if ($esDocencia) {
                        $groups[$key]['por_docencia_eib']['docencia_directa']++;
                    } else {
                        $groups[$key]['por_docencia_eib']['no_docencia']++;
                    }
                    if ($esEib) {
                        $groups[$key]['por_docencia_eib']['eib']++;
                    }
                }
            }

            if ($tieneUbicacionVerificable) {
                $usuariosConUbicacion[$uid] = true;
            }
        }

        // Puntos agregados
        $points = [];
        foreach ($groups as $group) {
            $n = count($group['personas']);
            if ($n < 1) continue;
            unset($group['personas']);
            $group['n'] = $n;
            $group['suprimido'] = $n < 5;
            $group['valor_seguro'] = $n < 5 ? '<5' : (string) $n;
            $points[] = $group;
        }

        // Cálculo riguroso de "Sin ubicación verificable"
        $sinUbicacionUsuarios = $usuarios->filter(fn ($u, $uid) => !isset($usuariosConUbicacion[$uid]));
        $sinUbicacionFID = 0;
        $sinUbicacionPPD = 0;
        $sinUbicacionContacto = [
            'contacto_efectivo' => 0,
            'intento_sin_respuesta' => 0,
            'sin_intento' => 0,
            'sin_contacto_efectivo' => 0,
            'sin_canal_valido' => 0,
        ];

        foreach ($sinUbicacionUsuarios as $uid => $u) {
            $gruposU = $clasificacion->resolver($u)['grupos'];
            if (in_array('FID', $gruposU)) $sinUbicacionFID++;
            if (in_array('PPD', $gruposU)) $sinUbicacionPPD++;

            $cStat = $contactosPorUsuario[$uid] ?? [];
            if (!empty($cStat['contacto_efectivo'])) $sinUbicacionContacto['contacto_efectivo']++;
            if (!empty($cStat['intento_sin_respuesta'])) $sinUbicacionContacto['intento_sin_respuesta']++;
            if (!empty($cStat['sin_intento'])) $sinUbicacionContacto['sin_intento']++;
            if (!empty($cStat['sin_contacto_efectivo'])) $sinUbicacionContacto['sin_contacto_efectivo']++;
            if (!empty($cStat['sin_canal_valido'])) $sinUbicacionContacto['sin_canal_valido']++;
        }

        $sinUbicacionData = [
            'total' => $sinUbicacionUsuarios->count(),
            'por_grupo' => ['FID' => $sinUbicacionFID, 'PPD' => $sinUbicacionPPD],
            'por_contacto' => $sinUbicacionContacto,
        ];

        return response()->json([
            'puntos' => $points,
            'sin_ubicacion' => $sinUbicacionData,
            'indicadores' => array_map(fn ($n) => $safe->protegerConteo($n), $counts),
            'grupo_formacion' => $grupoFormacion,
            'campana_id' => $campanaId,
            'estado_contacto' => $estadoContactoFiltro,
            'tipo_ubicacion' => $tipoUbicacionFiltro,
            'resumen_grupos' => $resumenGrupos,
            'cobertura' => $rows->count(),
            'pendientes_coordenadas' => $missing,
            'actualizado_at' => now()->toIso8601String(),
        ]);
    }
}
