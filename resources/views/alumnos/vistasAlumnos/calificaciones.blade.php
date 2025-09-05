@extends('layouts.alumno')
@section('contenido')
    <style>
        table thead tr th {
            pointer-events: none;
        }

        table thead tr {
            pointer-events: none;
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="mb-0 text-uppercase text-primary font-weight-bold">Notas </h4>
            <span class="font-weight-bold">
                {{ $alumno->programa->nombre }} - {{ $alumno->ciclo->nombre }}
            </span>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row mb-4">
            <div class="col-lg-12">

                @if ($periodosAgrupados->isNotEmpty())
                    <div class="container d-flex justify-content-center my-2">
                        <div class="col-md-6">
                            <label for="selectorPeriodo" class="form-label fw-bold text-center w-100 mb-2">
                                Calificaciones de periodos anteriores
                            </label>
                            <select id="selectorPeriodo" class="form-select form-control form-control-sm text-center"
                                onchange="mostrarPeriodoAgrupado(this.value)">
                                <option selected disabled>-- Selecciona un período --</option>
                                @foreach ($periodosAgrupados as $nombrePeriodo => $periodos)
                                    <option value="periodo-{{ \Illuminate\Support\Str::slug($nombrePeriodo) }}">
                                        {{ $nombrePeriodo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @foreach ($periodosAgrupados as $nombrePeriodo => $periodos)
                        <div id="periodo-{{ \Illuminate\Support\Str::slug($nombrePeriodo) }}"
                            class="tabla-periodo d-none table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-secondary thead-dark">
                                    <tr>
                                        <th colspan="4" class="text-center h5 mb-0">
                                            Periodo: {{ $nombrePeriodo }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Cursos</th>
                                        <th>Valoración del curso</th>
                                        <th>Calificación del curso</th>
                                        <th>Calificación del sistema</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodos as $periodo)
                                        @php
                                            $bgColor = is_null($periodo->calificacion_sistema)
                                                ? '#fff3cd'
                                                : ($periodo->calificacion_sistema > 11
                                                    ? '#d4edda'
                                                    : '#f8d7da');
                                        @endphp
                                        <tr>
                                            <td>
                                                <span
                                                    class="font-weight-bold">{{ $periodo->curso->nombre ?? 'No asignado' }}</span>
                                                <span style="font-size: 12px">
                                                    ({{ $periodo->curso->ciclo->programa->nombre ?? 'No asignado' }} -
                                                    {{ $periodo->curso->ciclo->nombre ?? 'No asignado' }})
                                                </span>
                                            </td>
                                            <td style="text-align: center; background-color: {{ $bgColor }};">
                                                {{ $periodo->valoracion_curso ?? 'Sin datos' }}
                                            </td>
                                            <td style="text-align: center; background-color: {{ $bgColor }};">
                                                {{ $periodo->calificacion_curso ?? 'Sin datos' }}
                                            </td>
                                            <td style="text-align: center; background-color: {{ $bgColor }};">
                                                {{ $periodo->calificacion_sistema ?? 'Sin datos' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    <script>
                        function mostrarPeriodoAgrupado(id) {
                            document.querySelectorAll('.tabla-periodo').forEach(div => div.classList.add('d-none'));
                            const seleccionado = document.getElementById(id);
                            if (seleccionado) seleccionado.classList.remove('d-none');
                        }
                    </script>
                @endif


                @if ($alumno->ciclo->cursos->isNotEmpty())
                    <div class="p-2 table-responsive mt-4">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-dark text-white">
                                    <th colspan="6" class="text-center h5 mb-0">Periodo actual</th>
                                </tr>
                                <tr style="font-size: 14px" class="bg-dark text-white">
                                    <td class="font-weight-bold align-middle align-middle">Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Parcial</td>
                                    <td class="font-weight-bold align-middle text-center">Valoración Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Sistema</td>
                                    <th class="font-weight-bold align-middle text-center">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $cursosParaMostrar = $alumno->cursos->isNotEmpty()
                                        ? $alumno->cursos
                                        : $alumno->ciclo->cursos;
                                @endphp

                                @foreach ($cursosParaMostrar as $curso)
                                    @php
                                        $periodoUno = $curso->periodos()->where('alumno_id', $alumno->id)->first();
                                        $periodoDos = $curso->periododos()->where('alumno_id', $alumno->id)->first();
                                        $periodoTres = $curso->periodotres()->where('alumno_id', $alumno->id)->first();
                                    @endphp
                                    <tr>
                                        <td rowspan="3" class="align-middle bg-light font-weight-bold"
                                            style="border-bottom: 1px solid #39779b; border-left: 1px solid #39779b">
                                            {{ $curso->nombre }}<br>
                                            @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                <span class="badge badge-info">Ciclo {{ $curso->ciclo->nombre }}</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-primary">Parcial 1</td>

                                        @if ($periodoUno)
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->valoracion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->calificacion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->calificacion_sistema }}
                                            </td>

                                            <td rowspan="3"
                                                style="width: 600px; border-top: 1px solid #39779b; border-bottom: 1px solid #39779b"
                                                class="align-middle">
                                                @if (!empty($periodoDos?->observaciones))
                                                    {{ $periodoDos->observaciones }}
                                                @elseif (!empty($periodoUno?->observaciones))
                                                    {{ $periodoUno->observaciones }}
                                                @else
                                                    Sin observaciones
                                                @endif
                                            </td>
                                        @else
                                            <td colspan="3" class="text-center">Sin datos disponibles</td>
                                            <td rowspan="3"
                                                style="width: 600px; border-top: 1px solid #39779b; border-bottom: 1px solid #39779b">
                                                Sin datos disponibles</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-info">Parcial 2</td>
                                        <!-- Calificaciones del segundo periodo -->
                                        @if ($periodoDos)
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->valoracion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->calificacion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->calificacion_sistema }}
                                            </td>
                                            {{-- <td rowspan="3" style="width: 600px; border-top: 1px solid #39779b"
                                                class="align-middle">
                                                @if (!empty($periodoDos?->observaciones))
                                                    {{ $periodoDos->observaciones }}
                                                @elseif (!empty($periodoUno?->observaciones))
                                                    {{ $periodoUno->observaciones }}
                                                @else
                                                    Sin observaciones
                                                @endif
                                            </td> --}}
                                        @else
                                            <td colspan="3" class="text-center">Sin datos disponibles</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #39779b;" class="text-success font-weight-bold">
                                            Promedio</td>
                                        @if ($periodoTres)
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->valoracion_curso }}
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->calificacion_curso }}
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->calificacion_sistema }}
                                            </td>
                                        @else
                                            <td colspan="3" class="text-center"
                                                style="border-bottom: 1px solid #39779b;">Sin datos disponibles
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning" role="alert">
                        No hay cursos asignados para este alumno.
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
