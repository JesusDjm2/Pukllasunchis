@extends('layouts.alumno')
@section('contenido')
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
                                @foreach ($periodosAgrupados as $nombrePeriodo => $grupos)
                                    <option value="periodo-{{ Str::slug($nombrePeriodo) }}">{{ $nombrePeriodo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @foreach ($periodosAgrupados as $nombrePeriodo => $grupos)
                        <div id="periodo-{{ Str::slug($nombrePeriodo) }}" class="tabla-periodo d-none table-responsive">
                            <h5 class="mt-3 font-weight-bold">Período: {{ $nombrePeriodo }}</h5>
                            <table class="table table-bordered table-hover">
                                <thead class="table-secondary thead-dark">
                                    <tr>
                                        <th>Cursos</th>
                                        <th>Valoración del curso</th>
                                        <th>Calificación del curso</th>
                                        <th>Calificación del sistema</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($grupos as $periodo)
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
                            document.querySelectorAll('.tabla-periodo').forEach(div => {
                                div.classList.add('d-none');
                            });

                            const seleccionado = document.getElementById(id);
                            if (seleccionado) {
                                seleccionado.classList.remove('d-none');
                            }
                        }
                    </script>
                @endif

                @if ($alumno->ciclo->cursos->isNotEmpty())
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr style="font-size: 14px" class="bg-secondary text-white">
                                    <td class="font-weight-bold align-middle align-middle">Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Periodo</td>
                                    <td class="font-weight-bold align-middle text-center">Valoración Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Sistema</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumno->ciclo->cursos as $curso)
                                    @php
                                        // Encuentra el periodo asociado al curso actual
                                        $periodoUno = $curso->periodos()->where('alumno_id', $alumno->id)->first();
                                        $periodoDos = $curso->periododos()->where('alumno_id', $alumno->id)->first();
                                        $periodoTres = $curso->periodotres()->where('alumno_id', $alumno->id)->first();
                                    @endphp
                                    <tr>
                                        <!-- Nombre del curso con rowspan -->
                                        <td rowspan="3" class="align-middle bg-light font-weight-bold"
                                            style="border-bottom: 1px solid #39779b; border-left: 1px solid #39779b">
                                            {{-- <a href="{{ route('curso.show', $curso->id) }}" class="font-weight-bold">{{ $curso->nombre }}</a> --}}
                                            {{ $curso->nombre }}
                                        </td>
                                        <td class="font-weight-bold text-primary">Parcial 1</td>

                                        <!-- Calificaciones del primer periodo -->
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
                                        @else
                                            <td colspan="3" class="text-center">No hay calificaciones disponibles</td>
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
                                        @else
                                            <td colspan="3" class="text-center">No hay calificaciones disponibles</td>
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
                                                style="border-bottom: 1px solid #39779b;">No hay calificaciones disponibles
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
