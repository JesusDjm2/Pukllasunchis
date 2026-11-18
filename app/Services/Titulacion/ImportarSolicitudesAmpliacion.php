<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Solicitud;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Importar solicitudes de ampliación de PTI desde formulario de Google o Excel de trámites extraordinarios
 * → solicitudes de tipo ampliacion_pti. Ver ImportarFormularioGoogle.
 */
class ImportarSolicitudesAmpliacion extends ImportarFormularioGoogle
{
    public const TIPO = Solicitud::AMPLIACION_PTI;

    protected const COLUMNAS = [
        'fecha' => [['marca temporal'], ['timestamp'], ['fecha']],
        'correo' => [['correo']],
        'dni1' => [['dni'], ['documento'], ['cedula']],
        'nombre1' => [['apellidos', 'nombres'], ['apellidos'], ['nombre'], ['estudiante'], ['tesista']],
        'celulares' => [['celular'], ['telefono'], ['whatsapp'], ['contacto']],
        'programa' => [['programa de estudios'], ['programa'], ['especialidad'], ['carrera']],
        'titulo' => [['titulo del plan de trabajo'], ['plan de trabajo'], ['titulo'], ['tema'], ['proyecto']],
        'motivo' => [['motivo'], ['justificacion'], ['fundamentacion'], ['razon'], ['fundamento']],
        'meses' => [['meses'], ['plazo solicitado'], ['plazo'], ['tiempo'], ['prorroga']],
        'comprobante' => [['comprobante'], ['voucher'], ['boleta'], ['pago'], ['anexo']],
        'rd' => [['numero de rd'], ['numero rd'], ['rd de ampliacion'], ['resolucion'], ['rd']],
        'declaracion' => [['declaracion']],
    ];

    protected const IMPORTANTES = ['fecha', 'correo', 'comprobante'];

    protected static function esHoja(array $cabecera): bool
    {
        return self::tieneEncabezado($cabecera, ['extension', 'ampliacion', 'plazo', 'prorroga'])
            || self::tieneEncabezado($cabecera, ['tramites extraordinarios', 'extraordinario'])
            || (self::tieneEncabezado($cabecera, ['marca temporal', 'timestamp', 'fecha']) && self::tieneEncabezado($cabecera, ['correo']));
    }

    protected static function fila(string $hoja, int $numFila, \Closure $txt, Carbon $fecha, string $crudoFecha, ReconocerPersonas $p): array
    {
        $correo = mb_strtolower(trim($txt('correo')));
        $dni1 = preg_replace('/\D/', '', $txt('dni1'));
        $celulares = collect([$txt('celulares')])->filter()->unique()->implode(' · ');
        $nombre = $txt('nombre1');

        [$integrantes, $usuario, $metodo] = self::reconocerGrupo($p, [
            ['nombre' => $nombre, 'dni' => $dni1],
        ], $correo, $celulares);

        $seguimiento = $usuario?->seguimientoTitulacion;
        $pti = $seguimiento?->pti;
        $motivo = $txt('motivo') ?: 'Ampliación de plazo para culminación de investigación';
        $meses = preg_replace('/\D/', '', $txt('meses'));
        $mesesVal = in_array((int) $meses, [3, 6, 12, 18, 24], true) ? (int) $meses : 6;
        $numeroRd = trim($txt('rd'));

        // Si ya tiene una ampliación registrada en el PTI o en resoluciones, o si el Excel trae N° de RD:
        $tieneAmpliacion = filled($pti?->rd_ampliacion)
            || ($seguimiento && $seguimiento->resoluciones->where('tipo', 'AmpliacionPti')->isNotEmpty())
            || filled($numeroRd);

        $titulo = $txt('titulo') ?: ($pti?->titulo ?: 'Plan de Trabajo de Investigación');

        return [
            'correo' => $correo,
            'integrantes' => $integrantes,
            'celulares' => $celulares,
            'programa' => $txt('programa') ?: ($usuario?->programa?->nombre ?? null),
            'ciclo' => 'Egresado',
            'titulo' => $titulo,
            'archivo' => null,
            'comprobante' => $txt('comprobante'),
            'declaracion' => $txt('declaracion') !== '',
            'datos' => array_filter([
                'motivo' => $motivo,
                'meses_solicitados' => $mesesVal,
                'numero_rd_excel' => $numeroRd ?: null,
                'dni_solicitante' => $dni1 ?: null,
                'hoja' => $hoja,
                'fila' => $numFila,
                'reconocido_por' => $metodo,
            ]),
            'usuario' => $usuario,
            'metodo' => $metodo,
            'motivo' => $usuario ? null : 'No se encontró a quien envió (correo, DNI, nombre ni celular)',
            'seguimiento_id' => $seguimiento?->id,
            'estado' => $tieneAmpliacion ? 'atendida' : 'recibida',
            'clave' => self::clave($hoja, $crudoFecha, $correo, $nombre, $motivo),
        ];
    }
}
