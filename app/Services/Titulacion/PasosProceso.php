<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\SeguimientoTitulacion;
use Illuminate\Support\Str;

/**
 * Pasos de la línea de tiempo de la ficha del gestor (PTI → TI → Tesis), cada uno con su estado:
 *  - hecho:        registrado.
 *  - actual:       lo que toca hacer ahora (el primero sin hacer después del último hecho).
 *  - espera:       es el actual, pero depende de alguien de fuera (repositorio, SUNEDU).
 *  - pendiente:    viene después del actual.
 *  - bloqueado:    su etapa todavía no se abre (ReglasProceso).
 *  - sin_registro: falta, pero ya hay un paso posterior hecho (dato que no se cargó; no se "debe").
 * Los hitos salen de AvanceTitulacion; los pasos que no son hito (asesores, informe, jurado,
 * programación de la sustentación) se miran en sus datos.
 */
class PasosProceso
{
    /** Etapas: clave => [nombre, hito que la abre (null = siempre), hito que la cierra]. */
    public const ETAPAS = [
        'pti' => ['Plan de Trabajo de Investigación', null, 'pti_aprobado'],
        'ti' => ['Trabajo de Investigación · Bachiller', 'pti_aprobado', 'bachiller'],
        'tesis' => ['Tesis · Licenciatura', 'bachiller', 'licenciado'],
    ];

    /** @var array<string, array{clave:string, etapa:string, titulo:string, estado:string, resumen:?string, ayuda:string, espera:?string}> */
    public readonly array $pasos;

    public readonly ?string $actual;

