<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteDocumento;
use App\Models\Incidencia;
use App\Models\SeguimientoAccion;
use App\Models\TrayectoriaEvento;
use App\Models\Tutoria;
use App\Models\User;
use App\Services\HistorialAcademico;
use App\Services\Trayectoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Expediente del estudiante (admin y super-admin): resumen, línea de tiempo de su
 * trayectoria y notas por periodo. La trayectoria se registra sola al cambiar perfil /
 * condición / ciclo (ver Trayectoria); aquí además se completan fechas y se registran
 * a mano hechos que el sistema no puede detectar (sanciones, observaciones, licencias
 * antiguas…).
 */
class ExpedienteController extends Controller
{
    /** Tipos que se pueden registrar a mano (los demás los genera el sistema). */
    private const TIPOS_MANUALES = ['licencia', 'retiro', 'reincorporacion', 'sancion', 'observacion', 'beca', 'egreso'];

    public function show(User $user)
    {
        abort_unless($user->hasAnyRole(['alumno', 'alumnoB', 'inhabilitado']) || $user->alumno || $user->alumnoB, 404);

        $user->load(['programa', 'ciclo', 'alumno', 'alumnoB', 'roles']);
        $historial = HistorialAcademico::de($user);
        $licencia = Trayectoria::licenciaAcumulada($user);
        $eventos = TrayectoriaEvento::with(['registradoPor', 'periodoActual', 'periodoActualPpd', 'cicloOrigen', 'cicloDestino'])
            ->where('user_id', $user->id)->get();

        // Línea de tiempo: eventos + matrículas (cada matrícula es un hito con su ciclo).
        $hitos = $eventos->map(fn ($e) => (object) [
            'clase' => 'evento',
            'fecha' => $e->fecha_inicio ?? null,
            'orden' => $e->fecha_inicio?->timestamp ?? PHP_INT_MAX,
            'evento' => $e,
        ]);
        foreach ($historial['periodos']->where('matriculado', true) as $p) {
            $hitos->push((object) [
                'clase' => 'matricula',
                'fecha' => null,
                'orden' => null,
                'periodo' => $p,
            ]);
        }
        // Las matrículas no tienen fecha propia fiable: se ordenan por periodo, intercaladas
        // con los eventos de ese periodo; los eventos sin fecha (estado inicial) van arriba.
        $hitos = $hitos->sortByDesc(function ($h) {
            if ($h->clase === 'evento') {
                return [$h->evento->fecha_inicio ? 1 : 2, $h->orden, $h->evento->id];
            }

            return [1, $this->fechaPeriodo($h->periodo), 0];
        })->values();

        // Admin y super-admin gestionan el expediente; el gestor de titulación solo lo consulta y
        // NO ve el seguimiento ni los documentos (pueden ser confidenciales: solo admin/super-admin).
        $puedeGestionar = auth()->user()->hasAnyRole(['admin', 'super-admin']);

        $acciones = $puedeGestionar
            ? SeguimientoAccion::with(['responsable', 'registradoPor', 'documentos'])
                ->where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->get()
            : collect();
        $documentos = $puedeGestionar
            ? ExpedienteDocumento::with(['evento', 'accion', 'subidoPor'])->where('user_id', $user->id)->latest()->get()
            : collect();
        // Incidencias y tutorías existentes (apuntan a la ficha FID del alumno)
        $incidencias = $puedeGestionar && $user->alumno ? Incidencia::where('alumno_id', $user->alumno->id)->latest('fecha')->get() : collect();
        $tutorias = $puedeGestionar && $user->alumno ? Tutoria::where('alumno_id', $user->alumno->id)->latest()->get() : collect();

        $titulacion = $this->titulacion($user);

        // Cursos asignados con "Asignar cursos" (FID), por período. Solo cuentan los del período
        // en que se asignaron (Curso::alumnosDelPeriodo); las filas antiguas sin período se
        // muestran aparte, como referencia, porque ya no hacen que el alumno figure en un curso.
        $filasAsignacion = $user->alumno
            ? \Illuminate\Support\Facades\DB::table('alumno_cursos')->where('alumno_id', $user->alumno->id)->get(['curso_id', 'periodo_actual_id'])
            : collect();
        $cursosAsignados = \App\Models\Curso::with('ciclo')->whereIn('id', $filasAsignacion->pluck('curso_id')->unique())->get()->keyBy('id');
        $asignaciones = $filasAsignacion->groupBy(fn ($r) => $r->periodo_actual_id ?? 'sin')
            ->map(fn ($filas) => $filas->map(fn ($r) => $cursosAsignados[$r->curso_id] ?? null)->filter()->sortBy('nombre')->values());
        $periodoActualFid = \App\Models\PeriodoActual::actual();

        $alertas = $this->alertas($user, $historial, $licencia);
        $vencidas = $acciones->filter(fn ($a) => $a->vencida);
        if ($vencidas->isNotEmpty()) {
            $alertas[] = ['warning', 'fa-calendar-times', $vencidas->count() . ' acción(es) de seguimiento con la revisión vencida (la más antigua: '
                . $vencidas->min('proxima_revision')->format('d/m/Y') . ').'];
        }

        // Personal que puede figurar como responsable de un seguimiento
        $responsables = User::role(['admin', 'super-admin', 'tutor', 'docente'])
            ->orderBy('apellidos')->get(['id', 'name', 'apellidos']);

        return view('admin.expediente.show', [
            'u' => $user,
            'historial' => $historial,
            'licencia' => $licencia,
            'hitos' => $hitos,
            'alertas' => $alertas,
            'tiposManuales' => collect(self::TIPOS_MANUALES)->mapWithKeys(fn ($t) => [$t => TrayectoriaEvento::TIPOS[$t]]),
            'acciones' => $acciones,
            'documentos' => $documentos,
            'incidencias' => $incidencias,
            'tutorias' => $tutorias,
            'eventos' => $eventos,
            'responsables' => $responsables,
            'puedeGestionar' => $puedeGestionar,
            'titulacion' => $titulacion,
            'asignaciones' => $asignaciones,
            'periodoActualFid' => $periodoActualFid,
            'layout' => $this->layout($puedeGestionar),
        ]);
    }

