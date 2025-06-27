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
            <div class="col-lg-12">
                <div class="text-center mb-3">
                    <div class="btn-group" role="group" aria-label="Controles de Periodo">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnPeriodoUno">Parcial 1</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnPeriodoDos">Parcial 2</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnDesempeno">Desempeño</button>
                    </div>
                </div>
            </div>
            <div id="tablaPeriodoUno" class="col-lg-12 table-responsive">
                <form action="{{ route('periodouno.storeBloque') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm mb-2">Guardar/Actualizar Parcial 1</button>
                    </div>
                    <div style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered text-center" style="font-size: 13px">
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
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                        value="{{ $docente->id }}">
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                        value="{{ $curso->id }}">
                                    <tr>
                                        <td
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b; font-weight: bold">
                                            {{ $index + 1 }}
                                        </td>
                                        <td
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; font-weight: bold">
                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
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
                                            @endphp

                                            <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                                value="{{ $competencia->id }}">
                                            <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_{{ $index + 1 }}]">
                                                    <option value="0" selected>Seleccionar</option>
                                                    @foreach ($valoracionTexto as $valor => $texto)
                                                        <option value="{{ $valor }}"
                                                            {{ $calificacion && $calificacion->{'valoracion_' . ($index + 1)} == $valor ? 'selected' : '' }}>
                                                            {{ $texto }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control form-control-sm input-competencia"
                                                    name="alumnos[{{ $alumno->id }}][nota_{{ $competencia->id }}]"
                                                    value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach

                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][valoracion_curso]"
                                                value="{{ $calificacion ? $calificacion->valoracion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                value="{{ $calificacion ? $calificacion->calificacion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                value="{{ $calificacion ? $calificacion->calificacion_sistema : '' }}"
                                                readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <tr>
                                        <td rowspan="2"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b; font-weight: bold">
                                            {{ $index + 1 }}</td>
                                        <td rowspan="2"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; font-weight: bold">
                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold">Parcial 1:</p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $valoracionPeriodo1 =
                                                    $alumno->periodos->where('curso_id', $curso->id)->first()
                                                        ?->{'valoracion_' . ($index + 1)} ?? null;

                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];

                                                $textoPeriodo1 =
                                                    $valoracionPeriodo1 !== null
                                                        ? $valoracionTexto[$valoracionPeriodo1] ?? '-'
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
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->valoracion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_sistema ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <p class="mt-1 text-info mb-0 font-weight-bold">Parcial 2</p>
                                        </td>
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                            value="{{ $docente->id }}">
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                            value="{{ $curso->id }}">

                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $calificacion = $alumno
                                                    ->calificaciones()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();
                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];
                                            @endphp
                                            <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                                value="{{ $competencia->id }}">
                                            <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_{{ $index + 1 }}]">
                                                    <option value="0" selected>Seleccionar</option>
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
                                                    name="alumnos[{{ $alumno->id }}][nota_{{ $competencia->id }}]"
                                                    value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][valoracion_curso]"
                                                value="{{ $calificacion ? $calificacion->valoracion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                value="{{ $calificacion ? $calificacion->calificacion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                value="{{ $calificacion ? $calificacion->calificacion_sistema : '' }}"
                                                readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody> --}}
                        </table>
                    </div>
                </form>
            </div>
            <div id="tablaCalificaciones" class="col-lg-12 table-responsive">
                <form action="{{ route('guardarCalificacionesEnBloque') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-sm mb-2">Guardar/Actualizar Parcial 2</button>
                    </div>
                    <div style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered text-center" style="font-size: 13px">
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
                                </tr>
                                <tr>
                                    <th class="align-middle sortable">Valoración del Curso</th>
                                    <th class="align-middle sortable">Calificación del Curso</th>
                                    <th class="align-middle sortable">Calificación para el Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <tr>
                                        <td rowspan="2"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b; font-weight: bold">
                                            {{ $index + 1 }}</td>
                                        <td rowspan="2"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; font-weight: bold">
                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold">Parcial 1:</p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $valoracionPeriodo1 =
                                                    $alumno->periodos->where('curso_id', $curso->id)->first()
                                                        ?->{'valoracion_' . ($index + 1)} ?? null;

                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];

                                                $textoPeriodo1 =
                                                    $valoracionPeriodo1 !== null
                                                        ? $valoracionTexto[$valoracionPeriodo1] ?? '-'
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
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->valoracion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_sistema ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <p class="mt-1 text-info mb-0 font-weight-bold">Parcial 2</p>
                                        </td>
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                            value="{{ $docente->id }}">
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                            value="{{ $curso->id }}">

                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $calificacion = $alumno
                                                    ->periododos()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();
                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];
                                            @endphp
                                            <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                                value="{{ $competencia->id }}">
                                            <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_{{ $index + 1 }}]">
                                                    <option value="0" selected>Seleccionar</option>
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
                                                    name="alumnos[{{ $alumno->id }}][nota_{{ $competencia->id }}]"
                                                    value="{{ $calificacion ? $calificacion->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm valoracion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][valoracion_curso]"
                                                value="{{ $calificacion ? $calificacion->valoracion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-curso text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                value="{{ $calificacion ? $calificacion->calificacion_curso : '' }}"
                                                readonly>
                                        </td>
                                        <td style="vertical-align: middle; border-bottom: 1px solid #39779b;">
                                            <input type="text"
                                                class="form-control form-control-sm calificacion-sistema text-center"
                                                name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                value="{{ $calificacion ? $calificacion->calificacion_sistema : '' }}"
                                                readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div id="tablaDesempeno" class="col-lg-12 table-responsive desempeno" style="display: none;">
                <form action="{{ route('guardarPeriodoTres') }}" method="POST">
                    @csrf
                    <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                    <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info btn-sm mb-2">Guardar/Actualizar Desempeño</button>
                    </div>
                    <div style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle sortable">#</th>
                                    <th rowspan="2" class="text-center align-middle sortable">Alumno</th>
                                    <th rowspan="2" class="text-center align-middle sortable">Periodo</th>
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
                                </tr>
                                <tr>
                                    <th class="align-middle sortable">Valoración del Curso</th>
                                    <th class="align-middle sortable">Calificación del Curso</th>
                                    <th class="align-middle sortable">Calificación para el Sistema</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    <input type="hidden" name="alumnos[{{ $alumno->id }}][alumno_id]"
                                        value="{{ $alumno->id }}">
                                    <tr>
                                        <td rowspan="3" class="font-weight-bold text-secondary"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b; border-left: 1px solid #39779b">
                                            {{ $index + 1 }}.</td>
                                        <td rowspan="3" class="font-weight-bold text-secondary"
                                            style="vertical-align: middle; border-bottom: 1px solid #39779b">
                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                        </td>
                                        <td>
                                            <p class="mt-1 text-primary mb-0 font-weight-bold" style="font-size: 14px">
                                                Parcial 1:
                                            </p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $valoracionPeriodo1 =
                                                    $alumno->periodos->where('curso_id', $curso->id)->first()
                                                        ?->{'valoracion_' . ($index + 1)} ?? null;

                                                $textoPeriodo1 =
                                                    $valoracionPeriodo1 !== null
                                                        ? $valoracionTexto[$valoracionPeriodo1] ?? '-'
                                                        : '-';
                                            @endphp
                                            <td>
                                                @if ($textoPeriodo1 !== '-')
                                                    <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                        style="font-size: 13px"> {{ $textoPeriodo1 }}</p>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->valoracion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_curso ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alumno->periodos->where('curso_id', $curso->id)->isNotEmpty())
                                                <p class="mt-1 text-primary mb-0 font-weight-bold"
                                                    style="font-size: 13px">
                                                    {{ $alumno->periodos->where('curso_id', $curso->id)->first()->calificacion_sistema ?? '-' }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="mt-1 text-info mb-0 font-weight-bold">
                                                Periodo 2:
                                            </p>
                                        </td>
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $calificacion = $alumno
                                                    ->periododos()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();

                                                // Definir las valoraciones disponibles
                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];
                                                $valoracion = $calificacion
                                                    ? $calificacion->{'valoracion_' . ($index + 1)}
                                                    : null;

                                                $textoValoracion = isset($valoracionTexto[$valoracion])
                                                    ? $valoracionTexto[$valoracion]
                                                    : 'Seleccionar';
                                            @endphp
                                            <td>
                                                {{-- <p class="mt-1 text-info mb-0 font-weight-bold" style="font-size: 13px">
                                                    {{ $textoValoracion }}
                                                </p> --}}
                                                @if ($textoValoracion !== '-' && $textoValoracion !== 'Seleccionar')
                                                    <p class="mt-1 text-info mb-0 font-weight-bold"
                                                        style="font-size: 13px">
                                                        {{ $textoValoracion }}
                                                    </p>
                                                @else
                                                    <p class="mt-1 text-center mb-0 font-weight-bold"
                                                        style="font-size: 13px">-</p>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td>
                                            <p class="mt-1 text-info mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $calificacion ? $calificacion->valoracion_curso : '' }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="mt-1 text-info mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $calificacion ? $calificacion->calificacion_curso : '' }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="mt-1 text-info mb-0 font-weight-bold" style="font-size: 13px">
                                                {{ $calificacion ? $calificacion->calificacion_sistema : '' }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #39779b">
                                            <p class="mt-1 text-secondary mb-0 font-weight-bold">Promedio final:</p>
                                        </td>
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][docente_id]"
                                            value="{{ $docente->id }}">
                                        <input type="hidden" name="alumnos[{{ $alumno->id }}][curso_id]"
                                            value="{{ $curso->id }}">
                                        @foreach ($competenciasSeleccionadas as $index => $competencia)
                                            @php
                                                $periodoTres = $alumno
                                                    ->periodotres()
                                                    ->where('curso_id', $curso->id)
                                                    ->first();

                                                $valoracionTexto = [
                                                    5 => 'Destacado',
                                                    4 => 'Logrado',
                                                    3 => 'En Proceso',
                                                    2 => 'Inicio',
                                                    1 => 'Previo al Inicio',
                                                ];
                                                $valoracionPeriodoTres = $periodoTres
                                                    ? $periodoTres->{'valoracion_' . ($index + 1)}
                                                    : null;
                                                // Definir el texto de la valoración
                                                $textoPeriodoTres = isset($valoracionTexto[$valoracionPeriodoTres])
                                                    ? $valoracionTexto[$valoracionPeriodoTres]
                                                    : 'Seleccionar';
                                            @endphp
                                            <input type="hidden" name="alumnos[{{ $alumno->id }}][competencias][]"
                                                value="{{ $competencia->id }}">
                                            <td style="border-bottom: 1px solid #39779b">
                                                <select class="form-control form-control-sm select-competencia"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_{{ $index + 1 }}]">
                                                    <option value="0" selected>Seleccionar</option>
                                                    <option value="5"
                                                        {{ $valoracionPeriodoTres == 5 ? 'selected' : '' }}>
                                                        Destacado</option>
                                                    <option value="4"
                                                        {{ $valoracionPeriodoTres == 4 ? 'selected' : '' }}>
                                                        Logrado</option>
                                                    <option value="3"
                                                        {{ $valoracionPeriodoTres == 3 ? 'selected' : '' }}>
                                                        En Proceso</option>
                                                    <option value="2"
                                                        {{ $valoracionPeriodoTres == 2 ? 'selected' : '' }}>
                                                        Inicio</option>
                                                    <option value="1"
                                                        {{ $valoracionPeriodoTres == 1 ? 'selected' : '' }}>
                                                        Previo al Inicio</option>
                                                </select>
                                                <input type="text"
                                                    class="form-control form-control-sm input-competencia"
                                                    name="alumnos[{{ $alumno->id }}][nota_{{ $competencia->id }}]"
                                                    value="{{ $periodoTres ? $periodoTres->nota : '' }}" readonly
                                                    style="display: none">
                                            </td>
                                        @endforeach
                                        <td style="border-bottom: 1px solid #39779b">
                                            @if ($periodoTres)
                                                <input type="text"
                                                    class="form-control form-control-sm valoracion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_curso]"
                                                    value="{{ $periodoTres->valoracion_curso }}" readonly>
                                            @else
                                                <input type="text"
                                                    class="form-control form-control-sm valoracion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][valoracion_curso]" value=""
                                                    readonly>
                                            @endif
                                        </td>
                                        <td style="border-bottom: 1px solid #39779b">
                                            @if ($periodoTres)
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                    value="{{ $periodoTres->calificacion_curso }}" readonly>
                                            @else
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-curso text-center"
                                                    name="alumnos[{{ $alumno->id }}][calificacion_curso]"
                                                    value="" readonly>
                                            @endif
                                        </td>
                                        <td style="border-bottom: 1px solid #39779b">
                                            @if ($periodoTres)
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-sistema text-center"
                                                    name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                    value="{{ $periodoTres->calificacion_sistema }}" readonly>
                                            @else
                                                <input type="text"
                                                    class="form-control form-control-sm calificacion-sistema text-center"
                                                    name="alumnos[{{ $alumno->id }}][calificacion_sistema]"
                                                    value="" readonly>
                                            @endif
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
            activarBotonYMostrarTabla('btnPeriodoUno', 'tablaPeriodoUno'); // Mostrar Periodo 1 al cargar
        });

        function activarBotonYMostrarTabla(botonId, tablaId) {
            const botones = ['btnPeriodoUno', 'btnPeriodoDos', 'btnDesempeno'];
            const tablas = ['tablaPeriodoUno', 'tablaCalificaciones', 'tablaDesempeno'];

            // Mostrar tabla activa
            tablas.forEach(id => {
                document.getElementById(id).style.display = (id === tablaId) ? 'block' : 'none';
            });

            // Quitar y agregar clase active a botones
            botones.forEach(id => {
                document.getElementById(id).classList.remove('active');
            });
            document.getElementById(botonId).classList.add('active');
        }

        document.getElementById('btnPeriodoUno').addEventListener('click', function() {
            activarBotonYMostrarTabla('btnPeriodoUno', 'tablaPeriodoUno');
        });

        document.getElementById('btnPeriodoDos').addEventListener('click', function() {
            activarBotonYMostrarTabla('btnPeriodoDos', 'tablaCalificaciones');
        });

        document.getElementById('btnDesempeno').addEventListener('click', function() {
            activarBotonYMostrarTabla('btnDesempeno', 'tablaDesempeno');
        });
    </script>

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarTabla('tablaPeriodoUno'); // Mostrar Periodo 1 al cargar
        });

        function mostrarTabla(idTabla) {
            const tablas = ['tablaPeriodoUno', 'tablaCalificaciones', 'tablaDesempeno'];
            tablas.forEach(id => {
                document.getElementById(id).style.display = (id === idTabla) ? 'block' : 'none';
            });
        }

        document.getElementById('btnPeriodoUno').addEventListener('click', function() {
            mostrarTabla('tablaPeriodoUno');
        });

        document.getElementById('btnPeriodoDos').addEventListener('click', function() {
            mostrarTabla('tablaCalificaciones');
        });

        document.getElementById('btnDesempeno').addEventListener('click', function() {
            mostrarTabla('tablaDesempeno');
        });
    </script> --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const competenciaSelects = document.querySelectorAll('.select-competencia');
            // Recalcula todas las filas al cargar la página
            competenciaSelects.forEach(select => {
                const row = select.closest('tr');
                const valor = select.value;
                // Si el valor es diferente de 0, se recalcula
                if (valor !== "0") {
                    const input = select.nextElementSibling;
                    input.value = valor;
                    recalcularValoracionCurso(row);
                }
            });
            competenciaSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const valor = this.value;

                    // Si el valor es diferente de 0, se recalcula
                    if (valor !== "0") {
                        const input = this.nextElementSibling;
                        input.value = valor;
                        recalcularValoracionCurso(row);
                    }
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
        });
    </script>
@endsection
