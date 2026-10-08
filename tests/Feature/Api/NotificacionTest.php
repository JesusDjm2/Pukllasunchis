<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\PeriodoUno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * No usa Notification::fake() a propósito: queremos confirmar que la
 * notificación realmente llega a la tabla `notifications` (vía
 * NotaParcialObserver -> canal `database`), no solo que "se intentó enviar".
 */
class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearNotificacion(User $user, string $cursoNombre = 'Curso de prueba'): void
    {
        $alumno = Alumno::firstOrCreate(['user_id' => $user->id], Alumno::factory()->raw());
        $curso = Curso::factory()->create(['nombre' => $cursoNombre]);

        PeriodoUno::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'calificacion_curso' => '16',
        ]);
    }

    public function test_lists_own_notifications_most_recent_first(): void
    {
        $user = User::factory()->create();
        $this->crearNotificacion($user, 'Primero');
        $this->travel(1)->seconds();
        $this->crearNotificacion($user, 'Segundo');

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notificaciones');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.datos.curso_nombre', 'Segundo')
            ->assertJsonPath('data.0.tipo', 'CalificacionRegistrada')
            ->assertJsonPath('data.0.leida', false)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total']]);
    }

    public function test_does_not_list_other_users_notifications(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $this->crearNotificacion($otro);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notificaciones');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_no_leidas_count(): void
    {
        $user = User::factory()->create();
        $this->crearNotificacion($user, 'Uno');
        $this->crearNotificacion($user, 'Dos');

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notificaciones/no-leidas-count');

        $response->assertOk()->assertJsonPath('data.count', 2);
    }

    public function test_marcar_leida(): void
    {
        $user = User::factory()->create();
        $this->crearNotificacion($user);

        $token = $user->createToken('test')->plainTextToken;
        $notificacionId = $user->notifications()->first()->id;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notificaciones/{$notificacionId}/leer");

        $response->assertOk()->assertJsonPath('data.leida', true);
        $this->assertNotNull($user->notifications()->first()->read_at);
    }

    public function test_no_puede_marcar_leida_una_notificacion_ajena(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $this->crearNotificacion($otro);

        $token = $user->createToken('test')->plainTextToken;
        $notificacionId = $otro->notifications()->first()->id;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notificaciones/{$notificacionId}/leer");

        $response->assertStatus(404);
        $this->assertNull($otro->notifications()->first()->read_at);
    }

    public function test_marcar_todas_leidas(): void
    {
        $user = User::factory()->create();
        $this->crearNotificacion($user, 'Uno');
        $this->crearNotificacion($user, 'Dos');

        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/notificaciones/marcar-todas-leidas')
            ->assertOk();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_unauthenticated_user_cannot_access_notificaciones(): void
    {
        $this->getJson('/api/v1/notificaciones')->assertStatus(401);
        $this->getJson('/api/v1/notificaciones/no-leidas-count')->assertStatus(401);
        $this->postJson('/api/v1/notificaciones/x/leer')->assertStatus(401);
        $this->postJson('/api/v1/notificaciones/marcar-todas-leidas')->assertStatus(401);
    }
}
