{{-- Todas las resoluciones (RD) de la persona en orden cronológico, para la
     pestaña Resumen. Une la lista de resoluciones con las RD anotadas en los
     campos del PTI / TI / Tesis que no estén ya en la lista (mismo número),
     para que no falte ninguna. Las que no tienen fecha van al final.
     Espera $seguimiento. --}}
@php
    use App\Models\Titulacion\Resolucion;

    $normalizar = fn ($n) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $n)));
    $items = $seguimiento->resoluciones->map(fn ($r) => [
        'tipo' => $r->tipo, 'numero' => $r->numero_rd, 'fecha' => $r->fecha,
        'archivo' => $r->archivo, 'detalle' => $r->detalle, 'deCampo' => false,
    ]);
    $yaListadas = $items->pluck('numero')->filter()->map($normalizar)->all();

    $deCampos = [];
    foreach ($seguimiento->ptis ?? collect([$seguimiento->pti])->filter() as $p) {
        $deCampos[] = ['PTI', $p->rd_pti, $p->fecha_pti, 'PTI N° '.$p->numero.' ('.(\App\Models\Titulacion\Pti::ETAPAS[$p->etapa] ?? $p->etapa).')'];
        $deCampos[] = ['AmpliacionPti', $p->rd_ampliacion, $p->fecha_ampliacion, $p->vencimiento_ampliado ? 'Nuevo vencimiento: '.$p->vencimiento_ampliado->format('d/m/Y') : null];
    }
    if ($t = $seguimiento->tesis) {
        $deCampos[] = ['Exposicion', $t->rd_ti, $t->fecha_exposicion, null];
        $deCampos[] = ['Extension', $t->rd_extension, $t->fecha_extension, null];
        $deCampos[] = ['Tesis', $t->rd_tesis, $t->fecha_sustentacion, null];
    }
    foreach ($deCampos as [$tipo, $numero, $fecha, $detalle]) {
        if (filled($numero) && ! in_array($normalizar($numero), $yaListadas, true)) {
            $items->push(['tipo' => $tipo, 'numero' => $numero, 'fecha' => $fecha, 'archivo' => null, 'detalle' => $detalle, 'deCampo' => true]);
            $yaListadas[] = $normalizar($numero);
        }
    }
    $items = $items->sortBy(fn ($i) => $i['fecha'] ? $i['fecha']->format('Y-m-d') : '9999-99-99')->values();
@endphp

<div class="mt-subtitulo d-flex align-items-center">
    Resoluciones en orden cronológico
    <span class="ml-auto font-weight-normal text-muted" style="text-transform:none;letter-spacing:0;">{{ $items->count() }} RD</span>
</div>
@if ($items->isEmpty())
    <p class="small text-muted mb-3">Todavía no hay resoluciones registradas.</p>
@else
    <ol class="ft-rd-linea mb-3">
        @foreach ($items as $i)
            @php [$icono, $color] = Resolucion::ESTILOS[$i['tipo']] ?? ['fa-gavel', 'dark']; @endphp
            <li class="ft-rd-linea__item">
                <span class="ft-rd-linea__punto bg-{{ $color }}"><i class="fas {{ $icono }}"></i></span>
                <div class="ft-rd-linea__cuerpo">
                    <div class="d-flex flex-wrap align-items-center" style="gap:.35rem;">
                        <span class="ft-rd-linea__fecha">{{ $i['fecha'] ? $i['fecha']->format('d/m/Y') : 'Sin fecha' }}</span>
                        <span class="badge badge-{{ $color }}">{{ Resolucion::nombreTipo($i['tipo']) }}</span>
                        @if ($i['archivo'])
                            <a href="{{ $i['archivo'] }}" target="_blank" rel="noopener" class="small ml-auto text-nowrap"><i class="fas fa-file-pdf mr-1"></i>Ver PDF</a>
                        @endif
                    </div>
                    <div class="small ft-rd-linea__numero">{{ $i['numero'] }}</div>
                    @if ($i['detalle'] || $i['deCampo'])
                        <div class="small text-muted">
                            {{ $i['detalle'] }}
                            @if ($i['deCampo']) <span title="Anotada en el formulario de la etapa; agréguela en Resoluciones para enlazar su PDF">· anotada en la etapa</span> @endif
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
