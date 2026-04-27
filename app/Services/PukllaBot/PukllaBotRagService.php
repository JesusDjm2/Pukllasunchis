<?php

namespace App\Services\PukllaBot;

use App\Models\KnowledgeChunk;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

/**
 * RAG léxico (corpus en BD) + chat vía OpenRouter (API OpenAI-compatible, Guzzle).
 */
class PukllaBotRagService
{
    public function hasIndex(): bool
    {
        return KnowledgeChunk::query()->exists();
    }

    public function hasIndexForScope(string $scope): bool
    {
        return KnowledgeChunk::query()->where('scope', $scope)->exists();
    }

    public function isLlmConfigured(): bool
    {
        $k = config('pukllabot.openrouter.api_key');

        return is_string($k) && $k !== '';
    }

    /**
     * @return array{reply: string, sources: array<int, string>, scope: string}
     */
    public function answer(string $userMessage, string $scope = 'general'): array
    {
        if (! $this->isLlmConfigured()) {
            throw new RuntimeException('Configura OPENROUTER_API_KEY en .env');
        }

        $query = trim($userMessage);
        if ($query === '') {
            throw new RuntimeException('Mensaje vacío.');
        }

        if (! in_array($scope, array_keys(config('pukllabot.chat_scopes', [])), true)) {
            throw new RuntimeException('Ámbito no válido.');
        }

        if (! $this->hasIndexForScope($scope)) {
            throw new RuntimeException('No hay textos indexados para este ámbito. En el servidor: php artisan pukllabot:ingest --force');
        }

        $faqHit = $this->matchFaqAnswer($query, $scope);
        if ($faqHit !== null) {
            return [
                'reply' => $faqHit,
                'sources' => ['Preguntas frecuentes (respuesta fija)'],
                'scope' => $scope,
            ];
        }

        $k = (int) config('pukllabot.top_k', 5);
        $maxCand = (int) config('pukllabot.retrieval_max_candidates', 180);
        $chunks = $this->loadChunksForRetrieval($scope, $query, $k, $maxCand);
        $top = $this->topLexical($chunks, $query, $k);

        $context = $this->formatContext($top);
        $sources = $top->map(fn (KnowledgeChunk $c) => $c->title ?: mb_substr($c->content, 0, 60).'…')->values()->all();

        $meta = config("pukllabot.chat_scopes.{$scope}");
        $label = is_array($meta) ? (string) ($meta['label'] ?? $scope) : $scope;
        $blurb = is_array($meta) ? trim((string) ($meta['description'] ?? '')) : '';

        $system = <<<TXT
Eres "PukllaBot", asistente de la Escuela de Educación Superior Pedagógica Pukllasunchis (EESPP) en Cusco, Perú.
Responde en español, con tono claro y respetuoso, como un asesor informativo público (no eres trámite legal ni reemplazas a secretaría).
Usa SOLO la información del contexto provisto. Si el contexto no basta, dilo con honestidad y sugiere contactar por los medios oficiales (web o secretaría) sin inventar cifras ni requisitos. No inventes enlaces, plazos ni requisitos.
Ámbito de consulta elegido por el visitante: «{$label}».
{$blurb}
Responde solo con datos coherentes con este ámbito; no mezcles requisitos de programas, admisión PPD u otros trámites ajenos al contexto, salvo que el propio contexto indique un vínculo (p. ej. lista de programas al postular).
Sé **breve y directo** (párrafos y listas cortas) salvo que el visitante pida explícitamente un detalle amplio. Es un chat: prioriza utilidad y lectura rápida.

Formato de salida: usa **Markdown** (el chat lo mostrará con negritas y listas). Estructura con párrafos cortos, listas con guion (-) para pasos o requisitos, y **negrita** solo para conceptos clave. No uses HTML. No uses tablas salvo que sea imprescindible. Evita títulos # enormes: usa **negrita** o ## como máximo.
TXT;

        $user = "Contexto (fragmentos oficiales):\n\n{$context}\n\n---\nPregunta del visitante: {$query}";

        try {
            $text = $this->chatOpenRouter($system, $user);
        } catch (Throwable $e) {
            throw new RuntimeException('Error al contactar OpenRouter: '.$e->getMessage(), 0, $e);
        }

        if ($text === null || $text === '') {
            throw new RuntimeException('Respuesta vacía del modelo.');
        }

        return [
            'reply' => $text,
            'sources' => $sources,
            'scope' => $scope,
        ];
    }

