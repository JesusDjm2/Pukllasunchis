<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ArchivosTaqe;
use App\Http\Controllers\Concerns\GestionaGruposTaqe;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\TaqeGrupo;
use App\Models\TaqeSolicitudAyuda;
use App\Models\TaqeTrabajo;
use App\Models\User;
use App\Notifications\TaqeIntegranteRetirado;
use App\Notifications\TaqeInvitacionGrupo;
use App\Notifications\TaqeMiembroAcepto;
use App\Notifications\TaqeMiembroSalio;
use App\Notifications\TaqeOrganizadorCambiado;
use App\Notifications\TaqeSolicitudAyudaCreada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaqeController extends Controller
{
    use ArchivosTaqe, GestionaGruposTaqe;

    private function alumnoActual(): ?Alumno
    {
        $alumno = auth()->user()?->alumno;

        // Taq'e es un espacio de trabajo entre alumnos activos: uno inhabilitado
        // (retirado, con licencia, con deuda o cualquier otro motivo — el
        // criterio es "tiene el rol inhabilitado", sin excepción, a diferencia
        // de otros módulos que sí dejan pasar Deuda/Licencia) no debe poder
        // crear ni responder nada aquí. Al devolver null, todos los métodos de
        // este controlador ya cortan solos con abort_unless($alumno, 403).
        if ($alumno && $alumno->user?->hasRole('inhabilitado')) {
            return null;
        }

        return $alumno;
    }

    /** true si el alumno está inhabilitado (cualquier motivo: retirado, licencia, deuda, etc.). */
    private function estaInhabilitado(Alumno $alumno): bool
    {
        return (bool) $alumno->user?->hasRole('inhabilitado');
    }

    public function index()
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);

        $periodoActual = PeriodoActual::where('actual', true)->first();

        // Misma lógica que el dashboard del alumno: si no hay asignaciones
        // explícitas en alumno_cursos para el periodo, se cae a los cursos
        // de su ciclo (ver AlumnoController@index).
        $cursosAsignados = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->with('ciclo')->get()
            : collect();

        $cursos = $cursosAsignados->isNotEmpty()
            ? $cursosAsignados
            : ($alumno->ciclo ? $alumno->ciclo->cursos : collect());

        $misGrupos = $periodoActual
            ? $alumno->taqeGrupos()
                ->wherePivot('estado', 'aceptado')
                ->where('periodo_actual_id', $periodoActual->id)
                ->with(['curso', 'proyecto', 'alumnos', 'evidencias.subidoPor', 'finales.subidoPor'])
                ->get()
            : collect();

        // Un grupo por periodo: con grupo ya no se muestran invitaciones (ni se puede crear otro).
        $invitacionesPendientes = $periodoActual && $misGrupos->isEmpty()
            ? $alumno->taqeGrupos()
                ->wherePivot('estado', 'pendiente')
                ->where('periodo_actual_id', $periodoActual->id)
                ->with(['curso', 'creador', 'proyecto'])
                ->get()
            : collect();

        // Grupos de periodos anteriores: archivo de solo lectura con sus evidencias y trabajo final.
        $gruposAnteriores = $alumno->taqeGrupos()
            ->wherePivot('estado', 'aceptado')
            ->when($periodoActual, fn ($q) => $q->where('periodo_actual_id', '!=', $periodoActual->id))
            ->with(['curso', 'ciclo', 'programa', 'periodoActual', 'proyecto', 'alumnos', 'evidencias.subidoPor', 'finales.subidoPor'])
            ->orderByDesc('periodo_actual_id')
            ->get();

        // Proyecto Integrador del alumno = el de SU ciclo. Solo puede formar grupo en cursos de ese
        // mismo proyecto (un alumno de Ciclo V no hace el proyecto de Ciclo II, aunque lleve un curso de II).
        $alumno->loadMissing('ciclo.proyecto');
        $proyectoAlumno = $alumno->ciclo?->proyecto;
        $cursosSinGrupo = $misGrupos->isNotEmpty() ? collect() : $cursos
            ->filter(fn ($curso) => $proyectoAlumno && (int) $curso->ciclo?->proyecto_id === (int) $proyectoAlumno->id)
            ->values();

        // Para los grupos donde el propio alumno es el organizador, se
        // precarga la lista de compañeros que aún podría invitar — así el
        // modal de "editar grupo" no necesita una llamada AJAX aparte (el
        // curso ya es fijo, a diferencia del modal de crear grupo).
        $companerosPorGrupo = [];
        if ($periodoActual) {
            foreach ($misGrupos as $grupo) {
                if ($grupo->creador_alumno_id === $alumno->id) {
                    $yaEnGrupo = $grupo->alumnos->pluck('id');
                    $companerosPorGrupo[$grupo->id] = $this->companerosDisponibles($grupo->curso, $periodoActual, $alumno->id, $grupo->proyecto_id)
                        ->reject(fn ($a) => $yaEnGrupo->contains($a->id))
                        ->values();
                }
            }
        }

        // Contacto del/los Investigador(es) — para que el organizador pueda
        // escribirles por fuera si lo prefiere, además del botón "Solicitar
        // ayuda" (que sí queda registrado en el sistema).
        $investigadores = User::role('investigador')->with('docente')->get();

        return view('alumnos.taqe.index', compact(
            'alumno', 'periodoActual', 'misGrupos', 'invitacionesPendientes', 'cursosSinGrupo',
            'companerosPorGrupo', 'investigadores', 'proyectoAlumno', 'gruposAnteriores'
        ));
    }

    /** Alumnos matriculados en un curso este periodo, para elegir integrantes al crear un grupo. */
    public function alumnosDelCurso(Request $request, Curso $curso)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);

        $periodoActual = PeriodoActual::where('actual', true)->first();
        if (! $periodoActual) {
            return response()->json([]);
        }

        $compañeros = $this->companerosDisponibles($curso, $periodoActual, $alumno->id, $this->proyectoDelAlumno($alumno));

        return response()->json($compañeros->map(fn ($a) => [
            'id' => $a->id,
            'nombre' => $a->apellidos.', '.$a->nombres.' — DNI '.$a->dni,
        ]));
    }

    public function store(Request $request)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'curso_id' => 'required|exists:cursos,id',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:alumnos,id',
        ]);

        $periodoActual = PeriodoActual::where('actual', true)->first();
        if (! $periodoActual) {
            return back()->withInput()->with('error', 'No hay un periodo activo configurado.');
        }

        $curso = Curso::with('ciclo')->findOrFail($data['curso_id']);

        // Igual que en index(): un alumno sin asignación explícita en
        // alumno_cursos para el periodo se considera matriculado si el
        // curso pertenece a su ciclo (fallback del dashboard).
        $matriculadoEnCurso = $alumno->cursosDelPeriodo($periodoActual->id)->where('cursos.id', $curso->id)->exists()
            || ($alumno->ciclo && $alumno->ciclo->id === $curso->ciclo_id);
        if (! $matriculadoEnCurso) {
            return back()->withInput()->with('error', 'No estás matriculado en ese curso este periodo.');
        }

        // Proyecto Integrador: el curso debe tener uno y debe ser el del ciclo del propio alumno.
        $proyectoId = $curso->ciclo?->proyecto_id;
        if (! $proyectoId) {
            return back()->withInput()->with('error', "El ciclo de ese curso no tiene un Proyecto Integrador asignado. Avisa a la coordinación para poder formar tu grupo Taq'e.");
        }
        if ((int) $this->proyectoDelAlumno($alumno) !== (int) $proyectoId) {
            return back()->withInput()->with('error', 'Ese curso pertenece al Proyecto Integrador de otro ciclo. Solo puedes formar grupo en el Proyecto Integrador de tu ciclo.');
        }

        // Un grupo por alumno y periodo (de cualquier curso).
        $yaTieneGrupo = $alumno->taqeGrupos()
            ->wherePivot('estado', 'aceptado')
            ->where('periodo_actual_id', $periodoActual->id)
            ->exists();
        if ($yaTieneGrupo) {
            return back()->withInput()->with('error', "Ya perteneces a un grupo Taq'e este periodo. Solo se puede estar en un grupo por periodo.");
        }

        $grupo = TaqeGrupo::create([
            'nombre' => $data['nombre'],
            'curso_id' => $curso->id,
            'ciclo_id' => $curso->ciclo_id,
            'programa_id' => $curso->ciclo->programa_id,
            'proyecto_id' => $proyectoId,
            'periodo_actual_id' => $periodoActual->id,
            'creador_alumno_id' => $alumno->id,
        ]);

        $grupo->alumnos()->attach($alumno->id, ['estado' => 'aceptado', 'respondido_at' => now()]);
        $this->rechazarInvitacionesDelPeriodo($alumno, $periodoActual->id, $grupo->id);

        $integrantesIds = collect($data['integrantes'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $alumno->id)
            ->unique();

        // Quien ya está aceptado en otro grupo del periodo no se
        // invita (aunque lo hayan marcado en el formulario) — igual quedaría
        // bloqueado al intentar aceptar, mejor avisar de una vez. Tampoco se
        // invita a ningún alumno inhabilitado (retirado, licencia, deuda,
        // etc.) — defensa adicional por si el request se manipula a mano,
        // ya que alumnosDelCurso() ni siquiera los ofrece en la lista.
        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $inhabilitados = Alumno::whereIn('id', $integrantesIds)
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'inhabilitado'))
            ->pluck('id');

        $deOtroProyecto = $this->alumnoIdsDeOtroProyecto($integrantesIds, $proyectoId);

        $integrantesOmitidosPorGrupo = $integrantesIds->intersect($comprometidos);
        $integrantesOmitidosPorInhabilitado = $integrantesIds->intersect($inhabilitados)->diff($integrantesOmitidosPorGrupo);
        $integrantesOmitidosPorProyecto = $integrantesIds->intersect($deOtroProyecto)->diff($integrantesOmitidosPorGrupo)->diff($integrantesOmitidosPorInhabilitado);
        $integrantesAInvitar = $integrantesIds->diff($comprometidos)->diff($inhabilitados)->diff($deOtroProyecto);

        foreach ($integrantesAInvitar as $integranteId) {
            $grupo->alumnos()->attach($integranteId, ['estado' => 'pendiente']);
            $integrante = Alumno::find($integranteId);
            if ($integrante?->user) {
                $integrante->user->notify(new TaqeInvitacionGrupo($grupo));
            }
        }

        $mensaje = 'Grupo "'.$grupo->nombre.'" creado correctamente.';
        if ($integrantesOmitidosPorGrupo->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $integrantesOmitidosPorGrupo)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (ya pertenecen a otro grupo este periodo).';
        }
        if ($integrantesOmitidosPorInhabilitado->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $integrantesOmitidosPorInhabilitado)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (no está habilitado).';
        }
        if ($integrantesOmitidosPorProyecto->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $integrantesOmitidosPorProyecto)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (su ciclo tiene otro Proyecto Integrador).';
        }

        return redirect()->route('taqe.index')->with('success', $mensaje);
    }

    public function responder(Request $request, TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);

        $data = $request->validate(['respuesta' => 'required|in:aceptado,rechazado']);

        $pivotExiste = $grupo->alumnos()->wherePivot('alumno_id', $alumno->id)->exists();
        abort_unless($pivotExiste, 403);
        $this->bloquearSiPeriodoCerrado($grupo);

        // Barrera real contra pertenecer a dos grupos en el mismo periodo: alumnosDelCurso() y store()
        // ya evitan que esto se dé desde la invitación, pero aquí se confirma la membresía (a alguien
        // pudieron invitarlo a dos grupos antes de que aceptara ninguno).
        if ($data['respuesta'] === 'aceptado') {
            $yaTieneOtroGrupo = $alumno->taqeGrupos()
                ->wherePivot('estado', 'aceptado')
                ->where('periodo_actual_id', $grupo->periodo_actual_id)
                ->where('taqe_grupos.id', '!=', $grupo->id)
                ->exists();

            if ($yaTieneOtroGrupo) {
                return back()->with('error', "Ya perteneces a un grupo Taq'e este periodo (solo se puede estar en uno). Sal de él primero si quieres cambiarte a este.");
            }

            if ($grupo->proyecto_id && (int) $this->proyectoDelAlumno($alumno) !== (int) $grupo->proyecto_id) {
                return back()->with('error', 'No puedes unirte: ese grupo trabaja el Proyecto Integrador de otro ciclo. Solo puedes participar en el de tu ciclo.');
            }
        }

        $grupo->alumnos()->updateExistingPivot($alumno->id, [
            'estado' => $data['respuesta'],
            'respondido_at' => now(),
        ]);

        if ($data['respuesta'] === 'aceptado') {
            // Un grupo por periodo: las demás invitaciones pendientes del periodo se rechazan solas.
            $rechazadas = $this->rechazarInvitacionesDelPeriodo($alumno, $grupo->periodo_actual_id, $grupo->id);

            // El organizador y el resto de integrantes que ya estaban
            // aceptados se enteran de que se sumó un nuevo compañero.
            $grupo->alumnosAceptados()
                ->where('alumnos.id', '!=', $alumno->id)
                ->get()
                ->each(function (Alumno $integrante) use ($grupo, $alumno) {
                    if ($integrante->user) {
                        try {
                            $integrante->user->notify(new TaqeMiembroAcepto($grupo, $alumno));
                        } catch (\Throwable $e) {
                            Log::warning("Notificación de nuevo integrante Taq'e a alumno {$integrante->id}: ".$e->getMessage());
                        }
                    }
                });
        }

        $mensaje = $data['respuesta'] === 'aceptado'
            ? 'Te uniste al grupo "'.$grupo->nombre.'".'.(($rechazadas ?? 0) ? ' Tus otras invitaciones de este periodo se rechazaron (solo puedes estar en un grupo).' : '')
            : 'Rechazaste la invitación al grupo "'.$grupo->nombre.'".';

        return back()->with('success', $mensaje);
    }

    public function salir(TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $this->verificarIntegranteAceptado($grupo, $alumno);
        $this->bloquearSiPeriodoCerrado($grupo);

        $otrosAceptados = $grupo->alumnosAceptados()
            ->where('alumnos.id', '!=', $alumno->id)
            ->orderBy('taqe_grupo_alumno.respondido_at')
            ->get();

        $huboCambioDeOrganizador = false;

        if ($otrosAceptados->isEmpty()) {
            // Era el último integrante aceptado: no tiene sentido dejar un
            // grupo vacío dando vueltas, se elimina directo (cascada limpia
            // pivote e invitaciones pendientes/rechazadas y trabajos subidos;
            // los archivos en Drive se borran aparte).
            $this->eliminarArchivosDelGrupo($grupo);
            $grupo->delete();

            return redirect()->route('taqe.index')->with('success', 'Saliste del grupo "'.$grupo->nombre.'". Como eras el único integrante, el grupo se eliminó.');
        }

        if ($grupo->creador_alumno_id === $alumno->id) {
            // El organizador se va pero el grupo sigue vivo: el rol pasa al
            // integrante que aceptó primero (después del propio creador).
            $grupo->creador_alumno_id = $otrosAceptados->first()->id;
            $grupo->save();
            $huboCambioDeOrganizador = true;
        }

        $grupo->alumnos()->detach($alumno->id);

        foreach ($otrosAceptados as $integrante) {
            if ($integrante->user) {
                try {
                    $integrante->user->notify(new TaqeMiembroSalio($grupo, $alumno, $huboCambioDeOrganizador));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de salida de grupo Taq'e a alumno {$integrante->id}: ".$e->getMessage());
                }
            }
        }

        return redirect()->route('taqe.index')->with('success', 'Saliste del grupo "'.$grupo->nombre.'".');
    }

    /** Solo el organizador: retira a un integrante (aceptado o con invitación pendiente) del grupo. */
    public function retirarIntegrante(TaqeGrupo $grupo, Alumno $integrante)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        abort_unless($grupo->creador_alumno_id === $alumno->id, 403, 'Solo el organizador del grupo puede retirar integrantes.');
        $this->bloquearSiPeriodoCerrado($grupo);
        abort_if($integrante->id === $alumno->id, 403, 'Usa "Salir del grupo" para retirarte a ti mismo.');

        $pivot = $grupo->alumnos()->wherePivot('alumno_id', $integrante->id)->first();
        abort_unless($pivot, 404);

        $eraAceptado = $pivot->pivot->estado === 'aceptado';

        $grupo->alumnos()->detach($integrante->id);

        // Al integrante ya aceptado se le avisa que fue retirado — a uno que
        // seguía "pendiente" solo se le canceló una invitación que nunca
        // llegó a aceptar, no hace falta notificarle una salida.
        if ($eraAceptado && $integrante->user) {
            try {
                $integrante->user->notify(new TaqeIntegranteRetirado($grupo));
            } catch (\Throwable $e) {
                Log::warning("Notificación de retiro de grupo Taq'e a alumno {$integrante->id}: ".$e->getMessage());
            }
        }

        $mensaje = $eraAceptado
            ? $integrante->apellidos.', '.$integrante->nombres.' fue retirado del grupo.'
            : 'Invitación a '.$integrante->apellidos.', '.$integrante->nombres.' cancelada.';

        return back()->with('success', $mensaje);
    }

    /** Solo el organizador: invita nuevos integrantes a un grupo ya creado. */
    public function agregarIntegrantes(Request $request, TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        abort_unless($grupo->creador_alumno_id === $alumno->id, 403, 'Solo el organizador del grupo puede agregar integrantes.');
        $this->bloquearSiPeriodoCerrado($grupo);

        $data = $request->validate([
            'integrantes' => 'required|array|min:1',
            'integrantes.*' => 'exists:alumnos,id',
        ]);

        $periodoActual = PeriodoActual::findOrFail($grupo->periodo_actual_id);

        // Quién ya tiene una relación activa con este grupo (aceptado o con
        // invitación pendiente) no se vuelve a invitar; quien lo rechazó
        // antes sí puede reintentarse (se reactiva su mismo registro).
        $pivotesActuales = $grupo->alumnos()->pluck('taqe_grupo_alumno.estado', 'alumnos.id');
        $activosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado !== 'rechazado')->keys();
        $rechazadosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado === 'rechazado')->keys();

        $integrantesIds = collect($data['integrantes'])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $alumno->id)
            ->unique()
            ->diff($activosEnGrupo);

        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $inhabilitados = Alumno::whereIn('id', $integrantesIds)
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'inhabilitado'))
            ->pluck('id');

        $deOtroProyecto = $this->alumnoIdsDeOtroProyecto($integrantesIds, $grupo->proyecto_id);

        $omitidosPorGrupo = $integrantesIds->intersect($comprometidos);
        $omitidosPorInhabilitado = $integrantesIds->intersect($inhabilitados)->diff($omitidosPorGrupo);
        $omitidosPorProyecto = $integrantesIds->intersect($deOtroProyecto)->diff($omitidosPorGrupo)->diff($omitidosPorInhabilitado);
        $aInvitar = $integrantesIds->diff($comprometidos)->diff($inhabilitados)->diff($deOtroProyecto);

        foreach ($aInvitar as $integranteId) {
            if ($rechazadosEnGrupo->contains($integranteId)) {
                $grupo->alumnos()->updateExistingPivot($integranteId, ['estado' => 'pendiente', 'respondido_at' => null]);
            } else {
                $grupo->alumnos()->attach($integranteId, ['estado' => 'pendiente']);
            }
            $integrante = Alumno::find($integranteId);
            if ($integrante?->user) {
                $integrante->user->notify(new TaqeInvitacionGrupo($grupo));
            }
        }

        if ($aInvitar->isEmpty() && $omitidosPorGrupo->isEmpty() && $omitidosPorInhabilitado->isEmpty() && $omitidosPorProyecto->isEmpty()) {
            return back()->with('error', 'No hay nuevos integrantes para invitar.');
        }

        $mensaje = $aInvitar->isNotEmpty() ? 'Invitación enviada.' : 'No se pudo invitar a nadie nuevo.';
        if ($omitidosPorGrupo->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $omitidosPorGrupo)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (ya pertenecen a otro grupo este periodo).';
        }
        if ($omitidosPorInhabilitado->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $omitidosPorInhabilitado)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (no está habilitado).';
        }
        if ($omitidosPorProyecto->isNotEmpty()) {
            $nombres = Alumno::whereIn('id', $omitidosPorProyecto)->get()
                ->map(fn ($a) => $a->apellidos.', '.$a->nombres)
                ->implode('; ');
            $mensaje .= ' No se pudo invitar a: '.$nombres.' (su ciclo tiene otro Proyecto Integrador).';
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Límites por tipo de archivo. Producción admite hasta 256 MB por petición (upload_max_filesize y
     * post_max_size, verificados el 2026-10-16), así que el video de 50 MB cabe con margen.
     */
    private const LIMITES_KB = ['imagen' => 10240, 'pdf' => 25600, 'word' => 10240, 'audio' => 10240, 'video' => 51200];

    /** Formatos aceptados: los que se ven/escuchan en el navegador (AVI y MOV no se reproducen en Chrome). */
    private const EXTENSIONES = 'jpg,jpeg,png,webp,pdf,doc,docx,mp3,wav,m4a,ogg,mp4,webm';

    /**
     * Cualquier integrante aceptado sube un ARCHIVO o un ENLACE, como evidencia o directo al trabajo
     * final (destino). No se puede mientras el grupo esté entregado.
     */
    public function storeTrabajo(Request $request, TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $this->verificarIntegranteAceptado($grupo, $alumno);
        $this->bloquearSiPeriodoCerrado($grupo);

        if ($grupo->estaEntregado()) {
            return back()->with('error', 'El grupo ya entregó su trabajo final: no se pueden agregar archivos. El organizador puede reabrir la entrega si hace falta.');
        }

        $esEnlace = $request->input('modo') === 'enlace';
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'destino' => 'required|in:evidencia,final',
            'modo' => 'required|in:archivo,enlace',
            'enlace' => [$esEnlace ? 'required' : 'nullable', 'url:http,https', 'max:2048'],
            'archivo' => [
                $esEnlace ? 'nullable' : 'required',
                'file',
                'mimes:'.self::EXTENSIONES,
                function ($attribute, $value, $fail) {
                    $tipo = $this->tipoArchivo(strtolower((string) $value->getClientOriginalExtension()));
                    $limiteKB = self::LIMITES_KB[$tipo] ?? 10240;
                    if ($value->getSize() > $limiteKB * 1024) {
                        $fail('El archivo supera el límite de '.($limiteKB / 1024).' MB permitido para este formato.');
                    }
                },
            ],
        ], [
            'enlace.required' => 'Pega el enlace (debe empezar con http:// o https://).',
            'enlace.url' => 'El enlace no es válido: debe empezar con http:// o https://.',
            'archivo.required' => 'Elige el archivo que quieres subir.',
            'archivo.mimes' => 'Formato no admitido. Usa imagen (JPG, PNG, WEBP), PDF, Word, audio (MP3, WAV, M4A, OGG) o video (MP4, WEBM).',
        ]);

        $destino = $data['destino'];
        if ($esEnlace) {
            $trabajo = TaqeTrabajo::create([
                'taqe_grupo_id' => $grupo->id,
                'nombre' => $data['nombre'],
                'enlace' => $data['enlace'],
                'tipo_archivo' => 'enlace',
                'categoria' => $destino,
                'subido_por_alumno_id' => $alumno->id,
            ]);
        } else {
            $archivo = $request->file('archivo');
            $ext = strtolower((string) $archivo->getClientOriginalExtension());
            $driveFileId = $this->drive()->upload(
                config('services.google_drive.folders.taqe'),
                $archivo,
                $this->nombreArchivoDrive($grupo, $destino === 'final' ? 'TRABAJO FINAL' : $alumno->apellidos.' '.$alumno->nombres, $data['nombre'], $ext)
            );
            $trabajo = TaqeTrabajo::create([
                'taqe_grupo_id' => $grupo->id,
                'nombre' => $data['nombre'],
                'archivo' => $driveFileId,
                'tipo_archivo' => $this->tipoArchivo($ext),
                'categoria' => $destino,
                'subido_por_alumno_id' => $alumno->id,
            ]);
        }

        $que = $esEnlace ? 'Enlace' : 'Archivo';
        $donde = $destino === 'final' ? 'al trabajo final' : 'a las evidencias';

        return back()->with('success', $que.' "'.$trabajo->nombre.'" agregado '.$donde.'.');
    }

    /** Cualquier integrante aceptado: pasa una evidencia al trabajo final, o la devuelve a evidencias. */
    public function moverTrabajo(TaqeTrabajo $trabajo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $grupo = $trabajo->grupo;
        $this->verificarIntegranteAceptado($grupo, $alumno);
        $this->bloquearSiPeriodoCerrado($grupo);

        if ($grupo->estaEntregado()) {
            return back()->with('error', 'El grupo ya entregó su trabajo final: no se puede modificar. El organizador puede reabrir la entrega.');
        }

        $trabajo->categoria = $trabajo->esFinal() ? 'evidencia' : 'final';
        $trabajo->save();

        return back()->with('success', $trabajo->esFinal()
            ? '"'.$trabajo->nombre.'" ahora es parte del trabajo final.'
            : '"'.$trabajo->nombre.'" volvió a las evidencias.');
    }

    /** Solo el organizador: entrega el trabajo final (debe tener al menos un archivo o enlace). */
    public function entregar(TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        abort_unless($grupo->creador_alumno_id === $alumno->id, 403, 'Solo el organizador del grupo puede entregar el trabajo final.');
        $this->bloquearSiPeriodoCerrado($grupo);

        if ($grupo->estaEntregado()) {
            return back()->with('error', 'Este trabajo final ya fue entregado.');
        }
        if (! $grupo->finales()->exists()) {
            return back()->with('error', 'Antes de entregar, agrega al menos un archivo o enlace al trabajo final (súbelo directo o pasa una evidencia).');
        }

        $grupo->update(['estado' => 'finalizado', 'entregado_at' => now()]);

        return back()->with('success', 'Trabajo final entregado. El grupo queda Finalizado y ya no se pueden modificar sus archivos.');
    }

    /** Solo el organizador: reabre una entrega para corregir (el grupo vuelve a En proceso). */
    public function reabrir(TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        abort_unless($grupo->creador_alumno_id === $alumno->id, 403, 'Solo el organizador del grupo puede reabrir la entrega.');
        $this->bloquearSiPeriodoCerrado($grupo);

        if (! $grupo->estaEntregado()) {
            return back()->with('error', 'El trabajo final todavía no se ha entregado.');
        }

        $grupo->update(['estado' => 'en_proceso', 'entregado_at' => null]);

        return back()->with('success', 'Entrega reabierta. El grupo vuelve a estar En proceso: recuerda entregar de nuevo cuando termines.');
    }

    /**
     * Elimina un archivo o enlace del grupo (y su archivo de Drive): quien lo subió o el organizador,
     * mientras el trabajo final no esté entregado.
     */
    public function destroyTrabajo(TaqeTrabajo $trabajo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $grupo = $trabajo->grupo;
        $this->verificarIntegranteAceptado($grupo, $alumno);
        $this->bloquearSiPeriodoCerrado($grupo);

        $esOrganizador = $grupo->creador_alumno_id === $alumno->id;
        abort_unless($esOrganizador || $trabajo->subido_por_alumno_id === $alumno->id, 403, 'Solo quien lo subió o el organizador pueden eliminarlo.');

        if ($grupo->estaEntregado()) {
            return back()->with('error', 'El grupo ya entregó su trabajo final: no se puede eliminar nada. El organizador puede reabrir la entrega.');
        }

        $this->eliminarTrabajo($trabajo);

        return back()->with('success', '"'.$trabajo->nombre.'" eliminado.');
    }


    /**
     * Nombre con el que el archivo queda guardado en Drive: "grupo - autor -
     * nombre del trabajo.ext" (autor = el alumno en una evidencia, "TRABAJO
     * FINAL" en el final) — así se puede identificar navegando la carpeta
     * compartida a mano, sin abrir el sistema.
     */
    private function nombreArchivoDrive(TaqeGrupo $grupo, string $autor, string $nombreTrabajo, string $ext): string
    {
        $limpiar = fn (string $s) => trim(preg_replace('/[\\/:*?"<>|]+/', '-', $s));

        $partes = [
            $limpiar($grupo->nombre),
            $limpiar($autor),
            $limpiar($nombreTrabajo),
        ];

        return implode(' - ', $partes).'.'.$ext;
    }

    /** Clasifica un trabajo subido por su extensión — usado para validar, guardar y mostrar el ícono. */
    private function tipoArchivo(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) => 'imagen',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['doc', 'docx']) => 'word',
            in_array($ext, ['mp3', 'wav', 'm4a', 'ogg']) => 'audio',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi']) => 'video', // WebM es video (antes se clasificaba como audio)
            default => 'otro',
        };
    }

    /** Muestra (streaming) el archivo de un trabajo desde Drive; con ?descargar=1 lo baja como adjunto. */
    public function verTrabajo(Request $request, TaqeTrabajo $trabajo): \Symfony\Component\HttpFoundation\Response
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $this->verificarIntegranteAceptado($trabajo->grupo, $alumno);

        return $this->servirArchivo($trabajo, $request->boolean('descargar'));
    }

    /** Solo el organizador actual: transfiere el rol de organizador a otro integrante ya aceptado. */
    public function cambiarOrganizador(Request $request, TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        abort_unless($grupo->creador_alumno_id === $alumno->id, 403, 'Solo el organizador del grupo puede transferir ese rol.');
        $this->bloquearSiPeriodoCerrado($grupo);

        $data = $request->validate([
            'nuevo_organizador_id' => 'required|exists:alumnos,id',
        ]);

        $nuevoOrganizadorId = (int) $data['nuevo_organizador_id'];
        abort_if($nuevoOrganizadorId === $alumno->id, 422, 'Ya eres el organizador de este grupo.');

        $esAceptado = $grupo->alumnosAceptados()->where('alumnos.id', $nuevoOrganizadorId)->exists();
        abort_unless($esAceptado, 422, 'Solo puedes transferir el rol a un integrante ya aceptado del grupo.');

        $grupo->creador_alumno_id = $nuevoOrganizadorId;
        $grupo->save();

        $nuevoOrganizador = Alumno::find($nuevoOrganizadorId);
        if ($nuevoOrganizador?->user) {
            try {
                $nuevoOrganizador->user->notify(new TaqeOrganizadorCambiado($grupo));
            } catch (\Throwable $e) {
                Log::warning("Notificación de nuevo organizador Taq'e a alumno {$nuevoOrganizador->id}: ".$e->getMessage());
            }
        }

        return back()->with('success', $nuevoOrganizador->apellidos.', '.$nuevoOrganizador->nombres.' ahora es el organizador del grupo.');
    }

    /**
     * Cualquier integrante ACEPTADO (no solo el organizador — si el problema
     * es justo que el organizador no coopera, no tendría sentido exigir que
     * sea él quien pida ayuda) puede enviar una solicitud al Investigador.
     */
    public function solicitarAyuda(Request $request, TaqeGrupo $grupo)
    {
        $alumno = $this->alumnoActual();
        abort_unless($alumno, 403);
        $this->verificarIntegranteAceptado($grupo, $alumno);
        $this->bloquearSiPeriodoCerrado($grupo);

        $data = $request->validate([
            'motivo' => 'required|in:cambiar_organizador,disolver_grupo,otro',
            'mensaje' => 'nullable|string|max:1000|required_if:motivo,otro',
        ]);

        $solicitud = TaqeSolicitudAyuda::create([
            'taqe_grupo_id' => $grupo->id,
            'grupo_nombre' => $grupo->nombre,
            'alumno_id' => $alumno->id,
            'motivo' => $data['motivo'],
            'mensaje' => $data['mensaje'] ?? null,
            'estado' => 'pendiente',
        ]);

        $investigadores = User::role('investigador')->get();
        foreach ($investigadores as $investigador) {
            try {
                $investigador->notify(new TaqeSolicitudAyudaCreada($solicitud));
            } catch (\Throwable $e) {
                Log::warning("Notificación de solicitud de ayuda Taq'e a investigador {$investigador->id}: ".$e->getMessage());
            }
        }

        return back()->with('success', 'Solicitud enviada al Investigador. Te avisaremos cuando la atiendan.');
    }

    private function verificarIntegranteAceptado(TaqeGrupo $grupo, Alumno $alumno): void
    {
        $esIntegrante = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();
        abort_unless($esIntegrante, 403, 'No perteneces a este grupo.');
    }

}
