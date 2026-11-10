{{-- Registros de solicitudes de carta de aceptación de la IE (CartaAceptacionController::registros).
     Del más reciente al más antiguo, con filtros. Secretaría revisa los datos y el comprobante y envía la
     respuesta de aprobación (correo) u observa; Investigación solo los ve. ?resaltar={lote} (el aviso de la
     campanita) resalta y abre esa solicitud. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\CartaAceptacion;
        use App\Services\Titulacion\DiasHabiles;
        $total = $conteo->sum();
        $url = fn (array $extra) => route('titulacion.cartas.index', array_filter(array_merge(request()->except('page', 'resaltar'), $extra), fn ($v) => $v !== null && $v !== ''));
        $hayFiltros = collect($filtros)->except('resaltar')->filter(fn ($v) => filled($v))->isNotEmpty();
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-stamp"></i> Secretaría · Titulación</div>
                <h1 class="tl-hero__titulo">Cartas de aceptación</h1>
                <p class="tl-hero__sub">Solicitudes de los estudiantes para la Institución Educativa donde harán su investigación. Revise los datos y el comprobante y envíe la respuesta de aprobación (plazo: {{ CartaAceptacion::DIAS_RESPUESTA }} días hábiles).</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
                @if (\App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.cartas.importar'))
                    <a href="{{ route('titulacion.cartas.importar') }}" class="tl-hero__btn" title="Registrar las solicitudes hechas con el formulario de Google (Excel de respuestas)"><i class="fas fa-file-import"></i>Importar del formulario de Google</a>
                @endif
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <a href="{{ $url(['estado' => null]) }}" class="tl-stat tl-stat--link {{ empty($filtros['estado']) ? 'is-activo' : '' }}">
                        <span class="tl-stat__num">{{ $total }}</span><span class="tl-stat__eti">Todas</span>
                    </a>
                    @foreach (CartaAceptacion::ESTADOS as $clave => $nombre)
                        <a href="{{ $url(['estado' => $clave]) }}" class="tl-stat tl-stat--link {{ $clave === 'enviada' ? 'tl-stat--oro' : '' }} {{ ($filtros['estado'] ?? null) === $clave ? 'is-activo' : '' }}">
                            <span class="tl-stat__num">{{ $conteo[$clave] ?? 0 }}</span>
                            <span class="tl-stat__eti">{{ $clave === 'enviada' ? 'Por responder' : ($clave === 'emitida' ? 'Aprobadas' : 'Observadas') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        <div class="al-ui">
            @if (session('success'))
                <div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
            @endif
            @if (session('error'))
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
            @endif
            @unless ($puedeGestionar)
                <div class="al-aviso is-info"><i class="fas fa-eye"></i><div>Vista de solo lectura: las solicitudes las gestiona Secretaría.</div></div>
            @endunless
        </div>

        <section class="tl-tabla-card">
            <form method="GET" action="{{ route('titulacion.cartas.index') }}" class="tl-filtros ca-filtros">
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="ca-q">Buscar</label>
                    <div class="tl-buscar">
                        <i class="fas fa-search"></i>
                        <input type="search" id="ca-q" name="q" value="{{ $filtros['q'] ?? '' }}" class="form-control" placeholder="Nombre, DNI, expediente, institución, director, título, correo…">
                    </div>
                </div>
                <div class="tl-filtro">
                    <label for="ca-estado">Estado</label>
                    <select id="ca-estado" name="estado" class="form-control">
                        <option value="">Todos</option>
                        @foreach (CartaAceptacion::ESTADOS as $clave => $nombre)
                            <option value="{{ $clave }}" @selected(($filtros['estado'] ?? '') === $clave)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="ca-formacion">Programa</label>
                    <select id="ca-formacion" name="formacion" class="form-control">
                        <option value="">Todos</option>
                        @foreach (CartaAceptacion::PROGRAMAS_FORMACION as $clave => $nombre)
                            <option value="{{ $clave }}" @selected(($filtros['formacion'] ?? '') === $clave)>{{ $clave }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="ca-origen">Origen</label>
                    <select id="ca-origen" name="origen" class="form-control">
                        <option value="">Todos</option>
                        @foreach (CartaAceptacion::ORIGENES as $clave => $nombre)
                            <option value="{{ $clave }}" @selected(($filtros['origen'] ?? '') === $clave)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="ca-desde">Recibidas desde</label>
                    <input type="date" id="ca-desde" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="form-control">
                </div>
                <div class="tl-filtro">
                    <label for="ca-hasta">hasta</label>
                    <input type="date" id="ca-hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="form-control">
                </div>
                <div class="tl-filtro ca-filtros__botones">
                    <button type="submit" class="btn tp-btn"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                    @if ($hayFiltros)<a href="{{ route('titulacion.cartas.index') }}" class="btn tp-btn tp-btn--claro">Limpiar</a>@endif
                </div>
            </form>

            <div class="tl-resumen">
                <span><strong>{{ $registros->total() }}</strong> <span class="tl-resumen__suave">{{ $registros->total() === 1 ? 'solicitud' : 'solicitudes' }}{{ $hayFiltros ? ' con estos filtros' : '' }} · de la más reciente a la más antigua</span></span>
            </div>

            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead>
                        <tr><th>Recibida</th><th>Estudiante</th><th>Institución Educativa</th><th>Programa</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            @php
                                $resaltada = $resaltar && $resaltar === $r->lote;
                                $limite = $r->fechaLimite();
                                $quedan = $r->estado === 'enviada' ? DiasHabiles::restantes($limite) : null;
                                $s = $r->solicitante;
                                $esImagen = in_array($r->extensionComprobante(), ['jpg', 'jpeg', 'png'], true);
                                $idFila = 'ca-'.$r->id;
                            @endphp
                            <tr class="ca-fila {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}">
                                <td class="ca-c-fecha">
                                    <strong>{{ $r->created_at->format('d/m/Y') }}</strong>
                                    <small>{{ $r->created_at->format('H:i') }}</small>
                                    @if ($quedan !== null)
                                        <span class="ca-plazo {{ $quedan < 0 ? 'is-vencido' : ($quedan === 0 ? 'is-hoy' : '') }}">
                                            {{ $quedan < 0 ? 'Venció el '.$limite->format('d/m') : ($quedan === 0 ? 'Vence hoy' : 'Hasta el '.$limite->format('d/m')) }}
                                        </span>
                                    @endif
                                    @if ($resaltada)<span class="ca-nuevo">Nueva</span>@endif
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        <strong>{{ $s?->apellidos }}, {{ $s?->name }}</strong>
                                        <small>DNI {{ $s?->dni ?: '—' }}@if ($r->seguimiento?->numero_expediente) · Exp. {{ \App\Services\Titulacion\Expediente::mostrar($r->seguimiento->numero_expediente) }}@endif</small>
                                        <small class="ca-grupo" title="Grupo de investigación">{{ \Illuminate\Support\Str::limit($r->grupo, 90) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        <strong>{{ $r->institucion }}</strong>
                                        @if ($r->director)<small>Dir.: {{ $r->director }}</small>@endif
                                    </div>
                                </td>
                                <td><small>{{ $r->programa_estudios ?: '—' }}</small>@if ($r->programa_formacion)<span class="ca-chip">{{ $r->programa_formacion }}</span>@endif</td>
                                <td>
                                    <span class="ca-estado ca-estado--{{ $r->estado }}">{{ CartaAceptacion::ESTADOS[$r->estado] ?? $r->estado }}</span>
                                    @if ($r->origen !== 'sistema')<span class="ca-origen" title="{{ CartaAceptacion::ORIGENES[$r->origen] ?? '' }}">{{ $r->origen === 'formulario' ? 'Formulario de Google' : 'Registrada a mano' }}</span>@endif
                                    @if ($r->atendido_en)<small class="d-block text-muted mt-1">{{ $r->atendido_en->format('d/m/Y') }}{{ $r->atendidoPor ? ' · '.$r->atendidoPor->name : '' }}</small>@endif
                                </td>
                                <td class="ca-acciones">
                                    @if ($r->comprobante)
                                        <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'comprobante']) }}" target="_blank" rel="noopener" class="btn btn-sm tp-btn tp-btn--claro" title="Abrir el comprobante en otra pestaña"><i class="fas fa-file-invoice-dollar"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-sm tp-btn js-ca-revisar" aria-expanded="{{ $resaltada ? 'true' : 'false' }}" aria-controls="{{ $idFila }}-det">
                                        <i class="fas fa-chevron-down mr-1"></i>{{ $puedeGestionar && $r->estado === 'enviada' ? 'Revisar' : 'Ver' }}
                                    </button>
                                </td>
                            </tr>
                            <tr class="ca-detalle {{ $resaltada ? 'is-resaltada' : '' }}" id="{{ $idFila }}-det" @unless ($resaltada) hidden @endunless>
                                <td colspan="6">
                                    <div class="ca-detalle__grid">
                                        <dl class="tp-carta-datos mb-0">
                                            <div class="tp-carta-ancho"><dt>Grupo de investigación</dt><dd>{{ $r->grupo }}</dd></div>
                                            @if ($r->titulo)<div class="tp-carta-ancho"><dt>Título del trabajo</dt><dd>“{{ $r->titulo }}”</dd></div>@endif
                                            <div><dt>Institución Educativa</dt><dd>{{ $r->institucion }}</dd></div>
                                            @if ($r->director)<div><dt>Director(a)</dt><dd>{{ $r->director }}</dd></div>@endif
                                            @if ($r->correo)<div><dt>Correo institucional</dt><dd>{{ $r->correo }}</dd></div>@endif
                                            @if ($r->celular)<div><dt>Celular</dt><dd><a href="https://wa.me/51{{ preg_replace('/\D/', '', $r->celular) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp mr-1"></i>{{ $r->celular }}</a></dd></div>@endif
                                            @if ($r->programa_estudios)<div><dt>Programa de Estudios</dt><dd>{{ $r->programa_estudios }}</dd></div>@endif
                                            @if ($r->programa_formacion)<div><dt>Programa de Formación</dt><dd>{{ CartaAceptacion::PROGRAMAS_FORMACION[$r->programa_formacion] ?? $r->programa_formacion }}</dd></div>@endif
                                            <div><dt>Declaración de veracidad</dt><dd>{{ $r->declaracion ? 'Aceptada' : '—' }}</dd></div>
                                            {{-- Lo que traía el formulario antiguo y cómo se reconoció a la persona al importar. --}}
                                            @foreach (['codigo_modular' => 'Código modular de la IE', 'direccion' => 'Dirección de la IE', 'ciclo' => 'Ciclo (al solicitar)'] as $k => $eti)
                                                @if (filled($r->datos_extra[$k] ?? null))<div><dt>{{ $eti }}</dt><dd>{{ $r->datos_extra[$k] }}</dd></div>@endif
                                            @endforeach
                                            @if ($r->origen === 'formulario')
                                                <div class="tp-carta-ancho"><dt>Origen</dt><dd>Formulario de Google · hoja «{{ $r->datos_extra['hoja'] ?? '—' }}», fila {{ $r->datos_extra['fila'] ?? '—' }}
                                                    · reconocida por {{ \Illuminate\Support\Str::lower(\App\Services\Titulacion\ImportarCartasFormulario::METODOS[$r->datos_extra['reconocido_por'] ?? ''] ?? '—') }}. Aprobada fuera del sistema (sin correo).</dd></div>
                                            @endif
                                            @if ($r->observacion && $r->estado !== 'enviada')
                                                <div class="tp-carta-ancho"><dt>{{ $r->estado === 'observada' ? 'Observación enviada' : ($r->origen === 'sistema' ? 'Respuesta enviada' : 'Nota') }}</dt><dd class="tp-carta-mensaje">{{ $r->observacion }}</dd></div>
                                            @endif
                                        </dl>
                                        <div class="ca-comprobante">
                                            <span class="tp-label d-block">Comprobante de pago</span>
                                            @if (! $r->comprobante)
                                                <small class="text-muted">Sin comprobante (registrada fuera del sistema).</small>
                                            @elseif ($r->comprobanteEsEnlace())
                                                <a href="{{ $r->comprobante }}" target="_blank" rel="noopener" class="tp-enlace mb-0"><i class="fab fa-google-drive"></i><span>Abrir el comprobante (Drive)</span><i class="fas fa-external-link-alt"></i></a>
                                            @elseif ($esImagen)
                                                <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'comprobante']) }}" target="_blank" rel="noopener" title="Abrir en otra pestaña">
                                                    <img src="{{ route('mi-titulacion.carta.archivo', [$r->id, 'comprobante']) }}" alt="Comprobante de pago" loading="lazy">
                                                </a>
                                            @else
                                                <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'comprobante']) }}" target="_blank" rel="noopener" class="tp-enlace mb-0"><i class="fas fa-file-pdf"></i><span>Abrir el comprobante (PDF)</span><i class="fas fa-external-link-alt"></i></a>
                                            @endif
                                            @if ($r->carta)
                                                <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'carta']) }}" target="_blank" rel="noopener" class="tp-enlace mt-2 mb-0"><i class="far fa-file-word"></i><span>Carta de Presentación{{ $r->numero_carta ? " N° 0".$r->numero_carta : "" }}</span><i class="fas fa-download"></i></a>
                                                <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'modelo']) }}" class="tp-enlace mt-2 mb-0"><i class="far fa-file-word"></i><span>Modelo de Carta de Aceptación</span><i class="fas fa-download"></i></a>
                                            @endif
                                            @if ($r->seguimiento)
                                                <a href="{{ route('titulacion.index', ['ver' => $r->seguimiento->user_id, 'tab' => 'proceso', 'paso' => 'pti_carta']) }}" class="ca-ficha"><i class="fas fa-user-graduate mr-1"></i>Abrir la ficha de titulación</a>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($r->estado === 'enviada' && $puedeGestionar)
                                        <form method="POST" action="{{ route('titulacion.cartas.atender', $r->id) }}" enctype="multipart/form-data" class="tp-form js-carta-form ca-respuesta">
                                            @csrf
                                            <span class="tp-label d-block">Respuesta de Secretaría</span>
                                            <div class="tp-enlace-opciones mb-2">
                                                <label class="tp-enlace-opcion"><input type="radio" name="accion" value="aprobar" checked><span><i class="fas fa-check-circle"></i> Datos y comprobante correctos: aprobar y enviar correo</span></label>
                                                <label class="tp-enlace-opcion"><input type="radio" name="accion" value="observar"><span><i class="fas fa-undo"></i> Observar la solicitud</span></label>
                                            </div>
                                            <div class="js-carta-aprobar">
                                                @php $cp = \App\Services\Titulacion\CartaPresentacion::sugeridos($r); $vc = fn ($k) => old($k, $cp[$k] ?? ''); @endphp
                                                <span class="tp-label d-block mb-1"><i class="far fa-file-word mr-1"></i>Carta de Presentación (Word, se llena con su plantilla)</span>
                                                <div class="ca-carta-grid">
                                                    <label>N° de carta
                                                        <span class="ca-numero">0<input type="text" name="numero_carta" value="{{ $vc('numero_carta') }}" class="form-control form-control-sm" inputmode="numeric" maxlength="4" required>-<span class="js-ca-anio">{{ substr($vc('fecha_carta'), 0, 4) }}</span></span>
                                                    </label>
                                                    <label>Fecha<input type="date" name="fecha_carta" value="{{ $vc('fecha_carta') }}" class="form-control form-control-sm js-ca-fecha" required></label>
                                                    <label>Dirigida a
                                                        <select name="destinatario_genero" class="form-control form-control-sm" required>
                                                            <option value="">— Elegir —</option>
                                                            <option value="f" @selected($vc('destinatario_genero') === 'f')>Señora · Directora</option>
                                                            <option value="m" @selected($vc('destinatario_genero') === 'm')>Señor · Director</option>
                                                        </select>
                                                    </label>
                                                    <label class="ca-ancho">Nombre del director(a)<input type="text" name="destinatario" value="{{ $vc('destinatario') }}" class="form-control form-control-sm" maxlength="200" required></label>
                                                    <label>Antes de la IE<input type="text" name="art_ie" value="{{ $vc('art_ie') }}" class="form-control form-control-sm" maxlength="20" placeholder="de la"></label>
                                                    <label class="ca-ancho">Institución Educativa<input type="text" name="institucion_carta" value="{{ $vc('institucion_carta') }}" class="form-control form-control-sm" maxlength="300" required></label>
                                                    <label>Periodo académico<input type="text" name="periodo" value="{{ $vc('periodo') }}" class="form-control form-control-sm" maxlength="20" required></label>
                                                    <label>Estudiantes (concordancia)
                                                        <select name="genero" class="form-control form-control-sm">
                                                            @foreach (\App\Services\Titulacion\PlantillasRd::GENEROS as $clave => $texto)
                                                                <option value="{{ $clave }}" @selected($vc('genero') === $clave)>{{ $texto }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label class="ca-total">Título de la investigación<textarea name="titulo_carta" rows="2" class="form-control form-control-sm" maxlength="1000" required>{{ $vc('titulo_carta') }}</textarea></label>
                                                </div>
                                                <div class="form-group mt-2 mb-2">
                                                    <label class="tp-label" for="{{ $idFila }}-msg">Correo de aprobación (llega a cada integrante, con la Carta de Presentación y el modelo de Carta de Aceptación adjuntos)</label>
                                                    <textarea name="mensaje" id="{{ $idFila }}-msg" rows="7" maxlength="5000" class="form-control" required>{{ old('mensaje', $r->mensajeAprobacion()) }}</textarea>
                                                </div>
                                                <div class="ca-adjuntos">
                                                    <span><i class="far fa-file-word"></i> Carta de Presentación N° 0<span class="js-ca-num">{{ $vc('numero_carta') }}</span>-<span class="js-ca-anio">{{ substr($vc('fecha_carta'), 0, 4) }}</span>.docx</span>
                                                    <a href="{{ route('mi-titulacion.carta.archivo', [$r->id, 'modelo']) }}"><i class="far fa-file-word"></i> Modelo de Carta de Aceptación.docx</a>
                                                    <button type="submit" name="accion" value="previa" class="btn btn-sm tp-btn tp-btn--claro" formnovalidate title="Descarga la Carta de Presentación llena, sin enviar nada"><i class="fas fa-eye mr-1"></i>Vista previa (Word)</button>
                                                </div>
                                            </div>
                                            <div class="js-carta-observar" hidden>
                                                <div class="form-group mb-2">
                                                    <label class="tp-label" for="{{ $idFila }}-obs">¿Qué debe corregir el estudiante?</label>
                                                    <textarea name="observacion" id="{{ $idFila }}-obs" rows="3" maxlength="1500" class="form-control" placeholder="Ej.: El comprobante no corresponde al derecho de trámite."></textarea>
                                                </div>
                                            </div>
                                            <div class="small text-muted mb-2"><i class="fas fa-users mr-1"></i>Cada integrante recibe un aviso en el sistema y el correo. Con la aprobación se abre el TI · Bachiller.</div>
                                            <button type="submit" class="btn tp-btn"><i class="fas fa-paper-plane mr-1"></i><span class="js-carta-boton" data-aprobar="Aprobar y enviar correo" data-observar="Enviar observación">Aprobar y enviar correo</span></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">{{ $hayFiltros ? 'Ninguna solicitud coincide con los filtros.' : 'Todavía no hay solicitudes.' }}</td></tr>
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
    (function () {
        // «Revisar / Ver» despliega el detalle de la solicitud bajo su fila.
        document.addEventListener('click', function (e) {
            var b = e.target.closest('.js-ca-revisar');
            if (!b) return;
            var det = document.getElementById(b.getAttribute('aria-controls'));
            det.hidden = !det.hidden;
            b.setAttribute('aria-expanded', det.hidden ? 'false' : 'true');
        });
        // Aprobar / Observar muestra el campo que corresponde.
        document.addEventListener('change', function (e) {
            var r = e.target.closest('.js-carta-form input[name="accion"]');
            if (!r) return;
            var f = r.closest('form'), obs = r.value === 'observar';
            var aprobar = f.querySelector('.js-carta-aprobar');
            aprobar.hidden = obs;
            f.querySelector('.js-carta-observar').hidden = !obs;
            // Lo oculto no se valida ni se envía.
            aprobar.querySelectorAll('input, select, textarea, button').forEach(function (c) { c.disabled = obs; });
            f.querySelector('textarea[name="observacion"]').required = obs;
            var t = f.querySelector('.js-carta-boton');
            t.textContent = obs ? t.dataset.observar : t.dataset.aprobar;
        });
        // N° y año de la carta: se ven en el nombre del adjunto mientras se escriben.
        document.addEventListener('input', function (e) {
            var f = e.target.closest('.js-carta-form');
            if (!f) return;
            if (e.target.name === 'numero_carta') f.querySelectorAll('.js-ca-num').forEach(function (s) { s.textContent = e.target.value; });
            if (e.target.name === 'fecha_carta' && e.target.value) f.querySelectorAll('.js-ca-anio').forEach(function (s) { s.textContent = e.target.value.slice(0, 4); });
        });
        // Desde la campanita: lleva a la solicitud nueva.
        var nueva = document.querySelector('.ca-fila.is-resaltada');
        if (nueva) setTimeout(function () { nueva.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 250);
    })();
    </script>
@endsection
