{{-- Bandeja de solicitudes de revisión de originalidad (Turnitin) del TI o de la Tesis (SolicitudOriginalidadController).
     Investigación atiende cada una subiendo el reporte y el % de similitud, o la observa. Mismo diseño que las otras
     bandejas de Solicitudes (css/pk-titulacion.css, clases tl-* y ca-*). --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\Solicitud;
        use App\Services\Titulacion\DiasHabiles;
        use Illuminate\Support\Str;
        $total = $conteo->sum();
        $url = fn (array $extra) => route('titulacion.solicitudes-originalidad.index', array_filter(array_merge(request()->except('page', 'resaltar'), $extra), fn ($v) => $v !== null && $v !== ''));
        $hayFiltros = collect($filtros)->except('resaltar')->filter(fn ($v) => filled($v))->isNotEmpty();
        $etiquetas = ['recibida' => 'Por atender', 'atendida' => 'Atendidas', 'observada' => 'Observadas'];
        $max = Solicitud::SIMILITUD_MAXIMA;
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-spell-check"></i> Titulación · Solicitudes · Investigación</div>
                <h1 class="tl-hero__titulo">Revisión de originalidad</h1>
                <p class="tl-hero__sub">Solicitudes de revisión en Turnitin del Trabajo de Investigación (cód. {{ Solicitud::PAGO_ORIGINALIDAD['ti'][0] }}) y de la Tesis (cód. {{ Solicitud::PAGO_ORIGINALIDAD['tesis'][0] }}). Se atienden subiendo el reporte con el % de similitud (apto hasta {{ $max }} %), en un plazo de {{ Solicitud::DIAS_ORIGINALIDAD }} días hábiles.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
                @if (\App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.solicitudes-originalidad.importar'))
                    <a href="{{ route('titulacion.solicitudes-originalidad.importar') }}" class="tl-hero__btn" title="Registrar las solicitudes hechas con el formulario de Google"><i class="fas fa-file-import"></i>Importar del formulario de Google</a>
                @endif
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <a href="{{ $url(['estado' => null]) }}" class="tl-stat tl-stat--link {{ empty($filtros['estado']) ? 'is-activo' : '' }}"><span class="tl-stat__num">{{ $total }}</span><span class="tl-stat__eti">Todas</span></a>
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
            @if ($errors->any())<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>@endif
            @unless ($puedeAtender)
                <div class="al-aviso is-info"><i class="fas fa-eye"></i><div>Las revisiones de originalidad las atiende el Área de Investigación (reporte o observación).@if ($puedeEditar) Secretaría puede corregir sus datos con «Editar la solicitud».@endif</div></div>
            @endunless
        </div>

        <section class="tl-tabla-card">
            <form method="GET" action="{{ route('titulacion.solicitudes-originalidad.index') }}" class="tl-filtros ca-filtros">
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="so-q">Buscar</label>
                    <div class="tl-buscar"><i class="fas fa-search"></i><input type="search" id="so-q" name="q" value="{{ $filtros['q'] ?? '' }}" class="form-control" placeholder="Nombre, DNI, expediente, título, correo…"></div>
                </div>
                <div class="tl-filtro">
                    <label for="so-etapa">Documento</label>
                    <select id="so-etapa" name="etapa" class="form-control"><option value="">TI y Tesis</option>@foreach (Solicitud::ETAPAS as $k => $n)<option value="{{ $k }}" @selected(($filtros['etapa'] ?? '') === $k)>{{ $k === 'ti' ? 'TI' : 'Tesis' }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro">
                    <label for="so-estado">Estado</label>
                    <select id="so-estado" name="estado" class="form-control"><option value="">Todos</option>@foreach (Solicitud::ESTADOS as $k => $n)<option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro">
                    <label for="so-resultado">Resultado</label>
                    <select id="so-resultado" name="resultado" class="form-control">
                        <option value="">Todos</option>
                        <option value="apto" @selected(($filtros['resultado'] ?? '') === 'apto')>Apto (≤ {{ $max }} %)</option>
                        <option value="no_apto" @selected(($filtros['resultado'] ?? '') === 'no_apto')>No apto (&gt; {{ $max }} %)</option>
                        <option value="sin_reporte" @selected(($filtros['resultado'] ?? '') === 'sin_reporte')>Atendida fuera del sistema</option>
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="so-anio">Año</label>
                    <select id="so-anio" name="anio" class="form-control"><option value="">Todos</option>@foreach ($anios as $a)<option value="{{ $a }}" @selected((string) ($filtros['anio'] ?? '') === (string) $a)>{{ $a }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro">
                    <label for="so-origen">Origen</label>
                    <select id="so-origen" name="origen" class="form-control"><option value="">Todos</option>@foreach (Solicitud::ORIGENES as $k => $n)<option value="{{ $k }}" @selected(($filtros['origen'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro ca-filtros__botones">
                    <button type="submit" class="btn tp-btn"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                    @if ($hayFiltros)<a href="{{ route('titulacion.solicitudes-originalidad.index') }}" class="btn tp-btn tp-btn--claro">Limpiar</a>@endif
                </div>
            </form>

            <div class="tl-resumen">
                <span><strong>{{ $registros->total() }}</strong> <span class="tl-resumen__suave">{{ $registros->total() === 1 ? 'solicitud' : 'solicitudes' }}{{ $hayFiltros ? ' con estos filtros' : '' }} · de la más reciente a la más antigua</span></span>
            </div>

            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead><tr><th>Recibida</th><th>Tesistas</th><th>Documento</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @forelse ($registros as $r)
                            @php
                                $resaltada = $resaltar === $r->id;
                                $idFila = 'so-'.$r->id;
                                $limite = $r->fechaLimite();
                                $quedan = $r->estado === 'recibida' ? DiasHabiles::restantes($limite) : null;
                                $documentos = $r->urls('archivo');
                                $comprobantes = $r->urls('comprobante');
                                $reportes = $r->urls('reporte');
                                $sim = $r->similitud();
                                $fichaDe = $r->seguimiento?->user_id;
                            @endphp
                            <tr class="ca-fila {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}">
                                <td class="ca-c-fecha">
                                    <strong>{{ $r->created_at->format('d/m/Y') }}</strong><small>{{ $r->created_at->format('H:i') }}</small>
                                    @if ($quedan !== null)
                                        <span class="ca-plazo {{ $quedan < 0 ? 'is-vencido' : ($quedan === 0 ? 'is-hoy' : '') }}">{{ $quedan < 0 ? 'Venció el '.$limite->format('d/m') : ($quedan === 0 ? 'Vence hoy' : 'Hasta el '.$limite->format('d/m')) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        @foreach ($r->integrantes ?? [] as $i)
                                            <span class="d-block">{{ $i['nombre'] }}@unless ($i['user_id'] ?? null) <small class="text-muted">(sin cuenta reconocida)</small>@endunless</span>
                                        @endforeach
                                        @if ($r->seguimiento?->numero_expediente)<small>Exp. {{ \App\Services\Titulacion\Expediente::mostrar($r->seguimiento->numero_expediente) }}</small>@endif
                                    </div>
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        <span><span class="ca-chip">{{ $r->etapa() === 'tesis' ? 'Tesis' : 'TI' }}</span></span>
                                        <strong>{{ Str::limit($r->titulo, 130) ?: '—' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="ca-estado ca-estado--{{ ['recibida' => 'enviada', 'atendida' => 'emitida', 'observada' => 'observada'][$r->estado] ?? $r->estado }}">{{ Solicitud::ESTADOS[$r->estado] ?? $r->estado }}</span>
                                    @if ($sim !== null)
                                        <small class="d-block mt-1 font-weight-bold {{ $r->esApto() ? 'text-success' : 'text-danger' }}">{{ rtrim(rtrim(number_format($sim, 2), '0'), '.') }} % · {{ $r->esApto() ? 'apto' : 'no apto' }}</small>
                                    @elseif ($r->estado === 'atendida')
                                        <small class="d-block text-muted mt-1">Fuera del sistema</small>
                                    @endif
                                    @if ($r->origen !== 'sistema')<span class="ca-origen">{{ $r->origen === 'formulario' ? 'Formulario de Google' : 'Registrada a mano' }}</span>@endif
                                </td>
                                <td class="ca-acciones">
                                    @if ($documentos)<a href="{{ $documentos[0] }}" target="_blank" rel="noopener" class="btn btn-sm tp-btn tp-btn--claro" title="Abrir el documento"><i class="fas fa-file-word"></i></a>@endif
                                    <button type="button" class="btn btn-sm tp-btn js-ca-revisar" aria-expanded="{{ $resaltada ? 'true' : 'false' }}" aria-controls="{{ $idFila }}-det">
                                        <i class="fas fa-chevron-down mr-1"></i>{{ $puedeAtender && $r->estado === 'recibida' ? 'Atender' : 'Ver' }}
                                    </button>
                                </td>
                            </tr>
                            <tr class="ca-detalle {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}-det" @unless ($resaltada) hidden @endunless>
                                <td colspan="5">
                                    <div class="ca-detalle__grid">
                                        <dl class="tp-carta-datos mb-0">
                                            <div class="tp-carta-ancho"><dt>Tesistas</dt><dd>{{ implode('; ', $r->nombresIntegrantes()) ?: '—' }}</dd></div>
                                            <div class="tp-carta-ancho"><dt>{{ Solicitud::ETAPAS[$r->etapa()] ?? 'Documento' }}@if ($r->datos['etapa_supuesta'] ?? false) <small class="text-muted">(supuesto: la respuesta antigua no lo indicaba)</small>@endif</dt><dd>“{{ $r->titulo ?: '—' }}”</dd></div>
                                            @if ($r->programa_estudios)<div><dt>Programa de Estudios</dt><dd>{{ $r->programa_estudios }}</dd></div>@endif
                                            @if ($r->ciclo || ($r->datos['modalidad'] ?? null))<div><dt>Ciclo · modalidad</dt><dd>{{ collect([$r->ciclo, $r->datos['modalidad'] ?? null])->filter()->implode(' · ') }}</dd></div>@endif
                                            @if ($r->celulares)<div><dt>Celulares</dt><dd>{{ $r->celulares }}</dd></div>@endif
                                            @if ($r->correo)<div><dt>Correo</dt><dd>{{ $r->correo }}</dd></div>@endif
                                            @if ($r->datos['fecha_pago'] ?? null)<div><dt>Fecha de pago</dt><dd>{{ $r->datos['fecha_pago'] }}</dd></div>@endif
                                            @if ($r->datos['domicilio'] ?? null)<div><dt>Domicilio</dt><dd>{{ $r->datos['domicilio'] }}</dd></div>@endif
                                            @if ($r->origen === 'formulario')
                                                <div class="tp-carta-ancho"><dt>Origen</dt><dd>Formulario de Google · hoja «{{ $r->datos['hoja'] ?? '—' }}», fila {{ $r->datos['fila'] ?? '—' }} · reconocida por {{ Str::lower(\App\Services\Titulacion\ReconocerPersonas::METODOS[$r->datos['reconocido_por'] ?? ''] ?? '—') }}.
                                                    @if (($r->datos['atendida_fuera'] ?? null) === 'avance') Se da por atendida porque ya avanzó a un paso posterior. @elseif (($r->datos['atendida_fuera'] ?? null) === 'antigua') Se da por atendida por su antigüedad. @endif
                                                    El reporte se envió por correo.</dd></div>
                                            @endif
                                            @if ($r->observacion)<div class="tp-carta-ancho"><dt>{{ $r->estado === 'observada' ? 'Observación' : 'Mensaje con el reporte' }}</dt><dd class="tp-carta-mensaje">{{ $r->observacion }}</dd></div>@endif
                                            @if ($r->atendido_en)<div><dt>{{ $r->estado === 'observada' ? 'Observada' : 'Atendida' }}</dt><dd>{{ $r->atendido_en->format('d/m/Y') }}{{ $r->atendidoPor ? ' · '.$r->atendidoPor->name : '' }}</dd></div>@endif
                                        </dl>
                                        <div class="ca-comprobante">
                                            <span class="tp-label d-block">Anexos</span>
                                            @forelse ($documentos as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-word"></i><span>Versión final en Word{{ count($documentos) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i></a>
                                            @empty
                                                <small class="text-muted d-block mb-2">Sin el documento.</small>
                                            @endforelse
                                            @foreach ($comprobantes as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-invoice-dollar"></i><span>Comprobante de pago{{ count($comprobantes) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i></a>
                                            @endforeach
                                            @foreach (Solicitud::enlaces($r->datos['informe_ti'] ?? null) as $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-pdf"></i><span>Informe de TI apto</span><i class="fas fa-external-link-alt"></i></a>
                                            @endforeach
                                            @foreach ($reportes as $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2"><i class="fas fa-file-pdf"></i><span>Reporte de originalidad</span><i class="fas fa-external-link-alt"></i></a>
                                            @endforeach
                                            @if ($fichaDe)
                                                <a href="{{ route('titulacion.index', ['ver' => $fichaDe, 'tab' => 'proceso']) }}" class="ca-ficha"><i class="fas fa-user-graduate mr-1"></i>Abrir la ficha de titulación</a>
                                            @endif
                                            @if ($puedeEditar)
                                                <a href="{{ route('titulacion.solicitudes.editar', $r->id) }}" class="ca-ficha"><i class="fas fa-pen mr-1"></i>Editar la solicitud</a>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($r->estado === 'recibida' && $puedeAtender)
                                        <form method="POST" action="{{ route('titulacion.solicitudes-originalidad.atender', $r->id) }}" enctype="multipart/form-data" class="tp-form ca-respuesta">
                                            @csrf
                                            <span class="tp-label d-block mb-1"><i class="fas fa-spell-check mr-1"></i>Resultado de Turnitin</span>
                                            <div class="ca-carta-grid">
                                                <label>% de similitud<input type="number" name="similitud" step="0.01" min="0" max="100" required class="form-control form-control-sm" placeholder="Ej.: 12"></label>
                                                <label class="ca-ancho">Reporte de Turnitin (PDF, hasta 10 MB)<input type="file" name="reporte" accept=".pdf,application/pdf" required class="form-control-file"></label>
                                                <label class="ca-total">Mensaje para los tesistas (opcional)<textarea name="mensaje" rows="2" maxlength="1500" class="form-control form-control-sm" placeholder="Ej.: Su documento está apto; puede solicitar la revisión del TI."></textarea></label>
                                            </div>
                                            <div class="small text-muted my-2"><i class="fas fa-info-circle mr-1"></i>Apto hasta {{ $max }} %. Cada tesista recibe el resultado en su campanita y lo ve en su intranet con el reporte.</div>
                                            <button type="submit" class="btn tp-btn"><i class="fas fa-paper-plane mr-1"></i>Enviar reporte</button>
                                        </form>
                                        @if ($r->origen === 'sistema')
                                            <details class="mt-2 px-3 pb-3">
                                                <summary class="small" style="cursor:pointer;"><i class="fas fa-undo mr-1"></i>Observar la solicitud (sin reporte)</summary>
                                                <form method="POST" action="{{ route('titulacion.solicitudes-originalidad.observar', $r->id) }}" class="mt-2">
                                                    @csrf
                                                    <textarea name="observacion" rows="3" maxlength="1500" required class="form-control form-control-sm" placeholder="¿Qué debe corregir? Ej.: el archivo no es la versión final en Word."></textarea>
                                                    <button type="submit" class="btn btn-sm tp-btn tp-btn--claro mt-2"><i class="fas fa-paper-plane mr-1"></i>Enviar observación</button>
                                                </form>
                                            </details>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">{{ $hayFiltros ? 'Ninguna solicitud coincide con los filtros.' : 'Todavía no hay solicitudes: importe las del formulario de Google.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($registros->hasPages())<div class="px-3 pb-3">{{ $registros->links() }}</div>@endif
        </section>
    </div>

    <script>
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.js-ca-revisar');
        if (!b) return;
        var det = document.getElementById(b.getAttribute('aria-controls'));
        det.hidden = !det.hidden;
        b.setAttribute('aria-expanded', det.hidden ? 'false' : 'true');
    });
    var nueva = document.querySelector('.ca-fila.is-resaltada');
    if (nueva) setTimeout(function () { nueva.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 250);
    </script>
@endsection