    public function __construct(public readonly SeguimientoTitulacion $seguimiento, public readonly AvanceTitulacion $avance)
    {
        $h = fn (string $hito) => (bool) ($avance->hitos[$hito]['logrado'] ?? false);
        $pti = $seguimiento->pti;
        $tesis = $seguimiento->tesis;
        $part = $seguimiento->participantes;
        $asesoresTi = $part->where('etapa', 'ti')->where('funcion', 'asesor');
        $juradoTi = $part->where('etapa', 'ti')->where('funcion', 'jurado');
        $actaExpo = $seguimiento->actas->firstWhere('tipo', 'expo');
        $rdNumero = fn (string $hito) => $avance->hitos[$hito]['detalle'] ?? null;
        $fecha = fn (string $hito) => $avance->hitos[$hito]['fecha']?->format('d/m/Y');
        $nombres = fn ($lista) => $lista->map(fn ($p) => $p->asesorNombre())->filter()->implode(', ');
        $asesorPrevio = $pti?->asesorNombre() ?: $tesis?->asesorNombre();
        $rdFechaSust = $seguimiento->resoluciones->where('tipo', 'FechaSustentacion')->first(fn ($r) => filled($r->numero_rd));
        // Jurados de la Tesis: los dictaminantes (revisan la tesis) y el jurado del acto de sustentación.
        $juradoTesis = $part->where('etapa', 'tesis')->where('funcion', 'jurado');
        $dictaminantes = $juradoTesis->filter(fn ($p) => self::esDictaminante($p->cargo));
        $juradoActo = $juradoTesis->reject(fn ($p) => self::esDictaminante($p->cargo));
        $actaTesis = $seguimiento->actas->firstWhere('tipo', 'tesis');

        $definicion = [
            ['pti_datos', 'pti', 'Datos del PTI', $h('pti_inscrito'),
                $pti ? Str::limit((string) $pti->titulo, 60) : null,
                'Registre el título y las fechas del Plan de Trabajo de Investigación.'],
            ['pti_rd', 'pti', 'RD de aprobación del PTI', $h('pti_aprobado'),
                $this->conFecha(Str::limit((string) $rdNumero('pti_aprobado'), 30), $fecha('pti_aprobado')),
                'Genere la RD que aprueba el PTI o adjunte la que se emitió.'],

            ['ti_asesores', 'ti', 'Asesores', $asesoresTi->isNotEmpty() || filled($asesorPrevio),
                $asesoresTi->isNotEmpty() ? $nombres($asesoresTi) : $asesorPrevio,
                'Asigne uno o más asesores al trabajo de investigación.'],
            ['ti_informe', 'ti', 'Informe ITI del asesor', filled($pti?->link_iti),
                filled($pti?->link_iti) ? 'Informe registrado' : null,
                'Registre el enlace al informe del asesor (Drive u otro).'],
            ['ti_jurado', 'ti', 'Jurado', $juradoTi->isNotEmpty() || (bool) $actaExpo?->jurados?->isNotEmpty(),
                $juradoTi->isNotEmpty() ? $nombres($juradoTi) : null,
                'Designe a los docentes del jurado de la exposición.'],
            ['ti_expo', 'ti', 'Fecha de exposición', $h('ti_expuesto'),
                $fecha('ti_expuesto'),
                'Registre la fecha en que el grupo expone su trabajo.'],
            ['ti_rd', 'ti', 'RD de aprobación de exposición', $h('ti_aprobado'),
                $this->conFecha(Str::limit((string) $rdNumero('ti_aprobado'), 30), $fecha('ti_aprobado')),
                'Genere la RD que aprueba la exposición o adjunte la que se emitió.'],
            ['ti_repo', 'ti', 'Repositorio Institucional', $h('ti_repositorio'),
                $h('ti_repositorio') ? 'Publicado' : null,
                'Cuando el trabajo esté publicado en el repositorio, registre su enlace.'],
            ['ti_bachiller', 'ti', 'Bachiller en SUNEDU', $h('bachiller'),
                $this->conFecha($tesis?->bachiller_sunedu ? 'N° '.$tesis->bachiller_sunedu : null, $fecha('bachiller')),
                'Registre la inscripción del grado de Bachiller en SUNEDU.'],

            // Tesis · Licenciatura (orden de la Unidad de Investigación, 23/10).
            ['tesis_dictamen', 'tesis', 'Enlaces de los dictaminantes', ! empty($tesis?->enlaces_dictamen),
                ! empty($tesis?->enlaces_dictamen) ? count($tesis->enlaces_dictamen).' enlace(s)' : null,
                'Registre el enlace de cada dictamen (Drive u otro); puede agregar más de uno.'],
            ['tesis_dictaminantes', 'tesis', 'Dictaminantes', $dictaminantes->isNotEmpty(),
                $dictaminantes->isNotEmpty() ? $nombres($dictaminantes) : null,
                'Agregue a los docentes que dictaminan la tesis.'],
            ['tesis_fecha', 'tesis', 'Fecha de sustentación', $h('tesis_sustentada'),
                $this->conFecha($fecha('tesis_sustentada'), $rdFechaSust ? 'RD '.Str::limit((string) $rdFechaSust->numero_rd, 20) : null),
                'Registre la fecha de la sustentación y genere o adjunte la RD que la programa.'],
            ['tesis_jurado', 'tesis', 'Jurado de la sustentación', $juradoActo->isNotEmpty() || (bool) $actaTesis?->jurados?->isNotEmpty(),
                $juradoActo->isNotEmpty() ? $nombres($juradoActo) : null,
                'Agregue al jurado que preside la sustentación (Presidente, Secretario, Vocal…).'],
            // El enlace lo comparten los estudiantes con el jurado ANTES de sustentar.
            ['tesis_enlace', 'tesis', 'Enlace de la Tesis', filled($tesis?->link_tesis),
                filled($tesis?->link_tesis) ? 'Enlace registrado' : null,
                'Registre el enlace de la tesis que los estudiantes comparten con el jurado antes de sustentar.'],
            ['tesis_rd', 'tesis', 'RD de aprobación de la Tesis', $h('tesis_aprobada'),
                $this->conFecha(Str::limit((string) $rdNumero('tesis_aprobada'), 30), $fecha('tesis_aprobada')),
                'Genere la RD que aprueba la sustentación o adjunte la que se emitió.'],
            ['tesis_situacion', 'tesis', 'Situación de la Tesis', in_array($tesis?->estado, ['en_repositorio', 'no_solicito', 'no_se_presenta', 'aprobado'], true),
                \App\Models\Titulacion\Tesis::SITUACIONES[$tesis?->estado ?? ''] ?? null,
                'Indique si la tesis está en el repositorio, pendiente, no lo solicitó o no se presenta.'],
            ['tesis_repo', 'tesis', 'Repositorio Institucional', filled($tesis?->url_repositorio),
                filled($tesis?->url_repositorio) ? 'Publicada' : null,
                'Cuando la tesis esté publicada en el repositorio, registre su enlace.'],
            ['tesis_escuela', 'tesis', 'Licenciatura aceptada por la Escuela', (bool) $tesis?->licenciatura_escuela,
                $tesis?->licenciatura_escuela ? 'Aceptada'.($tesis->fecha_licenciatura_escuela ? ' · '.$tesis->fecha_licenciatura_escuela->format('d/m/Y') : '') : null,
                'Marque cuando la Escuela acepte el grado de Licenciatura.'],
            ['tesis_licenciado', 'tesis', 'Licenciado en SUNEDU', $h('licenciado'),
                $this->conFecha($tesis?->titulo_sunedu && $tesis->titulo_sunedu !== 'Sí' ? 'N° '.$tesis->titulo_sunedu : ($h('licenciado') ? 'Sí' : null), $fecha('licenciado')),
                'Indique si la Licenciatura ya está inscrita en SUNEDU.'],
        ];

        $ultimoHecho = -1;
        foreach ($definicion as $i => $d) {
            if ($d[3]) {
                $ultimoHecho = $i;
            }
        }

        $pasos = [];
        $actual = null;
        foreach ($definicion as $i => [$clave, $etapa, $titulo, $hecho, $resumen, $ayuda]) {
            $abre = self::ETAPAS[$etapa][1];
            $estado = match (true) {
                $hecho => 'hecho',
                $i < $ultimoHecho => 'sin_registro',
                $abre && ! $h($abre) => 'bloqueado',
                $actual === null => 'actual',
                default => 'pendiente',
            };
            if ($estado === 'actual') {
                $actual = $clave;
            }
            $pasos[$clave] = [
                'clave' => $clave, 'etapa' => $etapa, 'titulo' => $titulo, 'estado' => $estado,
                'resumen' => filled($resumen) ? $resumen : null, 'ayuda' => $ayuda, 'espera' => null,
            ];
        }

        // Pasos que dependen de terceros: siguen siendo "lo que toca", pero se muestran en espera.
        if ($actual === 'ti_repo') {
            $pasos['ti_repo']['estado'] = 'espera';
            $pasos['ti_repo']['espera'] = 'Pendiente de subir al Repositorio Institucional';
        } elseif ($actual === 'ti_bachiller' && $h('ti_repositorio')) {
            $pasos['ti_bachiller']['estado'] = 'espera';
            $pasos['ti_bachiller']['espera'] = 'A la espera de SUNEDU';
        } elseif ($actual === 'tesis_licenciado') {
            $pasos['tesis_licenciado']['estado'] = 'espera';
            $pasos['tesis_licenciado']['espera'] = 'A la espera de SUNEDU';
        }

        $this->pasos = $pasos;
        $this->actual = $actual;
    }

