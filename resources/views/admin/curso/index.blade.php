@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    @include('admin._partials.prog-styles')
    <style>
        /* Hero compacto (.cu-hero / .cu-kpi): vive en admin._partials.prog-styles */

        /* ── Barra de filtros en una tarjeta ──────────────────────────── */
        .cu-toolbar {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem .75rem;
            padding: .5rem .6rem; border-radius: .8rem;
            background: var(--pg-card); border: 1px solid var(--pg-border); box-shadow: var(--pg-shadow);
        }
        .cu-toolbar__programas { display: flex; align-items: center; gap: .3rem; overflow-x: auto; scrollbar-width: thin; padding-bottom: 1px; }
        .cu-toolbar__programas .pg-filter-btn { flex-shrink: 0; white-space: nowrap; }
        .cu-toolbar__sep { width: 1px; align-self: stretch; margin: .2rem .15rem; background: var(--pg-border); flex-shrink: 0; }
        .cu-chip-num {
            display: inline-block; min-width: 1.35rem; padding: 0 .35rem; border-radius: 999px;
            background: rgba(0, 0, 0, .07); font-size: .68rem; font-weight: 700; text-align: center;
        }
        .pg-filter-btn.active .cu-chip-num { background: rgba(255, 255, 255, .25); }
        .cu-toolbar__buscar { display: flex; align-items: center; gap: .35rem; flex: 1 1 26rem; justify-content: flex-end; min-width: 0; }
        .cu-toolbar__buscar .pg-search-wrap { max-width: 26rem; }
        .cu-toolbar__buscar .pg-input { padding-right: 2rem !important; }
        .cu-toolbar__buscar input[type="search"]::-webkit-search-cancel-button { cursor: pointer; }
        .cu-kbd {
            position: absolute; right: .55rem; top: 50%; transform: translateY(-50%);
            padding: 0 .38rem; border-radius: .3rem; font-size: .68rem; line-height: 1.35;
            color: var(--pg-muted); background: var(--pg-input-bg); border: 1px solid var(--pg-border); box-shadow: none;
            pointer-events: none;
        }
        .pg-search-wrap:focus-within .cu-kbd { display: none; }
        .dark-mode .cu-chip-num, .dim-mode .cu-chip-num { background: rgba(255, 255, 255, .1); }
        @media (max-width: 767.98px) {
            .cu-toolbar__buscar { justify-content: stretch; }
            .cu-toolbar__buscar .pg-search-wrap { max-width: none; }
            .cu-kbd { display: none; }
        }
        /* ── Filtro de tabs ────────────────────────────────────────────── */
        .pg-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
            padding: .32rem .78rem;
            border-radius: .5rem;
            font-size: .74rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--pg-border);
            background: var(--pg-card);
            color: var(--pg-muted);
            transition: background .18s, color .18s, border-color .18s;
        }

        .pg-filter-btn:hover {
            background: rgba(10, 91, 209, .1);
            color: #0a5bd1;
            border-color: rgba(10, 91, 209, .28);
        }

        .pg-filter-btn.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        /* Inicial / Primaria EIB: conservan siempre una ligera transparencia azul
           como identidad, igual que PPD ya tenía la suya en ámbar. */
        .pg-filter-btn.active-fid {
            background: rgba(10, 91, 209, .1);
            color: #0a5bd1;
            border-color: rgba(10, 91, 209, .28);
        }

        .pg-filter-btn.active-fid.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .1);
            color: #b45309;
            border-color: rgba(245, 158, 11, .28);
        }

        .pg-filter-btn.active-ppd.active {
            background: #b45309;
            color: #fff;
            border-color: #b45309;
        }

        .dark-mode .pg-filter-btn {
            background: rgba(255, 255, 255, .04);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        .dark-mode .pg-filter-btn:hover {
            background: rgba(10, 91, 209, .3);
            color: #93c5fd;
            border-color: rgba(10, 91, 209, .5);
        }

        .dark-mode .pg-filter-btn.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        .dark-mode .pg-filter-btn.active-fid {
            background: rgba(10, 91, 209, .22);
            color: #93c5fd;
            border-color: rgba(10, 91, 209, .4);
        }

        .dark-mode .pg-filter-btn.active-fid.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        .dark-mode .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .18);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, .3);
        }

        .dark-mode .pg-filter-btn.active-ppd.active {
            background: #b45309;
            color: #fff;
            border-color: #b45309;
        }

        .dim-mode .pg-filter-btn {
            background: rgba(255, 255, 255, .03);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        .dim-mode .pg-filter-btn:hover {
            background: rgba(10, 91, 209, .22);
            color: #93c5fd;
            border-color: rgba(10, 91, 209, .4);
        }

        .dim-mode .pg-filter-btn.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        .dim-mode .pg-filter-btn.active-fid {
            background: rgba(10, 91, 209, .16);
            color: #93c5fd;
            border-color: rgba(10, 91, 209, .32);
        }

        .dim-mode .pg-filter-btn.active-fid.active {
            background: #0a5bd1e8;
            color: #fff;
            border-color: #0a5bd1e8;
        }

        .dim-mode .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .14);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, .25);
        }

        .dim-mode .pg-filter-btn.active-ppd.active {
            background: #b45309;
            color: #fff;
            border-color: #b45309;
        }

        /* ── Buscador ─────────────────────────────────────────────────── */
        .pg-search-wrap {
            position: relative;
            flex: 1 1 260px;
            min-width: 0;
            max-width: 480px;
        }

        .pg-search-wrap .pg-search-icon {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pg-muted);
            font-size: .8rem;
            pointer-events: none;
        }

        .pg-search-wrap .pg-input {
            padding-left: 2rem !important;
            width: 100%;
        }

        /* ── Filtros de Programa/Ciclo: todo en una sola línea ───────────── */
        .pg-filtros-linea {
            flex-wrap: nowrap;
            min-width: 0;
        }

        .pg-filtros-linea .pg-select-compact,
        .pg-filtros-linea .pg-input,
        .pg-filtros-linea #limpiarFiltros {
            height: 34px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            box-sizing: border-box;
        }

        .pg-select-compact {
            width: 95px !important;
            flex-shrink: 0;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        @media (max-width: 575.98px) {
            .pg-select-compact {
                width: 80px !important;
            }
        }

        /* ── Contador de cursos visibles ──────────────────────────────── */
        .pg-count-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            height: 34px;
            padding: 0 .7rem;
            border-radius: 8px;
            background: rgba(10, 91, 209, .1);
            border: 1px solid rgba(10, 91, 209, .28);
            color: #0a5bd1;
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pg-count-badge i {
            font-size: .74rem;
            opacity: .85;
        }

        .dark-mode .pg-count-badge {
            background: rgba(10, 91, 209, .22);
            border-color: rgba(10, 91, 209, .38);
            color: #93c5fd;
        }

        .dim-mode .pg-count-badge {
            background: rgba(10, 91, 209, .16);
            border-color: rgba(10, 91, 209, .32);
            color: #93c5fd;
        }

        /* ── Tabla container ──────────────────────────────────────────── */
        .pg-table-wrap {
            background: var(--pg-card);
            border: 1px solid var(--pg-border);
            border-radius: .85rem;
            overflow: hidden;
            box-shadow: var(--pg-shadow);
        }

        /* ── Celda datos curso ────────────────────────────────────────── */
        .curso-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--pg-text);
            line-height: 1.3;
        }

        /* ── Columna Calificación (antes dentro de Datos del curso) ── */
        .curso-calif-th { width: 30%; min-width: 220px; }
        .curso-calif-td { vertical-align: middle !important; }
        .curso-calif { margin-top: 0 !important; }
        .curso-calif li.curso-pct-ppd,
        .curso-calif li.curso-pct-fid { max-width: none !important; }
        .curso-calif li.curso-pct-ppd,
        .curso-calif li.curso-pct-fid-p1 { margin-top: 0 !important; }
        .curso-calif li + li { margin-top: .2rem; }
        .curso-calif-vacio { color: var(--pg-muted); }

        .curso-meta {
            margin: 0;
            padding: 0;
            list-style: none;
            margin-top: .3rem;
        }

        .curso-meta li {
            font-size: .76rem;
            color: var(--pg-muted);
            line-height: 1.7;
        }

        .curso-meta li strong {
            color: var(--pg-text-2, var(--pg-text));
            font-weight: 600;
        }

        /* ── % calificado (solo cursos PPD) ───────────────────────────── */
        .curso-meta li.curso-pct-ppd {
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 300px;
            margin-top: .15rem;
        }

        .curso-pct-ppd-label,
        .curso-pct-ppd-val {
            font-weight: 600;
            color: #d97706;
            white-space: nowrap;
        }

        .curso-pct-ppd-val {
            min-width: 48px;
            text-align: right;
        }

        .curso-pct-ppd-bar {
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, .06);
        }

        .curso-pct-ppd-bar .progress-bar {
            background: linear-gradient(90deg, #e5973a, #f5b45f);
        }

        .dark-mode .curso-pct-ppd-bar,
        .dim-mode .curso-pct-ppd-bar {
            background: rgba(255, 255, 255, .1);
        }

        .dark-mode .curso-pct-ppd-label,
        .dark-mode .curso-pct-ppd-val,
        .dim-mode .curso-pct-ppd-label,
        .dim-mode .curso-pct-ppd-val {
            color: #fcd34d;
        }

        /* ── % calificado por periodo (solo cursos FID) ───────────────── */
        .curso-meta li.curso-pct-fid {
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 300px;
        }

        .curso-meta li.curso-pct-fid-p1 {
            margin-top: .15rem;
        }

        .curso-pct-fid-label,
        .curso-pct-fid-val {
            font-weight: 600;
            white-space: nowrap;
        }

        .curso-pct-fid-label {
            min-width: 72px;
        }

        .curso-pct-fid-val {
            min-width: 48px;
            text-align: right;
        }

        .curso-pct-fid-bar {
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, .06);
        }

        .curso-pct-fid-p1 .curso-pct-fid-label,
        .curso-pct-fid-p1 .curso-pct-fid-val { color: #4e73df; }
        .curso-pct-fid-p1 .progress-bar { background: #4e73df; }

        .curso-pct-fid-p2 .curso-pct-fid-label,
        .curso-pct-fid-p2 .curso-pct-fid-val { color: #17a673; }
        .curso-pct-fid-p2 .progress-bar { background: #1cc88a; }

        .curso-pct-fid-p3 .curso-pct-fid-label,
        .curso-pct-fid-p3 .curso-pct-fid-val { color: #2c9faf; }
        .curso-pct-fid-p3 .progress-bar { background: #36b9cc; }

        .dark-mode .curso-pct-fid-bar,
        .dim-mode .curso-pct-fid-bar {
            background: rgba(255, 255, 255, .1);
        }

        .dark-mode .curso-pct-fid-p1 .curso-pct-fid-label,
        .dark-mode .curso-pct-fid-p1 .curso-pct-fid-val,
        .dim-mode .curso-pct-fid-p1 .curso-pct-fid-label,
        .dim-mode .curso-pct-fid-p1 .curso-pct-fid-val { color: #93a8f4; }

        .dark-mode .curso-pct-fid-p2 .curso-pct-fid-label,
        .dark-mode .curso-pct-fid-p2 .curso-pct-fid-val,
        .dim-mode .curso-pct-fid-p2 .curso-pct-fid-label,
        .dim-mode .curso-pct-fid-p2 .curso-pct-fid-val { color: #6ee7b7; }

        .dark-mode .curso-pct-fid-p3 .curso-pct-fid-label,
        .dark-mode .curso-pct-fid-p3 .curso-pct-fid-val,
        .dim-mode .curso-pct-fid-p3 .curso-pct-fid-label,
        .dim-mode .curso-pct-fid-p3 .curso-pct-fid-val { color: #7dd3e0; }
    </style>

    <div class="pg-wrap">
        <div class="container-fluid py-3">

            {{-- ── Hero compacto: título + conteos + acción ── --}}
            <div class="pg-hero cu-hero" id="pg-hero">
                <div class="cu-hero__fila">
                    <div class="cu-hero__titulo">
                        <h4 class="mb-0">Cursos</h4>
                        <p class="pg-hero-sub">Por programa y ciclo · EESP Pukllasunchis</p>
                    </div>
                    <div class="cu-hero__kpis" aria-label="Resumen de cursos">
                        <div class="cu-kpi">
                            <span class="cu-kpi__val">{{ $cant }}</span>
                            <span class="cu-kpi__lbl"><i class="fas fa-book"></i> Total</span>
                        </div>
                        <div class="cu-kpi">
                            <span class="cu-kpi__val">{{ $inicial + $EIB }}</span>
                            <span class="cu-kpi__lbl"><i class="fas fa-graduation-cap"></i> FID</span>
                        </div>
                        <div class="cu-kpi cu-kpi--ppd">
                            <span class="cu-kpi__val">{{ $iniPPD + $priPPD }}</span>
                            <span class="cu-kpi__lbl"><i class="fas fa-user-graduate"></i> PPD</span>
                        </div>
                    </div>
                    <a href="{{ route('curso.create') }}" class="pg-hero-btn cu-hero__btn">
                        <i class="fas fa-plus fa-xs"></i> Nuevo curso
                    </a>
                </div>
            </div>

            {{-- ── Alerta ── --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                    <a type="button" class="close" data-dismiss="alert"><span>&times;</span></a>
                </div>
            @endif

            {{-- ── Barra de filtros: programa (con su conteo) + búsqueda + ciclo ── --}}
            <div class="cu-toolbar mb-3" role="search">
                <div class="cu-toolbar__programas" role="tablist" aria-label="Programa">
                    <button class="pg-filter-btn active" id="todos" role="tab">
                        Todos <span class="cu-chip-num">{{ $cant }}</span>
                    </button>
                    <button class="pg-filter-btn active-fid" id="inicial" data-programa-id="1" role="tab">
                        Inicial <span class="cu-chip-num">{{ $inicial }}</span>
                    </button>
                    <button class="pg-filter-btn active-fid" id="eib" data-programa-id="2" role="tab">
                        Primaria EIB <span class="cu-chip-num">{{ $EIB }}</span>
                    </button>
                    <span class="cu-toolbar__sep" aria-hidden="true"></span>
                    <button class="pg-filter-btn active-ppd" id="inippd" data-programa-id="3" role="tab">
                        Inicial PPD <span class="cu-chip-num">{{ $iniPPD }}</span>
                    </button>
                    <button class="pg-filter-btn active-ppd" id="prippd" data-programa-id="4" role="tab">
                        Primaria PPD <span class="cu-chip-num">{{ $priPPD }}</span>
                    </button>
                </div>
                <div class="cu-toolbar__buscar pg-filtros-linea">
                    <div class="pg-search-wrap mb-0">
                        <i class="fas fa-search pg-search-icon"></i>
                        <input type="search" class="pg-input" id="buscador" placeholder="Buscar curso, docente o competencia…" autocomplete="off" aria-label="Buscar curso">
                        <kbd class="cu-kbd" title="Atajo: presione / para buscar">/</kbd>
                    </div>
                    <select id="filtroCiclo" class="pg-select pg-select-compact" title="Filtrar por ciclo" aria-label="Ciclo">
                        <option value="">Ciclo</option>
                        @foreach ($ciclosPorPrograma as $programaId => $ciclosDelPrograma)
                            <optgroup label="{{ optional($ciclosDelPrograma->first()->programa)->nombre ?? 'Programa' }}" data-programa-id="{{ $programaId }}">
                                @foreach ($ciclosDelPrograma as $ciclo)
                                    <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <span class="pg-count-badge" id="pgCountBadge" title="Cursos visibles con los filtros actuales" aria-live="polite">
                        <span id="pgCountNum">{{ $cant }}</span>&nbsp;visibles
                    </span>
                    <button type="button" class="pg-filter-btn flex-shrink-0" id="limpiarFiltros" title="Quitar programa, ciclo y búsqueda (Esc)" hidden>
                        <i class="fas fa-times fa-xs"></i> Limpiar
                    </button>
                </div>
            </div>

            {{-- ── Macro tabla reutilizable ── --}}
            @php
                function renderCursoRow($curso)
                {
                    return $curso;
                }
            @endphp

            {{-- ── Tabla TODOS ── --}}
            <div id="tablaCursos" class="pg-table-wrap mb-3">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th class="curso-calif-th">Calificación</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursos as $curso)
                                <tr data-programa-id="{{ $curso->ciclo->programa_id }}" data-ciclo-id="{{ $curso->ciclo_id }}">
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if ($curso->ciclo->programa->esFid() && $curso->ciclo->programa->eib)
                                                    EIB
                                                @elseif($curso->ciclo->programa->esFid() && $curso->ciclo->programa->esInicial())
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td class="curso-calif-td">
                                        @if (is_null($pctPpd[$curso->id] ?? null) && is_null($pctFid[$curso->id] ?? null))
                                            <span class="curso-calif-vacio">—</span>
                                        @else
                                            <ul class="curso-meta curso-calif">
                                                @include('admin.curso._pct-ppd', ['pct' => $pctPpd[$curso->id] ?? null])
                                                @include('admin.curso._pct-fid', ['pct' => $pctFid[$curso->id] ?? null])
                                            </ul>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"
                                                title="Ver">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"
                                                title="Editar">
                                                <i class="fas fa-pen fa-xs"></i>
                                            </a>
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle"
                                                title="Asignar docentes">
                                                <i class="fas fa-chalkboard-teacher fa-xs"></i>
                                            </a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del" title="Eliminar">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla INICIAL ── --}}
            <div id="tablaInicial" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th class="curso-calif-th">Calificación</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosInicial as $curso)
                                <tr data-programa-id="{{ $curso->ciclo->programa_id }}" data-ciclo-id="{{ $curso->ciclo_id }}">
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if ($curso->ciclo->programa->esFid() && $curso->ciclo->programa->eib)
                                                    EIB
                                                @elseif($curso->ciclo->programa->esFid() && $curso->ciclo->programa->esInicial())
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td class="curso-calif-td">
                                        @if (is_null($pctPpd[$curso->id] ?? null) && is_null($pctFid[$curso->id] ?? null))
                                            <span class="curso-calif-vacio">—</span>
                                        @else
                                            <ul class="curso-meta curso-calif">
                                                @include('admin.curso._pct-ppd', ['pct' => $pctPpd[$curso->id] ?? null])
                                                @include('admin.curso._pct-fid', ['pct' => $pctFid[$curso->id] ?? null])
                                            </ul>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle"
                                                title="Asignar docentes"><i
                                                    class="fas fa-chalkboard-teacher fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla EIB ── --}}
            <div id="tablaEib" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th class="curso-calif-th">Calificación</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosEib as $curso)
                                <tr data-programa-id="{{ $curso->ciclo->programa_id }}" data-ciclo-id="{{ $curso->ciclo_id }}">
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if ($curso->ciclo->programa->esFid() && $curso->ciclo->programa->eib)
                                                    EIB
                                                @elseif($curso->ciclo->programa->esFid() && $curso->ciclo->programa->esInicial())
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td class="curso-calif-td">
                                        @if (is_null($pctPpd[$curso->id] ?? null) && is_null($pctFid[$curso->id] ?? null))
                                            <span class="curso-calif-vacio">—</span>
                                        @else
                                            <ul class="curso-meta curso-calif">
                                                @include('admin.curso._pct-ppd', ['pct' => $pctPpd[$curso->id] ?? null])
                                                @include('admin.curso._pct-fid', ['pct' => $pctFid[$curso->id] ?? null])
                                            </ul>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle"
                                                title="Asignar docentes"><i
                                                    class="fas fa-chalkboard-teacher fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla INICIAL PPD ── --}}
            <div id="tablaIniPpd" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th class="curso-calif-th">Calificación</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inicialPPD as $curso)
                                <tr data-programa-id="{{ $curso->ciclo->programa_id }}" data-ciclo-id="{{ $curso->ciclo_id }}">
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if ($curso->ciclo->programa->esFid() && $curso->ciclo->programa->eib)
                                                    EIB
                                                @elseif($curso->ciclo->programa->esFid() && $curso->ciclo->programa->esInicial())
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td class="curso-calif-td">
                                        @if (is_null($pctPpd[$curso->id] ?? null) && is_null($pctFid[$curso->id] ?? null))
                                            <span class="curso-calif-vacio">—</span>
                                        @else
                                            <ul class="curso-meta curso-calif">
                                                @include('admin.curso._pct-ppd', ['pct' => $pctPpd[$curso->id] ?? null])
                                                @include('admin.curso._pct-fid', ['pct' => $pctFid[$curso->id] ?? null])
                                            </ul>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle"
                                                title="Asignar docentes"><i
                                                    class="fas fa-chalkboard-teacher fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla PRIMARIA PPD ── --}}
            <div id="tablaPriPpd" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th class="curso-calif-th">Calificación</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($primariaPPD as $curso)
                                <tr data-programa-id="{{ $curso->ciclo->programa_id }}" data-ciclo-id="{{ $curso->ciclo_id }}">
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if ($curso->ciclo->programa->esFid() && $curso->ciclo->programa->eib)
                                                    EIB
                                                @elseif($curso->ciclo->programa->esFid() && $curso->ciclo->programa->esInicial())
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td class="curso-calif-td">
                                        @if (is_null($pctPpd[$curso->id] ?? null) && is_null($pctFid[$curso->id] ?? null))
                                            <span class="curso-calif-vacio">—</span>
                                        @else
                                            <ul class="curso-meta curso-calif">
                                                @include('admin.curso._pct-ppd', ['pct' => $pctPpd[$curso->id] ?? null])
                                                @include('admin.curso._pct-fid', ['pct' => $pctFid[$curso->id] ?? null])
                                            </ul>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle"
                                                title="Asignar docentes"><i
                                                    class="fas fa-chalkboard-teacher fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- ── GSAP ── --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            gsap.timeline({
                    defaults: {
                        ease: 'power3.out'
                    }
                })
                .fromTo('#pg-hero', {
                    opacity: 0,
                    y: -20,
                    scale: .98
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .5
                })
                .fromTo('.cu-kpi', {
                    opacity: 0,
                    y: 18,
                    scale: .93
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .35,
                    stagger: .08
                }, '-=.18');
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var $allBtns = $('.pg-filter-btn');

            function showTable(tableId, btn) {
                $('#tablaCursos, #tablaInicial, #tablaEib, #tablaIniPpd, #tablaPriPpd').hide();
                $(tableId).show();
                $allBtns.removeClass('active');
                $(btn).addClass('active');

                // El badge de Programa elegido decide qué Ciclos mostrar en el select.
                var programaId = $(btn).attr('data-programa-id') || '';
                var selectCiclo = document.getElementById('filtroCiclo');
                if (selectCiclo) {
                    selectCiclo.value = '';
                    Array.prototype.forEach.call(selectCiclo.querySelectorAll('optgroup'), function(optgroup) {
                        optgroup.hidden = !!programaId && optgroup.getAttribute('data-programa-id') !== programaId;
                    });
                }
                if (window.aplicarFiltrosCursos) window.aplicarFiltrosCursos();
            }

            $('#todos').click(function() {
                showTable('#tablaCursos', this);
            });
            $('#inicial').click(function() {
                showTable('#tablaInicial', this);
            });
            $('#eib').click(function() {
                showTable('#tablaEib', this);
            });
            $('#inippd').click(function() {
                showTable('#tablaIniPpd', this);
            });
            $('#prippd').click(function() {
                showTable('#tablaPriPpd', this);
            });
        });
    </script>

    <script>
        (function() {
            'use strict';

            var buscador = document.getElementById('buscador');
            var selectCiclo = document.getElementById('filtroCiclo');
            var btnLimpiar = document.getElementById('limpiarFiltros');
            var countNum = document.getElementById('pgCountNum');

            // El Programa ya lo deciden los badges de la izquierda (que además muestran
            // solo la tabla de ese programa); el select de Ciclo filtra dentro de eso.
            selectCiclo.addEventListener('change', aplicarFiltros);

            // ── Filtro combinado: búsqueda de texto + Ciclo, en tiempo real ──
            function aplicarFiltros() {
                var texto = (buscador.value || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                var terminos = texto.split(' ').filter(Boolean);
                var cicloId = selectCiclo.value;
                var visibles = 0;

                document.querySelectorAll('table').forEach(function(tabla) {
                    var tablaVisible = tabla.offsetParent !== null;
                    tabla.querySelectorAll('tbody tr').forEach(function(fila) {
                        if (!fila.dataset.programaId) return; // filas vacías ("Sin cursos", etc.)

                        var coincideTexto = terminos.every(function(t) {
                            return fila.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').indexOf(t) !== -1;
                        });
                        var coincideCiclo = !cicloId || fila.dataset.cicloId === cicloId;
                        var visible = coincideTexto && coincideCiclo;

                        fila.style.display = visible ? '' : 'none';
                        if (visible && tablaVisible) visibles++;
                    });
                });

                if (countNum) countNum.textContent = visibles;
            }

            buscador.addEventListener('input', aplicarFiltros);
            window.aplicarFiltrosCursos = aplicarFiltros;

            // "Limpiar" solo aparece si hay algo que limpiar.
            function actualizarLimpiar() {
                var todos = document.getElementById('todos');
                btnLimpiar.hidden = !buscador.value && !selectCiclo.value && todos.classList.contains('active');
            }
            document.addEventListener('click', function(e) {
                if (e.target.closest('.pg-filter-btn')) setTimeout(actualizarLimpiar, 0);
            });
            buscador.addEventListener('input', actualizarLimpiar);
            selectCiclo.addEventListener('change', actualizarLimpiar);

            // Atajos: "/" enfoca la búsqueda; Esc la limpia (o quita todos los filtros).
            document.addEventListener('keydown', function(e) {
                var escribiendo = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
                if (e.key === '/' && !escribiendo) {
                    e.preventDefault();
                    buscador.focus();
                } else if (e.key === 'Escape' && document.activeElement === buscador) {
                    if (buscador.value) {
                        buscador.value = '';
                        aplicarFiltros();
                        actualizarLimpiar();
                    } else {
                        buscador.blur();
                    }
                }
            });

            btnLimpiar.addEventListener('click', function() {
                buscador.value = '';
                selectCiclo.value = '';
                Array.prototype.forEach.call(selectCiclo.querySelectorAll('optgroup'), function(optgroup) {
                    optgroup.hidden = false;
                });
                document.getElementById('todos').click();
                aplicarFiltros();
            });
        })();
    </script>
@endsection
