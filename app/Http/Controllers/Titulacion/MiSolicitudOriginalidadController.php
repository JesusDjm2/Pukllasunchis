<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Notifications\TitulacionOriginalidadSolicitada;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\GrupoTitulacion;
use App\Support\PanelDeUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Solicitud de revisión de originalidad (Turnitin) del TI o de la Tesis desde la intranet del tesista. Mismas
 * preguntas que el formulario de Google; los tesistas son el grupo que el sistema conoce (no se escriben). Llega a
 * la bandeja de Investigación (SolicitudOriginalidadController), que responde con el reporte y el % de similitud.
 * Se puede pedir otra revisión cuando la anterior ya tiene respuesta (por ejemplo, si no salió apta).
 */
class MiSolicitudOriginalidadController extends Controller
{
    public function crear(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }
        $seguimiento = $this->seguimiento($user);
        $historial = Solicitud::dePersona(Solicitud::ORIGINALIDAD, $user)->get();
        $ultima = $historial->first();
        $etapa = $this->etapaSugerida($seguimiento);
        $valores = [
            'etapa' => $etapa,
            'titulo' => $this->tituloDe($seguimiento, $etapa),
            'celulares' => (string) (($user->alumno ?? $user->alumnoB)?->numero ?: $user->telefono),
            'domicilio' => '', 'fecha_pago' => '',
        ];
        if ($ultima?->estado === 'observada') {
            $valores = array_merge($valores, ['etapa' => $ultima->etapa() ?? $etapa, 'titulo' => $ultima->titulo, 'celulares' => $ultima->celulares,
                'domicilio' => $ultima->datos['domicilio'] ?? '', 'fecha_pago' => $ultima->datos['fecha_pago'] ?? '']);
        }

