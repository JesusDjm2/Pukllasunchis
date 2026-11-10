<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * «Carta de Presentación para aplicación» que Secretaría envía al aprobar una solicitud de carta de
 * aceptación. Se llena la plantilla Word de Secretaría (resources/plantillas/titulacion/carta-presentacion.docx:
 * su documento original, con los campos de combinación convertidos en marcas [[Campo]]), así conserva
 * membrete, firma y formato. Junto a ella va el modelo de Carta de Aceptación, que llena la IE.
 */
class CartaPresentacion
{
    public const PLANTILLA = 'plantillas/titulacion/carta-presentacion.docx';

    public const MODELO_ACEPTACION = 'plantillas/titulacion/modelo-carta-aceptacion.docx';

    public const TIPO_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    /** Integrantes del grupo de la solicitud, ordenados por apellidos. */
    public static function integrantes(CartaAceptacion $carta): Collection
    {
        return User::whereIn('id', $carta->delLote()->with('seguimiento')->get()->pluck('seguimiento.user_id')->filter())
            ->with(['programa', 'alumno:id,user_id,genero', 'alumnoB:id,user_id,genero'])
            ->orderBy('apellidos')->orderBy('name')->get();
    }

    /** Lo que Secretaría revisa antes de enviar, ya rellenado. */
    public static function sugeridos(CartaAceptacion $carta): array
    {
        $integrantes = self::integrantes($carta);

        return [
            'numero_carta' => self::numero((string) self::siguienteNumero((int) now()->year)),
            'fecha_carta' => now()->toDateString(),
            'destinatario_genero' => '',
            'destinatario' => $carta->director,
            'art_ie' => 'de la',
            'institucion_carta' => $carta->institucion,
            'periodo' => (string) now()->year,
            'genero' => PlantillasRd::generoSugerido($integrantes),
            'titulo_carta' => $carta->titulo,
        ];
    }

    /** Siguiente N° de carta del año (las ya enviadas por el sistema). */
    public static function siguienteNumero(int $anio): int
    {
        return (int) CartaAceptacion::whereNotNull('numero_carta')->whereYear('atendido_en', $anio)
            ->get(['numero_carta'])->map(fn ($c) => (int) $c->numero_carta)->max() + 1;
    }

    /** Genera el .docx lleno en un archivo temporal y devuelve su ruta. */
    public static function generar(CartaAceptacion $carta, array $d): string
    {
        $integrantes = self::integrantes($carta);
        $g = PlantillasRd::gramatica($d['genero']);
        $fem = in_array($g['art'], ['la', 'las'], true);
        $fecha = Carbon::parse($d['fecha_carta']);
        $directora = $d['destinatario_genero'] === 'f';
        $programa = $integrantes->first()?->programa;
        $nombrePrograma = $programa ? (PlantillasRd::programaOficial($programa) ?: $programa->nombre) : $carta->programa_estudios;
        if ($programa?->esPpd() && ! Str::contains(Str::lower($nombrePrograma), 'profesionaliz')) {
            $nombrePrograma = 'Profesionalización Docente en '.$nombrePrograma;
        }
        $nombres = PlantillasRd::listaNombres($integrantes->map(fn (User $u) => mb_convert_case(trim($u->name.' '.$u->apellidos), MB_CASE_TITLE)));

        $valores = [
            // La plantilla ya pone un «0» delante: 1 → «001», 45 → «045» (como las RD).
            'N_Carta' => self::numero($d['numero_carta']),
            'Anio' => (string) $fecha->year,
            'Fecha_de_oficio_' => PlantillasRd::fechaLarga($fecha).' del '.$fecha->year,
            'Señora' => $directora ? 'Señora' : 'Señor',
            'Nombre_Destinatario' => trim($d['destinatario']),
            'Cargo_DirProf' => $directora ? 'Directora' : 'Director',
            'Art_de_la_IE' => trim($d['art_ie']),
            'Institución' => trim($d['institucion_carta']),
            'laslosella' => $g['art'],
            'estudiantes' => $g['estudiante'],
            'Nombres_Estudiantes' => $nombres ?: $carta->grupo,
            'Quienquienes' => $g['quien'],
            'se_encuentra_encuentran_cursando' => $g['plural'] ? 'se encuentran cursando' : 'se encuentra cursando',
            'Programa_de_E' => $nombrePrograma,
            'Periodo_20' => trim($d['periodo']),
            'mensionadasos' => ($fem ? 'mencionada' : 'mencionado').($g['plural'] ? 's' : ''),
            'puedanpueda' => $g['plural'] ? 'puedan' : 'pueda',
            'Título_de_Investigación' => trim($d['titulo_carta'], " \"“”"),
        ];

        $ruta = tempnam(sys_get_temp_dir(), 'carta').'.docx';
        copy(resource_path(self::PLANTILLA), $ruta);
        $zip = new ZipArchive;
        if ($zip->open($ruta) !== true) {
            throw new \RuntimeException('No se pudo abrir la plantilla de la Carta de Presentación.');
        }
        $xml = $zip->getFromName('word/document.xml');
        foreach ($valores as $campo => $valor) {
            $xml = str_replace('[['.$campo.']]', htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $xml);
        }
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return $ruta;
    }

    /** Número sin el «0» inicial de la plantilla, con al menos 2 cifras: 1 → «01» (se ve «001»). */
    public static function numero(?string $numero): string
    {
        return str_pad(ltrim((string) $numero, '0') ?: '0', 2, '0', STR_PAD_LEFT);
    }

    /** Nombre del archivo para el estudiante: «Carta de Presentación N° 045-2026.docx». */
    public static function nombreArchivo(?string $numero, $fecha = null): string
    {
        $anio = $fecha ? Carbon::parse($fecha)->year : now()->year;

        return 'Carta de Presentación'.($numero ? ' N° 0'.self::numero($numero).'-'.$anio : '').'.docx';
    }
}
