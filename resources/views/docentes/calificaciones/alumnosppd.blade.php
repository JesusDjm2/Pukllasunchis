@extends('layouts.docente')
@section('contenido')
    <style>
        .highlighted {
            background-color: #d4edda;
            transition: background-color 0.5s ease;
        }

        th.sortable {
            pointer-events: none;
        }

        .sticky-col-left-1 {
            position: sticky;
            left: 0;
            background-color: white;
            /* O el color de fondo de tus filas */
            z-index: 5;
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
            {{-- <form action="{{ route('calificaciones.exportar', ['docenteId' => $docente->id, 'cursoId' => $curso->id]) }}"
                method="GET">
                @csrf
                @foreach ($competenciasSeleccionadas as $competencia)
                    <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                @endforeach
                <div class="text-center"><button type="submit" class="btn btn-primary btn-sm mb-2">Exportar CSV <i
                            class="fa fa-file-csv"></i></button></div>
            </form> --}}
            <a href="{{ route('calificar', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right mb-3">
                <i class="fa fa-arrow-left fa-sm"></i> Volver
            </a>
        </div>
        <div class="col-lg-12">
            <p class="text-center" style="color: #000000">
                <span style="color:#103b86">Destacado: 17 - 20</span>
                <span>|</span>
                <span style="color:#0b954e"> Logrado: 14 - 16</span>
                <span>|</span>
                <span style="color:#c1ac0f"> En Proceso: 11 - 13</span>
                <span>|</span>
                <span class="text-danger"> Inicio: 6 - 10 </span>
                <span>|</span>
                <span class="text-danger"> Previo al Inicio: 1- 5</span>
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
                <form action="{{ route('calificarppd') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    @foreach ($competenciasSeleccionadas as $competencia)
                        <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                    @endforeach
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm mb-2">Guardar/Actualizar</button>
                    </div>
                    <div style="max-height: 800px; overflow-x: auto;">
                        <table class="table table-hover table-bordered text-center text-dark" style="font-size: 13px; min-width: 4000px;">
                            <thead style="color: #000">
                                <tr>
                                    <th rowspan="3" class="text-center align-middle sortable bg-dark text-white">#</th>
                                    <th rowspan="3"
                                        class="text-center align-middle sortable sticky-col-left-1 bg-dark text-white">
                                        Alumno</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 4 }}"
                                        class="text-center align-middle sortable" style="background-color: #e5973a">
                                        Productos de Proceso 40%</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
                                        class="text-center align-middle sortable" style="background-color: #ffd39f">Producto
                                        Final 60%</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
                                        class="text-center align-middle sortable bg-success">Promedios Generales por
                                        competencia</th>
                                    <th rowspan="3" class="align-middle sortable bg-warning">Nivel de desempeño</th>
                                    <th rowspan="3" class="align-middle sortable bg-warning">Calificación del Curso</th>
                                    <th rowspan="3" class="align-middle sortable bg-warning">Calificación en el Sistema
                                        Superior</th>
                                    <th rowspan="3" class="align-middle text-white bg-dark">
                                        Observaciones
                                    </th>
                                </tr>

                                <tr style="pointer-events: none">
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="4" class="text-center sortable" style="background-color: #e5973a">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6)
                                                    ...
                                                @endif
                                            </small>
                                        </th>
                                    @endforeach

                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="3" class="text-center sortable" style="background: #ffd39f">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6)
                                                    ...
                                                @endif
                                            </small>
                                        </th>
                                    @endforeach

                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="3" class="text-center sortable bg-success">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6)
                                                    ...
                                                @endif
                                            </small>
                                        </th>
                                    @endforeach
                                </tr>

                                <tr style="pointer-events: none; font-size: 12px">
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th style="background: #e5973a">Participación</th>
                                        <th style="background: #e5973a">Trabajo Individual</th>
                                        <th style="background: #e5973a">Trabajo Grupal</th>
                                        <th style="background: #e5973a">Promedio</th>
                                    @endforeach

                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th style="background-color: #ffd39f">Autoevaluación 40%</th>
                                        <th style="background-color: #ffd39f">Evaluación 60%</th>
                                        <th style="background-color: #ffd39f">Promedio</th>
                                    @endforeach

                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th class="text-center bg-success">Proceso 40%</th>
                                        <th class="text-center bg-success">Final 60%</th>
                                        <th class="text-center bg-success">Valoración</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    @php
                                        $ppd = $alumno->user->alumnoB ?? null;
                                        $calificacion = $ppd?->calificaciones->where('curso_id', $curso->id)->first();
                                    @endphp

                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                        value="{{ $docente->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                        value="{{ $curso->id }}">

                                    <tr>
                                        <td class="align-middle text-center">{{ $index + 1 }}</td>
                                        <td class="align-middle sticky-col-left-1">{{ $alumno->apellidos }},
                                            {{ $alumno->nombres }}</td>

                                        @foreach ([1, 2, 3] as $c)
                                            @foreach (range(1, 4) as $i)
                                                @php $campo = "pp_c{$c}_{$i}"; @endphp
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][proceso][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center indicador-input {{ $i < 4 ? 'editable' : '' }}"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}"
                                                        value="{{ old("alumnos.{$alumno->id}.proceso.c{$c}.indicador_{$i}", $calificacion?->$campo) }}"
                                                        min="0" max="20" step="1"
                                                        {{ $i == 4 ? 'readonly' : '' }}>
                                                </td>
                                            @endforeach
                                        @endforeach

                                        @foreach ([1, 2, 3] as $c)
                                            @foreach (range(1, 3) as $i)
                                                @php $campo = "pf_c{$c}_{$i}"; @endphp 
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][final][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center final-indicador-input"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}"
                                                        value="{{ old("alumnos.{$alumno->id}.final.c{$c}.indicador_{$i}", $calificacion?->$campo) }}"
                                                        min="0" max="20" step="1"
                                                        {{ $i == 3 ? 'readonly' : '' }}>
                                                </td>
                                            @endforeach
                                        @endforeach

                                        @foreach ([1, 2, 3] as $c)
                                            @foreach (range(1, 3) as $i)
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][promedios][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center promedio-general-input"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}" min="0"
                                                        max="20" step="1" readonly>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        <td>
                                            <input type="number" name="alumnos[{{ $alumno->id }}][nivel_desempeno]"
                                                class="form-control form-control-sm text-center nivel-desempeno-input"
                                                min="0" max="20" readonly>
                                        </td>

                                        <td>
                                            <input type="number" name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                class="form-control form-control-sm text-center calificacion-curso-input"
                                                min="0" max="20" readonly>
                                        </td>

                                        <td>
                                            <input type="number"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                class="form-control form-control-sm text-center calificacion-sistema-input"
                                                min="0" max="20" readonly>
                                        </td>
                                        <td style="width: 950px;">
                                            <textarea name="alumnos[{{ $alumno->id }}][observaciones]" class="form-control form-control-sm text-start"
                                                rows="2" style="resize: vertical; width: 100%;" placeholder="Observaciones (Opcional)">{{ old("alumnos.{$alumno->id}.observaciones", $calificacion?->observaciones) }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.indicador-input.editable');
            inputs.forEach(input => {
                // Validar solo enteros en tiempo real
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, ''); // Solo dígitos

                    const alumnoId = this.dataset.alumno;
                    const competenciaId = this.dataset.competencia;

                    // Obtener los inputs del mismo grupo
                    const i1 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="1"]`
                    );
                    const i2 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="2"]`
                    );
                    const i3 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="3"]`
                    );
                    const i4 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="4"]`
                    );

                    const v1 = parseInt(i1.value) || 0;
                    const v2 = parseInt(i2.value) || 0;
                    const v3 = parseInt(i3.value) || 0;

                    const promedio = Math.round((v1 + v2 + v3) / 3); // Solo enteros

                    i4.value = promedio;
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const finalInputs = document.querySelectorAll('.final-indicador-input');
            finalInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Validar solo enteros
                    this.value = this.value.replace(/[^0-9]/g, '');
                    const alumnoId = this.dataset.alumno;
                    const competenciaId = this.dataset.competencia;
                    const i1 = document.querySelector(
                        `input.final-indicador-input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="1"]`
                    );
                    const i2 = document.querySelector(
                        `input.final-indicador-input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="2"]`
                    );
                    const i3 = document.querySelector(
                        `input.final-indicador-input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="3"]`
                    );
                    const v1 = parseInt(i1.value) || 0;
                    const v2 = parseInt(i2.value) || 0;
                    const promedio = Math.round((v1 + v2) / 2);
                    if (i3) i3.value = promedio;
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function calcularPromedio(indicadores, total) {
                const valores = indicadores.map(i => parseInt(i.value) || 0);
                return Math.round(valores.reduce((a, b) => a + b, 0) / total);
            }

            function manejarInputs(selector, resultadoIndex) {
                const inputs = document.querySelectorAll(selector);
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');

                        const alumnoId = this.dataset.alumno;
                        const competenciaId = this.dataset.competencia;

                        let grupo = [];
                        for (let i = 1; i < resultadoIndex; i++) {
                            grupo.push(document.querySelector(
                                `${selector}[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="${i}"]`
                            ));
                        }

                        const resultado = document.querySelector(
                            `${selector}[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="${resultadoIndex}"]`
                        );
                        if (resultado) resultado.value = calcularPromedio(grupo, grupo.length);
                    });
                });
            }

            manejarInputs('.indicador-input.editable', 4); 
            manejarInputs('.final-indicador-input', 3); 
            manejarInputs('.promedio-general-input', 3); 
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cache = {};

            function getValor(alumnoId, competenciaId, tipo, indicador) {
                const input = document.querySelector(
                    `input[name="alumnos[${alumnoId}][${tipo}][c${competenciaId}][indicador_${indicador}]"]`);
                return parseFloat(input?.value) || 0;
            }

            function setValor(input, valor, redondear = true) {
                if (!input) return;
                const nuevoValor = redondear ? Math.round(valor) : parseFloat(valor).toFixed(2);
                if (input.value != nuevoValor) {
                    input.value = nuevoValor;
                    input.classList.add('highlighted');
                    setTimeout(() => input.classList.remove('highlighted'), 600);
                }
            }

            function convertirCalificacionACurso(valor) {
                if (valor >= 1 && valor < 1.5) return 1.0;
                if (valor >= 1.5 && valor < 2.5) return 1.1;
                if (valor >= 2.5 && valor < 3.5) return 1.2;
                if (valor >= 3.5 && valor < 4.5) return 1.3;
                if (valor >= 4.5 && valor < 5.5) return 1.4;
                if (valor >= 5.5 && valor < 6.5) return 1.5;
                if (valor >= 6.5 && valor < 7.5) return 1.6;
                if (valor >= 7.5 && valor < 8.5) return 1.7;
                if (valor >= 8.5 && valor < 9.5) return 1.8;
                if (valor >= 9.5 && valor < 10.5) return 1.9;
                if (valor >= 10.5 && valor < 11.5) return 2.2;
                if (valor >= 11.5 && valor < 12.5) return 2.5;
                if (valor >= 12.5 && valor < 13.5) return 2.7;
                if (valor >= 13.5 && valor < 14.5) return 2.9;
                if (valor >= 14.5 && valor < 15.5) return 3.1;
                if (valor >= 15.5 && valor < 16.5) return 3.3;
                if (valor >= 16.5 && valor < 17.5) return 3.5;
                if (valor >= 17.5 && valor < 18.5) return 3.7;
                if (valor >= 18.5 && valor < 19.5) return 3.9;
                if (valor >= 19.5 && valor <= 20) return 4.0;
                return '';
            }

            function actualizarTodo() {
                document.querySelectorAll('.promedio-general-input').forEach(input => {
                    const alumnoId = input.dataset.alumno;
                    const competenciaId = input.dataset.competencia;

                    const ppVal = getValor(alumnoId, competenciaId, 'proceso', 4);
                    const pfVal = getValor(alumnoId, competenciaId, 'final', 3);

                    const pg1 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_1]"]`);
                    const pg2 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_2]"]`);
                    const pg3 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_3]"]`);
                   
                    setValor(pg1, Math.round(ppVal));
                    setValor(pg2, Math.round(pfVal));
                    setValor(pg3, Math.round(ppVal * 0.4 + pfVal * 0.6));
                });

                document.querySelectorAll('tr').forEach(tr => {
                    const nivelInput = tr.querySelector('[name^="alumnos["][name$="[nivel_desempeno]"]');
                    if (!nivelInput) return;

                    const alumnoIdMatch = nivelInput.name.match(/\[(\d+)]/);
                    if (!alumnoIdMatch) return;

                    const alumnoId = alumnoIdMatch[1];

                    const getPromedio = (c) => {
                        const input = tr.querySelector(
                            `.promedio-general-input[data-competencia="${c}"][data-indicador="3"]`);
                        return input ? parseFloat(input.value) || 0 : 0;
                    };

                    const pg_c1 = getPromedio(1);
                    const pg_c2 = getPromedio(2);
                    const pg_c3 = getPromedio(3);

                    const promedioSistema = (pg_c1 + pg_c2 + pg_c3) / 3;
                    const calificacionCurso = convertirCalificacionACurso(promedioSistema);
                    const nivelDesempeno = Math.floor(calificacionCurso);

                    const inputSistema = tr.querySelector(
                        `[name="alumnos[${alumnoId}][calificacion_sistema]"]`);
                    const inputCurso = tr.querySelector(
                        `[name="alumnos[${alumnoId}][calificacion_curso]"]`);
                    const inputDesempeno = tr.querySelector(
                        `[name="alumnos[${alumnoId}][nivel_desempeno]"]`);

                    // calificacion_sistema como entero
                    setValor(inputSistema, Math.round(promedioSistema));
                    setValor(inputCurso, calificacionCurso, false);
                    setValor(inputDesempeno, nivelDesempeno);
                });
            }
            setInterval(actualizarTodo, 200);
        });
    </script>
@endsection
