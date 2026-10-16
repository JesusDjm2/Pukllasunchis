<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clasificación explícita de los programas. Hasta ahora FID/PPD, el nivel
     * y si es EIB se deducían del texto de `nombre` ("... PPD", "Inicial",
     * "EIB"); los valores iniciales se calculan con esa misma regla para que
     * todo siga funcionando igual.
     *
     * `modalidad` se pasa a NOT NULL con SQL crudo después del rellenado
     * porque el proyecto no tiene doctrine/dbal (requisito de ->change()).
     */
    public function up(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->string('modalidad', 3)->nullable()->after('nombre')->index();
            $table->string('nivel', 10)->nullable()->after('modalidad');
            $table->boolean('eib')->default(false)->after('nivel');
        });

        DB::statement("UPDATE programas SET
            modalidad = IF(nombre LIKE '%PPD%', 'PPD', 'FID'),
            nivel = CASE
                WHEN nombre LIKE '%Inicial%' THEN 'INICIAL'
                WHEN nombre LIKE '%Primaria%' THEN 'PRIMARIA'
                ELSE NULL
            END,
            eib = IF(nombre LIKE '%EIB%' OR nombre LIKE '%Intercultural%', 1, 0)");

        DB::statement('ALTER TABLE programas MODIFY modalidad VARCHAR(3) NOT NULL');
    }

    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropIndex(['modalidad']);
            $table->dropColumn(['modalidad', 'nivel', 'eib']);
        });
    }
};
