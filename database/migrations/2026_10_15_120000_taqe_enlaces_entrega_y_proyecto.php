<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Taq'e:
 * - Evidencias y trabajo final pueden ser un ENLACE (sin archivo en Drive): taqe_trabajos.enlace,
 *   y taqe_trabajos.archivo pasa a ser opcional.
 * - El trabajo final ya no es un único PDF: varios archivos/enlaces con categoria = 'final'; el grupo
 *   se ENTREGA explícitamente (taqe_grupos.entregado_at + estado 'finalizado').
 * - Cada grupo queda ligado al Proyecto Integrador del ciclo de su curso (taqe_grupos.proyecto_id),
 *   el mismo que muestra el sílabo (ciclos.proyecto_id → proyectos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taqe_trabajos', function (Blueprint $table) {
            $table->string('archivo')->nullable()->change();
            $table->string('enlace', 2048)->nullable()->after('archivo');
        });

        Schema::table('taqe_grupos', function (Blueprint $table) {
            $table->foreignId('proyecto_id')->nullable()->after('programa_id')->constrained('proyectos')->nullOnDelete();
            $table->timestamp('entregado_at')->nullable()->after('estado');
        });

        // Grupos existentes: el proyecto del ciclo de su curso; los ya finalizados quedan entregados.
        DB::statement('UPDATE taqe_grupos g JOIN ciclos c ON c.id = g.ciclo_id SET g.proyecto_id = c.proyecto_id');
        DB::statement("UPDATE taqe_grupos SET entregado_at = updated_at WHERE estado = 'finalizado'");
    }

    public function down(): void
    {
        Schema::table('taqe_grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proyecto_id');
            $table->dropColumn('entregado_at');
        });

        Schema::table('taqe_trabajos', function (Blueprint $table) {
            $table->dropColumn('enlace');
            $table->string('archivo')->nullable(false)->change();
        });
    }
};
