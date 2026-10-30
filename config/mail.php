<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "log", "array", "failover", "roundrobin"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // Cuenta de Secretaría: los correos de las RD salen con ella (ver App\Mail\Titulacion\Remitente).
        'secretaria' => [
            'transport' => 'smtp',
            'host' => env('MAIL_SECRETARIA_HOST'),
            'port' => env('MAIL_SECRETARIA_PORT', 587),
            'encryption' => env('MAIL_SECRETARIA_ENCRYPTION', 'tls'),
            'username' => env('MAIL_SECRETARIA_USERNAME', 'eespp@pukllasunchis.org'),
            'password' => env('MAIL_SECRETARIA_PASSWORD'),
            'timeout' => 20,
        ],

        // Cuenta del Área de Investigación: avisos del PTI y correos al jurado (ver App\Mail\Titulacion\Remitente).
        // Es de Google: la contraseña es una «contraseña de aplicación», no la de inicio de sesión.
        'investigacion' => [
            'transport' => 'smtp',
            'host' => env('MAIL_INVESTIGACION_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_INVESTIGACION_PORT', 587),
            'encryption' => env('MAIL_INVESTIGACION_ENCRYPTION', 'tls'),
            'username' => env('MAIL_INVESTIGACION_USERNAME', 'u.investigacion.eesp@pukllavirtual.edu.pe'),
            'password' => env('MAIL_INVESTIGACION_PASSWORD'),
            'timeout' => 20,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => null,
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    // Solo para pruebas en local (la base tiene correos reales de estudiantes): con MAIL_PRUEBA_DESTINO
    // en .env, TODOS los correos llegan a esa dirección. En producción esa variable no debe existir.
    'to' => env('MAIL_PRUEBA_DESTINO') ? ['address' => env('MAIL_PRUEBA_DESTINO'), 'name' => 'Prueba local'] : null,

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@eesp-pukllasunchis.edu.pe'),
        'name' => env('MAIL_FROM_NAME', 'EESP Pukllasunchis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
