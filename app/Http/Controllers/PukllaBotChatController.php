<?php

namespace App\Http\Controllers;

use App\Services\PukllaBot\PukllaBotRagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class PukllaBotChatController extends Controller
{
    public function __construct(
        private readonly PukllaBotRagService $rag
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $scopeKeys = array_keys(config('pukllabot.chat_scopes', []));
        if ($scopeKeys === []) {
            $scopeKeys = ['general'];
        }
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'scope' => ['nullable', 'string', Rule::in($scopeKeys)],
        ]);
        $message = $validated['message'];
        $scope = $validated['scope'] ?? 'general';

        try {
            if (! $this->rag->isLlmConfigured()) {
                return response()->json([
                    'error' => 'Falta OPENROUTER_API_KEY en .env (clave en openrouter.ai/keys).',
                ], 503);
            }
            if (! $this->rag->hasIndexForScope($scope)) {
                return response()->json([
                    'error' => 'No hay textos indexados para el tema elegido. En el servidor: php artisan pukllabot:ingest --force',
                ], 503);
            }

            $out = $this->rag->answer($message, $scope);

            return response()->json($out);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'No se pudo generar la respuesta. Revisa la conexión y el saldo de la API.',
            ], 500);
        }
    }
}
