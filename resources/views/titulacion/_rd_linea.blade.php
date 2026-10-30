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
    $puedeRdLinea = \App\Services\Titulacion\AreasTitulacion::puede('secretaria');
@endphp

<section class="ft-hist mb-4">
    <header class="ft-hist__cab">
        <h3 class="ft-hist__titulo"><i class="fas fa-gavel"></i>Resoluciones</h3>
        <span class="ft-hist__meta">
            {{ $items->count() }} RD
            @if ($sinPdf) · <span class="text-warning">{{ $sinPdf }} sin PDF</span> @endif
        </span>
        <button type="button" class="btn btn-sm btn-primary ft-hist__nuevo" @unless ($puedeRdLinea) hidden @endunless data-ft-toggle="{{ $idNueva }}" aria-expanded="false" aria-controls="{{ $idNueva }}">
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
                                        <button type="button" class="ft-chip-aviso" @if ($puedeRdLinea) data-ft-toggle="{{ $idPanel }}" aria-controls="{{ $idPanel }}" title="Editar para adjuntar el PDF" @else disabled @endif>
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
                                    {{-- El documento es lo que más se busca en el historial: botón con texto, no solo ícono.
                                         PDF guardado por el sistema o enlace externo (Drive u otro). --}}
                                    @php
                                        $esPdf = (bool) \App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($i['archivo']);
                                        $textoDoc = \App\Services\Titulacion\ArchivosRd::etiqueta($i['archivo']);
                                    @endphp
                                    <a href="{{ $i['archivo'] }}" target="_blank" rel="noopener" class="ft-doc {{ $esPdf ? 'ft-doc--pdf' : 'ft-doc--enlace' }}"
                                       title="{{ $esPdf ? 'Abrir el PDF de la RD' : 'Abrir el enlace de la RD' }} en otra pestaña" aria-label="{{ $textoDoc }} de {{ $nombre }} {{ $i['numero'] }}">
                                        <span class="ft-doc__icono"><i class="fas {{ $esPdf ? 'fa-file-pdf' : ($textoDoc === 'Ver en Drive' ? 'fa-cloud' : 'fa-link') }}"></i></span>
                                        <span class="ft-doc__texto">{{ $textoDoc }}</span>
                                        <i class="fas fa-external-link-alt ft-doc__salida" aria-hidden="true"></i>
                                    </a>
                                @endif
                                @if (! $puedeRdLinea)
                                @elseif ($res)
                                    @if (filled($i['numero']))
                                        <button type="button" class="ft-accion" data-ft-toggle="{{ $idPanel }}-correo" aria-expanded="false" aria-controls="{{ $idPanel }}-correo"
                                            title="{{ $res->correo_enviado_en ? 'Enviada por correo el '.$res->correo_enviado_en->format('d/m/Y').'. Enviar de nuevo' : 'Enviar por correo' }}" aria-label="Enviar por correo {{ $nombre }}"><i class="{{ $res->correo_enviado_en ? 'fas' : 'far' }} fa-paper-plane"></i></button>
                                    @endif
                                    {{-- Las RD no vencen ni se eliminan: solo se corrigen sus datos o su PDF. --}}
                                    <button type="button" class="ft-accion" data-ft-toggle="{{ $idPanel }}" aria-expanded="false" aria-controls="{{ $idPanel }}" title="Editar" aria-label="Editar {{ $nombre }}"><i class="fas fa-pen"></i></button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary ft-accion-texto" data-ft-toggle="{{ $idPanel }}" aria-expanded="false" aria-controls="{{ $idPanel }}">
                                        <i class="fas fa-plus mr-1"></i>Registrar
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($res && filled($i['numero']) && $puedeRdLinea)
                            <div id="{{ $idPanel }}-correo" class="ft-hist__panel ft-hist__panel--dentro" hidden>
                                <div class="ft-hist__panel-titulo">Enviar por correo</div>
                                @include('titulacion._rd_correo', ['res' => $res, 'idC' => $idPanel.'-correo-f', 'tabVolver' => 'resoluciones'])
                                <button type="button" class="btn btn-sm btn-link text-muted ft-hist__cancelar" data-ft-toggle="{{ $idPanel }}-correo">Cancelar</button>
                            </div>
                        @endif

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
