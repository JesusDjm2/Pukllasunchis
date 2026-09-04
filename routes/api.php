<?php

use App\Http\Controllers\AdminFidController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\CursoEspecialController;
use App\Http\Controllers\Api\MatriculaController;
use App\Http\Controllers\Api\PeriodoActualController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Route::get('/consulta-dni', [AdminFidController::class, 'consultar']); */
/* Route::get('/consulta-dni', [AdminFidController::class, 'consultar']); */

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/alumno/perfil', [AlumnoController::class, 'perfil']);
        Route::put('/alumno/perfil', [AlumnoController::class, 'actualizarPerfil']);

        Route::get('/periodo-actual', [PeriodoActualController::class, 'show']);

        Route::get('/matriculas', [MatriculaController::class, 'index']);

        Route::get('/cursos', [CursoController::class, 'index']);
        Route::get('/calificaciones', [CursoController::class, 'calificaciones']);

        Route::get('/cursos-especiales', [CursoEspecialController::class, 'index']);
        Route::post('/cursos-especiales/{curso}/inscribir', [CursoEspecialController::class, 'inscribir']);
        Route::get('/cursos-especiales/{curso}', [CursoEspecialController::class, 'show']);
        Route::get('/cursos-especiales/{curso}/progreso', [CursoEspecialController::class, 'progreso']);
        Route::post('/lecciones/{leccion}/completar', [CursoEspecialController::class, 'completarLeccion']);
        Route::post('/ejercicios/{ejercicio}/responder', [CursoEspecialController::class, 'responderEjercicio']);
    });
});
