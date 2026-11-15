{{-- Formulario para generar una Resolución Directoral en PDF (ver PlantillasRd). Todo viene
     rellenado desde los datos del sistema; se puede corregir antes de emitir. "Vista previa"
     abre el PDF sin registrar nada; "Emitir y registrar" guarda el PDF y registra la RD para
     cada integrante marcado. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
@php
    $volver = route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso']);
    $marcados = collect(old('integrantes', $preseleccion->pluck('id')->all()))->map(fn ($id) => (int) $id);
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $anio = (int) substr($v('fecha') ?: now()->toDateString(), 0, 4);
    $rdPrevia = fn ($u) => $u->seguimientoTitulacion?->resoluciones?->firstWhere('tipo', $def['tipo'])?->numero_rd;
@endphp
@include('titulacion._rdg_estilos')
<link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">

<div class="container-fluid py-3 rdg">
    <a href="{{ $volver }}" class="small text-muted"><i class="fas fa-arrow-left fa-xs mr-1"></i> Volver a la ficha</a>
    <h4 class="font-weight-bold mt-2 mb-1"><i class="fas fa-file-signature mr-2" style="color:#4e73df;"></i>Generar RD · {{ $def['titulo'] }}</h4>
    {{-- Datos que identifican el proceso: siempre a la vista al generar cualquier RD. --}}
    <div class="rdg-clave">
        @if ($seguimiento->numero_expediente)
            <span class="tc-exp">Exp. N° {{ $seguimiento->numero_expediente }}</span>
        @else
            <span class="tc-exp tc-exp--falta">Sin expediente</span>
        @endif
        @include('titulacion._rd_pti_chip', ['seguimiento' => $seguimiento, 'personaId' => $egresado->id])
    </div>
    <p class="rdg-ayuda mb-3">
        {{ $egresado->apellidos }}, {{ $egresado->name }} · {{ $egresado->programa->nombre ?? 'Sin programa' }}.
        Los datos vienen del sistema: revíselos, abra la <strong>vista previa</strong> y, si todo está bien, <strong>emita</strong>.
        Al emitir, la RD queda registrada para cada integrante marcado, con su PDF.
    </p>

    {{-- RD que ya tiene esta persona: pestaña flotante (derecha, a media altura) que abre un panel lateral.
         Las generadas por el sistema se ven dentro del panel; las subidas (PDF firmado o Drive), en otra pestaña. --}}
    @php
        $rdsPrevias = $seguimiento->resoluciones
            ->sortByDesc(fn ($r) => ($r->fecha?->format('Ymd') ?? '00000000').str_pad((string) $r->id, 10, '0', STR_PAD_LEFT))
            ->values();
    @endphp
    <button type="button" class="rdp-pestana" id="rdpAbrir" aria-controls="rdpPanel" aria-expanded="false" title="Ver las RD anteriores de {{ $egresado->name }}">
        <i class="fas fa-history"></i>
        <span class="rdp-pestana-txt">RD anteriores</span>
        <span class="rdp-pestana-n">{{ $rdsPrevias->count() }}</span>
    </button>

    <div class="rdp-fondo" id="rdpFondo" hidden></div>
    <aside class="rdp-panel" id="rdpPanel" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="rdpTitulo">
        <header class="rdp-head">
            <button type="button" class="rdp-icono" id="rdpVolver" title="Volver a la lista" hidden><i class="fas fa-arrow-left"></i></button>
            <div class="rdp-head-txt">
                <h5 id="rdpTitulo">RD anteriores</h5>
                <small id="rdpSub">{{ $egresado->apellidos }}, {{ $egresado->name }} · {{ $rdsPrevias->count() }} {{ $rdsPrevias->count() === 1 ? 'resolución' : 'resoluciones' }}</small>
            </div>
            <a href="#" target="_blank" rel="noopener" class="rdp-icono" id="rdpPestanaNueva" title="Abrir en otra pestaña" hidden><i class="fas fa-external-link-alt"></i></a>
            <button type="button" class="rdp-icono" id="rdpCerrar" title="Cerrar (Esc)"><i class="fas fa-times"></i></button>
        </header>

        <div class="rdp-cuerpo" id="rdpLista">
            @forelse ($rdsPrevias as $r)
                @php
                    [$icono, $color] = \App\Models\Titulacion\Resolucion::ESTILOS[$r->tipo] ?? ['fa-gavel', 'dark'];
                    $sistema = $r->archivo && \App\Services\Titulacion\ArchivosRd::generadoPorSistema($r->archivo);
                    $tipoRd = \App\Models\Titulacion\Resolucion::nombreTipo($r->tipo);
                    // Los históricos guardan el nombre del archivo ("RD 144 - TI ….pdf"): sin el «RD» repetido.
                    $numRd = trim(preg_replace('/^\s*RD[\s\-]*/i', '', (string) $r->numero_rd));
                    if (preg_match('/^(\d{1,4})(?:\s*-\s*((?:19|20)\d{2})\b)?/', $numRd, $mNum)) {
                        $numRd = $mNum[1].(isset($mNum[2]) ? '-'.$mNum[2] : '');
                    }
                @endphp
                <article class="rdp-item">
                    <span class="rdp-item-ico text-{{ $color }}"><i class="fas {{ $icono }}"></i></span>
                    <div class="rdp-item-txt">
                        <strong>{{ $tipoRd }}</strong>
                        @if ($numRd !== '')<span class="rdp-item-num" title="{{ $r->numero_rd }}">RD {{ $numRd }}</span>@endif
                        <small>
                            <i class="far fa-calendar fa-xs mr-1"></i>{{ $r->fecha?->format('d/m/Y') ?? 'Sin fecha' }}
                            @if ($r->detalle)<span class="d-block">{{ \Illuminate\Support\Str::limit($r->detalle, 160) }}</span>@endif
                        </small>
                    </div>
                    <div class="rdp-item-accion">
                        @if (! $r->archivo)
                            <span class="rdp-sin">Sin documento</span>
                        @elseif ($sistema)
                            <a href="{{ $r->archivo }}" class="btn btn-sm btn-primary js-rdp-ver" data-titulo="{{ $tipoRd }}{{ $numRd !== '' ? ' · RD '.$numRd : '' }}">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                        @else
                            <a href="{{ $r->archivo }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Documento subido: se abre en otra pestaña">
                                <i class="fas fa-external-link-alt mr-1"></i>{{ \App\Services\Titulacion\ArchivosRd::etiqueta($r->archivo) }}
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rdp-vacio"><i class="fas fa-folder-open"></i>Todavía no tiene resoluciones registradas.</div>
            @endforelse
        </div>

        <div class="rdp-visor" id="rdpVisor" hidden>
            <div class="rdp-carga" id="rdpCarga"><i class="fas fa-circle-notch fa-spin mr-1"></i> Cargando la RD…</div>
            <iframe id="rdpFrame" title="Resolución Directoral"></iframe>
        </div>

        @if ($rdsPrevias->isNotEmpty())
            <footer class="rdp-pie" id="rdpPie">
                <span><i class="fas fa-eye fa-xs mr-1"></i>Del sistema: se ven aquí</span>
                <span><i class="fas fa-external-link-alt fa-xs mr-1"></i>Subidas o Drive: otra pestaña</span>
            </footer>
        @endif
    </aside>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('titulacion.rd.emitir', [$egresado->id, $plantilla]) }}" id="rdgForm">
        @csrf

        <div class="rdg-card">
            <div class="rdg-h"><span class="n">1</span> Integrantes del grupo</div>
            @php
                // Para el buscador: todos los del programa, con lo que hace falta para mostrarlos y validarlos.
                $candidatosJs = $candidatos->concat($preseleccion)->unique('id')->map(fn ($u) => [
                    'id' => $u->id,
                    'nombre' => $u->apellidos.', '.$u->name,
                    'dni' => $u->dni ?: '—',
                    'meta' => trim(($u->condicion === 'Egresado' ? 'Egresado(a)' : '').($u->seguimientoTitulacion?->numero_expediente ? ' · Exp. '.$u->seguimientoTitulacion->numero_expediente : '')
                        .($u->seguimientoTitulacion ? (fn ($d) => match ($d['estado']) {
                            'completa' => ' · RD PTI '.$d['mostrar'],
                            'incompleta' => ' · RD PTI '.$d['mostrar'].' (falta año)',
                            default => ' · sin RD de PTI',
                        })(\App\Services\Titulacion\RdPti::de($u->seguimientoTitulacion)) : ''), ' ·'),
                    'previa' => $rdPrevia($u),
                    'exp' => $u->seguimientoTitulacion?->numero_expediente,
                    'falta' => \App\Services\Titulacion\ReglasProceso::faltaIntegrante($u->seguimientoTitulacion, $def['tipo']),
                    'buscar' => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($u->apellidos.' '.$u->name.' '.$u->dni)),
                ])->keyBy('id');
            @endphp
            <p class="rdg-ayuda mb-2">
                @if ($plantilla === 'retiro_grupo')
                    <strong>{{ $egresado->apellidos }}, {{ $egresado->name }}</strong> es quien se retira. Los demás integrantes son quienes continúan con el PTI.
                    Al emitir, deja de figurar en este grupo y su PTI pasa a su historial; conserva sus RD.
                @elseif ($plantilla === 'incorporacion_grupo')
                    Ya están puestos los integrantes del grupo que recibe. <strong>Agregue con el buscador a quien se incorpora.</strong>
                    Al emitir, entra al grupo con el mismo PTI (título, RD, asesor y vigencia). Si pertenece a otro grupo, primero emita su RD de retiro.
                @else
                    {{ $preseleccion->count() > 1 ? 'Ya están puestos esta persona y su grupo (según las RD o actas que comparten).' : 'Está puesta esta persona.' }}
                    Si el grupo recién presenta su PTI, agréguelos con el buscador: desde esta RD quedarán agrupados.
                @endif
            </p>
            <div class="rdg-elegidos" id="rdgElegidos" aria-live="polite"></div>
            <div id="rdgInputs"></div>
            <div class="rdg-agregar">
                <label for="rdgBuscar" class="mb-1">Agregar integrante</label>
                <input type="search" class="form-control form-control-sm" id="rdgBuscar" placeholder="Escriba nombre o DNI (mismo programa)…" autocomplete="off">
                <div class="rdg-resultados" id="rdgResultados" hidden></div>
            </div>
            @error('integrantes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <script>
                window.RDG_INTEGRANTES = {
                    candidatos: @json($candidatosJs),
                    titular: {{ $egresado->id }},
                    elegidos: @json($marcados->values()),
                };
            </script>
            <div class="rdg-ayuda mt-1"><strong id="rdgCuenta">{{ $marcados->count() }}</strong> integrante(s) en esta RD.</div>
        </div>

        <div class="rdg-card">
            <div class="rdg-h"><span class="n">2</span> Datos de la resolución</div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="numero_rd">N° de RD</label>
                    <div class="rdg-numero">
                        <input type="text" name="numero_rd" id="numero_rd" class="form-control form-control-sm" value="{{ $v('numero_rd') }}" inputmode="numeric" required>
                        <span>-<span id="rdgAnio">{{ $anio }}</span></span>
                    </div>
                    <div class="rdg-ayuda">Sugerido: siguiente de las RD de Titulación de este año.</div>
                </div>
                <div class="form-group col-md-3">
                    <label for="fecha">Fecha de la RD</label>
                    <input type="text" name="fecha" id="fecha" class="form-control form-control-sm js-rdg-fecha" value="{{ $v('fecha') ?: now()->toDateString() }}" required>
                </div>
                @if (in_array($plantilla, \App\Services\Titulacion\PlantillasRd::CAMBIAN_GRUPO, true))
                    {{-- Quien sale o entra y el grupo concuerdan por separado. Sin elegir, según la ficha de cada persona. --}}
                    <input type="hidden" name="genero" value="{{ $v('genero') }}">
                    @foreach (['genero_aparte' => $plantilla === 'retiro_grupo' ? 'Quien se retira' : 'Quien se incorpora', 'genero_resto' => $plantilla === 'retiro_grupo' ? 'Quienes continúan' : 'El grupo que recibe'] as $campoG => $etiquetaG)
                        <div class="form-group col-md-3">
                            <label for="{{ $campoG }}">{{ $etiquetaG }} (para concordar)</label>
                            <select name="{{ $campoG }}" id="{{ $campoG }}" class="form-control form-control-sm">
                                <option value="">Según su ficha</option>
                                <option value="f" @selected($v($campoG) === 'f')>Mujer o mujeres</option>
                                <option value="m" @selected($v($campoG) === 'm')>Hombre, hombres o mixto</option>
                            </select>
                        </div>
                    @endforeach
                    <div class="form-group col-12 mb-0"><div class="rdg-ayuda">Revise «la/el/las/los» en la vista previa: si la ficha no indica el género, se asume masculino.</div></div>
                @else
                <div class="form-group col-md-6">
                    <label for="genero">Integrantes (para concordar la RD)</label>
                    <select name="genero" id="genero" class="form-control form-control-sm">
                        @foreach (\App\Services\Titulacion\PlantillasRd::GENEROS as $clave => $texto)
                            <option value="{{ $clave }}" @selected($v('genero') === $clave)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <div class="rdg-ayuda">Según el género de su ficha: decide «la/el/las/los», «egresada(s)», «APTA/APTOS», etc.</div>
                </div>
                @endif
                <div class="form-group col-md-6">
                    <label for="programa">Programa de Estudios</label>
                    {{-- Es el programa del alumno: no se edita (el servidor usa siempre el oficial). Solo si no tiene programa se escribe. --}}
                    @if ($egresado->programa)
                        <input type="text" name="programa" id="programa" class="form-control form-control-sm" value="{{ $v('programa') }}" readonly
                               title="Programa del alumno: no se puede cambiar" style="background-color:var(--rdg-soft, #f1f5f9); cursor:not-allowed;">
                        <div class="rdg-ayuda"><i class="fas fa-lock fa-xs mr-1"></i>Es el programa al que pertenece {{ $egresado->name }}.</div>
                    @else
                        <input type="text" name="programa" id="programa" class="form-control form-control-sm" value="{{ $v('programa') }}" required>
                        <div class="rdg-ayuda text-warning">Esta persona no tiene programa asignado: escríbalo y corrija su ficha.</div>
                    @endif
                </div>
                <div class="form-group col-md-6">
                    <label for="lema">Lema del año (encabezado)</label>
                    <input type="text" name="lema" id="lema" class="form-control form-control-sm" value="{{ $v('lema') }}" required>
                </div>
            </div>
        </div>

        <div class="rdg-card">
            <div class="rdg-h"><span class="n">3</span> Contenido de la resolución</div>
            <div class="form-row">
                @foreach ($campos as $campo => [$etiqueta, $tipo, $obligatorio])
                    @if ($tipo === 'asesor')
                        <div class="form-group col-md-5">
                            <label for="asesor">{{ $etiqueta }}</label>
                            <select name="asesor" id="asesor" class="form-control form-control-sm">
                                <option value="">— Elegir —</option>
                                <optgroup label="Docentes">
                                    @foreach ($docentes as $d)
                                        <option value="{{ $d->id }}" data-nombre="{{ trim($d->name.' '.$d->apellidos) }}" @selected((string) $v('asesor') === (string) $d->id)>{{ $d->apellidos }}, {{ $d->name }}</option>
                                    @endforeach
                                </optgroup>
                                @if ($asesores->isNotEmpty())
                                    <optgroup label="Asesores registrados">
                                        @foreach ($asesores as $a)
                                            <option value="a{{ $a->id }}" data-nombre="{{ $a->nombreCompleto() }}" @selected((string) $v('asesor') === 'a'.$a->id)>{{ $a->nombreCompleto() }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <div class="rdg-ayuda">{{ $plantilla === 'cambio_asesor' ? 'Al emitir, reemplaza al asesor(a) del TI de cada integrante.' : 'Al emitir, queda como asesor(a) del PTI de cada integrante.' }}</div>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="asesor_nombre">Nombre como irá en la RD</label>
                            <input type="text" name="asesor_nombre" id="asesor_nombre" class="form-control form-control-sm" value="{{ $v('asesor_nombre') }}" placeholder="Ej.: Mag. Rosa Quispe Huamán" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="asesor_cargo">Designación</label>
                            <select name="asesor_cargo" id="asesor_cargo" class="form-control form-control-sm">
                                @foreach (['Asesor', 'Asesora'] as $c)
                                    <option value="{{ $c }}" @selected($v('asesor_cargo') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        @continue
                    @endif
                    <div class="form-group {{ $tipo === 'area' ? 'col-12' : ($tipo === 'cargo' ? 'col-md-2' : 'col-md-4') }}">
                        <label for="{{ $campo }}">{{ $etiqueta }}</label>
                        @switch($tipo)
                            @case('cargo')
                                <select name="{{ $campo }}" id="{{ $campo }}" class="form-control form-control-sm">
                                    @foreach (['Asesor', 'Asesora'] as $c)
                                        <option value="{{ $c }}" @selected($v($campo) === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @break
                            @case('area')
                                <textarea name="{{ $campo }}" id="{{ $campo }}" class="form-control form-control-sm" rows="{{ $campo === 'jurado' ? 3 : 2 }}" @required($obligatorio)>{{ $v($campo) }}</textarea>
                                @break
                            @case('fecha')
                                {{-- Sin dato registrado, hoy (editable). El calendario lo pone flatpickr (más abajo). --}}
                                <input type="text" name="{{ $campo }}" id="{{ $campo }}" class="form-control form-control-sm js-rdg-fecha" value="{{ $v($campo) ?: now()->toDateString() }}" @required($obligatorio)>
                                @break
                            @case('hora')
                                <input type="time" name="{{ $campo }}" id="{{ $campo }}" class="form-control form-control-sm" value="{{ $v($campo) }}" @required($obligatorio)>
                                @break
                            @default
                                @if ($campo === 'expedientes')
                                    {{-- Como el programa: no se edita. Sale del expediente de cada integrante (se arma al agregar o quitar). --}}
                                    <input type="text" name="expedientes" id="expedientes" class="form-control form-control-sm" value="{{ $v('expedientes') }}" readonly
                                           title="Expediente de cada integrante: no se puede cambiar aquí" style="background-color:var(--rdg-soft, #f1f5f9); cursor:not-allowed;">
                                    <div class="rdg-ayuda"><i class="fas fa-lock fa-xs mr-1"></i>Es el N° de expediente de cada integrante; se corrige en su ficha (Datos y grupo).</div>
                                    @break
                                @endif
                                <input type="text" name="{{ $campo }}" id="{{ $campo }}" class="form-control form-control-sm" value="{{ $v($campo) }}"
                                       @if ($tipo === 'numero') inputmode="numeric" placeholder="Ej.: 12" @endif
                                       @if ($campo === 'rd_referida') placeholder="Ej.: 045-2025" @endif
                                       @required($obligatorio)>
                        @endswitch
                        @if ($campo === 'rd_referida')
                            <div class="rdg-ayuda">Solo el número y año; la RD agrega «-DG/EESP-…».</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rdg-acciones">
            <span class="rdg-ayuda">La vista previa no registra nada.</span>
            <a href="{{ $volver }}" class="btn btn-sm btn-light ml-auto">Cancelar</a>
            <button type="submit" name="accion" value="previa" formtarget="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye mr-1"></i> Vista previa (PDF)
            </button>
            <button type="submit" name="accion" value="emitir" class="btn btn-sm btn-primary" id="rdgEmitir">
                <i class="fas fa-file-signature mr-1"></i> Emitir y registrar
            </button>
        </div>
    </form>
</div>

{{-- Calendario de las fechas (flatpickr, en español). --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
<script>
(function () {
    var form = document.getElementById('rdgForm');
    // Integrantes: tarjetas de los elegidos (el titular, fijo) + buscador que agrega.
    var D = window.RDG_INTEGRANTES, C = D.candidatos;
    var elegidos = D.elegidos.map(Number).filter(function (id) { return C[id] && (!C[id].falta || id === D.titular); });
    if (elegidos.indexOf(D.titular) === -1) elegidos.unshift(D.titular);
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var cajaElegidos = document.getElementById('rdgElegidos'), cajaInputs = document.getElementById('rdgInputs');
    var buscar = document.getElementById('rdgBuscar'), resultados = document.getElementById('rdgResultados');

    var contar = function () { return elegidos.length; };
    function pintar() {
        cajaElegidos.innerHTML = elegidos.map(function (id) {
            var c = C[id] || { nombre: 'Persona #' + id, dni: '—', meta: '' };
            var titular = id === D.titular;
            return '<div class="rdg-chip' + (titular ? ' is-titular' : '') + '">'
                + '<span class="rdg-chip-ini">' + esc((c.nombre || '?').charAt(0)) + '</span>'
                + '<span class="rdg-chip-txt"><strong>' + esc(c.nombre) + '</strong>'
                + '<small>DNI ' + esc(c.dni) + (c.meta ? ' · ' + esc(c.meta) : '') + (titular ? ' · titular' : '') + '</small>'
                + (c.previa ? '<span class="rdg-aviso">Ya tiene RD ' + esc(c.previa) + '</span>' : '') + '</span>'
                + (titular ? '<i class="fas fa-lock rdg-chip-lock" title="La RD se genera desde su ficha"></i>'
                           : '<button type="button" data-quitar="' + id + '" aria-label="Quitar a ' + esc(c.nombre) + '">&times;</button>')
                + '</div>';
        }).join('');
        cajaInputs.innerHTML = elegidos.map(function (id) { return '<input type="hidden" name="integrantes[]" value="' + id + '">'; }).join('');
        document.getElementById('rdgCuenta').textContent = elegidos.length;
        // Expediente(s): los de los integrantes, en el orden de sus nombres en la RD (igual lo arma el servidor).
        var exp = document.getElementById('expedientes');
        if (exp) {
            exp.value = elegidos.map(function (id) { return C[id] || {}; })
                .sort(function (a, b) { return String(a.nombre || '').localeCompare(String(b.nombre || ''), 'es'); })
                .map(function (c) { return c.exp; })
                .filter(function (e, i, todos) { return e && todos.indexOf(e) === i; }).join(', ');
        }
    }
    cajaElegidos.addEventListener('click', function (e) {
        var b = e.target.closest('[data-quitar]');
        if (!b) return;
        elegidos = elegidos.filter(function (id) { return id !== +b.dataset.quitar; });
        pintar();
    });

    function buscarCoincidencias() {
        var q = buscar.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        if (q.length < 2) { resultados.hidden = true; resultados.innerHTML = ''; return; }
        var lista = Object.keys(C).map(function (k) { return C[k]; })
            .filter(function (c) { return elegidos.indexOf(c.id) === -1 && c.buscar.indexOf(q) !== -1; })
            .slice(0, 8);
        resultados.innerHTML = lista.length ? lista.map(function (c) {
            return '<button type="button" class="rdg-res' + (c.falta ? ' is-bloqueado' : '') + '" data-agregar="' + c.id + '"' + (c.falta ? ' disabled' : '') + '>'
                + '<strong>' + esc(c.nombre) + '</strong> <small>DNI ' + esc(c.dni) + (c.meta ? ' · ' + esc(c.meta) : '') + '</small>'
                + (c.falta ? '<span class="rdg-res-falta"><i class="fas fa-lock fa-xs"></i> Falta: ' + esc(c.falta) + '</span>' : '')
                + (!c.falta && c.previa ? '<span class="rdg-aviso">Ya tiene RD ' + esc(c.previa) + '</span>' : '')
                + '</button>';
        }).join('') : '<div class="rdg-res-vacio">Nadie del programa coincide con «' + esc(buscar.value) + '».</div>';
        resultados.hidden = false;
    }
    buscar.addEventListener('input', buscarCoincidencias);
    buscar.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    resultados.addEventListener('click', function (e) {
        var b = e.target.closest('[data-agregar]');
        if (!b || b.disabled) return;
        elegidos.push(+b.dataset.agregar);
        buscar.value = '';
        resultados.hidden = true;
        pintar();
        buscar.focus();
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.rdg-agregar')) resultados.hidden = true;
    });
    pintar();

    // Fechas: calendario en español (mes y año navegables, botón «Hoy»). Se guarda AAAA-MM-DD y se
    // muestra «22 de octubre de 2026»; también se puede escribir. Sin la librería, queda el campo de texto.
    if (window.flatpickr) {
        document.querySelectorAll('.js-rdg-fecha').forEach(function (campo) {
            flatpickr(campo, {
                locale: flatpickr.l10ns.es, dateFormat: 'Y-m-d', altInput: true, altFormat: 'larga',
                // «22 de octubre de 2026»: el mes en minúscula, como en la RD.
                formatDate: function (d, formato, loc) {
                    return formato === 'larga'
                        ? d.getDate() + ' de ' + loc.months.longhand[d.getMonth()].toLowerCase() + ' de ' + d.getFullYear()
                        : flatpickr.formatDate(d, formato, loc);
                },
                allowInput: true, monthSelectorType: 'dropdown', disableMobile: true,
                onReady: function (s, t, fp) {
                    fp.altInput.classList.add('rdg-fecha-visible');
                    var hoy = document.createElement('button');
                    hoy.type = 'button'; hoy.className = 'rdg-fp-hoy'; hoy.textContent = 'Hoy';
                    hoy.addEventListener('click', function () { fp.setDate(new Date(), true); fp.close(); });
                    fp.calendarContainer.appendChild(hoy);
                },
                onChange: function (s, t, fp) { fp.input.dispatchEvent(new Event('change')); },
            });
        });
    }
    document.getElementById('fecha').addEventListener('change', function () {
        if (this.value) document.getElementById('rdgAnio').textContent = this.value.slice(0, 4);
    });

    // Elegir asesor(a) rellena el nombre que irá en la RD (se puede anteponer "Mag.", "Dra.", etc.).
    var asesor = document.getElementById('asesor');
    if (asesor) asesor.addEventListener('change', function () {
        var op = this.selectedOptions[0];
        if (op && op.dataset.nombre) document.getElementById('asesor_nombre').value = op.dataset.nombre;
    });

    // RD anteriores: pestaña flotante → panel lateral con la lista. Las del sistema se ven dentro del
    // panel (se ensancha); «volver» regresa a la lista. Todo sin salir del formulario.
    (function () {
        var panel = document.getElementById('rdpPanel'), fondo = document.getElementById('rdpFondo');
        var abrirBtn = document.getElementById('rdpAbrir');
        if (!panel || !abrirBtn) return;
        // Fuera del contenedor de la página: así nada lo recorta ni lo desplaza.
        document.body.appendChild(abrirBtn); document.body.appendChild(fondo); document.body.appendChild(panel);
        var lista = document.getElementById('rdpLista'), visor = document.getElementById('rdpVisor');
        var frame = document.getElementById('rdpFrame'), carga = document.getElementById('rdpCarga');
        var titulo = document.getElementById('rdpTitulo'), sub = document.getElementById('rdpSub');
        var volver = document.getElementById('rdpVolver'), nueva = document.getElementById('rdpPestanaNueva');
        var pie = document.getElementById('rdpPie');
        var tituloLista = titulo.textContent, subLista = sub.textContent, ultimoFoco = null;

        function verLista() {
            panel.classList.remove('is-visor');
            lista.hidden = false; visor.hidden = true; volver.hidden = true; nueva.hidden = true;
            if (pie) pie.hidden = false;
            titulo.textContent = tituloLista; sub.textContent = subLista;
            frame.src = 'about:blank';
        }
        function verRd(href, nombre) {
            panel.classList.add('is-visor');
            lista.hidden = true; visor.hidden = false; volver.hidden = false; nueva.hidden = false;
            if (pie) pie.hidden = true;
            titulo.textContent = nombre || 'Resolución Directoral'; sub.textContent = 'Generada por el sistema';
            nueva.href = href;
            carga.hidden = false; frame.style.visibility = 'hidden';
            frame.onload = function () { carga.hidden = true; frame.style.visibility = ''; };
            frame.src = href;
            volver.focus();
        }
        function abrir() {
            ultimoFoco = document.activeElement;
            verLista();
            fondo.hidden = false;
            requestAnimationFrame(function () { panel.classList.add('is-open'); fondo.classList.add('is-open'); });
            panel.setAttribute('aria-hidden', 'false'); abrirBtn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('rdp-abierto');
            document.getElementById('rdpCerrar').focus();
        }
        function cerrar() {
            panel.classList.remove('is-open'); fondo.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true'); abrirBtn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('rdp-abierto');
            setTimeout(function () { if (!panel.classList.contains('is-open')) { fondo.hidden = true; verLista(); } }, 280);
            if (ultimoFoco) ultimoFoco.focus();
        }
        abrirBtn.addEventListener('click', abrir);
        document.getElementById('rdpCerrar').addEventListener('click', cerrar);
        fondo.addEventListener('click', cerrar);
        volver.addEventListener('click', verLista);
        lista.addEventListener('click', function (e) {
            var a = e.target.closest('.js-rdp-ver');
            if (!a) return;
            e.preventDefault();
            verRd(a.href, a.dataset.titulo);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !panel.classList.contains('is-open')) return;
            if (panel.classList.contains('is-visor')) verLista(); else cerrar();
        });
    })();

    // Emitir registra la RD para todo el grupo: se confirma con el sistema de alertas propio.
    form.addEventListener('submit', function (e) {
        var boton = e.submitter;
        if (!boton || boton.value !== 'emitir' || form.dataset.ok === '1') return;
        e.preventDefault();
        var n = contar();
        var titulo = '¿Emitir la RD N° ' + document.getElementById('numero_rd').value + '-' + document.getElementById('rdgAnio').textContent + '?';
        var texto = 'Se guardará el PDF y quedará registrada para ' + n + ' integrante(s).';
        var seguir = function () {
            form.dataset.ok = '1';
            var h = document.createElement('input'); h.type = 'hidden'; h.name = 'accion'; h.value = 'emitir';
            form.appendChild(h); form.removeAttribute('target'); form.submit();
        };
        if (window.PkAlert) PkAlert.confirmar({ icon: 'question', title: titulo, text: texto, confirmButtonText: 'Sí, emitir' }).then(function (ok) { if (ok) seguir(); });
        else if (confirm(titulo + '\n' + texto)) seguir();
    });
})();
</script>
@endsection
