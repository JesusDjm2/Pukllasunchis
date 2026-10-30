@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Padrón de Estudiantes - IESP Ricardo Palma')

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
                    <h1>Padrón de Estudiantes</h1>
                    <p class="al-hero-sub">
                        Directorio y búsqueda de estudiantes registrados en las actas consolidadas históricas.
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

        {{-- Tarjeta Principal con Búsqueda y Tabla --}}
        <div class="al-card">
            <div class="al-card-cab">
                <div class="al-card-cab-icono">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h2>Padrón Histórico de Estudiantes</h2>
                    <p>Búsqueda por matrícula, documento nacional de identidad o nombres</p>
                </div>
                <div class="al-card-cab-extra">
                    <span class="al-badge is-info">
                        {{ $estudiantes->total() }} {{ $estudiantes->total() == 1 ? 'estudiante' : 'estudiantes' }}
                    </span>
                </div>
            </div>

            {{-- Buscador y Filtros --}}
            <form action="{{ route('rp.estudiantes.index') }}" method="GET" class="al-filtros">
                <input type="text" name="buscar" value="{{ $buscar }}" class="al-campo" style="flex: 2 1 280px;" placeholder="Buscar por N° Matrícula, DNI, Apellidos o Nombres...">

                <select name="programa_id" class="al-campo" style="flex: 1 1 220px;">
                    <option value="">-- Todas las Carreras --</option>
                    @foreach ($programas as $p)
                        <option value="{{ $p->id }}" {{ $programaId == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>

                <select name="periodo_id" class="al-campo" style="flex: 0 1 190px;" aria-label="Período">
                    <option value="">-- Todos los períodos --</option>
                    @foreach ($periodos as $per)
                        <option value="{{ $per->id }}" {{ $periodoId == $per->id ? 'selected' : '' }}>{{ $per->codigo }}</option>
                    @endforeach
                </select>

                <button type="submit" class="al-btn al-btn-petroleo">
                    <i class="fas fa-search"></i> Buscar
                </button>

                @if ($buscar || $programaId || $periodoId)
                    <a href="{{ route('rp.estudiantes.index') }}" class="al-btn al-btn-borde" title="Limpiar filtros">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                @endif
            </form>

            {{-- Tabla de Estudiantes --}}
            <div class="al-tabla-marco">
                <table class="al-tabla">
                    <thead>
                        <tr>
                            <th>N° Matrícula</th>
                            <th>DNI</th>
                            <th>Apellidos y Nombres</th>
                            <th>Carrera</th>
                            <th>Período</th>
                            <th>Teléfono</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $est)
                            <tr>
                                <td>
                                    <span class="font-weight-bold" style="color: var(--al-oro);">
                                        {{ $est->numero_matricula ?: '—' }}
                                    </span>
                                </td>
                                <td>{{ $est->dni ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('rp.estudiantes.show', $est) }}" class="font-weight-bold">
                                        {{ $est->apellidos }}, {{ $est->nombres }}
                                    </a>
                                    @if ($est->sexo)
                                        <span class="al-badge is-muted ml-1" style="font-size: .65rem;">{{ $est->sexo }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="al-suave">{{ $est->programa?->nombre ?: 'Sin carrera' }}</span>
                                </td>
                                <td>
                                    {{-- Año académico de cada acta en la que figura (y su semestre de estudios) --}}
                                    @forelse ($periodosPorEstudiante->get($est->id, collect()) as $p)
                                        <span class="al-badge is-info" title="Semestre {{ $p->numero_semestre }}">{{ $p->codigo }} · Sem. {{ $p->numero_semestre }}</span>
                                    @empty
                                        <span class="al-suave">—</span>
                                    @endforelse
                                </td>
                                <td>{{ $est->telefono ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="al-badge {{ $est->estado === 'RETIRADO' ? 'is-mal' : 'is-ok' }}">
                                        {{ $est->estado }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('rp.estudiantes.show', $est) }}" class="al-btn al-btn-sm al-btn-borde">
                                        <i class="fas fa-id-card"></i> Récord
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="al-vacio">
                                        <i class="fas fa-user-slash"></i>
                                        No se encontraron estudiantes registrados con los criterios seleccionados.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($estudiantes->hasPages())
                <div class="al-paginacion">
                    {{ $estudiantes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
