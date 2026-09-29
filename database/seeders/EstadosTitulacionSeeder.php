<?php

namespace Database\Seeders;

use App\Models\Titulacion\EstadoTitulacion;
use Illuminate\Database\Seeder;

class EstadosTitulacionSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Egresado', 'orden' => 1],
            ['nombre' => 'PTI en proceso', 'orden' => 2],
            ['nombre' => 'PTI aprobado', 'orden' => 3],
            ['nombre' => 'Bachiller', 'orden' => 4],
            ['nombre' => 'Tesis en proceso', 'orden' => 5],
            ['nombre' => 'Titulado', 'orden' => 6],
        ];

        foreach ($estados as $estado) {
            EstadoTitulacion::firstOrCreate(['nombre' => $estado['nombre']], $estado);
        }
    }
}
