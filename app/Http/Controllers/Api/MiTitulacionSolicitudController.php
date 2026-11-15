<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Titulacion\InicioProcesoController;
use App\Http\Controllers\Titulacion\MiSolicitudPtiController;
use App\Mail\Titulacion\CartaRecibidaMail;
use App\Mail\Titulacion\Remitente;
use App\Models\Titulacion\CartaAceptacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Notifications\TitulacionCartaAtendida;
use App\Notifications\TitulacionCartaSolicitada;
use App\Notifications\TitulacionOriginalidadSolicitada;
use App\Notifications\TitulacionPtiSolicitada;
use App\Services\GoogleDriveService;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\ReconocerPersonas;
use App\Support\PanelDeUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Solicitudes de titulación del estudiante desde la app: aprobación del PTI, carta de aceptación de la IE
 * y revisión de originalidad (Turnitin). Cada una con un GET (lo que necesita el formulario) y un POST
 * multipart (el envío).
 *
 * ESPEJO de los controladores web — mismas reglas, mismos datos guardados, mismos avisos:
 *   - PTI          → Titulacion\MiSolicitudPtiController@enviar
 *   - Carta        → Titulacion\CartaAceptacionController@enviar
 *   - Originalidad → Titulacion\MiSolicitudOriginalidadController@enviar
 * La API es aditiva (no se tocan los controladores web), así que si cambian las reglas allá hay que
 * cambiarlas aquí también. tests/Feature/Api/MiTitulacionSolicitudTest cubre los casos principales.
 *
 * shortcut: reglas de validación y "cuándo no se puede enviar" duplicadas de los 3 controladores web,
 * unificarlas en un servicio compartido (app/Services/Titulacion) cuando se coordine con el equipo de la web.
 */
class MiTitulacionSolicitudController extends Controller
{
    // ── Aprobación del PTI (primer paso, antes de tener proceso) ─────────

