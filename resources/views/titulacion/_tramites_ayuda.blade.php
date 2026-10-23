{{-- Ayuda paso a paso del tramo en curso (GuiaTramites): solo los trámites que el alumno
     tiene que hacer AHORA para avanzar, con pago en Caja Cusco, formulario, descargas, qué
     adjuntar y qué recibirá. Funciona sin el JS de Bootstrap (/egresado): acordeón con
     <details>; "Ya hice este trámite" se recuerda solo en este navegador (localStorage).
     Espera $bloque (GuiaTramites::actual/bloque) y $clavePersona (para recordar lo marcado). --}}
@php
    $tramites = $bloque['tramites'] ?? [];
    $conceptosPdf = config('titulacion.conceptos_pago_pdf');
    $contactos = collect(config('titulacion.contactos', []))->filter(fn ($c) => \App\Support\Telefono::whatsapp($c[1] ?? null))->all();
    $url = fn (string $destino) => \Illuminate\Support\Str::startsWith($destino, ['http://', 'https://'])
        ? $destino
        : (\Illuminate\Support\Facades\Route::has($destino) ? route($destino) : asset($destino));
@endphp
@if (! empty($tramites))
    @once
        <link rel="stylesheet" href="{{ asset('css/pk-tramites.css') }}?v={{ @filemtime(public_path('css/pk-tramites.css')) }}">
    @endonce

    <section class="tr-ayuda" data-tr-ayuda="{{ $clavePersona }}-{{ $bloque['clave'] }}" aria-labelledby="tr-titulo-{{ $clavePersona }}">
        <header class="tr-ayuda__cab">
            <span class="tr-ayuda__icono" aria-hidden="true"><i class="fas fa-hands-helping"></i></span>
            <div class="tr-ayuda__textos">
                <div class="tr-ayuda__kicker">Qué hacer ahora</div>
                <h3 class="tr-ayuda__titulo" id="tr-titulo-{{ $clavePersona }}">{{ $bloque['titulo'] }}</h3>
                <p class="tr-ayuda__sub">
                    @if (count($tramites) > 1)
                        Sigue estos {{ count($tramites) }} trámites en orden.
                    @else
                        Sigue este trámite.
                    @endif
                    Cuando la Unidad de Investigación registre el resultado, aquí aparecerá tu siguiente paso.
                </p>
            </div>
            @if (count($tramites) > 1)
                <span class="tr-ayuda__progreso" data-tr-progreso hidden></span>
            @endif
        </header>

        <div class="tr-lista">
            @foreach ($tramites as $clave => $t)
                @php [$monto, $codigo, $concepto] = $t['pago'] ?? [null, null, null]; @endphp
                <details class="tr-tramite" data-tr-clave="{{ $clave }}" @if ($loop->first) open @endif>
                    <summary class="tr-tramite__cab">
                        <span class="tr-tramite__n" aria-hidden="true"><span>{{ $loop->iteration }}</span><i class="fas fa-check"></i></span>
                        <span class="tr-tramite__titulo">
                            {{ $t['titulo'] }}
                            @if (! empty($t['opcional'])) <span class="tr-chip tr-chip--suave">Recomendado</span> @endif
                            <span class="tr-hecho-texto">· hecho</span>
                        </span>
                        <span class="tr-tramite__chips">
                            @if (! empty($t['modalidad'])) <span class="tr-chip tr-chip--suave"><i class="fas {{ $t['modalidad'] === 'presencial' ? 'fa-building' : 'fa-laptop' }}"></i>{{ ucfirst($t['modalidad']) }}</span> @endif
                            @if ($monto) <span class="tr-chip">S/ {{ $monto }}</span> @endif
                        </span>
                        <i class="fas fa-chevron-down tr-tramite__chev" aria-hidden="true"></i>
                    </summary>

                    <div class="tr-tramite__cuerpo">
                        @if (! empty($t['opcional'])) <p class="tr-nota tr-nota--info"><i class="fas fa-lightbulb"></i>{{ $t['opcional'] }}</p> @endif

                        @if ($monto)
                            <div class="tr-pago">
                                <div class="tr-pago__eti"><i class="fas fa-receipt"></i>Paga en Caja Cusco <span>(pago ordinario)</span></div>
                                <dl class="tr-pago__datos">
                                    <div><dt>Monto</dt><dd>S/ {{ $monto }}</dd></div>
                                    <div><dt>Código</dt><dd>{{ $codigo }}</dd></div>
                                    <div class="tr-pago__concepto">
                                        <dt>Concepto</dt>
                                        <dd>
                                            {{ $concepto }}
                                            <button type="button" class="tr-copiar" data-tr-copiar="{{ $concepto }}" title="Copiar concepto" aria-label="Copiar el concepto de pago"><i class="far fa-copy"></i></button>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        @endif

                        @if (! empty($t['pasos']))
                            <ol class="tr-pasos">
                                @foreach ($t['pasos'] as $paso) <li>{{ $paso }}</li> @endforeach
                            </ol>
                        @endif

                        @if (! empty($t['formulario']) || ! empty($t['descargas']) || ! empty($t['enlace']))
                            <div class="tr-acciones">
                                @if (! empty($t['formulario']))
                                    <a class="btn btn-sm btn-primary" href="{{ $url($t['formulario'][1]) }}" target="_blank" rel="noopener">
                                        <i class="fas fa-clipboard-list mr-1"></i>{{ $t['formulario'][0] }}<i class="fas fa-external-link-alt fa-xs ml-1"></i>
                                    </a>
                                @endif
                                @foreach ($t['descargas'] ?? [] as [$texto, $ruta])
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $url($ruta) }}" download>
                                        <i class="fas fa-file-download mr-1"></i>{{ $texto }}
                                    </a>
                                @endforeach
                                @if (! empty($t['enlace']))
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $url($t['enlace'][1]) }}" target="_blank" rel="noopener">
                                        <i class="fas fa-external-link-alt mr-1"></i>{{ $t['enlace'][0] }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if (! empty($t['adjuntar']))
                            <div class="tr-adjuntar">
                                <div class="tr-sub">Adjunta en el formulario</div>
                                <ul>
                                    @foreach ($t['adjuntar'] as $doc) <li><i class="fas fa-paperclip"></i>{{ $doc }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! empty($t['resultado']) || ! empty($t['plazo']))
                            <p class="tr-resultado">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>
                                    {{ $t['resultado'] ?? '' }}
                                    @if (! empty($t['plazo'])) <strong class="d-block">Plazo: {{ $t['plazo'] }}</strong> @endif
                                </span>
                            </p>
                        @endif
                        @if (! empty($t['nota'])) <p class="tr-nota"><i class="fas fa-exclamation-triangle"></i>{{ $t['nota'] }}</p> @endif

                        <label class="tr-marcar">
                            <input type="checkbox" data-tr-marcar>
                            <span>Ya hice este trámite</span>
                        </label>
                    </div>
                </details>
            @endforeach
        </div>

        <footer class="tr-ayuda__pie">
            @if ($conceptosPdf)
                <a href="{{ asset($conceptosPdf) }}" target="_blank" rel="noopener"><i class="fas fa-file-invoice-dollar"></i>Conceptos de pago de Caja Cusco</a>
            @endif
            @foreach ($contactos as [$area, $numero])
                <a href="{{ \App\Support\Telefono::whatsapp($numero) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i>{{ $area }} · {{ $numero }}</a>
            @endforeach
        </footer>
    </section>

    @once
        <script>
            (function () {
                // "Ya hice este trámite": solo una ayuda visual para el alumno, guardada en su navegador.
                function guardar(clave, valor) { try { valor ? localStorage.setItem(clave, '1') : localStorage.removeItem(clave); } catch (e) {} }
                function leer(clave) { try { return localStorage.getItem(clave) === '1'; } catch (e) { return false; } }

                function actualizar(seccion) {
                    var tramites = seccion.querySelectorAll('.tr-tramite');
                    var hechos = seccion.querySelectorAll('.tr-tramite.is-hecho').length;
                    var progreso = seccion.querySelector('[data-tr-progreso]');
                    if (progreso) {
                        progreso.hidden = hechos === 0;
                        progreso.textContent = hechos + ' de ' + tramites.length + ' hechos';
                    }
                }

                function iniciar(seccion) {
                    var base = 'pk-tramite:' + seccion.dataset.trAyuda + ':';
                    var abierto = false;
                    seccion.querySelectorAll('.tr-tramite').forEach(function (d) {
                        var caja = d.querySelector('[data-tr-marcar]');
                        var hecho = leer(base + d.dataset.trClave);
                        caja.checked = hecho;
                        d.classList.toggle('is-hecho', hecho);
                        // Abierto: el primero que falta marcar.
                        d.open = !hecho && !abierto;
                        if (d.open) abierto = true;
                        caja.addEventListener('change', function () {
                            guardar(base + d.dataset.trClave, caja.checked);
                            d.classList.toggle('is-hecho', caja.checked);
                            if (caja.checked) {
                                d.open = false;
                                var siguiente = d.nextElementSibling;
                                while (siguiente && siguiente.classList.contains('is-hecho')) siguiente = siguiente.nextElementSibling;
                                if (siguiente) { siguiente.open = true; siguiente.querySelector('summary').focus(); }
                            }
                            actualizar(seccion);
                        });
                    });
                    actualizar(seccion);
                }

                document.addEventListener('click', function (e) {
                    var boton = e.target.closest('[data-tr-copiar]');
                    if (!boton || !navigator.clipboard) return;
                    navigator.clipboard.writeText(boton.dataset.trCopiar).then(function () {
                        boton.classList.add('is-copiado');
                        boton.innerHTML = '<i class="fas fa-check"></i>';
                        setTimeout(function () { boton.classList.remove('is-copiado'); boton.innerHTML = '<i class="far fa-copy"></i>'; }, 1600);
                    });
                });

                function todo() { document.querySelectorAll('[data-tr-ayuda]').forEach(iniciar); }
                document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', todo) : todo();
            })();
        </script>
    @endonce
@endif
