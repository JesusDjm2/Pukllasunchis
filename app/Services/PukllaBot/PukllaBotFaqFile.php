<?php

namespace App\Services\PukllaBot;

use Illuminate\Support\Facades\File;

/**
 * Carga Preguntas frecuentes desde storage/app/pukllabot/faqs.json
 * (objeto: cada clave = scope, valor = array de { "q", "a" }).
 */
class PukllaBotFaqFile
{
    public static function path(): string
    {
        return (string) config('pukllabot.faqs_path', storage_path('app/pukllabot/faqs.json'));
    }

    /**
     * @return array<string, array<int, array{q: string, a: string}>>
     */
    public static function all(): array
    {
        $path = self::path();
        if (! File::isReadable($path)) {
            return [];
        }
        $raw = File::get($path);
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true, 512, JSON_INVALID_UTF8_IGNORE);
        if (! is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $scope => $items) {
            if (str_starts_with((string) $scope, '_')) {
                continue;
            }
            if (! is_array($items)) {
                continue;
            }
            $list = [];
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $q = trim((string) ($row['q'] ?? $row['pregunta'] ?? ''));
                $a = trim((string) ($row['a'] ?? $row['respuesta'] ?? ''));
                if ($q === '') {
                    continue;
                }
                if ($a === '') {
                    continue;
                }
                $list[] = ['q' => $q, 'a' => $a];
            }
            if ($list !== []) {
                $out[$scope] = $list;
            }
        }

        return $out;
    }

    /**
     * Misma estructura que en faqs.json; "a" puede quedar vacío (solo rellena la pregunta al pulsar; no se indexa sin "a" en all()).
     *
     * @return array<string, array<int, array{q: string, a: string}>>
     */
    public static function allForWidget(): array
    {
        $path = self::path();
        if (! File::isReadable($path)) {
            return [];
        }
        $data = json_decode((string) File::get($path), true, 512, JSON_INVALID_UTF8_IGNORE);
        if (! is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $scope => $items) {
            if (str_starts_with((string) $scope, '_')) {
                continue;
            }
            if (! is_array($items)) {
                continue;
            }
            $list = [];
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $q = trim((string) ($row['q'] ?? $row['pregunta'] ?? ''));
                if ($q === '') {
                    continue;
                }
                $a = trim((string) ($row['a'] ?? $row['respuesta'] ?? ''));
                $list[] = ['q' => $q, 'a' => $a];
            }
            if ($list !== []) {
                $out[$scope] = $list;
            }
        }

        return $out;
    }
}