    private function chatOpenRouter(string $system, string $user): string
    {
        $or = config('pukllabot.openrouter', []);
        $key = (string) ($or['api_key'] ?? '');
        $base = rtrim((string) ($or['api_base'] ?? 'https://openrouter.ai/api/v1'), '/');
        $model = (string) ($or['model'] ?? 'openrouter/free');
        $extra = [
            'HTTP-Referer' => (string) ($or['http_referer'] ?? ''),
            'X-Title' => (string) ($or['app_name'] ?? 'PukllaBot'),
        ];

        return $this->postOpenAiStyleChatCompletions(
            rtrim($base, '/'),
            $key,
            $model,
            $system,
            $user,
            $extra
        );
    }

    /**
     * @param  array<string, string>  $extraHeaders
     */
    private function postOpenAiStyleChatCompletions(
        string $apiBase,
        string $apiKey,
        string $model,
        string $system,
        string $user,
        array $extraHeaders
    ): string {
        $url = $apiBase.'/chat/completions';
        if (! str_starts_with($url, 'http')) {
            $url = 'https://'.$url;
        }

        $headers = array_merge(
            [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ],
            array_filter($extraHeaders, fn ($v) => is_string($v) && $v !== '')
        );

        $or = config('pukllabot.openrouter', []);
        $timeout = (float) ($or['timeout'] ?? 75);
        $connect = (float) ($or['connect_timeout'] ?? 12);
        $client = new Client([
            'timeout' => $timeout,
            'connect_timeout' => $connect,
            'http_errors' => false,
        ]);

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => (int) config('pukllabot.llm_max_tokens', 600),
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();
        $data = json_decode($raw, true);

        if ($status < 200 || $status >= 300) {
            $msg = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : $raw;
            throw new RuntimeException("OpenRouter HTTP {$status}: {$msg}");
        }

        if (! is_array($data)) {
            throw new RuntimeException('Respuesta JSON inválida (OpenRouter).');
        }

        $text = $data['choices'][0]['message']['content'] ?? null;

        return is_string($text) ? $text : '';
    }

