@extends('layouts.docente')
@section('titulo', 'Datos del Sílabo')
@section('contenido')
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400&display=swap" rel="stylesheet">
    <style>
        h2,
        h4,
        h5 {
            font-size: 20px;
            color: #c78d40;
            font-weight: 600;
        }

        h3 {
            font-size: 16px;
            color: #c78d40;
            font-weight: 600;
        }

        .print-btn {
            position: fixed;
            bottom: 30%;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px 20px;
            font-size: 16px;
            border-radius: 50%;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: background 0.3s;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }

        @media print {
            header,
            footer,
            .sidebar,
            .navbar,
            .boton-volver,
            .print-btn,
            .floating-bubble-editor {
                display: none !important;
            }
            thead {
                background-color: #c78d40 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: #858796 !important;
            }

            thead>tr>th {
                color: #c78d40 !important;
            }           

            @page {
                margin: 20mm;
                /* Ajusta márgenes para evitar espacio extra */
            }

            a[href]:after {
                content: none !important;
                /* Oculta los enlaces */
            }

            header,
            footer {
                display: none !important;
            }

            h4,
            {
            font-family: 'EB Garamond', serif !important;
            font-size: 20px !important;
            font-weight: normal !important;
            color: #ffa537 !important;
        }

        body,
        table,
        p,
        span,
        h1,
        h3,
        h5,
        h6 {
            font-family: 'EB Garamond', serif !important;
            font-size: 14px !important;
            font-weight: normal !important;
        }

        h3 {
            font-size: 16px !important;
            color: #ffa537 !important;
        }


        h2 {
            font-family: 'EB Garamond', serif !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            color: #ffa537 !important;
        }
        }
    </style>

    <div class="container-fluid bg-white">
        <div class="container">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1em;">
                <tr>
                    <td style="width: 30%; vertical-align: middle; text-align: left;">
                        <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Logo IESP Pukllasunchis"
                            class="img-fluid" style="max-width: 200px;">
                    </td>
                    <td style="width: 70%; vertical-align: middle; text-align: right">
                        <h5 class="font-weight-bold mb-1">
                            {{ $curso->ciclo->programa->nombre }} - Ciclo: {{ $curso->ciclo->nombre }}
                        </h5>
                        <h4 class="font-weight-bold cursoHead" id="cursoHead">
                            {{ $curso->nombre }}
                        </h4>
                    </td>
                </tr>
            </table>

            <div style="width: 100%; height: 2px; border-top: 1px dashed #c78d40;"></div>
            <!---Boton flotante de impresión------->
            <button id="printButton" onclick="window.print()" class="btn btn-primary print-btn">
                <i class="fa fa-file-export"></i>
            </button>

            <!-- Información del Curso -->
            <div class="mb-4 mt-2">
                <h2 class="mt-3">I. Datos Generales:</h2>
                <table class="table table-borderless" style="margin-bottom: 0; font-size: 14px; margin-left: 1em;">
                    <tbody>
                        <tr>
                            <td style="font-weight: 600; width: 40%; padding: 2px;">1.1 <span
                                    style="margin-left:1em">Programa
                                    de Estudios</span></td>
                            <td style="padding: 2px;">: {{ $curso->ciclo->programa->nombre }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.2 <span style="margin-left:1em">Componente
                                    Curricular</span></td>
                            <td style="padding: 2px;">: {{ $curso->cc }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.3 <span style="margin-left:1em">Curso o
                                    módulo</span>
                            </td>
                            <td style="padding: 2px;">: {{ $curso->nombre }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.4 <span
                                    style="margin-left:1em">Competencias</span>
                            </td>
                            <td style="padding: 2px;">:
                                @foreach ($competencias as $competencia)
                                    @php
                                        preg_match('/\d+/', $competencia->nombre, $matches);
                                    @endphp
                                    {{ $matches[0] ?? '' }}@if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.5 <span style="margin-left:1em">Semestre
                                    Académico</span></td>
                            <td style="padding: 1px;">: 2025 - I</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.6 <span style="margin-left:1em">Créditos</span>
                            </td>
                            <td style="padding: 2px;">: {{ $curso->creditos }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.7 <span style="margin-left:1em">Horas del
                                    ciclo</span>
                            </td>
                            <td style="padding: 2px;">: {{ $curso->horas }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.8 <span style="margin-left:1em">Horas
                                    Semanales</span>
                            </td>
                            <td style="padding: 2px;">: {{ $curso->horas }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.9 <span style="margin-left:1em">Docente(s)</span>
                            </td>
                            <td style="padding: 2px;">:
                                @foreach ($curso->docentes as $docente)
                                    {{ $docente->nombre }}@if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.10 <span style="margin-left:0.4em">Correo
                                    Institucional</span></td>
                            <td style="padding: 2px;">:
                                {{ $curso->docentes->first()->email ?? 'Sin datos' }}
                            </td>
                        </tr>

                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.11 <span style="margin-left:0.4em">Fecha de
                                    inicio</span></td>
                            <td style="padding: 2px;">:
                                <span>
                                    {{ $silabo->fecha1 ? $silabo->fecha1 : 'Sin datos' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: 2px;">1.12 <span style="margin-left:0.4em">Fecha de
                                    término</span></td>
                            <td style="padding: 2px;">:
                                <span>
                                    {{ $silabo->fecha2 ? $silabo->fecha2 : 'Sin datos' }}
                                </span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="col-lg-12 mb-1">
                <h2 class="mt-3">II. Sumilla:</h2>
                <div class="p-2" style="text-align: justify">
                    {!! $silabo->sumilla ?? 'Sin datos' !!}
                </div>
            </div>

            <div class="col-lg-12 mb-1">
                <h2 class="mt-2">III. Vinculación al Proyecto Integrador:</h2>
            </div>
            <table class="table">
                <tbody>
                    <tr>
                        <td style="border:none; color: #c78d40"><strong>Proyecto Integrador:</strong></td>
                        <td style="border:none">
                            <span class="p-2">
                                {{ $silabo->proyecto_integrador ?? 'Sin datos' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none; color: #c78d40"><strong>Producto del Proyecto Integrador:</strong></td>
                        <td style="border:none">
                            <div>
                                {{ $curso->ciclo->proyecto->producto ?? 'Sin proyecto asignado' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="col-lg-12 mb-3">
                <label for="descripcion_proyecto" style="color: #c78d40"><strong>Propósito del Proyecto
                        Integrador:</strong></label>
                <div style="text-align: justify">
                    {!! $silabo->descripcion_proyecto_integrador ?? 'Sin datos' !!}
                </div>
            </div>

            <div class="col-lg-12 mb-3">
                <label for="vinculacion_pi" style="color: #c78d40"><strong>Vinculación o aporte del curso con el proyecto
                        integrador:</strong></label>
                <div>
                    {!! $silabo->vinculacion_pi ?? 'Sin datos' !!}
                </div>
            </div>

            <div class="col-lg-12 mb-3">
                <label for="producto_curso" style="color: #c78d40"><strong>Producto del Curso:</strong></label>
                <div>
                    {!! $silabo->producto_curso ?? 'Sin datos' !!}
                </div>
            </div>

            <div class="col-lg-12 mb-3">
                <h2 class="mt-5">IV. Enfoques Transversales:</h2>
            </div>
            <table class="table table-bordered">
                <thead style="color: #c78d40">
                    <tr>
                        <th style="width: 35%">Enfoque</th>
                        <th>¿Cuándo son observables en la EESP?</th>
                        <th>¿En qué acciones concretas se observa?</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($silabo->enfoques as $enfoque)
                        <tr>
                            <td style="text-align: justify"><strong style="color: #c78d40">{{ $enfoque->nombre }}</strong><br>
                                {{ $enfoque->descripcion }}
                            </td>
                            <td style="text-align: justify">{{ $enfoque->enfoque_observables }}</td>
                            <td style="text-align: justify">{{ $enfoque->silabo_concretas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No hay enfoques registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="col-lg-12 mb-3">
                <h2 class="mt-5"> V. Matriz de planificación y evaluación de aprendizaje:</h2>
            </div>

            <div class="col-lg-12 mb-4">
                @foreach ($competencias as $index => $competencia)
                    @php
                        $num = $index + 1;
                    @endphp
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="align-middle">
                                    <h3>{{ $competencia->nombre }}:</h3>
                                    <br>
                                    <p style="text-align: justify;">{{ $competencia->descripcion }}</p>
                                </td>
                                <td>
                                    @if ($competencia->estandares->isNotEmpty())
                                        @foreach ($competencia->estandares as $estandar)
                                            <p style="text-align: justify" class="p-2">
                                                {{ $estandar->descripcion }}
                                            </p>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No hay estándares asociados</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-bordered" style="margin-top: -15px">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th style="width: 20%;">Capacidades</th>
                                <th style="width: 20%;">Desempeños Específicos</th>
                                <th style="width: 20%;">Criterios de Evaluación</th>
                                <th style="width: 20%;">Evidencias</th>
                                <th style="width: 20%;">Instrumento/Fuente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $silabo->{'capacidad' . $num} ?? '--' }}</td>
                                <td>{{ $silabo->{'desempeno' . $num} ?? '--' }}</td>
                                <td>{{ $silabo->{'criterio' . $num} ?? '--' }}</td>
                                <td>{{ $silabo->{'evidencia' . $num} ?? '--' }}</td>
                                <td>{{ $silabo->{'instrumento' . $num} ?? '--' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>

            <div class="col-lg-12 mb-3">
                <h2 class="mt-5"> VI. Organización de Unidades de aprendizaje:</h2>
            </div>
            @foreach ($silabo->unidades as $unidad)
                <!-- Primera tabla con el título y la situación de aprendizaje -->
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Título de la Unidad</th>
                            <th style="width: 80%">{{ $unidad->titulo }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Situación de Aprendizaje</td>
                            <td>{{ $unidad->situacion }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Segunda tabla con los demás campos -->
                <table class="table table-bordered" style="margin-top:-15px">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Duración</th>
                            <th>Desempeño Específico</th>
                            <th>Ejes Temáticos (Conocimiento)</th>
                            <th>Evidencia de Proceso</th>
                            <th>Evidencia Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $unidad->duracion }}</td>
                            <td>{{ $unidad->desempeno }}</td>
                            <td>{{ $unidad->ejes }}</td>
                            <td>{{ $unidad->evidencia }}</td>
                            <td>{{ $unidad->final }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            <div class="col-lg-12 mb-3">
                <h2 class="mt-5"> VII. RÚBRICAS DE EVALUACIÓN</h2>
            </div>

            <table class="table table-bordered" id="tabla-rubricas">
                <thead class="thead-dark">
                    <tr>
                        <th>Criterio de Evaluación</th>
                        <th>Destacado</th>
                        <th>Logrado</th>
                        <th>En Proceso</th>
                        <th>En Inicio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($silabo->rubricas as $rubrica)
                        <tr>
                            <td>{{ $rubrica->criterio }}</td>
                            <td>{{ $rubrica->destacado }}</td>
                            <td>{{ $rubrica->logrado }}</td>
                            <td>{{ $rubrica->proceso }}</td>
                            <td>{{ $rubrica->inicio }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


            <div class="col-lg-12 mb-3">
                <h2 class="mt-3">VIII. MODELOS METODOLÓGICOS (METODOLOGÍA)</h2>
                <p>{!! $silabo->modelos_metodologicos !!}</p>
            </div>

            <div class="col-lg-12 mb-3">
                <h2 class="mt-3">IX. RECURSOS Y MATERIALES DIDÁCTICOS</h2>
                <p>{!! $silabo->recursos !!}</p>
            </div>

            <div class="col-lg-12 mb-5">
                <h2 class="mt-3">X. Referencias</h4>
                    <p>{!! $silabo->referencias !!}</p>
            </div>
        </div>
    </div>
    {{-- <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}"
        class="btn btn-secondary float-right boton-volver">Volver</a> --}}
        <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}"
            class="btn btn-danger boton-volver"
            style="position: fixed; top: 120px; right: 20px; z-index: 1000;">
            Volver
         </a>         
@endsection
