@extends('layouts.docente')

@section('titulo', 'Mis Incidencias — Docente')

@push('styles')
    @include('docentes.incidencias.styles')
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
                                <p class="inc-reporte mb-2">{{ $inc->reporte }}</p>

                                {{-- Acciones sobre la incidencia --}}
                                <div class="d-flex flex-wrap justify-content-end" style="gap:.4rem;">
                                    <a href="{{ route('docente.incidencias.show', [$docente->id, $inc->id]) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye fa-xs mr-1"></i>Ver ficha
                                    </a>
                                    <a href="{{ route('docente.incidencias.edit', [$docente->id, $inc->id]) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-pen fa-xs mr-1"></i>Editar
                                    </a>
                                    <form action="{{ route('docente.incidencias.destroy', [$docente->id, $inc->id]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta incidencia? No se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash fa-xs mr-1"></i>Eliminar
                                        </button>
                                    </form>
                                </div>
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
