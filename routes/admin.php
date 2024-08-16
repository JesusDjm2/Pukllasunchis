<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\BolsaController;
use App\Http\Controllers\CicloController;
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
Route::get('/docente', [DocenteCOntroller::class, 'showDocente'])->name('docente')->middleware('auth');
//Alumnos
Route::prefix('alumnos')->group(function () {
    Route::resource('alumnos', AlumnoController::class)->except(['store']);
    Route::post('alumnos', [AlumnoController::class, 'store'])
        ->name('alumnos.store')
        ->middleware('web');
    Route::get('alumnos/create', [AlumnoController::class, 'create'])
        ->name('alumnos.create')
        ->withoutMiddleware('auth');
});
Route::post('/mostrar-contenido', [AlumnoController::class, 'mostrarContenido'])->name('mostrar-contenido');
Route::get('ficha-matricula/{alumno}', [AlumnoController::class, 'ficha'])->name('ficha-matricula')->middleware('auth');
Route::put('/ciclo/update-alumnos', [CicloController::class, 'updateCicloAlumnos'])->name('ciclo.updateAlumnos');

Route::get('/filtrar-datos', [AlumnoController::class, 'filtro'])->name('filtro');
//Vista Alumnos
Route::get('Alumnos-Formulario', [vistasAlumnosController::class, 'form'])->name('vistAlumno');
Route::get('/obtener-ciclos/{programa}', [vistasAlumnosController::class, 'obtenerCiclos']);


Route::resource('trabajo', BolsaController::class);
Route::resource('postulante', PostulanteController::class);
Route::get('lista-postulantes', [PostulanteController::class, 'lista'])->middleware('auth')->name('listaPostulantes');




