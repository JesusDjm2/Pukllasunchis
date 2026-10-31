<?php

namespace Tests\Feature\Api;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Incidencia;
use App\Models\PeriodoActual;
use App\Models\Silabo;
use App\Models\User;
use App\Notifications\TutoriaCreada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Área docente de la API móvil: el resumen y los cursos que sustituyen al
 * dashboard de alumno, que para un docente devuelve 404 en todos los endpoints.
 */
class DocenteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['docente', 'tutor', 'admin', 'super-admin', 'alumno'] as $rol) {
            Role::findOrCreate($rol, 'web');
        }
    }

    public function test_el_resumen_cuenta_cursos_incidencias_y_notificaciones(): void
    {
        $docente = $this->docenteConCursos();

        Incidencia::factory()->create([
            'docente_id' => $docente->docente->id,
            'estado' => 'pendiente',
        ]);
        Incidencia::factory()->create([
            'docente_id' => $docente->docente->id,
            'estado' => 'atendido',
        ]);
        Incidencia::factory()->create();

        $this->notificar($docente);

        $this->actingAs($docente)
            ->getJson('/api/v1/docente/resumen')
            ->assertOk()
            ->assertJsonPath('data.cursos', 2)
            ->assertJsonPath('data.cursos_fid', 1)
            ->assertJsonPath('data.cursos_ppd', 1)
            ->assertJsonPath('data.incidencias_total', 2)
            ->assertJsonPath('data.incidencias_pendientes', 1)
            ->assertJsonPath('data.notificaciones_sin_leer', 1);
    }

    public function test_los_cursos_del_docente_traen_area_y_estado_de_silabo(): void
    {
        $docente = $this->docenteConCursos();

        $this->actingAs($docente)
            ->getJson('/api/v1/docente/cursos')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.area', 'fid')
            ->assertJsonPath('data.0.silabo_listo', false)
            ->assertJsonPath('data.0.progreso.parcial_1', 0)
            ->assertJsonPath('data.1.area', 'ppd')
            ->assertJsonPath('data.1.progreso.calificado', 0);
    }

    /**
     * Regresión: `tieneSilabo()` no debe contar un sílabo de un periodo
     * anterior como "listo" para el periodo actual, igual que
     * `docentes.partials.dashboard-curso-fid` en la web.
     */
    public function test_un_silabo_de_un_periodo_anterior_no_cuenta_como_listo(): void
    {
        $docente = $this->docenteConCursos();
        $periodoActual = PeriodoActual::factory()->create(['actual' => true]);

        $cursoFidId = $docente->docente->cursos()->first()->id;

        Silabo::create([
            'curso_id' => $cursoFidId,
            'nombre' => 'Sílabo antiguo',
            'periodo' => 'Periodo pasado',
        ]);

        $this->actingAs($docente)
            ->getJson('/api/v1/docente/cursos')
            ->assertOk()
            ->assertJsonPath('data.0.silabo_listo', false);
    }

    public function test_un_alumno_no_entra_al_area_docente(): void
    {
        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $this->actingAs($alumno)->getJson('/api/v1/docente/resumen')->assertForbidden();
        $this->actingAs($alumno)->getJson('/api/v1/docente/cursos')->assertForbidden();
    }

    public function test_un_docente_sin_perfil_recibe_404(): void
    {
        $usuario = User::factory()->create();
        $usuario->assignRole('docente');

        $this->actingAs($usuario)->getJson('/api/v1/docente/resumen')->assertNotFound();
    }

    public function test_sin_token_no_se_accede_al_area_docente(): void
    {
        $this->getJson('/api/v1/docente/resumen')->assertUnauthorized();
    }

    /** @return User usuario docente con un curso FID y uno PPD asignados */
    private function docenteConCursos(): User
    {
        $user = User::factory()->create();
        $user->assignRole('docente');

        $docente = Docente::factory()->create(['user_id' => $user->id]);

        $user->setRelation('docente', $docente);

        $docente->cursos()->attach([
            $this->curso('Curso FID', 'Educación Secundaria'),
            $this->curso('Curso PPD', 'PPD Profesionalización'),
        ]);

        return $user;
    }

    private function curso(string $nombre, string $programa): int
    {
        return Curso::factory()->create([
            'nombre' => $nombre,
            'ciclo_id' => \App\Models\Ciclo::factory()->create(['programa_id' => \App\Models\Programa::factory()->create(['nombre' => $programa])->id]),
        ])->id;
    }

    private function notificar(User $user): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => TutoriaCreada::class,
            'data' => ['titulo' => 'Aviso de prueba', 'resumen' => 'Para la métrica.'],
        ]);
    }
}
