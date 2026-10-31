<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fase 1D: Token Hashing, Anti-Filtración PII y Privacidad de Bienestar.
     */
    public function up(): void
    {
        // 1. Mejoras de seguridad en egresado_invitaciones (Hashing, Intentos, Revocación)
        if (Schema::hasTable('egresado_invitaciones')) {
            Schema::table('egresado_invitaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_invitaciones', 'token_hash')) {
                    $table->string('token_hash', 64)->nullable()->index()->after('token');
                }
                if (!Schema::hasColumn('egresado_invitaciones', 'intentos')) {
                    $table->unsignedSmallInteger('intentos')->default(0)->after('usado');
                }
                if (!Schema::hasColumn('egresado_invitaciones', 'revocado')) {
                    $table->boolean('revocado')->default(false)->after('intentos');
                }
                if (!Schema::hasColumn('egresado_invitaciones', 'fecha_revocacion')) {
                    $table->timestamp('fecha_revocacion')->nullable()->after('revocado');
                }
                if (!Schema::hasColumn('egresado_invitaciones', 'motivo_revocacion')) {
                    $table->string('motivo_revocacion', 150)->nullable()->after('fecha_revocacion');
                }
            });

            // Backfill seguro: Calcular SHA-256 para tokens existentes
            $existentes = DB::table('egresado_invitaciones')->whereNull('token_hash')->get();
            foreach ($existentes as $inv) {
                if (!empty($inv->token)) {
                    DB::table('egresado_invitaciones')->where('id', $inv->id)->update([
                        'token_hash' => hash('sha256', $inv->token)
                    ]);
                }
            }
        }

        // 2. Segregación de privacidad en egresado_casos (Notas confidenciales de bienestar)
        if (Schema::hasTable('egresado_casos')) {
            Schema::table('egresado_casos', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_casos', 'es_confidencial_bienestar')) {
                    $table->boolean('es_confidencial_bienestar')->default(false)->after('origen');
                }
            });
        }
    }

    /**
     * Reversión segura y defensiva: Elimina únicamente columnas auxiliares de Fase 1D.
     * NUNCA elimina tablas ni datos históricos de egresados o casos.
     */
    public function down(): void
    {
        if (Schema::hasTable('egresado_invitaciones')) {
            Schema::table('egresado_invitaciones', function (Blueprint $table) {
                $cols = ['token_hash', 'intentos', 'revocado', 'fecha_revocacion', 'motivo_revocacion'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('egresado_invitaciones', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('egresado_casos')) {
            Schema::table('egresado_casos', function (Blueprint $table) {
                if (Schema::hasColumn('egresado_casos', 'es_confidencial_bienestar')) {
                    $table->dropColumn('es_confidencial_bienestar');
                }
            });
        }
    }
};
