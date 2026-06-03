@extends('layouts.alumno')
@section('titulo', 'Mi Progreso — ' . $curso->nombre)

@push('styles')
<style>
:root {
    --ce-blue:#3a86ff; --ce-teal:#06d6a0; --ce-amber:#ffb703;
    --ce-purple:#8338ec; --ce-text:#1b1b2f; --ce-muted:#6c757d;
    --ce-radius:1rem; --ce-shadow:0 4px 24px rgba(58,134,255,.10);
}

/* ── Header ── */
.ce-prog-header {
    background: linear-gradient(135deg, #1b1b2f, #2d3561, #3a86ff);
    border-radius: var(--ce-radius);
    padding: 2rem 2rem 1.5rem;
    color: #fff; margin-bottom: 2rem;
    position: relative; overflow: hidden;
}
.ce-prog-header::before {
    content: ''; position: absolute;
    width: 260px; height: 260px; border-radius: 50%;
    background: rgba(255,255,255,.06);
    top: -80px; right: -60px; pointer-events: none;
}
.ce-prog-back {
    display: inline-flex; align-items: center; gap: .4rem;
    color: rgba(255,255,255,.75); font-size: .82rem; font-weight: 600;
    text-decoration: none; margin-bottom: 1rem;
    transition: color .2s;
}
.ce-prog-back:hover { color: #fff; text-decoration: none; }
.ce-prog-title {
    font-size: clamp(1.1rem, 2.5vw, 1.6rem);
    font-weight: 800; margin: 0 0 .25rem;
}
.ce-prog-sub { font-size: .85rem; opacity: .7; margin: 0 0 1.5rem; }

/* ── Main progress ring + bar ── */
.ce-prog-main {
    display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;
}
.ce-ring-wrap {
    position: relative; flex-shrink: 0;
    width: 80px; height: 80px;
}
.ce-ring {
    transform: rotate(-90deg);
}
.ce-ring-bg { fill: none; stroke: rgba(255,255,255,.2); stroke-width: 8; }
.ce-ring-fill {
    fill: none; stroke: var(--ce-teal); stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 226;
    stroke-dashoffset: 226;
    transition: stroke-dashoffset 1.5s cubic-bezier(.4,0,.2,1);
}
.ce-ring-pct {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 800; color: #fff;
}
.ce-prog-bar-block { flex: 1; min-width: 180px; }
.ce-prog-bar-label {
    font-size: .8rem; opacity: .8; margin-bottom: .4rem;
    display: flex; justify-content: space-between;
}
.ce-prog-bar-outer {
    height: 10px; background: rgba(255,255,255,.2);
    border-radius: 99px; overflow: hidden;
}
.ce-prog-bar-inner {
    height: 100%;
    background: linear-gradient(90deg, var(--ce-teal), #00f0c8);
    border-radius: 99px; width: 0;
    transition: width 1.5s cubic-bezier(.4,0,.2,1);
}

/* ── Stats grid ── */
.ce-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem; margin-bottom: 2rem;
}
.ce-stat-card {
    background: #fff; border-radius: .85rem;
    box-shadow: var(--ce-shadow);
    padding: 1.1rem 1.25rem;
    display: flex; align-items: center; gap: .9rem;
}
.ce-stat-ico {
    width: 44px; height: 44px; border-radius: .6rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.ico-blue  { background: #e8f0ff; color: var(--ce-blue); }
.ico-teal  { background: #e0fbf4; color: var(--ce-teal); }
.ico-amber { background: #fff8e0; color: var(--ce-amber); }
.ico-purple{ background: #f2e8ff; color: var(--ce-purple); }
.ce-stat-val { font-size: 1.5rem; font-weight: 800; color: var(--ce-text); line-height: 1; }
.ce-stat-lbl { font-size: .75rem; color: var(--ce-muted); margin-top: .1rem; }

/* ── Section heading ── */
.ce-sec-heading {
    font-size: .78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: var(--ce-muted); margin: 2rem 0 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.ce-sec-heading::after {
    content: ''; flex: 1; height: 1px; background: #e9ecef;
}

/* ── Level breakdown ── */
.ce-level-block {
    background: #fff; border-radius: var(--ce-radius);
    box-shadow: var(--ce-shadow); margin-bottom: 1rem; overflow: hidden;
}
.ce-level-block-hdr {
    display: flex; align-items: center; gap: .85rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f0f4ff, #e8f0ff);
    border-bottom: 1px solid #e9ecef;
}
.ce-level-num {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--ce-blue); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 800; flex-shrink: 0;
}
.ce-level-name { font-weight: 700; font-size: .9rem; color: var(--ce-text); flex: 1; }
.ce-level-pct-badge {
    font-size: .78rem; font-weight: 700;
    padding: .2rem .6rem; border-radius: 99px;
    background: var(--ce-teal); color: #fff;
}

/* ── Unit rows ── */
.ce-unit-row {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid #f5f5f5;
}
.ce-unit-row:last-child { border-bottom: none; }
.ce-unit-row-hdr {
    display: flex; align-items: center; gap: .6rem; margin-bottom: .5rem;
}
.ce-unit-name { font-size: .86rem; font-weight: 700; color: var(--ce-text); flex: 1; }
.ce-unit-counts { font-size: .75rem; color: var(--ce-muted); white-space: nowrap; }
.ce-unit-bar-wrap { height: 6px; background: #e9ecef; border-radius: 99px; overflow: hidden; }
.ce-unit-bar-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, var(--ce-teal), var(--ce-blue));
    width: 0; transition: width 1s ease;
}

/* ── Lesson dots ── */
.ce-dots { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
.ce-dot {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .6rem; border: 2px solid #dee2e6; color: #dee2e6;
    background: #fff; transition: transform .2s;
}
.ce-dot.done { background: var(--ce-teal); border-color: var(--ce-teal); color: #fff; }
.ce-dot.ex   { border-color: var(--ce-amber); color: var(--ce-amber); }
.ce-dot.ex.done { background: var(--ce-amber); color: #fff; }
.ce-dot:hover { transform: scale(1.2); }

@media (max-width: 576px) {
    .ce-prog-header { padding: 1.5rem 1.25rem; }
    .ce-prog-main { gap: 1rem; }
    .ce-stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('contenido')

{{-- Progress header --}}
<div class="ce-prog-header" id="prog-header">
    <a href="{{ route('ce.alumno.show', $curso) }}" class="ce-prog-back">
        <i class="fas fa-arrow-left"></i> Volver al curso
    </a>
    <h1 class="ce-prog-title">Mi Progreso</h1>
    <p class="ce-prog-sub">{{ $curso->nombre }}</p>

    <div class="ce-prog-main">
        {{-- SVG ring --}}
        <div class="ce-ring-wrap">
            <svg class="ce-ring" viewBox="0 0 80 80" width="80" height="80">
                <circle class="ce-ring-bg" cx="40" cy="40" r="36"/>
                <circle class="ce-ring-fill" id="ring-fill" cx="40" cy="40" r="36"
                        data-pct="{{ $porcentaje }}"/>
            </svg>
            <div class="ce-ring-pct">{{ $porcentaje }}%</div>
        </div>

        {{-- Linear bar --}}
        <div class="ce-prog-bar-block">
            <div class="ce-prog-bar-label">
                <span>Progreso general</span>
                <span>{{ $completadasL + $completadasE }}/{{ $totalLecciones + $totalEjercicios }}</span>
            </div>
            <div class="ce-prog-bar-outer">
                <div class="ce-prog-bar-inner" id="prog-bar-inner" data-pct="{{ $porcentaje }}"></div>
            </div>
        </div>
    </div>
</div>

{{-- Stats grid --}}
<div class="ce-stats-grid">
    <div class="ce-stat-card">
        <div class="ce-stat-ico ico-blue"><i class="fas fa-book-open"></i></div>
        <div>
            <div class="ce-stat-val">{{ $completadasL }}</div>
            <div class="ce-stat-lbl">Lecciones completadas</div>
        </div>
    </div>
    <div class="ce-stat-card">
        <div class="ce-stat-ico ico-teal"><i class="fas fa-tasks"></i></div>
        <div>
            <div class="ce-stat-val">{{ $completadasE }}</div>
            <div class="ce-stat-lbl">Ejercicios resueltos</div>
        </div>
    </div>
    <div class="ce-stat-card">
        <div class="ce-stat-ico ico-amber"><i class="fas fa-layer-group"></i></div>
        <div>
            <div class="ce-stat-val">{{ $totalLecciones }}</div>
            <div class="ce-stat-lbl">Total lecciones</div>
        </div>
    </div>
    <div class="ce-stat-card">
        <div class="ce-stat-ico ico-purple"><i class="fas fa-star"></i></div>
        <div>
            <div class="ce-stat-val">{{ $progresoEjercicios->sum('puntaje') }}</div>
            <div class="ce-stat-lbl">Puntos obtenidos</div>
        </div>
    </div>
</div>

{{-- Level breakdown --}}
<p class="ce-sec-heading"><i class="fas fa-layer-group"></i> Detalle por nivel</p>

@foreach ($curso->niveles as $ni => $nivel)
    @php
        $totalNivelL = $nivel->unidades->sum(fn($u) => $u->lecciones->count());
        $totalNivelE = $nivel->unidades->sum(fn($u) => $u->ejercicios->count());
        $doneNivelL  = $nivel->unidades->sum(fn($u) =>
            $u->lecciones->filter(fn($l) => ($progresoLecciones[$l->id]->completado ?? false))->count()
        );
        $doneNivelE  = $nivel->unidades->sum(fn($u) =>
            $u->ejercicios->filter(fn($e) => ($progresoEjercicios[$e->id]->completado ?? false))->count()
        );
        $totalN = $totalNivelL + $totalNivelE;
        $doneN  = $doneNivelL + $doneNivelE;
        $pctN   = $totalN > 0 ? round($doneN / $totalN * 100) : 0;
    @endphp
    <div class="ce-level-block" id="lvl-{{ $nivel->id }}">
        <div class="ce-level-block-hdr">
            <span class="ce-level-num">{{ $ni + 1 }}</span>
            <span class="ce-level-name">{{ $nivel->nombre }}</span>
            <span class="ce-level-pct-badge">{{ $pctN }}%</span>
        </div>

        @foreach ($nivel->unidades as $unidad)
            @php
                $totalUL = $unidad->lecciones->count();
                $totalUE = $unidad->ejercicios->count();
                $doneUL  = $unidad->lecciones->filter(fn($l) => ($progresoLecciones[$l->id]->completado ?? false))->count();
                $doneUE  = $unidad->ejercicios->filter(fn($e) => ($progresoEjercicios[$e->id]->completado ?? false))->count();
                $totalU  = $totalUL + $totalUE;
                $doneU   = $doneUL + $doneUE;
                $pctU    = $totalU > 0 ? round($doneU / $totalU * 100) : 0;
            @endphp
            <div class="ce-unit-row">
                <div class="ce-unit-row-hdr">
                    <i class="fas fa-folder-open" style="color:var(--ce-blue);font-size:.8rem"></i>
                    <span class="ce-unit-name">{{ $unidad->nombre }}</span>
                    <span class="ce-unit-counts">{{ $doneU }}/{{ $totalU }}</span>
                </div>
                <div class="ce-unit-bar-wrap">
                    <div class="ce-unit-bar-fill" data-pct="{{ $pctU }}"></div>
                </div>
                {{-- Lesson dots --}}
                <div class="ce-dots">
                    @foreach ($unidad->lecciones as $leccion)
                        @php $doneL = $progresoLecciones[$leccion->id]->completado ?? false; @endphp
                        <a href="{{ route('ce.alumno.leccion', [$curso, $leccion]) }}"
                           class="ce-dot {{ $doneL ? 'done' : '' }}"
                           title="{{ $leccion->nombre }}">
                            <i class="fas fa-{{ $leccion->tipo === 'video' ? 'play' : ($leccion->tipo === 'audio' ? 'headphones' : 'file-alt') }}"></i>
                        </a>
                    @endforeach
                    @foreach ($unidad->ejercicios as $ejercicio)
                        @php $doneE = $progresoEjercicios[$ejercicio->id]->completado ?? false; @endphp
                        <span class="ce-dot ex {{ $doneE ? 'done' : '' }}" title="{{ Str::limit($ejercicio->pregunta, 40) }}">
                            <i class="fas fa-tasks"></i>
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
(function () {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animateBars() {
        document.querySelectorAll('.ce-unit-bar-fill').forEach(b => b.style.width = b.dataset.pct + '%');
        const inner = document.getElementById('prog-bar-inner');
        if (inner) inner.style.width = inner.dataset.pct + '%';
    }

    function animateRing() {
        const ring = document.getElementById('ring-fill');
        if (!ring) return;
        const pct = parseInt(ring.dataset.pct) || 0;
        const circ = 2 * Math.PI * 36;
        ring.style.strokeDashoffset = circ - (pct / 100 * circ);
    }

    if (reduced) {
        document.querySelectorAll('.ce-prog-header,.ce-stat-card,.ce-level-block')
            .forEach(el => { el.style.opacity=1; el.style.transform='none'; });
        animateBars(); animateRing(); return;
    }

    if (typeof gsap === 'undefined') { animateBars(); animateRing(); return; }
    gsap.registerPlugin(ScrollTrigger);

    // Header
    gsap.from('#prog-header', { opacity:0, y:-20, duration:.65, ease:'power3.out' });

    setTimeout(() => { animateBars(); animateRing(); }, 400);

    // Stat cards
    gsap.set('.ce-stat-card', { opacity:0, y:16 });
    gsap.to('.ce-stat-card', {
        opacity:1, y:0, duration:.5, stagger:.09, ease:'power2.out',
        scrollTrigger: { trigger: '.ce-stats-grid', start: 'top 88%' }
    });

    // Level blocks
    gsap.set('.ce-level-block', { opacity:0, y:24 });
    gsap.utils.toArray('.ce-level-block').forEach((el, i) => {
        gsap.to(el, {
            opacity:1, y:0, duration:.5, ease:'power2.out',
            scrollTrigger: { trigger: el, start: 'top 90%' },
            delay: i * .07
        });
    });
}());
</script>
@endpush
