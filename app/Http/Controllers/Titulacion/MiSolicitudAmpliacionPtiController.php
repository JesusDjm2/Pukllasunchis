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
    /** Fecha (Y-m-d) de la RD de aprobación del PTI más reciente, para sugerirla en el formulario. */
    private function fechaRdPti(?SeguimientoTitulacion $seguimiento): string
    {
        return (string) $seguimiento?->resoluciones->where('tipo', 'PTI')->filter(fn ($r) => $r->fecha)
            ->sortByDesc(fn ($r) => $r->fecha->format('Y-m-d'))->first()?->fecha?->format('Y-m-d');
    }

    /** Ampliaciones de plazo permitidas por PTI. */
    public const MAXIMO = 2;

    /**
     * Ampliaciones ya concedidas al grupo: las solicitudes atendidas (de cualquier integrante) o las
     * RD de ampliación registradas, lo que sea mayor (una RD puede haberse registrado sin solicitud).
     */
    private function ampliacionesPrevias(User $user, ?SeguimientoTitulacion $seguimiento): int
    {
        $ids = collect([$user->id]);
        if ($seguimiento) {
            $ids = $ids->merge(GrupoTitulacion::companeros($seguimiento)->pluck('id'));
        }
        $atendidas = Solicitud::deTipo(Solicitud::AMPLIACION_PTI)->where('estado', 'atendida')
            ->where(function ($q) use ($ids) {
                $q->whereIn('solicitante_id', $ids);
                foreach ($ids as $id) {
                    $q->orWhereJsonContains('integrantes', ['id' => $id])->orWhereJsonContains('integrantes', ['user_id' => $id]);
                }
            })
            ->count();
        $rds = $seguimiento
            ? $seguimiento->resoluciones->where('tipo', 'AmpliacionPti')->pluck('numero_rd')
                ->map(fn ($n) => GrupoTitulacion::claveRd($n))->filter()->unique()->count()
            : 0;

        return max($atendidas, $rds);
    }

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
        $previas = $this->ampliacionesPrevias($user, $seguimiento);

        // Preguntas del formulario oficial «Solicitud de extensión de plazo de investigación» (25/11/2026).
        $valores = [
            // Lo que corresponde según lo aprobado; el estudiante puede marcar 2.ª si tuvo una fuera del sistema.
            'vez' => (string) min($previas + 1, self::MAXIMO),
            'ciclo' => MiSolicitudPtiController::segunFicha($user)['ciclo'] ?? '',
            'domicilio' => '',
            'titulo' => (string) $pti?->titulo,
            'fecha_pti' => $this->fechaRdPti($seguimiento),
            'motivo' => '',
        ];

        if ($ultima?->estado === 'observada') {
            $valores = [
                'vez' => (string) ($ultima->datos['vez'] ?? $valores['vez']),
                'ciclo' => $ultima->ciclo ?: $valores['ciclo'],
                'domicilio' => $ultima->datos['domicilio'] ?? '',
                'titulo' => $ultima->titulo ?: $valores['titulo'],
                'fecha_pti' => $ultima->datos['fecha_aprobacion_pti'] ?? $valores['fecha_pti'],
                'motivo' => $ultima->datos['motivo'] ?? '',
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
            'previas' => $previas,
            'maximo' => self::MAXIMO,
            'programa' => MiSolicitudPtiController::programaDe($user) ?: $user->programa?->nombre,
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

        $vigencia = new VigenciaPti($seguimiento->pti);
        if ($vigencia->debeReiniciarProceso()) {
            return redirect()->route('mi-titulacion.ampliacion-pti')
                ->with('error', 'El plazo reglamentario para solicitar ampliación finalizó el '.$vigencia->limiteSolicitarAmpliacion()?->format('d/m/Y').' (máximo 2 días tras el vencimiento). Debes iniciar nuevamente el proceso desde cero con un nuevo PTI.');
        }

        $ultima = Solicitud::dePersona(Solicitud::AMPLIACION_PTI, $user)->first();
        if ($ultima && $ultima->estado === 'recibida') {
            return redirect()->route('mi-titulacion.ampliacion-pti')
                ->with('error', 'Ya tienes una solicitud de ampliación en curso: espera la respuesta de Secretaría General.');
        }

        // Máximo 2 ampliaciones por PTI. Una observada que se corrige no cuenta como envío nuevo.
        $previas = $this->ampliacionesPrevias($user, $seguimiento);
        if ($previas >= self::MAXIMO) {
            return redirect()->route('mi-titulacion.ampliacion-pti')
                ->with('error', 'Ya se aprobaron las '.self::MAXIMO.' ampliaciones permitidas para tu Plan de Trabajo de Investigación: no se puede solicitar otra.');
        }

        $data = $request->validate([
            'vez' => 'required|in:1,2',
            'ciclo' => 'required|in:'.implode(',', Solicitud::CICLOS),
            'domicilio' => 'required|string|min:5|max:300',
            'titulo' => 'required|string|max:1000',
            'fecha_pti' => 'required|date|before_or_equal:today',
            'motivo' => 'required|string|min:15|max:2000',
            'rd_pti' => 'required|file|mimes:pdf|max:10240',
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracion' => 'accepted',
        ], [
            'vez.required' => 'Indique si es la 1.ª o la 2.ª vez que solicita la ampliación.',
            'vez.in' => 'Indique si es la 1.ª o la 2.ª vez que solicita la ampliación.',
            'ciclo.required' => 'Indique su ciclo o si es egresado(a).',
            'domicilio.required' => 'Escriba su domicilio actual.',
            'titulo.required' => 'Escriba el título con el que se aprobó su investigación.',
            'fecha_pti.required' => 'Escriba la fecha de la RD de aprobación del PTI.',
            'fecha_pti.before_or_equal' => 'La fecha de aprobación del PTI no puede ser posterior a hoy.',
            'motivo.required' => 'Detalle las razones por las que solicita la extensión de plazo.',
            'motivo.min' => 'Las razones deben tener al menos 15 caracteres.',
            'rd_pti.required' => 'Adjunte la RD de aprobación del PTI y asignación de asesor (PDF).',
            'rd_pti.mimes' => 'La RD de aprobación del PTI debe estar en PDF.',
            'comprobante.required' => 'Adjunte el comprobante de pago por derecho de extensión de plazo (Cód. 11 · S/ 50.00).',
            'declaracion.accepted' => 'Debe aceptar la declaración de veracidad.',
        ]);

        if ((int) $data['vez'] <= $previas) {
            return back()->withInput()->withErrors(['vez' => 'Ya tienes '.$previas.' ampliación aprobada en el sistema: esta sería la '.($previas + 1).'.ª vez.']);
        }

        $vigencia = new VigenciaPti($seguimiento->pti);

        // Guardar archivos
        $subir = function (UploadedFile $f) {
            $ext = strtolower($f->getClientOriginalExtension());
            $nombre = (string) Str::uuid().'.'.$ext;
            $f->storeAs(Solicitud::CARPETA, $nombre, 'local');
            return 'local:'.$nombre;
        };

        $comprobanteRef = $subir($data['comprobante']);
        $archivoRef = $subir($data['rd_pti']); // Anexo 1: RD de aprobación del PTI

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
            // El formulario oficial no pide teléfono: va el de la ficha, para que Secretaría pueda contactar.
            'celulares' => (string) (($user->alumno ?? $user->alumnoB)?->numero ?: $user->telefono),
            'programa_estudios' => MiSolicitudPtiController::programaDe($user) ?: ($user->programa?->nombre ?? 'Educación'),
            'ciclo' => $data['ciclo'],
            'titulo' => trim($data['titulo']),
            'archivo' => $archivoRef,
            'comprobante' => $comprobanteRef,
            'declaracion' => true,
            'estado' => 'recibida',
            'origen' => 'sistema',
            'datos' => [
                'vez' => (int) $data['vez'],
                'motivo' => $data['motivo'],
                'domicilio' => trim($data['domicilio']),
                'fecha_aprobacion_pti' => $data['fecha_pti'],
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

    /**
     * Mismo filtro que los demás formularios de titulación (PTI, carta, originalidad): el personal ve la página
     * «solo para estudiantes» (con acceso a la bandeja si la atiende); el estudiante sin proceso va primero al PTI.
     */
    private function noCorresponde(User $user)
    {
        if (PanelDeUsuario::esPersonal($user)) {
            return view('titulacion.carta.solo-estudiantes', [
                'user' => $user, 'panel' => PanelDeUsuario::url($user), 'tramite' => 'ampliacion',
                'puedeRegistros' => $user->hasAnyRole(['super-admin', 'secretaria', 'gestor-titulacion']),
            ]);
        }
        if (! $user->seguimientoTitulacion()->exists()) {
            return redirect()->route('mi-titulacion.solicitud-pti')
                ->with('error', 'La extensión de plazo se pide cuando ya tienes tu proceso de titulación iniciado y tu PTI aprobado: primero solicita la aprobación de tu PTI.');
        }

        return null;
    }
}
