@extends('layouts.docente')

@section('titulo', 'Incidencia N° ' . $incidencia->id)

@push('styles')
    @include('docentes.incidencias.styles')
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Incidencias',
        'title'     => 'Ficha de incidencia N° ' . $incidencia->id,
        'subtitle'  => $incidencia->alumno
            ? $incidencia->alumno->apellidos . ', ' . $incidencia->alumno->nombres
            : 'Estudiante sin ficha registrada',
        'backUrl'   => route('docente.incidencias.index', $incidencia->docente_id),
        'backLabel' => 'Mis incidencias',
    ])

    <div class="card inc-card">
        <div class="card-body py-4 px-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap mb-3" style="gap:.5rem;">
                <div class="d-flex flex-wrap" style="gap:.35rem;">
                    @if ($incidencia->ciclo)
                        <span class="inc-ciclo-badge">
                            <i class="fas fa-layer-group fa-xs mr-1"></i>Ciclo {{ $incidencia->ciclo->nombre }}
                        </span>
                    @endif
                    @if ($incidencia->programa)
                        <span class="inc-prog-badge">{{ $incidencia->programa->nombre }}</span>
                    @endif
                </div>
                <span class="inc-estado {{ $incidencia->atendido_at ? 'inc-estado--aten' : 'inc-estado--pend' }}">
                    {{ $incidencia->atendido_at ? 'Atendida' : 'Pendiente' }}
                </span>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="inc-dato">
                        <span class="inc-dato-label">Fecha del hecho</span>
                        <span class="inc-dato-valor">{{ optional($incidencia->fecha)->format('d/m/Y') ?? $incidencia->fecha }}</span>
                    </div>
                    <div class="inc-dato">
                        <span class="inc-dato-label">Estudiante</span>
                        <span class="inc-dato-valor">
                            {{ $incidencia->alumno ? $incidencia->alumno->apellidos . ', ' . $incidencia->alumno->nombres : '—' }}
                        </span>
                    </div>
                    <div class="inc-dato">
                        <span class="inc-dato-label">Reportada</span>
                        <span class="inc-dato-valor">{{ $incidencia->created_at->format('d/m/Y h:i A') }}</span>
                    </div>
                    @if ($incidencia->atendidoPor)
                        <div class="inc-dato">
                            <span class="inc-dato-label">Atendida por</span>
                            <span class="inc-dato-valor">
                                {{ $incidencia->atendidoPor->name }}
                                @if ($incidencia->atendido_at)
                                    · {{ $incidencia->atendido_at->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>

                @if ($incidencia->imagen)
                    <div class="col-lg-5 text-center">
                        <img src="{{ asset('img/incidencias/' . $incidencia->imagen) }}"
                             alt="Imagen adjunta"
                             class="inc-foto"
                             onclick="openImgModal('{{ asset('img/incidencias/' . $incidencia->imagen) }}')"
                             oncontextmenu="return false;">
                        <p class="inc-meta mt-2 mb-0">Toca la imagen para verla en grande</p>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <span class="inc-dato-label">Reporte</span>
                <p class="inc-cuerpo mb-0">{{ $incidencia->reporte }}</p>
            </div>

            @if ($incidencia->notas_atencion)
                <div class="mt-4">
                    <span class="inc-dato-label">Notas de atención</span>
                    <p class="inc-cuerpo mb-0">{{ $incidencia->notas_atencion }}</p>
                </div>
            @endif

            <div class="d-flex flex-wrap justify-content-end mt-4 pt-3" style="gap:.4rem;">
                <a href="{{ route('docente.incidencias.edit', [$incidencia->docente_id, $incidencia->id]) }}"
                   class="btn btn-primary btn-sm px-3">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                <form action="{{ route('docente.incidencias.destroy', [$incidencia->docente_id, $incidencia->id]) }}"
                      method="POST"
                      onsubmit="return confirm('¿Eliminar esta incidencia? No se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="fas fa-trash mr-1"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal imagen ampliada --}}
@if ($incidencia->imagen)
    <div id="incImgModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true"
         onclick="closeImgModal()">
        <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
            <button type="button" class="docente-photo-modal-close" onclick="closeImgModal()"
                    aria-label="Cerrar">&times;</button>
            <img id="incImgModalSrc" src="" alt="Imagen de incidencia" oncontextmenu="return false;">
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if ($incidencia->imagen)
<script>
function openImgModal(src) {
    document.getElementById('incImgModalSrc').src = src;
    document.getElementById('incImgModal').classList.add('is-open');
}
function closeImgModal() {
    document.getElementById('incImgModal').classList.remove('is-open');
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeImgModal(); });
</script>
@endif
@endpush
