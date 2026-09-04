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
                            <i class="fas fa-university"></i> {{ $ciclo->programa->nombre }} - Ciclo {{ $ciclo->nombre }}
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

            {{-- ── Stats ── --}}
            <div class="row mb-4">
                @if (!str_contains($ciclo->nombre, 'Egresados'))
                    <div class="col-sm-4 col-6 mb-3">
                        <div class="pg-stat">
                            <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                                <i class="fas fa-book" style="color:#2563eb;"></i>
                            </div>
                            <div>
                                <div class="pg-stat-val">{{ $ciclo->cursos->count() }}</div>
                                <div class="pg-stat-lbl">Cursos</div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                            <i class="fas fa-users" style="color:#16a34a;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $totalAlumnos }}</div>
                            <div class="pg-stat-lbl">Alumnos</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                            <i class="fas fa-layer-group" style="color:#d97706;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val" style="font-size:.82rem; word-break:break-word;">
                                {{ Str::limit($ciclo->programa->nombre, 16) }}</div>
                            <div class="pg-stat-lbl">Programa</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Alertas ── --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
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

                <div class="pg-section-lbl" id="pg-section-lbl">
                    <i class="fas fa-users"></i> Alumnos Egresados
                </div>

                <div
                    style="background:var(--pg-card);border:1px solid var(--pg-border);border-radius:.85rem;overflow:hidden;box-shadow:var(--pg-shadow);margin-bottom:1.5rem;">
                    <div class="table-responsive">
                        <table class="pg-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Programa</th>
                                    <th>Teléfono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</td>
                                        <td>{{ $ciclo->programa->nombre }}</td>
                                        <td>{{ $alumno->numero }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Formulario cambio de ciclo --}}
                <div class="pg-section-lbl" style="opacity:1;">
                    <i class="fas fa-exchange-alt"></i> Mover Alumnos de Ciclo
                </div>
                <div class="pg-move-card mb-4">
                    <form action="{{ route('ciclo.updateAlumnos') }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="ciclo_id" value="{{ $ciclo->id }}">

                        <div class="mb-3">
                            <label class="pg-label" for="nuevo_ciclo_egresados">Mover al ciclo:</label>
                            <select name="nuevo_ciclo_id" id="nuevo_ciclo_egresados" class="pg-select" required>
                                @foreach ($ciclosDisponibles as $cicloDisponible)
                                    @if ($cicloDisponible->id != $ciclo->id)
                                        <option value="{{ $cicloDisponible->id }}">{{ $cicloDisponible->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <p class="pg-label mb-2">Seleccionar alumnos para cambiar de ciclo:</p>
                            <div class="table-responsive"
                                style="border:1px solid var(--pg-border);border-radius:.6rem;overflow:hidden;">
                                <table class="pg-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Sel.</th>
                                            <th>Nombre y Apellido</th>
                                            <th>DNI</th>
                                            <th>Teléfono</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $alumno)
                                            @if ($alumno->user)
                                                @php $esLicencia = $alumno->user->perfil === 'Licencia'; @endphp
                                                <tr class="{{ $esLicencia ? 'pg-table-warn' : '' }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="text-center">
                                                        <input type="checkbox" name="alumnos[]"
                                                            value="{{ $alumno->user->id }}"
                                                            style="width:15px;height:15px;{{ $esLicencia ? 'cursor:not-allowed' : 'cursor:pointer' }}"
                                                            {{ $esLicencia ? 'disabled title="En licencia — no se puede cambiar de ciclo"' : '' }}>
                                                    </td>
                                                    <td>
                                                        {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        @if ($esLicencia)
                                                            <span class="badge badge-secondary ml-1">Licencia</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $alumno->dni }}</td>
                                                    <td>{{ $alumno->numero }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @foreach ($alumnosB as $alumno)
                                            @php
                                                $esInhabilitadoBEgr = $alumno->hasRole('inhabilitado');
                                                $motivoBEgr = $alumno->perfil ?? null;
                                            @endphp
                                            <tr class="{{ $esInhabilitadoBEgr ? 'pg-table-warn' : '' }}">
                                                <td></td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="alumnos[]" value="{{ $alumno->id }}"
                                                        style="width:15px;height:15px;{{ $esInhabilitadoBEgr ? 'cursor:not-allowed' : 'cursor:pointer' }}"
                                                        {{ $esInhabilitadoBEgr ? 'disabled title="Inhabilitado — no se puede cambiar de ciclo"' : '' }}>
                                                </td>
                                                <td>
                                                    {{ $alumno->apellidos }}, {{ $alumno->name }}
                                                    @if ($esInhabilitadoBEgr && $motivoBEgr)
                                                        <br><span class="badge badge-warning text-dark mt-1">⚠
                                                            {{ $motivoBEgr }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $alumno->dni }}</td>
                                                <td>{{ $alumno->telefono }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="pg-submit">
                            <i class="fas fa-exchange-alt mr-1"></i> Cambiar Ciclo
                        </button>
                    </form>
                </div>
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
                <div class="pg-section-lbl" style="opacity:1;">
                    <i class="fas fa-users"></i> Alumnos del Ciclo
                </div>
                <div class="pg-move-card mb-4">
                    <form action="{{ route('ciclo.updateAlumnos') }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="ciclo_id" value="{{ $ciclo->id }}">

                        <div class="mb-3">
                            <label class="pg-label" for="nuevo_ciclo">Mover al ciclo:</label>
                            <select name="nuevo_ciclo_id" id="nuevo_ciclo" class="pg-select" required>
                                @foreach ($ciclosDisponibles as $cicloDisponible)
                                    @if ($cicloDisponible->id != $ciclo->id)
                                        <option value="{{ $cicloDisponible->id }}">{{ $cicloDisponible->nombre }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <p class="pg-label mb-2">Seleccionar alumnos para cambiar de ciclo:</p>
                            <div class="table-responsive"
                                style="border:1px solid var(--pg-border);border-radius:.6rem;overflow:hidden;">
                                <table class="pg-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Sel.</th>
                                            <th>Nombre y Apellido</th>
                                            <th>Datos</th>
                                            <th>{{ isset($alumnosB) ? 'Matriculado' : 'Pendiente' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $alumno)
                                            @if ($alumno->user)
                                                @php
                                                    $esInhabilitado = $alumno->user->hasRole('inhabilitado');
                                                    $motivo = $alumno->user->perfil ?? null;
                                                @endphp
                                                <tr class="{{ $esInhabilitado ? 'pg-table-warn' : '' }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                            value="{{ $alumno->user->id }}"
                                                            id="alumno{{ $alumno->id }}"
                                                            style="width:15px;height:15px;{{ $esInhabilitado ? 'cursor:not-allowed' : 'cursor:pointer' }}"
                                                            {{ $esInhabilitado ? 'disabled title="Inhabilitado — no se puede cambiar de ciclo"' : '' }}>
                                                    </td>
                                                    <td>
                                                        <label class="form-check-label mb-0"
                                                            for="alumno{{ $alumno->id }}">
                                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        </label>
                                                        @if ($esInhabilitado && $motivo)
                                                            <br><span class="badge badge-warning text-dark mt-1">⚠
                                                                {{ $motivo }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="font-size:.78rem;line-height:1.6;">
                                                        <div>DNI: {{ $alumno->dni }}</div>
                                                        <div>Tel: {{ $alumno->numero }}</div>
                                                        <div>{{ $alumno->email }}</div>
                                                        <div>
                                                            @if ($alumno->fechaNacimientoResueltaFormateada() !== '')
                                                                {{ \Carbon\Carbon::parse($alumno->fechaNacimientoResuelta())->locale('es')->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}
                                                                &middot;
                                                                {{ $alumno->edad !== null ? $alumno->edad . ' años' : '—' }}
                                                            @else
                                                                Sin fecha
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $alumno->user->pendiente ?? '---' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @foreach ($alumnosB as $alumno)
                                            @php
                                                $esInhabilitadoB = $alumno->hasRole('inhabilitado');
                                                $motivoB = $alumno->perfil ?? null;
                                            @endphp
                                            <tr class="{{ $esInhabilitadoB ? 'pg-table-warn' : '' }}">
                                                <td></td>
                                                <td class="text-center">
                                                    <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                        value="{{ $alumno->id }}" id="alumnoB{{ $alumno->id }}"
                                                        style="width:15px;height:15px;{{ $esInhabilitadoB ? 'cursor:not-allowed' : 'cursor:pointer' }}"
                                                        {{ $esInhabilitadoB ? 'disabled title="Inhabilitado — no se puede cambiar de ciclo"' : '' }}>
                                                </td>
                                                <td>
                                                    <label class="form-check-label mb-0"
                                                        for="alumnoB{{ $alumno->id }}">
                                                        {{ $alumno->apellidos }}, {{ $alumno->name }}
                                                    </label>
                                                    <div style="font-size:.76rem;color:var(--pg-muted);">Tel:
                                                        {{ $alumno->telefono }}</div>
                                                    @if ($esInhabilitadoB && $motivoB)
                                                        <br><span class="badge badge-warning text-dark mt-1">⚠
                                                            {{ $motivoB }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $alumno->dni }}</td>
                                                <td>{{ $alumno->pendiente ?? '---' }}</td>
                                                <td class="text-center">
                                                    @if ($alumno->alumnoB)
                                                        ✔
                                                    @else
                                                        ✘
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="pg-submit">
                            <i class="fas fa-exchange-alt mr-1"></i> Cambiar Ciclo
                        </button>
                    </form>
                </div>

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
                .fromTo('.pg-stat', {
                    opacity: 0,
                    y: 18,
                    scale: .93
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .35,
                    stagger: .08
                }, '-=.18')
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
