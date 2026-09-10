@extends('layouts.alumno')
@section('titulo', $leccion->nombre)

@push('styles')
<style>
/* ══ Fix sticky: SB Admin 2 pone overflow-x:hidden en #content-wrapper
   lo que rompe position:sticky en todos los hijos.
   overflow-x:clip hace el mismo clip visual sin crear scroll container. ══ */
#content-wrapper { overflow-x: clip !important; }

/* ══ Tokens ═══════════════════════════════════════════════════════ */
:root {
    --lec-blue:   #3a86ff;
    --lec-teal:   #06d6a0;
    --lec-purple: #8338ec;
    --lec-red:    #e63946;
    --lec-green:  #059669;
    --lec-text:   #1b1b2f;
    --lec-muted:  #6c757d;
    --lec-bg:     #f4f6fb;
    --lec-card:   #fff;
    --lec-radius: 1.1rem;
    --lec-shadow: 0 4px 28px rgba(58,134,255,.09);
    --lec-sidebar-top: 1.25rem; /* offset sticky sidebar en desktop */
}

/* ══ Progress strip ════════════════════════════════════════════════ */
.lec-strip { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; background: #e9ecef; }
.lec-strip-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--lec-teal), var(--lec-blue), var(--lec-purple));
    width: 0; transition: width 1.3s cubic-bezier(.4,0,.2,1);
}

/* ══ Breadcrumb ════════════════════════════════════════════════════ */
.lec-breadcrumb {
    display: flex; align-items: center; flex-wrap: wrap; gap: .3rem;
    font-size: .78rem; color: var(--lec-muted); margin-bottom: 1.1rem;
}
.lec-breadcrumb a { color: var(--lec-blue); text-decoration: none; font-weight: 600; transition: opacity .15s; }
.lec-breadcrumb a:hover { opacity: .7; text-decoration: none; }
.lec-breadcrumb-sep { opacity: .35; font-size: .7rem; }

