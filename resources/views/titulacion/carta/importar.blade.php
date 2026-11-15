{{-- Importar solicitudes de Carta de Presentación del formulario de Google (ImportarCartasController). Paso 1:
     subir el Excel de respuestas. Paso 2: vista previa (a quién se reconoció y cómo; nada se guarda). Paso 3:
     importar como aprobadas, sin correos. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Services\Titulacion\ImportarCartasFormulario as Imp;
        $persona = fn ($f) => $f['usuario'] ? trim($f['usuario']->apellidos.', '.$f['usuario']->name) : null;
        if ($filas) {
            $nuevas = $filas->filter(fn ($f) => $f['usuario'] && ! $f['ya_importada']);
            $ya = $filas->where('ya_importada', true);
            $sinPersona = $filas->filter(fn ($f) => ! $f['usuario']);
            $revisar = $nuevas->filter(fn ($f) => in_array($f['metodo'], ['nombre', 'celular'], true));
            $porMetodo = $nuevas->countBy('metodo');
        }
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-file-import"></i> Secretaría · Cartas de aceptación</div>
                <h1 class="tl-hero__titulo">Importar del formulario de Google</h1>
                <p class="tl-hero__sub">Las solicitudes de Carta de Presentación hechas antes con los formularios de Google (Excel «Formularios de Titulación (Respuestas)», hojas «Carta de Presentación» y «Carta Presentación») se registran como <strong>aprobadas</strong>, con su fecha original y <strong>sin enviar correos</strong>. Se importan todas, también las repetidas, como historial.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.cartas.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a los registros</a>
            </div>
            @if ($filas)
                <div class="tl-hero__stats">
                    <div class="tl-hero__pista">
                        <span class="tl-stat"><span class="tl-stat__num">{{ $filas->count() }}</span><span class="tl-stat__eti">Respuestas en el Excel</span></span>
                        <span class="tl-stat tl-stat--oro"><span class="tl-stat__num">{{ $nuevas->count() }}</span><span class="tl-stat__eti">Se importarán</span></span>
                        <span class="tl-stat"><span class="tl-stat__num">{{ $ya->count() }}</span><span class="tl-stat__eti">Ya importadas</span></span>
                        <span class="tl-stat"><span class="tl-stat__num">{{ $sinPersona->count() }}</span><span class="tl-stat__eti">Sin persona</span></span>
                    </div>
                </div>
            @endif
        </header>

        <div class="al-ui">
            @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>@endif
            @if ($errors->any())<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>@endif

            @if (! $filas)
                <form method="POST" action="{{ route('titulacion.cartas.importar.previa') }}" enctype="multipart/form-data" class="al-card al-form">
                    @csrf
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-file-excel"></i></span>
                        <div><h2>1. Subir el Excel de respuestas</h2><p>Todavía no se guarda nada: primero verá a quién se reconoció.</p></div></div>
                    <div class="al-card-cuerpo">
                        <input type="file" name="excel" accept=".xlsx,.xls" required class="form-control-file">
                    </div>
                    <div class="al-form-pie"><button type="submit" class="al-btn al-btn-petroleo"><i class="fas fa-search"></i> Ver vista previa</button></div>
                </form>
            @else
                <div class="al-aviso is-info"><i class="fas fa-info-circle"></i><div>
                    Se reconoció a cada persona por su <strong>correo institucional</strong> o <strong>DNI</strong>; si escribió otro correo, por su <strong>nombre</strong> (cuando es único) o su <strong>celular</strong>.
                    Revise abajo las {{ $revisar->count() }} reconocidas por nombre o celular. Cada solicitud se registra para todo el grupo que el sistema conoce.
                    @if ($porMetodo->isNotEmpty()) <br><span class="small">Se importarán: {{ $porMetodo->map(fn ($n, $m) => (Imp::METODOS[$m] ?? $m).' '.$n)->implode(' · ') }}.</span> @endif
                </div></div>
            @endif
        </div>

        @if ($filas)
            @if ($sinPersona->isNotEmpty())
                <section class="tl-tabla-card">
                    <div class="tl-resumen"><span><strong>{{ $sinPersona->count() }}</strong> <span class="tl-resumen__suave">no se importan: no se encontró a la persona. Regístrelas a mano desde su ficha («¿Ya la tramitó fuera del sistema?») si corresponde.</span></span></div>
                    <div class="table-responsive"><table class="table titulacion-tabla">
                        <thead><tr><th>Fecha</th><th>Escribió</th><th>Correo / DNI</th><th>Institución</th><th>Motivo</th></tr></thead>
                        <tbody>@foreach ($sinPersona as $f)
                            <tr><td class="small">{{ $f['fecha']->format('d/m/Y') }}</td><td>{{ \Illuminate\Support\Str::limit($f['grupo'], 60) }}</td><td class="small">{{ $f['correo'] ?: ($f['dni'] ? 'DNI '.$f['dni'] : '—') }}</td><td class="small">{{ \Illuminate\Support\Str::limit($f['institucion'], 50) }}</td><td class="small text-muted">{{ $f['motivo'] }}</td></tr>
                        @endforeach</tbody>
                    </table></div>
                </section>
            @endif

            @if ($revisar->isNotEmpty())
                <section class="tl-tabla-card">
                    <div class="tl-resumen"><span><strong>{{ $revisar->count() }}</strong> <span class="tl-resumen__suave">reconocidas por nombre o celular: revise que sea la persona correcta</span></span></div>
                    <div class="table-responsive" style="max-height: 360px;"><table class="table titulacion-tabla">
                        <thead><tr><th>Escribió</th><th>Correo</th><th>Se reconoció como</th><th>Por</th></tr></thead>
                        <tbody>@foreach ($revisar as $f)
                            <tr><td>{{ \Illuminate\Support\Str::limit($f['grupo'], 60) }}</td><td class="small">{{ $f['correo'] }}</td><td><strong>{{ $persona($f) }}</strong> <span class="small text-muted">DNI {{ $f['usuario']->dni ?: '—' }}</span></td><td class="small">{{ Imp::METODOS[$f['metodo']] }}</td></tr>
                        @endforeach</tbody>
                    </table></div>
                </section>
            @endif

            @if ($nuevas->isNotEmpty())
                <section class="tl-tabla-card">
                    <details>
                        <summary class="tl-resumen" style="cursor:pointer;"><span><strong>{{ $nuevas->count() }}</strong> <span class="tl-resumen__suave">solicitudes que se importarán (ver todas)</span></span></summary>
                        <div class="table-responsive" style="max-height: 420px;"><table class="table titulacion-tabla">
                            <thead><tr><th>Fecha</th><th>Persona</th><th>Institución Educativa</th><th>Director(a)</th><th>Hoja</th></tr></thead>
                            <tbody>@foreach ($nuevas as $f)
                                <tr><td class="small">{{ $f['fecha']->format('d/m/Y') }}</td><td>{{ $persona($f) }}</td><td class="small">{{ \Illuminate\Support\Str::limit($f['institucion'], 60) }}</td><td class="small">{{ \Illuminate\Support\Str::limit($f['director'], 40) }}</td><td class="small text-muted">{{ $f['tipo'] === 'antigua' ? 'Antiguo (2022)' : 'Nuevo' }}</td></tr>
                            @endforeach</tbody>
                        </table></div>
                    </details>
                    <form method="POST" action="{{ route('titulacion.cartas.importar.aplicar') }}" class="p-3 d-flex flex-wrap align-items-center" style="gap:.75rem;" id="icImportar">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <label class="mb-0 small"><input type="checkbox" name="confirmar" value="1" required class="mr-1">Revisé la vista previa: importar estas {{ $nuevas->count() }} solicitudes como aprobadas, sin enviar correos.</label>
                        <button type="submit" class="btn btn-sm btn-primary ml-auto"><i class="fas fa-file-import mr-1"></i>Importar</button>
                    </form>
                </section>
            @else
                <div class="al-ui"><div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>No hay nada nuevo que importar: todas las respuestas reconocidas ya están en el sistema.</div></div></div>
            @endif
        @endif
    </div>

    <script>
    document.getElementById('icImportar')?.addEventListener('submit', function (e) {
        var f = this;
        if (f.dataset.ok === '1') return;
        e.preventDefault();
        if (!f.checkValidity()) { f.reportValidity(); return; }
        var seguir = function () { f.dataset.ok = '1'; f.submit(); };
        var texto = 'Se registrarán {{ $filas ? $nuevas->count() : 0 }} solicitudes como aprobadas (abre el TI · Bachiller a esos grupos). No se envía ningún correo.';
        if (window.PkAlert) PkAlert.confirmar({ title: '¿Importar las solicitudes?', text: texto, confirmButtonText: 'Sí, importar' }).then(function (ok) { if (ok) seguir(); });
        else if (confirm(texto)) seguir();
    });
    </script>
@endsection
