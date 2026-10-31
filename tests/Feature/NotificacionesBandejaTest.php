<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\User;
use App\Notifications\TutoriaCreada;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Bandeja de notificaciones de la web: solo las propias, filtro por sin leer y
 * las acciones de marcar leída / eliminar sobre una ajena no prosperan.
 */
class NotificacionesBandejaTest extends TestCase
{
    use DatabaseTransactions;

    private int $secuencia = 0;
    private User $destinatario;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['docente', 'admin', 'super-admin'] as $rol) {
            Role::findOrCreate($rol, 'web');
        }

        $this->destinatario = $this->docenteConPerfil();
    }

    public function test_la_bandeja_exige_haber_iniciado_sesion(): void
    {
        $this->get(route('notificaciones.index'))->assertRedirect(route('login'));
    }

    public function test_la_bandeja_muestra_solo_las_notificaciones_del_usuario(): void
    {
        $propia = $this->notificar($this->destinatario, ['alumno_nombre' => 'PROPIA, Usuario']);
        $ajena  = $this->notificar($this->docenteConPerfil(), ['alumno_nombre' => 'AJENA, Usuario']);

        $respuesta = $this->actingAs($this->destinatario)
            ->get(route('notificaciones.index'))
            ->assertOk()
            ->assertSee('PROPIA, Usuario')
            ->assertDontSee('AJENA, Usuario');

        $this->assertNotEmpty($propia);
        $this->assertNotEmpty($ajena);
    }

    public function test_el_filtro_de_sin_leer_deja_fuera_las_ya_leidas(): void
    {
        $this->notificar($this->destinatario, ['alumno_nombre' => 'PENDIENTE, Visibilidad']);
        $this->notificar($this->destinatario, ['alumno_nombre' => 'LEIDA, Visibilidad'], true);

        // Se comprueba la vista, no el HTML: la campanita del layout siempre
        // muestra sus 8 últimas notificaciones, leídas o no.
        $this->actingAs($this->destinatario)
            ->get(route('notificaciones.index', ['filtro' => 'no-leidas']))
            ->assertOk()
            ->assertViewHas('notificaciones', function ($pagina) {
                return $pagina->count() === 1
                    && $pagina->first()->data['alumno_nombre'] === 'PENDIENTE, Visibilidad';
            })
            ->assertViewHas('noLeidas', 1);
    }

    public function test_un_usuario_no_puede_marcar_leida_ni_eliminar_una_notificacion_ajena(): void
    {
        $otro = $this->docenteConPerfil();
        $id   = $this->notificar($otro, ['alumno_nombre' => 'blindaje']);

        $this->actingAs($this->destinatario)
            ->post(route('notificaciones.marcarLeida', $id))
            ->assertNotFound();

        $this->actingAs($this->destinatario)
            ->delete(route('notificaciones.eliminar', $id))
            ->assertNotFound();

        $this->assertNull($otro->notifications()->first()->read_at, 'Se marcó como leída una notificación ajena.');
        $this->assertDatabaseHas('notifications', ['id' => $id]);
    }

    public function test_marcar_leida_y_eliminar_sobre_la_notificacion_propia(): void
    {
        $id = $this->notificar($this->destinatario, ['alumno_nombre' => 'Cierre, Propio']);

        $this->actingAs($this->destinatario)
            ->post(route('notificaciones.marcarLeida', $id))
            ->assertRedirect();

        $this->assertNotNull($this->destinatario->notifications()->find($id)->read_at);

        $this->actingAs($this->destinatario)
            ->delete(route('notificaciones.eliminar', $id))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $id]);
    }

    public function test_la_bandeja_tambien_se_abre_desde_el_panel_de_administracion(): void
    {
        $admin = $this->crearUsuario('admin');
        $admin->assignRole('admin');
        $this->notificar($admin, ['alumno_nombre' => 'Administrada, Panel']);

        $this->actingAs($admin)
            ->get(route('notificaciones.index'))
            ->assertOk()
            ->assertSee('Administrada, Panel');
    }

    public function test_la_bandeja_tambien_se_abre_desde_el_panel_de_super_administracion(): void
    {
        $super = $this->crearUsuario();
        $super->assignRole('super-admin');
        $this->notificar($super, ['alumno_nombre' => 'Supervisada, Panel']);

        $this->actingAs($super)
            ->get(route('notificaciones.index'))
            ->assertOk()
            ->assertSee('Supervisada, Panel');
    }

    /** @return string id de la notificación creada */
    private function notificar(User $user, array $data, bool $leida = false): string
    {
        return $user->notifications()->create([
            'id'      => (string) Str::uuid(),
            'type'    => TutoriaCreada::class,
            'data'    => $data + ['resumen' => 'Resumen de prueba de la bandeja.'],
            'read_at' => $leida ? now() : null,
        ])->id;
    }

    private function docenteConPerfil(): User
    {
        $user = $this->crearUsuario();
        $user->assignRole('docente');

        Docente::create([
            'user_id' => $user->id,
            'nombre'  => $user->name,
            'dni'     => $user->dni,
            'email'   => $user->email,
        ]);

        return $user->fresh('docente');
    }

    private function crearUsuario(): User
    {
        $dni = '7' . str_pad((string) (++$this->secuencia), 7, '0', STR_PAD_LEFT);

        return User::create([
            'name'      => 'Docente ' . $dni,
            'apellidos' => 'Bandeja ' . $dni,
            'dni'       => $dni,
            'genero'    => 1,
            'email'     => 'bandeja-' . $dni . '@puklla.test',
            'password'  => Hash::make('secreto-de-prueba'),
        ]);
    }
}
