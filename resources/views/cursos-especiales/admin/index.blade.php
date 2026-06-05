@extends('layouts.superadmin')
@section('titulo', 'Cursos Asincrónicos')

@push('styles')
    <style>
        .ce-admin-page {
            padding-bottom: 2rem;
        }

        /* ── Page header ── */
        .cea-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.75rem 0 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 2rem;
        }

        .cea-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #2d3561;
            margin: 0;
        }

        .cea-title small {
            font-size: .75rem;
            font-weight: 500;
            color: #888;
            display: block;
            margin-top: .1rem;
        }

        .cea-btn-new {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff;
            border: none;
            border-radius: .6rem;
            padding: .6rem 1.3rem;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 12px rgba(78, 115, 223, .35);
        }

        .cea-btn-new:hover {
            opacity: .88;
            transform: translateY(-1px);
            color: #fff;
            text-decoration: none;
        }

        /* ── Empty state ── */
        .cea-empty {
            text-align: center;
            padding: 5rem 2rem;
            color: #aaa;
        }

        .cea-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* ── Course cards grid ── */
        .cea-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* ── Course card ── */
        .cea-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease, box-shadow .25s ease;
            border: 1px solid #f0f0f0;
        }

        .cea-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .12);
        }

        /* ── Card banner ── */
        .cea-card-banner {
            height: 140px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #4e73df, #224abe);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cea-card-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            inset: 0;
        }

        .cea-card-banner-icon {
            font-size: 3rem;
            color: rgba(255, 255, 255, .7);
            position: relative;
            z-index: 1;
        }

        .cea-card-status {
            position: absolute;
            top: .7rem;
            left: .7rem;
            font-size: .7rem;
            font-weight: 700;
            padding: .2rem .65rem;
            border-radius: 99px;
            z-index: 2;
            backdrop-filter: blur(4px);
        }

        .cea-card-status.activo {
            background: rgba(28, 200, 138, .85);
            color: #fff;
        }

        .cea-card-status.inactivo {
            background: rgba(108, 117, 125, .8);
            color: #fff;
        }

        .cea-card-orden {
            position: absolute;
            top: .7rem;
            right: .7rem;
            background: rgba(0, 0, 0, .4);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            padding: .2rem .55rem;
            border-radius: 99px;
            z-index: 2;
        }

        /* ── Card body ── */
        .cea-card-body {
            padding: 1.25rem 1.25rem .75rem;
            flex: 1;
        }

        .cea-card-title {
            font-size: 1rem;
            font-weight: 800;
            color: #2d3561;
            margin: 0 0 .4rem;
            line-height: 1.3;
        }

        .cea-card-desc {
            font-size: .82rem;
            color: #888;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ── Card chips ── */
        .cea-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            padding: .75rem 1.25rem;
            border-top: 1px solid #f5f5f5;
        }

        .cea-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: .4rem;
            background: #f0f4ff;
            color: #4e73df;
        }

        .cea-chip.green {
            background: #e0fbf4;
            color: #1cc88a;
        }

        .cea-chip.amber {
            background: #fff8e0;
            color: #f6c23e;
        }

        /* ── Card actions ── */
        .cea-card-actions {
            display: flex;
            gap: .5rem;
            padding: .75rem 1.25rem;
            border-top: 1px solid #f5f5f5;
            background: #fafbff;
        }

        .cea-act-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .85rem;
            border-radius: .5rem;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity .15s, transform .12s;
            flex: 1;
            justify-content: center;
        }

        .cea-act-btn:hover {
            opacity: .85;
            transform: scale(1.03);
            text-decoration: none;
        }

        .cea-act-btn.view {
            background: #e8f4fd;
            color: #36b9cc;
        }

        .cea-act-btn.edit {
            background: #fff3cd;
            color: #f6c23e;
        }

        .cea-act-btn.delete {
            background: #fde8e8;
            color: #e74a3b;
        }

        @media (max-width: 576px) {
            .cea-grid {
                grid-template-columns: 1fr;
            }

            .cea-header {
                padding: 1.25rem 0 1rem;
            }
        }
    </style>
@endpush

