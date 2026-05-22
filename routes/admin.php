<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFidController;
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
use App\Http\Controllers\SilaboController;
use App\Http\Controllers\vistasAlumnosController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

// ═══════════════════════════════════════════════════════════════
// 🌐 RUTAS PÚBLICAS (sin autenticación requerida)
// ═══════════════════════════════════════════════════════════════

Route::get('login', [AdminController::class, 'login'])->name('login');

Route::get('/inhabilitado', fn () => view('admin.inhabilitado'))->name('inhabilitado');

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

    Route::get('alumnos/actualizar-datos', [AlumnoController::class, 'editarDatos'])->name('alumnos.editarDatos');
    Route::post('alumnos/actualizar-datos', [AlumnoController::class, 'actualizarDatos'])->name('alumnos.actualizarDatos');
    Route::post('/mostrar-contenido', [AlumnoController::class, 'mostrarContenido'])->name('mostrar-contenido');
    Route::get('ficha-matricula/{alumno}', [AlumnoController::class, 'ficha'])->name('ficha-matricula');
    Route::get('/alumnos/{alumno}/ficha-pdf', [vistasAlumnosController::class, 'exportarFichaPDF'])->name('alumno.ficha.pdf');

    Route::get('Alumnos-Formulario', [vistasAlumnosController::class, 'form'])->name('vistAlumno');
    Route::get('/obtener-ciclos/{programa}', [vistasAlumnosController::class, 'obtenerCiclos']);
    Route::get('/get-cursos/{ciclo}', [vistasAlumnosController::class, 'getCursos']);
    Route::get('/obtener-cursos/{cicloId}', [vistasAlumnosController::class, 'getCursos'])->name('obtener.cursos');

    // ── PPD ───────────────────────────────────────────────────
    Route::resource('profesionalización-docente', PpdController::class)->names('ppd');
    Route::get('calificacionesppd/{alumno}', [PpdController::class, 'calificacionesppd'])->name('calificacionesppd');
    Route::post('/Calificar-Profesionalizacion-Docente', [PpdController::class, 'calificar'])->name('calificarppd');
    Route::get('calificaciones-PPD/{alumno}', [PpdController::class, 'calificaciones'])->name('calificacionesPPD');
    Route::get('form-PPD', [PpdController::class, 'form'])->name('formPPD');

    // ── Tutor ─────────────────────────────────────────────────
    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TutorController::class, 'index'])->name('dashboard');
        Route::get('/ciclo/{ciclo}', [App\Http\Controllers\TutorController::class, 'ciclo'])->name('ciclo');
    });

    Route::get('/admin/tutor/{user}/ciclos', [AdminController::class, 'tutorCiclosForm'])->name('admin.tutor.ciclos');
    Route::post('/admin/tutor/{user}/ciclos', [AdminController::class, 'tutorCiclosUpdate'])->name('admin.tutor.ciclos.update');

    // ── Docentes (compartido con admin) ───────────────────────
    Route::resource('docente', DocenteCOntroller::class)->names('docente');
    Route::get('Dashboard-Docente/{docente}', [DocenteCOntroller::class, 'vistaDocente'])->name('vistaDocente');
    Route::get('asignar-cursos/{id}', [DocenteCOntroller::class, 'asignar'])->name('asignar');
    Route::get('/Alumnos-fid/{docente}', [DocenteCOntroller::class, 'alumnos'])->name('vistaAlumnos');
    Route::get('repositorio-de-silabos/{docente}', [DocenteCOntroller::class, 'repositorio'])->name('repositorio');
    Route::get('calificar/{id}', [DocenteCOntroller::class, 'calificar'])->name('calificar');

    Route::get('/docente/{docente}/blog/', [DocenteController::class, 'showBlog'])->name('docente.blog.show');
    Route::get('/Lista-de-alumnos/{curso}/{docente}', [DocenteController::class, 'showAlumnos'])->name('docentes.cursos.alumnos');
    Route::delete('/docente/{docente}/curso/{curso}', [DocenteController::class, 'eliminarCurso'])->name('docente.curso.eliminar');
    Route::post('docentes/{docente}/cursos/{curso}/calificar', [DocenteController::class, 'calificarCurso'])->name('competencias.calificar');
    Route::post('docentes/{docente}/cursosPPD/{curso}/calificar', [DocenteController::class, 'calificarCursoPPD'])->name('competencias.calificar.ppd');
    Route::post('/docentes/blog/{id}', [DocenteController::class, 'updateBlog'])->name('docentes.updateBlog');
    Route::get('/alumnos-ppd/{docente}', [DocenteController::class, 'alumnosppd'])->name('alumnosppd2');

    // ── Incidencias (docentes autenticados) ───────────────────
    Route::get('admin/incidencias', [App\Http\Controllers\IncidenciaController::class, 'adminAll'])->name('admin.incidencias.todas');
    Route::get('admin/docente/{docente}/incidencias', [App\Http\Controllers\IncidenciaController::class, 'adminIndex'])->name('admin.docente.incidencias');

    Route::prefix('docente/{docente}/incidencias')->name('docente.incidencias.')->group(function () {
        Route::get('/', [App\Http\Controllers\IncidenciaController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\IncidenciaController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\IncidenciaController::class, 'store'])->name('store');
    });

    // ── Calificaciones (docentes + admin) ─────────────────────
    Route::post('/publicar-periodo-uno', [CalificacionController::class, 'publicarPeriodoUno'])->name('publicar.periodo.uno');
    Route::post('/periodouno/storeBloque', [CalificacionController::class, 'storePeriodoEnBloque'])->name('periodouno.storeBloque');
    Route::delete('/eliminar-periodo-uno', [CalificacionController::class, 'eliminarPeriodoUno'])->name('periodouno.eliminar');
    Route::post('/periododos/storeBloque', [CalificacionController::class, 'storePeriodoDos'])->name('storePeriodoDos');
    Route::delete('/eliminar-periodo-dos', [CalificacionController::class, 'eliminarPeriodoDos'])->name('periododos.eliminar');
    Route::post('/periodotres/storeBloque', [CalificacionController::class, 'storePeriodoTres'])->name('storePeriodoTres');
    Route::delete('/eliminar-periodo-tres', [CalificacionController::class, 'eliminarPeriodoTres'])->name('periodotres.eliminar');
    Route::delete('/docentes/cursos/eliminar-todos-global', [CalificacionController::class, 'eliminarTodosCursosGlobal'])->name('docente.cursos.eliminarTodosGlobal');
    Route::post('/calificaciones/store', [CalificacionController::class, 'nuevaCalificacion'])->name('guardarCalificacion');
    Route::post('/guardar-calificaciones', [CalificacionController::class, 'guardarCalificacionesEnBloque'])->name('guardarCalificacionesEnBloque');
    Route::post('/guardar-periodo-tres', [CalificacionController::class, 'guardarPeriodoTres'])->name('guardarPeriodoTres');
    Route::post('/borrarCalificaciones', [CalificacionController::class, 'borrarCalificaciones'])->name('borrarCalificaciones');
    Route::get('/calificaciones/eliminar-todas', [CalificacionController::class, 'borrarTodasLasCalificaciones'])->name('calificaciones.eliminarTodas');
    Route::get('/exportar-csv/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSV'])->name('calificaciones.exportar');
    Route::get('/exportar-csvppd/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSVppd'])->name('calificaciones.exportar.ppd');

    // ── Cursos, Sílabos, Competencias ─────────────────────────
    Route::prefix('cursos')->group(function () {
        Route::resource('curso', CursoController::class);
    });
    Route::get('/asignar-curso/{id}', [AlumnoCursoController::class, 'asignar'])->name('asignar.cursos');
    Route::post('/guardar-cursos/{id}', [AlumnoCursoController::class, 'guardarCursos'])->name('guardar.cursos');
    Route::post('/asignar-curso/{docenteId}', [DocenteCOntroller::class, 'asignarCurso'])->name('cursos.asignar');
    Route::post('cursos/{curso}/upload-silabo', [CursoController::class, 'uploadSilabo'])->name('cursos.uploadSilabo');
    Route::get('cursos/{curso}/edit-silabo', [CursoController::class, 'editSilabo'])->name('cursos.editSilabo');
    Route::delete('cursos/{curso}/destroy-silabo', [CursoController::class, 'destroySilabo'])->name('cursos.destroySilabo');
    Route::post('/cursos/{curso}/classroomClaveCRUD', [CursoController::class, 'classroomClaveCRUD'])->name('cursos.classroomClaveCRUD');
    Route::get('/curso/{cursoId}/competencias', [CalificacionController::class, 'gestionarCompetencias'])->name('curso.gestionar.competencias');
    Route::post('/curso/{cursoId}/competencias/guardar', [CalificacionController::class, 'guardarCompetenciasSeleccionadas'])->name('curso.guardar.competencias');

    Route::resource('silabos', SilaboController::class)->names('silabos');
    Route::get('/silabo/{silabo}/pdf', [SilaboController::class, 'exportarPDF'])->name('silabo.pdf');
    Route::resource('competencias', CompetenciaController::class)->names('competencias');
    Route::resource('capacidades', CapacidadesController::class)->names('capacidades');
    Route::resource('enfoques', EnfoquesController::class)->names('enfoques');
    Route::resource('proyectos', ProyectoController::class)->names('proyectos');
    Route::resource('estandares', EstandaresController::class)->names('estandares');

    // ── Períodos ──────────────────────────────────────────────
    Route::resource('periodos', PeriodoController::class)
        ->only(['index', 'create', 'store'])
        ->names('periodos');
    Route::get('/periodos/{nombre}', [PeriodoController::class, 'show'])->name('periodos.show');
    Route::resource('Periodo-Actual', PeriodoActualController::class)
        ->names('periodoactual')
        ->parameters(['Periodo-Actual' => 'periodoactual']);
    Route::post('Periodo-Actual/{periodoactual}/crear-calificaciones', [PeriodoActualController::class, 'crearCalificaciones'])->name('periodoactual.crearCalificaciones');
    Route::get('/periodo-actual/{id}/actualizar-notas', [PeriodoActualController::class, 'actualizarNotas'])->name('periodoactual.actualizarNotas');
    Route::get('Periodo-Actual/{periodoactual}/registros', [PeriodoActualController::class, 'showRegistros'])->name('periodoactual.showRegistros');
    Route::post('Periodo-Actual/{periodoactual}/toggle-formulario', [PeriodoActualController::class, 'toggleFormulario'])->name('periodoactual.toggleFormulario');
    Route::get('/admin/periodos/{id}/export', [PeriodoActualController::class, 'exportExcel'])->name('periodos.export');
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
        Route::delete('/{minkarikuy}', [App\Http\Controllers\MinkarikuyController::class, 'destroy'])->name('destroy');
    });

    // ═══════════════════════════════════════════════════════════
    // 🛡️ RUTAS SOLO ADMINISTRADOR
    // ═══════════════════════════════════════════════════════════

    Route::middleware('role:admin')->group(function () {

        // Panel principal y gestión de usuarios
        Route::get('Administrador', [AdminController::class, 'index'])->name('admin');
        Route::get('admin/edit/{id}', [AdminController::class, 'edit'])->name('adminEdit');
        Route::get('admin/destroy/{id}', [AdminController::class, 'destroy'])->name('adminDestroy');
        Route::put('/admin/update/{id}', [AdminController::class, 'update'])->name('adminUpdate');
        Route::get('/registro', [AdminController::class, 'create'])->name('registerAdmin');
        Route::post('/admin/store', [AdminController::class, 'store'])->name('adminStore');

        // Listados y exportaciones de alumnos
        Route::get('/admin/alumnos', [AdminController::class, 'alumnos'])->name('adminAlumnos');
        Route::post('/admin/alumnos/exportar-excel', [AdminController::class, 'exportAlumnosExcel'])->name('admin.alumnos.export-excel');
        Route::get('/admin/alumnos/{alumno}/carnet', [AdminController::class, 'alumnoCarnet'])->name('admin.alumnos.carnet');
        Route::get('/admin/alumnos/demograficos', [AlumnoController::class, 'estadisticas'])->name('alumnos.demograficos');
        Route::delete('/admin/matriculas/{matricula}/quitar', [AdminController::class, 'quitarMatricula'])->name('matriculas.quitar');

        Route::get('/admin/alumnosPPD', [AdminController::class, 'alumnosppd'])->name('alumnosppd');
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

}); // fin auth
