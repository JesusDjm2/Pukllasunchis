<?php

use App\Http\Controllers\PukllaBotChatController;
use Illuminate\Support\Facades\Route;

/** Solo middleware web (sesión, CSRF). No duplicar en el grupo api/* de RouteServiceProvider. */
Route::post('/pukllabot/chat', [PukllaBotChatController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('pukllabot.chat');
