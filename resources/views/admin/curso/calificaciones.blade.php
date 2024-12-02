@extends('layouts.admin')
@section('contenido')
    <style>
        th.sortable {
            pointer-events: none;
        }

        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #343a40;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #000000;
            font-weight: bold;
            right: 1em !important;
            position: absolute;
            font-weight: bold
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .nav-pills .nav-link {
            background-color: white;
            color: #0d6efd;
            border: #00000030 1px solid;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
    <div class="container-fluid bg-light">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">{{ $curso->nombre }}
                <small class="text-secondary">({{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }}) <br>
                    <span class="text-primary">Docente: {{ $docente->nombre }} </span></small>
            </h4>
            <form action="{{ route('calificaciones.exportar', ['docenteId' => $docente->id, 'cursoId' => $curso->id]) }}"
                method="GET">
                @csrf
                @foreach ($competenciasSeleccionadas as $competencia)
                    <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                @endforeach
                <div class="text-center"><button type="submit" class="btn btn-primary btn-sm mb-2">Exportar CSV <i
                            class="fa fa-file-csv"></i></button></div>
            </form>
            <a href="{{ route('docente.index') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right mb-3">
                Volver
            </a>
        </div>
        <div class="col-lg-12 text-center mb-2">
            @foreach ($competenciasSeleccionadas as $competencia)
                <a class="text-center align-middle" style="font-size: 16px; cursor: pointer;"
                    data-id="{{ $competencia->id }}" data-nombre="{{ $competencia->nombre }}"
                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                    data-capacidades="{!! addslashes($competencia->capacidades) !!}" onclick="openModal(this)">
                    {{ $competencia->nombre }}
                </a>
                @if (!$loop->last)
                    |
                @endif
            @endforeach
        </div>
        {{-- <div class="col-lg-12 text-center mt-3 mb-3">
            <div class="col-lg-12 text-center mt-3">
                <button type="button" class="btn btn-sm btn-primary mx-1" onclick="mostrarTabla('tablaCalificaciones')">Ver
                    calificaciones</button>
                <button type="button" class="btn btn-sm btn-info mx-1" onclick="mostrarTabla('tablaPeriodo1')">Periodo
                    1</button>
                <button type="button" class="btn btn-sm btn-info mx-1" onclick="mostrarTabla('tablaPeriodo2')">Periodo
                    2</button>
                <button type="button" class="btn btn-sm btn-success mx-1"
                    onclick="mostrarTabla('tablaDesempeno')">Desempeño</button>
            </div>
        </div> --}}
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-pills nav-fill" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active" role="tab"
                            aria-controls="active" aria-selected="true">Calificaciones</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="longer-tab" data-bs-toggle="tab" href="#longer" role="tab"
                            aria-controls="longer" aria-selected="false">Periodo 1</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="link-tab" data-bs-toggle="tab" href="#link" role="tab"
                            aria-controls="link" aria-selected="false">Periodo 2</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="disabled-tab" data-bs-toggle="tab" href="#desempeno" role="tab"
                            aria-controls="disabled" aria-selected="false" tabindex="-1" aria-disabled="true">Desempeño</a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                        <div style="max-height: 700px; overflow-y: auto;" id="tablaPeriodo1">
                            <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                                <thead class="thead-dark">
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle sortable">#</th>
                                        <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                        @foreach ($competenciasSeleccionadas as $competencia)
                                            <th rowspan="2" class="text-center align-middle sortable"
                                                style="font-size: 14px">
                                                {{ $competencia->nombre }}                                                
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="align-middle sortable">Valoración del Curso</th>
                                        <th class="align-middle sortable">Calificación del Curso</th>
                                        <th class="align-middle sortable">Calificación para el Sistema</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($alumnos as $index => $alumno)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td style="text-align: left">{{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                            </td>

                                            @php
                                                $calificacion = $alumno
                                                    ->calificaciones()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();
                                            @endphp
                                            @foreach ($competenciasSeleccionadas as $index => $competencia)
                                                <td>
                                                    @php
                                                        $valoracion = $calificacion
                                                            ? $calificacion->{'valoracion_' . ($index + 1)}
                                                            : null;
                                                        $valoracionTexto = '';

                                                        switch ($valoracion) {
                                                            case 5:
                                                                $valoracionTexto = 'Destacado';
                                                                break;
                                                            case 4:
                                                                $valoracionTexto = 'Logrado';
                                                                break;
                                                            case 3:
                                                                $valoracionTexto = 'En Proceso';
                                                                break;
                                                            case 2:
                                                                $valoracionTexto = 'Inicio';
                                                                break;
                                                            case 1:
                                                                $valoracionTexto = 'Previo al Inicio';
                                                                break;
                                                            default:
                                                                $valoracionTexto = '-';
                                                                break;
                                                        }
                                                    @endphp
                                                    {{ $valoracionTexto }}
                                                    <input type="text"
                                                        class="form-control form-control-sm input-competencia"
                                                        value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                        style="display: none">
                                                </td>
                                            @endforeach
                                            <td>
                                                {{ $calificacion && $calificacion->valoracion_curso ? $calificacion->valoracion_curso : '-' }}
                                            </td>
                                            <td>
                                                {{ $calificacion && $calificacion->calificacion_curso ? $calificacion->calificacion_curso : '-' }}
                                            </td>
                                            <td>
                                                {{ $calificacion && $calificacion->calificacion_sistema ? $calificacion->calificacion_sistema : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="longer" role="tabpanel" aria-labelledby="longer-tab">
                        <table class="table table-hover table-bordered text-center" id="tablaCalificaciones"
                            style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Apellidos, Nombres</th>
                                    @foreach ($competenciasSeleccionadas as $index => $competencia)
                                        <th>{{ $competencia->nombre }}</th>
                                    @endforeach
                                    <th>Valoración Curso</th>
                                    <th>Calificación Curso</th>
                                    <th>Calificación Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($alumnos->some(fn($alumno) => $alumno->periodos->isNotEmpty()))
                                    @php $alumnoIndex = 1; @endphp
                                    @foreach ($alumnos as $index => $alumno)
                                        @php
                                            $periodo = $alumno->periodos->firstWhere('curso_id', $curso->id);
                                        @endphp
                                        @if ($periodo)
                                            <tr>
                                                <td>{{ $alumnoIndex++ }}</td>
                                                <td style="text-align: left">{{ $alumno->apellidos }},
                                                    {{ $alumno->nombres }}
                                                </td>
                                                @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                                    <td>
                                                        @php
                                                            $valoracionKey = 'valoracion_' . ($compIndex + 1);
                                                            $valoracion = $periodo->$valoracionKey;
                                                        @endphp
                                                        @switch($valoracion)
                                                            @case(1)
                                                                Previo al Inicio
                                                            @break

                                                            @case(2)
                                                                En Proceso
                                                            @break

                                                            @case(3)
                                                                Procesando
                                                            @break

                                                            @case(4)
                                                                Logrado
                                                            @break

                                                            @case(5)
                                                                Destacado
                                                            @break

                                                            @default
                                                                Sin Valoración
                                                        @endswitch
                                                    </td>
                                                @endforeach
                                                <td>{{ $periodo->valoracion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_sistema ?? '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <p>No hay periodos publicados.</p>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="link" role="tabpanel" aria-labelledby="link-tab">
                        <table class="table table-hover table-bordered text-center" id="tablaPeriodo2"
                            style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Apellidos, Nombres</th>
                                    @foreach ($competenciasSeleccionadas as $index => $competencia)
                                        <th>{{ $competencia->nombre }}</th>
                                    @endforeach
                                    <th>Valoración Curso</th>
                                    <th>Calificación Curso</th>
                                    <th>Calificación Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($alumnos->some(fn($alumno) => $alumno->periodoDos->isNotEmpty()))
                                    @php $alumnoIndex = 1; @endphp
                                    @foreach ($alumnos as $index => $alumno)
                                        @php
                                            $periodo = $alumno->periodos->firstWhere('curso_id', $curso->id);
                                        @endphp
                                        @if ($periodo)
                                            <tr>
                                                <td>{{ $alumnoIndex++ }}</td>
                                                <td style="text-align: left">{{ $alumno->apellidos }},
                                                    {{ $alumno->nombres }}
                                                </td>
                                                @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                                    <td>
                                                        @php
                                                            $valoracionKey = 'valoracion_' . ($compIndex + 1);
                                                            $valoracion = $periodo->$valoracionKey;
                                                        @endphp
                                                        @switch($valoracion)
                                                            @case(1)
                                                                Previo al Inicio
                                                            @break

                                                            @case(2)
                                                                En Proceso
                                                            @break

                                                            @case(3)
                                                                Procesando
                                                            @break

                                                            @case(4)
                                                                Logrado
                                                            @break

                                                            @case(5)
                                                                Destacado
                                                            @break

                                                            @default
                                                                Sin Valoración
                                                        @endswitch
                                                    </td>
                                                @endforeach
                                                <td>{{ $periodo->valoracion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_sistema ?? '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <p>No hay periodos publicados.</p>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="desempeno" role="tabpanel" aria-labelledby="desempeno-tab">
                        <table class="table table-hover table-bordered text-center" id="tablaPeriodo3"
                            style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Apellidos, Nombres</th>
                                    @foreach ($competenciasSeleccionadas as $index => $competencia)
                                        <th>{{ $competencia->nombre }}</th>
                                    @endforeach
                                    <th>Valoración Curso</th>
                                    <th>Calificación Curso</th>
                                    <th>Calificación Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($alumnos->some(fn($alumno) => $alumno->periodotres->isNotEmpty()))
                                    @php $alumnoIndex = 1; @endphp
                                    @foreach ($alumnos as $index => $alumno)
                                        @php
                                            $periodo = $alumno->periodos->firstWhere('curso_id', $curso->id);
                                        @endphp
                                        @if ($periodo)
                                            <tr>
                                                <td>{{ $alumnoIndex++ }}</td>
                                                <td style="text-align: left">{{ $alumno->apellidos }},
                                                    {{ $alumno->nombres }}
                                                </td>
                                                @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                                    <td>
                                                        @php
                                                            $valoracionKey = 'valoracion_' . ($compIndex + 1);
                                                            $valoracion = $periodo->$valoracionKey;
                                                        @endphp
                                                        @switch($valoracion)
                                                            @case(1)
                                                                Previo al Inicio
                                                            @break

                                                            @case(2)
                                                                En Proceso
                                                            @break

                                                            @case(3)
                                                                Procesando
                                                            @break

                                                            @case(4)
                                                                Logrado
                                                            @break

                                                            @case(5)
                                                                Destacado
                                                            @break

                                                            @default
                                                                Sin Valoración
                                                        @endswitch
                                                    </td>
                                                @endforeach
                                                <td>{{ $periodo->valoracion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_curso ?? '-' }}</td>
                                                <td>{{ $periodo->calificacion_sistema ?? '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <p>No hay periodos asociados a los alumnos.</p>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>       
    </div>
    <div id="competenciaModal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation();">
            <span class="close" onclick="closeModal(event)">&times;</span>
            <h4 id="competenciaNombre" class="font-weight-bold"></h4>
            <p id="competenciaDescripcion" class="text-justify"></p>
            <h5>Capacidades:</h5>
            <p id="competenciaCapacidades" class="text-justify"></p>
        </div>
    </div>
    {{-- Modal --}}
    <script>
        function openModal(element) {
            // Extraer datos del elemento a partir de los atributos "data-"
            var nombre = element.getAttribute('data-nombre');
            var descripcion = element.getAttribute('data-descripcion');
            var capacidades = element.getAttribute('data-capacidades');

            // Asigna los valores al modal
            document.getElementById("competenciaNombre").innerText = nombre;
            document.getElementById("competenciaDescripcion").innerText = descripcion;
            document.getElementById("competenciaCapacidades").innerHTML = capacidades; // Texto enriquecido

            // Muestra el modal
            document.getElementById("competenciaModal").style.display = "block";
        }

        function closeModal(event) {
            if (event) {
                event.stopPropagation();
            }
            document.getElementById("competenciaModal").style.display = "none";
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
