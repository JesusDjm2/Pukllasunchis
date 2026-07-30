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

        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->has('silabo'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Atención, {{ $primerNombre }}:</strong> {{ $errors->first('silabo') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
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
                            $silaboActual = $curso->silabos->firstWhere(
                                'periodo',
                                $periodoActualSilabo->nombre ?? null,
                            );
                            $silaboValido = $silaboActual !== null;
                            $silaboPdf = $curso->silabosPdf
                                ->where('periodo_actual_id', $periodoActualSilabo->id ?? null)
                                ->first();
                            $tieneSilabo = $silaboValido || $silaboPdf || $curso->silabo;
                        @endphp
                        <div class="card docente-ui-card docente-curso-card mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start">
                                    <div class="d-flex align-items-start" style="gap:0.65rem;">
                                        <span class="docente-curso-index">{{ $contador }}</span>
                                        <div>
                                            <h3 class="docente-curso-nombre">{{ $curso->nombre }}</h3>
                                            <p class="docente-curso-meta">
                                                {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                — {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </p>
                                        </div>
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
                                                <a href="javascript:void(0)" class="docente-curso-comp-chip"
                                                    data-nombre="{{ $competencia->nombre }}"
                                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                                    onclick="openModal(this)"
                                                    title="Toca para ver la descripción completa">
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
                                                        <strong>{{ $periodoActualSilabo->nombre ?? 'actual' }}</strong>.
                                                    </p>

                                                    <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                        class="btn btn-primary btn-sm mb-2">
                                                        <i class="fas fa-plus mr-1"></i>Crear sílabo
                                                    </a>
                                                    <div class="mt-1">
                                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                                onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                                <i class="fas fa-upload"></i> Subir PDF
                                                            </button>
                                                            <input type="file" id="file-input-{{ $curso->id }}"
                                                                name="silabo" accept=".pdf" style="display:none;"
                                                                onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm ml-1">Guardar
                                                                PDF</button>
                                                            <div id="file-name-{{ $curso->id }}"
                                                                class="docente-curso-file-name"></div>
                                                        </form>
                                                    </div>

                                                    {{-- ✅ Caso 2: Existe sílabo (tabla silabos) --}}
                                                @elseif ($silaboValido)
                                                    <a href="{{ route('silabo.pdf', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-danger btn-sm mb-1">
                                                        <i class="fas fa-file-pdf"></i> PDF
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
                                            method="POST" class="docente-curso-classroom-form">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Enlace de Classroom">
                                            </div>
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="clave" class="form-control form-control-sm"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom">
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <button type="submit"
                                                    class="btn btn-primary btn-sm">{{ $buttonText }}</button>
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    name="delete" value="true"
                                                    onclick="return confirm('¿Estás seguro de eliminar estos campos?');">Eliminar</button>
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
                            $tieneSilaboPpd = $curso->relacionsilabo || $curso->silabo;
                        @endphp
                        <div class="card docente-ui-card docente-curso-card mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start">
                                    <div class="d-flex align-items-start" style="gap:0.65rem;">
                                        <span class="docente-curso-index">{{ $contador }}</span>
                                        <div>
                                            <h3 class="docente-curso-nombre">{{ $curso->nombre }}</h3>
                                            <p class="docente-curso-meta">
                                                {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                — {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </p>
                                        </div>
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
                                                <a href="javascript:void(0)" class="docente-curso-comp-chip"
                                                    data-nombre="{{ $competencia->nombre }}"
                                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                                    onclick="openModal(this)"
                                                    title="Toca para ver la descripción completa">
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
                                                @if (!$curso->relacionsilabo && !$curso->silabo)
                                                    <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                        class="btn btn-primary btn-sm mb-2">
                                                        <i class="fas fa-plus mr-1"></i>Crear sílabo
                                                    </a>
                                                    <div class="mt-1">
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
                                                    </div>
                                                @elseif ($curso->relacionsilabo)
                                                    <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-success btn-sm mb-1"><i
                                                            class="fa fa-eye"></i> Ver Sílabo</a>

                                                    <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                        class="btn btn-warning btn-sm mb-1"><i
                                                            class="fa fa-edit"></i> Editar Sílabo</a>
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
                                            method="POST" class="docente-curso-classroom-form">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Enlace de Classroom">
                                            </div>
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="clave" class="form-control form-control-sm"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom">
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <button type="submit"
                                                    class="btn btn-primary btn-sm">{{ $buttonText }}</button>
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    name="delete" value="true"
                                                    onclick="return confirm('¿Estás seguro de eliminar estos campos?');">Eliminar</button>
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
                <div class="modal-dialog" role="document">
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
