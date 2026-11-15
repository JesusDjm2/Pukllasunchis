<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Solicitud;
use Carbon\Carbon;

/**
 * Formulario de Google «Solicitud de aprobación de Plan de Trabajo de Investigación y asignación de asesor»
 * → solicitudes de PTI (tipo aprobacion_pti). Ver ImportarFormularioGoogle.
 *
 *  - La pestaña es la que pregunta «…solicito la aprobación de mi Plan de Trabajo de Investigación titulado».
 *  - El formulario antiguo pedía el DNI del integrante 1 (y del 2) y un celular; el actual, el correo y los
 *    «números de contacto». Se usan los dos.
 *  - Estado: «atendida» si quien envió ya tiene un PTI en el sistema (queda enlazada a su proceso); si no,
 *    «por atender», para que Investigación inicie el proceso desde la solicitud.
 */
class ImportarSolicitudesPti extends ImportarFormularioGoogle
{
    public const TIPO = Solicitud::PTI;

    protected const COLUMNAS = [
        'fecha' => [['marca temporal'], ['timestamp']],
        'correo' => [['correo']],
        // Antes que los integrantes: «Número de DNI del Integrante 1» también contiene «integrante 1».
        'dni1' => [['dni', 'integrante 1']],
        'dni2' => [['dni', 'integrante 2']],
        'integrante1' => [['integrante 1']],
        'integrante2' => [['integrante 2']],
        'integrante3' => [['integrante 3']],
        'celulares' => [['contacto']],          // formulario actual
        'celular' => [['celular'], ['telefono']],  // formulario antiguo («Número de celular»)
        'programa' => [['programa de estudios']],
        'ciclo' => [['ciclo']],
        'area' => [['area de investigacion']],
        'linea' => [['linea de investigacion']],
        'titulo' => [['plan de trabajo', 'titulado'], ['titulado'], ['titulo']],
        'archivo' => [['anexo 1'], ['adjunto', 'plan de trabajo']],
        'comprobante' => [['anexo 2'], ['comprobante']],
        'declaracion' => [['declaracion']],
    ];

    protected const IMPORTANTES = ['fecha', 'correo', 'programa', 'archivo', 'comprobante'];

    protected static function esHoja(array $cabecera): bool
    {
        return self::tieneEncabezado($cabecera, ['integrante 1'])
            && self::tieneEncabezado($cabecera, ['aprobacion', 'plan de trabajo', 'titulado']);
    }

    protected static function fila(string $hoja, int $numFila, \Closure $txt, Carbon $fecha, string $crudoFecha, ReconocerPersonas $p): array
    {
        $correo = mb_strtolower($txt('correo'));
        $celulares = collect([$txt('celulares'), $txt('celular')])->filter()->unique()->implode(' · ');
        $dni1 = preg_replace('/\D/', '', $txt('dni1'));
        $dni2 = preg_replace('/\D/', '', $txt('dni2'));

        [$integrantes, $usuario, $metodo] = self::reconocerGrupo($p, [
            ['nombre' => $txt('integrante1'), 'dni' => $dni1],
            ['nombre' => $txt('integrante2'), 'dni' => $dni2],
            ['nombre' => $txt('integrante3')],
        ], $correo, $celulares);

        $seguimiento = $usuario?->seguimientoTitulacion;
        $tienePti = (bool) $seguimiento?->pti;
        $area = $txt('area');
        $linea = $txt('linea');

        return [
            'correo' => $correo, 'integrantes' => $integrantes, 'celulares' => $celulares,
            'programa' => $txt('programa'), 'ciclo' => $txt('ciclo'), 'titulo' => $txt('titulo'),
            'archivo' => $txt('archivo'), 'comprobante' => $txt('comprobante'), 'declaracion' => $txt('declaracion') !== '',
            'datos' => array_filter([
                'area' => Solicitud::claveArea($area), 'area_texto' => Solicitud::claveArea($area) ? null : ($area ?: null),
                'linea' => Solicitud::claveLinea($linea), 'linea_texto' => Solicitud::claveLinea($linea) ? null : ($linea ?: null),
                'dni_integrante1' => $dni1 ?: null, 'dni_integrante2' => $dni2 ?: null,
                'hoja' => $hoja, 'fila' => $numFila, 'reconocido_por' => $metodo,
            ]),
            'usuario' => $usuario, 'metodo' => $metodo,
            'motivo' => $usuario ? null : 'No se encontró a quien envió (correo, DNI, nombre de los integrantes ni celular)',
            'seguimiento_id' => $tienePti ? $seguimiento->id : null,
            'estado' => $tienePti ? 'atendida' : 'recibida',
            'clave' => self::clave($hoja, $crudoFecha, $correo, $txt('integrante1'), $txt('titulo')),
        ];
    }
}
