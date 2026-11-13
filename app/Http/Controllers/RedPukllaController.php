<?php
namespace App\Http\Controllers;
use App\Models\Ayllu\{AylluPublicacion,AylluGrupo,AylluComentario,AylluMensajeChat,AylluEventoCalendario};
use App\Models\{User,BolsaTrabajoOferta};
use App\Services\RedPuklla\{AccesoRed,ContenidoRed};
use App\Services\SeguimientoEgresado\{GrupoFormacionService,PerfilTerritorialService};
use App\Notifications\RedPukllaAviso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RedPukllaController extends Controller {
 public function __construct(private AccesoRed $acceso){
  $this->middleware('auth');
  $this->middleware(function($r,$next){abort_unless($this->acceso->entrar($r->user()),403);abort_unless(Schema::hasColumn('ayllu_publicaciones','red_egresados')&&Schema::hasTable('red_puklla_guardados'),503,'Ejecuta las migraciones de Red Puklla antes de activar.');return $next($r);});
 }
 private function aviso(int $persona,string $mensaje,string $tipo,int $id):void {
  if($persona===(int)auth()->id())return;$pref=DB::table('red_puklla_preferencias')->where('user_id',$persona)->value('avisos');if($pref!==null&&!(bool)$pref)return;
  User::find($persona)?->notify(new RedPukllaAviso($mensaje,$tipo,$id));
 }
 private function avisarGrupo(AylluGrupo $g,string $mensaje):void{foreach($g->miembros()->wherePivot('estado_red','aceptado')->wherePivot('avisos_red',true)->pluck('users.id') as $id)$this->aviso($id,$mensaje,'grupo',$g->id);}
 private function enlace(?string $url):?string{try{return ContenidoRed::enlace($url)['url']??null;}catch(\InvalidArgumentException $e){throw ValidationException::withMessages(['enlace_externo'=>$e->getMessage()]);}}
 private function etiqueta(?User $u):string{
  if(!$u)return 'Comunidad';if($u->condicion==='Egresado'||$u->hasRole('egresado')){$u->loadMissing(GrupoFormacionService::RELACIONES);return app(GrupoFormacionService::class)->resolver($u)['etiqueta'];}return $u->hasRole('docente')?'Docente':'Equipo EESP';
 }
 private function etiquetar($posts):void{foreach($posts as $p)$p->setAttribute('etiqueta_autor_red',$this->etiqueta($p->autor));}
 private function base(array $datos=[]):array{
  $u=auth()->user();$grupos=AylluGrupo::deRedPuklla()->where('activo',true)->withCount(['miembros'=>fn($q)=>$q->where('ayllu_grupo_miembros.estado_red','aceptado')])->orderBy('nombre')->get();
  $misIds=DB::table('ayllu_grupo_miembros')->where('user_id',$u->id)->where('estado_red','aceptado')->pluck('ayllu_grupo_id');
  return $datos+['grupos'=>$grupos,'misGrupos'=>$grupos->whereIn('id',$misIds),'puedeModerar'=>$this->acceso->moderar($u),'categorias'=>ContenidoRed::CATEGORIAS,'intenciones'=>ContenidoRed::INTENCIONES];
 }
 public function inicio(Request $r){
  $r->validate(['categoria'=>'nullable|in:'.implode(',',array_keys(ContenidoRed::CATEGORIAS)),'grupo_formacion'=>'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR','buscar'=>'nullable|string|max:100']);
  $grupoFormacion=$r->query('grupo_formacion','TODOS');$q=$this->acceso->publicaciones($r->user());
  if($r->filled('categoria'))$q->where('categoria_red',$r->categoria);
  if($r->filled('buscar'))$q->where(fn($q)=>$q->where('titulo','like','%'.$r->buscar.'%')->orWhere('contenido','like','%'.$r->buscar.'%'));
  $clasificador=app(GrupoFormacionService::class);
  if($grupoFormacion==='COMPARAR'){$ids=$clasificador->aplicar(User::where('condicion','Egresado'),'TODOS')->get()->filter(fn($u)=>(bool)array_intersect(['FID','PPD'],$clasificador->resolver($u)['grupos']))->pluck('id');$q->whereIn('user_id',$ids);}
  elseif($grupoFormacion!=='TODOS')$q->whereIn('user_id',$clasificador->aplicar(User::where('condicion','Egresado'),$grupoFormacion)->pluck('id'));
  $publicaciones=$q->with('autor')->withCount('comentarios')->latest()->paginate(15)->withQueryString();$this->etiquetar($publicaciones);$datos=$this->base(compact('publicaciones','grupoFormacion'));
  $ids=$datos['grupos']->filter(fn($g)=>$this->acceso->grupo($r->user(),$g))->pluck('id');
  $eventos=AylluEventoCalendario::deRedPuklla()->whereIn('ayllu_grupo_id',$ids)->where('fecha_inicio','>=',now()->toDateString())->orderBy('fecha_inicio')->limit(5)->get();
  $actividad=$this->acceso->publicaciones($r->user())->whereIn('ayllu_grupo_id',$ids)->selectRaw('ayllu_grupo_id, MAX(created_at) as ultima')->groupBy('ayllu_grupo_id')->pluck('ultima','ayllu_grupo_id');
  $activos=$datos['grupos']->whereIn('id',$actividad->keys())->sortByDesc(fn($g)=>$actividad[$g->id])->take(5);
  return view('red_puklla.inicio',$datos+compact('activos','eventos'));
 }
 public function publicar(Request $r){
  $v=$r->validate(['titulo'=>'required|string|max:180','contenido'=>'required|string|max:10000','resumen_quechua'=>'nullable|string|max:2000','categoria_red'=>'required|in:'.implode(',',array_keys(ContenidoRed::CATEGORIAS)),'intencion_red'=>'required|in:'.implode(',',array_keys(ContenidoRed::INTENCIONES)),'enlace_externo'=>'nullable|string|max:500','ayllu_grupo_id'=>'nullable|integer']);
  $v['enlace_externo']=$this->enlace($v['enlace_externo']??null);
  if(!empty($v['ayllu_grupo_id'])){$g=AylluGrupo::deRedPuklla()->findOrFail($v['ayllu_grupo_id']);abort_unless($this->acceso->grupo($r->user(),$g,true),403);}
  $p=DB::transaction(function()use($v,$r){$p=AylluPublicacion::create($v+['user_id'=>$r->user()->id,'ambito'=>empty($v['ayllu_grupo_id'])?'institucional':'grupo','tipo'=>'reflexion','red_egresados'=>true,'estado_red'=>'visible','visible'=>true]);if($p->ayllu_grupo_id)$this->avisarGrupo(AylluGrupo::deRedPuklla()->findOrFail($p->ayllu_grupo_id),'Hay una nueva conversación en uno de tus ayllus.');return $p;});
  return redirect()->route('red.publicacion',['id'=>$p->id])->with('success','Publicación compartida.');
 }
 public function publicacion(int $id){
  $p=AylluPublicacion::deRedPuklla()->with(['autor','comentarios.autor'])->findOrFail($id);abort_unless($this->acceso->publicacion(auth()->user(),$p,true),404);$p->setAttribute('etiqueta_autor_red',$this->etiqueta($p->autor));
  $puedeParticipar=$this->acceso->interactuar(auth()->user(),$p);$acciones=DB::table('red_puklla_acciones')->join('users','users.id','=','red_puklla_acciones.user_id')->where('publicacion_id',$id)->get(['red_puklla_acciones.*','users.name','users.apellidos']);
  $moderaciones=DB::table('red_puklla_moderacion')->where('tipo','publicacion')->where('referencia_id',$id)->where('accion','!=','reportar')->latest()->get();
  return view('red_puklla.publicacion',$this->base(compact('p','acciones','puedeParticipar','moderaciones')));
 }
 public function editarPublicacion(Request $r,int $id){
  $v=$r->validate(['titulo'=>'required|string|max:180','contenido'=>'required|string|max:10000','resumen_quechua'=>'nullable|string|max:2000','enlace_externo'=>'nullable|string|max:500','retirar'=>'nullable|boolean']);$v['enlace_externo']=$this->enlace($v['enlace_externo']??null);
  DB::transaction(function()use($v,$r,$id){$p=AylluPublicacion::deRedPuklla()->lockForUpdate()->findOrFail($id);abort_unless((int)$p->user_id===(int)$r->user()->id&&$this->acceso->interactuar($r->user(),$p),403);$retirar=!empty($v['retirar']);unset($v['retirar']);$p->fill($v);if($retirar){$p->estado_red='retirado';$p->visible=false;DB::table('red_puklla_moderacion')->insert(['tipo'=>'publicacion','referencia_id'=>$id,'actor_id'=>$r->user()->id,'accion'=>'retirar','motivo'=>'Retirada por su autor.','created_at'=>now(),'updated_at'=>now()]);}$p->save();});
  return back()->with('success','Publicación actualizada. Se conserva su audiencia, categoría e intención.');
 }
 public function comentar(Request $r,int $id){
  $p=AylluPublicacion::deRedPuklla()->findOrFail($id);abort_unless($this->acceso->interactuar($r->user(),$p),404);$v=$r->validate(['contenido'=>'required|string|max:4000']);AylluComentario::create($v+['ayllu_publicacion_id'=>$id,'user_id'=>$r->user()->id,'es_aporte_docente'=>false]);$this->aviso($p->user_id,'Hay un nuevo aporte a tu publicación.','publicacion',$id);return back()->with('success','Aporte compartido.');
 }
 public function accion(Request $r,int $id){
  $p=AylluPublicacion::deRedPuklla()->findOrFail($id);abort_unless($this->acceso->interactuar($r->user(),$p),404);$v=$r->validate(['aporte'=>'required|string|max:2000','estado'=>'required|in:ofrecido,en_curso,realizado,retirado']);
  DB::table('red_puklla_acciones')->updateOrInsert(['publicacion_id'=>$id,'user_id'=>$r->user()->id],$v+['accion'=>$p->intencion_red,'created_at'=>now(),'updated_at'=>now()]);$this->aviso($p->user_id,'Hay una propuesta de colaboración en tu publicación.','publicacion',$id);return back()->with('success','Participación registrada.');
 }
 public function difundir(Request $r,int $id){
  $p=AylluPublicacion::deRedPuklla()->findOrFail($id);abort_unless($this->acceso->publicacion($r->user(),$p),404);$r->validate(['grupo_id'=>'required|integer']);$g=AylluGrupo::deRedPuklla()->findOrFail($r->grupo_id);abort_unless($this->acceso->grupo($r->user(),$g,true),403);
  if($p->ayllu_grupo_id){$origen=AylluGrupo::deRedPuklla()->findOrFail($p->ayllu_grupo_id);abort_unless($origen->privacidad_red==='abierto'||$origen->id===$g->id,403,'No se difunde contenido privado fuera de su ayllu.');}
  DB::table('red_puklla_difusiones')->updateOrInsert(['publicacion_id'=>$id,'ayllu_grupo_id'=>$g->id],['user_id'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Compartido por referencia; se conserva la conversación original.');
 }
 public function grupos(){return view('red_puklla.grupos',$this->base());}
 public function crearGrupo(Request $r){
  $v=$r->validate(['nombre'=>'required|string|max:140','descripcion'=>'required|string|max:1000','tipo'=>'required|in:intercultural,circulo_estudio,taller,promocion','promocion_red'=>'required_if:tipo,promocion|nullable|integer|min:1950|max:'.date('Y'),'privacidad_red'=>'required|in:abierto,privado','origen_publicacion_id'=>'nullable|integer']);
  if(!empty($v['origen_publicacion_id'])){$p=AylluPublicacion::deRedPuklla()->findOrFail($v['origen_publicacion_id']);abort_unless($this->acceso->publicacion($r->user(),$p),404);}
  $g=DB::transaction(function()use($v,$r){$g=AylluGrupo::create($v+['slug'=>Str::slug($v['nombre']).'-'.Str::lower(Str::random(10)),'creado_por'=>$r->user()->id,'activo'=>true,'es_oficial'=>false,'red_egresados'=>true]);$g->miembros()->attach($r->user()->id,['rol'=>'moderador','estado_red'=>'aceptado']);return $g;});return redirect()->route('red.grupo',['id'=>$g->id])->with('success','Ayllu creado.');
 }
 public function grupo(int $id){
  $g=AylluGrupo::deRedPuklla()->findOrFail($id);abort_unless($g->activo,404);$u=auth()->user();$participa=$this->acceso->grupo($u,$g,true);$puedeVer=$this->acceso->grupo($u,$g);$facilita=$this->acceso->facilitar($u,$g);
  $publicaciones=$puedeVer?$this->acceso->publicaciones($u)->where(fn($q)=>$q->where('ayllu_grupo_id',$id)->orWhereIn('id',DB::table('red_puklla_difusiones')->where('ayllu_grupo_id',$id)->select('publicacion_id')))->with('autor')->withCount('comentarios')->latest()->paginate(12):null;if($publicaciones)$this->etiquetar($publicaciones);
  $miembros=$puedeVer?$g->miembros()->wherePivot('estado_red','aceptado')->get(['users.id','users.name','users.apellidos','users.condicion','users.programa_id']):collect();foreach($miembros as $m)$m->setAttribute('etiqueta_red',$this->etiqueta($m));
  $pendientes=$facilita?$g->miembros()->wherePivot('estado_red','pendiente')->get(['users.id','users.name','users.apellidos']):collect();
  $mensajes=$participa?AylluMensajeChat::where('canal_tipo','grupo')->where('ayllu_grupo_id',$id)->with('emisor')->latest()->limit(60)->get()->reverse():collect();
  $eventos=$puedeVer?AylluEventoCalendario::deRedPuklla()->where('ayllu_grupo_id',$id)->orderBy('fecha_inicio')->get():collect();
  $aportes=$puedeVer?DB::table('red_puklla_acciones')->whereIn('publicacion_id',$this->acceso->publicaciones($u)->where('ayllu_grupo_id',$id)->select('id'))->get()->groupBy('user_id'):collect();
  $recursosPorMiembro=$puedeVer?$this->acceso->publicaciones($u)->where('ayllu_grupo_id',$id)->latest()->get(['id','user_id','titulo'])->groupBy('user_id'):collect();
  $avisosGrupo=$participa?(bool)DB::table('ayllu_grupo_miembros')->where('ayllu_grupo_id',$id)->where('user_id',$u->id)->value('avisos_red'):false;
  return view('red_puklla.grupo',$this->base(compact('g','participa','puedeVer','facilita','publicaciones','miembros','pendientes','mensajes','eventos','aportes','recursosPorMiembro','avisosGrupo')));
 }
 public function membresia(Request $r,int $id){
  $g=AylluGrupo::deRedPuklla()->findOrFail($id);abort_unless($g->activo,404);$r->validate(['accion'=>'required|in:unir,salir,aceptar,rechazar,avisos','user_id'=>'nullable|integer','avisos_red'=>'nullable|boolean']);
  DB::transaction(function()use($g,$r){AylluGrupo::deRedPuklla()->whereKey($g->id)->lockForUpdate()->first();$actor=$r->user()->id;
   if($r->accion==='avisos'){abort_unless($this->acceso->miembro($r->user(),$g),403);$g->miembros()->updateExistingPivot($actor,['avisos_red'=>$r->boolean('avisos_red')]);}
   elseif(in_array($r->accion,['aceptar','rechazar'])){abort_unless($this->acceso->facilitar($r->user(),$g),403);abort_unless($g->miembros()->where('users.id',$r->user_id)->wherePivot('estado_red','pendiente')->exists(),404);if($r->accion==='aceptar'){$g->miembros()->updateExistingPivot($r->user_id,['estado_red'=>'aceptado']);$this->aviso((int)$r->user_id,'Tu solicitud de ingreso fue aceptada.','grupo',$g->id);}else $g->miembros()->detach($r->user_id);}
   elseif($r->accion==='salir'){abort_unless(!$this->acceso->facilitar($r->user(),$g),422,'El facilitador debe conservar su membresía.');$g->miembros()->detach($actor);}
   elseif(!$g->miembros()->where('users.id',$actor)->exists()){$g->miembros()->attach($actor,['rol'=>'miembro','estado_red'=>$g->privacidad_red==='abierto'?'aceptado':'pendiente']);$this->aviso($g->creado_por,'Hay una nueva participación o solicitud en tu ayllu.','grupo',$g->id);}
  });return back()->with('success',$r->accion==='avisos'?'Preferencia guardada.':'Membresía actualizada; los ayllus privados requieren aceptación.');
 }
 public function mensaje(Request $r,int $id){$g=AylluGrupo::deRedPuklla()->findOrFail($id);abort_unless($this->acceso->grupo($r->user(),$g,true),403);$v=$r->validate(['mensaje'=>'required|string|max:3000']);AylluMensajeChat::create($v+['canal_tipo'=>'grupo','ayllu_grupo_id'=>$id,'emisor_user_id'=>$r->user()->id]);return back()->with('success','Mensaje enviado.');}
 public function evento(Request $r,int $id){$g=AylluGrupo::deRedPuklla()->findOrFail($id);abort_unless($this->acceso->grupo($r->user(),$g,true),403);$v=$r->validate(['titulo'=>'required|string|max:180','descripcion'=>'nullable|string|max:1000','fecha_inicio'=>'required|date|after_or_equal:today','hora'=>'nullable|date_format:H:i','enlace'=>'nullable|string|max:500']);$v['enlace']=$this->enlace($v['enlace']??null);AylluEventoCalendario::create($v+['red_egresados'=>true,'ayllu_grupo_id'=>$id,'creado_por'=>$r->user()->id,'tipo'=>'comunitario']);$this->avisarGrupo($g,'Hay un nuevo encuentro en uno de tus ayllus.');return back()->with('success','Encuentro propuesto.');}
 private function validarOferta(Request $r,bool $editar=false):array{
  $v=$r->validate(['nombre'=>'required|string|max:180','institucion_red'=>'required|string|max:180','detalles'=>'required|string|max:10000','tipo_red'=>'required|in:trabajo,formacion,convocatoria','fuente_red'=>'required|in:ofrece,comparte','enlace_red'=>'nullable|string|max:500','correo_red'=>'nullable|email|max:150','telefono_publicador'=>'nullable|string|max:30|regex:/^\+?[0-9][0-9 ()-]{5,28}[0-9]$/','lugar_red'=>'required|string|max:200','plazo_red'=>$editar?'nullable|date':'nullable|date|after_or_equal:today','situacion_red'=>$editar?'required|in:abierta,cerrada':'sometimes|in:abierta']);
  $v['enlace_red']=$this->enlace($v['enlace_red']??null);if(empty($v['enlace_red'])&&empty($v['correo_red'])&&empty($v['telefono_publicador']))throw ValidationException::withMessages(['enlace_red'=>'Indica un medio de contacto o postulación.']);return $v;
 }
 public function oportunidades(Request $r){$r->validate(['tipo'=>'nullable|in:trabajo,formacion,convocatoria','buscar'=>'nullable|string|max:100']);$q=BolsaTrabajoOferta::deRedPuklla()->where('estado_red','visible');if($r->filled('tipo'))$q->where('tipo_red',$r->tipo);if($r->filled('buscar'))$q->where('nombre','like','%'.$r->buscar.'%');return view('red_puklla.oportunidades',$this->base(['ofertas'=>$q->latest()->paginate(15)->withQueryString()]));}
 public function crearOportunidad(Request $r){$v=$this->validarOferta($r);$o=BolsaTrabajoOferta::create($v+['autor_red_id'=>$r->user()->id,'red_egresados'=>true,'estado_red'=>'visible','nombre_publicador'=>trim($r->user()->name.' '.$r->user()->apellidos),'relacion_publicador'=>$v['fuente_red']==='ofrece'?'Ofrece una oportunidad':'Comparte una fuente']);return redirect()->route('red.oportunidad',['id'=>$o->id])->with('success','Oportunidad compartida.');}
 public function editarOportunidad(Request $r,int $id){$o=BolsaTrabajoOferta::deRedPuklla()->findOrFail($id);abort_unless((int)$o->autor_red_id===(int)$r->user()->id&&$o->estado_red==='visible',403);$o->update($this->validarOferta($r,true));foreach(DB::table('red_puklla_guardados')->where('tipo','oportunidad')->where('referencia_id',$id)->pluck('user_id')->unique() as $persona)$this->aviso($persona,'Una oportunidad que guardaste fue actualizada.','oportunidad',$id);return back()->with('success','Oportunidad actualizada.');}
 public function oportunidad(int $id){$oferta=BolsaTrabajoOferta::deRedPuklla()->findOrFail($id);abort_unless($oferta->estado_red==='visible'||$this->acceso->moderar(auth()->user())||(int)$oferta->autor_red_id===(int)auth()->id(),404);$moderaciones=DB::table('red_puklla_moderacion')->where('tipo','oportunidad')->where('referencia_id',$id)->where('accion','!=','reportar')->latest()->get();return view('red_puklla.oportunidad',$this->base(compact('oferta','moderaciones')));}
 public function guardar(Request $r){
  $v=$r->validate(['tipo'=>'required|in:publicacion,oportunidad','referencia_id'=>'required|integer','estado'=>'required|in:guardado,me_interesa,contacte,postule,finalizada,quitar']);$key=['user_id'=>$r->user()->id,'tipo'=>$v['tipo'],'referencia_id'=>$v['referencia_id']];
  if($v['estado']==='quitar'){DB::table('red_puklla_guardados')->where($key)->delete();return back()->with('success','Quitado de tus guardados.');}
  if($v['tipo']==='publicacion'){$p=AylluPublicacion::deRedPuklla()->findOrFail($v['referencia_id']);abort_unless($this->acceso->publicacion($r->user(),$p),404);}else{$o=BolsaTrabajoOferta::deRedPuklla()->findOrFail($v['referencia_id']);abort_unless($o->estado_red==='visible',404);}
  DB::table('red_puklla_guardados')->updateOrInsert($key,['estado'=>$v['estado'],'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Seguimiento privado guardado.');
 }
 public function espacio(){
  $guardados=DB::table('red_puklla_guardados')->where('user_id',auth()->id())->latest()->get()->map(function($g){$obj=$g->tipo==='publicacion'?AylluPublicacion::deRedPuklla()->find($g->referencia_id):BolsaTrabajoOferta::deRedPuklla()->find($g->referencia_id);$visible=$obj&&($g->tipo==='publicacion'?$this->acceso->publicacion(auth()->user(),$obj):$obj->estado_red==='visible');$g->disponible=$visible;$g->titulo=$visible?($obj->titulo??$obj->nombre):'Contenido ya no disponible para tu cuenta';return $g;});
  $avisos=auth()->user()->notifications()->where('type',RedPukllaAviso::class)->latest()->limit(30)->get();$misPublicaciones=AylluPublicacion::deRedPuklla()->where('user_id',auth()->id())->latest()->limit(20)->get();$misOfertas=BolsaTrabajoOferta::deRedPuklla()->where('autor_red_id',auth()->id())->latest()->limit(20)->get();$recibirAvisos=DB::table('red_puklla_preferencias')->where('user_id',auth()->id())->value('avisos')??true;
  $avancePerfil=null;if((auth()->user()->condicion==='Egresado'||auth()->user()->hasRole('egresado'))&&Schema::hasTable('egresado_perfil_versiones')){$servicio=app(PerfilTerritorialService::class);$avancePerfil=$servicio->avance($servicio->actual(auth()->id()));}
  return view('red_puklla.espacio',$this->base(compact('guardados','avisos','misPublicaciones','misOfertas','recibirAvisos','avancePerfil')));
 }
 public function avisos(Request $r){$v=$r->validate(['avisos'=>'required|boolean']);DB::table('red_puklla_preferencias')->updateOrInsert(['user_id'=>$r->user()->id],$v+['created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Preferencia guardada.');}
 public function leerAviso(string $id){$aviso=auth()->user()->notifications()->where('type',RedPukllaAviso::class)->findOrFail($id);$aviso->markAsRead();$d=$aviso->data;return redirect()->route($d['tipo']==='grupo'?'red.grupo':($d['tipo']==='oportunidad'?'red.oportunidad':'red.publicacion'),['id'=>$d['referencia']]);}
 public function moderar(\Illuminate\Http\Request $request)
 {
     $datos = $request->validate([
         'tipo' => 'required|in:publicacion,oportunidad',
         'referencia_id' => 'required|integer|min:1',
         'accion' => 'required|in:reportar,ocultar,retirar,restaurar,solicitar_revision',
         'motivo' => 'required|string|min:10|max:2000',
     ]);

     \Illuminate\Support\Facades\DB::transaction(function () use ($request, $datos) {
         $usuario = $request->user();

         $modelo = $datos['tipo'] === 'publicacion'
             ? \App\Models\Ayllu\AylluPublicacion::class
             : \App\Models\BolsaTrabajoOferta::class;

         $contenido = $modelo::deRedPuklla()
             ->lockForUpdate()
             ->findOrFail($datos['referencia_id']);

         $autorId = (int) (
             $datos['tipo'] === 'publicacion'
                 ? $contenido->user_id
                 : $contenido->autor_red_id
         );

         if ($datos['accion'] === 'reportar') {
             $puedeReportar = $datos['tipo'] === 'publicacion'
                 ? $this->acceso->publicacion($usuario, $contenido)
                 : $contenido->estado_red === 'visible';

             abort_unless($puedeReportar, 404);
         } elseif ($datos['accion'] === 'solicitar_revision') {
             abort_unless((int) $usuario->id === $autorId, 403);

             abort_unless(
                 in_array($contenido->estado_red, ['oculto', 'retirado'], true),
                 422,
                 'Solo se solicita revisión de contenidos ocultos o retirados.'
             );

             // Solicitar revisión nunca vuelve a publicar el contenido.
         } else {
             // Autorizar en servidor, también en ayllus privados o inactivos.
             abort_unless($this->acceso->moderar($usuario), 403);

             $contenido->estado_red = match ($datos['accion']) {
                 'restaurar' => 'visible',
                 'retirar' => 'retirado',
                 default => 'oculto',
             };

             if ($datos['tipo'] === 'publicacion') {
                 $contenido->visible = $contenido->estado_red === 'visible';
             }

             $contenido->save();

             $autor = \App\Models\User::find($autorId);

             if ($autor) {
                 $mensaje = sprintf(
                     'Tu contenido fue revisado. Estado: %s. Motivo: %s',
                     $contenido->estado_red,
                     $datos['motivo']
                 );

                 // Aviso obligatorio de moderación, independiente de los
                 // avisos comunitarios opcionales. Solo notificación interna.
                 $autor->notify(
                     new \App\Notifications\RedPukllaAviso(
                         $mensaje,
                         $datos['tipo'],
                         $contenido->id
                     )
                 );
             }
         }

         \Illuminate\Support\Facades\DB::table('red_puklla_moderacion')->insert([
             'tipo' => $datos['tipo'],
             'referencia_id' => $contenido->id,
             'actor_id' => $usuario->id,
             'accion' => $datos['accion'],
             'motivo' => $datos['motivo'],
             'created_at' => now(),
             'updated_at' => now(),
         ]);
     });

     return back()->with(
         'success',
         match ($datos['accion']) {
             'reportar' => 'Reporte enviado para revisión.',
             'solicitar_revision' => 'Solicitud de revisión registrada.',
             'restaurar' => 'Contenido restaurado con trazabilidad.',
             default => 'Contenido retirado de circulación con motivo registrado.',
         }
     );
 }
 public function moderacion(){abort_unless($this->acceso->moderar(auth()->user()),403);return view('red_puklla.moderacion',$this->base(['registros'=>DB::table('red_puklla_moderacion')->latest()->paginate(30)]));}
}
