<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Mockery;
use Tests\TestCase;

class SeguimientoJerarquiaMainTest extends TestCase
{
    public function test_admin_bolsa_does_not_implicitly_manage_graduate_records(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasAnyRole')->with(['admin','super-admin'])->andReturn(false);
        $user->shouldReceive('can')->with('seguimiento-egresados.gestionar-campanas')->andReturn(false);
        $this->assertFalse((new SeguimientoAutorizacionService)->esPersonalGestion($user));
    }

    public function test_institutional_administrator_keeps_management_access(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasAnyRole')->with(['admin','super-admin'])->andReturn(true);
        $this->assertTrue((new SeguimientoAutorizacionService)->esPersonalGestion($user));
    }
}
