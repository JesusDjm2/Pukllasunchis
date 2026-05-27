@extends('layouts.alumno')
@section('titulo', 'Calificaciones')
@section('contenido')
    <style>
        table thead tr th {
            pointer-events: none;
        }

        table thead tr {
            pointer-events: none;
        }
    </style>
    @php
        $actions =
            '<a href="javascript:history.go(-1)" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>';
    @endphp
    @include('partials.alumno-page-header', [
        'title' => 'Calificaciones',
        'subtitle' =>
            'Notas del periodo actual y consulta de periodos anteriores. Programa y ciclo se muestran como referencia.',
        'actions' => $actions,
    ])

    <div class="d-flex flex-wrap justify-content-center justify-content-md-end mb-3">
        <span class="alumno-badge-program">
            <i class="fas fa-graduation-cap mr-1"></i>
            {{ $alumno->programa->nombre }} — {{ $alumno->ciclo->nombre }}
        </span>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">

            @if ($periodosAgrupados->isNotEmpty())
                <div class="alumno-shell alumno-cal-filter mb-4">
                    <label for="selectorPeriodo" class="d-block font-weight-bold text-center w-100 mb-2">
                        Periodos anteriores
                    </label>
                    <select id="selectorPeriodo" class="form-control form-control-sm text-center"
                        onchange="mostrarPeriodoAgrupado(this.value)">
                        <option selected disabled>— Selecciona un período —</option>
                        @foreach ($periodosAgrupados as $nombrePeriodo => $periodos)
                            <option value="periodo-{{ \Illuminate\Support\Str::slug($nombrePeriodo) }}">
                                {{ $nombrePeriodo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @foreach ($periodosAgrupados as $nombrePeriodo => $periodos)
                    <div id="periodo-{{ \Illuminate\Support\Str::slug($nombrePeriodo) }}"
                        class="tabla-periodo d-none table-responsive mb-4">
                        <table class="table table-bordered table-hover alumno-table-cal mb-0">
                            <thead>
                                <tr>
                                    <th colspan="4" class="text-center py-3 alumno-cal-period-title">
                                        {{ $nombrePeriodo }}
                                    </th>
                                </tr>
                                <tr class="bg-light">
                                    <th>Curso</th>
                                    <th class="text-center">Valoración del curso</th>
                                    <th class="text-center">Calificación del curso</th>
                                    <th class="text-center">Calificación del sistema</th>
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
                                            <span class="font-weight-bold d-block">{{ $periodo->curso->nombre ?? 'No asignado' }}</span>
                                            <span class="small text-muted">
                                                ({{ $periodo->curso->ciclo->programa->nombre ?? '—' }} —
                                                {{ $periodo->curso->ciclo->nombre ?? '—' }})
                                            </span>
                                        </td>
                                        <td class="text-center" style="background-color: {{ $bgColor }};">
                                            {{ $periodo->valoracion_curso ?? 'Sin datos' }}
                                        </td>
                                        <td class="text-center" style="background-color: {{ $bgColor }};">
                                            {{ $periodo->calificacion_curso ?? 'Sin datos' }}
                                        </td>
                                        <td class="text-center" style="background-color: {{ $bgColor }};">
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


            @if ($cursosDelAlumno->isNotEmpty())
                <div class="alumno-shell p-0 overflow-hidden">
                    <div class="p-3 border-bottom bg-light">
                        <h2 class="h5 mb-0 font-weight-bold text-primary">
                            <i class="fas fa-calendar-check mr-2"></i> Periodo actual
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr style="font-size: 14px" class="bg-dark text-white">
                                    <td class="font-weight-bold align-middle">Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Parcial</td>
                                    <td class="font-weight-bold align-middle text-center">Valoración curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación sistema</td>
                                    <th class="font-weight-bold align-middle text-center">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $cursosDelAlumno = ciclo->cursos + extras de otros ciclos del período actual --}}
                                @php $cursosParaMostrar = $cursosDelAlumno; @endphp

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
                                            <td colspan="3" class="text-center">Sin datos disponibles</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #39779b;"
                                            class="text-success font-weight-bold">
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
                </div>
            @else
                <div class="alert alert-warning border-0 shadow-sm" role="alert">
                    <i class="fas fa-info-circle mr-2"></i> No hay cursos asignados para este alumno.
                </div>
            @endif

        </div>
    </div>
@endsection
