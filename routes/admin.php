<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoCursoController;
use App\Http\Controllers\BolsaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CapacidadesController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DocenteCOntroller;
use App\Http\Controllers\EnfoquesController;
use App\Http\Controllers\EstadarController;
use App\Http\Controllers\EstandaresController;
use App\Http\Controllers\PeriodoActualController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PostulantesRegularController;
use App\Http\Controllers\PpdController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SilaboController;
use App\Http\Controllers\vistasAlumnosController;
use App\Models\Calificacion;

Auth::routes();

Route::get('/Administrador', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Administrador
Route::get('Administrador', [AdminController::class, 'index'])->name('admin')->middleware('auth');
Route::get('login', [AdminController::class, 'login'])->name('login');
Route::get('admin/edit/{id}', [AdminController::class, 'edit'])->name('adminEdit');
Route::get('admin/destroy/{id}', [AdminController::class, 'destroy'])->name('adminDestroy');
Route::put('/admin/update/{id}', [AdminController::class, 'update'])->name('adminUpdate');
Route::get('/registro', [AdminController::class, 'create'])->name('registerAdmin');
Route::post('/admin/store', [AdminController::class, 'store'])->name('adminStore');
Route::get('/admin/alumnos', [AdminController::class, 'alumnos'])->name('adminAlumnos');
Route::get('/admin/alumnosPPD', [AdminController::class, 'alumnosppd'])->name('alumnosppd');
Route::post('/relacionar-usuario/{alumno}', [AdminController::class, 'relacionarUsuario'])->name('relacionarUsuario');
Route::post('/asignar-rol-alumno/{alumno}', [AdminController::class, 'asignarRolAlumno'])->name('asignarRolAlumno');
Route::get('/inhabilitado', function () {
    return view('admin.inhabilitado');
})->name('inhabilitado');

//Programas
Route::prefix('programas')->middleware(['auth'])->group(function () {
    Route::resource('programa', ProgramaController::class);
});

//Ciclos
Route::prefix('ciclos')->middleware(['auth'])->group(function () {
    Route::resource('ciclo', CicloController::class);
});

//Cursos
Route::prefix('cursos')->middleware(['auth'])->group(function () {
    Route::resource('curso', CursoController::class);
});
//Asignar cursos
Route::get('/asignar-curso/{id}', [AlumnoCursoController::class, 'asignar'])->name('asignar.cursos');
Route::post('/guardar-cursos/{id}', [AlumnoCursoController::class, 'guardarCursos'])->name('guardar.cursos');


//Docentes
Route::resource('docente', DocenteCOntroller::class)->names('docente')->middleware('auth');
Route::get('Dashboard-Docente/{docente}', [DocenteCOntroller::class, 'vistaDocente'])->name('vistaDocente')->middleware('auth');
Route::get('asignar-cursos/{id}', [DocenteCOntroller::class, 'asignar'])->name('asignar')->middleware('auth');
Route::post('/asignar-curso/{docenteId}', [DocenteCOntroller::class, 'asignarCurso'])->name('cursos.asignar');
Route::get('/Alumnos-fid/{docente}', [DocenteCOntroller::class, 'alumnos'])->name('vistaAlumnos')->middleware('auth');
Route::get('/alumnos-ppd/{docente}', [DocenteController::class, 'alumnosppd'])->name('alumnosppd2');

Route::get('repositorio-de-silabos/{docente}', [DocenteCOntroller::class, 'repositorio'])->name('repositorio')->middleware('auth');

//Periodos
Route::post('/publicar-periodo-uno', [CalificacionController::class, 'publicarPeriodoUno'])->name('publicar.periodo.uno');

Route::post('/periodouno/storeBloque', [CalificacionController::class, 'storePeriodoEnBloque'])->name('periodouno.storeBloque');
Route::delete('/eliminar-periodo-uno', [CalificacionController::class, 'eliminarPeriodoUno'])->name('periodouno.eliminar');

Route::post('/periododos/storeBloque', [CalificacionController::class, 'storePeriodoDos'])->name('storePeriodoDos');
Route::delete('/eliminar-periodo-dos', [CalificacionController::class, 'eliminarPeriodoDos'])->name('periododos.eliminar');

Route::post('/periodotres/storeBloque', [CalificacionController::class, 'storePeriodoTres'])->name('storePeriodoTres');
Route::delete('/eliminar-periodo-tres', [CalificacionController::class, 'eliminarPeriodoTres'])->name('periodotres.eliminar');

//Quitar relaciones entre docentes/Cursos
Route::delete(
    '/docentes/cursos/eliminar-todos-global',
    [CalificacionController::class, 'eliminarTodosCursosGlobal']
)->name('docente.cursos.eliminarTodosGlobal');

//Periodos
Route::resource('Periodo-Actual', PeriodoActualController::class)->middleware('auth')->names('periodoactual')->parameters(['Periodo-Actual' => 'periodoactual']);
Route::post('Periodo-Actual/{periodoactual}/crear-calificaciones', [PeriodoActualController::class, 'crearCalificaciones'])
    ->name('periodoactual.crearCalificaciones')->middleware('auth');
Route::get('Periodo-Actual/{periodoactual}/registros', [PeriodoActualController::class, 'showRegistros'])
    ->name('periodoactual.showRegistros')->middleware('auth');
Route::resource('periodos', PeriodoController::class)->middleware('auth')->names('periodos');
Route::get('/periodos/{nombre}', [PeriodoController::class, 'show'])->name('periodos.show');


//Blog de notas docentes
/* Route::get('/docente/{id}/blog', [DocenteController::class, 'showBlog'])->name('docente.blog.show'); */
Route::get('/docente/{docente}/blog/', [DocenteController::class, 'showBlog'])->name('docente.blog.show');


Route::get('/exportar-csv/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSV'])->name('calificaciones.exportar');
Route::get('/exportar-csvppd/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSVppd'])->name('calificaciones.exportar.ppd');



