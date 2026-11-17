<?php
namespace App\Http\Controllers;

use App\Models\SeguimientoEgresado\{Campana,Caso,Llamada,AsignacionCampana};
use App\Models\User;
use App\Services\SeguimientoEgresado\{SeguimientoAutorizacionService,GrupoFormacionService,CampanaAsignacionService,PerfilTerritorialService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeguimientoPanelController extends Controller
{
    public function __construct(private SeguimientoAutorizacionService $acceso, private GrupoFormacionService $grupos, private CampanaAsignacionService $campanas, private PerfilTerritorialService $perfil) { $this->middleware('auth'); }

    public function coordinacion(Request $r)
    {
        abort_unless($this->acceso->esPersonalGestion(),403);
        $r->validate(['grupo_formacion'=>'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion=$this->grupos->normalizar($r->query('grupo_formacion'));
        $personas=$this->grupos->aplicar(User::where('condicion','Egresado'),$grupoFormacion)->get();
        $ids=$personas->pluck('id');
        $casos=Caso::whereIn('user_id',$ids)->orderBy('fecha_gestion')->get();
        $campanas=Campana::latest('id')->get();
        $responsables=$this->campanas->obtenerDocentesElegibles();
        $perfiles=Schema::hasTable('egresado_perfil_versiones') ? DB::table('egresado_perfil_versiones')->whereIn('user_id',$ids)->whereIn('id',fn($q)=>$q->selectRaw('MAX(id)')->from('egresado_perfil_versiones')->groupBy('user_id'))->get()->keyBy('user_id') : collect();
        $filas=$personas->map(function($u)use($perfiles){$p=json_decode($perfiles->get($u->id)?->datos ?? '{}',true); return ['id'=>$u->id,'nombre'=>trim($u->name.' '.$u->apellidos),'grupo'=>$this->grupos->resolver($u)['etiqueta'],'avance'=>$this->perfil->avance($p),'formacion'=>$p['formacion']??[],'encuentros'=>$p['encuentros']??[]];});
        $cortes=Schema::hasTable('egresado_campana_cortes') ? DB::table('egresado_campana_cortes')->latest('id')->get(['id','campana_id','created_at']) : collect();
        return view('seguimiento_egresados.panel_coordinacion',compact('grupoFormacion','filas','casos','campanas','responsables','cortes'));
    }

    public function crearCampana(Request $r)
    {
        abort_unless($this->acceso->esPersonalGestion(),403);
        $data=$r->validate(['nombre'=>'required|string|max:100','codigo'=>'required|string|max:30|unique:egresado_campanas,codigo','fecha_inicio'=>'required|date','fecha_fin'=>'required|date|after_or_equal:fecha_inicio','descripcion'=>'required|string|max:2000','es_ensayo'=>'nullable|boolean']);
        $data+=['anio'=>(int)substr($data['fecha_inicio'],0,4),'semestre'=>(int)substr($data['fecha_inicio'],5,2)<=6?'I':'II','estado'=>'activa'];
        Campana::create($data);
        return back()->with('success','Campaña creada. Selecciona docentes y revisa la distribución antes de asignar.');
    }

    public function gestionarCaso(Request $r,int $caso)
    {
        abort_unless($this->acceso->puedeGestionarCasos(),403);
        $c=Caso::findOrFail($caso);
        abort_unless(!$c->es_confidencial_bienestar || $this->acceso->puedeVerNotasConfidencialesBienestar(),403);
        $v=$r->validate(['responsable'=>'required|integer|exists:users,id','fecha_gestion'=>'required|date','estado'=>'required|in:Por revisar,En atención,Derivado,Resuelto,Cerrado']);
        $u=User::findOrFail($v['responsable']);
        abort_unless($u->hasAnyRole(['docente','admin','super-admin','bienestar']) || $u->can('seguimiento-egresados.gestionar-casos'),422);
        DB::transaction(function()use($c,$u,$v){
            DB::table('egresado_panel_auditoria')->insert(['actor_id'=>auth()->id(),'tipo'=>'caso','referencia_id'=>$c->id,'datos'=>json_encode(['antes'=>$c->only(['responsable','fecha_gestion','estado']),'despues'=>$v]),'created_at'=>now()]);
            $c->update(['responsable'=>trim($u->name.' '.$u->apellidos),'fecha_gestion'=>$v['fecha_gestion'],'estado'=>$v['estado']]);
        });
        return back()->with('success','Solicitud actualizada con trazabilidad.');
    }

    public function cerrarCampana(Request $r,int $campana)
    {
        abort_unless($this->acceso->esPersonalGestion(),403);
        $v=$r->validate(['balance'=>'required|string|max:4000','horas'=>'required|numeric|min:0','costo'=>'required|numeric|min:0','presupuesto_siguiente'=>'required|numeric|min:0']);
        abort_unless(Schema::hasTable('egresado_campana_cortes'),503,'Ejecuta la migración de paneles.');
        $id=DB::transaction(function()use($campana,$v){
            $c=Campana::lockForUpdate()->findOrFail($campana);
            abort_unless(in_array(strtolower($c->estado),['activo','activa']),422,'La campaña ya está cerrada.');
            $datos=[];
            foreach(['TODOS','FID','PPD','POR_VERIFICAR'] as $grupo) {
                $m=$this->campanas->obtenerMetricasCampana($c->id,$grupo);
                // El informe congelado no contiene nombres, contactos ni notas personales.
                $datos[$grupo]=collect($m)->filter(fn($value)=>is_scalar($value)||$value===null)->all();
                $padron=$this->campanas->obtenerPadronEgresadosElegibles($c->id,false,$grupo);
                $asignados=AsignacionCampana::activos()->where('campana_id',$c->id)->whereIn('egresado_user_id',$padron->pluck('user_id'))->pluck('egresado_user_id')->unique();
                $resumen=['perfiles_confirmados'=>0,'trabaja'=>0,'no_trabaja'=>0,'reside_comunidad'=>0,'trabaja_comunidad'=>0];
                foreach($asignados as $persona) {
                    $p=$this->perfil->actual($persona);
                    if(!$p) continue;
                    $resumen['perfiles_confirmados']++;
                    if(($p['trabaja']??null)==='si') $resumen['trabaja']++;
                    if(($p['trabaja']??null)==='no') $resumen['no_trabaja']++;
                    if(in_array($p['residencia']['comunidad_tipo']??null,['campesina','nativa'])) $resumen['reside_comunidad']++;
                    if(collect($p['trabajos']??[])->contains(fn($j)=>(empty($j['fin']) || $j['fin']>=now()->toDateString()) && (empty($j['inicio']) || $j['inicio']<=now()->toDateString()) && in_array($j['lugar']['comunidad_tipo']??null,['campesina','nativa']))) $resumen['trabaja_comunidad']++;
                }
                $datos[$grupo]['perfil_territorio']=$resumen;

            }
            $id=DB::table('egresado_campana_cortes')->insertGetId(['campana_id'=>$c->id,'registrado_por'=>auth()->id(),'datos'=>json_encode(['campana'=>$c->only(['nombre','codigo','fecha_inicio','fecha_fin','es_ensayo']),'metricas'=>$datos,'balance'=>$v]),'created_at'=>now()]);
            $c->update(['estado'=>'cerrada']);
            return $id;
        });
        return redirect()->route('seguimiento.panel.informe',['corte'=>$id]);
    }

    public function informe(int $corte)
    {
        abort_unless($this->acceso->esPersonalGestion() || auth()->user()->can('seguimiento-egresados.ver-analitica'),403);
        $registro=DB::table('egresado_campana_cortes')->where('id',$corte)->first();abort_unless($registro,404);
        $informe=json_decode($registro->datos,true);
        return view('seguimiento_egresados.informe_campana',compact('registro','informe'));
    }

    public function conciliacionHistorica(Request $r)
    {
        abort_unless($this->acceso->esPersonalGestion(), 403);
        abort_unless(Schema::hasTable('egresado_historico_registros'), 503, 'Ejecuta la migración de conciliación histórica.');

        $estado = $r->query('estado', 'pendientes');
        $q = DB::table('egresado_historico_registros')->orderBy('id');
        if ($estado === 'pendientes') {
            $q->whereIn('estado_conciliacion', ['candidato_revision', 'candidato_ambiguo', 'identidad_confirmada_trayectoria_pendiente', 'sin_correspondencia']);
        } elseif (in_array($estado, ['vinculo_confirmado', 'candidato_revision', 'candidato_ambiguo', 'sin_correspondencia'], true)) {
            $q->where('estado_conciliacion', $estado);
        }

        $registros = $q->paginate(25)->withQueryString();
        $idsReg = collect($registros->items())->pluck('id');
        $candidaturas = DB::table('egresado_historico_candidaturas as c')
            ->join('users as u', 'u.id', '=', 'c.user_id_candidato')
            ->whereIn('c.registro_id', $idsReg)
            ->get(['c.*', 'u.name', 'u.apellidos', 'u.condicion'])
            ->groupBy('registro_id');

        $resumenPorFuente = DB::table('egresado_historico_registros')
            ->selectRaw('fuente_codigo, estado_conciliacion, COUNT(*) as total')
            ->groupBy('fuente_codigo', 'estado_conciliacion')
            ->get();

        return view('seguimiento_egresados.conciliacion_historica', compact('registros', 'candidaturas', 'resumenPorFuente', 'estado'));
    }

    public function resolverConciliacionHistorica(Request $r, int $registro)
    {
        abort_unless($this->acceso->esPersonalGestion(), 403);
        $v = $r->validate([
            'accion' => 'required|in:aprobar,rechazar',
            'user_id_validado' => 'required_if:accion,aprobar|nullable|integer|exists:users,id',
            'trayectoria_tipo' => 'nullable|in:FID,PPD,TRANSVERSAL,PENDIENTE',
            'motivo' => 'required|string|min:5|max:1000',
        ]);

        $servicio = app(\App\Services\SeguimientoEgresado\ConciliacionHistoricoService::class);
        $res = $servicio->resolverConciliacionManual(
            $registro,
            $v['accion'],
            isset($v['user_id_validado']) ? (int) $v['user_id_validado'] : null,
            $v['trayectoria_tipo'] ?? null,
            (int) auth()->id(),
            $v['motivo']
        );

        if ($r->expectsJson()) {
            return response()->json(['success' => true, 'resultado' => $res]);
        }

        return back()->with('success', 'Decisión de conciliación histórica registrada con auditoría.');
    }

    public function antecedentesEgresado(Request $r, int $egresado)
    {
        abort_unless($this->acceso->puedeConsultarEgresado($egresado), 403);
        $userObj = User::findOrFail($egresado);
        $servicio = app(\App\Services\SeguimientoEgresado\ConciliacionHistoricoService::class);

        return response()->json([
            'user_id' => $userObj->id,
            'hitos_oficiales' => $servicio->obtenerHitosOficialesSeparados($userObj),
            'antecedentes_seguimiento' => $servicio->obtenerAntecedentesPersona($userObj->id, auth()->user()),
        ]);
    }
}
