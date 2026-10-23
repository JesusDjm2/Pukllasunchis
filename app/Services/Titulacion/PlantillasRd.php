<?php

namespace App\Services\Titulacion;

use App\Models\Programa;
use App\Models\Titulacion\Resolucion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Resoluciones Directorales que el sistema genera en PDF (modelos oficiales de la institución).
 * Cada plantilla dice qué tipo de RD registra (titulacion_resoluciones.tipo), a qué etapa
 * pertenece, qué vista la dibuja, qué datos propios pide (ver CAMPOS), sus iniciales al pie y
 * su cierre. Una plantilla sin 'vista' todavía no tiene su modelo: aparece en la ficha, pero
 * deshabilitada ("falta el modelo").
 */
class PlantillasRd
{
    private const INICIALES_INVESTIGACION = ['Dir-RSS', 'CI. CAGZ.', 'Sac.Adm/MLV'];

    private const INICIALES_ACADEMICAS = ['DG/RSS', 'JUI/CAGZ', 'Sec.Adm/MLV'];

    public const PLANTILLAS = [
        'aprobacion_pti' => [
            'titulo' => 'Aprobación de PTI', 'tipo' => 'PTI', 'etapa' => 'pti', 'vista' => 'titulacion.rd.pdf.aprobacion-pti',
            'campos' => ['expedientes', 'numero_informe', 'titulo', 'texto_adicional'], // sin asesor: se designa en el TI
            'iniciales' => self::INICIALES_INVESTIGACION, 'cierre' => 'formal',
        ],
        'anulacion_pti' => [
            'titulo' => 'Anulación de PTI', 'tipo' => 'AnulacionPti', 'etapa' => 'otros', 'vista' => 'titulacion.rd.pdf.anulacion-pti',
            'campos' => ['rd_referida' => 'RD que se deja sin efecto', 'titulo'],
            'iniciales' => self::INICIALES_INVESTIGACION, 'cierre' => 'simple',
        ],
        'aprobacion_exposicion' => [
            'titulo' => 'Aprobación de Exposición', 'tipo' => 'Exposicion', 'etapa' => 'ti', 'vista' => 'titulacion.rd.pdf.aprobacion-exposicion',
            'campos' => ['expedientes', 'titulo', 'acta' => 'N° de página del acta de exposición', 'fecha_acto' => 'Fecha de la exposición del TI'],
            'iniciales' => self::INICIALES_ACADEMICAS, 'cierre' => 'simple',
        ],
        'fecha_sustentacion' => [
            'titulo' => 'Fecha de sustentación de Tesis', 'tipo' => 'FechaSustentacion', 'etapa' => 'tesis', 'vista' => 'titulacion.rd.pdf.fecha-sustentacion',
            'campos' => ['expedientes', 'titulo', 'fecha_acto' => 'Fecha de la sustentación', 'hora', 'lugar', 'jurado'],
            'iniciales' => self::INICIALES_ACADEMICAS, 'cierre' => 'mayusculas',
        ],
        'aprobacion_sustentacion' => [
            'titulo' => 'Aprobación de sustentación', 'tipo' => 'Tesis', 'etapa' => 'tesis', 'vista' => 'titulacion.rd.pdf.aprobacion-sustentacion',
            'campos' => ['expedientes', 'titulo', 'rd_referida' => 'RD de designación del jurado', 'acta' => 'N° de página del acta de sustentación', 'fecha_acto' => 'Fecha de la sustentación'],
            'iniciales' => self::INICIALES_ACADEMICAS, 'cierre' => 'simple_coma',
        ],
        'cambio_tema' => [
            'titulo' => 'Cambio de tema de investigación', 'tipo' => 'CambioTema', 'etapa' => 'otros', 'vista' => 'titulacion.rd.pdf.cambio-tema',
            'campos' => ['expedientes', 'rd_referida' => 'RD que aprobó el PTI anterior', 'titulo_nuevo'],
            'iniciales' => self::INICIALES_INVESTIGACION, 'cierre' => 'simple',
        ],
        'cambio_asesor' => [
            'titulo' => 'Cambio de asesor', 'tipo' => 'CambioAsesor', 'etapa' => 'otros', 'vista' => 'titulacion.rd.pdf.cambio-asesor',
            'campos' => ['expedientes', 'rd_referida' => 'RD que aprobó el PTI (se modifica)', 'fecha_rd_referida', 'titulo',
                'asesor_anterior', 'asesor_anterior_cargo', 'asesor' => 'Nuevo asesor(a)', 'acompanante'],
            'iniciales' => self::INICIALES_INVESTIGACION, 'cierre' => 'simple',
        ],
    ];

    /** Campos que una plantilla puede pedir: [etiqueta, tipo, obligatorio]. */
    public const CAMPOS = [
        'expedientes' => ['Expediente(s)', 'texto', false],
        'numero_informe' => ['N° de informe de la Coordinación', 'numero', true],
        'titulo' => ['Título del trabajo de investigación', 'area', true],
        'titulo_nuevo' => ['Nuevo título (lo aprueba esta RD)', 'area', true],
        'asesor' => ['Asesor(a)', 'asesor', true],
        'texto_adicional' => ['Texto adicional en el artículo PRIMERO (opcional)', 'area', false],
        'rd_referida' => ['RD referida', 'texto', true],
        'fecha_rd_referida' => ['Fecha de esa RD', 'fecha', true],
        'asesor_anterior' => ['Asesor(a) anterior (como irá en la RD)', 'texto', true],
        'asesor_anterior_cargo' => ['Era', 'cargo', true],
        'acompanante' => ['Asesor(a) acompañante (opcional: va al final del artículo SEGUNDO)', 'texto', false],
        'acta' => ['N° de página del acta', 'texto', true],
        'fecha_acto' => ['Fecha', 'fecha', true],
        'hora' => ['Hora', 'hora', true],
        'lugar' => ['Lugar', 'texto', true],
        'jurado' => ['Jurado evaluador (un integrante por línea: «Cargo: Nombre»)', 'area', true],
    ];

