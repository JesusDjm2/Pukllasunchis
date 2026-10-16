@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <style>
        /* ══ Lista de docentes: pestañas FID/PPD + tarjetas ═════════════════ */
        .dl {
            --dl-card: #fff;
            --dl-soft: #f8fafc;
            --dl-border: #e5e7eb;
            --dl-text: #1e293b;
            --dl-muted: #64748b;
            --dl-fid: #16a34a;
            --dl-fid-soft: rgba(22, 163, 74, .08);
            --dl-ppd: #d97706;
            --dl-ppd-soft: rgba(217, 119, 6, .09);
            --dl-accent: #0891b2;
            --dl-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 4px 14px rgba(15, 23, 42, .04);
            /* hereda el fondo de la página para que la barra fija (sticky) no quede transparente */
            background: inherit;
        }

        .dark-mode .dl,
        .dim-mode .dl {
            --dl-card: rgba(255, 255, 255, .035);
            --dl-soft: rgba(255, 255, 255, .03);
            --dl-border: rgba(255, 255, 255, .1);
            --dl-text: #e2e8f0;
            --dl-muted: #94a3b8;
            --dl-fid: #4ade80;
            --dl-fid-soft: rgba(74, 222, 128, .1);
            --dl-ppd: #fbbf24;
            --dl-ppd-soft: rgba(251, 191, 36, .1);
            --dl-accent: #22d3ee;
            --dl-shadow: none;
        }

        /* Barra de herramientas */
        .dl-toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
            padding: .65rem 0;
            margin-bottom: .75rem;
            background: inherit;
            border-bottom: 1px solid var(--dl-border);
        }

        .dl-tabs {
            display: inline-flex;
            padding: 3px;
            border-radius: 10px;
            background: var(--dl-soft);
            border: 1px solid var(--dl-border);
        }

        .dl-tab {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 0;
            background: transparent;
            color: var(--dl-muted);
            font-size: .8rem;
            font-weight: 600;
            padding: .38rem .85rem;
            border-radius: 7px;
            cursor: pointer;
            transition: background .15s, color .15s, box-shadow .15s;
        }

        .dl-tab:hover { color: var(--dl-text); }
        .dl-tab:focus-visible { outline: 2px solid var(--dl-accent); outline-offset: 1px; }

        .dl-tab.is-active {
            background: var(--dl-card);
            color: var(--dl-text);
            box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
        }

        .dark-mode .dl-tab.is-active,
        .dim-mode .dl-tab.is-active { background: rgba(255, 255, 255, .1); box-shadow: none; }

        .dl-tab[data-tab="fid"].is-active { color: var(--dl-fid); }
        .dl-tab[data-tab="ppd"].is-active { color: var(--dl-ppd); }

        .dl-tab-count {
            font-size: .68rem;
            font-weight: 700;
            padding: .05rem .45rem;
            border-radius: 999px;
            background: var(--dl-border);
            color: var(--dl-muted);
        }

        .dl-search {
            position: relative;
            flex: 1 1 260px;
            max-width: 420px;
        }

        .dl-search i {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dl-muted);
            font-size: .78rem;
        }

        .dl-search input {
            width: 100%;
            height: 34px;
            padding: 0 .75rem 0 2rem;
            font-size: .82rem;
            color: var(--dl-text);
            background: var(--dl-card);
            border: 1px solid var(--dl-border);
            border-radius: 8px;
        }

        .dl-search input:focus {
            outline: none;
            border-color: var(--dl-accent);
            box-shadow: 0 0 0 3px rgba(8, 145, 178, .15);
        }

        .dl-chip {
            border: 1px solid var(--dl-border);
            background: var(--dl-card);
            color: var(--dl-muted);
            font-size: .76rem;
            font-weight: 600;
            padding: .32rem .7rem;
            border-radius: 999px;
            cursor: pointer;
        }

        .dl-chip.is-active {
            border-color: var(--dl-accent);
            color: var(--dl-accent);
            background: rgba(8, 145, 178, .08);
        }

        .dl-toolbar-end {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .dl-result {
            font-size: .76rem;
            color: var(--dl-muted);
            white-space: nowrap;
        }

        .dl-link-btn {
            border: 0;
            background: none;
            color: var(--dl-accent);
            font-size: .76rem;
            font-weight: 600;
            padding: .25rem .35rem;
            cursor: pointer;
        }

        /* Tarjeta del docente */
        .dl-card {
            background: var(--dl-card);
            border: 1px solid var(--dl-border);
            border-radius: 12px;
            box-shadow: var(--dl-shadow);
            margin-bottom: .7rem;
        }

        .dl-head {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .8rem 1rem;
            border-radius: 11px;
            cursor: pointer;
            user-select: none;
        }

        .dl-head:hover { background: var(--dl-soft); }

        .dl-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            color: #0e7490;
            background: linear-gradient(135deg, #cffafe, #a5f3fc);
        }

        .dark-mode .dl-avatar,
        .dim-mode .dl-avatar { color: #a5f3fc; background: rgba(34, 211, 238, .15); }

        .dl-id { flex: 1 1 auto; min-width: 0; }

        .dl-name {
            font-size: .92rem;
            font-weight: 700;
            color: var(--dl-text);
            line-height: 1.25;
        }

        .dl-sub {
            font-size: .74rem;
            color: var(--dl-muted);
            display: flex;
            flex-wrap: wrap;
            gap: .15rem .65rem;
            margin-top: .1rem;
        }

        .dl-sub a { color: inherit; text-decoration: underline dotted; }

        /* Nombre, correo y DNI se pueden seleccionar y copiar aunque estén en la cabecera
           del acordeón (que es clicable): al soltar con texto seleccionado no se abre/cierra. */
        .dl-head .dl-name, .dl-head .dl-sub { user-select: text; }
        .dl-head .dl-sub { cursor: auto; }
        .dl-copiar {
            border: 0; background: transparent; padding: 0 .15rem; margin-left: .15rem;
            color: inherit; opacity: .45; cursor: pointer; font-size: .72rem; line-height: 1;
            border-radius: 4px; transition: opacity .15s, color .15s;
        }
        .dl-copiar:hover, .dl-copiar:focus-visible { opacity: 1; color: var(--dl-accent, #4e73df); outline: 0; }
        .dl-copiar.is-ok { opacity: 1; color: #16a34a; }

        .dl-tutor {
            display: inline-block;
            font-size: .66rem;
            font-weight: 700;
            padding: .05rem .45rem;
            border-radius: 999px;
            background: rgba(100, 116, 139, .12);
            color: var(--dl-muted);
            margin-left: .2rem;
        }

        .dl-stats {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-shrink: 0;
        }

        .dl-pill {
            font-size: .7rem;
            font-weight: 700;
            padding: .18rem .55rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        .dl-pill-fid { background: var(--dl-fid-soft); color: var(--dl-fid); }
        .dl-pill-ppd { background: var(--dl-ppd-soft); color: var(--dl-ppd); }
        .dl-pill-none { background: var(--dl-soft); color: var(--dl-muted); font-weight: 600; }

        .dl-ring {
            --p: 0;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(var(--dl-accent) calc(var(--p) * 1%), var(--dl-border) 0);
        }

        .dl-ring span {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--dl-card);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .62rem;
            font-weight: 700;
            color: var(--dl-text);
        }

        .dark-mode .dl-ring span,
        .dim-mode .dl-ring span { background: #1f2937; }

        .dl-actions {
            display: flex;
            gap: .3rem;
            flex-shrink: 0;
        }

        .dl-icon-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--dl-border);
            background: var(--dl-card);
            color: var(--dl-muted);
            font-size: .75rem;
            cursor: pointer;
            text-decoration: none !important;
            transition: color .15s, border-color .15s, background .15s;
        }

        .dl-icon-btn:hover { color: var(--dl-accent); border-color: var(--dl-accent); }
        .dl-icon-btn.is-add:hover { color: var(--dl-fid); border-color: var(--dl-fid); }
        .dl-icon-btn.is-danger:hover { color: #dc2626; border-color: #dc2626; background: rgba(220, 38, 38, .06); }

        .dl-chevron {
            color: var(--dl-muted);
            font-size: .75rem;
            transition: transform .2s;
            flex-shrink: 0;
        }

        .dl-card.is-open .dl-chevron { transform: rotate(180deg); }

        .dl-body {
            display: none;
            border-top: 1px solid var(--dl-border);
            padding: .35rem 1rem .8rem;
        }

        .dl-card.is-open .dl-body { display: block; }

        .dl-empty-cursos {
            font-size: .8rem;
            color: var(--dl-muted);
            font-style: italic;
            padding: .6rem 0 .1rem;
        }

        /* Grupo FID / PPD dentro de la tarjeta */
        .dl-group-title {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin: .75rem 0 .35rem;
        }

        .dl-group-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--dl-border);
        }

        .dl-group[data-mod="fid"] .dl-group-title { color: var(--dl-fid); }
        .dl-group[data-mod="ppd"] .dl-group-title { color: var(--dl-ppd); }

        /* Fila de curso */
        .dl-course {
            display: grid;
            grid-template-columns: minmax(200px, 1.3fr) minmax(220px, 1.4fr) auto;
            align-items: center;
            gap: .5rem 1.1rem;
            padding: .55rem .7rem;
            border-radius: 9px;
            border-left: 3px solid transparent;
        }

        .dl-course + .dl-course { margin-top: .3rem; }
        .dl-group[data-mod="fid"] .dl-course { border-left-color: var(--dl-fid); background: var(--dl-fid-soft); }
        .dl-group[data-mod="ppd"] .dl-course { border-left-color: var(--dl-ppd); background: var(--dl-ppd-soft); }

        .dl-course-name {
            font-size: .84rem;
            font-weight: 700;
            color: var(--dl-text);
            line-height: 1.3;
        }

        .dl-course-meta {
            font-size: .72rem;
            color: var(--dl-muted);
            margin-top: .1rem;
        }

        .dl-tag {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .66rem;
            font-weight: 700;
            padding: .05rem .45rem;
            border-radius: 999px;
            margin-left: .35rem;
            white-space: nowrap;
        }

        .dl-tag-ok { background: rgba(22, 163, 74, .12); color: var(--dl-fid); }
        .dl-tag-warn { background: rgba(220, 38, 38, .1); color: #dc2626; }
        .dl-tag-muted { background: var(--dl-soft); color: var(--dl-muted); }

        .dark-mode .dl-tag-warn,
        .dim-mode .dl-tag-warn { color: #fca5a5; }

        .dl-bars { display: grid; gap: .28rem; }

        .dl-bar {
            display: grid;
            grid-template-columns: 74px 1fr 38px;
            align-items: center;
            gap: .5rem;
            font-size: .68rem;
            font-weight: 700;
        }

        .dl-bar-track {
            height: 6px;
            border-radius: 999px;
            background: rgba(100, 116, 139, .15);
            overflow: hidden;
        }

        .dl-bar-fill { height: 100%; border-radius: 999px; }
        .dl-bar-val { text-align: right; color: var(--dl-text); }

        .dl-bar.p1 .dl-bar-label { color: #4e73df; }
        .dl-bar.p1 .dl-bar-fill { background: #4e73df; }
        .dl-bar.p2 .dl-bar-label { color: #17a673; }
        .dl-bar.p2 .dl-bar-fill { background: #1cc88a; }
        .dl-bar.p3 .dl-bar-label { color: #2c9faf; }
        .dl-bar.p3 .dl-bar-fill { background: #36b9cc; }
        .dl-bar.ppd .dl-bar-label { color: var(--dl-ppd); }
        .dl-bar.ppd .dl-bar-fill { background: linear-gradient(90deg, #e5973a, #f5b45f); }

        .dark-mode .dl-bar.p1 .dl-bar-label,
        .dim-mode .dl-bar.p1 .dl-bar-label { color: #93a8f4; }
        .dark-mode .dl-bar.p2 .dl-bar-label,
        .dim-mode .dl-bar.p2 .dl-bar-label { color: #6ee7b7; }
        .dark-mode .dl-bar.p3 .dl-bar-label,
        .dim-mode .dl-bar.p3 .dl-bar-label { color: #7dd3e0; }

        .dl-course-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .3rem;
        }

        .dl-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .74rem;
            font-weight: 600;
            padding: .3rem .65rem;
            border-radius: 7px;
            border: 1px solid var(--dl-border);
            background: var(--dl-card);
            color: var(--dl-text);
            white-space: nowrap;
            text-decoration: none !important;
        }

        .dl-btn:hover { border-color: var(--dl-accent); color: var(--dl-accent); }

        .dl-btn-primary {
            background: #0891b2;
            border-color: #0891b2;
            color: #fff;
        }

        .dl-btn-primary:hover { background: #0e7490; border-color: #0e7490; color: #fff; }

        .dl-menu .dropdown-item { font-size: .8rem; }
        .dl-menu .dropdown-item i { width: 16px; }

        .dl-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--dl-muted);
            font-size: .85rem;
        }

        @media (max-width: 991.98px) {
            .dl-course { grid-template-columns: 1fr; }
            .dl-course-actions { justify-content: flex-start; }
        }

        @media (max-width: 767.98px) {
            .dl-head { flex-wrap: wrap; }
            .dl-id { flex-basis: calc(100% - 60px); }
            .dl-stats { order: 3; }
            .dl-actions { order: 4; margin-left: auto; }
            .dl-chevron { order: 2; }
            .dl-toolbar-end { margin-left: 0; width: 100%; justify-content: space-between; }
        }

        /* ── "Exportar por ciclo": select + botón unificados ── */
        .export-ciclo-group {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            overflow: hidden;
        }
        .export-ciclo-group .input-group-text {
            background: #f8f9fc;
            border: none;
            border-right: 1px solid #ced4da;
            color: #6c757d;
        }
        .export-ciclo-group select.form-select {
            border: none;
            box-shadow: none;
            min-width: 150px;
        }
        .export-ciclo-group select.form-select:focus {
            box-shadow: none;
        }
        .export-ciclo-group .btn {
            border: none;
            border-radius: 0;
        }

        /* ── Zona de riesgo: cierre de periodo ── */
        .danger-zone-panel {
            border: 1px solid #f3c6c6;
            border-radius: .5rem;
            background: #fffafa;
            overflow: hidden;
        }
        .dark-mode .danger-zone-panel,
        .dim-mode  .danger-zone-panel {
            border-color: rgba(220, 53, 69, .35);
            background: rgba(220, 53, 69, .06);
        }
        .danger-zone-panel-header {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            padding: .7rem 1rem;
            background: #fdecec;
            border-bottom: 1px solid #f3c6c6;
            font-size: .8rem;
            color: #6c757d;
        }
        .dark-mode .danger-zone-panel-header,
        .dim-mode  .danger-zone-panel-header {
            background: rgba(220, 53, 69, .12);
            border-bottom-color: rgba(220, 53, 69, .3);
        }
        .danger-zone-panel-header i {
            color: #dc3545;
            margin-top: .15rem;
            flex-shrink: 0;
        }
        .danger-zone-panel-header strong {
            display: block;
            color: #842029;
            font-size: .92rem;
        }
        .dark-mode .danger-zone-panel-header strong,
        .dim-mode  .danger-zone-panel-header strong {
            color: #f5b5bb;
        }
        .danger-zone-panel-header a {
            font-weight: 600;
        }
        .danger-zone-panel-body {
            padding: .85rem 1rem;
        }
        .danger-zone-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
        }
        .danger-zone-actions .btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }
        .danger-zone-sep {
            width: 1px;
            align-self: stretch;
            background: #e9ecef;
            margin: 0 .25rem;
        }
        .dark-mode .danger-zone-sep,
        .dim-mode  .danger-zone-sep {
            background: rgba(255, 255, 255, .12);
        }
        @media (max-width: 575.98px) {
            .danger-zone-sep {
                display: none;
            }
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-2 pt-3 pb-1">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-chalkboard-teacher mr-2" style="color:#06b6d4;"></i>Lista de Docentes
                </h4>
                <small class="text-muted">{{ $docentes->count() }} docentes registrados &mdash; gestión de carga horaria e incidencias</small>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="gap:8px">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary shadow-sm dropdown-toggle" type="button"
                        id="exportarDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-file-export fa-sm mr-1"></i> Exportar
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="exportarDropdown"
                        style="min-width: 300px;">
                        <h6 class="dropdown-header">Calificaciones</h6>
                        <a class="dropdown-item" href="{{ route('admin.becas.calificaciones.export') }}">
                            <i class="fa fa-graduation-cap fa-sm mr-2 text-warning"></i> Calificaciones de becarios
                            <small class="d-block text-muted pl-4">Parcial 1, Parcial 2 y Desempeño en vivo</small>
                        </a>
                        @if ($periodoActual)
                            <a class="dropdown-item" href="{{ route('calificaciones.exportar.periodoActualFID') }}">
                                <i class="fa fa-file-archive fa-sm mr-2 text-success"></i> Todos los cursos FID
                                ({{ $periodoActual->nombre }})
                                <small class="d-block text-muted pl-4">.zip con un Excel por curso, formato "Ver
                                    calificaciones"</small>
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header">Un solo ciclo</h6>
                            <form action="{{ route('periodos.export', $periodoActual->id) }}" method="GET"
                                class="px-3 py-1" onclick="event.stopPropagation()">
                                <div class="input-group input-group-sm">
                                    <select name="ciclo_id" class="form-control form-control-sm"
                                        aria-label="Elegir ciclo a exportar">
                                        <option value="">Elegir ciclo…</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-secondary"
                                            title="Exportar Excel del ciclo seleccionado">
                                            <i class="fa fa-file-excel fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <a href="{{ route('silabos.index') }}" class="btn btn-sm btn-outline-primary shadow-sm"
                    title="Ver todos los sílabos registrados">
                    <i class="fa fa-file-alt fa-sm mr-1"></i> Sílabos
                    <span class="badge badge-primary ml-1">{{ $totalSilabos }}</span>
                </a>
                <a href="{{ route('admin.seguimiento') }}" class="btn btn-sm btn-outline-warning shadow-sm"
                    title="Ver incidencias, tutorías y sugerencias">
                    <i class="fa fa-exclamation-triangle fa-sm mr-1"></i> Incidencias
                    <span class="badge badge-warning ml-1">{{ $totalIncidencias }}</span>
                </a>
                <a href="{{ route('registerAdmin') }}" class="btn btn-sm btn-primary shadow-sm"
                    title="Registrar un nuevo docente">
                    <i class="fa fa-plus fa-sm mr-1"></i> Nuevo Docente
                </a>
            </div>
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ Session::get('error') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="col-12 mb-4">
                <div class="danger-zone-panel">                    
                    <div class="danger-zone-panel-body">
                        <div class="danger-zone-actions">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                title="Elimina las notas de Parcial 1 de TODOS los cursos y alumnos"
                                data-form="formPeriodo1"
                                data-mensaje="Esto eliminará de forma permanente las {{ number_format($conteosZonaRiesgo['parcial1']) }} notas de Parcial 1 de TODOS los cursos y alumnos."
                                {{ $conteosZonaRiesgo['parcial1'] === 0 ? 'disabled' : '' }}>
                                <i class="fa fa-trash fa-xs mr-1"></i> Eliminar registro de Periodo 1
                            </button>
                            <form id="formPeriodo1" action="{{ route('periodouno.eliminar') }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                title="Elimina las notas de Parcial 2 de TODOS los cursos y alumnos"
                                data-form="formPeriodo2"
                                data-mensaje="Esto eliminará de forma permanente las {{ number_format($conteosZonaRiesgo['parcial2']) }} notas de Parcial 2 de TODOS los cursos y alumnos."
                                {{ $conteosZonaRiesgo['parcial2'] === 0 ? 'disabled' : '' }}>
                                <i class="fa fa-trash fa-xs mr-1"></i> Eliminar registro de Periodo 2
                            </button>
                            <form id="formPeriodo2" action="{{ route('periododos.eliminar') }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                title="Elimina las notas finales de Desempeño de TODOS los cursos y alumnos"
                                data-form="formPeriodo3"
                                data-mensaje="Esto eliminará de forma permanente las {{ number_format($conteosZonaRiesgo['desempeno']) }} notas de Desempeño Final de TODOS los cursos y alumnos."
                                {{ $conteosZonaRiesgo['desempeno'] === 0 ? 'disabled' : '' }}>
                                <i class="fa fa-trash fa-xs mr-1"></i> Eliminar registro de Desempeño
                            </button>
                            <form id="formPeriodo3" action="{{ route('periodotres.eliminar') }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>

                            <span class="danger-zone-sep"></span>

                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                title="Quita a TODOS los docentes sus cursos FID asignados (no afecta PPD)"
                                data-form="formQuitarAsignacionesFID"
                                data-mensaje="Esto quitará la asignación de las {{ number_format($conteosZonaRiesgo['asignacionesFid']) }} asignaciones de cursos FID a TODOS los docentes. Los cursos PPD no se ven afectados."
                                {{ $conteosZonaRiesgo['asignacionesFid'] === 0 ? 'disabled' : '' }}>
                                <i class="fa fa-unlink fa-xs mr-1"></i> Quitar asignaciones FID
                            </button>
                            <form id="formQuitarAsignacionesFID"
                                action="{{ route('docente.cursos.eliminarTodosGlobal') }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                title="Quita a TODOS los docentes sus cursos PPD asignados (no afecta FID)"
                                data-form="formQuitarAsignacionesPPD"
                                data-mensaje="Esto quitará la asignación de las {{ number_format($conteosZonaRiesgo['asignacionesPpd']) }} asignaciones de cursos PPD a TODOS los docentes. Los cursos FID no se ven afectados."
                                {{ $conteosZonaRiesgo['asignacionesPpd'] === 0 ? 'disabled' : '' }}>
                                <i class="fa fa-unlink fa-xs mr-1"></i> Quitar asignaciones PPD
                            </button>
                            <form id="formQuitarAsignacionesPPD"
                                action="{{ route('docente.cursos.eliminarTodosGlobalPPD') }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    document.querySelectorAll('.btn-eliminar').forEach(btn => {

                        btn.addEventListener('click', function() {

                            let formId = this.dataset.form;
                            let mensaje = this.dataset.mensaje;

                            Swal.fire({
                                title: 'Esta acción no se puede deshacer',
                                html: mensaje +
                                    '<br><br>Escribe <strong>ELIMINAR</strong> para confirmar:',
                                icon: 'warning',
                                input: 'text',
                                inputPlaceholder: 'ELIMINAR',
                                inputAttributes: {
                                    autocapitalize: 'off',
                                    autocorrect: 'off'
                                },
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Sí, eliminar definitivamente',
                                cancelButtonText: 'Cancelar',
                                inputValidator: (value) => {
                                    if (value !== 'ELIMINAR') {
                                        return 'Debes escribir ELIMINAR (en mayúsculas) para confirmar';
                                    }
                                }
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    document.getElementById(formId).submit();
                                }

                            });

                        });

                    });

                });
            </script>

            {{-- <div class="col-lg-12">
                <table class="table table-bordered">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>Periodo 1</th>
                            <th>Periodo 2</th>
                            <th>Desempeño Final</th>
                            <th><i class="fa fa-unlink text-danger"></i> Quitar Asignaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <form action="{{ route('periodouno.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 1?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Periodo 1</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('periododos.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 2?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Periodo 2</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('periodotres.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 3?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Desempeño</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('docente.cursos.eliminarTodosGlobal') }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('⚠️ Esto quitará TODOS los cursos de TODOS los docentes. ¿Estás 100% seguro?')">
                                        Quitar TODOS los cursos asignados ss<i class="fa fa-unlink text-danger"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div> --}}

        </div>
        @php
            // Agrupación FID / PPD y avance por docente, resuelto una sola vez para toda la vista.
            // $avanceCursos llega de DocenteCOntroller@index (FID: [1,2,3]; PPD: float).
            $avanceDe = fn ($curso) => is_array($a = $avanceCursos[$curso->id] ?? 0) ? array_sum($a) / 3 : (float) $a;
            $fichas = $docentes->map(function ($docente) use ($avanceDe) {
                $cursos = $docente->cursos->sortBy('nombre');
                [$ppd, $fid] = $cursos->partition(fn ($c) => $c->esPpd());

                return (object) [
                    'docente' => $docente,
                    'fid' => $fid->values(),
                    'ppd' => $ppd->values(),
                    'avance' => $cursos->isNotEmpty() ? round($cursos->avg($avanceDe)) : null,
                    'esTutor' => (bool) $docente->user?->hasRole('tutor'),
                ];
            });
            $nDocFid = $fichas->filter(fn ($f) => $f->fid->isNotEmpty())->count();
            $nDocPpd = $fichas->filter(fn ($f) => $f->ppd->isNotEmpty())->count();
            $iniciales = fn ($nombre) => collect(preg_split('/\s+/', trim($nombre)))
                ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        @endphp

        <div class="dl" id="dl">
            {{-- ── Barra: pestañas FID/PPD + búsqueda + filtros ── --}}
            <div class="dl-toolbar">
                <div class="dl-tabs" role="tablist" aria-label="Modalidad">
                    <button type="button" class="dl-tab is-active" data-tab="todos" role="tab" aria-selected="true">
                        Todos <span class="dl-tab-count">{{ $fichas->count() }}</span>
                    </button>
                    <button type="button" class="dl-tab" data-tab="fid" role="tab" aria-selected="false">
                        <i class="fas fa-graduation-cap fa-xs"></i> FID <span class="dl-tab-count">{{ $nDocFid }}</span>
                    </button>
                    <button type="button" class="dl-tab" data-tab="ppd" role="tab" aria-selected="false">
                        <i class="fas fa-user-graduate fa-xs"></i> PPD <span class="dl-tab-count">{{ $nDocPpd }}</span>
                    </button>
                </div>

                <div class="dl-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="dlBuscar" placeholder="Buscar docente, DNI, email o curso…" autocomplete="off">
                </div>

                <button type="button" class="dl-chip" data-chip="tutor">
                    <i class="fas fa-user-shield fa-xs mr-1"></i> Tutores
                </button>
                <button type="button" class="dl-chip" data-chip="sincursos">
                    Sin cursos
                </button>

                <div class="dl-toolbar-end">
                    <span class="dl-result"><span id="dlVisibles">{{ $fichas->count() }}</span> docentes</span>
                    <button type="button" class="dl-link-btn" id="dlToggleTodo">Expandir todo</button>
                </div>
            </div>

            {{-- ── Tarjetas ── --}}
            @foreach ($fichas as $f)
                @php
                    $docente = $f->docente;
                    $totalEnviadas = $docente->incidencias->count();
                    $totalRecibidas = $f->esTutor
                        ? $docente->user->tutorCiclos->sum(fn ($c) => $c->incidencias->count())
                        : 0;
                    $textoBusqueda = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [
                        $docente->nombre, $docente->email, $docente->dni,
                        $f->fid->pluck('nombre')->implode(' '), $f->ppd->pluck('nombre')->implode(' '),
                    ])));
                @endphp
                <div class="dl-card" data-fid="{{ $f->fid->count() }}" data-ppd="{{ $f->ppd->count() }}"
                    data-tutor="{{ $f->esTutor ? 1 : 0 }}" data-buscar="{{ $textoBusqueda }}">

                    <div class="dl-head" role="button" tabindex="0" aria-expanded="false">
                        <div class="dl-avatar" aria-hidden="true">{{ $iniciales($docente->nombre) }}</div>

                        <div class="dl-id">
                            <div class="dl-name">
                                {{ $docente->nombre }}
                                @if ($f->esTutor)
                                    @foreach ($docente->user->tutorCiclos as $cicloTutor)
                                        @php
                                            $progTutor = $cicloTutor->programa;
                                            $progAbrev = match (true) {
                                                (bool) $progTutor?->esInicial() => 'INI',
                                                (bool) $progTutor?->eib => 'EIB',
                                                default => $progTutor->nombre ?? '',
                                            };
                                        @endphp
                                        <span class="dl-tutor" title="Tutor de este ciclo">
                                            <i class="fas fa-user-shield fa-xs"></i>
                                            @if ($progAbrev) {{ $progAbrev }} · @endif{{ $cicloTutor->nombre }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="dl-sub">
                                <span>
                                    {{ $docente->email }}
                                    @if ($docente->email)
                                        <button type="button" class="dl-copiar" data-copiar="{{ $docente->email }}" data-que="Correo" title="Copiar correo" aria-label="Copiar correo"><i class="far fa-copy"></i></button>
                                    @endif
                                </span>
                                <span>
                                    DNI {{ $docente->dni }}
                                    @if ($docente->dni)
                                        <button type="button" class="dl-copiar" data-copiar="{{ $docente->dni }}" data-que="DNI" title="Copiar DNI" aria-label="Copiar DNI"><i class="far fa-copy"></i></button>
                                    @endif
                                </span>
                                <span>
                                    <i class="fas fa-exclamation-triangle fa-xs"></i>
                                    @if ($totalEnviadas > 0)
                                        <a href="{{ route('admin.docente.incidencias', $docente->id) }}" onclick="event.stopPropagation()">{{ $totalEnviadas }} incid. enviadas</a>
                                    @else
                                        0 incid. enviadas
                                    @endif
                                </span>
                                @if ($f->esTutor)
                                    <span>
                                        <i class="fas fa-inbox fa-xs"></i>
                                        @if ($totalRecibidas > 0)
                                            <a href="{{ route('admin.docente.incidencias', $docente->id) }}" onclick="event.stopPropagation()">{{ $totalRecibidas }} recibidas</a>
                                        @else
                                            0 recibidas
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="dl-stats">
                            @if ($f->fid->isEmpty() && $f->ppd->isEmpty())
                                <span class="dl-pill dl-pill-none">Sin cursos</span>
                            @else
                                @if ($f->fid->isNotEmpty())
                                    <span class="dl-pill dl-pill-fid" data-pill="fid">FID · {{ $f->fid->count() }}</span>
                                @endif
                                @if ($f->ppd->isNotEmpty())
                                    <span class="dl-pill dl-pill-ppd" data-pill="ppd">PPD · {{ $f->ppd->count() }}</span>
                                @endif
                                <div class="dl-ring" style="--p: {{ $f->avance }};"
                                    title="Avance promedio de calificación de sus cursos: {{ $f->avance }}%">
                                    <span>{{ $f->avance }}%</span>
                                </div>
                            @endif
                        </div>

                        <div class="dl-actions" onclick="event.stopPropagation()">
                            @if ($docente->user)
                                <a href="{{ route('asignar', ['id' => $docente->user->id]) }}" class="dl-icon-btn is-add"
                                    title="Asignar curso"><i class="fas fa-plus"></i></a>
                                <a href="{{ route('adminEdit', ['id' => $docente->user->id]) }}" class="dl-icon-btn"
                                    title="Editar docente"><i class="fas fa-pen"></i></a>
                            @endif
                            <button type="button" class="dl-icon-btn is-danger js-eliminar-docente" title="Eliminar docente"
                                data-url="{{ route('docente.destroy', $docente->id) }}" data-nombre="{{ $docente->nombre }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <i class="fas fa-chevron-down dl-chevron" aria-hidden="true"></i>
                    </div>

                    <div class="dl-body">
                        @if ($f->fid->isEmpty() && $f->ppd->isEmpty())
                            <div class="dl-empty-cursos">
                                No tiene cursos asignados.
                                @if ($docente->user)
                                    <a href="{{ route('asignar', ['id' => $docente->user->id]) }}">Asignar curso</a>
                                @endif
                            </div>
                        @endif

                        @foreach (['fid' => $f->fid, 'ppd' => $f->ppd] as $mod => $cursosMod)
                            @continue($cursosMod->isEmpty())
                            <div class="dl-group" data-mod="{{ $mod }}">
                                <div class="dl-group-title">
                                    <i class="fas {{ $mod === 'fid' ? 'fa-graduation-cap' : 'fa-user-graduate' }}"></i>
                                    {{ strtoupper($mod) }} · {{ $cursosMod->count() }} {{ $cursosMod->count() === 1 ? 'curso' : 'cursos' }}
                                </div>

                                @foreach ($cursosMod as $curso)
                                    @php
                                        $esPPD = $mod === 'ppd';
                                        $competenciasParaUrl = $curso->competenciasSeleccionadas->isNotEmpty()
                                            ? $curso->competenciasSeleccionadas->pluck('id')->all()
                                            : ($curso->competencias->count() <= 3 ? $curso->competencias->pluck('id')->all() : []);
                                        $urlVerCalificaciones = route($esPPD ? 'competencias.calificar.ppd' : 'competencias.calificar', [
                                            'docente' => $docente->id,
                                            'curso' => $curso->id,
                                            'competencias' => $competenciasParaUrl,
                                        ]);
                                        $esExtra = str_contains((string) $curso->cc, 'Extracurricular');
                                        $urlSilabo = $curso->relacionsilabo
                                            ? route('silabos.show', $curso->relacionsilabo->id)
                                            : ($curso->silabo ? asset('docentes/silabo/' . $curso->silabo) : null);
                                        $faltaElegirComp = $curso->competenciasSeleccionadas->isEmpty() && $curso->competencias->count() > 3;
                                        $avance = $avanceCursos[$curso->id] ?? ($esPPD ? 0 : [1 => 0, 2 => 0, 3 => 0]);
                                    @endphp
                                    <div class="dl-course">
                                        <div>
                                            <div class="dl-course-name">{{ $curso->nombre }}</div>
                                            <div class="dl-course-meta">
                                                {{ $curso->ciclo->programa->nombre ?? '' }} · {{ $curso->ciclo->nombre ?? '' }}
                                                @if ($esExtra)
                                                    <span class="dl-tag dl-tag-muted">Extracurricular</span>
                                                @elseif ($urlSilabo)
                                                    <a href="{{ $urlSilabo }}" target="_blank" class="dl-tag dl-tag-ok" title="Ver sílabo">
                                                        <i class="fas fa-file-alt"></i> Sílabo
                                                    </a>
                                                @else
                                                    <span class="dl-tag dl-tag-warn"><i class="fas fa-file-excel"></i> Sin sílabo</span>
                                                @endif
                                                @if ($faltaElegirComp)
                                                    <a href="{{ route('curso.gestionar.competencias', $curso->id) }}" class="dl-tag dl-tag-warn"
                                                        title="El curso tiene más de 3 competencias: hay que elegir cuáles se califican">
                                                        <i class="fas fa-tasks"></i> Elegir competencias
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="dl-bars">
                                            @if ($esPPD)
                                                <div class="dl-bar ppd">
                                                    <span class="dl-bar-label">Calificado</span>
                                                    <div class="dl-bar-track"><div class="dl-bar-fill" style="width: {{ $avance }}%;"></div></div>
                                                    <span class="dl-bar-val">{{ number_format($avance, 0) }}%</span>
                                                </div>
                                            @else
                                                @foreach ([1 => 'Parcial 1', 2 => 'Parcial 2', 3 => 'Desempeño'] as $n => $etiqueta)
                                                    <div class="dl-bar p{{ $n }}">
                                                        <span class="dl-bar-label">{{ $etiqueta }}</span>
                                                        <div class="dl-bar-track"><div class="dl-bar-fill" style="width: {{ $avance[$n] }}%;"></div></div>
                                                        <span class="dl-bar-val">{{ number_format($avance[$n], 0) }}%</span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <div class="dl-course-actions">
                                            <a href="{{ $urlVerCalificaciones }}" class="dl-btn dl-btn-primary">
                                                <i class="fas fa-clipboard-check"></i> Calificaciones
                                            </a>
                                            <div class="dropdown">
                                                <button type="button" class="dl-icon-btn" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false" title="Más acciones"><i class="fas fa-ellipsis-h"></i></button>
                                                <div class="dropdown-menu dropdown-menu-right shadow dl-menu">
                                                    @if ($urlSilabo && ! $esExtra)
                                                        <a class="dropdown-item" href="{{ $urlSilabo }}" target="_blank">
                                                            <i class="fas fa-file-alt text-primary"></i> Ver sílabo
                                                        </a>
                                                    @endif
                                                    @if ($curso->competenciasSeleccionadas->isNotEmpty() || $faltaElegirComp)
                                                        <a class="dropdown-item" href="{{ route('curso.gestionar.competencias', $curso->id) }}">
                                                            <i class="fas fa-tasks text-info"></i>
                                                            {{ $faltaElegirComp ? 'Elegir competencias (máx. 3)' : 'Editar competencias a calificar' }}
                                                        </a>
                                                    @endif
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('docente.curso.eliminar', [$docente->id, $curso->id]) }}" method="POST"
                                                        onsubmit="return confirm('¿Quitar «{{ addslashes($curso->nombre) }}» de {{ addslashes($docente->nombre) }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-unlink"></i> Quitar curso
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="dl-empty" id="dlVacio" style="display:none;">
                <i class="fas fa-search mb-2 d-block" style="font-size:1.4rem;opacity:.35;"></i>
                Ningún docente coincide con los filtros.
            </div>
        </div>

        {{-- Modal ÚNICO de eliminación: recibe el docente desde el botón que lo abre (antes había
             un modal por fila con el mismo id y siempre se abría el del primer docente). --}}
        <div class="modal fade" id="modalEliminarDocente" tabindex="-1" role="dialog" aria-labelledby="modalEliminarDocenteLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarDocenteLabel">Eliminar docente</h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ¿Seguro que deseas eliminar a <strong id="modalEliminarDocenteNombre"></strong>? Esta acción no se puede deshacer.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                        <form id="formEliminarDocente" action="" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cards = Array.prototype.slice.call(document.querySelectorAll('#dl .dl-card'));
            var tabs = document.querySelectorAll('#dl .dl-tab');
            var chips = document.querySelectorAll('#dl .dl-chip');
            var buscar = document.getElementById('dlBuscar');
            var visiblesEl = document.getElementById('dlVisibles');
            var vacioEl = document.getElementById('dlVacio');
            var toggleTodo = document.getElementById('dlToggleTodo');
            var estado = { tab: 'todos', tutor: false, sincursos: false, q: '' };

            try {
                var guardada = localStorage.getItem('docentes.tab');
                if (['todos', 'fid', 'ppd'].indexOf(guardada) !== -1) estado.tab = guardada;
            } catch (e) {}

            function normalizar(s) {
                return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            }

            function abrir(card, abierta) {
                card.classList.toggle('is-open', abierta);
                card.querySelector('.dl-head').setAttribute('aria-expanded', abierta ? 'true' : 'false');
            }

            function aplicar() {
                var terminos = normalizar(estado.q).split(/\s+/).filter(Boolean);
                var visibles = 0;

                cards.forEach(function (card) {
                    var nFid = +card.dataset.fid, nPpd = +card.dataset.ppd;
                    var ok = true;

                    if (estado.tab === 'fid' && nFid === 0) ok = false;
                    if (estado.tab === 'ppd' && nPpd === 0) ok = false;
                    if (estado.tutor && card.dataset.tutor !== '1') ok = false;
                    if (estado.sincursos && nFid + nPpd > 0) ok = false;
                    if (ok && terminos.length) {
                        ok = terminos.every(function (t) { return card.dataset.buscar.indexOf(t) !== -1; });
                    }

                    card.style.display = ok ? '' : 'none';
                    if (ok) visibles++;

                    // En FID/PPD solo se ve el grupo de esa modalidad dentro de la tarjeta.
                    card.querySelectorAll('.dl-group').forEach(function (g) {
                        g.style.display = (estado.tab === 'todos' || g.dataset.mod === estado.tab) ? '' : 'none';
                    });
                    card.querySelectorAll('[data-pill]').forEach(function (p) {
                        p.style.display = (estado.tab === 'todos' || p.dataset.pill === estado.tab) ? '' : 'none';
                    });

                    // Al buscar, se abren las tarjetas que coinciden para ver el curso encontrado.
                    if (terminos.length) abrir(card, ok);
                });

                visiblesEl.textContent = visibles;
                vacioEl.style.display = visibles ? 'none' : '';

                tabs.forEach(function (t) {
                    var activa = t.dataset.tab === estado.tab;
                    t.classList.toggle('is-active', activa);
                    t.setAttribute('aria-selected', activa ? 'true' : 'false');
                });
            }

            tabs.forEach(function (t) {
                t.addEventListener('click', function () {
                    estado.tab = t.dataset.tab;
                    try { localStorage.setItem('docentes.tab', estado.tab); } catch (e) {}
                    aplicar();
                });
            });

            chips.forEach(function (c) {
                c.addEventListener('click', function () {
                    estado[c.dataset.chip] = !estado[c.dataset.chip];
                    c.classList.toggle('is-active', estado[c.dataset.chip]);
                    aplicar();
                });
            });

            var espera;
            buscar.addEventListener('input', function () {
                clearTimeout(espera);
                espera = setTimeout(function () {
                    estado.q = buscar.value;
                    aplicar();
                }, 120);
            });

            cards.forEach(function (card) {
                var head = card.querySelector('.dl-head');
                head.addEventListener('click', function (e) {
                    // Copiar, o terminar de seleccionar texto (correo, DNI): no abre ni cierra.
                    if (e.target.closest('.dl-copiar')) return;
                    if (String(window.getSelection ? window.getSelection() : '').trim() !== '') return;
                    abrir(card, !card.classList.contains('is-open'));
                });
                head.addEventListener('keydown', function (e) {
                    if (e.target === head && (e.key === 'Enter' || e.key === ' ')) {
                        e.preventDefault();
                        abrir(card, !card.classList.contains('is-open'));
                    }
                });
            });

            toggleTodo.addEventListener('click', function () {
                var expandir = toggleTodo.textContent.trim() === 'Expandir todo';
                cards.forEach(function (card) { if (card.style.display !== 'none') abrir(card, expandir); });
                toggleTodo.textContent = expandir ? 'Contraer todo' : 'Expandir todo';
            });

            document.querySelectorAll('.js-eliminar-docente').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('formEliminarDocente').action = btn.dataset.url;
                    document.getElementById('modalEliminarDocenteNombre').textContent = btn.dataset.nombre;
                    $('#modalEliminarDocente').modal('show');
                });
            });

            aplicar();
        });
    </script>
    <script>
        document.addEventListener('click', function (e) {
            var b = e.target.closest('.dl-copiar');
            if (!b) return;
            e.preventDefault();
            e.stopPropagation();
            var texto = b.dataset.copiar, que = b.dataset.que || 'Dato';
            var listo = function () {
                b.classList.add('is-ok');
                b.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(function () { b.classList.remove('is-ok'); b.innerHTML = '<i class="far fa-copy"></i>'; }, 1400);
                if (window.PkAlert) PkAlert.toast(que + ' copiado: ' + texto);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(texto).then(listo);
            } else {
                var t = document.createElement('textarea');
                t.value = texto; t.style.position = 'fixed'; t.style.opacity = '0';
                document.body.appendChild(t); t.select();
                try { document.execCommand('copy'); listo(); } catch (err) {}
                t.remove();
            }
        }, true);
    </script>
@endsection
