<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\SeguimientoTitulacion;
use Illuminate\Support\Carbon;

/**
 * Avance real del proceso de titulación, calculado SOLO a partir de los datos
 * registrados (nunca de un select manual). Reglas de la institución:
 *  - Toda RD (resolución) equivale a aprobación de esa etapa.
 *  - Los grados (Bachiller y Licenciado) cuentan desde su inscripción en SUNEDU.
 *  - FID y PPD siguen el mismo camino: PTI → TI (Bachiller) → Tesis (Licenciatura).
 *
 * Una RD se reconoce tanto en su campo propio (pti.rd_pti, tesis.rd_ti,
 * tesis.rd_tesis) como en la lista de Resoluciones, porque el importador y el
 * panel las cargan por ambos lados.
 *
 * Espera $seguimiento con pti, tesis, actas y resoluciones cargados (si no,
 * Eloquent los carga solos, con una consulta por relación).
 */
class AvanceTitulacion
{
    /**
     * Estados (clave => nombre visible), en orden. La clave es la que se guarda
     * en estados_titulacion.clave; el estado de cada seguimiento es el hito
     * "de estado" más alto alcanzado.
     */
    public const ESTADOS = [
        'sin_pti' => 'Sin PTI',
        'pti_en_proceso' => 'PTI en proceso',
        'pti_aprobado' => 'PTI aprobado',
        'ti_aprobado' => 'TI aprobado',
        'bachiller' => 'Bachiller',
        'tesis_aprobada' => 'Tesis aprobada',
        'licenciado' => 'Licenciado',
    ];

    /** Color (badge de Bootstrap) de cada estado; los grados resaltan. */
    public static function badge(?string $estadoClave): string
    {
        return match ($estadoClave) {
            'licenciado' => 'success',
            'bachiller' => 'info',
            'pti_aprobado', 'ti_aprobado', 'tesis_aprobada' => 'primary',
            'pti_en_proceso' => 'warning',
            default => 'secondary',
        };
    }

    /** Hito que define cada estado (los demás hitos son intermedios, solo informativos). */
    private const HITO_DE_ESTADO = [
        'licenciado' => 'licenciado',
        'tesis_aprobada' => 'tesis_aprobada',
        'bachiller' => 'bachiller',
        'ti_aprobado' => 'ti_aprobado',
        'pti_aprobado' => 'pti_aprobado',
        'pti_en_proceso' => 'pti_inscrito',
    ];

    /** @var array<string, array{clave:string, etapa:string, label:string, logrado:bool, fecha:?Carbon, detalle:?string, falta:string, enlace:?string}> */
    public readonly array $hitos;