    /**
     * FAQ: primero texto exacto / parecido; luego solapamiento léxico (typos, “precio” sin la misma redacción);
     * opcionalmente busca en otros temas si el visitante no cambió el desplegable.
     */
    private function matchFaqAnswer(string $userMessage, string $scope): ?string
    {
        $faqs = PukllaBotFaqFile::all();
        $nUser = $this->normalizeFaqString($userMessage);
        if ($nUser === '') {
            return null;
        }

        $list = $faqs[$scope] ?? [];
        $hit = $this->matchFaqStrictList($list, $nUser);
        if ($hit !== null) {
            return $hit;
        }

        $minIn = (float) config('pukllabot.faq_lexical_min_score_in_scope', 2.75);
        $best = $this->bestFaqLexicalMatch($list, $nUser);
        if ($best !== null && $best['score'] >= $minIn) {
            return $best['answer'];
        }

        if (! filter_var(config('pukllabot.faq_cross_scope', true), FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $minCross = (float) config('pukllabot.faq_lexical_min_score_cross_scope', 4);
        $bestAll = $this->bestFaqLexicalMatchAllScopes($faqs, $nUser);
        if ($bestAll !== null && $bestAll['score'] >= $minCross) {
            return $bestAll['answer'];
        }

        return null;
    }

    /**
     * @param  array<int, array{q?: string, a?: string}>  $items
     */
    private function matchFaqStrictList(array $items, string $nUser): ?string
    {
        foreach ($items as $item) {
            $q = (string) ($item['q'] ?? '');
            $a = (string) ($item['a'] ?? '');
            if ($q === '' || $a === '') {
                continue;
            }
            $nQ = $this->normalizeFaqString($q);
            if ($nUser === $nQ) {
                return $a;
            }
            if (mb_strlen($nQ) >= 12 && str_contains($nUser, $nQ)) {
                return $a;
            }
            if (mb_strlen($nUser) >= 10 && str_contains($nQ, $nUser)) {
                return $a;
            }
            $pct = 0.0;
            similar_text($nUser, $nQ, $pct);
            if ($pct >= 82.0) {
                return $a;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{q?: string, a?: string}>  $items
     * @return array{answer: string, score: float}|null
     */
    private function bestFaqLexicalMatch(array $items, string $nUser): ?array
    {
        $bestScore = -1.0;
        $bestAnswer = null;
        foreach ($items as $item) {
            $q = (string) ($item['q'] ?? '');
            $a = (string) ($item['a'] ?? '');
            if ($q === '' || $a === '') {
                continue;
            }
            $s = $this->faqLexicalScore($nUser, $q, $a);
            if ($s > $bestScore) {
                $bestScore = $s;
                $bestAnswer = $a;
            }
        }
        if ($bestAnswer === null) {
            return null;
        }

        return ['answer' => $bestAnswer, 'score' => $bestScore];
    }

    /**
     * @param  array<string, array<int, array{q?: string, a?: string}>>  $faqsByScope
     * @return array{answer: string, score: float}|null
     */
    private function bestFaqLexicalMatchAllScopes(array $faqsByScope, string $nUser): ?array
    {
        $bestScore = -1.0;
        $bestAnswer = null;
        foreach ($faqsByScope as $scopeKey => $items) {
            if (str_starts_with((string) $scopeKey, '_')) {
                continue;
            }
            if (! is_array($items)) {
                continue;
            }
            $m = $this->bestFaqLexicalMatch($items, $nUser);
            if ($m !== null && $m['score'] > $bestScore) {
                $bestScore = $m['score'];
                $bestAnswer = $m['answer'];
            }
        }
        if ($bestAnswer === null) {
            return null;
        }

        return ['answer' => $bestAnswer, 'score' => $bestScore];
    }

    private function faqLexicalScore(string $nUser, string $q, string $a): float
    {
        $hay = $this->normalizeFaqString($q.' '.mb_substr($a, 0, 260));
        $cleanUser = preg_replace('/[¿?«»]/u', '', $nUser) ?? $nUser;
        $terms = $this->tokenizeQuery($cleanUser);
        $score = 0.0;
        foreach ($terms as $t) {
            $len = mb_strlen($t);
            if ($len < 3) {
                continue;
            }
            if (str_contains($hay, $t)) {
                $score += 1.15;

                continue;
            }
            if ($len >= 10) {
                $pfx = mb_substr($t, 0, 12);
                if ($pfx !== '' && str_contains($hay, $pfx)) {
                    $score += 1.1;
                }
            }
        }

        $userHasPrice = (bool) preg_match('/precio|costo|cuesta|cuánto|matrícula|mensualidad|tarifa/u', $nUser);
        $userHasPpd = (bool) preg_match('/profesionaliz|ppd/u', $nUser);
        if ($userHasPrice && preg_match('/S\/|soles|matrícula|mensualidad|ciclo|\d{2,4}/u', $a)) {
            $score += 2.25;
        }
        if ($userHasPpd && preg_match('/ppd|profesionaliz|dos ciclos|ciclos/u', $hay)) {
            $score += 1.75;
        }
        if ($userHasPrice && $userHasPpd && preg_match('/ppd|profesionaliz/u', $hay)) {
            $score += 2.5;
        }

        return $score;
    }

    private function normalizeFaqString(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', $s) ?? '';
        $s = trim($s);

        return mb_strtolower($s);
    }

    /**
     * Carga candidatos al RAG: evita traer todo el scope cuando hay términos (menos memoria/CPU).
     *
     * @return Collection<int, KnowledgeChunk>
     */
    private function loadChunksForRetrieval(string $scope, string $query, int $k, int $maxCand): Collection
    {
        $terms = $this->tokenizeQuery($query);
        $base = KnowledgeChunk::query()->where('scope', $scope);

        if ($terms === []) {
            return (clone $base)->orderBy('id')->limit($k)->get();
        }

        $termsUse = array_slice($terms, 0, 8);
        $candidates = (clone $base)->where(function ($q) use ($termsUse) {
            foreach ($termsUse as $t) {
                $e = $this->escapeLikeTerm($t);
                $q->orWhere('content', 'like', '%'.$e.'%');
                $q->orWhere('title', 'like', '%'.$e.'%');
            }
        })->get();

        if ($candidates->isEmpty()) {
            return $base->get();
        }

        if ($candidates->count() > $maxCand) {
            return $this->roughPruneCandidates($candidates, $terms, $maxCand);
        }

        return $candidates;
    }

    /**
     * @param  Collection<int, KnowledgeChunk>  $chunks
     * @return Collection<int, KnowledgeChunk>
     */
    private function roughPruneCandidates(Collection $chunks, array $terms, int $max): Collection
    {
        if ($chunks->count() <= $max) {
            return $chunks;
        }
        $needle = $terms[0] ?? '';
        if ($needle === '') {
            return $chunks->take($max);
        }
        $scored = $chunks->map(function (KnowledgeChunk $c) use ($needle) {
            $text = mb_strtolower(($c->title ? $c->title.' ' : '').$c->content);
            $score = substr_count($text, $needle);

            return ['chunk' => $c, 'score' => $score];
        });

        return $scored->sortByDesc('score')->take($max)->pluck('chunk')->values();
    }

    private function escapeLikeTerm(string $t): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $t);
    }

    /**
     * @param  Collection<int, KnowledgeChunk>  $chunks
     * @return Collection<int, KnowledgeChunk>
     */
    private function topLexical(Collection $chunks, string $query, int $k): Collection
    {
        $terms = $this->tokenizeQuery($query);
        if ($terms === []) {
            return $chunks->take($k);
        }
        $scored = $chunks->map(function (KnowledgeChunk $c) use ($terms) {
            $text = mb_strtolower(($c->title ? $c->title.' ' : '').$c->content);
            $score = 0.0;
            foreach ($terms as $t) {
                if (str_contains($text, $t)) {
                    $score += 1.0;
                }
            }

            return ['chunk' => $c, 'score' => $score];
        });

        return $scored->sortByDesc('score')->take($k)->pluck('chunk')->values();
    }

    /**
     * @return array<int, string>
     */
    private function tokenizeQuery(string $q): array
    {
        $q = mb_strtolower($q);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $q, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($parts)) {
            return [];
        }
        $stop = [
            'de', 'la', 'el', 'y', 'a', 'en', 'un', 'que', 'es', 'se', 'no', 'te', 'lo', 'le', 'da', 'su', 'por',
            'con', 'para', 'al', 'del', 'los', 'las', 'una', 'o', 'más', 'muy', 'hay', 'fue', 'son', 'les',
            'me', 'mi', 'sus', 'como', 'puede', 'ser', 'este', 'esta', 'también', 'ya', 'si',
        ];
        $out = [];
        foreach ($parts as $p) {
            if (mb_strlen($p) < 2) {
                continue;
            }
            if (in_array($p, $stop, true)) {
                continue;
            }
            $out[] = $p;
        }

        return array_values(array_unique($out, SORT_REGULAR));
    }

    /**
     * @param  Collection<int, KnowledgeChunk>  $top
     */
    private function formatContext(Collection $top): string
    {
        $parts = [];
        foreach ($top as $i => $c) {
            $label = $c->title ?: 'Fragmento '.($i + 1);
            $parts[] = "[{$label}]\n".trim($c->content);
        }

        return implode("\n\n", $parts);
    }
}
