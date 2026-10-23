@extends('layouts.profesionalizacion')
@section('titulo', 'Calificaciones')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        // Reglamento: aprobado con 11 o más (vigesimal).
        $notaMinima = 11;
        $num = fn ($v) => is_numeric($v) && (float) $v > 0 ? (float) $v : null;
        $tono = fn ($n) => $n === null ? 'pendiente' : ($n >= $notaMinima ? 'ok' : 'mal');
        $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format($n, 2), '0'), '.');
        $nombreCiclo = fn ($c) => preg_match('/^ciclo\s/i', $c->nombre) ? $c->nombre : 'Ciclo ' . $c->nombre;

        // Una fila por curso: nota por competencia = 40 % proceso + 60 % final (igual que la pantalla del docente).
        $bloques = collect($ciclosConCursos ?? [])->filter(fn ($c) => $c->cursos->isNotEmpty())->map(function ($ciclo) use ($num) {
            $cursos = $ciclo->cursos->map(function ($curso) use ($num) {
                $calif = $curso->calificacionesppd->first();
                $comps = $curso->competencias
                    ->sortBy(fn ($comp) => (int) preg_replace('/\D/', '', $comp->nombre))->values()->take(3)
                    ->map(function ($comp, $i) use ($calif, $num) {
                        $pp = $num($calif?->{'pp_c' . ($i + 1) . '_4'});
                        $pf = $num($calif?->{'pf_c' . ($i + 1) . '_3'});

                        return (object) ['numero' => $i + 1, 'pp' => $pp, 'pf' => $pf,
                            'nota' => $pp !== null && $pf !== null ? round($pp * 0.4 + $pf * 0.6) : null];
                    });

                return (object) [
                    'curso' => $curso,
                    'docentes' => $curso->docentes->pluck('nombre')->filter()->join(', '),
                    'comps' => $comps,
                    'nivel' => $num($calif?->nivel_desempeno),
                    'valoracion' => $num($calif?->calificacion_curso),
                    'nota' => $num($calif?->calificacion_sistema),
                    'obs' => $calif?->observaciones,
                ];
            });

            return (object) ['ciclo' => $ciclo, 'cursos' => $cursos];
        })->values();

        $finales = $bloques->flatMap(fn ($b) => $b->cursos)->pluck('nota')->filter(fn ($n) => $n !== null);
        $promedio = $finales->isNotEmpty() ? $finales->avg() : null;
    @endphp

    <div class="al-ui">
        @if (! empty($mensaje))
            <div class="al-aviso is-warn"><i class="fas fa-exclamation-circle"></i><div>{{ $mensaje }}</div></div>
        @else
            <section class="al-hero">
                <div class="al-hero-fila">
                    <div class="al-hero-texto">
                        <div class="al-kicker">Calificaciones</div>
                        <h1>{{ $programa->nombre ?? 'Programa PPD' }}</h1>
                        <div class="al-chips">
                            @if ($alumno->ciclo)
                                <span class="al-chip"><i class="fas fa-layer-group"></i>{{ $nombreCiclo($alumno->ciclo) }}</span>
                            @endif
                            <span class="al-chip"><i class="fas fa-check-double"></i>Aprobatoria: {{ $notaMinima }}</span>
                        </div>
                    </div>
                    <div class="al-hero-acciones">
                        <a href="{{ route('ppd.index') }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Ficha técnica</a>
                    </div>
                </div>
            </section>

            <div class="al-stats">
                <div class="al-stat">
                    <span class="al-stat-icono"><i class="fas fa-book-open"></i></span>
                    <div><div class="al-stat-valor">{{ $finales->count() }} <small style="font-size:.8rem;font-weight:700;">/ {{ $bloques->sum(fn ($b) => $b->cursos->count()) }}</small></div><div class="al-stat-etiqueta">Cursos con nota</div></div>
                </div>
                <div class="al-stat">
                    <span class="al-stat-icono is-oro"><i class="fas fa-star"></i></span>
                    <div><div class="al-stat-valor">{{ $promedio !== null ? number_format($promedio, 1) : '—' }}</div><div class="al-stat-etiqueta">Promedio</div></div>
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

            @if ($bloques->isEmpty())
                <div class="al-card"><div class="al-vacio"><i class="fas fa-info-circle"></i>Aún no hay cursos registrados para tu programa.</div></div>
            @else
                <div>
                    {{-- Un ciclo por pestaña; abre en el ciclo del estudiante --}}
                    <div class="al-tabs" role="tablist" aria-label="Ciclos" data-al-tabs>
                        @foreach ($bloques as $b)
                            @php $suyo = $alumno->ciclo_id ? $alumno->ciclo_id === $b->ciclo->id : $loop->first; @endphp
                            <button type="button" class="al-tab {{ $suyo ? 'is-activa' : '' }}" role="tab" data-panel="ciclo-{{ $b->ciclo->id }}">
                                <i class="fas fa-layer-group"></i> {{ $nombreCiclo($b->ciclo) }}
                                <span class="al-badge is-muted">{{ $b->cursos->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($bloques as $b)
                        <section class="al-panel" role="tabpanel" data-panel="ciclo-{{ $b->ciclo->id }}">
                            <div class="cal-grid">
                                @foreach ($b->cursos as $f)
                                    <article class="al-card cal-curso">
                                        <div class="al-card-cab">
                                            <span class="al-card-cab-icono"><i class="fas fa-book"></i></span>
                                            <div style="flex:1; min-width:0;">
                                                <h2>{{ $f->curso->nombre }}</h2>
                                                <div class="cal-docente"><i class="fas fa-chalkboard-teacher"></i>{{ $f->docentes ?: 'Docente por asignar' }}</div>
                                            </div>
                                            <div class="cal-final is-{{ $tono($f->nota) }}" title="Calificación del sistema (vigesimal)">
                                                <b>{{ $f->nota !== null ? $fmt($f->nota) : '—' }}</b><small>Final</small>
                                            </div>
                                        </div>
                                        <div class="cal-filas">
                                            <div class="cal-fila cal-fila-cab is-3">
                                                <span></span><span>Proceso · Final</span><span>Nota</span>
                                            </div>
                                            @forelse ($f->comps as $c)
                                                <div class="cal-fila is-3">
                                                    <span class="cal-etiqueta">Competencia {{ $c->numero }}</span>
                                                    <span class="cal-valor">{{ $fmt($c->pp) }} · {{ $fmt($c->pf) }}</span>
                                                    <span><b class="cal-nota is-{{ $tono($c->nota) }}">{{ $fmt($c->nota) }}</b></span>
                                                </div>
                                            @empty
                                                <div class="cal-fila is-3"><span class="cal-espera" style="grid-column:1 / -1;">El curso aún no tiene competencias registradas.</span></div>
                                            @endforelse
                                            <div class="cal-fila is-3 is-final">
                                                <span class="cal-etiqueta">Resultado</span>
                                                <span class="cal-nivel">
                                                    @if ($f->valoracion !== null)
                                                        Valoración {{ $fmt($f->valoracion) }}@if ($f->nivel !== null) <small class="text-muted">· nivel {{ $fmt($f->nivel) }} de 4</small>@endif
                                                    @else
                                                        <span class="text-muted">Sin calificar</span>
                                                    @endif
                                                </span>
                                                <span><b class="cal-nota is-{{ $tono($f->nota) }}">{{ $fmt($f->nota) }}</b></span>
                                            </div>
                                        </div>
                                        @if (filled($f->obs))
                                            <div class="cal-obs"><i class="fas fa-comment-alt"></i> <span><b>Observación del docente:</b> {!! nl2br(e($f->obs)) !!}</span></div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