    public function __construct(public readonly SeguimientoTitulacion $seguimiento)
    {
        // Los hitos de la etapa PTI miran el último PTI de Bachiller: si vence
        // sin TI se registra otro y el proceso vuelve a empezar; un PTI de
        // Licenciatura (vence después del TI) no borra lo ya logrado.
        $ptis = $seguimiento->relationLoaded('ptis') ? $seguimiento->ptis : collect(array_filter([$seguimiento->pti]));
        $ptisBachiller = $ptis->where('etapa', '!=', 'licenciatura');
        $pti = $ptisBachiller->sortBy('numero')->last() ?? $seguimiento->pti;
        $tesis = $seguimiento->tesis;
        // Si hay varias RD del tipo, primero la que tiene su PDF enlazado.
        $rd = fn (string $tipo) => $seguimiento->resoluciones
            ->where('tipo', $tipo)
            ->sortByDesc(fn ($r) => filled($r->archivo))
            ->first(fn ($r) => filled($r->numero_rd));
        $acta = fn (string $tipo) => $seguimiento->actas->firstWhere('tipo', $tipo);

        $rdPti = $rd('PTI');
        if ($ptisBachiller->count() > 1) {
            // Con un PTI nuevo, las RD de PTI anteriores ya no lo aprueban:
            // solo vale la que coincide con la RD de este PTI (para su PDF).
            $rdPti = filled($pti?->rd_pti)
                ? $seguimiento->resoluciones->where('tipo', 'PTI')->first(fn ($r) => trim((string) $r->numero_rd) === trim($pti->rd_pti))
                : null;
        }
        $rdTi = $rd('Exposicion');
        $rdTesis = $rd('Tesis');
        $actaExpo = $acta('expo');
        $actaTesis = $acta('tesis');

        $hitos = [
            $this->hito('pti_inscrito', 'pti', 'PTI inscrito',
                // Un vencimiento registrado ("FV julio 2026") también prueba que el PTI se inscribió.
                (bool) ($pti && ($pti->fecha_pti || $pti->fecha_vencimiento || filled($pti->titulo) || filled($pti->rd_pti))),
                $pti?->fecha_pti, null,
                'Inscribir el Plan de Trabajo de Investigación (PTI).'),
            $this->hito('pti_aprobado', 'pti', 'PTI aprobado (RD)',
                filled($pti?->rd_pti) || $rdPti,
                $rdPti?->fecha, $pti?->rd_pti ?: $rdPti?->numero_rd,
                'Obtener la RD que aprueba el PTI.'),
            $this->hito('ti_expuesto', 'ti', 'TI expuesto',
                (bool) ($tesis?->fecha_exposicion || $actaExpo),
                $tesis?->fecha_exposicion ?? $actaExpo?->fecha, $actaExpo?->numero_acta ? 'Acta '.$actaExpo->numero_acta : null,
                'Exponer el Trabajo de Investigación (TI).'),
            $this->hito('ti_aprobado', 'ti', 'TI aprobado (RD)',
                filled($tesis?->rd_ti) || $rdTi,
                $rdTi?->fecha, $tesis?->rd_ti ?: $rdTi?->numero_rd,
                'Obtener la RD que aprueba el TI.'),
            // Desde el 19/10: el TI aprobado se sube al Repositorio Institucional antes de
            // inscribir el Bachiller en SUNEDU.
            $this->hito('ti_repositorio', 'ti', 'TI en Repositorio Institucional',
                filled($tesis?->url_repositorio_ti),
                null, null,
                'Subir el TI al Repositorio Institucional y registrar su enlace.'),
            $this->hito('bachiller', 'ti', 'Bachiller (SUNEDU)',
                (bool) ($tesis?->fecha_bachiller_sunedu || filled($tesis?->bachiller_sunedu)),
                $tesis?->fecha_bachiller_sunedu, $tesis?->bachiller_sunedu,
                'Inscribir el grado de Bachiller en SUNEDU.'),
            $this->hito('tesis_sustentada', 'tesis', 'Tesis sustentada',
                (bool) ($tesis?->fecha_sustentacion || $actaTesis),
                $tesis?->fecha_sustentacion ?? $actaTesis?->fecha, $actaTesis?->numero_acta ? 'Acta '.$actaTesis->numero_acta : null,
                'Sustentar la Tesis.'),
            $this->hito('tesis_aprobada', 'tesis', 'Tesis aprobada (RD)',
                filled($tesis?->rd_tesis) || $rdTesis,
                $rdTesis?->fecha, $tesis?->rd_tesis ?: $rdTesis?->numero_rd,
                'Obtener la RD que aprueba la Tesis.'),
            $this->hito('licenciado', 'tesis', 'Licenciado (SUNEDU)',
                (bool) ($tesis?->fecha_titulo_sunedu || filled($tesis?->titulo_sunedu)),
                $tesis?->fecha_titulo_sunedu, $tesis?->titulo_sunedu,
                'Inscribir el título de Licenciado en SUNEDU.'),
        ];

        // Enlace al PDF de la RD (titulacion_resoluciones.archivo), si se tiene.
        $enlaces = ['pti_aprobado' => $rdPti?->archivo, 'ti_aprobado' => $rdTi?->archivo, 'ti_repositorio' => $tesis?->url_repositorio_ti, 'tesis_aprobada' => $rdTesis?->archivo];

        $this->hitos = collect($hitos)
            ->map(fn ($h) => $h + ['enlace' => filled($enlaces[$h['clave']] ?? null) ? $enlaces[$h['clave']] : null])
            ->keyBy('clave')->all();
    }

    private function hito(string $clave, string $etapa, string $label, $logrado, $fecha, ?string $detalle, string $falta): array
    {
        return [
            'clave' => $clave,
            'etapa' => $etapa,
            'label' => $label,
            'logrado' => (bool) $logrado,
            'fecha' => $fecha instanceof Carbon ? $fecha : null,
            'detalle' => filled($detalle) ? trim($detalle) : null,
            'falta' => $falta,
        ];
    }

