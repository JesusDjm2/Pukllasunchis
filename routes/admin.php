<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFidController;
use App\Http\Controllers\CursosEspeciales\CeAlumnoController;
use App\Http\Controllers\CursosEspeciales\CeContenidoController;
use App\Http\Controllers\CursosEspeciales\CeDocenteController;
use App\Http\Controllers\CursosEspeciales\CeProgresoController;
use App\Http\Controllers\CursosEspeciales\CursoEspecialController;
use App\Http\Controllers\AdminPpdController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoCursoController;
use App\Http\Controllers\BolsaController;
use App\Http\Controllers\BolsaTrabajoOfertaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CapacidadesController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\ComunicadoController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DocenteCOntroller;
use App\Http\Controllers\EnfoquesController;
use App\Http\Controllers\EstandaresController;
use App\Http\Controllers\PeriodoActualController;
use App\Http\Controllers\PeriodoActualPpdController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PostulantesPpdController;
use App\Http\Controllers\PostulantesRegularController;
use App\Http\Controllers\PpdController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\Seguridad\BitacoraAccesosController;
use App\Http\Controllers\SilaboController;
use App\Http\Controllers\vistasAlumnosController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

// ═══════════════════════════════════════════════════════════════
// 🌐 RUTAS PÚBLICAS (sin autenticación requerida)
// ═══════════════════════════════════════════════════════════════

Route::get('login', [AdminController::class, 'login'])->name('login');

// RouteServiceProvider::HOME = '/home': a dónde redirige el middleware
// "guest" a un usuario ya autenticado (p. ej. POST /login con sesión activa).
// Sin esta ruta esa redirección terminaba en un 404 real.
Route::get('/home', [AdminController::class, 'homeRedirect'])
    ->middleware('auth')
    ->name('home.guest-redirect');

Route::get('/inhabilitado', fn () => view('admin.inhabilitado'))->name('inhabilitado');

// Intranet simple para egresados (condicion='Egresado') — separada de la
// intranet normal de alumno/alumnoB porque esa asume matrícula/ciclo activo.
Route::get('/egresado', [App\Http\Controllers\EgresadoIntranetController::class, 'index'])
    ->middleware('auth')
    ->name('egresado.intranet');

// "¿Algún dato no coincide?" desde Mi proceso de titulación (egresado o alumno FID).
Route::post('/mi-titulacion/reportar', [App\Http\Controllers\MiTitulacionController::class, 'reportar'])
    ->middleware(['auth', 'throttle:5,60'])
    ->name('mi-titulacion.reportar');

// Carta de aceptación de la Institución Educativa (paso después de la RD del PTI): la solicita el
// propio estudiante con su correo institucional; el archivo lo abren gestores o integrantes del grupo.
Route::middleware('auth')->group(function () {
    Route::get('/mi-titulacion/carta-aceptacion', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'crear'])
        ->name('mi-titulacion.carta');
    Route::post('/mi-titulacion/carta-aceptacion', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'enviar'])
        ->middleware('throttle:30,10')->name('mi-titulacion.carta.enviar');
    Route::get('/mi-titulacion/carta-aceptacion/{carta}/{cual}', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'archivo'])
        ->whereIn('cual', ['comprobante', 'carta', 'modelo'])->name('mi-titulacion.carta.archivo');
});

// GET simple para mantener viva la sesión (y el token CSRF) mientras se llena
// un formulario largo, como el de Sílabos. Un GET no necesita token CSRF y
// StartSession ya renueva la actividad de la sesión al procesarlo.
Route::get('/ping-sesion', fn () => response()->json(['ok' => true]))
    ->middleware('auth')
    ->name('ping.sesion');

// AJAX helpers para formulario público de incidencias
// Nota: Se mantienen públicos porque el formulario /incidencias (web.php) los necesita.
// Se protegen con throttle para evitar enumeración masiva.
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/ciclos-por-programa/{programa}', [App\Http\Controllers\IncidenciaController::class, 'ciclosPorPrograma'])->name('api.ciclos');
    Route::get('/api/alumnos-por-ciclo/{ciclo}', [App\Http\Controllers\IncidenciaController::class, 'alumnosPorCiclo'])->name('api.alumnos');
});

// Formularios públicos de inscripción / postulación
Route::get('formulario-de-inscrición-regular', [PostulantesRegularController::class, 'form'])->name('formInscripcionRegular');
Route::get('/get-provincias/{departamento}', [PostulantesRegularController::class, 'getProvincias']);
Route::get('/get-distritos/{provincia}', [PostulantesRegularController::class, 'getDistritos']);
Route::get('profesionalizacion-docente/inscripcion', [PostulantesPpdController::class, 'create'])->name('postulantes.ppd.create');
Route::resource('inscripcion-regulares', PostulantesRegularController::class)
    ->only(['create', 'store'])->names('regulares');

// Consulta DNI (usada en formularios públicos — throttled)
Route::post('/consulta-dni', [AdminFidController::class, 'consultar'])
    ->name('consulta.dni')
    ->middleware('throttle:30,1');

// Bolsa de trabajo: envío público del formulario por empleadores
Route::post('bolsa-trabajo/ofertas', [BolsaTrabajoOfertaController::class, 'store'])
    ->name('bolsa-trabajo.ofertas.store');

// Postulantes bolsa de trabajo (registro público de candidatos)
Route::resource('postulante', PostulanteController::class);

// ═══════════════════════════════════════════════════════════════
// 🔐 RUTAS AUTENTICADAS (cualquier usuario con sesión activa)
// ═══════════════════════════════════════════════════════════════

