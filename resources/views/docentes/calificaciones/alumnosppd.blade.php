@extends('layouts.docente')

@section('titulo', 'Calificaciones PPD')

@section('contenido')
    <style>
        .docente-cal-progress {
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.06);
        }

        .docente-cal-progress .progress-bar {
            transition: width 0.9s cubic-bezier(.22, 1, .36, 1);
        }

        .docente-cal-progress-ppd .progress-bar {
            background: linear-gradient(90deg, #e5973a, #f5b45f);
        }

        @media (prefers-reduced-motion: reduce) {
            .docente-cal-progress .progress-bar {
                transition: none;
            }
        }

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
            z-index: 5;
        }

        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #343a40;
            color: white;
        }

        /* Quitar flechas de inputs numéricos */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] { -moz-appearance: textfield; }

        /* Resaltado de selección para copiar */
        input.xl-selected {
            outline: 2px solid #e6a817 !important;
            background-color: #fff9e6 !important;
        }

        /* ── Rediseño premium ── */
        .docente-cal-savebar {
            position: sticky;
            top: 0.5rem;
            z-index: 30;
            display: flex;
            justify-content: center;
            margin-bottom: 0.65rem;
        }

        .docente-cal-savebar .btn {
            border-radius: 999px;
            padding: 0.5rem 1.5rem;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
        }

        .docente-cal-savebar .btn.is-guardando {
            opacity: 0.85;
            cursor: progress;
        }

        /* Affordance de scroll horizontal para la tabla PPD (muy ancha por diseño) */
        .docente-cal-scroll-wrap {
            position: relative;
        }

        .docente-cal-scroll-hint {
            display: none;
            text-align: center;
            font-size: 0.78rem;
            color: #858796;
            padding: 0.35rem 0 0.5rem;
        }

        .docente-cal-scroll-hint i {
            margin-right: 0.3rem;
        }

        @media (max-width: 991.98px) {
            .docente-cal-scroll-hint {
                display: block;
            }
        }

        table.docente-cal-table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
    </style>
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Calificaciones PPD',
            'title' => $curso->nombre,
            'subtitle' => ($curso->ciclo->programa->nombre ?? '') . ' — ' . ($curso->ciclo->nombre ?? ''),
            'backUrl' => route('vistaDocente', $docente->id),
            'backLabel' => 'Volver a cursos',
            'competencias' => $competenciasSeleccionadas,
            'rightExtra' => view('docentes.partials.calificacion-legenda')->render(),
        ])

        @php $pctPPD = $curso->porcentajePPD(); @endphp
        <div class="row">
            <div class="col-12">
                <div class="mb-3" style="max-width:420px;">
                    <div class="d-flex align-items-center" style="gap:6px;">
                        <span style="font-size:12px;width:78px;font-weight:600;color:#d97706;">Calificado:</span>
                        <div class="progress flex-fill docente-cal-progress docente-cal-progress-ppd">
                            <div class="progress-bar" role="progressbar" style="width:{{ $pctPPD }}%;"></div>
                        </div>
                        <small style="width:50px;text-align:right;font-size:12px;color:#d97706;font-weight:600;">{{ number_format($pctPPD, 2) }}%</small>
                    </div>
                </div>
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        Algunas notas no se guardaron por tener valores fuera de rango (0–20). Corrige los campos
                        resaltados y vuelve a guardar. Lo que ya tenías escrito no se perdió.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
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
                    <div class="docente-cal-savebar">
                        <button type="submit" class="btn btn-primary btn-sm mb-2" data-loading-text="Guardando…"><i class="fas fa-save mr-1"></i> Guardar/Actualizar</button>
                    </div>
                    <p class="docente-cal-scroll-hint"><i class="fas fa-arrows-alt-h"></i>Desliza horizontalmente para ver todas las columnas</p>
                    <div class="docente-cal-scroll-wrap" style="max-height: 800px; overflow-x: auto;">
                        <table class="table table-hover table-bordered text-center text-dark docente-cal-table"
                            style="font-size: 13px; min-width: 4000px;">
                            <thead style="color: #000">
                                <tr>
                                    <th rowspan="3" class="text-center align-middle sortable bg-dark text-white">#</th>
                                    <th rowspan="3"
                                        class="text-center align-middle sortable sticky-col-left-1 bg-dark text-white">
                                        Alumno</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
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
                                        <th colspan="3" class="text-center sortable" style="background-color: #e5973a">
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
                                        <th style="background: #e5973a">Actividad</th>
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
                                        $ppd = $alumno->alumnoB;
                                        // Calificación: primero por ppd_id, luego por user_id (inhabilitados sin PPD)
                                        $calificacion = $ppd?->calificaciones->where('curso_id', $curso->id)->first()
                                            ?? \App\Models\Calificacionesppd::where('user_id', $alumno->id)
                                                ->where('curso_id', $curso->id)->first();
                                        // Todos los alumnos (incluyendo inhabilitados) tienen inputs habilitados
                                        $readonly = '';
                                    @endphp

                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][ppd_id]"
                                        value="{{ $alumno->alumnoB?->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                        value="{{ $docente->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                        value="{{ $curso->id }}">

                                    <tr class="{{ $alumno->es_inhabilitado ? 'table-secondary' : '' }}">
                                        <td class="align-middle text-center">{{ $index + 1 }}</td>
                                        <td class="align-middle sticky-col-left-1">
                                            <div>
                                                {{ $alumno->apellidos }}, {{ $alumno->name }}
                                            </div>
                                            <div class="mt-1">
                                                @if ($alumno->es_inhabilitado)
                                                    <span class="badge bg-danger text-white">Inhabilitado</span>
                                                @endif
                                                @if (!$alumno->tiene_ppd)
                                                    <span class="badge bg-warning text-dark" title="No se encontró registro PPD para este alumno">Sin registro PPD</span>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Proceso --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach ([1, 2, 4] as $i)
                                                @php $campo = "pp_c{$c}_{$i}"; @endphp
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][proceso][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center indicador-input {{ $i < 4 ? 'editable' : '' }} @error("alumnos.{$alumno->id}.proceso.c{$c}.indicador_{$i}") is-invalid @enderror"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}"
                                                        value="{{ old("alumnos.{$alumno->id}.proceso.c{$c}.indicador_{$i}", $calificacion?->$campo) }}"
                                                        min="0" max="20" step="1"
                                                        {{ $i == 4 ? 'readonly' : '' }} {{ $readonly }}>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Final --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach (range(1, 3) as $i)
                                                @php $campo = "pf_c{$c}_{$i}"; @endphp
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][final][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center final-indicador-input @error("alumnos.{$alumno->id}.final.c{$c}.indicador_{$i}") is-invalid @enderror"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}"
                                                        value="{{ old("alumnos.{$alumno->id}.final.c{$c}.indicador_{$i}", $calificacion?->$campo) }}"
                                                        min="0" max="20" step="1"
                                                        {{ $i == 3 ? 'readonly' : '' }} {{ $readonly }}>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Promedios --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach (range(1, 3) as $i)
                                                <td>
                                                    <input type="number" style="width: 60px"
                                                        name="alumnos[{{ $alumno->id }}][promedios][c{{ $c }}][indicador_{{ $i }}]"
                                                        class="form-control form-control-sm text-center promedio-general-input"
                                                        data-alumno="{{ $alumno->id }}"
                                                        data-competencia="{{ $c }}"
                                                        data-indicador="{{ $i }}" min="0"
                                                        max="20" step="1" readonly {{ $readonly }}>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Nivel de desempeño --}}
                                        <td>
                                            <input type="number" name="alumnos[{{ $alumno->id }}][nivel_desempeno]"
                                                class="form-control form-control-sm text-center nivel-desempeno-input"
                                                min="0" max="20" readonly {{ $readonly }}>
                                        </td>
                                        {{-- Calificación curso --}}
                                        <td>
                                            <input type="number" name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                class="form-control form-control-sm text-center calificacion-curso-input"
                                                min="0" max="20" readonly {{ $readonly }}>
                                        </td>
                                        {{-- Calificación sistema --}}
                                        <td>
                                            <input type="number"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                class="form-control form-control-sm text-center calificacion-sistema-input"
                                                min="0" max="20" readonly {{ $readonly }}>
                                        </td>
                                        {{-- Observaciones --}}
                                        <td style="width: 950px;">
                                            <textarea name="alumnos[{{ $alumno->id }}][observaciones]" class="form-control form-control-sm text-start"
                                                rows="2" style="resize: vertical; width: 100%;" placeholder="Observaciones (Opcional)"
                                                {{ $readonly }}>{{ old("alumnos.{$alumno->id}.observaciones", $calificacion?->observaciones) }}</textarea>
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

    @include('docentes.partials.calificar-scripts')
    @include('docentes.partials.competencia-modal')
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
                    const i4 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="4"]`
                    );

                    const v1 = parseInt(i1.value) || 0;
                    const v2 = parseInt(i2.value) || 0;

                    const promedio = Math.round((v1 + v2) / 2);

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
                            const el = document.querySelector(
                                `${selector}[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="${i}"]`
                            );
                            if (el) grupo.push(el);
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
            // --- utilidades ---
            function getValor(alumnoId, competenciaId, tipo, indicador) {
                const input = document.querySelector(
                    `input[name="alumnos[${alumnoId}][${tipo}][c${competenciaId}][indicador_${indicador}]"]`
                );
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

            // --- listeners que calculan i4 (proceso) y i3 (final) al teclear / pegar ---
            // proceso: inputs .indicador-input.editable --> llenan indicador_4 (readonly)
            document.querySelectorAll('.indicador-input.editable').forEach(input => {
                input.addEventListener('input', function() {
                    // solo dígitos
                    this.value = this.value.replace(/[^0-9]/g, '');
                    const alumnoId = this.dataset.alumno;
                    const competenciaId = this.dataset.competencia;
                    const i1 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="1"]`
                    );
                    const i2 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="2"]`
                    );
                    const i4 = document.querySelector(
                        `input[data-alumno="${alumnoId}"][data-competencia="${competenciaId}"][data-indicador="4"]`
                    );

                    const v1 = parseInt(i1?.value) || 0;
                    const v2 = parseInt(i2?.value) || 0;
                    const promedio = Math.round((v1 + v2) / 2);
                    if (i4) i4.value = promedio;
                });
            });

            // final: inputs .final-indicador-input -> indicador 3 = round((i1+i2)/2)
            document.querySelectorAll('.final-indicador-input').forEach(input => {
                input.addEventListener('input', function() {
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
                    const v1 = parseInt(i1?.value) || 0;
                    const v2 = parseInt(i2?.value) || 0;
                    const promedio = Math.round((v1 + v2) / 2);
                    if (i3) i3.value = promedio;
                });
            });

            // --- función que actualiza promedios generales y calificaciones del curso/sistema ---
            function actualizarTodo() {
                document.querySelectorAll('.promedio-general-input').forEach(input => {
                    const alumnoId = input.dataset.alumno;
                    const competenciaId = input.dataset.competencia;

                    const ppVal = getValor(alumnoId, competenciaId, 'proceso', 4);
                    const pfVal = getValor(alumnoId, competenciaId, 'final', 3);

                    const pg1 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_1]"]`
                    );
                    const pg2 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_2]"]`
                    );
                    const pg3 = document.querySelector(
                        `input[name="alumnos[${alumnoId}][promedios][c${competenciaId}][indicador_3]"]`
                    );

                    setValor(pg1, Math.round(ppVal));
                    setValor(pg2, Math.round(pfVal));
                    setValor(pg3, Math.round(ppVal * 0.4 + pfVal * 0.6));
                });

                document.querySelectorAll('tr').forEach(tr => {
                    const nivelInput = tr.querySelector('[name^="alumnos["][name$="[nivel_desempeno]"]');
                    if (!nivelInput) return;

                    const alumnoIdMatch = nivelInput.name.match(/\[(\d+)\]/);
                    if (!alumnoIdMatch) return;

                    const alumnoId = alumnoIdMatch[1];

                    const getPromedio = (c) => {
                        const input = tr.querySelector(
                            `.promedio-general-input[data-competencia="${c}"][data-indicador="3"]`
                        );
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

                    setValor(inputSistema, Math.round(promedioSistema));
                    setValor(inputCurso, calificacionCurso, false);
                    setValor(inputDesempeno, nivelDesempeno);
                });
            }

            // intervalo para updates (puedes reducir o eliminar si prefieres disparar solo por eventos)
            const intervalId = setInterval(actualizarTodo, 200);

            // --- manejo del pegado (soporta horizontal y vertical) ---
            const tbody = document.querySelector('tbody');

            if (tbody) {
                tbody.addEventListener('paste', function(e) {
                    const target = e.target;
                    if (target.tagName !== 'INPUT') return; // sólo inputs

                    e.preventDefault();
                    const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
                    if (!pastedData) return;

                    const rows = pastedData.split(/\r?\n/); // filas del bloque pegado

                    // selector de celdas "rellenables" por fila (proceso 1..3 y final 1..2 — evitamos readonly calculados)
                    const fillableSelector = 'input.indicador-input, input.final-indicador-input';

                    // fila inicial y posición inicial
                    let startRow = target.closest('tr');
                    if (!startRow) return;

                    // inputs rellenables de la fila inicial (excluimos readonly)
                    let startFillables = Array.from(startRow.querySelectorAll(fillableSelector)).filter(i =>
                        !i.readOnly);

                    // índice de la celda inicial dentro de startFillables
                    let startIndex = startFillables.indexOf(target);

                    // si no encontró el target entre fillables (pegaste en un readonly), buscamos el fillable más cercano a la derecha
                    if (startIndex === -1) {
                        const allInputsStartRow = Array.from(startRow.querySelectorAll(
                            'input[type=number]'));
                        const startAllIndex = allInputsStartRow.indexOf(target);
                        startIndex = startFillables.findIndex(i => allInputsStartRow.indexOf(i) >=
                            startAllIndex);
                        if (startIndex === -1) startIndex = 0; // fallback
                    }

                    // todas las filas del tbody (para navegar verticalmente)
                    const allRows = Array.from(tbody.querySelectorAll('tr'));
                    let currentRowIndex = allRows.indexOf(startRow);

                    // Recorremos cada fila pegada
                    rows.forEach((rowData) => {
                        if (currentRowIndex < 0 || currentRowIndex >= allRows.length) return;
                        const values = rowData.split(/\t/).map(v => v.trim());

                        const rowElement = allRows[currentRowIndex];
                        const fillablesInRow = Array.from(rowElement.querySelectorAll(
                            fillableSelector)).filter(i => !i.readOnly);

                        // Por cada columna en el bloque pegado, asignamos a la celda correspondiente
                        values.forEach((rawVal, colIndex) => {
                            const input = fillablesInRow[startIndex + colIndex];
                            if (!input) return;

                            // limpiamos valor (acepta "18", "18.0", "18,0", etc.) y dejamos entero 0-20
                            let cleaned = rawVal.replace(',', '.').replace(/[^0-9.-]/g, '');
                            if (cleaned === '') return;
                            let num = Math.round(parseFloat(cleaned));
                            if (isNaN(num)) return;
                            if (num < 0) num = 0;
                            if (num > 20) num = 20;

                            input.value = num;
                            // disparamos input para que otros listeners (i4, i3) reaccionen
                            input.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        });
                        // avanzamos a la siguiente fila del tbody
                        currentRowIndex++;
                    });
                    // recalculamos globalmente después del pegado
                    actualizarTodo();
                });
            }
        });
    </script>
    <script>
    /* ── Excel-like navigation, fill handle & copy selection ── */
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.querySelector('tbody');
        if (!tbody) return;

        /* helpers */
        function buildGrid() {
            return Array.from(tbody.querySelectorAll('tr'))
                .map(tr => Array.from(tr.querySelectorAll(
                    'input[type=number]:not([readonly]):not([disabled])'
                )))
                .filter(row => row.length > 0);
        }

        function findInGrid(grid, el) {
            for (let r = 0; r < grid.length; r++)
                for (let c = 0; c < grid[r].length; c++)
                    if (grid[r][c] === el) return [r, c];
            return null;
        }

        function focusCell(grid, r, c) {
            const row = grid[r];
            if (!row) return;
            const inp = row[Math.min(c, row.length - 1)];
            if (inp) { inp.focus(); inp.select(); }
        }

        /* ── Selección para copiar ── */
        let selAnchor      = null;   // [r, c] — esquina fija
        let selActive      = null;   // [r, c] — esquina móvil
        let isShiftMouse   = false;
        let isDragSelecting = false;  // arrastre con mouse para seleccionar

        function getSelRect() {
            if (!selAnchor || !selActive) return null;
            return {
                r1: Math.min(selAnchor[0], selActive[0]),
                r2: Math.max(selAnchor[0], selActive[0]),
                c1: Math.min(selAnchor[1], selActive[1]),
                c2: Math.max(selAnchor[1], selActive[1]),
            };
        }

        function updateSelVisual(grid) {
            tbody.querySelectorAll('input.xl-selected').forEach(el => el.classList.remove('xl-selected'));
            const rect = getSelRect();
            if (!rect) return;
            for (let r = rect.r1; r <= rect.r2; r++)
                for (let c = rect.c1; c <= rect.c2; c++)
                    grid[r]?.[c]?.classList.add('xl-selected');
        }

        function fallbackCopy(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
            document.body.appendChild(ta);
            ta.focus(); ta.select();
            try { document.execCommand('copy'); } catch (_) {}
            document.body.removeChild(ta);
        }

        /* ── Teclas: flechas, Enter, Ctrl+D/R/C ── */
        tbody.addEventListener('keydown', function (e) {
            const el = e.target;
            if (el.tagName !== 'INPUT' || el.type !== 'number' || el.readOnly || el.disabled) return;

            const grid = buildGrid();
            const pos  = findInGrid(grid, el);
            if (!pos) return;
            let [r, c] = pos;

            if (!selAnchor) { selAnchor = [r, c]; selActive = [r, c]; }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (e.shiftKey) {
                    selActive = [Math.max(0, selActive[0] - 1), selActive[1]];
                    updateSelVisual(grid);
                } else {
                    r = Math.max(0, r - 1);
                    selAnchor = selActive = [r, c];
                    focusCell(grid, r, c);
                    updateSelVisual(grid);
                }
            } else if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                if (e.shiftKey) {
                    selActive = [Math.min(grid.length - 1, selActive[0] + 1), selActive[1]];
                    updateSelVisual(grid);
                } else {
                    r = Math.min(grid.length - 1, r + 1);
                    selAnchor = selActive = [r, c];
                    focusCell(grid, r, c);
                    updateSelVisual(grid);
                }
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                if (e.shiftKey) {
                    selActive = [selActive[0], Math.max(0, selActive[1] - 1)];
                    updateSelVisual(grid);
                } else {
                    c = Math.max(0, c - 1);
                    selAnchor = selActive = [r, c];
                    focusCell(grid, r, c);
                    updateSelVisual(grid);
                }
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                if (e.shiftKey) {
                    selActive = [selActive[0], Math.min((grid[selActive[0]]?.length ?? 1) - 1, selActive[1] + 1)];
                    updateSelVisual(grid);
                } else {
                    c = Math.min((grid[r]?.length ?? 1) - 1, c + 1);
                    selAnchor = selActive = [r, c];
                    focusCell(grid, r, c);
                    updateSelVisual(grid);
                }

            /* Ctrl+D: rellenar hacia abajo */
            } else if (e.ctrlKey && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                const val = el.value;
                for (let i = r + 1; i < grid.length; i++) {
                    const t = grid[i]?.[c];
                    if (t) { t.value = val; t.dispatchEvent(new Event('input', { bubbles: true })); }
                }

            /* Ctrl+R: rellenar hacia la derecha */
            } else if (e.ctrlKey && e.key.toLowerCase() === 'r') {
                e.preventDefault();
                const val = el.value;
                const row = grid[r];
                for (let j = c + 1; j < row.length; j++) {
                    row[j].value = val;
                    row[j].dispatchEvent(new Event('input', { bubbles: true }));
                }

            /* Ctrl+C: copiar selección al portapapeles */
            } else if (e.ctrlKey && e.key.toLowerCase() === 'c') {
                e.preventDefault();
                const rect = getSelRect();
                let text;
                if (!rect) {
                    text = el.value;
                } else {
                    const lines = [];
                    for (let row = rect.r1; row <= rect.r2; row++) {
                        const cols = [];
                        for (let col = rect.c1; col <= rect.c2; col++)
                            cols.push(grid[row]?.[col]?.value ?? '');
                        lines.push(cols.join('\t'));
                    }
                    text = lines.join('\n');
                }
                navigator.clipboard.writeText(text).catch(() => fallbackCopy(text));
                /* flash verde para confirmar */
                tbody.querySelectorAll('input.xl-selected').forEach(inp => {
                    inp.style.setProperty('outline', '2px solid #27ae60', 'important');
                    setTimeout(() => inp.style.removeProperty('outline'), 600);
                });
            }
        });

        /* ── Fill handle ── */
        const handle = document.createElement('div');
        handle.id = 'xl-fill-handle';
        Object.assign(handle.style, {
            position: 'fixed', width: '8px', height: '8px',
            background: '#1E3A6F', border: '1.5px solid #fff',
            cursor: 'crosshair', zIndex: '9999', display: 'none',
            boxSizing: 'border-box',
        });
        document.body.appendChild(handle);

        let activeEl   = null;
        let dragging   = false;
        let dragSource = null;
        let fillRange  = [];

        function placeHandle(inp) {
            const r = inp.getBoundingClientRect();
            handle.style.left    = (r.right  - 5) + 'px';
            handle.style.top     = (r.bottom - 5) + 'px';
            handle.style.display = 'block';
        }

        function clearFill() {
            fillRange.forEach(el => el.style.outline = '');
            fillRange = [];
        }

        /* mousedown: Shift+Click extiende / click normal inicia arrastre */
        tbody.addEventListener('mousedown', function (e) {
            const el = e.target;
            if (el.tagName !== 'INPUT' || el.type !== 'number' || el.readOnly || el.disabled) return;
            isShiftMouse = e.shiftKey;
            if (e.shiftKey) {
                const grid = buildGrid();
                const pos  = findInGrid(grid, el);
                if (!pos) return;
                if (!selAnchor) selAnchor = pos;
                selActive = pos;
                updateSelVisual(grid);
            } else {
                isDragSelecting = true;
                document.body.style.userSelect = 'none';
            }
        });

        /* mousemove: extiende selección mientras se arrastra con el botón presionado */
        tbody.addEventListener('mousemove', function (e) {
            if (!isDragSelecting || dragging) return;
            const raw = document.elementFromPoint(e.clientX, e.clientY);
            if (!raw) return;
            const target = (raw.tagName === 'INPUT' && raw.type === 'number')
                ? raw
                : raw.closest('td')?.querySelector('input[type=number]:not([readonly]):not([disabled])');
            if (!target || target.readOnly || target.disabled) return;
            const grid = buildGrid();
            const pos  = findInGrid(grid, target);
            if (!pos) return;
            if (pos[0] === selActive?.[0] && pos[1] === selActive?.[1]) return; // sin cambio
            selActive = pos;
            updateSelVisual(grid);
        });

        tbody.addEventListener('focusin', function (e) {
            const el = e.target;
            if (el.tagName !== 'INPUT' || el.type !== 'number' || el.readOnly || el.disabled) {
                if (!dragging) { handle.style.display = 'none'; activeEl = null; }
                return;
            }
            activeEl = el;
            placeHandle(el);
            if (!isShiftMouse) {
                const grid = buildGrid();
                const pos  = findInGrid(grid, el);
                if (pos) { selAnchor = pos; selActive = pos; updateSelVisual(grid); }
            }
            isShiftMouse = false;
        });

        tbody.addEventListener('focusout', function () {
            if (!dragging) setTimeout(() => {
                if (document.activeElement !== activeEl) {
                    handle.style.display = 'none'; activeEl = null;
                }
            }, 120);
        });

        const scrollable = document.querySelector('div[style*="overflow-x"]');
        if (scrollable) scrollable.addEventListener('scroll', () => {
            if (activeEl && !dragging) placeHandle(activeEl);
        });

        handle.addEventListener('mousedown', function (e) {
            if (!activeEl) return;
            e.preventDefault();
            dragging   = true;
            dragSource = activeEl;
        });

        document.addEventListener('mousemove', function (e) {
            if (!dragging || !dragSource) return;
            handle.style.left = (e.clientX - 4) + 'px';
            handle.style.top  = (e.clientY - 4) + 'px';
            clearFill();

            const grid     = buildGrid();
            const startPos = findInGrid(grid, dragSource);
            if (!startPos) return;
            const [sr, sc] = startPos;

            handle.style.pointerEvents = 'none';
            const els = document.elementsFromPoint(e.clientX, e.clientY);
            handle.style.pointerEvents = 'auto';

            const target = els.find(el =>
                el.tagName === 'INPUT' && el.type === 'number' && !el.readOnly && !el.disabled
            );
            if (!target) return;
            const tp = findInGrid(grid, target);
            if (!tp) return;
            const [tr_, tc_] = tp;

            const dr = Math.abs(tr_ - sr), dc = Math.abs(tc_ - sc);
            if (dr >= dc) {
                const [mn, mx] = [Math.min(sr, tr_), Math.max(sr, tr_)];
                for (let r = mn; r <= mx; r++) {
                    if (r === sr) continue;
                    const inp = grid[r]?.[sc];
                    if (inp) { inp.style.outline = '2px solid #1E3A6F'; fillRange.push(inp); }
                }
            } else {
                const [mn, mx] = [Math.min(sc, tc_), Math.max(sc, tc_)];
                for (let c = mn; c <= mx; c++) {
                    if (c === sc) continue;
                    const inp = grid[sr]?.[c];
                    if (inp) { inp.style.outline = '2px solid #1E3A6F'; fillRange.push(inp); }
                }
            }
        });

        document.addEventListener('mouseup', function () {
            if (isDragSelecting) {
                isDragSelecting = false;
                document.body.style.userSelect = '';
            }
            if (!dragging) return;
            dragging = false;
            const val = dragSource?.value ?? '';
            fillRange.forEach(inp => {
                inp.value = val;
                inp.dispatchEvent(new Event('input', { bubbles: true }));
            });
            clearFill();
            dragSource = null;
            if (activeEl) placeHandle(activeEl); else handle.style.display = 'none';
        });
    });
    </script>
@endsection
