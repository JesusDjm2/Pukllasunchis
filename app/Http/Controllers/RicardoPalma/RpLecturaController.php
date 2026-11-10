<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Http\Controllers\Controller;
use App\Imports\RicardoPalma\ActaConsolidadaRpImport;
use App\Imports\RicardoPalma\ActaFisicaRpImport;
use App\Models\RicardoPalma\RpActa;
use App\Models\RicardoPalma\RpPrograma;
use App\Services\RicardoPalma\BorradorLecturaRp;
use App\Services\RicardoPalma\LectorActaGemini;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Acta escaneada (PDF) → lectura con IA → pantalla de corrección con el escaneo al lado →
 * carga con el mismo importador del acta física. Nada entra a las tablas rp_* hasta que el
 * usuario confirma y todas las filas cuadran.
 */
class RpLecturaController extends Controller
{
    /** Sube el PDF y abre la pantalla de corrección inmediatamente (la IA lee vía stream). */
    public function subir(Request $request): RedirectResponse
    {
        $request->validate([
            'escaneo' => 'required|file|mimes:pdf|max:14336',
        ], [
            'escaneo.required' => 'Selecciona el PDF escaneado del acta.',
            'escaneo.mimes' => 'El archivo debe ser un PDF.',
            'escaneo.max' => 'El PDF no debe pasar de 14 MB. Escanéalo a 300 ppp o divide el archivo.',
        ]);

        $archivo = $request->file('escaneo');
        $borrador = BorradorLecturaRp::crear($archivo->getRealPath(), $archivo->getClientOriginalName(), auth()->id(), auth()->user()?->name);

        return redirect()->route('rp.lecturas.editar', $borrador->id);
    }

    public function editar(string $lectura): View|RedirectResponse
    {
        $borrador = $this->borrador($lectura);

        return view('admin.ricardo-palma.lecturas.editar', [
            'borrador' => $borrador,
            'meta' => $borrador->meta(),
            'datos' => $borrador->datos(),
            'necesitaLectura' => $borrador->necesitaLectura(),
            'programas' => RpPrograma::orderBy('id')->get(['codigo', 'nombre']),
            'iaConfigurada' => LectorActaGemini::configurado(),
        ]);
    }

