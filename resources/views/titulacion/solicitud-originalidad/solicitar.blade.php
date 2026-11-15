{{-- Revisión de originalidad (Turnitin) del TI o de la Tesis, desde la intranet del tesista (MiSolicitudOriginalidadController).
     Arriba, la última solicitud (en revisión, con su reporte y % de similitud, u observada); abajo, el formulario para
     una revisión nueva. Mismas preguntas que el formulario de Google; los tesistas son el grupo que conoce el sistema.
     Diseño de las solicitudes del estudiante (clases cr-* y al-* de css/pk-ui.css). --}}
@extends($layout)
@section('titulo', 'Revisión de originalidad')
@section('contenido')
@include('alumnos.partials.ui')
@php
    use App\Models\Titulacion\Solicitud;
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $estado = $ultima?->estado;
    $max = Solicitud::SIMILITUD_MAXIMA;
    $conReporte = $ultima && $ultima->similitud() !== null;
    // Formulario abierto: primera vez, observada o no apta. Plegado: si ya hay una revisión apta o atendida fuera del sistema.
    $formAbierto = ! $ultima || $estado === 'observada' || ($conReporte && ! $ultima->esApto());
    $formPlegado = $ultima && $estado === 'atendida' && ! $formAbierto;
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}#titulacion" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi proceso de titulación</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación y Tesis</span>
                <h1>Revisión de originalidad (Turnitin)</h1>
                <p class="al-hero-sub">Solicita la revisión del porcentaje de similitud de tu Trabajo de Investigación o de tu Tesis. El Área de Investigación te enviará el reporte en hasta {{ Solicitud::DIAS_ORIGINALIDAD }} días hábiles. La similitud máxima es {{ $max }} %.</p>
            </div>
            @if ($ultima)
                @php [$cl, $ic, $tx] = match (true) {
                    $estado === 'recibida' => ['is-info', 'fa-hourglass-half', 'En revisión'],
                    $estado === 'observada' => ['is-warn', 'fa-exclamation-circle', 'Observada'],
                    $conReporte && $ultima->esApto() => ['is-ok', 'fa-check-circle', 'Apto · '.$fmt($ultima->similitud()).' %'],
                    $conReporte => ['is-mal', 'fa-times-circle', 'No apto · '.$fmt($ultima->similitud()).' %'],
                    default => ['is-muted', 'fa-check', 'Atendida'],
                }; @endphp
                <span class="al-badge {{ $cl }} cr-hero-estado"><i class="fas {{ $ic }}"></i> {{ $tx }}</span>
            @endif
        </div>
    </section>

    @if (session('success'))<div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>@endif
    @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>@endif

    {{-- ── Última solicitud ── --}}
    @if ($ultima && $estado !== 'observada')
        <div class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas {{ $estado === 'recibida' ? 'fa-inbox' : 'fa-spell-check' }}"></i></span>
                <div>
                    <h2>{{ $estado === 'recibida' ? 'Tu revisión está en curso' : ($conReporte ? 'Tu reporte de originalidad' : 'Tu última revisión') }}</h2>
                    <p>{{ Solicitud::ETAPAS[$ultima->etapa()] ?? 'Documento' }} · enviada el {{ $ultima->created_at->format('d/m/Y') }}{{ $ultima->origen === 'formulario' ? ' (formulario de Google)' : '' }}@if ($ultima->atendido_en && $estado === 'atendida') · respondida el {{ $ultima->atendido_en->format('d/m/Y') }}@endif</p>
                </div>
                @if ($r = $ultima->urls('reporte')[0] ?? null)
                    <div class="al-card-cab-extra"><a href="{{ $r }}" target="_blank" rel="noopener" class="al-btn al-btn-oro al-btn-sm"><i class="fas fa-file-pdf"></i> Ver reporte</a></div>
                @endif
            </div>
            <div class="al-card-cuerpo">
                @if ($estado === 'recibida')
                    <div class="al-aviso is-info mb-3"><i class="fas fa-info-circle"></i><div>El Área de Investigación revisará tu documento en Turnitin y te enviará el reporte a más tardar el <strong>{{ $ultima->fechaLimite()->format('d/m/Y') }}</strong>. Te avisaremos en la campanita.</div></div>
                @elseif ($conReporte && $ultima->esApto())
                    <div class="al-aviso is-ok mb-3"><i class="fas fa-check-circle"></i><div><strong>Tu documento tiene {{ $fmt($ultima->similitud()) }} % de similitud: está apto</strong> (máximo {{ $max }} %). Adjunta este reporte en tu siguiente trámite{{ $ultima->etapa() === 'ti' ? ' (revisión del TI por tu asesor/a)' : '' }}.</div></div>
                @elseif ($conReporte)
                    <div class="al-aviso is-mal mb-3"><i class="fas fa-times-circle"></i><div><strong>Tu documento tiene {{ $fmt($ultima->similitud()) }} % de similitud: supera el máximo de {{ $max }} %.</strong> Corrígelo y solicita una nueva revisión abajo (con un nuevo pago).</div></div>
                @else
                    <div class="al-aviso is-info mb-3"><i class="fas fa-envelope"></i><div>Esta revisión se atendió fuera del sistema: el reporte se te envió a tu correo institucional.</div></div>
                @endif
                @if ($ultima->observacion)<div class="al-aviso is-ok mb-3"><i class="fas fa-comment-dots"></i><div class="cr-mensaje"><strong>Mensaje del Área de Investigación:</strong><br>{{ $ultima->observacion }}</div></div>@endif
                <dl class="al-datos">
                    <div class="al-dato cr-dato-ancho"><dt>Tesistas</dt><dd>{{ implode('; ', $ultima->nombresIntegrantes()) }}</dd></div>
                    <div class="al-dato cr-dato-ancho"><dt>Título</dt><dd>“{{ $ultima->titulo }}”</dd></div>
                </dl>
            </div>
        </div>
    @endif

    {{-- ── Formulario ── --}}
    @if ($formAbierto || $formPlegado)
        @if ($estado === 'observada')
            <div class="al-aviso is-warn"><i class="fas fa-exclamation-triangle"></i><div><strong>El Área de Investigación observó tu solicitud:</strong> {{ $ultima->observacion }}<br><span class="cr-suave">Corrige lo indicado y envíala de nuevo; tus datos ya están escritos (los archivos debes adjuntarlos otra vez).</span></div></div>
        @endif
        @if ($errors->any())
            <div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div><strong>Revisa el formulario:</strong><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
        @endif

        @if ($formPlegado)<details class="mb-3" @if ($errors->any()) open @endif><summary class="al-btn al-btn-borde mb-3" style="cursor:pointer; list-style:none;"><i class="fas fa-plus"></i> Solicitar otra revisión (por ejemplo, de tu Tesis)</summary>@endif

        <form method="POST" action="{{ route('mi-titulacion.originalidad.enviar') }}" enctype="multipart/form-data" class="al-form" id="soForm" novalidate>
            @csrf
            {{-- 1. Tesistas --}}
            <div class="al-card">
                <div class="al-card-cab"><span class="cr-num">1</span><div><h2>Tesistas</h2><p>Tu grupo de investigación según el sistema.</p></div></div>
                <div class="al-card-cuerpo">
                    <div class="cr-correo"><i class="fas fa-envelope"></i><div><span>Correo electrónico</span><strong>{{ $user->email }}</strong></div></div>
                    <ul class="mb-3 pl-3">
                        @foreach ($tesistas as $t)<li>{{ \App\Http\Controllers\Titulacion\MiSolicitudPtiController::nombre($t) }}@if ($t->id === $user->id) <span class="cr-suave">(tú)</span>@endif</li>@endforeach
                    </ul>
                    <div class="al-form-grid">
                        <label class="al-form-ancho">
                            Números de celular de los tesistas <span class="cr-req">*</span>
                            <input type="text" name="celulares" class="al-campo @error('celulares') is-invalid @enderror" value="{{ $v('celulares') }}" required maxlength="200" placeholder="Ej.: 984 123 456 · 974 654 321">
                        </label>
                        <label class="al-form-ancho">
                            Domicilio actual (Barrio o Comunidad / Distrito / Provincia / Departamento)
                            <input type="text" name="domicilio" class="al-campo" value="{{ $v('domicilio') }}" maxlength="300">
                        </label>
                    </div>
                    <div class="cr-ayuda mt-2"><i class="fas fa-info-circle mr-1"></i>Si tu grupo no es correcto, avísanos desde «¿Algún dato no coincide?» en tu proceso de titulación.</div>
                </div>
            </div>

            {{-- 2. Documento --}}
            <div class="al-card">
                <div class="al-card-cab"><span class="cr-num">2</span><div><h2>Solicitud</h2><p>Con el debido respeto me presento y solicito la revisión del porcentaje de similitud en Turnitin de:</p></div></div>
                <div class="al-card-cuerpo">
                    <fieldset class="cr-grupo-opc">
                        <legend>Documento <span class="cr-req">*</span></legend>
                        <div class="cr-opciones">
                            @foreach (Solicitud::ETAPAS as $clave => $nombre)
                                @php [$cod, $monto] = Solicitud::PAGO_ORIGINALIDAD[$clave]; @endphp
                                <label class="cr-opcion">
                                    <input type="radio" name="etapa" value="{{ $clave }}" @checked($v('etapa') === $clave) required data-titulo="{{ $titulos[$clave] }}">
                                    <span><i class="fas {{ $clave === 'ti' ? 'fa-book' : 'fa-graduation-cap' }}"></i><span>{{ $nombre }}<small>Pago: S/ {{ number_format($monto, 2) }} · Cód. {{ $cod }}</small></span></span>
                                </label>
                            @endforeach
                        </div>
                        @error('etapa')<div class="cr-error">{{ $message }}</div>@enderror
                    </fieldset>
                    <div class="al-form-grid">
                        <label class="al-form-ancho">
                            Título del documento <span class="cr-req">*</span>
                            <textarea name="titulo" id="soTitulo" rows="3" class="al-campo @error('titulo') is-invalid @enderror" required maxlength="1000">{{ $v('titulo') }}</textarea>
                            <span class="cr-ayuda">Lo tomamos de tu proceso; corrígelo si cambió.</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 3. Anexos --}}
            <div class="al-card">
                <div class="al-card-cab"><span class="cr-num">3</span><div><h2>Anexos y declaración</h2><p>Remito la versión digital, según el formato establecido por la institución.</p></div></div>
                <div class="al-card-cuerpo">
                    <label class="cr-archivo js-so-archivo @error('documento') is-invalid @enderror" data-max="10">
                        <input type="file" name="documento" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <span class="cr-archivo-icono"><i class="fas fa-file-word"></i></span>
                        <span class="cr-archivo-texto"><strong>Anexo 1: Versión final en formato WORD <span class="cr-req">*</span></strong><small class="js-so-nombre">Word (.doc o .docx) de hasta 10 MB.</small></span>
                    </label>
                    @error('documento')<div class="cr-error">{{ $message }}</div>@enderror

                    <label class="cr-archivo js-so-archivo mt-3 @error('comprobantes') is-invalid @enderror @error('comprobantes.*') is-invalid @enderror" data-max="10">
                        <input type="file" name="comprobantes[]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple required>
                        <span class="cr-archivo-icono"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="cr-archivo-texto"><strong>Anexo 2: Comprobante de pago <span class="cr-req">*</span></strong><small class="js-so-nombre">Emitido por la EESPP (llega a tu correo dentro de dos días hábiles del pago); <u>no vale el voucher de Caja Cusco</u>. Hasta 5 archivos PDF, JPG o PNG.</small></span>
                    </label>
                    @error('comprobantes')<div class="cr-error">{{ $message }}</div>@enderror
                    @error('comprobantes.*')<div class="cr-error">{{ $message }}</div>@enderror

                    <div class="al-form-grid mt-3">
                        <label>
                            Fecha de pago <span class="cr-req">*</span>
                            <input type="date" name="fecha_pago" class="al-campo @error('fecha_pago') is-invalid @enderror" value="{{ $v('fecha_pago') }}" max="{{ now()->toDateString() }}" required>
                            <span class="cr-ayuda">El pago vale una semana e incluye 1 revisión.</span>
                        </label>
                    </div>

                    <label class="cr-declaracion @error('declaracion') is-invalid @enderror">
                        <input type="checkbox" name="declaracion" value="1" @checked(old('declaracion')) required>
                        <span><strong>Declaración de veracidad.</strong> Declaro bajo juramento que la información registrada expresa la verdad.</span>
                    </label>
                    @error('declaracion')<div class="cr-error">{{ $message }}</div>@enderror
                </div>
                <div class="al-form-pie">
                    <a href="{{ $volver }}#titulacion" class="al-btn al-btn-borde">Cancelar</a>
                    <button type="submit" class="al-btn al-btn-oro" id="soEnviar"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                </div>
            </div>
        </form>
        @if ($formPlegado)</details>@endif
    @endif

    {{-- ── Historial ── --}}
    @if ($historial->count() > 1)
        <div class="al-card">
            <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-history"></i></span><div><h2>Revisiones anteriores</h2><p>{{ $historial->count() }} solicitudes de revisión de originalidad.</p></div></div>
            <div class="al-tabla-marco"><table class="al-tabla">
                <thead><tr><th>Enviada</th><th>Documento</th><th>Resultado</th><th></th></tr></thead>
                <tbody>@foreach ($historial as $h)
                    <tr>
                        <td>{{ $h->created_at->format('d/m/Y') }}</td>
                        <td>{{ $h->etapa() === 'tesis' ? 'Tesis' : 'TI' }}</td>
                        <td>@if ($h->similitud() !== null){{ $fmt($h->similitud()) }} % · {{ $h->esApto() ? 'apto' : 'no apto' }}@else{{ \App\Models\Titulacion\Solicitud::ESTADOS[$h->estado] ?? $h->estado }}@endif</td>
                        <td>@if ($rep = $h->urls('reporte')[0] ?? null)<a href="{{ $rep }}" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> Reporte</a>@endif</td>
                    </tr>
                @endforeach</tbody>
            </table></div>
        </div>
    @endif
