<?php

namespace App\Http\Controllers;

use App\Mail\TutoriaMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\Sugerencia;
use App\Models\Tutoria;
use App\Models\User;
use App\Notifications\TutoriaAtendida;
use App\Notifications\TutoriaCreada;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TutoriaController extends Controller
{
    // ── Formulario público (sin login, identificado por selección de alumno) ──

    public function publicCreate(Request $request)
    {
        $programas = Programa::fid()->orderBy('nombre')->get();

        $cicloPreseleccionado = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        $docentesElegibles = Tutoria::docentesElegibles($cicloPreseleccionado?->id);

        return view('tutorias.public-create', compact('programas', 'cicloPreseleccionado', 'docentesElegibles'));
    }

    public function publicStore(Request $request)
    {
        // Honeypot: campo invisible para humanos. Si viene lleno, es un bot.
        if ($request->filled('sitio_web')) {
            return redirect()->route('tutorias.public.create')
                ->with('success', '¡Solicitud enviada correctamente! El tutor se pondrá en contacto contigo.');
        }

        // Time-trap: un envío en menos de 2s desde que se cargó el formulario es casi
        // siempre un bot.
        $tsForm = (int) $request->input('ts_form', 0);
        if ($tsForm > 0 && (now()->timestamp - $tsForm) < 2) {
            return redirect()->route('tutorias.public.create')
                ->withInput()
                ->with('error', 'Hubo un problema al procesar tu solicitud. Por favor, inténtalo nuevamente.');
        }

        $data = $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id'    => 'required|exists:ciclos,id',
            'alumno_id'   => 'required|exists:alumnos,id',
        ] + $this->reglasOpcion(), $this->mensajesOpcion());

        // El alumno elegido en el <select> debe pertenecer realmente al ciclo enviado,
        // para que nadie pueda manipular el formulario y enviar la solicitud a nombre
        // de un alumno de otro ciclo.
        $alumnoValido = Alumno::where('id', $data['alumno_id'])
            ->where('ciclo_id', $data['ciclo_id'])
            ->exists();

        if (! $alumnoValido) {
            return redirect()->route('tutorias.public.create', ['ciclo' => $request->input('ciclo_id')])
                ->withInput()
                ->with('error', 'El alumno seleccionado no pertenece al ciclo indicado. Por favor, inténtalo nuevamente.');
        }

        $tutoria = Tutoria::create($this->limpiarOpcion($data));
        $tutoria->load(['alumno', 'ciclo.programa', 'docenteSolicitado']);

        $this->notificarTutores($tutoria);

        return redirect()->route('tutorias.public.create')
            ->with('success', $tutoria->pideOtroDocente()
                ? '¡Solicitud enviada correctamente! El/la docente que elegiste se pondrá en contacto contigo.'
                : '¡Solicitud enviada correctamente! El tutor de tu ciclo se pondrá en contacto contigo.');
    }

    // ── Opción de conversación (QR e intranet) ─────────────────────────────

    private function reglasOpcion(): array
    {
        return [
            'urgencia'   => 'required|in:'.implode(',', array_keys(Tutoria::OPCIONES)),
            'docente_id' => 'nullable|required_if:urgencia,'.Tutoria::OTRO_DOCENTE.'|integer|exists:users,id',
            'motivo'     => 'nullable|string|max:3000',
        ];
    }

    private function mensajesOpcion(): array
    {
        return [
            'urgencia.required'      => 'Elige una respuesta a “¿Te gustaría conversar individualmente con el tutor?”.',
            'urgencia.in'            => 'Elige una respuesta a “¿Te gustaría conversar individualmente con el tutor?”.',
            'docente_id.required_if' => 'Elige con qué docente te gustaría conversar.',
        ];
    }

    /** El docente solo se guarda con la opción "otro docente" y debe ser uno de la lista. */
    private function limpiarOpcion(array $data): array
    {
        if ($data['urgencia'] !== Tutoria::OTRO_DOCENTE) {
            $data['docente_id'] = null;
        } else {
            $permitidos = collect(Tutoria::docentesElegibles())->flatten(1)->pluck('id');
            if (! $permitidos->contains((int) $data['docente_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'docente_id' => 'El docente elegido no está disponible. Elige otro de la lista.',
                ]);
            }
        }

        $data['motivo'] = trim((string) ($data['motivo'] ?? '')) ?: null;

        return $data;
    }

    // ── Intranet del alumno ────────────────────────────────────────────────
    // Los mismos formularios del QR, pero con la sesión iniciada: el alumno no elige
    // programa, ciclo ni su nombre (salen de su cuenta) y ve el estado de lo que envió.

    /** Alumno FID con ciclo; null si no aplica (PPD, sin ficha o sin ciclo). */
    public static function alumnoDeLaSesion(): ?Alumno
    {
        $alumno = auth()->user()?->alumno;

        return ($alumno && $alumno->ciclo_id) ? $alumno->loadMissing('ciclo.programa', 'ciclo.tutores') : null;
    }

    public function alumnoPanel()
    {
        $alumno = self::alumnoDeLaSesion();
        $tutorias = $alumno ? Tutoria::where('alumno_id', $alumno->id)->latest()->take(15)->get() : collect();
        $sugerencias = $alumno ? Sugerencia::where('alumno_id', $alumno->id)->latest()->take(15)->get() : collect();
        $docentesElegibles = $alumno ? Tutoria::docentesElegibles($alumno->ciclo_id) : [];

        return view('alumnos.atencion', compact('alumno', 'tutorias', 'sugerencias', 'docentesElegibles'));
    }

    public function alumnoStore(Request $request)
    {
        $alumno = self::alumnoDeLaSesion();
        abort_unless($alumno, 403);

        $data = $request->validate($this->reglasOpcion(), $this->mensajesOpcion());

        $tutoria = Tutoria::create($this->limpiarOpcion($data) + [
            'programa_id' => $alumno->ciclo->programa_id,
            'ciclo_id'    => $alumno->ciclo_id,
            'alumno_id'   => $alumno->id,
        ]);
        $tutoria->load(['alumno', 'ciclo.programa', 'docenteSolicitado']);

        $this->notificarTutores($tutoria);

        return redirect()->route('alumno.atencion', ['tab' => 'tutoria'])
            ->with('success', $tutoria->pideOtroDocente()
                ? 'Solicitud enviada. El/la docente que elegiste ya fue notificado y se pondrá en contacto contigo.'
                : 'Solicitud enviada. Tu tutor(a) ya fue notificado y se pondrá en contacto contigo.');
    }

    // ── Panel admin ────────────────────────────────────────────────────────

    public function qr(Request $request)
    {
        $ciclo = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        $url = route('tutorias.public.create', $ciclo ? ['ciclo' => $ciclo->id] : []);
        $descripcion = 'Escanea el código con tu celular y cuéntanos cuándo y cómo te gustaría conversar. Tu tutor(a) será notificado de inmediato.';

        $data = [
            'url'         => $url,
            'tipo'        => 'tutoria',
            'titulo'      => 'Solicitud de Tutoría Individual',
            'descripcion' => $descripcion,
            'contexto'    => $ciclo ? (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre : null,
            'cicloNombre' => $ciclo ? 'Ciclo ' . $ciclo->nombre : null,
            'programaNombre' => $ciclo ? optional($ciclo->programa)->nombre : null,
            'tutor'       => $ciclo ? $ciclo->tutores->map->nombreCorto()->filter()->join(', ') : null,
        ];

        if ($request->boolean('panel')) {
            return view('partials.qr-poster', $data);
        }

        return view('admin.tutorias.qr', $data);
    }

    // ── Marcar estado (panel de tutor / admin) ────────────────────────────

    public function marcarEstado(Request $request, Tutoria $tutoria)
    {
        $data = $request->validate([
            'notas_atencion' => 'nullable|string',
        ]);

        $pasaAAtendida = $tutoria->estado !== 'atendida';

        $tutoria->estado = $tutoria->estado === 'atendida' ? 'pendiente' : 'atendida';
        $tutoria->atendido_por = $tutoria->estado === 'atendida' ? auth()->id() : null;
        $tutoria->atendido_at = $tutoria->estado === 'atendida' ? now() : null;
        $tutoria->notas_atencion = $data['notas_atencion'] ?? $tutoria->notas_atencion;
        $tutoria->save();

        // Solo al pasar a "atendida" (no al reabrirla) y solo si el alumno
        // identificado tiene cuenta propia — así se entera en su perfil.
        if ($pasaAAtendida && $tutoria->alumno?->user) {
            try {
                $tutoria->alumno->user->notify(new TutoriaAtendida($tutoria));
            } catch (\Throwable $e) {
                Log::warning("Notificación de tutoría atendida a alumno {$tutoria->alumno_id}: ".$e->getMessage());
            }
        }

        return back()->with('success', 'Solicitud de tutoría actualizada.');
    }

    public function destroy(Tutoria $tutoria)
    {
        $tutoria->delete();

        return back()->with('success', 'Solicitud de tutoría eliminada correctamente.');
    }

    // ── Notificación ───────────────────────────────────────────────────────

    private function notificarTutores(Tutoria $tutoria): void
    {
        $ciclo = Ciclo::with('tutores')->find($tutoria->ciclo_id);

        // Copia al correo de administración: se envía siempre, independiente de
        // si el ciclo tiene o no tutor(es) asignados.
        if ($adminEmail = env('NOTIF_EMAIL_ADMIN')) {
            try {
                Mail::to($adminEmail)->send(new TutoriaMail($tutoria));
            } catch (\Throwable $e) {
                Log::warning('Notificación de tutoría a admin: '.$e->getMessage());
            }
        }

        // Alerta en el sistema (campanita) solo para los super-admin (decisión 23/10: a las cuentas admin
        // —Cobranzas, Titulación…— no les corresponde): siempre,
        // de todos los tutores/ciclos — el correo ya se cubrió arriba, aquí solo
        // se genera la notificación in-app (via() la deja sin canal de mail).
        foreach (User::role('super-admin')->get() as $adminUser) {
            try {
                $adminUser->notify(new TutoriaCreada($tutoria));
            } catch (\Throwable $e) {
                Log::warning("Notificación de tutoría a admin {$adminUser->id}: ".$e->getMessage());
            }
        }

        // Si el alumno pidió hablar con otro docente, el aviso va a ese docente y
        // no a los tutores del ciclo (igual la ven en su panel, por ser de su ciclo).
        $destinatarios = $tutoria->pideOtroDocente()
            ? collect([$tutoria->docenteSolicitado])->filter()
            : ($ciclo?->tutores ?? collect());

        if (!$ciclo || $destinatarios->isEmpty()) {
            return;
        }

        $alumno = $tutoria->alumno;
        $nombreAl = $alumno ? $alumno->apellidos.', '.$alumno->nombres : ($tutoria->nombre_alumno ?: 'Alumno');
        $motivo = (string) $tutoria->motivo;

        $textoWA = "🙋 *Nueva solicitud de tutoría - EESP Pukllasunchis*\n"
            ."💬 ¿Conversar?: ".(Tutoria::OPCIONES[$tutoria->urgencia][0] ?? $tutoria->urgencia)."\n"
            ."👤 Alumno: {$nombreAl}\n"
            ."📞 Contacto: ".($alumno?->numero ?? '—')."\n"
            ."📚 Ciclo: {$ciclo->nombre}\n"
            .($motivo !== ''
                ? "📝 Motivo: ".mb_substr($motivo, 0, 200).(mb_strlen($motivo) > 200 ? '...' : '')
                : "📝 Sin mensaje adicional");

        foreach ($destinatarios as $tutor) {
            try {
                $tutor->notify(new TutoriaCreada($tutoria));
            } catch (\Throwable $e) {
                Log::warning("Notificación tutoría a tutor {$tutor->id}: ".$e->getMessage());
            }

            if ($tutor->telefono && $tutor->whatsapp_key) {
                $phone = preg_replace('/\D/', '', $tutor->telefono);
                WhatsappService::send($phone, $tutor->whatsapp_key, $textoWA);
            }
        }
    }
}
