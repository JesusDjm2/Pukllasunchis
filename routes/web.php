<?php

/* use App\Http\Controllers\Auth\RegisterController; */
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\CursosEspeciales\CeAudioStreamController;
use App\Http\Controllers\EnlacesController;
use App\Http\Controllers\PpdController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

Route::get('/', function () {
    return view('welcome');
})->name('index');
Route::post('solicitar-informes', [App\Http\Controllers\InformesController::class, 'enviar'])
    ->name('informes.enviar')->middleware('throttle:5,1');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('nosotros', [EnlacesController::class, 'nosotros'])->name('nosotros');
//Programas
Route::get('programas/educacion-inicial', [EnlacesController::class, 'inicial'])->name('inicial');
Route::get('programas/educacion-primaria', [EnlacesController::class, 'primaria'])->name('primaria');
Route::get('programas/educacion-primaria-EIB', [EnlacesController::class, 'primariaEIB'])->name('primariaEIB');
Route::get('programas/formacion-continua', [EnlacesController::class, 'formacion'])->name('formacion');

Route::get('profesionalizacion-docente', [EnlacesController::class, 'profesionalizacion'])->name('profesionalizacion');

//Admisión
Route::get('admision/admision-ordinario', [EnlacesController::class, 'ordinario'])->name('ordinario');
Route::get('admision/admison-exoneracion', [EnlacesController::class, 'exoneracion'])->name('exoneracion');
Route::get('admision/traslado-externo', [EnlacesController::class, 'traslado'])->name('traslado');
Route::get('admision/resultados', [EnlacesController::class, 'resultados'])->name('resultados');

//Titulacion
Route::get('tramite-de-titulacion', [EnlacesController::class, 'tramiteTitulacion'])->name('tramiteTitulacion');
Route::get('plan-de-trabajo', [EnlacesController::class, 'plan'])->name('plan');
Route::get('titulacion/investigacion', [EnlacesController::class, 'tinvestigacion'])->name('tinvestigacion');
Route::get('titulacion/tesis', [EnlacesController::class, 'tesis'])->name('tesis');
Route::get('tramites-presenciales', [EnlacesController::class, 'tramites'])->name('tramites');

//Trámites
Route::get('tramites/matricula', [EnlacesController::class, 'matricula'])->name('matricula');
Route::get('tramites/traslado', [EnlacesController::class, 'Ttraslado'])->name('Ttraslado');
Route::get('tramites/licencia', [EnlacesController::class, 'licencia'])->name('licencia');
Route::get('tramites/mesa-de-partes', [EnlacesController::class, 'partes'])->name('partes');
Route::get('tramites/pagos-extraordinarios', [EnlacesController::class, 'extraordinarios'])->name('extraordinarios');

//Líneas
Route::get('lineas/tutoria', [EnlacesController::class, 'tutoria'])->name('tutoria');
Route::get('lineas/bienestar', [EnlacesController::class, 'bienestar'])->name('bienestar');
Route::get('lineas/area-de-investigacion', [EnlacesController::class, 'investigacion'])->name('investigacion');
Route::get('lineas/practica-pre-profesional', [EnlacesController::class, 'preProfesional'])->name('preProfesional');
Route::get('lineas/subvenciones-y-becas', [EnlacesController::class, 'subvenciones'])->name('subvenciones');

//Información
Route::get('informacion/novedades', [EnlacesController::class, 'novedades'])->name('novedades');
Route::get('informacion/articulos', [EnlacesController::class, 'articulos'])->name('articulos');
Route::get('informacion/proyectos-académicos', [EnlacesController::class, 'proyectos'])->name('proyectos');
Route::get('informacion/innovaciones', [EnlacesController::class, 'innovaciones'])->name('innovaciones');
Route::get('informacion/bolsa-de-trabajo', [EnlacesController::class, 'bolsa'])->name('bolsa');

//Foot
Route::get('informacion-institucional', [EnlacesController::class, 'informacion'])->name('informacion');
Route::get('informacion-institucional/plana-docente', [EnlacesController::class, 'planaDocente'])->name('planaDocente');
Route::get('politica-de-privacidad', [EnlacesController::class, 'politica'])->name('politica');
Route::get('terminos-y-condiciones', [EnlacesController::class, 'terminos'])->name('terminos');

//bolsa de trabajo sin base de datos
Route::get('informacion/convocatorias', [EnlacesController::class, 'conv'])->name('conv');
Route::get('informacion/convocatorias-2', [EnlacesController::class, 'conv2'])->name('conv2');
Route::get('informacion/convocatorias-2', [EnlacesController::class, 'conv2'])->name('conv2');

