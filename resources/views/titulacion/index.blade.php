@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    {{-- Diseño del listado en css/pk-titulacion.css (sección "Listado"). --}}
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-user-graduate"></i> Unidad de Investigación</div>
                <h1 class="tl-hero__titulo">Titulación</h1>
                <p class="tl-hero__sub">Seguimiento del proceso de cada egresado: PTI, Trabajo de Investigación y Tesis.</p>
            </div>
            <div class="tl-hero__acciones">
                @php $cartasPorResponder = \App\Models\Titulacion\CartaAceptacion::where('estado', 'enviada')->distinct('lote')->count('lote'); @endphp
                <a href="{{ route('titulacion.cartas.index', $cartasPorResponder ? ['estado' => 'enviada'] : []) }}" class="tl-hero__btn" title="Solicitudes de carta de aceptación de la Institución Educativa (las gestiona Secretaría)">
                    <i class="fas fa-envelope-open-text"></i>Cartas de aceptación
                    @if ($cartasPorResponder)<span class="badge badge-warning ml-1">{{ $cartasPorResponder }}</span>@endif
                </a>
                <a href="{{ route('titulacion.asesores.index') }}" class="tl-hero__btn" title="Asesores y jurados que no son docentes con cuenta">
                    <i class="fas fa-user-tie"></i>Asesores
                </a>
                <a href="{{ route('titulacion.demograficos') }}" class="tl-hero__btn" title="Perfil demográfico de quienes están en el proceso">
                    <i class="fas fa-chart-pie"></i>Datos demográficos
                </a>
                <a href="{{ route('titulacion.sincronizar.form') }}" @unless (\App\Services\Titulacion\AreasTitulacion::puede('super-admin')) hidden @endunless class="tl-hero__btn" title="Completar el proceso de cada persona desde la planilla de expedientes (EXP. PPD / FID)">
                    <i class="fas fa-file-import"></i>Sincronizar planilla
                </a>
                <a href="{{ route('titulacion.rdpti.normalizar') }}" @unless (\App\Services\Titulacion\AreasTitulacion::puede('super-admin')) hidden @endunless class="tl-hero__btn" title="Dejar la RD del PTI importada del Excel en formato «088-2021», con vista previa">
                    <i class="fas fa-broom"></i>Normalizar RD del PTI
                </a>
                @if ($totalPendientes > 0 && \App\Services\Titulacion\AreasTitulacion::puedeRuta('crear-cuentas-masivo.preview'))
                    <a href="{{ route('titulacion.crear-cuentas-masivo.preview') }}" class="tl-hero__btn tl-hero__btn--oro">
                        <i class="fas fa-users"></i>Crear cuentas pendientes
                    </a>
                @endif
            </div>
            {{-- Cifras y accesos rápidos en una sola tira (scroll lateral si no caben). Cada uno filtra la lista. --}}
            @php
                $tareasPanel = collect(['por_vencer' => ['PTI por vencer (≤ 4 meses)', 'fa-hourglass-half', 'PTI que vencen en 4 meses o menos.']])
                    ->merge(\App\Http\Controllers\Titulacion\TitulacionController::TAREAS)
                    ->map(fn ($t, $clave) => [
                        'etiqueta' => $t[0], 'icono' => $t[1], 'ayuda' => $t[2],
                        'total' => $clave === 'por_vencer' ? $conteoVigencia['por_vencer'] : $conteoTareas[$clave],
                        'url' => route('titulacion.index', $clave === 'por_vencer' ? ['vigencia' => 'por_vencer'] : ['tarea' => $clave]),
                        'activa' => $clave === 'por_vencer' ? $filtroVigencia === 'por_vencer' : $filtroTarea === $clave,
                        // Prioridad por color: urgente (vence el PTI), esta semana (exposiciones/sustentaciones), informativo.
                        'prioridad' => match ($clave) { 'por_vencer' => 'rojo', 'proximas' => 'oro', default => 'azul' },
                    ])
                    ->filter(fn ($t) => $t['total'] > 0);
            @endphp
            <div class="tl-hero__stats" data-tl-scroll>
                <div class="tl-hero__pista">
                    <a href="{{ route('titulacion.index') }}" class="tl-stat tl-stat--link {{ request()->except('page') ? '' : 'is-activo' }}" title="Personas con proceso de titulación registrado">
                        <span class="tl-stat__num">{{ $totalEgresados }}</span>
                        <span class="tl-stat__eti">Registrados</span>
                    </a>
                    @if ($totalActivosElegibles > 0)
                        <a href="{{ route('titulacion.index', ['estado' => 'activos']) }}" class="tl-stat tl-stat--link tl-stat--azul {{ request('estado') === 'activos' ? 'is-activo' : '' }}"
                           title="Alumnos activos que ya pueden iniciar su proceso">
                            <span class="tl-stat__num">{{ $totalActivosElegibles }}</span>
                            <span class="tl-stat__eti">Pueden iniciar</span>
                        </a>
                    @endif
                    @if ($totalPendientes > 0)
                        <a href="{{ route('titulacion.index', ['estado' => 'pendientes']) }}" class="tl-stat tl-stat--link tl-stat--oro {{ request('estado') === 'pendientes' ? 'is-activo' : '' }}"
                           title="Personas de las planillas que aún no tienen cuenta o ficha">
                            <span class="tl-stat__num">{{ $totalPendientes }}</span>
                            <span class="tl-stat__eti">Pendientes de importación</span>
                        </a>
                    @endif
                    @if ($totalRetirados > 0)
                        <a href="{{ route('titulacion.index', ['estado' => 'retirados']) }}" class="tl-stat tl-stat--link tl-stat--rojo {{ request('estado') === 'retirados' ? 'is-activo' : '' }}"
                           title="Se retiraron de la institución: perfil «Retirado» o marcados así en las hojas de expedientes">
                            <span class="tl-stat__num">{{ $totalRetirados }}</span>
                            <span class="tl-stat__eti">Retirados</span>
                        </a>
                    @endif
                    @if ($tareasPanel->isNotEmpty())
                        <span class="tl-hero__grupo"><i class="fas fa-bolt"></i>Por atender</span>
                        @foreach ($tareasPanel as $t)
                            <a href="{{ $t['url'] }}" title="{{ $t['ayuda'] }}"
                               class="tl-stat tl-stat--link tl-stat--{{ $t['prioridad'] }} {{ $t['activa'] ? 'is-activo' : '' }}">
                                <span class="tl-stat__num">{{ $t['total'] }}</span>
                                <span class="tl-stat__eti"><i class="fas {{ $t['icono'] }}"></i>{{ $t['etiqueta'] }}</span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </header>

        {{-- Con ?ver= el mensaje se muestra dentro de la ficha abierta, no aquí. --}}
        @if (Session::has('success') && ! request()->filled('ver'))
            <div class="alert alert-success alert-dismissible fade show js-flash-toast" role="alert" data-toast="{{ Session::get('success') }}">
                {{ Session::get('success') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif
        @if (Session::has('error') && ! request()->filled('ver'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif

        @foreach ($reportesDeDatos as $reporte)
            <div class="tl-reporte" role="alert">
                <i class="fas fa-flag"></i>
                <div>
                    <strong>{{ $reporte->data['alumno_nombre'] ?? 'Egresado(a)' }}</strong> indicó que un dato no coincide
                    <span class="tl-reporte__fecha">· {{ $reporte->created_at->format('d/m/Y H:i') }}</span>
                    <div class="tl-reporte__msg">“{{ $reporte->data['mensaje'] ?? $reporte->data['resumen'] ?? '' }}”</div>
                </div>
            </div>
        @endforeach

        @php
            $qFiltroBase = array_filter(['search' => request('search')], fn ($v) => $v !== null && $v !== '');
        @endphp
        {{-- Recorrido del proceso: un estado por tramo, en el orden del proceso. Cada tramo filtra la lista. --}}
        <section class="tl-seccion">
            <div class="tl-seccion__cab">
                <h2 class="tl-seccion__titulo"><i class="fas fa-route"></i> Recorrido del proceso</h2>
                @if (request()->filled('estado_titulacion_id') || request()->filled('estado'))
                    <a href="{{ route('titulacion.index', $qFiltroBase) }}" class="tl-seccion__link"><i class="fas fa-times"></i> Quitar filtro de estado</a>
                @endif
            </div>
            <div class="tl-recorrido">
                <div class="tl-recorrido__pasos">
                    @foreach ($estados as $estado)
                        @php $activo = (int) request('estado_titulacion_id') === $estado->id && request('estado') !== 'pendientes'; @endphp
                        <a href="{{ route('titulacion.index', $qFiltroBase + ['estado_titulacion_id' => $estado->id]) }}"
                           class="tl-paso {{ $activo ? 'is-activo' : '' }} {{ ($conteoPorEstado[$estado->id] ?? 0) === 0 ? 'is-vacio' : '' }}" title="{{ $estado->nombre }}">
                            <span class="tl-paso__num">{{ $conteoPorEstado[$estado->id] ?? 0 }}</span>
                            <span class="tl-paso__eti">{{ $estado->nombre }}</span>
                        </a>
                    @endforeach
                </div>
                {{-- «Pueden iniciar» y «Pendientes de importación» se filtran desde el hero. --}}
            </div>
        </section>

        <section class="tl-tabla-card">
            <form method="GET" action="{{ route('titulacion.index') }}" class="tl-filtros">
                {{-- El filtro de estado ya se elige en el recorrido de arriba; los
                     demás filtros conservan ese filtro activo vía estos hidden. --}}
                @if (request()->filled('estado_titulacion_id'))
                    <input type="hidden" name="estado_titulacion_id" value="{{ request('estado_titulacion_id') }}">
                @endif
                @if (in_array(request('estado'), ['pendientes', 'activos', 'retirados'], true))
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                @endif
                @if ($filtroTarea)
                    <input type="hidden" name="tarea" value="{{ $filtroTarea }}">
                @endif
                <div class="tl-filtro tl-filtro--buscar">
                    <label for="tlBuscar">Buscar</label>
                    <div class="tl-buscar">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="tlBuscar" class="form-control" value="{{ request('search') }}" placeholder="Nombre, DNI, Acta o RD…">
                    </div>
                </div>
                {{-- Se aplican al elegir (data-tl-auto). Modalidad → Programa → Promoción: cada lista solo ofrece
                     lo que hay con las anteriores (sin contadores: la cantidad se ve en el resultado). --}}
                <div class="tl-filtro">
                    <label for="tlModalidad">Modalidad</label>
                    <select name="modalidad" id="tlModalidad" class="form-control" data-tl-auto>
                        <option value="">Todas</option>
                        @foreach (\App\Enums\Modalidad::cases() as $mod)
                            <option value="{{ $mod->value }}" {{ request('modalidad') === $mod->value ? 'selected' : '' }}>{{ $mod->value }} — {{ $mod->etiqueta() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="tlPrograma">Programa</label>
                    <select name="programa_id" id="tlPrograma" class="form-control" data-tl-auto>
                        <option value="">Todos</option>
                        @foreach ($programasFiltro->groupBy(fn ($p) => $p->modalidad->value) as $modalidad => $progs)
                            <optgroup label="{{ $modalidad }}">
                                @foreach ($progs as $prog)
                                    <option value="{{ $prog->id }}" {{ (int) request('programa_id') === $prog->id ? 'selected' : '' }}>
                                        {{ preg_replace('/^Programa\s+/iu', '', $prog->nombre) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="tlAnio">Promoción</label>
                    <select name="anio" id="tlAnio" class="form-control" data-tl-auto>
                        <option value="">Todas</option>
                        @if ($opcionesPromocion['egresados'])
                            <optgroup label="Año de egreso">
                                @foreach ($opcionesPromocion['egresados'] as $clave => [$etiqueta, $n, $esSemestre])
                                    <option value="{{ $clave }}" {{ request('anio') === $clave ? 'selected' : '' }}>{{ $esSemestre ? "\u{A0}\u{A0}\u{A0}└ ".$etiqueta : $etiqueta }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($opcionesPromocion['ciclos'])
                            <optgroup label="Aún estudian (ciclo actual)">
                                @foreach ($opcionesPromocion['ciclos'] as $clave => [$etiqueta, $n])
                                    <option value="{{ $clave }}" {{ request('anio') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if (request()->filled('anio') && ! isset($opcionesPromocion['egresados'][request('anio')]) && ! isset($opcionesPromocion['ciclos'][request('anio')]))
                            {{-- La elegida ya no tiene a nadie con los otros filtros: se mantiene visible para poder quitarla. --}}
                            <option value="{{ request('anio') }}" selected>{{ ltrim(request('anio'), 'ec') }}</option>
                        @endif
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="tlVigencia">Vigencia PTI</label>
                    <select name="vigencia" id="tlVigencia" class="form-control" data-tl-auto>
                        <option value="">Todas</option>
                        @foreach (\App\Http\Controllers\Titulacion\TitulacionController::FILTROS_VIGENCIA as $clave => [$etiqueta])
                            <option value="{{ $clave }}" {{ $filtroVigencia === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro">
                    <label for="tlGrado">Grado SUNEDU</label>
                    <select name="grado" id="tlGrado" class="form-control" data-tl-auto>
                        <option value="">Todos</option>
                        @foreach (\App\Http\Controllers\Titulacion\TitulacionController::FILTROS_GRADO as $clave => $etiqueta)
                            <option value="{{ $clave }}" {{ $filtroGrado === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tl-filtro tl-filtro--botones">
                    <button type="submit" class="tl-btn tl-btn--primario" title="Las listas se aplican solas; este botón busca lo escrito"><i class="fas fa-search"></i>Buscar</button>
                    <a href="{{ request()->fullUrlWithQuery(['exportar' => 1, 'page' => null, 'ver' => null, 'tab' => null]) }}" class="tl-btn tl-btn--excel"
                       title="Descarga en Excel lo que se ve con estos filtros ({{ $egresados->total() }} filas, todas las páginas)">
                        <i class="fas fa-file-excel"></i>Exportar
                    </a>
                </div>
            </form>

            <div class="tl-resumen">
                <span><strong>{{ $egresados->total() }}</strong> {{ $egresados->total() === 1 ? 'persona' : 'personas' }}
                    @if ($egresados->lastPage() > 1)<span class="tl-resumen__suave">· página {{ $egresados->currentPage() }} de {{ $egresados->lastPage() }}</span>@endif
                </span>
                @if (request()->filled('search') || request()->filled('estado_titulacion_id') || request()->filled('estado') || request()->filled('modalidad') || request()->filled('programa_id') || request()->filled('anio') || $filtroVigencia || $filtroTarea || $filtroGrado)
                    <a href="{{ route('titulacion.index') }}" class="tl-resumen__limpiar"><i class="fas fa-eraser"></i> Limpiar filtros</a>
                @endif
            </div>
            @if ($busquedaGlobal && collect(request()->except(['search', 'page', 'ver', 'tab', 'paso']))->filter(fn ($v) => filled($v))->isNotEmpty())
                {{-- DNI / expediente: se buscó en toda la lista; los filtros siguen elegidos, en pausa. --}}
                <div class="tl-aviso-busqueda">
                    <i class="fas fa-globe-americas"></i>
                    <span>Buscando {{ \App\Services\Titulacion\Expediente::normalizar(request('search')) ? 'el expediente' : 'el DNI' }} <strong>{{ request('search') }}</strong>
                        en <strong>toda la lista</strong>: los filtros elegidos no se aplican a esta búsqueda.</span>
                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}"><i class="fas fa-undo"></i> Volver a los filtros</a>
                </div>
            @endif

        <div class="table-responsive">
            <table class="table titulacion-tabla">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th>Titulando(a)</th>
                        <th style="white-space:nowrap;">N° Expediente</th>
                        <th>Programa</th>
                        <th>Promoción / ciclo</th>
                        <th>Estado del proceso</th>
                    </tr>
                </thead>
                <tbody id="titulacionFilas">
                    @forelse ($egresados as $i => $item)
                        @php
                            $filaClase = match ($item->tipo) {
                                'registrado' => '',
                                'activo_elegible' => 'tl-fila--elegible',
                                'retirado' => 'tl-fila--retirado',
                                default => 'tl-fila--pendiente',
                            };
                            if ($item->retiro ?? null) {
                                $filaClase = 'tl-fila--retirado';
                            }
                            // Iniciales: primer apellido + primer nombre ("APELLIDOS, Nombres").
                            $partesNombre = array_map('trim', explode(',', $item->nombre, 2));
                            $iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1).mb_substr($partesNombre[1] ?? '', 0, 1));
                        @endphp
                        {{-- "collapsed" desde el inicio: Bootstrap solo la agrega tras el
                             primer clic, y sin ella la flecha arrancaba girada. --}}
                        <tr class="{{ $filaClase }} titulacion-fila-toggle collapsed" aria-expanded="false"
                            data-toggle="collapse" data-target="#detalle-{{ $item->row_id }}" title="Ver la ficha">
                            <td class="tit-c-flecha"><span class="tl-flecha"><i class="fas fa-chevron-right titulacion-chevron"></i></span></td>
                            <td class="tit-c-persona">
                                <div class="tl-persona">
                                    <span class="tl-avatar">{{ $iniciales ?: '?' }}</span>
                                    <span class="tl-persona__texto">
                                        <span class="tl-persona__linea">
                                            <span class="tit-c-nombre">{{ $item->nombre }}</span>
                                            <span class="tl-persona__dni" title="DNI">{{ $item->dni ?: 'Sin DNI' }}</span>
                                        </span>
                                        {{-- Contacto a un clic: el dato se copia; WhatsApp y correo abren aparte. No abre la ficha. --}}
                                        @if (filled($item->telefono ?? null) || filled($item->email ?? null))
                                            <span class="tl-contacto" data-tl-contacto>
                                                @if (filled($item->telefono ?? null))
                                                    <span class="tl-contacto__item">
                                                        <button type="button" class="tl-contacto__dato js-tl-copiar" data-copiar="{{ $item->telefono }}" title="Copiar celular">
                                                            <i class="fas fa-mobile-alt"></i><span>{{ $item->telefono }}</span>
                                                        </button>
                                                        @if ($wa = \App\Support\Telefono::whatsapp($item->telefono))
                                                            <a href="{{ $wa }}" target="_blank" rel="noopener" class="tl-contacto__ir tl-contacto__ir--wa" title="Escribir por WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                                        @endif
                                                    </span>
                                                @endif
                                                @if (filled($item->email ?? null))
                                                    <span class="tl-contacto__item">
                                                        <button type="button" class="tl-contacto__dato js-tl-copiar" data-copiar="{{ $item->email }}" title="Copiar correo">
                                                            <i class="far fa-envelope"></i><span class="tl-contacto__correo" title="{{ $item->email }}">{{ $item->email }}</span>
                                                        </button>
                                                        <a href="mailto:{{ $item->email }}" class="tl-contacto__ir" title="Escribir un correo"><i class="fas fa-paper-plane"></i></a>
                                                    </span>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="tit-c-exp">
                                @if ($item->expediente ?? null)
                                    <span class="tl-exp">{{ \App\Services\Titulacion\Expediente::mostrar($item->expediente) }}</span>
                                @else
                                    <span class="tl-exp tl-exp--falta">Sin expediente</span>
                                @endif
                            </td>
                            <td class="tit-c-prog">
                                <div class="tl-prog">
                                    @if ($item->modalidad ?? null)
                                        <span class="tl-modalidad tl-modalidad--{{ strtolower($item->modalidad) }}">{{ $item->modalidad }}</span>
                                    @endif
                                    {{-- La etiqueta FID/PPD ya dice el tipo: «Programa Primaria EIB» se lee «Primaria EIB». --}}
                                    <span title="{{ $item->programa }}">{{ preg_replace('/^Programa\s+/iu', '', $item->programa) }}</span>
                                </div>
                            </td>
                            <td class="tit-c-anio">{{ \App\Http\Controllers\Titulacion\TitulacionController::etiquetaAnio($item->anio ?? null) ?? '—' }}</td>
                            <td class="tit-c-estado">
                                <div class="tl-estado">
                                    <div class="tl-estado__fila">
                                        <span class="tl-pill tl-pill--{{ $item->estado_badge }}">{{ $item->estado_label }}</span>
                                        @if (($item->retiro ?? null) && $item->tipo !== 'retirado')
                                            <span class="tl-pill tl-pill--danger"
                                                  title="{{ $item->retiro === 'perfil' ? 'Su cuenta tiene el perfil «Retirado».' : 'La hoja de expedientes lo(a) marca como retirado(a); falta registrar el retiro en su expediente.' }}">
                                                <i class="fas fa-user-times"></i> {{ $item->retiro === 'perfil' ? 'Retirado(a)' : 'Retirado(a) según planilla' }}
                                            </span>
                                        @endif
                                        @if (! empty($item->etapas))
                                            {{-- Mini barra del proceso: PTI | TI | Tesis (verde completo, azul en curso, gris pendiente) --}}
                                            <span class="tl-barra" aria-hidden="true">
                                                @foreach (['pti' => 'PTI', 'ti' => 'TI', 'tesis' => 'Tesis'] as $ce => $ne)
                                                    <span class="tl-barra__tramo tl-barra__tramo--{{ $item->etapas[$ce]['estado'] }}" title="{{ $ne }}: {{ $item->etapas[$ce]['resumen'] ?? 'Pendiente' }}"></span>
                                                @endforeach
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->siguiente ?? null)
                                        <span class="titulacion-siguiente" title="Siguiente paso"><i class="fas fa-arrow-right"></i>{{ $item->siguiente }}</span>
                                    @endif
                                    {{-- Sin filtro solo se marca lo urgente; con filtro, la fecha de cada fila. --}}
                                    @if (($item->vigencia_grupo ?? null) === 'por_vencer' || ($filtroVigencia && in_array($item->vigencia_grupo ?? null, ['proximo_anio', 'vencido'], true)))
                                        <span class="titulacion-vence titulacion-vence--{{ $item->vigencia_grupo === 'proximo_anio' ? 'suave' : 'urgente' }}" title="Vigencia del PTI">
                                            <i class="far fa-clock"></i>{{ $item->vigencia_grupo === 'vencido' ? 'Venció' : 'Vence' }} {{ $item->vigencia->vencimiento->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse titulacion-detalle" id="detalle-{{ $item->row_id }}" data-parent="#titulacionFilas">
                            <td colspan="6">
                                @if ($item->tipo === 'registrado' && (int) request('ver') === $item->user_id)
                                    {{-- La ficha recién editada viene ya armada: trae el mensaje de "guardado". --}}
                                    @include('titulacion._show_contenido', [
                                        'egresado' => $item->user,
                                        'seguimiento' => $item->seguimiento,
                                        'estados' => $estados,
                                        'docentes' => $docentes,
                                        'asesores' => $asesores,
                                        'integrantes' => $item->integrantes_grupo,
                                        'filasImportadas' => $item->filas_importadas ?? collect(),
                                        'enModal' => true,
                                        'enAcordeon' => true,
                                    ])
                                @elseif ($item->tipo === 'registrado')
                                    {{-- Las demás se piden al abrir la fila (TitulacionController@ficha). --}}
                                    <div class="js-ficha-lazy" data-url="{{ route('titulacion.ficha', $item->user_id) }}"
                                         data-completa="{{ route('titulacion.show', $item->user_id) }}">
                                        <div class="text-center text-muted small py-4"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando la ficha…</div>
                                    </div>
                                @elseif ($item->tipo === 'activo_elegible')
                                    <div class="row mb-2">
                                        <div class="col-md-3 small"><strong>DNI:</strong> {{ $item->dni ?: '—' }}</div>
                                        <div class="col-md-3 small"><strong>Nombre completo:</strong> {{ $item->nombre }}</div>
                                        <div class="col-md-3 small"><strong>Programa:</strong> {{ $item->programa }}</div>
                                        <div class="col-md-3 small"><strong>Ciclo actual:</strong> {{ $item->anio ?? '—' }}</div>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Alumno(a) activo(a) con matrícula regular — todavía no es egresado(a), pero ya está en un
                                        ciclo desde el cual la institución permite iniciar el PTI. Al iniciar se registran los datos
                                        del PTI, se genera su N° de expediente y se agregan los integrantes del grupo (si los hay).
                                    </p>
                                    <a href="{{ $item->accion_url }}" class="btn btn-sm {{ $item->accion_clase }}">
                                        <i class="fa fa-play fa-sm"></i> {{ $item->accion_label }}
                                    </a>
                                    @if (! empty($item->filas_importadas) && count($item->filas_importadas))
                                        <details class="ft-desplegable mt-3">
                                            <summary><i class="fas fa-file-import mr-1"></i>Datos de archivos importados ({{ count($item->filas_importadas) }})</summary>
                                            <div class="mt-2">@include('titulacion._filas_importadas', ['filas' => $item->filas_importadas])</div>
                                        </details>
                                    @endif
                                @elseif ($item->tipo === 'retirado')
                                    <p class="small text-muted mb-2">
                                        <i class="fas fa-user-times mr-1"></i>
                                        @if (($item->retiro ?? null) === 'perfil')
                                            Se retiró de la institución (perfil «Retirado» en su cuenta). No inició el proceso de titulación.
                                        @elseif ($item->user_id ?? null)
                                            La hoja de expedientes lo(a) marca como retirado(a), pero su cuenta aún no lo registra.
                                            Si es correcto, registre el retiro en su expediente (queda en su trayectoria).
                                        @else
                                            La hoja de expedientes lo(a) marca como retirado(a): por eso no se le crea cuenta.
                                            Si se reincorpora, quite «Retirada» en la hoja y vuelva a sincronizar.
                                        @endif
                                    </p>
                                    @if ($item->accion_url ?? null)
                                        <a href="{{ $item->accion_url }}" class="btn btn-sm {{ $item->accion_clase }} mb-3">
                                            <i class="fas fa-folder-open fa-sm"></i> {{ $item->accion_label }}
                                        </a>
                                    @endif
                                    @if (! empty($item->filas_origen) && count($item->filas_origen))
                                        @include('titulacion._filas_importadas', ['filas' => $item->filas_origen])
                                    @elseif (! empty($item->filas_importadas) && count($item->filas_importadas))
                                        @include('titulacion._filas_importadas', ['filas' => $item->filas_importadas])
                                    @endif
                                @else
                                    {{-- La acción vive aquí (antes en una columna "Acciones"): la fila
                                         entera ya abre este detalle, así que es el paso natural. --}}
                                    <a href="{{ $item->accion_url }}" class="btn btn-sm {{ $item->accion_clase }} mb-3">
                                        <i class="fa fa-user-plus fa-sm"></i> {{ $item->accion_label }}
                                    </a>
                                    <p class="small font-weight-bold text-secondary mb-2">
                                        <i class="fas fa-file-import mr-1"></i>Viene de {{ $item->filas_origen->count() }} archivo(s) importado(s):
                                    </p>
                                    @include('titulacion._filas_importadas', ['filas' => $item->filas_origen])
                                @endif

                                {{-- La ficha es larga (8 pestañas): cerrar desde abajo evita
                                     volver a subir hasta la fila. Bootstrap sincroniza la
                                     flecha y el estado de la fila con este mismo target. --}}
                                <div class="titulacion-detalle__cerrar">
                                    <button type="button" class="btn btn-sm btn-link"
                                            data-toggle="collapse" data-target="#detalle-{{ $item->row_id }}"
                                            aria-controls="detalle-{{ $item->row_id }}">
                                        <i class="fas fa-chevron-up fa-xs mr-1"></i> Cerrar ficha de {{ \Illuminate\Support\Str::of($item->nombre)->before(',')->trim() }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if ($coincidenciasFuera > 0)
                            {{-- El nombre existe, pero fuera de los filtros elegidos. --}}
                            <tr><td colspan="6" class="tl-vacio-lista">
                                <i class="fas fa-filter"></i>
                                <strong>No hay coincidencias para «{{ request('search') }}» con estos filtros</strong>
                                <span>Pero hay {{ $coincidenciasFuera }} {{ $coincidenciasFuera === 1 ? 'coincidencia' : 'coincidencias' }} en toda la lista.</span>
                                <a href="{{ route('titulacion.index', ['search' => request('search')]) }}" class="tl-btn tl-btn--primario tl-vacio-lista__accion"><i class="fas fa-globe-americas"></i>Ver en toda la lista</a>
                            </td></tr>
                        @else
                            <tr><td colspan="6" class="tl-vacio-lista"><i class="fas fa-search"></i><strong>No hay resultados</strong><span>Pruebe con otros filtros o limpie la búsqueda.</span></td></tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>

            @if ($egresados->hasPages())<div class="tl-paginacion">{{ $egresados->links() }}</div>@endif
        </section>
    </div>

    @if (request()->filled('ver'))
        {{-- Al volver de guardar algo dentro del acordeón (POST -> redirect a
             /titulacion?ver={id}), se reabre solo el acordeón de esa fila y se
             hace scroll hasta ahí — todo servido por el servidor, sin AJAX. Si
             la persona quedó en otra página de la paginación, la fila
             simplemente no está en el DOM y esto no hace nada (degrada bien). --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var fila = document.getElementById('detalle-reg{{ (int) request('ver') }}');
                if (!fila) return;
                // Abre la ficha en el panel lateral (ver más abajo) y deja la fila a la vista.
                setTimeout(function () {
                    $(fila).collapse('show');
                    fila.previousElementSibling.scrollIntoView({ block: 'center' });
                }, 0);
            });
        </script>
    @endif
    @include('titulacion._ficha_recursos')
    {{-- Ficha al abrir la fila: se pide una sola vez y queda en la página. Si falla,
         se ofrece abrirla en su página propia. --}}
    {{-- Panel lateral: la ficha se abre deslizándose desde la derecha, sin perder la posición en la
         lista. Se reutiliza el contenido de la fila de detalle (que se "presta" al panel y se devuelve
         al cerrar); si el JS no carga, las filas siguen abriéndose como acordeón. --}}
    <div class="tl-panel" id="tlPanel" aria-hidden="true">
        <div class="tl-panel__fondo" data-tl-cerrar></div>
        <aside class="tl-panel__hoja" role="dialog" aria-modal="true" aria-labelledby="tlPanelTitulo">
            <div class="tl-panel__barra">
                <button type="button" class="tl-panel__nav" data-tl-mover="-1" title="Anterior de la lista (↑)"><i class="fas fa-chevron-up"></i></button>
                <button type="button" class="tl-panel__nav" data-tl-mover="1" title="Siguiente de la lista (↓)"><i class="fas fa-chevron-down"></i></button>
                <div class="tl-panel__titulo" id="tlPanelTitulo"></div>
                <a href="#" class="tl-panel__nav" id="tlPanelCompleta" title="Abrir en página completa" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i></a>
                <button type="button" class="tl-panel__cerrar" data-tl-cerrar title="Cerrar (Esc)"><i class="fas fa-times"></i></button>
            </div>
            <div class="tl-panel__cuerpo" id="tlPanelCuerpo"></div>
        </aside>
    </div>
    <script>
        // Filtros: cada lista se aplica al elegirla. Al cambiar la modalidad, un programa de la otra
        // modalidad ya no aplica y se quita (la promoción se mantiene: el año vale para FID y PPD).
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-tl-auto]').forEach(function (lista) {
                lista.addEventListener('change', function () {
                    var form = lista.form;
                    if (lista.name === 'modalidad') {
                        var prog = form.elements.programa_id;
                        var grupo = prog.selectedOptions[0] && prog.selectedOptions[0].parentElement;
                        if (lista.value && grupo && grupo.label && grupo.label !== lista.value) prog.value = '';
                    }
                    form.querySelectorAll('select').forEach(function (s) { s.classList.add('is-cargando'); });
                    form.submit();
                });
            });
        });

        // Contacto de cada fila: copiar el celular o el correo (con aviso en el mismo botón) sin abrir la ficha.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-tl-contacto]').forEach(function (caja) {
                caja.addEventListener('click', function (e) {
                    e.stopPropagation(); // la fila entera abre la ficha (collapse de Bootstrap, delegado en document)
                    var boton = e.target.closest('.js-tl-copiar');
                    if (!boton || !navigator.clipboard) return;
                    navigator.clipboard.writeText(boton.dataset.copiar).then(function () {
                        var icono = boton.querySelector('i');
                        var antes = icono.className;
                        boton.classList.add('is-copiado');
                        icono.className = 'fas fa-check';
                        boton.setAttribute('title', '¡Copiado!');
                        setTimeout(function () {
                            boton.classList.remove('is-copiado');
                            icono.className = antes;
                            boton.setAttribute('title', boton.dataset.copiar.includes('@') ? 'Copiar correo' : 'Copiar celular');
                        }, 1600);
                    });
                });
            });
        });

        // Tira del hero: difumina el borde del lado que tiene más cifras ocultas y lleva a la vista la activa.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-tl-scroll]').forEach(function (caja) {
                var pista = caja.querySelector('.tl-hero__pista');
                function marcar() {
                    caja.classList.toggle('hay-izq', pista.scrollLeft > 4);
                    caja.classList.toggle('hay-der', pista.scrollLeft + pista.clientWidth < pista.scrollWidth - 4);
                }
                var activo = pista.querySelector('.is-activo');
                if (activo && activo !== pista.firstElementChild) {
                    pista.scrollLeft = activo.offsetLeft - (pista.clientWidth - activo.offsetWidth) / 2;
                }
                pista.addEventListener('scroll', marcar, { passive: true });
                window.addEventListener('resize', marcar);
                marcar();
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var panel = document.getElementById('tlPanel');
            var cuerpo = document.getElementById('tlPanelCuerpo');
            var titulo = document.getElementById('tlPanelTitulo');
            var completa = document.getElementById('tlPanelCompleta');
            var prestado = null; // { td, fila }

            function cargarFicha(caja) {
                if (!caja || caja.dataset.estado) return;
                caja.dataset.estado = 'cargando';
                caja.innerHTML = '<div class="tl-esqueleto"><span></span><span></span><span class="is-corta"></span><div class="tl-esqueleto__grid"><i></i><i></i></div></div>';
                fetch(caja.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
                    .then(function (html) { caja.innerHTML = html; caja.dataset.estado = 'lista'; })
                    .catch(function () {
                        caja.dataset.estado = '';
                        caja.innerHTML = '<div class="text-center small py-4">No se pudo cargar la ficha. '
                            + '<a href="' + caja.dataset.completa + '">Abrirla en su página</a> o vuelva a intentarlo.</div>';
                    });
            }

            function devolver() {
                if (!prestado) return;
                while (cuerpo.firstChild) prestado.td.appendChild(cuerpo.firstChild);
                prestado.fila.classList.remove('is-abierta');
                prestado = null;
            }

            function abrir(detalle) {
                devolver();
                var td = detalle.querySelector('td');
                var fila = detalle.previousElementSibling;
                while (td.firstChild) cuerpo.appendChild(td.firstChild);
                prestado = { td: td, fila: fila };
                fila.classList.add('is-abierta');
                var nombre = fila.querySelector('.tit-c-nombre');
                titulo.textContent = nombre ? nombre.textContent.trim() : 'Ficha';
                var lazy = cuerpo.querySelector('.js-ficha-lazy');
                completa.hidden = !lazy;
                if (lazy) completa.href = lazy.dataset.completa;
                cargarFicha(lazy);
                cuerpo.scrollTop = 0;
                panel.classList.add('is-abierto');
                panel.setAttribute('aria-hidden', 'false');
                document.body.classList.add('tl-sin-scroll');
            }

            function cerrar() {
                panel.classList.remove('is-abierto');
                panel.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('tl-sin-scroll');
                setTimeout(devolver, 220);
            }

            // Abrir una fila: en vez del acordeón, el panel.
            $('#titulacionFilas').on('show.bs.collapse', '.titulacion-detalle', function (e) {
                if (e.target !== this) return;
                e.preventDefault();
                abrir(this);
            });

            panel.addEventListener('click', function (e) {
                if (e.target.closest('[data-tl-cerrar]')) { cerrar(); return; }
                var mover = e.target.closest('[data-tl-mover]');
                if (mover && prestado) moverA(parseInt(mover.dataset.tlMover, 10));
            });

            function moverA(paso) {
                var filas = Array.prototype.slice.call(document.querySelectorAll('#titulacionFilas .titulacion-detalle'));
                var actual = filas.indexOf(prestado.td.parentElement);
                var destino = filas[actual + paso];
                if (destino) { abrir(destino); destino.previousElementSibling.scrollIntoView({ block: 'nearest' }); }
            }

            document.addEventListener('keydown', function (e) {
                if (!panel.classList.contains('is-abierto')) return;
                if (e.key === 'Escape') cerrar();
                // ↑/↓ cambian de persona solo si no se está escribiendo en un campo.
                if ((e.key === 'ArrowUp' || e.key === 'ArrowDown') && !e.target.closest('input, textarea, select, [contenteditable]')) {
                    e.preventDefault();
                    moverA(e.key === 'ArrowUp' ? -1 : 1);
                }
            });
        });
    </script>
@endsection
