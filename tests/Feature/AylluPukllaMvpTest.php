<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $curso = $alumno->cursos()->first() ?? Curso::first();

        $this->actingAs($user)
            ->get('/inicio')
            ->assertOk()
            ->assertSee('Ayllu Puklla')
            ->assertSee('Un espacio para aprender y construir comunidad')
            ->assertDontSee('Plaza Puklla');

        if ($curso) {
            $this->actingAs($user)
                ->get(route('ayllu.cursos.show', $curso->id))
                ->assertOk()
                ->assertSee($curso->nombre);
        }

        $this->actingAs($user)->get(route('ayllu.proyecto'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.grupos'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.calendario'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.chat'))->assertOk();
        $this->actingAs($user)->get(route('ayllu.privado'))->assertOk()->assertSee('Mi Espacio Privado');
        $this->actingAs($user)->get(route('ayllu.perfil'))->assertOk();
    }

    public function test_docente_puede_publicar_en_muro_general_institucional(): void
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

        $this->assertDatabaseHas('ayllu_publicaciones', [
            'user_id' => $user->id,
            'ambito' => 'institucional',
            'titulo' => 'Bienvenida al nuevo semestre en Ayllu Puklla',
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

    public function test_entrega_formativa_y_retroalimentacion_no_alteran_calificaciones_oficiales(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $docente = Docente::whereHas('user')->first();
        $curso = Curso::first();

        $conteoCalificacionesAntes = Calificacion::count();

        // 1. Estudiante envía avance formativo (Plano 2)
        $this->actingAs($alumno->user)->post(route('ayllu.cursos.entregas.store', $curso->id), [
            'titulo' => 'Ensayo formativo sobre diálogo de saberes',
            'descripcion_reflexion' => 'Primer borrador reflexivo articulado al proyecto anual.',
            'enlace_evidencia' => 'https://drive.google.com/file/d/ejemplo-evidencia/view',
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

        // 2. Docente registra retroalimentación formativa (Plano 2)
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

    public function test_comunidades_reacciones_chat_calendario_y_perfil_funcionan_integralmente(): void
    {
        $alumno = Alumno::whereHas('user')->first();
        $docente = Docente::whereHas('user')->first();
        $user = $alumno->user;

        // 1. Crear una nueva comunidad Ayllu
        $this->actingAs($user)->post(route('ayllu.grupos.store'), [
            'nombre' => 'Círculo de Lectura EIB Cusco',
            'nombre_quechua' => 'Kuska Ñawinchasun',
            'descripcion' => 'Lectura colectiva de textos pedagógicos interculturales.',
            'tipo' => 'circulo_estudio',
        ])->assertRedirect();

        $grupo = AylluGrupo::where('nombre', 'Círculo de Lectura EIB Cusco')->first();
        $this->assertNotNull($grupo);

        // 2. Enviar mensaje en el Chat Académico de la comunidad
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
