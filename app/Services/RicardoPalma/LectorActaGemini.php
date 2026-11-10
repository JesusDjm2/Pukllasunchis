<?php

namespace App\Services\RicardoPalma;

use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Lee un acta consolidada escaneada (PDF) con la API de Gemini y la devuelve con la forma
 * que usa la pantalla de corrección (BorradorLecturaRp). La IA solo copia lo que ve; los
 * créditos de cada asignatura (no están impresos en el acta) se deducen de los puntajes.
 */
class LectorActaGemini
{
    private const URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public static function configurado(): bool
    {
        return (string) config('services.gemini.key') !== '';
    }

    /** Modelo con el que se leyó la última acta. */
    public ?string $modeloUsado = null;

    /** @return string[] modelos a probar en orden */
    public static function modelos(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) config('services.gemini.model')))));
    }

    /** @return array datos para la pantalla de corrección */
    public function leer(string $rutaPdf, ?callable $progresoCallback = null): array
    {
        if (! self::configurado()) {
            throw new RuntimeException('Falta la clave GEMINI_API_KEY en el archivo .env.');
        }

        $programas = RpPrograma::orderBy('id')->get(['codigo', 'nombre']);

        $cuerpo = [
            'contents' => [[
                'parts' => [
                    ['inline_data' => ['mime_type' => 'application/pdf', 'data' => base64_encode(file_get_contents($rutaPdf))]],
                    ['text' => $this->instrucciones($programas)],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0,
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->esquema($programas->pluck('codigo')->all()),
            ],
        ];

        // Si un modelo está saturado o retirado, se prueba el siguiente de la lista
        $respuesta = null;
        $sinRespuesta = false;
        $opciones = [];
        if ($progresoCallback) {
            $opciones['progress'] = $progresoCallback;
        }

        foreach (self::modelos() as $modelo) {
            try {
                $cliente = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                    ->timeout(65);
                if (! empty($opciones)) {
                    $cliente = $cliente->withOptions($opciones);
                }
                $respuesta = $cliente->post(sprintf(self::URL, $modelo), $cuerpo);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $sinRespuesta = true; // tardó demasiado: se prueba de inmediato el siguiente modelo

                continue;
            }
            if ($respuesta->successful()) {
                $this->modeloUsado = $modelo;
                break;
            }
            if (! in_array($respuesta->status(), [404, 429, 500, 503], true)) {
                break; // clave inválida, PDF rechazado…: otro modelo no lo arregla
            }
        }

        if (! $respuesta && $sinRespuesta) {
            throw new RuntimeException('Gemini no respondió a tiempo. Vuelve a intentarlo en unos minutos.');
        }
        if (! $respuesta || $respuesta->failed()) {
            $mensaje = $respuesta?->json('error.message') ?: 'HTTP '.$respuesta?->status();
            if ($respuesta?->status() === 429) {
                $mensaje = 'Se alcanzó el límite de lecturas de la cuenta de Gemini. Espera un minuto y vuelve a intentarlo. ('.$mensaje.')';
            } elseif (in_array($respuesta?->status(), [500, 503], true)) {
                $mensaje = 'Los servidores de Gemini están saturados en este momento. Vuelve a intentarlo en unos minutos.';
            }
            throw new RuntimeException('Gemini no pudo leer el acta: '.$mensaje);
        }

        // La respuesta puede venir en varias partes; las de «pensamiento» se descartan
        $texto = collect($respuesta->json('candidates.0.content.parts', []))
            ->reject(fn ($p) => ! empty($p['thought']))
            ->pluck('text')->implode('');
        $leido = json_decode($texto, true);
        if (! is_array($leido)) {
            $motivo = $respuesta->json('candidates.0.finishReason') ?: 'respuesta vacía';
            throw new RuntimeException("Gemini no devolvió los datos del acta ({$motivo}). Vuelve a intentarlo.");
        }

        return self::normalizar($leido);
    }

    /** Respuesta de la IA → datos de la pantalla de corrección. */
    public static function normalizar(array $leido): array
    {
        $txt = fn ($v) => trim((string) ($v ?? ''));
        $asignaturas = array_values(array_filter(array_map($txt, (array) ($leido['asignaturas'] ?? []))));
        $k = count($asignaturas);

        $alumnos = [];
        foreach ((array) ($leido['alumnos'] ?? []) as $al) {
            $casillas = array_map($txt, array_values((array) ($al['casillas'] ?? [])));
            $casillas = array_slice(array_pad($casillas, $k, ''), 0, $k);
            $dudas = [];
            foreach ((array) ($al['casillas_dudosas'] ?? []) as $i) {
                if (is_numeric($i) && $i >= 1 && $i <= $k) {
                    $dudas[] = 'v'.((int) $i - 1);
                }
            }
            foreach ($casillas as $i => $v) {
                if ($v === '?') {
                    $dudas[] = 'v'.$i;
                }
            }
            if (! empty($al['total_dudoso'])) {
                $dudas[] = 'total';
            }
            if (! empty($al['promedio_dudoso'])) {
                $dudas[] = 'promedio';
            }
            if (! empty($al['nombre_dudoso'])) {
                $dudas[] = 'nombre';
            }

            $alumnos[] = [
                'matricula' => $txt($al['matricula'] ?? ''),
                'nombre' => preg_replace('/\s*,\s*/u', ',', $txt($al['apellidos_nombres'] ?? ''), 1),
                'valores' => $casillas,
                'total_acta' => $txt($al['puntaje_total'] ?? ''),
                'creditos_acta' => $txt($al['creditos_matriculados'] ?? ''),
                'promedio_acta' => $txt($al['promedio'] ?? ''),
                'obs' => $txt($al['observacion'] ?? ''),
                'dudas' => array_values(array_unique($dudas)),
            ];
        }

        $creditos = self::deducirCreditos($alumnos, $k);

        return [
            'encabezado' => [
                'periodo' => $txt($leido['anio_academico'] ?? ''),
                'semestre' => $txt($leido['semestre'] ?? ''),
                'fecha' => $txt($leido['fecha'] ?? ''),
                'programa' => $txt($leido['programa_codigo'] ?? ''),
                'turno' => $txt($leido['turno'] ?? ''),
                'seccion' => $txt($leido['seccion'] ?? ''),
                'nro_acta' => $txt($leido['nro_acta'] ?? ''),
                'texto_especialidad' => $txt($leido['especialidad_texto'] ?? ''),
            ],
            'en_puntaje' => true,
            'asignaturas' => array_map(fn ($nombre, $i) => [
                'nombre' => $nombre,
                'creditos' => $creditos[$i] ?? '',
            ], $asignaturas, array_keys($asignaturas)),
            'alumnos' => $alumnos,
        ];
    }

    /**
     * El acta no imprime los créditos: cada puntaje es nota × créditos. Para cada asignatura se
     * buscan los créditos que dividen exacto todos sus puntajes sin pasar de nota 20, y se elige
     * la combinación que suma el «N.º de créditos matriculados» que figura en el acta.
     */
    public static function deducirCreditos(array $alumnos, int $k): array
    {
        $candidatos = [];
        for ($i = 0; $i < $k; $i++) {
            $vals = [];
            foreach ($alumnos as $al) {
                $v = $al['valores'][$i] ?? '';
                if (ctype_digit($v) && (int) $v > 0) {
                    $vals[] = (int) $v;
                }
            }
            $opc = [];
            for ($c = 10; $c >= 1; $c--) {
                if (! $vals) {
                    break;
                }
                // Se tolera alguna casilla mal leída: basta con que el 85 % se divida exacto
                $exactas = array_filter($vals, fn ($v) => $v % $c === 0);
                if ($exactas && max($exactas) / $c <= 20 && count($exactas) >= (int) ceil(count($vals) * 0.85)) {
                    $opc[] = $c;
                }
            }
            $candidatos[$i] = $opc ?: [null];
        }

        // Créditos matriculados más frecuentes en el acta
        $frec = array_count_values(array_filter(array_map(fn ($a) => $a['creditos_acta'] ?? '', $alumnos), 'ctype_digit'));
        arsort($frec);
        $objetivo = $frec ? (int) array_key_first($frec) : null;

        $primera = array_map(fn ($o) => $o[0], $candidatos);
        if (! $objetivo || in_array(null, $primera, true)) {
            return $primera;
        }

        // Búsqueda de la combinación que suma el objetivo, prefiriendo créditos mayores
        $solucion = null;
        $buscar = function (int $i, int $suma, array $elegidos) use (&$buscar, &$solucion, $candidatos, $k, $objetivo) {
            if ($solucion !== null || $suma > $objetivo) {
                return;
            }
            if ($i === $k) {
                if ($suma === $objetivo) {
                    $solucion = $elegidos;
                }

                return;
            }
            foreach ($candidatos[$i] as $c) {
                $buscar($i + 1, $suma + $c, [...$elegidos, $c]);
            }
        };
        $buscar(0, 0, []);

        return $solucion ?? $primera;
    }

    private function instrucciones($programas): string
    {
        $catalogo = $programas->map(fn ($p) => "- {$p->codigo}: {$p->nombre}")->implode("\n");

        return <<<TXT
Eres un transcriptor de documentos históricos. El PDF es un «Acta consolidada de evaluación del rendimiento académico» del ISP Ricardo Palma (Cusco, Perú), escaneada; puede ocupar varias páginas (la tabla de alumnos continúa en la página siguiente).

Tu único trabajo es COPIAR lo que está escrito. No calcules, no corrijas, no completes nada que no se vea.

ENCABEZADO
- anio_academico: el «AÑO ACADÉMICO» tal como figura (ej.: «1993-I»).
- semestre: el «SEMESTRE» tal como figura (ej.: «PRIMER», «DÉCIMO»).
- fecha: el «FECHA:» del encabezado tal como figura (ej.: «08-09-93»).
- especialidad_texto: la línea que describe la carrera, tal como figura (ej.: «EDUCACION SECUNDARIA ESP. MATEMATICA - TURNO MAÑANA»).
- programa_codigo: el código del catálogo que corresponde a esa carrera:
{$catalogo}
- turno: MAÑANA, TARDE o NOCHE si figura; si no, vacío.
- seccion: la sección si figura; si no, vacío.
- nro_acta: el número de acta si figura; si no, vacío.

ASIGNATURAS
- asignaturas: los nombres escritos en vertical en la cabecera «ASIGNATURAS», en orden de izquierda a derecha, solo las columnas que tienen nombre. Copia el nombre completo como está (ej.: «MATEMATICA I», «INTROD. A LAS CIENCIAS SOC.»).

ALUMNOS (todas las filas con nombre, en orden, de todas las páginas)
- matricula: el «N° de matrícula» tal como figura (conserva ceros a la izquierda: «089»).
- apellidos_nombres: tal como está escrito a máquina, con la coma entre apellidos y nombres («ALOSILLA SERRUTO,Gilberto Willy»).
- casillas: una casilla por cada asignatura, en el mismo orden y con la misma cantidad que «asignaturas». Copia el número escrito a mano EXACTAMENTE: «00» se copia «00»; una casilla en blanco o con raya se deja ""; si no se puede leer, escribe "?". No dividas ni conviertas: son puntajes, no notas.
- casillas_dudosas: los números de casilla (1 = primera asignatura) cuya lectura no es segura: trazos que pueden ser 1/7, 4/9, 3/8, 5/6, 0/6, tachones, tinta corrida o manchas. Es mejor marcar de más que de menos.
- puntaje_total: el «PUNTAJE TOTAL» escrito a mano, o "" si está vacío.
- creditos_matriculados: el «N° DE CRÉDITOS MATRICULADOS», o "" si está vacío.
- promedio: el «PROMEDIO PONDERADO SEMESTRAL» tal como está, con coma decimal (ej.: «14,45»), o "".
- total_dudoso / promedio_dudoso / nombre_dudoso: true si esa lectura no es segura.
- observacion: el texto de la columna OBSERVACIONES (ej.: «1ra. observación», «RETIRADO…»). Si junto al nombre hay una anotación a mano, agrégala aquí. Ignora los vistos (✓), firmas y sellos.
TXT;
    }

    private function esquema(array $codigos): array
    {
        $s = ['type' => 'STRING'];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'anio_academico' => $s,
                'semestre' => $s,
                'fecha' => $s,
                'especialidad_texto' => $s,
                'programa_codigo' => ['type' => 'STRING', 'enum' => array_values($codigos)],
                'turno' => $s,
                'seccion' => $s,
                'nro_acta' => $s,
                'asignaturas' => ['type' => 'ARRAY', 'items' => $s],
                'alumnos' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'matricula' => $s,
                            'apellidos_nombres' => $s,
                            'nombre_dudoso' => ['type' => 'BOOLEAN'],
                            'casillas' => ['type' => 'ARRAY', 'items' => $s],
                            'casillas_dudosas' => ['type' => 'ARRAY', 'items' => ['type' => 'INTEGER']],
                            'puntaje_total' => $s,
                            'total_dudoso' => ['type' => 'BOOLEAN'],
                            'creditos_matriculados' => $s,
                            'promedio' => $s,
                            'promedio_dudoso' => ['type' => 'BOOLEAN'],
                            'observacion' => $s,
                        ],
                        'required' => ['matricula', 'apellidos_nombres', 'casillas', 'puntaje_total', 'creditos_matriculados', 'promedio', 'observacion'],
                        'propertyOrdering' => ['matricula', 'apellidos_nombres', 'nombre_dudoso', 'casillas', 'casillas_dudosas', 'puntaje_total', 'total_dudoso', 'creditos_matriculados', 'promedio', 'promedio_dudoso', 'observacion'],
                    ],
                ],
            ],
            'required' => ['anio_academico', 'semestre', 'fecha', 'especialidad_texto', 'programa_codigo', 'asignaturas', 'alumnos'],
            'propertyOrdering' => ['anio_academico', 'semestre', 'fecha', 'especialidad_texto', 'programa_codigo', 'turno', 'seccion', 'nro_acta', 'asignaturas', 'alumnos'],
        ];
    }
}
