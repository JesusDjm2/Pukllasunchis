<?php

use App\Http\Controllers\AdminController;
/* use App\Http\Controllers\Auth\RegisterController; */
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\EnlacesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('index');

//Pandero
Route::get('pandero', [EnlacesController::class, 'pandero'])->name('pandero');

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
Route::get('informacion/convocatorias', [EnlacesController::class, 'conv'])->name('conv');

//Foot
Route::get('informacion-institucional', [EnlacesController::class, 'informacion'])->name('informacion');
Route::get('politica-de-privacidad', [EnlacesController::class, 'politica'])->name('politica');
Route::get('terminos-y-condiciones', [EnlacesController::class, 'terminos'])->name('terminos');

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');    
    return 'DONE';
});






