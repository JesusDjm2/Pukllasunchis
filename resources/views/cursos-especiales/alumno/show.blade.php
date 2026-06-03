@extends('layouts.alumno')
@section('titulo', $curso->nombre)

@push('styles')
<style>
:root {
    --ce-blue:#3a86ff; --ce-teal:#06d6a0; --ce-amber:#ffb703;
    --ce-purple:#8338ec; --ce-text:#1b1b2f; --ce-muted:#6c757d;
    --ce-radius:1rem; --ce-shadow:0 4px 24px rgba(58,134,255,.10);
}

/* ── Sticky header ── */
.ce-sticky-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: .85rem 1.25rem;
    margin: -1rem -1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.ce-sticky-back {
    display: flex; align-items: center; gap: .4rem;
    color: var(--ce-blue); font-weight: 700; font-size: .88rem;
    text-decoration: none; white-space: nowrap;
    transition: opacity .2s;
}
.ce-sticky-back:hover { opacity: .7; color: var(--ce-blue); text-decoration: none; }
.ce-sticky-title {
    font-weight: 800; font-size: .95rem; color: var(--ce-text);
    flex: 1; min-width: 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ce-sticky-pct {
    font-size: .8rem; font-weight: 700; color: var(--ce-teal);
    white-space: nowrap;
}
.ce-global-bar-wrap {
    width: 100%; height: 6px;
    background: #e9ecef; border-radius: 99px; overflow: hidden;
    margin-top: .4rem;
}
.ce-global-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--ce-teal), var(--ce-blue));
    border-radius: 99px;
    width: 0;
    transition: width 1.4s cubic-bezier(.4,0,.2,1);
}

