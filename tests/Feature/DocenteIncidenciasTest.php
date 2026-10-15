<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * CRUD de incidencias del docente: las rutas exigen rol y perfil propio, y el
 * acceso real se comprueba con datos creados en la base de pruebas
 * (puklla_testing), dentro de una transacción que se descarta al terminar.
 */
class DocenteIncidenciasTest extends TestCase
{
    use DatabaseTransactions;

    private const RUTAS_PANELES = [
        'docente.incidencias.index',
        'docente.incidencias.create',
        'docente.incidencias.store',
        'docente.incidencias.show',
        'docente.incidencias.edit',
        'docente.incidencias.update',
        'docente.incidencias.destroy',
    ];

    private int $secuencia = 0;
    private ?int $alumnoId = null;
    private ?array $cicloIds = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['docente', 'alumno', 'admin'] as $rol) {
            Role::findOrCreate($rol, 'web');
        }
    }

    public function test_todas_las_rutas_de_incidencias_exigen_rol_y_perfil_propio(): void
    {
        foreach (self::RUTAS_PANELES as $name) {
            $route = collect(app('router')->getRoutes()->getRoutes())
                ->first(fn ($r) => $r->getName() === $name);

            $this->assertNotNull($route, "Falta la ruta {$name}");

            $middleware = $route->gatherMiddleware();
            $this->assertContains('role:docente|tutor|admin|super-admin', $middleware, "{$name} sin control de rol");
            $this->assertContains('docente.propio', $middleware, "{$name} sin control de propiedad");
        }
    }

    public function test_un_docente_abre_su_listado_y_no_el_de_otro_docente(): void
    {
        $a = $this->docenteConPerfil('A');
        $b = $this->docenteConPerfil('B');

        $this->actingAs($a)
            ->get(route('docente.incidencias.index', $a->docente->id))
            ->assertOk();

        $this->actingAs($a)
            ->get(route('docente.incidencias.index', $b->docente->id))
            ->assertForbidden();
    }

    public function test_un_docente_no_puede_ver_editar_ni_borrar_una_incidencia_ajena(): void
    {
        $a = $this->docenteConPerfil('A');
        $b = $this->docenteConPerfil('B');

        $ajena  = $this->incidenciaDe($b->docente);
        $propia = $this->incidenciaDe($a->docente);

        foreach (['show', 'edit'] as $action) {
            $this->actingAs($a)
                ->get(route("docente.incidencias.{$action}", [$a->docente->id, $ajena->id]))
                ->assertForbidden();
        }

        $this->actingAs($a)
            ->delete(route('docente.incidencias.destroy', [$a->docente->id, $ajena->id]))
            ->assertForbidden();

        $this->assertNotNull(Incidencia::find($ajena->id), 'Se eliminó una incidencia ajena.');

        // Las propias sí son accesibles: la guarda no está bloqueando de más.
        $this->actingAs($a)
            ->get(route('docente.incidencias.show', [$a->docente->id, $propia->id]))
            ->assertOk();

        $this->actingAs($a)
            ->get(route('docente.incidencias.edit', [$a->docente->id, $propia->id]))
            ->assertOk();
    }

    public function test_un_docente_actualiza_su_incidencia_sin_poder_cambiar_el_estado(): void
    {
        $a   = $this->docenteConPerfil('A');
        $inc = $this->incidenciaDe($a->docente);

        $this->actingAs($a)
            ->put(route('docente.incidencias.update', [$a->docente->id, $inc->id]), [
                'fecha'   => '2026-10-01',
                'reporte' => 'Reporte editado por el docente para la prueba.',
                'estado'  => 'atendido',
            ])
            ->assertRedirect(route('docente.incidencias.show', [$a->docente->id, $inc->id]));

        $actualizada = $inc->fresh();

        $this->assertSame('Reporte editado por el docente para la prueba.', $actualizada->reporte);
        $this->assertSame('2026-10-01', $actualizada->fecha->toDateString());
        $this->assertSame('pendiente', $actualizada->estado, 'El docente no debería cerrar su propia incidencia.');
    }

    private function docenteConPerfil(string $etiqueta): User
    {
        $dni = $this->dni();

        $user = User::create([
            'name'      => 'Docente ' . $etiqueta,
            'apellidos' => 'Protagonista ' . $etiqueta,
            'dni'       => $dni,
            'genero'    => 1,
            'email'     => 'docente-' . strtolower($etiqueta) . '-' . $dni . '@puklla.test',
            'password'  => Hash::make('secreto-de-prueba'),
        ]);
        $user->assignRole('docente');

        Docente::create([
            'user_id' => $user->id,
            'nombre'  => 'Docente ' . $etiqueta,
            'dni'     => $dni,
            'email'   => $user->email,
        ]);

        return $user->fresh('docente');
    }

    private function incidenciaDe(Docente $docente): Incidencia
    {
        [$programaId, $cicloId] = $this->ciclo();

        return Incidencia::create([
            'docente_id'     => $docente->id,
            'nombre_docente' => $docente->nombre,
            'alumno_id'      => $this->alumnoId($programaId, $cicloId),
            'programa_id'    => $programaId,
            'ciclo_id'       => $cicloId,
            'fecha'          => now()->toDateString(),
            'reporte'        => 'Incidencia de prueba para verificación de permisos.',
        ]);
    }

    /** @return array{0:int,1:int} ids de programa y ciclo de prueba */
    private function ciclo(): array
    {
        if ($this->cicloIds === null) {
            $programaId = $this->crearFila('programas', ['nombre' => 'Programa de prueba']);
            $cicloId    = $this->crearFila('ciclos', [
                'nombre'      => 'Ciclo de prueba',
                'programa_id' => $programaId,
            ]);
            $this->cicloIds = [$programaId, $cicloId];
        }

        return $this->cicloIds;
    }

    private function alumnoId(int $programaId, int $cicloId): int
    {
        if ($this->alumnoId === null) {
            $dni          = $this->dni();
            $this->alumnoId = $this->crearFila('alumnos', [
                'apellidos'    => 'Protagonista ' . $dni,
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

    /**
     * Inserta una fila completando desde el propio esquema las columnas
     * obligatorias que la prueba no menciona explícitamente.
     */
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
                str_contains($columna->Type, 'int')                    => 0,
                str_contains($columna->Type, 'date') || str_contains($columna->Type, 'time') => now()->toDateString(),
                default                                                => 'prueba',
            };
        }

        return DB::table($tabla)->insertGetId($datos);
    }

    private function dni(): string
    {
        return '7' . str_pad((string) (++$this->secuencia), 7, '0', STR_PAD_LEFT);
    }
}
