<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Titulación, pedidos de Secretaría (01/10):
 *  - Catálogo propio de asesores (externos o docentes sin cuenta): se suman a
 *    los docentes en los selects de asesor y jurado, sin crear cuenta de acceso.
 *  - Historial de PTI: una persona puede tener varios (vence sin TI → nuevo PTI
 *    de Bachiller; vence con TI → nuevo PTI de Licenciatura). La ampliación
 *    extiende el MISMO PTI (RD + nuevo vencimiento).
 *  - Resoluciones con una nota libre (detalle) para los casos excepcionales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_asesores', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('dni', 15)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('institucion', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('titulacion_ptis', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero')->default(1)->after('seguimiento_id');
            $table->string('etapa', 20)->default('bachiller')->after('numero'); // bachiller|licenciatura
            $table->foreignId('asesor_externo_id')->nullable()->after('asesor_id')->constrained('titulacion_asesores')->nullOnDelete();
            $table->string('rd_ampliacion')->nullable()->after('fecha_vencimiento');
            $table->date('fecha_ampliacion')->nullable()->after('rd_ampliacion');
            $table->date('vencimiento_ampliado')->nullable()->after('fecha_ampliacion');
        });

        foreach (['titulacion_tesis', 'titulacion_actas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('asesor_externo_id')->nullable()->after('asesor_id')->constrained('titulacion_asesores')->nullOnDelete();
            });
        }

        Schema::table('titulacion_jurados', function (Blueprint $table) {
            $table->foreignId('asesor_externo_id')->nullable()->after('docente_id')->constrained('titulacion_asesores')->cascadeOnDelete();
        });
        // Un jurado es docente O asesor del catálogo: docente_id pasa a opcional.
        DB::statement('ALTER TABLE titulacion_jurados MODIFY docente_id BIGINT UNSIGNED NULL');

        Schema::table('titulacion_resoluciones', function (Blueprint $table) {
            $table->string('detalle', 500)->nullable()->after('archivo');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_resoluciones', fn (Blueprint $t) => $t->dropColumn('detalle'));

        Schema::table('titulacion_jurados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asesor_externo_id');
        });

        foreach (['titulacion_tesis', 'titulacion_actas'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->dropConstrainedForeignId('asesor_externo_id'));
        }

        Schema::table('titulacion_ptis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asesor_externo_id');
            $table->dropColumn(['numero', 'etapa', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado']);
        });

        Schema::dropIfExists('titulacion_asesores');
    }
};
