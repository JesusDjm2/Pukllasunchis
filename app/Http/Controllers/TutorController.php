<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Incidencia;
use App\Models\Sugerencia;
use App\Models\Tutoria;
use App\Services\MensajeAlumnos;
use App\Services\SeguimientoCiclo;
use App\Support\ContactoAlumno;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $docente  = $user->docente;
        $ciclos   = $user->tutorCiclos()->with('programa')->get();
        $cicloIds = $ciclos->pluck('id');
        // Resumen académico de cada ciclo para su tarjeta (alumnos que están desaprobando).
        $resumenCiclos = $ciclos->mapWithKeys(fn ($c) => [$c->id => SeguimientoCiclo::de($c, $c->alumnos()->with('user')->get())['resumen']]);

        $incidencias = Incidencia::with(['docente', 'alumno', 'ciclo.programa'])
            ->whereIn('ciclo_id', $cicloIds)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        // Las de sus ciclos + las de alumnos de otros ciclos que pidieron hablar con él/ella.
        $tutorias = Tutoria::with(['alumno.ciclo.programa', 'ciclo.programa', 'docenteSolicitado.docente'])
            ->where(fn ($q) => $q->whereIn('ciclo_id', $cicloIds)->orWhere('docente_id', $user->id))
            ->orderByDesc('created_at')
            ->get();

        $sugerencias = Sugerencia::with(['alumno.ciclo.programa', 'ciclo.programa'])
            ->whereIn('ciclo_id', $cicloIds)
            ->orderByDesc('created_at')
            ->get();

        return view('tutor.index', compact('docente', 'ciclos', 'incidencias', 'tutorias', 'sugerencias', 'resumenCiclos'));
    }

    public function ciclo($cicloId)
    {
        $user  = auth()->user();
        $ciclo = Ciclo::with(['programa', 'alumnos.user'])->findOrFail($cicloId);

        abort_if(!$user->tutorCiclos->contains($cicloId), 403);

        $alumnos = $ciclo->alumnos()->with('user')->orderBy('apellidos')->get();
        $contactos = $alumnos->mapWithKeys(fn ($a) => [$a->id => ContactoAlumno::desdeAlumno($a)]);
        $puedeEscribir = (bool) $user->docente;
        $seguimiento = SeguimientoCiclo::de($ciclo, $alumnos);

        return view('tutor.ciclo', compact('ciclo', 'alumnos', 'contactos', 'puedeEscribir', 'seguimiento'));
    }

    /** El tutor escribe por correo a uno, varios o todos los alumnos de un ciclo que tutorea. */
    public function enviarMensaje(Request $request, Ciclo $ciclo)
    {
        $user = auth()->user();
        abort_if(! $user->tutorCiclos->contains($ciclo->id), 403);

        $ciclo->loadMissing('programa');
        $permitidos = $ciclo->alumnos()->with('user')->get()->map(fn ($a) => ContactoAlumno::desdeAlumno($a));
        $docente = $user->docente;

        [$tipo, $texto] = MensajeAlumnos::desdeFormulario(
            $request,
            $permitidos,
            $docente?->nombre ?: trim($user->name.' '.$user->apellidos),
            $docente?->email ?: $user->email,
            'Tutor(a) del Ciclo '.$ciclo->nombre.' · '.(optional($ciclo->programa)->nombre ?? ''),
        );

        return redirect()->route('tutor.ciclo', $ciclo->id)->with($tipo, $texto);
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
            'descripcion' => 'Escanea el código con tu celular y cuéntanos cuándo y cómo te gustaría conversar. Tu tutor(a) será notificado de inmediato.',
            'contexto'    => (optional($ciclo->programa)->nombre ?? '') . ' · Ciclo ' . $ciclo->nombre,
            'cicloNombre' => 'Ciclo ' . $ciclo->nombre,
            'programaNombre' => optional($ciclo->programa)->nombre,
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
            'cicloNombre' => 'Ciclo ' . $ciclo->nombre,
            'programaNombre' => optional($ciclo->programa)->nombre,
            'tutor'       => $ciclo->tutores->map->nombreCorto()->filter()->join(', '),
            'volverA'     => route('tutor.dashboard'),
        ];

        if ($request->boolean('panel')) {
            return view('partials.qr-poster', $data);
        }

        return view('tutor.qr', $data);
    }
}