</div>

<script>
(function () {
    var form = document.getElementById('soForm');
    if (!form) return;
    // Al cambiar TI ↔ Tesis se propone el título que el sistema conoce (si no se escribió otro).
    var titulo = document.getElementById('soTitulo'), propuesto = titulo.value;
    form.querySelectorAll('input[name="etapa"]').forEach(function (r) {
        r.addEventListener('change', function () {
            if (titulo.value === propuesto && r.dataset.titulo) { titulo.value = r.dataset.titulo; propuesto = titulo.value; }
        });
    });
    document.querySelectorAll('.js-so-archivo').forEach(function (caja) {
        var input = caja.querySelector('input'), nombre = caja.querySelector('.js-so-nombre'), inicial = nombre.innerHTML, max = +caja.dataset.max;
        input.addEventListener('change', function () {
            var fs = Array.prototype.slice.call(input.files);
            var pesado = fs.filter(function (f) { return f.size > max * 1048576; })[0];
            if (pesado || fs.length > 5) {
                input.value = ''; caja.classList.remove('is-cargado'); caja.classList.add('is-invalid');
                nombre.innerHTML = pesado ? '<strong>' + pesado.name + ' pesa ' + (pesado.size / 1048576).toFixed(1) + ' MB: supera el máximo de ' + max + ' MB.</strong>' : '<strong>Puedes adjuntar hasta 5 archivos.</strong>';
                return;
            }
            caja.classList.remove('is-invalid'); caja.classList.toggle('is-cargado', fs.length > 0);
            nombre.textContent = fs.map(function (f) { return f.name + ' · ' + (f.size / 1048576).toFixed(2) + ' MB'; }).join(' | ');
            if (!fs.length) nombre.innerHTML = inicial;
        });
    });
    setInterval(function () { fetch(@json(route('ping.sesion')), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {}); }, 10 * 60 * 1000);
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        var b = document.getElementById('soEnviar'); b.disabled = true; b.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando…';
    });
})();
</script>
@endsection