Route::middleware('auth')->group(function () {

    Route::get('/Administrador', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // ── Alumnos FID ───────────────────────────────────────────
    Route::prefix('alumnos')->group(function () {
        Route::resource('alumnos', AlumnoController::class)->except(['store']);
        Route::post('alumnos', [AlumnoController::class, 'store'])
            ->name('alumnos.store')
            ->middleware('web');
        Route::get('alumnos/create', [AlumnoController::class, 'create'])
            ->name('alumnos.create')
            ->withoutMiddleware('auth');
        Route::get('calificaciones/{alumno}', [AlumnoController::class, 'calificaciones'])->name('calificaciones');
    });

    // Tutoría individual y buzón de sugerencias desde la intranet (los mismos del QR, con sesión).
    Route::middleware('role:alumno')->group(function () {
        Route::get('alumnos/tutoria-y-sugerencias', [App\Http\Controllers\TutoriaController::class, 'alumnoPanel'])->name('alumno.atencion');
        Route::post('alumnos/tutoria', [App\Http\Controllers\TutoriaController::class, 'alumnoStore'])->name('alumno.tutoria.store')->middleware('throttle:5,1');
        Route::post('alumnos/sugerencia', [App\Http\Controllers\SugerenciaController::class, 'alumnoStore'])->name('alumno.sugerencia.store')->middleware('throttle:5,1');
    });

    Route::get('alumnos/actualizar-datos', [AlumnoController::class, 'editarDatos'])->name('alumnos.editarDatos');
    Route::post('alumnos/actualizar-datos', [AlumnoController::class, 'actualizarDatos'])->name('alumnos.actualizarDatos');
    Route::get('ficha-matricula/{alumno}', [AlumnoController::class, 'ficha'])->name('ficha-matricula');
    Route::get('/alumnos/{alumno}/ficha-pdf', [vistasAlumnosController::class, 'exportarFichaPDF'])->name('alumno.ficha.pdf');

    // ── Investigaciones (Ferias de Investigación) — alumno ────────────────
    Route::prefix('taqe')->name('taqe.')->group(function () {
        Route::get('/', [App\Http\Controllers\TaqeController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\TaqeController::class, 'store'])->name('store');
        Route::get('/guia-pdf', function () {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.instructivo-investigaciones-ciclo2');
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('Guia_Estudiante_Investigaciones_Ciclo_II.pdf');
        })->name('guia.pdf');
        Route::get('/curso/{curso}/alumnos', [App\Http\Controllers\TaqeController::class, 'alumnosDelCurso'])->name('alumnosDelCurso');
        Route::post('/{grupo}/responder', [App\Http\Controllers\TaqeController::class, 'responder'])->name('responder');
        Route::post('/{grupo}/salir', [App\Http\Controllers\TaqeController::class, 'salir'])->name('salir');
        Route::post('/{grupo}/integrantes', [App\Http\Controllers\TaqeController::class, 'agregarIntegrantes'])->name('integrantes.agregar');
        Route::delete('/{grupo}/integrantes/{integrante}', [App\Http\Controllers\TaqeController::class, 'retirarIntegrante'])->name('integrantes.retirar');
        Route::post('/{grupo}/organizador', [App\Http\Controllers\TaqeController::class, 'cambiarOrganizador'])->name('organizador.cambiar');
        Route::post('/{grupo}/solicitar-ayuda', [App\Http\Controllers\TaqeController::class, 'solicitarAyuda'])->name('solicitarAyuda');
        Route::post('/{grupo}/trabajos', [App\Http\Controllers\TaqeController::class, 'storeTrabajo'])->name('trabajos.store');
        // Trabajo final: varios archivos/enlaces (subidos directo o pasados desde evidencias); el organizador lo entrega.
        Route::post('/{grupo}/entregar', [App\Http\Controllers\TaqeController::class, 'entregar'])->name('entregar');
        Route::post('/{grupo}/reabrir', [App\Http\Controllers\TaqeController::class, 'reabrir'])->name('reabrir');
        Route::post('/trabajos/{trabajo}/mover', [App\Http\Controllers\TaqeController::class, 'moverTrabajo'])->name('trabajos.mover');
        Route::get('/trabajos/{trabajo}/ver', [App\Http\Controllers\TaqeController::class, 'verTrabajo'])->name('trabajos.ver');
        Route::delete('/trabajos/{trabajo}', [App\Http\Controllers\TaqeController::class, 'destroyTrabajo'])->name('trabajos.destroy');
    });

    Route::get('Alumnos-Formulario', [vistasAlumnosController::class, 'form'])->name('vistAlumno');
    Route::get('/obtener-ciclos/{programa}', [vistasAlumnosController::class, 'obtenerCiclos']);
    Route::get('/get-cursos/{ciclo}', [vistasAlumnosController::class, 'getCursos']);
    Route::get('/obtener-cursos/{cicloId}', [vistasAlumnosController::class, 'getCursos'])->name('obtener.cursos');

    // ── PPD ───────────────────────────────────────────────────
    Route::resource('profesionalización-docente', PpdController::class)->names('ppd');
    Route::get('calificacionesppd/{alumno}', [PpdController::class, 'calificacionesppd'])->name('calificacionesppd');
    Route::post('/Calificar-Profesionalizacion-Docente', [PpdController::class, 'calificar'])->name('calificarppd')->middleware('role:docente|admin|super-admin');
    Route::get('calificaciones-PPD/{alumno}', [PpdController::class, 'calificaciones'])->name('calificacionesPPD');
    Route::get('form-PPD', [PpdController::class, 'form'])->name('formPPD');

    // ── Tutor ─────────────────────────────────────────────────
    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TutorController::class, 'index'])->name('dashboard');
        Route::get('/ciclo/{ciclo}', [App\Http\Controllers\TutorController::class, 'ciclo'])->name('ciclo');
        Route::get('/ciclo/{ciclo}/qr-tutoria', [App\Http\Controllers\TutorController::class, 'qrTutoria'])->name('qr.tutoria');
        Route::get('/ciclo/{ciclo}/qr-sugerencia', [App\Http\Controllers\TutorController::class, 'qrSugerencia'])->name('qr.sugerencia');
        Route::post('/tutorias/{tutoria}/estado', [App\Http\Controllers\TutoriaController::class, 'marcarEstado'])->name('tutorias.estado');
        Route::post('/sugerencias/{sugerencia}/estado', [App\Http\Controllers\SugerenciaController::class, 'marcarEstado'])->name('sugerencias.estado');
        Route::post('/ciclo/{ciclo}/mensaje', [App\Http\Controllers\TutorController::class, 'enviarMensaje'])->name('mensaje')->middleware('throttle:10,1');
    });

    // ── Notificaciones (in-app) ──────────────────────────────
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/{id}/ir', [App\Http\Controllers\NotificacionController::class, 'ir'])->name('ir');
        Route::post('/marcar-todas-leidas', [App\Http\Controllers\NotificacionController::class, 'marcarTodasLeidas'])->name('marcarTodasLeidas');
    });

    Route::get('/admin/tutor/{user}/ciclos', [AdminController::class, 'tutorCiclosForm'])->name('admin.tutor.ciclos');
    Route::post('/admin/tutor/{user}/ciclos', [AdminController::class, 'tutorCiclosUpdate'])->name('admin.tutor.ciclos.update');

    // ═══════════════════════════════════════════════════════════════
    // 📚 CURSOS ESPECIALES ASINCRÓNICOS — Panel del Docente
    // (debe ir ANTES del resource 'docente' para evitar que {docente} capture el prefijo)
    // ═══════════════════════════════════════════════════════════════
    Route::middleware('role:docente')
        ->prefix('docente/mis-cursos-asincronicos')
        ->name('ce.docente.')
        ->group(function () {
            Route::get('/', [CeDocenteController::class, 'index'])->name('index');
            Route::get('/{curso}', [CeDocenteController::class, 'show'])->name('show');
            Route::get('/{curso}/edit', [CeDocenteController::class, 'edit'])->name('edit');
            Route::put('/{curso}', [CeDocenteController::class, 'update'])->name('update');

            // Niveles
            Route::get('/{curso}/niveles/create', [CeContenidoController::class, 'nivelesCreate'])->name('niveles.create');
            Route::post('/{curso}/niveles', [CeContenidoController::class, 'nivelesStore'])->name('niveles.store');
            Route::get('/{curso}/niveles/{nivel}/edit', [CeContenidoController::class, 'nivelesEdit'])->name('niveles.edit');
            Route::put('/{curso}/niveles/{nivel}', [CeContenidoController::class, 'nivelesUpdate'])->name('niveles.update');
            Route::delete('/{curso}/niveles/{nivel}', [CeContenidoController::class, 'nivelesDestroy'])->name('niveles.destroy');

            // Unidades
            Route::get('/{curso}/niveles/{nivel}/unidades/create', [CeContenidoController::class, 'unidadesCreate'])->name('unidades.create');
            Route::post('/{curso}/niveles/{nivel}/unidades', [CeContenidoController::class, 'unidadesStore'])->name('unidades.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/edit', [CeContenidoController::class, 'unidadesEdit'])->name('unidades.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}', [CeContenidoController::class, 'unidadesUpdate'])->name('unidades.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}', [CeContenidoController::class, 'unidadesDestroy'])->name('unidades.destroy');

            // Lecciones
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/create', [CeContenidoController::class, 'leccionesCreate'])->name('lecciones.create');
            Route::post('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones', [CeContenidoController::class, 'leccionesStore'])->name('lecciones.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}/edit', [CeContenidoController::class, 'leccionesEdit'])->name('lecciones.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}', [CeContenidoController::class, 'leccionesUpdate'])->name('lecciones.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}', [CeContenidoController::class, 'leccionesDestroy'])->name('lecciones.destroy');
            Route::post('/upload-audio-leccion', [CeContenidoController::class, 'uploadAudioLeccion'])->name('lecciones.upload-audio');

            // Ejercicios
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/create', [CeContenidoController::class, 'ejerciciosCreate'])->name('ejercicios.create');
            Route::post('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios', [CeContenidoController::class, 'ejerciciosStore'])->name('ejercicios.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}/edit', [CeContenidoController::class, 'ejerciciosEdit'])->name('ejercicios.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}', [CeContenidoController::class, 'ejerciciosUpdate'])->name('ejercicios.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}', [CeContenidoController::class, 'ejerciciosDestroy'])->name('ejercicios.destroy');
            Route::post('/upload-audio-ejercicio', [CeContenidoController::class, 'uploadAudioEjercicio'])->name('ejercicios.upload-audio');
        });

    // ── Docentes (compartido con admin) ───────────────────────
    Route::resource('docente', DocenteCOntroller::class)->names('docente');
    Route::get('Dashboard-Docente/{docente}', [DocenteCOntroller::class, 'vistaDocente'])->name('vistaDocente');
    Route::get('asignar-cursos/{id}', [DocenteCOntroller::class, 'asignar'])->name('asignar');
    Route::get('/docente/{docente}/alumnos', [DocenteCOntroller::class, 'alumnosPanel'])->name('docente.alumnos');
    Route::get('/docente/{docente}/curso/{curso}/calificaciones/excel', [DocenteCOntroller::class, 'exportarCalificacionesCurso'])->name('docente.curso.excel');
    Route::post('/docente/{docente}/curso/{curso}/mensaje', [DocenteCOntroller::class, 'enviarMensajeCurso'])->name('docente.curso.mensaje')->middleware(['role:docente', 'throttle:10,1']);
    Route::get('repositorio-de-silabos/{docente}', [DocenteCOntroller::class, 'repositorio'])->name('repositorio');
    Route::get('calificar/{id}', [DocenteCOntroller::class, 'calificar'])->name('calificar');

    Route::get('/docente/{docente}/blog/', [DocenteCOntroller::class, 'showBlog'])->name('docente.blog.show');
    Route::get('/Lista-de-alumnos/{curso}/{docente}', [DocenteCOntroller::class, 'showAlumnos'])->name('docentes.cursos.alumnos');
    Route::delete('/docente/{docente}/curso/{curso}', [DocenteCOntroller::class, 'eliminarCurso'])->name('docente.curso.eliminar');
    Route::match(['get', 'post'], 'docentes/{docente}/cursos/{curso}/calificar', [DocenteCOntroller::class, 'calificarCurso'])->name('competencias.calificar')->middleware('role:docente|admin|super-admin');
    Route::match(['get', 'post'], 'docentes/{docente}/cursosPPD/{curso}/calificar', [DocenteCOntroller::class, 'calificarCursoPPD'])->name('competencias.calificar.ppd')->middleware('role:docente|admin|super-admin');
    Route::post('/docentes/blog/{id}', [DocenteCOntroller::class, 'updateBlog'])->name('docentes.updateBlog');

    // ── Investigaciones — vista de solo lectura del docente, por curso ────
    Route::get('docentes/{docente}/cursos/{curso}/taqe', [DocenteCOntroller::class, 'verTaqe'])->name('docente.curso.taqe');

    // ── Investigaciones — encargados/organizadores de un Proyecto Integrador (proyecto_docente) ────
    Route::middleware('role:docente|admin|super-admin')->prefix('proyectos-integradores')->name('taqe.proyecto.')->group(function () {
        Route::get('/{proyecto}/taqe', [App\Http\Controllers\TaqeProyectoDocenteController::class, 'index'])->name('index');
        Route::get('/taqe/trabajos/{trabajo}/ver', [App\Http\Controllers\TaqeProyectoDocenteController::class, 'verTrabajo'])->name('trabajos.ver');
        Route::get('/taqe/{grupo}/descargar/{que}', [App\Http\Controllers\TaqeProyectoDocenteController::class, 'descargarZip'])->whereIn('que', ['evidencias', 'final', 'todo'])->name('descargar');
    });

    // ── Investigaciones — seguimiento institucional del Investigador ──────
    Route::middleware('role:investigador')->prefix('taqe-investigador')->group(function () {
        Route::get('/', [App\Http\Controllers\TaqeInvestigadorController::class, 'index'])->name('taqe.investigador.index');
        Route::post('/solicitudes/{solicitud}/atender', [App\Http\Controllers\TaqeInvestigadorController::class, 'marcarAtendida'])->name('taqe.investigador.solicitudes.atender');
        Route::get('/trabajos/{trabajo}/ver', [App\Http\Controllers\TaqeInvestigadorController::class, 'verTrabajo'])->name('taqe.investigador.trabajos.ver');
        Route::get('/{grupo}/descargar/{que}', [App\Http\Controllers\TaqeInvestigadorController::class, 'descargarZip'])->whereIn('que', ['evidencias', 'final', 'todo'])->name('taqe.investigador.descargar');
    });

    // ── Investigaciones — gestión institucional del Super Admin (control total sobre cualquier grupo) ──
    Route::middleware('role:super-admin')->prefix('admin/taqe')->name('admin.taqe.')->group(function () {
        Route::get('/', [App\Http\Controllers\TaqeSuperAdminController::class, 'index'])->name('index');
        Route::get('/{grupo}/alumnos-disponibles', [App\Http\Controllers\TaqeSuperAdminController::class, 'alumnosDisponibles'])->name('alumnosDisponibles');
        Route::post('/{grupo}/integrantes', [App\Http\Controllers\TaqeSuperAdminController::class, 'agregarIntegrantes'])->name('integrantes.agregar');
        Route::delete('/{grupo}/integrantes/{integrante}', [App\Http\Controllers\TaqeSuperAdminController::class, 'retirarIntegrante'])->name('integrantes.retirar');
        Route::post('/solicitudes/{solicitud}/atender', [App\Http\Controllers\TaqeSuperAdminController::class, 'marcarAtendidaSolicitud'])->name('solicitudes.atender');
        Route::get('/trabajos/{trabajo}/ver', [App\Http\Controllers\TaqeSuperAdminController::class, 'verTrabajo'])->name('trabajos.ver');
        Route::delete('/trabajos/{trabajo}', [App\Http\Controllers\TaqeSuperAdminController::class, 'destroyTrabajo'])->name('trabajos.destroy');
        Route::get('/{grupo}/descargar/{que}', [App\Http\Controllers\TaqeSuperAdminController::class, 'descargarZip'])->whereIn('que', ['evidencias', 'final', 'todo'])->name('descargar');
        Route::delete('/{grupo}', [App\Http\Controllers\TaqeSuperAdminController::class, 'destroy'])->name('destroy');
    });

    // ── Incidencias (docentes autenticados) ───────────────────
    Route::get('admin/docente/{docente}/incidencias', [App\Http\Controllers\IncidenciaController::class, 'adminIndex'])->name('admin.docente.incidencias');

    // ── Tutorías individuales y buzón de sugerencias (QR) ──────
    Route::get('admin/tutorias/qr', [App\Http\Controllers\TutoriaController::class, 'qr'])->name('admin.tutorias.qr');
    Route::get('admin/sugerencias/qr', [App\Http\Controllers\SugerenciaController::class, 'qr'])->name('admin.sugerencias.qr');

    // ── Seguimiento consolidado: Incidencias · Tutorías · Sugerencias ──
    Route::get('admin/seguimiento', [App\Http\Controllers\SeguimientoController::class, 'index'])->name('admin.seguimiento');
    Route::get('admin/seguimiento/qr', [App\Http\Controllers\SeguimientoController::class, 'qrCiclos'])->name('admin.seguimiento.qr');
    Route::post('admin/tutorias/{tutoria}/estado', [App\Http\Controllers\TutoriaController::class, 'marcarEstado'])->name('admin.tutorias.estado');
    Route::post('admin/sugerencias/{sugerencia}/estado', [App\Http\Controllers\SugerenciaController::class, 'marcarEstado'])->name('admin.sugerencias.estado');

    // Eliminar registros de seguimiento — solo administrador/super-administrador
    Route::delete('admin/incidencias/{incidencia}', [App\Http\Controllers\IncidenciaController::class, 'destroy'])
        ->name('admin.incidencias.destroy')->middleware('role:admin|super-admin');
    Route::delete('admin/tutorias/{tutoria}', [App\Http\Controllers\TutoriaController::class, 'destroy'])
        ->name('admin.tutorias.destroy')->middleware('role:admin|super-admin');
    Route::delete('admin/sugerencias/{sugerencia}', [App\Http\Controllers\SugerenciaController::class, 'destroy'])
        ->name('admin.sugerencias.destroy')->middleware('role:admin|super-admin');

    Route::prefix('docente/{docente}/incidencias')->name('docente.incidencias.')->group(function () {
        Route::get('/', [App\Http\Controllers\IncidenciaController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\IncidenciaController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\IncidenciaController::class, 'store'])->name('store');
    });

    // ── Calificaciones (docentes + admin) ─────────────────────
    Route::post('/publicar-periodo-uno', [CalificacionController::class, 'publicarPeriodoUno'])->name('publicar.periodo.uno')->middleware('role:admin|super-admin');
    Route::post('/periodouno/storeBloque', [CalificacionController::class, 'storePeriodoEnBloque'])->name('periodouno.storeBloque')->middleware('role:docente|admin|super-admin');
    Route::delete('/eliminar-periodo-uno', [CalificacionController::class, 'eliminarPeriodoUno'])->name('periodouno.eliminar')->middleware('role:admin|super-admin');
    // Deja en blanco las calificaciones de UN curso (con respaldo JSON previo).
    Route::delete('/cursos/{curso}/calificaciones', [CalificacionController::class, 'vaciarCurso'])->name('curso.calificaciones.vaciar')->middleware('role:admin|super-admin');
    Route::post('/periododos/storeBloque', [CalificacionController::class, 'storePeriodoDos'])->name('storePeriodoDos')->middleware('role:admin|super-admin');
    Route::delete('/eliminar-periodo-dos', [CalificacionController::class, 'eliminarPeriodoDos'])->name('periododos.eliminar')->middleware('role:admin|super-admin');
    Route::post('/periodotres/storeBloque', [CalificacionController::class, 'storePeriodoTres'])->name('storePeriodoTres')->middleware('role:admin|super-admin');
    Route::delete('/eliminar-periodo-tres', [CalificacionController::class, 'eliminarPeriodoTres'])->name('periodotres.eliminar')->middleware('role:admin|super-admin');
    Route::delete('/docentes/cursos/eliminar-todos-global', [CalificacionController::class, 'eliminarTodosCursosGlobal'])->name('docente.cursos.eliminarTodosGlobal')->middleware('role:admin|super-admin');
    Route::delete('/docentes/cursos-ppd/eliminar-todos-global', [CalificacionController::class, 'eliminarTodosCursosGlobalPPD'])->name('docente.cursos.eliminarTodosGlobalPPD')->middleware('role:admin|super-admin');
    Route::post('/calificaciones/store', [CalificacionController::class, 'nuevaCalificacion'])->name('guardarCalificacion')->middleware('role:admin|super-admin');
    Route::post('/guardar-calificaciones', [CalificacionController::class, 'guardarCalificacionesEnBloque'])->name('guardarCalificacionesEnBloque')->middleware('role:admin|super-admin');
    Route::post('/guardar-periodo-tres', [CalificacionController::class, 'guardarPeriodoTres'])->name('guardarPeriodoTres')->middleware('role:admin|super-admin');
    Route::post('/guardar-periodo2-desempeno', [CalificacionController::class, 'guardarPeriodo2yDesempenoEnBloque'])->name('guardarPeriodo2yDesempenoEnBloque')->middleware('role:docente|admin|super-admin');
    Route::post('/borrarCalificaciones', [CalificacionController::class, 'borrarCalificaciones'])->name('borrarCalificaciones')->middleware('role:admin|super-admin');
    Route::get('/calificaciones/eliminar-todas', [CalificacionController::class, 'borrarTodasLasCalificaciones'])->name('calificaciones.eliminarTodas')->middleware('role:admin|super-admin');
    Route::get('/exportar-csv/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSV'])->name('calificaciones.exportar')->middleware('role:docente|admin|super-admin');
    Route::get('/exportar-csvppd/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSVppd'])->name('calificaciones.exportar.ppd')->middleware('role:docente|admin|super-admin');
    Route::get('/exportar-periodo-actual-fid', [CalificacionController::class, 'exportarPeriodoActualFID'])->name('calificaciones.exportar.periodoActualFID')->middleware('role:admin|super-admin');

    // ── Cursos, Sílabos, Competencias ─────────────────────────
    Route::prefix('cursos')->group(function () {
        Route::resource('curso', CursoController::class);
    });
    Route::get('/asignar-curso/{id}', [AlumnoCursoController::class, 'asignar'])->name('asignar.cursos');
    Route::post('/guardar-cursos/{id}', [AlumnoCursoController::class, 'guardarCursos'])->name('guardar.cursos');
    Route::post('/asignar-curso/{docenteId}', [DocenteCOntroller::class, 'asignarCurso'])->name('cursos.asignar');
    Route::post('cursos/{curso}/upload-silabo', [CursoController::class, 'uploadSilabo'])->name('cursos.uploadSilabo');
    Route::delete('cursos/{curso}/destroy-silabo', [CursoController::class, 'destroySilabo'])->name('cursos.destroySilabo');
    Route::post('/cursos/{curso}/classroomClaveCRUD', [CursoController::class, 'classroomClaveCRUD'])->name('cursos.classroomClaveCRUD');
    Route::get('/curso/{cursoId}/competencias', [CalificacionController::class, 'gestionarCompetencias'])->name('curso.gestionar.competencias')->middleware('role:docente|admin|super-admin');
    Route::post('/curso/{cursoId}/competencias/guardar', [CalificacionController::class, 'guardarCompetenciasSeleccionadas'])->name('curso.guardar.competencias')->middleware('role:docente|admin|super-admin');
    Route::get('/curso/{curso}/docentes', [CursoController::class, 'asignarDocentesForm'])->name('curso.docentes.form');
    Route::post('/curso/{curso}/docentes', [CursoController::class, 'asignarDocentesUpdate'])->name('curso.docentes.update');

    Route::resource('silabos', SilaboController::class)->names('silabos');
    Route::post('/silabos/{silabo}/reuse', [SilaboController::class, 'reuse'])->name('silabos.reuse');
    Route::get('/silabo/{silabo}/pdf', [SilaboController::class, 'exportarPDF'])->name('silabo.pdf');
    Route::resource('competencias', CompetenciaController::class)->names('competencias');
    Route::resource('capacidades', CapacidadesController::class)->names('capacidades');
    Route::resource('enfoques', EnfoquesController::class)->names('enfoques');
    // Proyectos Integradores: solo admin y super-admin (antes bastaba con iniciar sesión).
    Route::resource('proyectos', ProyectoController::class)->names('proyectos')->middleware('role:admin|super-admin');
    Route::resource('estandares', EstandaresController::class)->names('estandares');

    // ── Períodos ──────────────────────────────────────────────
    Route::resource('periodos', PeriodoController::class)
        ->only(['index', 'create', 'store'])
        ->names('periodos');
    Route::get('/periodos/{nombre}', [PeriodoController::class, 'show'])->name('periodos.show');
    Route::resource('Periodo-Actual', PeriodoActualController::class)
        ->names('periodoactual')
        ->parameters(['Periodo-Actual' => 'periodoactual'])
        ->middleware('role:admin|super-admin');
    Route::post('Periodo-Actual/{periodoactual}/crear-calificaciones', [PeriodoActualController::class, 'crearCalificaciones'])->name('periodoactual.crearCalificaciones')->middleware('role:admin|super-admin');
    Route::get('/periodo-actual/{id}/actualizar-notas', [PeriodoActualController::class, 'actualizarNotas'])->name('periodoactual.actualizarNotas')->middleware('role:admin|super-admin');
    Route::get('Periodo-Actual/{periodoactual}/registros', [PeriodoActualController::class, 'showRegistros'])->name('periodoactual.showRegistros')->middleware('role:admin|super-admin');
    Route::patch('admin/periodo-registro/{registro}', [PeriodoActualController::class, 'updateRegistro'])->name('periodoactual.registro.update')->middleware('role:admin|super-admin');
    Route::post('Periodo-Actual/{periodoactual}/toggle-formulario', [PeriodoActualController::class, 'toggleFormulario'])->name('periodoactual.toggleFormulario')->middleware('role:admin|super-admin');
    Route::get('/admin/periodos/{id}/export', [PeriodoActualController::class, 'exportExcel'])->name('periodos.export')->middleware('role:admin|super-admin');
    Route::get('/admin/becas/calificaciones/export', [AdminController::class, 'exportBecasCalificaciones'])->name('admin.becas.calificaciones.export')->middleware('role:admin|super-admin');
    Route::get('periodos-de-admision', [PeriodoActualController::class, 'periodos'])->name('periodos.admision');
    Route::put('/ciclo/update-alumnos', [CicloController::class, 'updateCicloAlumnos'])->name('ciclo.updateAlumnos');

    // ── Bolsa de trabajo (gestión) ────────────────────────────
    Route::get('bolsa-trabajo/ofertas', [BolsaTrabajoOfertaController::class, 'index'])->name('bolsa-trabajo.ofertas.index');
    Route::get('bolsa-trabajo/ofertas/{oferta}/edit', [BolsaTrabajoOfertaController::class, 'edit'])->name('bolsa-trabajo.ofertas.edit');
    Route::put('bolsa-trabajo/ofertas/{oferta}', [BolsaTrabajoOfertaController::class, 'update'])->name('bolsa-trabajo.ofertas.update');
    Route::delete('bolsa-trabajo/ofertas/{oferta}', [BolsaTrabajoOfertaController::class, 'destroy'])->name('bolsa-trabajo.ofertas.destroy');
    Route::resource('trabajo', BolsaController::class)->names('trabajo');
    Route::get('lista-postulantes', [PostulanteController::class, 'lista'])->name('listaPostulantes');

    // ── Comunicados ───────────────────────────────────────────
    Route::prefix('admin/comunicados')->name('admin.comunicados.')->group(function () {
        Route::get('/', [ComunicadoController::class, 'index'])->name('index');
        Route::post('/', [ComunicadoController::class, 'store'])->name('store');
        Route::get('/{comunicado}/edit', [ComunicadoController::class, 'edit'])->name('edit');
        Route::put('/{comunicado}', [ComunicadoController::class, 'update'])->name('update');
        Route::delete('/{comunicado}', [ComunicadoController::class, 'destroy'])->name('destroy');
    });

    // ── Minkarikuy ────────────────────────────────────────────
    Route::prefix('admin/minkarikuy')->name('admin.minkarikuy.')->group(function () {
        Route::get('/', [App\Http\Controllers\MinkarikuyController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\MinkarikuyController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\MinkarikuyController::class, 'store'])->name('store');
        Route::get('/{minkarikuy}/edit', [App\Http\Controllers\MinkarikuyController::class, 'edit'])->name('edit');
        Route::put('/{minkarikuy}', [App\Http\Controllers\MinkarikuyController::class, 'update'])->name('update');
        Route::post('/{minkarikuy}/activar', [App\Http\Controllers\MinkarikuyController::class, 'activar'])->name('activar');
        Route::get('/{minkarikuy}/qr', [App\Http\Controllers\MinkarikuyController::class, 'qr'])->name('qr');
        Route::delete('/{minkarikuy}', [App\Http\Controllers\MinkarikuyController::class, 'destroy'])->name('destroy');
    });

    // ═══════════════════════════════════════════════════════════
    // 🛡️ RUTAS SOLO ADMINISTRADOR
    // ═══════════════════════════════════════════════════════════

    // Expediente del estudiante: admin y super-admin lo gestionan; el gestor de titulación lo
    // ve en solo lectura (con la pestaña Titulación). Las acciones siguen en el grupo de abajo.
    Route::get('/expediente/{user}', [\App\Http\Controllers\ExpedienteController::class, 'show'])
        ->middleware('role:admin|super-admin|gestor-titulacion|secretaria')->name('expediente.show');

    Route::middleware('role:admin|super-admin')->group(function () {

        // Panel principal y gestión de usuarios
        Route::get('Administrador', [AdminController::class, 'index'])->name('admin');
        Route::get('admin/edit/{id}', [AdminController::class, 'edit'])->name('adminEdit');
        Route::get('admin/destroy/{id}', [AdminController::class, 'destroy'])->name('adminDestroy');
        Route::put('/admin/update/{id}', [AdminController::class, 'update'])->name('adminUpdate');
        Route::put('/admin/inhabilitados/{user}/perfil', [AdminController::class, 'actualizarPerfilInhabilitado'])->name('adminInhabilitados.perfil');
        Route::get('/registro', [AdminController::class, 'create'])->name('registerAdmin');
        Route::post('/admin/store', [AdminController::class, 'store'])->name('adminStore');

        // Expediente del estudiante: trayectoria (licencias, retiros, reincorporaciones…) y notas por periodo
        Route::get('/seguimiento-estudiantil', [\App\Http\Controllers\PanelSeguimientoController::class, 'index'])->name('expediente.panel');
        Route::get('/seguimiento-estudiantil/exportar/{lista}', [\App\Http\Controllers\PanelSeguimientoController::class, 'exportar'])->name('expediente.panel.exportar');
        Route::post('/expediente/{user}/eventos', [\App\Http\Controllers\ExpedienteController::class, 'storeEvento'])->name('expediente.eventos.store');
        Route::put('/expediente/eventos/{evento}', [\App\Http\Controllers\ExpedienteController::class, 'updateEvento'])->name('expediente.eventos.update');
        Route::delete('/expediente/eventos/{evento}', [\App\Http\Controllers\ExpedienteController::class, 'destroyEvento'])->name('expediente.eventos.destroy');
        Route::post('/expediente/{user}/seguimiento', [\App\Http\Controllers\ExpedienteController::class, 'storeAccion'])->name('expediente.acciones.store');
        Route::put('/expediente/seguimiento/{accion}', [\App\Http\Controllers\ExpedienteController::class, 'updateAccion'])->name('expediente.acciones.update');
        Route::patch('/expediente/seguimiento/{accion}/estado', [\App\Http\Controllers\ExpedienteController::class, 'toggleAccion'])->name('expediente.acciones.toggle');
        Route::delete('/expediente/seguimiento/{accion}', [\App\Http\Controllers\ExpedienteController::class, 'destroyAccion'])->name('expediente.acciones.destroy');
        Route::post('/expediente/{user}/documentos', [\App\Http\Controllers\ExpedienteController::class, 'storeDocumento'])->name('expediente.documentos.store');
        Route::get('/expediente/documentos/{documento}', [\App\Http\Controllers\ExpedienteController::class, 'verDocumento'])->name('expediente.documentos.ver');
        Route::delete('/expediente/documentos/{documento}', [\App\Http\Controllers\ExpedienteController::class, 'destroyDocumento'])->name('expediente.documentos.destroy');

        // Listados y exportaciones de alumnos
        // "Matrículas FID/PPD" ahora son pestañas del directorio (/Administrador): las URLs viejas redirigen.
        Route::get('/admin/alumnos', [AdminController::class, 'directorioRedirect'])->defaults('tab', 'fid')->name('adminAlumnos');
        Route::post('/admin/estudiantes/exportar', [AdminController::class, 'exportarDirectorio'])->name('admin.estudiantes.exportar');
        Route::post('/admin/alumnos/exportar-excel', [AdminController::class, 'exportAlumnosExcel'])->name('admin.alumnos.export-excel');
        Route::get('/admin/alumnos/{alumno}/carnet', [AdminController::class, 'alumnoCarnet'])->name('admin.alumnos.carnet');
        Route::get('/admin/alumnos/demograficos', [AlumnoController::class, 'estadisticas'])->name('alumnos.demograficos');
        Route::delete('/admin/matriculas/{matricula}/quitar', [AdminController::class, 'quitarMatricula'])
            ->name('matriculas.quitar')->middleware('role:super-admin');
        Route::delete('/admin/matriculas-ppd/{matricula}/quitar', [AdminController::class, 'quitarMatriculaPpd'])
            ->name('matriculasppd.quitar')->middleware('role:super-admin');

        // Verificación de voucher + envío manual de ficha de matrícula (disponible para admin normal,
        // ya que cobranzas no tiene un rol propio en el sistema).
        Route::post('/admin/matriculas/{matricula}/verificar-voucher', [AdminController::class, 'verificarVoucherMatricula'])
            ->name('matriculas.verificarVoucher');
        Route::post('/admin/matriculas/{matricula}/enviar-ficha', [AdminController::class, 'enviarFichaMatricula'])
            ->name('matriculas.enviarFicha');
        Route::post('/admin/matriculas-ppd/{matricula}/verificar-voucher', [AdminController::class, 'verificarVoucherMatriculaPpd'])
            ->name('matriculasppd.verificarVoucher');
        Route::post('/admin/matriculas-ppd/{matricula}/enviar-ficha', [AdminController::class, 'enviarFichaMatriculaPpd'])
            ->name('matriculasppd.enviarFicha');

        // Libro de Reclamaciones
        Route::get('admin/reclamos', [App\Http\Controllers\ReclamoController::class, 'adminAll'])->name('admin.reclamos.todas');
        Route::get('admin/reclamos/{reclamo}', [App\Http\Controllers\ReclamoController::class, 'adminShow'])->name('admin.reclamos.show');

        Route::get('/admin/alumnosPPD', [AdminController::class, 'directorioRedirect'])->defaults('tab', 'ppd')->name('alumnosppd');
        Route::post('/admin/alumnosPPD/exportar-excel', [AdminController::class, 'exportAlumnosPpdExcel'])->name('admin.alumnosppd.export-excel');

        // Asignación de roles y usuarios (operaciones críticas)
        Route::post('/relacionar-usuario/{alumno}', [AdminController::class, 'relacionarUsuario'])->name('relacionarUsuario');
        Route::post('/asignar-rol-alumno/{alumno}', [AdminController::class, 'asignarRolAlumno'])->name('asignarRolAlumno');

        // Estructura académica (solo admin puede crear/editar)
        Route::prefix('programas')->group(function () {
            Route::resource('programa', ProgramaController::class);
        });
        Route::prefix('ciclos')->group(function () {
            Route::resource('ciclo', CicloController::class);
            Route::put('/ciclo/{ciclo}/marcar-egresados', [CicloController::class, 'marcarComoEgresados'])->name('ciclo.marcarEgresados');
        });

        // Gestión PPD
        Route::resource('admin-gestion-ppd', AdminPpdController::class)
            ->names('admin.ppd')
            ->parameters(['admin-gestion-ppd' => 'admin_ppd']);
        Route::resource('periodos-de-ppd', PeriodoActualPpdController::class)->names('periodos.admin.ppd');
        Route::get('admin/periodos/ppd/{id}/export', [PeriodoActualPpdController::class, 'export'])->name('periodos.admin.ppd.export');
        Route::patch('admin/periodo-ppd-registro/{registro}', [PeriodoActualPpdController::class, 'updateRegistro'])->name('periodos.admin.ppd.registro.update');
        Route::post('admin/periodo-ppd-registro', [PeriodoActualPpdController::class, 'storeRegistro'])->name('periodos.admin.ppd.registro.store');
        Route::post('periodos-admision-ppd/{id}/crear-calificaciones', [PeriodoActualPpdController::class, 'crearCalificaciones'])->name('periodos.admin.ppd.crearCalificaciones');
        Route::post('periodos-admision-ppd/{id}/sincronizar-calificaciones', [PeriodoActualPpdController::class, 'sincronizarCalificaciones'])->name('periodos.admin.ppd.sincronizarCalificaciones');
        Route::post('periodos-de-ppd/{periodos_de_ppd}/toggle-formulario', [PeriodoActualPpdController::class, 'toggleFormulario'])->name('periodos.admin.ppd.toggleFormulario');
        // Cierre del año PPD: vista previa y confirmación del paso de la promoción a egresados
        Route::get('periodos-de-ppd/{periodos_de_ppd}/cierre', [PeriodoActualPpdController::class, 'cierreAnio'])->name('periodos.admin.ppd.cierre');
        Route::post('periodos-de-ppd/{periodos_de_ppd}/cierre', [PeriodoActualPpdController::class, 'cerrarAnio'])->name('periodos.admin.ppd.cerrar');

        // Gestión FID
        Route::resource('admin-fids', AdminFidController::class);
        Route::post('/admin-fids/{adminFid}/crear-registros', [AdminFidController::class, 'asociarSinRelacion'])->name('admin-fids.asociar-sin-relacion');
        Route::get('/admin-fids/{adminFid}/ver-postulantes', [AdminFidController::class, 'verPostulantes'])->name('admin-fids.ver-postulantes');
        Route::get('/admin-fids/{adminFid}/postulantes', [AdminFidController::class, 'verPostulantes'])->name('admin-fids.verPostulantes');

        // Gestión de postulantes regulares (admin)
        Route::resource('inscripcion-regulares-fits', PostulantesRegularController::class)
            ->only(['index', 'edit', 'update', 'destroy', 'show'])
            ->names('regulares');
        Route::get('/regulares/{id}/enviar-correo', [PostulantesRegularController::class, 'enviarCorreo'])->name('regulares.enviarCorreo');
        Route::get('/postulantes/ingresantes', [PostulantesRegularController::class, 'crearIngresantes'])->name('postulantes.ingresantes');
        Route::post('/guardar-ingresantes', [PostulantesRegularController::class, 'guardarIngresantes'])->name('postulantes.guardarIngresantes');
        Route::post('/postulantes-regular/{id}/apto', [PostulantesRegularController::class, 'updateApto']);
        Route::post('/postulantes-regular/{id}/apto2', [PostulantesRegularController::class, 'updateApto2'])->name('postulantes.apto2');
        Route::post('/postulantes-regular/{id}/apto-status', [PostulantesRegularController::class, 'updateAptoStatus'])->name('postulantes.apto-status');
        Route::get('/postulante/{id}/toggle-observacion', [PostulantesRegularController::class, 'toggleObservacion'])->name('postulante.toggleObservacion');
        Route::get('/postulantes/exportar', [PostulantesRegularController::class, 'exportarCSV'])->name('postulantes.exportar');

        // Gestión de postulantes PPD (admin)
        Route::resource('postulantes-profesionalizacion-docente', PostulantesPpdController::class)
            ->parameters(['postulantes-profesionalizacion-docente' => 'postulante'])
            ->names('postulantes.ppd');
        Route::post('profesionalizacion-docente-postulantes/{postulanteId}/enviar-correo-ppd', [PostulantesPpdController::class, 'enviarCorreo'])->name('enviarcorreo.ppd');
        Route::get('/postulantes-ppd/seleccion-masiva', [PostulantesPpdController::class, 'seleccionMasiva'])->name('postulantes.ppd.seleccion-masiva');
        Route::post('/postulantes-ppd/convertir-masivo', [PostulantesPpdController::class, 'convertirMasivo'])->name('postulantes.ppd.convertir-masivo');
        Route::post('/postulantes/{id}/apto', [PostulantesPpdController::class, 'updateApto']);

    }); // fin role:admin

    // ═══════════════════════════════════════════════════════════════
    // 📚 CURSOS ESPECIALES ASINCRÓNICOS — Vista del alumno
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('cursos-especiales')->name('ce.alumno.')->group(function () {
        Route::get('/', [CeAlumnoController::class, 'index'])->name('index');
        Route::post('/{curso}/inscribir', [CeAlumnoController::class, 'inscribir'])->name('inscribir');
        Route::get('/{curso}', [CeAlumnoController::class, 'show'])->name('show');
        Route::get('/{curso}/leccion/{leccion}', [CeAlumnoController::class, 'leccion'])->name('leccion');
        Route::get('/{curso}/mi-progreso', [CeAlumnoController::class, 'progreso'])->name('progreso');

        // Progreso
        Route::post('/{curso}/leccion/{leccion}/completar', [CeProgresoController::class, 'marcarLeccion'])->name('leccion.completar');
        Route::post('/{curso}/ejercicio/{ejercicio}/resolver', [CeProgresoController::class, 'resolverEjercicio'])->name('ejercicio.resolver');
    });

    // ═══════════════════════════════════════════════════════════════
    // 📚 CURSOS ESPECIALES ASINCRÓNICOS — Gestión super-admin
    // ═══════════════════════════════════════════════════════════════
    Route::middleware('role:super-admin')
        ->prefix('admin/cursos-especiales')
        ->name('ce.cursos.')
        ->group(function () {
            Route::get('/', [CursoEspecialController::class, 'index'])->name('index');
            Route::get('/create', [CursoEspecialController::class, 'create'])->name('create');
            Route::post('/', [CursoEspecialController::class, 'store'])->name('store');
            Route::get('/{curso}', [CursoEspecialController::class, 'show'])->name('show');
            Route::get('/{curso}/edit', [CursoEspecialController::class, 'edit'])->name('edit');
            Route::put('/{curso}', [CursoEspecialController::class, 'update'])->name('update');
            Route::delete('/{curso}', [CursoEspecialController::class, 'destroy'])->name('destroy');

            // Niveles
            Route::get('/{curso}/niveles/create', [CeContenidoController::class, 'nivelesCreate'])->name('niveles.create');
            Route::post('/{curso}/niveles', [CeContenidoController::class, 'nivelesStore'])->name('niveles.store');
            Route::get('/{curso}/niveles/{nivel}/edit', [CeContenidoController::class, 'nivelesEdit'])->name('niveles.edit');
            Route::put('/{curso}/niveles/{nivel}', [CeContenidoController::class, 'nivelesUpdate'])->name('niveles.update');
            Route::delete('/{curso}/niveles/{nivel}', [CeContenidoController::class, 'nivelesDestroy'])->name('niveles.destroy');

            // Unidades
            Route::get('/{curso}/niveles/{nivel}/unidades/create', [CeContenidoController::class, 'unidadesCreate'])->name('unidades.create');
            Route::post('/{curso}/niveles/{nivel}/unidades', [CeContenidoController::class, 'unidadesStore'])->name('unidades.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/edit', [CeContenidoController::class, 'unidadesEdit'])->name('unidades.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}', [CeContenidoController::class, 'unidadesUpdate'])->name('unidades.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}', [CeContenidoController::class, 'unidadesDestroy'])->name('unidades.destroy');

            // Lecciones
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/create', [CeContenidoController::class, 'leccionesCreate'])->name('lecciones.create');
            Route::post('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones', [CeContenidoController::class, 'leccionesStore'])->name('lecciones.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}/edit', [CeContenidoController::class, 'leccionesEdit'])->name('lecciones.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}', [CeContenidoController::class, 'leccionesUpdate'])->name('lecciones.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}/lecciones/{leccion}', [CeContenidoController::class, 'leccionesDestroy'])->name('lecciones.destroy');
            Route::post('/upload-audio-leccion', [CeContenidoController::class, 'uploadAudioLeccion'])->name('lecciones.upload-audio');

            // Ejercicios
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/create', [CeContenidoController::class, 'ejerciciosCreate'])->name('ejercicios.create');
            Route::post('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios', [CeContenidoController::class, 'ejerciciosStore'])->name('ejercicios.store');
            Route::get('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}/edit', [CeContenidoController::class, 'ejerciciosEdit'])->name('ejercicios.edit');
            Route::put('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}', [CeContenidoController::class, 'ejerciciosUpdate'])->name('ejercicios.update');
            Route::delete('/{curso}/niveles/{nivel}/unidades/{unidad}/ejercicios/{ejercicio}', [CeContenidoController::class, 'ejerciciosDestroy'])->name('ejercicios.destroy');
            Route::post('/upload-audio-ejercicio', [CeContenidoController::class, 'uploadAudioEjercicio'])->name('ejercicios.upload-audio');
        });

    // ═══════════════════════════════════════════════════════════════
    // 🔒 BITÁCORA DE ACCESOS — Gestión super-admin
    // ═══════════════════════════════════════════════════════════════
    Route::middleware('role:super-admin')
        ->prefix('admin/bitacora-accesos')
        ->name('bitacora.')
        ->group(function () {
            Route::get('/', [BitacoraAccesosController::class, 'index'])->name('index');
            Route::get('/en-vivo', [BitacoraAccesosController::class, 'activos'])->name('activos');
            Route::get('/fallidos', [BitacoraAccesosController::class, 'fallidos'])->name('fallidos');
        });

    // ═══════════════════════════════════════════════════════════════
    // 🎓 TITULACIÓN — seguimiento de PTI/Tesis/Actas de egresados
    // Secretaría (rol secretaria) gestiona las RD; Investigación (gestor-titulacion), lo demás; las dos ven
    // todo. Qué le corresponde a cada una: App\Services\Titulacion\AreasTitulacion. Super-admin, todo.
    // ═══════════════════════════════════════════════════════════════
    Route::middleware(['role:super-admin|gestor-titulacion|secretaria', 'titulacion.area'])
        ->prefix('titulacion')
        ->name('titulacion.')
        ->group(function () {
            Route::get('/', [App\Http\Controllers\Titulacion\TitulacionController::class, 'index'])->name('index');
            Route::get('/crear-cuentas-masivo', [App\Http\Controllers\Titulacion\TitulacionController::class, 'crearCuentasMasivoPreview'])->name('crear-cuentas-masivo.preview');
            Route::post('/crear-cuentas-masivo', [App\Http\Controllers\Titulacion\TitulacionController::class, 'crearCuentasMasivoEjecutar'])->name('crear-cuentas-masivo.ejecutar');
            // Sincronizar la planilla de expedientes (EXP. PPD / EXP. FID): vista previa exacta y confirmación
            Route::get('/sincronizar', [App\Http\Controllers\Titulacion\SincronizacionController::class, 'form'])->name('sincronizar.form');
            Route::post('/sincronizar/vista-previa', [App\Http\Controllers\Titulacion\SincronizacionController::class, 'previsualizar'])->name('sincronizar.previa');
            Route::post('/sincronizar/aplicar', [App\Http\Controllers\Titulacion\SincronizacionController::class, 'aplicar'])->name('sincronizar.aplicar');
            Route::get('/importaciones/{importacion}/crear-cuenta', [App\Http\Controllers\Titulacion\TitulacionController::class, 'importacionCrearCuentaForm'])->name('importaciones.crear-cuenta.form');
            Route::post('/importaciones/{importacion}/crear-cuenta', [App\Http\Controllers\Titulacion\TitulacionController::class, 'importacionCrearCuentaGuardar'])->name('importaciones.crear-cuenta.guardar');
            Route::get('/importaciones/{importacion}/completar-ficha', [App\Http\Controllers\Titulacion\TitulacionController::class, 'importacionCompletarFichaForm'])->name('importaciones.completar-ficha.form');
            Route::post('/importaciones/{importacion}/completar-ficha', [App\Http\Controllers\Titulacion\TitulacionController::class, 'importacionCompletarFichaGuardar'])->name('importaciones.completar-ficha.guardar');
            Route::get('/egresados/{egresado}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'show'])->name('show');
            Route::get('/egresados/{egresado}/ficha', [App\Http\Controllers\Titulacion\TitulacionController::class, 'ficha'])->name('ficha');
            Route::post('/activos/{alumno}/iniciar', [App\Http\Controllers\Titulacion\TitulacionController::class, 'iniciarSeguimiento'])->name('iniciar');
            // Iniciar el proceso: datos del PTI + expediente correlativo + integrantes del grupo
            Route::get('/iniciar/buscar', [App\Http\Controllers\Titulacion\InicioProcesoController::class, 'buscar'])->name('inicio.buscar');
            Route::get('/iniciar/{persona}', [App\Http\Controllers\Titulacion\InicioProcesoController::class, 'crear'])->name('inicio.crear');
            Route::post('/iniciar/{persona}', [App\Http\Controllers\Titulacion\InicioProcesoController::class, 'guardar'])->name('inicio.guardar');
            // Etapa TI: informe ITI del asesor, asesores y jurado (1 o más, cargo opcional)
            Route::post('/egresados/{egresado}/informe-iti', [App\Http\Controllers\Titulacion\EtapaTiController::class, 'informe'])->name('ti.informe');
            Route::post('/egresados/{egresado}/participantes', [App\Http\Controllers\Titulacion\EtapaTiController::class, 'participanteGuardar'])->name('participantes.guardar');
            Route::post('/egresados/{egresado}/correo-jurado', [App\Http\Controllers\Titulacion\EtapaTiController::class, 'correoJurado'])->name('ti.correo-jurado');
            Route::get('/cartas-aceptacion', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'registros'])->name('cartas.index');
            // Importar las del formulario de Google (Excel de respuestas): subir → vista previa → importar (super-admin).
            Route::get('/cartas-aceptacion/importar', [App\Http\Controllers\Titulacion\ImportarCartasController::class, 'form'])->name('cartas.importar');
            Route::post('/cartas-aceptacion/importar', [App\Http\Controllers\Titulacion\ImportarCartasController::class, 'previa'])->name('cartas.importar.previa');
            Route::get('/cartas-aceptacion/importar/{token}', [App\Http\Controllers\Titulacion\ImportarCartasController::class, 'vista'])->name('cartas.importar.vista');
            Route::post('/cartas-aceptacion/importar/aplicar', [App\Http\Controllers\Titulacion\ImportarCartasController::class, 'aplicar'])->name('cartas.importar.aplicar');
            Route::post('/cartas-aceptacion/{carta}/atender', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'atender'])->name('cartas.atender');
            Route::post('/egresados/{egresado}/carta-externa', [App\Http\Controllers\Titulacion\CartaAceptacionController::class, 'registrarExterna'])->name('cartas.externa');
            // Secretaría corrige el grupo (retiro / incorporación con RD ya emitida): revisar y aplicar.
            Route::get('/egresados/{egresado}/grupo', [App\Http\Controllers\Titulacion\CambioGrupoController::class, 'editar'])->name('grupo.editar');
            Route::post('/egresados/{egresado}/grupo', [App\Http\Controllers\Titulacion\CambioGrupoController::class, 'aplicar'])->name('grupo.cambiar');
            // RD del PTI importada del Excel: completar número y año (Secretaría) y normalizar en bloque (super-admin).
            Route::post('/egresados/{egresado}/rd-pti', [App\Http\Controllers\Titulacion\RdPtiController::class, 'completar'])->name('rdpti.completar');
            Route::get('/rd-pti/normalizar', [App\Http\Controllers\Titulacion\RdPtiController::class, 'normalizar'])->name('rdpti.normalizar');
            Route::post('/rd-pti/normalizar', [App\Http\Controllers\Titulacion\RdPtiController::class, 'aplicar'])->name('rdpti.aplicar');
            Route::post('/egresados/{egresado}/tramite-sustentacion', [App\Http\Controllers\Titulacion\EtapaTesisController::class, 'tramite'])->name('tesis.tramite');
            Route::delete('/participantes/{participante}', [App\Http\Controllers\Titulacion\EtapaTiController::class, 'participanteEliminar'])->name('participantes.eliminar');
            Route::put('/egresados/{egresado}/estado', [App\Http\Controllers\Titulacion\TitulacionController::class, 'actualizarEstado'])->name('estado.actualizar');
            Route::post('/egresados/{egresado}/pti', [App\Http\Controllers\Titulacion\TitulacionController::class, 'ptiGuardar'])->name('pti.guardar');
            Route::post('/egresados/{egresado}/pti/nuevo', [App\Http\Controllers\Titulacion\TitulacionController::class, 'ptiNuevo'])->name('pti.nuevo');
            Route::post('/egresados/{egresado}/pti/ampliacion', [App\Http\Controllers\Titulacion\TitulacionController::class, 'ptiAmpliacion'])->name('pti.ampliacion');
            Route::post('/egresados/{egresado}/aviso-pti', [App\Http\Controllers\Titulacion\TitulacionController::class, 'avisoPtiEnviar'])->name('aviso-pti.enviar');
            Route::get('/egresados/{egresado}/aviso-pti/{tipo}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'avisoPtiVista'])->name('aviso-pti.vista');
            Route::post('/egresados/{egresado}/tesis',[App\Http\Controllers\Titulacion\TitulacionController::class, 'tesisGuardar'])->name('tesis.guardar');
            Route::post('/egresados/{egresado}/actas', [App\Http\Controllers\Titulacion\TitulacionController::class, 'actaGuardar'])->name('actas.guardar');
            Route::delete('/actas/{acta}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'actaEliminar'])->name('actas.eliminar');
            Route::put('/actas/{acta}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'actaActualizar'])->name('actas.actualizar');
            Route::post('/actas/{acta}/jurados', [App\Http\Controllers\Titulacion\TitulacionController::class, 'juradoGuardar'])->name('jurados.guardar');
            Route::delete('/jurados/{jurado}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'juradoEliminar'])->name('jurados.eliminar');
            Route::post('/egresados/{egresado}/resoluciones', [App\Http\Controllers\Titulacion\TitulacionController::class, 'resolucionGuardar'])->name('resoluciones.guardar');
            Route::delete('/resoluciones/{resolucion}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'resolucionEliminar'])->name('resoluciones.eliminar');
            Route::put('/resoluciones/{resolucion}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'resolucionActualizar'])->name('resoluciones.actualizar');
            Route::get('/asesores', [App\Http\Controllers\Titulacion\TitulacionController::class, 'asesoresIndex'])->name('asesores.index');
            Route::get('/demograficos', [App\Http\Controllers\Titulacion\TitulacionController::class, 'demograficos'])->name('demograficos');
            Route::post('/asesores', [App\Http\Controllers\Titulacion\TitulacionController::class, 'asesorGuardar'])->name('asesores.guardar');
            Route::put('/asesores/{asesor}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'asesorActualizar'])->name('asesores.actualizar');
            Route::post('/egresados/{egresado}/documentos', [App\Http\Controllers\Titulacion\TitulacionController::class, 'documentoGuardar'])->name('documentos.guardar');
            Route::delete('/documentos/{documento}', [App\Http\Controllers\Titulacion\TitulacionController::class, 'documentoEliminar'])->name('documentos.eliminar');
            // Resoluciones Directorales generadas por el sistema (PDF) y carga de la versión firmada
            Route::get('/egresados/{egresado}/rd/{plantilla}', [App\Http\Controllers\Titulacion\ResolucionDirectoralController::class, 'crear'])->name('rd.crear');
            Route::post('/egresados/{egresado}/rd/{plantilla}', [App\Http\Controllers\Titulacion\ResolucionDirectoralController::class, 'emitir'])->name('rd.emitir');
            Route::post('/resoluciones/{resolucion}/pdf', [App\Http\Controllers\Titulacion\ResolucionDirectoralController::class, 'subir'])->name('rd.subir');
            Route::post('/resoluciones/{resolucion}/correo', [App\Http\Controllers\Titulacion\CorreoRdController::class, 'enviar'])->name('rd.correo');
        });

    // PDF de una RD guardada por el sistema: el controlador verifica quién puede abrirlo.
    Route::get('/titulacion/rd/archivo/{nombre}', [App\Http\Controllers\Titulacion\ResolucionDirectoralController::class, 'archivo'])->name('titulacion.rd.archivo');

    // =========================================================================
    // IESP Ricardo Palma - Módulo Histórico (Actas Consolidadas 1995-2015)
    // =========================================================================
    Route::middleware('role:admin|super-admin')
        ->prefix('admin/ricardo-palma')
        ->name('rp.')
        ->group(function () {
            Route::get('/', [App\Http\Controllers\RicardoPalma\RpDashboardController::class, 'index'])->name('dashboard');
            Route::get('/programas', [App\Http\Controllers\RicardoPalma\RpProgramaController::class, 'index'])->name('programas.index');
            Route::get('/programas/{programa}', [App\Http\Controllers\RicardoPalma\RpProgramaController::class, 'show'])->name('programas.show');
            Route::get('/estudiantes', [App\Http\Controllers\RicardoPalma\RpEstudianteController::class, 'index'])->name('estudiantes.index');
            Route::get('/estudiantes/{estudiante}', [App\Http\Controllers\RicardoPalma\RpEstudianteController::class, 'show'])->name('estudiantes.show');
            Route::get('/estudiantes/{estudiante}/editar', [App\Http\Controllers\RicardoPalma\RpEstudianteController::class, 'editar'])->name('estudiantes.editar');
            Route::put('/estudiantes/{estudiante}', [App\Http\Controllers\RicardoPalma\RpEstudianteController::class, 'actualizar'])->name('estudiantes.actualizar');
            Route::delete('/estudiantes/{estudiante}', [App\Http\Controllers\RicardoPalma\RpEstudianteController::class, 'eliminar'])->name('estudiantes.eliminar');
            Route::get('/actas/{acta}/editar', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'editar'])->name('actas.editar');
            Route::put('/actas/{acta}', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'actualizar'])->name('actas.actualizar');
            Route::delete('/actas/{acta}', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'eliminar'])->name('actas.eliminar');
            Route::get('/actas', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'index'])->name('actas.index');
            Route::get('/actas/{acta}', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'show'])->name('actas.show');
            Route::get('/actas/{acta}/imprimir', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'imprimir'])->name('actas.imprimir');
            Route::get('/actas/{acta}/excel', [App\Http\Controllers\RicardoPalma\RpActaController::class, 'exportarExcel'])->name('actas.excel');
            Route::get('/importar', [App\Http\Controllers\RicardoPalma\RpImportacionController::class, 'index'])->name('importar.index');
            Route::post('/importar/acta', [App\Http\Controllers\RicardoPalma\RpImportacionController::class, 'importarActa'])->name('importar.acta');
            Route::get('/importar/plantilla', [App\Http\Controllers\RicardoPalma\RpImportacionController::class, 'descargarPlantilla'])->name('importar.plantilla');
            // Acta escaneada (PDF) leída con IA y corregida en pantalla
            Route::post('/importar/escaneo', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'subir'])->name('lecturas.subir');
            Route::get('/lecturas/{lectura}', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'editar'])->name('lecturas.editar');
            Route::get('/lecturas/{lectura}/stream', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'stream'])->name('lecturas.stream');
            Route::get('/lecturas/{lectura}/pdf', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'pdf'])->name('lecturas.pdf');
            Route::put('/lecturas/{lectura}', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'autoguardar'])->name('lecturas.autoguardar');
            Route::post('/lecturas/{lectura}/releer', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'releer'])->name('lecturas.releer');
            Route::post('/lecturas/{lectura}/cargar', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'cargar'])->name('lecturas.cargar');
            Route::delete('/lecturas/borradores/limpiar', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'limpiarTodos'])->name('lecturas.limpiar');
            Route::delete('/lecturas/{lectura}', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'descartar'])->name('lecturas.descartar');
            Route::get('/actas/{acta}/escaneo', [App\Http\Controllers\RicardoPalma\RpLecturaController::class, 'escaneoActa'])->name('actas.escaneo');
        });

}); // fin auth
