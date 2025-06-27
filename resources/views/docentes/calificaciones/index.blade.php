@extends('layouts.docente')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Calificar Cursos:</strong></h4>
            <div class="mb-3">
                <button class="btn btn-primary mb-2 btn-sm" id="btnPPD">Mostrar cursos PPD</button>
                <button class="btn btn-success mb-2 btn-sm" id="btnFID">Mostrar cursos FID</button>
            </div>

            <a href="{{ route('vistaDocente', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right">
                Volver
            </a>

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
            </div>
        </div>
        <div class="row pb-5">
            <div class="col-lg-12">
                <!-- Tabla PPD -->
                <div id="tablaPPD" style="display: none;" class="table-responsive">
                    @php
                        $cursosPPD = $docente->cursos->filter(
                            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                        );
                    @endphp
                    <table class="table table-hover table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>Detalles del Curso</th>
                                <th>Competencias</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 0; @endphp
                            @foreach ($cursosPPD as $curso)
                                @php $contador++; @endphp
                                <tr>
                                    <td>{{ $contador }}</td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }}
                                                    - {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                            </ul>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach ($curso->competencias as $competencia)
                                                <li>{{ $competencia->nombre }}</li>
                                                <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td style="width: 18%" class="text-center">
                                        <form
                                            action="{{ route('competencias.calificar.ppd', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                            <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                                            <ul style="display: none">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                    <input type="hidden" name="competencias[]"
                                                        value="{{ $competencia->id }}">
                                                @endforeach
                                            </ul>

                                            @if ($curso->competencias->count() <= 3)
                                                <button type="submit" class="btn btn-primary btn-sm"
                                                    id="calificar-btn-{{ $curso->id }}">
                                                    Calificar
                                                </button>
                                            @endif
                                        </form>                                       
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tabla FID -->
                <div id="tablaFID" style="display: none;" class="table-responsive">
                    @php
                        $cursosFID = $docente->cursos->reject(
                            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                        );
                    @endphp
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Detalles del Curso</th>
                                <th>Competencias</th>
                                <th>Competencias Seleccionadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 0; @endphp
                            @foreach ($cursosFID as $curso)
                                @php $contador++; @endphp
                                <tr>
                                    <td>{{ $contador }}</td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }}
                                                    - {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                            </ul>
                                        </ul>
                                    </td>
                                    <td>
                                        <form
                                            action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                            <input type="hidden" name="curso_id" value="{{ $curso->id }}">

                                            <ul>
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                    <input type="hidden" name="competencias[]"
                                                        value="{{ $competencia->id }}">
                                                @endforeach
                                            </ul>
                                            @if ($curso->competencias->count() <= 3)
                                                <button type="submit" class="btn btn-primary btn-sm float-right"
                                                    id="calificar-btn-{{ $curso->id }}">
                                                    Calificar
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                    <td>
                                        @if ($curso->competenciasSeleccionadas->isNotEmpty())
                                            <form
                                                action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                method="POST">
                                                @csrf
                                                <ul>
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
            {{-- <div class="accordion" id="accordionCursos">
                    @php
                        $cursosPPD = $docente->cursos->filter(
                            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                        );
                    @endphp
                    @if ($cursosPPD->isNotEmpty())
                        <div class="card">
                            <div class="card-header" id="headingPPD">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary font-weight-bold" type="button"
                                        data-toggle="collapse" data-target="#collapsePPD" aria-expanded="true"
                                        aria-controls="collapsePPD">
                                        Cursos Profesionalización Docente (PPD)
                                    </button>
                                </h2>
                            </div>
                            <div id="collapsePPD" class="collapse show" aria-labelledby="headingPPD"
                                data-parent="#accordionCursos">
                                <div class="card-body">
                                    <table class="table table-hover table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>N°</th>
                                                <th>Detalles del Curso</th>
                                                <th>Competencias</th>
                                                <th>Competencias a calificar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $contador = 0; @endphp
                                            @foreach ($cursosPPD as $curso)
                                                @php $contador++; @endphp
                                                <tr>
                                                    <td>{{ $contador }}</td>
                                                    <td>
                                                        <ul>
                                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                                            <ul>
                                                                <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }}
                                                                    - {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                                <li>Horas: {{ $curso->horas }}</li>
                                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                            </ul>
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <form
                                                            action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="docente_id"
                                                                value="{{ $docente->id }}">
                                                            <input type="hidden" name="curso_id"
                                                                value="{{ $curso->id }}">

                                                            <ul>
                                                                @foreach ($curso->competencias as $competencia)
                                                                    <li>{{ $competencia->nombre }}</li>
                                                                    <input type="hidden" name="competencias[]"
                                                                        value="{{ $competencia->id }}">
                                                                @endforeach
                                                            </ul>
                                                            @if ($curso->competencias->count() <= 3)
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm float-right"
                                                                    id="calificar-btn-{{ $curso->id }}">
                                                                    Calificar
                                                                </button>
                                                            @endif
                                                        </form>
                                                    </td>
                                                    <td>
                                                        @if ($curso->competenciasSeleccionadas->isNotEmpty())
                                                            <form
                                                                action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                <ul>
                                                                    @foreach ($curso->competenciasSeleccionadas as $competencia)
                                                                        <li>{{ $competencia->nombre }}</li>
                                                                        <input type="hidden" name="competencias[]"
                                                                            value="{{ $competencia->id }}">
                                                                    @endforeach
                                                                </ul>
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm">Calificar</button>
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
                    @endif
                    <!-- Cursos FID -->
                    @php
                        $cursosFID = $docente->cursos->reject(
                            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                        );
                    @endphp
                    @if ($cursosFID->isNotEmpty())
                        <div class="card">
                            <div class="card-header" id="headingFID">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary font-weight-bold collapsed" type="button"
                                        data-toggle="collapse" data-target="#collapseFID" aria-expanded="false"
                                        aria-controls="collapseFID">
                                        Cursos Formación Regular FID
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseFID" class="collapse" aria-labelledby="headingFID"
                                data-parent="#accordionCursos">
                                <div class="card-body">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Curso</th>
                                                <th>Competencias</th>
                                                <th>Competencias Seleccionadas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $contador = 0; @endphp
                                            @foreach ($cursosFID as $curso)
                                                @php $contador++; @endphp
                                                <tr>
                                                    <td>{{ $contador }}</td>
                                                    <td>
                                                        <ul>
                                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                                            <ul>
                                                                <li>{{ $curso->ciclo->programa->nombre ?? 'Sin programa' }}
                                                                    - {{ $curso->ciclo->nombre ?? 'Sin ciclo' }}</li>
                                                                <li>Horas: {{ $curso->horas }}</li>
                                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                            </ul>
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <form
                                                            action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="docente_id"
                                                                value="{{ $docente->id }}">
                                                            <input type="hidden" name="curso_id"
                                                                value="{{ $curso->id }}">

                                                            <ul>
                                                                @foreach ($curso->competencias as $competencia)
                                                                    <li>{{ $competencia->nombre }}</li>
                                                                    <input type="hidden" name="competencias[]"
                                                                        value="{{ $competencia->id }}">
                                                                @endforeach
                                                            </ul>
                                                            @if ($curso->competencias->count() <= 3)
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm float-right"
                                                                    id="calificar-btn-{{ $curso->id }}">
                                                                    Calificar
                                                                </button>
                                                            @endif
                                                        </form>
                                                    </td>
                                                    <td>
                                                        @if ($curso->competenciasSeleccionadas->isNotEmpty())
                                                            <form
                                                                action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                <ul>
                                                                    @foreach ($curso->competenciasSeleccionadas as $competencia)
                                                                        <li>{{ $competencia->nombre }}</li>
                                                                        <input type="hidden" name="competencias[]"
                                                                            value="{{ $competencia->id }}">
                                                                    @endforeach
                                                                </ul>
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm">Calificar</button>
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
                    @endif
                </div> --}}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnPPD = document.getElementById('btnPPD');
            const btnFID = document.getElementById('btnFID');
            const tablaPPD = document.getElementById('tablaPPD');
            const tablaFID = document.getElementById('tablaFID');

            btnPPD.addEventListener('click', () => {
                tablaPPD.style.display = 'block';
                tablaFID.style.display = 'none';
                btnPPD.classList.add('btn-dark');
                btnFID.classList.remove('btn-dark');
            });

            btnFID.addEventListener('click', () => {
                tablaFID.style.display = 'block';
                tablaPPD.style.display = 'none';
                btnFID.classList.add('btn-dark');
                btnPPD.classList.remove('btn-dark');
            });

            // Mostrar PPD por defecto
            btnPPD.click();
        });
    </script>
@endsection
