<?php
// Ejecuta el servicio real, sustituyendo solo identidad y repositorios de asignación.
namespace App\Models {
 class User {
  public function __construct(public int $id,public array $roles=[],public array $permisos=[],public ?string $condicion=null){}
  public function hasRole($r):bool{return in_array($r,$this->roles,true);}
  public function hasAnyRole($rs):bool{return count(array_intersect($rs,$this->roles))>0;}
  public function can($p):bool{return in_array($p,$this->permisos,true);}
 }
}
namespace App\Models\SeguimientoEgresado {
 class AsignacionCampana {public static array $vigentes=[];public static function esDocenteAsignado($actor,$titular,$campana=null):bool{return in_array([$actor,$titular],self::$vigentes,true);}}
 class GrupoEntrevistador {public static function esEntrevistadorAsignado($actor,$titular):bool{return false;}}
}
namespace {
 require __DIR__.'/../../app/Services/SeguimientoEgresado/SeguimientoAutorizacionService.php';
 use App\Models\User;
 use App\Models\SeguimientoEgresado\AsignacionCampana;
 $s=new App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
 $pruebas=0;
 function verificar($condicion,$nombre){global $pruebas;if(!$condicion)throw new RuntimeException($nombre);echo "OK: $nombre\n";$pruebas++;}
 $doc=new User(10,['docente','seguimiento-egresados']);
 verificar(!$s->esPersonalGestion($doc),'ser entrevistador no concede coordinación');
 verificar(!$s->puedeModificarEgresado(20,$doc),'sin asignación vigente no se edita');
 verificar(!$s->puedeRegistrarLlamada(20,$doc),'sin asignación vigente no se llama');
 AsignacionCampana::$vigentes=[[10,20]];
 verificar($s->puedeConsultarEgresado(20,$doc),'docente asignado consulta su egresado');
 verificar($s->puedeModificarEgresado(20,$doc),'entrevistador asignado completa el mismo formulario');
 verificar($s->puedeRegistrarLlamada(20,$doc),'entrevistador asignado registra llamada');
 verificar(!$s->puedeConsultarEgresado(21,$doc),'otro egresado permanece fuera del alcance');
 AsignacionCampana::$vigentes=[];
 verificar(!$s->puedeModificarEgresado(20,$doc),'al caducar asignación desaparece edición');
 $coord=new User(10,['docente','seguimiento-egresados'],['seguimiento-egresados.gestionar-campanas','seguimiento-egresados.gestionar-grupos']);
 verificar($s->esPersonalGestion($coord),'docente puede coordinar con permisos institucionales');
 verificar($s->esDocenteOEntrevistador($coord),'coordinar no elimina función de entrevistador');
 verificar(!$s->esPersonalGestion(new User(30,['adminB'])),'bolsa de trabajo no hereda coordinación');
 verificar(!$s->esPersonalGestion(new User(31,['docente'],['seguimiento-egresados.gestionar-campanas'])),'un permiso parcial no concede gestión completa');
 verificar($s->esPersonalGestion(new User(1,['super-admin'])),'se conserva jerarquía superadministrador');
 $eg=new User(20,['egresado'],[],'Egresado');
 verificar($s->puedeModificarEgresado(20,$eg),'egresado edita sus datos');
 verificar(!$s->puedeRegistrarLlamada(21,$eg),'egresado no registra llamadas ajenas');
 verificar(!$s->puedeVerNotasConfidencialesBienestar($doc),'entrevistador no lee notas de bienestar');
 echo "$pruebas pruebas superadas.\n";
}
