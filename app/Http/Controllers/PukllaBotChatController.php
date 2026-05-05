<?php

namespace App\Http\Controllers;

use App\Services\PukllaBot\PukllaBotRagService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class PukllaBotChatController extends Controller
{
    /** WhatsApp área informática (solo dígitos país+número). */
    private const WHATSAPP_INFORMATICA_URL = 'https://wa.me/51984529158';

    private const TELEFONO_INFORMATICA = '+51 984 529 158';

    /** Mensaje visible en el chat; nunca exponer SQL ni trazas al visitante. */
    private const MSG_SERVICE_UNAVAILABLE = 'En este momento no puedo completar tu consulta. Intenta de nuevo en unos minutos.';

    private const MSG_DB_TEMPORARY = 'Hay un inconveniente temporal al consultar la información. Intenta de nuevo más tarde.';

    private const MSG_LLM_NOT_CONFIGURED = 'El asistente no está disponible por ahora. Vuelve a intentar más tarde.';

    private const MSG_NO_CONTENT_FOR_SCOPE = 'No encontré una respuesta con la información disponible; lo más probable es que el contenido aún no esté cargado en el asistente. También puedes probar otro tema en el menú.';

    private const MSG_LLM_UPSTREAM = 'No pude obtener una respuesta del servicio en este momento. Intenta de nuevo en unos minutos.';

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
                    'error' => $this->messageWithInformaticaContact(self::MSG_LLM_NOT_CONFIGURED),
                ], 503);
            }
            if (! $this->rag->hasIndexForScope($scope)) {
                return response()->json([
                    'error' => $this->messageWithInformaticaContact(self::MSG_NO_CONTENT_FOR_SCOPE),
                ], 503);
            }

            $out = $this->rag->answer($message, $scope);

            return response()->json($out);
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'error' => $this->messageWithInformaticaContact(self::MSG_DB_TEMPORARY),
            ], 503);
        } catch (RuntimeException $e) {
            report($e);

            return response()->json([
                'error' => $this->messageWithInformaticaContact($this->friendlyRuntimeMessage($e->getMessage())),
            ], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => $this->messageWithInformaticaContact(self::MSG_SERVICE_UNAVAILABLE),
            ], 500);
        }
    }

    private function friendlyRuntimeMessage(string $internalMessage): string
    {
        $m = mb_strtolower($internalMessage);

        if (str_contains($m, 'openrouter') || str_contains($m, 'http ') || preg_match('/\bhttp\s*\d{3}\b/', $internalMessage)) {
            return self::MSG_LLM_UPSTREAM;
        }

        if (str_contains($m, 'indexados') || str_contains($m, 'ingest') || str_contains($m, 'ámbito')) {
            return self::MSG_NO_CONTENT_FOR_SCOPE;
        }

        if (str_contains($m, 'vacía') || str_contains($m, 'vacío') || str_contains($m, 'respuesta vacía')) {
            return 'No obtuve una respuesta clara en este momento. Reformula tu pregunta o intenta de nuevo más tarde.';
        }

        return self::MSG_SERVICE_UNAVAILABLE;
    }

    /** Pie común: área de Informática + teléfono + WhatsApp (enlace clicable al copiar/pegar o en clientes que detecten URL). */
    private function messageWithInformaticaContact(string $mainText): string
    {
        $pie = "\n\n".
            'Para más ayuda, comunícate con el área de Informática de la Escuela (sobre todo si la información aún no está cargada en este asistente).'."\n".
            'Tel.: '.self::TELEFONO_INFORMATICA."\n".
            'WhatsApp: '.self::WHATSAPP_INFORMATICA_URL;

        return trim($mainText).$pie;
    }
}
