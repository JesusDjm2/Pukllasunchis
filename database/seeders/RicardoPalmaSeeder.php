<?php

namespace Database\Seeders;

use App\Models\RicardoPalma\RpPeriodo;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Database\Seeder;

class RicardoPalmaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Puebla programas base y periodos 1995-2015 para el IESP Ricardo Palma.
     */
    public function run(): void
    {
        // 1. Los 4 Programas Iniciales
        $programas = [
            [
                'codigo' => 'INICIAL',
                'nombre' => 'Educación Inicial',
                'nivel' => 'Superior Pedagógico',
                'total_semestres' => 10,
                'activo' => true,
            ],
            [
                'codigo' => 'PRIMARIA',
                'nombre' => 'Educación Primaria',
                'nivel' => 'Superior Pedagógico',
                'total_semestres' => 10,
                'activo' => true,
            ],
            [
                'codigo' => 'SEC-COM',
                'nombre' => 'Educación Secundaria: Especialidad Comunicación',
                'nivel' => 'Superior Pedagógico',
                'total_semestres' => 10,
                'activo' => true,
            ],
            [
                'codigo' => 'SEC-MAT',
                'nombre' => 'Educación Secundaria: Especialidad Matemáticas',
                'nivel' => 'Superior Pedagógico',
                'total_semestres' => 10,
                'activo' => true,
            ],
        ];

        foreach ($programas as $prog) {
            RpPrograma::updateOrCreate(
                ['codigo' => $prog['codigo']],
                $prog
            );
        }

        // 2. Periodos Académicos 1995 a 2015 (I y II)
        for ($anio = 1995; $anio <= 2015; $anio++) {
            foreach (['I', 'II'] as $semestre) {
                $codigo = "{$anio}-{$semestre}";
                RpPeriodo::updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'anio' => $anio,
                        'semestre' => $semestre,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
