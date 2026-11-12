<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\Titulacion\Solicitud;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Las bandejas de solicitudes de Titulación, como pestañas de una sola sección «Solicitudes»:
 * «Por atender» (lo pendiente de todos los trámites) y una pestaña por trámite. Para sumar un trámite
 * nuevo (cambio de asesor, extensión de plazo…) se agrega aquí su pestaña y su consulta de pendientes.
 */
class BandejasSolicitudes
{
    /** clave => [título, icono, ruta, patrón de ruta activa, área que lo atiende]. En el orden del proceso. */
    public const PESTANAS = [
        'pti' => ['Aprobación de PTI', 'fa-file-signature', 'titulacion.solicitudes-pti.index', 'titulacion.solicitudes-pti.*', 'Investigación'],
        'cartas' => ['Carta de aceptación', 'fa-envelope-open-text', 'titulacion.cartas.index', 'titulacion.cartas.*', 'Secretaría'],
        'originalidad' => ['Revisión de originalidad', 'fa-spell-check', 'titulacion.solicitudes-originalidad.index', 'titulacion.solicitudes-originalidad.*', 'Investigación'],
    ];

    /** Cuántas hay por atender en cada trámite (clave => número). */
    public static function pendientes(): array
    {
        return [
            'pti' => Solicitud::deTipo(Solicitud::PTI)->where('estado', 'recibida')->count(),
            'cartas' => CartaAceptacion::where('estado', 'enviada')->distinct('lote')->count('lote'),
            'originalidad' => Solicitud::deTipo(Solicitud::ORIGINALIDAD)->where('estado', 'recibida')->count(),
        ];
    }

    /**
     * Lo pendiente de todos los trámites, de lo más antiguo a lo más reciente.
     *
     * @return Collection<int, array{tipo:string, fecha:\Carbon\CarbonInterface, quien:string, detalle:string, limite:?\Carbon\CarbonInterface, url:string}>
     */
    public static function porAtender(): Collection
    {
        $pti = Solicitud::deTipo(Solicitud::PTI)->where('estado', 'recibida')->with('solicitante')->get()
            ->map(fn (Solicitud $s) => [
                'tipo' => 'pti',
                'fecha' => $s->created_at,
                'quien' => $s->solicitante ? trim($s->solicitante->apellidos.', '.$s->solicitante->name) : (implode('; ', $s->nombresIntegrantes()) ?: '—'),
                'detalle' => '“'.Str::limit((string) $s->titulo, 110).'”',
                'origen' => $s->origen,
                'limite' => null,
                'url' => route('titulacion.solicitudes-pti.index', ['resaltar' => $s->id]),
            ]);

        // La primera fila de cada lote es la de quien la envió.
        $cartas = CartaAceptacion::whereIn('id', CartaAceptacion::selectRaw('MIN(id)')->groupBy('lote'))
            ->where('estado', 'enviada')->with('solicitante')->get()
            ->map(fn (CartaAceptacion $c) => [
                'tipo' => 'cartas',
                'fecha' => $c->created_at,
                'quien' => trim(($c->solicitante?->apellidos ?? '').', '.($c->solicitante?->name ?? ''), ', ') ?: Str::limit($c->grupo, 60),
                'detalle' => 'Para «'.Str::limit($c->institucion, 90).'»',
                'origen' => $c->origen,
                'limite' => $c->fechaLimite(),
                'url' => route('titulacion.cartas.index', ['resaltar' => $c->lote]),
            ]);

        $originalidad = Solicitud::deTipo(Solicitud::ORIGINALIDAD)->where('estado', 'recibida')->with('solicitante')->get()
            ->map(fn (Solicitud $s) => [
                'tipo' => 'originalidad',
                'fecha' => $s->created_at,
                'quien' => $s->solicitante ? trim($s->solicitante->apellidos.', '.$s->solicitante->name) : (implode('; ', $s->nombresIntegrantes()) ?: '—'),
                'detalle' => (Solicitud::ETAPAS[$s->etapa()] ?? 'Documento').': “'.Str::limit((string) $s->titulo, 100).'”',
                'origen' => $s->origen,
                'limite' => $s->fechaLimite(),
                'url' => route('titulacion.solicitudes-originalidad.index', ['resaltar' => $s->id]),
            ]);

        return $pti->concat($cartas)->concat($originalidad)->sortBy('fecha')->values();
    }

    public static function total(): int
    {
        return array_sum(self::pendientes());
    }
}
