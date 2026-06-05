@extends($layout ?? 'layouts.superadmin')
@section('titulo', 'Estructura: ' . $curso->nombre)
@php $rp = $rp ?? 'ce.cursos'; @endphp

@push('styles')
    <style>
        /* ── Sticky toolbar ── */
        .cea-toolbar {
            position: sticky;
            top: 0;
            z-index: 200;
            background: #fff;
            border-bottom: 2px solid #e9ecef;
            padding: .85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            margin: 0 -1.5rem 1.75rem;
        }

        .cea-toolbar-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: #4e73df;
            font-weight: 700;
            font-size: .85rem;
            text-decoration: none;
        }

        .cea-toolbar-back:hover {
            opacity: .75;
            text-decoration: none;
            color: #4e73df;
        }

        .cea-toolbar-title {
            font-weight: 800;
            font-size: .95rem;
            color: #2d3561;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cea-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem 1rem;
            border-radius: .5rem;
            font-size: .8rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s, transform .12s;
        }

        .cea-btn:hover {
            opacity: .85;
            transform: translateY(-1px);
            text-decoration: none;
        }

        .cea-btn-primary {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff;
        }

        .cea-btn-primary:hover {
            color: #fff;
        }

        /* ── Level card ── */
        .cea-nivel {
            border-radius: .9rem;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(0, 0, 0, .07);
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .cea-nivel-hdr {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #2d3561, #4e73df);
            color: #fff;
        }

        .cea-nivel-num {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .cea-nivel-name {
            font-weight: 700;
            font-size: .95rem;
            flex: 1;
        }

        .cea-nivel-actions {
            display: flex;
            gap: .4rem;
            flex-shrink: 0;
        }

        .cea-ico-btn {
            width: 30px;
            height: 30px;
            border-radius: .4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s;
        }

        .cea-ico-btn:hover {
            opacity: .8;
            text-decoration: none;
        }

        .cea-ico-btn.add {
            background: rgba(28, 200, 138, .8);
            color: #fff;
        }

        .cea-ico-btn.edit {
            background: rgba(246, 194, 62, .9);
            color: #fff;
        }

        .cea-ico-btn.delete {
            background: rgba(231, 74, 59, .85);
            color: #fff;
        }

        /* ── Level body ── */
        .cea-nivel-body {
            padding: 1rem;
            background: #f8f9fc;
        }

        /* ── Unit card ── */
        .cea-unit {
            background: #fff;
            border-radius: .7rem;
            border: 1px solid #e9ecef;
            margin-bottom: .85rem;
            overflow: hidden;
        }

        .cea-unit-hdr {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1rem;
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
        }

        .cea-unit-icon {
            color: #36b9cc;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .cea-unit-name {
            font-weight: 700;
            font-size: .88rem;
            color: #2d3561;
            flex: 1;
        }

        .cea-unit-desc {
            font-size: .75rem;
            color: #aaa;
        }

        .cea-unit-actions {
            display: flex;
            gap: .3rem;
            flex-shrink: 0;
        }

        /* ── Content list ── */
        .cea-content-list {
            padding: .5rem .85rem .75rem;
        }

        .cea-content-section-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #aaa;
            margin: .6rem 0 .35rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .cea-content-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }

        .cea-item {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .55rem .75rem;
            border-radius: .5rem;
            margin-bottom: .25rem;
            background: #f8f9fc;
            border: 1px solid #f0f0f0;
            transition: background .15s;
        }

        .cea-item:hover {
            background: #f0f4ff;
        }

        .cea-item-icon {
            width: 28px;
            height: 28px;
            border-radius: .4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            flex-shrink: 0;
        }

        .cea-item-icon.texto {
            background: #e8f0ff;
            color: #4e73df;
        }

        .cea-item-icon.audio {
            background: #e0fbf4;
            color: #1cc88a;
        }

        .cea-item-icon.video {
            background: #fde8e8;
            color: #e74a3b;
        }

        .cea-item-icon.ejercicio {
            background: #fff3cd;
            color: #f6c23e;
        }

        .cea-item-name {
            font-size: .84rem;
            font-weight: 600;
            color: #2d3561;
            flex: 1;
            min-width: 0;
        }

        .cea-item-name small {
            font-weight: 400;
            color: #aaa;
            display: block;
            font-size: .72rem;
        }

        .cea-item-acts {
            display: flex;
            gap: .25rem;
            flex-shrink: 0;
        }

        .cea-item-act {
            width: 26px;
            height: 26px;
            border-radius: .35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .12s;
        }

        .cea-item-act:hover {
            opacity: .75;
            text-decoration: none;
        }

        .cea-item-act.edit {
            background: #fff3cd;
            color: #dda20a;
        }

        .cea-item-act.delete {
            background: #fde8e8;
            color: #e74a3b;
        }

        .cea-empty-unit {
            text-align: center;
            padding: 1rem;
            color: #ccc;
            font-size: .82rem;
        }

        /* ── Accordion toggle areas ── */
        .cea-acc-toggle {
            display: flex; align-items: center; gap: .85rem;
            flex: 1; cursor: pointer; min-width: 0; padding: 0;
        }
        .cea-unit-hdr .cea-acc-toggle {
            gap: .65rem;
        }
        .cea-acc-chev {
            font-size: .7rem; opacity: .65;
            transition: transform .28s ease;
            flex-shrink: 0;
        }
        .cea-nivel-body { overflow: hidden; }
        .cea-content-list { overflow: hidden; }

        /* ── Inscritos table ── */
        .cea-inscritos-table thead th {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #999;
            border-top: none;
            padding: .5rem .75rem;
            background: #f8f9fc;
        }

        /* Group row */
        .cea-group-row {
            cursor: pointer;
            background: #f3f0ff;
            border-left: 3px solid #8338ec;
            transition: background .15s;
        }
        .cea-group-row:hover { background: #ede8ff; }
        .cea-group-row td { border-top: 4px solid #fff; }

        .cea-group-badge-count {
            font-size: .68rem;
            font-weight: 700;
            background: rgba(131,56,236,.12);
            color: #8338ec;
            border-radius: 99px;
            padding: .1rem .5rem;
            margin-left: .15rem;
        }

        /* Student rows */
        .cea-student-row { transition: background .1s; }
        .cea-student-row:hover { background: #fafbff; }
        .cea-student-row td { border-top: 1px solid #f5f5f5; }

        /* Mini progress bar */
        .cea-bar-wrap {
            height: 6px;
            background: #e9ecef;
            border-radius: 99px;
            overflow: hidden;
            min-width: 60px;
        }
        .cea-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .6s ease;
        }
        .cea-bar-pct {
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
            display: inline-block;
            margin-top: .15rem;
        }

        /* ── Chip de respuesta correcta en ejercicios ── */
        .cea-resp-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            margin-top: .25rem;
            padding: .15rem .55rem;
            background: rgba(28, 200, 138, .1);
            color: #0d7a54;
            border: 1px solid rgba(28, 200, 138, .25);
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── Mini reproductor de audio en ejercicios ── */
        .cea-audio-mini-btn {
            width: 26px;
            height: 26px;
            border-radius: .35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            border: none;
            cursor: pointer;
            background: rgba(28, 200, 138, .15);
            color: #1cc88a;
            transition: background .15s, color .15s;
            flex-shrink: 0;
        }
        .cea-audio-mini-btn:hover { background: rgba(28, 200, 138, .3); }
        .cea-audio-mini-btn.playing { background: #1cc88a; color: #fff; }

        @media (max-width: 576px) {
            .cea-toolbar {
                padding: .75rem 1rem;
            }

            .cea-nivel-hdr {
                flex-wrap: wrap;
            }

            .cea-unit-hdr {
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@section('contenido')
    {{-- Sticky toolbar --}}
    <div class="cea-toolbar mx-2">
        <a href="{{ route($rp . '.index') }}" class="cea-toolbar-back">
            <i class="fas fa-arrow-left"></i> Mis Cursos
        </a>
        <span style="color:#dee2e6">|</span>
        <span class="cea-toolbar-title"><i class="fas fa-sitemap mr-1" style="color:#4e73df"></i>{{ $curso->nombre }}</span>
        <a href="{{ route($rp . '.niveles.create', $curso) }}" class="cea-btn cea-btn-primary ml-auto">
            <i class="fas fa-plus"></i> Agregar Nivel
        </a>
        <a href="{{ route($rp . '.edit', $curso) }}" class="cea-btn" style="background:#fff3cd;color:#dda20a">
            <i class="fas fa-edit"></i> Editar Curso
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (! empty($alumnos) && $alumnos->isNotEmpty())
        <div class="cea-nivel cea-inscritos-card" style="margin-bottom:1.5rem;">
            <div class="cea-nivel-hdr">
                <div class="cea-acc-toggle" onclick="toggleInscritos()">
                    <span class="cea-nivel-num"><i class="fas fa-users"></i></span>
                    <span class="cea-nivel-name">Estudiantes inscritos</span>
                    <i class="fas fa-chevron-down cea-acc-chev" id="nc-inscritos"></i>
                </div>
                <div class="cea-nivel-actions" onclick="event.stopPropagation()">
                    <span class="cea-ico-btn" style="background:rgba(78,115,223,.12);color:#4e73df;cursor:default;width:auto;padding:0 .7rem;">
                        {{ $alumnos->count() }} alumno{{ $alumnos->count() !== 1 ? 's' : '' }}
                    </span>
                    <span class="cea-ico-btn" style="background:rgba(28,200,138,.12);color:#1cc88a;cursor:default;width:auto;padding:0 .7rem;">
                        <i class="fas fa-chart-line mr-1" style="font-size:.7rem"></i>{{ $promedioAvance }}% promedio
                    </span>
                    <span class="cea-ico-btn" style="background:rgba(131,56,236,.1);color:#8338ec;cursor:default;width:auto;padding:0 .7rem;">
                        {{ $alumnosPorGrupo->count() }} grupo{{ $alumnosPorGrupo->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div class="cea-nivel-body" id="nb-inscritos" style="padding:.75rem 1rem 1rem;">
                <div class="table-responsive">
                    <table class="table table-sm cea-inscritos-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:33%">Estudiante</th>
                                <th style="width:11%">DNI</th>
                                <th>Ciclo</th>
                                <th style="width:30%">Avance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($alumnosPorGrupo as $grupo => $items)
                            @php
                                $gId      = 'g' . $loop->index;
                                $gAvg     = (int) round($items->avg('porcentaje'));
                                $gCount   = $items->count();
                                [$gProg, $gCiclo] = array_pad(explode(' / ', $grupo, 2), 2, '—');
                            @endphp
                            {{-- Fila de grupo (colapsable) --}}
                            <tr class="cea-group-row" data-group="{{ $gId }}" onclick="toggleGrupo('{{ $gId }}', this)">
                                <td colspan="3" style="padding:.55rem .75rem;">
                                    <div style="display:flex;align-items:center;gap:.55rem;">
                                        <i class="fas fa-chevron-down cea-group-chev" id="chev-{{ $gId }}"
                                           style="font-size:.65rem;color:#8338ec;transition:transform .25s;"></i>
                                        <span style="font-weight:800;font-size:.83rem;color:#2d3561;">{{ $gProg }}</span>
                                        <span style="font-size:.75rem;color:#888;font-weight:600;">/ {{ $gCiclo }}</span>
                                        <span class="cea-group-badge-count">{{ $gCount }} alumno{{ $gCount !== 1 ? 's' : '' }}</span>
                                    </div>
                                </td>
                                <td style="padding:.55rem .75rem;">
                                    <div class="cea-bar-wrap">
                                        <div class="cea-bar-fill" style="width:{{ $gAvg }}%;background:linear-gradient(90deg,#8338ec,#3a86ff);"></div>
                                    </div>
                                    <span class="cea-bar-pct" style="color:#8338ec;">{{ $gAvg }}%</span>
                                </td>
                            </tr>
                            {{-- Filas de alumnos del grupo --}}
                            @foreach ($items as $alumno)
                                <tr class="cea-student-row" data-parent="{{ $gId }}">
                                    <td style="padding:.45rem .75rem .45rem 2rem;">
                                        <span style="font-size:.84rem;font-weight:600;color:#2d3561;">
                                            {{ trim(($alumno['user']->nombre ?? '') . ' ' . ($alumno['user']->apellidos ?? '')) ?: ($alumno['user']->name ?? 'Sin nombre') }}
                                        </span>
                                    </td>
                                    <td style="padding:.45rem .75rem;">
                                        <span style="font-size:.78rem;color:#888;font-family:monospace;letter-spacing:.03em;">
                                            {{ $alumno['user']->dni ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:.45rem .75rem;">
                                        <span style="font-size:.78rem;color:#666;">{{ $alumno['ciclo'] }}</span>
                                    </td>
                                    <td style="padding:.45rem .75rem;">
                                        <div style="display:flex;align-items:center;gap:.5rem;">
                                            <div class="cea-bar-wrap" style="flex:1;">
                                                <div class="cea-bar-fill" style="width:{{ $alumno['porcentaje'] }}%;background:{{ $alumno['porcentaje'] >= 80 ? 'linear-gradient(90deg,#1cc88a,#13a368)' : ($alumno['porcentaje'] >= 40 ? 'linear-gradient(90deg,#f6c23e,#e0a800)' : 'linear-gradient(90deg,#e74a3b,#c0392b)') }};"></div>
                                            </div>
                                            <span style="font-size:.78rem;font-weight:700;width:32px;text-align:right;color:#444;">{{ $alumno['porcentaje'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @forelse ($curso->niveles as $nivel)
        <div class="cea-nivel">
            <div class="cea-nivel-hdr">
                <div class="cea-acc-toggle" onclick="toggleNivel({{ $nivel->id }})">
                    <span class="cea-nivel-num">{{ $loop->iteration }}</span>
                    <span class="cea-nivel-name">{{ $nivel->nombre }}</span>
                    <span style="font-size:.72rem;opacity:.55;margin-right:.25rem;">
                        {{ $nivel->unidades->count() }} unidad{{ $nivel->unidades->count() !== 1 ? 'es' : '' }}
                    </span>
                    <i class="fas fa-chevron-down cea-acc-chev" id="nc-{{ $nivel->id }}"
                       style="{{ $loop->first ? '' : 'transform:rotate(-90deg)' }}"></i>
                </div>
                <div class="cea-nivel-actions" onclick="event.stopPropagation()">
                    <a href="{{ route($rp . '.unidades.create', [$curso, $nivel]) }}" class="cea-ico-btn add"
                        title="Agregar Unidad">
                        <i class="fas fa-folder-plus"></i>
                    </a>
                    <a href="{{ route($rp . '.niveles.edit', [$curso, $nivel]) }}" class="cea-ico-btn edit"
                        title="Editar Nivel">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form action="{{ route($rp . '.niveles.destroy', [$curso, $nivel]) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('¿Eliminar el nivel «{{ $nivel->nombre }}» y todo su contenido?')">
                        @csrf @method('DELETE')
                        <button class="cea-ico-btn delete" title="Eliminar Nivel">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="cea-nivel-body" id="nb-{{ $nivel->id }}"
                 style="{{ $loop->first ? '' : 'display:none' }}">
                @forelse ($nivel->unidades as $unidad)
                    @php
                        $uTotal = $unidad->lecciones->count() + $unidad->ejercicios->count();
                    @endphp
                    <div class="cea-unit">
                        <div class="cea-unit-hdr">
                            <div class="cea-acc-toggle" onclick="toggleUnidad({{ $unidad->id }})">
                                <i class="fas fa-folder-open cea-unit-icon"></i>
                                <div style="flex:1;min-width:0">
                                    <div class="cea-unit-name">{{ $unidad->nombre }}</div>
                                    @if ($unidad->descripcion)
                                        <div class="cea-unit-desc">{{ Str::limit($unidad->descripcion, 70) }}</div>
                                    @endif
                                </div>
                                <span style="font-size:.72rem;color:#aaa;white-space:nowrap;margin-right:.3rem;">
                                    {{ $uTotal }} ítem{{ $uTotal !== 1 ? 's' : '' }}
                                </span>
                                <i class="fas fa-chevron-down cea-acc-chev" id="uc-{{ $unidad->id }}"
                                   style="{{ $loop->first ? '' : 'transform:rotate(-90deg)' }}"></i>
                            </div>
                            <div class="cea-unit-actions" onclick="event.stopPropagation()">
                                <a href="{{ route($rp . '.lecciones.create', [$curso, $nivel, $unidad]) }}"
                                    class="cea-ico-btn add" title="Agregar Lección"
                                    style="background:rgba(78,115,223,.15);color:#4e73df">
                                    <i class="fas fa-book-open"></i>
                                </a>
                                <a href="{{ route($rp . '.ejercicios.create', [$curso, $nivel, $unidad]) }}"
                                    class="cea-ico-btn add" title="Agregar Ejercicio">
                                    <i class="fas fa-tasks"></i>
                                </a>
                                <a href="{{ route($rp . '.unidades.edit', [$curso, $nivel, $unidad]) }}"
                                    class="cea-ico-btn edit" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route($rp . '.unidades.destroy', [$curso, $nivel, $unidad]) }}"
                                    method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta unidad?')">
                                    @csrf @method('DELETE')
                                    <button class="cea-ico-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>

                        <div class="cea-content-list" id="ul-{{ $unidad->id }}"
                             style="{{ $loop->first ? '' : 'display:none' }}">
                            @if ($unidad->lecciones->isNotEmpty())
                                <div class="cea-content-section-label">
                                    <i class="fas fa-book-open"></i> Lecciones ({{ $unidad->lecciones->count() }})
                                </div>
                                @foreach ($unidad->lecciones as $leccion)
                                    <div class="cea-item">
                                        <span class="cea-item-icon {{ $leccion->tipo }}">
                                            <i
                                                class="fas fa-{{ $leccion->tipo === 'video' ? 'play-circle' : ($leccion->tipo === 'audio' ? 'headphones' : 'file-alt') }}"></i>
                                        </span>
                                        <div class="cea-item-name">
                                            {{ $leccion->nombre }}
                                            <small>
                                                {{ ucfirst($leccion->tipo) }}
                                                @if ($leccion->duracion_min)
                                                    · {{ $leccion->duracion_min }} min
                                                @endif
                                            </small>
                                        </div>
                                        <div class="cea-item-acts">
                                            <a href="{{ route($rp . '.lecciones.edit', [$curso, $nivel, $unidad, $leccion]) }}"
                                                class="cea-item-act edit" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form
                                                action="{{ route($rp . '.lecciones.destroy', [$curso, $nivel, $unidad, $leccion]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Eliminar esta lección?')">
                                                @csrf @method('DELETE')
                                                <button class="cea-item-act delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if ($unidad->ejercicios->isNotEmpty())
                                <div class="cea-content-section-label">
                                    <i class="fas fa-tasks"></i> Ejercicios ({{ $unidad->ejercicios->count() }})
                                </div>
                                @foreach ($unidad->ejercicios as $ejercicio)
                                    <div class="cea-item">
                                        <span class="cea-item-icon ejercicio">
                                            <i class="fas fa-question-circle"></i>
                                        </span>
                                        <div class="cea-item-name">
                                            {{ Str::limit($ejercicio->pregunta, 65) }}
                                            <small>{{ ucfirst($ejercicio->tipo) }} · {{ $ejercicio->puntaje_max }}
                                                pt{{ $ejercicio->puntaje_max != 1 ? 's' : '' }}
                                                @if ($ejercicio->audio_url)
                                                    &nbsp;&middot;&nbsp;<i class="fas fa-microphone" style="color:#1cc88a;font-size:.65rem;"></i> audio
                                                @endif
                                            </small>
                                            @if ($ejercicio->respuesta_correcta)
                                                <span class="cea-resp-chip">
                                                    <i class="fas fa-check" style="font-size:.6rem;"></i>
                                                    {{ Str::limit($ejercicio->respuesta_correcta, 40) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($ejercicio->audio_url)
                                            @php
                                                $aMime = match(pathinfo($ejercicio->audio_url, PATHINFO_EXTENSION)) {
                                                    'm4a', 'mp4' => 'audio/mp4',
                                                    'ogg'        => 'audio/ogg',
                                                    default      => 'audio/webm',
                                                };
                                            @endphp
                                            <button type="button"
                                                    class="cea-audio-mini-btn"
                                                    id="btn-audio-{{ $ejercicio->id }}"
                                                    data-id="{{ $ejercicio->id }}"
                                                    title="Reproducir pronunciación">
                                                <i class="fas fa-play" id="icon-audio-{{ $ejercicio->id }}"></i>
                                            </button>
                                            <audio id="audio-{{ $ejercicio->id }}"
                                                   class="cea-admin-audio"
                                                   preload="none"
                                                   style="display:none">
                                                <source src="{{ $ejercicio->audio_url }}" type="{{ $aMime }}">
                                            </audio>
                                        @endif
                                        <div class="cea-item-acts">
                                            <a href="{{ route($rp . '.ejercicios.edit', [$curso, $nivel, $unidad, $ejercicio]) }}"
                                                class="cea-item-act edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form
                                                action="{{ route($rp . '.ejercicios.destroy', [$curso, $nivel, $unidad, $ejercicio]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Eliminar este ejercicio?')">
                                                @csrf @method('DELETE')
                                                <button class="cea-item-act delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if ($unidad->lecciones->isEmpty() && $unidad->ejercicios->isEmpty())
                                <div class="cea-empty-unit">
                                    <i class="fas fa-inbox mr-1"></i> Unidad vacía — agrega lecciones o ejercicios
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="cea-empty-unit" style="padding:1.5rem">
                        <i class="fas fa-folder-open mr-1"></i> Sin unidades —
                        <a href="{{ route($rp . '.unidades.create', [$curso, $nivel]) }}">Agregar la primera</a>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-layer-group fa-3x mb-3 d-block" style="opacity:.2"></i>
            <p class="mb-3">Este curso no tiene niveles aún.</p>
            <a href="{{ route($rp . '.niveles.create', $curso) }}" class="cea-btn cea-btn-primary"
                style="display:inline-flex">
                <i class="fas fa-plus"></i> Agregar el primer nivel
            </a>
        </div>
    @endforelse
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        /* ── Inscritos accordion ── */
        function toggleInscritos() {
            const body = document.getElementById('nb-inscritos');
            const chev = document.getElementById('nc-inscritos');
            const isOpen = body.style.display !== 'none';
            if (isOpen) {
                gsap.to(body, {
                    height: 0, opacity: 0, duration: .3, ease: 'power2.in',
                    onStart: () => { body.style.overflow = 'hidden'; },
                    onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
                });
                gsap.to(chev, { rotation: -90, duration: .25, ease: 'power1.out' });
            } else {
                body.style.display = 'block';
                body.style.overflow = 'hidden';
                body.style.height = '0';
                const h = body.scrollHeight;
                gsap.to(body, {
                    height: h, opacity: 1, duration: .38, ease: 'power2.out',
                    onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
                });
                gsap.to(chev, { rotation: 0, duration: .25, ease: 'power1.out' });
            }
        }

        /* ── Nivel accordion ── */
        function toggleNivel(id) {
            const body = document.getElementById('nb-' + id);
            const chev = document.getElementById('nc-' + id);
            const isOpen = body.style.display !== 'none';

            if (isOpen) {
                gsap.to(body, {
                    height: 0, opacity: 0, duration: .3, ease: 'power2.in',
                    onStart: () => { body.style.overflow = 'hidden'; },
                    onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
                });
                gsap.to(chev, { rotation: -90, duration: .25, ease: 'power1.out' });
            } else {
                body.style.display = 'block';
                body.style.overflow = 'hidden';
                body.style.height = '0';
                const h = body.scrollHeight;
                gsap.to(body, {
                    height: h, opacity: 1, duration: .38, ease: 'power2.out',
                    onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
                });
                gsap.to(chev, { rotation: 0, duration: .25, ease: 'power1.out' });
            }
        }

        /* ── Unidad accordion ── */
        function toggleUnidad(id) {
            const body = document.getElementById('ul-' + id);
            const chev = document.getElementById('uc-' + id);
            const isOpen = body.style.display !== 'none';

            if (isOpen) {
                gsap.to(body, {
                    height: 0, opacity: 0, duration: .25, ease: 'power2.in',
                    onStart: () => { body.style.overflow = 'hidden'; },
                    onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
                });
                gsap.to(chev, { rotation: -90, duration: .2, ease: 'power1.out' });
            } else {
                body.style.display = 'block';
                body.style.overflow = 'hidden';
                body.style.height = '0';
                const h = body.scrollHeight;
                gsap.to(body, {
                    height: h, opacity: 1, duration: .3, ease: 'power2.out',
                    onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
                });
                gsap.to(chev, { rotation: 0, duration: .2, ease: 'power1.out' });
            }
        }

        /* ── Grupo toggle ── */
        function toggleGrupo(gId, headerRow) {
            const rows  = document.querySelectorAll('.cea-student-row[data-parent="' + gId + '"]');
            const chev  = document.getElementById('chev-' + gId);
            const open  = headerRow.dataset.open !== '1';
            headerRow.dataset.open = open ? '1' : '0';

            if (typeof gsap !== 'undefined') {
                if (open) {
                    rows.forEach(r => { r.style.display = ''; });
                    gsap.fromTo(rows,
                        { opacity: 0, y: -6 },
                        { opacity: 1, y: 0, duration: .25, stagger: .04, ease: 'power2.out' }
                    );
                    gsap.to(chev, { rotation: 0, duration: .22, ease: 'power1.out' });
                } else {
                    gsap.to(rows, {
                        opacity: 0, y: -4, duration: .18, stagger: .02, ease: 'power1.in',
                        onComplete: () => rows.forEach(r => r.style.display = 'none')
                    });
                    gsap.to(chev, { rotation: -90, duration: .22, ease: 'power1.out' });
                }
            } else {
                rows.forEach(r => r.style.display = open ? '' : 'none');
                if (chev) chev.style.transform = open ? '' : 'rotate(-90deg)';
            }
        }

        /* ── Mini reproductores de audio ── */
        document.querySelectorAll('.cea-audio-mini-btn').forEach(btn => {
            const id    = btn.dataset.id;
            const audio = document.getElementById('audio-' + id);
            const icon  = document.getElementById('icon-audio-' + id);
            if (!audio) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (audio.paused) {
                    document.querySelectorAll('.cea-admin-audio').forEach(a => {
                        if (a === audio) return;
                        a.pause();
                        a.currentTime = 0;
                        const oid = a.id.replace('audio-', '');
                        const oi  = document.getElementById('icon-audio-' + oid);
                        const ob  = document.getElementById('btn-audio-'  + oid);
                        if (oi) oi.className = 'fas fa-play';
                        if (ob) ob.classList.remove('playing');
                    });
                    audio.play().catch(() => {});
                    if (icon) icon.className = 'fas fa-pause';
                    btn.classList.add('playing');
                } else {
                    audio.pause();
                    if (icon) icon.className = 'fas fa-play';
                    btn.classList.remove('playing');
                }
            });

            audio.addEventListener('ended', () => {
                if (icon) icon.className = 'fas fa-play';
                btn.classList.remove('playing');
                audio.currentTime = 0;
            });
        });

        /* Inicializar: todos los grupos abiertos al cargar */
        document.querySelectorAll('.cea-group-row').forEach(row => {
            row.dataset.open = '1';
        });

        (function() {
            if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            gsap.set('.cea-nivel', { opacity: 0, y: 20 });
            gsap.to('.cea-nivel', {
                opacity: 1, y: 0,
                duration: .5, stagger: .1, ease: 'power2.out', delay: .1
            });
        }());
    </script>
@endpush
