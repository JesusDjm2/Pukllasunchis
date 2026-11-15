<?php

namespace Tests\Feature\Api;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MiTitulacionSolicitudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
        Mail::fake();
        // Sin salir a Google en los tests: Drive "falla" y el comprobante se guarda en el servidor (respaldo de la web).
        $this->mock(GoogleDriveService::class, fn ($m) => $m->shouldReceive('carpeta')->andThrow(new \RuntimeException('sin Drive')));
    }

    private function egresado(array $extra = []): User
    {
        return User::factory()->create($extra + ['condicion' => 'Egresado', 'email' => 'egresado'.uniqid().'@pukllavirtual.edu.pe']);
    }

    private function conPtiAprobado(User $user): SeguimientoTitulacion
    {
        $s = SeguimientoTitulacion::create(['user_id' => $user->id, 'estado_titulacion_id' => DB::table('estados_titulacion')->value('id')]);
        Pti::create(['seguimiento_id' => $s->id, 'titulo' => 'Mi investigación formativa', 'fecha_pti' => now()->subMonths(2)->toDateString(), 'rd_pti' => 'RD-010-2026', 'estado' => 'aprobado']);

        return $s;
    }

    private function datosPti(array $extra = []): array
    {
        return array_merge([
            'celulares' => '987654321',
            'programa_estudios' => 'Educación Inicial',
            'ciclo' => 'Egresado',
            'area' => 'D2',
            'linea' => 'D2-3',
            'titulo' => 'La evaluación formativa en el aula de inicial',
            'pti' => UploadedFile::fake()->create('pti.pdf', 200, 'application/pdf'),
            'comprobantes' => [UploadedFile::fake()->image('voucher.jpg')],
            'declaracion' => '1',
        ], $extra);
    }

    // ── PTI ──

    public function test_formulario_pti_para_egresado_sin_proceso(): void
    {
        Sanctum::actingAs($this->egresado());

        $this->getJson('/api/v1/alumno/titulacion/solicitud-pti')
            ->assertOk()
            ->assertJsonPath('data.motivo_no_puede', null)
            ->assertJsonPath('data.ultima', null)
            ->assertJsonPath('data.valores.ciclo', 'Egresado')
            ->assertJsonPath('data.opciones.areas.0.valor', 'D1')
            ->assertJsonPath('data.opciones.lineas.0.valor', 'D1-1');
    }

    public function test_enviar_pti_guarda_la_solicitud_y_sus_archivos(): void
    {
        $user = $this->egresado();
        Sanctum::actingAs($user);

        $this->post('/api/v1/alumno/titulacion/solicitud-pti', $this->datosPti(), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.estado', 'recibida');

        $s = Solicitud::ptiDe($user)->first();
        $this->assertNotNull($s);
        $this->assertSame('sistema', $s->origen);
        $this->assertSame(['area' => 'D2', 'linea' => 'D2-3'], $s->datos);
        $this->assertSame($user->id, $s->integrantes[0]['user_id']);
        Storage::disk('local')->assertExists(Solicitud::CARPETA.'/'.\Illuminate\Support\Str::after($s->archivo, 'local:'));
    }

    public function test_no_se_envia_otro_pti_mientras_hay_uno_en_revision(): void
    {
        Sanctum::actingAs($this->egresado());
        $this->post('/api/v1/alumno/titulacion/solicitud-pti', $this->datosPti(), ['Accept' => 'application/json'])->assertCreated();

        $this->post('/api/v1/alumno/titulacion/solicitud-pti', $this->datosPti(), ['Accept' => 'application/json'])
            ->assertStatus(409);
    }

    public function test_pti_rechaza_linea_de_otra_area(): void
    {
        Sanctum::actingAs($this->egresado());

        $this->post('/api/v1/alumno/titulacion/solicitud-pti', $this->datosPti(['linea' => 'D4-1']), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('linea');
    }

    public function test_alumno_que_aun_no_puede_iniciar_no_envia_pti(): void
    {
        $user = User::factory()->create(['condicion' => 'Regular']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alumno/titulacion/solicitud-pti')->assertOk()
            ->assertJsonPath('data.motivo_no_puede', fn ($m) => is_string($m) && $m !== '');
        $this->post('/api/v1/alumno/titulacion/solicitud-pti', $this->datosPti(), ['Accept' => 'application/json'])
            ->assertStatus(409);
        $this->assertSame(0, Solicitud::count());
    }

    // ── Carta ──

    public function test_enviar_carta_con_pti_aprobado(): void
    {
        $user = $this->egresado();
        $seguimiento = $this->conPtiAprobado($user);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alumno/titulacion/carta')->assertOk()
            ->assertJsonPath('data.motivo_no_puede', null)
            ->assertJsonPath('data.valores.titulo', 'Mi investigación formativa');

        $this->post('/api/v1/alumno/titulacion/carta', [
            'grupo' => 'Ana Quispe Mamani',
            'celular' => '987654321',
            'programa_estudios' => 'Educación Inicial',
            'programa_formacion' => 'FID',
            'institucion' => 'I.E. Inicial N° 123 Cusco',
            'director' => 'María Huamán Ccori',
            'titulo' => 'Mi investigación formativa',
            'comprobante' => UploadedFile::fake()->image('comprobante.png'),
            'declaracion' => '1',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.estado', 'enviada');

        $carta = CartaAceptacion::where('seguimiento_id', $seguimiento->id)->first();
        $this->assertNotNull($carta);
        Storage::disk('local')->assertExists(CartaAceptacion::CARPETA.'/'.$carta->comprobante);
        Mail::assertSent(\App\Mail\Titulacion\CartaRecibidaMail::class);
    }

    public function test_carta_exige_correo_institucional(): void
    {
        $user = $this->egresado(['email' => 'personal'.uniqid().'@gmail.com']);
        $this->conPtiAprobado($user);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alumno/titulacion/carta')->assertOk()
            ->assertJsonPath('data.motivo_no_puede', fn ($m) => str_contains($m, 'correo institucional'));
    }

    // ── Originalidad ──

    public function test_originalidad_apagada_remite_al_formulario_de_google(): void
    {
        config(['titulacion.originalidad_alumnos' => false]);
        $user = $this->egresado();
        $this->conPtiAprobado($user);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alumno/titulacion/originalidad')->assertOk()
            ->assertJsonPath('data.habilitada', false);
        $this->post('/api/v1/alumno/titulacion/originalidad', [], ['Accept' => 'application/json'])->assertStatus(409);
    }

    public function test_enviar_originalidad_cuando_esta_habilitada(): void
    {
        config(['titulacion.originalidad_alumnos' => true]);
        $user = $this->egresado();
        $seguimiento = $this->conPtiAprobado($user);
        Sanctum::actingAs($user);

        $this->post('/api/v1/alumno/titulacion/originalidad', [
            'etapa' => 'ti',
            'titulo' => 'Mi investigación formativa',
            'celulares' => '987654321',
            'fecha_pago' => now()->toDateString(),
            'documento' => UploadedFile::fake()->create('ti.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'comprobantes' => [UploadedFile::fake()->create('pago.pdf', 50, 'application/pdf')],
            'declaracion' => '1',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.estado', 'recibida')
            ->assertJsonPath('data.etapa', 'ti');

        $this->assertSame($seguimiento->id, Solicitud::dePersona(Solicitud::ORIGINALIDAD, $user)->first()->seguimiento_id);
    }
}
