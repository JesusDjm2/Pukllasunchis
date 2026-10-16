<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;

/**
 * Orden obligatorio del proceso de titulación: nada se registra antes de que esté logrado
 * el paso anterior (no se puede subir la RD de la Tesis si recién se inscribió el PTI).
 *
 *   PTI inscrito → PTI aprobado (RD) → TI expuesto → TI aprobado (RD) → Bachiller (SUNEDU)
 *   → Tesis sustentada → Tesis aprobada (RD) → Licenciado (SUNEDU)
 *
 * Lo ya registrado se puede corregir siempre (los datos importados de secretaría tienen
 * huecos): la regla solo frena registrar algo NUEVO fuera de orden. La usan la ficha del
 * gestor (botones bloqueados con el motivo) y el servidor (TitulacionController,
 * ResolucionDirectoralController), para que no se pueda saltar desde ningún lado.
 */
class ReglasProceso
{
    /** Hito que debe estar logrado para registrar cada RD, por tipo. */
    public const REQUISITO_RD = [
        'PTI' => 'pti_inscrito',
        'AmpliacionPti' => 'pti_inscrito',
        'AnulacionPti' => 'pti_inscrito',
        'CambioTema' => 'pti_inscrito',
        'CambioAsesor' => 'pti_inscrito',
        'CambioGrupo' => 'pti_inscrito',
        'Exposicion' => 'ti_expuesto',
        'FechaSustentacion' => 'bachiller',
        'Extension' => 'bachiller',
        'Tesis' => 'tesis_sustentada',
    ];

    /** Hito que debe estar logrado para registrar cada dato de TI/Tesis. */
    public const REQUISITO_CAMPO = [
        'fecha_exposicion' => 'pti_aprobado',
        'rd_ti' => 'ti_expuesto',
        'fecha_bachiller_sunedu' => 'ti_aprobado',
        'bachiller_sunedu' => 'ti_aprobado',
        'fecha_sustentacion' => 'bachiller',
        'rd_extension' => 'bachiller',
        'fecha_extension' => 'bachiller',
        'rd_tesis' => 'tesis_sustentada',
        'url_repositorio' => 'tesis_sustentada',
        'fecha_titulo_sunedu' => 'tesis_aprobada',
        'titulo_sunedu' => 'tesis_aprobada',
    ];

    /** Nombre de cada dato, para el mensaje. */
    private const NOMBRE_CAMPO = [
        'fecha_exposicion' => 'la fecha de exposición del TI',
        'rd_ti' => 'la RD de aprobación del TI',
        'fecha_bachiller_sunedu' => 'el Bachiller (SUNEDU)',
        'bachiller_sunedu' => 'el Bachiller (SUNEDU)',
        'fecha_sustentacion' => 'la fecha de sustentación',
        'rd_extension' => 'la ampliación de sustentación',
        'fecha_extension' => 'la ampliación de sustentación',
        'rd_tesis' => 'la RD de aprobación de la Tesis',
        'url_repositorio' => 'el enlace del repositorio',
        'fecha_titulo_sunedu' => 'la Licenciatura (SUNEDU)',
        'titulo_sunedu' => 'la Licenciatura (SUNEDU)',
    ];

    /**
     * RD grupales en las que CADA integrante debe estar en su paso. La RD del PTI y los
     * trámites (cambio de tema, anulación…) no: son los que registran el PTI del grupo.
     */
    public const RD_VALIDA_INTEGRANTES = ['Exposicion', 'FechaSustentacion', 'Extension', 'Tesis'];

    /** Qué le falta a un integrante para figurar en una RD de este tipo (null = puede). */
    public static function faltaIntegrante(?SeguimientoTitulacion $seguimiento, string $tipo): ?string
    {
        if (! in_array($tipo, self::RD_VALIDA_INTEGRANTES, true)) {
            return null;
        }
        if (! $seguimiento) {
            return 'aún no inicia su proceso';
        }

        return self::falta($seguimiento->avance(), self::REQUISITO_RD[$tipo] ?? null);
    }

    /** Actas: exposición del TI / sustentación de la Tesis. */
    public const REQUISITO_ACTA = ['expo' => 'pti_aprobado', 'tesis' => 'bachiller'];

    /** null si el hito está logrado; si no, el texto "PTI aprobado (RD)" de lo que falta. */
    public static function falta(AvanceTitulacion $avance, ?string $hito): ?string
    {
        if (! $hito || ($avance->hitos[$hito]['logrado'] ?? false)) {
            return null;
        }

        return $avance->hitos[$hito]['label'] ?? $hito;
    }

    /** Mensaje si no se puede registrar una RD de este tipo todavía. */
    public static function validarRd(SeguimientoTitulacion $seguimiento, string $tipo): ?string
    {
        $falta = self::falta($seguimiento->avance(), self::REQUISITO_RD[$tipo] ?? null);

        return $falta ? 'Todavía no se puede registrar «'.(\App\Models\Titulacion\Resolucion::TIPOS[$tipo] ?? $tipo).'»: primero debe estar registrado «'.$falta.'».' : null;
    }

    /** Mensaje si no se puede registrar un acta de este tipo todavía. */
    public static function validarActa(SeguimientoTitulacion $seguimiento, string $tipo): ?string
    {
        $falta = self::falta($seguimiento->avance(), self::REQUISITO_ACTA[$tipo] ?? null);
        $nombre = $tipo === 'tesis' ? 'el acta de sustentación' : 'el acta de exposición';

        return $falta ? "Todavía no se puede registrar {$nombre}: primero debe estar registrado «{$falta}»." : null;
    }

    /**
     * Mensaje si algún dato NUEVO de TI/Tesis va fuera de orden. Se evalúa con los cambios
     * ya aplicados (en memoria), así que un mismo envío puede completar pasos seguidos;
     * los datos que no cambian (aunque estén fuera de orden por la importación) no se tocan.
     */
    public static function validarTesis(SeguimientoTitulacion $seguimiento, array $data): ?string
    {
        $actual = $seguimiento->tesis;
        $nuevos = collect(self::REQUISITO_CAMPO)->keys()->filter(function ($campo) use ($data, $actual) {
            if (! array_key_exists($campo, $data) || blank($data[$campo])) {
                return false;
            }
            $antes = $actual?->{$campo};
            $antes = $antes instanceof \DateTimeInterface ? $antes->format('Y-m-d') : (string) $antes;

            return trim((string) $data[$campo]) !== trim($antes);
        });
        if ($nuevos->isEmpty()) {
            return null;
        }

        $tesis = $actual ? clone $actual : new Tesis();
        $tesis->fill(collect($data)->only($nuevos->all())->all());
        $simulado = clone $seguimiento;
        $simulado->setRelation('tesis', $tesis);
        $avance = new AvanceTitulacion($simulado);

        foreach ($nuevos as $campo) {
            if ($falta = self::falta($avance, self::REQUISITO_CAMPO[$campo])) {
                return 'Todavía no se puede registrar '.self::NOMBRE_CAMPO[$campo].': primero debe estar registrado «'.$falta.'».';
            }
        }

        return null;
    }
}
