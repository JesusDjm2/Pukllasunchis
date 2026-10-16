<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El estado de titulación deja de elegirse a mano: se calcula de los datos
 * (App\Services\Titulacion\AvanceTitulacion). Aquí:
 *  - estados_titulacion gana una `clave` estable (el código ya no busca por
 *    nombre) y el catálogo pasa al camino real PTI → TI → Bachiller → Tesis →
 *    Licenciado. Se renombran las filas existentes (mismos ids, así ningún
 *    seguimiento pierde su FK) y se agrega "TI aprobado".
 *  - titulacion_tesis gana la inscripción SUNEDU de ambos grados (el grado
 *    cuenta desde esa inscripción).
 * Después de migrar, correr `php artisan titulacion:recalcular-estados`.
 */
return new class extends Migration
{
    /** [nombre anterior (null = estado nuevo), clave, nombre nuevo, orden, descripcion] */
    private const CATALOGO = [
        ['Egresado', 'sin_pti', 'Sin PTI', 1, 'Aún no registra su Plan de Trabajo de Investigación.'],
        ['PTI en proceso', 'pti_en_proceso', 'PTI en proceso', 2, 'PTI inscrito, sin RD de aprobación todavía.'],
        ['PTI aprobado', 'pti_aprobado', 'PTI aprobado', 3, 'Tiene RD de PTI; desarrolla su Trabajo de Investigación.'],
        [null, 'ti_aprobado', 'TI aprobado', 4, 'Tiene RD de TI; falta inscribir el Bachiller en SUNEDU.'],
        ['Bachiller', 'bachiller', 'Bachiller', 5, 'Bachiller inscrito en SUNEDU; desarrolla su Tesis.'],
        ['Tesis en proceso', 'tesis_aprobada', 'Tesis aprobada', 6, 'Tiene RD de Tesis; falta inscribir el título en SUNEDU.'],
        ['Titulado', 'licenciado', 'Licenciado', 7, 'Título de Licenciado inscrito en SUNEDU.'],
    ];

    public function up(): void
    {
        Schema::table('estados_titulacion', function (Blueprint $table) {
            $table->string('clave', 30)->nullable()->unique()->after('id');
        });

        foreach (self::CATALOGO as [$anterior, $clave, $nombre, $orden, $descripcion]) {
            $datos = ['clave' => $clave, 'nombre' => $nombre, 'orden' => $orden, 'descripcion' => $descripcion, 'updated_at' => now()];
            $existe = $anterior !== null && DB::table('estados_titulacion')->where('nombre', $anterior)->exists();

            if ($existe) {
                DB::table('estados_titulacion')->where('nombre', $anterior)->update($datos);
            } else {
                DB::table('estados_titulacion')->insert($datos + ['created_at' => now()]);
            }
        }

        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->date('fecha_bachiller_sunedu')->nullable()->after('bachiller_sunedu');
            $table->string('titulo_sunedu', 50)->nullable()->after('rd_tesis');
            $table->date('fecha_titulo_sunedu')->nullable()->after('titulo_sunedu');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->dropColumn(['fecha_bachiller_sunedu', 'titulo_sunedu', 'fecha_titulo_sunedu']);
        });

        foreach (self::CATALOGO as [$anterior, $clave]) {
            if ($anterior === null) {
                // Solo se borra si nadie quedó en ese estado (la FK lo impediría).
                DB::table('estados_titulacion')->where('clave', $clave)
                    ->whereNotExists(fn ($q) => $q->from('seguimiento_titulacion')->whereColumn('estado_titulacion_id', 'estados_titulacion.id'))
                    ->delete();
            } else {
                DB::table('estados_titulacion')->where('clave', $clave)->update(['nombre' => $anterior]);
            }
        }

        Schema::table('estados_titulacion', function (Blueprint $table) {
            $table->dropUnique(['clave']);
            $table->dropColumn('clave');
        });
    }
};
