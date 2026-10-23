@extends('layouts.alumno')
@section('titulo', 'Calificaciones')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        // Reglamento: aprobado con 11 o más (vigesimal). Antes se pintaba el 11 como desaprobado (> 11).
        $notaMinima = 11;
        $tono = fn ($n) => ! is_numeric($n) ? 'pendiente' : ($n >= $notaMinima ? 'ok' : 'mal');
        $p1Visible = ! $periodoActual || $periodoActual->parcial1Visible();
        $p2Visible = ! $periodoActual || $periodoActual->parcial2DesempenoVisible();

        // Notas del periodo actual, una sola vez por curso.
        $filas = $cursosDelAlumno->map(function ($curso) use ($alumno) {
            $p1 = $curso->periodos()->where('alumno_id', $alumno->id)->first();
            $p2 = $curso->periododos()->where('alumno_id', $alumno->id)->first();
            $p3 = $curso->periodotres()->where('alumno_id', $alumno->id)->first();

            return (object) [
                'curso' => $curso, 'p1' => $p1, 'p2' => $p2, 'p3' => $p3,
                'obs' => $p2?->observaciones ?? ($p1?->observaciones ?? null),
            ];
        });
        $finales = $p2Visible
            ? $filas->map(fn ($f) => $f->p3?->calificacion_sistema)->filter(fn ($n) => is_numeric($n))->values()
            : collect();
        $promedioFinal = $finales->isNotEmpty() ? round($finales->avg(), 1) : null;
    @endphp

    <div class="al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">Calificaciones</div>
                    <h1>{{ $periodoActual ? 'Periodo '.$periodoActual->nombre : 'Mis calificaciones' }}</h1>
                    <div class="al-chips">
                        <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $alumno->programa->nombre }}</span>
                        @if ($alumno->ciclo)
                            <span class="al-chip"><i class="fas fa-layer-group"></i>Ciclo {{ $alumno->ciclo->nombre }}</span>
                        @endif
                        <span class="al-chip"><i class="fas fa-check-double"></i>Aprobatoria: {{ $notaMinima }}</span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="javascript:history.go(-1)" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        <div class="al-stats">
            <div class="al-stat">
                <span class="al-stat-icono"><i class="fas fa-book-open"></i></span>
                <div><div class="al-stat-valor">{{ $filas->count() }}</div><div class="al-stat-etiqueta">Cursos del periodo</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-star"></i></span>
                <div>
                    <div class="al-stat-valor">{{ $promedioFinal !== null ? number_format($promedioFinal, 1) : '—' }}</div>
                    <div class="al-stat-etiqueta">Promedio final</div>
                </div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-ok"><i class="fas fa-check"></i></span>
                <div><div class="al-stat-valor">{{ $finales->filter(fn ($n) => $n >= $notaMinima)->count() }}</div><div class="al-stat-etiqueta">Aprobados</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-mal"><i class="fas fa-times"></i></span>
                <div><div class="al-stat-valor">{{ $finales->filter(fn ($n) => $n < $notaMinima)->count() }}</div><div class="al-stat-etiqueta">Desaprobados</div></div>
            </div>
        </div>

        @if ($periodoActual && ! $p2Visible)
            <div class="al-aviso is-info"><i class="fas fa-calendar-alt"></i>
                <div>
                    @if (! $p1Visible)
                        Las notas del <strong>Parcial 1</strong> se publican el <strong>{{ $periodoActual->fechaNotasVisibles(1)->format('d/m/Y') }}</strong>;
                    @endif
                    las del <strong>Parcial 2</strong> y el <strong>promedio final</strong>, el <strong>{{ $periodoActual->fechaNotasVisibles(2)->format('d/m/Y') }}</strong>.
                </div>
            </div>
        @endif

        {{-- ── Periodo actual: una tarjeta por curso ── --}}
        @if ($filas->isNotEmpty())
            <div class="cal-grid">
                @foreach ($filas as $f)
                    @php
                        $bloques = [
                            ['Parcial 1', $f->p1, $p1Visible, 1],
                            ['Parcial 2', $f->p2, $p2Visible, 2],
                            ['Promedio final', $f->p3, $p2Visible, 2],
                        ];
                        $notaFinal = $p2Visible ? $f->p3?->calificacion_sistema : null;
                    @endphp
                    <article class="al-card cal-curso">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-book"></i></span>
                            <div style="flex:1; min-width:0;">
                                <h2>{{ $f->curso->nombre }}</h2>
                                @if ($f->curso->ciclo_id != $alumno->ciclo_id)
                                    <span class="al-badge is-info mt-1">Ciclo {{ $f->curso->ciclo->nombre ?? '–' }}</span>
                                @endif
                            </div>
                            <div class="cal-final is-{{ $tono($notaFinal) }}" title="Nota final">
                                <b>{{ is_numeric($notaFinal) ? $notaFinal : '—' }}</b><small>Final</small>
                            </div>
                        </div>
                        <div class="cal-filas">
                            <div class="cal-fila cal-fila-cab">
                                <span></span><span>Nivel de logro</span><span>Valoración</span><span>Nota</span>
                            </div>
                            @foreach ($bloques as [$etiqueta, $reg, $visible, $parcial])
                                <div class="cal-fila {{ $etiqueta === 'Promedio final' ? 'is-final' : '' }}">
                                    <span class="cal-etiqueta">{{ $etiqueta }}</span>
                                    @if (! $visible)
                                        <span class="cal-espera"><i class="far fa-clock"></i> Desde el {{ $periodoActual->fechaNotasVisibles($parcial)->format('d/m/Y') }}</span>
                                    @elseif ($reg)
                                        <span class="cal-nivel">{{ $reg->calificacion_curso && $reg->calificacion_curso !== '-' ? $reg->calificacion_curso : '—' }}</span>
                                        <span class="cal-valor">{{ $reg->valoracion_curso ?? '—' }}</span>
                                        <span><b class="cal-nota is-{{ $tono($reg->calificacion_sistema) }}">{{ $reg->calificacion_sistema ?? '—' }}</b></span>
                                    @else
                                        <span class="cal-espera">Sin datos aún</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if (! empty($f->obs))
                            <div class="cal-obs"><i class="fas fa-comment-alt"></i> <span><b>Observación del docente:</b> {{ $f->obs }}</span></div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="al-card"><div class="al-vacio"><i class="fas fa-info-circle"></i>No hay cursos asignados para este período.</div></div>
        @endif

        {{-- ── Periodos anteriores (pestañas; abre en el más reciente) ── --}}
        @if ($periodosAgrupados->isNotEmpty())
            @php $ultimo = \Illuminate\Support\Str::slug($periodosAgrupados->keys()->last()); @endphp
            <div class="al-card">
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-history"></i></span>
                    <div><h2>Periodos anteriores</h2><p>Tu historial de notas por periodo.</p></div>
                </div>
                <div class="cal-tabs" role="tablist">
                    @foreach ($periodosAgrupados->keys()->reverse() as $nombrePeriodo)
                        @php $slug = \Illuminate\Support\Str::slug($nombrePeriodo); @endphp
                        <button type="button" role="tab" class="cal-tab {{ $slug === $ultimo ? 'is-activa' : '' }}"
                            data-periodo="periodo-{{ $slug }}" onclick="mostrarPeriodoAgrupado('periodo-{{ $slug }}')">{{ $nombrePeriodo }}</button>
                    @endforeach
                </div>

                @foreach ($periodosAgrupados as $nombrePeriodo => $periodos)
                    @php
                        $slug = \Illuminate\Support\Str::slug($nombrePeriodo);
                        $notasP = $periodos->pluck('calificacion_sistema')->filter(fn ($n) => is_numeric($n));
                    @endphp
                    <div id="periodo-{{ $slug }}" class="tabla-periodo {{ $slug === $ultimo ? '' : 'd-none' }}">
                        <div class="cal-hist-resumen">
                            <span><i class="fas fa-calendar-alt"></i> {{ $nombrePeriodo }}</span>
                            <span>{{ $periodos->count() }} {{ $periodos->count() === 1 ? 'curso' : 'cursos' }}</span>
                            @if ($notasP->isNotEmpty())
                                <span>Promedio <b class="cal-nota is-{{ $tono($notasP->avg()) }}">{{ number_format($notasP->avg(), 1) }}</b></span>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="cal-tabla">
                                <thead>
                                    <tr><th>Curso</th><th>Nivel de logro</th><th>Valoración</th><th>Nota</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodos as $periodo)
                                        <tr>
                                            <td>
                                                <div class="cal-hist-curso">{{ $periodo->curso->nombre ?? 'No asignado' }}</div>
                                                <small>{{ $periodo->curso->ciclo->programa->nombre ?? '—' }} · Ciclo {{ $periodo->curso->ciclo->nombre ?? '—' }}</small>
                                            </td>
                                            <td>{{ $periodo->calificacion_curso ?? '—' }}</td>
                                            <td>{{ $periodo->valoracion_curso ?? '—' }}</td>
                                            <td><b class="cal-nota is-{{ $tono($periodo->calificacion_sistema) }}">{{ $periodo->calificacion_sistema ?? '—' }}</b></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <script>
                function mostrarPeriodoAgrupado(id) {
                    document.querySelectorAll('.tabla-periodo').forEach(function (d) { d.classList.toggle('d-none', d.id !== id); });
                    document.querySelectorAll('.cal-tab').forEach(function (t) { t.classList.toggle('is-activa', t.dataset.periodo === id); });
                }
            </script>
        @endif
    </div>

    {{-- Estilos .cal-* en public/css/pk-ui.css (compartidos con calificaciones PPD) --}}
@endsection
