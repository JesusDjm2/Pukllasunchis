@extends('layouts.docente')
@section('titulo', 'Calificaciones FID')
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

        .alumno-identidad {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-align: left;
        }

        .alumno-foto-btn {
            border: 0;
            background: transparent;
            padding: 0;
            border-radius: 999px;
            line-height: 0;
            cursor: pointer;
        }

        .alumno-foto-btn:focus {
            outline: 2px solid #4e73df;
            outline-offset: 2px;
        }

        .alumno-foto-thumb {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d6dde8;
            transition: transform .2s ease, border-color .2s ease;
        }

        .alumno-foto-btn:hover .alumno-foto-thumb {
            transform: scale(1.07);
            border-color: #4e73df;
        }

        .alumno-foto-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #c6cfdd;
            background: #f8fafd;
            color: #8a96a8;
            flex-shrink: 0;
        }

        .alumno-identidad-nombre {
            font-weight: 700;
            line-height: 1.2;
        }

        .alumno-identidad-badges .badge {
            margin-top: 0.2rem;
            margin-right: 0.2rem;
        }

        .alumno-foto-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(11, 18, 32, 0.8);
            z-index: 2100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .alumno-foto-modal-overlay.is-open {
            display: flex;
        }

        .alumno-foto-modal-card {
            width: min(92vw, 560px);
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .28);
        }

        .alumno-foto-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .7rem .95rem;
            background: #33445a;
            color: #fff;
        }

        .alumno-foto-modal-close {
            border: 0;
            background: transparent;
            color: #fff;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
        }

        .alumno-foto-modal-body {
            padding: .75rem;
            text-align: center;
            background: #f8fafc;
        }

        .alumno-foto-modal-body img {
            max-width: 100%;
            max-height: 72vh;
            border-radius: 10px;
            object-fit: contain;
        }

        @media (max-width: 576px) {
            .alumno-identidad {
                gap: 0.5rem;
            }

            .alumno-foto-thumb,
            .alumno-foto-placeholder {
                width: 36px;
                height: 36px;
            }
        }

        /* ── Rediseño premium: leyenda, pestañas, barra de guardado, filas ── */
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

        table.docente-cal-table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .docente-cal-periodo-tabs .btn {
            border-radius: 999px;
            font-weight: 600;
            padding: 0.4rem 1.15rem;
        }
    </style>
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Calificaciones FID',
            'title' => $curso->nombre,
            'subtitle' => ($curso->ciclo->programa->nombre ?? '') . ' — ' . ($curso->ciclo->nombre ?? ''),
            'backUrl' => route('calificar', $docente->id),
            'backLabel' => 'Volver a cursos',
            'competencias' => $competenciasSeleccionadas,
        ])

        <div class="card docente-ui-card mb-3">
            <div class="card-body py-3">
                <p class="docente-ui-legenda text-center mb-0">
                    <span class="legenda-item" style="color:#103b86">Destacado: 17–20</span>
                    <span class="d-none d-sm-inline"> | </span>
                    <span class="legenda-item d-block d-sm-inline" style="color:#0b954e">Logrado: 14–16</span>
                    <span class="d-none d-sm-inline"> | </span>
                    <span class="legenda-item d-block d-sm-inline" style="color:#c1ac0f">En proceso: 11–13</span>
                    <span class="d-none d-sm-inline"> | </span>
                    <span class="legenda-item d-block d-sm-inline text-danger">Inicio: 6–10</span>
                    <span class="d-none d-sm-inline"> | </span>
                    <span class="legenda-item d-block d-sm-inline text-danger">Previo al inicio: 1–5</span>
                </p>
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
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        Algunas notas no se guardaron por tener valores fuera de rango. Corrige los campos
                        resaltados y vuelve a guardar. Lo que ya tenías escrito no se perdió.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="row pb-5">
            <div class="col-lg-12">
                <div class="text-center mb-3 docente-cal-periodo-tabs">
                    <div class="btn-group" role="group" aria-label="Controles de Periodo">
                        <button type="button" class="btn btn-outline-primary btn-sm active" id="btnPeriodoUno">Parcial
                            1</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnPeriodoDos">Parcial 2 /
                            Desempeño</button>
                    </div>
                </div>
            </div>
            <div id="tablaPeriodoUno" class="col-lg-12 table-responsive">
                <form action="{{ route('periodouno.storeBloque') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <div class="docente-cal-savebar">
                        <button type="submit" class="btn btn-primary btn-sm mb-2" data-loading-text="Guardando…"><i class="fas fa-save mr-1"></i> Guardar/Actualizar Parcial 1</button>
                    </div>
                    <div style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered text-center docente-cal-table" style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle sortable">#</th>
                                    <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th rowspan="2" class="text-center align-middle sortable"
                                            style="font-size: 14px">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6)
                                                    ...
                                                @endif
                                            </small>
                                        </th>
                                    @endforeach
                                    <th colspan="3" class="text-center sortable">Calificación</th>
                                    <th rowspan="2" class="align-middle sortable">Observaciones</th>
                                </tr>
                                <tr>
                                    <th class="align-middle sortable">Valoración del Curso</th>
                                    <th class="align-middle sortable">Calificación del Curso</th>
                                    <th class="align-middle sortable">Calificación para el Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    @php
                                        $esInhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
                                        $fotoAlumnoUrl =
                                            $alumno->user && $alumno->user->foto
                                                ? asset('img/estudiantes/' . $alumno->user->foto)
                                                : null;
                                        $nombreAlumno = $alumno->apellidos . ', ' . $alumno->nombres;
                                    @endphp

                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                        value="{{ $docente->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                        value="{{ $curso->id }}">
                                    <tr style="{{ $esInhabilitado ? 'background-color: #f8d7da;' : '' }}">
                                        <td
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b; font-weight: bold">
                                            {{ $index + 1 }}
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b; width:200px ">
                                            <div class="alumno-identidad">
                                                @if ($fotoAlumnoUrl)
                                                    <button type="button" class="alumno-foto-btn"
                                                        onclick='openAlumnoFotoCalif(@json($fotoAlumnoUrl), @json($nombreAlumno))'
                                                        title="Ver foto de {{ $nombreAlumno }}">
                                                        <img src="{{ $fotoAlumnoUrl }}" alt="Foto de {{ $nombreAlumno }}"
                                                            class="alumno-foto-thumb">
                                                    </button>
                                                @else
                                                    <span class="alumno-foto-placeholder" title="Sin foto">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="alumno-identidad-nombre">{{ $nombreAlumno }}</div>
                                                    <div class="alumno-identidad-badges">
                                                        @if ($alumno->ciclo_id !== $curso->ciclo_id)
                                                            <span class="badge badge-info">Ciclo {{ $alumno->ciclo->nombre }}</span>
                                                        @endif
                                                        @if ($esInhabilitado)
                                                            <span class="badge badge-danger">{{ $alumno->user->perfil }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $calificacion = $alumno
                                                    ->periodos()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();

                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];

                                                $valoracionActual = old(
                                                    "alumnos.{$alumno->id}.valoracion_" . ($index + 1),
                                                    $calificacion->{'valoracion_' . ($index + 1)} ?? null,
                                                );
                                            @endphp

                                            <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                                value="{{ $competencia->id }}">
                                            <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                                <select class="form-control form-control-sm select-competencia"
                                                    style="font-size: 0.95em"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_{{ $index + 1 }}]"
                                                    {{ $esInhabilitado ? 'disabled' : '' }}>
                                                    <option value="0" selected>Seleccionar</option>
                                                    @foreach ($valoracionTexto as $valor => $texto)
                                                        <option value="{{ $valor }}"
                                                            {{ (string) $valoracionActual === (string) $valor ? 'selected' : '' }}>
                                                            {{ $texto }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text"
                                                    class="form-control form-control-sm input-competencia"
                                                    name="alumnos[{{ $alumno->id }}][nota_{{ $competencia->id }}]"
                                                    value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach

                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                style="font-size: 0.95em"
                                                name="alumnos[{{ $alumno->id }}][valoracion_curso]"
                                                value="{{ old("alumnos.{$alumno->id}.valoracion_curso", $calificacion?->valoracion_curso) }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                style="font-size: 0.95em"
                                                name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                value="{{ old("alumnos.{$alumno->id}.calificacion_curso", $calificacion?->calificacion_curso) }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                style="font-size: 0.95em"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                value="{{ old("alumnos.{$alumno->id}.calificacion_sistema", $calificacion?->calificacion_sistema) }}"
                                                readonly>
                                        </td>
                                        <td style="width: 400px;vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <textarea name="alumnos[{{ $alumno->id }}][observaciones]" class="form-control form-control-sm text-start"
                                                rows="2" style="resize: vertical; width: 100%;" placeholder="Observaciones (Opcional)"
                                                {{ $esInhabilitado ? 'disabled' : '' }}>{{ old("alumnos.{$alumno->id}.observaciones", $calificacion?->observaciones) }}</textarea>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div id="tablaCalificaciones" class="col-lg-12 table-responsive">
                <form action="{{ route('guardarPeriodo2yDesempenoEnBloque') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <div class="docente-cal-savebar">
                        <button type="submit" class="btn btn-success btn-sm mb-2" data-loading-text="Guardando…"><i class="fas fa-save mr-1"></i> Guardar/Actualizar Parcial 2 y Desempeño</button>
                    </div>
                    <div style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered text-center docente-cal-table" style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle sortable">#</th>
                                    <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                    <th rowspan="2" class="text-center align-middle sortable" style="width: 90px">
                                        Periodo
                                    </th>
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th rowspan="2" class="text-center align-middle sortable"
                                            style="font-size: 14px">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6)
                                                    ...
                                                @endif
                                            </small>
                                        </th>
                                    @endforeach
                                    <th colspan="3" class="text-center sortable">Calificación</th>
                                    <th rowspan="2" class="align-middle sortable">Observaciones</th>
                                </tr>
                                <tr>
                                    <th class="align-middle sortable">Valoración del Curso</th>
                                    <th class="align-middle sortable">Calificación del Curso</th>
                                    <th class="align-middle sortable">Calificación para el Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    @php
                                        $esInhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
                                        $fotoAlumnoUrl =
                                            $alumno->user && $alumno->user->foto
                                                ? asset('img/estudiantes/' . $alumno->user->foto)
                                                : null;
                                        $nombreAlumno = $alumno->apellidos . ', ' . $alumno->nombres;
                                        $valoracionTextoMap = [
                                            5 => 'Destacado',
                                            4 => 'Logrado',
                                            3 => 'En Proceso',
                                            2 => 'Inicio',
                                            1 => 'Previo al Inicio',
                                        ];
                                        // Cada periodo vive en su propia tabla (periodos, periodo_dos, periodo_tres):
                                        // se leen y guardan por separado para que Parcial 2 y Desempeño nunca se mezclen.
                                        $periodo1Alumno = $alumno->periodos->where('curso_id', $curso->id)->first();
                                        $periodo2Alumno = $alumno->periododos()->where('curso_id', $curso->id)->first();
                                        $periodo3Alumno = $alumno->periodotres()->where('curso_id', $curso->id)->first();
                                    @endphp
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                        value="{{ $docente->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                        value="{{ $curso->id }}">
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                            value="{{ $competencia->id }}">
                                    @endforeach

                                    {{-- Fila de referencia: Parcial 1 (solo lectura) --}}
                                    <tr style="{{ $esInhabilitado ? 'background-color: #f8d7da;' : '' }}">
                                        <td rowspan="3"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b; font-weight: bold">
                                            {{ $index + 1 }}</td>
                                        <td rowspan="3"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; font-weight: bold">
                                            <div class="alumno-identidad">
                                                @if ($fotoAlumnoUrl)
                                                    <button type="button" class="alumno-foto-btn"
                                                        onclick='openAlumnoFotoCalif(@json($fotoAlumnoUrl), @json($nombreAlumno))'
                                                        title="Ver foto de {{ $nombreAlumno }}">
                                                        <img src="{{ $fotoAlumnoUrl }}" alt="Foto de {{ $nombreAlumno }}"
                                                            class="alumno-foto-thumb">
                                                    </button>
                                                @else
                                                    <span class="alumno-foto-placeholder" title="Sin foto">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="alumno-identidad-nombre">{{ $nombreAlumno }}</div>
                                                    <div class="alumno-identidad-badges">
                                                        @if ($alumno->ciclo_id !== $curso->ciclo_id)
                                                            <span class="badge badge-info">Ciclo {{ $alumno->ciclo->nombre }}</span>
                                                        @endif
                                                        @if ($esInhabilitado)
                                                            <span class="badge badge-danger">{{ $alumno->user->perfil }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold">Parcial 1:</p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                            @php
                                                $valoracionPeriodo1 = $periodo1Alumno?->{'valoracion_' . ($compIndex + 1)} ?? null;
                                                $textoPeriodo1 =
                                                    $valoracionPeriodo1 !== null
                                                        ? $valoracionTextoMap[$valoracionPeriodo1] ?? '-'
                                                        : '-';
                                            @endphp
                                            <td>
                                                @if ($textoPeriodo1 !== '-')
                                                    <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                        style="font-size: 13px">
                                                        {{ $textoPeriodo1 }}</p>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $periodo1Alumno?->valoracion_curso ?? '-' }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $periodo1Alumno?->calificacion_curso ?? '-' }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $periodo1Alumno?->calificacion_sistema ?? '-' }}
                                            </p>
                                        </td>
                                        <td style="width: 400px;vertical-align: middle;">
                                            @if ($periodo1Alumno)
                                                <textarea class="form-control form-control-sm text-start" readonly rows="2"
                                                    style="resize: vertical; width: 100%;" placeholder="Observaciones (Opcional)">{{ $periodo1Alumno->observaciones }}</textarea>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Fila editable: Parcial 2 (guarda en periodo_dos, namespace periodo2) --}}
                                    <tr style="{{ $esInhabilitado ? 'background-color: #f8d7da;' : '' }}">
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <p class="mt-1 text-info mb-0 font-weight-bold">Parcial 2:</p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                            @php
                                                $valoracionActualP2 = old(
                                                    "alumnos.{$alumno->id}.periodo2.valoracion_" . ($compIndex + 1),
                                                    $periodo2Alumno?->{'valoracion_' . ($compIndex + 1)} ?? null,
                                                );
                                            @endphp
                                            <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="alumnos[{{ $alumno->id }}][periodo2][valoracion_{{ $compIndex + 1 }}]"
                                                    {{ $esInhabilitado ? 'disabled' : '' }}>
                                                    <option value="0" selected>Seleccionar</option>
                                                    @foreach ($valoracionTextoMap as $valor => $texto)
                                                        <option value="{{ $valor }}"
                                                            {{ (string) $valoracionActualP2 === (string) $valor ? 'selected' : '' }}>
                                                            {{ $texto }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text"
                                                    class="form-control form-control-sm input-competencia"
                                                    name="alumnos[{{ $alumno->id }}][periodo2][nota_{{ $competencia->id }}]"
                                                    value="{{ $periodo2Alumno?->nota ?? '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][periodo2][valoracion_curso]"
                                                value="{{ old("alumnos.{$alumno->id}.periodo2.valoracion_curso", $periodo2Alumno?->valoracion_curso) }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][periodo2][calificacion_curso]"
                                                value="{{ old("alumnos.{$alumno->id}.periodo2.calificacion_curso", $periodo2Alumno?->calificacion_curso) }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="alumnos[{{ $alumno->id }}][periodo2][calificacion_sistema]"
                                                value="{{ old("alumnos.{$alumno->id}.periodo2.calificacion_sistema", $periodo2Alumno?->calificacion_sistema) }}"
                                                readonly>
                                        </td>
                                        <td style="width: 400px;vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <textarea name="alumnos[{{ $alumno->id }}][periodo2][observaciones]" class="form-control form-control-sm text-start"
                                                rows="2" style="resize: vertical; width: 100%;" placeholder="Observaciones (Opcional)"
                                                {{ $esInhabilitado ? 'disabled' : '' }}>{{ old("alumnos.{$alumno->id}.periodo2.observaciones", $periodo2Alumno?->observaciones) }}</textarea>
                                        </td>
                                    </tr>

                                    {{-- Fila editable: Desempeño / Periodo 3 (guarda en periodo_tres, namespace periodo3).
                                         Se bloquea hasta que Parcial 2 llegue al 50% (mismo umbral que antes desbloqueaba la pestaña). --}}
                                    <tr style="{{ $esInhabilitado ? 'background-color: #f8d7da;' : '' }}">
                                        <td style="border-bottom: 1px solid #39779b">
                                            <p class="mt-1 text-secondary mb-0 font-weight-bold">Desempeño:</p>
                                        </td>
                                        @if ($mostrarBotonDesempeno)
                                            @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                                @php
                                                    $valoracionActualP3 = old(
                                                        "alumnos.{$alumno->id}.periodo3.valoracion_" . ($compIndex + 1),
                                                        $periodo3Alumno?->{'valoracion_' . ($compIndex + 1)} ?? null,
                                                    );
                                                @endphp
                                                <td style="border-bottom: 1px solid #39779b">
                                                    <select class="form-control form-control-sm select-competencia"
                                                        name="alumnos[{{ $alumno->id }}][periodo3][valoracion_{{ $compIndex + 1 }}]"
                                                        {{ $esInhabilitado ? 'disabled' : '' }}>
                                                        <option value="0" selected>Seleccionar</option>
                                                        @foreach ($valoracionTextoMap as $valor => $texto)
                                                            <option value="{{ $valor }}"
                                                                {{ (string) $valoracionActualP3 === (string) $valor ? 'selected' : '' }}>
                                                                {{ $texto }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text"
                                                        class="form-control form-control-sm input-competencia"
                                                        name="alumnos[{{ $alumno->id }}][periodo3][nota_{{ $competencia->id }}]"
                                                        value="{{ $periodo3Alumno?->nota ?? '' }}" readonly
                                                        style="display: none">
                                                </td>
                                            @endforeach
                                            <td style="border-bottom: 1px solid #39779b">
                                                <input type="text"
                                                    class="form-control form-control-sm valoracion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][periodo3][valoracion_curso]"
                                                    value="{{ old("alumnos.{$alumno->id}.periodo3.valoracion_curso", $periodo3Alumno?->valoracion_curso) }}"
                                                    readonly>
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b">
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][periodo3][calificacion_curso]"
                                                    value="{{ old("alumnos.{$alumno->id}.periodo3.calificacion_curso", $periodo3Alumno?->calificacion_curso) }}"
                                                    readonly>
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b">
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-sistema text-center"
                                                    name="alumnos[{{ $alumno->id }}][periodo3][calificacion_sistema]"
                                                    value="{{ old("alumnos.{$alumno->id}.periodo3.calificacion_sistema", $periodo3Alumno?->calificacion_sistema) }}"
                                                    readonly>
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b"></td>
                                        @else
                                            <td colspan="{{ count($competenciasSeleccionadas) + 4 }}" class="text-center text-muted" style="border-bottom: 1px solid #39779b">
                                                <i class="fas fa-lock mr-1"></i> Se habilita al completar el 50% de Parcial 2
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="alumnoFotoModalCalif" class="alumno-foto-modal-overlay" onclick="closeAlumnoFotoCalif(event)">
        <div class="alumno-foto-modal-card" onclick="event.stopPropagation();">
            <div class="alumno-foto-modal-head">
                <strong id="alumnoFotoModalCalifNombre">Foto del estudiante</strong>
                <button type="button" class="alumno-foto-modal-close" onclick="closeAlumnoFotoCalif(event)"
                    aria-label="Cerrar">&times;</button>
            </div>
            <div class="alumno-foto-modal-body">
                <img id="alumnoFotoModalCalifImg" src="" alt="Foto del estudiante">
            </div>
        </div>
    </div>

    @include('docentes.partials.calificar-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxVeces = 3; // máximo de veces que se mostrará
            let contador = localStorage.getItem('popupCompetenciasVisto') || 0;

            if (contador < maxVeces) {
                abrirAviso(); // usa tu función real del modal
                localStorage.setItem('popupCompetenciasVisto', Number(contador) + 1);
            }
        });
    </script>
    <script>
        function openAlumnoFotoCalif(src, nombre) {
            var modal = document.getElementById('alumnoFotoModalCalif');
            var img = document.getElementById('alumnoFotoModalCalifImg');
            var lbl = document.getElementById('alumnoFotoModalCalifNombre');
            if (!modal || !img || !lbl) return;
            img.src = src || '';
            img.alt = nombre || 'Foto del estudiante';
            lbl.textContent = nombre || 'Foto del estudiante';
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeAlumnoFotoCalif(event) {
            if (event) event.stopPropagation();
            var modal = document.getElementById('alumnoFotoModalCalif');
            var img = document.getElementById('alumnoFotoModalCalifImg');
            if (!modal || !img) return;
            modal.classList.remove('is-open');
            img.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAlumnoFotoCalif();
        });
    </script>
    @include('docentes.partials.competencia-modal')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            activarBotonYMostrarTabla('btnPeriodoUno', 'tablaPeriodoUno');
        });

        function activarBotonYMostrarTabla(botonId, tablaId, animar) {
            const botones = ['btnPeriodoUno', 'btnPeriodoDos'];
            const tablas = ['tablaPeriodoUno', 'tablaCalificaciones'];
            if (animar && window.calificarCrossfade) {
                const mostrar = document.getElementById(tablaId);
                const ocultar = tablas.filter(id => id !== tablaId).map(id => document.getElementById(id)).filter(Boolean);
                window.calificarCrossfade(mostrar, ocultar);
            } else {
                tablas.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = (id === tablaId) ? 'block' : 'none';
                });
            }
            botones.forEach(id => {
                document.getElementById(id).classList.remove('active');
            });
            document.getElementById(botonId).classList.add('active');
        }
        document.getElementById('btnPeriodoUno').addEventListener('click', function() {
            activarBotonYMostrarTabla('btnPeriodoUno', 'tablaPeriodoUno', true);
        });
        document.getElementById('btnPeriodoDos').addEventListener('click', function() {
            activarBotonYMostrarTabla('btnPeriodoDos', 'tablaCalificaciones', true);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const competenciaSelects = document.querySelectorAll('.select-competencia');

            // Sincroniza el input oculto con el select: 0 cuando queda en "Seleccionar",
            // para que cuente como 0 en el promedio en vez de quedar excluido.
            function sincronizarInput(select) {
                const input = select.nextElementSibling;
                input.value = select.value !== "0" ? select.value : "0";
            }

            // Recalcula todas las filas al cargar la página (solo las que ya tienen
            // alguna competencia calificada; las filas totalmente sin calificar se
            // dejan como estaban para no mostrar un promedio "0" antes de tiempo).
            const filasProcesadas = new Set();
            competenciaSelects.forEach(select => {
                const row = select.closest('tr');
                if (filasProcesadas.has(row)) return;
                filasProcesadas.add(row);

                const selectsFila = row.querySelectorAll('.select-competencia');
                const tieneAlgunaSeleccion = Array.from(selectsFila).some(s => s.value !== "0");
                if (tieneAlgunaSeleccion) {
                    selectsFila.forEach(sincronizarInput);
                    recalcularValoracionCurso(row);
                }
            });

            competenciaSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    sincronizarInput(this);
                    recalcularValoracionCurso(row);
                });
            });

            function recalcularValoracionCurso(row) {
                const inputs = row.querySelectorAll('.input-competencia');
                let sum = 0;

                inputs.forEach(input => {
                    sum += input.value ? parseInt(input.value, 10) : 0;
                });

                const promedio = inputs.length > 0 ? (sum / inputs.length).toFixed(2) : 0;
                const valoracionInput = row.querySelector('.valoracion-curso');
                if (valoracionInput) {
                    valoracionInput.value = promedio;
                }
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
        });
    </script>
@endsection
