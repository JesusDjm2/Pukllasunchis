<?php

namespace Database\Seeders;

use App\Models\Titulacion\EstadoTitulacion;
use App\Services\Titulacion\AvanceTitulacion;
use Illuminate\Database\Seeder;

class EstadosTitulacionSeeder extends Seeder
{
    /**
     * Catálogo de estados. El estado de cada seguimiento se calcula de sus datos
     * (AvanceTitulacion), así que clave y orden salen de ahí. Idempotente: la
     * migración 2026_09_30_000001 ya deja el catálogo creado.
     */
    public function run(): void
    {
        $orden = 1;
        foreach (AvanceTitulacion::ESTADOS as $clave => $nombre) {
            EstadoTitulacion::updateOrCreate(['clave' => $clave], ['nombre' => $nombre, 'orden' => $orden++]);
        }
    }
}
