<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Sugerencia;
use App\Models\Tutoria;
use App\Notifications\SugerenciaCreada;
use App\Notifications\TutoriaCreada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TutoriaController extends Controller
{
    /**
     * Retorna los datos necesarios para la pantalla de Tutoría y Sugerencias:
     * - Datos del alumno y tutor(es) de su ciclo
     * - Las 5 opciones estándar de urgencia de Tutoria::OPCIONES
     * - Lista de docentes elegibles para la opción "otro docente"
     * - Historial de solicitudes y sugerencias enviadas por el alumno
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $alumno = $user->alumno;

        if (! $alumno) {
            return response()->json([
                'message' => 'No se encontró un perfil de alumno asociado a esta cuenta.',
            ], 404);
        }

        $alumno->loadMissing('ciclo.programa', 'ciclo.tutores');

        $tutoresNombres = $alumno->ciclo
            ? $alumno->ciclo->tutores->map->nombreCorto()->filter()->values()->all()
            : [];

        // Opciones de urgencia
        $opciones = collect(Tutoria::OPCIONES)->map(fn ($val, $key) => [
            'clave'          => $key,
            'texto'          => $val[0],
            'etiqueta_corta' => $val[1],
            'color'          => $val[2],
        ])->values()->all();

        // Docentes elegibles para "otro docente"
        $docentesAgrupados = Tutoria::docentesElegibles($alumno->ciclo_id);
        $docentesLista = [];
        foreach ($docentesAgrupados as $grupo => $docentes) {
            foreach ($docentes as $d) {
                $docentesLista[] = [
                    'id'      => $d['id'],
                    'nombre'  => $d['nombre'],
                    'detalle' => $d['detalle'],
                    'grupo'   => $grupo,
                ];
            }
        }

        // Historial unificado
        $tutorias = Tutoria::where('alumno_id', $alumno->id)->latest()->take(20)->get();
        $sugerencias = Sugerencia::where('alumno_id', $alumno->id)->latest()->take(20)->get();

        $historial = $tutorias->map(fn ($t) => [
            'id'       => 'tut_' . $t->id,
            'real_id'  => $t->id,
            'tipo'     => 'tutoria',
            'etiqueta' => 'Tutoría',
            'texto'    => $t->motivo ?: (Tutoria::OPCIONES[$t->urgencia][0] ?? 'Solicitud de tutoría'),
            'fecha'    => $t->created_at?->format('d/m/Y H:i'),
            'created_at' => $t->created_at?->toISOString(),
            'listo'    => $t->estado === 'atendida',
            'estado'   => $t->estado === 'atendida' ? 'Atendida' : 'Pendiente',
            'extra'    => $t->urgencia_texto . ($t->pideOtroDocente() && $t->docente_solicitado_nombre ? ': ' . $t->docente_solicitado_nombre : ''),
        ])->concat($sugerencias->map(fn ($s) => [
            'id'       => 'sug_' . $s->id,
            'real_id'  => $s->id,
            'tipo'     => 'sugerencia',
            'etiqueta' => 'Sugerencia',
            'texto'    => $s->mensaje,
            'fecha'    => $s->created_at?->format('d/m/Y H:i'),
            'created_at' => $s->created_at?->toISOString(),
            'listo'    => $s->estado === 'revisada',
            'estado'   => $s->estado === 'revisada' ? 'Revisada' : 'Pendiente',
            'extra'    => null,
        ]))->sortByDesc('created_at')->values()->all();

        return response()->json([
            'data' => [
                'alumno' => [
                    'id'             => $alumno->id,
                    'nombre_completo'=> $alumno->nombreCompleto(),
                    'programa'       => $alumno->ciclo?->programa?->nombre ?? 'Sin programa',
                    'ciclo'          => $alumno->ciclo ? 'Ciclo ' . $alumno->ciclo->nombre : 'Sin ciclo',
                    'tutores'        => $tutoresNombres,
                ],
                'opciones'           => $opciones,
                'docentes_elegibles' => $docentesLista,
                'historial'          => $historial,
            ],
        ]);
    }

    /**
     * Registrar solicitud de tutoría individual.
     */
    public function storeTutoria(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno || ! $alumno->ciclo_id) {
            return response()->json([
                'message' => 'Necesitas tener un ciclo asignado para solicitar tutorías.',
            ], 422);
        }

        $clavesOpciones = implode(',', array_keys(Tutoria::OPCIONES));
        $data = $request->validate([
            'urgencia'   => "required|string|in:$clavesOpciones",
            'docente_id' => 'required_if:urgencia,' . Tutoria::OTRO_DOCENTE . '|nullable|integer|exists:users,id',
            'motivo'     => 'nullable|string|max:3000',
        ], [
            'urgencia.required'    => 'Debes elegir cómo deseas conversar.',
            'docente_id.required_if' => 'Debes elegir con qué docente deseas conversar.',
            'motivo.max'           => 'El motivo no puede exceder los 3000 caracteres.',
        ]);

        if ($data['urgencia'] !== Tutoria::OTRO_DOCENTE) {
            $data['docente_id'] = null;
        }

        $tutoria = Tutoria::create([
            'programa_id' => $alumno->ciclo?->programa_id,
            'ciclo_id'    => $alumno->ciclo_id,
            'alumno_id'   => $alumno->id,
            'urgencia'    => $data['urgencia'],
            'docente_id'  => $data['docente_id'],
            'motivo'      => trim((string) ($data['motivo'] ?? '')) ?: null,
            'estado'      => 'pendiente',
        ]);

        $tutoria->loadMissing(['alumno', 'ciclo.programa', 'docenteSolicitado']);

        // Notificar en segundo plano / si hay tutores
        try {
            $destinatarios = $tutoria->pideOtroDocente() && $tutoria->docenteSolicitado
                ? collect([$tutoria->docenteSolicitado])
                : ($alumno->ciclo?->tutores ?? collect());

            foreach ($destinatarios as $dest) {
                $dest->notify(new TutoriaCreada($tutoria));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar notificación de tutoría: ' . $e->getMessage());
        }

        $mensaje = $tutoria->pideOtroDocente()
            ? 'Solicitud enviada. El/la docente que elegiste se pondrá en contacto contigo.'
            : 'Solicitud enviada. Tu tutor(a) ya fue notificado y se pondrá en contacto contigo.';

        return response()->json([
            'message' => $mensaje,
            'data'    => $tutoria,
        ], 201);
    }

    /**
     * Registrar mensaje en el buzón de sugerencias.
     */
    public function storeSugerencia(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno || ! $alumno->ciclo_id) {
            return response()->json([
                'message' => 'Necesitas tener un ciclo asignado para enviar sugerencias.',
            ], 422);
        }

        $data = $request->validate([
            'mensaje' => 'required|string|min:10|max:3000',
        ], [
            'mensaje.required' => 'Escribe tu sugerencia.',
            'mensaje.min'      => 'Escribe al menos 10 caracteres para que tu tutor(a) entienda la idea.',
            'mensaje.max'      => 'La sugerencia no puede exceder los 3000 caracteres.',
        ]);

        $sugerencia = Sugerencia::create([
            'programa_id' => $alumno->ciclo?->programa_id,
            'ciclo_id'    => $alumno->ciclo_id,
            'alumno_id'   => $alumno->id,
            'mensaje'     => trim($data['mensaje']),
            'es_anonima'  => false,
            'estado'      => 'pendiente',
        ]);

        $sugerencia->loadMissing(['alumno', 'ciclo.programa']);

        try {
            $tutores = $alumno->ciclo?->tutores ?? collect();
            foreach ($tutores as $tutor) {
                $tutor->notify(new SugerenciaCreada($sugerencia));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar notificación de sugerencia: ' . $e->getMessage());
        }

        return response()->json([
            'message' => '¡Gracias por tu sugerencia! Tu tutor(a) la revisará.',
            'data'    => $sugerencia,
        ], 201);
    }
}