    /** Cargo de un jurado de la Tesis que lo marca como dictaminante (no como jurado del acto). */
    public static function esDictaminante(?string $cargo): bool
    {
        return str_starts_with(mb_strtolower(trim((string) $cargo)), 'dictaminante');
    }

    /** Estado de una etapa para la cabecera de su grupo en la línea: completa | en_curso | bloqueada. */
    public function estadoEtapa(string $etapa): string
    {
        [, $abre, $cierra] = self::ETAPAS[$etapa];
        $h = fn ($hito) => (bool) ($this->avance->hitos[$hito]['logrado'] ?? false);

        return match (true) {
            $h($cierra) => 'completa',
            $abre && ! $h($abre) => 'bloqueada',
            default => 'en_curso',
        };
    }

    /** Pasos de una etapa, en orden. */
    public function deEtapa(string $etapa): array
    {
        return array_values(array_filter($this->pasos, fn ($p) => $p['etapa'] === $etapa));
    }

    /** "2 de 7" dentro de su etapa. */
    public function posicion(string $clave): string
    {
        $etapa = $this->pasos[$clave]['etapa'];
        $lista = array_column($this->deEtapa($etapa), 'clave');

        return (array_search($clave, $lista, true) + 1).' de '.count($lista);
    }

    /** Porcentaje del proceso completo (pasos hechos o superados). */
    public function porcentaje(): int
    {
        $total = count($this->pasos);
        $hechos = count(array_filter($this->pasos, fn ($p) => in_array($p['estado'], ['hecho', 'sin_registro'], true)));

        return $total ? (int) round($hechos * 100 / $total) : 0;
    }

    private function conFecha(?string $texto, ?string $fecha): ?string
    {
        return collect([$texto, $fecha])->filter(fn ($v) => filled($v))->implode(' · ') ?: null;
    }
}
