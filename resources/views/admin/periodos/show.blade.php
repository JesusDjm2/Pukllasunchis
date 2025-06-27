@extends('layouts.admin')

@section('contenido')
    
   
    <div class="container-fluid bg-white pt-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-3 text-primary font-weight-bold">Detalles del Período: {{ $nombre }}</h4>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm">
                <i class="fa fa-arrow-left fa-sm"></i> Volver
            </a>
        </div>

        <div class="mb-3">
            <input type="text" id="search" class="form-control form-control-sm"
                placeholder="Buscar por alumno o curso...">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Cursos</th>
                        <th style="font-size: 12px; text-align: center">Valoración Curso</th>
                        <th style="font-size: 12px; text-align: center">Calificación Curso</th>
                        <th style="font-size: 12px; text-align: center">Calificación Sistema</th>
                    </tr>
                </thead>
                <tbody id="periodos-table">
                    @php $contador = 1; @endphp
                    @forelse ($periodos as $alumnoPeriodos)
                        @php
                            $periodoEjemplo = $alumnoPeriodos->first();
                            $rowspan = $alumnoPeriodos->count();
                        @endphp

                        @foreach ($alumnoPeriodos as $index => $periodo)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $contador++ }}</td>
                                    <td rowspan="{{ $rowspan }}">
                                        <strong>{{ $periodo->alumno->apellidos }} {{ $periodo->alumno->nombres }}</strong>
                                        <ul style="padding-left: 1.2em">
                                            <li>{{ $periodo->alumno->email ?? 'N/A' }}</li>
                                            <li>{{ $periodo->alumno->programa->nombre ?? 'N/A' }} -
                                                {{ $periodo->alumno->ciclo->nombre ?? 'N/A' }}</li>
                                            <li>DNI: {{ $periodo->alumno->dni ?? 'N/A' }}</li>
                                            <li>Número: {{ $periodo->alumno->numero ?? 'N/A' }}</li>
                                            <li>Beca: {{ $periodo->alumno->user->beca == 1 ? 'Sí' : 'No' }}</li>
                                        </ul>
                                    </td>
                                @endif

                                <td>{{ $periodo->curso->nombre ?? 'Sin asignar' }}</td>

                                @php
                                    $bgColor = is_null($periodo->calificacion_sistema)
                                        ? '#fff3cd'
                                        : ($periodo->calificacion_sistema > 11
                                            ? '#d4edda'
                                            : '#f8d7da');
                                @endphp

                                <td style="text-align: center; background-color: {{ $bgColor }};">
                                    {{ $periodo->valoracion_curso }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }};">
                                    {{ $periodo->calificacion_curso }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }};">
                                    {{ $periodo->calificacion_sistema ?? 'Sin datos' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay datos para este período</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('search').addEventListener('input', function(event) {
            const searchText = event.target.value.toLowerCase();
            const filas = document.querySelectorAll('#periodos-table tr');

            let alumnoTexto = '';
            let mostrarGrupo = false;

            filas.forEach(row => {
                // Si esta fila tiene celda con rowspan (o sea, es la primera fila del grupo)
                const rowspanCell = row.querySelector('td[rowspan]');
                if (rowspanCell) {
                    const contenidoCelda = rowspanCell.parentElement.innerText.toLowerCase();
                    alumnoTexto = contenidoCelda;
                    mostrarGrupo = contenidoCelda.includes(searchText);
                }

                row.style.display = mostrarGrupo ? '' : 'none';
            });
        });
    </script>



@endsection
