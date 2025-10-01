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
    </style>

    <div class="container-fluid bg-white pt-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-3 text-primary font-weight-bold">Registros de Calificaciones - {{ $nombre }}</h4>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm"><i class="fa fa-arrow-left fa-sm"></i>
                Volver</a>

            <a href="{{ route('periodos.export', $periodoActual->id ?? request()->route('id')) }}"
                class="btn btn-success btn-sm shadow-sm">
                <i class="fa fa-file-excel"></i> Exportar Excel
            </a>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 mb-2">
                <input type="text" id="search" class="form-control form-control-sm"
                    placeholder="Buscar por alumno, DNI, correo, curso o ciclo...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaCalificaciones">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Alumno</th>
                        <th>Curso</th>
                        <th>Ciclo</th>
                        <th style="text-align: center">Valoración Curso</th>
                        <th style="text-align: center">Calificación Curso</th>
                        <th style="text-align: center">Calificación Sistema</th>
                    </tr>
                </thead>
                <tbody>
                    @php $contador = 1; @endphp

                    @foreach ($filas as $entry)
                        @php
                            $alumno = $entry['alumno'];
                            $cursos = collect($entry['cursos']);
                            $periodosAlumno = $entry['periodos'];
                            if ($cursos->isEmpty()) {
                                $cursos = collect([null]);
                            }
                            $rowspan = $cursos->count();
                            $searchAlumno = strtolower(
                                trim(
                                    ($alumno->apellidos ?? '') .
                                        ' ' .
                                        ($alumno->nombres ?? '') .
                                        ' ' .
                                        ($alumno->email ?? '') .
                                        ' ' .
                                        ($alumno->dni ?? ''),
                                ),
                            );
                        @endphp

                        @foreach ($cursos as $i => $curso)
                            @php
                                $cursoNombre = $curso ? $curso->nombre ?? 'Sin asignar' : '— Sin cursos asignados —';
                                $cicloNombre = $curso ? optional($curso->ciclo)->nombre ?? 'No asignado' : '';
                                $periodo = $curso ? $periodosAlumno->firstWhere('curso_id', $curso->id) : null;
                                $bgColor = $periodo
                                    ? (is_null($periodo->calificacion_sistema)
                                        ? '#fff3cd'
                                        : ($periodo->calificacion_sistema > 11
                                            ? '#d4edda'
                                            : '#f8d7da'))
                                    : '#e2e3e5';
                                $searchFila = trim(strtolower($searchAlumno . ' ' . $cursoNombre . ' ' . $cicloNombre));
                            @endphp

                            <tr data-group="{{ $alumno->id }}" data-search="{{ $searchFila }}"
                                @if ($i === $rowspan - 1) style="border-bottom: 2px solid #000000;" @endif>
                                @if ($i === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $contador++ }}</td>
                                    <td rowspan="{{ $rowspan }}">
                                        <strong>{{ $alumno->apellidos }} {{ $alumno->nombres }}</strong>
                                        <ul style="padding-left: 1.2em; font-size: 14px;">
                                            <li>{{ $alumno->email ?? 'N/A' }}</li>
                                            <li>DNI: {{ $alumno->dni ?? 'N/A' }}</li>
                                            <li>Programa: {{ $alumno->programa->nombre ?? 'N/A' }}</li>
                                            <li>Beca: {{ $alumno->user->beca == 1 ? 'Sí' : 'No' }}</li>
                                            <li>Id: {{ $alumno->id }}</li>
                                        </ul>
                                    </td>
                                @endif
                                <td>{{ $cursoNombre }}</td>
                                <td>{{ $cicloNombre }}</td>
                                <td style="text-align: center; background-color: {{ $bgColor }}">
                                    {{ $periodo->valoracion_curso ?? ($curso ? 'N/A' : '—') }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }}">
                                    {{ $periodo->calificacion_curso ?? ($curso ? 'N/A' : '—') }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }}">
                                    {{ $periodo->calificacion_sistema ?? ($curso ? 'Sin datos' : '—') }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- JS: filtro por grupo (alumno) en tiempo real --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');

            function buildGroups() {
                const allRows = Array.from(document.querySelectorAll('#tablaCalificaciones tbody tr'));
                const grupos = {};

                allRows.forEach(row => {
                    const gid = row.dataset.group ?? 'no-group';
                    if (!grupos[gid]) grupos[gid] = {
                        rows: [],
                        searchText: ''
                    };
                    grupos[gid].rows.push(row);
                    grupos[gid].searchText += ' ' + ((row.dataset.search || '').toLowerCase());
                });

                return grupos;
            }

            let grupos = buildGroups();

            function filtrar() {
                const q = (searchInput.value || '').toLowerCase().trim();
                Object.values(grupos).forEach(gr => {
                    const match = q === '' ? true : gr.searchText.includes(q);
                    gr.rows.forEach(r => r.style.display = match ? '' : 'none');
                });
            }

            searchInput.addEventListener('input', filtrar);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
@endsection
