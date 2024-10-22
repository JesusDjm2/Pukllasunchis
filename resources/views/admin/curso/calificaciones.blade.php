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
            /* 15% desde arriba y centrado */
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            /* Ancho del modal */
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
    </style>
    <div class="container-fluid bg-light">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">{{ $curso->nombre }}
                <small class="text-secondary">({{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }}) <br>
                    <span class="text-primary">Docente: {{ $docente->nombre }}</span></small>
            </h4>
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
        <div class="col-lg-12 text-center mb-2">
            <div class="d-flex justify-content-center mb-2">
                <form action="{{ route('publicar.periodo.uno') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    @foreach ($competenciasSeleccionadas as $competencia)
                        <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-success mr-1">Publicar Periodo 1</button>
                </form>
                @php
                    $hayPeriodoUno = \App\Models\PeriodoUno::where('curso_id', $curso->id)->exists();
                @endphp

                @if ($hayPeriodoUno)
                    <form action="{{ route('eliminarPeriodoUno') }}" method="POST" onsubmit="return confirmDelete();">
                        @csrf
                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                        <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                        @foreach ($competenciasSeleccionadas as $competencia)
                            <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                        @endforeach
                        <button type="submit" class="btn btn-sm btn-danger mr-2">Eliminar Periodo 1</button>
                    </form>
                @endif
                <script>
                    function confirmDelete() {
                        return confirm('¿Estás seguro que deseas eliminar el Periodo 1? Esta acción no se puede deshacer.');
                    }
                </script>

                {{-- <a href="" class="btn btn-sm btn-success mr-1">Publicar Perido 2</a>
                <a href="" class="btn btn-sm btn-success mr-1">Publicar Desempeño Semestral</a> --}}

                <form action="{{ route('borrarCalificaciones') }}" method="POST"
                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todas las calificaciones?');">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    @foreach ($alumnos as $alumno)
                        <input type="hidden" name="alumnos_ids[]" value="{{ $alumno->id }}">
                    @endforeach
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    @foreach ($competenciasSeleccionadas as $index => $competencia)
                        <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-danger btn-sm mb-2">Eliminar todas las calificaciones</button>
                </form>

            </div>
        </div>
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
        <div class="row pb-5">
            <div class="col-lg-12 table-responsive">
                <div style="max-height: 400px; overflow-y: auto;">
                    {{-- <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="2" class="text-center align-middle sortable">#</th>
                                <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                @foreach ($competenciasSeleccionadas as $competencia)
                                    <th rowspan="2" class="text-center align-middle sortable" style="font-size: 14px">
                                        {{ $competencia->nombre }}<br>
                                        <small style="font-size: 10px">
                                            {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 7)) }}
                                            @if (str_word_count($competencia->descripcion) > 6)
                                                ...
                                            @endif
                                        </small>
                                    </th>
                                @endforeach

                                <th colspan="3" class="text-center sortable">Calificación</th>
                                <th rowspan="2" class="align-middle sortable">Calificar</th>
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
                                    <td style="text-align: left">
                                        {{ $alumno->apellidos }},
                                        {{ $alumno->nombres }}
                                    </td>
                                    <form action="{{ route('guardarCalificacion') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                        <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                                            <td>
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="valoracion_{{ $index + 1 }}">
                                                    <option value="0" selected>Seleccionar</option>
                                                    @php
                                                        $calificacion = $alumno
                                                            ->calificaciones()
                                                            ->where('curso_id', $curso->id)
                                                            ->first();
                                                    @endphp
                                                    <option value="5"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 5 ? 'selected' : '' }}>
                                                        Destacado
                                                    </option>
                                                    <option value="4"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 4 ? 'selected' : '' }}>
                                                        Logrado
                                                    </option>
                                                    <option value="3"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 3 ? 'selected' : '' }}>
                                                        En Proceso
                                                    </option>
                                                    <option value="2"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 2 ? 'selected' : '' }}>
                                                        Inicio
                                                    </option>
                                                    <option value="1"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 1 ? 'selected' : '' }}>
                                                        Previo al Inicio
                                                    </option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm input-competencia"
                                                    name="nota_{{ $competencia->id }}[]"
                                                    value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="valoracion_curso"
                                                value="{{ $calificacion ? $calificacion->valoracion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="calificacion_curso"
                                                value="{{ $calificacion ? $calificacion->calificacion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="calificacion_sistema"
                                                value="{{ $calificacion ? $calificacion->calificacion_sistema : '' }}"
                                                readonly>
                                        </td>
                                        <td>
                                            <button type="submit"
                                                class="btn btn-sm {{ $calificacion ? 'btn-success' : 'btn-primary' }}">
                                                {{ $calificacion ? 'Actualizar' : 'Guardar' }}
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table> --}}
                    <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="2" class="text-center align-middle sortable">#</th>
                                <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                @foreach ($competenciasSeleccionadas as $competencia)
                                    <th rowspan="2" class="text-center align-middle sortable" style="font-size: 14px">
                                        {{ $competencia->nombre }}<br>
                                        <small style="font-size: 10px">
                                            {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 7)) }}
                                            @if (str_word_count($competencia->descripcion) > 6)
                                                ...
                                            @endif
                                        </small>
                                    </th>
                                @endforeach
                                <th colspan="3" class="text-center sortable">Calificación</th>
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
                                    <td style="text-align: left">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</td>
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
                                            <input type="text" class="form-control form-control-sm input-competencia"
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
@endsection
