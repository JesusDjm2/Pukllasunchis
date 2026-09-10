<?php

namespace App\Http\Controllers;

use App\Mail\TutoriaMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\Tutoria;
use App\Models\User;
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
        $programas = Programa::where('nombre', 'not like', '%PPD%')->orderBy('nombre')->get();

        $cicloPreseleccionado = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        return view('tutorias.public-create', compact('programas', 'cicloPreseleccionado'));
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
            'motivo'      => 'required|string|min:10',
            'urgencia'    => 'required|in:Alta,Media,Baja',
        ]);

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

        $tutoria = Tutoria::create($data);
        $tutoria->load(['alumno', 'ciclo.programa']);

        $this->notificarTutores($tutoria);

        return redirect()->route('tutorias.public.create')
            ->with('success', '¡Solicitud enviada correctamente! El tutor de tu ciclo se pondrá en contacto contigo.');
    }

    // ── Panel admin ────────────────────────────────────────────────────────

    public function qr(Request $request)
    {
        $ciclo = $request->filled('ciclo')
            ? Ciclo::with(['programa', 'tutores'])->find($request->query('ciclo'))
            : null;

        $url = route('tutorias.public.create', $ciclo ? ['ciclo' => $ciclo->id] : []);
        $descripcion = 'Escanea este código para solicitar una atención personalizada con el tutor de tu ciclo. Indica el motivo y el nivel de prioridad — el tutor será notificado de inmediato.';

        $data = [
            'url'         => $url,
            'tipo'        => 'tutoria',
            'titulo'      => 'Solicitud de Tutoría Individual',
            'descripcion' => $descripcion,
            'contexto'    => $ciclo ? (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre : null,
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

        $tutoria->estado = $tutoria->estado === 'atendida' ? 'pendiente' : 'atendida';
        $tutoria->atendido_por = $tutoria->estado === 'atendida' ? auth()->id() : null;
        $tutoria->atendido_at = $tutoria->estado === 'atendida' ? now() : null;
        $tutoria->notas_atencion = $data['notas_atencion'] ?? $tutoria->notas_atencion;
        $tutoria->save();

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

        // Alerta en el sistema (campanita) para todo admin/super-admin: siempre,
        // de todos los tutores/ciclos — el correo ya se cubrió arriba, aquí solo
        // se genera la notificación in-app (via() la deja sin canal de mail).
        foreach (User::role(['admin', 'super-admin'])->get() as $adminUser) {
            try {
                $adminUser->notify(new TutoriaCreada($tutoria));
            } catch (\Throwable $e) {
                Log::warning("Notificación de tutoría a admin {$adminUser->id}: ".$e->getMessage());
            }
        }

        if (!$ciclo || $ciclo->tutores->isEmpty()) {
            return;
        }

        $alumno = $tutoria->alumno;
        $nombreAl = $alumno ? $alumno->apellidos.', '.$alumno->nombres : ($tutoria->nombre_alumno ?: 'Alumno');

        $textoWA = "🙋 *Nueva solicitud de tutoría - EESP Pukllasunchis*\n"
            ."⚠️ Prioridad: {$tutoria->urgencia}\n"
            ."👤 Alumno: {$nombreAl}\n"
            ."📞 Contacto: ".($alumno?->numero ?? '—')."\n"
            ."📚 Ciclo: {$ciclo->nombre}\n"
            ."📝 Motivo: ".mb_substr($tutoria->motivo, 0, 200)
            .(mb_strlen($tutoria->motivo) > 200 ? '...' : '');

        foreach ($ciclo->tutores as $tutor) {
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
