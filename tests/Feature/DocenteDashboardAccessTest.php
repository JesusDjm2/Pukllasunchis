<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\User;
use App\Policies\DocentePolicy;
use Tests\TestCase;

/**
 * Fija quién puede entrar a cada pantalla del panel docente.
 * No toca la base de datos: verifica el middleware declarado en las rutas y
 * la lógica de pertenencia de la política con modelos en memoria.
 */
class DocenteDashboardAccessTest extends TestCase
{
    private const PANEL_DOCENTE = [
        'vistaDocente',
        'docente.edit',
        'docente.update',
        'calificar',
        'repositorio',
        'docente.alumnos',
        'docente.blog.show',
        'competencias.calificar',
        'competencias.calificar.ppd',
        'docentes.cursos.alumnos',
        'docente.incidencias.index',
        'docente.incidencias.create',
        'docente.incidencias.store',
    ];

    private const SOLO_ADMINISTRACION = [
        'docente.index',
        'docente.create',
        'docente.store',
        'docente.destroy',
        'docente.curso.eliminar',
        'asignar',
        'cursos.asignar',
        'curso.docentes.form',
        'admin.docente.incidencias',
    ];

    private const BORRADOS_MASIVOS = [
        'docente.cursos.eliminarTodosGlobal',
        'docente.cursos.eliminarTodosGlobalPPD',
        'calificaciones.eliminarTodas',
    ];

    private function middlewareOf(string $routeName): array
    {
        $route = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($r) => $r->getName() === $routeName);

        $this->assertNotNull($route, "La ruta '{$routeName}' no está registrada.");

        return $route->gatherMiddleware();
    }

    public function test_el_panel_docente_exige_rol_y_perfil_propio(): void
    {
        foreach (self::PANEL_DOCENTE as $name) {
            $middleware = $this->middlewareOf($name);

            $this->assertContains('role:docente|tutor|admin|super-admin', $middleware, "{$name} no exige rol");
            $this->assertContains('docente.propio', $middleware, "{$name} no exige perfil propio");
        }
    }

    public function test_la_gestion_de_docentes_es_solo_de_administracion(): void
    {
        foreach (self::SOLO_ADMINISTRACION as $name) {
            $this->assertContains('role:admin|super-admin', $this->middlewareOf($name), "{$name} debería ser solo admin");
        }
    }

    public function test_los_borrados_masivos_son_solo_de_administracion(): void
    {
        foreach (self::BORRADOS_MASIVOS as $name) {
            $this->assertContains('role:admin|super-admin', $this->middlewareOf($name), "{$name} debería ser solo admin");
        }
    }

    public function test_un_alumno_sigue_pudiendo_ver_el_perfil_de_su_docente(): void
    {
        $this->assertContains(
            'role:docente|tutor|admin|super-admin|alumno|alumnoB',
            $this->middlewareOf('docente.show')
        );
    }

    public function test_la_politica_permite_al_docente_sobre_su_perfil_y_a_la_administracion_sobre_todos(): void
    {
        $policy = new DocentePolicy();
        $propio = $this->docente(7);
        $ajeno = $this->docente(8);

        $this->assertTrue($policy->gestionar($this->user(['docente'], $propio), $propio));
        $this->assertFalse($policy->gestionar($this->user(['docente'], $propio), $ajeno));
        $this->assertFalse($policy->gestionar($this->user(['alumno']), $propio));

        $admin = $this->user(['admin']);
        $this->assertTrue($policy->gestionar($admin, $propio));
        $this->assertTrue($policy->gestionar($admin, $ajeno));
        $this->assertTrue($policy->delete($admin, $ajeno));
        $this->assertFalse($policy->delete($this->user(['docente'], $propio), $ajeno));
    }

    public function test_la_politica_deja_ver_el_perfil_a_los_alumnos(): void
    {
        $policy = new DocentePolicy();

        $this->assertTrue($policy->view($this->user(['alumno']), $this->docente(7)));
        $this->assertTrue($policy->view($this->user(['alumnoB']), $this->docente(7)));
        $this->assertFalse($policy->view($this->user(['inhabilitado']), $this->docente(7)));
    }

    private function docente(int $id): Docente
    {
        $docente = new Docente();
        $docente->id = $id;

        return $docente;
    }

    /** Usuario en memoria con roles simulados, sin pasar por Spatie ni la BD. */
    private function user(array $roles, ?Docente $docente = null): User
    {
        $user = new class extends User {
            public array $roleNames = [];
            public $docente;

            public function hasAnyRole(...$roles): bool
            {
                return array_intersect((array) ($roles[0] ?? []), $this->roleNames) !== [];
            }

            public function hasRole(...$roles): bool
            {
                return array_intersect((array) ($roles[0] ?? []), $this->roleNames) !== [];
            }
        };

        $user->roleNames = $roles;
        $user->docente = $docente;

        return $user;
    }
}
