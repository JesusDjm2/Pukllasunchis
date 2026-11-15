<?php

namespace App\Services\Titulacion;

/**
 * Catálogo único de los trámites extraordinarios de titulación: código y monto de Caja Cusco (según el PDF
 * oficial public/pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf) y cómo se solicita cada uno.
 * De aquí salen la página pública /tramites/pagos-extraordinarios y, a medida que cada trámite pase al
 * sistema, el formulario de «Mi titulación» y la bandeja del gestor.
 *
 * 'formulario': enlace del formulario de Google mientras el trámite no esté en el sistema (null = no hay un
 * enlace válido: la página lo muestra como «no disponible»).
 */
class TramitesExtraordinarios
{
    public const TIPOS = [
        'aprobacion_pti' => [
            'codigo' => 10, 'monto' => 50.00, 'concepto_caja' => 'APROBACIÓN DE PTI Y ASESOR',
            'titulo' => 'Aprobación de PTI y Asesor', 'icono' => 'fa-file-circle-check',
            'resumen' => 'Inscripción inicial o reaprobación tras cambio sustancial',
            'descripcion' => 'Inscripción formal del PTI y designación o ratificación de asesor mediante R.D.',
            'formulario' => 'https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform',
        ],
        'cambio_tema' => [
            'codigo' => 12, 'monto' => 30.00, 'concepto_caja' => 'MODIFICACIÓN DE TEMA DE INVEST.',
            'titulo' => 'Cambio de tema de investigación', 'icono' => 'fa-pen-ruler',
            'resumen' => 'Modificación del título, reajuste temático o metodológico del PTI aprobado',
            'descripcion' => 'Modificación del título de investigación, objetivos o reajuste metodológico aprobado.',
            'formulario' => null,
        ],
        'cambio_asesor' => [
            'codigo' => 13, 'monto' => 30.00, 'concepto_caja' => 'CAMBIO DE ASESOR',
            'titulo' => 'Cambio de Asesor/a', 'icono' => 'fa-user-pen',
            'resumen' => 'Reasignación justificada de docente asesor',
            'descripcion' => 'Solicitud de reasignación fundamentada de asesor/a.',
            'formulario' => 'https://docs.google.com/forms/d/1m7kWkj6C_Uoz252CHr_IsM1xkRhygy5DsCYKl_bH36E/viewform',
        ],
        'separacion_grupo' => [
            'codigo' => 43, 'monto' => 30.00, 'concepto_caja' => 'SEPARACION GRUPO DE INVESTIGACION',
            'titulo' => 'Separación de Grupo de Investigación', 'icono' => 'fa-users-slash',
            'resumen' => 'Cuando solo uno de los integrantes se retira del grupo de investigación',
            'descripcion' => 'Solicitud de retiro de un integrante del equipo de investigación.',
            'formulario' => null,
        ],
        'disolucion_grupo' => [ // Alias de compatibilidad hacia separacion_grupo
            'codigo' => 43, 'monto' => 30.00, 'concepto_caja' => 'SEPARACION GRUPO DE INVESTIGACION',
            'titulo' => 'Separación de Grupo de Investigación', 'icono' => 'fa-users-slash',
            'resumen' => 'Cuando solo uno de los integrantes se retira del grupo de investigación',
            'descripcion' => 'Solicitud de retiro de un integrante del equipo de investigación.',
            'formulario' => null,
        ],
        'integracion_grupo' => [
            'codigo' => 47, 'monto' => 30.00, 'concepto_caja' => 'INTEGRACION A GRUPO DE INVESTIGACION',
            'titulo' => 'Integración al Grupo de Investigación', 'icono' => 'fa-user-plus',
            'resumen' => 'Incorporación de un estudiante a un grupo de investigación existente',
            'descripcion' => 'Solicitud de incorporación de un estudiante a un grupo de investigación ya conformado.',
            'formulario' => null,
        ],
        'dejar_sin_efecto_rd' => [
            'codigo' => 14, 'monto' => 30.00, 'concepto_caja' => 'DEJAR SIN EFECTO RD DE PTI',
            'titulo' => 'Dejar sin efecto R.D. de PTI', 'icono' => 'fa-ban',
            'resumen' => 'Dejar sin efecto la resolución directoral de plan de trabajo',
            'descripcion' => 'Solicitud formal para dejar sin efecto la R.D. de aprobación del Plan de Trabajo de Investigación.',
            'formulario' => null,
        ],
        'anulacion_rd_pti' => [ // Alias de compatibilidad hacia dejar_sin_efecto_rd
            'codigo' => 14, 'monto' => 30.00, 'concepto_caja' => 'DEJAR SIN EFECTO RD DE PTI',
            'titulo' => 'Dejar sin efecto R.D. de PTI', 'icono' => 'fa-ban',
            'resumen' => 'Dejar sin efecto la resolución directoral de plan de trabajo',
            'descripcion' => 'Solicitud formal para dejar sin efecto la R.D. de aprobación del Plan de Trabajo de Investigación.',
            'formulario' => null,
        ],
        'extension_plazo' => [
            'codigo' => 11, 'monto' => 50.00, 'concepto_caja' => 'EXTENSIÓN DE PLAZO PARA SUSTENTACIÓN',
            'titulo' => 'Extensión de plazo', 'icono' => 'fa-clock-rotate-left',
            'resumen' => 'Prórroga excepcional para concluir el trabajo de campo, la redacción o la sustentación',
            'descripcion' => 'Prórroga extraordinaria fundamentada para la culminación y entrega del informe final.',
            'formulario' => null,
        ],
        'revision_ti' => [
            'codigo' => 15, 'monto' => 100.00, 'concepto_caja' => 'REVISION DE TRABAJO DE INVESTI',
            'titulo' => 'Revisión del TI y designación de dictaminantes', 'icono' => 'fa-user-graduate',
            'resumen' => 'Revisión formal del Trabajo de Investigación y nombramiento del jurado dictaminante',
            'descripcion' => 'Trámite para la revisión del TI y la designación del jurado dictaminante.',
            'formulario' => null,
        ],
        'asesoria_adicional' => [
            'codigo' => 52, 'monto' => 20.00, 'concepto_caja' => 'ASESORIA ADICIONAL POR SESION',
            'titulo' => 'Asesoría Adicional (por sesión)', 'icono' => 'fa-comments',
            'resumen' => 'Acompañamiento especializado fuera de cronograma lectivo',
            'descripcion' => 'Solicitar sesiones complementarias de acompañamiento con docente especialista por sesión.',
            'formulario' => 'https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform',
        ],
        'rectificacion_nombre' => [
            'codigo' => 24, 'monto' => 100.00, 'concepto_caja' => 'RECTIFICACIÓN DE NOMBRE',
            'titulo' => 'Rectificación de Nombre', 'icono' => 'fa-id-badge',
            'resumen' => 'Corrección formal en diplomas o resoluciones institucionales',
            'descripcion' => 'Solicitud de rectificación de datos personales o nombres en registros oficiales.',
            'formulario' => null,
        ],
        'duplicado_diploma' => [
            'codigo' => 23, 'monto' => 800.00, 'concepto_caja' => 'DUPLICADO DE TITULOS',
            'titulo' => 'Duplicado de Grados y Títulos', 'icono' => 'fa-copy',
            'resumen' => 'Duplicado de diploma de Bachiller o Título Profesional',
            'descripcion' => 'Emisión de duplicado de diploma por pérdida, deterioro o robo (anula el anterior).',
            'formulario' => null,
        ],
    ];

    /** PDF oficial de conceptos de Caja Cusco del que salen códigos y montos. */
    public const PDF_CONCEPTOS = 'pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf';

    public const PERIODO = '2026';

    public static function tipo(string $clave): ?array
    {
        return isset(self::TIPOS[$clave]) ? self::TIPOS[$clave] + ['clave' => $clave] : null;
    }

    /** Monto con formato «S/ 30.00». */
    public static function monto(array $tipo): string
    {
        return 'S/ '.number_format($tipo['monto'], 2);
    }

    /** Cómo se pide hoy: enlace del formulario de Google, o null si todavía no hay ninguno disponible. */
    public static function enlace(array $tipo): ?string
    {
        return $tipo['formulario'] ?? null;
    }
}
