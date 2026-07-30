@extends('layouts.alumno')
@section('titulo', 'Cursos Asincrónicos')

@push('styles')
<style>
/* ── Variables ── */
:root {
    --ce-blue:   #3a86ff;
    --ce-teal:   #06d6a0;
    --ce-amber:  #ffb703;
    --ce-purple: #8338ec;
    --ce-bg:     #f0f4ff;
    --ce-card:   #ffffff;
    --ce-text:   #1b1b2f;
    --ce-muted:  #6c757d;
    --ce-radius: 1rem;
    --ce-shadow: 0 4px 24px rgba(58,134,255,.10);
    --ce-shadow-hover: 0 8px 32px rgba(58,134,255,.22);
}

/* ── Page shell ── */
.ce-page { padding: 0 0 3rem; }

/* ── Hero header ── */
.ce-hero {
    background: linear-gradient(135deg, #1b1b2f 0%, #2d3561 60%, #3a86ff 100%);
    border-radius: var(--ce-radius);
    padding: 2.5rem 2rem;
    margin-bottom: 2.5rem;
    position: relative;
    overflow: hidden;
}
.ce-hero::before {
    content: '';
    position: absolute;
    width: 320px; height: 320px;
    background: rgba(58,134,255,.15);
    border-radius: 50%;
    top: -80px; right: -60px;
    pointer-events: none;
}
.ce-hero-title {
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 .4rem;
    line-height: 1.2;
}
.ce-hero-sub {
    color: rgba(255,255,255,.72);
    font-size: .95rem;
    margin: 0;
}
.ce-hero-icon {
    font-size: 3.5rem;
    opacity: .25;
    position: absolute;
    right: 2rem; top: 50%;
    transform: translateY(-50%);
}

/* ── Section title ── */
.ce-section-title {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--ce-muted);
    margin-bottom: 1.2rem;
}

/* ── Course card ── */
.ce-card {
    background: var(--ce-card);
    border-radius: var(--ce-radius);
    box-shadow: var(--ce-shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: transform .3s ease, box-shadow .3s ease;
    will-change: transform;
}
.ce-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--ce-shadow-hover);
}

/* ── Card banner ── */
.ce-card-banner {
    height: 160px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--ce-blue), var(--ce-purple));
    display: flex;
    align-items: center;
    justify-content: center;
}
.ce-card-banner img {
    width: 100%; height: 100%;
    object-fit: cover;
    position: absolute; inset: 0;
}
.ce-card-banner-icon {
    font-size: 3.5rem;
    color: rgba(255,255,255,.85);
    position: relative;
    z-index: 1;
}
.ce-card-badge {
    position: absolute;
    top: .75rem; right: .75rem;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(6px);
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    padding: .2rem .6rem;
    border-radius: 2rem;
    z-index: 2;
}

/* ── Card body ── */
.ce-card-body {
    padding: 1.4rem 1.4rem 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.ce-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--ce-text);
    margin: 0 0 .5rem;
    line-height: 1.3;
}
.ce-card-desc {
    font-size: .84rem;
    color: var(--ce-muted);
    margin: 0 0 1rem;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── Progress ── */
.ce-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .78rem;
    font-weight: 600;
    color: var(--ce-muted);
    margin-bottom: .35rem;
}
.ce-progress-label .pct { color: var(--ce-teal); font-size: .9rem; }
.ce-progress-bar-wrap {
    height: 8px;
    background: #e9ecef;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 1rem;
}
.ce-progress-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--ce-teal), var(--ce-blue));
    width: 0; /* animated by GSAP */
    transition: width 1.2s cubic-bezier(.4,0,.2,1);
}

