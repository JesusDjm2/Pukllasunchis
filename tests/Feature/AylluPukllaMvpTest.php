<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPerfil;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Ayllu\AylluRetroalimentacion;
use App\Models\Calificacion;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\TaqeGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AylluPukllaMvpTest extends TestCase
{
    use DatabaseTransactions;

    public function test_invitado_es_redirigido_a_login_al_intentar_entrar_a_inicio(): void
    {
        $response = $this->get('/inicio');
        $response->assertRedirect('/login');
    }

    public function test_estudiante_fid_puede_navegar_por_todas_las_vistas_de_ayllu_puklla(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $this->assertNotNull($alumno, 'Debe existir al menos un estudiante FID en la BD de desarrollo');

        $user = $alumno->user;
        $curso = $alumno->cursos()->first();
        if (!$curso) {
            $curso = Curso::first();
            $alumno->cursos()->syncWithoutDetaching([$curso->id]);
        }

        $this->actingAs($user)
            ->get('/inicio')
            ->assertOk()
            ->assertSee('Ayllu Puklla')
            ->assertSee('Un espacio para aprender y construir comunidad')
            ->assertDontSee('Plaza Puklla');

        $this->actingAs($user)
            ->get(route('ayllu.cursos.show', $curso->id))
            ->assertOk()
            ->assertSee($curso->nombre);

        $this->actingAs($user)->get(route('ayllu.proyecto'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.grupos'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.calendario'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.chat'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.privado'))->assertOk()->assertSee('Mi Espacio Privado');
        $this->actingAs($user)->get(route('ayllu.perfil'))->assertOk();
    }

    public function test_docente_puede_publicar_y_editar_en_muro_general_institucional(): void
    {
        $docente = Docente::whereHas('user')->first();
        $this->assertNotNull($docente, 'Debe existir al menos un docente en la BD de desarrollo');

        $user = $docente->user;
        if (!$user->hasRole('docente')) {
            $user->assignRole('docente');
        }

        $response = $this->actingAs($user)->post(route('ayllu.publicaciones.store'), [
            'ambito' => 'institucional',
            'tipo' => 'comunicado',
            'titulo' => 'Bienvenida al nuevo semestre en Ayllu Puklla',
            'contenido' => 'Invitamos a todas las comunidades de curso a articular sus avances con el Proyecto Integrador.',
            'lengua' => 'bilingue',
        ]);

        $response->assertRedirect();

        $pub = AylluPublicacion::where('user_id', $user->id)
            ->where('titulo', 'Bienvenida al nuevo semestre en Ayllu Puklla')
            ->latest('id')
            ->first();

        $this->assertNotNull($pub);
        $this->assertSame('institucional', $pub->ambito);

        // Edición de publicación propia (PUT/PATCH)
        $this->actingAs($user)->put(route('ayllu.publicaciones.update', $pub->id), [
            'titulo' => 'Bienvenida al nuevo semestre en Ayllu Puklla (Actualizado)',
            'contenido' => 'Contenido actualizado por el docente autor.',
            'tipo' => 'comunicado',
        ])->assertRedirect();

        $this->assertDatabaseHas('ayllu_publicaciones', [
            'id' => $pub->id,
            'titulo' => 'Bienvenida al nuevo semestre en Ayllu Puklla (Actualizado)',
            'contenido' => 'Contenido actualizado por el docente autor.',
        ]);
    }

    public function test_politica_muro_general_decision_a3_estudiante_publica_en_su_comunidad_y_no_en_institucional(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $user = $alumno->user;

        $response = $this->actingAs($user)->post(route('ayllu.publicaciones.store'), [
            'ambito' => 'institucional',
            'tipo' => 'dialogo_saber',
            'titulo' => 'Reflexión estudiantil desde práctica EIB',
            'contenido' => 'Comparto mis apuntes de observación en escuela EIB.',
        ]);

        $response->assertRedirect();

        $pub = AylluPublicacion::where('user_id', $user->id)
            ->where('titulo', 'Reflexión estudiantil desde práctica EIB')
            ->latest('id')
            ->first();

        $this->assertNotNull($pub);
        $this->assertNotEquals('institucional', $pub->ambito, 'Un estudiante no publica directamente como comunicado institucional sin moderación');
    }

    /**
     * Caso requerido Fase 1E #1:
     * Un estudiante no puede abrir un curso ajeno por ID.
     */
    public function test_estudiante_no_puede_abrir_curso_ajeno_por_id(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $user = $alumno->user;

        // Crear un curso en un ciclo distinto y sin matrícula para este estudiante
        $cicloAjeno = Ciclo::where('id', '!=', (int) $alumno->ciclo_id)->first()
            ?? Ciclo::create([
                'nombre' => 'Ciclo X Experimental Ajeno',
                'programa_id' => 1,
            ]);

        $cursoAjeno = Curso::create([
            'nombre' => 'Curso Exclusivo de Ciclo Ajeno 1E',
            'cc' => 'CC-1E-AJENO',
            'ciclo_id' => $cicloAjeno->id,
            'horas' => '4',
            'creditos' => '3',
        ]);

        // GET al curso ajeno debe devolver 403
        $this->actingAs($user)
            ->get(route('ayllu.cursos.show', $cursoAjeno->id))
            ->assertForbidden();

        // POST de entrega formativa al curso ajeno también debe devolver 403
        $this->actingAs($user)
            ->post(route('ayllu.cursos.entregas.store', $cursoAjeno->id), [
                'titulo' => 'Intento de entrega no autorizada',
                'descripcion_reflexion' => 'No pertenezco a este curso.',
                'autoevaluacion_nivel' => 'en_inicio',
            ])
            ->assertForbidden();
    }

    /**
     * Caso requerido Fase 1E #2:
     * Un estudiante no puede publicar ni enviar mensajes en comunidades/cursos/grupos a los que no tiene acceso.
     */
    public function test_estudiante_no_puede_publicar_ni_enviar_mensajes_en_comunidades_sin_acceso(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $docente = Docente::whereHas('user')->first();
        $user = $alumno->user;

        $cicloAjeno = Ciclo::where('id', '!=', (int) $alumno->ciclo_id)->first();
        $cursoAjeno = Curso::create([
            'nombre' => 'Seminario Ajeno Sin Matrícula',
            'cc' => 'CC-1E-SEMINARIO',
            'ciclo_id' => $cicloAjeno ? $cicloAjeno->id : 999,
            'horas' => '2',
            'creditos' => '2',
        ]);

        $grupoPrivado = AylluGrupo::create([
            'nombre' => 'Equipo Cerrado de Investigación Docente',
            'slug' => 'equipo-cerrado-inv-' . uniqid(),
            'tipo' => 'equipo_taqe',
            'es_abierto' => false,
            'creador_user_id' => $docente->user->id,
        ]);

        // 1. No puede publicar en el muro de un curso ajeno
        $this->actingAs($user)->post(route('ayllu.publicaciones.store'), [
            'ambito' => 'curso',
            'curso_id' => $cursoAjeno->id,
            'tipo' => 'reflexion',
            'contenido' => 'Intento publicar en curso ajeno.',
        ])->assertForbidden();

        // 2. No puede publicar en un grupo del que no es miembro
        $this->actingAs($user)->post(route('ayllu.publicaciones.store'), [
            'ambito' => 'grupo',
            'ayllu_grupo_id' => $grupoPrivado->id,
            'tipo' => 'reflexion',
            'contenido' => 'Intento publicar en grupo sin ser miembro.',
        ])->assertForbidden();

        // 3. No puede enviar mensajes en el chat de un curso ajeno
        $this->actingAs($user)->post(route('ayllu.chat.store'), [
            'canal_tipo' => 'curso',
            'canal_id' => $cursoAjeno->id,
            'mensaje' => 'Intento chatear en curso ajeno.',
        ])->assertForbidden();

        // 4. No puede enviar mensajes en el chat de un grupo al que no pertenece
        $this->actingAs($user)->post(route('ayllu.chat.store'), [
            'canal_tipo' => 'grupo',
            'canal_id' => $grupoPrivado->id,
            'mensaje' => 'Intento chatear en grupo privado ajeno.',
        ])->assertForbidden();

        // 5. No puede comentar ni reaccionar en una publicación de un curso ajeno
        $pubCursoAjeno = AylluPublicacion::create([
            'user_id' => $docente->user->id,
            'ambito' => 'curso',
            'curso_id' => $cursoAjeno->id,
            'tipo' => 'anuncio',
            'contenido' => 'Anuncio interno del curso ajeno.',
            'lengua' => 'castellano',
        ]);

        $this->actingAs($user)->post(route('ayllu.publicaciones.comentar', $pubCursoAjeno->id), [
            'contenido' => 'Comentario intruso.',
        ])->assertForbidden();

        $this->actingAs($user)->post(route('ayllu.publicaciones.reaccionar', $pubCursoAjeno->id), [
            'tipo' => 'allin',
        ])->assertForbidden();
    }

    /**
     * Caso requerido Fase 1E #3:
     * Un docente no asignado no puede ver ni retroalimentar entregas de otro curso.
     */
    public function test_docente_no_asignado_no_puede_ver_ni_retroalimentar_entregas_de_otro_curso(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $curso = $alumno->cursos()->first() ?? Curso::first();
        $alumno->cursos()->syncWithoutDetaching([$curso->id]);

        // Crear un usuario Docente que NO está asignado a $curso ni es tutor del ciclo ni admin
        $userDocenteAjeno = User::create([
            'name' => 'Docente Externo',
            'apellidos' => 'No Asignado',
            'dni' => (string) random_int(70000000, 79999999),
            'email' => 'docente.noasignado.' . uniqid() . '@puklla.edu.pe',
            'password' => bcrypt('secret123'),
        ]);
        $userDocenteAjeno->assignRole('docente');

        Docente::create([
            'nombre' => 'Docente Externo No Asignado',
            'dni' => $userDocenteAjeno->dni,
            'email' => $userDocenteAjeno->email,
            'user_id' => $userDocenteAjeno->id,
        ]);

        $entrega = AylluEntregaFormativa::create([
            'curso_id' => $curso->id,
            'user_id' => $alumno->user->id,
            'alumno_id' => $alumno->id,
            'titulo' => 'Avance reservado para mi docente del curso',
            'descripcion_reflexion' => 'Reflexión formativa del estudiante.',
            'autoevaluacion_nivel' => 'en_proceso',
            'estado' => 'enviado',
        ]);

        // 1. El docente no asignado no puede abrir el curso ajeno (403)
        $this->actingAs($userDocenteAjeno)
            ->get(route('ayllu.cursos.show', ['curso' => $curso->id, 'tab' => 'formativa']))
            ->assertForbidden();

        // 2. El docente no asignado no puede registrar retroalimentación en la entrega de ese curso (403)
        $this->actingAs($userDocenteAjeno)
            ->post(route('ayllu.entregas.retroalimentar', $entrega->id), [
                'valoracion_formativa' => 'logrado',
                'comentario_general' => 'Intento de evaluación por docente no asignado.',
            ])
            ->assertForbidden();
    }

    /**
     * Caso requerido Fase 1E #4:
     * Un estudiante no puede usar identificadores de grupos Taq'e ajenos.
     */
    public function test_estudiante_no_puede_usar_identificador_de_grupo_taqe_ajeno(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $curso = $alumno->cursos()->first() ?? Curso::first();
        $alumno->cursos()->syncWithoutDetaching([$curso->id]);

        // Crear un grupo Taq'e donde $alumno NO es integrante
        $otroAlumno = Alumno::where('id', '!=', $alumno->id)->first() ?? $alumno;
        $taqeGrupoAjeno = TaqeGrupo::create([
            'nombre' => 'Equipo Taq\'e Ajeno',
            'curso_id' => $curso->id,
            'ciclo_id' => $curso->ciclo_id,
            'programa_id' => $curso->ciclo?->programa_id ?? 1,
            'periodo_actual_id' => \App\Models\PeriodoActual::actual()?->id ?? 1,
            'creador_alumno_id' => $otroAlumno->id,
            'estado' => 'borrador',
        ]);

        $this->actingAs($alumno->user)
            ->post(route('ayllu.cursos.entregas.store', $curso->id), [
                'titulo' => 'Intento de inyectar evidencia en equipo Taq\'e ajeno',
                'descripcion_reflexion' => 'Prueba de control de pertenencia Taq\'e.',
                'autoevaluacion_nivel' => 'en_proceso',
                'es_aporte_proyecto_integrador' => 1,
                'taqe_grupo_id' => $taqeGrupoAjeno->id,
            ])
            ->assertForbidden();
    }

    /**
     * Caso requerido Fase 1E #5:
     * Un visitante de perfil no puede ver portafolios privados ni retroalimentaciones formativas confidenciales.
     */
    public function test_visitante_no_puede_ver_portafolio_privado_ni_retroalimentacion_confidencial_en_perfil(): void
    {
        $alumnos = Alumno::whereHas('user')->take(2)->get();
        $this->assertGreaterThanOrEqual(2, $alumnos->count(), 'Se requieren al menos 2 alumnos con usuario');

        $titular = $alumnos[0];
        $visitante = $alumnos[1];
        $docente = Docente::whereHas('user')->first();
        $curso = $titular->cursos()->first() ?? Curso::first();

        $entregaPortafolio = AylluEntregaFormativa::create([
            'curso_id' => $curso->id,
            'user_id' => $titular->user->id,
            'alumno_id' => $titular->id,
            'titulo' => 'Evidencia Curada en Portafolio EIB',
            'descripcion_reflexion' => 'Sistematización pública de mi práctica.',
            'autoevaluacion_nivel' => 'en_inicio',
            'en_portafolio' => true,
            'reflexion_portafolio' => 'Reflexión pública de portafolio.',
            'estado' => 'acompanado',
        ]);

        AylluRetroalimentacion::create([
            'ayllu_entrega_formativa_id' => $entregaPortafolio->id,
            'autor_user_id' => $docente->user->id,
            'tipo' => 'docente',
            'valoracion_formativa' => 'en_proceso',
            'fortalezas' => 'Fortaleza privada confidencial.',
            'sugerencias_mejora' => 'Sugerencia confidencial exclusiva para el estudiante.',
            'comentario_general' => 'OBSERVACION_CONFIDENCIAL_DOCENTE_1E',
        ]);

        // 1. Cuando mostrar_portafolio_comunidad = false, el visitante no ve los ítems del portafolio
        AylluPerfil::updateOrCreate(
            ['user_id' => $titular->user->id],
            [
                'saludo_intercultural' => 'Allillanchu',
                'mostrar_portafolio_comunidad' => false,
            ]
        );

        $this->actingAs($visitante->user)
            ->get(route('ayllu.perfil', $titular->user->id))
            ->assertOk()
            ->assertSee('mantiene su portafolio en modo privado')
            ->assertDontSee('Evidencia Curada en Portafolio EIB')
            ->assertDontSee('OBSERVACION_CONFIDENCIAL_DOCENTE_1E');

        // 2. Cuando mostrar_portafolio_comunidad = true, el visitante ve la evidencia curada pero NUNCA la retroalimentación confidencial
        AylluPerfil::where('user_id', $titular->user->id)->update(['mostrar_portafolio_comunidad' => true]);

        $responsePublico = $this->actingAs($visitante->user)
            ->get(route('ayllu.perfil', $titular->user->id));

        $responsePublico->assertOk()
            ->assertSee('Evidencia Curada en Portafolio EIB')
            ->assertDontSee('OBSERVACION_CONFIDENCIAL_DOCENTE_1E')
            ->assertDontSee('Sugerencia confidencial exclusiva para el estudiante.');

        // Verificar que las retroalimentaciones están vacías en memoria para terceros y autoevaluación es null
        $portafolioItemsVista = $responsePublico->viewData('portafolioItems');
        $this->assertNotEmpty($portafolioItemsVista);
        $this->assertTrue($portafolioItemsVista->first()->retroalimentaciones->isEmpty());
        $this->assertNull($portafolioItemsVista->first()->autoevaluacion_nivel);
    }

    /**
     * Caso requerido Fase 1E #6:
     * La evaluación formativa no altera las calificaciones oficiales.
     */
    public function test_entrega_formativa_y_retroalimentacion_no_alteran_calificaciones_oficiales(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $docente = Docente::whereHas('user')->first();
        $curso = $alumno->cursos()->first() ?? Curso::first();

        // Asegurar matrícula del estudiante y asignación real del docente al curso
        $alumno->cursos()->syncWithoutDetaching([$curso->id]);
        DB::table('curso_docente')->updateOrInsert([
            'curso_id' => $curso->id,
            'docente_id' => $docente->id,
        ]);

        $conteoCalificacionesAntes = Calificacion::count();

        // 1. Estudiante envía avance formativo con confirmación de protección de menores (Plano 2)
        $this->actingAs($alumno->user)->post(route('ayllu.cursos.entregas.store', $curso->id), [
            'titulo' => 'Ensayo formativo sobre diálogo de saberes',
            'descripcion_reflexion' => 'Primer borrador reflexivo articulado al proyecto anual.',
            'enlace_evidencia' => 'https://drive.google.com/file/d/ejemplo-evidencia/view',
            'confirmacion_politica_menores' => '1',
            'autoevaluacion_nivel' => 'en_proceso',
            'es_aporte_proyecto_integrador' => 1,
            'en_portafolio' => 1,
            'reflexion_portafolio' => 'Selecciono este ensayo porque evidencia mi progreso en quechua.',
        ])->assertRedirect();

        $entrega = AylluEntregaFormativa::where('user_id', $alumno->user->id)
            ->where('curso_id', $curso->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($entrega);
        $this->assertTrue($entrega->es_aporte_proyecto_integrador);
        $this->assertTrue($entrega->en_portafolio);

        // 2. Docente asignado al curso registra retroalimentación formativa (Plano 2)
        if (!$docente->user->hasRole('docente')) {
            $docente->user->assignRole('docente');
        }

        $this->actingAs($docente->user)->post(route('ayllu.entregas.retroalimentar', $entrega->id), [
            'valoracion_formativa' => 'logrado',
            'fortalezas' => 'Excelente articulación con los saberes locales.',
            'sugerencias_mejora' => 'Profundizar en la citación de fuentes comunitarias.',
            'comentario_general' => 'Sigue adelante, buen aporte para tu grupo Taq\'e.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ayllu_retroalimentaciones', [
            'ayllu_entrega_formativa_id' => $entrega->id,
            'autor_user_id' => $docente->user->id,
            'tipo' => 'docente',
            'valoracion_formativa' => 'logrado',
        ]);

        // 3. Verificar que las actas/calificaciones oficiales (Plano 3) permanecen intactas
        $this->assertSame($conteoCalificacionesAntes, Calificacion::count());
    }

    /**
     * Caso requerido Fase 1E #7:
     * Las rutas de desarrollo (/dev/ayllu/*) no son accesibles en entorno de producción.
     */
    public function test_rutas_de_desarrollo_no_son_accesibles_en_entorno_de_produccion(): void
    {
        $entornoOriginal = $this->app['env'];

        try {
            $this->app['env'] = 'production';
            $this->get('/dev/ayllu/fid')->assertNotFound();
            $this->get('/dev/ayllu/ppd')->assertNotFound();
            $this->get('/dev/ayllu/docente')->assertNotFound();
        } finally {
            $this->app['env'] = $entornoOriginal;
        }
    }

    public function test_comunidades_reacciones_chat_calendario_y_perfil_funcionan_integralmente(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $docente = Docente::whereHas('user')->first();
        $user = $alumno->user;

        // 1. Crear una nueva comunidad Ayllu (el creador queda inscrito como moderador)
        $this->actingAs($user)->post(route('ayllu.grupos.store'), [
            'nombre' => 'Círculo de Lectura EIB Cusco',
            'nombre_quechua' => 'Kuska Ñawinchasun',
            'descripcion' => 'Lectura colectiva de textos pedagógicos interculturales.',
            'tipo' => 'circulo_estudio',
        ])->assertRedirect();

        $grupo = AylluGrupo::where('nombre', 'Círculo de Lectura EIB Cusco')->first();
        $this->assertNotNull($grupo);

        // 2. Enviar mensaje en el Chat Académico de la comunidad donde es miembro
        $this->actingAs($user)->post(route('ayllu.chat.store'), [
            'canal_tipo' => 'grupo',
            'canal_id' => $grupo->id,
            'mensaje' => 'Allinllachu compañeros, nos reunimos el viernes a las 3 pm.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ayllu_mensajes_chat', [
            'canal_tipo' => 'grupo',
            'ayllu_grupo_id' => $grupo->id,
            'emisor_user_id' => $user->id,
        ]);

        // 3. Actualizar Perfil Intercultural
        $this->actingAs($user)->post(route('ayllu.perfil.update'), [
            'saludo_intercultural' => 'Allin p’unchay · Kuska wiñasun',
            'biografia' => 'Estudiante de formación inicial docente apasionado por la EIB.',
            'lengua_preferida' => 'castellano_quechua',
            'saberes_texto' => 'Quechua Collao, Agroecología, Sikuris',
            'mostrar_portafolio_comunidad' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('ayllu_perfiles', [
            'user_id' => $user->id,
            'saludo_intercultural' => 'Allin p’unchay · Kuska wiñasun',
        ]);

        // 4. Docente programa actividad en el Calendario Vivo
        if (!$docente->user->hasRole('docente')) {
            $docente->user->assignRole('docente');
        }
        $this->actingAs($docente->user)->post(route('ayllu.calendario.store'), [
            'titulo' => 'Socialización intermedia de Proyecto Integrador',
            'descripcion' => 'Presentación de avances por equipo Taq\'e.',
            'tipo' => 'taqe',
            'fecha_inicio' => now()->addDays(5)->format('Y-m-d'),
            'hora' => '10:00 am',
        ])->assertRedirect();

        $this->assertDatabaseHas('ayllu_eventos_calendario', [
            'titulo' => 'Socialización intermedia de Proyecto Integrador',
            'tipo' => 'taqe',
        ]);
    }
}