    /**
     * Pestaña Titulación: solo para el gestor de titulación, y solo si el módulo existe en este
     * servidor (hoy está únicamente en local: en producción la pestaña no aparece y nada falla).
     */
    private function titulacion(User $user): ?array
    {
        if (! auth()->user()->hasRole('gestor-titulacion')
            || ! class_exists(\App\Models\Titulacion\SeguimientoTitulacion::class)
            || ! \Illuminate\Support\Facades\Schema::hasTable('seguimiento_titulacion')) {
            return null;
        }

        $seguimiento = \App\Models\Titulacion\SeguimientoTitulacion::with(['ptis.asesor', 'ptis.asesorExterno'])
            ->where('user_id', $user->id)->first();

        return [
            'seguimiento' => $seguimiento,
            'avance' => $seguimiento && method_exists($seguimiento, 'avance') ? $seguimiento->avance() : null,
            'url' => $seguimiento && \Illuminate\Support\Facades\Route::has('titulacion.show')
                ? route('titulacion.show', $seguimiento->user_id) : null,
        ];
    }

    /** El gestor de titulación (sin rol admin) ve el expediente con su propio layout. */
    private function layout(bool $puedeGestionar): string
    {
        if (! $puedeGestionar && class_exists(\App\Http\Controllers\Titulacion\TitulacionController::class)) {
            return \App\Http\Controllers\Titulacion\TitulacionController::layout();
        }

        return auth()->user()->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';
    }

