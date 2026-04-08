<table>
    <thead>
        <tr style="background-color: #17a2b8; color: #ffffff;">
            <th colspan="11" style="font-size: 14pt; text-align: center;">
                PERÍODO PPD: {{ $periodo->nombre }}
                @if ($periodo->actual)
                    (ACTUAL)
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center;">
                Fecha de exportación: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr style="background-color: #e9ecef;">
            <th colspan="2" style="text-align: center;">RESUMEN</th>
            <th colspan="9"></th>
        </tr>
        <tr>
            <th>Total Registros</th>
            <th>Total Alumnos</th>
            <th>Total Cursos</th>
            <th>Promedio Calificación</th>
            <th colspan="7"></th>
        </tr>
        <tr>
            <td>{{ $totalRegistros }}</td>
            <td>{{ $totalAlumnos }}</td>
            <td>{{ $totalCursos }}</td>
            <td>{{ number_format($promedioCalificacion, 2) }}</td>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="11"></td>
        </tr>

        @foreach ($registrosPorPrograma as $programaNombre => $alumnosDelPrograma)
            <tr style="background-color: #17a2b8; color: #ffffff;">
                <th colspan="11" style="font-size: 12pt;">
                    PROGRAMA: {{ $programaNombre }} ({{ count($alumnosDelPrograma) }} alumnos)
                </th>
            </tr>
            <tr style="background-color: #212529; color: #ffffff;">
                <th>#</th>
                <th>Apellidos</th>
                <th>Nombres</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Curso</th>
                <th>Ciclo</th>
                <th>Calificación Curso</th>
                <th>Calificación Sistema</th>
                <th>Nivel Desempeño</th>
            </tr>

            @php $contador = 1; @endphp
            @foreach ($alumnosDelPrograma as $alumnoData)
                @php
                    $alumno = $alumnoData['alumno'];
                    $cursos = collect($alumnoData['cursos']);
                    $rowspan = $cursos->count();
                @endphp

                @foreach ($cursos as $i => $cursoData)
                    @php
                        $curso = $cursoData['curso'];
                        $registro = $cursoData['registro']; // Puede ser null
                    @endphp
                    <tr>
                        @if ($i === 0)
                            <td rowspan="{{ $rowspan }}">{{ $contador++ }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $alumno->apellidos ?? '' }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $alumno->nombres ?? ($alumno->name ?? '') }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $alumno->dni ?? 'N/A' }}</td>
                            <td rowspan="{{ $rowspan }}">{{ $alumno->email ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $curso->nombre ?? 'Sin curso' }}</td>
                        <td>{{ $curso->ciclo->nombre ?? 'No asignado' }}</td>
                        <td style="text-align: center;">
                            @if ($registro && $registro->calificacion_curso)
                                {{ $registro->calificacion_curso }}
                            @else
                                <!-- Espacio en blanco -->
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($registro && $registro->calificacion_sistema)
                                {{ $registro->calificacion_sistema }}
                            @else
                                <!-- Espacio en blanco -->
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($registro && $registro->nivel_desempeno)
                                {{ $registro->nivel_desempeno }}
                            @else
                                <!-- Espacio en blanco -->
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
            <tr>
                <td colspan="11"></td>
            </tr>
        @endforeach
    </thead>
</table>