//Formatos de TI y Tesis
Route::get('/alumnos/formatos', [AlumnoController::class, 'formatos'])->name('alumno.formatos');
Route::get('/alumnos/comunicados', [EnlacesController::class, 'alumnoComunicados'])
    ->middleware('auth')
    ->name('alumno.comunicados');
Route::get('/Formatos-de-TI-y-Tesis', [PpdController::class, 'formatos'])->name('ppd.formatos');

//Videos
Route::get('La-pulga-aventura', [EnlacesController::class, 'video1'])->name('video1');
Route::get('luis-pescetti-el-nino-canibal', [EnlacesController::class, 'video2'])->name('video2');
Route::get('El-negrito-aquel', [EnlacesController::class, 'video3'])->name('video3');
Route::get('La-flor-de-la-canela', [EnlacesController::class, 'video4'])->name('video4');

//360
Route::get('Tour-360', [EnlacesController::class, 'tour'])->name('tour');

/* Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');

    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    return 'DONE';
}); */

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/'); // Redirige al inicio
})->name('logout');

Route::get('/test-419', function () {
    throw new HttpException(419, 'Página expirada');
});

// Proxy de audio para Google Drive (requiere sesión activa)
Route::get('/ce/audio/{fileId}', [CeAudioStreamController::class, 'stream'])
    ->middleware('auth')
    ->name('ce.audio.stream');

// Formulario público de incidencias (sin login)
Route::get('/incidencias', [App\Http\Controllers\IncidenciaController::class, 'publicCreate'])->name('incidencias.public.create');
Route::post('/incidencias', [App\Http\Controllers\IncidenciaController::class, 'publicStore'])->name('incidencias.public.store');

// Formulario público del Libro de Reclamaciones (sin login)
Route::get('/libro-de-reclamaciones', [App\Http\Controllers\ReclamoController::class, 'publicCreate'])->name('reclamos.public.create');
Route::post('/libro-de-reclamaciones', [App\Http\Controllers\ReclamoController::class, 'publicStore'])
    ->middleware('throttle:3,60')
    ->name('reclamos.public.store');

// Formulario público de solicitud de tutoría individual — acceso vía QR.
// Requiere identificarse como alumno (login inline, sin salir de la vista).
Route::get('/solicitud-tutoria', [App\Http\Controllers\TutoriaController::class, 'publicCreate'])->name('tutorias.public.create');
Route::post('/solicitud-tutoria', [App\Http\Controllers\TutoriaController::class, 'publicStore'])
    ->middleware('throttle:5,60')
    ->name('tutorias.public.store');

// Formulario público del buzón de sugerencias — acceso vía QR.
// Anónima: sin login. No anónima: requiere login inline (identifica al alumno).
Route::get('/buzon-sugerencias', [App\Http\Controllers\SugerenciaController::class, 'publicCreate'])->name('sugerencias.public.create');
Route::post('/buzon-sugerencias', [App\Http\Controllers\SugerenciaController::class, 'publicStore'])
    ->middleware('throttle:5,60')
    ->name('sugerencias.public.store');

// Login inline (AJAX) para los formularios públicos de arriba — no navega fuera de la vista.
Route::post('/alumno-login-inline', [App\Http\Controllers\AlumnoAuthController::class, 'loginInline'])
    ->middleware('throttle:10,1')
    ->name('alumno.login.inline');

// ══════════════════════════════════════════════════════════════════
// SEGUIMIENTO DE EGRESADOS (Sistema Sostenible EESP Pukllasunchis - Fase 1C)
// ══════════════════════════════════════════════════════════════════

// 1. Rutas públicas de campaña (WhatsApp / Magic Link / Acceso Abierto)
Route::prefix('seguimiento-egresados')->group(function () {
    Route::get('/campana-publica', [App\Http\Controllers\SeguimientoEgresadoController::class, 'campanaPublica'])
        ->middleware('throttle:60,1')
        ->name('seguimiento-egresados.campana-publica');

    Route::get('/campana/{token}', [App\Http\Controllers\SeguimientoEgresadoController::class, 'campanaPublica'])
        ->middleware('throttle:60,1')
        ->name('seguimiento-egresados.campana-token');

    Route::post('/campana-publica/actualizar', [App\Http\Controllers\SeguimientoEgresadoController::class, 'guardarActualizacion'])
        ->middleware('throttle:30,1')
        ->name('seguimiento-egresados.campana-publica.actualizar');

    Route::get('/visualizador', function () {
        return response()->file(public_path('visualizador_diseno_puklla.html'));
    })->name('seguimiento-egresados.visualizador');
});