/* ══ Header card ═══════════════════════════════════════════════════ */
.lec-header-card {
    background: var(--lec-card);
    border-radius: var(--lec-radius);
    box-shadow: var(--lec-shadow);
    padding: 1.5rem 1.75rem 1.35rem;
    margin-bottom: 1.1rem;
    position: relative; overflow: hidden;
}
.lec-header-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3.5px;
    background: linear-gradient(90deg, var(--lec-teal), var(--lec-blue), var(--lec-purple));
}
.lec-tags { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .7rem; }
.lec-tag {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .68rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .09em; padding: .22rem .7rem; border-radius: 99px;
}
.lec-tag.audio { background: #d1fae5; color: #065f46; }
.lec-tag.video { background: #fee2e2; color: #991b1b; }
.lec-tag.texto { background: #dbeafe; color: #1e40af; }
.lec-title {
    font-size: clamp(1.15rem, 3vw, 1.5rem);
    font-weight: 800; color: var(--lec-text);
    margin: 0 0 .5rem; line-height: 1.25;
}
.lec-meta { display: flex; flex-wrap: wrap; gap: .8rem; font-size: .79rem; color: var(--lec-muted); }
.lec-meta span { display: flex; align-items: center; gap: .3rem; }

/* ══ Tabs (solo mobile) ════════════════════════════════════════════ */
.lec-tabs-wrap {
    position: sticky; top: 0; z-index: 200;
    background: rgba(244,246,251,.92);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    padding: .5rem 0; margin-bottom: .85rem;
    display: none; /* ocultos en desktop */
}
.lec-tabs {
    display: flex; gap: .4rem; flex-wrap: nowrap;
    overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;
}
.lec-tabs::-webkit-scrollbar { display: none; }
.lec-tab {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .42rem 1rem; border-radius: 99px;
    font-size: .78rem; font-weight: 700; white-space: nowrap;
    border: 2px solid transparent; cursor: pointer;
    background: #fff; color: var(--lec-muted);
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    text-decoration: none; transition: all .18s;
}
.lec-tab.audio.active, .lec-tab.audio:hover { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.lec-tab.video.active, .lec-tab.video:hover { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.lec-tab.texto.active, .lec-tab.texto:hover { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
.lec-tab:hover { text-decoration: none; }

/* ══ Grid principal 7/5 ════════════════════════════════════════════ */
.lec-grid {
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 1rem;
    /* sin align-items: la celda lateral se estira al alto de la columna de texto */
    margin-bottom: 1rem;
}

/* Columna principal (texto) */
.lec-col-main { min-width: 0; align-self: start; }

/* Columna lateral: ocupa todo el alto de la fila (igual al texto) */
.lec-col-side { min-width: 0; }

/* Wrapper interior sticky: se desliza dentro del alto de .lec-col-side */
.lec-side-sticky {
    position: sticky;
    top: var(--lec-sidebar-top);
    display: flex;
    flex-direction: column;
    gap: .85rem;
}

/* ══ Bloques ═══════════════════════════════════════════════════════ */
.lec-block {
    background: var(--lec-card);
    border-radius: var(--lec-radius);
    box-shadow: var(--lec-shadow);
    overflow: hidden;
    opacity: 0; transform: translateY(20px);
}

/* Block header */
.lec-block-hdr {
    display: flex; align-items: center; gap: .6rem;
    padding: .78rem 1.25rem;
    font-size: .7rem; font-weight: 800; letter-spacing: .09em;
    text-transform: uppercase; border-bottom: 1px solid transparent;
}
.lec-block-hdr.audio { background: linear-gradient(135deg,#ecfdf5,#f0fdf4); color: var(--lec-green); border-color: #d1fae5; }
.lec-block-hdr.video { background: linear-gradient(135deg,#fff5f5,#fff8f8); color: var(--lec-red);   border-color: #fee2e2; }
.lec-block-hdr.texto { background: linear-gradient(135deg,#eff6ff,#f0f8ff); color: #1d4ed8;          border-color: #dbeafe; }
.lec-block-hdr-icon {
    width: 26px; height: 26px; border-radius: .45rem; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .8rem;
}
.lec-block-hdr.audio .lec-block-hdr-icon { background: #d1fae5; color: #065f46; }
.lec-block-hdr.video .lec-block-hdr-icon { background: #fee2e2; color: #991b1b; }
.lec-block-hdr.texto .lec-block-hdr-icon { background: #dbeafe; color: #1e40af; }

/* ══ Audio player ══════════════════════════════════════════════════ */
.lec-audio-shell {
    padding: 1.1rem 1.25rem 1.25rem;
    background: linear-gradient(135deg,#ecfdf5,#eff6ff);
}
.lec-audio-player {
    background: #fff; border-radius: .8rem;
    padding: 1rem 1.1rem;
    box-shadow: 0 2px 12px rgba(6,214,160,.11);
    display: flex; align-items: center; gap: .85rem;
}
.lec-play-btn {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--lec-teal), #00c896);
    color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: .9rem;
    box-shadow: 0 4px 14px rgba(6,214,160,.35);
    transition: transform .15s, box-shadow .15s;
}
.lec-play-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(6,214,160,.45); }
.lec-play-btn.playing {
    background: linear-gradient(135deg, var(--lec-purple), var(--lec-blue));
    box-shadow: 0 4px 14px rgba(131,56,236,.35);
    animation: lec-pulse .9s ease infinite;
}
@keyframes lec-pulse {
    0%,100% { box-shadow: 0 0 0 0   rgba(6,214,160,.5); }
    70%      { box-shadow: 0 0 0 9px rgba(6,214,160,0);  }
}
.lec-audio-track { flex: 1; min-width: 0; }
.lec-audio-title {
    font-size: .77rem; font-weight: 700; color: var(--lec-text);
    margin-bottom: .4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.lec-progress-wrap {
    height: 5px; background: #e9ecef; border-radius: 99px; overflow: hidden;
    cursor: pointer; margin-bottom: .3rem; transition: height .15s;
}
.lec-progress-wrap:hover { height: 8px; }
.lec-progress-fill {
    height: 100%; width: 0;
    background: linear-gradient(90deg, var(--lec-teal), var(--lec-blue));
    border-radius: 99px; transition: width .1s linear; pointer-events: none;
}
.lec-audio-times {
    display: flex; justify-content: space-between;
    font-size: .65rem; font-weight: 600; color: #9ca3af;
}
.lec-speed-btn {
    flex-shrink: 0;
    background: #f0f4ff; color: var(--lec-blue);
    border: none; border-radius: .4rem;
    padding: .2rem .5rem; font-size: .68rem; font-weight: 800;
    cursor: pointer; transition: background .15s; white-space: nowrap;
}
.lec-speed-btn:hover { background: #dbeafe; }

/* ══ Video ══════════════════════════════════════════════════════════ */
.lec-video-shell { padding: .9rem 1.1rem 1.1rem; }
.lec-video-frame {
    position: relative; padding-bottom: 56.25%; height: 0;
    border-radius: .7rem; overflow: hidden;
    background: #000; box-shadow: 0 4px 18px rgba(0,0,0,.18);
}
.lec-video-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

/* ══ Texto ══════════════════════════════════════════════════════════ */
.lec-text-shell { padding: 1.6rem 1.75rem 1.8rem; }
.lec-text-body {
    font-size: .95rem; line-height: 1.85; color: var(--lec-text);
}
.lec-text-body h2,.lec-text-body h3,.lec-text-body h4 { font-weight: 800; color: var(--lec-text); margin: 1.6em 0 .5em; line-height: 1.25; }
.lec-text-body h2 { font-size: 1.2rem; }
.lec-text-body h3 { font-size: 1.05rem; }
.lec-text-body p  { margin-bottom: 1em; }
.lec-text-body ul,.lec-text-body ol { padding-left: 1.4rem; margin-bottom: 1em; }
.lec-text-body li { margin-bottom: .35rem; }
.lec-text-body table { width: 100%; border-collapse: collapse; margin: 1.2em 0; font-size: .88rem; }
.lec-text-body th { background: #eff6ff; padding: .55rem .85rem; text-align: left; font-weight: 700; border: 1px solid #dbeafe; color: #1e40af; }
.lec-text-body td { padding: .5rem .85rem; border: 1px solid #e9ecef; vertical-align: top; }
.lec-text-body tr:nth-child(even) td { background: #fafbff; }
.lec-text-body code { background: #f0f4ff; color: var(--lec-purple); padding: .18rem .5rem; border-radius: .35rem; font-size: .84rem; }
.lec-text-body pre { background: #1b1b2f; color: #e8f0ff; padding: 1.25rem 1.4rem; border-radius: .75rem; overflow-x: auto; font-size: .85rem; line-height: 1.65; margin: 1.2em 0; }
.lec-text-body blockquote { border-left: 4px solid var(--lec-blue); padding: .85rem 1.1rem; margin: 1.2em 0; background: #eff6ff; border-radius: 0 .6rem .6rem 0; color: #374151; font-style: italic; }
.lec-text-body img { max-width: 100%; border-radius: .5rem; margin: .75em 0; }

/* ══ Action bar ════════════════════════════════════════════════════ */
.lec-action-bar {
    background: var(--lec-card); border-radius: var(--lec-radius);
    box-shadow: var(--lec-shadow); padding: 1.1rem 1.4rem;
    display: flex; align-items: center; flex-wrap: wrap; gap: .75rem;
    margin-bottom: 1.5rem;
    opacity: 0; transform: translateY(12px);
}
.lec-done-badge { display: flex; align-items: center; gap: .5rem; color: var(--lec-green); font-weight: 700; font-size: .95rem; }
.lec-done-badge i { font-size: 1.2rem; }
.lec-btn-complete {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--lec-teal), #00c896);
    color: #fff; border: none; border-radius: .65rem;
    padding: .68rem 1.45rem; font-weight: 800; font-size: .88rem; cursor: pointer;
    box-shadow: 0 4px 14px rgba(6,214,160,.3);
    transition: transform .15s, box-shadow .15s, filter .15s;
}
.lec-btn-complete:hover { filter: brightness(1.06); box-shadow: 0 6px 20px rgba(6,214,160,.4); transform: translateY(-1px); }
.lec-nav-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #f4f6fb; border: 1.5px solid #e2e8f0; border-radius: .65rem;
    padding: .6rem 1.1rem; font-weight: 700; font-size: .84rem;
    color: var(--lec-text); text-decoration: none;
    transition: border-color .18s, color .18s, background .18s;
}
.lec-nav-btn:hover { border-color: var(--lec-blue); color: var(--lec-blue); background: #eff6ff; text-decoration: none; }
.lec-nav-btn.next {
    background: linear-gradient(135deg, var(--lec-blue), var(--lec-purple));
    color: #fff; border-color: transparent;
    box-shadow: 0 3px 12px rgba(131,56,236,.25);
}
.lec-nav-btn.next:hover { filter: brightness(1.07); color: #fff; transform: translateY(-1px); }

/* ══ Empty ══════════════════════════════════════════════════════════ */
.lec-empty { padding: 3rem 2rem; text-align: center; color: var(--lec-muted); }
.lec-empty i { font-size: 2.5rem; opacity: .3; display: block; margin-bottom: .85rem; }

/* ══ RESPONSIVE ════════════════════════════════════════════════════ */

/* Tablet y móvil: columna única, orden: texto → audio → video */
@media (max-width: 991px) {
    .lec-grid {
        display: flex;
        flex-direction: column;
        gap: .85rem;
        margin-bottom: .85rem;
    }
    .lec-col-main  { order: 1; }                   /* texto primero */
    .lec-col-side  { order: 2; }                   /* audio + video después */
    .lec-side-sticky { position: static; }         /* sin sticky en móvil */
    .lec-tabs-wrap { display: block; } /* tabs visibles en móvil */
}

@media (max-width: 576px) {
    .lec-header-card  { padding: 1.1rem 1.1rem .95rem; }
    .lec-block-hdr    { padding: .68rem .9rem; }
    .lec-audio-shell  { padding: .9rem; }
    .lec-audio-player { gap: .65rem; }
    .lec-play-btn     { width: 38px; height: 38px; font-size: .82rem; }
    .lec-video-shell  { padding: .65rem .75rem .75rem; }
    .lec-text-shell   { padding: 1.1rem .95rem 1.3rem; }
    .lec-action-bar   { flex-direction: column; align-items: stretch; }
    .lec-btn-complete,.lec-nav-btn { justify-content: center; width: 100%; }
    .lec-nav-btn.next { order: -1; }
}
</style>
@endpush

@php
    $tipoDisplay = $leccion->tipo_display;
    $hasAudio    = !empty($leccion->archivo_url);
    $hasVideo    = !empty($leccion->video_url);
    $hasTexto    = !empty($leccion->contenido_texto);
    $hasSide     = $hasAudio || $hasVideo;   /* hay contenido en la columna derecha */
    $mixto       = $hasAudio + $hasVideo + $hasTexto > 1;

    $audioExt  = $hasAudio ? strtolower(pathinfo($leccion->archivo_url, PATHINFO_EXTENSION)) : '';
    $audioMime = $hasAudio
        ? (str_starts_with($leccion->archivo_url, 'gdrive:') ? 'audio/mpeg'
           : match($audioExt) {
               'm4a','mp4','aac' => 'audio/mp4',
               'ogg'             => 'audio/ogg',
               'wav'             => 'audio/wav',
               default           => 'audio/mpeg',
           })
        : 'audio/mpeg';
@endphp

@section('contenido')

<div class="lec-strip"><div class="lec-strip-fill" id="lec-strip" data-pct="{{ $porcentajeCurso }}"></div></div>

{{-- Breadcrumb --}}
<nav class="lec-breadcrumb" aria-label="breadcrumb">
    <a href="{{ route('ce.alumno.index') }}"><i class="fas fa-home fa-xs"></i> Cursos</a>
    <span class="lec-breadcrumb-sep"><i class="fas fa-chevron-right fa-xs"></i></span>
    <a href="{{ route('ce.alumno.show', $curso) }}">{{ Str::limit($curso->nombre, 28) }}</a>
    <span class="lec-breadcrumb-sep"><i class="fas fa-chevron-right fa-xs"></i></span>
    <span>{{ Str::limit($leccion->unidad->nombre, 26) }}</span>
</nav>

{{-- Header --}}
<div class="lec-header-card" id="lec-header">
    <div class="lec-tags">
        @if ($mixto)
            @if ($hasTexto) <span class="lec-tag texto"><i class="fas fa-align-left"></i> Lectura</span> @endif
            @if ($hasAudio) <span class="lec-tag audio"><i class="fas fa-headphones"></i> Audio</span> @endif
            @if ($hasVideo) <span class="lec-tag video"><i class="fas fa-play-circle"></i> Video</span> @endif
        @else
            @if ($hasTexto) <span class="lec-tag texto"><i class="fas fa-align-left"></i> Lectura</span>
            @elseif ($hasAudio) <span class="lec-tag audio"><i class="fas fa-headphones"></i> Audio</span>
            @elseif ($hasVideo) <span class="lec-tag video"><i class="fas fa-play-circle"></i> Video</span> @endif
        @endif
    </div>
    <h1 class="lec-title">{{ $leccion->nombre }}</h1>
    <div class="lec-meta">
        <span><i class="fas fa-layer-group"></i> {{ $leccion->unidad->nivel->nombre }}</span>
        <span><i class="fas fa-folder-open"></i> {{ $leccion->unidad->nombre }}</span>
        @if ($leccion->duracion_min)
            <span><i class="far fa-clock"></i> {{ $leccion->duracion_min }} min</span>
        @endif
    </div>
</div>

{{-- Tabs móvil (solo si hay más de un tipo) --}}
@if ($mixto)
<div class="lec-tabs-wrap">
    <div class="lec-tabs" id="lec-tabs">
        @if ($hasTexto) <a class="lec-tab texto" href="#bloque-texto" id="tab-texto"><i class="fas fa-align-left"></i> Lectura</a> @endif
        @if ($hasAudio) <a class="lec-tab audio" href="#bloque-audio" id="tab-audio"><i class="fas fa-headphones"></i> Audio</a> @endif
        @if ($hasVideo) <a class="lec-tab video" href="#bloque-video" id="tab-video"><i class="fas fa-play-circle"></i> Video</a> @endif
    </div>
</div>
@endif

{{-- Grid 7/5 ─ si no hay sidebar se usa columna única --}}
@if ($hasTexto || $hasSide)
<div class="lec-grid {{ $hasSide && $hasTexto ? '' : 'lec-grid--solo' }}"
     style="{{ !$hasSide || !$hasTexto ? 'grid-template-columns:1fr' : '' }}">

    {{-- ── Columna izquierda: TEXTO ── --}}
    @if ($hasTexto)
    <div class="lec-col-main">
        <div class="lec-block" id="bloque-texto">
            @if ($mixto)
            <div class="lec-block-hdr texto">
                <span class="lec-block-hdr-icon"><i class="fas fa-align-left"></i></span>
                Contenido de la lección
            </div>
            @endif
            <div class="lec-text-shell">
                <div class="lec-text-body">{!! $leccion->contenido_texto !!}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Columna derecha: AUDIO + VIDEO (sticky en desktop) ── --}}
    @if ($hasSide)
    <div class="lec-col-side">
    <div class="lec-side-sticky">

        {{-- Audio --}}
        @if ($hasAudio)
        <div class="lec-block" id="bloque-audio">
            @if ($mixto)
            <div class="lec-block-hdr audio">
                <span class="lec-block-hdr-icon"><i class="fas fa-headphones"></i></span>
                Audio de la lección
            </div>
            @endif
            <div class="lec-audio-shell">
                <div class="lec-audio-player">
                    <button class="lec-play-btn" id="lec-play-btn" aria-label="Reproducir">
                        <i class="fas fa-play" id="lec-play-icon"></i>
                    </button>
                    <div class="lec-audio-track">
                        <div class="lec-audio-title">{{ $leccion->nombre }}</div>
                        <div class="lec-progress-wrap" id="lec-prog-wrap">
                            <div class="lec-progress-fill" id="lec-prog-fill"></div>
                        </div>
                        <div class="lec-audio-times">
                            <span id="lec-cur">0:00</span>
                            <span id="lec-tot">-:--</span>
                        </div>
                    </div>
                    <button class="lec-speed-btn" id="lec-speed-btn" title="Velocidad de reproducción">1×</button>
                </div>
                <audio id="lec-audio" preload="metadata" style="display:none">
                    <source src="{{ $leccion->archivo_src }}" type="{{ $audioMime }}">
                </audio>
            </div>
        </div>
        @endif

        {{-- Video --}}
        @if ($hasVideo)
        <div class="lec-block" id="bloque-video">
            @if ($mixto)
            <div class="lec-block-hdr video">
                <span class="lec-block-hdr-icon"><i class="fas fa-play-circle"></i></span>
                Video de la lección
            </div>
            @endif
            <div class="lec-video-shell">
                <div class="lec-video-frame">
                    <iframe src="{{ $leccion->video_url }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /.lec-side-sticky --}}
    </div>{{-- /.lec-col-side --}}
    @endif

</div>
@else
{{-- Sin contenido --}}
<div class="lec-block" id="bloque-vacio" style="opacity:1;transform:none;margin-bottom:1rem">
    <div class="lec-empty">
        <i class="fas fa-hourglass-half"></i>
        Contenido en preparación, próximamente disponible.
    </div>
</div>
@endif

{{-- Action bar --}}
<div class="lec-action-bar" id="lec-action-bar">
    @if ($anterior)
        <a href="{{ route('ce.alumno.leccion', [$curso, $anterior]) }}" class="lec-nav-btn">
            <i class="fas fa-arrow-left"></i> Anterior
        </a>
    @endif
    <div class="flex-grow-1"></div>
    @if ($completada)
        <span class="lec-done-badge"><i class="fas fa-check-circle"></i> Lección completada</span>
    @else
        <form action="{{ route('ce.alumno.leccion.completar', [$curso, $leccion]) }}"
              method="POST" id="lec-form-completar">
            @csrf
            <button type="submit" class="lec-btn-complete" id="lec-btn-completar">
                <i class="fas fa-check"></i> Marcar como completada
            </button>
        </form>
    @endif
    @if ($siguiente)
        <a href="{{ route('ce.alumno.leccion', [$curso, $siguiente]) }}" class="lec-nav-btn next">
            Siguiente <i class="fas fa-arrow-right"></i>
        </a>
    @else
        <a href="{{ route('ce.alumno.show', $curso) }}" class="lec-nav-btn next">
            Ver curso <i class="fas fa-list"></i>
        </a>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Progress strip ── */
    setTimeout(() => {
        const s = document.getElementById('lec-strip');
        if (s) s.style.width = s.dataset.pct + '%';
    }, 250);

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── GSAP ── */
    if (typeof gsap !== 'undefined' && !reducedMotion) {
        if (typeof ScrollTrigger !== 'undefined') gsap.registerPlugin(ScrollTrigger);

        gsap.from('#lec-header', { opacity: 0, y: 18, duration: .5, ease: 'power3.out', delay: .05 });

        document.querySelectorAll('.lec-block').forEach((el, i) => {
            if (typeof ScrollTrigger !== 'undefined') {
                gsap.to(el, {
                    opacity: 1, y: 0, duration: .55, ease: 'power3.out',
                    scrollTrigger: { trigger: el, start: 'top 90%', once: true },
                    delay: i * .05,
                });
            } else {
                gsap.to(el, { opacity: 1, y: 0, duration: .5, ease: 'power2.out', delay: .15 + i * .1 });
            }
        });

        const actionBar = document.getElementById('lec-action-bar');
        if (actionBar) {
            if (typeof ScrollTrigger !== 'undefined') {
                gsap.to(actionBar, {
                    opacity: 1, y: 0, duration: .45, ease: 'power2.out',
                    scrollTrigger: { trigger: actionBar, start: 'top 96%', once: true },
                });
            } else {
                gsap.to(actionBar, { opacity: 1, y: 0, duration: .4, ease: 'power2.out', delay: .5 });
            }
        }

        const btnC = document.getElementById('lec-btn-completar');
        if (btnC) {
            btnC.addEventListener('mouseenter', () => gsap.to(btnC, { scale: 1.04, duration: .15 }));
            btnC.addEventListener('mouseleave', () => gsap.to(btnC, { scale: 1,    duration: .15 }));
            btnC.addEventListener('click', e => {
                e.preventDefault();
                gsap.to(btnC, {
                    scale: .93, duration: .1, yoyo: true, repeat: 1,
                    onComplete: () => document.getElementById('lec-form-completar')?.submit(),
                });
            });
        }
    } else {
        document.querySelectorAll('.lec-block, #lec-action-bar').forEach(el => {
            el.style.opacity = '1'; el.style.transform = 'none';
        });
    }

    /* ── Tabs móvil: highlight activo en scroll ── */
    const tabEls = document.querySelectorAll('.lec-tab');
    if (tabEls.length) {
        const bloques = ['texto','audio','video']
            .map(k => document.getElementById('bloque-' + k))
            .filter(Boolean);

        const io = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const key = entry.target.id.replace('bloque-', '');
                    tabEls.forEach(t => t.classList.remove('active'));
                    document.getElementById('tab-' + key)?.classList.add('active');
                }
            });
        }, { threshold: 0.45 });

        bloques.forEach(b => io.observe(b));

        tabEls.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                const target = document.querySelector(tab.getAttribute('href'));
                if (!target) return;
                const offset = document.querySelector('.lec-tabs-wrap')?.offsetHeight || 50;
                window.scrollTo({ top: target.getBoundingClientRect().top + scrollY - offset - 8, behavior: 'smooth' });
            });
        });
    }

    /* ── Custom audio player ── */
    const audio    = document.getElementById('lec-audio');
    const playBtn  = document.getElementById('lec-play-btn');
    const playIcon = document.getElementById('lec-play-icon');
    const progWrap = document.getElementById('lec-prog-wrap');
    const progFill = document.getElementById('lec-prog-fill');
    const curEl    = document.getElementById('lec-cur');
    const totEl    = document.getElementById('lec-tot');
    const speedBtn = document.getElementById('lec-speed-btn');
    if (!audio || !playBtn) return;

    const speeds = [1, 1.25, 1.5, 2, 0.75];
    let speedIdx = 0;

    const fmt = s => (!s || !isFinite(s)) ? '-:--'
        : Math.floor(s / 60) + ':' + String(Math.floor(s % 60)).padStart(2, '0');

    const syncDur = () => { if (isFinite(audio.duration) && audio.duration > 0 && totEl) totEl.textContent = fmt(audio.duration); };
    syncDur();
    audio.addEventListener('loadedmetadata', syncDur);
    audio.addEventListener('durationchange',  syncDur);

    audio.addEventListener('timeupdate', () => {
        if (!audio.duration || !isFinite(audio.duration)) return;
        if (progFill) progFill.style.width = (audio.currentTime / audio.duration * 100) + '%';
        if (curEl)    curEl.textContent = fmt(audio.currentTime);
    });
    audio.addEventListener('ended', () => {
        playIcon && (playIcon.className = 'fas fa-play');
        playBtn?.classList.remove('playing');
        if (progFill) progFill.style.width = '0%';
        audio.currentTime = 0;
    });
    audio.addEventListener('pause', () => { playIcon && (playIcon.className = 'fas fa-play');  playBtn?.classList.remove('playing'); });
    audio.addEventListener('play',  () => { playIcon && (playIcon.className = 'fas fa-pause'); playBtn?.classList.add('playing'); });

    playBtn.addEventListener('click', () => {
        audio.paused ? audio.play().catch(() => {}) : audio.pause();
        if (typeof gsap !== 'undefined' && !reducedMotion)
            gsap.fromTo(playBtn, { scale: .88 }, { scale: 1, duration: .25, ease: 'elastic.out(1,.5)' });
    });

    progWrap?.addEventListener('click', function (e) {
        if (!audio.duration || !isFinite(audio.duration)) return;
        const r = this.getBoundingClientRect();
        audio.currentTime = ((e.clientX - r.left) / r.width) * audio.duration;
    });

    speedBtn?.addEventListener('click', () => {
        speedIdx = (speedIdx + 1) % speeds.length;
        audio.playbackRate = speeds[speedIdx];
        speedBtn.textContent = speeds[speedIdx] + '×';
        if (typeof gsap !== 'undefined' && !reducedMotion)
            gsap.fromTo(speedBtn, { scale: .82 }, { scale: 1, duration: .2, ease: 'back.out(2)' });
    });

}());
</script>
@endpush
