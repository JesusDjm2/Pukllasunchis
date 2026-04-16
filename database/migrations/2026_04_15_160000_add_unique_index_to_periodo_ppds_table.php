<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'periodo_ppds_periodo_alumno_curso_unique';

    public function up(): void
    {
        if (! Schema::hasTable('periodo_ppds')) {
            return;
        }

        // Sin índice único, upsert() no actualiza filas: MySQL solo reacciona a claves únicas/PK.
        DB::statement('DELETE t1 FROM periodo_ppds t1
            INNER JOIN periodo_ppds t2
            ON t1.periodo_actual_ppd_id = t2.periodo_actual_ppd_id
            AND t1.alumno_id = t2.alumno_id
            AND t1.curso_id = t2.curso_id
            AND t1.id < t2.id');

        $already = DB::select(
            'SHOW INDEX FROM periodo_ppds WHERE Key_name = ?',
            [self::INDEX]
        );

        if (empty($already)) {
            Schema::table('periodo_ppds', function (Blueprint $table) {
                $table->unique(
                    ['periodo_actual_ppd_id', 'alumno_id', 'curso_id'],
                    self::INDEX
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('periodo_ppds')) {
            return;
        }

        $exists = DB::select(
            'SHOW INDEX FROM periodo_ppds WHERE Key_name = ?',
            [self::INDEX]
        );

        if (! empty($exists)) {
            Schema::table('periodo_ppds', function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        }
    }
};
