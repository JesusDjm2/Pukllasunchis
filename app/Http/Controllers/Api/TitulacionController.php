<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EgresadoIntranetController;
use App\Http\Controllers\Titulacion\TitulacionController as WebTitulacionController;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Services\Titulacion\AvanceTitulacion;
use App\Services\Titulacion\GuiaTramites;
use App\Services\Titulacion\VigenciaPti;
use App\Support\PanelDeUsuario;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * "Mi proceso de titulación" para la app: lo mismo que la web muestra en
 * titulacion/_avance_alumno (con proceso) o titulacion/_guia_inicio (sin
 * proceso, pero ya puede iniciarlo), calculado con los mismos servicios.
 *
 * Solo lectura. Las solicitudes (PTI, carta, originalidad) llevan archivos y
 * se envían desde la web: aquí van sus enlaces y el estado de la última.
 */
class TitulacionController extends Controller
{
    /** Ruta web del formulario → formulario nativo de la app (MiTitulacionSolicitudController). */
    private const FORMULARIOS_APP = [
        'mi-titulacion.solicitud-pti' => 'solicitud-pti',
        'mi-titulacion.carta' => 'carta',
        'mi-titulacion.originalidad' => 'originalidad',
    ];

    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $esEgresado = $user->condicion === 'Egresado';

        $seguimiento = SeguimientoTitulacion::where('user_id', $user->id)
            ->with(['pti.asesor', 'pti.asesorExterno', 'ptis', 'tesis.asesor', 'tesis.asesorExterno', 'actas', 'resoluciones', 'cartasAceptacion'])
            ->first();

        // Un egresado siempre puede iniciar; un alumno activo, según su ciclo (FID desde el IX).
        $puedeIniciar = ! $seguimiento && ($esEgresado || WebTitulacionController::puedeIniciar($user));

        $base = [
            'es_egresado' => $esEgresado,
            'promocion' => $esEgresado ? EgresadoIntranetController::promocion($user) : null,
            'disponible' => (bool) $seguimiento || $puedeIniciar,
            'tiene_proceso' => (bool) $seguimiento,
            'puede_iniciar' => $puedeIniciar,
            'enlace_web' => PanelDeUsuario::url($user),
        ];

        if (! $seguimiento) {
            return response()->json(['data' => $base + [
                'estado' => null,
                'siguiente_paso' => null,
                'etapas' => $puedeIniciar ? $this->etapas($this->avanceVacio()) : [],
                'vigencia_pti' => null,
                'solicitud_pti' => $puedeIniciar ? $this->solicitudPti($user) : null,
                'carta' => null,
                'originalidad' => null,
                'tramites' => $puedeIniciar ? $this->tramites(GuiaTramites::bloque('pti')) : null,
            ]]);
        }

        $avance = $seguimiento->avance();
        $estadoClave = $avance->estadoClave();
        $siguiente = $avance->siguientePaso();

