<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class ConfirmarPerfilEgresado {
 public function handle(Request $r,Closure $next) {
  $u=$r->user();
  if($u && ($u->condicion==='Egresado'||$u->hasRole('egresado')) && $r->routeIs('egresado.intranet','ayllu.*')) {
   $confirmado=Schema::hasTable('egresado_perfil_versiones') && DB::table('egresado_perfil_versiones')->where('user_id',$u->id)->where('registrado_por',$u->id)->whereNotNull('confirmado_at')->exists();
   if($confirmado) { $datos=app(\App\Services\SeguimientoEgresado\PerfilTerritorialService::class)->actual($u->id); foreach(['contacto.programa','contacto.egreso','contacto.correo'] as $campo) if(!data_get($datos,$campo)) $confirmado=false; }
   if(!$confirmado) {
    if($r->expectsJson()) return response()->json(['message'=>'Confirma tus datos para ingresar a la comunidad.','redirect'=>route('seguimiento.mi-espacio')],409);
    return redirect()->route('seguimiento.mi-espacio')->with('success','Te damos la bienvenida. Confirma tus datos principales para continuar; las preguntas opcionales pueden esperar.');
   }
  }
  return $next($r);
 }
}
