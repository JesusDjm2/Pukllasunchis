@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        $secciones = [
            'completa' => ['Se completan', 'fa-check-circle', 'is-ok', 'Personas con cuenta: se llenan los datos que faltaban (nada ya cargado se reemplaza).'],
            'crear' => [$paso === 'hecho' ? 'Cuentas creadas' : 'Cuentas nuevas', 'fa-user-plus', 'is-oro', 'No tienen cuenta: se crea su cuenta de egresado (contraseña inicial: su DNI) y se cargan sus datos.'],
            'diferencias' => ['Diferencias', 'fa-not-equal', 'is-info', 'El sistema ya tiene otro valor: se respeta lo cargado. Revisa y corrige a mano si la planilla es la correcta.'],
            'revisar' => ['Revisar a mano', 'fa-exclamation-triangle', 'is-mal', 'No se pueden procesar solos: datos dudosos o incompletos en la planilla.'],
            'al_dia' => ['Ya al día', 'fa-equals', '', 'Lo que dice la planilla ya está en el sistema.'],
            'omitido' => ['Omitidos', 'fa-user-slash', '', 'Retirados según la planilla' . ($paso === 'hecho' ? ' y cuentas que desmarcaste' : '') . '.'],
        ];
        if (isset($reporte)) {
            $items = $reporte['items'];
            $porSeccion = [
                'completa' => $items->where('accion', 'completa'),
                'crear' => $items->where('accion', 'crear'),
                'diferencias' => $items->filter(fn ($i) => ! empty($i['diferencias'])),
                'revisar' => $items->where('accion', 'revisar'),
                'al_dia' => $items->where('accion', 'al_dia'),
                'omitido' => $items->whereIn('accion', ['omitido', 'no_marcado']),
            ];
            // Se abre en la primera pestaña con algo que hacer (no en una vacía).
            $pestanaInicial = collect(['completa', 'crear', 'diferencias', 'revisar'])
                ->first(fn ($s) => $porSeccion[$s]->isNotEmpty()) ?? 'completa';
        }
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-file-import"></i> Unidad de Investigación · Titulación</div>
                <h1 class="tl-hero__titulo">Sincronizar planilla</h1>
                <p class="tl-hero__sub">
                    @if ($paso === 'subir')
                        Sube la hoja de expedientes y el sistema completa el proceso de titulación de cada persona. Primero verás una vista previa; nada se guarda hasta que confirmes.
                    @elseif ($paso === 'previa')
                        Vista previa de «{{ $nombreArchivo }}». <strong>Todavía no se guardó nada.</strong>
                    @else
                        Sincronización aplicada desde «{{ $nombreArchivo }}».
                    @endif
                </p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
            </div>
        </header>

        <div class="al-ui">
            @if ($errors->any())
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
            @endif

            @if ($paso === 'subir')
                <div class="row">
                    <div class="col-lg-7">
                        <form method="POST" action="{{ route('titulacion.sincronizar.previa') }}" enctype="multipart/form-data" class="al-card">
                            @csrf
                            <div class="al-card-cab">
                                <span class="al-card-cab-icono"><i class="fas fa-upload"></i></span>
                                <div><h2>1. Sube la planilla</h2><p>Desde Google Sheets: Archivo › Descargar › Valores separados por comas (.csv), de la hoja correspondiente.</p></div>
                            </div>
                            <div class="al-card-cuerpo">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-uppercase text-muted">¿Qué hoja es?</label>
                                    @foreach ($origenes as $clave => $o)
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="origen-{{ $clave }}" name="origen" value="{{ $clave }}" class="custom-control-input" {{ old('origen', 'exp-ppd-marilyn') === $clave ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="origen-{{ $clave }}">{{ $o['etiqueta'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold small text-uppercase text-muted" for="archivo">Archivo CSV</label>
                                    <input type="file" name="archivo" id="archivo" accept=".csv,text/csv" class="form-control-file" required>
                                </div>
                                <button type="submit" class="al-btn al-btn-petroleo" data-cargando="Analizando la planilla…"><i class="fas fa-search"></i> Ver vista previa</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-5">
                        <div class="al-card">
                            <div class="al-card-cab">
                                <span class="al-card-cab-icono"><i class="fas fa-info"></i></span>
                                <div><h2>Qué hace</h2><p>Reglas para no perder ni pisar información.</p></div>
                            </div>
                            <ul class="sy-reglas">
                                <li><i class="fas fa-check"></i> Busca a cada persona por su <strong>DNI</strong>.</li>
                                <li><i class="fas fa-check"></i> <strong>Solo completa lo vacío</strong>: expediente, PTI, exposición, bachiller, tesis y sus RD. Lo ya cargado no se reemplaza; si difiere, te lo muestra.</li>
                                <li><i class="fas fa-check"></i> Quien no tiene cuenta recibe una de egresado (contraseña: su DNI). Tú eliges a quién crear.</li>
                                <li><i class="fas fa-check"></i> Retirados, DNI dudosos y correos repetidos quedan para revisión manual.</li>
                                <li><i class="fas fa-check"></i> Se puede volver a subir la planilla actualizada cuantas veces quieras: no duplica nada.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                @if ($paso === 'hecho')
                    <div class="al-aviso is-info"><i class="fas fa-check-circle"></i>
                        <div><strong>Listo.</strong> Se completaron {{ $porSeccion['completa']->count() }} persona(s) y se crearon {{ $porSeccion['crear']->count() }} cuenta(s).
                            Lo de «Revisar a mano» y «Diferencias» sigue pendiente de una decisión tuya.</div>
                    </div>
                @endif

                <div class="al-stats">
                    @foreach ($secciones as $clave => [$titulo, $icono, $tono])
                        <a href="#{{ $clave }}" data-al-ir class="al-stat text-decoration-none">
                            <span class="al-stat-icono {{ $tono }}"><i class="fas {{ $icono }}"></i></span>
                            <div><div class="al-stat-valor">{{ $porSeccion[$clave]->count() }}</div><div class="al-stat-etiqueta">{{ $titulo }}</div></div>
                        </a>
                    @endforeach
                </div>

                @if (! empty($reporte['asesores_creados']))
                    <div class="al-aviso is-info"><i class="fas fa-user-plus"></i>
                        <div><strong>Agregados al catálogo de asesores:</strong> {{ implode(' · ', $reporte['asesores_creados']) }}.</div>
                    </div>
                @endif
                @if (! empty($reporte['faltantes']))
                    <div class="al-aviso is-warn"><i class="fas fa-user-tie"></i>
                        <div>
                            <strong>Asesores, jurados y dictaminantes que no están en el sistema</strong> (ni como docentes con cuenta ni en el catálogo).
                            @if ($paso === 'previa')
                                Marca los que quieras <strong>agregar al catálogo al confirmar</strong>: se vincularán en esta misma sincronización.
                                Desmarca los que estén mal escritos o repetidos (p. ej. la misma persona con otro apellido) y agrégalos tú en
                                <a href="{{ route('titulacion.asesores.index') }}" target="_blank">Titulación › Asesores</a>.
                            @else
                                Agrégalos en <a href="{{ route('titulacion.asesores.index') }}" target="_blank">Titulación › Asesores</a> y vuelve a sincronizar: se vincularán solos.
                            @endif
                            <div class="d-flex flex-wrap mt-2" style="gap:.35rem .9rem;">
                                @foreach ($reporte['faltantes'] as $nombre => $veces)
                                    @if ($paso === 'previa')
                                        <label class="mb-0 small" style="display:inline-flex;align-items:center;gap:.3rem;">
                                            <input type="checkbox" name="asesores_nuevos[]" value="{{ $nombre }}" form="formAplicar" {{ $veces >= 2 ? 'checked' : '' }}>
                                            {{ $nombre }} <span class="text-muted">· {{ $veces }} {{ $veces === 1 ? 'mención' : 'menciones' }}</span>
                                        </label>
                                    @else
                                        <span class="al-badge is-warn">{{ $nombre }} · {{ $veces }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('titulacion.sincronizar.aplicar') }}" id="formAplicar">
                    @csrf
                    @if ($paso === 'previa')<input type="hidden" name="token" value="{{ $token }}">@endif

                    <div class="al-tabs" role="tablist" aria-label="Resultado" data-al-tabs>
                        @foreach ($secciones as $clave => [$titulo, $icono])
                            <button type="button" class="al-tab {{ $clave === $pestanaInicial ? 'is-activa' : '' }}" role="tab" data-panel="{{ $clave }}">
                                <i class="fas {{ $icono }}"></i> {{ $titulo }} <span class="al-badge is-muted">{{ $porSeccion[$clave]->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($secciones as $clave => [$titulo, $icono, $tono, $ayuda])
                        <section class="al-panel" role="tabpanel" data-panel="{{ $clave }}">
                            <p class="small text-muted mb-2">{{ $ayuda }}</p>
                            <div class="al-card">
                                @if ($clave === 'crear' && $paso === 'previa' && $porSeccion['crear']->isNotEmpty())
                                    <div class="sy-barra">
                                        <label class="mb-0"><input type="checkbox" id="marcarTodos" checked> Marcar / desmarcar todas</label>
                                        <span class="small text-muted">Desmarca a quien no quieras crear ahora. Las marcadas con <span class="al-badge is-warn">verificar nombre</span> se separaron por regla (2 apellidos + nombres).</span>
                                    </div>
                                @endif
                                <ul class="sy-lista">
                                    @forelse ($porSeccion[$clave] as $i)
                                        <li class="sy-fila">
                                            @if ($clave === 'crear' && $paso === 'previa')
                                                <input type="checkbox" name="crear[]" value="{{ preg_replace('/\D/', '', $i['dni']) }}" class="sy-check js-crear" checked aria-label="Crear cuenta de {{ $i['nombre'] }}">
                                            @endif
                                            <div class="sy-cuerpo">
                                                <div class="sy-nombre">
                                                    @if (! empty($i['user_id']) && $clave !== 'crear')
                                                        <a href="{{ route('titulacion.index', ['ver' => $i['user_id']]) }}" target="_blank">{{ $i['nombre'] }}</a>
                                                    @else
                                                        {{ $i['nombre'] }}
                                                    @endif
                                                    @if (! empty($i['condicion']) && $i['condicion'] !== 'Egresado')
                                                        <span class="al-badge is-info">{{ $i['condicion'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="sy-meta">
                                                    DNI {{ $i['dni'] ?: '—' }}
                                                    @if ($i['periodo']) · Cohorte {{ $i['periodo'] }} @endif
                                                    @if ($i['expediente']) · Exp. {{ $i['expediente'] }} @endif
                                                    @if ($i['programa']) · {{ $i['programa'] }} @endif
                                                </div>
                                                @if ($clave === 'crear' && $i['propuesta'])
                                                    @php $p = $i['propuesta']; @endphp
                                                    <div class="sy-propuesta">
                                                        <span><b>Apellidos:</b> {{ $p['apellidos'] }}</span>
                                                        <span><b>Nombres:</b> {{ $p['nombres'] }}</span>
                                                        @if ($p['nombre_por_regla'])<span class="al-badge is-warn">verificar nombre</span>@endif
                                                        <span><b>Correo:</b> {{ $p['correo'] }}</span>
                                                        <span><b>Programa:</b> {{ $p['programa'] }}</span>
                                                    </div>
                                                @endif
                                                @php
                                                    $lineas = match ($clave) {
                                                        'diferencias' => $i['diferencias'],
                                                        'revisar', 'omitido' => $i['motivos'] ?: ($i['accion'] === 'no_marcado' ? ['No marcada: no se creó la cuenta'] : []),
                                                        default => $i['cambios'],
                                                    };
                                                @endphp
                                                @if ($lineas)
                                                    <ul class="sy-detalle {{ in_array($clave, ['revisar', 'diferencias']) ? 'is-alerta' : '' }}">
                                                        @foreach ($lineas as $l)<li>{{ $l }}</li>@endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="al-vacio"><i class="fas fa-inbox"></i>Nadie en esta sección.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </section>
                    @endforeach

                    @if ($paso === 'previa')
                        <div class="sy-confirmar">
                            <div>
                                <strong>¿Todo bien?</strong> Al confirmar se guardan los cambios de «Se completan» y se crean las cuentas marcadas
                                (<span id="cuentaMarcadas">{{ $porSeccion['crear']->count() }}</span>).
                            </div>
                            <div class="d-flex flex-wrap" style="gap:.5rem;">
                                <a href="{{ route('titulacion.sincronizar.form') }}" class="al-btn al-btn-borde">Cancelar</a>
                                <button type="submit" class="al-btn al-btn-oro" data-cargando="Guardando…"><i class="fas fa-check"></i> Confirmar sincronización</button>
                            </div>
                        </div>
                    @else
                        <div class="sy-confirmar">
                            <div>¿Corregiste la planilla? Puedes volver a subirla: solo completará lo que siga faltando.</div>
                            <a href="{{ route('titulacion.sincronizar.form') }}" class="al-btn al-btn-petroleo"><i class="fas fa-redo"></i> Sincronizar otra vez</a>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            // Evita doble envío (la vista previa puede tardar unos segundos con planillas grandes).
            document.querySelectorAll('form').forEach(function (f) {
                f.addEventListener('submit', function () {
                    var b = f.querySelector('[data-cargando]');
                    if (b) { b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + b.dataset.cargando; }
                });
            });
            var todos = document.getElementById('marcarTodos');
            var checks = document.querySelectorAll('.js-crear');
            var cuenta = document.getElementById('cuentaMarcadas');
            function contar() { if (cuenta) cuenta.textContent = Array.prototype.filter.call(checks, function (c) { return c.checked; }).length; }
            if (todos) todos.addEventListener('change', function () { checks.forEach(function (c) { c.checked = todos.checked; }); contar(); });
            checks.forEach(function (c) { c.addEventListener('change', contar); });
        })();
    </script>
@endpush
