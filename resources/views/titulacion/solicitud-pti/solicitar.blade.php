{{-- Solicitud de aprobación del Plan de Trabajo de Investigación y asignación de asesor (MiSolicitudPtiController).
     Primer paso del proceso: la envía el estudiante o egresado que todavía no tiene su PTI registrado. Mismas
     preguntas que el formulario de Google. Llega a la bandeja del Área de Investigación (aviso en la campanita).
     Diseño de la solicitud de carta (clases cr-* y al-* de css/pk-ui.css). --}}
@extends($layout)
@section('titulo', 'Solicitud de aprobación del PTI')
@section('contenido')
@include('alumnos.partials.ui')
@php
    use App\Models\Titulacion\Solicitud;
    use App\Services\Titulacion\TramitesExtraordinarios as Tramites;
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $estado = $ultima?->estado;
    $bloqueo = $bloqueo ?? null;
    $mostrarForm = ! $bloqueo && (! $ultima || $estado === 'observada');
    $tramite = Tramites::tipo('aprobacion_pti');
    $estados = ['recibida' => ['is-info', 'fa-hourglass-half', 'En revisión'], 'atendida' => ['is-ok', 'fa-check-circle', 'Atendida'], 'observada' => ['is-warn', 'fa-exclamation-circle', 'Observada']];
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}#titulacion" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi intranet</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Paso 1 · Plan de Trabajo de Investigación</span>
                <h1>Solicitud de aprobación del PTI y asignación de asesor</h1>
                <p class="al-hero-sub">Con esta solicitud inicias tu proceso de titulación. El Área de Investigación revisará tu Plan de Trabajo de Investigación y tu comprobante, y registrará tu proceso. La presente solicitud tiene carácter de declaración jurada.</p>
            </div>
            @if ($ultima)
                <span class="al-badge {{ $estados[$estado][0] }} cr-hero-estado"><i class="fas {{ $estados[$estado][1] }}"></i> {{ $estados[$estado][2] }}</span>
            @endif
        </div>
    </section>

    @if (session('success'))<div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>@endif
    @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>@endif

    @if ($bloqueo)
        <div class="al-aviso is-warn">
            <i class="fas fa-lock"></i>
            <div><strong>Todavía no puedes enviar esta solicitud:</strong> {{ $bloqueo }}. Si crees que es un error, comunícate con el Área de Investigación (<a href="https://wa.me/51972577170" target="_blank" rel="noopener">WhatsApp +51 972 577 170</a>).</div>
        </div>
    @endif

    {{-- ── Solicitud ya enviada (en revisión o atendida) ── --}}
    @if ($ultima && $estado !== 'observada')
        <div class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas {{ $estado === 'atendida' ? 'fa-check-circle' : 'fa-inbox' }}"></i></span>
                <div>
                    <h2>{{ $estado === 'atendida' ? 'Tu solicitud fue atendida' : 'Recibimos tu solicitud' }}</h2>
                    <p>Enviada el {{ $ultima->created_at->format('d/m/Y') }}{{ $ultima->origen === 'formulario' ? ' (formulario de Google)' : '' }}@if ($estado === 'atendida' && $ultima->atendido_en) · atendida el {{ $ultima->atendido_en->format('d/m/Y') }}@endif</p>
                </div>
            </div>
            <div class="al-card-cuerpo">
                <ol class="cr-estados">
                    <li class="is-hecho"><span><i class="fas fa-check"></i></span>Recibida</li>
                    <li class="{{ $estado === 'atendida' ? 'is-hecho' : 'is-actual' }}"><span><i class="fas {{ $estado === 'atendida' ? 'fa-check' : 'fa-hourglass-half' }}"></i></span>Revisión del Área de Investigación</li>
                    <li class="{{ $estado === 'atendida' ? 'is-actual' : '' }}"><span><i class="fas fa-route"></i></span>Proceso de titulación iniciado</li>
                </ol>
                @if ($estado === 'recibida')
                    <div class="al-aviso is-info mb-3"><i class="fas fa-info-circle"></i><div>El Área de Investigación revisará tu PTI y tu comprobante. Cuando registre tu proceso, en tu intranet verás «Mi proceso de titulación» con tu número de expediente.</div></div>
                @endif
                <dl class="al-datos">
                    <div class="al-dato cr-dato-ancho"><dt>Grupo de investigación</dt><dd>{{ implode('; ', $ultima->nombresIntegrantes()) }}</dd></div>
                    <div class="al-dato"><dt>Programa</dt><dd>{{ $ultima->programa_estudios ?: '—' }}{{ $ultima->ciclo ? ' · '.$ultima->ciclo : '' }}</dd></div>
                    @if ($ultima->area())<div class="al-dato"><dt>Área de investigación</dt><dd>{{ $ultima->area() }}</dd></div>@endif
                    @if ($ultima->linea())<div class="al-dato cr-dato-ancho"><dt>Línea de investigación</dt><dd>{{ $ultima->linea() }}</dd></div>@endif
                    <div class="al-dato cr-dato-ancho"><dt>Título del Plan de Trabajo de Investigación</dt><dd>“{{ $ultima->titulo }}”</dd></div>
                    <div class="al-dato"><dt>Anexos</dt><dd>
                        @foreach ($ultima->urls('archivo') as $u)<a href="{{ $u }}" target="_blank" rel="noopener" class="d-block"><i class="fas fa-paperclip fa-xs mr-1"></i>Plan de Trabajo de Investigación</a>@endforeach
                        @foreach ($ultima->urls('comprobante') as $u)<a href="{{ $u }}" target="_blank" rel="noopener" class="d-block"><i class="fas fa-paperclip fa-xs mr-1"></i>Comprobante de pago</a>@endforeach
                    </dd></div>
                </dl>
            </div>
        </div>
    @endif

    {{-- ── Formulario (primera vez o para corregir una observada) ── --}}
    @if ($mostrarForm)
        @if ($estado === 'observada')
            <div class="al-aviso is-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <div><strong>El Área de Investigación observó tu solicitud:</strong> {{ $ultima->observacion }}<br><span class="cr-suave">Corrige lo indicado y envíala de nuevo; tus datos ya están escritos (los archivos debes adjuntarlos otra vez).</span></div>
            </div>
        @endif

        @if ($errors->any())
            <div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div><strong>Revisa el formulario:</strong><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
        @endif

        <form method="POST" action="{{ route('mi-titulacion.solicitud-pti.enviar') }}" enctype="multipart/form-data" class="al-form" id="spForm" novalidate>
            @csrf

            {{-- 1. Grupo de investigación --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">1</span>
                    <div><h2>Grupo de investigación</h2><p>Registra a todos los integrantes del grupo, en orden alfabético, con apellidos y nombres completos.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <div class="cr-correo">
                        <i class="fas fa-envelope"></i>
                        <div><span>Correo electrónico</span><strong>{{ $user->email }}</strong></div>
                    </div>
                    <div class="al-form-grid">
                        <label class="al-form-ancho">
                            Integrante 1 <span class="cr-req">*</span>
                            <input type="text" class="al-campo" value="{{ \App\Http\Controllers\Titulacion\MiSolicitudPtiController::nombre($user) }}" disabled>
                            <span class="cr-ayuda">Eres tú (según tu ficha).</span>
                        </label>
                        <label class="al-form-ancho">
                            Integrante 2
                            <input type="text" name="integrante2" class="al-campo @error('integrante2') is-invalid @enderror" value="{{ $v('integrante2') }}" maxlength="200" placeholder="Apellidos y nombres completos">
                        </label>
                        <label class="al-form-ancho">
                            Integrante 3
                            <input type="text" name="integrante3" class="al-campo @error('integrante3') is-invalid @enderror" value="{{ $v('integrante3') }}" maxlength="200" placeholder="Apellidos y nombres completos">
                        </label>
                        <label class="al-form-ancho">
                            Números de contacto VIGENTES <span class="cr-req">*</span>
                            <input type="text" name="celulares" class="al-campo @error('celulares') is-invalid @enderror" value="{{ $v('celulares') }}" required maxlength="200" placeholder="Ej.: 984 123 456 · 974 654 321">
                            <span class="cr-ayuda">De todos los integrantes, separados por un punto o una coma.</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Programa: el de la ficha, fijo (solo se elige si la ficha no lo indica) --}}
            @php
                $grupos = [
                    'programa_estudios' => ['Programa de Estudios', Solicitud::PROGRAMAS_ESTUDIOS, 'fa-graduation-cap'],
                    'ciclo' => ['Ciclo o programa', Solicitud::CICLOS, 'fa-layer-group'],
                ];
                $todoFijo = isset($fijos['programa_estudios'], $fijos['ciclo']);
            @endphp
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">2</span>
                    <div><h2>Programa</h2><p>{{ $todoFijo ? 'Es el que corresponde a tu ficha; no se puede cambiar.' : 'Marca lo que corresponde a tu ficha.' }}</p></div>
                    @if ($todoFijo)<div class="al-card-cab-extra"><span class="al-badge is-muted"><i class="fas fa-lock"></i> Según tu ficha</span></div>@endif
                </div>
                <div class="al-card-cuerpo">
                    @foreach ($grupos as $campo => [$titulo, $opciones, $icono])
                        @php $fijo = isset($fijos[$campo]); @endphp
                        <fieldset class="cr-grupo-opc {{ $fijo ? 'is-fijo' : '' }}">
                            <legend>{{ $titulo }} @unless ($fijo)<span class="cr-req">*</span>@endunless</legend>
                            <div class="cr-opciones">
                                @foreach ($opciones as $p)
                                    @php $marcada = $v($campo) === $p; @endphp
                                    <label class="cr-opcion {{ $fijo && ! $marcada ? 'is-bloqueada' : '' }}">
                                        <input type="radio" name="{{ $campo }}" value="{{ $p }}" @checked($marcada) @disabled($fijo && ! $marcada) @required(! $fijo)>
                                        <span><i class="fas {{ $fijo && $marcada ? 'fa-lock' : $icono }}"></i><span>{{ $p }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error($campo)<div class="cr-error">{{ $message }}</div>@enderror
                        </fieldset>
                    @endforeach
                </div>
            </div>

            {{-- 3. Investigación --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">3</span>
                    <div><h2>Solicitud</h2><p>Área, línea y título de tu investigación.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <p class="cr-carta">Señor Director de la Escuela de Educación Superior Pedagógica Pukllasunchis: con el debido respeto me presento y solicito la aprobación de mi <strong>Plan de Trabajo de Investigación</strong>. Para este fin remito a su despacho el PTI en versión digital, según el formato establecido por la institución. Asimismo, solicito la designación de Asesor de Investigación.</p>
                    <div class="al-form-grid">
                        <label>
                            Área de Investigación <span class="cr-req">*</span>
                            <select name="area" id="spArea" class="al-campo @error('area') is-invalid @enderror" required>
                                <option value="">— Elige el área —</option>
                                @foreach (Solicitud::AREAS as $clave => $nombre)<option value="{{ $clave }}" @selected($v('area') === $clave)>{{ $nombre }}</option>@endforeach
                            </select>
                        </label>
                        <label>
                            Línea de Investigación <span class="cr-req">*</span>
                            <select name="linea" id="spLinea" class="al-campo @error('linea') is-invalid @enderror" required>
                                <option value="">— Elige primero el área —</option>
                                @foreach (Solicitud::LINEAS as $clave => $nombre)<option value="{{ $clave }}" data-area="{{ substr($clave, 0, 2) }}" @selected($v('linea') === $clave)>{{ $nombre }}</option>@endforeach
                            </select>
                        </label>
                        <label class="al-form-ancho">
                            Título del Plan de Trabajo de Investigación <span class="cr-req">*</span>
                            <textarea name="titulo" rows="3" class="al-campo @error('titulo') is-invalid @enderror" required maxlength="1000">{{ $v('titulo') }}</textarea>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 4. Anexos y declaración --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">4</span>
                    <div><h2>Anexos y declaración</h2><p>Tu PTI y el comprobante del derecho de trámite.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <label class="cr-archivo js-sp-archivo @error('pti') is-invalid @enderror" data-max="10">
                        <input type="file" name="pti" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <span class="cr-archivo-icono"><i class="fas fa-file-alt"></i></span>
                        <span class="cr-archivo-texto">
                            <strong>Anexo 1: Plan de Trabajo de Investigación <span class="cr-req">*</span></strong>
                            <small class="js-sp-nombre">PDF o Word de hasta 10 MB. Haz clic o arrastra el archivo aquí.</small>
                        </span>
                    </label>
                    @error('pti')<div class="cr-error">{{ $message }}</div>@enderror

                    <label class="cr-archivo js-sp-archivo mt-3 @error('comprobantes') is-invalid @enderror @error('comprobantes.*') is-invalid @enderror" data-max="10">
                        <input type="file" name="comprobantes[]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple required>
                        <span class="cr-archivo-icono"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="cr-archivo-texto">
                            <strong>Anexo 2: Comprobante de pago ({{ Tramites::monto($tramite) }} · Cód. {{ $tramite['codigo'] }}) <span class="cr-req">*</span></strong>
                            <small class="js-sp-nombre">El comprobante lo emite la responsable de pagos de la EESPP (llega a tu correo dentro de dos días hábiles del pago); <u>no vale el voucher de Caja Cusco</u>. Hasta 5 archivos PDF, JPG o PNG de 10 MB cada uno.</small>
                        </span>
                    </label>
                    @error('comprobantes')<div class="cr-error">{{ $message }}</div>@enderror
                    @error('comprobantes.*')<div class="cr-error">{{ $message }}</div>@enderror

                    <label class="cr-declaracion @error('declaracion') is-invalid @enderror">
                        <input type="checkbox" name="declaracion" value="1" @checked(old('declaracion')) required>
                        <span><strong>Declaración de veracidad.</strong> Declaro bajo juramento que la información registrada expresa la verdad.</span>
                    </label>
                    @error('declaracion')<div class="cr-error">{{ $message }}</div>@enderror
                </div>
                <div class="al-form-pie">
                    <a href="{{ $volver }}#titulacion" class="al-btn al-btn-borde">Cancelar</a>
                    <button type="submit" class="al-btn al-btn-oro" id="spEnviar"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
(function () {
    var form = document.getElementById('spForm');
    if (!form) return;
    // Línea de investigación: solo las del área elegida.
    var area = document.getElementById('spArea'), linea = document.getElementById('spLinea');
    function filtrar() {
        var a = area.value;
        Array.prototype.forEach.call(linea.options, function (o) {
            if (!o.value) { o.textContent = a ? '— Elige la línea —' : '— Elige primero el área —'; return; }
            o.hidden = o.disabled = o.dataset.area !== a;
        });
        if (linea.selectedOptions[0] && linea.selectedOptions[0].disabled) linea.value = '';
    }
    area.addEventListener('change', filtrar);
    filtrar();
    // Cajas de archivo: nombre y peso; se avisa ya si alguno supera el máximo.
    document.querySelectorAll('.js-sp-archivo').forEach(function (caja) {
        var input = caja.querySelector('input'), nombre = caja.querySelector('.js-sp-nombre'), inicial = nombre.innerHTML, max = +caja.dataset.max;
        input.addEventListener('change', function () {
            var fs = Array.prototype.slice.call(input.files);
            var pesado = fs.filter(function (f) { return f.size > max * 1048576; })[0];
            if (pesado || fs.length > 5) {
                input.value = '';
                caja.classList.remove('is-cargado'); caja.classList.add('is-invalid');
                nombre.innerHTML = pesado ? '<strong>' + pesado.name + ' pesa ' + (pesado.size / 1048576).toFixed(1) + ' MB: supera el máximo de ' + max + ' MB.</strong>' : '<strong>Puedes adjuntar hasta 5 archivos.</strong>';
                return;
            }
            caja.classList.remove('is-invalid');
            caja.classList.toggle('is-cargado', fs.length > 0);
            nombre.textContent = fs.map(function (f) { return f.name + ' · ' + (f.size / 1048576).toFixed(2) + ' MB'; }).join(' | ');
            if (!fs.length) nombre.innerHTML = inicial;
        });
        ['dragenter', 'dragover'].forEach(function (ev) { caja.addEventListener(ev, function () { caja.classList.add('is-arrastrando'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { caja.addEventListener(ev, function () { caja.classList.remove('is-arrastrando'); }); });
    });
    // Mantiene viva la sesión mientras se llena (si no, «Página expirada» al enviar y se pierden los archivos).
    setInterval(function () { fetch(@json(route('ping.sesion')), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {}); }, 10 * 60 * 1000);
    // Un solo envío: los archivos tardan en subir y un segundo clic duplicaría la solicitud.
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        var b = document.getElementById('spEnviar');
        b.disabled = true; b.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando…';
    });
})();
</script>
@endsection
