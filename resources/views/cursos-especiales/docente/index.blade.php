@extends('layouts.docente')
@section('titulo', 'Mis Cursos Asincrónicos')

@push('styles')
<style>
/* ── Variables ── */
:root {
    --ced-blue:    #4e73df;
    --ced-dark:    #2d3561;
    --ced-teal:    #1cc88a;
    --ced-amber:   #f6c23e;
    --ced-red:     #e74a3b;
    --ced-muted:   #858796;
    --ced-radius:  .75rem;
    --ced-shadow:  0 .15rem 1.75rem rgba(58,59,69,.10);
    --ced-shadow-hover: 0 .5rem 2.5rem rgba(58,59,69,.20);
}

/* ── Shell ── */
.ced-page {
    padding: 1rem 1.5rem 3rem;
}
@media (max-width: 767.98px) {
    .ced-page { padding: .75rem .75rem 2.5rem; }
}

/* ── Hero ── */
.ced-hero {
    background: linear-gradient(135deg, #1b1b2f 0%, #2d3561 55%, #4e73df 100%);
    border-radius: var(--ced-radius);
    padding: 2rem 2rem 1.75rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.ced-hero::before {
    content: '';
    position: absolute;
    width: 280px; height: 280px;
    background: rgba(78,115,223,.18);
    border-radius: 50%;
    top: -80px; right: -60px;
    pointer-events: none;
}
.ced-hero::after {
    content: '';
    position: absolute;
    width: 160px; height: 160px;
    background: rgba(28,200,138,.12);
    border-radius: 50%;
    bottom: -50px; left: 40%;
    pointer-events: none;
}
.ced-hero-kicker {
    font-size: .68rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: rgba(255,255,255,.55);
    margin-bottom: .35rem;
}
.ced-hero-title {
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    font-weight: 800; color: #fff; margin: 0 0 .4rem; line-height: 1.2;
}
.ced-hero-sub {
    color: rgba(255,255,255,.65); font-size: .875rem; margin: 0;
}
.ced-hero-icon {
    font-size: 4rem; opacity: .12;
    position: absolute; right: 1.75rem; top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}
@media (max-width: 575.98px) {
    .ced-hero { padding: 1.5rem 1.25rem; }
    .ced-hero-icon { display: none; }
}

/* ── Summary chips ── */
.ced-chips { display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1.75rem; }
.ced-chip {
    display: inline-flex; align-items: center; gap: .45rem;
    background: #fff; border: 1px solid #e3e6f0;
    border-radius: 99px; padding: .3rem .9rem;
    font-size: .78rem; font-weight: 700; color: #5a5c69;
    box-shadow: 0 1px 4px rgba(58,59,69,.06);
}
.ced-chip i { color: var(--ced-blue); font-size: .75rem; }

/* ── Section label ── */
.ced-section-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--ced-muted); margin-bottom: 1.1rem;
    display: flex; align-items: center; gap: .5rem;
}
.ced-section-label::after { content: ''; flex: 1; height: 1px; background: #e3e6f0; }

/* ── Card ── */
.ced-card {
    background: #fff;
    border: 0;
    border-radius: var(--ced-radius);
    box-shadow: var(--ced-shadow);
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    transition: transform .28s cubic-bezier(.4,0,.2,1), box-shadow .28s cubic-bezier(.4,0,.2,1);
    will-change: transform;
}
.ced-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--ced-shadow-hover);
}

