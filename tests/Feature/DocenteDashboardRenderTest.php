<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\User;
use App\Notifications\TutoriaCreada;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Red del dashboard del docente: la vista se arma con cursos FID y PPD reales.
 * Sirve de malla de seguridad para el refactor de docentes/show.blade.php, que
 * concentra casi todo el renderizado de la pantalla.
 */
class DocenteDashboardRenderTest extends TestCase
{
    use DatabaseTransactions;

    private int $secuencia = 0;
    private array $ciclosPorPrograma = [];
    private ?int $alumnoId = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['docente', 'admin', 'super-admin'] as $rol) {
            Role::findOrCreate($rol, 'web');
        }
    }

    public function test_el_dashboard_muestra_los_cursos_fid_y_ppd_del_docente(): void
    {
        $docente = $this->docenteConPerfil();

        $this->cursoAsignadoA($docente, 'Curso FID visible', 'Programa FID');
        $this->cursoAsignadoA($docente, 'Curso PPD visible', 'PPD Programa');
        $this->crearFila('periodo_actual', ['nombre' => '2026-II', 'actual' => true]);

        $this->actingAs($docente->user)
            ->get(route('vistaDocente', $docente->id))
            ->assertOk()
            ->assertSee('Curso FID visible')
            ->assertSee('Curso PPD visible')
            ->assertSee('Hola,')
            // El CSS propio del panel viaja en el stack de estilos del layout.
            ->assertSee('.docente-dash-hero h1', false);
    }

    public function test_el_dashboard_no_consulta_el_periodo_actual_por_cada_curso(): void
    {
        $docente = $this->docenteConPerfil();
        $this->cursoAsignadoA($docente, 'Curso Uno', 'Programa FID');
        $this->cursoAsignadoA($docente, 'Curso Dos', 'Programa FID');
        $this->cursoAsignadoA($docente, 'Curso Tres', 'Programa FID');
        $this->crearFila('periodo_actual', ['nombre' => '2026-II', 'actual' => true]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($docente->user)->get(route('vistaDocente', $docente->id))->assertOk();

        $consultasPeriodo = collect(DB::getQueryLog())
            ->filter(fn (array $q) => str_contains($q['query'], 'from `periodo_actual`'))
            ->count();

        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            2,
            $consultasPeriodo,
            "El dashboard repite la consulta de periodo_actual por cada curso ({$consultasPeriodo} veces)."
        );
    }

    public function test_las_tarjetas_de_metricas_reflejan_los_datos_del_docente(): void
    {
        $docente = $this->docenteConPerfil();

        $this->cursoAsignadoA($docente, 'Curso FID uno', 'Programa FID');
        $this->cursoAsignadoA($docente, 'Curso FID dos', 'Programa FID');
        $this->cursoAsignadoA($docente, 'Curso PPD uno', 'PPD Programa');

        [$programaId, $cicloId] = $this->programaConCiclo('Programa FID');

        DB::table('incidencias')->insert([
            'docente_id'     => $docente->id,
            'nombre_docente' => $docente->nombre,
            'alumno_id'      => $this->alumnoDe($programaId, $cicloId),
            'programa_id'    => $programaId,
            'ciclo_id'       => $cicloId,
            'fecha'          => now()->toDateString(),
            'reporte'        => 'Incidencia contada por las métricas del dashboard.',
            'estado'         => 'pendiente',
        ]);

        $docente->user->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => TutoriaCreada::class,
            'data' => ['titulo' => 'Aviso de prueba', 'resumen' => 'Sin leer para la métrica.'],
        ]);

        $this->crearFila('cursos_especiales', [
            'nombre'     => 'Taller asincrónico de prueba',
            'docente_id' => $docente->id,
        ]);

        $this->actingAs($docente->user)
            ->get(route('vistaDocente', $docente->id))
            ->assertOk()
            ->assertViewHas('metricas', fn (array $m) => $m['cursos'] === 3
                && $m['cursosFid'] === 2
                && $m['cursosPpd'] === 1
                && $m['asincronicos'] === 1
                && $m['incidencias'] === 1
                && $m['incidenciasPendientes'] === 1
                && $m['notificaciones'] === 1)
            ->assertSee('Cursos a tu cargo')
            ->assertSee('2 FID · 1 PPD', false);
    }

    private function docenteConPerfil(): Docente
    {
        $dni  = '7' . str_pad((string) (++$this->secuencia), 7, '0', STR_PAD_LEFT);
        $user = User::create([
            'name'      => 'Docente ' . $dni,
            'apellidos' => 'Panel ' . $dni,
            'dni'       => $dni,
            'genero'    => 1,
            'email'     => 'panel-' . $dni . '@puklla.test',
            'password'  => Hash::make('secreto-de-prueba'),
        ]);
        $user->assignRole('docente');

        $docente = Docente::create([
            'user_id' => $user->id,
            'nombre'  => 'Docente ' . $dni,
            'dni'     => $dni,
            'email'   => $user->email,
        ]);

        $user->setRelation('docente', $docente);

        return $docente;
    }

    private function cursoAsignadoA(Docente $docente, string $nombre, string $programa): int
    {
        [, $cicloId] = $this->programaConCiclo($programa);

        $cursoId = $this->crearFila('cursos', [
            'nombre'   => $nombre,
            'cc'       => '01',
            'ciclo_id' => $cicloId,
        ]);

        DB::table('curso_docente')->insert(['curso_id' => $cursoId, 'docente_id' => $docente->id]);

        return $cursoId;
    }

    /** @return array{0:int,1:int} ids de programa y ciclo, creados una sola vez */
    private function programaConCiclo(string $programa): array
    {
        if (!isset($this->ciclosPorPrograma[$programa])) {
            $programaId = $this->crearFila('programas', ['nombre' => $programa]);
            $cicloId    = $this->crearFila('ciclos', [
                'nombre'      => 'Ciclo ' . $programa,
                'programa_id' => $programaId,
            ]);
            $this->ciclosPorPrograma[$programa] = [$programaId, $cicloId];
        }

        return $this->ciclosPorPrograma[$programa];
    }

    private function alumnoDe(int $programaId, int $cicloId): int
    {
        if ($this->alumnoId === null) {
            $dni          = '9' . str_pad((string) (++$this->secuencia), 7, '0', STR_PAD_LEFT);
            $this->alumnoId = $this->crearFila('alumnos', [
                'apellidos'    => 'Panel ' . $dni,
                'nombres'      => 'Alumno de prueba',
                'dni'          => $dni,
                'email'        => 'alumno-' . $dni . '@puklla.test',
                'numero'       => $dni,
                'programa_id'  => $programaId,
                'ciclo_id'     => $cicloId,
            ]);
        }

        return $this->alumnoId;
    }

    private function crearFila(string $tabla, array $datos): int
    {
        foreach (DB::select("SHOW FULL COLUMNS FROM {$tabla}") as $columna) {
            $nombre = $columna->Field;

            if (array_key_exists($nombre, $datos)
                || $columna->Null === 'YES'
                || $columna->Default !== null
                || $columna->Extra === 'auto_increment') {
                continue;
            }

            $datos[$nombre] = match (true) {
                str_contains($columna->Type, 'int') => 0,
                str_contains($columna->Type, 'date') || str_contains($columna->Type, 'time') => now()->toDateString(),
                default => 'prueba',
            };
        }

        return DB::table($tabla)->insertGetId($datos);
    }
}
