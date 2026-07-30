<div class="table-responsive" id="ppd-tabla-responsive">
    <table class="table table-hover" style="font-size: 14px">
        <thead class="thead-dark">
            <tr>
                <th scope="col">N°</th>
                <th scope="col">Nombre</th>
                <th scope="col">Detalles académicos</th>
                <th scope="col">Matrícula</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php $grupoActual = null; $n = 1; @endphp
            @forelse ($alumnos as $alumno)
                @php
                    $grupo =
                        (optional($alumno->programa)->nombre
                            ?? optional(optional($alumno->ciclo)->programa)->nombre
                            ?? 'Sin programa') .
                        ' — Ciclo ' .
                        (optional($alumno->ciclo)->nombre ?? '?');
                @endphp
                @if ($grupo !== $grupoActual)
                    @php
                        $grupoActual = $grupo;
                        $kGrupo =
                            (string) ($alumno->programa_id ?? '0') .
                            '|' .
                            (string) ($alumno->ciclo_id ?? '0');
                        $nGrupo = $conteoGrupoListado[$kGrupo] ?? 0;
                        $nTotalCiclo = optional($totalesPorCicloId->get($alumno->ciclo_id))->total;
                    @endphp
                    <tr class="table-active">
                        <td colspan="5" class="py-2">
                            <strong>{{ $grupo }}</strong>
                            <span class="badge badge-secondary ml-2">
                                {{ $nGrupo }} {{ $nGrupo === 1 ? 'alumno' : 'alumnos' }}
                            </span>
                            @if ($nTotalCiclo !== null && (int) $nGrupo !== (int) $nTotalCiclo)
                                <span class="text-muted small ml-2"
                                    title="Total PPD en este ciclo (sin filtros de búsqueda)">
                                    · {{ $nTotalCiclo }} en ciclo (total)
                                </span>
                            @endif
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="text-muted font-weight-bold">{{ $n }}</td>
                    <td>
                        <strong>{{ $alumno->apellidos }}, {{ $alumno->name }}</strong>
                        <ul class="mb-0 pl-3">
                            <li>DNI: {{ $alumno->dni }}</li>
                            <li>{{ $alumno->email }}</li>
                            @if ($alumno->alumnoB)
                                <li>Número: {{ $alumno->alumnoB->numero ?? '—' }}</li>
                                <li>Referencia: {{ $alumno->alumnoB->numero_referencia ?? '—' }}</li>
                            @elseif ($alumno->telefono)
                                <li>Tel: {{ $alumno->telefono }}</li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        <ul class="mb-0 pl-3">
                            <li>{{ optional($alumno->programa)->nombre ?? optional(optional($alumno->ciclo)->programa)->nombre ?? '—' }}
                                – {{ optional($alumno->ciclo)->nombre ?? '?' }}</li>
                            @if ($alumno->lengua_1)
                                <li>Lengua 1: {{ $alumno->lengua_1 }}</li>
                            @endif
                            @if ($alumno->fecha_nacimiento)
                                <li>Nac.: {{ $alumno->fecha_nacimiento }}</li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        @if ($alumno->alumnoB)
                            <span class="badge badge-success">✅ Completa</span>
                        @else
                            <span class="badge badge-secondary">❌ Sin matrícula</span>
                        @endif
                    </td>
                    <td>
                        @if ($alumno->alumnoB)
                            <a href="{{ route('ppd.show', $alumno->alumnoB->id) }}"
                                class="btn btn-sm btn-primary" title="Ver">
                                <i class="fa fa-eye fa-sm"></i>
                            </a>
                            <a href="{{ route('ppd.edit', $alumno->alumnoB->id) }}"
                                class="btn btn-sm btn-warning" title="Editar">
                                <i class="fa fa-edit fa-sm"></i>
                            </a>
                        @else
                            <a href="{{ route('alumnos.edit', ['alumno' => $alumno->alumno->id ?? 0]) }}"
                                class="btn btn-sm btn-warning" title="Editar usuario">
                                <i class="fa fa-edit fa-sm"></i>
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="confirmarEliminar('{{ route('adminDestroy', ['id' => $alumno->id]) }}', '{{ addslashes($alumno->apellidos . ', ' . $alumno->name) }}')"
                            title="Eliminar">
                            <i class="fa fa-trash fa-sm"></i>
                        </button>
                    </td>
                </tr>
                @php $n++; @endphp
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No se encontraron alumnos PPD.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if (session('error'))
        <span class="text-danger text-sm">{{ session('error') }}</span>
    @endif
</div>
