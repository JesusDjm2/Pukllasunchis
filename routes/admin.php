<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\BolsaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DocenteCOntroller;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\vistasAlumnosController;

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

//Docentes
Route::resource('docente', DocenteCOntroller::class)->names('docente')->middleware('auth');
Route::get('Dashboard-Docente/{docente}', [DocenteCOntroller::class, 'vistaDocente'])->name('vistaDocente')->middleware('auth');
Route::get('asignar-cursos/{id}', [DocenteCOntroller::class, 'asignar'])->name('asignar')->middleware('auth');
Route::post('/asignar-curso/{docenteId}', [DocenteCOntroller::class, 'asignarCurso'])->name('cursos.asignar');
Route::get('/ver-mis-alumnos/{docente}', [DocenteCOntroller::class, 'alumnos'])->name('vistaAlumnos')->middleware('auth');

//Periodos
Route::post('/publicar-periodo-uno', [CalificacionController::class, 'publicarPeriodoUno'])->name('publicar.periodo.uno');
Route::post('/eliminarPeriodoUno', [CalificacionController::class, 'eliminarPeriodoUno'])->name('eliminarPeriodoUno');
//Exportar CSV
/* Route::post('/calificaciones/exportar/{docente}', [CalificacionController::class, 'exportarCSV'])->name('calificaciones.exportar'); */
Route::get('/exportar-csv/{docenteId}/{cursoId}', [CalificacionController::class, 'exportarCSV'])->name('calificaciones.exportar');



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
//Guardar Calificacion de alumnos
Route::post('/calificaciones/store', [CalificacionController::class, 'nuevaCalificacion'])->name('guardarCalificacion');
Route::post('/guardar-calificaciones', [CalificacionController::class, 'guardarCalificacionesEnBloque'])->name('guardarCalificacionesEnBloque');

//Blog de notas
Route::post('/docentes/blog/{id}', [DocenteController::class, 'updateBlog'])->name('docentes.updateBlog');

Route::post('/borrarCalificaciones', [CalificacionController::class, 'borrarCalificaciones'])->name('borrarCalificaciones');

//Competencias 
Route::resource('competencias', CompetenciaController::class)->middleware('auth')->names('competencias');

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
Route::post('/mostrar-contenido', [AlumnoController::class, 'mostrarContenido'])->name('mostrar-contenido');
Route::get('ficha-matricula/{alumno}', [AlumnoController::class, 'ficha'])->name('ficha-matricula')->middleware('auth');
Route::put('/ciclo/update-alumnos', [CicloController::class, 'updateCicloAlumnos'])->name('ciclo.updateAlumnos');

Route::get('/filtrar-datos', [AlumnoController::class, 'filtro'])->name('filtro');

//Vista Alumnos
Route::get('Alumnos-Formulario', [vistasAlumnosController::class, 'form'])->name('vistAlumno');
Route::get('/obtener-ciclos/{programa}', [vistasAlumnosController::class, 'obtenerCiclos']);
Route::get('/obtener-cursos/{cicloId}', [vistasAlumnosController::class, 'getCursos'])->name('obtener.cursos');

Route::resource('trabajo', BolsaController::class);
Route::resource('postulante', PostulanteController::class);
Route::get('lista-postulantes', [PostulanteController::class, 'lista'])->middleware('auth')->name('listaPostulantes');





