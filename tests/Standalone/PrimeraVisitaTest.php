<?php
namespace Illuminate\Http {
 class Request {public function __construct(public $usuario,public string $ruta,public bool $json=false){}public function user(){return $this->usuario;}public function routeIs(...$patrones){foreach($patrones as $p)if(fnmatch($p,$this->ruta))return true;return false;}public function expectsJson(){return $this->json;}}
}
namespace Illuminate\Support\Facades {
 class Schema {public static function hasTable($t){return true;}}
 class DB {public static bool $confirmado=false;public static function table($t){return new self;}public function where(...$v){return $this;}public function whereNotNull($v){return $this;}public function exists(){return self::$confirmado;}}
}
namespace {
 require __DIR__.'/../../app/Http/Middleware/ConfirmarPerfilEgresado.php';
 function data_get($p,$ruta){foreach(explode('.',$ruta) as $k)$p=$p[$k]??null;return $p;}
 function route($r){return $r;}
 function app($s){return new class {public function actual($id){return $GLOBALS['perfil'];}};}
 function redirect(){return new class {public function route($r){return $this;}public function with($k,$v){return 'redirige-perfil';}};}
 function response(){return new class {public function json($v,$status){return $status;}};}
 class Usuario {public int $id=20;public function __construct(public string $condicion){}public function hasRole($r){return false;}}
 $GLOBALS['perfil']=['contacto'=>['programa'=>'Inicial','egreso'=>2025,'correo'=>'prueba@example.test']];
 $m=new App\Http\Middleware\ConfirmarPerfilEgresado;
 $eg=new Usuario('Egresado');$prof=new Usuario('Docente');$siguiente=fn($r)=>'continua';$n=0;
 function verificar($real,$esperado,$nombre){global $n;if($real!==$esperado)throw new RuntimeException($nombre);echo "OK: $nombre\n";$n++;}
 verificar($m->handle(new Illuminate\Http\Request($eg,'ayllu.inicio'),$siguiente),'redirige-perfil','primera visita a comunidad exige confirmación');
 verificar($m->handle(new Illuminate\Http\Request($eg,'egresado.intranet'),$siguiente),'redirige-perfil','primera visita de egresado exige confirmación');
 verificar($m->handle(new Illuminate\Http\Request($eg,'seguimiento.mi-espacio'),$siguiente),'continua','el propio formulario sigue accesible');
 verificar($m->handle(new Illuminate\Http\Request($prof,'vistaDocente'),$siguiente),'continua','no se bloquea la docencia institucional');
 verificar($m->handle(new Illuminate\Http\Request($eg,'ayllu.publicaciones.store',true),$siguiente),409,'no se elude la confirmación mediante publicación AJAX');
 Illuminate\Support\Facades\DB::$confirmado=true;
 verificar($m->handle(new Illuminate\Http\Request($eg,'ayllu.inicio'),$siguiente),'continua','núcleo confirmado permite comunidad sin respuestas opcionales');
 $GLOBALS['perfil']['contacto']['correo']='';
 verificar($m->handle(new Illuminate\Http\Request($eg,'ayllu.inicio'),$siguiente),'redirige-perfil','una versión sin correo no satisface primera confirmación');
 verificar($m->handle(new Illuminate\Http\Request(null,'ayllu.inicio'),$siguiente),'continua','el acceso de invitados queda a cargo de auth');
 echo "$n pruebas superadas.\n";
}
