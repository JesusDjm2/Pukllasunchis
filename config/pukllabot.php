<?php

return [
    'retrieval' => 'lexical',

    /** Fragmentos de corpus que se inyectan al LLM (menos = contexto más corto y algo más rápido) */
    'top_k' => (int) env('PUKLLABOT_TOP_K', 5),

    /**Límite duro al cargar candidatos desde BD (léxico). Baja el costo de memoria y CPU en corpus grandes.
     */
    'retrieval_max_candidates' => (int) env('PUKLLABOT_RETRIEVAL_MAX_CANDIDATES', 180),

    /** Tamaño máximo de la respuesta del modelo (más bajo = suele acabar antes) */
    'llm_max_tokens' => (int) env('PUKLLABOT_LLM_MAX_TOKENS', 600),

    /** Directorio de los .md ingeridos */
    'corpus_dir' => storage_path('app/pukllabot'),

    /** Ruta legada: `corpus.md` en corpus_dir (igual que el primer ítem de corpora) */
    'corpus_path' => storage_path('app/pukllabot/corpus.md'),

    /** Preguntas frecuentes (JSON). Editar y luego: php artisan pukllabot:ingest --force */
    'faqs_path' => storage_path('app/pukllabot/faqs.json'),

    /** Umbral de coincidencia léxica FAQ en el mismo tema elegido (typos: precio + profesionalización…). */
    'faq_lexical_min_score_in_scope' => (float) env('PUKLLABOT_FAQ_MIN_SCORE_SCOPE', 2.75),

    /** Umbral más alto si hay que buscar FAQs en otros temas (olvido de cambiar el desplegable). */
    'faq_lexical_min_score_cross_scope' => (float) env('PUKLLABOT_FAQ_MIN_SCORE_CROSS', 4),

    /** Buscar FAQs en todos los temas cuando no hay match en el tema actual (por defecto sí). */
    'faq_cross_scope' => filter_var(env('PUKLLABOT_FAQ_CROSS_SCOPE', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * Archivos a ingerir y su ámbito (evita mezclar programas, admisión FID/PPD, etc.).
     * `php artisan pukllabot:ingest --force` vacía la tabla e indexa todos.
     */
    'corpora' => [
        ['file' => 'corpus-admision-ordinario.md', 'scope' => 'admision-ordinario'],
        ['file' => 'corpus-admision-profesionalizacion-docente.md', 'scope' => 'admision-profesionalizacion-docente'],
        ['file' => 'corpus-programas-inicial.md', 'scope' => 'programas-inicial'],
        ['file' => 'corpus-programas-eib.md', 'scope' => 'programas-eib'],
    ],

    /**
     * Opciones del desplegable del chat: id => metadatos (mismo id que `scope` en la BD).
     */
    'chat_scopes' => [
        'admision-ordinario' => [
            'label' => 'Admisión: Ingreso ordinario',
            'description' => 'Página pública de ingreso ordinario; no es Profesionalización docente (PPD) ni 2.ª especialidad',
        ],
        'admision-profesionalizacion-docente' => [
            'label' => 'Admisión: Profesionalización docente (PPD)',
            'description' => 'Página /profesionalizacion-docente: programa de 1 año, requisitos, examen de admisión PPD; distinto del ingreso ordinario FID salvo que el contexto lo relacione.',
        ],
        'programas-inicial' => [
            'label' => 'Programa: Educación Inicial',
            'description' => 'Contenido de /programas/educacion-inicial (formación inicial docente FID)',
        ],
        'programas-eib' => [
            'label' => 'Programa: Primaria EIB',
            'description' => 'Contenido de /programas/educacion-primaria-EIB (Intercultural Bilingüe, FID)',
        ],
        
    ],

    /**
     * OpenRouter: API compatible con OpenAI (POST .../v1/chat/completions).
     * @see https://openrouter.ai/docs/guides/routing/routers/free-models-router
     */
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'api_base' => rtrim(env('OPENROUTER_API_BASE', 'https://openrouter.ai/api/v1'), '/'),
        /** @see https://openrouter.ai/docs/models — openrouter/free suele ser lento; un modelo fijado paga y suele responder más rápido */
        'model' => env('PUKLLABOT_OPENROUTER_MODEL', 'openrouter/free'),
        'http_referer' => env('OPENROUTER_HTTP_REFERER', env('APP_URL', 'http://localhost')),
        'app_name' => env('OPENROUTER_APP_NAME', 'PukllaBot'),
        'timeout' => (float) env('PUKLLABOT_OPENROUTER_TIMEOUT', 75),
        'connect_timeout' => (float) env('PUKLLABOT_OPENROUTER_CONNECT_TIMEOUT', 12),
    ],
];
