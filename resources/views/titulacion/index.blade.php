@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <style>
        /* Barra de chips de estado + filtros: una sola fila en PC (>=992px),
           apilada en pantallas chicas. Los chips se vuelven una tira con
           scroll horizontal en móvil en vez de romper en varias líneas —
           patrón estándar de "filter chips" (tabs horizontales scrolleables). */
        .titulacion-toolbar {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .titulacion-chips {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding: .125rem .125rem .5rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .titulacion-chips::-webkit-scrollbar {
            height: 6px;
        }

        .titulacion-chips::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .titulacion-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            flex-shrink: 0;
            min-width: 6.75rem;
            padding: .5rem .8rem;
            border-radius: .6rem;
            background: #fff;
            border: 1px solid #e3e6f0;
            text-decoration: none;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease, background-color .15s ease;
        }

        .titulacion-chip:hover {
            box-shadow: 0 .2rem .6rem rgba(31, 41, 55, .1);
            transform: translateY(-1px);
            text-decoration: none;
            border-color: #c7d2fe;
        }

        .titulacion-chip__count {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.15;
            color: #2d3748;
        }

        .titulacion-chip__label {
            font-size: .66rem;
            font-weight: 600;
            color: #8892a6;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
            max-width: 9rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .titulacion-chip--active {
            background: #eef2ff;
            border-color: #4e73df;
        }

        .titulacion-chip--active .titulacion-chip__count { color: #4e73df; }
        .titulacion-chip--active .titulacion-chip__label { color: #4e73df; }

        .titulacion-chip--warning.titulacion-chip--active {
            background: #fff8e6;
            border-color: #f6c23e;
        }

        .titulacion-chip--warning .titulacion-chip__count { color: #dda60a; }
        .titulacion-chip--warning.titulacion-chip--active .titulacion-chip__label { color: #b98b06; }

        .titulacion-chip--primary .titulacion-chip__count { color: #4e73df; }

        .titulacion-chip--danger .titulacion-chip__count { color: #e74a3b; }
        .titulacion-chip--danger.titulacion-chip--active { background: #fdecea; border-color: #e74a3b; }
        .titulacion-chip--danger.titulacion-chip--active .titulacion-chip__label { color: #c0392b; }
        .dark-mode .titulacion-chip--danger.titulacion-chip--active,
        .dim-mode .titulacion-chip--danger.titulacion-chip--active { background: rgba(231, 74, 59, .16); }
        .dark-mode .titulacion-chip--danger.titulacion-chip--active .titulacion-chip__label,
        .dim-mode .titulacion-chip--danger.titulacion-chip--active .titulacion-chip__label { color: #ff9a8f; }

        .tit-tareas { padding: .65rem .8rem; border: 1px solid #e3e6f0; border-radius: .7rem; background: #f8f9fc; }
        .tit-tareas__titulo { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #8892a6; margin-bottom: .45rem; }
        .tit-tareas__lista { display: flex; flex-wrap: wrap; gap: .45rem; }
        .tit-tarea { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .7rem; border: 1px solid #e3e6f0; border-radius: 999px; background: #fff; color: #4a5568; font-size: .8rem; text-decoration: none !important; transition: border-color .15s, background-color .15s; }
        .tit-tarea:hover { border-color: #4e73df; color: #4e73df; }
        .tit-tarea__icono { font-size: .75rem; opacity: .7; }
        .tit-tarea__total { font-weight: 800; color: #1e293b; }
        .tit-tarea.is-activa { border-color: #4e73df; background: #eef2ff; color: #4e73df; }
        .tit-tarea--urgente .tit-tarea__total, .tit-tarea--urgente .tit-tarea__icono { color: #e74a3b; opacity: 1; }
        .tit-tarea--urgente.is-activa { border-color: #e74a3b; background: #fdecea; color: #c0392b; }
        .tit-tarea--quitar { color: #6b7280; }
        .dark-mode .tit-tareas, .dim-mode .tit-tareas { background: rgba(255, 255, 255, .03); border-color: rgba(255, 255, 255, .1); }
        .dark-mode .tit-tarea, .dim-mode .tit-tarea { background: transparent; border-color: rgba(255, 255, 255, .15); color: inherit; }
        .dark-mode .tit-tarea__total, .dim-mode .tit-tarea__total { color: inherit; }
        .dark-mode .tit-tarea.is-activa { background: rgba(88, 166, 255, .12); border-color: #58a6ff; }
        .dim-mode .tit-tarea.is-activa { background: var(--lm-acento-fondo, #452638); border-color: var(--lm-acento, #f49ac1); }
        .dark-mode .tit-tarea--urgente .tit-tarea__total, .dim-mode .tit-tarea--urgente .tit-tarea__total,
        .dark-mode .tit-tarea--urgente .tit-tarea__icono, .dim-mode .tit-tarea--urgente .tit-tarea__icono { color: #ff8a80; }
        .titulacion-vence { display: block; margin-top: .2rem; font-size: .72rem; white-space: nowrap; }
        .titulacion-siguiente { display: block; margin-top: .2rem; font-size: .72rem; color: #6b7280; max-width: 16rem; line-height: 1.25; }
        .dark-mode .titulacion-siguiente, .dim-mode .titulacion-siguiente { color: inherit; opacity: .75; }
        .tit-c-eti { display: none; }
        .titulacion-filters .form-group-sm.tit-filtros-botones { flex: 0 0 auto; min-width: auto; display: flex; flex-wrap: wrap; gap: .35rem; align-items: flex-end; }

        /* Celular: cada persona es una tarjeta (nombre, estado y siguiente paso arriba;
           expediente, DNI, programa y ciclo en una línea chica). La ficha se abre debajo. */
        @media (max-width: 767.98px) {
            .titulacion-tabla thead { display: none; }
            .titulacion-tabla, .titulacion-tabla tbody { display: block; width: 100%; }
            .titulacion-tabla tr.titulacion-fila-toggle {
                display: flex; flex-wrap: wrap; align-items: baseline; column-gap: .6rem; row-gap: .15rem;
                padding: .7rem .5rem .7rem 1.9rem; border-bottom: 1px solid #e3e6f0; position: relative;
            }
            .titulacion-tabla tr.titulacion-fila-toggle > td { display: block; border: 0; padding: 0; background: transparent !important; }
            .titulacion-tabla .tit-c-flecha { position: absolute; left: .5rem; top: .85rem; }
            .titulacion-tabla .tit-c-nombre { order: 1; flex: 1 1 100%; font-size: .95rem; }
            .titulacion-tabla .tit-c-estado { order: 2; flex: 1 1 100%; margin-bottom: .15rem; }
            .titulacion-tabla .tit-c-estado .titulacion-siguiente { max-width: none; }
            .titulacion-tabla .tit-c-exp, .titulacion-tabla .tit-c-dni, .titulacion-tabla .tit-c-prog, .titulacion-tabla .tit-c-anio {
                order: 3; font-size: .76rem; color: #6b7280; white-space: normal !important;
            }
            .titulacion-tabla .tit-c-eti { display: inline; }
            .titulacion-tabla tr.titulacion-detalle.show, .titulacion-tabla tr.titulacion-detalle.collapsing { display: block; }
            .titulacion-tabla tr.titulacion-detalle > td { display: block; padding: .75rem .5rem !important; }
            .dark-mode .titulacion-tabla tr.titulacion-fila-toggle, .dim-mode .titulacion-tabla tr.titulacion-fila-toggle { border-color: rgba(255, 255, 255, .12); }
            .dark-mode .titulacion-tabla .tit-c-exp, .dark-mode .titulacion-tabla .tit-c-dni, .dark-mode .titulacion-tabla .tit-c-prog, .dark-mode .titulacion-tabla .tit-c-anio,
            .dim-mode .titulacion-tabla .tit-c-exp, .dim-mode .titulacion-tabla .tit-c-dni, .dim-mode .titulacion-tabla .tit-c-prog, .dim-mode .titulacion-tabla .tit-c-anio { color: inherit; opacity: .75; }
        }

        .titulacion-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: .6rem;
            flex-shrink: 0;
        }

        .titulacion-filters .form-group-sm { min-width: 9rem; }
        .titulacion-filters .form-group-sm.is-search { min-width: 13rem; flex: 1 1 13rem; }

        /* Flechita del acordeón: gira 180° al abrir. Bootstrap 4 agrega/quita
           la clase .collapsed en el <tr data-toggle="collapse"> solo, según
           el estado del collapse que apunta — no hace falta JS propio. */
        .titulacion-chevron {
            transition: transform .2s ease;
        }
        .titulacion-fila-toggle:not(.collapsed) .titulacion-chevron {
            transform: rotate(180deg);
        }

        /* ── Registro abierto: se lee como "seleccionado" ──────────────────
           Fila y detalle forman una sola tarjeta: misma barra de acento a la
           izquierda, fondo tintado en la fila y panel elevado debajo. El resto
           de filas se atenúa mientras hay una abierta (foco), sin ocultarlas:
           al pasar el mouse recuperan opacidad para poder saltar a otra.
           Colores en variables para ajustarlos por tema (claro/oscuro/tenue)
           sin tocar darkmode.css. */
        .titulacion-tabla {
            --tit-acento: #4e73df;
            --tit-fila-abierta: #eef2ff;
            --tit-panel: #f8f9fc;
            --tit-panel-borde: #dbe2f7;
            --tit-sombra: rgba(78, 115, 223, .14);
        }
        body.dark-mode .titulacion-tabla {
            --tit-acento: #58a6ff;
            --tit-fila-abierta: rgba(88, 166, 255, .13);
            --tit-panel: #161b22;
            --tit-panel-borde: #30363d;
            --tit-sombra: rgba(0, 0, 0, .45);
        }
        body.dim-mode .titulacion-tabla {
            --tit-acento: var(--lm-acento);
            --tit-fila-abierta: rgba(244, 154, 193, .14);
            --tit-panel: var(--lm-superficie);
            --tit-panel-borde: var(--lm-borde);
            --tit-sombra: rgba(0, 0, 0, .45);
        }

        .titulacion-fila-toggle > td {
            transition: background-color .2s ease, opacity .2s ease, box-shadow .2s ease;
        }

        /* Fila abierta: fondo tintado + barra de acento + texto destacado.
           !important porque darkmode.css pinta td y :hover con !important. */
        .titulacion-tabla .titulacion-fila-toggle:not(.collapsed) > td {
            background-color: var(--tit-fila-abierta) !important;
            border-top: 2px solid var(--tit-acento);
            border-bottom: 0;
        }
        .titulacion-tabla .titulacion-fila-toggle:not(.collapsed) > td:first-child {
            box-shadow: inset 4px 0 0 var(--tit-acento);
        }
        .titulacion-tabla .titulacion-fila-toggle:not(.collapsed) > td:last-child {
            box-shadow: inset -2px 0 0 var(--tit-acento);
        }
        .titulacion-fila-toggle:not(.collapsed) .titulacion-chevron {
            color: var(--tit-acento);
        }

        /* Panel del detalle: continúa la barra de acento y se cierra con un
           borde inferior de color, así se ve dónde termina la ficha. */
        .titulacion-tabla tr.titulacion-detalle > td {
            background-color: var(--tit-panel) !important;
            border-top: 0;
            border-bottom: 3px solid var(--tit-acento);
            box-shadow: inset 4px 0 0 var(--tit-acento),
                        inset -2px 0 0 var(--tit-acento),
                        0 .5rem 1rem -.5rem var(--tit-sombra);
            padding: 1rem 1.25rem .75rem 1.5rem;
        }
        .titulacion-tabla tr.titulacion-detalle:hover > td {
            background-color: var(--tit-panel) !important;
        }

        .titulacion-detalle__cerrar {
            display: flex;
            justify-content: center;
            margin-top: .75rem;
            padding-top: .5rem;
            border-top: 1px dashed var(--tit-panel-borde);
        }
        .titulacion-detalle__cerrar .btn {
            color: var(--tit-acento);
            font-weight: 600;
        }

        /* Foco: con una ficha abierta, las demás filas pasan a segundo plano.
           :has() degrada bien — en navegadores sin soporte no se atenúa nada. */
        .titulacion-tabla tbody:has(.titulacion-fila-toggle:not(.collapsed)) .titulacion-fila-toggle.collapsed > td {
            opacity: .5;
        }
        .titulacion-tabla tbody:has(.titulacion-fila-toggle:not(.collapsed)) .titulacion-fila-toggle.collapsed:hover > td {
            opacity: 1;
        }

        @media (prefers-reduced-motion: reduce) {
            .titulacion-fila-toggle > td,
            .titulacion-chevron { transition: none; }
        }

        /* Chips arriba y filtros debajo también en PC: con 5 filtros, ponerlos en
           la misma fila aplastaba los chips y empujaba los filtros fuera de la pantalla. */
        @media (min-width: 992px) {
            .titulacion-chips { padding-bottom: .125rem; }
            .titulacion-filters .form-group-sm { flex: 1 1 9rem; }
            .titulacion-filters .form-group-sm.is-search { flex: 2 1 15rem; }
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-graduate mr-2" style="color:#4e73df;"></i>Titulación de Egresados
                </h4>
                <small class="text-muted">
                    {{ $totalEgresados }} registrados &nbsp;&middot;&nbsp;
                    {{ $totalConSeguimiento }} con seguimiento iniciado
                    @if ($totalPendientes > 0)
                        &nbsp;&middot;&nbsp; <span class="text-warning font-weight-bold">{{ $totalPendientes }} pendientes de importación</span>
                    @endif
                    @if ($totalActivosElegibles > 0)
                        &nbsp;&middot;&nbsp; <span class="text-primary font-weight-bold">{{ $totalActivosElegibles }} activos que pueden iniciar (FID desde Ciclo VIII · PPD todos los vigentes)</span>
                    @endif
                </small>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-sm-0" style="gap:.4rem;">
                <a href="{{ route('titulacion.asesores.index') }}" class="btn btn-sm btn-outline-primary" title="Asesores y jurados que no son docentes con cuenta">
                    <i class="fas fa-user-tie mr-1"></i>Asesores
                </a>
                <a href="{{ route('titulacion.demograficos') }}" class="btn btn-sm btn-outline-primary" title="Perfil demográfico de quienes están en el proceso">
                    <i class="fas fa-chart-pie mr-1"></i>Datos demográficos
                </a>
                @if ($totalPendientes > 0)
                    <a href="{{ route('titulacion.crear-cuentas-masivo.preview') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-users mr-1"></i>Crear cuentas pendientes
                    </a>
                @endif
            </div>
        </div>

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
            <div class="alert alert-warning small" role="alert">
                <i class="fas fa-user-graduate mr-1"></i>
                <strong>{{ $reporte->data['alumno_nombre'] ?? 'Egresado(a)' }}</strong> indicó que un dato no coincide
                <span class="text-muted">({{ $reporte->created_at->format('d/m/Y H:i') }})</span>:
                <div class="mt-1 font-italic" style="white-space:pre-line;">“{{ $reporte->data['mensaje'] ?? $reporte->data['resumen'] ?? '' }}”</div>
            </div>
        @endforeach

        @php
            $qFiltroBase = array_filter(['search' => request('search')], fn ($v) => $v !== null && $v !== '');
        @endphp
        {{-- Por atender: lo que hoy solo se veía abriendo cada ficha. Cada número abre la lista filtrada. --}}
        @php
            $tareasPanel = collect(['por_vencer' => ['PTI por vencer (≤ 4 meses)', 'fa-hourglass-half', 'PTI que vencen en 4 meses o menos.']])
                ->merge(\App\Http\Controllers\Titulacion\TitulacionController::TAREAS)
                ->map(fn ($t, $clave) => [
                    'etiqueta' => $t[0], 'icono' => $t[1], 'ayuda' => $t[2],
                    'total' => $clave === 'por_vencer' ? $conteoVigencia['por_vencer'] : $conteoTareas[$clave],
                    'url' => route('titulacion.index', $clave === 'por_vencer' ? ['vigencia' => 'por_vencer'] : ['tarea' => $clave]),
                    'activa' => $clave === 'por_vencer' ? $filtroVigencia === 'por_vencer' : $filtroTarea === $clave,
                    'urgente' => $clave === 'por_vencer',
                ])
                ->filter(fn ($t) => $t['total'] > 0);
        @endphp
        @if ($tareasPanel->isNotEmpty())
            <div class="tit-tareas mb-3">
                <div class="tit-tareas__titulo"><i class="fas fa-tasks mr-1"></i>Por atender</div>
                <div class="tit-tareas__lista">
                    @foreach ($tareasPanel as $clave => $t)
                        <a href="{{ $t['url'] }}" title="{{ $t['ayuda'] }}"
                           class="tit-tarea {{ $t['urgente'] ? 'tit-tarea--urgente' : '' }} {{ $t['activa'] ? 'is-activa' : '' }}">
                            <i class="fas {{ $t['icono'] }} tit-tarea__icono"></i>
                            <span class="tit-tarea__total">{{ $t['total'] }}</span>
                            <span class="tit-tarea__eti">{{ $t['etiqueta'] }}</span>
                        </a>
                    @endforeach
                    @if ($filtroTarea || $filtroVigencia)
                        <a href="{{ route('titulacion.index') }}" class="tit-tarea tit-tarea--quitar"><i class="fas fa-times mr-1"></i>Ver todos</a>
                    @endif
                </div>
            </div>
        @endif
        <div class="titulacion-toolbar">
            <div class="titulacion-chips">
                @foreach ($estados as $estado)
                    @php
                        $activo = (int) request('estado_titulacion_id') === $estado->id && request('estado') !== 'pendientes';
                        $urlEstado = route('titulacion.index', $qFiltroBase + ['estado_titulacion_id' => $estado->id]);
                    @endphp
                    <a href="{{ $urlEstado }}" class="titulacion-chip {{ $activo ? 'titulacion-chip--active' : '' }}" title="{{ $estado->nombre }}">
                        <span class="titulacion-chip__count">{{ $conteoPorEstado[$estado->id] ?? 0 }}</span>
                        <span class="titulacion-chip__label">{{ $estado->nombre }}</span>
                    </a>
                @endforeach
                @if ($totalPendientes > 0)
                    @php
                        $activoPendientes = request('estado') === 'pendientes';
                        $urlPendientes = route('titulacion.index', $qFiltroBase + ['estado' => 'pendientes']);
                    @endphp
                    <a href="{{ $urlPendientes }}" class="titulacion-chip titulacion-chip--warning {{ $activoPendientes ? 'titulacion-chip--active' : '' }}">
                        <span class="titulacion-chip__count">{{ $totalPendientes }}</span>
                        <span class="titulacion-chip__label">Pendientes</span>
                    </a>
                @endif
                @if ($totalActivosElegibles > 0)
                    @php
                        $activoActivos = request('estado') === 'activos';
                        $urlActivos = route('titulacion.index', $qFiltroBase + ['estado' => 'activos']);
                    @endphp
                    <a href="{{ $urlActivos }}" class="titulacion-chip titulacion-chip--primary {{ $activoActivos ? 'titulacion-chip--active' : '' }}">
                        <span class="titulacion-chip__count">{{ $totalActivosElegibles }}</span>
                        <span class="titulacion-chip__label">Activos que pueden iniciar</span>
                    </a>
                @endif
                @if (request()->filled('estado_titulacion_id') || request()->filled('estado'))
                    <a href="{{ route('titulacion.index', $qFiltroBase) }}" class="titulacion-chip" title="Quitar filtro de estado">
                        <span class="titulacion-chip__count"><i class="fas fa-times-circle fa-sm text-muted"></i></span>
                        <span class="titulacion-chip__label">Quitar filtro</span>
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('titulacion.index') }}" class="titulacion-filters">
                {{-- El filtro de estado ya se elige con los chips de arriba; los
                     demás filtros conservan ese filtro activo vía estos hidden. --}}
                @if (request()->filled('estado_titulacion_id'))
                    <input type="hidden" name="estado_titulacion_id" value="{{ request('estado_titulacion_id') }}">
                @endif
                @if (request('estado') === 'pendientes')
                    <input type="hidden" name="estado" value="pendientes">
                @endif
                @if ($filtroTarea)
                    <input type="hidden" name="tarea" value="{{ $filtroTarea }}">
                @endif
                <div class="form-group-sm is-search">
                    <label class="small font-weight-bold text-secondary mb-1">Buscar</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nombre, DNI, Acta o RD...">
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Modalidad</label>
                    <select name="modalidad" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach (\App\Enums\Modalidad::cases() as $mod)
                            <option value="{{ $mod->value }}" {{ request('modalidad') === $mod->value ? 'selected' : '' }}>{{ $mod->value }} — {{ $mod->etiqueta() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Programa</label>
                    <select name="programa_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($programasFiltro->groupBy(fn ($p) => $p->modalidad->value) as $modalidad => $progs)
                            <optgroup label="{{ $modalidad }}">
                                @foreach ($progs as $prog)
                                    <option value="{{ $prog->id }}" {{ (int) request('programa_id') === $prog->id ? 'selected' : '' }}>{{ $prog->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Vigencia PTI</label>
                    <select name="vigencia" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach (\App\Http\Controllers\Titulacion\TitulacionController::FILTROS_VIGENCIA as $clave => [$etiqueta])
                            <option value="{{ $clave }}" {{ $filtroVigencia === $clave ? 'selected' : '' }}>{{ $etiqueta }} ({{ $conteoVigencia[$clave] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Promoción / ciclo</label>
                    <select name="anio" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($aniosFiltro as $anio)
                            <option value="{{ $anio }}" {{ request('anio') === $anio ? 'selected' : '' }}>{{ \App\Http\Controllers\Titulacion\TitulacionController::etiquetaAnio($anio) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm tit-filtros-botones">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Buscar</button>
                    <a href="{{ request()->fullUrlWithQuery(['exportar' => 1, 'page' => null, 'ver' => null, 'tab' => null]) }}" class="btn btn-sm btn-outline-success"
                       title="Descarga en Excel lo que se ve con estos filtros ({{ $egresados->total() }} filas, todas las páginas)">
                        <i class="fas fa-file-excel mr-1"></i>Exportar
                    </a>
                    @if (request()->filled('search') || request()->filled('estado_titulacion_id') || request()->filled('estado') || request()->filled('modalidad') || request()->filled('programa_id') || request()->filled('anio') || $filtroVigencia || $filtroTarea)
                        <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover titulacion-tabla" style="font-size: 14px">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:24px;"></th>
                        <th style="white-space:nowrap;">N° Expediente</th>
                        <th>Exalumno(a)</th>
                        <th>DNI</th>
                        <th>Programa</th>
                        <th>Promoción / ciclo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="titulacionFilas">
                    @forelse ($egresados as $i => $item)
                        @php
                            $filaClase = match ($item->tipo) {
                                'registrado' => '',
                                'activo_elegible' => 'table-primary',
                                default => 'table-warning',
                            };
                        @endphp
                        {{-- "collapsed" desde el inicio: Bootstrap solo la agrega tras el
                             primer clic, y sin ella la flecha arrancaba girada. --}}
                        <tr class="{{ $filaClase }} titulacion-fila-toggle collapsed" aria-expanded="false"
                            data-toggle="collapse" data-target="#detalle-{{ $item->row_id }}" style="cursor:pointer;" title="Ver más datos">
                            <td class="text-center text-muted tit-c-flecha"><i class="fas fa-chevron-down fa-xs titulacion-chevron"></i></td>
                            <td class="tit-c-exp" style="white-space:nowrap;">
                                @if ($item->expediente ?? null)
                                    <strong>{{ \App\Services\Titulacion\Expediente::mostrar($item->expediente) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="tit-c-nombre"><strong>{{ $item->nombre }}</strong></td>
                            <td class="tit-c-dni"><span class="tit-c-eti">DNI </span>{{ $item->dni ?: '—' }}</td>
                            <td class="tit-c-prog">
                                @if ($item->modalidad ?? null)
                                    <span class="badge {{ $item->modalidad === 'PPD' ? 'badge-warning' : 'badge-primary' }} mr-1">{{ $item->modalidad }}</span>
                                @endif
                                {{ $item->programa }}
                            </td>
                            <td class="tit-c-anio">{{ \App\Http\Controllers\Titulacion\TitulacionController::etiquetaAnio($item->anio ?? null) ?? '—' }}</td>
                            <td class="tit-c-estado">
                                <span class="badge badge-{{ $item->estado_badge }}">{{ $item->estado_label }}</span>
                                @if ($item->siguiente ?? null)
                                    <span class="titulacion-siguiente" title="Siguiente paso"><i class="fas fa-arrow-right fa-xs mr-1"></i>{{ $item->siguiente }}</span>
                                @endif
                                {{-- Sin filtro solo se marca lo urgente; con filtro, la fecha de cada fila. --}}
                                @if (($item->vigencia_grupo ?? null) === 'por_vencer' || ($filtroVigencia && in_array($item->vigencia_grupo ?? null, ['proximo_anio', 'vencido'], true)))
                                    <span class="titulacion-vence {{ $item->vigencia_grupo === 'proximo_anio' ? 'text-muted' : 'text-danger' }}"
                                          title="Vigencia del PTI">
                                        <i class="far fa-clock mr-1"></i>{{ $item->vigencia_grupo === 'vencido' ? 'Venció' : 'Vence' }} {{ $item->vigencia->vencimiento->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr class="collapse titulacion-detalle" id="detalle-{{ $item->row_id }}" data-parent="#titulacionFilas">
                            <td colspan="7">
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
                                        ciclo desde el cual la institución permite iniciar el PTI. Al iniciar, queda en estado
                                        "PTI en proceso" y aparece junto a los demás en la lista de arriba.
                                    </p>
                                    <form method="POST" action="{{ $item->accion_url }}" class="d-inline"
                                          data-confirm="Se iniciará el proceso de titulación para {{ $item->nombre }}."
                                          data-confirm-titulo="¿Iniciar el proceso?" data-confirm-boton="Sí, iniciar">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $item->accion_clase }}">
                                            <i class="fa fa-play fa-sm"></i> {{ $item->accion_label }}
                                        </button>
                                    </form>
                                    @if (! empty($item->filas_importadas) && count($item->filas_importadas))
                                        <details class="ft-desplegable mt-3">
                                            <summary><i class="fas fa-file-import mr-1"></i>Datos de archivos importados ({{ count($item->filas_importadas) }})</summary>
                                            <div class="mt-2">@include('titulacion._filas_importadas', ['filas' => $item->filas_importadas])</div>
                                        </details>
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
                        <tr><td colspan="7" class="text-center">No se encontraron exalumnos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $egresados->links() }}
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
                $(fila).collapse('show');
                fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        </script>
    @endif
    @include('titulacion._ficha_recursos')
    {{-- Ficha al abrir la fila: se pide una sola vez y queda en la página. Si falla,
         se ofrece abrirla en su página propia. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#titulacionFilas').on('show.bs.collapse', '.titulacion-detalle', function (e) {
                if (e.target !== this) return;
                var caja = this.querySelector('.js-ficha-lazy');
                if (!caja || caja.dataset.estado) return;
                caja.dataset.estado = 'cargando';
                fetch(caja.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
                    .then(function (html) { caja.innerHTML = html; caja.dataset.estado = 'lista'; })
                    .catch(function () {
                        caja.dataset.estado = '';
                        caja.innerHTML = '<div class="text-center small py-4">No se pudo cargar la ficha. '
                            + '<a href="' + caja.dataset.completa + '">Abrirla en su página</a> o vuelva a abrir la fila.</div>';
                    });
            });
        });
    </script>
    {{-- Acordeón: data-parent en cada detalle cierra el anterior al abrir otro.
         Si el que se cerraba estaba arriba, la fila recién abierta "salta" hacia
         arriba y puede quedar fuera de la pantalla: en ese caso se la trae a la vista. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#titulacionFilas').on('shown.bs.collapse', '.titulacion-detalle', function (e) {
                if (e.target !== this) return;
                var fila = this.previousElementSibling;
                if (!fila) return;
                var arriba = fila.getBoundingClientRect().top;
                if (arriba < 70 || arriba > window.innerHeight - 120) {
                    fila.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
@endsection
