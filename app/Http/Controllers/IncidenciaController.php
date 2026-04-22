<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Incidencia;
use App\Models\Programa;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class IncidenciaController extends Controller
{
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
            $name = time().'_'.$file->getClientOriginalName();
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
        $programas = Programa::orderBy('nombre')->get();
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
            $name = time().'_'.$file->getClientOriginalName();
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
            ."📚 Ciclo: {$ciclo->nombre}\n"
            ."📅 Fecha: {$incidencia->fecha->format('d/m/Y')}\n"
            ."✍️ Reportado por: {$reporter}\n"
            ."📝 Reporte: ".mb_substr($incidencia->reporte, 0, 200)
            .(mb_strlen($incidencia->reporte) > 200 ? '...' : '');

        foreach ($ciclo->tutores as $tutor) {
            // Email
            if ($tutor->email) {
                try {
                    Mail::to($tutor->email)->send(new IncidenciaMail($incidencia));
                } catch (\Throwable $e) {
                    Log::warning("Email incidencia a tutor {$tutor->id}: ".$e->getMessage());
                }
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
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($ciclos);
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
}
