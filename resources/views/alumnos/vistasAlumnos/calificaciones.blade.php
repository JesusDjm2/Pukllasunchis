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

    <style>
        .cal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(420px, 100%), 1fr)); gap: 0 1.25rem; }
        @media (max-width: 575.98px) { .cal-grid { grid-template-columns: 1fr; } }
        .cal-curso .al-card-cab h2 { font-size: .95rem; }
        .cal-final {
            flex: none; width: 58px; height: 58px; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center;
            background: var(--al-fondo-2); border: 1px solid var(--al-borde);
        }
        .cal-final b { font-size: 1.35rem; font-weight: 800; line-height: 1; color: var(--al-suave); }
        .cal-final small { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--al-suave); }
        .cal-final.is-ok { background: var(--al-ok-f); border-color: transparent; } .cal-final.is-ok b { color: var(--al-ok); }
        .cal-final.is-mal { background: var(--al-mal-f); border-color: transparent; } .cal-final.is-mal b { color: var(--al-mal); }

        .cal-filas { padding: .35rem 0; }
        .cal-fila { display: grid; grid-template-columns: 1.1fr 1.2fr .8fr .7fr; align-items: center; gap: .5rem; padding: .55rem 1.25rem; font-size: .86rem; color: var(--al-texto); }
        .cal-fila + .cal-fila { border-top: 1px dashed var(--al-borde); }
        .cal-fila-cab { font-size: .64rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--al-suave); padding-top: .2rem; padding-bottom: .2rem; }
        .cal-fila.is-final { background: var(--al-fondo-2); }
        .cal-etiqueta { font-weight: 700; }
        .cal-fila.is-final .cal-etiqueta { color: var(--al-oro-2); }
        .cal-nivel { font-weight: 600; }
        .cal-valor { color: var(--al-suave); font-variant-numeric: tabular-nums; }
        .cal-espera { grid-column: 2 / -1; color: var(--al-suave); font-size: .8rem; font-style: italic; }
        .cal-nota {
            display: inline-flex; align-items: center; justify-content: center; min-width: 2.3rem; padding: .18rem .5rem; border-radius: 8px;
            font-weight: 800; font-variant-numeric: tabular-nums; background: var(--al-fondo-2); color: var(--al-suave);
        }
        .cal-nota.is-ok { background: var(--al-ok-f); color: var(--al-ok); }
        .cal-nota.is-mal { background: var(--al-mal-f); color: var(--al-mal); }
        .cal-obs { display: flex; gap: .5rem; font-size: .82rem; color: var(--al-texto); padding: .7rem 1.25rem; border-top: 1px solid var(--al-borde); background: rgba(205, 146, 68, .07); }
        .cal-obs i { color: var(--al-oro); margin-top: .2rem; }

        .cal-tabs { display: flex; flex-wrap: wrap; gap: .4rem; padding: .9rem 1.25rem 0; }
        .cal-tab {
            border: 1px solid var(--al-borde); background: var(--al-fondo); color: var(--al-suave); font-weight: 700; font-size: .8rem;
            padding: .35rem .9rem; border-radius: 999px; cursor: pointer; transition: all .15s ease;
        }
        .cal-tab:hover { border-color: var(--al-oro); color: var(--al-texto); }
        .cal-tab.is-activa { background: var(--al-petroleo); border-color: var(--al-petroleo); color: #fff; }
        .cal-hist-resumen { display: flex; flex-wrap: wrap; gap: .4rem 1.25rem; align-items: center; padding: .9rem 1.25rem .5rem; font-size: .82rem; color: var(--al-suave); font-weight: 600; }
        .cal-hist-resumen i { color: var(--al-oro); }
        .cal-tabla { width: 100%; border-collapse: collapse; font-size: .86rem; color: var(--al-texto); }
        .cal-tabla th { font-size: .64rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--al-suave); padding: .55rem 1.25rem; border-bottom: 1px solid var(--al-borde); text-align: left; }
        .cal-tabla td { padding: .6rem 1.25rem; border-bottom: 1px solid var(--al-borde); vertical-align: middle; }
        .cal-tabla tr:last-child td { border-bottom: 0; }
        .cal-tabla th:not(:first-child), .cal-tabla td:not(:first-child) { text-align: center; }
        .cal-hist-curso { font-weight: 700; }
        .cal-tabla small { color: var(--al-suave); }
    </style>
@endsection
