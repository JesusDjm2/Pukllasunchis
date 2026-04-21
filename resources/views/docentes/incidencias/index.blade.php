@extends('layouts.docente')

@section('titulo', 'Mis Incidencias — Docente')

@push('styles')
<style>
    .inc-card {
        border: none;
        border-radius: .75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        transition: box-shadow .15s;
    }
    .inc-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.12); }

    .inc-alumno-name { font-weight: 700; font-size: .93rem; color: #3a3b45; }
    .inc-meta        { font-size: .76rem; color: #858796; }
    .inc-reporte     { font-size: .87rem; color: #444; white-space: pre-wrap; line-height: 1.55; }

    .inc-ciclo-badge {
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        background: #eef2ff; color: #4e73df;
        border: 1px solid #c7d2fe;
        border-radius: 20px; padding: 2px 9px;
    }
    .inc-prog-badge {
        font-size: .68rem; font-weight: 600;
        background: #f0fdf4; color: #16a34a;
        border: 1px solid #bbf7d0;
        border-radius: 20px; padding: 2px 9px;
    }

    .inc-img-thumb {
        width: 72px; height: 72px; object-fit: cover;
        border-radius: .45rem; border: 1px solid #dee2e6;
        cursor: pointer; flex-shrink: 0;
        transition: transform .15s, box-shadow .15s;
    }
    .inc-img-thumb:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,.18); }

    .inc-date-chip {
        font-size: .72rem; font-weight: 700; color: #6c757d;
        background: #f1f3f9; border-radius: 20px;
        padding: 3px 10px; white-space: nowrap;
    }

    .empty-state { padding: 4rem 1rem; }
    .empty-state i { font-size: 3rem; color: #dee2e6; display: block; margin-bottom: 1rem; }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Incidencias',
        'title'     => 'Mis incidencias registradas',
        'subtitle'  => 'Historial de incidencias que has reportado sobre tus estudiantes.',
        'backUrl'   => route('vistaDocente', $docente->id),
        'backLabel' => 'Mi panel',
    ])

    {{-- Botón nueva incidencia --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('docente.incidencias.create', $docente->id) }}"
           class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Nueva incidencia
        </a>
    </div>

    @if ($incidencias->isEmpty())
        <div class="card inc-card">
            <div class="card-body text-center empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p class="font-weight-bold text-muted mb-1">Aún no has registrado incidencias</p>
                <p class="small text-muted mb-3">Usa el formulario para reportar cualquier situación relevante de tus alumnos.</p>
                <a href="{{ route('docente.incidencias.create', $docente->id) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Registrar primera incidencia
                </a>
            </div>
        </div>
    @else
        <div class="d-flex flex-column" style="gap:.85rem;">
            @foreach ($incidencias as $inc)
                @php
                    $alumno  = $inc->alumno;
                    $ciclo   = $inc->ciclo;
                    $programa = optional($ciclo)->programa;
                @endphp
                <div class="card inc-card">
                    <div class="card-body py-3 px-4">
                        <div class="d-flex align-items-start flex-wrap" style="gap:.75rem;">

                            {{-- Miniatura imagen (si existe) --}}
                            @if ($inc->imagen)
                                <img src="{{ asset('img/incidencias/'.$inc->imagen) }}"
                                     alt="Imagen adjunta"
                                     class="inc-img-thumb"
                                     onclick="openImgModal('{{ asset('img/incidencias/'.$inc->imagen) }}')"
                                     oncontextmenu="return false;">
                            @endif

                            {{-- Contenido principal --}}
                            <div class="flex-grow-1" style="min-width:0;">
                                {{-- Cabecera: alumno + badges + fecha --}}
                                <div class="d-flex align-items-center flex-wrap justify-content-between mb-1"
                                     style="gap:.4rem;">
                                    <div>
                                        <span class="inc-alumno-name">
                                            {{ $alumno?->apellidos }}, {{ $alumno?->nombres }}
                                        </span>
                                    </div>
                                    <span class="inc-date-chip">
                                        <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                        {{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}
                                    </span>
                                </div>

                                {{-- Badges ciclo / programa --}}
                                <div class="mb-2 d-flex flex-wrap" style="gap:.35rem;">
                                    @if ($ciclo)
                                        <span class="inc-ciclo-badge">
                                            <i class="fas fa-layer-group fa-xs mr-1"></i>Ciclo {{ $ciclo->nombre }}
                                        </span>
                                    @endif
                                    @if ($programa)
                                        <span class="inc-prog-badge">{{ $programa->nombre }}</span>
                                    @endif
                                </div>

                                {{-- Reporte --}}
                                <p class="inc-reporte mb-0">{{ $inc->reporte }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if ($incidencias->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $incidencias->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Modal imagen ampliada --}}
<div id="incImgModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true"
     onclick="closeImgModal()">
    <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
        <button type="button" class="docente-photo-modal-close" onclick="closeImgModal()"
                aria-label="Cerrar">&times;</button>
        <img id="incImgModalSrc" src="" alt="Imagen de incidencia" oncontextmenu="return false;">
    </div>
</div>
@endsection

@push('scripts')
<script>
function openImgModal(src) {
    document.getElementById('incImgModalSrc').src = src;
    document.getElementById('incImgModal').classList.add('is-open');
}
function closeImgModal() {
    document.getElementById('incImgModal').classList.remove('is-open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeImgModal(); });
</script>
@endpush
