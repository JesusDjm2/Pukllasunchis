<?php

use App\Http\Controllers\AdminFidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Route::get('/consulta-dni', [AdminFidController::class, 'consultar']); */
/* Route::get('/consulta-dni', [AdminFidController::class, 'consultar']); */
