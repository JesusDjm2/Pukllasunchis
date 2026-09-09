@extends('layouts.alumno')
@section('titulo', 'Calificaciones')
@section('contenido')
    @php
        $actions =
            '<a href="javascript:history.go(-1)" class="btn btn-outline-secondary btn-sm shadow-sm">' .
            '<i class="fas fa-arrow-left mr-1"></i> Volver</a>';
    @endphp
    @include('partials.alumno-page-header', [
        'title' => 'Calificaciones',
        'subtitle' => 'Notas del período actual y consulta de períodos anteriores.',
        'actions' => $actions,
    ])
    {{-- ─── Período actual ──────────────────────────────────────────────────── --}}
    <div class="alumno-shell mb-4 p-0 overflow-hidden">
        <div class="alumno-ficha-section-header">
            <i class="fas fa-calendar-check mr-2"></i> Período actual
            @if ($periodoActual)
                <span class="alumno-grade-ciclo-badge">{{ $periodoActual->nombre }}</span>
            @endif
            <span class="alumno-cal-header-meta">
                <i class="fas fa-graduation-cap mr-1"></i>
                {{ $alumno->programa->nombre }} — {{ $alumno->ciclo->nombre }}
            </span>
        </div>

        @if ($cursosDelAlumno->isNotEmpty())
            <div class="alumno-grade-panels">
                @foreach ($cursosDelAlumno as $curso)
                    @php
                        $p1 = $curso->periodos()->where('alumno_id', $alumno->id)->first();
                        $p2 = $curso->periododos()->where('alumno_id', $alumno->id)->first();
                        $p3 = $curso->periodotres()->where('alumno_id', $alumno->id)->first();
                        $obs = $p2?->observaciones ?? ($p1?->observaciones ?? null);

                        $scoreClass = fn($s) => is_null($s)
                            ? 'alumno-score-pending'
                            : ($s > 11
                                ? 'alumno-score-good'
                                : 'alumno-score-fail');
                    @endphp

                    <div class="alumno-grade-panel">
                        {{-- Nombre del curso --}}
                        <div class="alumno-grade-panel-header">
                            <span class="alumno-grade-panel-title">{{ $curso->nombre }}</span>
                            @if ($curso->ciclo_id != $alumno->ciclo_id)
                                <span class="alumno-grade-ciclo-badge">
                                    Ciclo {{ $curso->ciclo->nombre ?? '–' }}
                                </span>
                            @endif
                        </div>

                        {{-- Grid de notas: fila por parcial --}}
                        <div class="agr-grid">

                            {{-- Cabecera de columnas --}}
                            <div class="agr-head">
                                <span></span>
                                <span>Valoración</span>
                                <span>Calificación</span>
                                <span>Sistema</span>
                            </div>

                            {{-- Parcial 1 --}}
                            <div class="agr-row agr-p1">
                                <span class="agr-lbl">Parcial 1</span>
                                @if ($periodoActual && !$periodoActual->parcial1Visible())
                                    <span class="agr-empty">Disponible desde el {{ $periodoActual->calificaciones_parcial1_inicio->format('d/m/Y') }}</span>
                                @elseif ($p1)
                                    <span class="agr-val">{{ $p1->valoracion_curso ?? '—' }}</span>
                                    <span class="agr-val">{{ $p1->calificacion_curso ?? '—' }}</span>
                                    <span class="agr-score">
                                        @php $s = $p1->calificacion_sistema; @endphp
                                        <span class="alumno-score {{ $scoreClass($s) }}">{{ $s ?? '—' }}</span>
                                    </span>
                                @else
                                    <span class="agr-empty">Sin datos aún</span>
                                @endif
                            </div>

                            {{-- Parcial 2 --}}
                            <div class="agr-row agr-p2">
                                <span class="agr-lbl">Parcial 2</span>
                                @if ($periodoActual && !$periodoActual->parcial2DesempenoVisible())
                                    <span class="agr-empty">Disponible desde el {{ $periodoActual->calificaciones_parcial2_inicio->format('d/m/Y') }}</span>
                                @elseif ($p2)
                                    <span class="agr-val">{{ $p2->valoracion_curso ?? '—' }}</span>
                                    <span class="agr-val">{{ $p2->calificacion_curso ?? '—' }}</span>
                                    <span class="agr-score">
                                        @php $s = $p2->calificacion_sistema; @endphp
                                        <span class="alumno-score {{ $scoreClass($s) }}">{{ $s ?? '—' }}</span>
                                    </span>
                                @else
                                    <span class="agr-empty">Sin datos aún</span>
                                @endif
                            </div>

                            {{-- Promedio --}}
                            <div class="agr-row agr-prom">
                                <span class="agr-lbl">Promedio</span>
                                @if ($periodoActual && !$periodoActual->parcial2DesempenoVisible())
                                    <span class="agr-empty">Disponible desde el {{ $periodoActual->calificaciones_parcial2_inicio->format('d/m/Y') }}</span>
                                @elseif ($p3)
                                    <span class="agr-val">{{ $p3->valoracion_curso ?? '—' }}</span>
                                    <span class="agr-val">{{ $p3->calificacion_curso ?? '—' }}</span>
                                    <span class="agr-score">
                                        @php $s = $p3->calificacion_sistema; @endphp
                                        <span class="alumno-score {{ $scoreClass($s) }}">{{ $s ?? '—' }}</span>
                                    </span>
                                @else
                                    <span class="agr-empty">Sin datos aún</span>
                                @endif
                            </div>

                        </div>

                        {{-- Observaciones --}}
                        @if (!empty($obs))
                            <div class="alumno-grade-obs">
                                <i class="fas fa-comment-alt mr-1"></i>
                                <span class="alumno-grade-obs-label">Obs.:</span>{{ $obs }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-4 py-3 text-muted small">
                <i class="fas fa-info-circle mr-1"></i> No hay cursos asignados para este período.
            </div>
        @endif
    </div>

    {{-- ─── Períodos anteriores ─────────────────────────────────────────────── --}}
    @if ($periodosAgrupados->isNotEmpty())
        <div class="alumno-shell mb-4 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-history mr-2"></i> Períodos anteriores
            </div>
            <div class="p-3">
                <div class="alumno-periodo-select-wrap mb-3">
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
                    <div id="periodo-{{ \Illuminate\Support\Str::slug($nombrePeriodo) }}" class="tabla-periodo d-none">
                        <div class="alumno-cal-hist-header">
                            <i class="fas fa-calendar-alt mr-1"></i> {{ $nombrePeriodo }}
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
                                            $sc = is_null($s)
                                                ? 'alumno-score-pending'
                                                : ($s > 11
                                                    ? 'alumno-score-good'
                                                    : 'alumno-score-fail');
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="alumno-hist-curso-nombre">
                                                    {{ $periodo->curso->nombre ?? 'No asignado' }}
                                                </span>
                                                <span class="alumno-hist-curso-sub">
                                                    {{ $periodo->curso->ciclo->programa->nombre ?? '—' }}
                                                    — {{ $periodo->curso->ciclo->nombre ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $periodo->valoracion_curso ?? '—' }}</td>
                                            <td class="text-center">{{ $periodo->calificacion_curso ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="alumno-score {{ $sc }}">{{ $s ?? '—' }}</span>
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
                document.querySelectorAll('.tabla-periodo').forEach(d => d.classList.add('d-none'));
                const el = document.getElementById(id);
                if (el) el.classList.remove('d-none');
            }
        </script>
    @endif

@endsection
