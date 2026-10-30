<?php

namespace App\Http\Controllers\Titulacion;

use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\ReglasProceso;
use Illuminate\Http\Request;

/**
 * Paso «RD de aprobación de la Tesis» (30/10): el estudiante paga el trámite de aprobación de
 * sustentación → se genera la RD → Secretaría la envía por correo (CorreoRdController).
 * El pago puede aplicarse también al grupo.
 */
class EtapaTesisController extends TitulacionController
{
    /** Pago del trámite de aprobación de sustentación (fecha y N° de recibo). Todo vacío = sin pago. */
    public function tramite(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tramite_sust_pagado_en' => 'nullable|date',
            'tramite_sust_recibo' => 'nullable|string|max:100',
        ]);
        $datos = filled($data['tramite_sust_pagado_en'] ?? null) || filled($data['tramite_sust_recibo'] ?? null)
            ? ['tramite_sust_pagado_en' => $data['tramite_sust_pagado_en'] ?? now()->toDateString(), 'tramite_sust_recibo' => $data['tramite_sust_recibo'] ?? null]
            : ['tramite_sust_pagado_en' => null, 'tramite_sust_recibo' => null];

        $seguimiento = SeguimientoTitulacion::with(['pti', 'ptis', 'tesis', 'actas', 'resoluciones', 'participantes'])
            ->where('user_id', $egresado->id)->firstOrFail();
        if ($datos['tramite_sust_pagado_en'] && ($falta = $this->faltaTramite($seguimiento))) {
            return $this->volverAlAcordeon($egresado->id, 'Todavía no se puede registrar el pago del trámite: '.$falta.'.', 'proceso', 'error');
        }
        Tesis::updateOrCreate(['seguimiento_id' => $seguimiento->id], $datos);

        [$aplicados, $omitidos] = [0, []];
        foreach ($this->companerosDestino($seguimiento, $request) as $otro) {
            if ($datos['tramite_sust_pagado_en'] && ($falta = $this->faltaTramite($otro))) {
                $omitidos[] = $otro->user?->apellidos.' ('.$falta.')';
                continue;
            }
            Tesis::updateOrCreate(['seguimiento_id' => $otro->id], $datos);
            $aplicados++;
        }

        $texto = $datos['tramite_sust_pagado_en'] ? 'Pago del trámite registrado' : 'Pago del trámite quitado';

        return $this->volverAlAcordeon($egresado->id, $texto.$this->textoGrupo($aplicados, $omitidos), 'proceso', $omitidos ? 'warning' : 'success');
    }

    /** Lo que falta para registrar el pago (el enlace de la Tesis aprobado), o null. */
    private function faltaTramite(SeguimientoTitulacion $seguimiento): ?string
    {
        $falta = ReglasProceso::falta($seguimiento->avance(), ReglasProceso::REQUISITO_CAMPO['tramite_sust_pagado_en']);

        return $falta ? 'primero debe estar registrado «'.$falta.'»' : null;
    }
}
