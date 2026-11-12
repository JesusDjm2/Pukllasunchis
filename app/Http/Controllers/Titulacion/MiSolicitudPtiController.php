<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Notifications\TitulacionPtiSolicitada;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\ReconocerPersonas;
use App\Support\PanelDeUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Solicitud de aprobación del PTI y asignación de asesor desde la intranet del estudiante: es el PRIMER
 * paso del proceso, así que se pide antes de tener proceso de titulación. Mismas preguntas que el
 * formulario de Google (que sigue abierto al público por ahora). Llega a la bandeja de Investigación
 * (SolicitudPtiController), que la atiende iniciando el proceso con estos datos, o la observa.
 */
class MiSolicitudPtiController extends Controller
{
    public function crear(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }
        $ultima = Solicitud::ptiDe($user)->first();
        $valores = $this->valoresIniciales($user);
        // Al corregir una observada, se parte de lo que ya escribió.
        if ($ultima?->estado === 'observada') {
            $nombres = $ultima->nombresIntegrantes();
            $valores = array_merge($valores, [
                'integrante2' => $nombres[1] ?? '', 'integrante3' => $nombres[2] ?? '',
                'celulares' => $ultima->celulares, 'titulo' => $ultima->titulo,
                'area' => $ultima->datos['area'] ?? '', 'linea' => $ultima->datos['linea'] ?? '',
            ], array_filter(['programa_estudios' => $ultima->programa_estudios, 'ciclo' => $ultima->ciclo]), $this->fijos($user));
        }