/* ── Banner ── */
.ced-banner {
    height: 168px;
    position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.ced-banner img {
    width: 100%; height: 100%; object-fit: cover;
    position: absolute; inset: 0;
    transition: transform .5s ease;
}
.ced-card:hover .ced-banner img { transform: scale(1.06); }
.ced-banner-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.45) 0%, transparent 55%);
}
.ced-banner-icon {
    font-size: 3.2rem; color: rgba(255,255,255,.75);
    position: relative; z-index: 1;
}
.ced-banner-badge {
    position: absolute; top: .75rem; right: .75rem; z-index: 2;
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .68rem; font-weight: 700; padding: .22rem .6rem;
    border-radius: 99px; backdrop-filter: blur(6px);
}
.ced-banner-badge.activo   { background: rgba(28,200,138,.85);  color: #fff; }
.ced-banner-badge.inactivo { background: rgba(231,74,59,.82);   color: #fff; }

/* Nivel count chip en el banner */
.ced-banner-levels {
    position: absolute; bottom: .75rem; left: .85rem; z-index: 2;
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(0,0,0,.45); backdrop-filter: blur(6px);
    color: rgba(255,255,255,.9);
    font-size: .7rem; font-weight: 700; padding: .2rem .6rem;
    border-radius: 99px;
}

/* ── Card body ── */
.ced-body { padding: 1.2rem 1.2rem .9rem; flex: 1; display: flex; flex-direction: column; }
.ced-title {
    font-size: .97rem; font-weight: 800; color: var(--ced-dark);
    margin: 0 0 .45rem; line-height: 1.3;
}
.ced-desc {
    font-size: .81rem; color: var(--ced-muted); flex: 1;
    margin: 0 0 .9rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

/* ── Card footer ── */
.ced-footer { padding: 0 1.2rem 1.2rem; display: flex; flex-direction: column; gap: .45rem; }
.ced-btn {
    display: flex; align-items: center; justify-content: center; gap: .45rem;
    border-radius: .5rem; padding: .6rem 1rem;
    font-weight: 700; font-size: .83rem;
    text-decoration: none; border: none; cursor: pointer;
    transition: opacity .18s, transform .14s;
    line-height: 1;
}
.ced-btn:hover { opacity: .88; transform: scale(1.015); text-decoration: none; }
.ced-btn-primary {
    background: linear-gradient(135deg, var(--ced-blue), #224abe);
    color: #fff !important;
}
.ced-btn-secondary {
    background: #f8f9fc; color: var(--ced-blue) !important;
    border: 1.5px solid #d1d9f0;
}
.ced-btn-secondary:hover { background: #eef2ff; }

/* ── Empty state ── */
.ced-empty {
    text-align: center; padding: 4.5rem 1rem 3rem;
    color: var(--ced-muted);
}
.ced-empty-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, #eef2ff, #e0e7ff);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 2rem; color: var(--ced-blue);
    margin-bottom: 1.25rem; opacity: .75;
}
.ced-empty h4 { font-size: 1rem; font-weight: 700; color: #5a5c69; margin-bottom: .4rem; }
.ced-empty p  { font-size: .84rem; max-width: 340px; margin: 0 auto; }
</style>
@endpush

@section('contenido')
<div class="container-fluid ced-page">

    {{-- ── Hero ─────────────────────────────────────────────── --}}
    <div class="ced-hero" id="ced-hero">
        <p class="ced-hero-kicker"><i class="fas fa-chalkboard-teacher mr-1"></i>Panel Docente</p>
        <h1 class="ced-hero-title">Mis Cursos Asincrónicos</h1>
        <p class="ced-hero-sub">Gestiona el contenido de los cursos que tienes asignados.</p>
        <i class="fas fa-graduation-cap ced-hero-icon"></i>
    </div>

    {{-- ── Chips de resumen ─────────────────────────────────── --}}
    @if ($cursos->isNotEmpty())
    <div class="ced-chips" id="ced-chips">
        <span class="ced-chip">
            <i class="fas fa-book"></i>
            {{ $cursos->count() }} {{ Str::plural('curso', $cursos->count()) }} asignado{{ $cursos->count() > 1 ? 's' : '' }}
        </span>
        <span class="ced-chip">
            <i class="fas fa-layer-group"></i>
            {{ $cursos->sum(fn($c) => $c->niveles->count()) }} {{ Str::plural('niveles', $cursos->sum(fn($c) => $c->niveles->count())) }} en total
        </span>
        @if ($cursos->where('activo', true)->count())
        <span class="ced-chip">
            <i class="fas fa-circle" style="color:#1cc88a;font-size:.6rem"></i>
            {{ $cursos->where('activo', true)->count() }} activo{{ $cursos->where('activo', true)->count() > 1 ? 's' : '' }}
        </span>
        @endif
    </div>
    @endif

    {{-- ── Flash ────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Grid de cursos ───────────────────────────────────── --}}
    @if ($cursos->isEmpty())
        <div class="ced-empty">
            <div class="ced-empty-icon"><i class="fas fa-book-open"></i></div>
            <h4>Sin cursos asignados aún</h4>
            <p>Cuando el administrador te asigne un curso asincrónico aparecerá aquí para que puedas gestionar su contenido.</p>
        </div>
    @else
        <p class="ced-section-label"><i class="fas fa-layer-group"></i> Tus cursos</p>

        <div class="row" id="ced-grid">
            @php
                $gradients = [
                    'linear-gradient(135deg,#4e73df,#224abe)',
                    'linear-gradient(135deg,#1cc88a,#13855c)',
                    'linear-gradient(135deg,#36b9cc,#1a8a9a)',
                    'linear-gradient(135deg,#f6c23e,#d4a017)',
                    'linear-gradient(135deg,#e74a3b,#be2617)',
                    'linear-gradient(135deg,#6f42c1,#4e2a8a)',
                ];
            @endphp

            @foreach ($cursos as $curso)
                @php $grad = $gradients[$loop->index % count($gradients)]; @endphp
                <div class="col-12 col-sm-6 col-lg-4 mb-4 ced-card-col">
                    <div class="ced-card">

                        {{-- Banner --}}
                        <div class="ced-banner" style="background: {{ $grad }}">
                            @if ($curso->imagen)
                                <img src="{{ $curso->imagen_url }}" alt="{{ $curso->nombre }}">
                                <div class="ced-banner-overlay"></div>
                            @else
                                <i class="fas fa-graduation-cap ced-banner-icon"></i>
                            @endif

                            <span class="ced-banner-badge {{ $curso->activo ? 'activo' : 'inactivo' }}">
                                <i class="fas fa-circle" style="font-size:.45rem"></i>
                                {{ $curso->activo ? 'Activo' : 'Inactivo' }}
                            </span>

                            @if ($curso->niveles->count())
                            <span class="ced-banner-levels">
                                <i class="fas fa-layer-group"></i>
                                {{ $curso->niveles->count() }} {{ Str::plural('nivel', $curso->niveles->count()) }}
                            </span>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="ced-body">
                            <h3 class="ced-title">{{ $curso->nombre }}</h3>
                            @if ($curso->descripcion)
                                <p class="ced-desc">{{ $curso->descripcion }}</p>
                            @else
                                <p class="ced-desc text-muted fst-italic">Sin descripción.</p>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="ced-footer">
                            <a href="{{ route('ce.docente.show', $curso) }}" class="ced-btn ced-btn-primary">
                                <i class="fas fa-sitemap"></i> Ver estructura y contenido
                            </a>
                            <a href="{{ route('ce.docente.edit', $curso) }}" class="ced-btn ced-btn-secondary">
                                <i class="fas fa-edit"></i> Editar información del curso
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
(function () {
    if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    gsap.registerPlugin(ScrollTrigger);

    /* Hero */
    gsap.from('#ced-hero', {
        y: -28, opacity: 0, duration: .65, ease: 'power3.out'
    });

    /* Chips */
    gsap.from('#ced-chips .ced-chip', {
        y: 12, opacity: 0, duration: .4,
        stagger: .08, ease: 'power2.out', delay: .3
    });

    /* Cards */
    const cols = document.querySelectorAll('.ced-card-col');
    if (cols.length) {
        gsap.set(cols, { opacity: 0, y: 40 });
        gsap.to(cols, {
            opacity: 1, y: 0,
            duration: .55, stagger: .1, ease: 'power3.out',
            scrollTrigger: {
                trigger: '#ced-grid',
                start: 'top 88%',
            }
        });
    }

    /* Hover magnético sutil en cards */
    document.querySelectorAll('.ced-card').forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var cx = rect.left + rect.width  / 2;
            var cy = rect.top  + rect.height / 2;
            var dx = (e.clientX - cx) / rect.width  * 6;
            var dy = (e.clientY - cy) / rect.height * 4;
            gsap.to(card, { rotateY: dx, rotateX: -dy, duration: .35, ease: 'power2.out', transformPerspective: 800 });
        });
        card.addEventListener('mouseleave', function () {
            gsap.to(card, { rotateY: 0, rotateX: 0, duration: .55, ease: 'elastic.out(1,.5)' });
        });
    });
}());
</script>
@endpush
