@extends('layouts.docente')
@section('titulo', 'Mis cursos')
@section('contenido')
    @php
        $primerNombre = explode(' ', trim($docente->nombre))[0];
    @endphp

    @push('styles')
        @include('docentes.partials.dashboard-styles')
    @endpush

    <div class="container-fluid docente-ui-page docente-cursos-page px-2 px-md-3 pb-5">
        @include('docentes.partials.dashboard-hero')
        @include('docentes.partials.dashboard-metricas')

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
                    @foreach ($otrosCursos->values() as $index => $curso)
                        @include('docentes.partials.dashboard-curso-fid', ['curso' => $curso, 'index' => $index])
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
                    @foreach ($cursosPPD->values() as $index => $curso)
                        @include('docentes.partials.dashboard-curso-ppd', ['curso' => $curso, 'index' => $index])
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
    @include('docentes.partials.dashboard-scripts')
@endpush
