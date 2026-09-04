<?php

use App\Http\Controllers\AdminFidController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
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
    });
});
