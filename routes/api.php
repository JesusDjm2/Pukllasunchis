<?php

use App\Http\Controllers\AdminFidController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BolsaTrabajoOfertaController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\ComunicadoController;
use App\Http\Controllers\Api\CursoEspecialController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\IncidenciaController;
use App\Http\Controllers\Api\MatriculaController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PeriodoActualController;
use App\Http\Controllers\Api\TaqeController;
use App\Http\Controllers\Api\TutoriaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Route::get('/consulta-dni', [AdminFidController::class, 'consultar']); */

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Servir fotos con encabezados CORS para apps móviles y Flutter Web
    Route::get('/foto/estudiante/{archivo}', function ($archivo) {
        $path = public_path('img/estudiantes/' . basename($archivo));
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->where('archivo', '.*');

    Route::get('/foto/docente/{archivo}', function ($archivo) {
        $path = public_path('docentes/fotos/' . basename($archivo));
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->where('archivo', '.*');

    // Pública, igual que informacion/bolsa-de-trabajo en la web.
    Route::get('/bolsa-trabajo', [BolsaTrabajoOfertaController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/alumno/perfil', [AlumnoController::class, 'perfil']);
        Route::put('/alumno/perfil', [AlumnoController::class, 'actualizarPerfil']);
        Route::get('/alumno/ficha-pdf', [AlumnoController::class, 'fichaPdf']);

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

        Route::get('/comunicados', [ComunicadoController::class, 'index']);

        // Tutoría individual y buzón de sugerencias
        Route::get('/tutoria', [TutoriaController::class, 'index']);
        Route::post('/tutoria', [TutoriaController::class, 'storeTutoria']);
        Route::post('/sugerencia', [TutoriaController::class, 'storeSugerencia']);

        // Taq'e: proyectos integradores, grupos y entregables
        Route::prefix('taqe')->group(function () {
            Route::get('/', [TaqeController::class, 'index']);
            Route::post('/grupos', [TaqeController::class, 'crearGrupo']);
            Route::get('/cursos/{curso}/alumnos', [TaqeController::class, 'alumnosDelCurso']);
            Route::post('/grupos/{grupo}/trabajos', [TaqeController::class, 'subirTrabajo']);
            Route::post('/grupos/{grupo}/entregar', [TaqeController::class, 'entregar']);
            Route::post('/grupos/{grupo}/reabrir', [TaqeController::class, 'reabrir']);
            Route::post('/grupos/{grupo}/integrantes', [TaqeController::class, 'agregarIntegrantes']);
            Route::delete('/grupos/{grupo}/integrantes/{integrante}', [TaqeController::class, 'retirarIntegrante']);
            Route::post('/grupos/{grupo}/salir', [TaqeController::class, 'salir']);
            Route::post('/trabajos/{trabajo}/mover', [TaqeController::class, 'moverTrabajo']);
            Route::delete('/trabajos/{trabajo}', [TaqeController::class, 'eliminarTrabajo']);
            Route::post('/responder', [TaqeController::class, 'responder']);
            Route::post('/solicitar-ayuda', [TaqeController::class, 'solicitarAyuda']);
        });

        Route::get('/incidencias', [IncidenciaController::class, 'index']);

        // Área docente: espejo del panel web según roles (docente, tutor, investigador, titulación, etc.)
        Route::middleware('role:docente|tutor|investigador|admin|super-admin|gestor-titulacion|secretaria')->prefix('docente')->group(function () {
            Route::get('/resumen', [DocenteController::class, 'resumen']);
            Route::get('/cursos', [DocenteController::class, 'cursos']);
            Route::get('/alumnos', [DocenteController::class, 'alumnos']);
            Route::get('/tutor', [DocenteController::class, 'tutorDashboard']);
        });

        Route::get('/notificaciones', [NotificacionController::class, 'index']);
        Route::get('/notificaciones/no-leidas-count', [NotificacionController::class, 'noLeidasCount']);
        Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida']);
        Route::post('/notificaciones/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas']);

        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
    });
});
