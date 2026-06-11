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
.ce-stat-icon.blue   { background: #e8f0ff; color: var(--ce-blue); }
.ce-stat-icon.teal   { background: #e0fbf4; color: var(--ce-teal); }
.ce-stat-icon.amber  { background: #fff8e0; color: var(--ce-amber); }
.ce-stat-icon.purple { background: #f0eeff; color: var(--ce-purple); }
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

/* ── Exercises section label ── */
.ce-content-section-label {
    display: flex; align-items: center; gap: .45rem;
    font-size: .74rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; color: var(--ce-purple);
    padding: .75rem 1rem .35rem; opacity: .9;
}
.ce-content-section-label span {
    font-weight: 500; text-transform: none;
    letter-spacing: 0; opacity: .65; font-size: .76rem;
}

/* ── Exercise card ── */
.ce-exercise-card {
    background: #fff;
    border: 1.5px solid #e9ecef;
    border-radius: .85rem;
    margin: .35rem .75rem .65rem;
    overflow: hidden;
    transition: box-shadow .25s, border-color .25s;
    will-change: transform, opacity;
}
.ce-exercise-card:hover { box-shadow: 0 6px 24px rgba(131,56,236,.10); border-color: #d8c8f8; }
.ce-exercise-card.solved { border-color: var(--ce-teal); background: linear-gradient(135deg,#f0fdf8,#fff); }

.ce-exercise-header {
    display: flex; align-items: flex-start; gap: .7rem;
    padding: .85rem 1rem .65rem;
    background: linear-gradient(135deg,#fafbff,#fff);
    border-bottom: 1px solid #f0f2f7;
}
.ce-exercise-num {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--ce-purple), var(--ce-blue));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 800;
}
.ce-exercise-meta { flex: 1; min-width: 0; }
.ce-exercise-pregunta {
    font-size: .88rem; font-weight: 700; color: var(--ce-text);
    margin: 0 0 .22rem; line-height: 1.45;
}
.ce-exercise-pts {
    font-size: .73rem; color: var(--ce-muted); display: flex; align-items: center; gap: .3rem; flex-wrap: wrap;
}
.ce-exercise-type-chip {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #f0eeff; color: var(--ce-purple);
    border-radius: 99px; padding: .12rem .5rem;
    font-size: .68rem; font-weight: 700;
}
.ce-exercise-badge {
    font-size: .7rem; font-weight: 700; letter-spacing: .04em;
    padding: .22rem .6rem; border-radius: 99px; white-space: nowrap; flex-shrink: 0;
}
.ce-exercise-badge.pending { background: #fff8e0; color: #d97706; }
.ce-exercise-badge.solved  { background: #e0fbf4; color: #059669; }
.ce-exercise-badge.wrong   { background: #fde8e8; color: #e74a3b; }

/* Wrong state card */
.ce-exercise-card.wrong { border-color: #f5a0a0; }
.ce-exercise-card.wrong .ce-exercise-header { background: linear-gradient(135deg,#fff8f8,#fff); }

/* Incorrect feedback banner */
.ce-feedback-wrong {
    display: flex; align-items: flex-start; gap: .6rem;
    background: #fff1f0; border: 1px solid #fca5a5;
    border-radius: .55rem; padding: .65rem .85rem;
    margin-bottom: .75rem; font-size: .84rem;
}
.ce-feedback-wrong i { color: #e74a3b; font-size: 1rem; margin-top: .1rem; flex-shrink: 0; }
.ce-feedback-wrong strong { display: block; color: #c0392b; font-size: .82rem; margin-bottom: .1rem; }
.ce-feedback-wrong em { color: #555; font-style: normal; font-weight: 600; }

/* Pre-selected wrong option */
.ce-option.was-wrong {
    border-color: #e74a3b !important;
    background: linear-gradient(135deg,#fff8f8,#fff5f5) !important;
}
.ce-option.was-wrong .ce-option-letter { background: #e74a3b !important; color: #fff !important; }
.ce-option.was-wrong .ce-option-dot {
    border-color: #e74a3b !important; background: #e74a3b !important;
    box-shadow: inset 0 0 0 3px #fff !important;
}

/* ── Option cards (multiple choice) ── */
.ce-exercise-body { padding: .85rem 1rem 1rem; }

.ce-options-list { display: flex; flex-direction: column; gap: .4rem; }

.ce-option {
    display: flex; align-items: center; gap: .65rem;
    padding: .65rem .9rem; border: 1.5px solid #e9ecef;
    border-radius: .6rem; cursor: pointer;
    transition: border-color .18s, background .18s, transform .12s;
    font-size: .87rem; font-weight: 500; color: var(--ce-text);
    user-select: none;
}
.ce-option input[type="radio"] { display: none; }
.ce-option:hover { border-color: #c4b0f5; background: #faf7ff; transform: translateX(3px); }
.ce-option.selected { border-color: var(--ce-purple); background: linear-gradient(135deg,#faf5ff,#f3ecff); }

.ce-option-letter {
    width: 24px; height: 24px; border-radius: .35rem; flex-shrink: 0;
    background: #f0f0f7; color: var(--ce-muted);
    display: flex; align-items: center; justify-content: center;
    font-size: .71rem; font-weight: 800;
    transition: background .18s, color .18s;
}
.ce-option.selected .ce-option-letter { background: var(--ce-purple); color: #fff; }

.ce-option-dot {
    width: 17px; height: 17px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid #d0d0e0; background: #fff;
    transition: border-color .18s, background .18s, box-shadow .18s;
    display: flex; align-items: center; justify-content: center;
}
.ce-option:hover .ce-option-dot { border-color: var(--ce-purple); }
.ce-option.selected .ce-option-dot {
    border-color: var(--ce-purple); background: var(--ce-purple);
    box-shadow: inset 0 0 0 3px #fff;
}

/* ── Free-text textarea ── */
.ce-textarea {
    width: 100%; border: 1.5px solid #e9ecef; border-radius: .6rem;
    padding: .75rem 1rem; font-size: .87rem; resize: vertical; outline: none;
    transition: border-color .2s, box-shadow .2s; font-family: inherit;
    background: #fafbff; color: var(--ce-text);
}
.ce-textarea:focus { border-color: var(--ce-purple); box-shadow: 0 0 0 3px rgba(131,56,236,.11); background: #fff; }

/* ── Audio pronunciación ── */
.ce-audio-pron {
    background: linear-gradient(135deg,#f0eeff,#e8f4ff);
    border: 1px solid rgba(131,56,236,.18);
    border-radius: .65rem;
    padding: .6rem .85rem .55rem;
    margin-bottom: .8rem;
}
.ce-audio-pron-label {
    font-size: .7rem; font-weight: 800; letter-spacing: .06em;
    text-transform: uppercase; color: var(--ce-purple);
    display: flex; align-items: center; gap: .35rem;
    margin-bottom: .45rem;
}
.ce-audio-pron-player {
    display: flex; align-items: center; gap: .6rem;
}
.ce-audio-play-btn {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: var(--ce-purple); color: #fff;
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    transition: transform .15s, background .15s, box-shadow .15s;
}
.ce-audio-play-btn:hover { transform: scale(1.1); box-shadow: 0 3px 12px rgba(131,56,236,.35); }
.ce-audio-play-btn.playing {
    background: linear-gradient(135deg,var(--ce-purple),var(--ce-blue));
    animation: ce-pulse-ring .9s ease infinite;
}
@@keyframes ce-pulse-ring {
    0%   { box-shadow: 0 0 0 0   rgba(131,56,236,.5); }
    70%  { box-shadow: 0 0 0 8px rgba(131,56,236,0);  }
    100% { box-shadow: 0 0 0 0   rgba(131,56,236,0);  }
}
.ce-audio-track { flex: 1; min-width: 0; }
.ce-audio-bar-wrap {
    height: 5px; background: rgba(131,56,236,.15);
    border-radius: 99px; overflow: hidden;
    cursor: pointer; margin-bottom: .2rem;
}
.ce-audio-bar-fill {
    height: 100%; width: 0;
    background: linear-gradient(90deg,var(--ce-purple),var(--ce-blue));
    border-radius: 99px; transition: width .1s linear;
    pointer-events: none;
}
.ce-audio-time {
    display: flex; justify-content: space-between;
    font-size: .67rem; font-weight: 600; color: #999;
}

/* ── Submit button ── */
.ce-btn-complete {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--ce-teal), #00b894);
    color: #fff; border: none; border-radius: .65rem;
    padding: .7rem 1.5rem; font-weight: 700; font-size: .9rem;
    cursor: pointer; transition: opacity .2s, transform .15s;
}
.ce-btn-complete:hover { opacity: .88; transform: scale(1.02); }
.ce-btn-submit {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--ce-purple), var(--ce-blue));
    color: #fff; border: none; border-radius: .65rem;
    padding: .62rem 1.35rem; font-weight: 700; font-size: .87rem;
    cursor: pointer; margin-top: .8rem;
    transition: opacity .2s, transform .15s, box-shadow .2s;
}
.ce-btn-submit:hover { opacity: .9; transform: scale(1.03); box-shadow: 0 4px 16px rgba(131,56,236,.3); }
.ce-btn-submit:active { transform: scale(.97); }
.ce-btn-submit.retry {
    background: linear-gradient(135deg, #e74a3b, #c0392b);
}

/* ── Solved row ── */
.ce-solved-row {
    display: flex; align-items: center; gap: .6rem;
    padding: .85rem 1rem;
    color: var(--ce-teal); font-weight: 700; font-size: .9rem;
}
.ce-solved-row i { font-size: 1.2rem; }

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
                <div class="ce-stat-label">Lecciones completadas</div>
            </div>
        </div>
        <div class="ce-stat">
            <div class="ce-stat-icon amber"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="ce-stat-val">{{ $totalLecciones }}</div>
                <div class="ce-stat-label">Lecciones totales</div>
            </div>
        </div>
        <div class="ce-stat">
            <div class="ce-stat-icon purple"><i class="fas fa-tasks"></i></div>
            <div>
                <div class="ce-stat-val">{{ $completadasECount }}</div>
                <div class="ce-stat-label">Ejercicios resueltos</div>
            </div>
        </div>
        <div class="ce-stat">
            <div class="ce-stat-icon purple"><i class="fas fa-list"></i></div>
            <div>
                <div class="ce-stat-val">{{ $totalEjercicios }}</div>
                <div class="ce-stat-label">Ejercicios totales</div>
            </div>
        </div>
    </div>

    @forelse ($curso->niveles as $nivel)
        <div class="ce-nivel">
            <button class="ce-nivel-header" onclick="toggleNivel({{ $nivel->id }})">
                <span class="ce-nivel-num">{{ $loop->iteration }}</span>
                <span class="ce-nivel-title">{{ $nivel->nombre }}</span>
                <span class="ce-nivel-meta">{{ $nivel->unidades->count() }} unidades</span>
                <i class="fas fa-chevron-down ce-nivel-chevron {{ $loop->first ? 'open' : '' }}"
                   id="chev-nivel-{{ $nivel->id }}"></i>
            </button>

            <div class="ce-nivel-body {{ $loop->first ? 'open' : '' }}" id="body-nivel-{{ $nivel->id }}">
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
                        <i class="fas fa-chevron-down ce-unidad-chevron {{ $loop->first ? 'open' : '' }}"
                           id="chev-unidad-{{ $unidad->id }}"></i>
                    </button>
                    <div class="ce-unidad-progress">
                        <div class="ce-unidad-progress-fill" data-pct="{{ $pctU }}"></div>
                    </div>
                    <div class="ce-unidad-body {{ $loop->first ? 'open' : '' }}" id="body-unidad-{{ $unidad->id }}">
                        @foreach ($unidad->lecciones as $leccion)
                            @php $done = isset($completadas[$leccion->id]); @endphp
                            <a href="{{ route('ce.alumno.leccion', [$curso, $leccion]) }}" class="ce-lesson">
                                <span class="ce-lesson-status {{ $done ? 'done' : '' }}">
                                    <i class="fas fa-{{ $done ? 'check' : 'circle' }}"></i>
                                </span>
                                @php $td = $leccion->tipo_display; @endphp
                                <i class="fas fa-{{ $td === 'audio' ? 'headphones' : ($td === 'video' ? 'play-circle' : ($td === 'mixto' ? 'layer-group' : 'file-alt')) }} ce-lesson-type-icon"></i>
                                <span class="ce-lesson-name">{{ $leccion->nombre }}</span>
                                @if ($leccion->duracion_min)
                                    <span class="ce-lesson-dur">
                                        <i class="far fa-clock mr-1"></i>{{ $leccion->duracion_min }} min
                                    </span>
                                @endif
                            </a>
                        @endforeach
                        @if ($unidad->ejercicios->isNotEmpty())
                            <div class="ce-content-section-label" style="margin-top:.5rem;">
                                <i class="fas fa-dumbbell"></i> Ejercicios
                                <span>{{ $unidad->ejercicios->count() }} en esta unidad</span>
                            </div>
                            @php $ejLetters = ['A','B','C','D','E','F','G','H']; @endphp
                            @foreach ($unidad->ejercicios as $ejercicio)
                                @php
                                    $doneEj   = isset($completadasEjercicios[$ejercicio->id]);
                                    $errorEj  = session('error_ej_' . $ejercicio->id);
                                    $lastResp = session('resp_ej_'  . $ejercicio->id);
                                @endphp
                                <div class="ce-exercise-card {{ $doneEj ? 'solved' : ($errorEj ? 'wrong' : '') }}"
                                     id="exercise-{{ $ejercicio->id }}">

                                    {{-- Header --}}
                                    <div class="ce-exercise-header">
                                        <div class="ce-exercise-num">{{ $loop->iteration }}</div>
                                        <div class="ce-exercise-meta">
                                            <p class="ce-exercise-pregunta">{{ $ejercicio->pregunta }}</p>
                                            <span class="ce-exercise-pts">
                                                <i class="fas fa-star" style="font-size:.6rem;color:var(--ce-amber);"></i>
                                                {{ $ejercicio->puntaje_max }} pt{{ $ejercicio->puntaje_max != 1 ? 's' : '' }}
                                                &nbsp;&middot;&nbsp;
                                                <span class="ce-exercise-type-chip">
                                                    @if ($ejercicio->tipo === 'multiple')
                                                        <i class="fas fa-list-ul"></i> Opción múltiple
                                                    @elseif ($ejercicio->tipo === 'completar')
                                                        <i class="fas fa-pencil-alt"></i> Completar
                                                    @else
                                                        <i class="fas fa-random"></i> Emparejar
                                                    @endif
                                                </span>
                                            </span>
                                        </div>
                                        <span class="ce-exercise-badge {{ $doneEj ? 'solved' : ($errorEj ? 'wrong' : 'pending') }}">
                                            @if ($doneEj)
                                                <i class="fas fa-check-circle"></i> Resuelto
                                            @elseif ($errorEj)
                                                <i class="fas fa-redo"></i> Reintentar
                                            @else
                                                Pendiente
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Body --}}
                                    @if ($doneEj)
                                        <div class="ce-solved-row">
                                            <i class="fas fa-check-circle"></i>
                                            <div>
                                                <span>¡Ejercicio completado — buen trabajo!</span>
                                                <span style="display:block;font-size:.78rem;color:#059669;opacity:.85;margin-top:.15rem;">
                                                    <i class="fas fa-lightbulb" style="font-size:.7rem;"></i>
                                                    Respuesta: <strong>{{ $ejercicio->respuesta_correcta }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                        @if ($ejercicio->audio_url)
                                            <div class="ce-audio-pron" style="margin-top:.75rem">
                                                <div class="ce-audio-pron-label">
                                                    <i class="fas fa-headphones"></i> Pronunciación de referencia
                                                </div>
                                                <div class="ce-audio-pron-player">
                                                    <button type="button"
                                                            class="ce-audio-play-btn"
                                                            id="play-{{ $ejercicio->id }}"
                                                            data-audio="audio-{{ $ejercicio->id }}"
                                                            aria-label="Reproducir audio">
                                                        <i class="fas fa-play" id="play-icon-{{ $ejercicio->id }}"></i>
                                                    </button>
                                                    <div class="ce-audio-track">
                                                        <div class="ce-audio-bar-wrap"
                                                             id="bar-wrap-{{ $ejercicio->id }}"
                                                             data-audio="audio-{{ $ejercicio->id }}">
                                                            <div class="ce-audio-bar-fill" id="bar-{{ $ejercicio->id }}"></div>
                                                        </div>
                                                        <div class="ce-audio-time">
                                                            <span id="cur-{{ $ejercicio->id }}">0:00</span>
                                                            <span id="tot-{{ $ejercicio->id }}">-:--</span>
                                                        </div>
                                                    </div>
                                                    <audio id="audio-{{ $ejercicio->id }}"
                                                           class="ce-exercise-audio"
                                                           preload="auto">
                                                        @php
                                                            $audioMime = match(strtolower(pathinfo($ejercicio->audio_url, PATHINFO_EXTENSION))) {
                                                                'm4a', 'mp4', 'aac' => 'audio/mp4',
                                                                'ogg'               => 'audio/ogg',
                                                                'mp3'               => 'audio/mpeg',
                                                                'wav'               => 'audio/wav',
                                                                default             => 'audio/webm',
                                                            };
                                                        @endphp
                                                        <source src="{{ $ejercicio->audio_src }}"
                                                                type="{{ $audioMime }}">
                                                    </audio>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="ce-exercise-body">
                                            {{-- Feedback de respuesta incorrecta --}}
                                            @if ($errorEj)
                                                <div class="ce-feedback-wrong">
                                                    <i class="fas fa-times-circle"></i>
                                                    <div>
                                                        <strong>Respuesta incorrecta — inténtalo de nuevo</strong>
                                                        Tu respuesta: <em>{{ $lastResp }}</em>
                                                    </div>
                                                </div>
                                            @endif

                                            <form action="{{ route('ce.alumno.ejercicio.resolver', [$curso, $ejercicio]) }}" method="POST">
                                                @csrf
                                                {{-- Reproductor de pronunciación --}}
                                                @if ($ejercicio->audio_url)
                                                    <div class="ce-audio-pron">
                                                        <div class="ce-audio-pron-label">
                                                            <i class="fas fa-headphones"></i> Escucha la pronunciación
                                                        </div>
                                                        <div class="ce-audio-pron-player">
                                                            <button type="button"
                                                                    class="ce-audio-play-btn"
                                                                    id="play-{{ $ejercicio->id }}"
                                                                    data-audio="audio-{{ $ejercicio->id }}"
                                                                    aria-label="Reproducir audio">
                                                                <i class="fas fa-play" id="play-icon-{{ $ejercicio->id }}"></i>
                                                            </button>
                                                            <div class="ce-audio-track">
                                                                <div class="ce-audio-bar-wrap"
                                                                     id="bar-wrap-{{ $ejercicio->id }}"
                                                                     data-audio="audio-{{ $ejercicio->id }}">
                                                                    <div class="ce-audio-bar-fill" id="bar-{{ $ejercicio->id }}"></div>
                                                                </div>
                                                                <div class="ce-audio-time">
                                                                    <span id="cur-{{ $ejercicio->id }}">0:00</span>
                                                                    <span id="tot-{{ $ejercicio->id }}">-:--</span>
                                                                </div>
                                                            </div>
                                                            <audio id="audio-{{ $ejercicio->id }}"
                                                                   class="ce-exercise-audio"
                                                                   preload="auto">
                                                                @php
                                                                    $audioMime = match(strtolower(pathinfo($ejercicio->audio_url, PATHINFO_EXTENSION))) {
                                                                        'm4a', 'mp4', 'aac' => 'audio/mp4',
                                                                        'ogg'               => 'audio/ogg',
                                                                        'mp3'               => 'audio/mpeg',
                                                                        'wav'               => 'audio/wav',
                                                                        default             => 'audio/webm',
                                                                    };
                                                                @endphp
                                                                <source src="{{ $ejercicio->audio_src }}"
                                                                        type="{{ $audioMime }}">
                                                            </audio>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (! empty($ejercicio->opciones) && is_array($ejercicio->opciones))
                                                    <div class="ce-options-list">
                                                        @foreach ($ejercicio->opciones as $opcion)
                                                            @php $wasWrong = $errorEj && $lastResp === $opcion; @endphp
                                                            <label class="ce-option {{ $wasWrong ? 'was-wrong' : '' }}">
                                                                <input type="radio" name="respuesta" value="{{ $opcion }}"
                                                                       {{ $wasWrong ? 'checked' : '' }} required>
                                                                <span class="ce-option-letter">{{ $ejLetters[$loop->index] ?? chr(65+$loop->index) }}</span>
                                                                <span class="ce-option-dot"></span>
                                                                {{ $opcion }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <textarea name="respuesta" class="ce-textarea" rows="3"
                                                              placeholder="Escribe tu respuesta aquí..."
                                                              required>{{ $lastResp }}</textarea>
                                                @endif
                                                <button type="submit" class="ce-btn-submit {{ $errorEj ? 'retry' : '' }}">
                                                    @if ($errorEj)
                                                        <i class="fas fa-redo"></i> Volver a intentar
                                                    @else
                                                        <i class="fas fa-paper-plane"></i> Enviar respuesta
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
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
<script>
/* ── Accordion toggles ── */
function toggleNivel(id) {
    const body = document.getElementById('body-nivel-' + id);
    const chev = document.getElementById('chev-nivel-' + id);
    body.classList.toggle('open');
    chev.classList.toggle('open');
    if (body.classList.contains('open')) animateExercisesIn(body);
}
function toggleUnidad(id) {
    const body = document.getElementById('body-unidad-' + id);
    const chev = document.getElementById('chev-unidad-' + id);
    body.classList.toggle('open');
    chev.classList.toggle('open');
    if (body.classList.contains('open')) {
        animateExercisesIn(body);
        // Forzar carga de metadatos de audios que estaban suspendidos (display:none)
        body.querySelectorAll('.ce-exercise-audio').forEach(a => {
            if (a.readyState === 0) a.load();
        });
    }
}

/* Animate exercise cards into view when their container opens */
function animateExercisesIn(container) {
    if (typeof gsap === 'undefined') return;
    const cards = container.querySelectorAll('.ce-exercise-card');
    if (!cards.length) return;
    gsap.fromTo(cards,
        { opacity: 0, y: 18, scale: .97 },
        { opacity: 1, y: 0, scale: 1, duration: .42, stagger: .08, ease: 'back.out(1.4)' }
    );
}

/* ── GSAP ── */
(function () {
    if (typeof gsap === 'undefined') return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Detectar si venimos de un submit de ejercicio ──────────
    const hash      = window.location.hash;
    const anchorEl  = (hash.startsWith('#exercise-')) ? document.querySelector(hash) : null;

    // Abrir los acordeones que contienen el ejercicio objetivo
    if (anchorEl) abrirPadres(anchorEl);

    if (reducedMotion) {
        document.querySelectorAll('.ce-stat,.ce-nivel').forEach(el => el.style.opacity = 1);
        animateBars();
        if (anchorEl) irAlEjercicio(anchorEl, false);
        return;
    }

    // Global progress bar
    setTimeout(() => {
        const bar = document.getElementById('global-bar');
        if (bar) bar.style.width = bar.dataset.pct + '%';
    }, 300);

    // Stats row
    gsap.set('.ce-stat', { opacity: 0, y: 20 });
    gsap.to('.ce-stat', { opacity: 1, y: 0, duration: .55, stagger: .1, ease: 'power2.out' });

    // Nivel cards
    if (anchorEl) {
        // Cuando hay anchor: mostrar todos los niveles inmediatamente sin stagger
        // (el usuario ya sabe dónde está, no necesita la animación de entrada)
        gsap.set('.ce-nivel', { opacity: 1, y: 0 });
        irAlEjercicio(anchorEl, true);
    } else {
        // Sin anchor: IntersectionObserver para animar al hacer scroll
        const nivelEls = gsap.utils.toArray('.ce-nivel');
        gsap.set(nivelEls, { opacity: 0, y: 30 });
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const idx = parseInt(entry.target.dataset.nivelIdx || 0);
                gsap.to(entry.target, { opacity: 1, y: 0, duration: .52, ease: 'power2.out', delay: idx * .05 });
                io.unobserve(entry.target);
            });
        }, { threshold: 0.04 });
        nivelEls.forEach((el, i) => { el.dataset.nivelIdx = i; io.observe(el); });
    }

    // Exercise cards ya visibles al cargar (unidades abiertas)
    document.querySelectorAll('.ce-unidad-body.open .ce-exercise-card').forEach((card, i) => {
        gsap.fromTo(card,
            { opacity: 0, y: 16, scale: .97 },
            { opacity: 1, y: 0, scale: 1, duration: .45, delay: .3 + i * .07, ease: 'back.out(1.3)' }
        );
    });

    function animateBars() {
        document.querySelectorAll('.ce-unidad-progress-fill').forEach(bar => {
            bar.style.width = bar.dataset.pct + '%';
        });
    }
    animateBars();

    // Option card press micro-animation
    document.querySelectorAll('.ce-option').forEach(opt => {
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) {
            radio.addEventListener('change', () => {
                const list = opt.closest('.ce-options-list');
                list.querySelectorAll('.ce-option').forEach(s => s.classList.remove('selected'));
                opt.classList.add('selected');
                gsap.fromTo(opt, { scale: .97 }, { scale: 1, duration: .25, ease: 'back.out(2)' });
            });
        }
    });

    // Submit button press animation
    document.querySelectorAll('.ce-btn-submit').forEach(btn => {
        btn.addEventListener('click', function () {
            gsap.to(this, { scale: .93, duration: .1, yoyo: true, repeat: 1, ease: 'power1.inOut' });
        });
    });

    // ── Reproductores de audio de pronunciación ──────────────
    function fmtTime(s) {
        if (!s || isNaN(s) || !isFinite(s)) return '-:--';
        return Math.floor(s / 60) + ':' + String(Math.floor(s % 60)).padStart(2, '0');
    }

    function stopAllAudio(exceptId) {
        document.querySelectorAll('.ce-exercise-audio').forEach(a => {
            if (a.id === exceptId) return;
            a.pause();
            const id   = a.id.replace('audio-', '');
            const icon = document.getElementById('play-icon-' + id);
            const btn  = document.getElementById('play-' + id);
            if (icon) icon.className = 'fas fa-play';
            if (btn)  btn.classList.remove('playing');
        });
    }

    document.querySelectorAll('.ce-audio-play-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const audioId = this.dataset.audio;
            const audio   = document.getElementById(audioId);
            const id      = audioId.replace('audio-', '');
            const icon    = document.getElementById('play-icon-' + id);
            if (!audio) return;

            const self = this;
            if (audio.paused) {
                stopAllAudio(audioId);
                audio.play()
                    .then(() => {
                        if (icon) icon.className = 'fas fa-pause';
                        self.classList.add('playing');
                    })
                    .catch(err => {
                        console.error('Audio error:', audio.currentSrc, err);
                    });
            } else {
                audio.pause();
                if (icon) icon.className = 'fas fa-play';
                this.classList.remove('playing');
            }
        });
    });

    document.querySelectorAll('.ce-exercise-audio').forEach(audio => {
        const id      = audio.id.replace('audio-', '');
        const bar     = document.getElementById('bar-'      + id);
        const cur     = document.getElementById('cur-'      + id);
        const tot     = document.getElementById('tot-'      + id);
        const btn     = document.getElementById('play-'     + id);
        const barWrap = document.getElementById('bar-wrap-' + id);

        function syncDur() {
            if (tot && isFinite(audio.duration) && audio.duration > 0)
                tot.textContent = fmtTime(audio.duration);
        }
        syncDur();
        audio.addEventListener('loadedmetadata', syncDur);
        audio.addEventListener('durationchange', syncDur);

        audio.addEventListener('timeupdate', () => {
            if (!audio.duration || !isFinite(audio.duration)) return;
            if (bar) bar.style.width = (audio.currentTime / audio.duration * 100) + '%';
            if (cur) cur.textContent = fmtTime(audio.currentTime);
        });
        audio.addEventListener('ended', () => {
            const icon = document.getElementById('play-icon-' + id);
            if (icon) icon.className = 'fas fa-play';
            if (btn)  btn.classList.remove('playing');
            if (bar)  bar.style.width = '0%';
            audio.currentTime = 0;
        });

        if (barWrap) {
            barWrap.addEventListener('click', function (e) {
                if (!audio.duration || !isFinite(audio.duration)) return;
                const rect = this.getBoundingClientRect();
                audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
            });
        }
    });

    // ── Helpers ────────────────────────────────────────────────

    /* Abre el nivel y la unidad que contienen el ejercicio */
    function abrirPadres(el) {
        [
            { bodyPrefix: 'body-unidad-', chevPrefix: 'chev-unidad-', cls: '.ce-unidad-body' },
            { bodyPrefix: 'body-nivel-',  chevPrefix: 'chev-nivel-',  cls: '.ce-nivel-body'  },
        ].forEach(({ bodyPrefix, chevPrefix, cls }) => {
            const body = el.closest(cls);
            if (!body) return;
            if (!body.classList.contains('open')) {
                body.classList.add('open');
                const id   = body.id.replace(bodyPrefix, '');
                const chev = document.getElementById(chevPrefix + id);
                if (chev) chev.classList.add('open');
            }
        });
    }

    /* Scroll suave + highlight de pulso al ejercicio */
    function irAlEjercicio(el, withGsap) {
        // Doble rAF: esperar a que el DOM pinte las unidades abiertas antes de medir posición
        requestAnimationFrame(() => requestAnimationFrame(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (!withGsap) return;
            // Pulso de highlight en el borde de la tarjeta
            gsap.fromTo(el,
                { boxShadow: '0 0 0 4px rgba(131,56,236,.7)' },
                { boxShadow: '0 0 0 0px rgba(131,56,236,0)', duration: 1.8, ease: 'power2.out', delay: .5 }
            );
        }));
    }
}());
</script>
@endpush
