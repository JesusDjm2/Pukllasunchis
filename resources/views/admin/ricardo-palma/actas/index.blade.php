@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Actas Consolidadas - IESP Ricardo Palma')

@section('contenido')
    @include('alumnos.partials.ui')
    @include('partials.flash-swal')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.dashboard') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Dashboard Ricardo Palma
                        </a>
                    </div>
                    <h1>Actas Consolidadas de Evaluación</h1>
                    <p class="al-hero-sub">
                        Repositorio de actas oficiales de formación docente (1995 - 2015) organizadas por periodo y carrera.
                    </p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.importar.index') }}" class="al-btn al-btn-oro">
                        <i class="fas fa-file-excel"></i> Carga Masiva
                    </a>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Tarjeta Principal con Filtros y Listado --}}
        <div class="al-card">
            <div class="al-card-cab">
                <div class="al-card-cab-icono">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h2>Registro de Actas Históricas</h2>
                    <p>Consulta por periodo académico, programa o semestre</p>
                </div>
                <div class="al-card-cab-extra">
                    <span class="al-badge is-info">
                        {{ $actas->total() }} {{ $actas->total() == 1 ? 'acta registrada' : 'actas registradas' }}
                    </span>
                </div>
            </div>

            {{-- Barra de Filtros --}}
            <form action="{{ route('rp.actas.index') }}" method="GET" class="al-filtros">
                <select name="periodo_id" class="al-campo" style="flex: 1 1 180px;">
                    <option value="">-- Todos los Periodos --</option>
                    @foreach ($periodos as $per)
                        <option value="{{ $per->id }}" {{ $periodoId == $per->id ? 'selected' : '' }}>{{ $per->codigo }}</option>
                    @endforeach
                </select>

                <select name="programa_id" class="al-campo" style="flex: 2 1 240px;">
                    <option value="">-- Todas las Carreras --</option>
                    @foreach ($programas as $p)
                        <option value="{{ $p->id }}" {{ $programaId == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>

                <select name="semestre" class="al-campo" style="flex: 1 1 140px;">
                    <option value="">-- Semestre --</option>
                    @for ($s = 1; $s <= 10; $s++)
                        <option value="{{ $s }}" {{ $semestre == $s ? 'selected' : '' }}>Semestre {{ $s }}</option>
                    @endfor
                </select>

                <button type="submit" class="al-btn al-btn-petroleo">
                    <i class="fas fa-filter"></i> Filtrar
                </button>

                @if ($periodoId || $programaId || $semestre)
                    <a href="{{ route('rp.actas.index') }}" class="al-btn al-btn-borde" title="Limpiar filtros">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                @endif
            </form>

            {{-- Tabla de Actas --}}
            <div class="al-tabla-marco">
                <table class="al-tabla">
                    <thead>
                        <tr>
                            <th>Acta Oficial</th>
                            <th>Periodo</th>
                            <th>Carrera / Especialidad</th>
                            <th class="text-center">Semestre</th>
                            <th class="text-center">Sección / Turno</th>
                            <th class="text-center">Calificaciones</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actas as $acta)
                            <tr>
                                <td>
                                    <a href="{{ route('rp.actas.show', $acta) }}" class="font-weight-bold">
                                        <i class="fas fa-file-invoice mr-1" style="color: var(--al-oro);"></i>
                                        {{ $acta->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta->id }}
                                    </a>
                                </td>
                                <td>
                                    <span class="al-badge is-info font-weight-bold">{{ $acta->periodo?->codigo }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $acta->programa?->nombre }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="al-badge is-muted">Semestre {{ $acta->numero_semestre }}</span>
                                </td>
                                <td class="text-center al-suave">
                                    {{ $acta->seccion }} · {{ $acta->turno }}
                                </td>
                                <td class="text-center">
                                    <span class="al-badge is-ok">
                                        <i class="fas fa-check-circle"></i> {{ $acta->calificaciones_count }} notas
                                    </span>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="{{ route('rp.actas.excel', $acta) }}" class="al-btn al-btn-sm al-btn-borde mr-1" title="Descargar Excel">
                                        <i class="fas fa-file-excel text-success"></i>
                                    </a>
                                    <a href="{{ route('rp.actas.imprimir', $acta) }}" target="_blank" class="al-btn al-btn-sm al-btn-borde mr-1" title="Vista de Impresión Oficial">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('rp.actas.show', $acta) }}" class="al-btn al-btn-sm al-btn-borde">
                                        <i class="fas fa-table"></i> Ver Matriz
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="al-vacio">
                                        <i class="fas fa-folder-open"></i>
                                        No se encontraron actas con los criterios seleccionados.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($actas->hasPages())
                <div class="al-paginacion">
                    {{ $actas->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