    public function storeEvento(Request $request, User $user)
    {
        $datos = $this->validar($request, true);

        // Una licencia nueva respeta el máximo acumulado (salvo que ya esté cerrada: es historia).
        if ($datos['tipo'] === 'licencia' && empty($datos['fecha_fin']) && ! Trayectoria::puedeIniciarLicencia($user)) {
            return back()->withInput()->with('error', Trayectoria::mensajeLicenciaAgotada($user));
        }

        Trayectoria::crear($user, $datos['tipo'], 'manual', array_filter([
            'fecha_inicio' => $datos['fecha_inicio'] ?? null,
            'fecha_fin' => $datos['fecha_fin'] ?? null,
            'motivo' => $datos['motivo'] ?? null,
            'resolucion' => $datos['resolucion'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));

        return redirect()->route('expediente.show', $user)->with('success', 'Evento registrado en la trayectoria.');
    }

    public function updateEvento(Request $request, TrayectoriaEvento $evento)
    {
        $datos = $this->validar($request, false);
        $evento->update([
            'fecha_inicio' => $datos['fecha_inicio'] ?? null,
            'fecha_fin' => $datos['fecha_fin'] ?? null,
            'motivo' => $datos['motivo'] ?? null,
            'resolucion' => $datos['resolucion'] ?? null,
        ]);

        return redirect()->route('expediente.show', $evento->user_id)->with('success', 'Evento actualizado.');
    }

    /** Solo se borran eventos registrados a mano; los automáticos son el registro del sistema. */
    public function destroyEvento(TrayectoriaEvento $evento)
    {
        abort_unless($evento->origen === 'manual', 403, 'Los eventos registrados por el sistema no se eliminan; corrígelos editándolos.');
        $evento->delete();

        return redirect()->route('expediente.show', $evento->user_id)->with('success', 'Evento eliminado.');
    }

    // ── Seguimiento (acompañamiento) ─────────────────────────────────────

    public function storeAccion(Request $request, User $user)
    {
        SeguimientoAccion::create($this->validarAccion($request) + [
            'user_id' => $user->id,
            'estado' => 'abierto',
            'registrado_por' => auth()->id(),
        ]);

        return redirect()->to(route('expediente.show', $user) . '#seguimiento')->with('success', 'Acción de seguimiento registrada.');
    }

    public function updateAccion(Request $request, SeguimientoAccion $accion)
    {
        $datos = $this->validarAccion($request);
        $datos['estado'] = $request->input('estado') === 'cerrado' ? 'cerrado' : 'abierto';
        $accion->update($datos);

        return redirect()->to(route('expediente.show', $accion->user_id) . '#seguimiento')->with('success', 'Acción de seguimiento actualizada.');
    }

    /** Cerrar o reabrir con un clic desde la lista. */
    public function toggleAccion(SeguimientoAccion $accion)
    {
        $accion->update(['estado' => $accion->estado === 'abierto' ? 'cerrado' : 'abierto']);

        return redirect()->to(route('expediente.show', $accion->user_id) . '#seguimiento')
            ->with('success', $accion->estado === 'cerrado' ? 'Acción cerrada.' : 'Acción reabierta.');
    }

    public function destroyAccion(SeguimientoAccion $accion)
    {
        $accion->delete();

        return redirect()->to(route('expediente.show', $accion->user_id) . '#seguimiento')->with('success', 'Acción eliminada.');
    }

    private function validarAccion(Request $request): array
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(SeguimientoAccion::TIPOS))],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'resumen' => ['required', 'string', 'max:5000'],
            'acuerdos' => ['nullable', 'string', 'max:5000'],
            'proxima_revision' => ['nullable', 'date', 'after_or_equal:fecha'],
        ], [
            'fecha.before_or_equal' => 'La fecha de la acción no puede ser futura.',
            'proxima_revision.after_or_equal' => 'La próxima revisión no puede ser anterior a la fecha de la acción.',
        ]);
        $datos['confidencial'] = $request->boolean('confidencial');

        return $datos;
    }

    // ── Documentos ───────────────────────────────────────────────────────

    public function storeDocumento(Request $request, User $user)
    {
        $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            // 10 MB: por debajo del tope que ya se probó seguro en el hosting (25 MB).
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
            'vinculo' => ['nullable', 'regex:/^(evento|accion):\d+$/'],
        ], [
            'archivo.max' => 'El archivo no puede pasar de 10 MB.',
            'archivo.mimes' => 'Formatos permitidos: PDF, imagen (JPG, PNG, WEBP), Word o Excel.',
        ]);

        [$evento, $accion] = [null, null];
        if ($request->filled('vinculo')) {
            [$tipo, $id] = explode(':', $request->input('vinculo'));
            $tipo === 'evento'
                ? $evento = TrayectoriaEvento::where('user_id', $user->id)->findOrFail($id)->id
                : $accion = SeguimientoAccion::where('user_id', $user->id)->findOrFail($id)->id;
        }

        $archivo = $request->file('archivo');
        $ruta = $archivo->storeAs(
            'expedientes/' . $user->id,
            now()->format('Ymd_His') . '_' . Str::random(6) . '.' . strtolower($archivo->getClientOriginalExtension()),
            'local'
        );

        ExpedienteDocumento::create([
            'user_id' => $user->id,
            'trayectoria_evento_id' => $evento,
            'seguimiento_accion_id' => $accion,
            'titulo' => $request->input('titulo'),
            'archivo' => $ruta,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getClientMimeType(),
            'tamano' => $archivo->getSize(),
            'subido_por' => auth()->id(),
        ]);

        return redirect()->to(route('expediente.show', $user) . '#documentos')->with('success', 'Documento subido al expediente.');
    }

    public function verDocumento(ExpedienteDocumento $documento)
    {
        abort_unless(Storage::disk('local')->exists($documento->archivo), 404, 'El archivo ya no está en el servidor.');

        // PDF e imágenes se abren en el navegador; el resto se descarga.
        $enLinea = $documento->es_imagen || $documento->mime === 'application/pdf';

        return Storage::disk('local')->response($documento->archivo, $documento->nombre_original, [], $enLinea ? 'inline' : 'attachment');
    }

    public function destroyDocumento(ExpedienteDocumento $documento)
    {
        // Borrado lógico: el archivo físico se conserva por si hay que recuperarlo.
        $documento->delete();

        return redirect()->to(route('expediente.show', $documento->user_id) . '#documentos')->with('success', 'Documento eliminado del expediente.');
    }

    private function validar(Request $request, bool $conTipo): array
    {
        return $request->validate(array_filter([
            'tipo' => $conTipo ? ['required', Rule::in(self::TIPOS_MANUALES)] : null,
            'fecha_inicio' => [$conTipo ? 'required' : 'nullable', 'date', 'before_or_equal:today'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio', 'before_or_equal:today'],
            'motivo' => ['nullable', 'string', 'max:2000'],
            'resolucion' => ['nullable', 'string', 'max:255'],
        ]), [
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'before_or_equal' => 'La fecha no puede ser futura.',
        ]);
    }

    private function alertas(User $user, array $historial, array $licencia): array
    {
        $alertas = [];

        if ($licencia['agotada']) {
            $alertas[] = ['danger', 'fa-ban', "Alcanzó el máximo de licencias: {$licencia['dias']} de {$licencia['limite']} días acumulados. No puede pedir otra licencia."];
        } elseif ($licencia['activa'] && $licencia['restante'] <= 90) {
            $alertas[] = ['warning', 'fa-hourglass-end', "Su licencia actual agota el máximo acumulado en {$licencia['restante']} días"
                . ($licencia['fecha_limite'] ? ' (' . $licencia['fecha_limite']->format('d/m/Y') . ')' : '') . '.'];
        }
        if ($licencia['sin_fecha'] > 0) {
            $alertas[] = ['info', 'fa-calendar-plus', 'Tiene ' . $licencia['sin_fecha'] . ' licencia(s) sin fecha de inicio: complétala(s) en la trayectoria para poder controlar el máximo de 2 años.'];
        }
        if ($historial['resumen']['repitencias']->isNotEmpty()) {
            $ciclos = $historial['resumen']['repitencias']->map(fn ($p) => ($p->ciclo->nombre ?? '?') . ' en ' . $p->periodo)->implode(', ');
            $alertas[] = ['warning', 'fa-redo', "Repitió ciclo: {$ciclos}."];
        }
        $ultimo = $historial['periodos']->filter(fn ($p) => $p->cursos->isNotEmpty())->last();
        if ($ultimo && $ultimo->desaprobados > 0) {
            $alertas[] = ['danger', 'fa-exclamation-circle', "Desaprobó {$ultimo->desaprobados} curso(s) en su último periodo con notas ({$ultimo->periodo})."];
        }

        return $alertas;
    }

    /** Valor ordenable de un periodo: los FID y PPD tienen tablas distintas, se usa el nombre (año). */
    private function fechaPeriodo($periodo): int
    {
        if (preg_match('/(\d{4})(?:\s*-\s*(I{1,2}))?/', $periodo->periodo, $m)) {
            $mes = ($m[2] ?? '') === 'II' ? 8 : 3;

            return mktime(0, 0, 0, $mes, 1, (int) $m[1]);
        }

        return 0;
    }
}