        return view('titulacion.solicitud-originalidad.solicitar', [
            'user' => $user, 'seguimiento' => $seguimiento, 'ultima' => $ultima, 'historial' => $historial, 'valores' => $valores,
            'tesistas' => $this->tesistas($user, $seguimiento),
            'titulos' => ['ti' => $this->tituloDe($seguimiento, 'ti'), 'tesis' => $this->tituloDe($seguimiento, 'tesis')],
            'layout' => MiSolicitudPtiController::layout($user), 'alumno' => $user->alumno ?? $user->alumnoB, 'volver' => PanelDeUsuario::url($user),
        ]);
    }

    public function enviar(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }
        $seguimiento = $this->seguimiento($user);
        $ultima = Solicitud::dePersona(Solicitud::ORIGINALIDAD, $user)->first();
        if ($ultima && $ultima->estado === 'recibida') {
            return redirect()->route('mi-titulacion.originalidad')->with('error', 'Ya tienes una revisión en curso: espera el reporte del Área de Investigación.');
        }

        $data = $request->validate([
            'etapa' => 'required|in:'.implode(',', array_keys(Solicitud::ETAPAS)),
            'titulo' => 'required|string|min:10|max:1000',
            'celulares' => 'required|string|min:9|max:200',
            'domicilio' => 'nullable|string|max:300',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'documento' => 'required|file|mimes:doc,docx|max:10240',
            'comprobantes' => 'required|array|min:1|max:5',
            'comprobantes.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracion' => 'accepted',
        ], [
            'documento.required' => 'Adjunta la versión final de tu documento en Word.',
            'documento.mimes' => 'El documento debe estar en Word (.doc o .docx): Turnitin lo revisa en ese formato.',
            'documento.max' => 'El documento no debe pesar más de 10 MB.',
            'comprobantes.required' => 'Adjunta el comprobante de pago emitido por la EESPP.',
            'comprobantes.*.mimes' => 'El comprobante debe ser PDF o imagen (JPG o PNG).',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], ['etapa' => 'documento a revisar', 'titulo' => 'título del documento', 'celulares' => 'números de celular', 'fecha_pago' => 'fecha de pago']);

        $tesistas = $this->tesistas($user, $seguimiento);
        $guardar = function (UploadedFile $f) {
            $ext = strtolower($f->getClientOriginalExtension() ?: 'pdf');
            $nombre = Str::uuid().'.'.($ext === 'jpeg' ? 'jpg' : $ext);
            $f->storeAs(Solicitud::CARPETA, $nombre, 'local');

            return 'local:'.$nombre;
        };
        $programa = MiSolicitudPtiController::programaDe($user);

        $solicitud = Solicitud::create([
            'tipo' => Solicitud::ORIGINALIDAD, 'solicitante_id' => $user->id, 'seguimiento_id' => $seguimiento->id,
            'correo' => (string) $user->email,
            'integrantes' => $tesistas->map(fn ($u) => ['nombre' => MiSolicitudPtiController::nombre($u), 'user_id' => $u->id])->values()->all(),
            'celulares' => trim($data['celulares']), 'programa_estudios' => (string) $programa,
            'ciclo' => InicioProcesoController::esPpd($user) ? 'PPD' : ($user->condicion === 'Egresado' ? 'Egresado' : (string) $user->ciclo?->nombre),
            'titulo' => trim($data['titulo'], " \"“”"),
            'datos' => array_filter(['etapa' => $data['etapa'], 'modalidad' => InicioProcesoController::esPpd($user) ? 'PPD' : 'FID',
                'domicilio' => filled($data['domicilio'] ?? null) ? trim($data['domicilio']) : null, 'fecha_pago' => $data['fecha_pago']]),
            'archivo' => $guardar($request->file('documento')),
            'comprobante' => collect($request->file('comprobantes'))->map($guardar)->implode(','),
            'declaracion' => true, 'estado' => 'recibida', 'origen' => 'sistema',
        ]);

        try {
            Notification::send(AreasTitulacion::destinatarios([AreasTitulacion::SECRETARIA, AreasTitulacion::INVESTIGACION]), new TitulacionOriginalidadSolicitada($solicitud, $user));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la solicitud de originalidad', ['solicitud' => $solicitud->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('mi-titulacion.originalidad')->with('success', 'Tu solicitud fue enviada. Recibirás el reporte de originalidad aquí y en tu campanita en un plazo de hasta '
            .Solicitud::DIAS_ORIGINALIDAD.' días hábiles (a más tardar el '.$solicitud->fechaLimite()->format('d/m/Y').').');
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    /** El personal ve la página para estudiantes; quien no tiene proceso todavía, un aviso de que primero va el PTI. */
    private function noCorresponde(User $user)
    {
        // Apagado por ahora (config titulacion.originalidad_alumnos): los alumnos siguen con el formulario de Google.
        if (! config('titulacion.originalidad_alumnos')) {
            return redirect(PanelDeUsuario::url($user).'#titulacion');
        }
        if (PanelDeUsuario::esPersonal($user)) {
            return view('titulacion.carta.solo-estudiantes', [
                'user' => $user, 'panel' => PanelDeUsuario::url($user),
                'puedeRegistros' => $user->hasAnyRole(['super-admin', 'secretaria', 'gestor-titulacion']),
            ]);
        }
        if (! $user->seguimientoTitulacion()->exists()) {
            return redirect()->route('mi-titulacion.solicitud-pti')->with('error', 'La revisión de originalidad se pide cuando ya tienes tu proceso de titulación iniciado: primero solicita la aprobación de tu PTI.');
        }

        return null;
    }

    private function seguimiento(User $user): SeguimientoTitulacion
    {
        return $user->seguimientoTitulacion()->with(['pti', 'tesis', 'actas', 'resoluciones', 'cartasAceptacion'])->firstOrFail();
    }

    /** Quien pide y sus compañeros de grupo según el sistema. */
    private function tesistas(User $user, SeguimientoTitulacion $s)
    {
        return collect([$user])->concat(GrupoTitulacion::companeros($s))->unique('id')->values();
    }

    /** Con el TI aprobado, lo que sigue es la Tesis. */
    private function etapaSugerida(SeguimientoTitulacion $s): string
    {
        return $s->avance()->hitos['ti_aprobado']['logrado'] ? 'tesis' : 'ti';
    }

    private function tituloDe(SeguimientoTitulacion $s, string $etapa): string
    {
        return (string) ($etapa === 'tesis' ? ($s->tesis?->titulo ?: $s->pti?->titulo) : $s->pti?->titulo);
    }
}
