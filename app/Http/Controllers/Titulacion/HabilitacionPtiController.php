<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Habilitacion;
use App\Models\User;
use App\Notifications\TitulacionPtiHabilitada;
use App\Services\Titulacion\AreasTitulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Excepciones de Secretaría: habilitar a un alumno para que solicite la aprobación de su PTI aunque todavía no
 * cumpla la regla del ciclo (FID desde el Ciclo IX). Se busca por DNI o apellidos; la lista muestra a los habilitados
 * (con quién y por qué) y se puede quitar la habilitación mientras no tenga proceso iniciado.
 */
class HabilitacionPtiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $resultados = collect();
        if (mb_strlen($q) >= 3) {
            $palabras = preg_split('/\s+/', $q);
            $resultados = User::query()
                ->whereHas('roles', fn ($r) => $r->whereIn('name', ['alumno', 'alumnoB']))
                ->where(fn ($w) => $w->where('dni', 'like', $q.'%')->orWhere(function ($w) use ($palabras) {
                    foreach ($palabras as $p) {
                        $w->where(fn ($x) => $x->where('apellidos', 'like', "%{$p}%")->orWhere('name', 'like', "%{$p}%"));
                    }
                }))
                ->with(['ciclo.programa', 'programa', 'roles', 'seguimientoTitulacion'])
                ->orderBy('apellidos')->limit(20)->get();
        }

        return view('titulacion.solicitudes.habilitaciones', [
            'q' => $q,
            'resultados' => $resultados,
            'habilitados' => Habilitacion::with(['user.ciclo', 'user.seguimientoTitulacion', 'habilitadoPor'])->latest()->get(),
            'puedeGestionar' => AreasTitulacion::puedeRuta('titulacion.habilitaciones.store'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'motivo' => 'required|string|min:5|max:500',
        ], ['motivo.required' => 'Escriba el motivo de la excepción (por ejemplo, la autorización de Dirección).'], ['motivo' => 'motivo']);
        $alumno = User::with('roles')->findOrFail($data['user_id']);
        $volver = redirect()->route('titulacion.habilitaciones.index');
        if (! $alumno->hasAnyRole(['alumno', 'alumnoB'])) {
            return $volver->with('error', 'Solo se puede habilitar a estudiantes.');
        }
        if ($alumno->seguimientoTitulacion()->exists()) {
            return $volver->with('error', 'Esa persona ya tiene su proceso de titulación iniciado: no necesita habilitación.');
        }

        Habilitacion::updateOrCreate(['user_id' => $alumno->id], ['motivo' => trim($data['motivo']), 'habilitado_por' => $request->user()->id]);
        Habilitacion::olvidar();
        try {
            $alumno->notify(new TitulacionPtiHabilitada);
        } catch (\Throwable $e) {
            Log::warning('No se pudo avisar la habilitación del PTI', ['user' => $alumno->id, 'error' => $e->getMessage()]);
        }

        return $volver->with('success', trim($alumno->apellidos.', '.$alumno->name).' ya puede solicitar la aprobación de su PTI desde su intranet (se le avisó en su campanita).');
    }

    public function destroy(Habilitacion $habilitacion)
    {
        $nombre = trim(($habilitacion->user?->apellidos ?? '').', '.($habilitacion->user?->name ?? ''), ', ');
        $habilitacion->delete();
        Habilitacion::olvidar();

        return redirect()->route('titulacion.habilitaciones.index')->with('success', "Se quitó la habilitación de {$nombre}. Si ya envió su solicitud, esta sigue en la bandeja.");
    }
}
