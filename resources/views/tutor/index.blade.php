@extends('layouts.docente')

@section('titulo', 'Panel de Tutor')

@push('styles')
<style>
    .tutor-tabs .nav-link { font-size: .85rem; font-weight: 600; color: #858796; }
    .tutor-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; }
    .inc-img-thumb {
        width: 64px; height: 64px; object-fit: cover;
        border-radius: .4rem; border: 1px solid #dee2e6; cursor: pointer;
    }
    .inc-reporte { font-size: .85rem; color: #3a3b45; white-space: pre-wrap; }
    .badge-ciclo { font-size: .7rem; font-weight: 700; }
    .inc-highlight {
        animation: incHighlightPulse 2.4s ease-out 1;
        border: 2px solid #4e73df !important;
    }
    @keyframes incHighlightPulse {
        0%   { box-shadow: 0 0 0 4px rgba(78,115,223,.35); }
        100% { box-shadow: 0 0 0 0 rgba(78,115,223,0); }
    }

    .seg-persona { display: flex; align-items: center; gap: .55rem; }
    .seg-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; min-width: 30px; border-radius: 50%;
        background: #eef1fc; color: #4e73df; font-weight: 700; font-size: .66rem;
    }
    .seg-avatar-muted { background: #f4f6f9; color: #b7b9c8; }

    .seg-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .28rem .65rem; border-radius: 20px; font-size: .68rem; font-weight: 700;
        white-space: nowrap;
    }
    .seg-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .seg-pill-ok { background: #e3f9ec; color: #17a673; }
    .seg-pill-pendiente { background: #f4f6f9; color: #858796; }
    .seg-pill-urg-slate { background: #f4f6f9; color: #858796; }
    .seg-pill-urg-cyan { background: #e6f8fb; color: #2596a6; }
    .seg-pill-urg-amber { background: #fef6e3; color: #c69107; }
    .seg-pill-urg-red { background: #fbeaea; color: #e0342a; }
    .seg-pill-urg-green { background: #e3f9ec; color: #17a673; }
    .seg-pill-warning { background: #fef1d9; color: #c07f06; }
    .seg-pill-anon { background: #f4f6f9; color: #858796; }

    .seg-ciclo-pill {
        display: inline-block; padding: .15rem .55rem; border-radius: 6px;
        background: #eef1fc; color: #4e73df; font-size: .68rem; font-weight: 700;
    }

    .seg-btn-estado { border-radius: 20px; font-weight: 600; }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    <div class="card docente-ui-card docente-ui-hero mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                <div class="mb-2 mb-md-0">
                    <p class="docente-ui-kicker mb-1">Panel de Tutor</p>
                    <h1 class="docente-ui-title mb-0">
                        ¡Bienvenido, {{ auth()->user()->name }} {{ auth()->user()->apellidos }}!
                    </h1>
                    <p class="docente-ui-subtitle mb-0 mt-2">Estás ingresando como <strong>Tutor</strong>.</p>
                </div>
                @role('docente')
                    @if(auth()->user()->docente)
                        <div class="flex-shrink-0">
                            @include('docentes.partials.boton-volver', [
                                'url' => route('vistaDocente', ['docente' => auth()->user()->docente->id]),
                                'label' => 'Ir a mi panel de Docente',
                            ])
                        </div>
                    @endif
                @endrole
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs tutor-tabs border-bottom mb-4" id="tutorTabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tabCiclos">
                <i class="fas fa-layer-group mr-1"></i> Ciclos
                <span class="badge badge-primary ml-1">{{ $ciclos->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabIncidencias">
                <i class="fas fa-clipboard-list mr-1"></i> Incidencias
                <span class="badge badge-secondary ml-1">{{ $incidencias->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabTutorias">
                <i class="fas fa-hands-helping mr-1"></i> Tutorías
                <span class="badge badge-danger ml-1">{{ $tutorias->where('estado', 'pendiente')->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabSugerencias">
                <i class="fas fa-comment-dots mr-1"></i> Sugerencias
                <span class="badge badge-info ml-1">{{ $sugerencias->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content pb-5">

        {{-- ── TAB CICLOS ── --}}
        <div class="tab-pane fade show active" id="tabCiclos">
            @if ($ciclos->isEmpty())
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-chalkboard fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">Aún no tienes ciclos asignados</p>
                        <p class="small mb-0">Un administrador debe asignarte los ciclos que supervisarás.</p>
                    </div>
                </div>
            @else
                {{-- Tarjeta por ciclo (sistema compartido .al-ui, public/css/pk-ui.css): cabecera con el
                     estado académico, los dos formularios para los alumnos (QR · copiar · abrir) y el acceso
                     al seguimiento. Los botones conservan .qrp-ver-btn y .tut-copiar-enlace (scripts de abajo). --}}
                @include('alumnos.partials.ui')
                <div class="row al-ui">
                    @foreach ($ciclos as $ciclo)
                        @php
                            $resumen = $resumenCiclos[$ciclo->id] ?? null;
                            $formularios = [
                                [
                                    'titulo' => 'Solicitud de tutoría', 'ayuda' => 'Piden conversar contigo', 'icono' => 'fa-hands-helping', 'tono' => 'is-mal',
                                    'url' => route('tutorias.public.create', ['ciclo' => $ciclo->id]),
                                    'qr' => 'tutor.qr.tutoria', 'qrTitulo' => 'QR Tutoría — Ciclo '.$ciclo->nombre,
                                ],
                                [
                                    'titulo' => 'Buzón de sugerencias', 'ayuda' => 'Envían ideas o inquietudes', 'icono' => 'fa-inbox', 'tono' => 'is-ok',
                                    'url' => route('sugerencias.public.create', ['ciclo' => $ciclo->id]),
                                    'qr' => 'tutor.qr.sugerencia', 'qrTitulo' => 'QR Sugerencias — Ciclo '.$ciclo->nombre,
                                ],
                            ];
                        @endphp
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="al-card h-100 mb-0 d-flex flex-column">
                                <a href="{{ route('tutor.ciclo', $ciclo->id) }}" class="al-card-cab text-decoration-none" title="Abrir el ciclo">
                                    <span class="al-card-cab-icono"><i class="fas fa-users"></i></span>
                                    <div>
                                        <h2>Ciclo {{ $ciclo->nombre }}</h2>
                                        <p class="text-truncate">{{ optional($ciclo->programa)->nombre ?? '—' }}</p>
                                    </div>
                                    <span class="al-card-cab-extra">
                                        @if ($resumen && $resumen['con_notas'])
                                            @if ($resumen['en_riesgo'])
                                                <span class="al-badge is-mal" title="Alumnos desaprobando algún curso en el período"><i class="fas fa-exclamation-triangle"></i> {{ $resumen['en_riesgo'] }}</span>
                                            @else
                                                <span class="al-badge is-ok" title="Nadie desaprueba por ahora"><i class="fas fa-check"></i></span>
                                            @endif
                                        @endif
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </span>
                                </a>

                                @if ($resumen && $resumen['con_notas'])
                                    <div class="al-grupo">
                                        <span>{{ $resumen['en_riesgo'] ? $resumen['en_riesgo'].' desaprobando' : 'Sin desaprobados' }}</span>
                                        @if ($resumen['promedio']) <span class="al-badge is-muted">Promedio {{ $resumen['promedio'] }}</span> @endif
                                    </div>
                                @endif

                                <ul class="al-cursos">
                                    @foreach ($formularios as $f)
                                        <li class="al-curso flex-wrap">
                                            <span class="al-curso-num {{ $f['tono'] }}"><i class="fas {{ $f['icono'] }}"></i></span>
                                            <div class="al-curso-texto">
                                                <div class="al-curso-nombre">{{ $f['titulo'] }}</div>
                                                <div class="al-curso-meta">{{ $f['ayuda'] }}</div>
                                            </div>
                                            <div class="al-curso-accion w-100 pl-5">
                                                <button type="button" class="al-btn al-btn-sm al-btn-borde qrp-ver-btn" title="Ver e imprimir el código QR"
                                                    data-panel-url="{{ route($f['qr'], ['ciclo' => $ciclo->id, 'panel' => 1]) }}"
                                                    data-print-url="{{ route($f['qr'], ['ciclo' => $ciclo->id, 'print' => 1]) }}"
                                                    data-titulo="{{ $f['qrTitulo'] }}" aria-label="Código QR: {{ $f['titulo'] }}">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                                <button type="button" class="al-btn al-btn-sm al-btn-borde tut-copiar-enlace" data-url="{{ $f['url'] }}" title="Copiar el enlace para compartir por WhatsApp o correo">
                                                    <i class="far fa-copy"></i> <span>Copiar</span>
                                                </button>
                                                <a href="{{ $f['url'] }}" target="_blank" rel="noopener" class="al-btn al-btn-sm al-btn-borde" title="Abrir el formulario en otra pestaña" aria-label="Abrir: {{ $f['titulo'] }}">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-auto p-3 d-flex gap-2">
                                    <a href="{{ route('tutor.ciclo', $ciclo->id) }}" class="al-btn al-btn-petroleo flex-fill justify-content-center">
                                        <i class="fas fa-chart-line"></i> Seguimiento
                                    </a>
                                    <a href="{{ route('tutor.asistencias', $ciclo->id) }}" class="al-btn al-btn-borde justify-content-center" title="Ver control de asistencia del ciclo">
                                        <i class="fas fa-clipboard-check"></i> Asistencia
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── TAB INCIDENCIAS ── --}}
        <div class="tab-pane fade" id="tabIncidencias">
            @if ($docente)
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('docente.incidencias.create', $docente->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus mr-1"></i> Nueva incidencia
                    </a>
                </div>
            @endif
            @if ($incidencias->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-check-circle fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">Sin incidencias registradas</p>
                        <p class="small mb-0">Los docentes podrán reportar incidencias desde su panel.</p>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column" style="gap:.85rem;">
                    @foreach ($incidencias as $inc)
                        <div class="card border-0 shadow-sm" id="incidencia-{{ $inc->id }}">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                @if ($inc->alumno)
                                    <div class="seg-persona">
                                        <span class="seg-avatar">{{ mb_strtoupper(mb_substr($inc->alumno->nombres, 0, 1).mb_substr($inc->alumno->apellidos, 0, 1)) }}</span>
                                        <div>
                                            <div class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                                {{ $inc->alumno->apellidos }}, {{ $inc->alumno->nombres }}
                                            </div>
                                            @if ($inc->alumno->ciclo)
                                                <div class="small text-muted">{{ optional($inc->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $inc->alumno->ciclo->nombre }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <span class="badge badge-light border text-muted ml-auto" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                    {{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}
                                </span>

                                <span class="badge badge-light border text-muted" style="font-size:.7rem;">
                                    <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i>
                                    {{ optional($inc->docente)->nombre ?? $inc->nombre_docente ?? '—' }}
                                </span>
                            </div>
                            <div class="card-body py-3 px-3">
                                <p class="inc-reporte mb-0">{{ $inc->reporte }}</p>
                                @if ($inc->imagen)
                                    <div class="mt-2">
                                        <img src="{{ asset('img/incidencias/'.$inc->imagen) }}"
                                             alt="Imagen adjunta"
                                             class="inc-img-thumb"
                                             onclick="openPhotoModal('{{ asset('img/incidencias/'.$inc->imagen) }}', 'Imagen adjunta')"
                                             oncontextmenu="return false;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── TAB TUTORÍAS ── --}}
        <div class="tab-pane fade" id="tabTutorias">
            @if ($tutorias->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-hands-helping fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">Sin solicitudes de tutoría</p>
                        <p class="small mb-0">Los alumnos pueden solicitar una atención personalizada escaneando el QR de tutorías.</p>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column" style="gap:.85rem;">
                    @foreach ($tutorias as $tut)
                        @php
                            $noEsDeEsteCiclo = $tut->alumno && $tut->ciclo && $tut->alumno->ciclo_id !== $tut->ciclo_id;
                        @endphp
                        <div class="card border-0 shadow-sm" id="tutoria-{{ $tut->id }}"
                             style="{{ $noEsDeEsteCiclo ? 'border-left:4px solid #f6c23e;' : ($tut->estado === 'atendida' ? '' : 'border-left:4px solid #e74a3b;') }}">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                @if ($tut->alumno)
                                    <div class="seg-persona">
                                        <span class="seg-avatar">{{ mb_strtoupper(mb_substr($tut->alumno->nombres, 0, 1).mb_substr($tut->alumno->apellidos, 0, 1)) }}</span>
                                        <div>
                                            <div class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                                {{ $tut->alumno->apellidos }}, {{ $tut->alumno->nombres }}
                                            </div>
                                            @if ($tut->alumno->ciclo)
                                                <div class="small text-muted">{{ optional($tut->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $tut->alumno->ciclo->nombre }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($tut->nombre_alumno)
                                    <div class="seg-persona">
                                        <span class="seg-avatar">{{ mb_strtoupper(mb_substr($tut->nombre_alumno, 0, 1)) }}</span>
                                        <div class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                            {{ $tut->nombre_alumno }}
                                        </div>
                                    </div>
                                @endif
                                <span class="seg-pill seg-pill-urg-{{ $tut->urgencia_color }}" title="{{ \App\Models\Tutoria::OPCIONES[$tut->urgencia][0] ?? 'Prioridad' }}"><span class="seg-pill-dot"></span>{{ $tut->urgencia_texto }}</span>
                                @if ($tut->pideOtroDocente() && $tut->docente_solicitado_nombre)
                                    <span class="seg-pill {{ $tut->docente_id == auth()->id() ? 'seg-pill-warning' : 'seg-pill-pendiente' }}" title="Docente con quien desea hablar">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        {{ $tut->docente_id == auth()->id() ? 'Pidió hablar contigo' : 'Pide hablar con '.$tut->docente_solicitado_nombre }}
                                    </span>
                                @endif
                                @if ($tut->ciclo)
                                    <span class="seg-ciclo-pill">Ciclo {{ $tut->ciclo->nombre }}</span>
                                @endif
                                @if ($noEsDeEsteCiclo)
                                    <span class="seg-pill seg-pill-warning" title="El alumno pertenece a otro ciclo">
                                        <i class="fas fa-exclamation-triangle"></i> No es de este ciclo
                                    </span>
                                @endif
                                <span class="badge badge-light border text-muted ml-auto" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                    {{ $tut->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="card-body py-3 px-3">
                                @if ($tut->alumno && ($tut->alumno->email || $tut->alumno->numero))
                                    <div class="text-muted mb-2" style="font-size:.78rem;">
                                        @if ($tut->alumno->email)
                                            <span class="mr-3"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $tut->alumno->email }}</span>
                                        @endif
                                        @if ($tut->alumno->numero)
                                            <span><i class="fas fa-phone fa-xs mr-1"></i>{{ $tut->alumno->numero }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if (filled($tut->motivo))
                                    <p class="inc-reporte mb-3">{{ $tut->motivo }}</p>
                                @else
                                    <p class="inc-reporte text-muted font-italic mb-3">{{ \App\Models\Tutoria::OPCIONES[$tut->urgencia][0] ?? '' }} Sin mensaje adicional.</p>
                                @endif
                                <form action="{{ route('tutor.tutorias.estado', $tut->id) }}" method="POST">
                                    @csrf
                                    @if ($tut->estado === 'atendida')
                                        <span class="seg-pill seg-pill-ok mr-2">
                                            <span class="seg-pill-dot"></span> Atendida
                                            @if ($tut->atendido_at) el {{ $tut->atendido_at->format('d/m/Y') }} @endif
                                        </span>
                                        <button type="submit" class="btn btn-outline-secondary btn-sm seg-btn-estado">Marcar como pendiente</button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-sm seg-btn-estado">
                                            <i class="fas fa-check mr-1"></i> Marcar como atendida
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── TAB SUGERENCIAS ── --}}
        <div class="tab-pane fade" id="tabSugerencias">
            @if ($sugerencias->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-comment-dots fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">Sin sugerencias registradas</p>
                        <p class="small mb-0">Los alumnos pueden dejar sugerencias escaneando el QR del buzón.</p>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column" style="gap:.85rem;">
                    @foreach ($sugerencias as $sug)
                        @php
                            $sugNoEsDeEsteCiclo = $sug->alumno && $sug->ciclo && $sug->alumno->ciclo_id !== $sug->ciclo_id;
                        @endphp
                        <div class="card border-0 shadow-sm" id="sugerencia-{{ $sug->id }}"
                             style="{{ $sugNoEsDeEsteCiclo ? 'border-left:4px solid #f6c23e;' : '' }}">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                @if ($sug->alumno)
                                    <div class="seg-persona">
                                        <span class="seg-avatar">{{ mb_strtoupper(mb_substr($sug->alumno->nombres, 0, 1).mb_substr($sug->alumno->apellidos, 0, 1)) }}</span>
                                        <div>
                                            <div class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                                {{ $sug->alumno->apellidos }}, {{ $sug->alumno->nombres }}
                                            </div>
                                            @if ($sug->alumno->ciclo)
                                                <div class="small text-muted">{{ optional($sug->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $sug->alumno->ciclo->nombre }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($sug->nombre_alumno)
                                    <div class="seg-persona">
                                        <span class="seg-avatar">{{ mb_strtoupper(mb_substr($sug->nombre_alumno, 0, 1)) }}</span>
                                        <div class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                            {{ $sug->nombre_alumno }}
                                        </div>
                                    </div>
                                @endif
                                @if ($sug->ciclo)
                                    <span class="seg-ciclo-pill">Ciclo {{ $sug->ciclo->nombre }}</span>
                                @endif
                                @if ($sugNoEsDeEsteCiclo)
                                    <span class="seg-pill seg-pill-warning" title="El alumno pertenece a otro ciclo">
                                        <i class="fas fa-exclamation-triangle"></i> No es de este ciclo
                                    </span>
                                @endif
                                <span class="badge badge-light border text-muted ml-auto" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                    {{ $sug->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="card-body py-3 px-3">
                                @if ($sug->alumno && ($sug->alumno->email || $sug->alumno->numero))
                                    <div class="text-muted mb-2" style="font-size:.78rem;">
                                        @if ($sug->alumno->email)
                                            <span class="mr-3"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $sug->alumno->email }}</span>
                                        @endif
                                        @if ($sug->alumno->numero)
                                            <span><i class="fas fa-phone fa-xs mr-1"></i>{{ $sug->alumno->numero }}</span>
                                        @endif
                                    </div>
                                @endif
                                <p class="inc-reporte mb-3">{{ $sug->mensaje }}</p>
                                <form action="{{ route('tutor.sugerencias.estado', $sug->id) }}" method="POST">
                                    @csrf
                                    @if ($sug->estado === 'revisada')
                                        <span class="seg-pill seg-pill-ok mr-2">
                                            <span class="seg-pill-dot"></span> Revisada
                                        </span>
                                        <button type="submit" class="btn btn-outline-secondary btn-sm seg-btn-estado">Marcar como pendiente</button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-sm seg-btn-estado">
                                            <i class="fas fa-check mr-1"></i> Marcar como revisada
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal foto --}}
<div id="photoModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true"
     onclick="closePhotoModal()">
    <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
        <button type="button" class="docente-photo-modal-close" onclick="closePhotoModal()"
                aria-label="Cerrar">&times;</button>
        <img id="photoModalImg" src="" alt="" oncontextmenu="return false;">
        <p id="photoModalName" class="text-white text-center mt-2 mb-0 font-weight-bold"
           style="font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.6);"></p>
    </div>
</div>

{{-- Popup del QR: previsualiza, imprime y descarga sin salir de esta vista --}}
<div class="modal fade" id="qrTutorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="qrTutorModalTitulo">Código QR</h6>
                <button type="button" class="close" id="qrTutorModalCerrar" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="qrTutorModalBody" style="min-height: 220px;">
                <div class="text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openPhotoModal(src, name) {
    var el = document.getElementById('photoModal');
    document.getElementById('photoModalImg').src = src;
    document.getElementById('photoModalName').textContent = name || '';
    if (el) el.classList.add('is-open');
}
function closePhotoModal() {
    var el = document.getElementById('photoModal');
    if (el) el.classList.remove('is-open');
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closePhotoModal(); });

(function () {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab');
    var incidenciaId = params.get('incidencia');
    var tutoriaId = params.get('tutoria');
    var sugerenciaId = params.get('sugerencia');

    var tabMap = { ciclos: '#tabCiclos', incidencias: '#tabIncidencias', tutorias: '#tabTutorias', sugerencias: '#tabSugerencias' };
    if (tabMap[tab]) {
        $('#tutorTabs a[href="' + tabMap[tab] + '"]').tab('show');
    }

    function highlight(el) {
        if (!el) return;
        setTimeout(function () {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('inc-highlight');
            setTimeout(function () { el.classList.remove('inc-highlight'); }, 2600);
        }, 300);
    }

    if (incidenciaId) highlight(document.getElementById('incidencia-' + incidenciaId));
    if (tutoriaId) highlight(document.getElementById('tutoria-' + tutoriaId));
    if (sugerenciaId) highlight(document.getElementById('sugerencia-' + sugerenciaId));
})();
</script>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(function () {
    var modalBody   = document.getElementById('qrTutorModalBody');
    var modalTitulo = document.getElementById('qrTutorModalTitulo');

    document.getElementById('qrTutorModalCerrar').addEventListener('click', function () {
        $('#qrTutorModal').modal('hide');
    });

    document.querySelectorAll('.qrp-ver-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalTitulo.textContent = btn.dataset.titulo;
            modalBody.dataset.printUrl = btn.dataset.printUrl;
            modalBody.innerHTML = '<div class="text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
            $('#qrTutorModal').modal('show');

            fetch(btn.dataset.panelUrl)
                .then(function (r) { return r.text(); })
                .then(function (html) { modalBody.innerHTML = html; })
                .catch(function () {
                    modalBody.innerHTML = '<div class="text-danger py-5">No se pudo cargar el código QR.</div>';
                });
        });
    });

    // El botón "Imprimir" del póster, dentro del popup, abre la versión completa
    // en una pestaña nueva (imprimir directamente desde dentro del modal no es confiable).
    document.addEventListener('click', function (e) {
        var imprimirBtn = e.target.closest('[data-qrp-imprimir]');
        if (!imprimirBtn || !modalBody.contains(imprimirBtn)) return;
        if (modalBody.dataset.printUrl) {
            window.open(modalBody.dataset.printUrl, '_blank');
        }
    });

    // Descargar imagen (html2canvas) — funciona igual dentro del popup.
    document.addEventListener('click', function (e) {
        var descargarBtn = e.target.closest('#qrpDescargarBtn');
        if (!descargarBtn || !modalBody.contains(descargarBtn)) return;

        var poster = document.getElementById('qrPoster');
        if (!poster) return;

        var original = descargarBtn.innerHTML;
        descargarBtn.disabled = true;
        descargarBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Generando…';

        html2canvas(poster, { scale: 3, backgroundColor: '#ffffff', useCORS: true })
            .then(function (canvas) {
                var link = document.createElement('a');
                link.download = 'qr-codigo.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            })
            .catch(function () {
                alert('No se pudo generar la imagen. Intenta abrir la versión completa para imprimir.');
            })
            .finally(function () {
                descargarBtn.disabled = false;
                descargarBtn.innerHTML = original;
            });
    });
})();

// Copiar el enlace del formulario (tutoría / sugerencias) al portapapeles
(function () {
    function copiar(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(texto);
        }
        // Respaldo para http (sin HTTPS no existe navigator.clipboard)
        return new Promise(function (resolve, reject) {
            var area = document.createElement('textarea');
            area.value = texto;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            try { document.execCommand('copy') ? resolve() : reject(); } catch (e) { reject(e); }
            document.body.removeChild(area);
        });
    }

    document.querySelectorAll('.tut-copiar-enlace').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var etiqueta = btn.querySelector('span');
            copiar(btn.dataset.url)
                .then(function () { etiqueta.textContent = '¡Copiado!'; btn.classList.add('text-success'); })
                .catch(function () { window.prompt('Copia este enlace:', btn.dataset.url); })
                .finally(function () {
                    setTimeout(function () { etiqueta.textContent = 'Copiar'; btn.classList.remove('text-success'); }, 1800);
                });
        });
    });
})();
</script>
@endpush
