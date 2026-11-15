{{-- Solicitud de la carta de aceptación de la Institución Educativa (paso siguiente a la RD del PTI).
     La ve el propio estudiante o egresado; solo se envía con el correo institucional. Va al Área de
     Secretaría (aviso en el sistema) y el estudiante recibe la respuesta automática. Ver CartaAceptacionController. --}}
@extends($layout)
@section('titulo', 'Carta de aceptación')
@section('contenido')
@include('alumnos.partials.ui')
@php
    use App\Models\Titulacion\CartaAceptacion;
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $estado = $ultima?->estado;
    $mostrarForm = $institucional && $ptiAprobado && (! $ultima || $estado === 'observada');
    $volver = match ($layout) {
        'layouts.profesionalizacion' => $user->condicion === 'Egresado' ? route('egresado.intranet') : route('ppd.index'),
        'layouts.alumno' => route('alumnos.index'),
        default => route('egresado.intranet'),
    };
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}#titulacion" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi proceso de titulación</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación</span>
                <h1>Carta de aceptación de la Institución Educativa</h1>
                <p class="al-hero-sub">Solicita a Secretaría la Carta de Presentación para realizar tu investigación en una Institución Educativa. La recibirás aquí y en tu correo institucional.</p>
            </div>
            @if ($ultima)
                <span class="al-badge {{ ['enviada' => 'is-info', 'emitida' => 'is-ok', 'observada' => 'is-warn'][$estado] }} cr-hero-estado">
                    <i class="fas {{ ['enviada' => 'fa-hourglass-half', 'emitida' => 'fa-check-circle', 'observada' => 'fa-exclamation-circle'][$estado] }}"></i>
                    {{ CartaAceptacion::ESTADOS[$estado] }}
                </span>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
    @endif
    @if (session('error'))
        <div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>
    @endif

    {{-- ── Sin las condiciones para pedirla ── --}}
    @if (! $institucional)
        <div class="al-aviso is-mal">
            <i class="fas fa-user-lock"></i>
            <div>
                <strong>Este formulario es institucional.</strong> Solo se puede enviar con un correo <strong>&#64;{{ CartaAceptacion::DOMINIO }}</strong>,
                y tu cuenta usa <strong>{{ $user->email }}</strong>. Comunícate con Secretaría para actualizar tu correo.
            </div>
        </div>
    @elseif (! $ptiAprobado)
        <div class="al-aviso is-warn">
            <i class="fas fa-lock"></i>
            <div><strong>Todavía no puedes solicitarla.</strong> La carta se pide después de que tu Plan de Trabajo de Investigación (PTI) tenga su RD de aprobación.</div>
        </div>
    @endif

    {{-- ── Solicitud ya enviada: Recibida o Aprobada ── --}}
    @if ($ultima && $estado !== 'observada')
        <div class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas {{ $estado === 'emitida' ? 'fa-check-circle' : 'fa-inbox' }}"></i></span>
                <div>
                    <h2>{{ $estado === 'emitida' ? 'Tu carta fue aprobada' : 'Recibimos tu solicitud' }}</h2>
                    <p>
                        Enviada el {{ $ultima->created_at->format('d/m/Y') }} por {{ $ultima->solicitante?->name }} {{ $ultima->solicitante?->apellidos }}
                        @if ($estado === 'emitida' && $ultima->atendido_en) · aprobada el {{ $ultima->atendido_en->format('d/m/Y') }} @endif
                    </p>
                </div>
                @if ($estado === 'emitida')
                    <div class="al-card-cab-extra">
                        @if ($ultima->carta)
                            <a href="{{ route('mi-titulacion.carta.archivo', [$ultima->id, 'carta']) }}" class="al-btn al-btn-oro al-btn-sm">
                                <i class="far fa-file-word"></i> Carta de Presentación
                            </a>
                        @endif
                        <a href="{{ route('mi-titulacion.carta.archivo', [$ultima->id, 'modelo']) }}" class="al-btn al-btn-borde al-btn-sm" title="La Institución Educativa la llena, firma y sella">
                            <i class="far fa-file-word"></i> Modelo de Carta de Aceptación
                        </a>
                    </div>
                @endif
            </div>
            <div class="al-card-cuerpo">
                <ol class="cr-estados">
                    <li class="is-hecho"><span><i class="fas fa-check"></i></span>Recibida</li>
                    <li class="{{ $estado === 'emitida' ? 'is-hecho' : 'is-actual' }}"><span><i class="fas {{ $estado === 'emitida' ? 'fa-check' : 'fa-hourglass-half' }}"></i></span>{{ $estado === 'emitida' ? 'Aprobada' : 'Respuesta de Secretaría' }}</li>
                    <li class="{{ $estado === 'emitida' ? 'is-actual' : '' }}"><span><i class="fas fa-graduation-cap"></i></span>Proceso de obtención del Bachiller</li>
                </ol>
                @if ($estado === 'enviada')
                    <div class="al-aviso is-info mb-3"><i class="fas fa-info-circle"></i><div><strong>{{ $ultima->mensajeRecepcion() }}</strong><br>Secretaría revisará tus datos y tu comprobante; te avisaremos en la campanita y en tu correo institucional. Con la aprobación continúas con el proceso de obtención del Bachiller.</div></div>
                @elseif ($ultima->observacion)
                    <div class="al-aviso is-ok mb-3"><i class="fas fa-comment-dots"></i><div class="cr-mensaje"><strong>Respuesta de Secretaría:</strong><br>{{ $ultima->observacion }}</div></div>
                @endif
                <dl class="al-datos">
                    <div class="al-dato"><dt>Institución Educativa</dt><dd>{{ $ultima->institucion }}</dd></div>
                    <div class="al-dato"><dt>Director(a)</dt><dd>{{ $ultima->director }}</dd></div>
                    <div class="al-dato"><dt>Grupo de investigación</dt><dd>{{ $ultima->grupo }}</dd></div>
                    <div class="al-dato"><dt>Programa</dt><dd>{{ $ultima->programa_estudios }} · {{ $ultima->programa_formacion }}</dd></div>
                    <div class="al-dato cr-dato-ancho"><dt>Título del trabajo</dt><dd>“{{ $ultima->titulo }}”</dd></div>
                    <div class="al-dato"><dt>Comprobante</dt><dd><a href="{{ route('mi-titulacion.carta.archivo', [$ultima->id, 'comprobante']) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip fa-xs mr-1"></i>Ver comprobante</a></dd></div>
                </dl>
            </div>
        </div>
    @endif

    {{-- ── Formulario (primera vez o para corregir una observada) ── --}}
    @if ($mostrarForm)
        @if ($estado === 'observada')
            <div class="al-aviso is-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <div><strong>Secretaría observó tu solicitud:</strong> {{ $ultima->observacion }}<br><span class="cr-suave">Corrige lo indicado y envíala de nuevo; tus datos ya están escritos (el comprobante debes adjuntarlo otra vez).</span></div>
            </div>
        @endif

        @if ($errors->any())
            <div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div><strong>Revisa el formulario:</strong><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
        @endif

        <form method="POST" action="{{ route('mi-titulacion.carta.enviar') }}" enctype="multipart/form-data" class="al-form" id="crForm" novalidate>
            @csrf

            {{-- 1. Solicitante --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">1</span>
                    <div><h2>Datos del grupo</h2><p>Quién solicita la carta y cómo contactarte.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <div class="cr-correo">
                        <i class="fas fa-envelope"></i>
                        <div><span>Correo institucional</span><strong>{{ $user->email }}</strong></div>
                        <span class="al-badge is-ok"><i class="fas fa-shield-alt"></i> Institucional</span>
                    </div>
                    <div class="al-form-grid">
                        <label class="al-form-ancho">
                            Grupo de investigación <span class="cr-req">*</span>
                            <textarea name="grupo" rows="2" class="al-campo @error('grupo') is-invalid @enderror" required maxlength="1000">{{ $v('grupo') }}</textarea>
                            <span class="cr-ayuda">Escribe el nombre completo de todos los integrantes del grupo de investigación.</span>
                        </label>
                        <label>
                            Número de celular <span class="cr-req">*</span>
                            <input type="tel" name="celular" class="al-campo @error('celular') is-invalid @enderror" value="{{ $v('celular') }}" required inputmode="tel" maxlength="15" placeholder="Ej.: 984 123 456">
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Programa: el de la ficha, fijo (solo se elige si la ficha no lo indica) --}}
            @php
                $todoFijo = $fijos['programa_estudios'] && $fijos['programa_formacion'];
                $grupos = [
                    'programa_estudios' => ['Egresado del Programa de Estudios', collect(CartaAceptacion::PROGRAMAS_ESTUDIOS)->mapWithKeys(fn ($p) => [$p => $p])->all(), 'fa-graduation-cap'],
                    'programa_formacion' => ['Programa de Formación', CartaAceptacion::PROGRAMAS_FORMACION, 'fa-chalkboard-teacher'],
                ];
            @endphp
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">2</span>
                    <div>
                        <h2>Programa</h2>
                        <p>{{ $todoFijo ? 'Es el programa al que perteneces según tu ficha; no se puede cambiar.' : 'Marca el que corresponde a tu ficha.' }}</p>
                    </div>
                    @if ($todoFijo)
                        <div class="al-card-cab-extra"><span class="al-badge is-muted"><i class="fas fa-lock"></i> Según tu ficha</span></div>
                    @endif
                </div>
                <div class="al-card-cuerpo">
                    @foreach ($grupos as $campo => [$titulo, $opciones, $icono])
                        @php $fijo = $fijos[$campo]; @endphp
                        <fieldset class="cr-grupo-opc {{ $fijo ? 'is-fijo' : '' }}">
                            <legend>{{ $titulo }} @unless ($fijo)<span class="cr-req">*</span>@endunless</legend>
                            <div class="cr-opciones">
                                @foreach ($opciones as $clave => $p)
                                    @php
                                        [$nombreP, $duracion] = array_pad(explode(' - ', $p, 2), 2, '');
                                        $marcada = $v($campo) === $clave;
                                    @endphp
                                    {{-- Fijo: solo la de la ficha se envía; las demás quedan deshabilitadas (el servidor igual usa la de la ficha). --}}
                                    <label class="cr-opcion {{ $fijo && ! $marcada ? 'is-bloqueada' : '' }}">
                                        <input type="radio" name="{{ $campo }}" value="{{ $clave }}" @checked($marcada) @disabled($fijo && ! $marcada) @required(! $fijo)>
                                        <span>
                                            <i class="fas {{ $fijo && $marcada ? 'fa-lock' : $icono }}"></i>
                                            <span>{{ $nombreP }}@if ($duracion)<small>{{ $duracion }}</small>@endif</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error($campo)<div class="cr-error">{{ $message }}</div>@enderror
                        </fieldset>
                    @endforeach
                    @if ($todoFijo)
                        <div class="cr-ayuda mt-2"><i class="fas fa-info-circle mr-1"></i>Si tu programa no es correcto, avísanos desde «¿Algún dato no coincide?» en tu proceso de titulación.</div>
                    @endif
                </div>
            </div>

            {{-- 3. Solicitud --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">3</span>
                    <div><h2>Solicitud</h2><p>Los datos que irán en la carta.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <p class="cr-carta">Es grato dirigirme a usted para solicitar formalmente la emisión de una <strong>Carta de Presentación</strong> para realizar mi Trabajo de Investigación. Para este fin informo lo siguiente:</p>
                    <div class="al-form-grid">
                        <label class="al-form-ancho">
                            La DENOMINACIÓN DE LA INSTITUCIÓN EDUCATIVA donde se realizará la investigación es: <span class="cr-req">*</span>
                            <input type="text" name="institucion" class="al-campo @error('institucion') is-invalid @enderror" value="{{ $v('institucion') }}" required maxlength="300" placeholder="Ej.: I.E.I. N.° 123 «Los Pequeños Inkas» – San Jerónimo, Cusco">
                        </label>
                        <label class="al-form-ancho">
                            Los NOMBRES Y APELLIDOS COMPLETOS del Director(a) de la Institución Educativa son: <span class="cr-req">*</span>
                            <input type="text" name="director" class="al-campo @error('director') is-invalid @enderror" value="{{ $v('director') }}" required maxlength="200" placeholder="Ej.: Mag. Rosa Elena Quispe Huamán">
                        </label>
                        <label class="al-form-ancho">
                            El TÍTULO de mi Trabajo de Investigación: <span class="cr-req">*</span>
                            <textarea name="titulo" rows="3" class="al-campo @error('titulo') is-invalid @enderror" required maxlength="1000">{{ $v('titulo') }}</textarea>
                            @if (filled($valores['titulo']) && ! old('titulo'))<span class="cr-ayuda">Lo tomamos de tu PTI aprobado.</span>@endif
                        </label>
                    </div>
                </div>
            </div>

            {{-- 4. Anexo y declaración --}}
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="cr-num">4</span>
                    <div><h2>Anexo y declaración</h2><p>Comprobante del derecho de trámite.</p></div>
                </div>
                <div class="al-card-cuerpo">
                    <label class="cr-archivo @error('comprobante') is-invalid @enderror" id="crArchivo">
                        <input type="file" name="comprobante" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
                        <span class="cr-archivo-icono"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="cr-archivo-texto">
                            <strong>Comprobante de pago por derecho de trámite (S/ 10.00) <span class="cr-req">*</span></strong>
                            <small id="crArchivoNombre">Comprobante, <u>no voucher</u>. PDF, JPG o PNG de hasta 5 MB. Haz clic o arrastra el archivo aquí.</small>
                        </span>
                    </label>
                    @error('comprobante')<div class="cr-error">{{ $message }}</div>@enderror

                    <label class="cr-declaracion @error('declaracion') is-invalid @enderror">
                        <input type="checkbox" name="declaracion" value="1" @checked(old('declaracion')) required>
                        <span><strong>Declaración de veracidad.</strong> Declaro bajo juramento que la información registrada expresa la verdad.</span>
                    </label>
                    @error('declaracion')<div class="cr-error">{{ $message }}</div>@enderror
                </div>
                <div class="al-form-pie">
                    <a href="{{ $volver }}#titulacion" class="al-btn al-btn-borde">Cancelar</a>
                    <button type="submit" class="al-btn al-btn-oro" id="crEnviar"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
(function () {
    var form = document.getElementById('crForm');
    if (!form) return;
    var caja = document.getElementById('crArchivo'), input = caja.querySelector('input'), nombre = document.getElementById('crArchivoNombre');
    var textoInicial = nombre.innerHTML;
    var MAX_MB = 5;
    input.addEventListener('change', function () {
        var f = input.files[0];
        // Demasiado pesado: se avisa ya (si no, el servidor lo rechaza tras subirlo entero, o muestra un error técnico).
        if (f && f.size > MAX_MB * 1048576) {
            input.value = '';
            caja.classList.remove('is-cargado');
            caja.classList.add('is-invalid');
            nombre.innerHTML = '<strong>' + (f.size / 1048576).toFixed(1) + ' MB: supera el máximo de ' + MAX_MB + ' MB.</strong> Toma una foto más liviana o guárdalo como PDF.';
            return;
        }
        caja.classList.remove('is-invalid');
        caja.classList.toggle('is-cargado', !!f);
        nombre.textContent = f ? f.name + ' · ' + (f.size / 1048576).toFixed(2) + ' MB' : '';
        if (!f) nombre.innerHTML = textoInicial;
    });
    // Mantiene viva la sesión mientras el formulario está abierto: sin esto, al tardar en llenarlo podía
    // aparecer «Página expirada» (419) al enviar y se perdía el archivo adjunto.
    setInterval(function () { fetch(@json(route('ping.sesion')), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {}); }, 10 * 60 * 1000);
    ['dragenter', 'dragover'].forEach(function (ev) { caja.addEventListener(ev, function () { caja.classList.add('is-arrastrando'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { caja.addEventListener(ev, function () { caja.classList.remove('is-arrastrando'); }); });
    // Un solo envío: el archivo tarda en subir y un segundo clic duplicaría la solicitud.
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        var b = document.getElementById('crEnviar');
        b.disabled = true; b.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando…';
    });
})();
</script>
@endsection
