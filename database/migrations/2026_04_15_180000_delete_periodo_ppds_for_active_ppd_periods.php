<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los períodos PPD con actual=1 no deben tener snapshot en periodo_ppds (siguen en calificacionesppds).
     */
    public function up(): void
    {
        if (! Schema::hasTable('periodo_ppds') || ! Schema::hasTable('periodo_actual_ppds')) {
            return;
        }

        $ids = DB::table('periodo_actual_ppds')->where('actual', 1)->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        DB::table('periodo_ppds')->whereIn('periodo_actual_ppd_id', $ids)->delete();
    }

    public function down(): void
    {
        // Irreversible: no se restauran filas borradas.
    }
};
