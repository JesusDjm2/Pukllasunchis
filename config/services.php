<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google_drive' => [
        'credentials'  => env('GOOGLE_DRIVE_CREDENTIALS', 'storage/app/google-drive-credentials.json'),
        'impersonate'  => env('GOOGLE_DRIVE_IMPERSONATE'),
        'folders' => [
            'ejercicios' => env('GOOGLE_DRIVE_EJERCICIOS_FOLDER_ID'),
            'lecciones'  => env('GOOGLE_DRIVE_LECCIONES_FOLDER_ID'),
            'taqe'       => env('GOOGLE_DRIVE_TAQE_FOLDER_ID'),
        ],
    ],

    'apiperu' => [
        'url' => env('APIPERU_URL', 'https://apiperu.dev/api'),
        'token' => env('APIPERU_TOKEN'),
    ],

    'google_analytics' => [
        // ID de medición de Google Analytics 4 (formato G-XXXXXXXXXX).
        // Configura GOOGLE_ANALYTICS_ID en .env para activar el tracking del sitio público.
        'id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'notificaciones' => [
        // Destinatarios internos para avisos de matrícula y registro.
        // Configura NOTIF_EMAIL_ADMIN y NOTIF_EMAIL_COBRANZAS en .env
        'emails' => array_filter([
            env('NOTIF_EMAIL_ADMIN'),
            env('NOTIF_EMAIL_COBRANZAS'),
        ]),
    ],

    // Lectura de actas escaneadas (Ricardo Palma) con la API de Gemini.
    // GEMINI_MODEL: uno o varios modelos separados por coma; si uno está saturado se prueba el siguiente.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash,gemini-3.6-flash,gemini-3.5-flash-lite,gemini-3-flash-preview'),
    ],

    'titulacion' => [
        // Correo de la Unidad de Investigación: las respuestas de los
        // egresados a los avisos de vencimiento del PTI llegan aquí
        // (Reply-To). Sin él, responderían a MAIL_FROM_ADDRESS.
        'correo_unidad' => env('TITULACION_CORREO_UNIDAD'),
        // Secretaría: remitente de los correos de las RD y destino de sus respuestas.
        'correo_secretaria' => env('TITULACION_CORREO_SECRETARIA', 'eespp@pukllasunchis.org'),
        // Área de Investigación: remitente de sus correos y destino de sus respuestas.
        'correo_investigacion' => env('TITULACION_CORREO_INVESTIGACION', 'u.investigacion.eesp@pukllavirtual.edu.pe'),
    ],
];