        return response()->json(['data' => $base + [
            'estado' => [
                'clave' => $estadoClave,
                'nombre' => $avance->estadoNombre(),
                'color' => AvanceTitulacion::badge($estadoClave),
            ],
            'siguiente_paso' => $siguiente ? [
                'clave' => $siguiente['clave'],
                'etapa' => $siguiente['etapa'],
                'etapa_titulo' => AvanceTitulacion::ETAPAS[$siguiente['etapa']] ?? null,
                'label' => $siguiente['label'],
                'falta' => $siguiente['falta'],
            ] : null,
            'etapas' => $this->etapas($avance),
            'vigencia_pti' => $this->vigencia($seguimiento, $estadoClave),
            'solicitud_pti' => null,
            'carta' => $this->carta($seguimiento, $avance),
            'originalidad' => $this->originalidad($avance, $seguimiento),
            'tramites' => $this->tramites(GuiaTramites::actual($avance)),
        ]]);
    }

    /** Línea de tiempo PTI → TI → Tesis con la situación de cada hito. */
    private function etapas(AvanceTitulacion $avance): array
    {
        return collect($avance->lineaDeTiempo())->map(fn ($etapa, $clave) => [
            'clave' => $clave,
            'titulo' => $etapa['titulo'],
            'estado' => $etapa['estado'],
            'resumen' => $etapa['resumen'],
            'hitos' => array_map(fn ($h) => [
                'clave' => $h['clave'],
                'label' => $h['label'],
                'situacion' => $h['situacion'],
                'fecha' => $this->fecha($h['fecha']),
                'detalle' => $h['detalle'],
                'falta' => $h['falta'],
                'enlace' => $h['enlace'] ?? null,
            ], $etapa['hitos']),
        ])->values()->all();
    }

    /** Las etapas con sus pasos, sin nada logrado (guía del que aún no inicia). */
    private function avanceVacio(): AvanceTitulacion
    {
        $vacio = new SeguimientoTitulacion();
        foreach (['ptis' => collect(), 'resoluciones' => collect(), 'actas' => collect(), 'cartasAceptacion' => collect(), 'participantes' => collect(), 'pti' => null, 'tesis' => null] as $rel => $valor) {
            $vacio->setRelation($rel, $valor);
        }

        return new AvanceTitulacion($vacio);
    }

    /** Vigencia del PTI: importa mientras falte llegar a la Tesis (mismo criterio que la web). */
    private function vigencia(SeguimientoTitulacion $seguimiento, string $estadoClave): ?array
    {
        $vigencia = new VigenciaPti($seguimiento->pti);
        if (! $vigencia->conocida() || ! in_array($estadoClave, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado', 'bachiller'], true)) {
            return null;
        }

        $extension = $seguimiento->tesis?->fecha_extension;
        $conExtension = $extension && $extension->gt($vigencia->vencimiento);
        $venceEl = $conExtension ? $extension : $vigencia->vencimiento;
        $vencido = $venceEl->copy()->endOfDay()->isPast() || $seguimiento->pti?->estado === 'vencido';

        return [
            'vence_el' => $this->fecha($venceEl),
            'vencido' => $vencido,
            'con_extension' => (bool) $conExtension,
            'meses_restantes' => $vencido ? 0 : max(0, (int) now()->startOfDay()->diffInMonths($venceEl, false)),
            'urgente' => ! $vencido && now()->startOfDay()->diffInMonths($venceEl, false) <= VigenciaPti::MESES_AVISO_URGENTE,
        ];
    }

    /** Última solicitud de aprobación del PTI (primer paso del que aún no tiene proceso). */
    private function solicitudPti(User $user): array
    {
        $s = Solicitud::ptiDe($user)->first();

        return [
            'estado' => $s?->estado,
            'fecha' => $this->fecha($s?->created_at),
            'mensaje' => match ($s?->estado) {
                'recibida' => 'El Área de Investigación recibió tu solicitud del '.$s->created_at->format('d/m/Y').' y la está revisando.',
                'observada' => 'El Área de Investigación observó tu solicitud: corrígela y envíala de nuevo.',
                'atendida' => 'Tu solicitud fue atendida: pronto verás aquí tu proceso de titulación.',
                default => 'Inscribe tu Plan de Trabajo de Investigación (PTI): envía tu solicitud de aprobación con tu PTI y el comprobante de pago.',
            },
            'boton' => match ($s?->estado) {
                'recibida', 'atendida' => 'Ver mi solicitud',
                'observada' => 'Corregir y enviar',
                default => 'Solicitar aprobación del PTI',
            },
            'url' => $this->ruta('mi-titulacion.solicitud-pti'),
            'formulario_app' => 'solicitud-pti',
        ];
    }

    /** Carta de aceptación de la IE: con el PTI aprobado (salvo que ya se dé por cumplida). */
    private function carta(SeguimientoTitulacion $seguimiento, AvanceTitulacion $avance): ?array
    {
        $ultima = $seguimiento->cartasAceptacion->first();
        if (! $avance->hitos['pti_aprobado']['logrado'] || (! $ultima && $avance->hitos['pti_carta']['logrado'])) {
            return null;
        }

        [$mensaje, $boton] = match ($ultima?->estado) {
            'emitida' => ['Tu carta para «'.Str::limit($ultima->institucion, 60).'» fue aprobada: ya continúas con el Bachiller.', 'Ver respuesta'],
            'enviada' => ['Secretaría recibió tu solicitud: te responderá en 3 días hábiles.', 'Ver mi solicitud'],
            'observada' => ['Secretaría observó tu solicitud: corrígela y envíala de nuevo.', 'Corregir y enviar'],
            default => ['Con tu PTI aprobado, solicita la carta para la Institución Educativa donde harás tu investigación.', 'Solicitar carta'],
        };

        return ['estado' => $ultima?->estado, 'mensaje' => $mensaje, 'boton' => $boton, 'url' => $this->ruta('mi-titulacion.carta'), 'formulario_app' => 'carta'];
    }

    /** Revisión de originalidad (Turnitin): solo si está encendida para alumnos, con el PTI aprobado y hasta titularse. */
    private function originalidad(AvanceTitulacion $avance, SeguimientoTitulacion $seguimiento): ?array
    {
        if (! config('titulacion.originalidad_alumnos') || ! $avance->hitos['pti_aprobado']['logrado'] || $avance->hitos['licenciado']['logrado']) {
            return null;
        }

        $ultima = Solicitud::dePersona(Solicitud::ORIGINALIDAD, $seguimiento->user)->first();
        $sim = $ultima?->similitud();
        $doc = $ultima ? (Solicitud::ETAPAS[$ultima->etapa()] ?? 'documento') : null;
        $pct = $sim !== null ? rtrim(rtrim(number_format($sim, 2), '0'), '.').' %' : null;

        [$mensaje, $boton] = match (true) {
            ! $ultima => ['Cuando termines tu TI o tu Tesis, solicita la revisión de originalidad en Turnitin (máximo '.Solicitud::SIMILITUD_MAXIMA.' % de similitud).', 'Solicitar revisión'],
            $ultima->estado === 'recibida' => ['Tu revisión de '.$doc.' está en curso: el reporte llega a más tardar el '.$ultima->fechaLimite()?->format('d/m/Y').'.', 'Ver mi solicitud'],
            $ultima->estado === 'observada' => ['Tu solicitud de revisión fue observada: corrígela y envíala de nuevo.', 'Corregir y enviar'],
            $sim !== null && $ultima->esApto() => ['Tu '.$doc.' tiene '.$pct.' de similitud: está apto.', 'Ver reporte'],
            $sim !== null => ['Tu '.$doc.' tiene '.$pct.' de similitud: corrígelo y solicita otra revisión.', 'Ver y solicitar otra'],
            default => ['Tu última revisión ('.$doc.') se atendió por correo. Solicita aquí la siguiente revisión.', 'Solicitar revisión'],
        };

        return ['estado' => $ultima?->estado, 'similitud' => $sim, 'mensaje' => $mensaje, 'boton' => $boton, 'url' => $this->ruta('mi-titulacion.originalidad'), 'formulario_app' => 'originalidad'];
    }

    /** "Qué hacer ahora": trámites del tramo en curso, con pago, pasos, formulario y descargas. */
    private function tramites(?array $bloque): ?array
    {
        if (! $bloque) {
            return null;
        }

        return [
            'clave' => $bloque['clave'],
            'titulo' => $bloque['titulo'],
            'items' => collect($bloque['tramites'])->map(fn ($t, $clave) => [
                'clave' => $clave,
                'titulo' => $t['titulo'] ?? '',
                'opcional' => $t['opcional'] ?? null,
                'pago' => isset($t['pago']) ? ['monto' => $t['pago'][0], 'codigo' => $t['pago'][1], 'concepto' => $t['pago'][2]] : null,
                'pasos' => $t['pasos'] ?? [],
                'formulario' => isset($t['formulario']) ? [
                    'texto' => $t['formulario'][0],
                    'url' => $this->ruta($t['formulario'][1]),
                    // Formularios del sistema que la app tiene en nativo (los de Google se abren en el navegador).
                    'formulario_app' => self::FORMULARIOS_APP[$t['formulario'][1]] ?? null,
                ] : null,
                'descargas' => array_map(fn ($d) => ['texto' => $d[0], 'url' => asset($d[1])], $t['descargas'] ?? []),
                'adjuntar' => $t['adjuntar'] ?? [],
                'resultado' => $t['resultado'] ?? null,
                'plazo' => $t['plazo'] ?? null,
                'nota' => $t['nota'] ?? null,
            ])->values()->all(),
            'contactos' => array_map(fn ($c) => ['nombre' => $c[0], 'telefono' => $c[1]], config('titulacion.contactos', [])),
        ];
    }

    /** Nombre de ruta → URL absoluta; una URL externa se deja tal cual. */
    private function ruta(string $nombreOUrl): string
    {
        return Route::has($nombreOUrl) ? route($nombreOUrl) : $nombreOUrl;
    }

    private function fecha($f): ?string
    {
        return $f instanceof CarbonInterface ? $f->toDateString() : null;
    }
}