/* ── Stats row ── */
.ce-stats-row {
    display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.75rem;
}
.ce-stat {
    background: #fff; border-radius: .75rem;
    padding: .9rem 1.25rem;
    box-shadow: var(--ce-shadow);
    display: flex; align-items: center; gap: .8rem;
    flex: 1; min-width: 140px;
}
.ce-stat-icon {
    width: 40px; height: 40px; border-radius: .6rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.ce-stat-icon.blue  { background: #e8f0ff; color: var(--ce-blue); }
.ce-stat-icon.teal  { background: #e0fbf4; color: var(--ce-teal); }
.ce-stat-icon.amber { background: #fff8e0; color: var(--ce-amber); }
.ce-stat-val { font-size: 1.3rem; font-weight: 800; color: var(--ce-text); line-height: 1; }
.ce-stat-label { font-size: .75rem; color: var(--ce-muted); margin-top: .15rem; }

/* ── Accordion levels ── */
.ce-nivel {
    background: #fff; border-radius: var(--ce-radius);
    box-shadow: var(--ce-shadow); margin-bottom: 1rem; overflow: hidden;
}
.ce-nivel-header {
    display: flex; align-items: center; gap: .75rem;
    padding: 1rem 1.25rem; cursor: pointer;
    background: linear-gradient(135deg, #1b1b2f, #2d3561);
    color: #fff; border: none; width: 100%; text-align: left;
    transition: opacity .2s;
}
.ce-nivel-header:hover { opacity: .9; }
.ce-nivel-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; font-weight: 800; flex-shrink: 0;
}
.ce-nivel-title { font-weight: 700; font-size: .95rem; flex: 1; }
.ce-nivel-meta { font-size: .75rem; opacity: .7; white-space: nowrap; }
.ce-nivel-chevron { transition: transform .3s ease; font-size: .85rem; opacity: .7; }
.ce-nivel-body {
    display: none;
    padding: .75rem;
    background: #fafbff;
}
.ce-nivel-body.open { display: block; }
.ce-nivel-chevron.open { transform: rotate(180deg); }

/* ── Unidad block ── */
.ce-unidad {
    background: #fff; border-radius: .75rem;
    border: 1px solid #e9ecef; margin-bottom: .6rem; overflow: hidden;
}
.ce-unidad-header {
    display: flex; align-items: center; gap: .65rem;
    padding: .75rem 1rem; cursor: pointer;
    border: none; background: none; width: 100%; text-align: left;
}
.ce-unidad-icon { color: var(--ce-blue); font-size: .9rem; }
.ce-unidad-name { font-weight: 700; font-size: .88rem; color: var(--ce-text); flex: 1; }
.ce-unidad-count { font-size: .75rem; color: var(--ce-muted); }
.ce-unidad-chevron { font-size: .8rem; color: var(--ce-muted); transition: transform .25s; }
.ce-unidad-body { display: none; border-top: 1px solid #f0f0f0; }
.ce-unidad-body.open { display: block; }
.ce-unidad-chevron.open { transform: rotate(180deg); }

/* ── Lesson row ── */
.ce-lesson {
    display: flex; align-items: center; gap: .75rem;
    padding: .7rem 1rem;
    text-decoration: none; color: var(--ce-text);
    transition: background .15s;
    border-bottom: 1px solid #f5f5f5;
}
.ce-lesson:last-child { border-bottom: none; }
.ce-lesson:hover { background: #f0f4ff; text-decoration: none; color: var(--ce-text); }
.ce-lesson-status {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; flex-shrink: 0;
    border: 2px solid #dee2e6; color: #dee2e6;
    background: #fff;
}
.ce-lesson-status.done {
    background: var(--ce-teal); border-color: var(--ce-teal); color: #fff;
}
.ce-lesson-type-icon { font-size: .8rem; color: var(--ce-muted); flex-shrink: 0; }
.ce-lesson-name { font-size: .86rem; font-weight: 600; flex: 1; }
.ce-lesson-dur { font-size: .74rem; color: var(--ce-muted); white-space: nowrap; }

/* ── Progress mini-bar inside unidad ── */
.ce-unidad-progress {
    height: 3px; background: #e9ecef;
    border-radius: 99px; overflow: hidden; margin-top: -.1rem;
}
.ce-unidad-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--ce-teal), var(--ce-blue));
    width: 0; transition: width 1s ease .3s;
}

@media (max-width: 576px) {
    .ce-sticky-header { flex-wrap: wrap; }
    .ce-stats-row { flex-direction: column; }
    .ce-stat { min-width: unset; }
}
</style>
@endpush

@section('contenido')
{{-- Sticky progress header --}}
<div class="ce-sticky-header">
    <a href="{{ route('ce.alumno.index') }}" class="ce-sticky-back">
        <i class="fas fa-arrow-left"></i> Mis cursos
    </a>
    <span class="ce-sticky-title">{{ $curso->nombre }}</span>
    <span class="ce-sticky-pct" id="pct-label">{{ $porcentaje }}%</span>
    <div class="ce-global-bar-wrap" style="flex-basis:100%">
        <div class="ce-global-bar-fill" id="global-bar" data-pct="{{ $porcentaje }}"></div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- Stats --}}
<div class="ce-stats-row">
    <div class="ce-stat">
        <div class="ce-stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div>
            <div class="ce-stat-val">{{ $curso->niveles->count() }}</div>
            <div class="ce-stat-label">Niveles</div>
        </div>
    </div>
    <div class="ce-stat">
        <div class="ce-stat-icon teal"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="ce-stat-val">{{ $completadasCount }}</div>
            <div class="ce-stat-label">Completadas</div>
        </div>
    </div>
    <div class="ce-stat">
        <div class="ce-stat-icon amber"><i class="fas fa-book-open"></i></div>
        <div>
            <div class="ce-stat-val">{{ $totalLecciones }}</div>
            <div class="ce-stat-label">Lecciones totales</div>
        </div>
    </div>
</div>

{{-- Levels accordion --}}
@forelse ($curso->niveles as $ni => $nivel)
    <div class="ce-nivel" id="nivel-{{ $nivel->id }}">
        <button class="ce-nivel-header" onclick="toggleNivel({{ $nivel->id }})">
            <span class="ce-nivel-num">{{ $ni + 1 }}</span>
            <span class="ce-nivel-title">{{ $nivel->nombre }}</span>
            <span class="ce-nivel-meta">{{ $nivel->unidades->count() }} unidades</span>
            <i class="fas fa-chevron-down ce-nivel-chevron" id="chev-nivel-{{ $nivel->id }}"></i>
        </button>

        <div class="ce-nivel-body {{ $ni === 0 ? 'open' : '' }}" id="body-nivel-{{ $nivel->id }}">
            @foreach ($nivel->unidades as $unidad)
                @php
                    $totalU     = $unidad->lecciones->count();
                    $doneU      = $unidad->lecciones->filter(fn($l) => isset($completadas[$l->id]))->count();
                    $pctU       = $totalU > 0 ? round($doneU / $totalU * 100) : 0;
                @endphp
                <div class="ce-unidad">
                    <button class="ce-unidad-header" onclick="toggleUnidad({{ $unidad->id }})">
                        <i class="fas fa-folder-open ce-unidad-icon"></i>
                        <span class="ce-unidad-name">{{ $unidad->nombre }}</span>
                        <span class="ce-unidad-count">{{ $doneU }}/{{ $totalU }}</span>
                        <i class="fas fa-chevron-down ce-unidad-chevron {{ $ni === 0 ? 'open' : '' }}"
                           id="chev-unidad-{{ $unidad->id }}"></i>
                    </button>
                    <div class="ce-unidad-progress">
                        <div class="ce-unidad-progress-fill" data-pct="{{ $pctU }}"></div>
                    </div>
                    <div class="ce-unidad-body {{ $ni === 0 ? 'open' : '' }}" id="body-unidad-{{ $unidad->id }}">
                        @foreach ($unidad->lecciones as $leccion)
                            @php $done = isset($completadas[$leccion->id]); @endphp
                            <a href="{{ route('ce.alumno.leccion', [$curso, $leccion]) }}" class="ce-lesson">
                                <span class="ce-lesson-status {{ $done ? 'done' : '' }}">
                                    <i class="fas fa-{{ $done ? 'check' : 'circle' }}"></i>
                                </span>
                                <i class="fas fa-{{ $leccion->tipo === 'audio' ? 'headphones' : ($leccion->tipo === 'video' ? 'play-circle' : 'file-alt') }} ce-lesson-type-icon"></i>
                                <span class="ce-lesson-name">{{ $leccion->nombre }}</span>
                                @if ($leccion->duracion_min)
                                    <span class="ce-lesson-dur">
                                        <i class="far fa-clock mr-1"></i>{{ $leccion->duracion_min }} min
                                    </span>
                                @endif
                            </a>
                        @endforeach
                        @if ($unidad->ejercicios->isNotEmpty())
                            <div class="ce-lesson" style="cursor:default; background:#fffbf0;">
                                <span class="ce-lesson-status" style="border-color:var(--ce-amber);color:var(--ce-amber)">
                                    <i class="fas fa-tasks"></i>
                                </span>
                                <i class="fas fa-tasks ce-lesson-type-icon" style="color:var(--ce-amber)"></i>
                                <span class="ce-lesson-name">{{ $unidad->ejercicios->count() }} ejercicio(s) de práctica</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-clock fa-2x mb-3 d-block opacity-50"></i>
        Contenido próximamente.
    </div>
@endforelse
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
/* ── Accordion toggles ── */
function toggleNivel(id) {
    const body = document.getElementById('body-nivel-' + id);
    const chev = document.getElementById('chev-nivel-' + id);
    body.classList.toggle('open');
    chev.classList.toggle('open');
}
function toggleUnidad(id) {
    const body = document.getElementById('body-unidad-' + id);
    const chev = document.getElementById('chev-unidad-' + id);
    body.classList.toggle('open');
    chev.classList.toggle('open');
}

/* ── GSAP ── */
(function () {
    if (typeof gsap === 'undefined') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.ce-stat,.ce-nivel').forEach(el => el.style.opacity = 1);
        animateBars();
        return;
    }
    gsap.registerPlugin(ScrollTrigger);

    // Global progress bar
    setTimeout(() => {
        const bar = document.getElementById('global-bar');
        if (bar) bar.style.width = bar.dataset.pct + '%';
    }, 300);

    // Stats row
    gsap.set('.ce-stat', { opacity: 0, y: 20 });
    gsap.to('.ce-stat', {
        opacity: 1, y: 0,
        duration: .55, stagger: .1, ease: 'power2.out',
    });

    // Nivel cards stagger on scroll
    gsap.set('.ce-nivel', { opacity: 0, y: 30 });
    gsap.utils.toArray('.ce-nivel').forEach((el, i) => {
        gsap.to(el, {
            opacity: 1, y: 0,
            duration: .55, ease: 'power2.out',
            delay: i * .08,
            scrollTrigger: { trigger: el, start: 'top 88%' }
        });
    });

    // Unit progress bars
    ScrollTrigger.create({
        trigger: '.ce-nivel',
        start: 'top 90%',
        onEnter: animateBars,
    });

    function animateBars() {
        document.querySelectorAll('.ce-unidad-progress-fill').forEach(bar => {
            bar.style.width = bar.dataset.pct + '%';
        });
    }
    animateBars();
}());
</script>
@endpush