    /** Quiénes son los integrantes, para concordar artículos y palabras en la RD. */
    public const GENEROS = [
        'f1' => 'Una mujer',
        'm1' => 'Un hombre',
        'fp' => 'Varias mujeres',
        'mp' => 'Varios (hombres o grupo mixto)',
    ];

    /** Lema oficial del año (encabezado de toda RD). Si falta el año, se usa el último conocido. */
    public const LEMAS = [
        2026 => 'Año de la Esperanza y el Fortalecimiento de la Democracia',
    ];

    public static function existe(string $clave): bool
    {
        return isset(self::PLANTILLAS[$clave]);
    }

    public static function disponible(string $clave): bool
    {
        return filled(self::PLANTILLAS[$clave]['vista'] ?? null);
    }

    /**
     * Campos propios de una plantilla con su etiqueta final.
     *
     * @return array<string, array{0:string,1:string,2:bool}>
     */
    public static function campos(string $clave): array
    {
        $campos = [];
        foreach (self::PLANTILLAS[$clave]['campos'] as $k => $v) {
            [$campo, $etiqueta] = is_int($k) ? [$v, null] : [$k, $v];
            $def = self::CAMPOS[$campo];
            $campos[$campo] = [$etiqueta ?? $def[0], $def[1], $def[2]];
        }

        return $campos;
    }

    public static function lema(int $anio): string
    {
        $lemas = self::LEMAS;

        return $lemas[$anio] ?? end($lemas);
    }

    /**
     * Siguiente N° de RD sugerido: el mayor correlativo de las RD de Titulación de ese año + 1.
     * Dirección también emite RD de otros temas que el sistema no conoce: es solo una sugerencia.
     */
    public static function numeroSugerido(int $anio): string
    {
        $max = Resolucion::query()
            ->where(fn ($q) => $q->whereYear('fecha', $anio)->orWhere('numero_rd', 'like', "%-{$anio}%"))
            ->pluck('numero_rd')
            ->map(fn ($n) => preg_match('/(\d{1,4})\s*-\s*'.$anio.'/', (string) $n, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    /** "Educación Primaria Intercultural Bilingüe", como va en la RD. */
    public static function programaOficial(?Programa $programa): string
    {
        if (! $programa) {
            return '';
        }
        $nivel = $programa->nivel instanceof \App\Enums\NivelPrograma ? $programa->nivel->etiqueta() : null;
        if (! $nivel) {
            return trim(preg_replace('/\b(Programa|PPD|EIB)\b/i', '', $programa->nombre));
        }

        return 'Educación '.$nivel.($programa->eib ? ' Intercultural Bilingüe' : '');
    }

    /**
     * Género y número de los integrantes según su ficha: 'f1', 'm1', 'fp' o 'mp'.
     *
     * @param  Collection<int, User>  $integrantes
     */
    public static function generoSugerido(Collection $integrantes): string
    {
        $todasMujeres = $integrantes->every(function (User $u) {
            $g = mb_strtolower((string) ($u->alumno?->genero ?? $u->alumnoB?->genero ?? $u->genero));

            return str_starts_with($g, 'f');
        });

        return ($todasMujeres ? 'f' : 'm').($integrantes->count() > 1 ? 'p' : '1');
    }

    /** Palabras que concuerdan con los integrantes ('f1' | 'm1' | 'fp' | 'mp'). */
    public static function gramatica(string $genero): array
    {
        $plural = ($genero[1] ?? '1') === 'p';
        $fem = ($genero[0] ?? 'm') === 'f';
        $elegir = fn ($fs, $ms, $fp, $mp) => $plural ? ($fem ? $fp : $mp) : ($fem ? $fs : $ms);

        return [
            'plural' => $plural,
            'art' => $elegir('la', 'el', 'las', 'los'),
            'a_art' => $elegir('a la', 'al', 'a las', 'a los'),
            'estudiante' => $plural ? 'estudiantes' : 'estudiante',
            'egresado' => $elegir('egresada', 'egresado', 'egresadas', 'egresados'),
            'bachiller' => $plural ? 'bachilleres' : 'bachiller',
            'licenciado' => $elegir('Licenciada', 'Licenciado', 'Licenciadas', 'Licenciados'),
            'apto' => $elegir('APTA', 'APTO', 'APTAS', 'APTOS'),
            'interesado' => $elegir('la interesada', 'el interesado', 'las interesadas', 'los interesados'),
            'quien' => $plural ? 'quienes' : 'quien',
            'n' => $plural ? 'n' : '',
            'ha' => $plural ? 'han' : 'ha',
        ];
    }

    /** "12 de marzo" (la RD agrega "del 2026"). */
    public static function fechaLarga(Carbon $fecha): string
    {
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        return $fecha->day.' de '.$meses[$fecha->month - 1];
    }

    /** "Ana Pérez Rojas, Luis Quispe Mamani y Rosa Huamán" */
    public static function listaNombres(Collection $nombres): string
    {
        $nombres = $nombres->filter()->values();
        if ($nombres->count() <= 1) {
            return (string) $nombres->first();
        }

        return $nombres->slice(0, -1)->implode(', ').' y '.$nombres->last();
    }
}
