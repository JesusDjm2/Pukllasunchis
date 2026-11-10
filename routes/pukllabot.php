<?php

use App\Http\Controllers\PukllaBotChatController;
use Illuminate\Support\Facades\Route;

/** Solo middleware web (sesión, CSRF). No duplicar en el grupo api/* de RouteServiceProvider. */
Route::post('/pukllabot/chat', [PukllaBotChatController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('pukllabot.chat');

/**
 * Super-admin: vuelve a indexar el conocimiento del asistente (storage/app/pukllabot: .md y faqs.json) sin
 * consola, por ejemplo después de cambiar un enlace en los textos. Equivale a `php artisan pukllabot:ingest --force`.
 */
Route::post('/admin/pukllabot/reindexar', function () {
    try {
        $codigo = \Illuminate\Support\Facades\Artisan::call('pukllabot:ingest', ['--force' => true]);
        $total = \App\Models\KnowledgeChunk::count();

        return back()->with($codigo === 0 ? 'success' : 'error', $codigo === 0
            ? "PukllaBot reindexado: {$total} fragmentos de conocimiento actualizados."
            : 'No se pudo reindexar PukllaBot: '.trim(\Illuminate\Support\Facades\Artisan::output()));
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Reindexar PukllaBot: '.$e->getMessage());

        return back()->with('error', 'No se pudo reindexar PukllaBot: '.$e->getMessage());
    }
})->middleware(['auth', 'role:super-admin'])->name('pukllabot.reindexar');
