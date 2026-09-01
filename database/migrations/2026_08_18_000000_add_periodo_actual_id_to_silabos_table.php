<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silabos', function (Blueprint $table) {
            $table->foreignId('periodo_actual_id')->nullable()->after('periodo')
                ->constrained('periodo_actual')->onDelete('set null');
        });

        // Backfill: hacer coincidir el texto guardado en `periodo` con el nombre real
        // del período, para los sílabos creados antes de que existiera esta relación.
        DB::table('silabos')->whereNull('periodo_actual_id')->orderBy('id')->get()->each(function ($silabo) {
            $periodo = DB::table('periodo_actual')->where('nombre', $silabo->periodo)->first();
            if ($periodo) {
                DB::table('silabos')->where('id', $silabo->id)->update(['periodo_actual_id' => $periodo->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('silabos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_actual_id');
        });
    }
};