    public function ptiFormulario(Request $request): JsonResponse
    {
        $user = $request->user();
        $ultima = Solicitud::ptiDe($user)->first();
        $fijos = $this->fijosPti($user);
        $ficha = $user->alumno ?? $user->alumnoB;
        $valores = array_merge([
            'integrante2' => '', 'integrante3' => '', 'titulo' => '', 'area' => '', 'linea' => '',
            'celulares' => (string) ($ficha?->numero ?: $user->telefono),
            'programa_estudios' => '', 'ciclo' => '',
        ], $fijos);
        // Al corregir una observada, se parte de lo que ya escribió (como en la web).
        if ($ultima?->estado === 'observada') {
            $nombres = $ultima->nombresIntegrantes();
            $valores = array_merge($valores, [
                'integrante2' => $nombres[1] ?? '', 'integrante3' => $nombres[2] ?? '',
                'celulares' => $ultima->celulares, 'titulo' => $ultima->titulo,
                'area' => $ultima->datos['area'] ?? '', 'linea' => $ultima->datos['linea'] ?? '',
            ], array_filter(['programa_estudios' => $ultima->programa_estudios, 'ciclo' => $ultima->ciclo]), $fijos);
        }

        return response()->json(['data' => [
            'motivo_no_puede' => $this->motivoNoPuedePti($user, $ultima),
            'ultima' => $this->resumenSolicitud($ultima),
            'solicitante' => MiSolicitudPtiController::nombre($user),
            'valores' => $valores,
            'fijos' => array_keys($fijos),
            'opciones' => [
                'programas_estudios' => Solicitud::PROGRAMAS_ESTUDIOS,
                'ciclos' => Solicitud::CICLOS,
                'areas' => $this->opciones(Solicitud::AREAS),
                'lineas' => $this->opciones(Solicitud::LINEAS),
            ],
            'archivos' => [
                'pti' => ['extensiones' => ['pdf', 'doc', 'docx'], 'max_mb' => 10],
                'comprobantes' => ['extensiones' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_mb' => 10, 'max_archivos' => 5],
            ],
            'pago' => config('titulacion.tramites.pti_aprobacion.pago'),
        ]]);
    }

    public function ptiEnviar(Request $request): JsonResponse
    {
        $user = $request->user();
        $ultima = Solicitud::ptiDe($user)->first();
        if ($motivo = $this->motivoNoPuedePti($user, $ultima)) {
            return response()->json(['message' => $motivo], 409);
        }

        // Lo que se sabe de la ficha no lo decide el formulario.
        $request->merge($this->fijosPti($user));
        $data = $request->validate([
            'integrante2' => 'nullable|string|min:5|max:200',
            'integrante3' => 'nullable|string|min:5|max:200',
            'celulares' => 'required|string|min:9|max:200',
            'programa_estudios' => 'required|in:'.implode(',', Solicitud::PROGRAMAS_ESTUDIOS),
            'ciclo' => 'required|in:'.implode(',', Solicitud::CICLOS),
            'area' => 'required|in:'.implode(',', array_keys(Solicitud::AREAS)),
            'linea' => 'required|in:'.implode(',', array_keys(Solicitud::LINEAS)),
            'titulo' => 'required|string|min:10|max:1000',
            'pti' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'comprobantes' => 'required|array|min:1|max:5',
            'comprobantes.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracion' => 'accepted',
        ], [
            'linea.in' => 'Elige una línea de investigación.',
            'pti.required' => 'Adjunta tu Plan de Trabajo de Investigación.',
            'pti.mimes' => 'El PTI debe ser PDF o Word.',
            'pti.max' => 'El PTI no debe pesar más de 10 MB.',
            'comprobantes.required' => 'Adjunta el comprobante de pago emitido por la EESPP.',
            'comprobantes.max' => 'Puedes adjuntar hasta 5 archivos de comprobante.',
            'comprobantes.*.mimes' => 'El comprobante debe ser PDF o imagen (JPG o PNG).',
            'comprobantes.*.max' => 'Cada comprobante debe pesar hasta 10 MB.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], [
            'integrante2' => 'integrante 2', 'integrante3' => 'integrante 3', 'celulares' => 'números de contacto',
            'programa_estudios' => 'programa de estudios', 'ciclo' => 'ciclo o programa', 'area' => 'área de investigación',
            'linea' => 'línea de investigación', 'titulo' => 'título del PTI',
        ]);
        if (! str_starts_with($data['linea'], $data['area'].'-')) {
            return response()->json([
                'message' => 'La línea elegida no pertenece al área de investigación elegida.',
                'errors' => ['linea' => ['La línea elegida no pertenece al área de investigación elegida.']],
            ], 422);
        }

        // Integrantes: quien envía (de su ficha) y los que escribió, reconocidos por nombre cuando se puede.
        $personas = new ReconocerPersonas;
        $integrantes = [['nombre' => MiSolicitudPtiController::nombre($user), 'user_id' => $user->id]];
        foreach (['integrante2', 'integrante3'] as $k) {
            if (filled($data[$k] ?? null)) {
                $nombre = trim(preg_replace('/\s+/', ' ', $data[$k]));
                [$u] = $personas->porNombre($nombre);
                $integrantes[] = ['nombre' => $nombre, 'user_id' => $u && $u->id !== $user->id ? $u->id : null];
            }
        }

        $solicitud = Solicitud::create([
            'tipo' => Solicitud::PTI, 'solicitante_id' => $user->id, 'correo' => (string) $user->email,
            'integrantes' => $integrantes, 'celulares' => trim($data['celulares']),
            'programa_estudios' => $data['programa_estudios'], 'ciclo' => $data['ciclo'],
            'titulo' => trim($data['titulo'], " \"“”"),
            'datos' => ['area' => $data['area'], 'linea' => $data['linea']],
            'archivo' => $this->guardarLocal($request->file('pti')),
            'comprobante' => collect($request->file('comprobantes'))->map(fn ($f) => $this->guardarLocal($f))->implode(','),
            'declaracion' => true, 'estado' => 'recibida', 'origen' => 'sistema',
        ]);
        $this->notificar(
            AreasTitulacion::destinatarios([AreasTitulacion::SECRETARIA, AreasTitulacion::INVESTIGACION]),
            new TitulacionPtiSolicitada($solicitud, $user), 'PTI', $solicitud->id
        );

        return response()->json([
            'message' => 'Tu solicitud fue enviada. El Área de Investigación revisará tu PTI y tu comprobante; verás en tu proceso de titulación cuando se inicie.',
            'data' => $this->resumenSolicitud($solicitud),
        ], 201);
    }

    // ── Carta de aceptación de la Institución Educativa (con el PTI aprobado) ──

    public function cartaFormulario(Request $request): JsonResponse
    {
        $user = $request->user();
        $seguimiento = $this->seguimiento($user);
        $ultima = $seguimiento?->cartasAceptacion()->first();
        $oficial = $this->programaOficialCarta($user);

        $valores = [
            'grupo' => $seguimiento
                ? collect([$user])->concat(GrupoTitulacion::companeros($seguimiento))
                    ->map(fn ($u) => trim(mb_convert_case(trim($u->name.' '.$u->apellidos), MB_CASE_TITLE)))->implode(', ')
                : '',
            'celular' => (string) ($user->alumno?->numero ?: ($user->alumnoB?->numero ?: $user->telefono)),
            'programa_estudios' => '', 'programa_formacion' => '',
            'institucion' => '', 'director' => '',
            'titulo' => (string) $seguimiento?->pti?->titulo,
        ];
        if ($ultima?->estado === 'observada') {
            $valores = array_merge($valores, $ultima->only(['grupo', 'celular', 'programa_estudios', 'programa_formacion', 'institucion', 'director', 'titulo']));
        }
        $valores = array_merge($valores, array_filter($oficial));

        return response()->json(['data' => [
            'motivo_no_puede' => $this->motivoNoPuedeCarta($user, $seguimiento, $ultima),
            'ultima' => $ultima ? [
                'estado' => $ultima->estado,
                'estado_nombre' => CartaAceptacion::ESTADOS[$ultima->estado] ?? $ultima->estado,
                'fecha' => $ultima->created_at?->toDateString(),
                'observacion' => $ultima->observacion,
                'institucion' => $ultima->institucion,
                'fecha_limite' => $ultima->estado === 'enviada' ? $ultima->fechaLimite()->toDateString() : null,
            ] : null,
            'valores' => $valores,
            'fijos' => array_keys(array_filter($oficial)),
            'opciones' => [
                'programas_estudios' => CartaAceptacion::PROGRAMAS_ESTUDIOS,
                'programas_formacion' => $this->opciones(CartaAceptacion::PROGRAMAS_FORMACION),
            ],
            'archivos' => ['comprobante' => ['extensiones' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_mb' => 5]],
            'pago' => config('titulacion.tramites.carta_aplicacion.pago'),
        ]]);
    }

    public function cartaEnviar(Request $request): JsonResponse
    {
        $user = $request->user();
        $seguimiento = $this->seguimiento($user);
        $ultima = $seguimiento?->cartasAceptacion()->first();
        if ($motivo = $this->motivoNoPuedeCarta($user, $seguimiento, $ultima)) {
            return response()->json(['message' => $motivo], 409);
        }

        // El programa no lo decide el formulario: el de la ficha manda.
        $request->merge(array_filter($this->programaOficialCarta($user)));
        $data = $request->validate([
            'grupo' => 'required|string|min:5|max:1000',
            'celular' => ['required', 'regex:/^\+?[\d\s]{9,15}$/'],
            'programa_estudios' => 'required|in:'.implode(',', CartaAceptacion::PROGRAMAS_ESTUDIOS),
            'programa_formacion' => 'required|in:'.implode(',', array_keys(CartaAceptacion::PROGRAMAS_FORMACION)),
            'institucion' => 'required|string|min:5|max:300',
            'director' => 'required|string|min:5|max:200',
            'titulo' => 'required|string|min:10|max:1000',
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'declaracion' => 'accepted',
        ], [
            'celular.regex' => 'Escribe un número de celular válido (9 dígitos).',
            'comprobante.required' => 'Adjunta el comprobante de pago del derecho de trámite.',
            'comprobante.mimes' => 'El comprobante debe ser PDF o imagen (JPG o PNG).',
            'comprobante.max' => 'El comprobante no debe pesar más de 5 MB.',
            'comprobante.uploaded' => 'No se pudo subir el comprobante: puede ser muy pesado (máximo 5 MB). Prueba con una foto más liviana o un PDF.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], [
            'grupo' => 'grupo de investigación', 'programa_estudios' => 'programa de estudios', 'programa_formacion' => 'programa de formación',
            'institucion' => 'denominación de la institución educativa', 'director' => 'nombre del director(a)', 'titulo' => 'título del trabajo de investigación',
        ]);

        $comprobante = $this->guardarComprobanteCarta($request->file('comprobante'), $user);

        // Una fila por integrante (el grupo que el sistema conoce), con el mismo lote.
        $seguimientos = collect([$seguimiento])->concat(
            GrupoTitulacion::companeros($seguimiento)->map(fn ($u) => $u->seguimientoTitulacion)->filter()
        )->unique('id');
        $lote = (string) Str::uuid();
        $principal = null;
        DB::transaction(function () use ($seguimientos, $lote, $user, $data, $comprobante, &$principal) {
            foreach ($seguimientos as $s) {
                $fila = CartaAceptacion::create([
                    'lote' => $lote, 'seguimiento_id' => $s->id, 'solicitante_id' => $user->id, 'correo' => $user->email,
                    'grupo' => trim($data['grupo']), 'celular' => trim($data['celular']),
                    'programa_estudios' => $data['programa_estudios'], 'programa_formacion' => $data['programa_formacion'],
                    'institucion' => trim($data['institucion']), 'director' => trim($data['director']), 'titulo' => trim($data['titulo'], " \"“”"),
                    'comprobante' => $comprobante, 'declaracion' => true, 'estado' => 'enviada',
                ]);
                $principal ??= $fila;
            }
        });

        // Aviso SOLO en el sistema a Secretaría (o super-admin si no hay), y respuesta automática a quien envió.
        $secretaria = User::role('secretaria')->get();
        $this->notificar($secretaria->isEmpty() ? User::role('super-admin')->get() : $secretaria,
            new TitulacionCartaSolicitada($principal, $user), 'carta', $principal->id);
        $this->notificar(collect([$user]), new TitulacionCartaAtendida($principal), 'recepción de carta', $principal->id);
        try {
            Mail::mailer(Remitente::mailer('secretaria'))->to($principal->correo)->send(new CartaRecibidaMail($principal, $user));
        } catch (\Throwable $e) {
            Log::warning('API: no se pudo enviar la respuesta automática de la carta de aceptación', ['carta' => $principal->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'message' => $principal->mensajeRecepcion(),
            'data' => ['estado' => $principal->estado, 'fecha_limite' => $principal->fechaLimite()->toDateString()],
        ], 201);
    }

    // ── Revisión de originalidad (Turnitin) del TI o de la Tesis ─────────

    public function originalidadFormulario(Request $request): JsonResponse
    {
        $user = $request->user();
        $seguimiento = $this->seguimiento($user);
        $historial = Solicitud::dePersona(Solicitud::ORIGINALIDAD, $user)->get();
        $ultima = $historial->first();
        $etapa = $seguimiento && $seguimiento->avance()->hitos['ti_aprobado']['logrado'] ? 'tesis' : 'ti';
        $tituloDe = fn (string $e) => (string) ($e === 'tesis' ? ($seguimiento?->tesis?->titulo ?: $seguimiento?->pti?->titulo) : $seguimiento?->pti?->titulo);

        $valores = [
            'etapa' => $etapa, 'titulo' => $tituloDe($etapa),
            'celulares' => (string) (($user->alumno ?? $user->alumnoB)?->numero ?: $user->telefono),
            'domicilio' => '', 'fecha_pago' => '',
        ];
        if ($ultima?->estado === 'observada') {
            $valores = array_merge($valores, ['etapa' => $ultima->etapa() ?? $etapa, 'titulo' => $ultima->titulo, 'celulares' => $ultima->celulares,
                'domicilio' => $ultima->datos['domicilio'] ?? '', 'fecha_pago' => $ultima->datos['fecha_pago'] ?? '']);
        }

        return response()->json(['data' => [
            'habilitada' => (bool) config('titulacion.originalidad_alumnos'),
            // Mientras esté apagada, la web manda al formulario de Google.
            'formulario_externo' => config('titulacion.originalidad_alumnos') ? null : (config('titulacion.tramites.turnitin.formulario')[1] ?? null),
            'motivo_no_puede' => $this->motivoNoPuedeOriginalidad($user, $seguimiento, $ultima),
            'ultima' => $this->resumenSolicitud($ultima),
            'historial' => $historial->map(fn ($s) => $this->resumenSolicitud($s))->values(),
            'tesistas' => $seguimiento ? $this->tesistas($user, $seguimiento)->map(fn ($u) => MiSolicitudPtiController::nombre($u))->values() : [],
            'titulos' => ['ti' => $tituloDe('ti'), 'tesis' => $tituloDe('tesis')],
            'valores' => $valores,
            'opciones' => ['etapas' => $this->opciones(Solicitud::ETAPAS)],
            'pagos' => collect(Solicitud::PAGO_ORIGINALIDAD)->map(fn ($p) => ['codigo' => (string) $p[0], 'monto' => number_format($p[1], 2), 'concepto' => $p[2]]),
            'archivos' => [
                'documento' => ['extensiones' => ['doc', 'docx'], 'max_mb' => 10],
                'comprobantes' => ['extensiones' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_mb' => 10, 'max_archivos' => 5],
            ],
            'similitud_maxima' => Solicitud::SIMILITUD_MAXIMA,
            'dias_respuesta' => Solicitud::DIAS_ORIGINALIDAD,
        ]]);
    }

    public function originalidadEnviar(Request $request): JsonResponse
    {
        $user = $request->user();
        $seguimiento = $this->seguimiento($user);
        $ultima = Solicitud::dePersona(Solicitud::ORIGINALIDAD, $user)->first();
        if ($motivo = $this->motivoNoPuedeOriginalidad($user, $seguimiento, $ultima)) {
            return response()->json(['message' => $motivo], 409);
        }

        $data = $request->validate([
            'etapa' => 'required|in:'.implode(',', array_keys(Solicitud::ETAPAS)),
            'titulo' => 'required|string|min:10|max:1000',
            'celulares' => 'required|string|min:9|max:200',
            'domicilio' => 'nullable|string|max:300',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'documento' => 'required|file|mimes:doc,docx|max:10240',
            'comprobantes' => 'required|array|min:1|max:5',
            'comprobantes.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracion' => 'accepted',
        ], [
            'documento.required' => 'Adjunta la versión final de tu documento en Word.',
            'documento.mimes' => 'El documento debe estar en Word (.doc o .docx): Turnitin lo revisa en ese formato.',
            'documento.max' => 'El documento no debe pesar más de 10 MB.',
            'comprobantes.required' => 'Adjunta el comprobante de pago emitido por la EESPP.',
            'comprobantes.*.mimes' => 'El comprobante debe ser PDF o imagen (JPG o PNG).',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], ['etapa' => 'documento a revisar', 'titulo' => 'título del documento', 'celulares' => 'números de celular', 'fecha_pago' => 'fecha de pago']);

        $esPpd = InicioProcesoController::esPpd($user);
        $solicitud = Solicitud::create([
            'tipo' => Solicitud::ORIGINALIDAD, 'solicitante_id' => $user->id, 'seguimiento_id' => $seguimiento->id,
            'correo' => (string) $user->email,
            'integrantes' => $this->tesistas($user, $seguimiento)->map(fn ($u) => ['nombre' => MiSolicitudPtiController::nombre($u), 'user_id' => $u->id])->values()->all(),
            'celulares' => trim($data['celulares']), 'programa_estudios' => (string) MiSolicitudPtiController::programaDe($user),
            'ciclo' => $esPpd ? 'PPD' : ($user->condicion === 'Egresado' ? 'Egresado' : (string) $user->ciclo?->nombre),
            'titulo' => trim($data['titulo'], " \"“”"),
            'datos' => array_filter(['etapa' => $data['etapa'], 'modalidad' => $esPpd ? 'PPD' : 'FID',
                'domicilio' => filled($data['domicilio'] ?? null) ? trim($data['domicilio']) : null, 'fecha_pago' => $data['fecha_pago']]),
            'archivo' => $this->guardarLocal($request->file('documento')),
            'comprobante' => collect($request->file('comprobantes'))->map(fn ($f) => $this->guardarLocal($f))->implode(','),
            'declaracion' => true, 'estado' => 'recibida', 'origen' => 'sistema',
        ]);
        $this->notificar(
            AreasTitulacion::destinatarios([AreasTitulacion::SECRETARIA, AreasTitulacion::INVESTIGACION]),
            new TitulacionOriginalidadSolicitada($solicitud, $user), 'originalidad', $solicitud->id
        );

        return response()->json([
            'message' => 'Tu solicitud fue enviada. Recibirás el reporte de originalidad en tu proceso de titulación y en tus notificaciones en un plazo de hasta '
                .Solicitud::DIAS_ORIGINALIDAD.' días hábiles (a más tardar el '.$solicitud->fechaLimite()->format('d/m/Y').').',
            'data' => $this->resumenSolicitud($solicitud),
        ], 201);
    }

    // ── Cuándo NO se puede enviar (mismos casos que la web; null = sí puede) ──

    private function motivoNoPuedePti(User $user, ?Solicitud $ultima): ?string
    {
        if ($user->seguimientoTitulacion()->whereHas('pti')->exists()) {
            return 'Tu proceso de titulación ya está iniciado: tu PTI está registrado.';
        }
        if (PanelDeUsuario::esPersonal($user)) {
            return 'Esta solicitud es para estudiantes y egresados.';
        }
        if ($motivo = InicioProcesoController::motivoNoPuede($user)) {
            return 'Todavía no puedes solicitar la aprobación del PTI: '.$motivo.'.';
        }
        if ($ultima?->estado === 'recibida') {
            return 'Ya tienes una solicitud en revisión: espera la respuesta del Área de Investigación.';
        }

        return null;
    }

    private function motivoNoPuedeCarta(User $user, ?SeguimientoTitulacion $seguimiento, ?CartaAceptacion $ultima): ?string
    {
        if (PanelDeUsuario::esPersonal($user)) {
            return 'Esta solicitud es para estudiantes y egresados.';
        }
        if (! $seguimiento) {
            return 'Todavía no tienes un proceso de titulación iniciado: primero solicita la aprobación de tu PTI.';
        }
        if (! CartaAceptacion::esCorreoInstitucional($user->email)) {
            return 'Solo se puede solicitar con el correo institucional (@'.CartaAceptacion::DOMINIO.').';
        }
        if (! $seguimiento->avance()->hitos['pti_aprobado']['logrado']) {
            return 'Primero debe estar aprobado tu PTI (RD).';
        }
        if ($ultima && $ultima->estado !== 'observada') {
            return 'Tu grupo ya tiene una solicitud '.($ultima->estado === 'emitida' ? 'con la carta emitida.' : 'en revisión.');
        }

        return null;
    }

    private function motivoNoPuedeOriginalidad(User $user, ?SeguimientoTitulacion $seguimiento, ?Solicitud $ultima): ?string
    {
        if (! config('titulacion.originalidad_alumnos')) {
            return 'Por ahora la revisión de originalidad se solicita con el formulario de Google.';
        }
        if (PanelDeUsuario::esPersonal($user)) {
            return 'Esta solicitud es para estudiantes y egresados.';
        }
        if (! $seguimiento) {
            return 'La revisión de originalidad se pide cuando ya tienes tu proceso de titulación iniciado: primero solicita la aprobación de tu PTI.';
        }
        if ($ultima?->estado === 'recibida') {
            return 'Ya tienes una revisión en curso: espera el reporte del Área de Investigación.';
        }

        return null;
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    private function seguimiento(User $user): ?SeguimientoTitulacion
    {
        return $user->seguimientoTitulacion()->with(['pti', 'ptis', 'tesis', 'actas', 'resoluciones', 'cartasAceptacion'])->first();
    }

    /** Quien pide y sus compañeros de grupo según el sistema. */
    private function tesistas(User $user, SeguimientoTitulacion $s)
    {
        return collect([$user])->concat(GrupoTitulacion::companeros($s))->unique('id')->values();
    }

    /** Programa de estudios y ciclo que se saben de la ficha (PTI). */
    private function fijosPti(User $user): array
    {
        $user->loadMissing(['programa', 'ciclo.programa']);
        $orden = $user->ciclo?->ordenCiclo();
        $ciclo = match (true) {
            InicioProcesoController::esPpd($user) => 'PPD',
            $user->condicion === 'Egresado' || $orden >= 999 => 'Egresado',
            $orden === 9 => 'IX Ciclo',
            $orden === 10 => 'X Ciclo',
            default => null,
        };

        return array_filter(['programa_estudios' => MiSolicitudPtiController::programaDe($user), 'ciclo' => $ciclo]);
    }

    /** Programa de estudios y de formación según la ficha (carta). */
    private function programaOficialCarta(User $user): array
    {
        return [
            'programa_estudios' => MiSolicitudPtiController::programaDe($user),
            'programa_formacion' => $user->programa ? ($user->programa->esPpd() ? 'PPD' : 'FID') : null,
        ];
    }

    private function resumenSolicitud(?Solicitud $s): ?array
    {
        if (! $s) {
            return null;
        }

        return [
            'id' => $s->id,
            'estado' => $s->estado,
            'estado_nombre' => Solicitud::ESTADOS[$s->estado] ?? $s->estado,
            'fecha' => $s->created_at?->toDateString(),
            'titulo' => $s->titulo,
            'observacion' => $s->observacion,
            'etapa' => $s->tipo === Solicitud::ORIGINALIDAD ? $s->etapa() : null,
            'similitud' => $s->tipo === Solicitud::ORIGINALIDAD ? $s->similitud() : null,
            'apto' => $s->tipo === Solicitud::ORIGINALIDAD ? $s->esApto() : null,
            'fecha_limite' => $s->fechaLimite()?->toDateString(),
        ];
    }

    /** ['clave' => 'texto'] → [{valor, texto}] (conserva el orden). */
    private function opciones(array $mapa): array
    {
        return collect($mapa)->map(fn ($texto, $valor) => ['valor' => (string) $valor, 'texto' => $texto])->values()->all();
    }

    /** Archivo de solicitud en el disco local, como en la web ('local:{uuid}.{ext}'). */
    private function guardarLocal(UploadedFile $f): string
    {
        $ext = strtolower($f->getClientOriginalExtension() ?: 'pdf');
        $nombre = Str::uuid().'.'.($ext === 'jpeg' ? 'jpg' : $ext);
        $f->storeAs(Solicitud::CARPETA, $nombre, 'local');

        return 'local:'.$nombre;
    }

    /** Comprobante de la carta → Drive de Secretaría; si Drive falla, al servidor (como en la web). */
    private function guardarComprobanteCarta(UploadedFile $archivo, User $user): string
    {
        $ext = strtolower($archivo->getClientOriginalExtension() === 'jpeg' ? 'jpg' : ($archivo->getClientOriginalExtension() ?: 'pdf'));
        try {
            $drive = app(GoogleDriveService::class);
            $carpeta = $drive->carpeta(config('services.google_drive.folders.cartas') ?: CartaAceptacion::CARPETA_DRIVE, CartaAceptacion::SUBCARPETA_DRIVE);
            $titulo = now()->format('Y-m-d H.i').' · '.trim($user->apellidos.', '.$user->name).($user->dni ? ' · DNI '.$user->dni : '').'.'.$ext;

            return 'drive:'.$drive->upload($carpeta, $archivo, str_replace(['/', '\\'], '-', $titulo)).':'.$ext;
        } catch (\Throwable $e) {
            Log::warning('API: comprobante de carta de aceptación no subió a Drive; se guarda en el servidor', ['user' => $user->id, 'error' => $e->getMessage()]);
            $nombre = Str::uuid().'.'.$ext;
            $archivo->storeAs(CartaAceptacion::CARPETA, $nombre, 'local');

            return $nombre;
        }
    }

    /** Un aviso que falla no debe tumbar la solicitud ya guardada (igual que en la web). */
    private function notificar($destinatarios, $aviso, string $que, int $id): void
    {
        try {
            Notification::send($destinatarios, $aviso);
        } catch (\Throwable $e) {
            Log::warning("API: no se pudo notificar la solicitud de {$que}", ['id' => $id, 'error' => $e->getMessage()]);
        }
    }
}
