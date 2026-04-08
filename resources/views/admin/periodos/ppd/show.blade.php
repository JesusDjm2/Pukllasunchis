@extends('layouts.admin')
@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <style>
        #tablaCalificaciones thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: #fff;
            z-index: 10;
            pointer-events: none;
        }

        .table-responsive {
            max-height: 75vh;
            overflow-y: auto;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #aaa;
            border-radius: 4px;
        }

        td[rowspan] {
            border-bottom: 2px solid #000 !important;
        }

        .badge-calificacion {
            font-size: 0.85em;
            padding: 0.25em 0.6em;
        }

        .programa-header {
            background-color: #e9ecef;
            padding: 10px;
            border-left: 4px solid #17a2b8;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
    <div class="container-fluid bg-white pt-3">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-3 text-primary font-weight-bold">
                <i class="fas fa-calendar-alt text-info"></i> Período PPD: {{ $periodo->nombre }}
                @if ($periodo->actual)
                    <span class="badge badge-success ml-2">ACTUAL</span>
                @endif
            </h4>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm">
                    <i class="fa fa-arrow-left fa-sm"></i> Volver
                </a>
                <a href="{{ route('periodos.admin.ppd.export', $periodo->id) }}" class="btn btn-success btn-sm shadow-sm">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>
        <!-- Información del período -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body">
                        <h6 class="text-primary">Total Registros</h6>
                        <h2 class="font-weight-bold">{{ $totalRegistros }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100">
                    <div class="card-body">
                        <h6 class="text-success">Promedio Calificación</h6>
                        <h2 class="font-weight-bold">{{ number_format($promedioCalificacion, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100">
                    <div class="card-body">
                        <h6 class="text-info">Total Alumnos</h6>
                        <h2 class="font-weight-bold">{{ $totalAlumnos }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-body">
                        <h6 class="text-warning">Total Cursos</h6>
                        <h2 class="font-weight-bold">{{ $totalCursos }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Búsqueda -->
        <div class="row mb-3">
            <div class="col-md-12">
                <input type="text" id="search" class="form-control form-control-sm"
                    placeholder="Buscar por apellidos, nombres, DNI, email, curso...">
            </div>
        </div>

        <!-- Listado por programas -->
        @foreach ($registrosPorPrograma as $programaNombre => $alumnosDelPrograma)
            <div class="programa-header" id="programa-{{ Str::slug($programaNombre) }}">
                <h5 class="font-weight-bold mb-0">
                    <i class="fas fa-graduation-cap text-info"></i> PROGRAMA: {{ $programaNombre }}
                    <spmall class="badge badge-info">{{ count($alumnosDelPrograma) }} alumnos</small>
                </h5>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover" id="tablaCalificaciones">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Ciclo</th>
                            <th style="text-align: center">Calificación Curso</th>
                            <th style="text-align: center">Calificación Sistema</th>
                            <th style="text-align: center">Nivel Desempeño</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $contadorPrograma = 1;
                        @endphp

                        @foreach ($alumnosDelPrograma as $alumnoData)
                            @php
                                $alumno = $alumnoData['alumno'];
                                $cursos = collect($alumnoData['cursos']);
                                $rowspan = $cursos->count();

                                // Texto para búsqueda del alumno
                                $alumnoSearch = strtolower(
                                    trim(
                                        ($alumno->apellidos ?? '') .
                                            ' ' .
                                            ($alumno->nombres ?? ($alumno->name ?? '')) .
                                            ' ' .
                                            ($alumno->email ?? '') .
                                            ' ' .
                                            ($alumno->dni ?? ''),
                                    ),
                                );
                            @endphp

                            @foreach ($cursos as $i => $cursoData)
                                @php
                                    $curso = $cursoData['curso'];
                                    $registro = $cursoData['registro'];
                                    $cursoNombre = $curso ? $curso->nombre : 'Sin curso';
                                    $cicloNombre = $curso && $curso->ciclo ? $curso->ciclo->nombre : 'No asignado';

                                    // Texto completo para búsqueda (incluye curso y ciclo)
                                    $filaSearch = strtolower(
                                        trim(
                                            $alumnoSearch .
                                                ' ' .
                                                $cursoNombre .
                                                ' ' .
                                                $cicloNombre .
                                                ' ' .
                                                $programaNombre,
                                        ),
                                    );
                                @endphp

                                <tr data-group="{{ $programaNombre }}-{{ $alumno->id }}"
                                    data-search="{{ $filaSearch }}"
                                    @if ($i === $rowspan - 1) style="border-bottom: 2px solid #dee2e6;" @endif>

                                    @if ($i === 0)
                                        <td rowspan="{{ $rowspan }}">{{ $contadorPrograma++ }}</td>
                                        <td rowspan="{{ $rowspan }}">
                                            <strong>{{ $alumno->apellidos ?? '' }},
                                                {{ $alumno->nombres ?? ($alumno->name ?? '') }}</strong>
                                            <ul style="padding-left: 1.2em; font-size: 14px; margin-bottom: 0;">
                                                <li>{{ $alumno->email ?? 'N/A' }}</li>
                                                <li>DNI: {{ $alumno->dni ?? 'N/A' }}</li>
                                                <li>ID: {{ $alumno->id }}</li>
                                            </ul>
                                        </td>
                                    @endif

                                    <td>{{ $cursoNombre }}</td>
                                    <td>{{ $cicloNombre }}</td>

                                    <td style="text-align: center;">
                                        @if ($registro && $registro->calificacion_curso)
                                            <span>
                                                {{ $registro->calificacion_curso }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;">
                                        @if ($registro && $registro->calificacion_sistema)
                                            <span>
                                                {{ $registro->calificacion_sistema }}
                                            </span>
                                        @else
                                            <span class="text-muted">Sin datos</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;">
                                        @if ($registro && $registro->nivel_desempeno)
                                            <span>
                                                {{ $registro->nivel_desempeno }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if (empty($registrosPorPrograma))
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No hay registros de calificaciones para este período.
            </div>
        @endif
    </div>

    {{-- JS: filtro por grupo (alumno) en tiempo real --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');

            // Construir grupos de filas (agrupadas por alumno dentro de programa)
            function buildGroups() {
                const allRows = Array.from(document.querySelectorAll('#tablaCalificaciones tbody tr'));
                const grupos = {};

                allRows.forEach(row => {
                    const groupId = row.dataset.group || 'no-group';
                    if (!grupos[groupId]) {
                        grupos[groupId] = {
                            rows: [],
                            searchText: ''
                        };
                    }
                    grupos[groupId].rows.push(row);
                    // Acumular texto de búsqueda de toda la fila
                    grupos[groupId].searchText += ' ' + (row.dataset.search || '').toLowerCase();
                });

                return grupos;
            }

            let grupos = buildGroups();

            function filtrar() {
                const query = (searchInput.value || '').toLowerCase().trim();

                // Mostrar/ocultar encabezados de programa primero
                const programaHeaders = document.querySelectorAll('.programa-header');
                programaHeaders.forEach(header => {
                    header.style.display = 'block'; // Mostrar todos inicialmente
                });

                // Filtrar filas por grupo
                Object.keys(grupos).forEach(groupId => {
                    const grupo = grupos[groupId];
                    const match = query === '' || grupo.searchText.includes(query);

                    // Mostrar/ocultar todas las filas del grupo
                    grupo.rows.forEach(row => {
                        row.style.display = match ? '' : 'none';
                    });

                    // Ocultar encabezado del programa si ningún grupo dentro de él coincide
                    if (!match) {
                        // Extraer nombre del programa del groupId (formato: "programa-alumno_id")
                        const programaNombre = groupId.split('-')[0];
                        const programaHeader = document.getElementById('programa-' + programaNombre
                            .toLowerCase().replace(/\s+/g, '-'));

                        // Verificar si hay otros grupos visibles en el mismo programa
                        const otrosGruposEnMismoPrograma = Object.keys(grupos).filter(g =>
                            g.startsWith(programaNombre + '-') && g !== groupId
                        );

                        const algunOtroVisible = otrosGruposEnMismoPrograma.some(otherGroupId => {
                            return grupos[otherGroupId].rows.some(row => row.style.display !==
                                'none');
                        });

                        if (programaHeader && !algunOtroVisible) {
                            // Verificar si este es el último grupo visible del programa
                            const gruposDelPrograma = Object.keys(grupos).filter(g => g.startsWith(
                                programaNombre + '-'));
                            const todosOcultos = gruposDelPrograma.every(gid => {
                                return grupos[gid].rows.every(row => row.style.display === 'none');
                            });

                            if (todosOcultos) {
                                programaHeader.style.display = 'none';
                            }
                        }
                    }
                });

                // Si hay búsqueda vacía, mostrar todo
                if (query === '') {
                    programaHeaders.forEach(header => {
                        header.style.display = 'block';
                    });
                }
            }

            // Event listener para búsqueda en tiempo real
            searchInput.addEventListener('input', filtrar);

            // Filtrar también al cargar la página si hay un parámetro de búsqueda en la URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                searchInput.value = urlParams.get('search');
                filtrar();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
@endsection
