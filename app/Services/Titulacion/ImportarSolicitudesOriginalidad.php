<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Solicitud;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Formulario de Google «Revisión de originalidad» (Turnitin), del TI o de la Tesis → solicitudes de tipo
 * revision_originalidad (datos.etapa = ti | tesis). Ver ImportarFormularioGoogle.
 *
 *  - Hasta 3 tesistas, cada uno con DNI y correo institucional (el formulario cambió con los años: hay dos
 *    columnas de correo para el tesista 2 y el 3; se usan las dos).
 *  - «…solicito la revisión porcentaje de similitud en TURNITIN de:» dice si es del TI o de la Tesis; en las
 *    respuestas más antiguas ahí escribían el título (se toma como TI).
 *  - El resultado (reporte y %) se enviaba por correo y no está en el Excel. Estado: «atendida» (fuera del
 *    sistema) si la persona ya pasó a un paso posterior o si la solicitud tiene más de DIAS_ANTIGUA días;
 *    si no, «por atender».
 */
class ImportarSolicitudesOriginalidad extends ImportarFormularioGoogle
{
    public const TIPO = Solicitud::ORIGINALIDAD;

    /** Una solicitud con más días que esto ya se atendió fuera del sistema (el plazo es de 20 días hábiles). */
    public const DIAS_ANTIGUA = 30;

    /** Pasos posteriores a la revisión de originalidad de cada etapa (AvanceTitulacion). */
    private const DESPUES = [
        'ti' => ['ti_enlace', 'ti_expuesto', 'ti_aprobado', 'ti_repositorio', 'bachiller'],
        'tesis' => ['tesis_enlace', 'tesis_sustentada', 'tesis_tramite', 'tesis_aprobada', 'licenciado'],
    ];

    protected const COLUMNAS = [
        'fecha' => [['marca temporal'], ['timestamp']],
        'dni1' => [['dni', 'tesista 1']],
        'dni2' => [['dni', 'tesista 2']],
        'dni3' => [['dni', 'tesista 3']],
        'correo1' => [['correo', 'tesista 1']],
        'correo2' => [['correo', 'tesista 2']],
        'correo2b' => [['correo', 'tesista 2']],
        'correo3' => [['correo', 'tesista 3']],
        'correo3b' => [['correo', 'tesista 3']],
        'nombre1' => [['apellidos', 'tesista 1'], ['nombre', 'tesista 1']],
        'nombre2' => [['apellidos', 'tesista 2'], ['nombre', 'tesista 2']],
        'nombre3' => [['apellidos', 'tesista 3'], ['nombre', 'tesista 3']],
        'celulares' => [['celular'], ['telefono']],
        'formacion' => [['programa de formacion']],
        'modalidad' => [['modalidad de formacion']],
        'programa' => [['programa de estudios']],
        'ciclo' => [['ciclo']],
        'domicilio' => [['domicilio']],
        'etapa' => [['turnitin']],
        'titulo' => [['titulo del documento'], ['titulo']],
        'archivo' => [['anexo 1']],
        'informe' => [['informe de ti apto']],
        'comprobante' => [['comprobante']],
        'fecha_pago' => [['fecha de pago']],
        'declaracion' => [['declaracion']],
    ];

    protected const IMPORTANTES = ['fecha', 'etapa', 'archivo', 'comprobante'];

    /** Avance ya calculado por persona (una persona puede tener muchas solicitudes). */
    private static array $hitos = [];

    protected static function esHoja(array $cabecera): bool
    {
        return self::tieneEncabezado($cabecera, ['turnitin']) && self::tieneEncabezado($cabecera, ['tesista 1']);
    }

