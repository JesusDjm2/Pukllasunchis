{{-- Filtros de grupos Taq'e (Investigador y Super Admin).
     Espera: $ruta (name de la ruta índice), $periodos, $programas, $proyectos, $ciclos, $cursos, $total. --}}
<form method="GET" action="{{ route($ruta) }}" class="al-card tq-filtros">
    <div class="tq-filtro">
        <label>Periodo</label>
        <select name="periodo_actual_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach ($periodos as $p)
                <option value="{{ $p->id }}" {{ request('periodo_actual_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="tq-filtro">
        <label>Programa</label>
        <select name="programa_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach ($programas as $p)
                <option value="{{ $p->id }}" {{ request('programa_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="tq-filtro">
        <label>Ciclo</label>
        <select name="ciclo_id" class="form-control form-control-sm" onchange="this.form.submit()" {{ $ciclos->isEmpty() ? 'disabled' : '' }}>
            <option value="">Todos</option>
            @foreach ($ciclos as $c)
                <option value="{{ $c->id }}" {{ request('ciclo_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="tq-filtro">
        <label>Curso</label>
        <select name="curso_id" class="form-control form-control-sm" onchange="this.form.submit()" {{ $cursos->isEmpty() ? 'disabled' : '' }}>
            <option value="">Todos</option>
            @foreach ($cursos as $c)
                <option value="{{ $c->id }}" {{ request('curso_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="tq-filtro">
        <label>Proyecto Integrador</label>
        <select name="proyecto_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach ($proyectos ?? [] as $p)
                <option value="{{ $p->id }}" {{ request('proyecto_id') == $p->id ? 'selected' : '' }} title="{{ $p->producto }}">{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="tq-filtros-fin">
        @if (request()->anyFilled(['periodo_actual_id', 'programa_id', 'ciclo_id', 'curso_id', 'proyecto_id']))
            <a href="{{ route($ruta) }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-times"></i> Limpiar</a>
        @endif
        <span class="al-badge is-muted">{{ $total }} {{ $total === 1 ? 'grupo' : 'grupos' }}</span>
    </div>
</form>
