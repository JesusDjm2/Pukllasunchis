@extends('layouts.alumno')
@section('titulo', $leccion->nombre)

@push('styles')
<style>
:root {
    --ce-blue:#3a86ff; --ce-teal:#06d6a0; --ce-amber:#ffb703;
    --ce-purple:#8338ec; --ce-text:#1b1b2f; --ce-muted:#6c757d;
    --ce-radius:1rem;
}

/* ── Top progress strip ── */
.ce-top-strip {
    position: fixed; top: 0; left: 0; right: 0;
    height: 4px; z-index: 9999;
    background: #e9ecef;
}
.ce-top-strip-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--ce-teal), var(--ce-blue));
    width: 0; transition: width 1.2s cubic-bezier(.4,0,.2,1);
}

/* ── Breadcrumb nav ── */
.ce-breadcrumb {
    display: flex; align-items: center; flex-wrap: wrap; gap: .35rem;
    font-size: .8rem; color: var(--ce-muted);
    margin-bottom: 1.25rem;
}
.ce-breadcrumb a { color: var(--ce-blue); text-decoration: none; font-weight: 600; }
.ce-breadcrumb a:hover { text-decoration: underline; }
.ce-breadcrumb-sep { opacity: .4; }

/* ── Lesson card ── */
.ce-lesson-card {
    background: #fff;
    border-radius: var(--ce-radius);
    box-shadow: 0 4px 24px rgba(58,134,255,.10);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

/* ── Lesson header ── */
.ce-lesson-hdr {
    padding: 1.5rem 1.75rem 1.25rem;
    border-bottom: 1px solid #f0f0f0;
}
.ce-lesson-type-tag {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; padding: .25rem .7rem;
    border-radius: 99px; margin-bottom: .65rem;
}
.ce-lesson-type-tag.texto  { background: #e8f0ff; color: var(--ce-blue); }
.ce-lesson-type-tag.audio  { background: #e0fbf4; color: #00b894; }
.ce-lesson-type-tag.video  { background: #fff0f0; color: #e63946; }
.ce-lesson-title {
    font-size: clamp(1.1rem, 2.5vw, 1.45rem);
    font-weight: 800; color: var(--ce-text); margin: 0 0 .35rem;
    line-height: 1.25;
}
.ce-lesson-meta {
    font-size: .8rem; color: var(--ce-muted);
    display: flex; flex-wrap: wrap; gap: .9rem;
}
.ce-lesson-meta span { display: flex; align-items: center; gap: .3rem; }

/* ── Video wrapper ── */
.ce-video-wrap {
    position: relative; padding-bottom: 56.25%;
    height: 0; overflow: hidden;
    background: #000; border-radius: 0;
}
.ce-video-wrap iframe {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    border: 0;
}

/* ── Audio player ── */
.ce-audio-wrap {
    padding: 1.75rem;
    background: linear-gradient(135deg, #e0fbf4, #e8f0ff);
    display: flex; align-items: center; justify-content: center;
}
.ce-audio-wrap audio { width: 100%; max-width: 560px; }

/* ── Text content ── */
.ce-text-content {
    padding: 1.75rem;
    font-size: .95rem;
    line-height: 1.8;
    color: var(--ce-text);
    max-width: 720px;
}
.ce-text-content h1,.ce-text-content h2,.ce-text-content h3 {
    font-weight: 800; margin: 1.5em 0 .5em; color: var(--ce-text);
}
.ce-text-content table {
    width: 100%; border-collapse: collapse; margin: 1em 0;
    font-size: .88rem;
}
.ce-text-content th {
    background: #f0f4ff; padding: .55rem .85rem;
    text-align: left; font-weight: 700;
    border: 1px solid #dee2e6;
}
.ce-text-content td {
    padding: .5rem .85rem; border: 1px solid #dee2e6;
    vertical-align: top;
}
.ce-text-content tr:nth-child(even) td { background: #fafbff; }
.ce-text-content code {
    background: #f0f4ff; color: var(--ce-purple);
    padding: .15rem .4rem; border-radius: .3rem; font-size: .84rem;
}
.ce-text-content pre {
    background: #1b1b2f; color: #e8f0ff;
    padding: 1.25rem; border-radius: .75rem;
    overflow-x: auto; font-size: .85rem; line-height: 1.6;
}
.ce-text-content blockquote {
    border-left: 4px solid var(--ce-blue);
    padding: .75rem 1rem; margin: 1em 0;
    background: #f0f4ff; border-radius: 0 .5rem .5rem 0;
    color: var(--ce-muted); font-style: italic;
}

/* ── Notes inline if video has text ── */
.ce-notes {
    padding: 1rem 1.75rem 1.75rem;
    background: #fafbff;
    border-top: 1px solid #f0f0f0;
}
.ce-notes-label {
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--ce-muted); margin-bottom: .6rem;
}

/* ── Complete action bar ── */
.ce-action-bar {
    background: #fff;
    border-radius: var(--ce-radius);
    box-shadow: 0 4px 24px rgba(58,134,255,.10);
    padding: 1.25rem 1.5rem;
    display: flex; align-items: center; flex-wrap: wrap; gap: 1rem;
    margin-bottom: 1.5rem;
}
.ce-done-badge {
    display: flex; align-items: center; gap: .5rem;
    color: var(--ce-teal); font-weight: 700; font-size: .95rem;
}
.ce-done-badge i { font-size: 1.3rem; }
.ce-btn-complete {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--ce-teal), #00b894);
    color: #fff; border: none; border-radius: .65rem;
    padding: .7rem 1.5rem; font-weight: 700; font-size: .9rem;
    cursor: pointer; transition: opacity .2s, transform .15s;
}
.ce-btn-complete:hover { opacity: .88; transform: scale(1.02); }
.ce-nav-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    background: none; border: 2px solid #dee2e6; border-radius: .65rem;
    padding: .6rem 1.1rem; font-weight: 700; font-size: .85rem;
    color: var(--ce-text); text-decoration: none; cursor: pointer;
    transition: border-color .2s, color .2s;
}
.ce-nav-btn:hover { border-color: var(--ce-blue); color: var(--ce-blue); text-decoration: none; }
.ce-nav-btn.next {
    background: linear-gradient(135deg, var(--ce-blue), var(--ce-purple));
    color: #fff; border-color: transparent;
}
.ce-nav-btn.next:hover { opacity: .88; color: #fff; }

@media (max-width: 576px) {
    .ce-lesson-hdr { padding: 1.1rem 1.1rem .9rem; }
    .ce-text-content { padding: 1.25rem 1.1rem; }
    .ce-action-bar { flex-direction: column; align-items: stretch; }
    .ce-btn-complete, .ce-nav-btn { justify-content: center; }
}
</style>
@endpush

@section('contenido')
{{-- Reading progress strip --}}
<div class="ce-top-strip">
    <div class="ce-top-strip-fill" id="top-strip-fill" data-pct="{{ $porcentajeCurso }}"></div>
</div>

{{-- Breadcrumb --}}
<nav class="ce-breadcrumb" aria-label="breadcrumb">
    <a href="{{ route('ce.alumno.index') }}"><i class="fas fa-home fa-xs"></i> Cursos</a>
    <span class="ce-breadcrumb-sep">›</span>
    <a href="{{ route('ce.alumno.show', $curso) }}">{{ Str::limit($curso->nombre, 30) }}</a>
    <span class="ce-breadcrumb-sep">›</span>
    <span>{{ Str::limit($leccion->unidad->nombre, 28) }}</span>
</nav>

{{-- Lesson card --}}
<div class="ce-lesson-card" id="lesson-card">

    {{-- Header --}}
    <div class="ce-lesson-hdr">
        @php $tipoMap = ['texto' => 'Lectura', 'audio' => 'Audio', 'video' => 'Video']; @endphp
        <span class="ce-lesson-type-tag {{ $leccion->tipo }}">
            <i class="fas fa-{{ $leccion->tipo === 'audio' ? 'headphones' : ($leccion->tipo === 'video' ? 'play-circle' : 'file-alt') }}"></i>
            {{ $tipoMap[$leccion->tipo] ?? $leccion->tipo }}
        </span>
        <h1 class="ce-lesson-title">{{ $leccion->nombre }}</h1>
        <div class="ce-lesson-meta">
            <span><i class="fas fa-layer-group"></i> {{ $leccion->unidad->nivel->nombre }}</span>
            <span><i class="fas fa-folder-open"></i> {{ $leccion->unidad->nombre }}</span>
            @if ($leccion->duracion_min)
                <span><i class="far fa-clock"></i> {{ $leccion->duracion_min }} min</span>
            @endif
        </div>
    </div>

    {{-- Content by type --}}
    @if ($leccion->tipo === 'video')
        @if ($leccion->archivo_url)
            <div class="ce-video-wrap">
                <iframe src="{{ $leccion->archivo_url }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        @endif
        @if ($leccion->contenido_texto)
            <div class="ce-notes">
                <p class="ce-notes-label"><i class="fas fa-sticky-note mr-1"></i>Notas de la lección</p>
                <div class="ce-text-content p-0" style="padding:0!important">
                    {!! $leccion->contenido_texto !!}
                </div>
            </div>
        @endif

    @elseif ($leccion->tipo === 'audio')
        @if ($leccion->archivo_url)
            <div class="ce-audio-wrap">
                <audio controls>
                    <source src="{{ $leccion->archivo_url }}">
                </audio>
            </div>
        @endif
        @if ($leccion->contenido_texto)
            <div class="ce-text-content">
                {!! $leccion->contenido_texto !!}
            </div>
        @endif

    @else
        <div class="ce-text-content">
            {!! $leccion->contenido_texto !!}
        </div>
    @endif
</div>

{{-- Action bar --}}
<div class="ce-action-bar" id="action-bar">
    @if ($anterior)
        <a href="{{ route('ce.alumno.leccion', [$curso, $anterior]) }}" class="ce-nav-btn">
            <i class="fas fa-arrow-left"></i> Anterior
        </a>
    @endif

    <div class="flex-grow-1"></div>

    @if ($completada)
        <span class="ce-done-badge">
            <i class="fas fa-check-circle"></i> Lección completada
        </span>
    @else
        <form action="{{ route('ce.alumno.leccion.completar', [$curso, $leccion]) }}" method="POST" id="form-completar">
            @csrf
            <button type="submit" class="ce-btn-complete" id="btn-completar">
                <i class="fas fa-check"></i> Marcar como completada
            </button>
        </form>
    @endif

    @if ($siguiente)
        <a href="{{ route('ce.alumno.leccion', [$curso, $siguiente]) }}" class="ce-nav-btn next">
            Siguiente <i class="fas fa-arrow-right"></i>
        </a>
    @else
        <a href="{{ route('ce.alumno.show', $curso) }}" class="ce-nav-btn next">
            Ver curso <i class="fas fa-list"></i>
        </a>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
(function () {
    // Progress strip
    setTimeout(() => {
        const strip = document.getElementById('top-strip-fill');
        if (strip) strip.style.width = strip.dataset.pct + '%';
    }, 200);

    if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    // Lesson card entrance
    gsap.from('#lesson-card', { opacity: 0, y: 20, duration: .55, ease: 'power3.out', delay: .1 });
    gsap.from('#action-bar',  { opacity: 0, y: 10, duration: .4,  ease: 'power2.out', delay: .35 });

    // Complete button pulse on hover
    const btn = document.getElementById('btn-completar');
    if (btn) {
        btn.addEventListener('mouseenter', () => gsap.to(btn, { scale: 1.04, duration: .15 }));
        btn.addEventListener('mouseleave', () => gsap.to(btn, { scale: 1, duration: .15 }));
        btn.addEventListener('click', () => {
            gsap.to(btn, {
                scale: .95, duration: .1, yoyo: true, repeat: 1,
                onComplete: () => document.getElementById('form-completar').submit()
            });
            return false;
        });
    }
}());
</script>
@endpush
