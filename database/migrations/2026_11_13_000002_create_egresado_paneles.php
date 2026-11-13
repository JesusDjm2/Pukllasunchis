<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(!Schema::hasTable('egresado_campana_cortes')) Schema::create('egresado_campana_cortes',function(Blueprint $t){$t->id();$t->unsignedBigInteger('campana_id')->index();$t->unsignedBigInteger('registrado_por');$t->json('datos');$t->timestamp('created_at');});
  if(!Schema::hasTable('egresado_panel_auditoria')) Schema::create('egresado_panel_auditoria',function(Blueprint $t){$t->id();$t->unsignedBigInteger('actor_id');$t->string('tipo',40);$t->unsignedBigInteger('referencia_id')->index();$t->json('datos');$t->timestamp('created_at');});
 }
 public function down():void { /* Conservar cortes y trazabilidad institucional. */ }
};
