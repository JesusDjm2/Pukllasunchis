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

    @forelse ($curso->niveles as $nivel)
        <div class="cea-nivel">
            <div class="cea-nivel-hdr">
                <span class="cea-nivel-num">{{ $loop->iteration }}</span>
                <span class="cea-nivel-name">{{ $nivel->nombre }}</span>
                <div class="cea-nivel-actions">
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

            <div class="cea-nivel-body">
                @forelse ($nivel->unidades as $unidad)
                    <div class="cea-unit">
                        <div class="cea-unit-hdr">
                            <i class="fas fa-folder-open cea-unit-icon"></i>
                            <div style="flex:1;min-width:0">
                                <div class="cea-unit-name">{{ $unidad->nombre }}</div>
                                @if ($unidad->descripcion)
                                    <div class="cea-unit-desc">{{ Str::limit($unidad->descripcion, 70) }}</div>
                                @endif
                            </div>
                            <div class="cea-unit-actions">
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

                        <div class="cea-content-list">
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
                                                pt{{ $ejercicio->puntaje_max != 1 ? 's' : '' }}</small>
                                        </div>
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
        (function() {
            if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            gsap.set('.cea-nivel', {
                opacity: 0,
                y: 20
            });
            gsap.to('.cea-nivel', {
                opacity: 1,
                y: 0,
                duration: .5,
                stagger: .1,
                ease: 'power2.out',
                delay: .1
            });
        }());
    </script>
@endpush
