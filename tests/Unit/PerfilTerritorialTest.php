<?php

namespace Tests\Unit;

use App\Services\SeguimientoEgresado\PerfilTerritorialService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerfilTerritorialTest extends TestCase
{
    public function test_optional_community_and_support_do_not_reduce_progress(): void
    {
        $service = new PerfilTerritorialService;
        $p = ['contacto' => ['programa' => 'Inicial', 'egreso' => 2025, 'correo' => 'prueba@example.test'], 'residencia' => ['departamento' => 'Cusco', 'provincia' => 'Canchis', 'distrito' => 'Sicuani'], 'trabaja' => 'no'];
        $this->assertSame(100, $service->avance($p)['porcentaje']);
    }

    public function test_incompatible_eib_scenario_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new PerfilTerritorialService)->validar(['trabajos' => [['institucion' => 'Escuela de prueba', 'cargo' => 'Docente', 'forma_eib' => 'fortalecimiento', 'escenario' => 4]]]);
    }

    public function test_draft_accepts_incomplete_job(): void
    {
        $data = (new PerfilTerritorialService)->validar(['trabajos' => [['institucion' => null, 'cargo' => null]]], true);
        $this->assertCount(1, $data['trabajos']);
    }

    public function test_unknown_location_has_no_invented_coordinates(): void
    {
        $place = (new PerfilTerritorialService)->lugar(['distrito' => 'Por confirmar']);
        $this->assertNull($place['latitud']);
        $this->assertNull($place['longitud']);
        $this->assertSame('sin_coordenadas', $place['precision']);
    }
}
