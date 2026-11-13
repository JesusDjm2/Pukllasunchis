<?php

namespace Tests\Feature;

use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\BolsaTrabajoOferta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RedPukllaComunidadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super-admin', 'admin', 'docente', 'tutor', 'adminB', 'secretaria', 'gestor-titulacion', 'egresado', 'alumno'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }
    }

    private function crearEgresadoConfirmado(string $nombre = 'Egresada Red'): User
    {
        $u = User::factory()->create([
            'name' => $nombre,
            'apellidos' => 'Puklla',
            'dni' => (string) rand(40000000, 49999999),
            'email' => 'eg_red_' . rand(10000, 99999) . '@pukllavirtual.edu.pe',
            'condicion' => 'Egresado',
        ]);
        $u->assignRole('egresado');

        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $u->id,
            'registrado_por' => $u->id,
            'datos' => json_encode([
                'contacto' => [
                    'programa' => 'Educación Inicial',
                    'egreso' => 2024,
                    'correo' => $u->email,
                ],
            ]),
            'confirmado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $u;
    }

    private function crearDocente(string $nombre = 'Docente Red'): User
    {
        $u = User::factory()->create([
            'name' => $nombre,
            'apellidos' => 'Puklla',
            'dni' => (string) rand(70000000, 79999999),
            'email' => 'doc_red_' . rand(10000, 99999) . '@pukllavirtual.edu.pe',
            'condicion' => null,
        ]);
        $u->assignRole('docente');

        return $u;
    }

    public function test_egresado_sin_confirmar_es_redirigido_a_mi_espacio_y_docente_entra_directo(): void
    {
        $egSinConfirmar = User::factory()->create([
            'name' => 'Egresado Nuevo',
            'apellidos' => 'Sin Confirmar',
            'dni' => (string) rand(40000000, 49999999),
            'email' => 'eg_nuevo_' . rand(10000, 99999) . '@pukllavirtual.edu.pe',
            'condicion' => 'Egresado',
        ]);
        $egSinConfirmar->assignRole('egresado');

        $this->actingAs($egSinConfirmar)
            ->get(route('red.inicio'))
            ->assertRedirect(route('seguimiento.mi-espacio'));

        // Confirmar núcleo (programa, año y correo) desbloquea acceso sin requerir campos voluntarios
        $this->actingAs($egSinConfirmar)
            ->postJson(route('seguimiento.mi-espacio.guardar'), [
                'contacto' => [
                    'programa' => 'Educación Primaria EIB',
                    'egreso' => 2024,
                    'correo' => $egSinConfirmar->email,
                ],
            ])->assertOk();

        $this->actingAs($egSinConfirmar)
            ->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Red Puklla de Egresados');

        // Docente general entra directo sin formulario de egresado
        $docente = $this->crearDocente();
        $this->actingAs($docente)
            ->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Red Puklla de Egresados');
    }

    public function test_publicaciones_categorias_intenciones_aportes_y_aislamiento_de_ambito(): void
    {
        $egresada = $this->crearEgresadoConfirmado();
        $docente = $this->crearDocente();

        $conteoEstudiantilAntes = AylluPublicacion::count();

        $res = $this->actingAs($egresada)->post(route('red.publicar'), [
            'titulo' => 'Experiencia de lectoescritura en quechua',
            'contenido' => 'Comparto materiales trabajados en aula multigrado.',
            'resumen_quechua' => 'Allinllachu yachachiqkuna',
            'categoria_red' => 'yachay',
            'intencion_red' => 'colaboracion',
            'enlace_externo' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $post = AylluPublicacion::deRedPuklla()->where('user_id', $egresada->id)->firstOrFail();
        $res->assertRedirect(route('red.publicacion', ['id' => $post->id]));

        // El scope global evita que aparezca en el muro estudiantil general
        $this->assertSame($conteoEstudiantilAntes, AylluPublicacion::count());

        // Docente comenta y registra participación concreta
        $this->actingAs($docente)->post(route('red.comentar', ['id' => $post->id]), [
            'contenido' => 'Excelente material para ciclo III.',
        ])->assertRedirect();

        $this->actingAs($docente)->post(route('red.accion', ['id' => $post->id]), [
            'aporte' => 'Puedo adaptar las fichas para ciclo IV.',
            'estado' => 'en_curso',
        ])->assertRedirect();

        $this->assertDatabaseHas('red_puklla_acciones', [
            'publicacion_id' => $post->id,
            'user_id' => $docente->id,
            'estado' => 'en_curso',
        ]);
    }

    public function test_ayllus_privados_solicitudes_difusion_y_restriccion_de_audiencia(): void
    {
        $facilitadora = $this->crearEgresadoConfirmado('Facilitadora');
        $solicitante = $this->crearEgresadoConfirmado('Solicitante');
        $miembro = $this->crearEgresadoConfirmado('Miembro');

        $this->actingAs($facilitadora)->post(route('red.grupos.crear'), [
            'nombre' => 'Círculo EIB Canchis',
            'descripcion' => 'Docentes egresados trabajando en Canchis.',
            'tipo' => 'promocion',
            'promocion_red' => 2023,
            'privacidad_red' => 'privado',
        ])->assertRedirect();

        $grupoPrivado = AylluGrupo::deRedPuklla()->where('creado_por', $facilitadora->id)->firstOrFail();

        // Solicitante pide unirse -> queda pendiente
        $this->actingAs($solicitante)->post(route('red.membresia', ['id' => $grupoPrivado->id]), [
            'accion' => 'unir',
        ])->assertRedirect();

        // Solicitante pendiente no puede publicar ni enviar chat en grupo privado
        $this->actingAs($solicitante)->post(route('red.mensaje', ['id' => $grupoPrivado->id]), [
            'mensaje' => 'Intento sin aceptación',
        ])->assertForbidden();

        // Facilitadora publica dentro del grupo privado
        $this->actingAs($facilitadora)->post(route('red.publicar'), [
            'titulo' => 'Acuerdo interno del ayllu',
            'contenido' => 'Solo para integrantes aceptados.',
            'categoria_red' => 'ruray',
            'intencion_red' => 'encuentro',
            'ayllu_grupo_id' => $grupoPrivado->id,
        ])->assertRedirect();

        $postPrivado = AylluPublicacion::deRedPuklla()->where('ayllu_grupo_id', $grupoPrivado->id)->firstOrFail();

        // Solicitante pendiente no puede abrir la publicación privada por URL directa (404)
        $this->actingAs($solicitante)->get(route('red.publicacion', ['id' => $postPrivado->id]))->assertNotFound();

        // Facilitadora acepta al solicitante
        $this->actingAs($facilitadora)->post(route('red.membresia', ['id' => $grupoPrivado->id]), [
            'accion' => 'aceptar',
            'user_id' => $solicitante->id,
        ])->assertRedirect();

        // Ahora sí puede ver la publicación privada
        $this->actingAs($solicitante)->get(route('red.publicacion', ['id' => $postPrivado->id]))->assertOk();

        // No se permite difundir una publicación privada hacia otro ayllu distinto
        $this->actingAs($facilitadora)->post(route('red.grupos.crear'), [
            'nombre' => 'Ayllu Abierto General',
            'descripcion' => 'Abierto a todos.',
            'tipo' => 'intercultural',
            'privacidad_red' => 'abierto',
        ]);
        $grupoAbierto = AylluGrupo::deRedPuklla()->where('nombre', 'Ayllu Abierto General')->firstOrFail();

        $this->actingAs($facilitadora)->post(route('red.difundir', ['id' => $postPrivado->id]), [
            'grupo_id' => $grupoAbierto->id,
        ])->assertForbidden();
    }

    public function test_oportunidades_guardados_privados_avisos_y_moderacion_con_trazabilidad(): void
    {
        $autor = $this->crearEgresadoConfirmado('Autor Oferta');
        $lectora = $this->crearEgresadoConfirmado('Lectora Oferta');
        $admin = User::factory()->create([
            'name' => 'Admin Moderador',
            'apellidos' => 'Puklla',
            'dni' => (string) rand(10000000, 19999999),
            'email' => 'admin_mod_' . rand(1000, 9999) . '@puklla.test',
        ]);
        $admin->assignRole('admin');

        $conteoBolsaPublicaAntes = BolsaTrabajoOferta::count();

        $this->actingAs($autor)->post(route('red.oportunidades.crear'), [
            'nombre' => 'Docente EIB Primaria en Urubamba',
            'institucion_red' => 'UGEL Urubamba',
            'detalles' => 'Convocatoria para plaza bilingüe.',
            'tipo_red' => 'trabajo',
            'fuente_red' => 'comparte',
            'lugar_red' => 'Urubamba, Cusco',
            'correo_red' => 'convocatoria@ugel.gob.pe',
        ])->assertRedirect();

        $oferta = BolsaTrabajoOferta::deRedPuklla()->where('autor_red_id', $autor->id)->firstOrFail();

        // No aparece en la bolsa pública general
        $this->assertSame($conteoBolsaPublicaAntes, BolsaTrabajoOferta::count());

        // Lectora guarda la oportunidad con su estado privado
        $this->actingAs($lectora)->post(route('red.guardar'), [
            'tipo' => 'oportunidad',
            'referencia_id' => $oferta->id,
            'estado' => 'postule',
        ])->assertRedirect();

        $this->assertDatabaseHas('red_puklla_guardados', [
            'user_id' => $lectora->id,
            'tipo' => 'oportunidad',
            'referencia_id' => $oferta->id,
            'estado' => 'postule',
        ]);

        // Un usuario sin permiso de moderación puede reportar pero NO ocultar ni retirar
        $this->actingAs($lectora)->post(route('red.moderar'), [
            'tipo' => 'oportunidad',
            'referencia_id' => $oferta->id,
            'accion' => 'ocultar',
            'motivo' => 'Intento no autorizado de ocultar',
        ])->assertForbidden();

        // Admin modera con motivo y queda registrado en auditoría
        $this->actingAs($admin)->post(route('red.moderar'), [
            'tipo' => 'oportunidad',
            'referencia_id' => $oferta->id,
            'accion' => 'ocultar',
            'motivo' => 'Verificando vigencia de la convocatoria con la UGEL.',
        ])->assertRedirect();

        $this->assertDatabaseHas('red_puklla_moderacion', [
            'tipo' => 'oportunidad',
            'referencia_id' => $oferta->id,
            'actor_id' => $admin->id,
            'accion' => 'ocultar',
        ]);
    }
}
