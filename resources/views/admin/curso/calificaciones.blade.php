@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
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
            <div class="d-flex align-items-center" style="gap:8px;">
                @php $esPPDHeader = str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'); @endphp
                <form action="{{ $esPPDHeader
                        ? route('calificaciones.exportar.ppd', [$docente->id, $curso->id])
                        : route('calificaciones.exportar', [$docente->id, $curso->id]) }}"
                    method="GET">
                    @foreach ($competenciasSeleccionadas as $comp)
                        <input type="hidden" name="competencias[]" value="{{ $comp->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-success shadow-sm">
                        <i class="fa fa-file-excel fa-sm mr-1"></i> Exportar Excel
                    </button>
                </form>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver
                </a>
            </div>
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
                    <div style="max-height: 800px; overflow-x: auto;">
                        <table class="table table-hover table-bordered text-center text-dark"
                            style="font-size: 13px; min-width: 4000px;">
                            <thead style="color: #000">
                                <tr>
                                    <th rowspan="3" class="text-center align-middle sortable bg-dark text-white">#</th>
                                    <th rowspan="3" class="text-center align-middle sortable bg-dark text-white">Alumno</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
                                        class="text-center align-middle" style="background-color: #e5973a">
                                        Productos de Proceso 40%</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
                                        class="text-center align-middle" style="background-color: #ffd39f">
                                        Producto Final 60%</th>
                                    <th colspan="{{ count($competenciasSeleccionadas) * 3 }}"
                                        class="text-center align-middle bg-success text-white">
                                        Promedios Generales por competencia</th>
                                    <th rowspan="3" class="align-middle bg-warning">Nivel de desempeño</th>
                                    <th rowspan="3" class="align-middle bg-warning">Calificación del Curso</th>
                                    <th rowspan="3" class="align-middle bg-warning">Calificación en el Sistema Superior</th>
                                    <th rowspan="3" class="align-middle text-white bg-dark">Observaciones</th>
                                </tr>
                                <tr style="pointer-events: none">
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="3" class="text-center" style="background-color: #e5973a">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6) ... @endif
                                            </small>
                                        </th>
                                    @endforeach
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="3" class="text-center" style="background: #ffd39f">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6) ... @endif
                                            </small>
                                        </th>
                                    @endforeach
                                    @foreach ($competenciasSeleccionadas as $competencia)
                                        <th colspan="3" class="text-center bg-success text-white">
                                            {{ $competencia->nombre }}<br>
                                            <small style="font-size: 10px">
                                                {{ implode(' ', array_slice(explode(' ', $competencia->descripcion), 0, 12)) }}
                                                @if (str_word_count($competencia->descripcion) > 6) ... @endif
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
                                        <th class="text-center bg-success text-white">Proceso 40%</th>
                                        <th class="text-center bg-success text-white">Final 60%</th>
                                        <th class="text-center bg-success text-white">Valoración</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    @php
                                        $calif = $curso->calificacionesppd->where('ppd_id', $alumno->alumnoB?->id)->first();
                                    @endphp
                                    <tr class="{{ $alumno->es_inhabilitado ? 'table-secondary' : '' }}">
                                        <td class="align-middle text-center">{{ $index + 1 }}</td>
                                        <td class="align-middle text-left">
                                            <div>{{ $alumno->apellidos }}, {{ $alumno->name }}</div>
                                            <div class="mt-1">
                                                @if ($alumno->es_inhabilitado)
                                                    <span class="badge badge-danger">Inhabilitado</span>
                                                @endif
                                                @unless ($alumno->tiene_ppd)
                                                    <span class="badge badge-warning text-dark">Sin matrícula</span>
                                                @endunless
                                            </div>
                                        </td>
                                        {{-- Proceso --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach ([1, 2, 4] as $i)
                                                @php $campo = "pp_c{$c}_{$i}"; @endphp
                                                <td class="align-middle">
                                                    <span class="{{ $calif?->$campo !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                        {{ $calif?->$campo ?? '—' }}
                                                    </span>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Final --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach ([1, 2, 3] as $i)
                                                @php $campo = "pf_c{$c}_{$i}"; @endphp
                                                <td class="align-middle">
                                                    <span class="{{ $calif?->$campo !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                        {{ $calif?->$campo ?? '—' }}
                                                    </span>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Promedios --}}
                                        @foreach ([1, 2, 3] as $c)
                                            @foreach ([1, 2, 3] as $i)
                                                @php $campo = "pg_c{$c}_{$i}"; @endphp
                                                <td class="align-middle">
                                                    <span class="{{ $calif?->$campo !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                        {{ $calif?->$campo ?? '—' }}
                                                    </span>
                                                </td>
                                            @endforeach
                                        @endforeach
                                        {{-- Nivel desempeño --}}
                                        <td class="align-middle">
                                            <span class="{{ $calif?->nivel_desempeno !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                {{ $calif?->nivel_desempeno ?? '—' }}
                                            </span>
                                        </td>
                                        {{-- Calificación curso --}}
                                        <td class="align-middle">
                                            <span class="{{ $calif?->calificacion_curso !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                {{ $calif?->calificacion_curso ?? '—' }}
                                            </span>
                                        </td>
                                        {{-- Calificación sistema --}}
                                        <td class="align-middle">
                                            <span class="{{ $calif?->calificacion_sistema !== null ? 'font-weight-bold' : 'text-muted' }}">
                                                {{ $calif?->calificacion_sistema ?? '—' }}
                                            </span>
                                        </td>
                                        {{-- Observaciones --}}
                                        <td class="align-middle text-left" style="min-width: 200px;">
                                            <span class="text-muted" style="font-size: 12px;">
                                                {{ $calif?->observaciones ?? '—' }}
                                            </span>
                                        </td>
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
                                @if ($alumnos->isEmpty())
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fa fa-users"></i> No hay alumnos registrados para este curso
                                        </td>
                                    </tr>
                                @else
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