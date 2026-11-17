<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('egresado_historico_fuentes')) {
            Schema::create('egresado_historico_fuentes', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 60)->unique();
                $table->string('nombre', 180);
                $table->string('tipo', 40);
                $table->string('archivo_relativo', 255);
                $table->char('sha256_esperado', 64);
                $table->date('fecha_corte_conocida')->nullable();
                $table->text('notas_calidad')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('egresado_historico_lotes')) {
            Schema::create('egresado_historico_lotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fuente_id');
                $table->char('archivo_hash_sha256', 64);
                $table->string('version', 40)->nullable();
                $table->string('entorno', 32);
                $table->string('base_datos', 64);
                $table->string('estado', 32)->default('procesado');
                $table->unsignedInteger('total_filas')->default(0);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->dateTime('importado_en');
                $table->dateTime('revertido_en')->nullable();
                $table->unsignedBigInteger('revertido_por')->nullable();
                $table->string('motivo_reversion', 255)->nullable();
                $table->json('resumen_json')->nullable();
                $table->timestamps();

                $table->unique(['fuente_id', 'archivo_hash_sha256', 'base_datos'], 'uq_hist_fuente_hash_db');
                $table->foreign('fuente_id', 'fk_hist_lotes_fuente')
                    ->references('id')->on('egresado_historico_fuentes')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('egresado_historico_registros')) {
            Schema::create('egresado_historico_registros', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lote_id');
                $table->string('fuente_codigo', 60)->index();
                $table->string('fila_origen', 80);
                $table->char('registro_hash', 64)->index();
                $table->longText('payload_json');
                $table->text('bienestar_sensible_cifrado')->nullable();
                $table->date('fecha_observacion')->nullable();
                $table->string('fecha_fuente', 40)->nullable();
                $table->string('estado_conciliacion', 50)->default('pendiente')->index();
                $table->unsignedBigInteger('user_id_vinculado')->nullable()->index();
                $table->string('trayectoria_tipo_vinculada', 30)->nullable();
                $table->unsignedBigInteger('programa_id_vinculado')->nullable();
                $table->timestamps();

                $table->unique(['lote_id', 'fila_origen'], 'uq_hist_lote_fila');
                $table->foreign('lote_id', 'fk_hist_registros_lote')
                    ->references('id')->on('egresado_historico_lotes')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('egresado_historico_candidaturas')) {
            Schema::create('egresado_historico_candidaturas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('registro_id')->index();
                $table->unsignedBigInteger('user_id_candidato')->index();
                $table->string('trayectoria_tipo_candidata', 30)->nullable();
                $table->unsignedBigInteger('programa_id_candidato')->nullable();
                $table->string('metodo', 50);
                $table->string('evidencia_referencia', 255);
                $table->string('confianza', 40);
                $table->string('estado_revision', 40)->default('pendiente_revision_humana');
                $table->timestamps();

                $table->unique(['registro_id', 'user_id_candidato', 'metodo'], 'uq_hist_cand_reg_user');
                $table->foreign('registro_id', 'fk_hist_cand_registro')
                    ->references('id')->on('egresado_historico_registros')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('egresado_historico_decisiones')) {
            Schema::create('egresado_historico_decisiones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('registro_id')->index();
                $table->unsignedBigInteger('user_id_validado')->nullable()->index();
                $table->string('trayectoria_tipo_validada', 30)->nullable();
                $table->unsignedBigInteger('programa_id_validado')->nullable();
                $table->string('decision', 50);
                $table->text('motivo');
                $table->string('evidencia', 255)->nullable();
                $table->unsignedBigInteger('validado_por_user_id')->nullable();
                $table->dateTime('validado_en');
                $table->unsignedSmallInteger('version')->default(1);
                $table->timestamps();

                $table->foreign('registro_id', 'fk_hist_dec_registro')
                    ->references('id')->on('egresado_historico_registros')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('egresado_historico_observaciones')) {
            Schema::create('egresado_historico_observaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('registro_origen_id')->unique();
                $table->unsignedBigInteger('fuente_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('trayectoria_tipo', 30)->nullable();
                $table->unsignedBigInteger('programa_id')->nullable();
                $table->date('observada_en')->nullable();
                $table->string('fecha_corte', 40)->nullable();
                $table->string('naturaleza', 40)->default('declarado_por_persona');
                $table->string('calidad', 50)->default('verificado_en_fuente');
                $table->string('situacion_laboral_declarada', 30)->nullable();
                $table->string('insercion_educativa_declarada', 80)->nullable();
                $table->string('sector_declarado', 100)->nullable();
                $table->string('tipo_practica_declarada', 120)->nullable();
                $table->string('cargo_declarado', 255)->nullable();
                $table->string('lugar_trabajo_texto_origen', 255)->nullable();
                $table->string('satisfaccion_historica_archivo', 120)->nullable();
                $table->text('aporte_formacion_original')->nullable();
                $table->text('necesidades_formacion_original')->nullable();
                $table->text('sugerencias_original')->nullable();
                $table->boolean('tiene_bienestar_restringido')->default(false);
                $table->json('atributos_normalizados')->nullable();
                $table->unsignedSmallInteger('version')->default(1);
                $table->timestamps();

                $table->foreign('registro_origen_id', 'fk_hist_obs_registro')
                    ->references('id')->on('egresado_historico_registros')
                    ->onDelete('cascade');
                $table->foreign('fuente_id', 'fk_hist_obs_fuente')
                    ->references('id')->on('egresado_historico_fuentes')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('PROHIBIDO: No se permite eliminar tablas de conciliación histórica en producción.');
        }

        $tablas = [
            'egresado_historico_observaciones',
            'egresado_historico_decisiones',
            'egresado_historico_candidaturas',
            'egresado_historico_registros',
            'egresado_historico_lotes',
            'egresado_historico_fuentes',
        ];

        foreach ($tablas as $t) {
            if (Schema::hasTable($t) && DB::table($t)->exists() && !filter_var(env('HISTORICO_ALLOW_ROLLBACK', false), FILTER_VALIDATE_BOOLEAN)) {
                throw new \RuntimeException(
                    "ROLLBACK BLOQUEADO: La tabla {$t} contiene registros de conciliación. Defina HISTORICO_ALLOW_ROLLBACK=true si cuenta con autorización."
                );
            }
        }

        foreach ($tablas as $t) {
            Schema::dropIfExists($t);
        }
    }
};
