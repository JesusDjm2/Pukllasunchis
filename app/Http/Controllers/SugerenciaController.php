<?php

namespace App\Http\Controllers;

use App\Mail\SugerenciaMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\Sugerencia;
use App\Models\User;
use App\Notifications\SugerenciaCreada;
use App\Notifications\SugerenciaRevisada;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SugerenciaController extends Controller
{
    // ── Formulario público (sin login, identificado por selección de alumno) ──

    public function publicCreate(Request $request)
    {
        $programas = Programa::fid()->orderBy('nombre')->get();

        $cicloPreseleccionado = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        return view('sugerencias.public-create', compact('programas', 'cicloPreseleccionado'));
    }

    public function publicStore(Request $request)
    {
        // Honeypot: campo invisible para humanos. Si viene lleno, es un bot.
        if ($request->filled('sitio_web')) {
            return redirect()->route('sugerencias.public.create')
                ->with('success', '¡Gracias por tu sugerencia!');
        }

        // Time-trap: un envío en menos de 2s desde que se cargó el formulario es casi
        // siempre un bot.
        $tsForm = (int) $request->input('ts_form', 0);
        if ($tsForm > 0 && (now()->timestamp - $tsForm) < 2) {
            return redirect()->route('sugerencias.public.create')
                ->withInput()
                ->with('error', 'Hubo un problema al procesar tu solicitud. Por favor, inténtalo nuevamente.');
        }

        $data = $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id'    => 'required|exists:ciclos,id',
            'alumno_id'   => 'required|exists:alumnos,id',
            'mensaje'     => 'required|string|min:10',
        ]);

        $data['es_anonima'] = false;

        // El alumno elegido en el <select> debe pertenecer realmente al ciclo enviado,
        // para que nadie pueda manipular el formulario y enviar la sugerencia a nombre
        // de un alumno de otro ciclo.
        $alumnoValido = Alumno::where('id', $data['alumno_id'])
            ->where('ciclo_id', $data['ciclo_id'])
            ->exists();

        if (! $alumnoValido) {
            return redirect()->route('sugerencias.public.create', ['ciclo' => $request->input('ciclo_id')])
                ->withInput()
                ->with('error', 'El alumno seleccionado no pertenece al ciclo indicado. Por favor, inténtalo nuevamente.');
        }

        $sugerencia = Sugerencia::create($data);
        $sugerencia->load(['alumno', 'ciclo.programa']);

        $this->notificarTutores($sugerencia);

        return redirect()->route('sugerencias.public.create')
            ->with('success', '¡Gracias por tu sugerencia! Será revisada por el tutor de tu ciclo.');
    }

    /** Sugerencia desde la intranet del alumno (la página la arma TutoriaController::alumnoPanel). */
    public function alumnoStore(Request $request)
    {
        $alumno = TutoriaController::alumnoDeLaSesion();
        abort_unless($alumno, 403);

        $data = $request->validate([
            'mensaje' => 'required|string|min:10|max:3000',
        ], [
            'mensaje.min' => 'Escribe al menos 10 caracteres para que tu tutor(a) entienda la idea.',
        ]);

        $sugerencia = Sugerencia::create($data + [
            'programa_id' => $alumno->ciclo->programa_id,
            'ciclo_id'    => $alumno->ciclo_id,
            'alumno_id'   => $alumno->id,
            'es_anonima'  => false,
        ]);
        $sugerencia->load(['alumno', 'ciclo.programa']);

        $this->notificarTutores($sugerencia);

        return redirect()->route('alumno.atencion', ['tab' => 'sugerencia'])
            ->with('success', '¡Gracias por tu sugerencia! Tu tutor(a) la revisará.');
    }

    // ── Panel admin ────────────────────────────────────────────────────────

    public function qr(Request $request)
    {
        $ciclo = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        $url = route('sugerencias.public.create', $ciclo ? ['ciclo' => $ciclo->id] : []);
        $descripcion = 'Escanea este código para dejarnos tu sugerencia o proponer un tema para trabajar en las tutorías.';

        $data = [
            'url'         => $url,
            'tipo'        => 'sugerencia',
            'titulo'      => 'Buzón de Sugerencias de Tutoría',
            'descripcion' => $descripcion,
            'contexto'    => $ciclo ? (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre : null,
            'tutor'       => $ciclo ? $ciclo->tutores->map->nombreCorto()->filter()->join(', ') : null,
        ];

        if ($request->boolean('panel')) {
            return view('partials.qr-poster', $data);
        }

        return view('admin.sugerencias.qr', $data);
    }

    // ── Marcar estado (panel de tutor / admin) ────────────────────────────

    public function marcarEstado(Request $request, Sugerencia $sugerencia)
    {
        $data = $request->validate([
            'notas_atencion' => 'nullable|string',
        ]);

        $pasaARevisada = $sugerencia->estado !== 'revisada';

        $sugerencia->estado = $sugerencia->estado === 'revisada' ? 'pendiente' : 'revisada';
        $sugerencia->atendido_por = $sugerencia->estado === 'revisada' ? auth()->id() : null;
        $sugerencia->atendido_at = $sugerencia->estado === 'revisada' ? now() : null;
        $sugerencia->notas_atencion = $data['notas_atencion'] ?? $sugerencia->notas_atencion;
        $sugerencia->save();

        // Solo al pasar a "revisada" (no al reabrirla), y nunca si es anónima
        // o si el alumno identificado no tiene cuenta propia.
        if ($pasaARevisada && ! $sugerencia->es_anonima && $sugerencia->alumno?->user) {
            try {
                $sugerencia->alumno->user->notify(new SugerenciaRevisada($sugerencia));
            } catch (\Throwable $e) {
                Log::warning("Notificación de sugerencia revisada a alumno {$sugerencia->alumno_id}: ".$e->getMessage());
            }
        }

        return back()->with('success', 'Sugerencia actualizada.');
    }

    public function destroy(Sugerencia $sugerencia)
    {
        $sugerencia->delete();

        return back()->with('success', 'Sugerencia eliminada correctamente.');
    }

    // ── Notificación ───────────────────────────────────────────────────────

    private function notificarTutores(Sugerencia $sugerencia): void
    {
        $ciclo = Ciclo::with('tutores')->find($sugerencia->ciclo_id);

        // Copia al correo de administración: se envía siempre, independiente de
        // si el ciclo tiene o no tutor(es) asignados.
        if ($adminEmail = env('NOTIF_EMAIL_ADMIN')) {
            try {
                Mail::to($adminEmail)->send(new SugerenciaMail($sugerencia));
            } catch (\Throwable $e) {
                Log::warning('Notificación de sugerencia a admin: '.$e->getMessage());
            }
        }

        // Alerta en el sistema (campanita) para todo admin/super-admin: siempre,
        // de todos los tutores/ciclos — el correo ya se cubrió arriba, aquí solo
        // se genera la notificación in-app (via() la deja sin canal de mail).
        foreach (User::role(['admin', 'super-admin'])->get() as $adminUser) {
            try {
                $adminUser->notify(new SugerenciaCreada($sugerencia));
            } catch (\Throwable $e) {
                Log::warning("Notificación de sugerencia a admin {$adminUser->id}: ".$e->getMessage());
            }
        }

        if (!$ciclo || $ciclo->tutores->isEmpty()) {
            return;
        }

        $alumno = $sugerencia->alumno;
        $nombreAl = $alumno ? $alumno->apellidos.', '.$alumno->nombres : ($sugerencia->nombre_alumno ?: 'Alumno');

        $textoWA = "💬 *Nueva sugerencia - EESP Pukllasunchis*\n"
            ."👤 Alumno: {$nombreAl}\n"
            ."📞 Contacto: ".($alumno?->numero ?? '—')."\n"
            ."📚 Ciclo: {$ciclo->nombre}\n"
            ."📝 Sugerencia: ".mb_substr($sugerencia->mensaje, 0, 200)
            .(mb_strlen($sugerencia->mensaje) > 200 ? '...' : '');

        foreach ($ciclo->tutores as $tutor) {
            try {
                $tutor->notify(new SugerenciaCreada($sugerencia));
            } catch (\Throwable $e) {
                Log::warning("Notificación sugerencia a tutor {$tutor->id}: ".$e->getMessage());
            }

            if ($tutor->telefono && $tutor->whatsapp_key) {
                $phone = preg_replace('/\D/', '', $tutor->telefono);
                WhatsappService::send($phone, $tutor->whatsapp_key, $textoWA);
            }
        }
    }
}
