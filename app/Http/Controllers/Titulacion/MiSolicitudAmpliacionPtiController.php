<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\CartaAceptacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Notifications\TitulacionAmpliacionSolicitada;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\VigenciaPti;
use App\Support\PanelDeUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MiSolicitudAmpliacionPtiController extends Controller
{
    public function crear(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }

        $seguimiento = $this->seguimiento($user);
        $pti = $seguimiento?->pti;
        $ptiAprobado = (bool) $seguimiento?->avance()->hitos['pti_aprobado']['logrado'];
        $vigencia = new VigenciaPti($pti);

        $historial = Solicitud::dePersona(Solicitud::AMPLIACION_PTI, $user)->get();
        $ultima = $historial->first();

        $valores = [
            'motivo' => '',
            'meses_solicitados' => '6',
            'celular' => (string) (($user->alumno ?? $user->alumnoB)?->numero ?: $user->telefono),
        ];

        if ($ultima?->estado === 'observada') {
            $valores = [
                'motivo' => $ultima->datos['motivo'] ?? '',
                'meses_solicitados' => (string) ($ultima->datos['meses_solicitados'] ?? '6'),
                'celular' => $ultima->celulares ?: $valores['celular'],
            ];
        }

        $institucional = CartaAceptacion::esCorreoInstitucional($user->email);
        $layout = MiSolicitudPtiController::layout($user);

        return view('titulacion.solicitud-ampliacion.solicitar', [
            'user' => $user,
            'seguimiento' => $seguimiento,
            'pti' => $pti,
            'vigencia' => $vigencia,
            'ptiAprobado' => $ptiAprobado,
            'institucional' => $institucional,
            'ultima' => $ultima,
            'historial' => $historial,
            'valores' => $valores,
            'companeros' => $seguimiento ? GrupoTitulacion::companeros($seguimiento) : collect(),
            'layout' => $layout,
            'volver' => PanelDeUsuario::url($user),
        ]);
    }

    public function enviar(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }

        $seguimiento = $this->seguimiento($user);
        if (! $seguimiento?->avance()->hitos['pti_aprobado']['logrado']) {
            return redirect()->route('mi-titulacion.ampliacion-pti')
                ->with('error', 'Solo puedes solicitar ampliación si tu Plan de Trabajo de Investigación ya fue aprobado (R.D.).');
        }

        $ultima = Solicitud::dePersona(Solicitud::AMPLIACION_PTI, $user)->first();
        if ($ultima && $ultima->estado === 'recibida') {
            return redirect()->route('mi-titulacion.ampliacion-pti')
                ->with('error', 'Ya tienes una solicitud de ampliación en curso: espera la respuesta de Secretaría General.');
        }

        $data = $request->validate([
            'motivo' => 'required|string|min:15|max:2000',
            'meses_solicitados' => 'required|in:6,12',
            'celular' => ['required', 'regex:/^\+?[\d\s]{9,15}$/'],
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'declaracion' => 'accepted',
        ], [
            'motivo.required' => 'Escriba los motivos o justificación académica de su ampliación de plazo.',
            'motivo.min' => 'La justificación debe contener al menos 15 caracteres.',
            'meses_solicitados.required' => 'Seleccione el tiempo de prórroga solicitado.',
            'comprobante.required' => 'Adjunte el comprobante de pago de Caja Cusco (Cód. 11 - S/ 50.00).',
            'declaracion.accepted' => 'Debe aceptar la declaración de veracidad.',
        ]);

        $vigencia = new VigenciaPti($seguimiento->pti);

        // Guardar archivos
        $subir = function (UploadedFile $f) {
            $ext = strtolower($f->getClientOriginalExtension());
            $nombre = (string) Str::uuid().'.'.$ext;
            $f->storeAs(Solicitud::CARPETA, $nombre, 'local');
            return 'local:'.$nombre;
        };

        $comprobanteRef = $subir($data['comprobante']);
        $archivoRef = isset($data['archivo']) && $data['archivo'] ? $subir($data['archivo']) : null;

        $integrantes = [['id' => $user->id, 'nombre' => trim($user->name.' '.$user->apellidos), 'dni' => $user->dni]];
        $companeros = GrupoTitulacion::companeros($seguimiento);
        foreach ($companeros as $c) {
            $integrantes[] = ['id' => $c->id, 'nombre' => trim($c->name.' '.$c->apellidos), 'dni' => $c->dni];
        }

        $solicitud = Solicitud::create([
            'tipo' => Solicitud::AMPLIACION_PTI,
            'solicitante_id' => $user->id,
            'seguimiento_id' => $seguimiento->id,
            'correo' => $user->email,
            'integrantes' => $integrantes,
            'celulares' => $data['celular'],
            'programa_estudios' => $user->programa?->nombre ?? 'Educación',
            'ciclo' => 'Egresado',
            'titulo' => $seguimiento->pti?->titulo ?: 'Plan de Trabajo de Investigación',
            'archivo' => $archivoRef,
            'comprobante' => $comprobanteRef,
            'declaracion' => true,
            'estado' => 'recibida',
            'origen' => 'sistema',
            'datos' => [
                'motivo' => $data['motivo'],
                'meses_solicitados' => (int) $data['meses_solicitados'],
                'fecha_vencimiento_actual' => $vigencia->vencimiento?->format('Y-m-d'),
                'rd_pti_actual' => $seguimiento->pti?->rd_pti,
            ],
        ]);

        try {
            $destinatarios = AreasTitulacion::destinatarios([AreasTitulacion::SECRETARIA]);
            Notification::send($destinatarios, new TitulacionAmpliacionSolicitada($solicitud, $user));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la solicitud de ampliación de PTI a Secretaría', [
                'solicitud' => $solicitud->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('mi-titulacion.ampliacion-pti')
            ->with('success', 'Tu solicitud de ampliación de plazo fue enviada con éxito. Secretaría General evaluará tu justificativo y pago; recibirás aquí y en tu correo institucional la R.D. de prórroga.');
    }

    public function archivo(Request $request, Solicitud $solicitud, string $cual, int $n)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria']) || $solicitud->incluye($user), 403);

        $nombre = Solicitud::locales($solicitud->valorArchivo($cual))[$n] ?? null;
        abort_unless($nombre && Storage::disk('local')->exists(Solicitud::CARPETA.'/'.$nombre), 404);

        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $tipos = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return response()->file(Storage::disk('local')->path(Solicitud::CARPETA.'/'.$nombre), [
            'Content-Type' => $tipos[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => (in_array($ext, ['doc', 'docx'], true) ? 'attachment' : 'inline').'; filename="'.basename($nombre).'"',
        ]);
    }

    private function seguimiento(User $user): ?SeguimientoTitulacion
    {
        return $user->seguimientoTitulacion()
            ->with(['pti', 'ptis', 'resoluciones', 'actas', 'participantes'])
            ->first();
    }

    private function noCorresponde(User $user)
    {
        if (! $user->hasAnyRole(['alumno', 'alumnoB', 'egresado'])) {
            return redirect(PanelDeUsuario::url($user));
        }
        return null;
    }
}