    /** Clave del estado actual (ver ESTADOS). */
    public function estadoClave(): string
    {
        foreach (self::HITO_DE_ESTADO as $estado => $hito) {
            if ($this->hitos[$hito]['logrado']) {
                return $estado;
            }
        }

        return 'sin_pti';
    }

    public function estadoNombre(): string
    {
        return self::ESTADOS[$this->estadoClave()];
    }

    /**
     * Estado de cada etapa para el indicador PTI → TI → Tesis:
     * 'completo' (llegó a su RD / grado), 'en_proceso' (tiene algún hito o es la
     * siguiente por hacer) o 'pendiente'.
     */
    public function etapas(): array
    {
        $cierre = ['pti' => 'pti_aprobado', 'ti' => 'bachiller', 'tesis' => 'licenciado'];
        $resultado = [];
        $anteriorCompleta = true;

        foreach ($cierre as $etapa => $hitoCierre) {
            $hitosEtapa = collect($this->hitos)->where('etapa', $etapa);
            $estado = match (true) {
                $this->hitos[$hitoCierre]['logrado'] => 'completo',
                $hitosEtapa->contains('logrado', true) || $anteriorCompleta => 'en_proceso',
                default => 'pendiente',
            };
            $ultimo = $hitosEtapa->where('logrado', true)->last();
            $resultado[$etapa] = [
                'estado' => $estado,
                'hitos' => $hitosEtapa->values()->all(),
                // Resumen corto bajo cada paso del indicador.
                'resumen' => $estado === 'completo'
                    ? $this->hitos[$hitoCierre]['label']
                    : ($ultimo['label'] ?? ($estado === 'en_proceso' ? 'Por iniciar' : null)),
            ];
            $anteriorCompleta = $estado === 'completo';
        }

        return $resultado;
    }

    /** Nombre largo de cada etapa (línea de tiempo del alumno). */
    public const ETAPAS = [
        'pti' => 'Plan de Trabajo de Investigación (PTI)',
        'ti' => 'Trabajo de Investigación (TI) · Grado de Bachiller',
        'tesis' => 'Tesis · Título de Licenciado',
    ];

    /**
     * Etapas con sus hitos para la línea de tiempo. Cada hito trae su
     * 'situacion': 'logrado'; 'actual' (el siguiente paso); 'pendiente'; o
     * 'sin_registro' cuando falta pero un hito posterior ya se logró (p. ej.
     * no se registró la fecha de exposición pero ya hay RD de TI): no es algo
     * que la persona deba, solo un dato que no se cargó.
     */
    public function lineaDeTiempo(): array
    {
        $claves = array_keys($this->hitos);
        $ultimoLogrado = -1;
        foreach ($claves as $i => $clave) {
            if ($this->hitos[$clave]['logrado']) {
                $ultimoLogrado = $i;
            }
        }
        $actual = $this->siguientePaso()['clave'] ?? null;

        $etapas = $this->etapas();
        foreach ($etapas as $etapa => &$datos) {
            $datos['titulo'] = self::ETAPAS[$etapa];
            $datos['hitos'] = array_map(function ($hito) use ($claves, $ultimoLogrado, $actual) {
                $hito['situacion'] = match (true) {
                    $hito['logrado'] => 'logrado',
                    array_search($hito['clave'], $claves) < $ultimoLogrado => 'sin_registro',
                    $hito['clave'] === $actual => 'actual',
                    default => 'pendiente',
                };

                return $hito;
            }, $datos['hitos']);
        }

        return $etapas;
    }

    /**
     * Primer hito pendiente en el orden del proceso (lo que toca hacer ahora).
     * Si un hito posterior ya está logrado, los anteriores faltantes se omiten:
     * p. ej. quien ya tiene RD de Tesis no "debe" una fecha de exposición
     * que simplemente no se registró.
     */
    public function siguientePaso(): ?array
    {
        $siguiente = null;
        foreach (array_reverse($this->hitos) as $hito) {
            if ($hito['logrado']) {
                break;
            }
            $siguiente = $hito;
        }

        return $siguiente;
    }
}
