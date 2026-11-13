<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
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

    public function entrevista(Request $request, int $egresado)
    {
        abort_unless($this->autorizacion->puedeModificarEgresado($egresado),403);
        abort_unless(\App\Models\User::whereKey($egresado)->where('condicion','Egresado')->exists(),404);
        $campanaId=$this->campanaEntrevista($request,$egresado);
        return $this->mostrar($egresado, true, $campanaId);
    }

    private function mostrar(int $id, bool $esEntrevista, ?int $campanaId=null)
    {
        $titular = \App\Models\User::findOrFail($id);
        $titular->loadMissing(\App\Services\SeguimientoEgresado\GrupoFormacionService::RELACIONES);
        $clasificacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->resolver($titular);
        $datos = $this->perfil->actual($id);
        if (!$datos) {
            $a = Actualizacion::where('user_id', $id)->where('estado_verificacion', 'verificado')->latest('id')->first();
            $u = $titular;
            $grupoSvc = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class);
            $ficha = $grupoSvc->ficha($u);
            $progModel = $grupoSvc->programa($u);
            $anioEgreso = $a?->cohorte_egreso ?: $u->seguimientoTitulacion?->anio_egreso;
            $datos = [
                'contacto' => [
                    'correo' => $a?->correo ?: ($ficha?->email ?: $u->email),
                    'telefono' => $a?->telefono ?: ($ficha?->numero ?: $ficha?->numero_referencia),
                    'programa' => $a?->programa ?: $progModel?->nombre,
                    'egreso' => $anioEgreso && preg_match('/(19\d{2}|20\d{2})/', (string) $anioEgreso, $m) ? (int) $m[1] : null,
                ],
                'residencia' => [
                    'departamento' => $a?->res_departamento ?: $ficha?->departamento,
                    'provincia' => $a?->res_provincia ?: $ficha?->provincia,
                    'distrito' => $a?->res_distrito ?: $ficha?->distrito,
                ],
                'trabaja' => $a?->trabaja === 'No trabaja' ? 'no' : ($a?->trabaja === 'Trabaja' ? 'si' : null),
            ];
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
        $ultima = DB::table('egresado_perfil_versiones')->where('user_id', $id)->latest('id')->value('confirmado_at');
        $casos = Caso::where('user_id', $id)->latest('id')->get(['tema','estado','fecha_gestion']);
        if ($esEntrevista) { unset($datos['apoyo'],$datos['contacto_alterno']); $casos=collect(); }
        return view('seguimiento_egresados.mi_espacio', compact('datos','borrador','ultima','casos','clasificacion','esEntrevista','titular','campanaId'));
    }

    public function guardar(Request $request)
    {
        $id = $this->titular();
        $this->validarConfirmacion($request);
        // Nunca aceptar user_id del cliente: este espacio es exclusivamente del titular.
        $datos = $this->perfil->guardar($id, $request->all());
        return response()->json(['success' => true, 'datos' => $datos, 'avance' => $this->perfil->avance($datos), 'confirmado_at' => now()->toIso8601String()]);
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
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = $clasificacion->normalizar($request->query('grupo_formacion'));
        $usuarios = $clasificacion->aplicar(\App\Models\User::where('condicion','Egresado'), $grupoFormacion)->get()->keyBy('id');
        $resumenGrupos = $clasificacion->resumen($usuarios);
        $rows = DB::table('egresado_perfil_versiones')->whereIn('user_id', $usuarios->keys())->whereIn('id', function ($q) {
            $q->selectRaw('MAX(id)')->from('egresado_perfil_versiones')->groupBy('user_id');
        })->get();
        $active = EpisodioLaboral::where('es_actual', true)->get()->groupBy('user_id');
        $groups = []; $counts = ['residencia_comunidad' => 0, 'trabajo_comunidad' => 0, 'contribucion' => 0];
        $missing = ['residencia' => 0, 'trabajo' => 0];
        foreach ($rows as $row) {
            $p = json_decode($row->datos, true);
            $res = $p['residencia'] ?? [];
            if (in_array($res['comunidad_tipo'] ?? null, ['campesina','nativa'])) $counts['residencia_comunidad']++;
            $jobs = collect($p['trabajos'] ?? [])->keyBy('episodio_id');
            $places = [['tipo' => 'residencia', 'lugar' => $res]];
            $inCommunity = false;
            foreach ($active->get($row->user_id, collect()) as $ep) {
                $place = $jobs->get($ep->id)['lugar'] ?? [];
                if (in_array($place['comunidad_tipo'] ?? null, ['campesina','nativa'])) $inCommunity = true;
                $places[] = ['tipo' => 'trabajo', 'lugar' => $place];
            }
            if ($inCommunity) $counts['trabajo_comunidad']++;
            if (in_array('colaboro', $p['comunidad']['vinculos'] ?? [])) $counts['contribucion']++;
            foreach ($places as $entry) {
                $place = $entry['lugar']; $type = $entry['tipo'];
                // Nunca usar centroides distritales para simular precisión ni coordenadas no verificadas.
                if (!isset($place['latitud'], $place['longitud']) || !in_array($place['precision'] ?? '', ['localidad','escuela','sede'])) { $missing[$type]++; continue; }
                $key = $type.':'.($place['lugar_id'] ?? '').':'.$place['latitud'].':'.$place['longitud'];
                if (!isset($groups[$key])) $groups[$key] = ['tipo' => $type, 'nombre' => $place['localidad'] ?? $place['distrito'] ?? 'Lugar', 'latitud' => $place['latitud'], 'longitud' => $place['longitud'], 'personas' => []];
                $groups[$key]['personas'][$row->user_id] = true;
                $groups[$key]['por_grupo'] ??= ['FID' => [], 'PPD' => []];
                foreach ($clasificacion->resolver($usuarios[$row->user_id])['grupos'] as $g) $groups[$key]['por_grupo'][$g][$row->user_id] = true;
            }
        }
        // Agregación fija sin filtros identificables. Nunca devolver identidades o coordenadas de viviendas.
        $points = [];
        foreach ($groups as $group) {
            $n = count($group['personas']);
            if ($n < 5) continue;
            unset($group['personas']);
            $small = collect($group['por_grupo'])->contains(fn ($personas) => count($personas) > 0 && count($personas) < 5);
            foreach ($group['por_grupo'] as $g => $personas) $group['por_grupo'][$g] = $small ? null : count($personas);
            $group['n'] = $n; $points[] = $group;
        }
        $safe = app(\App\Services\SeguimientoEgresado\EstadisticaSeguraService::class);
        return response()->json(['puntos' => $points, 'indicadores' => array_map(fn ($n) => $safe->protegerConteo($n), $counts),
            'grupo_formacion' => $grupoFormacion, 'resumen_grupos' => $resumenGrupos, 'cobertura' => $rows->count(), 'pendientes_coordenadas' => $missing, 'actualizado_at' => now()->toIso8601String()]);
    }
}
