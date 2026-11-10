<?php

/*
|--------------------------------------------------------------------------
| Proceso de titulación
|--------------------------------------------------------------------------
| Guías/instructivos por etapa que ven egresados y alumnos en su línea de
| tiempo ("Mi proceso de titulación"). Mientras una esté vacía, la etapa
| simplemente no muestra el enlace. Basta con pegar el enlace (Drive,
| PDF público, página del sitio) en el .env y correr `php artisan config:clear`.
*/

return [
    'guias' => [
        'pti' => env('TITULACION_GUIA_PTI'),
        'ti' => env('TITULACION_GUIA_TI'),
        'tesis' => env('TITULACION_GUIA_TESIS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trámites paso a paso (ayuda en "Mi proceso de titulación")
    |--------------------------------------------------------------------------
    | Lo mismo que publica /titulacion/investigacion, por trámite. El alumno solo
    | ve los del tramo en el que está (GuiaTramites::BLOQUES). Las descargas son
    | rutas dentro de public/ (se muestran con asset()); los formularios, enlaces.
    | Cada trámite: titulo, pago [monto, codigo, concepto] | null, modalidad,
    | pasos[], formulario [texto, url] | null, descargas[[texto, ruta]], adjuntar[],
    | resultado, plazo, nota.
    */
    'conceptos_pago_pdf' => 'pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf',

    // A quién escribir por WhatsApp (se muestra al pie de cada trámite).
    'contactos' => [
        ['Secretaría', '+51 969 572 566'],
        ['Unidad de Investigación', '+51 972 577 170'],
    ],

    'tramites' => [
        // ── PTI ──
        'pti_aprobacion' => [
            'titulo' => 'Solicitar la aprobación del PTI',
            'pago' => ['50.00', '10', 'Aprobación de PTI y Asesor'],
            'pasos' => [
                'Haz el pago por derecho de aprobación del PTI y asignación de asesor (pago ordinario en Caja Cusco).',
                'Descarga y completa el Formato del PTI.',
                'Llena el formulario de aprobación de PTI y adjunta los documentos.',
            ],
            'descargas' => [['Formato del PTI', 'pdf/Formato-PTI-2025.docx']],
            'formulario' => ['Formulario de aprobación de PTI', 'https://docs.google.com/forms/u/1/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform'],
            'adjuntar' => ['Plan de Trabajo de Investigación en PDF', 'Comprobante de pago por derecho de aprobación del PTI'],
            'resultado' => 'Recibirás en tu correo institucional la RD de aprobación del PTI y asignación de asesor, y la boleta de tu pago.',
        ],

        // ── TI · Bachiller ──
        'carta_aplicacion' => [
            'titulo' => 'Solicitar la carta de aplicación del TI',
            'opcional' => 'Hazlo apenas tengas tu PTI aprobado, antes de aplicar tu investigación.',
            'pago' => ['10.00', '31', 'Carta de presentación para INV'],
            'pasos' => ['Haz el pago en Caja Cusco (pago ordinario).', 'Llena el formulario del sistema con tu correo institucional y adjunta el comprobante (no voucher).'],
            // Formulario propio del sistema (06/11); reemplaza al de Google.
            'formulario' => ['Formulario de carta de aplicación', 'mi-titulacion.carta'],
            'resultado' => 'Secretaría te responde en 3 días hábiles con la carta de presentación y una carta modelo de aceptación, por correo y en tu intranet.',
        ],
        'turnitin' => [
            'titulo' => 'Solicitar la revisión de originalidad (Turnitin)',
            'pago' => ['25.00', '16', 'Revisión Turnitin de TI'],
            'pasos' => ['Cuando termines tu TI, haz el pago en Caja Cusco.', 'Llena el formulario de revisión de originalidad y adjunta los documentos.'],
            'formulario' => ['Formulario de revisión de originalidad', 'https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform'],
            'adjuntar' => ['Versión final del TI en Word', 'Comprobante de pago por derecho de revisión de originalidad'],
            'resultado' => 'Recibirás en tu correo institucional el reporte de originalidad (Turnitin).',
            'plazo' => 'Hasta 10 días hábiles. El pago vale una semana e incluye 1 revisión.',
            'nota' => 'La similitud máxima es 20 %. Si la superas, corrige el documento y solicita la revisión otra vez.',
        ],
        'revision_asesor' => [
            'titulo' => 'Solicitar la revisión del TI por tu asesor/a',
            'pago' => ['100.00', '15', 'Revisión de Trabajo de Investigación'],
            'pasos' => ['Con tu reporte Turnitin aprobado, haz el pago en Caja Cusco.', 'Llena el formulario de revisión de TI y adjunta los documentos.'],
            'formulario' => ['Formulario de revisión de TI', 'https://docs.google.com/forms/d/e/1FAIpQLSfr-AXQ-NLEpO_2PGr8E8MVvHp8Cw8ook1hfqVB_WFlYpRw1Q/viewform'],
            'adjuntar' => ['Trabajo de Investigación en Word', 'Reporte de originalidad Turnitin (máximo 20 %)', 'Comprobante de pago por derecho de revisión del TI'],
            'resultado' => 'Recibirás en tu correo institucional el Informe de TI apto.',
            'plazo' => 'Hasta 30 días hábiles.',
        ],
        'fecha_exposicion' => [
            'titulo' => 'Solicitar la fecha de exposición del TI',
            'pago' => ['50.00', '55', 'Fecha de Exposición de TI'],
            'pasos' => ['Haz el pago en Caja Cusco.', 'Llena el formulario de solicitud de fecha de exposición y adjunta los documentos.'],
            'formulario' => ['Formulario de fecha de exposición', 'https://docs.google.com/forms/d/e/1FAIpQLSfyasqxxCR72v7aSnmtpISOedxYQzqV11igfLnegIr_biXIew/viewform'],
            'adjuntar' => ['Informe de TI apto en PDF', 'Diapositivas para la exposición en PowerPoint'],
            'nota' => 'Este trámite pasa directamente a tu expediente de titulación.',
        ],
        'rd_ti' => [
            'titulo' => 'Solicitar la RD de aprobación del TI',
            'pago' => ['50.00', '17', 'RD de aprobación de TI'],
            'pasos' => ['Después de tu exposición, haz el pago en Caja Cusco.', 'Llena el formulario de aprobación de TI y adjunta los documentos.'],
            'formulario' => ['Formulario de aprobación de TI', 'https://docs.google.com/forms/d/e/1FAIpQLSeGQ3XQAjWD2IPRUQnHIGfreOzNmgs-GceyKXgaGPsRg4EngQ/viewform'],
            'adjuntar' => ['Informe de TI apto en PDF', 'Acta de aprobación de TI en PDF', 'Comprobante de pago por derecho de RD de aprobación del TI'],
            'resultado' => 'Recibirás en tu correo institucional la RD de aprobación del TI el lunes de la semana siguiente a tu solicitud.',
        ],
        'expediente' => [
            'titulo' => 'Armar tu expediente de graduación',
            'modalidad' => 'presencial',
            'pago' => ['500.00', '18', 'Armar expediente de titulación'],
            'pasos' => ['Este trámite se hace solo de manera presencial en la oficina de Secretaría de la EESP Pukllasunchis.'],
            'enlace' => ['Ver trámite', 'tramites'],
        ],
        'repositorio' => [
            'titulo' => 'Registrar tu TI en el Repositorio Institucional',
            'pago' => ['50.00', '19', 'Registro de TI al repositorio'],
            'pasos' => [
                'Haz el pago en Caja Cusco.',
                'Descarga, completa y firma la Autorización de registro en repositorio.',
                'Descarga, completa y firma la Declaración jurada de autenticidad.',
                'Llena el formulario de registro en repositorio y adjunta los documentos.',
            ],
            'descargas' => [
                ['Autorización de registro en repositorio', 'pdf/Autorizacion-Puklla.docx'],
                ['Declaración jurada de autenticidad', 'pdf/Declaración-jurada-de-autenticidad.docx'],
            ],
            'formulario' => ['Formulario de registro en repositorio', 'https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform'],
            'adjuntar' => ['Enlace a la versión final del TI en Word', 'Autorización de depósito en repositorio en Word', 'Declaración jurada de autenticidad en Word', 'Comprobante de pago por derecho de registro en repositorio'],
            'resultado' => 'Si tu TI no tiene observaciones, recibirás en tu correo institucional el enlace de tu TI en el repositorio.',
            'plazo' => 'Hasta 30 días hábiles.',
        ],
        'diploma' => [
            'titulo' => 'Solicitar la emisión del diploma de Bachiller',
            'modalidad' => 'virtual',
            'pago' => ['400.00', '22', 'Emisión Diploma de Bachiller'],
            'pasos' => ['Haz el pago en Caja Cusco.', 'Llena el formulario de solicitud de emisión de diploma y adjunta la boleta.'],
            'formulario' => ['Formulario de emisión de diploma', 'https://docs.google.com/forms/d/e/1FAIpQLSeVSEKKtkYyTxp4e_D6wb0sioHPUNsb57b5esIb7ZUI_Wsr4g/viewform'],
            'adjuntar' => ['Boleta de pago'],
            'resultado' => 'Te avisaremos la fecha en que debes venir de forma presencial a firmar el acta.',
            'plazo' => 'Hasta 15 días hábiles.',
        ],
        'sunedu_bachiller' => [
            'titulo' => 'Inscribir tu grado de Bachiller en SUNEDU',
            'pago' => ['200.00', '20', 'Inscripción Bachiller a SUNEDU'],
            'pasos' => ['Haz el pago en Caja Cusco.', 'Llena el formulario de inscripción del grado (solo con tu correo institucional) y adjunta el comprobante.'],
            'formulario' => ['Formulario de inscripción de grado de Bachiller', 'https://docs.google.com/forms/d/e/1FAIpQLScvKiKJABiDnXKgI2n77OupOZGJItf27NpSiQecyj1fmYwxgg/viewform'],
            'adjuntar' => ['Comprobante de pago por inscripción del grado de Bachiller'],
            'resultado' => 'Tu grado de Bachiller quedará reconocido oficialmente en SUNEDU.',
            'plazo' => 'Unos 30 días hábiles después del pago y de completar el trámite.',
        ],
    ],
];
