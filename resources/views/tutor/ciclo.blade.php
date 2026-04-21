@extends('layouts.docente')

@section('titulo', 'Ciclo ' . $ciclo->nombre . ' — Tutor')

@push('styles')
<style>
    .tutor-tabs .nav-link { font-size: .85rem; font-weight: 600; color: #858796; }
    .tutor-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; }
    .alumno-avatar {
        width: 44px; height: 44px; border-radius: 50%; object-fit: cover;
        border: 2px solid #e3e6f0; cursor: pointer;
        transition: transform .15s, border-color .15s;
    }
    .alumno-avatar:hover { transform: scale(1.1); border-color: #4e73df; }
    .alumno-avatar-placeholder {
        width: 44px; height: 44px; border-radius: 50%;
        background: #e9ecef; display: flex; align-items: center; justify-content: center;
        border: 2px solid #dee2e6; color: #adb5bd; font-size: 1.1rem; flex-shrink: 0;
    }
    .inc-img-thumb {
        width: 64px; height: 64px; object-fit: cover;
        border-radius: .4rem; border: 1px solid #dee2e6; cursor: pointer;
    }
    .inc-reporte { font-size: .85rem; color: #3a3b45; white-space: pre-wrap; }
    .badge-ciclo { font-size: .7rem; font-weight: 700; }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Tutor · ' . (optional($ciclo->programa)->nombre ?? ''),
        'title'     => 'Ciclo ' . $ciclo->nombre,
        'subtitle'  => 'Listado de alumnos e incidencias del ciclo.',
        'backUrl'   => route('tutor.dashboard'),
        'backLabel' => 'Mis ciclos',
    ])

    {{-- Tabs --}}
    <ul class="nav nav-tabs tutor-tabs border-bottom mb-4" id="tutorTabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tabAlumnos">
                <i class="fas fa-users mr-1"></i> Alumnos
                <span class="badge badge-primary ml-1">{{ $alumnos->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabIncidencias">
                <i class="fas fa-clipboard-list mr-1"></i> Incidencias
                <span class="badge badge-secondary ml-1">{{ $incidencias->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content pb-5">

        {{-- ── TAB ALUMNOS ── --}}
        <div class="tab-pane fade show active" id="tabAlumnos">
            @if ($alumnos->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-user-slash fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">No hay alumnos en este ciclo</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8f9fc;">
                                <tr>
                                    <th style="width:60px;" class="border-top-0 pl-4">Foto</th>
                                    <th class="border-top-0">Estudiante</th>
                                    <th class="border-top-0 d-none d-md-table-cell">Contacto</th>
                                    <th class="border-top-0 d-none d-md-table-cell">Condición</th>
                                    <th class="border-top-0 text-center">Incidencias</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    @php
                                        $fotoUrl  = ($alumno->user && $alumno->user->foto)
                                            ? asset('img/estudiantes/'.$alumno->user->foto) : null;
                                        $numInc   = $incidencias->where('alumno_id', $alumno->id)->count();
                                    @endphp
                                    <tr>
                                        <td class="pl-4 align-middle">
                                            @if ($fotoUrl)
                                                <img src="{{ $fotoUrl }}" alt="Foto"
                                                     class="alumno-avatar"
                                                     onclick="openPhotoModal('{{ $fotoUrl }}','{{ $alumno->apellidos }}, {{ $alumno->nombres }}')"
                                                     oncontextmenu="return false;">
                                            @else
                                                <div class="alumno-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold" style="font-size:.9rem;">
                                                {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                            </div>
                                            <div class="text-muted small">DNI: {{ $alumno->dni ?? '—' }}</div>
                                            @if ($alumno->user?->beca == 1)
                                                <span class="badge badge-success badge-ciclo">Beca</span>
                                            @endif
                                        </td>
                                        <td class="align-middle d-none d-md-table-cell">
                                            @if ($alumno->email)
                                                <div class="small"><i class="fas fa-envelope fa-xs mr-1 text-muted"></i>{{ $alumno->email }}</div>
                                            @endif
                                            @if ($alumno->numero)
                                                <div class="small"><i class="fas fa-phone fa-xs mr-1 text-muted"></i>{{ $alumno->numero }}</div>
                                            @endif
                                        </td>
                                        <td class="align-middle d-none d-md-table-cell">
                                            @if ($alumno->condicion)
                                                <span class="badge badge-light border badge-ciclo text-muted">{{ $alumno->condicion }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @if ($numInc > 0)
                                                <span class="badge badge-danger">{{ $numInc }}</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── TAB INCIDENCIAS ── --}}
        <div class="tab-pane fade" id="tabIncidencias">
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
                        @php
                            $aluCiclo    = optional($inc->alumno)->ciclo;
                            $aluPrograma = optional($aluCiclo)->programa;
                        @endphp
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                {{-- Nombre del alumno (sin color alarmante) --}}
                                <span class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                    {{ optional($inc->alumno)->apellidos }}, {{ optional($inc->alumno)->nombres }}
                                </span>

                                {{-- Programa → Ciclo del alumno --}}
                                @if ($aluPrograma || $aluCiclo)
                                    <span class="text-muted" style="font-size:.78rem;">
                                        {{ $aluPrograma?->nombre }}
                                        @if ($aluPrograma && $aluCiclo) · @endif
                                        {{ $aluCiclo ? 'Ciclo '.$aluCiclo->nombre : '' }}
                                    </span>
                                @endif

                                {{-- Fecha --}}
                                <span class="badge badge-light border text-muted ml-auto" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                    {{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}
                                </span>

                                {{-- Docente que reportó --}}
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
</script>
@endpush