    protected static function fila(string $hoja, int $numFila, \Closure $txt, Carbon $fecha, string $crudoFecha, ReconocerPersonas $p): array
    {
        $dni = fn ($k) => preg_replace('/\D/', '', $txt($k));
        $correos = [
            1 => mb_strtolower($txt('correo1')),
            2 => mb_strtolower($txt('correo2') ?: $txt('correo2b')),
            3 => mb_strtolower($txt('correo3') ?: $txt('correo3b')),
        ];
        [$integrantes, $usuario, $metodo] = self::reconocerGrupo($p, [
            ['nombre' => $txt('nombre1'), 'dni' => $dni('dni1'), 'correo' => $correos[1]],
            ['nombre' => $txt('nombre2'), 'dni' => $dni('dni2'), 'correo' => $correos[2]],
            ['nombre' => $txt('nombre3'), 'dni' => $dni('dni3'), 'correo' => $correos[3]],
        ], $correos[1], $txt('celulares'));

        // TI o Tesis; en las respuestas antiguas ahí venía el título.
        $deQue = Str::lower(Str::ascii($txt('etapa')));
        $etapa = match (true) {
            $deQue === 'tesis' || (str_contains($deQue, 'tesis') && ! str_contains($deQue, 'investigacion')) => 'tesis',
            str_contains($deQue, 'trabajo de investigacion') && mb_strlen($deQue) < 40 => 'ti',
            default => null,
        };
        $titulo = $txt('titulo') ?: ($etapa === null ? $txt('etapa') : '');

        $seguimiento = $usuario?->seguimientoTitulacion;
        [$estado, $porQue] = self::estado($usuario?->id, $seguimiento, $etapa ?? 'ti', $fecha);

        return [
            'correo' => $correos[1], 'integrantes' => $integrantes, 'celulares' => $txt('celulares'),
            'programa' => self::programa($txt('programa') ?: $txt('formacion')) ?? Str::limit($txt('programa') ?: $txt('formacion'), 100, ''),
            'ciclo' => $txt('ciclo'), 'titulo' => $titulo,
            'archivo' => $txt('archivo'), 'comprobante' => $txt('comprobante'), 'declaracion' => $txt('declaracion') !== '',
            'datos' => array_filter([
                'etapa' => $etapa ?? 'ti',
                'etapa_supuesta' => $etapa === null ?: null,
                'modalidad' => self::modalidad($txt('modalidad').' '.$txt('ciclo')),
                'domicilio' => $txt('domicilio') ?: null,
                'fecha_pago' => $txt('fecha_pago') ?: null,
                'informe_ti' => $txt('informe') ?: null,
                'atendida_fuera' => $estado === 'atendida' ? $porQue : null,
                'hoja' => $hoja, 'fila' => $numFila, 'reconocido_por' => $metodo,
            ]),
            'usuario' => $usuario, 'metodo' => $metodo,
            'motivo' => $usuario ? null : 'No se encontró a quien envió (DNI, correo, nombre de los tesistas ni celular)',
            'seguimiento_id' => $seguimiento?->id,
            'estado' => $estado,
            'clave' => self::clave($hoja, $crudoFecha, $correos[1], $txt('nombre1'), $txt('etapa'), $titulo),
        ];
    }

    /** @return array{0:string, 1:?string} [estado, por qué se da por atendida: 'avance' | 'antigua'] */
    private static function estado(?int $userId, $seguimiento, string $etapa, Carbon $fecha): array
    {
        if ($seguimiento) {
            $hitos = self::$hitos[$userId] ??= $seguimiento->avance()->hitos;
            if (collect(self::DESPUES[$etapa])->contains(fn ($h) => $hitos[$h]['logrado'] ?? false)) {
                return ['atendida', 'avance'];
            }
        }

        return $fecha->lt(now()->subDays(self::DIAS_ANTIGUA)) ? ['atendida', 'antigua'] : ['recibida', null];
    }

    /** «Educación Primaria EIB», «Educación Inicial EBR»… → nombre de Solicitud::PROGRAMAS_ESTUDIOS. */
    public static function programa(string $t): ?string
    {
        $t = Str::lower(Str::ascii($t));

        return match (true) {
            str_contains($t, 'inicial') => 'Educación Inicial',
            str_contains($t, 'eib') || str_contains($t, 'intercultural') => 'Educación Primaria Intercultural Bilingüe',
            str_contains($t, 'primaria') => 'Educación Primaria',
            default => null,
        };
    }

    private static function modalidad(string $t): ?string
    {
        $t = Str::lower(Str::ascii($t));

        return match (true) {
            str_contains($t, 'profesionaliz') || str_contains($t, 'ppd') => 'PPD',
            str_contains($t, 'formacion inicial') || str_contains($t, 'fid') || preg_match('/\b(x|xi|xii|ix) ciclo\b/', $t) === 1 => 'FID',
            default => null,
        };
    }
}
