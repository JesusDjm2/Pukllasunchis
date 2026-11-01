<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAlumnoPerfilRequest;
use App\Http\Resources\AlumnoResource;
use App\Mail\NotificacionRegistro;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AlumnoController extends Controller
{
    public function perfil(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $alumno->load('programa', 'ciclo.tutores.docente');

        return response()->json(['data' => new AlumnoResource($alumno)]);
    }

    public function carnet(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $alumno->loadMissing(['programa', 'ciclo', 'user']);

        $fotoNombre = $alumno->user?->foto;
        $fotoUrl = null;
        if ($fotoNombre) {
            if (str_starts_with($fotoNombre, 'http://') || str_starts_with($fotoNombre, 'https://')) {
                $fotoUrl = $fotoNombre;
            } else {
                $fotoUrl = $request->getSchemeAndHttpHost() . '/api/v1/foto/estudiante/' . ltrim($fotoNombre, '/');
            }
        }

        $email = $alumno->user?->email ?? $alumno->email ?? '';
        preg_match('/20\d{2}/', $email, $matches);
        $anioIngreso = $matches[0] ?? (($alumno->user?->created_at ?? $alumno->created_at)?->format('Y') ?? date('Y'));

        return response()->json([
            'data' => [
                'alumno_id' => $alumno->id,
                'nombres' => $alumno->nombres,
                'apellidos' => $alumno->apellidos,
                'nombre_completo' => trim("{$alumno->nombres} {$alumno->apellidos}"),
                'dni' => $alumno->dni,
                'email' => $email,
                'programa_nombre' => $alumno->programa?->nombre ?? 'EESP Pukllasunchis',
                'ciclo_nombre' => $alumno->ciclo?->nombre ?? 'I',
                'anio_ingreso' => (string) $anioIngreso,
                'foto' => $fotoUrl,
                'institucion' => 'EESP PUKLLASUNCHIS',
                'codigo_qr' => "PUKLLA-ALU-{$alumno->id}-{$alumno->dni}",
            ],
        ]);
    }

    /**
     * Mismo flujo que `AlumnoController@actualizarDatos` (web): actualiza los
     * datos socioeconómicos del alumno y, si el periodo actual tiene el
     * formulario de matrícula habilitado, completa/renueva su `Matricula` del
     * periodo (con el comprobante si no es becado) y notifica por correo a la
     * institución la primera vez que se matricula en ese periodo.
     */
    public function actualizarPerfil(UpdateAlumnoPerfilRequest $request)
    {
        $alumno = $request->user()->alumno;

        $datos = $request->safe()->except('comprobante');
        $datos['bienes_vivienda'] = $request->input('bienes_vivienda', []);
        $datos['otros_servicios'] = $request->input('otros_servicios', []);
        $alumno->update($datos);

        $periodoActual = PeriodoActual::actual();
        $matriculaCompletada = false;

        if ($periodoActual && $periodoActual->formulario_habilitado) {
            $yaEstabaMatriculado = $alumno->matriculaEnPeriodo($periodoActual->id) !== null;

            $comprobante = $request->filled('comprobante') ? $request->comprobante : null;
            Matricula::updateOrCreate(
                ['alumno_id' => $alumno->id, 'periodo_actual_id' => $periodoActual->id],
                array_filter(['fecha_completado' => now(), 'estado' => 'matriculado', 'comprobante' => $comprobante])
            );
            $matriculaCompletada = true;

            if (! $yaEstabaMatriculado) {
                try {
                    Mail::to(config('services.notificaciones.emails'))
                        ->send(new NotificacionRegistro($alumno, $periodoActual));
                } catch (\Exception $e) {
                    Log::error('Error enviando email de matrícula (API actualizarPerfil): '.$e->getMessage());
                }
            }
        }

        return response()->json([
            'data' => new AlumnoResource($alumno->fresh()),
            'matricula_completada' => $matriculaCompletada,
        ]);
    }

    /**
     * Misma plantilla que `vistasAlumnosController::exportarFichaPDF` (web),
     * pero escaneada al propio alumno autenticado — la ruta web acepta
     * cualquier `{alumno}` porque está detrás de permisos de admin/self ahí;
     * aquí no aceptamos un id arbitrario para evitar exponer fichas ajenas.
     */
    public function fichaPdf(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $alumno->load(['ciclo.cursos', 'cursos', 'programa']);
        $pdf = Pdf::loadView('alumnos.vistasAlumnos.pdf', compact('alumno'));

        return $pdf->download('ficha_'.Str::slug($alumno->apellidos.'_'.$alumno->nombres).'.pdf');
    }
}
