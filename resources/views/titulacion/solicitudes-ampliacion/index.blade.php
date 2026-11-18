@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\Solicitud;
        use Illuminate\Support\Str;
        use Carbon\Carbon;
        $total = $conteo->sum();
        $url = fn (array $extra) => route('titulacion.solicitudes-ampliacion.index', array_filter(array_merge(request()->except('page', 'resaltar'), $extra), fn ($v) => $v !== null && $v !== ''));
        $hayFiltros = collect($filtros)->except('resaltar')->filter(fn ($v) => filled($v))->isNotEmpty();
        $etiquetas = ['recibida' => 'Por atender', 'atendida' => 'Atendidas', 'observada' => 'Observadas'];
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-hourglass-half"></i> Titulación · Solicitudes · Secretaría</div>
                <h1 class="tl-hero__titulo">Ampliaciones de PTI</h1>
                <p class="tl-hero__sub">Solicitudes de ampliación y prórroga de vigencia de Plan de Trabajo de Investigación (tasa de Caja Cusco: Cód. 11 · S/ 50.00). Secretaría verifica la justificación, corrobora el pago y registra la R.D. de Ampliación de Plazo.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
                @if (\App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.solicitudes-ampliacion.importar'))
                    <a href="{{ route('titulacion.solicitudes-ampliacion.importar') }}" class="tl-hero__btn" title="Registrar las solicitudes desde el Excel de Google Forms o historial"><i class="fas fa-file-import"></i>Importar del formulario / Excel</a>
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
                <div class="al-aviso is-info"><i class="fas fa-eye"></i><div>Las solicitudes de ampliación las atiende Secretaría General (emisión de R.D. u observación).</div></div>
            @endunless
        </div>

        <section class="tl-tabla-card">
            <form method="GET" action="{{ route('titulacion.solicitudes-ampliacion.index') }}" class="tl-filtros ca-filtros">
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="sa-q">Buscar</label>
                    <div class="tl-buscar"><i class="fas fa-search"></i><input type="search" id="sa-q" name="q" value="{{ $filtros['q'] ?? '' }}" class="form-control" placeholder="Nombre, DNI, título, correo, N° RD…"></div>
                </div>
                <div class="tl-filtro">
                    <label for="sa-estado">Estado</label>
                    <select id="sa-estado" name="estado" class="form-control"><option value="">Todos</option>@foreach (Solicitud::ESTADOS as $k => $n)<option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro">
                    <label for="sa-anio">Año</label>
                    <select id="sa-anio" name="anio" class="form-control"><option value="">Todos</option>@foreach ($anios as $a)<option value="{{ $a }}" @selected((string) ($filtros['anio'] ?? '') === (string) $a)>{{ $a }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro">
                    <label for="sa-origen">Origen</label>
                    <select id="sa-origen" name="origen" class="form-control"><option value="">Todos</option>@foreach (Solicitud::ORIGENES as $k => $n)<option value="{{ $k }}" @selected(($filtros['origen'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                </div>
                <div class="tl-filtro ca-filtros__botones">
                    <button type="submit" class="btn tp-btn"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                    @if ($hayFiltros)<a href="{{ route('titulacion.solicitudes-ampliacion.index') }}" class="btn tp-btn tp-btn--claro">Limpiar</a>@endif
                </div>
            </form>

            <div class="tl-resumen">
                <span><strong>{{ $registros->total() }}</strong> <span class="tl-resumen__suave">{{ $registros->total() === 1 ? 'solicitud' : 'solicitudes' }}{{ $hayFiltros ? ' con estos filtros' : '' }} · de la más reciente a la más antigua</span></span>
            </div>

            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estudiante / Integrantes</th>
                            <th>Prórroga / Motivo</th>
                            <th>Estado</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            @php
                                $esResaltada = (int) request('resaltar') === $r->id;
                                $fichaDe = $r->seguimiento?->user_id ?? $r->solicitante_id;
                                $idDetalle = 'sa-det-'.$r->id;
                                $meses = $r->datos['meses_solicitados'] ?? 6;
                                $comprobantes = Solicitud::enlaces($r->comprobante, fn ($n) => route('mi-titulacion.ampliacion-pti.archivo', [$r->id, 'comprobante', $n]));
                                $documentos = Solicitud::enlaces($r->archivo, fn ($n) => route('mi-titulacion.ampliacion-pti.archivo', [$r->id, 'archivo', $n]));
                                $pti = $r->seguimiento?->pti;
                                $fechaVencSugerida = $pti?->fecha_vencimiento
                                    ? $pti->fecha_vencimiento->copy()->addMonths((int)$meses)->format('Y-m-d')
                                    : now()->addMonths((int)$meses)->format('Y-m-d');
                            @endphp
                            <tr class="ca-fila {{ $esResaltada ? 'is-resaltada' : '' }}">
                                <td class="ca-fecha">{{ $r->created_at?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <div class="ca-estudiante">
                                        @if ($r->solicitante)
                                            <a href="{{ route('titulacion.index', ['ver' => $r->solicitante_id, 'tab' => 'proceso']) }}" class="font-weight-bold text-dark">
                                                {{ $r->solicitante->apellidos }}, {{ $r->solicitante->name }}
                                            </a>
                                            <div class="small text-muted">DNI {{ $r->solicitante->dni ?: '—' }} · {{ $r->programa_estudios ?: 'Educación' }}</div>
                                        @else
                                            <span class="font-weight-bold">{{ implode('; ', $r->nombresIntegrantes()) ?: $r->correo }}</span>
                                            <div class="small text-muted">{{ $r->programa_estudios ?: '—' }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div><span class="badge badge-info">{{ $meses }} meses de prórroga</span></div>
                                    <div class="small text-dark mt-1" style="max-width:380px;">“{{ Str::limit($r->datos['motivo'] ?? $r->titulo, 100) }}”</div>
                                </td>
                                <td>
                                    <span class="ca-chip ca-chip--{{ $r->estado }}">
                                        <i class="fas {{ ['recibida' => 'fa-clock', 'atendida' => 'fa-check-circle', 'observada' => 'fa-exclamation-circle'][$r->estado] ?? 'fa-circle' }}"></i>
                                        {{ Solicitud::ESTADOS[$r->estado] ?? $r->estado }}
                                    </span>
                                    @if ($r->origen === 'formulario')
                                        <div class="small text-muted mt-1"><i class="fas fa-file-excel mr-1"></i>Excel / Google</div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm tp-btn tp-btn--claro js-ca-revisar" aria-expanded="false" aria-controls="{{ $idDetalle }}">
                                        {{ $r->estado === 'recibida' ? 'Atender / Revisar' : 'Ver detalle' }}
                                    </button>
                                </td>
                            </tr>
                            <tr id="{{ $idDetalle }}" class="ca-detalle-fila" hidden>
                                <td colspan="5" class="p-0">
                                    <div class="ca-detalle__grid p-3">
                                        <dl class="tp-carta-datos mb-0">
                                            <div class="tp-carta-ancho"><dt>Estudiante(s)</dt><dd>{{ implode('; ', $r->nombresIntegrantes()) ?: '—' }}</dd></div>
                                            <div class="tp-carta-ancho"><dt>Título del PTI</dt><dd>“{{ $r->titulo ?: '—' }}”</dd></div>
                                            <div class="tp-carta-ancho"><dt>Motivo y justificación</dt><dd class="tp-carta-mensaje">{{ $r->datos['motivo'] ?? '—' }}</dd></div>
                                            <div><dt>Prórroga pedida</dt><dd><strong>{{ $meses }} meses</strong></dd></div>
                                            @if ($r->celulares)<div><dt>Teléfono</dt><dd>{{ $r->celulares }}</dd></div>@endif
                                            @if ($r->correo)<div><dt>Correo</dt><dd>{{ $r->correo }}</dd></div>@endif
                                            @if ($r->origen === 'formulario')
                                                <div class="tp-carta-ancho"><dt>Origen</dt><dd>Formulario de Google / Excel · fila {{ $r->datos['fila'] ?? '—' }} · hoja «{{ $r->datos['hoja'] ?? '—' }}».</dd></div>
                                            @endif
                                            @if ($r->observacion)<div class="tp-carta-ancho"><dt>{{ $r->estado === 'observada' ? 'Observación enviada' : 'Mensaje / Observación' }}</dt><dd class="tp-carta-mensaje">{{ $r->observacion }}</dd></div>@endif
                                            @if ($r->atendido_en)<div><dt>{{ $r->estado === 'observada' ? 'Observada el' : 'Atendida el' }}</dt><dd>{{ $r->atendido_en->format('d/m/Y') }}{{ $r->atendidoPor ? ' · '.$r->atendidoPor->name : '' }}</dd></div>@endif
                                            @if ($r->datos['numero_rd'] ?? null)<div><dt>R.D. de Ampliación</dt><dd class="font-weight-bold text-success">{{ $r->datos['numero_rd'] }}</dd></div>@endif
                                        </dl>
                                        <div class="ca-comprobante">
                                            <span class="tp-label d-block font-weight-bold mb-2">Comprobante y Anexos</span>
                                            @forelse ($comprobantes as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2">
                                                    <i class="fas fa-file-invoice-dollar"></i><span>Comprobante de pago{{ count($comprobantes) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @empty
                                                <small class="text-muted d-block mb-2">Sin comprobante digital.</small>
                                            @endforelse

                                            @foreach ($documentos as $n => $e)
                                                <a href="{{ $e }}" target="_blank" rel="noopener" class="tp-enlace mb-2">
                                                    <i class="fas fa-file-alt"></i><span>Documento anexo{{ count($documentos) > 1 ? ' ('.($n + 1).')' : '' }}</span><i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endforeach

                                            @if ($fichaDe)
                                                <a href="{{ route('titulacion.index', ['ver' => $fichaDe, 'tab' => 'proceso']) }}" class="ca-ficha mt-2"><i class="fas fa-user-graduate mr-1"></i>Abrir la ficha de titulación</a>
                                            @endif
                                            @if ($puedeEditar)
                                                <a href="{{ route('titulacion.solicitudes.editar', $r->id) }}" class="ca-ficha"><i class="fas fa-pen mr-1"></i>Editar datos de la solicitud</a>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($r->estado === 'recibida' && $puedeAtender)
                                        <form method="POST" action="{{ route('titulacion.solicitudes-ampliacion.atender', $r->id) }}" enctype="multipart/form-data" class="tp-form ca-respuesta p-3 border-top bg-light">
                                            @csrf
                                            <span class="tp-label d-block mb-2 font-weight-bold"><i class="fas fa-stamp mr-1 text-primary"></i>Registrar R.D. de Ampliación de Plazo</span>
                                            <div class="form-row">
                                                <div class="form-group col-md-4 mb-2">
                                                    <label class="small font-weight-bold">N° de R.D. de Ampliación *</label>
                                                    <input type="text" name="numero_rd" class="form-control form-control-sm" placeholder="Ej.: RD - 0524-2026" required value="{{ $r->datos['numero_rd_excel'] ?? '' }}">
                                                </div>
                                                <div class="form-group col-md-4 mb-2">
                                                    <label class="small font-weight-bold">Fecha de la R.D.</label>
                                                    <input type="date" name="fecha_rd" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                                                </div>
                                                <div class="form-group col-md-4 mb-2">
                                                    <label class="small font-weight-bold">Nuevo vencimiento del PTI *</label>
                                                    <input type="date" name="vencimiento_ampliado" class="form-control form-control-sm" value="{{ $fechaVencSugerida }}" required>
                                                    <small class="text-muted d-block">Sugerido: prórroga de {{ $meses }} meses.</small>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6 mb-2">
                                                    <label class="small font-weight-bold">Subir PDF de la R.D.</label>
                                                    <input type="file" name="pdf" accept=".pdf,application/pdf" class="form-control-file small">
                                                </div>
                                                <div class="form-group col-md-6 mb-2">
                                                    <label class="small font-weight-bold">…o enlace a Drive / web</label>
                                                    <input type="url" name="archivo" class="form-control form-control-sm" placeholder="https://drive.google.com/…">
                                                </div>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Mensaje para el estudiante <span class="text-muted font-weight-normal">(opcional)</span></label>
                                                <textarea name="observacion" rows="2" maxlength="1500" class="form-control form-control-sm" placeholder="Ej.: Su prórroga ha sido aprobada mediante R.D. N° 0524-2026 por 6 meses adicionales."></textarea>
                                            </div>
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" name="aplicar_grupo" value="1" class="custom-control-input" id="grupo-{{ $r->id }}" checked>
                                                <label class="custom-control-label small" for="grupo-{{ $r->id }}">Aplicar también el nuevo vencimiento a los compañeros de grupo vinculados</label>
                                            </div>
                                            <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>Emitir / Registrar Ampliación</button>
                                        </form>

                                        <details class="px-3 pb-3">
                                            <summary class="small text-muted" style="cursor:pointer;"><i class="fas fa-undo mr-1"></i>Observar la solicitud (solicitar corrección o comprobante)</summary>
                                            <form method="POST" action="{{ route('titulacion.solicitudes-ampliacion.observar', $r->id) }}" class="mt-2">
                                                @csrf
                                                <textarea name="observacion" rows="2" maxlength="1500" required class="form-control form-control-sm" placeholder="Indique qué debe corregir el estudiante (ej.: El comprobante de pago no corresponde al concepto de prórroga o no es legible)."></textarea>
                                                <button type="submit" class="btn btn-sm tp-btn tp-btn--claro mt-2"><i class="fas fa-paper-plane mr-1"></i>Enviar observación</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">{{ $hayFiltros ? 'Ninguna solicitud coincide con los filtros.' : 'Todavía no hay solicitudes registradas.' }}</td></tr>
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
    if (nueva) {
        var btn = nueva.querySelector('.js-ca-revisar');
        if (btn) {
            var det = document.getElementById(btn.getAttribute('aria-controls'));
            if (det) {
                det.hidden = false;
                btn.setAttribute('aria-expanded', 'true');
            }
        }
        setTimeout(function () { nueva.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 250);
    }
    </script>
@endsection
