<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ConsultaDniService
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        $this->apiUrl = config('services.apiperu.url') . '/dni';
        $this->token  = config('services.apiperu.token');
    }

    /**
     * Consulta un DNI y devuelve datos o null si no se encontró.
     *
     * @param string $dni 8 dígitos
     * @return array|null
     */
    public function consultar(string $dni): ?array
    {
        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post($this->apiUrl, [
            'dni' => $dni,
        ]);

        if ($response->successful()) {
            $body = $response->json();
            if (!empty($body['success']) && !empty($body['data'])) {
                return $body['data'];
            }
        }

        // si hubo algún problema o no hay data → null
        return null;
    }
}