    /** Stream SSE de lectura con IA para evitar timeout de Nginx/Apache */
    public function stream(string $lectura): StreamedResponse
    {
        $borrador = $this->borrador($lectura);

        // Guardar la sesión antes de comenzar el stream largo para no bloquear otras peticiones del usuario
        if (session()->isStarted()) {
            session()->save();
        }

        return response()->stream(function () use ($borrador) {
            @ignore_user_abort(true);
            @set_time_limit(300);

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();

            $sendEvent = function (string $event, array $data) {
                echo "event: {$event}\n";
                echo 'data: '.json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n";
                flush();
            };

            // Si ya tiene datos leídos previamente y no necesita relectura, devolver de inmediato
            if (! $borrador->necesitaLectura() && $borrador->tieneDatos()) {
                $sendEvent('completado', [
                    'ok' => true,
                    'modelo' => $borrador->meta()['leido_con'] ?? 'Gemini',
                    'alumnos' => count($borrador->datos()['alumnos'] ?? []),
                    'asignaturas' => count($borrador->datos()['asignaturas'] ?? []),
                    'tiempo' => 0,
                ]);

                return;
            }

            $meta = $borrador->meta();
            $sendEvent('iniciado', [
                'archivo' => $meta['archivo'] ?? 'escaneo.pdf',
                'mensaje' => 'Conectando con la IA...',
            ]);

            $inicio = microtime(true);
            $ultimoPing = time();

            $onProgress = function ($dlTotal, $dlNow, $ulTotal, $ulNow) use (&$ultimoPing, $inicio, $sendEvent) {
                $ahora = time();
                if ($ahora - $ultimoPing >= 2) {
                    $ultimoPing = $ahora;
                    $segundos = (int) round(microtime(true) - $inicio);
                    $fase = $segundos < 8
                        ? 'Enviando documento a la IA...'
                        : ($segundos < 30
                            ? 'Analizando estructura de la tabla y encabezados...'
                            : 'Extrayendo calificaciones, asignaturas y estudiantes...');

                    $sendEvent('progreso', [
                        'segundos' => $segundos,
                        'fase' => $fase,
                    ]);
                }
            };

            try {
                $lector = new LectorActaGemini();
                $datos = $lector->leer($borrador->rutaPdf(), $onProgress);

                $borrador->guardarDatos($datos);
                $meta = $borrador->meta();
                $meta['leido_con'] = $lector->modeloUsado;
                $meta['error_lectura'] = null;
                $borrador->guardarMeta($meta);

                $sendEvent('completado', [
                    'ok' => true,
                    'modelo' => $lector->modeloUsado,
                    'alumnos' => count($datos['alumnos'] ?? []),
                    'asignaturas' => count($datos['asignaturas'] ?? []),
                    'tiempo' => round(microtime(true) - $inicio, 1),
                ]);
            } catch (\Throwable $e) {
                report($e);
                $meta = $borrador->meta();
                $meta['error_lectura'] = $e->getMessage();
                $borrador->guardarMeta($meta);

                $sendEvent('error', [
                    'ok' => false,
                    'mensaje' => $e->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function pdf(string $lectura): Response
    {
        $borrador = $this->borrador($lectura);

        return response()->file($borrador->rutaPdf(), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="escaneo.pdf"',
        ]);
    }

    /** Autoguardado de la pantalla de corrección. */
    public function autoguardar(Request $request, string $lectura): JsonResponse
    {
        $borrador = $this->borrador($lectura);
        $datos = $this->datosDelFormulario($request);
        if ($datos === null) {
            return response()->json(['ok' => false, 'mensaje' => 'Datos no válidos.'], 422);
        }
        $borrador->guardarDatos($datos);

        return response()->json(['ok' => true, 'hora' => now()->format('H:i:s')]);
    }

    /** Reinicia la lectura con IA del acta. */
    public function releer(string $lectura): RedirectResponse
    {
        $borrador = $this->borrador($lectura);
        $borrador->reiniciarLectura();

        return redirect()->route('rp.lecturas.editar', $borrador->id);
    }

    /** Carga el acta corregida. Si alguna fila no cuadra, no se carga nada. */
    public function cargar(Request $request, string $lectura): RedirectResponse
    {
        $borrador = $this->borrador($lectura);
        $datos = $this->datosDelFormulario($request);
        if ($datos === null) {
            return back()->with('error', 'No se recibieron los datos del acta.');
        }
        $borrador->guardarDatos($datos);

        $e = $datos['encabezado'];
        $errores = [];
        if (! ActaConsolidadaRpImport::normalizarPeriodo((string) $e['periodo'])) {
            $errores[] = "El año académico «{$e['periodo']}» debe tener la forma AAAA-I o AAAA-II.";
        }
        if (! RpPrograma::where('codigo', $e['programa'])->exists()) {
            $errores[] = 'Elige la especialidad del acta.';
        }

        $acta = [
            'periodo' => $e['periodo'],
            'semestre' => $e['semestre'],
            'fecha' => $e['fecha'],
            'programa' => $e['programa'],
            'turno' => $e['turno'],
            'seccion' => $e['seccion'],
            'nro_acta' => $e['nro_acta'],
            'en_puntaje' => $datos['en_puntaje'],
            'asignaturas' => [],
            'alumnos' => [],
        ];
        foreach ($datos['asignaturas'] as $i => $a) {
            $acta['asignaturas'][$i + 1] = $a;
        }
        foreach ($datos['alumnos'] as $i => $al) {
            $valores = [];
            foreach ($al['valores'] as $j => $v) {
                $valores[$j + 1] = $v;
            }
            $acta['alumnos'][] = ['ref' => 'N° '.($i + 1), 'valores' => $valores] + $al;
        }

        $filas = ActaFisicaRpImport::desdeActa($acta, 'Acta escaneada', $errores);
        if ($errores) {
            return redirect()->route('rp.lecturas.editar', $borrador->id)->with('rp_errores_carga', $errores);
        }

        $archivo = $borrador->meta()['archivo'] ?? 'acta escaneada.pdf';
        try {
            $import = new ActaConsolidadaRpImport('Escaneo: '.$archivo, auth()->id());
            $import->collection(collect($filas));
        } catch (\Throwable $ex) {
            return redirect()->route('rp.lecturas.editar', $borrador->id)->with('error', 'No se pudo cargar el acta: '.$ex->getMessage());
        }

        $resultado = $import->getResultado();
        $borrador->archivarEn($resultado['actas']);

        return redirect()->route('rp.importar.index')
            ->with('rp_resultado', $resultado + ['archivo' => $archivo]);
    }

    public function descartar(string $lectura): RedirectResponse
    {
        $this->borrador($lectura)->eliminar();

        return redirect()->route('rp.importar.index')->with('success', 'Se descartó el acta escaneada.');
    }

    /** Descarta todos los borradores pendientes de una sola vez. */
    public function limpiarTodos(): RedirectResponse
    {
        $eliminados = 0;
        foreach (BorradorLecturaRp::todos() as $b) {
            $borrador = BorradorLecturaRp::buscar($b['id']);
            if ($borrador) {
                $borrador->eliminar();
                $eliminados++;
            }
        }

        return redirect()->route('rp.importar.index')->with('success', "Se descartaron {$eliminados} borrador(es) pendiente(s).");
    }

    /** PDF escaneado guardado con el acta. */
    public function escaneoActa(RpActa $acta): Response
    {
        $ruta = BorradorLecturaRp::rutaEscaneoActa($acta->id);
        abort_unless(Storage::disk('local')->exists($ruta), 404);

        return response()->file(Storage::disk('local')->path($ruta), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="acta-'.$acta->id.'.pdf"',
        ]);
    }

    private function borrador(string $id): BorradorLecturaRp
    {
        $borrador = BorradorLecturaRp::buscar($id);
        abort_unless($borrador, 404, 'El acta escaneada ya no existe (se cargó o se descartó).');

        return $borrador;
    }

    private function leerConIa(BorradorLecturaRp $borrador): bool
    {
        @set_time_limit(300);
        try {
            $lector = new LectorActaGemini();
            $borrador->guardarDatos($lector->leer($borrador->rutaPdf()));
            $borrador->guardarMeta(['leido_con' => $lector->modeloUsado, 'error_lectura' => null] + $borrador->meta());

            return true;
        } catch (\Throwable $e) {
            report($e);
            $borrador->guardarMeta(['error_lectura' => $e->getMessage()] + $borrador->meta());

            return false;
        }
    }

    /** Valida y limpia el JSON que envía la pantalla de corrección. */
    private function datosDelFormulario(Request $request): ?array
    {
        $datos = json_decode((string) $request->input('datos'), true);
        if (! is_array($datos) || ! isset($datos['encabezado'], $datos['asignaturas'], $datos['alumnos'])) {
            return null;
        }
        $t = fn ($v, int $max = 255) => mb_substr(trim((string) (is_scalar($v) ? $v : '')), 0, $max);

        $enc = [];
        foreach (['periodo', 'semestre', 'fecha', 'programa', 'turno', 'seccion', 'nro_acta', 'texto_especialidad'] as $c) {
            $enc[$c] = $t($datos['encabezado'][$c] ?? '');
        }
        $asignaturas = [];
        foreach (array_slice((array) $datos['asignaturas'], 0, 15) as $a) {
            $asignaturas[] = ['nombre' => $t($a['nombre'] ?? ''), 'creditos' => $t($a['creditos'] ?? '', 3)];
        }
        $k = count($asignaturas);
        $alumnos = [];
        foreach (array_slice((array) $datos['alumnos'], 0, 300) as $al) {
            $valores = array_map(fn ($v) => $t($v, 8), array_values((array) ($al['valores'] ?? [])));
            $alumnos[] = [
                'matricula' => $t($al['matricula'] ?? '', 30),
                'nombre' => $t($al['nombre'] ?? ''),
                'valores' => array_slice(array_pad($valores, $k, ''), 0, $k),
                'total_acta' => $t($al['total_acta'] ?? '', 10),
                'creditos_acta' => $t($al['creditos_acta'] ?? '', 4),
                'promedio_acta' => $t($al['promedio_acta'] ?? '', 10),
                'obs' => $t($al['obs'] ?? ''),
                'dudas' => array_values(array_filter((array) ($al['dudas'] ?? []), 'is_string')),
            ];
        }

        return [
            'encabezado' => $enc,
            'en_puntaje' => (bool) ($datos['en_puntaje'] ?? true),
            'asignaturas' => $asignaturas,
            'alumnos' => $alumnos,
        ];
    }
}
