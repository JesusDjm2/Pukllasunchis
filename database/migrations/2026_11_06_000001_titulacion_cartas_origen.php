<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cartas de aceptación importadas de los formularios de Google (Excel de respuestas):
 *  - origen: 'sistema' (formulario del sistema), 'formulario' (importada del Excel) o 'manual' (registrada a mano).
 *  - datos_extra: lo que el formulario antiguo traía y la tabla no (código modular, dirección, ciclo, DNI) y
 *    cómo se reconoció a la persona (correo, nombre, celular, DNI).
 *  - clave_importacion: evita importar dos veces la misma respuesta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulacion_cartas_aceptacion', function (Blueprint $table) {
            $table->string('origen', 20)->default('sistema')->after('lote');
            $table->text('datos_extra')->nullable()->after('numero_carta');
            $table->string('clave_importacion', 40)->nullable()->after('datos_extra');
            $table->index('clave_importacion');
            // Los comprobantes del formulario de Google son enlaces de Drive de hasta ~340 caracteres.
            $table->string('comprobante', 500)->change();
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_cartas_aceptacion', function (Blueprint $table) {
            $table->dropIndex(['clave_importacion']);
            $table->dropColumn(['origen', 'datos_extra', 'clave_importacion']);
        });
    }
};