/* ── Card footer / actions ── */
.ce-card-footer {
    padding: 0 1.4rem 1.4rem;
}
.ce-btn-primary {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: linear-gradient(135deg, var(--ce-blue), var(--ce-purple));
    color: #fff !important;
    border: none;
    border-radius: .65rem;
    padding: .65rem 1.2rem;
    font-weight: 700;
    font-size: .88rem;
    width: 100%;
    text-decoration: none;
    transition: opacity .2s, transform .15s;
}
.ce-btn-primary:hover { opacity: .88; transform: scale(1.02); }
.ce-btn-secondary {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: transparent;
    color: var(--ce-blue) !important;
    border: 2px solid var(--ce-blue);
    border-radius: .65rem;
    padding: .55rem 1.2rem;
    font-weight: 700;
    font-size: .82rem;
    width: 100%;
    text-decoration: none;
    margin-top: .5rem;
    transition: background .2s, color .2s;
}
.ce-btn-secondary:hover { background: var(--ce-blue); color: #fff !important; }
.ce-btn-enroll {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: linear-gradient(135deg, var(--ce-teal), #00b894);
    color: #fff !important;
    border: none;
    border-radius: .65rem;
    padding: .65rem 1.2rem;
    font-weight: 700;
    font-size: .88rem;
    width: 100%;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
}
.ce-btn-enroll:hover { opacity: .88; transform: scale(1.02); }

/* ── Empty state ── */
.ce-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: var(--ce-muted);
}
.ce-empty i { font-size: 3rem; margin-bottom: 1rem; opacity: .4; }

/* ── Responsive ── */
@media (max-width: 576px) {
    .ce-hero { padding: 1.75rem 1.25rem; }
    .ce-hero-icon { display: none; }
    .ce-card-banner { height: 130px; }
}
</style>
@endpush

@section('contenido')
<div class="ce-page">

    {{-- Hero --}}
    <div class="ce-hero">
        <h1 class="ce-hero-title">Cursos Asincrónicos</h1>
        <p class="ce-hero-sub">Aprende a tu ritmo. Avanza cuando quieras, desde donde estés.</p>
        <i class="fas fa-graduation-cap ce-hero-icon"></i>
    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Listo!',
                    text: @json(session('success')),
                    confirmButtonColor: '#28a745',
                    timer: 4000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonColor: '#dc3545'
                });
            });
        </script>
    @endif

    @if ($cursos->isEmpty())
        <div class="ce-empty">
            <i class="fas fa-book-open d-block"></i>
            <p>No hay cursos disponibles por el momento.</p>
        </div>
    @else
        <p class="ce-section-title">
            <i class="fas fa-layer-group mr-1"></i>
             {{ $cursos->count() }} {{ Str::plural('curso', $cursos->count()) }} disponible{{ $cursos->count() > 1 ? 's' : '' }}
         </p>

        <div class="row" id="ce-cursos-grid">
            @foreach ($cursos as $curso)
                @php
                    $yaInscrito  = isset($inscrito[$curso->id]);
                    $pct         = $yaInscrito ? ($progresoPorCurso[$curso->id] ?? 0) : 0;
                    $gradientes  = [
                        'linear-gradient(135deg,#3a86ff,#8338ec)',
                        'linear-gradient(135deg,#06d6a0,#3a86ff)',
                        'linear-gradient(135deg,#ffb703,#fb5607)',
                        'linear-gradient(135deg,#8338ec,#ff006e)',
                    ];
                    $grad = $gradientes[$loop->index % count($gradientes)];
                @endphp
                <div class="col-12 col-sm-6 col-lg-4 mb-4 ce-card-col">
                    <div class="ce-card">
                        <div class="ce-card-banner" style="background:{{ $grad }}">
                            @if ($curso->imagen)
                                <img src="{{ $curso->imagen_url }}" alt="{{ $curso->nombre }}">
                            @else
                                <i class="fas fa-graduation-cap ce-card-banner-icon"></i>
                            @endif
                            @if ($yaInscrito)
                                <span class="ce-card-badge">
                                    <i class="fas fa-check-circle mr-1"></i>Inscrito
                                </span>
                            @endif
                        </div>

                        <div class="ce-card-body">
                            <h3 class="ce-card-title">{{ $curso->nombre }}</h3>
                            @if ($curso->descripcion)
                                <p class="ce-card-desc">{{ $curso->descripcion }}</p>
                            @endif

                            @if ($yaInscrito)
                                <div class="ce-progress-label">
                                    <span>Tu progreso</span>
                                    <span class="pct">{{ $pct }}%</span>
                                </div>
                                <div class="ce-progress-bar-wrap">
                                    <div class="ce-progress-bar-fill" data-pct="{{ $pct }}"></div>
                                </div>
                            @endif
                        </div>

                        <div class="ce-card-footer">
                            @if ($yaInscrito)
                                <a href="{{ route('ce.alumno.show', $curso) }}" class="ce-btn-primary">
                                    <i class="fas fa-play-circle"></i>
                                    {{ $pct > 0 ? 'Continuar' : 'Comenzar' }}
                                </a>
                                <a href="{{ route('ce.alumno.progreso', $curso) }}" class="ce-btn-secondary">
                                    <i class="fas fa-chart-line"></i> Ver mi progreso
                                </a>
                            @else
                                <form action="{{ route('ce.alumno.inscribir', $curso) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="ce-btn-enroll">
                                        <i class="fas fa-plus-circle"></i> Inscribirme gratis
                                    </button>
                                </form>
                            @endif
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
    // Animate progress bars (always, no GSAP needed)
    function animateBars() {
        document.querySelectorAll('.ce-progress-bar-fill').forEach(function (bar) {
            bar.style.width = (bar.dataset.pct || 0) + '%';
        });
    }

    if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        animateBars();
        return;
    }

    gsap.registerPlugin(ScrollTrigger);

    // Hero entrance
    gsap.from('.ce-hero', {
        y: -24, opacity: 0, duration: .65, ease: 'power3.out'
    });

    // Set initial state on the col wrappers (not the cards, so cards stay visible as fallback)
    gsap.set('.ce-card-col', { opacity: 0, y: 40 });

    gsap.to('.ce-card-col', {
        opacity: 1, y: 0,
        duration: .6,
        stagger: .11,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: '#ce-cursos-grid',
            start: 'top 88%',
        },
        onComplete: animateBars,
    });

    // Fallback: if already in viewport on load, fire immediately
    setTimeout(animateBars, 600);
}());
</script>
@endpush
