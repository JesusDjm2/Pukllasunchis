@extends('layouts.docente')

@section('titulo', 'Calificaciones — Docente')

@section('contenido')
    @php
        $cursosFID = $docente->cursos->reject(
            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $cursosPPD = $docente->cursos->filter(
            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $fechaLimite = \Carbon\Carbon::now()->addSeconds(20);
        $mostrarBoton = \Carbon\Carbon::now()->lt($fechaLimite);
    @endphp

    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Calificaciones',
            'title' => 'Elegir curso',
            'subtitle' => 'Seleccione FID o PPD y el curso que desea calificar.',
            'backUrl' => route('vistaDocente', $docente->id),
            'backLabel' => 'Mis cursos',
        ])

        <div class="card docente-ui-card mb-3">
            <div class="card-body py-3 d-flex flex-column flex-sm-row align-items-sm-center flex-wrap">
                <span class="small text-muted mr-sm-3 mb-2 mb-sm-0">Vista</span>
                <div class="btn-group" role="group" aria-label="Tipo de programa">
                    <button type="button" class="btn btn-success btn-sm" id="btnFID">Cursos FID</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnPPD">Cursos PPD</button>
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
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-12">
                <div id="tablaFID" class="card docente-ui-card">
                    <div class="card-header py-3">
                        <h2 class="h6 mb-0 font-weight-bold text-primary">
                            <i class="fas fa-graduation-cap mr-2 text-success"></i> Cursos FID
                        </h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0 docente-ui-table-wide">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Curso</th>
                                        <th>Competencias</th>
                                        <th>Seleccionadas / acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $contador = 0; @endphp
                                    @foreach ($cursosFID as $curso)
                                        @php $contador++; @endphp
                                        <tr>
                                            <td class="align-middle">{{ $contador }}</td>
                                            <td class="align-middle">
                                                <strong>{{ $curso->nombre }}</strong>
                                                <ul class="small mb-0 pl-3">
                                                    <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }} —
                                                        {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                    <li>Horas: {{ $curso->horas }} · Créditos: {{ $curso->creditos }}</li>
                                                </ul>
                                            </td>
                                            <td class="align-middle">
                                                <form
                                                    action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                                                    <ul class="small mb-2 pl-3">
                                                        @foreach ($curso->competencias as $competencia)
                                                            <li>{{ $competencia->nombre }}</li>
                                                            <input type="hidden" name="competencias[]"
                                                                value="{{ $competencia->id }}">
                                                        @endforeach
                                                    </ul>
                                                    @if ($curso->competencias->count() <= 3 && $mostrarBoton)
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            id="calificar-btn-{{ $curso->id }}">Calificar</button>
                                                    @endif
                                                </form>
                                            </td>
                                            <td class="align-middle">
                                                @if ($curso->competenciasSeleccionadas->isNotEmpty() && $mostrarBoton)
                                                    <form
                                                        action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                        method="POST" id="form-calificar-{{ $curso->id }}">
                                                        @csrf
                                                        <ul class="small mb-2 pl-3">
                                                            @foreach ($curso->competenciasSeleccionadas as $competencia)
                                                                <li>{{ $competencia->nombre }}</li>
                                                                <input type="hidden" name="competencias[]"
                                                                    value="{{ $competencia->id }}">
                                                            @endforeach
                                                        </ul>
                                                        <button type="submit" class="btn btn-primary btn-sm">Calificar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tablaPPD" class="card docente-ui-card mt-3" style="display: none;">
                    <div class="card-header py-3">
                        <h2 class="h6 mb-0 font-weight-bold text-primary">
                            <i class="fas fa-chalkboard-teacher mr-2"></i> Cursos PPD
                        </h2>
                    </div>
                    <div class="card-body p-0">
                        @if ($cursosPPD->isEmpty())
                            <div class="text-center text-muted py-5 px-3">
                                <i class="fas fa-folder-open fa-2x mb-3 text-gray-300"></i>
                                <p class="mb-0 font-weight-bold">No tiene cursos PPD asignados</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm mb-0 docente-ui-table-wide">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>N°</th>
                                            <th>Curso</th>
                                            <th>Competencias</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $contador = 0; @endphp
                                        @foreach ($cursosPPD as $curso)
                                            @php $contador++; @endphp
                                            <tr>
                                                <td class="align-middle">{{ $contador }}</td>
                                                <td class="align-middle">
                                                    <strong>{{ $curso->nombre }}</strong>
                                                    <ul class="small mb-0 pl-3">
                                                        <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }} —
                                                            {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                        <li>Horas: {{ $curso->horas }} · Créditos: {{ $curso->creditos }}</li>
                                                    </ul>
                                                </td>
                                                <td class="align-middle">
                                                    <ul class="small mb-0 pl-3">
                                                        @foreach ($curso->competencias as $competencia)
                                                            <li>{{ $competencia->nombre }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td class="align-middle text-center" style="min-width: 8rem;">
                                                    <form
                                                        action="{{ route('competencias.calificar.ppd', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                                                        <ul class="d-none">
                                                            @foreach ($curso->competencias as $competencia)
                                                                <input type="hidden" name="competencias[]"
                                                                    value="{{ $competencia->id }}">
                                                            @endforeach
                                                        </ul>
                                                        @if ($curso->competencias->count() <= 3)
                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                id="calificar-btn-ppd-{{ $curso->id }}">Calificar</button>
                                                        @endif
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btnPPD = document.getElementById('btnPPD');
            var btnFID = document.getElementById('btnFID');
            var tablaPPD = document.getElementById('tablaPPD');
            var tablaFID = document.getElementById('tablaFID');
            if (!btnPPD || !btnFID || !tablaPPD || !tablaFID) {
                return;
            }
            btnPPD.addEventListener('click', function() {
                tablaPPD.style.display = 'block';
                tablaFID.style.display = 'none';
                btnPPD.classList.remove('btn-outline-primary');
                btnPPD.classList.add('btn-primary');
                btnFID.classList.remove('btn-success');
                btnFID.classList.add('btn-outline-success');
            });
            btnFID.addEventListener('click', function() {
                tablaFID.style.display = 'block';
                tablaPPD.style.display = 'none';
                btnFID.classList.remove('btn-outline-success');
                btnFID.classList.add('btn-success');
                btnPPD.classList.remove('btn-primary');
                btnPPD.classList.add('btn-outline-primary');
            });
        });
    </script>
@endpush
