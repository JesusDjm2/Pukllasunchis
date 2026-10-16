<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TitulacionDatoReportado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Acciones del propio egresado/alumno sobre su proceso de titulación (la
 * tarjeta "Mi proceso de titulación" vive en su intranet: egresado o alumno FID).
 */
class MiTitulacionController extends Controller
{
    /** "¿Algún dato no coincide?" → aviso a la campanita de los gestores. */
    public function reportar(Request $request)
    {
        $user = $request->user();
        abort_unless($user->seguimientoTitulacion()->exists(), 403);

        $data = $request->validate([
            'mensaje' => 'required|string|min:10|max:1000',
        ], [], ['mensaje' => 'detalle']);

        // Gestores de titulación; si todavía no hay ninguno asignado, super-admin.
        $destinatarios = User::role('gestor-titulacion')->get();
        if ($destinatarios->isEmpty()) {
            $destinatarios = User::role('super-admin')->get();
        }

        try {
            Notification::send($destinatarios, new TitulacionDatoReportado($user, $data['mensaje']));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar reporte de titulación', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->with('titulacion_reporte_error', 'No se pudo enviar tu mensaje. Inténtalo de nuevo más tarde.');
        }

        // Clave propia (no 'success'): la intranet de alumno ya pinta 'success' en
        // otro lugar; así el aviso sale dentro de la tarjeta de titulación.
        return redirect()->to(url()->previous().'#titulacion')
            ->with('titulacion_reporte', 'Gracias, tu mensaje llegó a la coordinación de titulación. Revisarán tus datos.');
    }
}