//Asignar competencias a calificar
Route::get('/curso/{cursoId}/competencias', [CalificacionController::class, 'gestionarCompetencias'])
    ->name('curso.gestionar.competencias');

Route::post('/curso/{cursoId}/competencias/guardar', [CalificacionController::class, 'guardarCompetenciasSeleccionadas'])
    ->name('curso.guardar.competencias');

//Eliminar cursos asignados
Route::delete('/docente/{docente}/curso/{curso}', [DocenteController::class, 'eliminarCurso'])->name('docente.curso.eliminar');
Route::get('/Lista-de-alumnos/{curso}/{docente}', [DocenteController::class, 'showAlumnos'])->name('docentes.cursos.alumnos');
Route::post('cursos/{curso}/upload-silabo', [CursoController::class, 'uploadSilabo'])->name('cursos.uploadSilabo');
Route::get('cursos/{curso}/edit-silabo', [CursoController::class, 'editSilabo'])->name('cursos.editSilabo');
Route::post('cursos/{curso}/upload-silabo', [CursoController::class, 'uploadSilabo'])->name('cursos.uploadSilabo');
Route::delete('cursos/{curso}/destroy-silabo', [CursoController::class, 'destroySilabo'])->name('cursos.destroySilabo');
Route::post('/cursos/{curso}/classroomClaveCRUD', [CursoController::class, 'classroomClaveCRUD'])->name('cursos.classroomClaveCRUD');

//Calificaciones
Route::get('calificar/{id}', [DocenteCOntroller::class, 'calificar'])->name('calificar')->middleware('auth');
Route::post('docentes/{docente}/cursos/{curso}/calificar', [DocenteController::class, 'calificarCurso'])->name('competencias.calificar')->middleware('auth');
Route::post('docentes/{docente}/cursosPPD/{curso}/calificar', [DocenteController::class, 'calificarCursoPPD'])->name('competencias.calificar.ppd')->middleware('auth');
//Guardar Calificacion de alumnos
Route::post('/calificaciones/store', [CalificacionController::class, 'nuevaCalificacion'])->name('guardarCalificacion');
Route::post('/guardar-calificaciones', [CalificacionController::class, 'guardarCalificacionesEnBloque'])->name('guardarCalificacionesEnBloque');
Route::post('/guardar-periodo-tres', [CalificacionController::class, 'guardarPeriodoTres'])->name('guardarPeriodoTres');

//Blog de notas
Route::post('/docentes/blog/{id}', [DocenteController::class, 'updateBlog'])->name('docentes.updateBlog');

Route::post('/borrarCalificaciones', [CalificacionController::class, 'borrarCalificaciones'])->name('borrarCalificaciones');
Route::get('/calificaciones/eliminar-todas', [CalificacionController::class, 'borrarTodasLasCalificaciones'])->name('calificaciones.eliminarTodas');

