@extends('layouts.admin')

@section('contenido')
<style>
    .curso-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.18s;
        user-select: none;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
    }
    .curso-card:hover { border-color: #4e73df; background: #f0f4ff; }
    .curso-card.selected { border-color: #1cc88a; background: #eafaf3; color: #0d6e4d; font-weight: 600; }
    .curso-card.selected .curso-check { color: #1cc88a; }
    .curso-card .curso-check { font-size: 1.1rem; min-width: 18px; }
    .ciclo-extra-header {
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6c757d;
        border-bottom: 1px solid #e3e6f0;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .resumen-bar {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 2px solid #e3e6f0;
        padding: 14px 0;
        z-index: 10;
    }
</style>

<div class="container-fluid bg-white pb-5">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pt-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-0 font-weight-bold text-dark">
                Asignar cursos &mdash;
                <span class="text-primary">{{ $alumno->apellidos }}, {{ $alumno->user->name }}</span>
            </h5>
            <small class="text-muted">
                {{ $alumno->programa->nombre ?? '—' }} &middot; {{ $alumno->ciclo->nombre ?? '—' }}
                @if($periodoActual)
                    &middot; <span class="badge badge-primary ml-1">Período: {{ $periodoActual->nombre }}</span>
                @else
                    &middot; <span class="badge badge-warning ml-1">Sin período activo</span>
                @endif
            </small>
        </div>
        <a href="{{ route('admin') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <form action="{{ route('guardar.cursos', $alumno->id) }}" method="POST" id="formAsignacion">
        @csrf
        @php
            $asignadosIds = $asignadosIds ?? [];
        @endphp

        {{-- SECCIÓN 1: Cursos del ciclo propio --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                <span class="font-weight-bold text-dark">
                    <i class="fas fa-book-open mr-1 text-primary"></i>
                    Cursos del ciclo {{ $alumno->ciclo->nombre ?? '' }}
                </span>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-success mr-1" onclick="selectGroup('ciclo', true)">
                        Marcar todos
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectGroup('ciclo', false)">
                        Desmarcar todos
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Selecciona los cursos del ciclo que el alumno llevará este período.
                    Por defecto, todos están marcados.
                </p>
                <div class="row" id="ciclo-grid">
                    @foreach($cursosCiclo as $curso)
                        @php $sel = in_array($curso->id, $asignadosIds) || in_array($curso->id, $cicloIdsDefecto); @endphp
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="curso-card w-100 {{ $sel ? 'selected' : '' }}"
                                   data-group="ciclo"
                                   onclick="toggleCard(this)">
                                <input type="checkbox" name="cursos[]" value="{{ $curso->id }}"
                                       class="d-none" {{ $sel ? 'checked' : '' }}>
                                <i class="fas {{ $sel ? 'fa-check-circle' : 'fa-circle' }} curso-check"></i>
                                <span>{{ $curso->nombre }}</span>
                                @if($curso->horas)
                                    <span class="ml-auto text-muted small">{{ $curso->horas }}h</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: Cursos adicionales (a cargo) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-2">
                <span class="font-weight-bold text-dark">
                    <i class="fas fa-plus-circle mr-1 text-warning"></i>
                    Cursos adicionales a cargo
                    <span class="badge badge-warning ml-1" id="badge-extras">0</span>
                </span>
                <p class="small text-muted mb-0 mt-1">
                    Cursos de otros ciclos del mismo programa que el alumno llevará adicionalmente.
                </p>
            </div>
            <div class="card-body p-0">
                @forelse($ciclosExtras as $ciclo)
                    @php
                        $cursosDelCiclo = $ciclo->cursos;
                        $haySeleccionados = $cursosDelCiclo->contains(fn($c) => in_array($c->id, $asignadosIds));
                    @endphp
                    <div class="border-bottom">
                        <button type="button"
                                class="btn btn-link w-100 text-left py-2 px-3 d-flex align-items-center justify-content-between ciclo-toggle"
                                data-target="ciclo-extra-{{ $ciclo->id }}">
                            <span class="font-weight-bold text-dark small">
                                <i class="fas fa-chevron-right mr-1 toggle-icon" style="font-size:0.7rem;"></i>
                                {{ $ciclo->nombre }}
                                @if($haySeleccionados)
                                    <span class="badge badge-success ml-1">con selección</span>
                                @endif
                            </span>
                            <span class="text-muted small">{{ $cursosDelCiclo->count() }} cursos</span>
                        </button>
                        <div id="ciclo-extra-{{ $ciclo->id }}"
                             class="px-3 pb-3 {{ $haySeleccionados ? '' : 'd-none' }}">
                            <div class="row">
                                @foreach($cursosDelCiclo as $curso)
                                    @php $sel = in_array($curso->id, $asignadosIds); @endphp
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <label class="curso-card w-100 {{ $sel ? 'selected' : '' }}"
                                               data-group="extras"
                                               onclick="toggleCard(this)">
                                            <input type="checkbox" name="cursos[]" value="{{ $curso->id }}"
                                                   class="d-none" {{ $sel ? 'checked' : '' }}>
                                            <i class="fas {{ $sel ? 'fa-check-circle' : 'fa-circle' }} curso-check"></i>
                                            <span>{{ $curso->nombre }}</span>
                                            @if($curso->horas)
                                                <span class="ml-auto text-muted small">{{ $curso->horas }}h</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small p-3 mb-0">No hay otros ciclos disponibles en este programa.</p>
                @endforelse
            </div>
        </div>

        {{-- Barra de resumen fija --}}
        <div class="resumen-bar">
            <div class="d-flex align-items-center justify-content-between px-1">
                <div>
                    <span class="font-weight-bold">Total seleccionado:</span>
                    <span class="badge badge-primary ml-1 px-2 py-1" id="total-count" style="font-size:1rem;">0</span>
                    <span class="text-muted small ml-2" id="horas-count"></span>
                </div>
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="fas fa-save mr-1"></i> Guardar asignación
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    function toggleCard(label) {
        const checkbox = label.querySelector('input[type=checkbox]');
        const icon = label.querySelector('.curso-check');
        checkbox.checked = !checkbox.checked;
        label.classList.toggle('selected', checkbox.checked);
        icon.classList.toggle('fa-check-circle', checkbox.checked);
        icon.classList.toggle('fa-circle', !checkbox.checked);
        actualizarContadores();
    }

    function selectGroup(group, state) {
        document.querySelectorAll(`[data-group="${group}"]`).forEach(label => {
            const checkbox = label.querySelector('input[type=checkbox]');
            const icon = label.querySelector('.curso-check');
            checkbox.checked = state;
            label.classList.toggle('selected', state);
            icon.classList.toggle('fa-check-circle', state);
            icon.classList.toggle('fa-circle', !state);
        });
        actualizarContadores();
    }

    function actualizarContadores() {
        const checked = document.querySelectorAll('input[name="cursos[]"]:checked');
        document.getElementById('total-count').textContent = checked.length;

        const extras = document.querySelectorAll('[data-group="extras"] input:checked').length;
        document.getElementById('badge-extras').textContent = extras;
    }

    // Accordion para ciclos extra
    document.querySelectorAll('.ciclo-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            const icon = this.querySelector('.toggle-icon');
            target.classList.toggle('d-none');
            icon.style.transform = target.classList.contains('d-none') ? '' : 'rotate(90deg)';
        });
    });

    // Estado inicial de iconos de accordion
    document.querySelectorAll('.ciclo-toggle').forEach(btn => {
        const target = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('.toggle-icon');
        if (target && !target.classList.contains('d-none')) {
            icon.style.transform = 'rotate(90deg)';
        }
    });

    // Contador inicial
    actualizarContadores();
</script>
@endsection
