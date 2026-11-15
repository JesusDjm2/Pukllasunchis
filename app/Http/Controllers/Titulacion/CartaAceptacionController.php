<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Mail\Titulacion\CartaAtendidaMail;
use App\Mail\Titulacion\CartaRecibidaMail;
use App\Mail\Titulacion\Remitente;
use App\Models\Titulacion\CartaAceptacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use App\Notifications\TitulacionCartaAtendida;
use App\Notifications\TitulacionCartaSolicitada;
use App\Services\Titulacion\CartaPresentacion;
use App\Services\Titulacion\GrupoTitulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Carta de aceptación de la Institución Educativa (cierra la etapa PTI). La gestiona Secretaría;
 * Investigación solo la ve.
 *  - El estudiante la solicita desde su intranet, solo con su correo institucional. Recibe al instante
 *    la respuesta automática de Secretaría (en pantalla, en el sistema y por correo: respuesta en
 *    3 días hábiles) y a Secretaría le llega SOLO un aviso en el sistema (sin correo), que abre los registros.
 *  - En «Registros» (filtrables, por fecha) Secretaría revisa datos y comprobante y envía la respuesta
 *    de aprobación por correo, u observa la solicitud.
 * Sin colas: en producción no hay worker, así que el correo sale al enviar.
 */
class CartaAceptacionController extends Controller
{
    // ── Estudiante ──────────────────────────────────────────────────────

    public function crear(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->sinProceso($user)) {
            return $otra;
        }
        $seguimiento = $this->seguimientoPropio($user);
        $ultima = $seguimiento->cartasAceptacion()->first();
        $companeros = GrupoTitulacion::companeros($seguimiento);
        $pti = $seguimiento->pti;

        $valores = [
            'grupo' => collect([$user])->concat($companeros)
                ->map(fn ($u) => trim(mb_convert_case(trim($u->name.' '.$u->apellidos), MB_CASE_TITLE)))->implode(', '),
            'celular' => $user->alumno?->numero ?: ($user->alumnoB?->numero ?: $user->telefono),
            'institucion' => '',
            'director' => '',
            'titulo' => (string) $pti?->titulo,
        ];
        // Al volver a enviar una observada, se parte de lo que ya escribió.
        if ($ultima?->estado === 'observada') {
            $valores = array_merge($valores, $ultima->only(['grupo', 'celular', 'programa_estudios', 'programa_formacion', 'institucion', 'director', 'titulo']));
        }
        // El programa es el de su ficha: no se cambia (solo se elige si la ficha no lo tiene).
        $oficial = $this->programaOficial($user);
        $valores = array_merge($valores, array_filter($oficial));

