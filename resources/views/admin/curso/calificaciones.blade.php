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
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Estilo para datos en solo lectura */
        .readonly-data {
            background-color: #f8f9fa;
        }
        
        .data-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .data-value {
            font-weight: 500;
            margin-bottom: 0;
        }
    </style>
    
    <div class="container-fluid bg-light"> 
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 style="font-size: 20px" class="font-weight-bold text-primary">{{ $curso->nombre }}<br> 
                <small class="text-secondary">({{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }}) <br>
                    <span class="text-primary">Docente: {{ $docente->nombre }} </span></small>
            </h4>
            
            {{-- Admin NO ve botones de exportar --}}
            
           {{--  <a href="{{ route('admin.cursos.index') }}" 
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right mb-3">
                Volver
            </a> --}}
        </div>
        
        <!-- Modal de competencias (se mantiene porque es solo información) -->
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
                
                {{-- Mensaje informativo para admin --}}
                <div class="alert alert-info text-center mb-3">
                    <i class="fa fa-info-circle"></i> Vista de solo lectura - Datos de calificaciones del curso
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                @php
                    $esPPD = str_contains($curso->ciclo->programa->nombre ?? '', 'PPD');
                @endphp
                
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
                                    <th rowspan="2" class="text-center" style="font-size: 12px;">Calificación Sistema</th>
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
                                    <tr class="{{ $calif ? '' : 'table-secondary' }}">
                                        <td rowspan="2" class="text-center align-middle"
                                            style="border-bottom: 1px solid #b47e37;">
                                            {{ $index + 1 }}
                                        </td>
                                        <td rowspan="2" class="align-middle"
                                            style="border-bottom: 1px solid #b47e37;">
                                            {{ $alumno->apellidos }} {{ $alumno->nombres }}
                                            @if($alumno->es_inhabilitado)
                                                <span class="badge badge-danger">Inhabilitado</span>
                                            @endif
                                        </td>

                                        @foreach ($competencias as $compIndex => $competencia)
                                            @php
                                                preg_match('/\d+/', $competencia->nombre, $matches);
                                                $numero = $matches[0] ?? $compIndex + 1;
                                                $valor = $calif?->{'comp' . $numero};
                                            @endphp
                                            <td class="text-center align-middle" style="font-size: 12px;">
                                                <strong>Comp. {{ $numero }}</strong><br>
                                                <span class="{{ $valor ? 'font-weight-bold' : 'text-muted' }}">
                                                    {{ $valor ?? '—' }}
                                                </span>
                                            </td>
                                        @endforeach

                                        <td rowspan="2" class="text-center align-middle"
                                            style="font-size: 14px; border-bottom: 1px solid #b47e37;">
                                            <span class="{{ $calif?->nivel_desempeno ? 'badge badge-info' : 'text-muted' }}">
                                                {{ $calif?->nivel_desempeno ?? 'Sin calificar' }}
                                            </span>
                                        </td>
                                        <td rowspan="2" class="text-center align-middle"
                                            style="font-size: 15px; border-bottom: 1px solid #b47e37;">
                                            <span class="{{ $calif?->calificacion_curso ? 'font-weight-bold' : 'text-muted' }}">
                                                {{ $calif?->calificacion_curso ?? '—' }}
                                            </span>
                                        </td>
                                        <td rowspan="2" class="text-center align-middle"
                                            style="font-size: 15px; border-bottom: 1px solid #b47e37;">
                                            <span class="{{ $calif?->calificacion_sistema ? 'font-weight-bold' : 'text-muted' }}">
                                                {{ $calif?->calificacion_sistema ?? '—' }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr class="{{ $calif ? '' : 'table-secondary' }}">
                                        @foreach ([1, 2, 3] as $c)
                                            @php
                                                $pp = $calif?->{"pp_c{$c}_4"};
                                                $pf = $calif?->{"pf_c{$c}_3"};
                                                $pp_int = is_numeric($pp) ? round($pp) : null;
                                                $pf_int = is_numeric($pf) ? round($pf) : null;
                                                $val = is_numeric($pp) && is_numeric($pf) ? round($pp * 0.4 + $pf * 0.6) : '—';
                                            @endphp
                                            <td style="font-size: 11px; border-bottom: 1px solid #b47e37;">
                                                <div class="d-flex justify-content-between small">
                                                    <div class="text-center px-1 border-right">
                                                        <div class="data-label">40%</div>
                                                        <div class="data-value">{{ $pp_int ?? '—' }}</div>
                                                    </div>
                                                    <div class="text-center px-1 border-right">
                                                        <div class="data-label">60%</div>
                                                        <div class="data-value">{{ $pf_int ?? '—' }}</div>
                                                    </div>
                                                    <div class="text-center px-1">
                                                        <div class="data-label">Prom.</div>
                                                        <div class="data-value font-weight-bold">{{ $val }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center" style="font-size: 13px">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Apellidos, Nombres</th>
                                    <th>Periodo</th>
                                    @foreach ($competenciasSeleccionadas as $index => $competencia)
                                        <th>{{ $competencia->nombre }}</th>
                                    @endforeach
                                    <th>Valoración Curso</th>
                                    <th>Calificación Curso</th>
                                    <th>Calificación Sistema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($alumnos->some(fn($alumno) => $alumno->periodos->isNotEmpty() || $alumno->periododos->isNotEmpty() || $alumno->periodotres->isNotEmpty()))
                                    @php $alumnoIndex = 1; @endphp
                                    @foreach ($alumnos as $alumno)
                                        @php
                                            $periodos = [
                                                [
                                                    'label' => 'Parcial 1',
                                                    'data' => $alumno->periodos->firstWhere('curso_id', $curso->id),
                                                    'class' => 'text-primary',
                                                    'bg' => 'bg-light'
                                                ],
                                                [
                                                    'label' => 'Parcial 2',
                                                    'data' => $alumno->periododos->firstWhere('curso_id', $curso->id),
                                                    'class' => 'text-info',
                                                    'bg' => 'bg-white'
                                                ],
                                                [
                                                    'label' => 'Promedio',
                                                    'data' => $alumno->periodotres->firstWhere('curso_id', $curso->id),
                                                    'class' => 'text-success font-weight-bold',
                                                    'bg' => 'bg-light'
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($periodos as $i => $p)
                                            <tr class="{{ $p['bg'] }}">
                                                @if ($i === 0)
                                                    <td rowspan="3" class="align-middle text-center font-weight-bold"
                                                        style="border-left: #165874 1px solid; border-bottom: #165874 1px solid">
                                                        {{ $alumnoIndex++ }}
                                                    </td>
                                                    <td rowspan="3" class="align-middle text-center font-weight-bold"
                                                        style="border-left: #165874 1px solid; border-bottom: #165874 1px solid">
                                                        {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        @if ($alumno->ciclo_id !== $curso->ciclo_id)
                                                            <span class="badge badge-info">Ciclo {{ $alumno->ciclo->nombre }}</span>
                                                        @endif
                                                        @if($alumno->es_inhabilitado)
                                                            <span class="badge badge-danger">Inhabilitado</span>
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
                                                        <td @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                            @if($valoracion)
                                                                <span class="badge badge-pill {{ 
                                                                    $valoracion == 5 ? 'badge-success' : 
                                                                    ($valoracion == 4 ? 'badge-primary' : 
                                                                    ($valoracion == 3 ? 'badge-warning' : 
                                                                    ($valoracion == 2 ? 'badge-secondary' : 'badge-dark'))) 
                                                                }}">
                                                                    {{ $etiquetas[$valoracion] }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                        <span class="{{ $p['data']->valoracion_curso ? 'font-weight-bold' : 'text-muted' }}">
                                                            {{ $p['data']->valoracion_curso ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                        <span class="{{ $p['data']->calificacion_curso ? 'font-weight-bold' : 'text-muted' }}">
                                                            {{ $p['data']->calificacion_curso ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                        <span class="{{ $p['data']->calificacion_sistema ? 'font-weight-bold' : 'text-muted' }}">
                                                            {{ $p['data']->calificacion_sistema ?? '—' }}
                                                        </span>
                                                    </td>
                                                @else
                                                    @for ($j = 0; $j < count($competenciasSeleccionadas) + 3; $j++)
                                                        <td @if ($i === 2) style="border-bottom: #165874 1px solid" @endif>
                                                            <span class="text-muted">—</span>
                                                        </td>
                                                    @endfor
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fa fa-info-circle"></i> No hay periodos publicados para este curso
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Modal de competencias -->
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
            var nombre = element.getAttribute('data-nombre');
            var descripcion = element.getAttribute('data-descripcion');
            var capacidades = element.getAttribute('data-capacidades');

            document.getElementById("competenciaNombre").innerText = nombre;
            document.getElementById("competenciaDescripcion").innerText = descripcion;
            document.getElementById("competenciaCapacidades").innerHTML = capacidades;
            document.getElementById("competenciaModal").style.display = "block";
        }

        function closeModal(event) {
            if (event) event.stopPropagation();
            document.getElementById("competenciaModal").style.display = "none";
        }
        
        // Cerrar modal con tecla ESC
        window.onclick = function(event) {
            if (event.target == document.getElementById("competenciaModal")) {
                document.getElementById("competenciaModal").style.display = "none";
            }
        }
    </script>
@endsection