// 2. Ruta híbrida de actualización (Autenticada con control de roles o pública con token/cuarentena)
Route::post('/seguimiento-egresados/actualizar', [App\Http\Controllers\SeguimientoEgresadoController::class, 'guardarActualizacion'])
    ->middleware('throttle:30,1')
    ->name('seguimiento-egresados.actualizar');

// 3. Rutas de intranet protegidas por autenticación obligatoria y control estricto de roles
Route::middleware(['auth'])->prefix('seguimiento-egresados')->group(function () {
    // Vista principal con control de alcances (egresado solo ve su ficha, encuestador sus asignados, admin todo)
    Route::get('/', [App\Http\Controllers\SeguimientoEgresadoController::class, 'index'])
        ->name('seguimiento-egresados.index');
    Route::get('/padron', [App\Http\Controllers\SeguimientoEgresadoController::class, 'index'])
        ->name('seguimiento.index');

    // Laboratorio de Inteligencia Institucional y Observatorio Territorial (Fase 2A)
    Route::get('/laboratorio', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'index'])
        ->name('seguimiento.laboratorio');
    Route::post('/analitica', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'analiticaAjax'])
        ->name('seguimiento.analitica');
    Route::get('/analitica/exportar', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'exportar'])
        ->name('seguimiento.analitica.exportar');

    // Alias compatibles para seguimiento-egresados.*
    Route::get('/laboratorio-alias', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'index'])
        ->name('seguimiento-egresados.laboratorio');
    Route::post('/analitica-alias', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'analiticaAjax'])
        ->name('seguimiento-egresados.analitica');
    Route::get('/analitica/exportar-alias', [App\Http\Controllers\SeguimientoLaboratorioController::class, 'exportar'])
        ->name('seguimiento-egresados.analitica.exportar');

    // Episodios laborales (consulta y cierre con verificación en servidor de propiedad o asignación)
    Route::get('/episodios/{userId}', [App\Http\Controllers\SeguimientoEgresadoController::class, 'obtenerEpisodios'])
        ->name('seguimiento-egresados.episodios');
    Route::post('/episodios/{id}/cerrar', [App\Http\Controllers\SeguimientoEgresadoController::class, 'cerrarEpisodio'])
        ->name('seguimiento-egresados.episodios.cerrar');

    // Gestión de contactos, llamadas e invitaciones seguras WhatsApp
    Route::post('/llamada', [App\Http\Controllers\SeguimientoEgresadoController::class, 'guardarLlamada'])
        ->name('seguimiento-egresados.llamada');
    Route::post('/auditar-contacto', [App\Http\Controllers\SeguimientoEgresadoController::class, 'auditarContacto'])
        ->name('seguimiento-egresados.auditar-contacto');
    Route::get('/enlace-whatsapp', [App\Http\Controllers\SeguimientoEgresadoController::class, 'generarEnlaceWhatsapp'])
        ->name('seguimiento-egresados.enlace-whatsapp');

    // Coordinación y Administración: Grupos de entrevistadores y Casos institucionales de acompañamiento
    Route::post('/accion', [App\Http\Controllers\SeguimientoEgresadoController::class, 'guardarAccion'])
        ->name('seguimiento-egresados.accion');
    Route::post('/avanzar-accion/{id}', [App\Http\Controllers\SeguimientoEgresadoController::class, 'avanzarAccion'])
        ->name('seguimiento-egresados.avanzar-accion');
    Route::post('/grupos', [App\Http\Controllers\SeguimientoEgresadoController::class, 'guardarGrupo'])
        ->name('seguimiento-egresados.grupos.guardar');
    Route::delete('/grupos/{id}', [App\Http\Controllers\SeguimientoEgresadoController::class, 'eliminarGrupo'])
        ->name('seguimiento-egresados.grupos.eliminar');

    // Gestión Operativa de Campañas, Asignación Equilibrada y Monitoreo (Fase Campañas con Docentes y Egresados Reales)
    Route::get('/campanas/gestion', [App\Http\Controllers\SeguimientoCampanaController::class, 'gestion'])
        ->name('seguimiento.campanas.gestion');
    Route::get('/campanas/gestion-alias', [App\Http\Controllers\SeguimientoCampanaController::class, 'gestion'])
        ->name('seguimiento-egresados.campanas.gestion');

    Route::post('/campanas/distribuir/preview', [App\Http\Controllers\SeguimientoCampanaController::class, 'previewDistribucion'])
        ->name('seguimiento.campanas.distribuir.preview');
    Route::post('/campanas/distribuir', [App\Http\Controllers\SeguimientoCampanaController::class, 'ejecutarDistribucion'])
        ->name('seguimiento.campanas.distribuir');

    Route::get('/campanas/{campanaId}/monitoreo', [App\Http\Controllers\SeguimientoCampanaController::class, 'monitoreo'])
        ->name('seguimiento.campanas.monitoreo');
    Route::get('/campanas/{campanaId}/monitoreo-alias', [App\Http\Controllers\SeguimientoCampanaController::class, 'monitoreo'])
        ->name('seguimiento-egresados.campanas.monitoreo');

    Route::post('/campanas/reasignar', [App\Http\Controllers\SeguimientoCampanaController::class, 'reasignar'])
        ->name('seguimiento.campanas.reasignar');

    Route::get('/campanas/{campanaId}/territorio', [App\Http\Controllers\SeguimientoCampanaController::class, 'territorio'])
        ->name('seguimiento.campanas.territorio');
    Route::get('/campanas/{campanaId}/territorio-alias', [App\Http\Controllers\SeguimientoCampanaController::class, 'territorio'])
        ->name('seguimiento-egresados.campanas.territorio');

    // Panel del Docente: "Mis Egresados Asignados"
    Route::get('/mis-asignaciones', [App\Http\Controllers\SeguimientoCampanaController::class, 'misAsignaciones'])
        ->name('seguimiento-egresados.mis-asignaciones');
    Route::get('/mis-asignaciones-alias', [App\Http\Controllers\SeguimientoCampanaController::class, 'misAsignaciones'])
        ->name('seguimiento.docente.mis-asignaciones');
});

