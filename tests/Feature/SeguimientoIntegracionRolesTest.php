<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\SeguimientoEgresado\AuditoriaRolSeguimiento;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\User;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Suite de Pruebas de Integración y Autorización de Roles — Puklla en Red
 *
 * Aspectos verificados según directiva oficial:
 * 1. Acceso de super-admin al panel administrativo.
 * 2. Rechazo 403 a usuarios sin permiso para asignar roles.
 * 3. Idempotencia del rol seguimiento-egresados y sus permisos.
 * 4. Conservación del rol 'docente' al recibir 'seguimiento-egresados'.
 * 5. Persistencia y trazabilidad de asignación en egresado_auditoria_roles.
 * 6. Visualización de Puklla en Red en dashboard docente autorizado.
 * 7. Ocultación de Puklla en Red en dashboard docente no asignado.
 * 8. Protección backend (URL directa sin autorización retorna 403).
 * 9. Revocación del rol retira acceso al módulo.
 * 10. Revocación preserva intacto el rol y las funciones docentes.
 * 11. Entrevistadores no pueden consultar egresados no asignados (403).
 * 12. Separación estricta de permisos (operativos, analítica, confidencial bienestar).
 * 13. Flujo funcional completo de extremo a extremo.
 */
class SeguimientoIntegracionRolesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'egresado', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad', 'guard_name' => 'web']);
    }

    protected function crearDocenteSintetico(): User
    {
        $user = User::factory()->create([
            'name' => 'Docente Integracion ' . rand(100, 999),
            'apellidos' => 'Puklla ' . rand(1000, 9999),
            'email' => 'docente_int_' . rand(1000, 9999) . '@pukllavirtual.edu.pe',
            'dni' => (string) rand(70000000, 79999999),
            'condicion' => null,
        ]);
        $user->assignRole('docente');

        Docente::create([
            'nombre' => $user->name . ' ' . $user->apellidos,
            'dni' => $user->dni,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    protected function crearSuperAdminSintetico(): User
    {
        $user = User::factory()->create([
            'name' => 'Super Admin Test',
            'apellidos' => 'Pukllasunchis',
            'email' => 'superadmin_test_' . rand(1000, 9999) . '@puklla.test',
            'dni' => (string) rand(10000000, 19999999),
        ]);
        $user->assignRole('super-admin');

        return $user;
    }

    protected function crearEgresadoSintetico(string $nombre = 'Egresado Test'): User
    {
        $user = User::factory()->create([
            'name' => $nombre,
            'apellidos' => 'Puklla ' . rand(100, 999),
            'dni' => (string) rand(70000000, 79999999),
            'email' => 'egresado_' . rand(10000, 99999) . '@puklla.test',
            'condicion' => 'Egresado',
        ]);
        $user->assignRole('egresado');

        return $user;
    }

    /**
     * Test 1: El usuario super-admin puede acceder al panel administrativo institucional (HTTP 200).
     */
    public function test_01_super_admin_accede_al_panel_administrativo(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();

        $response = $this->actingAs($superAdmin)->get(route('admin'));
        $response->assertStatus(200);
    }

    /**
     * Test 2: Un usuario sin rol privilegiado (ej. alumno o docente raso) no puede modificar roles de usuarios (HTTP 403).
     */
    public function test_02_usuario_sin_permiso_no_puede_asignar_roles(): void
    {
        $docente = $this->crearDocenteSintetico();
        $otroDocente = $this->crearDocenteSintetico();

        $response = $this->actingAs($docente)->put(route('adminUpdate', $otroDocente->id), [
            'name' => $otroDocente->name,
            'apellidos' => $otroDocente->apellidos,
            'dni' => $otroDocente->dni,
            'email' => $otroDocente->email,
            'roles' => ['docente', 'seguimiento-egresados']
        ]);

        // Rutas bajo middleware role:admin|super-admin rechazan al docente sin rol de gestión
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    /**
     * Test 3: El rol seguimiento-egresados y sus permisos son idempotentes y no se duplican.
     */
    public function test_03_rol_seguimiento_egresados_es_idempotente(): void
    {
        // Re-crear rol y permisos deliberadamente
        $rol1 = Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);
        $perm1 = Permission::firstOrCreate(['name' => 'seguimiento-egresados.ver-dashboard', 'guard_name' => 'web']);

        $rol2 = Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);
        $perm2 = Permission::firstOrCreate(['name' => 'seguimiento-egresados.ver-dashboard', 'guard_name' => 'web']);

        $this->assertEquals($rol1->id, $rol2->id);
        $this->assertEquals($perm1->id, $perm2->id);

        $conteoRoles = Role::where('name', 'seguimiento-egresados')->count();
        $this->assertEquals(1, $conteoRoles, 'El rol seguimiento-egresados debe existir exactamente una vez.');
    }

    /**
     * Test 4: Un docente conserva el rol 'docente' cuando se le asigna adicionalmente 'seguimiento-egresados'.
     */
    public function test_04_docente_conserva_rol_docente_al_recibir_seguimiento_egresados(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();
        $docente = $this->crearDocenteSintetico();

        $this->assertTrue($docente->hasRole('docente'));
        $this->assertFalse($docente->hasRole('seguimiento-egresados'));

        // Super-admin actualiza roles asignando ambos roles
        $response = $this->actingAs($superAdmin)->put(route('adminUpdate', $docente->id), [
            'name' => $docente->name,
            'apellidos' => $docente->apellidos,
            'dni' => $docente->dni,
            'email' => $docente->email,
            'roles' => ['docente', 'seguimiento-egresados'],
            'telefono' => '984112233',
        ]);

        $response->assertSessionHasNoErrors();
        $docente->refresh();

        // Debe tener ambos roles simultáneamente
        $this->assertTrue($docente->hasRole('docente'), 'El docente debe conservar su rol docente.');
        $this->assertTrue($docente->hasRole('seguimiento-egresados'), 'El docente debe tener ahora el nuevo rol.');
        $this->assertNotNull($docente->docente, 'La entidad Docente asociada debe permanecer intacta.');
    }

    /**
     * Test 5: La asignación persiste y genera un registro de trazabilidad en egresado_auditoria_roles.
     */
    public function test_05_asignacion_genera_trazabilidad_en_base_de_datos(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();
        $docente = $this->crearDocenteSintetico();

        $this->actingAs($superAdmin)->put(route('adminUpdate', $docente->id), [
            'name' => $docente->name,
            'apellidos' => $docente->apellidos,
            'dni' => $docente->dni,
            'email' => $docente->email,
            'roles' => ['docente', 'seguimiento-egresados'],
        ]);

        $auditoria = AuditoriaRolSeguimiento::where('user_id', $docente->id)
            ->where('rol', 'seguimiento-egresados')
            ->where('accion', 'asignado')
            ->first();

        $this->assertNotNull($auditoria, 'Debe registrarse el evento en egresado_auditoria_roles.');
        $this->assertEquals($superAdmin->id, $auditoria->admin_id);
        $this->assertContains('seguimiento-egresados', $auditoria->roles_posteriores);
    }

    /**
     * Test 6: El docente autorizado ve la entrada "Seguimiento de Egresados — Puklla en Red" en su dashboard.
     */
    public function test_06_docente_autorizado_ve_puklla_en_red_en_su_dashboard(): void
    {
        $docenteUser = $this->crearDocenteSintetico();
        $docenteUser->assignRole('seguimiento-egresados');

        $docenteModel = $docenteUser->docente;

        $response = $this->actingAs($docenteUser)->get(route('vistaDocente', ['docente' => $docenteModel->id]));
        $response->assertStatus(200);

        // Debe contener la entrada tanto en el menú lateral como en la tarjeta del dashboard
        $response->assertSee('Seguimiento de Egresados — Puklla en Red');
        $response->assertSee(route('seguimiento-egresados.index'));
    }

    /**
     * Test 7: Un docente sin el rol ni permiso no ve la opción de Puklla en Red en su dashboard.
     */
    public function test_07_docente_sin_rol_no_ve_opcion_en_dashboard(): void
    {
        $docenteUser = $this->crearDocenteSintetico();
        // Solo rol 'docente', sin 'seguimiento-egresados'
        $this->assertFalse($docenteUser->hasRole('seguimiento-egresados'));

        $docenteModel = $docenteUser->docente;

        $response = $this->actingAs($docenteUser)->get(route('vistaDocente', ['docente' => $docenteModel->id]));
        $response->assertStatus(200);
        $response->assertDontSee('Seguimiento de Egresados — Puklla en Red');
    }

    /**
     * Test 8: Escribir directamente la URL no permite saltarse la autorización (HTTP 403 / 401).
     */
    public function test_08_escribir_url_directa_sin_autorizacion_es_rechazada(): void
    {
        // Un usuario egresado sin rol administrativo ni docente no puede consultar el padrón institucional completo
        $egresado = $this->crearEgresadoSintetico('Egresado Aislado');

        // Al consultar la URL, si intenta acceder a los episodios de otro usuario
        $otroUsuario = $this->crearEgresadoSintetico('Otro Egresado');

        $response = $this->actingAs($egresado)->get(route('seguimiento-egresados.episodios', $otroUsuario->id));
        $response->assertStatus(403);
    }

    /**
     * Test 9: Revocar el rol 'seguimiento-egresados' retira el acceso y la visibilidad en el dashboard.
     */
    public function test_09_revocar_rol_elimina_acceso_y_visibilidad(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();
        $docente = $this->crearDocenteSintetico();
        $docente->assignRole('seguimiento-egresados');

        $this->assertTrue($docente->hasRole('seguimiento-egresados'));

        // Super-admin revoca rol quitándolo de la lista enviada
        $response = $this->actingAs($superAdmin)->put(route('adminUpdate', $docente->id), [
            'name' => $docente->name,
            'apellidos' => $docente->apellidos,
            'dni' => $docente->dni,
            'email' => $docente->email,
            'roles' => ['docente'], // Solo docente
        ]);

        $response->assertSessionHasNoErrors();
        $docente->refresh();

        $this->assertFalse($docente->hasRole('seguimiento-egresados'));

        // Trazabilidad de revocación
        $auditoriaRevocacion = AuditoriaRolSeguimiento::where('user_id', $docente->id)
            ->where('rol', 'seguimiento-egresados')
            ->where('accion', 'revocado')
            ->first();
        $this->assertNotNull($auditoriaRevocacion);

        // Ya no aparece en su dashboard
        $responseDashboard = $this->actingAs($docente)->get(route('vistaDocente', ['docente' => $docente->docente->id]));
        $responseDashboard->assertDontSee('Seguimiento de Egresados — Puklla en Red');
    }

    /**
     * Test 10: La revocación de 'seguimiento-egresados' no afecta las demás funciones docentes ni sus relaciones.
     */
    public function test_10_revocacion_no_afecta_otras_funciones_docentes(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();
        $docenteUser = $this->crearDocenteSintetico();
        $docenteUser->assignRole('seguimiento-egresados');

        $docenteId = $docenteUser->docente->id;

        // Revocar
        $this->actingAs($superAdmin)->put(route('adminUpdate', $docenteUser->id), [
            'name' => $docenteUser->name,
            'apellidos' => $docenteUser->apellidos,
            'dni' => $docenteUser->dni,
            'email' => $docenteUser->email,
            'roles' => ['docente'],
        ]);

        $docenteUser->refresh();

        $this->assertTrue($docenteUser->hasRole('docente'));
        $this->assertNotNull($docenteUser->docente);
        $this->assertEquals($docenteId, $docenteUser->docente->id);
    }

    /**
     * Test 11: Los entrevistadores/docentes con 'seguimiento-egresados' NO pueden consultar egresados no asignados (HTTP 403).
     */
    public function test_11_entrevistadores_no_pueden_consultar_egresados_no_asignados(): void
    {
        $docenteEntrevistador = $this->crearDocenteSintetico();
        $docenteEntrevistador->assignRole('seguimiento-egresados');

        $egresadoNoAsignado = $this->crearEgresadoSintetico('Egresado Ajeno');

        // Intentar consultar episodios de egresado no asignado
        $response = $this->actingAs($docenteEntrevistador)->get(route('seguimiento-egresados.episodios', $egresadoNoAsignado->id));
        $response->assertStatus(403);

        // Crear grupo activo donde SÍ está asignado
        $egresadoAsignado = $this->crearEgresadoSintetico('Egresado Asignado');

        GrupoEntrevistador::create([
            'codigo' => 'GRP-TEST-' . rand(100, 999),
            'nombre' => 'Grupo Prueba Test',
            'periodo_campana' => '2026-II',
            'entrevistadores' => [$docenteEntrevistador->id],
            'egresados_ids' => [$egresadoAsignado->id],
            'estado' => 'activo'
        ]);

        // Ahora al asignado sí puede consultarlo exitosamente
        $responseAsignado = $this->actingAs($docenteEntrevistador)->get(route('seguimiento-egresados.episodios', $egresadoAsignado->id));
        $responseAsignado->assertStatus(200);
    }

    /**
     * Test 12: Separación estricta de privilegios: un docente con 'seguimiento-egresados' no tiene acceso a analítica institucional sin permiso adicional.
     */
    public function test_12_separacion_estricta_de_permisos_operativos_y_analitica(): void
    {
        $docenteEntrevistador = $this->crearDocenteSintetico();
        $docenteEntrevistador->assignRole('seguimiento-egresados');

        // Por defecto, solo tiene permisos operativos de mínimo privilegio (no tiene ver-analitica)
        $this->assertFalse($docenteEntrevistador->can('seguimiento-egresados.ver-analitica'));

        // No puede acceder al Laboratorio de Inteligencia
        $responseLaboratorio = $this->actingAs($docenteEntrevistador)->get(route('seguimiento.laboratorio'));
        $responseLaboratorio->assertStatus(403);

        // No puede consultar casos confidenciales de Bienestar
        /** @var SeguimientoAutorizacionService $authService */
        $authService = app(SeguimientoAutorizacionService::class);
        $this->assertFalse($authService->puedeVerNotasConfidencialesBienestar($docenteEntrevistador));

        // Si se le concede explícitamente el permiso de analítica:
        $docenteEntrevistador->givePermissionTo('seguimiento-egresados.ver-analitica');
        $docenteEntrevistador->refresh();

        $responseLaboratorioConPermiso = $this->actingAs($docenteEntrevistador)->get(route('seguimiento.laboratorio'));
        $responseLaboratorioConPermiso->assertStatus(200);
    }

    /**
     * Test 13: Flujo funcional completo de extremo a extremo:
     * Super-admin inicia sesión -> busca docente -> asigna rol -> docente inicia sesión ->
     * encuentra Puklla en Red en su dashboard -> accede al módulo -> super-admin revoca rol -> acceso retirado.
     */
    public function test_13_flujo_completo_extremo_a_extremo(): void
    {
        $superAdmin = $this->crearSuperAdminSintetico();
        $docente = $this->crearDocenteSintetico();

        // 1. Super-admin autenticado busca al docente en adminEdit
        $resEdit = $this->actingAs($superAdmin)->get(route('adminEdit', $docente->id));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Seguimiento Egresados');

        // 2. Super-admin asigna el rol
        $resAsignar = $this->actingAs($superAdmin)->put(route('adminUpdate', $docente->id), [
            'name' => $docente->name,
            'apellidos' => $docente->apellidos,
            'dni' => $docente->dni,
            'email' => $docente->email,
            'roles' => ['docente', 'seguimiento-egresados'],
        ]);
        $resAsignar->assertSessionHasNoErrors();

        // 3. Docente inicia sesión y visita su dashboard
        $docente->refresh();
        $this->assertTrue($docente->hasRole('seguimiento-egresados'));

        $resDashboard = $this->actingAs($docente)->get(route('vistaDocente', $docente->docente->id));
        $resDashboard->assertStatus(200);
        $resDashboard->assertSee('Seguimiento de Egresados — Puklla en Red');

        // 4. Docente accede al padrón del módulo Puklla en Red
        $resModulo = $this->actingAs($docente)->get(route('seguimiento-egresados.index'));
        $resModulo->assertStatus(200);

        // 5. Super-admin revoca el rol
        $resRevocar = $this->actingAs($superAdmin)->put(route('adminUpdate', $docente->id), [
            'name' => $docente->name,
            'apellidos' => $docente->apellidos,
            'dni' => $docente->dni,
            'email' => $docente->email,
            'roles' => ['docente'],
        ]);
        $resRevocar->assertSessionHasNoErrors();

        // 6. Docente ya no tiene la opción en su dashboard
        $docente->refresh();
        $this->assertFalse($docente->hasRole('seguimiento-egresados'));

        $resDashboardFinal = $this->actingAs($docente)->get(route('vistaDocente', $docente->docente->id));
        $resDashboardFinal->assertDontSee('Seguimiento de Egresados — Puklla en Red');
    }
}
