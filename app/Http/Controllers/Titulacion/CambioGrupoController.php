<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use App\Services\Titulacion\CambioGrupo;
use App\Services\Titulacion\GrupoTitulacion;
use Illuminate\Http\Request;

/**
 * Secretaría corrige el grupo de titulación de una persona (retiro de un grupo e incorporación a otro cuya
 * RD ya se emitió). Dos pasos: elegir el cambio y ver «Así quedará» (GET, no cambia nada) y aplicarlo
 * (POST, con motivo, verificación y alerta). La lógica está en CambioGrupo.
 */
class CambioGrupoController extends Controller
{
    public function editar(Request $request, User $egresado)
    {
        $s = $this->seguimiento($egresado);
        $companeros = CambioGrupo::companeros($s);

        $filtros = $request->validate([
            'accion' => 'nullable|in:mover,retirar',
            'destino' => 'nullable|integer|exists:users,id',
            'adoptar_pti' => 'nullable|boolean',
        ]);
        $accion = $filtros['accion'] ?? null;

        // Destinos posibles: del mismo programa, con proceso iniciado, fuera de su grupo actual.
        $excluir = $companeros->pluck('id')->push($egresado->id);
        $candidatos = User::whereHas('seguimientoTitulacion')->where('programa_id', $egresado->programa_id)
            ->whereNotIn('id', $excluir)->with('seguimientoTitulacion:id,user_id,numero_expediente,grupo_id')
            ->orderBy('apellidos')->orderBy('name')->get(['id', 'name', 'apellidos', 'dni', 'programa_id']);

        $revision = null;
        if ($accion === 'retirar' || ($accion === 'mover' && filled($filtros['destino'] ?? null))) {
            $destinoUser = $accion === 'mover' ? User::find($filtros['destino']) : null;
            $destino = $destinoUser ? $this->seguimiento($destinoUser) : null;
            if ($destino && ($destino->id === $s->id || $companeros->contains('id', $destinoUser->id))) {
                return back()->with('error', 'Esa persona ya es de su mismo grupo: elija a alguien del grupo al que se incorpora.');
            }
            $miembrosDestino = $destino ? CambioGrupo::companeros($destino)->reject(fn ($u) => $u->id === $egresado->id)->push($destinoUser)->unique('id')->sortBy('apellidos')->values() : collect();
            $revision = [
                'destino' => $destino,
                'destinoUser' => $destinoUser,
                'miembrosDestino' => $miembrosDestino,
                'adoptarPti' => (bool) ($filtros['adoptar_pti'] ?? ($accion === 'mover')),
                'ptiDestino' => $destino?->pti,
                'actasCompartidas' => CambioGrupo::actasCompartidas($s, $companeros),
                'otroPrograma' => $destinoUser && $destinoUser->programa_id !== $egresado->programa_id,
            ];
        }

        return view('titulacion.grupo.cambiar', compact('egresado', 's', 'companeros', 'candidatos', 'accion', 'revision', 'filtros'));
    }

    public function aplicar(Request $request, User $egresado)
    {
        $s = $this->seguimiento($egresado);
        $data = $request->validate([
            'accion' => 'required|in:mover,retirar',
            'destino' => 'required_if:accion,mover|nullable|integer|exists:users,id',
            'adoptar_pti' => 'nullable|boolean',
            'retiro_numero' => 'nullable|string|max:60',
            'retiro_fecha' => 'nullable|date',
            'incorporacion_numero' => 'nullable|string|max:60',
            'incorporacion_fecha' => 'nullable|date',
            'motivo' => 'required|string|min:10|max:1000',
            'verificado' => 'accepted',
        ], [
            'verificado.accepted' => 'Confirme que verificó el cambio en el expediente antes de guardarlo.',
            'motivo.required' => 'Escriba el motivo o el sustento del cambio (por ejemplo, la RD que lo dispone).',
            'motivo.min' => 'Describa el motivo con un poco más de detalle.',
        ]);

        $destino = $data['accion'] === 'mover' ? $this->seguimiento(User::findOrFail($data['destino'])) : null;
        if ($destino && ($destino->id === $s->id || CambioGrupo::companeros($s)->contains('id', $destino->user_id))) {
            return back()->withInput()->with('error', 'Esa persona ya es de su mismo grupo.');
        }

        CambioGrupo::aplicar($s, $destino, (bool) ($data['adoptar_pti'] ?? false), $data, trim($data['motivo']), $request->user());

        return redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'datos'])
            ->with('success', $destino
                ? 'Cambio de grupo guardado: '.$egresado->name.' ahora integra el grupo de '.$destino->user?->name.' '.$destino->user?->apellidos.'.'
                : 'Cambio de grupo guardado: '.$egresado->name.' quedó sin grupo.');
    }

    private function seguimiento(User $u): SeguimientoTitulacion
    {
        $s = SeguimientoTitulacion::with(['user', 'pti.asesor', 'pti.asesorExterno', 'ptis', 'actas', 'resoluciones'])->where('user_id', $u->id)->first();
        abort_unless((bool) $s, 404, 'Esta persona no tiene proceso de titulación.');

        return $s;
    }
}