@section('contenido')
    <div class="container-fluid ce-admin-page">
        <div class="cea-header">
            <div>
                <h1 class="cea-title">
                    <i class="fas fa-graduation-cap mr-2" style="color:#4e73df"></i>Cursos Especiales Asincrónicos
                    <small>Gestión de contenido — solo super admin</small>
                </h1>
            </div>
            <a href="{{ route('ce.cursos.create') }}" class="cea-btn-new">
                <i class="fas fa-plus-circle"></i> Nuevo Curso
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if ($cursos->isEmpty())
            <div class="cea-empty">
                <i class="fas fa-book-open d-block"></i>
                <p class="mb-3">No hay cursos creados aún.</p>
                <a href="{{ route('ce.cursos.create') }}" class="cea-btn-new" style="display:inline-flex">
                    <i class="fas fa-plus-circle"></i> Crear el primer curso
                </a>
            </div>
        @else
            <div class="cea-grid" id="cea-grid">
                @foreach ($cursos as $curso)
                    @php
                        $totalNiveles = $curso->niveles->count();
                        $totalUnidades = $curso->niveles->sum(fn($n) => $n->unidades->count());
                        $gradientes = [
                            'linear-gradient(135deg,#4e73df,#224abe)',
                            'linear-gradient(135deg,#1cc88a,#13855c)',
                            'linear-gradient(135deg,#36b9cc,#258391)',
                            'linear-gradient(135deg,#f6c23e,#dda20a)',
                        ];
                        $grad = $gradientes[$loop->index % count($gradientes)];
                    @endphp
                    <div class="cea-card">
                        <div class="cea-card-banner" style="background:{{ $grad }}">
                            @if ($curso->imagen)
                                <img src="{{ $curso->imagen_url }}" alt="{{ $curso->nombre }}">
                            @else
                                <i class="fas fa-graduation-cap cea-card-banner-icon"></i>
                            @endif
                            <span class="cea-card-status {{ $curso->activo ? 'activo' : 'inactivo' }}">
                                <i class="fas fa-circle fa-xs mr-1"></i>{{ $curso->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span class="cea-card-orden">#{{ $curso->orden }}</span>
                        </div>

                        <div class="cea-card-body">
                            <h3 class="cea-card-title">{{ $curso->nombre }}</h3>
                            @if ($curso->descripcion)
                                <p class="cea-card-desc">{{ $curso->descripcion }}</p>
                            @endif
                        </div>

                        <div class="cea-chips">
                            <span class="cea-chip"><i class="fas fa-layer-group"></i> {{ $totalNiveles }}
                                nivel{{ $totalNiveles != 1 ? 'es' : '' }}</span>
                            <span class="cea-chip green"><i class="fas fa-folder-open"></i> {{ $totalUnidades }}
                                unidad{{ $totalUnidades != 1 ? 'es' : '' }}</span>
                            @if (isset($curso->inscripciones_count))
                                <span class="cea-chip amber"><i class="fas fa-users"></i> {{ $curso->inscripciones_count }}
                                    inscrito{{ $curso->inscripciones_count != 1 ? 's' : '' }}</span>
                            @endif
                            @if (isset($curso->promedio_avance))
                                <span class="cea-chip green"><i class="fas fa-chart-line"></i>
                                    {{ $curso->promedio_avance }}% progreso</span>
                            @endif
                        </div>

                        <div class="cea-card-actions">
                            <a href="{{ route('ce.cursos.show', $curso) }}" class="cea-act-btn view">
                                <i class="fas fa-sitemap"></i> Estructura
                            </a>
                            <a href="{{ route('ce.cursos.edit', $curso) }}" class="cea-act-btn edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('ce.cursos.destroy', $curso) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar «{{ $curso->nombre }}» y todo su contenido? Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="cea-act-btn delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        (function() {
            if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            gsap.set('.cea-card', {
                opacity: 0,
                y: 30
            });
            gsap.to('.cea-card', {
                opacity: 1,
                y: 0,
                duration: .55,
                stagger: .1,
                ease: 'power3.out',
                delay: .1
            });
        }());
    </script>
@endpush
