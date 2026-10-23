{{-- Pestaña Historial › Resoluciones: TODAS las RD de la persona en orden cronológico, con
     sus acciones en la misma tarjeta (ver PDF, editar en línea, eliminar). Une la lista de
     resoluciones con las RD anotadas en los campos del PTI / TI / Tesis que no estén ya en la
     lista (mismo número): esas se muestran punteadas con «Registrar», que abre el alta con los
     datos ya puestos. Las que no tienen fecha van al final. Espera $seguimiento, $egresado, $tabId. --}}
@php
    use App\Models\Titulacion\Resolucion;

    $normalizar = fn ($n) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $n)));
    $items = $seguimiento->resoluciones->map(fn ($r) => [
        'tipo' => $r->tipo, 'numero' => $r->numero_rd, 'fecha' => $r->fecha,
        'archivo' => $r->archivo, 'detalle' => $r->detalle, 'res' => $r,
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
            $items->push(['tipo' => $tipo, 'numero' => $numero, 'fecha' => $fecha, 'archivo' => null, 'detalle' => $detalle, 'res' => null]);
            $yaListadas[] = $normalizar($numero);
        }
    }
    $items = $items->sortBy(fn ($i) => $i['fecha'] ? $i['fecha']->format('Y-m-d') : '9999-99-99')->values();
    $sinPdf = $items->filter(fn ($i) => $i['res'] && ! $i['archivo'])->count();
    $idNueva = $tabId.'-rd-nueva';
@endphp

<section class="ft-hist mb-4">
    <header class="ft-hist__cab">
        <h3 class="ft-hist__titulo"><i class="fas fa-gavel"></i>Resoluciones</h3>
        <span class="ft-hist__meta">
            {{ $items->count() }} RD
            @if ($sinPdf) · <span class="text-warning">{{ $sinPdf }} sin PDF</span> @endif
        </span>
        <button type="button" class="btn btn-sm btn-primary ft-hist__nuevo" data-ft-toggle="{{ $idNueva }}" aria-expanded="false" aria-controls="{{ $idNueva }}">
            <i class="fas fa-plus mr-1"></i>Registrar RD
        </button>
    </header>

    <div id="{{ $idNueva }}" class="ft-hist__panel mb-3" hidden>
        <div class="ft-hist__panel-titulo">Registrar una RD ya emitida</div>
        @include('titulacion._form_resolucion', ['res' => null])
    </div>

    @if ($items->isEmpty())
        <p class="ft-hist__vacio">Todavía no hay resoluciones registradas.</p>
    @else
        <ol class="ft-rd-linea">
            @foreach ($items as $n => $i)
                @php
                    [$icono, $color] = Resolucion::ESTILOS[$i['tipo']] ?? ['fa-gavel', 'dark'];
                    $res = $i['res'];
                    $idPanel = $tabId.'-rd-'.($res ? $res->id : 'c'.$n);
                    $nombre = Resolucion::nombreTipo($i['tipo']);
                @endphp
                <li class="ft-rd-linea__item">
                    <span class="ft-rd-linea__punto bg-{{ $color }}"><i class="fas {{ $icono }}"></i></span>
                    <div class="ft-rd-linea__cuerpo {{ $res ? '' : 'is-sin-registro' }}">
                        <div class="ft-rd-linea__cab">
                            <div class="ft-rd-linea__info">
                                <div class="d-flex flex-wrap align-items-center" style="gap:.35rem;">
                                    <span class="ft-rd-linea__fecha">{{ $i['fecha'] ? $i['fecha']->format('d/m/Y') : 'Sin fecha' }}</span>
                                    <span class="badge badge-{{ $color }}">{{ $nombre }}</span>
                                    @if ($res && ! $i['archivo'])
                                        <button type="button" class="ft-chip-aviso" data-ft-toggle="{{ $idPanel }}" aria-controls="{{ $idPanel }}" title="Editar para adjuntar el PDF">
                                            <i class="fas fa-paperclip"></i>sin PDF
                                        </button>
                                    @endif
                                </div>
                                <div class="small ft-rd-linea__numero">{{ $i['numero'] ?? '—' }}</div>
                                @if ($i['detalle'] || ! $res)
                                    <div class="small text-muted">
                                        {{ $i['detalle'] }}
                                        @if (! $res) <span class="ft-rd-linea__nota"><i class="fas fa-info-circle"></i>Anotada en la etapa, aún sin registro ni PDF</span> @endif
                                    </div>
                                @endif
                            </div>

                            <div class="ft-acciones">
                                @if ($i['archivo'])
                                    <a href="{{ $i['archivo'] }}" target="_blank" rel="noopener" class="ft-accion" title="Ver PDF" aria-label="Ver PDF de {{ $nombre }}"><i class="fas fa-file-pdf"></i></a>
                                @endif
                                @if ($res)
                                    <button type="button" class="ft-accion" data-ft-toggle="{{ $idPanel }}" aria-expanded="false" aria-controls="{{ $idPanel }}" title="Editar" aria-label="Editar {{ $nombre }}"><i class="fas fa-pen"></i></button>
                                    <form method="POST" action="{{ route('titulacion.resoluciones.eliminar', $res->id) }}" data-confirm="Se eliminará esta resolución del historial." data-confirm-danger data-confirm-titulo="¿Eliminar la resolución?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ft-accion ft-accion--peligro" title="Eliminar" aria-label="Eliminar {{ $nombre }}"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary ft-accion-texto" data-ft-toggle="{{ $idPanel }}" aria-expanded="false" aria-controls="{{ $idPanel }}">
                                        <i class="fas fa-plus mr-1"></i>Registrar
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div id="{{ $idPanel }}" class="ft-hist__panel ft-hist__panel--dentro" hidden>
                            @if ($res)
                                @include('titulacion._form_resolucion', ['res' => $res])
                            @else
                                @include('titulacion._form_resolucion', [
                                    'res' => null,
                                    'tipoFijo' => array_key_exists($i['tipo'], Resolucion::TIPOS) ? $i['tipo'] : null,
                                    'numeroInicial' => $i['numero'],
                                    'fechaInicial' => $i['fecha'],
                                ])
                            @endif
                            <button type="button" class="btn btn-sm btn-link text-muted ft-hist__cancelar" data-ft-toggle="{{ $idPanel }}">Cancelar</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
