@extends('layouts.docente')
@section('titulo', 'Mis cursos')
@section('contenido')
    @php
        $primerNombre = explode(' ', trim($docente->nombre))[0];
        $cursosPPD = $docente->cursos->filter(
            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $otrosCursos = $docente->cursos->filter(
            fn($curso) => !str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $cursosPPDCount = $cursosPPD->count();
        $cursosFidCount = $otrosCursos->count();
    @endphp

    <style>
        /* ── Alertas (SweetAlert2) con el mismo look de las tarjetas del dashboard:
           mismo radio, misma sombra suave y azul institucional (#4e73df). ── */
        .docente-swal-popup {
            border-radius: 0.65rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
            font-family: inherit;
        }

        .docente-swal-popup .swal2-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #4b4f5c;
        }

        .docente-swal-popup .swal2-html-container {
            font-size: 0.9rem;
            color: #5a5c69;
        }

        .docente-swal-popup .swal2-confirm {
            border-radius: 0.4rem !important;
            font-weight: 600;
        }

        .docente-swal-popup .swal2-cancel {
            border-radius: 0.4rem !important;
            font-weight: 600;
        }

        .docente-swal-toast {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
        }

        .docente-swal-toast .swal2-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b4f5c;
        }

        /* ── Hero / cabecera del dashboard docente ──
           El look de la tarjeta (fondo, sombra, acento izquierdo) viene de
           las clases globales .docente-ui-card .docente-ui-hero (definidas
           en layouts/docente.blade.php y reutilizadas por ui-header.blade.php
           en el resto de vistas del docente), así todas comparten un único
           diseño de encabezado. Aquí solo van los extras propios de esta
           página (saludo, badge de periodo, interruptor FID/PPD). ── */
        .docente-dash-hero h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4b4f5c;
            margin-bottom: 0.4rem;
        }

        .docente-dash-hero .badge-periodo {
            display: inline-block;
            background: #eef1fb;
            color: #2e3f8f;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
        }

        .docente-dash-toggle {
            display: inline-flex;
            background: #f4f6fb;
            border-radius: 999px;
            padding: 0.25rem;
            gap: 0.25rem;
        }

        .docente-dash-toggle .btn {
            border: 0;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.42rem 1.05rem;
            color: #5a5c69;
            background: transparent;
            transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .docente-dash-toggle .btn.active {
            background: #4e73df;
            color: #fff;
            box-shadow: 0 3px 8px rgba(78, 115, 223, 0.35);
        }

        .docente-dash-toggle .btn .badge {
            margin-left: 0.35rem;
            background: rgba(90, 92, 105, 0.12);
            color: #5a5c69;
        }

        .docente-dash-toggle .btn.active .badge {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* ── Tarjetas de curso ── */
        .docente-curso-card {
            border: 0;
            border-radius: 0.65rem;
            box-shadow: 0 0.15rem 1.5rem rgba(58, 59, 69, 0.1);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .docente-curso-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.6rem 1.75rem rgba(58, 59, 69, 0.16);
        }

        .docente-curso-index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 50%;
            background: #eef1fb;
            color: #4e73df;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .docente-curso-nombre {
            font-size: 1.05rem;
            font-weight: 700;
            color: #2e3448;
            margin: 0 0 0.15rem;
        }

        .docente-curso-meta {
            font-size: 0.82rem;
            color: #858796;
            margin: 0;
        }

        .docente-curso-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.6rem;
        }

        .docente-curso-stat {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: #5a5c69;
            background: #f4f6fb;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
        }

        .docente-curso-stat i {
            color: #4e73df;
            font-size: 0.72rem;
        }

        .docente-curso-sections {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #edf0f7;
        }

        .docente-curso-section-title {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 700;
            color: #858796;
            margin-bottom: 0.6rem;
        }

        .docente-curso-section-title i {
            color: #4e73df;
        }

        .docente-curso-status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            margin-left: auto;
        }

        .docente-curso-status.is-ok {
            background: rgba(28, 200, 138, 0.14);
            color: #0e9b6a;
        }

        .docente-curso-status.is-pending {
            background: rgba(246, 194, 62, 0.18);
            color: #b3860a;
        }

        .docente-curso-comp-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: #eef1fb;
            border: 1px solid rgba(78, 115, 223, 0.22);
            color: #2e3f8f !important;
            font-size: 0.76rem;
            font-weight: 600;
            text-decoration: none !important;
            margin: 0 0.3rem 0.3rem 0;
            transition: transform .15s ease, background-color .15s ease;
        }

        .docente-curso-comp-chip:hover {
            background: #dde3fa;
            transform: translateY(-1px);
        }

        .docente-curso-comp-chip.is-a-calificar {
            background: #fff6e6;
            border-color: rgba(217, 119, 6, 0.35);
            color: #9a5b00 !important;
        }

        .docente-curso-comp-chip.is-a-calificar:hover {
            background: #ffedc7;
        }

        .docente-comp-chip-star {
            color: #d97706;
            font-size: 0.68rem;
        }

        .docente-curso-empty-comp {
            font-size: 0.8rem;
            color: #a7abba;
            font-style: italic;
        }

        .docente-curso-silabo-actions,
        .docente-curso-classroom-form {
            font-size: 0.85rem;
        }

        .docente-curso-silabo-actions .btn,
        .docente-curso-classroom-form .btn {
            font-size: 0.78rem;
        }

        .docente-curso-silabo-hint {
            font-size: 0.78rem;
            color: #858796;
        }

        .docente-curso-file-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .docente-curso-file-name {
            font-size: 0.72rem;
            color: #858796;
            margin-top: 0.3rem;
            word-break: break-all;
        }

        .docente-classroom-group .input-group-text {
            background-color: #f8f9fc;
            color: #9a9ca8;
            border-right: 0;
        }

        .docente-classroom-input {
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .docente-classroom-input[readonly] {
            background-color: #f8f9fc;
            color: #5a5c69;
            cursor: default;
        }

        .docente-classroom-input:not([readonly]) {
            background-color: #fff;
            border-color: #4e73df;
            box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.12);
        }

        .docente-classroom-actions {
            display: flex;
            align-items: center;
        }

        .docente-classroom-hint {
            font-size: 0.68rem;
            color: #b7b9c8;
            margin-left: 0.6rem;
        }

        .docente-silabo-opcion {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.4rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background: #fbfbfe;
        }

        .docente-silabo-opcion-titulo {
            font-size: 0.82rem;
            font-weight: 700;
            color: #3a3b45;
        }

        .docente-silabo-opcion-desc {
            font-size: 0.72rem;
            color: #858796;
            line-height: 1.4;
        }

        .docente-silabo-opcion-divisor {
            display: flex;
            align-items: center;
            color: #b7b9c8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0.35rem 0;
        }

        .docente-silabo-opcion-divisor::before,
        .docente-silabo-opcion-divisor::after {
            content: '';
            flex: 1;
            border-bottom: 1px dashed #e3e6f0;
        }

        .docente-silabo-opcion-divisor span {
            padding: 0 0.6rem;
        }

        .docente-silabo-pdf-nota {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.68rem;
            color: #b78103;
            background: #fef6e3;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            margin-top: 0.4rem;
        }

        .docente-curso-cabecera {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            column-gap: 1.25rem;
            row-gap: 0.5rem;
            border-radius: 0.65rem;
            padding: 0.5rem 0.6rem;
            margin: -0.5rem -0.6rem 0.4rem -0.6rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .docente-curso-cabecera:hover {
            background: linear-gradient(180deg, rgba(78, 115, 223, 0.05) 0%, rgba(78, 115, 223, 0.015) 100%);
            box-shadow: inset 0 -1px 0 rgba(78, 115, 223, 0.12);
        }

        .docente-curso-cabecera-info {
            min-width: 0;
        }

        .docente-curso-cabecera-progreso {
            justify-self: center;
            min-width: 0;
        }

        .docente-curso-cabecera-accion {
            justify-self: end;
            text-align: right;
            flex-shrink: 0;
        }

        .docente-cabecera-progreso-titulo {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.045em;
            color: #b7b9c8;
            text-align: center;
            margin-bottom: 0.3rem;
        }

        .docente-cabecera-progreso-hint {
            display: inline-flex;
            align-items: center;
            font-size: 0.68rem;
            color: #b78103;
            background: #fef6e3;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .docente-cabecera-progreso-grid {
            display: flex;
            gap: 1.1rem;
        }

        .docente-cabecera-progreso-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
        }

        .docente-cabecera-progreso-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .docente-cabecera-progreso-item .docente-cal-progress {
            width: 4.6rem;
        }

        .docente-cabecera-progreso-pct {
            font-size: 0.68rem;
            font-weight: 700;
            color: #5a5c69;
        }

        @media (max-width: 767.98px) {
            .docente-curso-cabecera {
                grid-template-columns: 1fr;
                justify-items: start;
            }

            .docente-curso-cabecera-progreso,
            .docente-curso-cabecera-accion {
                justify-self: start;
                text-align: left;
                width: 100%;
            }

            .docente-cabecera-progreso-titulo {
                text-align: left;
            }
        }

        .docente-btn-header-accion {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            padding-left: 1rem;
            padding-right: 1rem;
            box-shadow: 0 2px 6px rgba(78, 115, 223, 0.18);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                box-shadow 0.28s ease, filter 0.28s ease;
        }

        .docente-btn-header-accion.btn-outline-primary {
            box-shadow: none;
        }

        .docente-btn-header-accion::after {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 45%;
            height: 100%;
            background: linear-gradient(115deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            transform: skewX(-20deg);
            transition: left 0.6s ease;
            pointer-events: none;
        }

        .docente-btn-header-accion i {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .docente-btn-header-accion:hover {
            transform: translateY(-2px);
            filter: brightness(1.04);
        }

        .docente-btn-header-accion.btn-primary:hover {
            box-shadow: 0 10px 20px -4px rgba(78, 115, 223, 0.35);
        }

        .docente-btn-header-accion.btn-outline-primary:hover {
            box-shadow: 0 6px 14px -4px rgba(78, 115, 223, 0.22);
        }

        .docente-btn-header-accion:hover::after {
            left: 125%;
        }

        .docente-btn-header-accion:hover i {
            transform: rotate(-10deg) scale(1.12);
        }

        .docente-btn-header-accion:active {
            transform: translateY(0);
            filter: brightness(0.98);
        }

        .docente-cal-select-link {
            font-size: 0.7rem;
            color: #858796;
            text-decoration: underline;
        }

        .docente-cal-select-link:hover {
            color: #4e73df;
        }

        .docente-cal-progress {
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.06);
        }

        .docente-cal-progress .progress-bar {
            transition: width 0.9s cubic-bezier(.22, 1, .36, 1);
        }

        .docente-cal-progress-p1 .progress-bar {
            background: linear-gradient(90deg, #4e73df, #6f8ff0);
        }

        .docente-cal-progress-p2 .progress-bar {
            background: linear-gradient(90deg, #1cc88a, #3ddba4);
        }

        .docente-cal-progress-p3 .progress-bar {
            background: linear-gradient(90deg, #36b9cc, #59d4e6);
        }

        .docente-cal-progress-ppd .progress-bar {
            background: linear-gradient(90deg, #e5973a, #f5b45f);
        }


        @media (prefers-reduced-motion: reduce) {
            .docente-cal-progress .progress-bar {
                transition: none;
            }
        }

        .docente-curso-classroom-form .form-control-sm {
            font-size: 0.82rem;
        }

        .docente-cal-empty {
            padding: 3rem 1rem;
            text-align: center;
        }

        .docente-cal-empty i {
            font-size: 2.25rem;
            color: #d1d7e6;
            margin-bottom: .75rem;
            display: block;
        }
    </style>

    <div class="container-fluid docente-ui-page docente-cursos-page px-2 px-md-3 pb-5">
        <div class="card docente-ui-card docente-ui-hero docente-dash-hero mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <p class="docente-ui-kicker mb-1">Área docente</p>
                        <h1 class="mb-2">Hola, {{ $primerNombre }} 👋</h1>
                        <p class="docente-ui-subtitle mb-2">
                            Aquí gestionas tus cursos, sílabos y datos de Classroom. Usa el interruptor para
                            alternar entre FID y PPD.
                        </p>
                        @if (isset($periodoActual) && $periodoActual)
                            <span class="badge-periodo"><i class="far fa-calendar-alt mr-1"></i>Periodo:
                                {{ $periodoActual->nombre }}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 text-center text-lg-right">
                        <div class="docente-dash-toggle" role="group" aria-label="Vista de cursos">
                            <button type="button" class="btn active" id="btn-tab-fid" onclick="mostrarTabla('fid')"
                                aria-pressed="true">
                                <i class="fas fa-graduation-cap mr-1"></i>FID
                                @if ($cursosFidCount > 0)
                                    <span class="badge badge-light">{{ $cursosFidCount }}</span>
                                @endif
                            </button>
                            <button type="button" class="btn" id="btn-tab-ppd" onclick="mostrarTabla('ppd')"
                                aria-pressed="false">
                                <i class="fas fa-chalkboard-teacher mr-1"></i>PPD
                                @if ($cursosPPDCount > 0)
                                    <span class="badge badge-light">{{ $cursosPPDCount }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row pb-5">
            <div class="col-12" id="tablafid">
                @if ($otrosCursos->isEmpty())
                    <div class="card docente-ui-card">
                        <div class="docente-cal-empty">
                            <i class="fas fa-inbox"></i>
                            <p class="mb-1 font-weight-bold text-secondary">No tienes cursos FID asignados</p>
                            <p class="mb-0 small text-muted">Si crees que es un error, comunícate con
                                administración.</p>
                        </div>
                    </div>
                @else
                    @php $contador = 0; @endphp
                    @foreach ($otrosCursos->values() as $index => $curso)
                        @php
                            $contador++;
                            $periodoActualSilabo = \App\Models\PeriodoActual::where('actual', true)->first();
                            $silaboActual = $curso->silabos->firstWhere('periodo_actual_id', $periodoActualSilabo->id ?? null)
                                ?? $curso->silabos->firstWhere('periodo', $periodoActualSilabo->nombre ?? null);
                            $silaboValido = $silaboActual !== null;
                            $silaboPdf = $curso->silabosPdf
                                ->where('periodo_actual_id', $periodoActualSilabo->id ?? null)
                                ->first();
                            $tieneSilabo = $silaboValido || $silaboPdf || $curso->silabo;

                            $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                            $necesitaSeleccionComp = $curso->competencias->count() > 3 && $competenciasSeleccionadas->isEmpty();
                            $competenciasACalificar = $competenciasSeleccionadas->isNotEmpty()
                                ? $competenciasSeleccionadas
                                : ($curso->competencias->count() <= 3 ? $curso->competencias : collect());
                            $idsACalificar = $competenciasACalificar->pluck('id')->all();
                            $urlCalificar = route('competencias.calificar', [
                                'docente' => $docente->id,
                                'curso' => $curso->id,
                                'competencias' => $idsACalificar,
                            ]);
                        @endphp
                        <div class="card docente-ui-card docente-curso-card mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="docente-curso-cabecera">
                                    <div class="d-flex align-items-start docente-curso-cabecera-info" style="gap:0.65rem;">
                                        <span class="docente-curso-index">{{ $contador }}</span>
                                        <div>
                                            <h3 class="docente-curso-nombre">{{ $curso->nombre }}</h3>
                                            <p class="docente-curso-meta">
                                                {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                — {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="docente-curso-cabecera-progreso">
                                        @if ($curso->competencias->isNotEmpty())
                                            <span class="docente-cabecera-progreso-titulo">
                                                <i class="fas fa-chart-line mr-1"></i>Calificaciones de parciales
                                            </span>
                                        @endif
                                        @if ($necesitaSeleccionComp)
                                            <span class="docente-cabecera-progreso-hint">
                                                <i class="fas fa-info-circle mr-1"></i>Elige hasta 3 competencias
                                            </span>
                                        @elseif ($curso->competencias->isNotEmpty())
                                            @php
                                                $p1 = $curso->porcentajePeriodo(1);
                                                $p2 = $curso->porcentajePeriodo(2);
                                                $p3 = $curso->porcentajePeriodo(3);
                                            @endphp
                                            <div class="docente-cabecera-progreso-grid">
                                                <div class="docente-cabecera-progreso-item">
                                                    <span class="docente-cabecera-progreso-label" style="color:#4e73df;">Parcial 1</span>
                                                    <div class="progress docente-cal-progress docente-cal-progress-p1">
                                                        <div class="progress-bar" role="progressbar" style="width:{{ $p1 }}%;"></div>
                                                    </div>
                                                    <span class="docente-cabecera-progreso-pct">{{ number_format($p1, 0) }}%</span>
                                                </div>
                                                <div class="docente-cabecera-progreso-item">
                                                    <span class="docente-cabecera-progreso-label" style="color:#1cc88a;">Parcial 2</span>
                                                    <div class="progress docente-cal-progress docente-cal-progress-p2">
                                                        <div class="progress-bar" role="progressbar" style="width:{{ $p2 }}%;"></div>
                                                    </div>
                                                    <span class="docente-cabecera-progreso-pct">{{ number_format($p2, 0) }}%</span>
                                                </div>
                                                <div class="docente-cabecera-progreso-item">
                                                    <span class="docente-cabecera-progreso-label" style="color:#36b9cc;">Desempeño</span>
                                                    <div class="progress docente-cal-progress docente-cal-progress-p3">
                                                        <div class="progress-bar" role="progressbar" style="width:{{ $p3 }}%;"></div>
                                                    </div>
                                                    <span class="docente-cabecera-progreso-pct">{{ number_format($p3, 0) }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="docente-curso-cabecera-accion">
                                        @if ($curso->competencias->isNotEmpty())
                                            @if ($necesitaSeleccionComp)
                                                <a href="{{ route('curso.gestionar.competencias', $curso->id) }}"
                                                    class="btn btn-outline-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-tasks mr-1"></i> Elegir competencias
                                                </a>
                                            @else
                                                <a href="{{ $urlCalificar }}" class="btn btn-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-pen mr-1"></i> Calificar
                                                </a>
                                                @if ($competenciasSeleccionadas->isNotEmpty())
                                                    <a href="{{ route('curso.gestionar.competencias', $curso->id) }}" class="docente-cal-select-link d-block mt-1">
                                                        Cambiar competencias
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="docente-curso-stats">
                                    <span class="docente-curso-stat"><i class="far fa-clock"></i>
                                        {{ $curso->horas }} horas</span>
                                    <span class="docente-curso-stat"><i class="fas fa-star"></i>
                                        {{ $curso->creditos }} créditos</span>
                                    <span class="docente-curso-stat"><i class="fas fa-tag"></i> {{ $curso->cc }}</span>
                                </div>

                                <div class="row docente-curso-sections">
                                    <div class="col-lg-3 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title"><i class="fas fa-bullseye"></i>
                                            Competencias</h4>
                                        @if ($curso->competencias->isEmpty())
                                            <p class="docente-curso-empty-comp mb-0">Sin competencias asignadas.</p>
                                        @else
                                            @foreach ($curso->competencias as $competencia)
                                                @php $esACalificar = in_array($competencia->id, $idsACalificar); @endphp
                                                <a href="javascript:void(0)" class="docente-curso-comp-chip {{ $esACalificar ? 'is-a-calificar' : '' }}"
                                                    data-nombre="{{ $competencia->nombre }}"
                                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                                    onclick="openModal(this)"
                                                    title="{{ $esACalificar ? 'Esta competencia se calificará este periodo' : 'Toca para ver la descripción completa' }}">
                                                    @if ($esACalificar)
                                                        <i class="fas fa-star docente-comp-chip-star"></i>
                                                    @endif
                                                    {{ $competencia->nombre }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="col-lg-5 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title">
                                            <i class="fas fa-file-pdf"></i> Sílabo
                                            <span
                                                class="docente-curso-status {{ $tieneSilabo ? 'is-ok' : 'is-pending' }}">
                                                <i
                                                    class="fas {{ $tieneSilabo ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                                                {{ $tieneSilabo ? 'Listo' : 'Pendiente' }}
                                            </span>
                                        </h4>
                                        <div class="docente-curso-silabo-actions">
                                            @if (!str_contains($curso->cc, 'Extracurricular'))
                                                {{-- ✅ Caso 1: No hay sílabo ni PDF del periodo actual --}}
                                                @if (!$silaboValido && !$silaboPdf && !$curso->silabo)
                                                    <p class="docente-curso-silabo-hint mb-2">
                                                        No hay sílabo registrado para el periodo
                                                        <strong>{{ $periodoActualSilabo->nombre ?? 'actual' }}</strong>. Elige cómo registrarlo:
                                                    </p>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-laptop-code mr-1"></i>Crear desde el sistema</div>
                                                        <div class="docente-silabo-opcion-desc">Llenas el formulario del sistema paso a paso. Podrás editarlo cuando quieras después.</div>
                                                        <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-plus mr-1"></i>Crear sílabo
                                                        </a>

                                                        @if ($curso->silabos->isNotEmpty())
                                                            <div class="docente-silabo-opcion-divisor"><span>o</span></div>
                                                            <div class="docente-silabo-opcion-desc">
                                                                <i class="fas fa-history mr-1"></i>Reusa el contenido de un sílabo tuyo de un periodo pasado para este mismo curso. Podrás ajustarlo antes de darlo por terminado.
                                                            </div>
                                                            <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                                                                <select class="form-control form-control-sm docente-silabo-reuse-select" id="reuse-select-{{ $curso->id }}" style="width:auto; max-width:220px;">
                                                                    @foreach ($curso->silabos->sortByDesc('id') as $sAnterior)
                                                                        <option value="{{ $sAnterior->id }}">{{ optional($sAnterior->periodoActual)->nombre ?? $sAnterior->periodo }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="reusarSilabo({{ $curso->id }})">
                                                                    <i class="fas fa-copy mr-1"></i> Reusar
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="docente-silabo-opcion-divisor"><span>o</span></div>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-file-upload mr-1"></i>Subir un PDF ya elaborado</div>
                                                        <div class="docente-silabo-opcion-desc">Adjuntas tu propio archivo. <strong>No podrás editarlo</strong> desde el sistema — para corregirlo deberás volver a subir el PDF actualizado.</div>
                                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                                onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                                <i class="fas fa-upload"></i> Elegir PDF
                                                            </button>
                                                            <input type="file" id="file-input-{{ $curso->id }}"
                                                                name="silabo" accept=".pdf" style="display:none;"
                                                                onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm ml-1">Guardar</button>
                                                            <div id="file-name-{{ $curso->id }}"
                                                                class="docente-curso-file-name"></div>
                                                        </form>
                                                    </div>

                                                    {{-- ✅ Caso 2: Existe sílabo (tabla silabos) --}}
                                                @elseif ($silaboValido)
                                                    <a href="{{ route('silabo.pdf', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-danger btn-sm mb-1" title="Descargar el sílabo en PDF">
                                                        <i class="fas fa-download"></i> Descargar PDF
                                                    </a>
                                                    <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-success btn-sm mb-1">
                                                        <i class="fa fa-eye"></i> Ver
                                                    </a>
                                                    <a href="{{ route('silabos.edit', [
                                                        'silabo' => $curso->relacionsilabo->id,
                                                        'curso_id' => $curso->id,
                                                        'docente_id' => $docente->id,
                                                    ]) }}"
                                                        class="btn btn-warning btn-sm mb-1">
                                                        <i class="fa fa-edit"></i> Editar
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm mb-1" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('silabos.destroy', $curso->relacionsilabo->id) }}"
                                                        title="Eliminar sílabo y volver a elegir cómo registrarlo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>

                                                    {{-- ✅ Caso 3: Existe PDF (tabla silabo_pdf) --}}
                                                @elseif ($silaboPdf)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo PDF"
                                                        href="{{ asset('docentes/silabo/' . $silaboPdf->pdf) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>

                                                    {{-- ✅ Caso 4: Campo antiguo (columna silabo en tabla cursos) --}}
                                                @elseif ($curso->silabo)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo"
                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>
                                                @endif
                                            @else
                                                <p class="docente-curso-silabo-hint mb-0">Extracurricular permite
                                                    sólo subir archivo en PDF.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-4 docente-curso-section">
                                        <h4 class="docente-curso-section-title"><i class="fab fa-google"></i>
                                            Classroom</h4>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST" class="docente-curso-classroom-form" id="classroom-form-{{ $curso->id }}">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-link fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->classroom }}"
                                                    placeholder="Enlace de Classroom" readonly>
                                            </div>
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="clave"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom" readonly>
                                            </div>

                                            <div class="docente-classroom-actions" id="classroom-actions-view-{{ $curso->id }}">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="habilitarEdicionClassroom({{ $curso->id }})">
                                                    <i class="fas fa-pen mr-1"></i> Editar
                                                </button>
                                                <span class="docente-classroom-hint">Protegido contra ediciones accidentales</span>
                                            </div>

                                            <div class="docente-classroom-actions d-none justify-content-between" id="classroom-actions-edit-{{ $curso->id }}">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <div>
                                                    <button type="submit" class="btn btn-primary btn-sm" disabled>{{ $buttonText }}</button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        onclick="cancelarEdicionClassroom({{ $curso->id }})">Cancelar</button>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm" disabled
                                                    title="Eliminar"
                                                    onclick="confirmarEliminarClassroom('classroom-form-{{ $curso->id }}')">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="col-12" id="tablappd" style="display: none;">
                @if ($cursosPPD->isEmpty())
                    <div class="card docente-ui-card">
                        <div class="docente-cal-empty">
                            <i class="fas fa-folder-open"></i>
                            <p class="mb-1 font-weight-bold text-secondary">No tienes cursos PPD asignados</p>
                            <p class="mb-0 small text-muted">Los cursos PPD aparecerán aquí cuando estén asignados.
                            </p>
                        </div>
                    </div>
                @else
                    @php $contador = 0; @endphp
                    @foreach ($cursosPPD->values() as $index => $curso)
                        @php
                            $contador++;
                            $periodoActualSilaboPpd = \App\Models\PeriodoActual::where('actual', true)->first();
                            $silaboPdf = $curso->silabosPdf
                                ->where('periodo_actual_id', $periodoActualSilaboPpd->id ?? null)
                                ->first();
                            $tieneSilaboPpd = $curso->relacionsilabo || $silaboPdf || $curso->silabo;

                            $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                            $necesitaSeleccionComp = $curso->competencias->count() > 3 && $competenciasSeleccionadas->isEmpty();
                            $competenciasACalificar = $competenciasSeleccionadas->isNotEmpty()
                                ? $competenciasSeleccionadas
                                : ($curso->competencias->count() <= 3 ? $curso->competencias : collect());
                            $idsACalificar = $competenciasACalificar->pluck('id')->all();
                            $urlCalificarPpd = route('competencias.calificar.ppd', [
                                'docente' => $docente->id,
                                'curso' => $curso->id,
                                'competencias' => $idsACalificar,
                            ]);
                        @endphp
                        <div class="card docente-ui-card docente-curso-card mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="docente-curso-cabecera">
                                    <div class="d-flex align-items-start docente-curso-cabecera-info" style="gap:0.65rem;">
                                        <span class="docente-curso-index">{{ $contador }}</span>
                                        <div>
                                            <h3 class="docente-curso-nombre">{{ $curso->nombre }}</h3>
                                            <p class="docente-curso-meta">
                                                {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                — {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="docente-curso-cabecera-progreso">
                                        @if ($curso->competencias->isNotEmpty())
                                            <span class="docente-cabecera-progreso-titulo">
                                                <i class="fas fa-chart-line mr-1"></i>Calificaciones de parciales
                                            </span>
                                        @endif
                                        @if ($necesitaSeleccionComp)
                                            <span class="docente-cabecera-progreso-hint">
                                                <i class="fas fa-info-circle mr-1"></i>Elige hasta 3 competencias
                                            </span>
                                        @elseif ($curso->competencias->isNotEmpty())
                                            @php $pctPPD = $curso->porcentajePPD(); @endphp
                                            <div class="docente-cabecera-progreso-grid">
                                                <div class="docente-cabecera-progreso-item">
                                                    <span class="docente-cabecera-progreso-label" style="color:#d97706;">Calificado</span>
                                                    <div class="progress docente-cal-progress docente-cal-progress-ppd">
                                                        <div class="progress-bar" role="progressbar" style="width:{{ $pctPPD }}%;"></div>
                                                    </div>
                                                    <span class="docente-cabecera-progreso-pct">{{ number_format($pctPPD, 0) }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="docente-curso-cabecera-accion">
                                        @if ($curso->competencias->isNotEmpty())
                                            @if ($necesitaSeleccionComp)
                                                <a href="{{ route('curso.gestionar.competencias', $curso->id) }}"
                                                    class="btn btn-outline-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-tasks mr-1"></i> Elegir competencias
                                                </a>
                                            @else
                                                <a href="{{ $urlCalificarPpd }}" class="btn btn-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-pen mr-1"></i> Calificar
                                                </a>
                                                @if ($competenciasSeleccionadas->isNotEmpty())
                                                    <a href="{{ route('curso.gestionar.competencias', $curso->id) }}" class="docente-cal-select-link d-block mt-1">
                                                        Cambiar competencias
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="docente-curso-stats">
                                    <span class="docente-curso-stat"><i class="far fa-clock"></i>
                                        {{ $curso->horas }} horas</span>
                                    <span class="docente-curso-stat"><i class="fas fa-star"></i>
                                        {{ $curso->creditos }} créditos</span>
                                    <span class="docente-curso-stat"><i class="fas fa-tag"></i> {{ $curso->cc }}</span>
                                </div>

                                <div class="row docente-curso-sections">
                                    <div class="col-lg-3 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title"><i class="fas fa-bullseye"></i>
                                            Competencias</h4>
                                        @if ($curso->competencias->isEmpty())
                                            <p class="docente-curso-empty-comp mb-0">Sin competencias asignadas.</p>
                                        @else
                                            @foreach ($curso->competencias as $competencia)
                                                @php $esACalificar = in_array($competencia->id, $idsACalificar); @endphp
                                                <a href="javascript:void(0)" class="docente-curso-comp-chip {{ $esACalificar ? 'is-a-calificar' : '' }}"
                                                    data-nombre="{{ $competencia->nombre }}"
                                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                                    onclick="openModal(this)"
                                                    title="{{ $esACalificar ? 'Esta competencia se calificará este periodo' : 'Toca para ver la descripción completa' }}">
                                                    @if ($esACalificar)
                                                        <i class="fas fa-star docente-comp-chip-star"></i>
                                                    @endif
                                                    {{ $competencia->nombre }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="col-lg-5 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title">
                                            <i class="fas fa-file-pdf"></i> Sílabo
                                            <span
                                                class="docente-curso-status {{ $tieneSilaboPpd ? 'is-ok' : 'is-pending' }}">
                                                <i
                                                    class="fas {{ $tieneSilaboPpd ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                                                {{ $tieneSilaboPpd ? 'Listo' : 'Pendiente' }}
                                            </span>
                                        </h4>
                                        <div class="docente-curso-silabo-actions">
                                            @if (!str_contains($curso->cc, 'Extracurricular'))
                                                @if (!$curso->relacionsilabo && !$silaboPdf && !$curso->silabo)
                                                    <p class="docente-curso-silabo-hint mb-2">Elige cómo registrarlo:</p>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-laptop-code mr-1"></i>Crear desde el sistema</div>
                                                        <div class="docente-silabo-opcion-desc">Llenas el formulario del sistema paso a paso. Podrás editarlo cuando quieras después.</div>
                                                        <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-plus mr-1"></i>Crear sílabo
                                                        </a>

                                                        @if ($curso->silabos->isNotEmpty())
                                                            <div class="docente-silabo-opcion-divisor"><span>o</span></div>
                                                            <div class="docente-silabo-opcion-desc">
                                                                <i class="fas fa-history mr-1"></i>Reusa el contenido de un sílabo tuyo de un periodo pasado para este mismo curso. Podrás ajustarlo antes de darlo por terminado.
                                                            </div>
                                                            <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                                                                <select class="form-control form-control-sm docente-silabo-reuse-select" id="reuse-select-{{ $curso->id }}" style="width:auto; max-width:220px;">
                                                                    @foreach ($curso->silabos->sortByDesc('id') as $sAnterior)
                                                                        <option value="{{ $sAnterior->id }}">{{ optional($sAnterior->periodoActual)->nombre ?? $sAnterior->periodo }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="reusarSilabo({{ $curso->id }})">
                                                                    <i class="fas fa-copy mr-1"></i> Reusar
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="docente-silabo-opcion-divisor"><span>o</span></div>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-file-upload mr-1"></i>Subir un PDF ya elaborado</div>
                                                        <div class="docente-silabo-opcion-desc">Adjuntas tu propio archivo. <strong>No podrás editarlo</strong> desde el sistema — para corregirlo deberás volver a subir el PDF actualizado.</div>
                                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                                onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                                <i class="fas fa-upload"></i> Elegir PDF
                                                            </button>
                                                            <input type="file" id="file-input-{{ $curso->id }}"
                                                                name="silabo" accept=".pdf" style="display: none;"
                                                                onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm ml-1">Guardar</button>
                                                            <div id="file-name-{{ $curso->id }}"
                                                                class="docente-curso-file-name"></div>
                                                        </form>
                                                    </div>
                                                @elseif ($curso->relacionsilabo)
                                                    <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-success btn-sm mb-1"><i
                                                            class="fa fa-eye"></i> Ver Sílabo</a>

                                                    <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                        class="btn btn-warning btn-sm mb-1"><i
                                                            class="fa fa-edit"></i> Editar Sílabo</a>
                                                    <a href="#" class="btn btn-danger btn-sm mb-1" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('silabos.destroy', $curso->relacionsilabo->id) }}"
                                                        title="Eliminar sílabo y volver a elegir cómo registrarlo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                @elseif ($silaboPdf)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo PDF"
                                                        href="{{ asset('docentes/silabo/' . $silaboPdf->pdf) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>
                                                @elseif ($curso->silabo)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo"
                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>
                                                @endif
                                            @else
                                                <p class="docente-curso-silabo-hint mb-2">Extracurricular permite
                                                    sólo subir archivo en PDF.</p>
                                                @if ($curso->silabo)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo"
                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                @else
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-upload"></i> Subir PDF
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit"
                                                            class="btn btn-primary btn-sm ml-1">Guardar
                                                            PDF</button>
                                                        <div id="file-name-{{ $curso->id }}"
                                                            class="docente-curso-file-name"></div>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-4 docente-curso-section">
                                        <h4 class="docente-curso-section-title"><i class="fab fa-google"></i>
                                            Classroom</h4>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST" class="docente-curso-classroom-form" id="classroom-form-{{ $curso->id }}">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-link fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->classroom }}"
                                                    placeholder="Enlace de Classroom" readonly>
                                            </div>
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="clave"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom" readonly>
                                            </div>

                                            <div class="docente-classroom-actions" id="classroom-actions-view-{{ $curso->id }}">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="habilitarEdicionClassroom({{ $curso->id }})">
                                                    <i class="fas fa-pen mr-1"></i> Editar
                                                </button>
                                                <span class="docente-classroom-hint">Protegido contra ediciones accidentales</span>
                                            </div>

                                            <div class="docente-classroom-actions d-none justify-content-between" id="classroom-actions-edit-{{ $curso->id }}">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <div>
                                                    <button type="submit" class="btn btn-primary btn-sm" disabled>{{ $buttonText }}</button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        onclick="cancelarEdicionClassroom({{ $curso->id }})">Cancelar</button>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm" disabled
                                                    title="Eliminar"
                                                    onclick="confirmarEliminarClassroom('classroom-form-{{ $curso->id }}')">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Modal de Confirmación -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas eliminar este Sílabo?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" id="confirm-delete" class="btn btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('docentes.partials.calificar-scripts')
    @include('docentes.partials.competencia-modal')
@endsection

@push('scripts')
    <script>
        // ── Alertas del dashboard (SweetAlert2, con el diseño de las tarjetas) ──
        var docenteSwalToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4200,
            timerProgressBar: true,
            customClass: { popup: 'docente-swal-toast' },
            didOpen: function (toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        function docenteConfirmar(opciones) {
            return Swal.fire({
                title: opciones.title,
                text: opciones.text,
                icon: opciones.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: opciones.confirmText || 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: opciones.confirmColor || '#4e73df',
                cancelButtonColor: '#858796',
                reverseButtons: true,
                customClass: { popup: 'docente-swal-popup' },
            });
        }

        @if (session('success'))
            docenteSwalToast.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if (session('error'))
            docenteSwalToast.fire({ icon: 'error', title: @json(session('error')) });
        @endif
        @if ($errors->has('silabo'))
            Swal.fire({
                icon: 'error',
                title: 'Atención, {{ $primerNombre }}',
                text: @json($errors->first('silabo')),
                confirmButtonColor: '#4e73df',
                customClass: { popup: 'docente-swal-popup' },
            });
        @endif

        function updateFileName(input, elementId) {
            var fileNameDisplay = document.getElementById(elementId);
            if (!fileNameDisplay) {
                return;
            }
            if (input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        function reusarSilabo(cursoId) {
            var select = document.getElementById('reuse-select-' + cursoId);
            if (!select || !select.value) return;

            docenteConfirmar({
                title: '¿Reusar este sílabo?',
                text: 'Se copiará como base para el periodo actual. Podrás ajustarlo antes de darlo por terminado.',
                icon: 'question',
                confirmText: 'Sí, reusar',
            }).then(function (resultado) {
                if (!resultado.isConfirmed) return;

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/silabos') }}/' + select.value + '/reuse';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            });
        }

        function confirmarEliminarClassroom(formId) {
            docenteConfirmar({
                title: '¿Eliminar estos campos?',
                text: 'Se borrará el enlace de Classroom y el código de este curso.',
                icon: 'warning',
                confirmText: 'Sí, eliminar',
                confirmColor: '#e74a3b',
            }).then(function (resultado) {
                if (!resultado.isConfirmed) return;

                var form = document.getElementById(formId);
                if (!form) return;

                var campoDelete = document.createElement('input');
                campoDelete.type = 'hidden';
                campoDelete.name = 'delete';
                campoDelete.value = 'true';
                form.appendChild(campoDelete);
                form.submit();
            });
        }

        function habilitarEdicionClassroom(cursoId) {
            var form = document.getElementById('classroom-form-' + cursoId);
            if (!form) return;

            form.querySelectorAll('.docente-classroom-input').forEach(function (input) {
                input.dataset.original = input.value;
                input.readOnly = false;
            });
            form.querySelectorAll('#classroom-actions-edit-' + cursoId + ' button').forEach(function (btn) {
                btn.disabled = false;
            });

            document.getElementById('classroom-actions-view-' + cursoId).classList.add('d-none');
            document.getElementById('classroom-actions-edit-' + cursoId).classList.remove('d-none');

            var primero = form.querySelector('.docente-classroom-input');
            if (primero) {
                primero.focus();
                primero.select();
            }
        }

        function cancelarEdicionClassroom(cursoId) {
            var form = document.getElementById('classroom-form-' + cursoId);
            if (!form) return;

            form.querySelectorAll('.docente-classroom-input').forEach(function (input) {
                if (input.dataset.original !== undefined) {
                    input.value = input.dataset.original;
                }
                input.readOnly = true;
            });
            form.querySelectorAll('#classroom-actions-edit-' + cursoId + ' button').forEach(function (btn) {
                btn.disabled = true;
            });

            document.getElementById('classroom-actions-edit-' + cursoId).classList.add('d-none');
            document.getElementById('classroom-actions-view-' + cursoId).classList.remove('d-none');
        }

        function mostrarTabla(tipo) {
            var tablaPPD = document.getElementById('tablappd');
            var tablaFID = document.getElementById('tablafid');
            var btnFid = document.getElementById('btn-tab-fid');
            var btnPpd = document.getElementById('btn-tab-ppd');
            if (!tablaPPD || !tablaFID || !btnFid || !btnPpd) {
                return;
            }
            if (tipo === 'ppd') {
                if (window.calificarCrossfade) {
                    window.calificarCrossfade(tablaPPD, [tablaFID]);
                } else {
                    tablaPPD.style.display = 'block';
                    tablaFID.style.display = 'none';
                }
                btnPpd.classList.add('active');
                btnFid.classList.remove('active');
                btnPpd.setAttribute('aria-pressed', 'true');
                btnFid.setAttribute('aria-pressed', 'false');
            } else if (tipo === 'fid') {
                if (window.calificarCrossfade) {
                    window.calificarCrossfade(tablaFID, [tablaPPD]);
                } else {
                    tablaFID.style.display = 'block';
                    tablaPPD.style.display = 'none';
                }
                btnFid.classList.add('active');
                btnPpd.classList.remove('active');
                btnFid.setAttribute('aria-pressed', 'true');
                btnPpd.setAttribute('aria-pressed', 'false');
            }
        }

        $(document).ready(function() {
            $('#confirmDeleteModal').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var url = button.data('href');
                $(this).find('#confirm-delete').data('href', url);
            });

            $('#confirm-delete').on('click', function() {
                var url = $(this).data('href');
                if (!url) {
                    return;
                }
                var form = $('<form>', {
                    method: 'POST',
                    action: url
                }).append(
                    $('<input>', {
                        name: '_token',
                        value: $('meta[name="csrf-token"]').attr('content'),
                        type: 'hidden'
                    })
                ).append(
                    $('<input>', {
                        name: '_method',
                        value: 'DELETE',
                        type: 'hidden'
                    })
                );
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush
