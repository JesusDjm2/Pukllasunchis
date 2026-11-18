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
    /*
     * Revisión de originalidad (Turnitin) desde la intranet del alumno: APAGADA por ahora (12/11). Los alumnos
     * siguen con el formulario de Google; la bandeja de Investigación (importar y atender) funciona igual.
     * Para encenderla: TITULACION_ORIGINALIDAD_ALUMNOS=true en el .env.
     */
    'originalidad_alumnos' => (bool) env('TITULACION_ORIGINALIDAD_ALUMNOS', false),

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
        // ── Fase 1 · PTI ──
        'pti_aprobacion' => [
            'titulo' => 'Solicitar la aprobación del PTI',
            'pago' => ['50.00', '10', 'APROBACIÓN DE PTI Y ASESOR'],  // PDF oficial Caja Cusco 2026-II
            'pasos' => [
                'Descarga y completa el Formato Oficial del PTI siguiendo la Guía de Investigación.',
                'Haz el pago ordinario en Caja Cusco (espera 2 días hábiles tu boleta electrónica oficial).',
                'Envía la solicitud desde el formulario de tu Intranet adjuntando tu PTI y tu boleta electrónica.',
            ],
            'descargas' => [
                ['Formato Oficial PTI (.docx)', 'pdf/Plan-de-Trabajo-de-Investigacion-PTI.docx'],
                ['Guía de Investigación (.pdf)', 'pdf/Guia-de-investigacion-2.pdf'],
                ['Guía de Redacción Académica (.pdf)', 'pdf/Guia-de-Investigacion-2025.pdf'],
            ],
            // Formulario propio del sistema (11/11); reemplaza al de Google en la intranet.
            'formulario' => ['Solicitar aprobación del PTI', 'mi-titulacion.solicitud-pti'],
            'adjuntar' => [
                'Plan de Trabajo de Investigación en PDF o Word (.docx)',
                'Boleta electrónica oficial de la EESPP (no voucher de ventanilla)',
            ],
            'resultado' => 'Recibirás en tu correo institucional la R.D. de aprobación del PTI y asignación de docente asesor/a.',
            'plazo' => 'Evaluación por Investigación y emisión de R.D. por Secretaría.',
        ],

        // ── Fase 2 · TI (Grado de Bachiller) ──
        'carta_aplicacion' => [
            'titulo' => 'Solicitar la carta de aplicación del TI',
            'opcional' => 'Hazlo apenas tengas tu PTI aprobado, antes de aplicar tu investigación en la I.E.',
            'pago' => ['10.00', '31', 'Carta de presentación para INV'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco (espera 2 días hábiles tu boleta electrónica oficial).',
                'Llena el formulario de tu Intranet con tu correo institucional y adjunta tu boleta electrónica.',
            ],
            // Formulario propio del sistema (06/11); reemplaza al de Google.
            'formulario' => ['Solicitar carta de aplicación', 'mi-titulacion.carta'],
            'adjuntar' => ['Boleta electrónica oficial de la EESPP (no voucher de ventanilla)'],
            'resultado' => 'Recibirás la carta de presentación y una carta modelo de aceptación por correo institucional y en tu intranet.',
            'plazo' => 'Hasta 3 días hábiles.',
        ],
        'turnitin' => [
            'titulo' => 'Solicitar la revisión de originalidad (Turnitin TI)',
            'pago' => ['25.00', '16', 'Revisión Turnitin de TI'],
            'pasos' => [
                'Cuando culmines tu TI en Word (.docx), haz el pago ordinario en Caja Cusco.',
                'Espera 2 días hábiles tu boleta electrónica oficial y llena el formulario de Turnitin.',
            ],
            'descargas' => [
                ['Formato TI (.docx)', 'pdf/FormatoTI.docx'],
                ['Carátula TI FID (.docx)', 'pdf/CaratulaTI-2025.docx'],
                ['Carátula TI PPD (.docx)', 'pdf/CaratulaTI-2025 PPD.docx'],
            ],
            // Mientras originalidad_alumnos esté apagado, el de Google; al encenderlo, el del sistema.
            'formulario' => env('TITULACION_ORIGINALIDAD_ALUMNOS', false)
                ? ['Formulario de revisión de originalidad', 'mi-titulacion.originalidad']
                : ['Formulario de revisión de originalidad', 'https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform'],
            'adjuntar' => [
                'Versión final del TI en formato editable Word (.docx)',
                'Boleta electrónica oficial de la EESPP (no voucher de ventanilla)',
            ],
            'resultado' => 'Recibirás en tu correo institucional el Reporte Oficial Turnitin.',
            'plazo' => 'Hasta 20 días hábiles (modificado el 10 de noviembre de 2026). Cada pago incluye 1 revisión.',
            'nota' => 'La similitud máxima permitida es 20 %. Si la superas, corrige el documento y solicita una nueva revisión.',
        ],
        'revision_asesor' => [
            'titulo' => 'Solicitar la revisión del TI por asesor/a y dictaminantes',
            'pago' => ['100.00', '15', 'Revisión de Trabajo de Investigación'],
            'pasos' => [
                'Con tu reporte Turnitin aprobado (≤ 20 %), haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de revisión de TI adjuntando tu documento, reporte Turnitin y boleta electrónica.',
            ],
            'formulario' => ['Formulario de revisión de TI', 'https://docs.google.com/forms/d/e/1FAIpQLSfr-AXQ-NLEpO_2PGr8E8MVvHp8Cw8ook1hfqVB_WFlYpRw1Q/viewform'],
            'adjuntar' => [
                'Trabajo de Investigación en Word (.docx)',
                'Reporte de originalidad Turnitin (máximo 20 %)',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Recibirás en tu correo institucional el Informe de TI Apto.',
            'plazo' => 'Hasta 30 días hábiles.',
        ],
        'fecha_exposicion' => [
            'titulo' => 'Solicitar la fecha de exposición del TI',
            'pago' => ['50.00', '55', 'Fecha de Exposición de TI'],
            'pasos' => [
                'Con tu Informe de TI Apto, haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de solicitud de fecha de exposición adjuntando los documentos requeridos.',
            ],
            'formulario' => ['Formulario de fecha de exposición', 'https://docs.google.com/forms/d/e/1FAIpQLSfyasqxxCR72v7aSnmtpISOedxYQzqV11igfLnegIr_biXIew/viewform'],
            'adjuntar' => [
                'Informe de TI Apto en PDF',
                'Diapositivas para la exposición en PowerPoint (PPT o PDF)',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Se programará la fecha y hora de tu exposición pública ante el jurado.',
            'nota' => 'Este trámite pasa directamente a tu expediente de titulación.',
        ],
        'rd_ti' => [
            'titulo' => 'Solicitar la R.D. de aprobación del TI',
            'pago' => ['50.00', '17', 'RD de Aprobación de TI'],
            'pasos' => [
                'Después de aprobar tu exposición, haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de aprobación de TI y adjunta los documentos.',
            ],
            'formulario' => ['Formulario de aprobación de TI', 'https://docs.google.com/forms/d/e/1FAIpQLSeGQ3XQAjWD2IPRUQnHIGfreOzNmgs-GceyKXgaGPsRg4EngQ/viewform'],
            'adjuntar' => [
                'Informe de TI Apto en PDF',
                'Acta de aprobación de exposición de TI en PDF',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Recibirás en tu correo institucional la R.D. de aprobación del TI.',
            'plazo' => 'El lunes de la semana siguiente a tu solicitud.',
        ],
        'expediente' => [
            'titulo' => 'Armar tu expediente de graduación',
            'modalidad' => 'presencial',
            'pago' => ['500.00', '18', 'ARMADO DE EXPEDIENTE DE TITULA'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco (espera 2 días hábiles tu boleta electrónica).',
                'Presenta tu expediente físico completo de manera presencial en Secretaría Académica.',
            ],
            'enlace' => ['Ver requisitos completos del expediente', 'tramites'],
            'resultado' => 'Recepción de la Carta de Aceptación de Expediente de Graduación Completo.',
        ],
        'repositorio' => [
            'titulo' => 'Registrar tu TI en el Repositorio Institucional',
            'pago' => ['50.00', '19', 'Registro de TI al repositorio'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Descarga, completa y firma la Autorización de registro y la Declaración jurada de autenticidad.',
                'Llena el formulario de registro en repositorio y adjunta los documentos.',
            ],
            'descargas' => [
                ['Autorización de registro en repositorio (.docx)', 'pdf/Autorizacion-Puklla.docx'],
                ['Declaración jurada de autenticidad (.docx)', 'pdf/Declaración-jurada-de-autenticidad.docx'],
            ],
            'formulario' => ['Formulario de registro en repositorio', 'https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform'],
            'adjuntar' => [
                'Enlace a la versión final del TI en Word',
                'Autorización de depósito en repositorio en Word',
                'Declaración jurada de autenticidad en Word',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Si tu TI no tiene observaciones, recibirás en tu correo institucional el enlace de tu TI en el repositorio.',
            'plazo' => 'Hasta 30 días hábiles.',
        ],
        'diploma' => [
            'titulo' => 'Solicitar la emisión del diploma de Bachiller',
            'modalidad' => 'virtual',
            'pago' => ['400.00', '22', 'Emisión Diploma de Bachiller'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de solicitud de emisión de diploma y adjunta tu boleta electrónica.',
            ],
            'formulario' => ['Formulario de emisión de diploma', 'https://docs.google.com/forms/d/e/1FAIpQLSeVSEKKtkYyTxp4e_D6wb0sioHPUNsb57b5esIb7ZUI_Wsr4g/viewform'],
            'adjuntar' => ['Boleta electrónica oficial de la EESPP (no voucher)'],
            'resultado' => 'Te avisaremos la fecha en que debes venir de forma presencial a firmar el acta.',
            'plazo' => 'Hasta 15 días hábiles.',
        ],
        'sunedu_bachiller' => [
            'titulo' => 'Inscribir tu grado de Bachiller en SUNEDU',
            'pago' => ['200.00', '20', 'Inscripción Bachiller a SUNEDU'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de inscripción del grado (solo con tu correo institucional) y adjunta tu boleta electrónica.',
            ],
            'formulario' => ['Formulario de inscripción de grado de Bachiller', 'https://docs.google.com/forms/d/e/1FAIpQLScvKiKJABiDnXKgI2n77OupOZGJItf27NpSiQecyj1fmYwxgg/viewform'],
            'adjuntar' => ['Boleta electrónica oficial de la EESPP (no voucher)'],
            'resultado' => 'Tu grado de Bachiller quedará reconocido oficialmente en SUNEDU.',
            'plazo' => 'Aprox. 30 días hábiles después del pago y de completar el trámite.',
        ],

        // ── Fase 3 · Tesis (Título Profesional de Licenciado) ──
        'turnitin_tesis' => [
            'titulo' => 'Solicitar la revisión de originalidad (Turnitin de Tesis)',
            'pago' => ['25.00', '37', 'UNA REVISION TURNITIN DE TESIS'],  // PDF oficial Caja Cusco 2026-II
            'pasos' => [
                'Cuando culmines tu Tesis en Word (.docx), haz el pago ordinario en Caja Cusco.',
                'Espera 2 días hábiles tu boleta electrónica oficial y llena el formulario de Turnitin.',
            ],
            'descargas' => [
                ['Formato Oficial de Tesis (.docx)', 'pdf/FormatoTesis.docx'],
                ['Carátula Tesis FID (.docx)', 'pdf/CaratulaTesis-2025.docx'],
                ['Carátula Tesis PPD (.docx)', 'pdf/CaratulaTesis-2025-PPD.docx'],
                ['Guía de Elaboración de Tesis (.pdf)', 'pdf/Guia-de-Elaboracion-de-Tesis.pdf'],
            ],
            // Se mantiene Google Forms hasta encender originalidad_alumnos.
            'formulario' => env('TITULACION_ORIGINALIDAD_ALUMNOS', false)
                ? ['Formulario de revisión de originalidad', 'mi-titulacion.originalidad']
                : ['Formulario de Turnitin Tesis', 'https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform'],
            'adjuntar' => [
                'Versión final de la Tesis en formato editable Word (.docx)',
                'Boleta electrónica oficial de la EESPP (no voucher de ventanilla)',
            ],
            'resultado' => 'Recibirás en tu correo institucional el Reporte Oficial Turnitin de tu Tesis.',
            'plazo' => 'Hasta 20 días hábiles (modificado el 10 de noviembre de 2026). Cada pago incluye 1 revisión.',
            'nota' => 'Similitud máxima: 20 %. Regla de Exclusión (Art. 94): si tu tesis continúa tu TI de bachiller, excluye solo la sección «Base Teórica» en esta revisión preliminar.',
        ],
        'dictamen_tesis' => [
            'titulo' => 'Solicitar la revisión de Tesis por dictaminantes',
            'pago' => ['100.00', '32', 'REVISIÓN DE TESIS'],
            'pasos' => [
                'Con tu reporte Turnitin aprobado (≤ 20 %), haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de revisión por dictaminantes adjuntando tu tesis, reporte Turnitin y boleta.',
            ],
            'formulario' => ['Solicitar revisión de dictaminantes', 'https://docs.google.com/forms/d/e/1FAIpQLSfU1nSpAs5WnyxP6rcmsQ8DIZlwetYSLyWRTqJ1srFtVCo5HQ/viewform'],
            'adjuntar' => [
                'Tesis completa en Word (.docx)',
                'Reporte de originalidad Turnitin adjunto (≤ 20 % de similitud)',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Recibirás en tu correo institucional el Informe de Tesis Apta para solicitar fecha de sustentación.',
            'plazo' => 'Hasta 60 días hábiles.',
        ],
        'sustentacion_tesis' => [
            'titulo' => 'Solicitar fecha y hora de sustentación de Tesis',
            'pago' => ['100.00', '21', 'RD FECHA DE SUSTENTACIÓN TESIS'],
            'pasos' => [
                'Con tu Informe de Tesis Apta y tu Constancia SUNEDU de Bachiller, haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de solicitud de fecha de sustentación y adjunta todos los documentos.',
            ],
            'formulario' => ['Solicitar fecha de sustentación', 'https://docs.google.com/forms/d/e/1FAIpQLSf4DRlwjTV-vK5FVud94tUxTHi8h61brejjn9bzxBpv7ouqpQ/viewform'],
            'adjuntar' => [
                'Informe de Tesis Apta (Word o PDF)',
                'Reporte Turnitin aprobado (≤ 20 %)',
                'Versión final de la Tesis en Word y Diapositivas en PowerPoint',
                'Constancia de inscripción en SUNEDU del grado de Bachiller (PDF)',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Fijación formal de la fecha y hora de defensa pública ante el jurado calificador.',
        ],
        'rd_tesis' => [
            'titulo' => 'Solicitar la R.D. de aprobación de sustentación',
            'pago' => ['50.00', '33', 'RD DE APROBACION DE TESIS'],
            'pasos' => [
                'Tras aprobar tu sustentación, haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de solicitud de R.D. adjuntando el acta de sustentación, informe y boleta.',
            ],
            'formulario' => ['Solicitar R.D. de sustentación', 'https://docs.google.com/forms/d/e/1FAIpQLSc8k02uEAuRt9OdrGpj1Xe6OIBdXXCZ2j7pNToBxhdktq_smQ/viewform'],
            'adjuntar' => [
                'Acta oficial de sustentación firmada por el jurado',
                'Informe de Tesis Apta',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Recibirás tu Resolución Directoral de aprobación de Tesis en tu correo institucional.',
            'plazo' => 'El lunes de la semana siguiente a tu solicitud.',
        ],
        'repositorio_tesis' => [
            'titulo' => 'Depositar tu Tesis en el Repositorio Institucional',
            'pago' => ['50.00', '34', 'REGISTRO DE TESIS AL REPOSITOR'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Descarga, completa y firma la Autorización de depósito y la Declaración jurada de autenticidad.',
                'Llena el formulario de registro en repositorio y adjunta los documentos.',
            ],
            'descargas' => [
                ['Autorización de Repositorio (.docx)', 'pdf/Autorizacion-Puklla.docx'],
                ['Declaración jurada de autenticidad (.docx)', 'pdf/Declaración-jurada-de-autenticidad.docx'],
            ],
            'formulario' => ['Registrar Tesis en Repositorio', 'https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform'],
            'adjuntar' => [
                'Versión final corregida de la Tesis en Word',
                'Autorización de depósito y Declaración Jurada firmadas',
                'Boleta electrónica oficial de la EESPP (no voucher)',
            ],
            'resultado' => 'Al no registrar observaciones, recibirás el enlace permanente (URL Handle) en el repositorio.',
            'plazo' => 'Hasta 30 días hábiles.',
        ],
        'diploma_tesis' => [
            'titulo' => 'Solicitar la emisión del diploma de Licenciado',
            'modalidad' => 'virtual',
            'pago' => ['400.00', '35', 'EMISION TITULO DE LICENCIADO'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de solicitud de emisión de diploma adjuntando tu boleta electrónica.',
            ],
            'formulario' => ['Formulario de emisión de diploma', 'https://forms.gle/oVAax2L4GiaPGTWp9'],
            'adjuntar' => ['Boleta electrónica oficial de la EESPP (no voucher)'],
            'resultado' => 'Se te notificará la fecha para acudir presencialmente a la firma del libro de títulos.',
            'plazo' => 'Hasta 15 días hábiles.',
        ],
        'sunedu_tesis' => [
            'titulo' => 'Inscribir tu Título de Licenciado en SUNEDU',
            'pago' => ['200.00', '36', 'REGISTRO E INSCRIPCION DE TITULO'],
            'pasos' => [
                'Haz el pago ordinario en Caja Cusco.',
                'Llena el formulario de inscripción ante SUNEDU (solo con tu correo institucional) y adjunta tu boleta.',
            ],
            'formulario' => ['Formulario de inscripción en SUNEDU', 'https://forms.gle/L7WHbS67NKYnWug69'],
            'adjuntar' => ['Boleta electrónica oficial de la EESPP (no voucher)'],
            'resultado' => 'Tu Título Profesional de Licenciado quedará inscrito oficialmente en SUNEDU.',
            'plazo' => 'Aprox. 30 días hábiles tras la remisión del expediente por Secretaría.',
        ],
    ],
];