        return view('titulacion.carta.solicitar', [
            'user' => $user,
            'seguimiento' => $seguimiento,
            'ultima' => $ultima,
            'valores' => $valores,
            'institucional' => CartaAceptacion::esCorreoInstitucional($user->email),
            'ptiAprobado' => $seguimiento->avance()->hitos['pti_aprobado']['logrado'],
            'layout' => $this->layout($user),
            'alumno' => $user->alumno ?? $user->alumnoB,
            'fijos' => array_map(fn ($v) => $v !== null, $oficial),
        ]);
    }

    public function enviar(Request $request)
    {
        $user = $request->user();
        if (! $user->seguimientoTitulacion()->exists()) {
            return redirect()->route('mi-titulacion.carta');
        }
        $seguimiento = $this->seguimientoPropio($user);
        abort_unless(CartaAceptacion::esCorreoInstitucional($user->email), 403, 'Solo se puede solicitar con el correo institucional (@'.CartaAceptacion::DOMINIO.').');
        if (! $seguimiento->avance()->hitos['pti_aprobado']['logrado']) {
            return redirect()->route('mi-titulacion.carta')->with('error', 'Primero debe estar aprobado tu PTI (RD).');
        }
        $ultima = $seguimiento->cartasAceptacion()->first();
        if ($ultima && $ultima->estado !== 'observada') {
            return redirect()->route('mi-titulacion.carta')->with('error', 'Tu grupo ya tiene una solicitud '.($ultima->estado === 'emitida' ? 'con la carta emitida.' : 'en revisión.'));
        }

        // El programa no lo decide el formulario: el de la ficha manda, aunque se altere la página.
        $request->merge(array_filter($this->programaOficial($user)));

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
            'comprobante.uploaded' => 'No se pudo subir el comprobante: puede ser muy pesado (máximo 5 MB). Pruebe con una foto más liviana o un PDF.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], [
            'grupo' => 'grupo de investigación', 'programa_estudios' => 'programa de estudios', 'programa_formacion' => 'programa de formación',
            'institucion' => 'denominación de la institución educativa', 'director' => 'nombre del director(a)', 'titulo' => 'título del trabajo de investigación',
        ]);

        $nombre = $this->guardarComprobante($request->file('comprobante'), $user);

        // Una fila por integrante (el grupo que el sistema conoce), con el mismo lote.
        $seguimientos = collect([$seguimiento])->concat(
            GrupoTitulacion::companeros($seguimiento)->map(fn ($u) => $u->seguimientoTitulacion)->filter()
        )->unique('id');
        $lote = (string) Str::uuid();
        $principal = null;
        DB::transaction(function () use ($seguimientos, $lote, $user, $data, $nombre, &$principal) {
            foreach ($seguimientos as $s) {
                $fila = CartaAceptacion::create([
                    'lote' => $lote, 'seguimiento_id' => $s->id, 'solicitante_id' => $user->id, 'correo' => $user->email,
                    'grupo' => trim($data['grupo']), 'celular' => trim($data['celular']),
                    'programa_estudios' => $data['programa_estudios'], 'programa_formacion' => $data['programa_formacion'],
                    'institucion' => trim($data['institucion']), 'director' => trim($data['director']), 'titulo' => trim($data['titulo'], " \"“”"),
                    'comprobante' => $nombre, 'declaracion' => true, 'estado' => 'enviada',
                ]);
                $principal ??= $fila;
            }
        });

        $this->avisarSecretaria($principal, $user);
        $this->responderRecepcion($principal, $user);

        return redirect()->route('mi-titulacion.carta')->with('success', $principal->mensajeRecepcion());
    }

    // ── Archivos (comprobante y carta): gestores o integrantes del grupo ──

    public function archivo(Request $request, CartaAceptacion $carta, string $cual)
    {
        abort_unless(in_array($cual, ['comprobante', 'carta', 'modelo'], true), 404);
        $user = $request->user();
        $autorizado = $user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria'])
            || $carta->delLote()->whereHas('seguimiento', fn ($q) => $q->where('user_id', $user->id))->exists();
        abort_unless($autorizado, 403);

        // Modelo de Carta de Aceptación (Word, igual para todos): solo una vez aprobada la solicitud.
        if ($cual === 'modelo') {
            abort_unless($carta->estado === 'emitida' || $user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria']), 404);

            return response()->download(resource_path(CartaPresentacion::MODELO_ACEPTACION), 'Modelo de Carta de Aceptación.docx', ['Content-Type' => CartaPresentacion::TIPO_DOCX]);
        }

        // Importada del formulario de Google: el comprobante es un enlace de Drive.
        if ($cual === 'comprobante' && $carta->comprobanteEsEnlace()) {
            return redirect()->away($carta->comprobante);
        }
        // Subido a la unidad compartida de Secretaría: pasa por el servidor (el estudiante no necesita acceso a Drive).
        if ($cual === 'comprobante' && ($enDrive = $carta->comprobanteEnDrive())) {
            [$id, $ext] = $enDrive;
            $archivoDrive = app(\App\Services\GoogleDriveService::class)->streamFile($id);
            abort_unless($archivoDrive['status'] === 200, 404, 'No se pudo abrir el comprobante en Drive.');
            $tipo = ['pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg'][$ext] ?? $archivoDrive['contentType'];
            $cuerpo = $archivoDrive['body'];

            return response()->stream(function () use ($cuerpo) {
                while (! $cuerpo->eof()) {
                    echo $cuerpo->read(65536);
                }
            }, 200, array_filter([
                'Content-Type' => $tipo,
                'Content-Length' => $archivoDrive['contentLen'],
                'Content-Disposition' => 'inline; filename="Comprobante.'.$ext.'"',
                'Cache-Control' => 'private, max-age=3600',
            ]));
        }
        $ruta = CartaAceptacion::ruta($carta->{$cual});
        abort_unless((bool) $ruta, 404);
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        if ($ext === 'docx') {
            return response()->download($ruta, CartaPresentacion::nombreArchivo($carta->numero_carta, $carta->atendido_en), ['Content-Type' => CartaPresentacion::TIPO_DOCX]);
        }
        $nombre = ($cual === 'carta' ? 'Carta' : 'Comprobante').' - '.Str::ascii(Str::limit($carta->institucion, 50, '')).'.'.$ext;

        return response()->file($ruta, [
            'Content-Type' => ['pdf' => 'application/pdf', 'png' => 'image/png'][$ext] ?? 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $nombre).'"',
        ]);
    }

    // ── Secretaría (Investigación: solo lectura) ────────────────────────

    /** Registros de solicitudes, del más reciente al más antiguo, con filtros. Una fila por solicitud (lote). */
    public function registros(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:'.implode(',', array_keys(CartaAceptacion::ESTADOS)),
            'formacion' => 'nullable|in:'.implode(',', array_keys(CartaAceptacion::PROGRAMAS_FORMACION)),
            'origen' => 'nullable|in:'.implode(',', array_keys(CartaAceptacion::ORIGENES)),
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
            'resaltar' => 'nullable|string|max:36',
        ]);

        // La primera fila de cada lote es la de quien la envió.
        $primeras = CartaAceptacion::selectRaw('MIN(id)')->groupBy('lote');
        $q = CartaAceptacion::whereIn('id', $primeras)->with(['solicitante.programa', 'seguimiento', 'atendidoPor']);
        if (filled($filtros['q'] ?? null)) {
            $t = '%'.trim($filtros['q']).'%';
            $q->where(fn ($w) => $w->where('grupo', 'like', $t)->orWhere('institucion', 'like', $t)->orWhere('director', 'like', $t)
                ->orWhere('titulo', 'like', $t)->orWhere('correo', 'like', $t)->orWhere('celular', 'like', $t)
                ->orWhereHas('solicitante', fn ($s) => $s->where('dni', 'like', $t)->orWhere('apellidos', 'like', $t)->orWhere('name', 'like', $t))
                ->orWhereHas('seguimiento', fn ($s) => $s->where('numero_expediente', 'like', $t)));
        }
        $q->when($filtros['estado'] ?? null, fn ($w, $e) => $w->where('estado', $e))
            ->when($filtros['formacion'] ?? null, fn ($w, $f) => $w->where('programa_formacion', $f))
            ->when($filtros['origen'] ?? null, fn ($w, $o) => $w->where('origen', $o))
            ->when($filtros['desde'] ?? null, fn ($w, $d) => $w->whereDate('created_at', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($w, $d) => $w->whereDate('created_at', '<=', $d));

        $registros = $q->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $conteo = CartaAceptacion::whereIn('id', CartaAceptacion::selectRaw('MIN(id)')->groupBy('lote'))
            ->selectRaw('estado, COUNT(*) total')->groupBy('estado')->pluck('total', 'estado');

        return view('titulacion.carta.registros', [
            'registros' => $registros,
            'conteo' => $conteo,
            'filtros' => $filtros,
            'resaltar' => $filtros['resaltar'] ?? null,
            'puedeGestionar' => \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.cartas.atender'),
        ]);
    }

    public function atender(Request $request, CartaAceptacion $carta)
    {
        $conCarta = in_array($request->input('accion'), ['aprobar', 'previa'], true);
        $data = $request->validate([
            'accion' => 'required|in:aprobar,observar,previa',
            'mensaje' => 'required_if:accion,aprobar|nullable|string|max:5000',
            'observacion' => 'required_if:accion,observar|nullable|string|max:1500',
            // Datos de la Carta de Presentación (Word) que se adjunta al aprobar.
            'numero_carta' => [$conCarta ? 'required' : 'nullable', 'regex:/^\d{1,4}$/'],
            'fecha_carta' => ($conCarta ? 'required' : 'nullable').'|date',
            'destinatario_genero' => ($conCarta ? 'required' : 'nullable').'|in:f,m',
            'destinatario' => ($conCarta ? 'required' : 'nullable').'|string|max:200',
            'art_ie' => 'nullable|string|max:20',
            'institucion_carta' => ($conCarta ? 'required' : 'nullable').'|string|max:300',
            'periodo' => ($conCarta ? 'required' : 'nullable').'|string|max:20',
            'genero' => ($conCarta ? 'required' : 'nullable').'|in:'.implode(',', array_keys(\App\Services\Titulacion\PlantillasRd::GENEROS)),
            'titulo_carta' => ($conCarta ? 'required' : 'nullable').'|string|max:1000',
        ], [
            'mensaje.required_if' => 'Escriba el mensaje del correo de aprobación.',
            'observacion.required_if' => 'Escriba qué debe corregir el estudiante.',
            'numero_carta.regex' => 'El N° de carta es solo el número (ej.: 45).',
            'destinatario_genero.required' => 'Indique si la carta va dirigida a un Director o a una Directora.',
        ], [
            'numero_carta' => 'N° de carta', 'fecha_carta' => 'fecha de la carta', 'destinatario' => 'nombre del director(a)',
            'institucion_carta' => 'institución educativa', 'periodo' => 'periodo académico', 'titulo_carta' => 'título de la investigación',
        ]);
        $data['art_ie'] = (string) ($data['art_ie'] ?? '');
        $volver = redirect()->route('titulacion.cartas.index', ['resaltar' => $carta->lote]);

        // Vista previa: descarga el Word lleno, sin registrar nada.
        if ($data['accion'] === 'previa') {
            $ruta = CartaPresentacion::generar($carta, $data);

            return response()->download($ruta, CartaPresentacion::nombreArchivo($data['numero_carta'], $data['fecha_carta']), ['Content-Type' => CartaPresentacion::TIPO_DOCX])->deleteFileAfterSend();
        }
        if ($carta->estado !== 'enviada') {
            return $volver->with('error', 'Esta solicitud ya fue atendida.');
        }

        $aprobar = $data['accion'] === 'aprobar';
        $cambios = ['estado' => $aprobar ? 'emitida' : 'observada', 'atendido_por' => $request->user()->id, 'atendido_en' => now(),
            'observacion' => trim($aprobar ? $data['mensaje'] : $data['observacion'])];
        if ($aprobar) {
            $temporal = CartaPresentacion::generar($carta, $data);
            $nombre = Str::uuid().'.docx';
            Storage::disk('local')->put(CartaAceptacion::CARPETA.'/'.$nombre, file_get_contents($temporal));
            @unlink($temporal);
            $cambios['carta'] = $nombre;
            $cambios['numero_carta'] = ltrim($data['numero_carta'], '0') ?: '0';
        }
        $carta->delLote()->update($cambios);
        $carta->refresh();

        $this->avisarGrupo($carta);

        return $volver->with('success', $aprobar
            ? 'Respuesta de aprobación enviada a todo el grupo (aviso en el sistema y correo). Ya puede continuar con el TI · Bachiller.'
            : 'Solicitud observada. Se avisó a los integrantes para que la corrijan y la envíen de nuevo.');
    }

    /**
     * Quien tramitó su carta antes de que existiera el formulario (Google Forms, en papel…): Investigación
     * la registra como aprobada para todo el grupo, sin enviar correo, y así se abre el TI · Bachiller.
     */
    public function registrarExterna(Request $request, User $egresado)
    {
        $data = $request->validate([
            'institucion' => 'required|string|min:3|max:300',
            'carta' => 'nullable|file|mimes:pdf|max:10240',
        ], [], ['institucion' => 'institución educativa']);
        $seguimiento = $egresado->seguimientoTitulacion()->with(['pti', 'cartasAceptacion'])->firstOrFail();
        $volver = redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso', 'paso' => 'pti_carta']);
        if ($seguimiento->cartasAceptacion->firstWhere('estado', 'emitida')) {
            return $volver->with('error', 'Este grupo ya tiene la carta aprobada.');
        }

        $nombre = null;
        if ($request->hasFile('carta')) {
            $nombre = Str::uuid().'.pdf';
            $request->file('carta')->storeAs(CartaAceptacion::CARPETA, $nombre, 'local');
        }
        $companeros = GrupoTitulacion::companeros($seguimiento);
        $seguimientos = collect([$seguimiento])->concat($companeros->map(fn ($u) => $u->seguimientoTitulacion)->filter())->unique('id');
        $lote = (string) Str::uuid();
        DB::transaction(function () use ($seguimientos, $lote, $egresado, $companeros, $data, $nombre, $seguimiento, $request) {
            foreach ($seguimientos as $s) {
                CartaAceptacion::create([
                    'lote' => $lote, 'seguimiento_id' => $s->id, 'solicitante_id' => $egresado->id, 'correo' => (string) $egresado->email,
                    'grupo' => collect([$egresado])->concat($companeros)->map(fn ($u) => trim($u->name.' '.$u->apellidos))->implode(', '),
                    'celular' => '', 'programa_estudios' => '', 'programa_formacion' => '',
                    'institucion' => trim($data['institucion']), 'director' => '', 'titulo' => (string) $seguimiento->pti?->titulo,
                    'comprobante' => '', 'declaracion' => false, 'estado' => 'emitida', 'carta' => $nombre,
                    'observacion' => 'Registrada por Secretaría: trámite realizado fuera del sistema.', 'origen' => 'manual',
                    'atendido_por' => $request->user()->id, 'atendido_en' => now(),
                ]);
            }
        });

        return $volver->with('success', 'Carta registrada como aprobada para '.$seguimientos->count().' integrante(s). Ya puede continuar con el TI · Bachiller.');
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    private function seguimientoPropio(User $user): SeguimientoTitulacion
    {
        $seguimiento = $user->seguimientoTitulacion()->with(['pti', 'ptis', 'tesis', 'actas', 'resoluciones', 'cartasAceptacion'])->first();
        abort_unless((bool) $seguimiento, 403, 'Todavía no tienes un proceso de titulación iniciado.');

        return $seguimiento;
    }

    /**
     * Quien abre el formulario sin tener proceso de titulación: el personal va a los registros de cartas
     * (lo que le sirve); el estudiante ve una página que le explica qué falta, en vez de un error.
     */
    private function sinProceso(User $user)
    {
        if ($user->seguimientoTitulacion()->exists()) {
            return null;
        }
        $panel = \App\Support\PanelDeUsuario::url($user);

        // Personal (admin, docente, tutor, Secretaría, Investigación…): página propia, sin redirigir a ningún panel
        // (antes iba a Registros y daba 403 a un admin sin rol de Secretaría o Investigación).
        if (\App\Support\PanelDeUsuario::esPersonal($user)) {
            return view('titulacion.carta.solo-estudiantes', [
                'user' => $user,
                'panel' => $panel,
                'puedeRegistros' => $user->hasAnyRole(['super-admin', 'secretaria', 'gestor-titulacion']),
            ]);
        }

        return view('titulacion.carta.sin-proceso', [
            'user' => $user,
            'layout' => $this->layout($user),
            'alumno' => $user->alumno ?? $user->alumnoB,
            'volver' => $panel,
            'institucional' => CartaAceptacion::esCorreoInstitucional($user->email),
        ]);
    }

    /** Layout según quién es: PPD (alumno o egresado), FID activo o egresado FID (página simple). */
    private function layout(User $user): string
    {
        return match (true) {
            (bool) $user->alumnoB => 'layouts.profesionalizacion',
            $user->condicion !== 'Egresado' && (bool) $user->alumno => 'layouts.alumno',
            default => 'titulacion.carta._simple',
        };
    }

    /** Programa de estudios y de formación según la ficha (null en lo que la ficha no permite saber). */
    private function programaOficial(User $user): array
    {
        return [
            'programa_estudios' => $this->programaEstudios($user),
            'programa_formacion' => $user->programa ? ($user->programa->esPpd() ? 'PPD' : 'FID') : null,
        ];
    }

    private function programaEstudios(User $user): ?string
    {
        $p = $user->programa;
        if (! $p) {
            return null;
        }

        return match (true) {
            $p->esInicial() => 'Educación Inicial',
            (bool) $p->eib => 'Educación Primaria Intercultural Bilingüe',
            $p->esPrimaria() => 'Educación Primaria',
            default => null,
        };
    }

    /**
     * Comprobante → unidad compartida de Secretaría en Google Drive (subcarpeta de comprobantes), con un
     * nombre que se reconoce al navegar la carpeta. Devuelve «drive:{id}:{ext}». Si Drive falla, se guarda en
     * el servidor (como antes) para no perder la solicitud.
     */
    private function guardarComprobante(\Illuminate\Http\UploadedFile $archivo, User $user): string
    {
        $ext = strtolower($archivo->getClientOriginalExtension() === 'jpeg' ? 'jpg' : ($archivo->getClientOriginalExtension() ?: 'pdf'));
        try {
            $drive = app(\App\Services\GoogleDriveService::class);
            $carpeta = $drive->carpeta(config('services.google_drive.folders.cartas') ?: CartaAceptacion::CARPETA_DRIVE, CartaAceptacion::SUBCARPETA_DRIVE);
            $titulo = now()->format('Y-m-d H.i').' · '.trim($user->apellidos.', '.$user->name).($user->dni ? ' · DNI '.$user->dni : '').'.'.$ext;
            $id = $drive->upload($carpeta, $archivo, str_replace(['/', '\\'], '-', $titulo));

            return 'drive:'.$id.':'.$ext;
        } catch (\Throwable $e) {
            Log::warning('Comprobante de carta de aceptación: no se pudo subir a Drive; se guarda en el servidor', ['user' => $user->id, 'error' => $e->getMessage()]);
            $nombre = Str::uuid().'.'.$ext;
            $archivo->storeAs(CartaAceptacion::CARPETA, $nombre, 'local');

            return $nombre;
        }
    }

    /** SOLO a Secretaría y SOLO en el sistema (sin correo): el aviso abre los registros con la solicitud resaltada. */
    private function avisarSecretaria(CartaAceptacion $carta, User $solicitante): void
    {
        $destinatarios = User::role('secretaria')->get();
        if ($destinatarios->isEmpty()) {
            $destinatarios = User::role('super-admin')->get();
        }
        try {
            Notification::send($destinatarios, new TitulacionCartaSolicitada($carta, $solicitante));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la solicitud de carta de aceptación', ['carta' => $carta->id, 'error' => $e->getMessage()]);
        }
    }

    /** Respuesta automática de Secretaría a quien envió: aviso en su intranet y correo (recibida, respuesta en 3 días hábiles). */
    private function responderRecepcion(CartaAceptacion $carta, User $solicitante): void
    {
        try {
            $solicitante->notify(new TitulacionCartaAtendida($carta));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la recepción de la carta de aceptación', ['carta' => $carta->id, 'error' => $e->getMessage()]);
        }
        try {
            Mail::mailer(Remitente::mailer('secretaria'))->to($carta->correo)->send(new CartaRecibidaMail($carta, $solicitante));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar la respuesta automática de la carta de aceptación', ['carta' => $carta->id, 'error' => $e->getMessage()]);
        }
    }

    /** Aviso y correo a cada integrante del grupo (correo institucional). */
    private function avisarGrupo(CartaAceptacion $carta): void
    {
        $filas = $carta->delLote()->with('seguimiento.user')->get();
        foreach ($filas as $fila) {
            $persona = $fila->seguimiento?->user;
            if (! $persona) {
                continue;
            }
            try {
                $persona->notify(new TitulacionCartaAtendida($fila));
            } catch (\Throwable $e) {
                Log::warning('No se pudo notificar la carta de aceptación', ['carta' => $fila->id, 'error' => $e->getMessage()]);
            }
            if (filled($persona->email)) {
                try {
                    Mail::mailer(Remitente::mailer('secretaria'))->to($persona->email)->send(new CartaAtendidaMail($fila, $persona));
                } catch (\Throwable $e) {
                    Log::warning('No se pudo enviar el correo de la carta de aceptación', ['carta' => $fila->id, 'error' => $e->getMessage()]);
                }
            }
        }
    }
}
