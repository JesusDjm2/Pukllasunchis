<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Comunicado;
use App\Models\Curso;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use App\Models\PeriodoUno;
use App\Models\User;
use App\Notifications\CalificacionRegistrada;
use App\Notifications\ComunicadoPublicado;
use App\Notifications\MatriculaVoucherVerificado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Verifica los 3 puntos reales donde se dispara una notificación hacia el
 * alumno (ver _notes/mobile-app-plan.md — sistema de notificaciones v1):
 * NotaParcialObserver, AdminController::verificarVoucherMatricula, y
 * ComunicadoController::store. El envío masivo de comunicados se prueba
 * directo (sin pasar por el endpoint HTTP, que sube un archivo real a
 * public/docs/comunicados — no queremos ese efecto secundario en los tests).
 */
class NotificacionesTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_nota_notifica_al_alumno(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $curso = Curso::factory()->create(['nombre' => 'Didáctica']);

        PeriodoUno::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'calificacion_curso' => '16',
        ]);

        Notification::assertSentTo(
            $user,
            CalificacionRegistrada::class,
            fn ($notification) => $notification->parcial === 'Parcial 1' && $notification->cursoNombre === 'Didáctica'
        );
    }

    public function test_crear_nota_sin_calificacion_aun_no_notifica(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $curso = Curso::factory()->create();

        // Fila creada por adelantado (p.ej. al asignar el curso) pero sin
        // nota todavía — no debería generar un "se registró tu nota".
        PeriodoUno::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'calificacion_curso' => null,
            'calificacion_sistema' => null,
        ]);

        Notification::assertNothingSent();
    }

    public function test_reguardar_la_misma_nota_no_duplica_la_notificacion(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $curso = Curso::factory()->create();

        $periodo = PeriodoUno::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'calificacion_curso' => '16',
        ]);

        // Reenvío del mismo formulario sin cambiar ningún valor — una
        // instancia nueva, como pasaría en una request real
        // (CalificacionController usa updateOrCreate, que recarga el
        // modelo; reusar el mismo objeto en memoria no resetea
        // wasRecentlyCreated y daría un falso positivo aquí).
        $periodo->fresh()->save();

        Notification::assertSentToTimes($user, CalificacionRegistrada::class, 1);
    }

    public function test_verificar_voucher_notifica_al_alumno(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $periodoActual = PeriodoActual::factory()->actual()->create(['nombre' => '2026-I']);
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'periodo_actual_id' => $periodoActual->id,
            'voucher_verificado' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('matriculas.verificarVoucher', $matricula))
            ->assertRedirect();

        Notification::assertSentTo(
            $user,
            MatriculaVoucherVerificado::class,
            fn ($notification) => str_contains($notification->toArray($user)['mensaje'], '2026-I')
        );
    }

    public function test_desverificar_voucher_no_notifica(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $periodoActual = PeriodoActual::factory()->actual()->create();
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'periodo_actual_id' => $periodoActual->id,
            'voucher_verificado' => true, // Ya estaba verificado -> este toggle lo quita.
        ]);

        $this->actingAs($admin)->post(route('matriculas.verificarVoucher', $matricula));

        Notification::assertNothingSent();
    }

    public function test_comunicado_publicado_solo_notifica_a_alumnos(): void
    {
        Notification::fake();

        $alumno1 = User::factory()->create();
        $alumno1->assignRole('alumno');
        $alumno2 = User::factory()->create();
        $alumno2->assignRole('alumno');
        $docente = User::factory()->create();
        $docente->assignRole('docente');

        $comunicado = Comunicado::factory()->create(['titulo' => 'Aviso importante']);

        // Misma llamada que ComunicadoController::store (ver ese archivo) —
        // probada directo para no subir un archivo real a public/ en los tests.
        Notification::send(User::role('alumno')->get(), new ComunicadoPublicado($comunicado));

        Notification::assertSentTo($alumno1, ComunicadoPublicado::class);
        Notification::assertSentTo($alumno2, ComunicadoPublicado::class);
        Notification::assertNotSentTo($docente, ComunicadoPublicado::class);
    }
}
