@extends('layouts.alumno')
@section('titulo', 'Calificaciones')
@section('contenido')
    @php
        $actions =
            '<a href="javascript:history.go(-1)" class="btn btn-outline-secondary btn-sm shadow-sm">' .
            '<i class="fas fa-arrow-left mr-1"></i> Volver</a>';
    @endphp
    @include('partials.alumno-page-header', [
        'title'    => 'Calificaciones',
        'subtitle' => 'Notas del período actual y consulta de períodos anteriores.',
        'actions'  => $actions,
    ])

    {{-- Badge programa / ciclo --}}
    <div class="d-flex flex-wrap justify-content-center justify-content-md-end mb-4">
        <span class="alumno-badge-program">
            <i class="fas fa-graduation-cap mr-1"></i>
            {{ $alumno->programa->nombre }} — {{ $alumno->ciclo->nombre }}
        </span>
    </div>

    {{-- ─── Períodos anteriores ─────────────────────────────────────────────── --}}
    @if ($periodosAgrupados->isNotEmpty())
        <div class="alumno-shell mb-4 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-history mr-2"></i> Períodos anteriores
            </div>
            <div class="p-3">
                <div class="alumno-periodo-select-wrap">
                    <select id="selectorPeriodo" class="form-control form-control-sm"
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
                         class="tabla-periodo d-none mt-3">
                        <div class="alumno-cal-hist-header">
                            <i class="fas fa-calendar-alt mr-2"></i> {{ $nombrePeriodo }}
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm alumno-table-cal mb-0">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th class="text-center">Valoración</th>
                                        <th class="text-center">Calificación</th>
                                        <th class="text-center">Sistema</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodos as $periodo)
                                        @php
                                            $s = $periodo->calificacion_sistema;
                                            $scoreClass = is_null($s)
                                                ? 'alumno-score-pending'
                                                : ($s > 11 ? 'alumno-score-good' : 'alumno-score-fail');
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold d-block">
                                                    {{ $periodo->curso->nombre ?? 'No asignado' }}
                                                </span>
                                                <span class="small text-muted">
                                                    {{ $periodo->curso->ciclo->programa->nombre ?? '—' }}
                                                    — {{ $periodo->curso->ciclo->nombre ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $periodo->valoracion_curso ?? '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $periodo->calificacion_curso ?? '—' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="alumno-score {{ $scoreClass }}">
                                                    {{ $s ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <script>
            function mostrarPeriodoAgrupado(id) {
                document.querySelectorAll('.tabla-periodo').forEach(div => div.classList.add('d-none'));
                const sel = document.getElementById(id);
                if (sel) sel.classList.remove('d-none');
            }
        </script>
    @endif

    {{-- ─── Período actual ──────────────────────────────────────────────────── --}}
    @if ($cursosDelAlumno->isNotEmpty())
        <div class="alumno-shell p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-calendar-check mr-2"></i> Período actual
            </div>
            <div class="p-3">
                <div class="alumno-grade-cards">
                    @foreach ($cursosDelAlumno as $curso)
                        @php
                            $periodoUno  = $curso->periodos()->where('alumno_id', $alumno->id)->first();
                            $periodoDos  = $curso->periododos()->where('alumno_id', $alumno->id)->first();
                            $periodoTres = $curso->periodotres()->where('alumno_id', $alumno->id)->first();
                            $obs = $periodoDos?->observaciones ?? $periodoUno?->observaciones ?? null;
                        @endphp
                        <div class="alumno-grade-card">
                            {{-- Encabezado del curso --}}
                            <div class="alumno-grade-card-header">
                                <span class="alumno-grade-card-title">{{ $curso->nombre }}</span>
                                @if ($curso->ciclo_id != $alumno->ciclo_id)
                                    <span class="alumno-grade-ciclo-badge">
                                        Ciclo {{ $curso->ciclo->nombre ?? '–' }}
                                    </span>
                                @endif
                            </div>

                            {{-- Tabla de notas --}}
                            <div class="table-responsive">
                                <table class="table table-sm alumno-grade-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="alumno-grade-th-parcial"></th>
                                            <th class="text-center alumno-grade-th">Valoración</th>
                                            <th class="text-center alumno-grade-th">Calificación</th>
                                            <th class="text-center alumno-grade-th">Sistema</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Parcial 1 --}}
                                        <tr>
                                            <td class="alumno-grade-parcial-label p1">Parcial 1</td>
                                            @if ($periodoUno)
                                                <td class="text-center align-middle">{{ $periodoUno->valoracion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">{{ $periodoUno->calificacion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">
                                                    @php $s = $periodoUno->calificacion_sistema; @endphp
                                                    <span class="alumno-score {{ is_null($s) ? 'alumno-score-pending' : ($s > 11 ? 'alumno-score-good' : 'alumno-score-fail') }}">
                                                        {{ $s ?? '—' }}
                                                    </span>
                                                </td>
                                            @else
                                                <td colspan="3" class="text-center text-muted small py-2">Sin datos aún</td>
                                            @endif
                                        </tr>
                                        {{-- Parcial 2 --}}
                                        <tr>
                                            <td class="alumno-grade-parcial-label p2">Parcial 2</td>
                                            @if ($periodoDos)
                                                <td class="text-center align-middle">{{ $periodoDos->valoracion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">{{ $periodoDos->calificacion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">
                                                    @php $s = $periodoDos->calificacion_sistema; @endphp
                                                    <span class="alumno-score {{ is_null($s) ? 'alumno-score-pending' : ($s > 11 ? 'alumno-score-good' : 'alumno-score-fail') }}">
                                                        {{ $s ?? '—' }}
                                                    </span>
                                                </td>
                                            @else
                                                <td colspan="3" class="text-center text-muted small py-2">Sin datos aún</td>
                                            @endif
                                        </tr>
                                        {{-- Promedio --}}
                                        <tr class="alumno-grade-row-promedio">
                                            <td class="alumno-grade-parcial-label promedio">Promedio</td>
                                            @if ($periodoTres)
                                                <td class="text-center align-middle">{{ $periodoTres->valoracion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">{{ $periodoTres->calificacion_curso ?? '—' }}</td>
                                                <td class="text-center align-middle">
                                                    @php $s = $periodoTres->calificacion_sistema; @endphp
                                                    <span class="alumno-score {{ is_null($s) ? 'alumno-score-pending' : ($s > 11 ? 'alumno-score-good' : 'alumno-score-fail') }}">
                                                        {{ $s ?? '—' }}
                                                    </span>
                                                </td>
                                            @else
                                                <td colspan="3" class="text-center text-muted small py-2">Sin datos aún</td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Observaciones --}}
                            @if (!empty($obs))
                                <div class="alumno-grade-obs">
                                    <i class="fas fa-comment-alt mr-1"></i>
                                    <span class="alumno-grade-obs-label">Obs.:</span>
                                    {{ $obs }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info border-0 shadow-sm" role="alert">
            <i class="fas fa-info-circle mr-2"></i> No hay cursos asignados para este período.
        </div>
    @endif

@endsection
