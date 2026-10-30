@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        $activos = $asesores->where('activo', true)->count();
        $sinCorreo = $asesores->where('activo', true)->filter(fn ($a) => blank($a->email))->count();
        // Tras un error de validación se reabre el formulario que se estaba llenando, con lo escrito.
        $editando = (int) old('editando');
        $abrirAlta = $errors->any() && ! $editando;
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-user-tie"></i> Unidad de Investigación · Titulación</div>
                <h1 class="tl-hero__titulo">Asesores</h1>
                <p class="tl-hero__sub">Asesores y jurados que no son docentes con cuenta en el sistema. No inician sesión: aparecen junto a los docentes al elegir asesor(a) o jurado en la ficha de cada persona.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
                <button type="button" class="tl-hero__btn tl-hero__btn--oro" @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion')) hidden @endunless data-as-abrir="as-alta"><i class="fas fa-plus"></i>Agregar asesor(a)</button>
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <button type="button" class="tl-stat tl-stat--link as-stat is-activo" data-as-filtro="todos">
                        <span class="tl-stat__num">{{ $asesores->count() }}</span>
                        <span class="tl-stat__eti">Registrados</span>
                    </button>
                    <button type="button" class="tl-stat tl-stat--link as-stat" data-as-filtro="activo">
                        <span class="tl-stat__num">{{ $activos }}</span>
                        <span class="tl-stat__eti">Activos</span>
                    </button>
                    @if ($asesores->count() > $activos)
                        <button type="button" class="tl-stat tl-stat--link as-stat" data-as-filtro="inactivo">
                            <span class="tl-stat__num">{{ $asesores->count() - $activos }}</span>
                            <span class="tl-stat__eti">Inactivos</span>
                        </button>
                    @endif
                    @if ($sinCorreo > 0)
                        <button type="button" class="tl-stat tl-stat--link tl-stat--oro as-stat" data-as-filtro="sin-correo" title="Sin correo no reciben los avisos al jurado">
                            <span class="tl-stat__num">{{ $sinCorreo }}</span>
                            <span class="tl-stat__eti"><i class="far fa-envelope"></i>Activos sin correo</span>
                        </button>
                    @endif
                </div>
            </div>
        </header>

        <div class="al-ui">
            @if (session('success'))
                <div class="al-aviso is-info"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
            @endif

            <form method="POST" action="{{ route('titulacion.asesores.guardar') }}" id="as-alta" class="al-card al-form" @unless ($abrirAlta) hidden @endunless>
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.asesores.guardar'))>
                @csrf
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-user-plus"></i></span>
                    <div><h2>Agregar asesor(a)</h2><p>Al guardarlo aparece en las listas de asesor y jurado.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    @include('titulacion._asesor_campos', ['a' => null, 'prefijo' => 'alta', 'conViejos' => $abrirAlta])
                    <div class="as-acciones">
                        <button type="submit" class="al-btn al-btn-petroleo"><i class="fas fa-plus"></i> Agregar</button>
                        <button type="button" class="al-btn al-btn-borde" data-as-cerrar="as-alta">Cancelar</button>
                    </div>
                </div>
            </fieldset>
