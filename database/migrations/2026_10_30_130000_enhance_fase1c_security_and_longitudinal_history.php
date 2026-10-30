<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fase 1C: Seguridad, Invitaciones Seguras e Historia Longitudinal.
     */
    public function up(): void
    {
        // 1. Tabla de invitaciones seguras para campañas públicas (WhatsApp / Magic Link)
        if (!Schema::hasTable('egresado_invitaciones')) {
            Schema::create('egresado_invitaciones', function (Blueprint $table) {
                $table->id();
                $table->string('token', 64)->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('identificador_temporal', 50)->nullable()->index();
                $table->string('campana', 30)->default('2026-II');
                $table->string('canal', 40)->default('whatsapp');
                $table->unsignedBigInteger('generado_por')->nullable();
                $table->boolean('usado')->default(false);
                $table->timestamp('fecha_expiracion')->nullable();
                $table->timestamp('fecha_uso')->nullable();
                $table->string('ip_uso', 45)->nullable();
                $table->timestamps();
            });
        }

        // 2. Columnas de seguridad, auditoría e historia longitudinal en egresado_actualizaciones
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_actualizaciones', 'tipo_registro')) {
                    $table->string('tipo_registro', 40)->default('respuesta_campana')->nullable()->after('periodo_campana');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'estado_verificacion')) {
                    $table->string('estado_verificacion', 40)->default('verificado')->nullable()->after('tipo_registro');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'procedencia')) {
                    $table->string('procedencia', 80)->nullable()->after('estado_verificacion');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'token_invitacion')) {
                    $table->string('token_invitacion', 64)->nullable()->index()->after('procedencia');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'ip_registro')) {
                    $table->string('ip_registro', 45)->nullable()->after('token_invitacion');
                }
            });
        }
    }

    /**
     * Reversión segura: No elimina tablas preexistentes ni datos históricos.
     */
    public function down(): void
    {
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                $cols = ['tipo_registro', 'estado_verificacion', 'procedencia', 'token_invitacion', 'ip_registro'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('egresado_actualizaciones', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('egresado_invitaciones')) {
            Schema::dropIfExists('egresado_invitaciones');
        }
    }
};
