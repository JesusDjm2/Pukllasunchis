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
            <h4 style="font-size: 20px" class="font-weight-bold text-primary">{{ $curso->nombre }}<br> 
                <small class="text-secondary">({{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }}) <br>
                    <span class="text-primary">Docente: {{ $docente->nombre }} </span></small>
            </h4>
            {{-- <form action="{{ route('calificaciones.exportar', ['docenteId' => $docente->id, 'cursoId' => $curso->id]) }}"
                method="GET">
                @csrf
                @foreach ($competenciasSeleccionadas as $competencia)
                    <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                @endforeach
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-sm mb-2 mr-2">Exportar CSV <i class="fa fa-file-csv"></i>
                    </button>
                </div>
            </form> --}}
            @php
                $esPPD = str_contains($curso->ciclo->programa->nombre ?? '', 'PPD');
            @endphp
            @if ($esPPD)
                <form
                    action="{{ route('calificaciones.exportar.ppd', ['docenteId' => $docente->id, 'cursoId' => $curso->id]) }}"
                    method="GET">
                    @csrf
                    @foreach ($competenciasSeleccionadas as $competencia)
                        <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                    @endforeach
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm mb-2 mr-2">
                            Exportar CSV PPD <i class="fa fa-file-csv"></i>
                        </button>
                    </div>
                </form>
            @else
                <form
                    action="{{ route('calificaciones.exportar', ['docenteId' => $docente->id, 'cursoId' => $curso->id]) }}"
                    method="GET">
                    @csrf
                    @foreach ($competenciasSeleccionadas as $competencia)
                        <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                    @endforeach
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-sm mb-2 mr-2">Exportar CSV <i
                                class="fa fa-file-csv"></i>
                        </button>
                    </div>
                </form>
            @endif
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
                @if ($esPPD)
                    <div class="table-responsive p-2">
                        <table class="table table-bordered">
                            <thead class="text-white thead-dark">
                                <tr>
                                    <th rowspan="2" class="text-center" style="width: 40px;">#</th>
                                    <th rowspan="2" class="text-center" style="width: 250px;">Alumno</th>
                                    <th class="text-center" colspan="3" style="width: 500px;">
                                        Promedios Generales por Competencia
                                    </th>
                                    <th rowspan="2" class="text-center" style="font-size: 12px;">Nivel de Desempeño</th>
                                    <th rowspan="2" class="text-center" style="font-size: 12px;">Calificación Curso</th>
                                    <th rowspan="2" class="text-center" style="font-size: 12px;">Calificación Sistema
                                    </th>
                                </tr>
                            </thead>
                            <tbody style="color: #000">
                                @foreach ($alumnos as $index => $alumno)
                                    @php
                                        $calif = $curso->calificacionesppd->where('ppd_id', $alumno->id)->first();
                                        $competencias = $curso->competencias
                                            ->sortBy(function ($comp) {
                                                return intval(preg_replace('/\D/', '', $comp->nombre));
                                            })
                                            ->values();
                                    @endphp
                                    <tr>
                                        <td rowspan="2" class="text-center"
                                            style="border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                            {{ $index + 1 }}
                                        </td>
                                        <td rowspan="2"
                                            style="border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                            {{ $alumno->apellidos }} {{ $alumno->nombres }}
                                        </td>

                                        @foreach ($competencias as $compIndex => $competencia)
                                            @php
                                                preg_match('/\d+/', $competencia->nombre, $matches);
                                                $numero = $matches[0] ?? $compIndex + 1;
                                            @endphp
                                            <td class="text-center" style="font-size: 12px;">
                                                <strong>Comp. {{ $numero }}</strong><br>
                                                <span>{{ $calif?->{'comp' . $numero} }}</span>
                                            </td>
                                        @endforeach

                                        <td rowspan="2" class="text-center"
                                            style="font-size: 14px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                            {{ $calif?->nivel_desempeno ?? 'Sin calificar' }}
                                        </td>
                                        <td rowspan="2" class="text-center"
                                            style="font-size: 15px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                            {{ $calif?->calificacion_curso ?? 'Sin calificar' }}
                                        </td>
                                        <td rowspan="2" class="text-center"
                                            style="font-size: 15px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                            {{ $calif?->calificacion_sistema ?? 'Sin calificar' }}
                                        </td>

                                    </tr>

                                    <tr>
                                        @foreach ([1, 2, 3] as $c)
                                            @php
                                                $pp = $calif?->{"pp_c{$c}_4"};
                                                $pf = $calif?->{"pf_c{$c}_3"};
                                                $pp_int = is_numeric($pp) ? round($pp) : null;
                                                $pf_int = is_numeric($pf) ? round($pf) : null;
                                                $val =
                                                    is_numeric($pp) && is_numeric($pf)
                                                        ? round($pp * 0.4 + $pf * 0.6)
                                                        : '-';
                                            @endphp
                                            <td style="font-size: 11px; border-bottom: 1px solid #b47e37;">
                                                <div
                                                    style="display: flex; justify-content: space-between; gap: 6px; width: 100%;">
                                                    <span
                                                        style="flex: 1; text-align: center; border-right: 1px solid #8f8f8f54; padding-right: 0.6em;">
                                                        <strong>40%</strong><br> {{ $pp_int ?? '-' }}
                                                    </span>
                                                    <span
                                                        style="flex: 1; text-align: center; border-right: 1px solid #8f8f8f54; padding-right: 0.6em;">
                                                        <strong>60%</strong><br> {{ $pf_int ?? '-' }}
                                                    </span>
                                                    <span style="flex: 1; text-align: center;">
                                                        <strong>Prom.</strong><br> {{ $val }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <table class="table table-hover table-bordered text-center" id="tablaCalificaciones"
                        style="font-size: 13px">
                        <thead class="thead-dark">
                            <tr>
                                <th class="sortable">#</th>
                                <th class="sortable">Apellidos, Nombres</th>
                                <th class="sortable">Periodo</th>
                                @foreach ($competenciasSeleccionadas as $index => $competencia)
                                    <th class="sortable">{{ $competencia->nombre }}</th>
                                @endforeach

                                <th class="sortable">Valoración Curso</th>
                                <th class="sortable">Calificación Curso</th>
                                <th class="sortable">Calificación Sistema</th>
                            </tr>
                        </thead>
                        <tbody>

                            {{-- --}}

                            @if ($alumnos->some(fn($alumno) => $alumno->periodos->isNotEmpty()))
                                @php $alumnoIndex = 1; @endphp
                                @foreach ($alumnos as $alumno)
                                    @php
                                        $periodos = [
                                            [
                                                'label' => 'Parcial 1',
                                                'data' => $alumno->periodos->firstWhere('curso_id', $curso->id),
                                                'class' => 'text-primary',
                                            ],
                                            [
                                                'label' => 'Parcial 2',
                                                'data' => $alumno->periododos->firstWhere('curso_id', $curso->id),
                                                'class' => 'text-info',
                                            ],
                                            [
                                                'label' => 'Promedio',
                                                'data' => $alumno->periodotres->firstWhere('curso_id', $curso->id),
                                                'class' => 'text-success',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($periodos as $i => $p)
                                        <tr>
                                            @if ($i === 0)
                                                <td rowspan="3" class="align-middle text-center font-weight-bold"
                                                    style="border-left: #165874 1px solid; border-bottom: #165874 1px solid">
                                                    {{ $alumnoIndex++ }}
                                                </td>
                                                <td rowspan="3" class="align-middle text-center font-weight-bold"
                                                    style="border-left: #165874 1px solid; border-bottom: #165874 1px solid">
                                                    {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                    @if ($alumno->ciclo_id !== $curso->ciclo_id)
                                                        <span class="badge badge-info">Ciclo
                                                            {{ $alumno->ciclo->nombre }}</span>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="{{ $p['class'] }} font-weight-bold"
                                                @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                {{ $p['label'] }}
                                            </td>

                                            @if ($p['data'])
                                                @foreach ($competenciasSeleccionadas as $compIndex => $competencia)
                                                    @php
                                                        $valoracionKey = 'valoracion_' . ($compIndex + 1);
                                                        $valoracion = $p['data']->$valoracionKey ?? null;
                                                        $etiquetas = [
                                                            1 => 'Previo al Inicio',
                                                            2 => 'Inicio',
                                                            3 => 'En Proceso',
                                                            4 => 'Logrado',
                                                            5 => 'Destacado',
                                                        ];
                                                    @endphp
                                                    <td
                                                        @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                        {{ $etiquetas[$valoracion] ?? '-' }}
                                                    </td>
                                                @endforeach
                                                <td
                                                    @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                    {{ $p['data']->valoracion_curso ?? 'Sin calificar' }}
                                                </td>
                                                <td
                                                    @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                    {{ $p['data']->calificacion_curso ?? 'Sin calificar' }}
                                                </td>
                                                <td
                                                    @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                    {{ $p['data']->calificacion_sistema ?? 'Sin calificar' }}
                                                </td>
                                            @else
                                                @for ($j = 0; $j < count($competenciasSeleccionadas) + 3; $j++)
                                                    <td
                                                        @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                        -</td>
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">No hay periodos publicados.</td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                @endif
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