</form>
        </div>

        <section class="tl-tabla-card">
            <div class="tl-filtros">
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="as-buscar">Buscar</label>
                    <div class="tl-buscar">
                        <i class="fas fa-search"></i>
                        <input type="search" id="as-buscar" class="form-control" placeholder="Nombre, DNI, correo, institución o estudiante que acompaña" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="tl-resumen">
                <span><strong id="as-conteo">{{ $asesores->count() }}</strong> <span class="tl-resumen__suave">de {{ $asesores->count() }} asesores</span></span>
            </div>

            <div class="table-responsive">
                <table class="table titulacion-tabla as-tabla">
                    <thead>
                        <tr><th>Asesor(a)</th><th>Institución</th><th>Participa en</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($asesores as $a)
                            @php
                                $abierto = $editando === $a->id;
                                $part = $participaciones[$a->id] ?? null;
                                $iniciales = mb_strtoupper(mb_substr($a->apellidos, 0, 1).mb_substr($a->nombres, 0, 1));
                                $buscable = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [$a->apellidos, $a->nombres, $a->dni, $a->email, $a->telefono, $a->institucion, $part['texto'] ?? ''])));
                            @endphp
                            <tr class="as-fila {{ $a->activo ? '' : 'is-inactivo' }}" data-as-texto="{{ $buscable }}"
                                data-as-estado="{{ $a->activo ? 'activo' : 'inactivo' }}" data-as-sin-correo="{{ $a->activo && blank($a->email) ? '1' : '0' }}">
                                <td class="as-c-persona">
                                    <div class="tl-persona">
                                        <span class="tl-avatar">{{ $iniciales ?: '?' }}</span>
                                        <span class="tl-persona__texto">
                                            <span class="tl-persona__linea">
                                                <span class="tit-c-nombre">{{ $a->apellidos }}, {{ $a->nombres }}</span>
                                                @if ($a->dni)<span class="tl-persona__dni" title="DNI">{{ $a->dni }}</span>@endif
                                            </span>
                                            @if ($a->telefono || $a->email)
                                                <span class="tl-contacto">
                                                    @if ($a->telefono)
                                                        <span class="tl-contacto__item">
                                                            <button type="button" class="tl-contacto__dato js-tl-copiar" data-copiar="{{ $a->telefono }}" title="Copiar teléfono">
                                                                <i class="fas fa-mobile-alt"></i><span>{{ $a->telefono }}</span>
                                                            </button>
                                                            @if ($wa = \App\Support\Telefono::whatsapp($a->telefono))
                                                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="tl-contacto__ir tl-contacto__ir--wa" title="Escribir por WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                                            @endif
                                                        </span>
                                                    @endif
                                                    @if ($a->email)
                                                        <span class="tl-contacto__item">
                                                            <button type="button" class="tl-contacto__dato js-tl-copiar" data-copiar="{{ $a->email }}" title="Copiar correo">
                                                                <i class="far fa-envelope"></i><span class="tl-contacto__correo" title="{{ $a->email }}">{{ $a->email }}</span>
                                                            </button>
                                                            <a href="mailto:{{ $a->email }}" class="tl-contacto__ir" title="Escribir un correo"><i class="fas fa-paper-plane"></i></a>
                                                        </span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="as-falta">Sin datos de contacto</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="as-c-inst {{ $a->institucion ? '' : 'is-vacio' }}">{{ $a->institucion ?: '—' }}</td>
                                <td class="as-c-part">
                                    @if ($part)
                                        <button type="button" class="as-rel-boton" data-as-abrir="as-rel-{{ $a->id }}" aria-controls="as-rel-{{ $a->id }}" aria-expanded="false" title="Ver a qué estudiantes acompaña">
                                            @foreach (\App\Services\Titulacion\ParticipacionAsesores::ROLES as $rol => [, , $corto])
                                                @isset($part['roles'][$rol])
                                                    <span><strong>{{ $part['roles'][$rol]['personas'] }}</strong> {{ $corto }}</span>
                                                @endisset
                                            @endforeach
                                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="as-falta">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="as-c-estado"><span class="tl-pill tl-pill--{{ $a->activo ? 'success' : 'dark' }}">{{ $a->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                <td class="as-c-accion">
                                    <button type="button" class="ft-accion as-editar" data-as-abrir="as-editar-{{ $a->id }}" aria-controls="as-editar-{{ $a->id }}" title="Editar datos de {{ $a->nombres }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                </td>
                            </tr>
                            @if ($part)
                                <tr class="as-detalle as-detalle--rel" id="as-rel-{{ $a->id }}" hidden>
                                    <td colspan="5">@include('titulacion._asesor_participacion', ['part' => $part])</td>
                                </tr>
                            @endif
                            <tr class="as-detalle" id="as-editar-{{ $a->id }}" @unless ($abierto) hidden @endunless>
                                <td colspan="5">
                                    <form method="POST" action="{{ route('titulacion.asesores.actualizar', $a->id) }}" class="al-ui al-form">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.asesores.actualizar'))>
                                        @csrf @method('PUT')
                                        <input type="hidden" name="editando" value="{{ $a->id }}">
                                        @include('titulacion._asesor_campos', ['a' => $a, 'prefijo' => 'e'.$a->id, 'conViejos' => $abierto])
                                        <div class="custom-control custom-switch as-switch">
                                            <input type="checkbox" class="custom-control-input" id="activo-{{ $a->id }}" name="activo" value="1" @checked($abierto ? old('activo') : $a->activo)>
                                            <label class="custom-control-label" for="activo-{{ $a->id }}">Activo: aparece en las listas de asesor y jurado</label>
                                            <small>Si lo desactivas se conserva donde ya fue asignado.</small>
                                        </div>
                                        <div class="as-acciones">
                                            <button type="submit" class="al-btn al-btn-petroleo"><i class="fas fa-save"></i> Guardar cambios</button>
                                            <button type="button" class="al-btn al-btn-borde" data-as-cerrar="as-editar-{{ $a->id }}">Cancelar</button>
                                        </div>
                                    </fieldset>
</form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="al-ui">
                <div class="al-vacio" id="as-vacio" @if ($asesores->isNotEmpty()) hidden @endif>
                    <i class="fas fa-user-tie"></i>
                    <span id="as-vacio-texto">{{ $asesores->isEmpty() ? 'Todavía no hay asesores registrados. Los docentes con cuenta ya aparecen en las listas sin registrarlos aquí.' : 'Ningún asesor coincide con la búsqueda.' }}</span>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filas = Array.prototype.slice.call(document.querySelectorAll('.as-fila'));
            var buscador = document.getElementById('as-buscar');
            var conteo = document.getElementById('as-conteo');
            var vacio = document.getElementById('as-vacio');
            var filtro = 'todos';
            var normalizar = function (t) { return t.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim(); };

            function aplicar() {
                var q = normalizar(buscador.value);
                var visibles = 0;
                filas.forEach(function (fila) {
                    var pasa = (!q || fila.dataset.asTexto.indexOf(q) !== -1)
                        && (filtro === 'todos' || (filtro === 'sin-correo' ? fila.dataset.asSinCorreo === '1' : fila.dataset.asEstado === filtro));
                    fila.hidden = !pasa;
                    // Sus paneles (detalle y edición) se esconden con la fila, sin perder lo escrito.
                    for (var d = fila.nextElementSibling; d && d.classList.contains('as-detalle'); d = d.nextElementSibling) {
                        if (!pasa) { d.hidden = true; }
                    }
                    if (!pasa) { fila.querySelectorAll('[aria-expanded]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); }); }
                    if (pasa) { visibles++; }
                });
                conteo.textContent = visibles;
                vacio.hidden = visibles > 0;
            }

            buscador.addEventListener('input', aplicar);
            document.querySelectorAll('[data-as-filtro]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    filtro = boton.dataset.asFiltro;
                    document.querySelectorAll('[data-as-filtro]').forEach(function (b) { b.classList.toggle('is-activo', b === boton); });
                    aplicar();
                });
            });

            document.addEventListener('click', function (e) {
                var abrir = e.target.closest('[data-as-abrir]');
                var cerrar = e.target.closest('[data-as-cerrar]');
                if (abrir) {
                    var caja = document.getElementById(abrir.dataset.asAbrir);
                    caja.hidden = abrir.closest('.as-fila') ? !caja.hidden : false;
                    if (abrir.hasAttribute('aria-expanded')) { abrir.setAttribute('aria-expanded', caja.hidden ? 'false' : 'true'); }
                    if (!caja.hidden) {
                        var campo = caja.querySelector('input:not([type=hidden])');
                        if (campo) { campo.focus(); }
                    }
                }
                if (cerrar) {
                    var form = cerrar.closest('form');
                    form.reset();
                    document.getElementById(cerrar.dataset.asCerrar).hidden = true;
                }
                var copiar = e.target.closest('.js-tl-copiar');
                if (copiar && navigator.clipboard) {
                    navigator.clipboard.writeText(copiar.dataset.copiar).then(function () {
                        var icono = copiar.querySelector('i');
                        var antes = icono.className;
                        var titulo = copiar.getAttribute('title');
                        copiar.classList.add('is-copiado');
                        icono.className = 'fas fa-check';
                        copiar.setAttribute('title', '¡Copiado!');
                        setTimeout(function () {
                            copiar.classList.remove('is-copiado');
                            icono.className = antes;
                            copiar.setAttribute('title', titulo);
                        }, 1600);
                    });
                }
            });
        });
    </script>
@endsection
