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
    /** Tramo => [título, trámites]. El orden es el del proceso. */
    public const BLOQUES = [
        'pti' => ['Aprobación de tu PTI', ['pti_aprobacion']],
        'ti_desarrollo' => ['Desarrollo y revisión de tu TI', ['carta_aplicacion', 'turnitin', 'revision_asesor']],
        'ti_exposicion' => ['Exposición de tu TI', ['fecha_exposicion']],
        'ti_rd' => ['Aprobación de tu TI', ['rd_ti']],
        'ti_repositorio' => ['Expediente y repositorio', ['expediente', 'repositorio']],
        'ti_bachiller' => ['Diploma y grado de Bachiller', ['diploma', 'sunedu_bachiller']],
    ];

    /**
     * Tramo en curso con sus trámites, o null si no hay ayuda para lo que sigue (p. ej. la
     * etapa de Tesis, todavía sin publicar). Sin $avance: alumno sin proceso iniciado (PTI).
     *
     * @return array{clave:string, titulo:string, tramites:array<string, array>}|null
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

        return $clave ? self::bloque($clave) : null;
    }

    /** @return array{clave:string, titulo:string, tramites:array<string, array>} */
    public static function bloque(string $clave): array
    {
        [$titulo, $tramites] = self::BLOQUES[$clave];
        $config = config('titulacion.tramites', []);

        return [
            'clave' => $clave,
            'titulo' => $titulo,
            'tramites' => collect($tramites)->filter(fn ($t) => isset($config[$t]))
                ->mapWithKeys(fn ($t) => [$t => $config[$t]])->all(),
        ];
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
        ];
    }
}
