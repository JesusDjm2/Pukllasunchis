@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

    @include('admin._partials.prog-styles')
    <style>
        .pg-card-curso:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
            border-color: rgba(0, 0, 0, .12);
        }

        .pg-move-card {
            background: var(--pg-card);
            border: 1px solid var(--pg-border);
            border-radius: .85rem;
            padding: 1.25rem 1.5rem;
            box-shadow: var(--pg-shadow);
        }

        .pg-move-card .pg-label {
            margin-bottom: .4rem;
        }
    </style>

    @php
        if (! function_exists('badgeColorForCC')) {
            function badgeColorForCC($cc)
            {
                $key = Str::ascii(strtolower(trim($cc ?? '')));
                return match ($key) {
                    'formacion practica e investigacion' => '#FFD966',
                    'formacion especifica' => '#D9B8FF',
                    'electivo' => '#FFB266',
                    'extracurricular' => '#ff000087',
                    'formacion general' => '#4e73df',
                    default => '#EFEFEF',
                };
            }
        }
        $totalAlumnos = $alumnos->count() + (isset($alumnosB) ? $alumnosB->count() : 0);
    @endphp

    <div class="pg-wrap">
        <div class="container-fluid py-3">

            {{-- ── Hero ── --}}
            <div class="pg-hero" id="pg-hero">
                <div class="d-flex align-items-start justify-content-between flex-wrap"
                    style="gap:1rem; position:relative; z-index:1;">
                    <div>
                        <div class="pg-hero-label">
                            <i class="fas fa-university"></i> {{ $ciclo->programa->nombre }} · {{ preg_match('/^[IVX]+$/', trim($ciclo->nombre)) ? 'Ciclo '.trim($ciclo->nombre) : $ciclo->nombre }}
                        </div>
                        <p class="pg-hero-sub">
                            @if (!str_contains($ciclo->nombre, 'Egresados'))
                                <i class="fas fa-book mr-1"></i>
                                {{ $ciclo->cursos->count() }} {{ $ciclo->cursos->count() == 1 ? 'curso' : 'cursos' }}
                                &nbsp;&middot;&nbsp;
                            @endif
                            <i class="fas fa-users mr-1"></i>
                            {{ $totalAlumnos }} {{ $totalAlumnos == 1 ? 'alumno' : 'alumnos' }}
                        </p>
                    </div>
                    <a href="{{ route('ciclo.index') }}" class="pg-hero-btn align-self-start">
                        <i class="fas fa-arrow-left fa-xs"></i> Volver
                    </a>
                </div>
            </div>

            {{-- ── Alertas ── --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @if (str_contains($ciclo->nombre, 'Egresados'))
                {{-- ══ RAMA EGRESADOS ══════════════════════════════════════════ --}}

                <span id="pg-section-lbl"></span>
                @include('admin.ciclo._alumnos_mover', ['titulo' => 'Alumnos egresados'])
            @else
                {{-- ══ RAMA REGULAR ════════════════════════════════════════════ --}}
                @php $cursosOrdenados = $ciclo->cursos->sortBy('nombre'); @endphp

                {{-- ── Cursos ── --}}
                <div class="pg-section-lbl" id="pg-section-lbl">
                    <i class="fas fa-book"></i> Cursos del Ciclo
                </div>

                @if ($cursosOrdenados->isEmpty())
                    <div class="pg-empty mb-4">
                        <i class="fas fa-book-open fa-3x mb-3" style="opacity:.25;"></i>
                        <p class="font-weight-bold mb-1">Sin cursos asignados</p>
                        <small>Este ciclo aún no tiene cursos registrados.</small>
                    </div>
                @else
                    <div class="row mb-4" id="cursos-grid">
                        @foreach ($cursosOrdenados as $curso)
                            @php
                                $badgeBg = badgeColorForCC($curso->cc);
                            @endphp
                            @php
                                $puedeEditarEsteCurso = auth()->user()?->hasRole('super-admin') || strtolower(trim($curso->cc ?? '')) !== 'extracurricular';
                            @endphp
                            <div class="col-lg-4 col-md-6 mb-3 d-flex">
                                <div class="pg-card pg-card-curso w-100 d-flex flex-column">
                                    <div class="pg-card-top" style="background:{{ $badgeBg }};"></div>
                                    <div class="pg-card-body d-flex flex-column" style="flex:1;">
                                        <div style="flex:1;">
                                            <div class="pg-card-name mb-1">
                                                <a href="{{ route('curso.show', $curso->id) }}"
                                                    style="color:var(--pg-text);text-decoration:none;">{{ $curso->nombre }}</a>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center"
                                                style="gap:.6rem;font-size:.74rem;color:var(--pg-muted);margin-top:.3rem;">
                                                <span><i class="fas fa-shapes fa-xs mr-1"></i>{{ $curso->cc }}</span>
                                                @if ($curso->horas)
                                                    <span><i class="fas fa-clock fa-xs mr-1"></i>{{ $curso->horas }} h</span>
                                                @endif
                                                @if ($curso->creditos)
                                                    <span><i class="fas fa-star fa-xs mr-1"></i>{{ $curso->creditos }} cr.</span>
                                                @endif
                                            </div>
                                            @if ($curso->docentes->isNotEmpty())
                                                <div style="font-size:.76rem;color:var(--pg-muted);margin-top:.35rem;">
                                                    @foreach ($curso->docentes as $docente)
                                                        {{ $docente->nombre }}{{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mt-2 d-flex flex-wrap" style="gap:.4rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view flex-fill justify-content-center">
                                                <i class="fas fa-eye fa-xs"></i> Ver
                                            </a>
                                            @if ($puedeEditarEsteCurso)
                                                <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit flex-fill justify-content-center">
                                                    <i class="fas fa-pen fa-xs"></i> Editar
                                                </a>
                                            @endif
                                            <a href="{{ route('curso.docentes.form', $curso->id) }}" class="pg-btn pg-btn-cycle flex-fill justify-content-center">
                                                @if ($curso->docentes->isNotEmpty())
                                                    <i class="fas fa-user-edit fa-xs"></i> Editar profe
                                                @else
                                                    <i class="fas fa-user-plus fa-xs"></i> Asignar profe
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ── Alumnos ── --}}
                @include('admin.ciclo._alumnos_mover', ['titulo' => 'Alumnos del ciclo'])

            @endif

        </div>
    </div>

    {{-- ── GSAP ── --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });
            tl.fromTo('#pg-hero', {
                    opacity: 0,
                    y: -20,
                    scale: .98
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .5
                })
                .fromTo('#pg-section-lbl', {
                    opacity: 0,
                    x: -12
                }, {
                    opacity: 1,
                    x: 0,
                    duration: .28
                }, '-=.1');
            var cards = document.querySelectorAll('#cursos-grid .pg-card');
            if (cards.length) {
                tl.fromTo(cards, {
                    opacity: 0,
                    y: 24,
                    scale: .91
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .4,
                    stagger: {
                        amount: .5,
                        from: 'start'
                    },
                    ease: 'back.out(1.3)'
                }, '-=.1');
            }
        });
    </script>

@endsection
