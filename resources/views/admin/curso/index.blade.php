@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    @include('admin._partials.prog-styles')
    <style>
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
    </style>

    <div class="pg-wrap">
        <div class="container-fluid py-3">

            {{-- ── Hero ── --}}
            <div class="pg-hero" id="pg-hero">
                <div class="d-flex align-items-center justify-content-between flex-wrap"
                    style="gap:1rem; position:relative; z-index:1;">
                    <div>
                        <div class="pg-hero-label">
                            <i class="fas fa-university"></i> EESP Pukllasunchis
                        </div>
                        <h4>Cursos</h4>
                        <p class="pg-hero-sub">Listado completo de cursos organizados por programa y ciclo</p>
                    </div>
                    <a href="{{ route('curso.create') }}" class="pg-hero-btn align-self-start">
                        <i class="fas fa-plus fa-xs"></i> Nuevo Curso
                    </a>
                </div>
            </div>

            {{-- ── Stats ── --}}
            <div class="row mb-3">
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <i class="fas fa-book" style="color:#2563eb;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $cant }}</div>
                            <div class="pg-stat-lbl">Total Cursos</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                            <i class="fas fa-graduation-cap" style="color:#16a34a;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $inicial + $EIB }}</div>
                            <div class="pg-stat-lbl">Cursos FID</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                            <i class="fas fa-user-graduate" style="color:#d97706;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $iniPPD + $priPPD }}</div>
                            <div class="pg-stat-lbl">Cursos PPD</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Alerta ── --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                    <a type="button" class="close" data-dismiss="alert"><span>&times;</span></a>
                </div>
            @endif

            {{-- ── Filtros + Buscador ── --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:.6rem;">
                <div class="d-flex flex-wrap" style="gap:.4rem;">
                    <button class="pg-filter-btn active" id="todos">
                        <i class="fas fa-th-list fa-xs"></i> Todos <span class="ml-1"
                            style="opacity:.7;">{{ $cant }}</span>
                    </button>
                    <button class="pg-filter-btn active-fid" id="inicial" data-programa-id="1">
                        <i class="fas fa-graduation-cap fa-xs"></i> Inicial <span class="ml-1"
                            style="opacity:.7;">{{ $inicial }}</span>
                    </button>
                    <button class="pg-filter-btn active-fid" id="eib" data-programa-id="2">
                        <i class="fas fa-graduation-cap fa-xs"></i> Primaria EIB <span class="ml-1"
                            style="opacity:.7;">{{ $EIB }}</span>
                    </button>
                    <button class="pg-filter-btn active-ppd" id="inippd" data-programa-id="3">
                        <i class="fas fa-user-graduate fa-xs"></i> Inicial PPD <span class="ml-1"
                            style="opacity:.7;">{{ $iniPPD }}</span>
                    </button>
                    <button class="pg-filter-btn active-ppd" id="prippd" data-programa-id="4">
                        <i class="fas fa-user-graduate fa-xs"></i> Primaria PPD <span class="ml-1"
                            style="opacity:.7;">{{ $priPPD }}</span>
                    </button>
                </div>
                <div class="d-flex align-items-center pg-filtros-linea" style="gap:.35rem;">
                    <span class="pg-count-badge" id="pgCountBadge" title="Cursos visibles con los filtros actuales">
                        <i class="fas fa-filter"></i> <span id="pgCountNum">{{ $cant }}</span> cursos
                    </span>
                    <button type="button" class="pg-filter-btn flex-shrink-0" id="limpiarFiltros" title="Quitar ciclo y búsqueda">
                        <i class="fas fa-rotate-left fa-xs"></i> Limpiar
                    </button>
                    <select id="filtroCiclo" class="pg-select pg-select-compact" title="Filtrar por ciclo">
                        <option value="">Ciclo</option>
                        @foreach ($ciclosPorPrograma as $programaId => $ciclosDelPrograma)
                            <optgroup label="{{ optional($ciclosDelPrograma->first()->programa)->nombre ?? 'Programa' }}" data-programa-id="{{ $programaId }}">
                                @foreach ($ciclosDelPrograma as $ciclo)
                                    <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div class="pg-search-wrap mb-0">
                        <i class="fas fa-search pg-search-icon"></i>
                        <input type="text" class="pg-input" id="buscador" placeholder="Buscar curso...">
                    </div>
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
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
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
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
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
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
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
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
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
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
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
                .fromTo('.pg-stat', {
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
