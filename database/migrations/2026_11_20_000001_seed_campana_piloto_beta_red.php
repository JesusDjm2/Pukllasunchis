<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SeguimientoEgresado\Campana;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Campana::firstOrCreate(
            ['codigo' => 'PUKLLA-BETA-PILOTO'],
            [
                'nombre' => 'Piloto Beta Red Puklla de Egresados',
                'anio' => 2026,
                'semestre' => 'II',
                'fecha_inicio' => '2026-08-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activa',
                'descripcion' => 'Campaña piloto para validación beta de la Red Puklla de Egresados y actualización de perfiles territoriales.',
                'es_ensayo' => false,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Campana::where('codigo', 'PUKLLA-BETA-PILOTO')->delete();
    }
};
