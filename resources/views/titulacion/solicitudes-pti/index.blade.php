{{-- Bandeja de solicitudes de aprobación de PTI (SolicitudPtiController::index). Por ahora llegan del formulario
     de Google (importadas); Investigación atiende cada una iniciando el proceso desde la solicitud. Mismo
     diseño que los registros de cartas de aceptación (css/pk-titulacion.css, clases tl-* y ca-*). --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\Solicitud;
        use App\Http\Controllers\Titulacion\InicioProcesoController as Inicio;
        use Illuminate\Support\Str;
        $total = $conteo->sum();
        $url = fn (array $extra) => route('titulacion.solicitudes-pti.index', array_filter(array_merge(request()->except('page', 'resaltar'), $extra), fn ($v) => $v !== null && $v !== ''));
        $hayFiltros = collect($filtros)->except('resaltar')->filter(fn ($v) => filled($v))->isNotEmpty();
        $etiquetas = ['recibida' => 'Por atender', 'atendida' => 'Atendidas', 'observada' => 'Observadas'];
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-file-signature"></i> Titulación · Solicitudes · Investigación</div>
                <h1 class="tl-hero__titulo">Solicitudes de PTI</h1>
                <p class="tl-hero__sub">Solicitudes de aprobación del Plan de Trabajo de Investigación y asignación de asesor (código {{ \App\Services\Titulacion\TramitesExtraordinarios::TIPOS['aprobacion_pti']['codigo'] }}). Por ahora llegan por el formulario de Google; cada una se atiende iniciando el proceso con sus datos ya puestos.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
                @if (\App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.solicitudes-pti.importar'))
                    <a href="{{ route('titulacion.solicitudes-pti.importar') }}" class="tl-hero__btn" title="Registrar las solicitudes hechas con el formulario de Google (Excel de respuestas)"><i class="fas fa-file-import"></i>Importar del formulario de Google</a>
                @endif
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <a href="{{ $url(['estado' => null]) }}" class="tl-stat tl-stat--link {{ empty($filtros['estado']) ? 'is-activo' : '' }}">
                        <span class="tl-stat__num">{{ $total }}</span><span class="tl-stat__eti">Todas</span>
                    </a>
                    @foreach (Solicitud::ESTADOS as $clave => $nombre)
                        <a href="{{ $url(['estado' => $clave]) }}" class="tl-stat tl-stat--link {{ $clave === 'recibida' ? 'tl-stat--oro' : '' }} {{ ($filtros['estado'] ?? null) === $clave ? 'is-activo' : '' }}">
                            <span class="tl-stat__num">{{ $conteo[$clave] ?? 0 }}</span><span class="tl-stat__eti">{{ $etiquetas[$clave] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        <div class="al-ui">
            @include('titulacion.solicitudes._pestanas')
            @if (session('success'))<div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>@endif
            @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>@endif
            @unless ($puedeIniciar)
                <div class="al-aviso is-info"><i class="fas fa-eye"></i><div>Las solicitudes de PTI las atiende el Área de Investigación (iniciar el proceso u observar).@if ($puedeEditar) Secretaría puede corregir sus datos con «Editar la solicitud».@endif</div></div>
            @endunless
        </div>

        <section class="tl-tabla-card">
            <form method="GET" action="{{ route('titulacion.solicitudes-pti.index') }}" class="tl-filtros ca-filtros">
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="sp-q">Buscar</label>
                    <div class="tl-buscar">
                        <i class="fas fa-search"></i>
                        <input type="search" id="sp-q" name="q" value="{{ $filtros['q'] ?? '' }}" class="form-control" placeholder="Nombre, DNI, expediente, título, correo, celular…">
                    </div>
                </div>
                <div class="tl-filtro">
                    <label for="sp-estado">Estado</label>
                    <select id="sp-estado" name="estado" class="form-control">
                        <option value="">Todos</option>
                        @foreach (Solicitud::ESTADOS as $clave => $nombre)<option value="{{ $clave }}" @selected(($filtros['estado'] ?? '') === $clave)>{{ $nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="sp-programa">Programa</label>
                    <select id="sp-programa" name="programa" class="form-control">
                        <option value="">Todos</option>
                        @foreach (Solicitud::PROGRAMAS_ESTUDIOS as $p)<option value="{{ $p }}" @selected(($filtros['programa'] ?? '') === $p)>{{ $p }}</option>@endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="sp-area">Área</label>
                    <select id="sp-area" name="area" class="form-control">
                        <option value="">Todas</option>
                        @foreach (Solicitud::AREAS as $clave => $nombre)<option value="{{ $clave }}" @selected(($filtros['area'] ?? '') === $clave)>{{ $clave }}</option>@endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="sp-origen">Origen</label>
                    <select id="sp-origen" name="origen" class="form-control">
                        <option value="">Todos</option>
                        @foreach (Solicitud::ORIGENES as $clave => $nombre)<option value="{{ $clave }}" @selected(($filtros['origen'] ?? '') === $clave)>{{ $nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="sp-anio">Año</label>
                    <select id="sp-anio" name="anio" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($anios as $a)<option value="{{ $a }}" @selected((string) ($filtros['anio'] ?? '') === (string) $a)>{{ $a }}</option>@endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="sp-desde">Recibidas desde</label>
                    <input type="date" id="sp-desde" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="form-control">
                </div>
                <div class="tl-filtro">
                    <label for="sp-hasta">hasta</label>
                    <input type="date" id="sp-hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="form-control">
                </div>
                <div class="tl-filtro ca-filtros__botones">
                    <button type="submit" class="btn tp-btn"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                    @if ($hayFiltros)<a href="{{ route('titulacion.solicitudes-pti.index') }}" class="btn tp-btn tp-btn--claro">Limpiar</a>@endif
                </div>
            </form>

            <div class="tl-resumen">
                <span><strong>{{ $registros->total() }}</strong> <span class="tl-resumen__suave">{{ $registros->total() === 1 ? 'solicitud' : 'solicitudes' }}{{ $hayFiltros ? ' con estos filtros' : '' }} · de la más reciente a la más antigua</span></span>
            </div>

            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead>
                        <tr><th>Recibida</th><th>Grupo de investigación</th><th>Plan de Trabajo de Investigación</th><th>Programa</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            @php
                                $resaltada = $resaltar === $r->id;
                                $s = $r->solicitante;
                                $idFila = 'sp-'.$r->id;
                                $ptis = $r->urls('archivo');
                                $comprobantes = $r->urls('comprobante');
                                // Puede iniciarse desde aquí: está por atender, se sabe quién la envió y esa persona puede iniciar.
                                $motivo = $r->estado === 'recibida' && $s ? Inicio::motivoNoPuede($s) : null;
                                $fichaDe = $r->seguimiento?->user_id ?? ($s?->seguimientoTitulacion ? $s->id : null);
                            @endphp
                            <tr class="ca-fila {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}">
                                <td class="ca-c-fecha">
                                    <strong>{{ $r->created_at->format('d/m/Y') }}</strong>
                                    <small>{{ $r->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        @foreach ($r->integrantes ?? [] as $i)
                                            <span class="d-block">{{ $i['nombre'] }}@unless ($i['user_id'] ?? null) <small class="text-muted" title="No se reconoció en el sistema">(sin cuenta reconocida)</small>@endunless</span>
                                        @endforeach
                                        @if ($r->seguimiento?->numero_expediente)<small>Exp. {{ \App\Services\Titulacion\Expediente::mostrar($r->seguimiento->numero_expediente) }}</small>@endif
                                    </div>
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        <strong>{{ Str::limit($r->titulo, 140) }}</strong>
                                        @if ($r->linea())<small title="{{ $r->area() }}">{{ Str::limit($r->linea(), 80) }}</small>@elseif ($r->area())<small>{{ $r->area() }}</small>@endif
                                    </div>
                                </td>
                                <td><small>{{ $r->programa_estudios ?: '—' }}</small>@if ($r->ciclo)<span class="ca-chip">{{ $r->ciclo }}</span>@endif</td>
                                <td>
                                    <span class="ca-estado ca-estado--{{ ['recibida' => 'enviada', 'atendida' => 'emitida', 'observada' => 'observada'][$r->estado] ?? $r->estado }}">{{ Solicitud::ESTADOS[$r->estado] ?? $r->estado }}</span>
                                    @if ($r->origen !== 'sistema')<span class="ca-origen" title="{{ Solicitud::ORIGENES[$r->origen] ?? '' }}">{{ $r->origen === 'formulario' ? 'Formulario de Google' : 'Registrada a mano' }}</span>@endif
                                    @if ($r->atendido_en)<small class="d-block text-muted mt-1">{{ $r->atendido_en->format('d/m/Y') }}{{ $r->atendidoPor ? ' · '.$r->atendidoPor->name : '' }}</small>@endif
                                </td>
                                <td class="ca-acciones">
                                    @if ($ptis)
                                        <a href="{{ $ptis[0] }}" target="_blank" rel="noopener" class="btn btn-sm tp-btn tp-btn--claro" title="Abrir el PTI"><i class="fas fa-file-alt"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-sm tp-btn js-ca-revisar" aria-expanded="{{ $resaltada ? 'true' : 'false' }}" aria-controls="{{ $idFila }}-det">
                                        <i class="fas fa-chevron-down mr-1"></i>Ver
                                    </button>
                                </td>
                            </tr>
                            <tr class="ca-detalle {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}-det" @unless ($resaltada) hidden @endunless>
                                <td colspan="6">
                                    <div class="ca-detalle__grid">
                                        <dl class="tp-carta-datos mb-0">
                                            <div class="tp-carta-ancho"><dt>Grupo de investigación (como lo escribieron)</dt><dd>{{ implode('; ', $r->nombresIntegrantes()) ?: '—' }}</dd></div>
                                            <div class="tp-carta-ancho"><dt>Título del Plan de Trabajo de Investigación</dt><dd>“{{ $r->titulo }}”</dd></div>
                                            @if ($r->area())<div class="tp-carta-ancho"><dt>Área de investigación</dt><dd>{{ $r->area() }}</dd></div>@endif
                                            @if ($r->linea())<div class="tp-carta-ancho"><dt>Línea de investigación</dt><dd>{{ $r->linea() }}</dd></div>@endif
                                            @if ($r->correo)<div><dt>Correo</dt><dd>{{ $r->correo }}</dd></div>@endif
                                            @if ($r->celulares)<div><dt>Celulares</dt><dd>{{ $r->celulares }}</dd></div>@endif
                                            @if ($r->programa_estudios)<div><dt>Programa de Estudios</dt><dd>{{ $r->programa_estudios }}</dd></div>@endif
                                            @if ($r->ciclo)<div><dt>Ciclo o programa</dt><dd>{{ $r->ciclo }}</dd></div>@endif
                                            <div><dt>Declaración de veracidad</dt><dd>{{ $r->declaracion ? 'Aceptada' : '—' }}</dd></div>
                                            @if ($s)<div><dt>Envió</dt><dd>{{ trim($s->apellidos.', '.$s->name) }} · DNI {{ $s->dni ?: '—' }}</dd></div>@endif
                                            @if ($r->origen === 'formulario')
                                                <div class="tp-carta-ancho"><dt>Origen</dt><dd>Formulario de Google · hoja «{{ $r->datos['hoja'] ?? '—' }}», fila {{ $r->datos['fila'] ?? '—' }}
                                                    · reconocida por {{ Str::lower(\App\Services\Titulacion\ReconocerPersonas::METODOS[$r->datos['reconocido_por'] ?? ''] ?? '—') }}.</dd></div>
                                            @endif
                                            @if ($r->observacion)<div class="tp-carta-ancho"><dt>{{ $r->estado === 'observada' ? 'Observación' : 'Nota' }}</dt><dd class="tp-carta-mensaje">{{ $r->observacion }}</dd></div>@endif
                                        </dl>
                                        <div class="ca-comprobante">
                                            <span class="tp-label d-block">Anexos</span>
                                            @forelse ($ptis as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-alt"></i><span>Plan de Trabajo de Investigación{{ count($ptis) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i></a>
                                            @empty
                                                <small class="text-muted d-block mb-2">Sin el archivo del PTI.</small>
                                            @endforelse
                                            @forelse ($comprobantes as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-invoice-dollar"></i><span>Comprobante de pago{{ count($comprobantes) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i></a>
                                            @empty
                                                <small class="text-muted d-block mb-2">Sin comprobante.</small>
                                            @endforelse

                                            @if ($r->estado === 'recibida' && $puedeIniciar)
                                                @if (! $s)
                                                    <small class="text-muted d-block">No se reconoció a quien la envió: inicie el proceso desde su ficha.</small>
                                                @elseif ($motivo)
                                                    <small class="text-muted d-block">No se puede iniciar desde aquí: {{ $motivo }}.</small>
                                                @else
                                                    <a href="{{ route('titulacion.inicio.crear', [$s->id, 'solicitud' => $r->id]) }}" class="btn tp-btn mt-1"><i class="fas fa-play mr-1"></i>Iniciar el proceso con estos datos</a>
                                                @endif
                                                {{-- Observar: el estudiante la ve en su intranet y la corrige (solo tiene sentido si la envió desde el sistema). --}}
                                                @if ($r->origen === 'sistema')
                                                    <details class="mt-2">
                                                        <summary class="small" style="cursor:pointer;"><i class="fas fa-undo mr-1"></i>Observar la solicitud</summary>
                                                        <form method="POST" action="{{ route('titulacion.solicitudes-pti.observar', $r->id) }}" class="mt-2">
                                                            @csrf
                                                            <textarea name="observacion" rows="3" maxlength="1500" required class="form-control form-control-sm" placeholder="¿Qué debe corregir? Ej.: el comprobante no corresponde al derecho de trámite."></textarea>
                                                            <button type="submit" class="btn btn-sm tp-btn tp-btn--claro mt-2"><i class="fas fa-paper-plane mr-1"></i>Enviar observación</button>
                                                        </form>
                                                    </details>
                                                @endif
                                            @endif
                                            @if ($fichaDe)
                                                <a href="{{ route('titulacion.index', ['ver' => $fichaDe, 'tab' => 'proceso']) }}" class="ca-ficha"><i class="fas fa-user-graduate mr-1"></i>Abrir la ficha de titulación</a>
                                            @endif
                                            @if ($puedeEditar)
                                                <a href="{{ route('titulacion.solicitudes.editar', $r->id) }}" class="ca-ficha"><i class="fas fa-pen mr-1"></i>Editar la solicitud</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">{{ $hayFiltros ? 'Ninguna solicitud coincide con los filtros.' : 'Todavía no hay solicitudes: importe las del formulario de Google.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($registros->hasPages())
                <div class="px-3 pb-3">{{ $registros->links() }}</div>
            @endif
        </section>
    </div>

    <script>
    // «Ver» despliega el detalle de la solicitud bajo su fila.
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.js-ca-revisar');
        if (!b) return;
        var det = document.getElementById(b.getAttribute('aria-controls'));
        det.hidden = !det.hidden;
        b.setAttribute('aria-expanded', det.hidden ? 'false' : 'true');
    });
    </script>
@endsection