        return view('titulacion.solicitud-pti.solicitar', [
            'user' => $user,
            'ultima' => $ultima,
            'valores' => $valores,
            'fijos' => $this->fijos($user),
            'layout' => self::layout($user),
            'alumno' => $user->alumno ?? $user->alumnoB,
            'volver' => PanelDeUsuario::url($user),
        ]);
    }

    public function enviar(Request $request)
    {
        $user = $request->user();
        if ($otra = $this->noCorresponde($user)) {
            return $otra;
        }
        $ultima = Solicitud::ptiDe($user)->first();
        if ($ultima && $ultima->estado === 'recibida') {
            return redirect()->route('mi-titulacion.solicitud-pti')->with('error', 'Ya tienes una solicitud en revisión: espera la respuesta del Área de Investigación.');
        }

        // Lo que se sabe de la ficha no lo decide el formulario.
        $request->merge($this->fijos($user));
        $data = $request->validate([
            'integrante2' => 'nullable|string|min:5|max:200',
            'integrante3' => 'nullable|string|min:5|max:200',
            'celulares' => 'required|string|min:9|max:200',
            'programa_estudios' => 'required|in:'.implode(',', Solicitud::PROGRAMAS_ESTUDIOS),
            'ciclo' => 'required|in:'.implode(',', Solicitud::CICLOS),
            'area' => 'required|in:'.implode(',', array_keys(Solicitud::AREAS)),
            'linea' => 'required|in:'.implode(',', array_keys(Solicitud::LINEAS)),
            'titulo' => 'required|string|min:10|max:1000',
            'pti' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'comprobantes' => 'required|array|min:1|max:5',
            'comprobantes.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracion' => 'accepted',
        ], [
            'linea.in' => 'Elige una línea de investigación.',
            'pti.required' => 'Adjunta tu Plan de Trabajo de Investigación.',
            'pti.mimes' => 'El PTI debe ser PDF o Word.',
            'pti.max' => 'El PTI no debe pesar más de 10 MB.',
            'comprobantes.required' => 'Adjunta el comprobante de pago emitido por la EESPP.',
            'comprobantes.max' => 'Puedes adjuntar hasta 5 archivos de comprobante.',
            'comprobantes.*.mimes' => 'El comprobante debe ser PDF o imagen (JPG o PNG).',
            'comprobantes.*.max' => 'Cada comprobante debe pesar hasta 10 MB.',
            'declaracion.accepted' => 'Debes aceptar la declaración de veracidad.',
        ], [
            'integrante2' => 'integrante 2', 'integrante3' => 'integrante 3', 'celulares' => 'números de contacto',
            'programa_estudios' => 'programa de estudios', 'ciclo' => 'ciclo o programa', 'area' => 'área de investigación',
            'linea' => 'línea de investigación', 'titulo' => 'título del PTI',
        ]);
        if (! str_starts_with($data['linea'], $data['area'].'-')) {
            return back()->withInput()->withErrors(['linea' => 'La línea elegida no pertenece al área de investigación elegida.']);
        }

        // Integrantes: quien envía (de su ficha) y los que escribió, reconocidos por nombre cuando se puede.
        $personas = new ReconocerPersonas;
        $integrantes = [['nombre' => self::nombre($user), 'user_id' => $user->id]];
        foreach (['integrante2', 'integrante3'] as $k) {
            if (filled($data[$k] ?? null)) {
                $nombre = trim(preg_replace('/\s+/', ' ', $data[$k]));
                [$u] = $personas->porNombre($nombre);
                $integrantes[] = ['nombre' => $nombre, 'user_id' => $u && $u->id !== $user->id ? $u->id : null];
            }
        }

        $archivo = $this->guardar($request->file('pti'));
        $comprobantes = collect($request->file('comprobantes'))->map(fn ($f) => $this->guardar($f))->implode(',');

        $solicitud = Solicitud::create([
            'tipo' => Solicitud::PTI, 'solicitante_id' => $user->id, 'correo' => (string) $user->email,
            'integrantes' => $integrantes, 'celulares' => trim($data['celulares']),
            'programa_estudios' => $data['programa_estudios'], 'ciclo' => $data['ciclo'],
            'titulo' => trim($data['titulo'], " \"“”"),
            'datos' => ['area' => $data['area'], 'linea' => $data['linea']],
            'archivo' => $archivo, 'comprobante' => $comprobantes, 'declaracion' => true,
            'estado' => 'recibida', 'origen' => 'sistema',
        ]);
        $this->avisarInvestigacion($solicitud, $user);

        return redirect()->route('mi-titulacion.solicitud-pti')
            ->with('success', 'Tu solicitud fue enviada. El Área de Investigación revisará tu PTI y tu comprobante; verás aquí y en tu intranet cuando se inicie tu proceso.');
    }

    /** Archivos de una solicitud de titulación (documento, comprobante o reporte): personal de Titulación o quien figura en ella. */
    public function archivo(Request $request, Solicitud $solicitud, string $cual, int $n)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria']) || $solicitud->incluye($user), 403);
        $nombre = Solicitud::locales($solicitud->valorArchivo($cual))[$n] ?? null;
        abort_unless($nombre && Storage::disk('local')->exists(Solicitud::CARPETA.'/'.$nombre), 404);
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $documento = $solicitud->tipo === Solicitud::PTI ? 'PTI' : 'Documento';
        $titulo = (['archivo' => $documento, 'comprobante' => 'Comprobante', 'reporte' => 'Reporte de originalidad'][$cual] ?? 'Archivo').' - '.Str::ascii(Str::limit(self::nombre($solicitud->solicitante ?? $user), 50, '')).'.'.$ext;
        $tipos = ['pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        return response()->file(Storage::disk('local')->path(Solicitud::CARPETA.'/'.$nombre), [
            'Content-Type' => $tipos[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => (in_array($ext, ['doc', 'docx'], true) ? 'attachment' : 'inline').'; filename="'.str_replace('"', '', $titulo).'"',
        ]);
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    /**
     * Quien ya tiene PTI vuelve a su intranet (ya está en su proceso). Quien no puede iniciar todavía ve el
     * formulario bloqueado con el motivo; el personal, una página que le explica que es para estudiantes.
     */
    private function noCorresponde(User $user)
    {
        if ($user->seguimientoTitulacion()->whereHas('pti')->exists()) {
            return redirect(PanelDeUsuario::url($user).'#titulacion')->with('info', 'Tu proceso de titulación ya está iniciado: tu PTI está registrado.');
        }
        if (PanelDeUsuario::esPersonal($user)) {
            return view('titulacion.carta.solo-estudiantes', [
                'user' => $user, 'panel' => PanelDeUsuario::url($user),
                'puedeRegistros' => $user->hasAnyRole(['super-admin', 'secretaria', 'gestor-titulacion']),
            ]);
        }
        if ($motivo = InicioProcesoController::motivoNoPuede($user)) {
            return view('titulacion.solicitud-pti.solicitar', [
                'user' => $user, 'ultima' => null, 'valores' => [], 'fijos' => [], 'bloqueo' => $motivo,
                'layout' => self::layout($user), 'alumno' => $user->alumno ?? $user->alumnoB, 'volver' => PanelDeUsuario::url($user),
            ]);
        }

        return null;
    }

    /** Lo que se sabe de la ficha: programa de estudios y ciclo o programa (null si no se sabe). */
    private function fijos(User $user): array
    {
        $user->loadMissing(['programa', 'ciclo.programa']);
        $programa = self::programaDe($user);
        $orden = $user->ciclo?->ordenCiclo();
        $ciclo = match (true) {
            InicioProcesoController::esPpd($user) => 'PPD',
            $user->condicion === 'Egresado' || $orden >= 999 => 'Egresado',
            $orden === 9 => 'IX Ciclo',
            $orden === 10 => 'X Ciclo',
            default => null,
        };

        return array_filter(['programa_estudios' => $programa, 'ciclo' => $ciclo]);
    }

    private function valoresIniciales(User $user): array
    {
        $ficha = $user->alumno ?? $user->alumnoB;

        return array_merge([
            'integrante2' => '', 'integrante3' => '', 'titulo' => '', 'area' => '', 'linea' => '',
            'celulares' => (string) ($ficha?->numero ?: $user->telefono),
        ], $this->fijos($user));
    }

    private function guardar(UploadedFile $f): string
    {
        $ext = strtolower($f->getClientOriginalExtension() ?: 'pdf');
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $nombre = Str::uuid().'.'.$ext;
        $f->storeAs(Solicitud::CARPETA, $nombre, 'local');

        return 'local:'.$nombre;
    }

    /**
     * Aviso SOLO en la campanita (sin correo) de Secretaría y de Investigación (la atiende Investigación; Secretaría
     * sigue todo el proceso y luego emite la RD). Abre la bandeja con la solicitud resaltada.
     */
    private function avisarInvestigacion(Solicitud $s, User $solicitante): void
    {
        $destinatarios = AreasTitulacion::destinatarios([AreasTitulacion::SECRETARIA, AreasTitulacion::INVESTIGACION]);
        try {
            Notification::send($destinatarios, new TitulacionPtiSolicitada($s, $solicitante));
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la solicitud de PTI', ['solicitud' => $s->id, 'error' => $e->getMessage()]);
        }
    }

    /** Programa de estudios según la ficha (null si la ficha no lo permite saber). */
    public static function programaDe(User $u): ?string
    {
        $p = $u->programa;

        return $p ? match (true) {
            $p->esInicial() => 'Educación Inicial',
            (bool) $p->eib => 'Educación Primaria Intercultural Bilingüe',
            $p->esPrimaria() => 'Educación Primaria',
            default => null,
        } : null;
    }

    public static function nombre(User $u): string
    {
        return trim(mb_convert_case(trim($u->apellidos), MB_CASE_TITLE).', '.mb_convert_case(trim($u->name), MB_CASE_TITLE), ', ');
    }

    /** Layout según quién es: PPD (alumno o egresado), FID activo o egresado FID (página simple). */
    public static function layout(User $user): string
    {
        return match (true) {
            (bool) $user->alumnoB => 'layouts.profesionalizacion',
            $user->condicion !== 'Egresado' && (bool) $user->alumno => 'layouts.alumno',
            default => 'titulacion.carta._simple',
        };
    }
}