/*
|--------------------------------------------------------------------------
| Ayllu Puklla — Un espacio para aprender y construir comunidad (MVP Fase 1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/inicio', [App\Http\Controllers\Ayllu\AylluController::class, 'inicio'])
        ->name('ayllu.inicio');
    Route::get('/ayllu', function () {
        return redirect()->route('ayllu.inicio');
    })->name('ayllu.redirect');

    // Publicaciones comunitarias, reacciones y moderación (Plano 1)
    Route::post('/ayllu/publicaciones', [App\Http\Controllers\Ayllu\AylluController::class, 'storePublicacion'])
        ->name('ayllu.publicaciones.store');
    Route::match(['put', 'patch'], '/ayllu/publicaciones/{publicacion}', [App\Http\Controllers\Ayllu\AylluController::class, 'updatePublicacion'])
        ->name('ayllu.publicaciones.update');
    Route::post('/ayllu/publicaciones/{publicacion}/comentar', [App\Http\Controllers\Ayllu\AylluController::class, 'storeComentario'])
        ->name('ayllu.publicaciones.comentar');
    Route::post('/ayllu/publicaciones/{publicacion}/reaccionar', [App\Http\Controllers\Ayllu\AylluController::class, 'toggleReaccion'])
        ->name('ayllu.publicaciones.reaccionar');
    Route::post('/ayllu/publicaciones/{publicacion}/destacar', [App\Http\Controllers\Ayllu\AylluController::class, 'toggleDestacar'])
        ->name('ayllu.publicaciones.destacar');
    Route::delete('/ayllu/publicaciones/{publicacion}', [App\Http\Controllers\Ayllu\AylluController::class, 'eliminarPublicacion'])
        ->name('ayllu.publicaciones.eliminar');

    // Comunidades de Curso, Sílabo Vivo y Evaluación Formativa (Plano 2)
    Route::get('/ayllu/cursos/{curso}', [App\Http\Controllers\Ayllu\AylluController::class, 'cursoComunidad'])
        ->name('ayllu.cursos.show');
    Route::post('/ayllu/cursos/{curso}/entregas', [App\Http\Controllers\Ayllu\AylluController::class, 'storeEntregaFormativa'])
        ->name('ayllu.cursos.entregas.store');
    Route::post('/ayllu/entregas/{entrega}/retroalimentar', [App\Http\Controllers\Ayllu\AylluController::class, 'storeRetroalimentacion'])
        ->name('ayllu.entregas.retroalimentar');
    Route::post('/ayllu/entregas/{entrega}/portafolio', [App\Http\Controllers\Ayllu\AylluController::class, 'togglePortafolio'])
        ->name('ayllu.entregas.portafolio');

    // Mesa Docente de Proyectos Integradores Anuales + Portafolio Docente EIB
    Route::get('/ayllu/proyecto-integrador', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'index'])
        ->name('ayllu.proyecto');
    Route::get('/ayllu/proyecto-integrador/aportes/{aporte}', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'showAporte'])
        ->name('ayllu.pi.aportes.show');
    Route::match(['put', 'patch'], '/ayllu/proyecto-integrador/aportes/{aporte}', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'actualizarAporteOperativo'])
        ->name('ayllu.pi.aportes.update');
    Route::post('/ayllu/proyecto-integrador/aportes/{aporte}/versiones', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'storeEvidenciaVersion'])
        ->name('ayllu.pi.versiones.store');
    Route::post('/ayllu/proyecto-integrador/versiones/{version}/revisar', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'revisarOIntegrarVersion'])
        ->name('ayllu.pi.versiones.revisar');
    Route::get('/ayllu/proyecto-integrador/versiones/{version}/archivo', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'descargarArchivoEvidencia'])
        ->name('ayllu.pi.versiones.archivo');
    Route::post('/ayllu/proyecto-integrador/versiones/{version}/restriccion', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'actualizarRestriccionEvidencia'])
        ->name('ayllu.pi.versiones.restriccion');
    Route::post('/ayllu/proyecto-integrador/articulaciones', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'storeArticulacion'])
        ->name('ayllu.pi.articulaciones.store');
    Route::post('/ayllu/proyecto-integrador/bitacora', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'storeBitacora'])
        ->name('ayllu.pi.bitacora.store');
    Route::post('/ayllu/proyecto-integrador/sintesis', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'solicitarSintesisComunitaria'])
        ->name('ayllu.pi.sintesis.store');
    Route::post('/ayllu/proyecto-integrador/sintesis/{sintesis}/revisar', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'revisarSintesisComunitaria'])
        ->name('ayllu.pi.sintesis.revisar');
    Route::post('/ayllu/proyecto-integrador/coordinacion/importar', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'ejecutarImportacionWeb'])
        ->name('ayllu.pi.coordinacion.importar');
    Route::post('/ayllu/proyecto-integrador/coordinacion/adoptar-version', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'adoptarVersionDefinicion'])
        ->name('ayllu.pi.coordinacion.adoptar');
    Route::post('/ayllu/proyecto-integrador/ejecuciones/{ejecucion}/hitos', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'guardarFechasHitosOperativos'])
        ->name('ayllu.pi.ejecuciones.hitos');
    Route::post('/ayllu/proyecto-integrador/coordinacion/asignaciones', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'asignarDocenteMesa'])
        ->name('ayllu.pi.asignaciones.store');
    Route::post('/ayllu/proyecto-integrador/coordinacion/asignaciones/{asignacion}/revocar', [App\Http\Controllers\Ayllu\AylluMesaProyectoController::class, 'revocarAsignacionMesa'])
        ->name('ayllu.pi.asignaciones.revocar');

    // Comunidades y Grupos Interculturales (Ayllus)
    Route::get('/ayllu/grupos', [App\Http\Controllers\Ayllu\AylluController::class, 'grupos'])
        ->name('ayllu.grupos');
    Route::post('/ayllu/grupos', [App\Http\Controllers\Ayllu\AylluController::class, 'storeGrupo'])
        ->name('ayllu.grupos.store');
    Route::post('/ayllu/grupos/{grupo}/miembro', [App\Http\Controllers\Ayllu\AylluController::class, 'toggleMiembroGrupo'])
        ->name('ayllu.grupos.miembro');

    // Calendario Vivo y Chat Académico (Rimanakuy)
    Route::get('/ayllu/calendario', [App\Http\Controllers\Ayllu\AylluController::class, 'calendario'])
        ->name('ayllu.calendario');
    Route::post('/ayllu/calendario', [App\Http\Controllers\Ayllu\AylluController::class, 'storeEventoCalendario'])
        ->name('ayllu.calendario.store');
    Route::get('/ayllu/chat', [App\Http\Controllers\Ayllu\AylluController::class, 'chat'])
        ->name('ayllu.chat');
    Route::post('/ayllu/chat', [App\Http\Controllers\Ayllu\AylluController::class, 'storeMensajeChat'])
        ->name('ayllu.chat.store');

    // Mi Espacio Privado (Plano 3: Notas Oficiales, Asistencia, Matrícula y Trámites)
    Route::get('/ayllu/mi-espacio-privado', [App\Http\Controllers\Ayllu\AylluController::class, 'miEspacioPrivado'])
        ->name('ayllu.privado');

    // Perfil Intercultural y Portafolio EIB
    Route::get('/ayllu/perfil/{usuario?}', [App\Http\Controllers\Ayllu\AylluController::class, 'perfil'])
        ->name('ayllu.perfil');
    Route::post('/ayllu/perfil', [App\Http\Controllers\Ayllu\AylluController::class, 'actualizarPerfil'])
        ->name('ayllu.perfil.update');
});

if (app()->isLocal()) {
    Route::prefix('auditoria')->group(function () {
        Route::get('/login/{perfil}', function (string $perfil) {
            $emails = [
                'superadmin' => 'auditor.superadmin@puklla.test',
                'docente-seguimiento' => 'auditor.docente.seguimiento@puklla.test',
                'docente-entrevistador' => 'marcelbosch.iesp@pukllavirtual.edu.pe',
                'docente-regular' => 'auditor.docente.regular@puklla.test',
                'coordinacion' => 'auditor.coordinacion@puklla.test',
                'direccion' => 'auditor.direccion@puklla.test',
                'egresada' => 'auditor.egresada@puklla.test',
            ];

            $email = $emails[$perfil] ?? null;
            if (!$email) {
                return redirect()->route('login')->withErrors(['email' => "Perfil de auditoría '{$perfil}' no reconocido."]);
            }

            $user = \App\Models\User::where('email', $email)->first();
            if (!$user) {
                \Illuminate\Support\Facades\Artisan::call('puklla:auditoria-perfiles');
                $user = \App\Models\User::where('email', $email)->first();
            }

            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user);
                request()->session()->regenerate();

                if ($user->hasRole('super-admin')) {
                    return redirect()->route('admin')->with('userData', $user);
                } elseif ($user->hasRole('coordinacion-empleabilidad')) {
                    return redirect()->route('seguimiento-egresados.index');
                } elseif ($user->hasRole('direccion')) {
                    return redirect()->route('seguimiento.laboratorio');
                } elseif ($user->hasRole('admin')) {
                    return redirect()->route('admin')->with('userData', $user);
                } elseif ($user->hasRole('docente') && $user->docente) {
                    return redirect()->route('vistaDocente', ['docente' => $user->docente->id]);
                } elseif ($user->condicion === 'Egresado') {
                    return redirect()->route('egresado.intranet');
                }
                return redirect('/home');
            }

            return redirect()->route('login')->withErrors(['email' => "No se pudo iniciar sesión como {$email}"]);
        })->name('auditoria.login.perfil');

        Route::get('/logout', function () {
            \Illuminate\Support\Facades\Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Sesión de auditoría cerrada correctamente.');
        })->name('auditoria.logout');
    });

    Route::get('/dev/login-docente', function () {
        $docente = \App\Models\Docente::first();
        if ($docente && $docente->user) {
            \Illuminate\Support\Facades\Auth::login($docente->user);
            return redirect()->route('vistaDocente', ['docente' => $docente->id]);
        }
        return response('No se encontró docente en la base de datos.', 404);
    })->name('dev.login-docente');

    // Accesos directos de prueba interactiva exclusivamente en entorno local para Ayllu Puklla
    Route::get('/dev/ayllu/{rol}', function (string $rol) {
        abort_unless(app()->environment('local'), 404);

        $user = null;
        if ($rol === 'alumno-fid') {
            $alumno = \App\Models\Alumno::whereHas('user')->whereHas('cursos')->first()
                ?? \App\Models\Alumno::whereHas('user')->first();
            $user = $alumno?->user;
        } elseif ($rol === 'alumno-ppd') {
            $ppd = \App\Models\ppd::whereHas('user')->first();
            $user = $ppd?->user;
        } elseif ($rol === 'docente') {
            $docente = \App\Models\Docente::whereHas('user')->whereHas('cursos')->first()
                ?? \App\Models\Docente::whereHas('user')->first();
            $user = $docente?->user;
        } elseif ($rol === 'coordinacion') {
            $user = \App\Models\User::role(['super-admin', 'admin', 'direccion'])->first();
        }

        if (!$user) {
            return response("No se encontró usuario local para el perfil '{$rol}'.", 404);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('ayllu.inicio')
            ->with('success', 'Sesión interactiva iniciada como: ' . $user->name . ' (' . strtoupper($rol) . ')');
    })->name('dev.ayllu.login');
}


