<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Incidencia;
use App\Models\Sugerencia;
use App\Models\Tutoria;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $docente  = $user->docente;
        $ciclos   = $user->tutorCiclos()->with('programa')->get();
        $cicloIds = $ciclos->pluck('id');

        $incidencias = Incidencia::with(['docente', 'alumno', 'ciclo.programa'])
            ->whereIn('ciclo_id', $cicloIds)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        $tutorias = Tutoria::with(['alumno.ciclo.programa', 'ciclo.programa'])
            ->whereIn('ciclo_id', $cicloIds)
            ->orderByDesc('created_at')
            ->get();

        $sugerencias = Sugerencia::with(['alumno.ciclo.programa', 'ciclo.programa'])
            ->whereIn('ciclo_id', $cicloIds)
            ->orderByDesc('created_at')
            ->get();

        return view('tutor.index', compact('docente', 'ciclos', 'incidencias', 'tutorias', 'sugerencias'));
    }

    public function ciclo($cicloId)
    {
        $user  = auth()->user();
        $ciclo = Ciclo::with(['programa', 'alumnos.user'])->findOrFail($cicloId);

        abort_if(!$user->tutorCiclos->contains($cicloId), 403);

        $alumnos = $ciclo->alumnos()->with('user')->orderBy('apellidos')->get();

        return view('tutor.ciclo', compact('ciclo', 'alumnos'));
    }

    public function qrTutoria($cicloId, Request $request)
    {
        $ciclo = Ciclo::with(['programa', 'tutores'])->findOrFail($cicloId);
        abort_if(!auth()->user()->tutorCiclos->contains($cicloId), 403);

        $url = route('tutorias.public.create', ['ciclo' => $ciclo->id]);

        $data = [
            'ciclo'       => $ciclo,
            'url'         => $url,
            'titulo'      => 'Solicitud de Tutoría Individual',
            'tipo'        => 'tutoria',
            'descripcion' => 'Escanea este código para solicitar una atención personalizada con el tutor de tu ciclo. Indica el motivo y el nivel de prioridad — el tutor será notificado de inmediato.',
            'contexto'    => (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre,
            'tutor'       => $ciclo->tutores->map->nombreCorto()->filter()->join(', '),
            'volverA'     => route('tutor.dashboard'),
        ];

        if ($request->boolean('panel')) {
            return view('partials.qr-poster', $data);
        }

        return view('tutor.qr', $data);
    }

    public function qrSugerencia($cicloId, Request $request)
    {
        $ciclo = Ciclo::with(['programa', 'tutores'])->findOrFail($cicloId);
        abort_if(!auth()->user()->tutorCiclos->contains($cicloId), 403);

        $url = route('sugerencias.public.create', ['ciclo' => $ciclo->id]);

        $data = [
            'ciclo'       => $ciclo,
            'url'         => $url,
            'titulo'      => 'Buzón de Sugerencias de Tutoría',
            'tipo'        => 'sugerencia',
            'descripcion' => 'Escanea este código para dejarnos tu sugerencia o proponer un tema para trabajar en las tutorías. Puedes enviarla de forma anónima si lo prefieres.',
            'contexto'    => (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre,
            'tutor'       => $ciclo->tutores->map->nombreCorto()->filter()->join(', '),
            'volverA'     => route('tutor.dashboard'),
        ];

        if ($request->boolean('panel')) {
            return view('partials.qr-poster', $data);
        }

        return view('tutor.qr', $data);
    }
}
