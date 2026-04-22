<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Envía un mensaje vía CallMeBot.
     * El tutor debe haber activado su clave en https://www.callmebot.com/blog/free-api-whatsapp-messages/
     *
     * @param string $phone     Número con código de país, sin +. Ej: 51984529158
     * @param string $apiKey    Clave personal de CallMeBot del receptor
     * @param string $message   Texto plano del mensaje
     */
    public static function send(string $phone, string $apiKey, string $message): bool
    {
        try {
            $response = Http::timeout(10)->get('https://api.callmebot.com/whatsapp.php', [
                'phone'  => $phone,
                'text'   => $message,
                'apikey' => $apiKey,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp CallMeBot error: '.$e->getMessage());
            return false;
        }
    }
}
