{{-- Importar las respuestas de un formulario de Google en la bandeja de un trámite (Concerns\ImportaFormularioGoogle).
     Paso 1: subir el Excel o CSV. Paso 2: vista previa (a quién se reconoció y cómo; nada se guarda). Paso 3: importar,
     sin correos. $cfg: kicker, titulo, sub, bandeja, ruta, estados. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Services\Titulacion\ReconocerPersonas as Rec;
        use Illuminate\Support\Str;
        $persona = fn ($f) => $f['usuario'] ? trim($f['usuario']->apellidos.', '.$f['usuario']->name) : null;
        $nombres = fn ($f) => implode('; ', array_column($f['integrantes'], 'nombre')) ?: '—';
        $filas = $lectura['filas'] ?? null;
        if ($filas) {
            $nuevas = $filas->filter(fn ($f) => $f['usuario'] && ! $f['ya_importada']);
            $ya = $filas->where('ya_importada', true);
            $sinPersona = $filas->filter(fn ($f) => ! $f['usuario']);
            $revisar = $nuevas->filter(fn ($f) => ! in_array($f['metodo'], ['correo', 'dni'], true));
            $porEstado = $nuevas->countBy('estado');
        }
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-file-import"></i> {{ $cfg['kicker'] }}</div>
                <h1 class="tl-hero__titulo">Importar del formulario de Google</h1>
                <p class="tl-hero__sub">{!! $cfg['sub'] !!} Se importan todas las respuestas, también las repetidas, como historial; volver a subir el archivo solo agrega las nuevas. <strong>No se envía ningún correo.</strong></p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route($cfg['bandeja']) }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a las solicitudes</a>
            </div>
            @if ($filas)
                <div class="tl-hero__stats">
                    <div class="tl-hero__pista">
                        <span class="tl-stat"><span class="tl-stat__num">{{ $filas->count() }}</span><span class="tl-stat__eti">Respuestas</span></span>
                        <span class="tl-stat tl-stat--oro"><span class="tl-stat__num">{{ $nuevas->count() }}</span><span class="tl-stat__eti">Se importarán</span></span>
                        @foreach ($cfg['estados'] as $clave => $texto)
                            <span class="tl-stat"><span class="tl-stat__num">{{ $porEstado[$clave] ?? 0 }}</span><span class="tl-stat__eti">{{ $texto }}</span></span>
                        @endforeach
                        <span class="tl-stat"><span class="tl-stat__num">{{ $ya->count() }}</span><span class="tl-stat__eti">Ya importadas</span></span>
                        <span class="tl-stat"><span class="tl-stat__num">{{ $sinPersona->count() }}</span><span class="tl-stat__eti">Sin persona</span></span>
                    </div>
                </div>
            @endif
        </header>

        <div class="al-ui">
            @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>@endif
            @if ($errors->any())<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>@endif

            @if (! $lectura)
                <form method="POST" action="{{ route($cfg['ruta'].'.previa') }}" enctype="multipart/form-data" class="al-card al-form">
                    @csrf
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-file-excel"></i></span>
                        <div><h2>1. Subir el Excel o CSV de respuestas</h2><p>Lo mejor es el libro completo «Formularios de Titulación (Respuestas)» en Excel (.xlsx): se toma solo la pestaña de este formulario. Un CSV trae una sola pestaña. Los adjuntos vienen como enlaces de Drive. Todavía no se guarda nada.</p></div></div>
                    <div class="al-card-cuerpo">
                        <input type="file" name="excel" accept=".xlsx,.xls,.csv" required class="form-control-file">
                    </div>
                    <div class="al-form-pie"><button type="submit" class="al-btn al-btn-petroleo"><i class="fas fa-search"></i> Ver vista previa</button></div>
                </form>
            @elseif (! $lectura['hojas'])
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>
                    No se encontró la pestaña de este formulario en el archivo. <a href="{{ route($cfg['ruta']) }}">Subir otro archivo</a>.
                </div></div>
            @else
                <div class="al-aviso is-info"><i class="fas fa-info-circle"></i><div>
                    Pestaña leída: <strong>{{ implode(', ', $lectura['hojas']) }}</strong>.
                    Se reconoció a cada persona por su <strong>DNI</strong> o su <strong>correo</strong>; si no, por su <strong>nombre</strong> (cuando es único) o por un <strong>celular</strong>.
                    Revise abajo las {{ $revisar->count() }} reconocidas por nombre o celular.
                    @if ($lectura['faltan'])<br><span class="small">Columnas que no se encontraron (quedan vacías): {{ implode(', ', $lectura['faltan']) }}.</span>@endif
                </div></div>
            @endif
        </div>

        @if ($filas)
            @if ($sinPersona->isNotEmpty())
                <section class="tl-tabla-card">
                    <div class="tl-resumen"><span><strong>{{ $sinPersona->count() }}</strong> <span class="tl-resumen__suave">no se importan: no se encontró a quien envió. Si le falta la cuenta, créela y vuelva a subir el archivo (las ya importadas no se repiten).</span></span></div>
                    <div class="table-responsive" style="max-height: 360px;"><table class="table titulacion-tabla">
                        <thead><tr><th>Fecha</th><th>Escribieron</th><th>Correo</th><th>Título</th><th>Fila</th></tr></thead>
                        <tbody>@foreach ($sinPersona as $f)
                            <tr><td class="small">{{ $f['fecha']->format('d/m/Y') }}</td><td class="small">{{ $nombres($f) }}</td><td class="small">{{ $f['correo'] ?: '—' }}</td><td class="small">{{ Str::limit($f['titulo'], 70) }}</td><td class="small text-muted">{{ $f['hoja'] }} · {{ $f['fila'] }}</td></tr>
                        @endforeach</tbody>
                    </table></div>
                </section>
            @endif

            @if ($revisar->isNotEmpty())
                <section class="tl-tabla-card">
                    <div class="tl-resumen"><span><strong>{{ $revisar->count() }}</strong> <span class="tl-resumen__suave">reconocidas por nombre o celular: revise que sea la persona correcta</span></span></div>
                    <div class="table-responsive" style="max-height: 360px;"><table class="table titulacion-tabla">
                        <thead><tr><th>Escribieron</th><th>Correo</th><th>Se reconoció como</th><th>Por</th></tr></thead>
                        <tbody>@foreach ($revisar as $f)
                            <tr><td class="small">{{ $nombres($f) }}</td><td class="small">{{ $f['correo'] ?: '—' }}</td><td><strong>{{ $persona($f) }}</strong> <span class="small text-muted">DNI {{ $f['usuario']->dni ?: '—' }}</span></td><td class="small">{{ Rec::METODOS[$f['metodo']] ?? $f['metodo'] }}</td></tr>
                        @endforeach</tbody>
                    </table></div>
                </section>
            @endif

            @if ($nuevas->isNotEmpty())
                <section class="tl-tabla-card">
                    <details>
                        <summary class="tl-resumen" style="cursor:pointer;"><span><strong>{{ $nuevas->count() }}</strong> <span class="tl-resumen__suave">solicitudes que se importarán (ver todas)</span></span></summary>
                        <div class="table-responsive" style="max-height: 420px;"><table class="table titulacion-tabla">
                            <thead><tr><th>Fecha</th><th>Envió</th><th>Integrantes</th><th>Título</th><th>Quedará</th></tr></thead>
                            <tbody>@foreach ($nuevas as $f)
                                <tr>
                                    <td class="small">{{ $f['fecha']->format('d/m/Y') }}</td>
                                    <td>{{ $persona($f) }}</td>
                                    <td class="small">{{ count($f['integrantes']) }} <span class="text-muted">({{ count(array_filter(array_column($f['integrantes'], 'user_id'))) }} reconocidos)</span></td>
                                    <td class="small">{{ Str::limit($f['titulo'], 80) }}@isset($f['datos']['etapa']) <span class="ca-chip">{{ strtoupper($f['datos']['etapa']) }}</span>@endisset</td>
                                    <td class="small">{{ $cfg['estados'][$f['estado']] ?? $f['estado'] }}</td>
                                </tr>
                            @endforeach</tbody>
                        </table></div>
                    </details>
                    <form method="POST" action="{{ route($cfg['ruta'].'.aplicar') }}" class="p-3 d-flex flex-wrap align-items-center" style="gap:.75rem;" id="sgImportar">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <label class="mb-0 small"><input type="checkbox" name="confirmar" value="1" required class="mr-1">Revisé la vista previa: importar estas {{ $nuevas->count() }} solicitudes, sin enviar correos.</label>
                        <button type="submit" class="btn btn-sm btn-primary ml-auto"><i class="fas fa-file-import mr-1"></i>Importar</button>
                    </form>
                </section>
            @elseif ($lectura['hojas'])
                <div class="al-ui"><div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>No hay nada nuevo que importar: todas las respuestas reconocidas ya están en el sistema.</div></div></div>
            @endif
        @endif
    </div>

    <script>
    document.getElementById('sgImportar')?.addEventListener('submit', function (e) {
        var f = this;
        if (f.dataset.ok === '1') return;
        e.preventDefault();
        if (!f.checkValidity()) { f.reportValidity(); return; }
        var seguir = function () { f.dataset.ok = '1'; f.submit(); };
        var texto = 'Se registrarán {{ $filas ? $nuevas->count() : 0 }} solicitudes. No se envía ningún correo.';
        if (window.PkAlert) PkAlert.confirmar({ title: '¿Importar las solicitudes?', text: texto, confirmButtonText: 'Sí, importar' }).then(function (ok) { if (ok) seguir(); });
        else if (confirm(texto)) seguir();
    });
    </script>
@endsection
