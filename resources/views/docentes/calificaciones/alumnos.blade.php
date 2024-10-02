@extends('layouts.docente')
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
                <small class="text-secondary">({{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }})</small>
            </h4>
            <a href="{{ route('calificar', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right mb-3">
                Volver
            </a>
        </div>
        <div class="col-lg-12">
            <p class="text-center">
                <span class="text-success">Destacado: 17 - 20</span>
                | <span class="text-primary">Logrado: 14 - 16</span>
                | <span class="text-info">En Proceso: 11 - 13</span>
                | <span class="text-warning">Inicio: 6 - 10 </span>
                | <span class="text-danger">Previo al Inicio: 1- 5</span>
            </p>
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
                <form action="{{ route('publicarPeriodoUno') }}" method="POST" class="me-2">
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
                @if (isset($success))
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        {{ $success }}
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
                    <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="2" class="text-center align-middle sortable">#</th>
                                <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                @foreach ($competenciasSeleccionadas as $competencia)
                                    <th rowspan="2" class="text-center align-middle sortable" style="font-size: 14px">
                                        {{ $competencia->nombre }}
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
                                                    <option selected>Seleccionar</option>
                                                    @php
                                                        // Obtener la calificación para el alumno y el curso
                                                        $calificacion = $alumno
                                                            ->calificaciones()
                                                            ->where('curso_id', $curso->id)
                                                            ->first();
                                                    @endphp
                                                    <option value="5"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 5 ? 'selected' : '' }}>
                                                        Destacado</option>
                                                    <option value="4"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 4 ? 'selected' : '' }}>
                                                        Logrado</option>
                                                    <option value="3"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 3 ? 'selected' : '' }}>
                                                        En Proceso</option>
                                                    <option value="2"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 2 ? 'selected' : '' }}>
                                                        Inicio</option>
                                                    <option value="1"
                                                        {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == 1 ? 'selected' : '' }}>
                                                        Previo al Inicio</option>
                                                </select>
                                                <input type="text"
                                                    class="form-control form-control-sm input-competencia"
                                                    name="nota_{{ $competencia->id }}[]"
                                                    value="{{ $alumno->calificacion ? $alumno->calificacion->nota : '' }}"
                                                    readonly style="display: none">
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

                                        {{-- <td>
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="valoracion_curso"
                                                value="{{ $alumno->calificacion ? $alumno->calificacion->valoracion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="calificacion_curso"
                                                value="{{ $alumno->calificacion ? $alumno->calificacion->calificacion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="calificacion_sistema"
                                                value="{{ $alumno->calificacion ? $alumno->calificacion->calificacion_sistema : '' }}"
                                                readonly>
                                        </td> --}}
                                        <td>
                                            <button type="submit"
                                                class="btn btn-sm {{ $alumno->calificacion ? 'btn-success' : 'btn-primary' }}">
                                                {{ $alumno->calificacion ? 'Actualizar' : 'Guardar' }}
                                            </button>
                                        </td>
                                    </form>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const competenciaSelects = document.querySelectorAll('.select-competencia');

            competenciaSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const valor = this.value;
                    const input = this
                        .nextElementSibling;
                    input.value = valor;
                    recalcularValoracionCurso(row);
                });
            });

            function recalcularValoracionCurso(row) {
                const inputs = row.querySelectorAll('.input-competencia');
                let sum = 0;
                let count = 0;

                inputs.forEach(input => {
                    if (input.value) {
                        sum += parseInt(input.value);
                        count++;
                    }
                });

                const promedio = count > 0 ? (sum / count).toFixed(2) : 0;

                // Actualiza el input de valoración del curso en la fila específica
                const valoracionInput = row.querySelector('.valoracion-curso');
                if (valoracionInput) {
                    valoracionInput.value = promedio;
                }

                // Calcula la calificación del sistema basada en el promedio en la fila específica
                const calificacionSistemaInput = row.querySelector('.calificacion-sistema');
                const valoracionSistema = calcularValoracion(promedio);
                if (calificacionSistemaInput) {
                    calificacionSistemaInput.value = valoracionSistema;

                    // Actualiza la calificación del curso basada en la calificación del sistema
                    actualizarCalificacionCurso(calificacionSistemaInput, row);
                }
            }

            function calcularValoracion(valor) {
                if (valor <= 1.144) return 1;
                if (valor <= 1.344) return 2;
                if (valor <= 1.544) return 3;
                if (valor <= 1.744) return 4;
                if (valor <= 1.944) return 5;
                if (valor <= 2.144) return 6;
                if (valor <= 2.344) return 7;
                if (valor <= 2.544) return 8;
                if (valor <= 2.744) return 9;
                if (valor <= 2.944) return 10;
                if (valor <= 3.244) return 11;
                if (valor <= 3.544) return 12;
                if (valor <= 3.744) return 13;
                if (valor <= 3.944) return 14;
                if (valor <= 4.144) return 15;
                if (valor <= 4.344) return 16;
                if (valor <= 4.544) return 17;
                if (valor <= 4.744) return 18;
                if (valor <= 4.944) return 19;
                if (valor <= 5) return 20;
                return '';
            }

            function actualizarCalificacionCurso(calificacionSistemaInput, row) {
                const valor = calificacionSistemaInput.value;
                const calificacionCursoInput = row.querySelector('.calificacion-curso');

                if (calificacionCursoInput) {
                    calificacionCursoInput.value = calcularCalificacionCurso(valor);
                }
            }

            function calcularCalificacionCurso(valor) {
                if (valor <= 5) return "Previo al inicio";
                if (valor <= 10) return "Inicio";
                if (valor <= 14) return "En proceso";
                if (valor <= 19) return "Logrado";
                if (valor == 20) return "Destacado";
                return '';
            }

            const calificacionSistemaInputs = document.querySelectorAll('.calificacion-sistema');

            calificacionSistemaInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr'); // Solo afecta la fila actual
                    actualizarCalificacionCurso(this, row);
                });
            });
        });
    </script>
@endsection
