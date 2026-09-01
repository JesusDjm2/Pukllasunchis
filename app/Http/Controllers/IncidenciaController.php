<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Incidencia;
use App\Models\Programa;
use App\Models\User;
use App\Notifications\IncidenciaCreada;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IncidenciaController extends Controller
{
    public function adminIndex(\App\Models\Docente $docente)
    {
        $incidencias = Incidencia::with(['alumno', 'ciclo.programa', 'docente'])
            ->where('docente_id', $docente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.docentes.incidencias', compact('docente', 'incidencias'));
    }

    public function index($docenteId)
    {
        $docente = auth()->user()->docente;
        abort_if(!$docente || $docente->id != $docenteId, 403);

        $incidencias = Incidencia::with(['alumno', 'ciclo.programa'])
            ->where('docente_id', $docente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('docentes.incidencias.index', compact('docente', 'incidencias'));
    }

    public function create($docenteId)
    {
        $docente   = auth()->user()->docente;
        abort_if(!$docente || $docente->id != $docenteId, 403);

        $programas = Programa::orderBy('nombre')->get();

        return view('docentes.incidencias.create', compact('docente', 'programas'));
    }

    public function store(Request $request, $docenteId)
    {
        $docente = auth()->user()->docente;
        abort_if(!$docente || $docente->id != $docenteId, 403);

        $data = $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id'    => 'required|exists:ciclos,id',
            'alumno_id'   => 'required|exists:alumnos,id',
            'fecha'       => 'required|date',
            'reporte'     => 'required|string|min:10',
            'imagen'      => 'nullable|image|max:3072',
        ]);

        $data['docente_id'] = $docente->id;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/incidencias'), $name);
            $data['imagen'] = $name;
        }

        $incidencia = Incidencia::create($data);
        $incidencia->load(['alumno', 'ciclo.programa', 'docente.user']);

        $this->notificarTutores($incidencia);

        return redirect()
            ->route('docente.incidencias.index', $docente->id)
            ->with('success', 'Incidencia registrada correctamente.');
    }

    // ── Formulario público (sin login) ───────────────────────────────────────

    public function publicCreate()
    {
        $programas = Programa::where('nombre', 'not like', '%PPD%')->orderBy('nombre')->get();
        return view('incidencias.public-create', compact('programas'));
    }

    public function publicStore(Request $request)
    {
        $data = $request->validate([
            'nombre_docente' => 'required|string|max:200',
            'programa_id'    => 'required|exists:programas,id',
            'ciclo_id'       => 'required|exists:ciclos,id',
            'alumno_id'      => 'required|exists:alumnos,id',
            'fecha'          => 'required|date',
            'reporte'        => 'required|string|min:10',
            'imagen'         => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/incidencias'), $name);
            $data['imagen'] = $name;
        }

        $incidencia = Incidencia::create($data);
        $incidencia->load(['alumno', 'ciclo.programa', 'docente.user']);

        $this->notificarTutores($incidencia);

        return redirect()->route('incidencias.public.create')
            ->with('success', '¡Incidencia enviada correctamente! Será revisada por el tutor del ciclo.');
    }

    // ── Lógica de notificación ───────────────────────────────────────────────

    private function notificarTutores(Incidencia $incidencia): void
    {
        $ciclo = Ciclo::with('tutores')->find($incidencia->ciclo_id);

        // Copia al correo de administración: se envía siempre, independiente de
        // si el ciclo tiene o no tutor(es) asignados.
        if ($adminEmail = env('NOTIF_EMAIL_ADMIN')) {
            try {
                Mail::to($adminEmail)->send(new IncidenciaMail($incidencia));
            } catch (\Throwable $e) {
                Log::warning('Notificación de incidencia a admin: '.$e->getMessage());
            }
        }

        // Alerta en el sistema (campanita) para todo admin/super-admin: siempre,
        // de todos los tutores/ciclos — el correo ya se cubrió arriba, aquí solo
        // se genera la notificación in-app (via() la deja sin canal de mail).
        foreach (User::role(['admin', 'super-admin'])->get() as $adminUser) {
            try {
                $adminUser->notify(new IncidenciaCreada($incidencia));
            } catch (\Throwable $e) {
                Log::warning("Notificación de incidencia a admin {$adminUser->id}: ".$e->getMessage());
            }
        }

        if (!$ciclo || $ciclo->tutores->isEmpty()) {
            return;
        }

        $alumno   = $incidencia->alumno;
        $nombreAl = $alumno ? $alumno->apellidos.', '.$alumno->nombres : 'Alumno';
        $reporter = $incidencia->docente
            ? ($incidencia->docente->user->apellidos.', '.$incidencia->docente->user->name)
            : ($incidencia->nombre_docente ?? 'Desconocido');

        $textoWA = "📋 *Nueva incidencia - EESP Pukllasunchis*\n"
            ."👤 Alumno: {$nombreAl}\n"
            ."📞 Contacto: ".($alumno?->numero ?? '—')."\n"
            ."📚 Ciclo: {$ciclo->nombre}\n"
            ."📅 Fecha: {$incidencia->fecha->format('d/m/Y')}\n"
            ."✍️ Reportado por: {$reporter}\n"
            ."📝 Reporte: ".mb_substr($incidencia->reporte, 0, 200)
            .(mb_strlen($incidencia->reporte) > 200 ? '...' : '');

        foreach ($ciclo->tutores as $tutor) {
            // Email + notificación in-app (unificadas vía Notification)
            try {
                $tutor->notify(new IncidenciaCreada($incidencia));
            } catch (\Throwable $e) {
                Log::warning("Notificación incidencia a tutor {$tutor->id}: ".$e->getMessage());
            }

            // WhatsApp (CallMeBot)
            if ($tutor->telefono && $tutor->whatsapp_key) {
                $phone = preg_replace('/\D/', '', $tutor->telefono);
                WhatsappService::send($phone, $tutor->whatsapp_key, $textoWA);
            }
        }
    }

    // AJAX: ciclos por programa
    public function ciclosPorPrograma($programaId)
    {
        $ciclos = Ciclo::where('programa_id', $programaId)
            ->with('tutores')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'programa_id']);

        return response()->json($ciclos->map(fn ($ciclo) => [
            'id' => $ciclo->id,
            'nombre' => $ciclo->nombre,
            'tutores' => $ciclo->tutores->map->nombreCorto()->filter()->implode(', '),
        ]));
    }

    // AJAX: alumnos por ciclo
    public function alumnosPorCiclo($cicloId)
    {
        $alumnos = Alumno::where('ciclo_id', $cicloId)
            ->orderBy('apellidos')
            ->get(['id', 'apellidos', 'nombres']);

        return response()->json($alumnos->map(fn ($a) => [
            'id'     => $a->id,
            'nombre' => $a->apellidos.', '.$a->nombres,
        ]));
    }

    // ── Marcar estado (panel de admin) ────────────────────────────────────

    public function marcarEstado(Request $request, Incidencia $incidencia)
    {
        $data = $request->validate([
            'notas_atencion' => 'nullable|string',
        ]);

        $incidencia->estado = $incidencia->estado === 'atendida' ? 'pendiente' : 'atendida';
        $incidencia->atendido_por = $incidencia->estado === 'atendida' ? auth()->id() : null;
        $incidencia->atendido_at = $incidencia->estado === 'atendida' ? now() : null;
        $incidencia->notas_atencion = $data['notas_atencion'] ?? $incidencia->notas_atencion;
        $incidencia->save();

        return back()->with('success', 'Incidencia actualizada.');
    }

    public function destroy(Incidencia $incidencia)
    {
        $incidencia->delete();

        return back()->with('success', 'Incidencia eliminada correctamente.');
    }
}
