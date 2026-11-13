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

    public function test_moderacion_retiro_global_ayllu_inactivo_aviso_obligatorio_y_solicitud_revision(): void
    {
        $autor = $this->crearEgresadoConfirmado('Autora Moderada');
        $facilitador = $this->crearEgresadoConfirmado('Facilitador Ayllu');
        $docenteSinPermiso = $this->crearDocente('Docente Ordinario');
        $coordinador = User::factory()->create([
            'name' => 'Coordinador Red',
            'apellidos' => 'Puklla',
            'dni' => (string) rand(10000000, 19999999),
            'email' => 'coord_mod_' . rand(1000, 9999) . '@puklla.test',
        ]);
        $coordinador->assignRole('admin');

        // Autora desactiva avisos comunitarios opcionales
        DB::table('red_puklla_preferencias')->insert([
            'user_id' => $autor->id,
            'avisos' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear dos ayllus abiertos para probar difusión por referencia y luego desactivar el origen
        $this->actingAs($facilitador)->post(route('red.grupos.crear'), [
            'nombre' => 'Ayllu Origen Lectura',
            'descripcion' => 'Espacio inicial.',
            'tipo' => 'circulo_estudio',
            'privacidad_red' => 'abierto',
        ]);
        $grupoOrigen = AylluGrupo::deRedPuklla()->where('nombre', 'Ayllu Origen Lectura')->firstOrFail();

        $this->actingAs($facilitador)->post(route('red.grupos.crear'), [
            'nombre' => 'Ayllu Destino Difusión',
            'descripcion' => 'Espacio donde se comparte por referencia.',
            'tipo' => 'taller',
            'privacidad_red' => 'abierto',
        ]);
        $grupoDestino = AylluGrupo::deRedPuklla()->where('nombre', 'Ayllu Destino Difusión')->firstOrFail();

        // Autora se une a ambos ayllus y publica en el de origen
        $this->actingAs($autor)->post(route('red.membresia', ['id' => $grupoOrigen->id]), ['accion' => 'unir']);
        $this->actingAs($autor)->post(route('red.membresia', ['id' => $grupoDestino->id]), ['accion' => 'unir']);

        $this->actingAs($autor)->post(route('red.publicar'), [
            'titulo' => 'Publicación compartida en varios ayllus',
            'contenido' => 'Contenido que será difundido y luego revisado por moderación.',
            'categoria_red' => 'rimay',
            'intencion_red' => 'pregunta',
            'ayllu_grupo_id' => $grupoOrigen->id,
        ])->assertRedirect();

        $post = AylluPublicacion::deRedPuklla()->where('user_id', $autor->id)->firstOrFail();

        // Difundir por referencia al segundo ayllu y guardar en Mi espacio de otro miembro
        $this->actingAs($autor)->post(route('red.difundir', ['id' => $post->id]), [
            'grupo_id' => $grupoDestino->id,
        ])->assertRedirect();

        $this->actingAs($facilitador)->post(route('red.guardar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'estado' => 'guardado',
        ])->assertRedirect();

        // Docente sin permiso de moderación ni coordinación, y facilitador de ayllu
        Role::findByName('docente')->revokePermissionTo('ayllu.moderar');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            // Docente o facilitador sin permisos intenta retirar por POST -> 403 y no cambia el contenido
            foreach ([$docenteSinPermiso, $facilitador] as $sinPermiso) {
                $this->actingAs($sinPermiso)->post(route('red.moderar'), [
                    'tipo' => 'publicacion',
                    'referencia_id' => $post->id,
                    'accion' => 'retirar',
                    'motivo' => 'Intento sin permiso institucional suficiente.',
                ])->assertForbidden();
            }
        } finally {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
        $this->assertSame('visible', $post->fresh()->estado_red);

        // El ayllu de origen pasa a estar privado e inactivo; coordinación autorizada aún puede revisar y retirar
        $grupoOrigen->update(['privacidad_red' => 'privado', 'activo' => false]);

        $this->actingAs($coordinador)->get(route('red.publicacion', ['id' => $post->id]))
            ->assertOk()
            ->assertSee('Publicación compartida en varios ayllus');

        $motivoRetiro = 'Incumple la política de convivencia al incluir datos personales de terceros sin autorización.';
        $this->actingAs($coordinador)->post(route('red.moderar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'accion' => 'retirar',
            'motivo' => $motivoRetiro,
        ])->assertRedirect();

        $post->refresh();
        $this->assertSame('retirado', $post->estado_red);
        $this->assertFalse((bool) $post->visible);

        // Reactivar el ayllu origen (conservándolo privado) para verificar el flujo ordinario de consulta del autor
        $grupoOrigen->update(['activo' => true]);

        // Desaparece de circulación en el segundo ayllu donde fue difundida por referencia y en el muro general
        $this->actingAs($facilitador)->get(route('red.grupo', ['id' => $grupoDestino->id]))
            ->assertOk()
            ->assertDontSee('Publicación compartida en varios ayllus');

        $this->actingAs($facilitador)->get(route('red.inicio'))
            ->assertOk()
            ->assertDontSee('Publicación compartida en varios ayllus');

        // En guardados privados ya no entrega copia del contenido retirado
        $this->actingAs($facilitador)->get(route('red.espacio'))
            ->assertOk()
            ->assertSee('Contenido ya no disponible para tu cuenta')
            ->assertDontSee('Publicación compartida en varios ayllus');

        // Autor recibe aviso interno obligatorio incluso con avisos comunitarios desactivados
        $aviso = $autor->notifications()
            ->where('type', \App\Notifications\RedPukllaAviso::class)
            ->latest()
            ->first();
        $this->assertNotNull($aviso);
        $this->assertStringContainsString('retirado', $aviso->data['mensaje']);
        $this->assertStringContainsString($motivoRetiro, $aviso->data['mensaje']);

        // Otro usuario intenta solicitar revisión ajena -> 403
        $this->actingAs($facilitador)->post(route('red.moderar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'accion' => 'solicitar_revision',
            'motivo' => 'Intento de revisión por quien no es autor.',
        ])->assertForbidden();

        // Autor intenta restaurar directamente su propio contenido moderado -> 403
        $this->actingAs($autor)->post(route('red.moderar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'accion' => 'restaurar',
            'motivo' => 'Quiero restaurar mi propia publicación directamente.',
        ])->assertForbidden();

        // Autor consulta la decisión, ve el formulario de solicitar revisión y envía su solicitud
        $this->actingAs($autor)->get(route('red.publicacion', ['id' => $post->id]))
            ->assertOk()
            ->assertSee('Solicitar revisión de esta decisión');

        $this->actingAs($autor)->post(route('red.moderar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'accion' => 'solicitar_revision',
            'motivo' => 'Ya edité el texto fuente y solicito revisión por coordinación.',
        ])->assertRedirect();

        // Solicitar revisión queda registrada en auditoría y panel pero NO vuelve a publicar el contenido
        $post->refresh();
        $this->assertSame('retirado', $post->estado_red);
        $this->assertFalse((bool) $post->visible);

        // Reactivar el ayllu origen (conservándolo privado) para verificar el flujo ordinario de consulta del autor
        $grupoOrigen->update(['activo' => true]);
        $this->assertDatabaseHas('red_puklla_moderacion', [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'actor_id' => $autor->id,
            'accion' => 'solicitar_revision',
        ]);

        // Restauración autorizada restablece visibilidad y deja nueva intervención registrada
        $grupoOrigen->update(['activo' => true, 'privacidad_red' => 'abierto']);
        $this->actingAs($coordinador)->post(route('red.moderar'), [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'accion' => 'restaurar',
            'motivo' => 'Revisado con el autor; cumple las pautas comunitarias.',
        ])->assertRedirect();

        $post->refresh();
        $this->assertSame('visible', $post->estado_red);
        $this->assertTrue((bool) $post->visible);
        $this->assertDatabaseHas('red_puklla_moderacion', [
            'tipo' => 'publicacion',
            'referencia_id' => $post->id,
            'actor_id' => $coordinador->id,
            'accion' => 'restaurar',
        ]);
    }

    public function test_middleware_archivo_red_visible_protege_adjuntos_y_evita_asociacion_cruzada(): void
    {
        // Registrar ruta efímera de prueba que sigue el contrato obligatorio de integración del documento
        \Illuminate\Support\Facades\Route::middleware([
            'web',
            'auth',
            \App\Http\Middleware\ArchivoRedVisible::class,
        ])->get('/_test/red/publicaciones/{publicacionId}/adjuntos/{archivoPublicacionId}', function (\Illuminate\Http\Request $request, string $publicacionId, string $archivoPublicacionId) {
            /** @var \App\Models\Ayllu\AylluPublicacion $autorizada */
            $autorizada = $request->attributes->get('publicacion_red_autorizada');
            // Contrato punto 2: comprobar que el adjunto solicitado pertenece exactamente a esa publicación
            abort_unless($autorizada && (int) $autorizada->id === (int) $archivoPublicacionId, 404);

            return response('CONTENIDO_PRIVADO_OK', 200);
        });

        $autor = $this->crearEgresadoConfirmado('Autor Adjunto');
        $lector = $this->crearEgresadoConfirmado('Lector Adjunto');
        $moderador = User::factory()->create([
            'name' => 'Moderador Adjunto',
            'apellidos' => 'Puklla',
            'dni' => (string) rand(10000000, 19999999),
            'email' => 'mod_adj_' . rand(1000, 9999) . '@puklla.test',
        ]);
        $moderador->assignRole('admin');

        $postVisible = AylluPublicacion::create([
            'user_id' => $autor->id,
            'ambito' => 'institucional',
            'tipo' => 'reflexion',
            'titulo' => 'Publicación visible',
            'contenido' => 'Con archivo visible.',
            'categoria_red' => 'yachay',
            'intencion_red' => 'recurso',
            'red_egresados' => true,
            'estado_red' => 'visible',
            'visible' => true,
        ]);

        $postRetirado = AylluPublicacion::create([
            'user_id' => $autor->id,
            'ambito' => 'institucional',
            'tipo' => 'reflexion',
            'titulo' => 'Publicación retirada',
            'contenido' => 'Con archivo retirado.',
            'categoria_red' => 'yachay',
            'intencion_red' => 'recurso',
            'red_egresados' => true,
            'estado_red' => 'retirado',
            'visible' => false,
        ]);

        // Publicación visible entrega adjunto con Cache-Control privado sin almacenamiento en caché
        $resOk = $this->actingAs($lector)->get("/_test/red/publicaciones/{$postVisible->id}/adjuntos/{$postVisible->id}");
        $resOk->assertOk();
        $this->assertStringContainsString('private', (string) $resOk->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $resOk->headers->get('Cache-Control'));

        // Acceso a archivo retirado mediante URL directa se rechaza (404) para lectores y también para el propio autor
        $this->actingAs($lector)->get("/_test/red/publicaciones/{$postRetirado->id}/adjuntos/{$postRetirado->id}")->assertNotFound();
        $this->actingAs($autor)->get("/_test/red/publicaciones/{$postRetirado->id}/adjuntos/{$postRetirado->id}")->assertNotFound();

        // Moderación autorizada sí conserva consulta privada para revisar el contenido retirado
        $this->actingAs($moderador)->get("/_test/red/publicaciones/{$postRetirado->id}/adjuntos/{$postRetirado->id}")->assertOk();

        // Usar una publicación visible para intentar abrir el adjunto de otra retirada se rechaza (404)
        $this->actingAs($lector)->get("/_test/red/publicaciones/{$postVisible->id}/adjuntos/{$postRetirado->id}")->assertNotFound();
    }
}