//Sílabos y desgloses 
Route::resource('silabos', SilaboController::class)->names('silabos')->middleware('auth');
Route::get('/silabo/{silabo}/pdf', [SilaboController::class, 'exportarPDF'])->name('silabo.pdf');
Route::resource('competencias', CompetenciaController::class)->middleware('auth')->names('competencias');
Route::resource('capacidades', CapacidadesController::class)->middleware('auth')->names('capacidades');
Route::resource('enfoques', EnfoquesController::class)->middleware('auth')->names('enfoques');
Route::resource('proyectos', ProyectoController::class)->names('proyectos')->middleware('auth');
Route::resource('estandares', EstandaresController::class)->middleware('auth')->names('estandares');

/* Route::get('/docente', [DocenteCOntroller::class, 'showDocente'])->name('docente')->middleware('auth'); */
//Alumnos
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

Route::resource('profesionalización-docente', PpdController::class)->middleware('auth')->names('ppd');
Route::get('calificacionesppd/{alumno}', [PpdController::class, 'calificacionesppd'])->name('calificacionesppd');

/* Route::post('calificar-PPD/', [PpdController::class, 'calificar'])->name('calificarppd')->middleware('auth'); */
Route::post('/Calificar-Profesionalizacion-Docente', [PpdController::class, 'calificar'])->name('calificarppd')->middleware('auth');

Route::get('calificaciones-PPD/{alumno}', [PpdController::class, 'calificaciones'])->name('calificacionesPPD')->middleware('auth');
Route::get('form-PPD', [PpdController::class, 'form'])->name('formPPD')->middleware('auth');
Route::post('/mostrar-contenido', [AlumnoController::class, 'mostrarContenido'])->name('mostrar-contenido');
Route::get('ficha-matricula/{alumno}', [AlumnoController::class, 'ficha'])->name('ficha-matricula')->middleware('auth');
Route::put('/ciclo/update-alumnos', [CicloController::class, 'updateCicloAlumnos'])->name('ciclo.updateAlumnos');
Route::get('/filtrar-datos', [AlumnoController::class, 'filtro'])->name('filtro');

//Vista Alumnos
Route::get('Alumnos-Formulario', [vistasAlumnosController::class, 'form'])->name('vistAlumno');
Route::get('/obtener-ciclos/{programa}', [vistasAlumnosController::class, 'obtenerCiclos']);
Route::get('/get-cursos/{ciclo}', [vistasAlumnosController::class, 'getCursos']);
Route::get('/obtener-cursos/{cicloId}', [vistasAlumnosController::class, 'getCursos'])->name('obtener.cursos');
// Ficha de matrículaen PDF
Route::get('/alumnos/{alumno}/ficha-pdf', [vistasAlumnosController::class, 'exportarFichaPDF'])->name('alumno.ficha.pdf');


Route::resource('trabajo', BolsaController::class)->middleware('auth')->names('trabajo');
Route::resource('postulante', PostulanteController::class);
Route::get('lista-postulantes', [PostulanteController::class, 'lista'])->middleware('auth')->name('listaPostulantes');

//Postulantes
Route::get('formulario-de-inscrición-regular', [PostulantesRegularController::class, 'form'])->name('formInscripcionRegular');
/* Route::resource('inscripcion-regulares', PostulantesRegularController::class)->middleware('auth')->names('regulares');*/
Route::resource('inscripcion-regulares-fits', PostulantesRegularController::class)
    ->only(['index', 'edit', 'update', 'destroy', 'show'])
    ->middleware('auth')
    ->names('regulares');
Route::get('/postulantes/ingresantes', [PostulantesRegularController::class, 'crearIngresantes'])->name('postulantes.ingresantes');
Route::post('/guardar-ingresantes', [PostulantesRegularController::class, 'guardarIngresantes'])->name('postulantes.guardarIngresantes');



Route::resource('inscripcion-regulares', PostulantesRegularController::class)
    ->only(['create', 'store'])
    ->names('regulares');

Route::get('/postulante/{id}/toggle-observacion', [PostulantesRegularController::class, 'toggleObservacion'])
    ->name('postulante.toggleObservacion');
Route::get('/postulantes/exportar', [PostulantesRegularController::class, 'exportarCSV'])
    ->name('postulantes.exportar');



