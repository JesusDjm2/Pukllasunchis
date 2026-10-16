@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

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
    .extras-elegidos { padding: .6rem .75rem; border: 1px dashed #f6c23e; border-radius: 8px; background: #fffbeb; }
    .extras-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
    .extras-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .3rem .2rem .65rem; border-radius: 999px; background: #fff; border: 1px solid #f6c23e; font-size: .8rem; }
    .extras-chip small { color: #92400e; }
    .extras-chip button { border: 0; background: transparent; width: 20px; height: 20px; border-radius: 50%; line-height: 1; font-size: 1rem; color: #92400e; cursor: pointer; }
    .extras-chip button:hover { background: rgba(0, 0, 0, .08); }
    .dark-mode .extras-elegidos, .dim-mode .extras-elegidos { background: rgba(246, 194, 62, .08); }
    .dark-mode .extras-chip, .dim-mode .extras-chip { background: transparent; color: inherit; }
    .dark-mode .extras-chip small, .dim-mode .extras-chip small, .dark-mode .extras-chip button, .dim-mode .extras-chip button { color: #f6c23e; }
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

        {{-- SECCIÓN 2: Cursos adicionales — se elige el ciclo y se marcan sus cursos.
             Todos los ciclos quedan en la página (ocultos): lo marcado en uno se conserva
             al cambiar a otro, y el resumen de arriba muestra todo lo elegido. --}}
        @php
            $ciclosConCursos = $ciclosExtras
                ->filter(fn ($c) => $c->cursos->isNotEmpty())
                ->sortBy(fn ($c) => $c->ordenCiclo())
                ->values();
            $etiquetaCiclo = fn ($c) => preg_match('/^[IVX]+$/', trim($c->nombre)) ? 'Ciclo '.trim($c->nombre) : $c->nombre;
            $cicloInicial = $ciclosConCursos->first(fn ($c) => $c->cursos->contains(fn ($cu) => in_array($cu->id, $asignadosIds)));
        @endphp
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-2">
                <span class="font-weight-bold text-dark">
                    <i class="fas fa-plus-circle mr-1 text-warning"></i>
                    Cursos adicionales a cargo
                    <span class="badge badge-warning ml-1" id="badge-extras">0</span>
                </span>
                <p class="small text-muted mb-0 mt-1">
                    Cursos de otros ciclos de su programa ({{ $alumno->programa->nombre ?? '—' }}) que el alumno llevará además
                    — por ejemplo, uno desaprobado. Puede elegir varios, de uno o más ciclos. Valen solo para este período.
                </p>
            </div>
            <div class="card-body">
                @if ($ciclosConCursos->isEmpty())
                    <p class="text-muted small mb-0">No hay otros ciclos con cursos en este programa.</p>
                @else
                    <div class="extras-elegidos mb-3" id="extras-elegidos" aria-live="polite"></div>

                    <div class="form-group mb-3" style="max-width: 360px;">
                        <label for="extras-ciclo" class="small font-weight-bold mb-1">Ciclo</label>
                        <select id="extras-ciclo" class="form-control">
                            <option value="">Elegir un ciclo…</option>
                            @foreach ($ciclosConCursos as $ciclo)
                                <option value="{{ $ciclo->id }}" data-nombre="{{ $etiquetaCiclo($ciclo) }}" @selected($cicloInicial && $cicloInicial->id === $ciclo->id)>
                                    {{ $etiquetaCiclo($ciclo) }} ({{ $ciclo->cursos->count() }} cursos)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="small text-muted mb-2" id="extras-vacio">Elija un ciclo para ver sus cursos.</p>
                    @foreach ($ciclosConCursos as $ciclo)
                        <div class="extras-panel d-none" data-ciclo="{{ $ciclo->id }}">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap:.5rem;">
                                <span class="ciclo-extra-header mb-0 border-0 p-0">{{ $etiquetaCiclo($ciclo) }}</span>
                                <span>
                                    <button type="button" class="btn btn-xs btn-outline-success mr-1" onclick="selectCiclo({{ $ciclo->id }}, true)">Marcar todos</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectCiclo({{ $ciclo->id }}, false)">Desmarcar todos</button>
                                </span>
                            </div>
                            <div class="row">
                                @foreach ($ciclo->cursos as $curso)
                                    @php $sel = in_array($curso->id, $asignadosIds); @endphp
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <label class="curso-card w-100 {{ $sel ? 'selected' : '' }}"
                                               data-group="extras" data-ciclo="{{ $ciclo->id }}" data-nombre="{{ $curso->nombre }}"
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
                    @endforeach
                @endif
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
        pintarExtras();
    }

    // Cursos adicionales: el selector muestra un ciclo a la vez; lo marcado en los demás se conserva.
    const selCiclo = document.getElementById('extras-ciclo');
    function mostrarCiclo(id) {
        document.querySelectorAll('.extras-panel').forEach(p => p.classList.toggle('d-none', p.dataset.ciclo !== String(id)));
        const vacio = document.getElementById('extras-vacio');
        if (vacio) vacio.classList.toggle('d-none', !!id);
    }
    if (selCiclo) {
        selCiclo.addEventListener('change', () => mostrarCiclo(selCiclo.value));
        mostrarCiclo(selCiclo.value);
    }

    function selectCiclo(cicloId, state) {
        document.querySelectorAll(`[data-group="extras"][data-ciclo="${cicloId}"]`).forEach(label => {
            const checkbox = label.querySelector('input[type=checkbox]');
            const icon = label.querySelector('.curso-check');
            checkbox.checked = state;
            label.classList.toggle('selected', state);
            icon.classList.toggle('fa-check-circle', state);
            icon.classList.toggle('fa-circle', !state);
        });
        actualizarContadores();
    }

    // Resumen de los cursos adicionales elegidos (de todos los ciclos), con opción de quitar.
    function pintarExtras() {
        const caja = document.getElementById('extras-elegidos');
        if (!caja || !selCiclo) return;
        const nombreCiclo = {};
        selCiclo.querySelectorAll('option[value]').forEach(o => { if (o.value) nombreCiclo[o.value] = o.dataset.nombre; });
        const porCiclo = {};
        document.querySelectorAll('[data-group="extras"]').forEach(label => {
            if (!label.querySelector('input').checked) return;
            (porCiclo[label.dataset.ciclo] = porCiclo[label.dataset.ciclo] || []).push(label);
        });
        // El conteo de cada ciclo, en el propio selector.
        selCiclo.querySelectorAll('option[value]').forEach(o => {
            if (!o.value) return;
            const n = (porCiclo[o.value] || []).length;
            const total = document.querySelectorAll(`[data-group="extras"][data-ciclo="${o.value}"]`).length;
            o.textContent = nombreCiclo[o.value] + ' (' + total + ' cursos' + (n ? ' · ' + n + ' elegido' + (n > 1 ? 's' : '') : '') + ')';
        });
        const ids = Object.keys(porCiclo);
        if (!ids.length) {
            caja.innerHTML = '<span class="small text-muted"><i class="fas fa-info-circle mr-1"></i>Sin cursos adicionales: llevará solo los de su ciclo.</span>';
            return;
        }
        let h = '<div class="small font-weight-bold mb-1">Cursos adicionales elegidos</div><div class="extras-chips">';
        ids.forEach(id => porCiclo[id].forEach(label => {
            const cursoId = label.querySelector('input').value;
            h += '<span class="extras-chip">' + label.dataset.nombre.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]))
                + ' <small>· ' + nombreCiclo[id] + '</small>'
                + '<button type="button" data-quitar="' + cursoId + '" title="Quitar" aria-label="Quitar">&times;</button></span>';
        }));
        caja.innerHTML = h + '</div>';
    }
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-quitar]');
        if (!b) return;
        const input = document.querySelector('[data-group="extras"] input[value="' + b.dataset.quitar + '"]');
        if (input && input.checked) toggleCard(input.closest('label'));
    });

    // Contador inicial
    actualizarContadores();
</script>
@endsection
