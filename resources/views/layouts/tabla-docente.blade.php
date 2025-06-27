<table class="table table-bordered table-hover">
    <thead class="bg-secondary text-white">
        <tr>
            <th>#</th>
            <th>Curso</th>
            <th>Competencias</th>
            <th>Competencias Seleccionadas</th>
        </tr>
    </thead>
    <tbody>
        @php $contador = 0; @endphp
        @foreach ($docente->cursos as $curso)
            @php
                $contador++;
                $esPpd = str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'); // Verifica si contiene PPD
            @endphp
            @if (!$esPpd)
                <tr>
                    <td>{{ $contador }}</td>
                    <td>{{ $curso->nombre }}</td>
                    <td>
                        <ul>
                            @foreach ($curso->competencias as $competencia)
                                <li>{{ $competencia->nombre }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <ul>
                            @foreach ($curso->competenciasSeleccionadas as $competencia)
                                <li>{{ $competencia->nombre }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>