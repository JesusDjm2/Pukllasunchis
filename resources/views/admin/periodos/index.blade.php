@extends('layouts.admin')
@section('contenido')
    <style>
        .logo-rotando {
            animation: giro 8s linear infinite;
        }

        @keyframes giro {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        table thead th {
            pointer-events: none;
        }
    </style>
    <div class="container-fluid bg-white pt-2">
        {{-- ENCABEZADO + BOTONES --}}
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-gradient-primary p-3 rounded-circle shadow-sm mr-2">
                    <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="color: #2c3e50;">
                        <span class="bg-gradient-to-right">Periodos Académicos</span>
                    </h3>
                    <p class="text-muted small mb-0">Gestión y visualización de períodos</p>
                </div>
            </div>
            <div>
                <button class="btn btn-primary btn-sm px-4 shadow-sm" onclick="mostrarTabla('tablaActual')">
                    <i class="fas fa-clock me-1"></i> Periodos FID
                </button>
                <button class="btn btn-info btn-sm px-4 shadow-sm" onclick="mostrarTabla('tablaPPD')">
                    <i class="fas fa-history me-1"></i> Periodos PPD
                </button>
            </div>
            <img src="{{ asset('img/Icono-Puklla.png') }}" width="50px" class="logo-rotando">
        </div>
        {{-- ALERTAS --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Éxito:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        {{-- TABLA PERIODO ACTUAL --}}
        <div id="tablaActual" class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-primary">
                        Periodos FID
                        <span class="badge bg-primary text-white" style="font-size: 12px">
                            {{ count($periodoactuales) }} registros
                        </span>
                    </h5>
                </div>
                <a href="{{ route('periodoactual.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Nuevo periodo FID
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th>Nombre</th>
                                <th>Horario</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periodoactuales as $key => $p)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">
                                        {{ $key + 1 }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->nombre }}</div>
                                        <small class="text-muted">ID: {{ $p->id }}</small>
                                    </td>
                                    <td>
                                        @if ($p->horario)
                                            <a href="{{ asset($p->horario) }}" target="_blank"
                                                class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-pdf me-1"></i>
                                                Ver horario
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Sin archivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->fecha_inicio)
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($p->fecha_inicio)->translatedFormat('d M. Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->fecha_cierre)
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($p->fecha_cierre)->translatedFormat('d M. Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($p->actual)
                                            <span class="badge bg-success text-white">
                                                <i class="fas fa-circle me-1 small"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white">
                                                <i class="fas fa-circle me-1 small"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1 pe-4">
                                            <!-- Botones editar/eliminar -->
                                            <div class="btn-group btn-group-sm me-1">
                                                <a href="{{ route('periodoactual.edit', $p) }}"
                                                    class="btn btn-warning mr-2" data-bs-toggle="tooltip" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i> Editar
                                                </a>
                                            </div>

                                            <!-- Acciones de calificaciones -->
                                            @if ($p->actual)
                                                @if ($p->periodos->isEmpty())
                                                    <form action="{{ route('periodoactual.crearCalificaciones', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success mr-2"
                                                            data-bs-toggle="tooltip" title="Crear calificaciones">
                                                            <i class="fas fa-book me-1"></i>
                                                            Crear
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="btn-group btn-group-sm">
                                                        <form
                                                            action="{{ route('periodoactual.crearCalificaciones', $p->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-info mr-2"
                                                                data-bs-toggle="tooltip" title="Sincronizar">
                                                                <i class="fas fa-sync-alt"></i> Sincronizar
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('periodoactual.showRegistros', $p->id) }}"
                                                            class="btn btn-info mr-2" data-bs-toggle="tooltip"
                                                            title="Ver registros">
                                                            <i class="fas fa-list"></i> Ver
                                                        </a>
                                                    </div>
                                                @endif
                                            @else
                                                @if (!$p->periodos->isEmpty())
                                                    <a href="{{ route('periodoactual.showRegistros', $p->id) }}"
                                                        class="btn btn-sm btn-info mr-2" data-bs-toggle="tooltip"
                                                        title="Ver registros">
                                                        <i class="fas fa-list me-1"></i>
                                                        Ver
                                                    </a>
                                                @endif
                                            @endif
                                            <form action="{{ route('periodoactual.destroy', $p) }}" method="POST"
                                                class="d-inline mr-2">
                                                @csrf @method('DELETE')
                                                <button type="submit" onclick="return confirm('¿Eliminar este período?')"
                                                    class="btn btn-danger" data-bs-toggle="tooltip" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                            <h6 class="fw-normal">No hay períodos registrados</h6>
                                            <a href="{{ route('periodoactual.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="fas fa-plus me-1"></i>
                                                Crear primer período
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLA PERIODO PPD --}}
        <div id="tablaPPD" class="card shadow-sm" style="display:none;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-info">
                        Periodos PPD
                        <small class="badge bg-info text-white" style="font-size: 12px">
                            {{ count($periodosppd) }} registros
                        </small>
                    </h5>
                </div>
                <a href="{{ route('periodos.admin.ppd.create') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Nuevo periodo PPD
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th>Nombre</th>
                                <th>Calendario</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="text-center">Estado</th>
                                <th class="pe-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periodosppd as $key => $p)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">
                                        {{ $key + 1 }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->nombre }}</div>
                                        <small class="text-muted">ID: {{ $p->id }}</small>
                                    </td>
                                    <td>
                                        @if ($p->calendario)
                                            <a href="{{ asset($p->calendario) }}" target="_blank"
                                                class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Ver calendario
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Sin archivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->fecha_inicio)
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($p->fecha_inicio)->translatedFormat('d M. Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->fecha_cierre)
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($p->fecha_cierre)->translatedFormat('d M. Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($p->actual)
                                            <span class="badge bg-success text-white">
                                                <i class="fas fa-circle me-1 small"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white">
                                                <i class="fas fa-circle me-1 small"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1 pe-4">
                                            <a href="{{ route('periodos.admin.ppd.edit', $p) }}"
                                                class="btn btn-sm btn-warning me-1" data-bs-toggle="tooltip"
                                                title="Editar">
                                                <i class="fas fa-pencil-alt me-1"></i>
                                                Editar
                                            </a>
                                            @if ($p->periodosPpd->isEmpty())
                                                <form
                                                    action="{{ route('periodos.admin.ppd.crearCalificaciones', $p->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success me-1"
                                                        data-bs-toggle="tooltip" title="Crear calificaciones">
                                                        <i class="fas fa-book me-1"></i>
                                                        Crear
                                                    </button>
                                                </form>
                                            @else
                                                <div class="btn-group btn-group-sm me-1">
                                                    <form
                                                        action="{{ route('periodos.admin.ppd.sincronizarCalificaciones', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="tooltip" title="Sincronizar calificaciones">
                                                            <i class="fas fa-sync-alt me-1"></i>
                                                            Sinc.
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('periodos.admin.ppd.show', $p->id) }}"
                                                        class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                                                        title="Ver registros">
                                                        <i class="fas fa-list me-1"></i>
                                                        Ver
                                                    </a>
                                                </div>
                                            @endif
                                            <form action="{{ route('periodos.admin.ppd.destroy', $p) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" onclick="return confirm('¿Eliminar este período?')"
                                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>                                    
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-clock fa-3x mb-3"></i>
                                            <h6 class="fw-normal">No hay períodos PPD registrados</h6>
                                            <a href="{{ route('periodos.admin.ppd.create') }}"
                                                class="btn btn-info btn-sm mt-2">
                                                <i class="fas fa-plus me-1"></i>
                                                Crear primer período
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function mostrarTabla(id) {
            document.getElementById('tablaActual').style.display = 'none';
            document.getElementById('tablaPPD').style.display = 'none';
            document.getElementById(id).style.display = 'block';
        }
    </script>
@endsection
