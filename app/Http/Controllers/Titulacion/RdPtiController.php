<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\NormalizarRdPti;
use App\Services\Titulacion\RdPti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RD de aprobación del PTI con datos del Excel (número guardado como nombre de archivo, sin año):
 *  - completar(): Secretaría escribe número y año desde la ficha; se corrige en todo el grupo.
 *  - normalizar()/aplicar(): el super-admin deja en formato «088-2021» las que ya traen el año, con vista
 *    previa y sin tocar los casos que cambiarían un grupo (ver NormalizarRdPti).
 */
class RdPtiController extends Controller
{
    public function completar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'numero' => ['required', 'regex:/^\d{1,4}$/'],
            'anio' => 'required|integer|min:1990|max:'.(now()->year + 1),
        ], ['numero.regex' => 'El N° de RD es solo el número (ej.: 88).'], ['anio' => 'año']);
        $s = SeguimientoTitulacion::with(['pti', 'ptis', 'resoluciones', 'actas'])->where('user_id', $egresado->id)->firstOrFail();
        $volver = redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso', 'paso' => 'pti_datos']);

        $d = RdPti::de($s);
        $nuevo = RdPti::rellenar($data['numero']).'-'.$data['anio'];
        $original = $d['texto'];

        // Todo el grupo comparte esta RD: se corrige en las fichas de todos (mismos textos originales).
        $grupo = GrupoTitulacion::companeros($s)->map(fn ($u) => $u->seguimientoTitulacion?->id)->filter()->push($s->id)->unique();
        $textos = collect([$original, $d['resolucion']?->numero_rd])->filter()->map(fn ($t) => trim($t))->unique();
        $resoluciones = Resolucion::whereIn('seguimiento_id', $grupo)->where('tipo', 'PTI')->get()
            ->filter(fn ($r) => $textos->contains(trim((string) $r->numero_rd)));
        $ptis = Pti::whereIn('seguimiento_id', $grupo)->get()->filter(fn ($p) => $textos->contains(trim((string) $p->rd_pti)));
        if ($original === null) {
            // No había RD: se anota en el PTI vigente de esta persona (el resto se registra en Historial).
            $ptis = collect([$s->pti])->filter();
        }

        // Ese número ya es de OTRO grupo: guardarlo los uniría. Se avisa y no se guarda.
        $clave = GrupoTitulacion::claveRd($nuevo);
        $ajenos = Resolucion::where('tipo', 'PTI')->where('agrupa', true)->whereNotIn('seguimiento_id', $grupo)->whereNotNull('numero_rd')->get()
            ->filter(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === $clave);
        if ($ajenos->isNotEmpty()) {
            $nombres = SeguimientoTitulacion::with('user:id,name,apellidos')->whereIn('id', $ajenos->pluck('seguimiento_id'))->get()
                ->map(fn ($x) => trim($x->user?->apellidos.', '.$x->user?->name))->implode('; ');

            return $volver->with('error', "La RD {$nuevo} ya está registrada para otro grupo ({$nombres}). Guardarla uniría a los dos grupos: verifique el número en el expediente.");
        }

        DB::transaction(function () use ($resoluciones, $ptis, $nuevo) {
            foreach ($resoluciones as $r) {
                $r->update(['numero_rd' => $nuevo]);
            }
            foreach ($ptis as $p) {
                $p->update(['rd_pti' => $nuevo]);
            }
        });

        $n = $grupo->count();

        return $volver->with('success', "RD del PTI guardada como {$nuevo}".($n > 1 ? " para los {$n} integrantes del grupo." : '.'));
    }

    public function normalizar()
    {
        return view('titulacion.rd.normalizar', ['plan' => NormalizarRdPti::plan(false)]);
    }

    public function aplicar(Request $request)
    {
        $request->validate(['confirmar' => 'accepted'], ['confirmar.accepted' => 'Marque la casilla de confirmación.']);
        [$n, $respaldo] = NormalizarRdPti::aplicar(false);

        return redirect()->route('titulacion.rdpti.normalizar')->with('success', $n
            ? "Listo: {$n} registros quedaron en formato «088-2021». Respaldo de los valores originales: storage/app/{$respaldo}."
            : 'No había nada que corregir.');
    }
}
