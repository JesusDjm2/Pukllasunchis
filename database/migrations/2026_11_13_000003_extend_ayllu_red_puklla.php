<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  foreach(['ayllu_publicaciones','ayllu_grupos','ayllu_eventos_calendario','bolsa_trabajo_ofertas'] as $tabla)if(Schema::hasTable($tabla)&&!Schema::hasColumn($tabla,'red_egresados'))Schema::table($tabla,fn(Blueprint $t)=>$t->boolean('red_egresados')->default(false)->index());
  $campos=[
   'ayllu_publicaciones'=>['categoria_red'=>['string',20,null],'intencion_red'=>['string',30,null],'estado_red'=>['string',20,'visible']],
   'ayllu_grupos'=>['privacidad_red'=>['string',20,'abierto'],'origen_publicacion_id'=>['unsignedBigInteger',null,null],'promocion_red'=>['unsignedSmallInteger',null,null]],
   'ayllu_grupo_miembros'=>['estado_red'=>['string',20,'aceptado'],'avisos_red'=>['boolean',null,true]],
   'ayllu_eventos_calendario'=>['ayllu_grupo_id'=>['unsignedBigInteger',null,null]],
   'bolsa_trabajo_ofertas'=>['tipo_red'=>['string',20,'trabajo'],'estado_red'=>['string',20,'visible'],'situacion_red'=>['string',20,'abierta'],'autor_red_id'=>['unsignedBigInteger',null,null],'fuente_red'=>['string',20,null],'institucion_red'=>['string',180,null],'enlace_red'=>['string',500,null],'correo_red'=>['string',150,null],'lugar_red'=>['string',200,null],'plazo_red'=>['date',null,null]]
  ];
  foreach($campos as $tabla=>$columnas)if(Schema::hasTable($tabla))foreach($columnas as $nombre=>[$tipo,$longitud,$valor])if(!Schema::hasColumn($tabla,$nombre))Schema::table($tabla,function(Blueprint $t)use($nombre,$tipo,$longitud,$valor){$c=$longitud?$t->$tipo($nombre,$longitud):$t->$tipo($nombre);$valor===null?$c->nullable():$c->default($valor);});
  if(!Schema::hasTable('red_puklla_acciones'))Schema::create('red_puklla_acciones',function(Blueprint $t){$t->id();$t->unsignedBigInteger('publicacion_id')->index();$t->unsignedBigInteger('user_id')->index();$t->string('accion',30);$t->text('aporte');$t->string('estado',20);$t->timestamps();$t->unique(['publicacion_id','user_id']);});
  if(!Schema::hasTable('red_puklla_difusiones'))Schema::create('red_puklla_difusiones',function(Blueprint $t){$t->id();$t->unsignedBigInteger('publicacion_id')->index();$t->unsignedBigInteger('ayllu_grupo_id')->index();$t->unsignedBigInteger('user_id');$t->timestamps();$t->unique(['publicacion_id','ayllu_grupo_id']);});
  if(!Schema::hasTable('red_puklla_guardados'))Schema::create('red_puklla_guardados',function(Blueprint $t){$t->id();$t->unsignedBigInteger('user_id')->index();$t->string('tipo',20);$t->unsignedBigInteger('referencia_id');$t->string('estado',20);$t->timestamps();$t->unique(['user_id','tipo','referencia_id']);});
  if(!Schema::hasTable('red_puklla_moderacion'))Schema::create('red_puklla_moderacion',function(Blueprint $t){$t->id();$t->string('tipo',20);$t->unsignedBigInteger('referencia_id')->index();$t->unsignedBigInteger('actor_id');$t->string('accion',20);$t->text('motivo');$t->timestamps();});
  if(!Schema::hasTable('red_puklla_preferencias'))Schema::create('red_puklla_preferencias',function(Blueprint $t){$t->id();$t->unsignedBigInteger('user_id')->unique();$t->boolean('avisos')->default(true);$t->timestamps();});
 }
 public function down():void {/* Conservar contenido, preferencias y trazabilidad. */}
};
