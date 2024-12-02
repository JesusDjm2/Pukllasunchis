@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white pt-3">
        <h4 class="mb-3 text-primary font-weight-bold">Detalles del Período: {{ $nombre }}</h4>
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Buscar por alumno o curso...">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Curso</th>
                        <th style="font-size: 12px; text-align: center">Valoración Curso</th>
                        <th style="font-size: 12px; text-align: center">Calificación Curso</th>
                        <th style="font-size: 12px; text-align: center">Calificación Sistema</th>
                    </tr>
                </thead>
                <tbody id="periodos-table">
                    @forelse ($periodos as $periodo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $periodo->alumno ? $periodo->alumno->apellidos . ' ' . $periodo->alumno->nombres : 'Sin asignar' }}</strong>
                                <br>
                                <ul>
                                    <li>DNI: {{ $periodo->alumno->dni ?? 'N/A' }}</li>
                                    <li>Programa: {{ $periodo->alumno->programa->nombre ?? 'N/A' }}</li>
                                    <li>Ciclo: {{ $periodo->alumno->ciclo->nombre ?? 'N/A' }}</li>
                                </ul>
                            </td>
                            <td>{{ $periodo->curso->nombre ?? 'Sin asignar' }}</td>

                            <td
                                style="text-align: center; background-color: 
    {{ is_null($periodo->calificacion_sistema) ? '#fff3cd' : ($periodo->calificacion_sistema > 11 ? '#d4edda' : '#f8d7da') }};">
                                {{ $periodo->valoracion_curso }}
                            </td>
                            <td
                                style="text-align: center; background-color: 
    {{ is_null($periodo->calificacion_sistema) ? '#fff3cd' : ($periodo->calificacion_sistema > 11 ? '#d4edda' : '#f8d7da') }};">
                                {{ $periodo->calificacion_curso }}
                            </td>
                            <td
                                style="text-align: center; background-color: 
    {{ is_null($periodo->calificacion_sistema) ? '#fff3cd' : ($periodo->calificacion_sistema > 11 ? '#d4edda' : '#f8d7da') }};">
                                {{ $periodo->calificacion_sistema ?? 'Sin datos' }}
                            </td>

                        </tr>
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
            const rows = document.querySelectorAll('#periodos-table tr');

            rows.forEach(row => {
                const alumno = row.children[1]?.innerText.toLowerCase() || '';
                const curso = row.children[2]?.innerText.toLowerCase() || '';

                if (alumno.includes(searchText) || curso.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection
