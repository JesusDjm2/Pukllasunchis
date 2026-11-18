<?php

namespace App\Services\Titulacion;

/**
 * Ayuda paso a paso para el alumno: qué trámites (config/titulacion.php › tramites) le
 * tocan AHORA según su avance. El proceso se parte en tramos que terminan en algo que el
 * sistema sí registra (RD, informe, fecha, enlace, SUNEDU); solo se muestra el tramo en
 * curso, y dentro de él los trámites en orden. Lo que no registra el sistema (Turnitin,
 * carta, expediente, diploma) va dentro del tramo cuyo cierre sí conoce.
 *
 * Igual que AvanceTitulacion::siguientePaso(): si un tramo posterior ya está cerrado, los
 * anteriores sin dato se dan por superados (un dato que no se cargó no es algo que se deba).
 */
class GuiaTramites
{
    /** Tramo => [título, trámites]. El orden es exactamente el del proceso oficial (PTI → TI → Tesis). */
    public const BLOQUES = [
        'pti' => ['Aprobación de tu PTI', ['pti_aprobacion']],
        'ti_desarrollo' => ['Desarrollo y revisión de tu TI', ['carta_aplicacion', 'turnitin', 'revision_asesor']],
        'ti_exposicion' => ['Exposición de tu TI', ['fecha_exposicion']],
        'ti_rd' => ['Aprobación de tu TI', ['rd_ti']],
        'ti_repositorio' => ['Expediente y repositorio', ['expediente', 'repositorio']],
        'ti_bachiller' => ['Diploma y grado de Bachiller', ['diploma', 'sunedu_bachiller']],
        'tesis_sustentacion' => ['Desarrollo, dictamen y sustentación de tu Tesis', ['turnitin_tesis', 'dictamen_tesis', 'sustentacion_tesis']],
        'tesis_rd' => ['Aprobación de tu Tesis (R.D.)', ['rd_tesis']],
        'tesis_titulo' => ['Repositorio, Diploma y Título Profesional', ['repositorio_tesis', 'diploma_tesis', 'sunedu_tesis']],
    ];

    /**
     * Tramo en curso con sus trámites y cuáles ya constan hechos en el sistema, o null si ya
     * terminó todo el proceso (Licenciado en SUNEDU). Sin $avance: alumno sin proceso iniciado (PTI).
     *
     * @return array{clave:string, titulo:string, tramites:array<string, array>, hechos:array<int, string>}|null
     */
    public static function actual(?AvanceTitulacion $avance): ?array
    {
        $cerrado = self::cierres($avance);

        $ultimoCerrado = -1;
        foreach (array_keys(self::BLOQUES) as $i => $clave) {
            if ($cerrado[$clave]) {
                $ultimoCerrado = $i;
            }
        }
        $clave = array_keys(self::BLOQUES)[$ultimoCerrado + 1] ?? null;

        return $clave ? self::bloque($clave, $avance) : null;
    }

    /** @return array{clave:string, titulo:string, tramites:array<string, array>, hechos:array<int, string>} */
    public static function bloque(string $clave, ?AvanceTitulacion $avance = null): array
    {
        [$titulo, $tramites] = self::BLOQUES[$clave];
        $config = config('titulacion.tramites', []);

        return [
            'clave' => $clave,
            'titulo' => $titulo,
            'tramites' => collect($tramites)->filter(fn ($t) => isset($config[$t]))
                ->mapWithKeys(fn ($t) => [$t => $config[$t]])->all(),
            'hechos' => self::tramitesHechos($avance),
        ];
    }

    /** Trámites individuales que el sistema ya sabe completados dentro de un tramo en curso. */
    private static function tramitesHechos(?AvanceTitulacion $avance): array
    {
        if (! $avance) {
            return [];
        }
        $h = fn (string $hito) => (bool) ($avance->hitos[$hito]['logrado'] ?? false);
        $tesis = $avance->seguimiento->tesis;
        $hechos = [];

        if ($h('pti_carta')) {
            $hechos[] = 'carta_aplicacion';
        }
        if (filled($tesis?->url_repositorio)) {
            $hechos[] = 'repositorio_tesis';
        }

        return $hechos;
    }

    /** Si cada tramo ya terminó, con lo que registra el sistema. */
    private static function cierres(?AvanceTitulacion $avance): array
    {
        $h = fn (string $hito) => (bool) ($avance?->hitos[$hito]['logrado'] ?? false);
        $pti = $avance?->seguimiento->pti;

        return [
            'pti' => $h('pti_aprobado'),
            // El Informe de TI apto del asesor (link_iti) cierra la revisión.
            'ti_desarrollo' => filled($pti?->link_iti),
            'ti_exposicion' => $h('ti_expuesto'),
            'ti_rd' => $h('ti_aprobado'),
            'ti_repositorio' => $h('ti_repositorio'),
            'ti_bachiller' => $h('bachiller'),
            'tesis_sustentacion' => $h('tesis_sustentada'),
            'tesis_rd' => $h('tesis_aprobada'),
            'tesis_titulo' => $h('licenciado'),
        ];
    }
}
