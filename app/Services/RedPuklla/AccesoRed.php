<?php
namespace App\Services\RedPuklla;
use App\Models\User;
use App\Models\Ayllu\{AylluGrupo,AylluPublicacion};
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
class AccesoRed {
 public function entrar(User $u):bool{return ContenidoRed::elegible($u->condicion,$u->getRoleNames()->all(),app(SeguimientoAutorizacionService::class)->esPersonalGestion($u));}
 public function moderar(User $u):bool{return $this->entrar($u)&&($u->can('ayllu.moderar')||app(SeguimientoAutorizacionService::class)->esPersonalGestion($u));}
 public function miembro(User $u,AylluGrupo $g):bool{return $g->miembros()->where('users.id',$u->id)->wherePivot('estado_red','aceptado')->exists();}
 public function facilitar(User $u,AylluGrupo $g):bool{return (int)$g->creado_por===(int)$u->id;}
 public function grupo(User $u,AylluGrupo $g,bool $participar=false):bool{return $this->entrar($u)&&$g->red_egresados&&$g->activo&&($this->miembro($u,$g)||(!$participar&&($g->privacidad_red==='abierto'||$this->moderar($u))));}
 public function publicacion(
     \App\Models\User $u,
     \App\Models\Ayllu\AylluPublicacion $p,
     bool $oculta = false
 ): bool {
     if (!$this->entrar($u) || !$p->red_egresados) {
         return false;
     }

     // La revisión autorizada no depende de la membresía ni de que
     // el ayllu continúe activo.
     if ($oculta && $this->moderar($u)) {
         return true;
     }

     if (
         (!$p->visible || $p->estado_red !== 'visible')
         && !($oculta && (int) $p->user_id === (int) $u->id)
     ) {
         return false;
     }

     if (!$p->ayllu_grupo_id) {
         return true;
     }

     $grupo = \App\Models\Ayllu\AylluGrupo::deRedPuklla()
         ->find($p->ayllu_grupo_id);

     return $grupo && $this->grupo($u, $grupo);
 }
 public function interactuar(User $u,AylluPublicacion $p):bool{if(!$this->publicacion($u,$p))return false;if(!$p->ayllu_grupo_id)return true;$g=AylluGrupo::deRedPuklla()->find($p->ayllu_grupo_id);return $g&&$this->grupo($u,$g,true);}
 public function publicaciones(User $u) {
  $grupos=AylluGrupo::deRedPuklla()->where('activo',true);
  if(!$this->moderar($u))$grupos->where(fn($q)=>$q->where('privacidad_red','abierto')->orWhereHas('miembros',fn($m)=>$m->where('users.id',$u->id)->where('ayllu_grupo_miembros.estado_red','aceptado')));
  return AylluPublicacion::deRedPuklla()->where('visible',true)->where('estado_red','visible')->where(fn($q)=>$q->whereNull('ayllu_grupo_id')->orWhereIn('ayllu_grupo_id',$grupos->pluck('id')));
 }
}
