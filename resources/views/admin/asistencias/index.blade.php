@php
    $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';
@endphp

@extends($layout)

@section('titulo', 'Control de Asistencias — Dirección y Coordinación')

@push('styles')
<style>
    .badge-amantani {
        background-color: #0d9488 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
        font-size: 0.72rem;
    }
    .badge-pronabec {
        background-color: #0284c7 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
        font-size: 0.72rem;
    }
    .badge-licencia {
        background-color: #f59e0b !important;
        color: #1e293b !important;
        font-weight: 800;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
        font-size: 0.72rem;
    }
    .card-kpi {
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.5rem rgba(58, 59, 69, 0.1);
        border: 0;
    }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid px-3 py-3">

    {{-- Cabecera --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                <i class="fas fa-clipboard-check text-primary mr-2"></i> Control Institucional de Asistencias
            </h1>
            <p class="text-muted small mb-0 mt-1">
                Monitoreo en tiempo real para Dirección, Coordinación Académica y Tutoría · Periodo {{ $periodoActual->nombre ?? 'Actual' }}
            </p>
        </div>
    </div>

    {{-- Tarjetas KPI --}}
    <div class="row mb-4">
        {{-- Total Sesiones --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-kpi border-left-primary h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Sesiones Registradas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSesiones }}</div>
                            <small class="text-muted">En este periodo académico</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asistencia Global --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-kpi border-left-success h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Asistencia Promedio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $porcentajeGlobal }}%</div>
                            <small class="text-muted">Presentes + tardanzas</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asistencia Becarios (Amantani y Pronabec) --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-kpi border-left-info h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Seguimiento Becarios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $porcentajeBecas }}%</div>
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                <span class="badge badge-amantani" title="Registros Beca Amantani">Amantani: {{ $detallesAmantani->count() }}</span>
                                <span class="badge badge-pronabec" title="Registros PRONABEC">Pronabec: {{ $detallesPronabec->count() }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estudiantes en Licencia / Faltas --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-kpi border-left-warning h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Licencias & Faltas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalLicencias }} / {{ $totalFaltas }}</div>
                            <small class="text-muted">{{ $totalLicencias }} con Licencia · {{ $totalFaltas }} faltas</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros Avanzados --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-1"></i> Filtros de Búsqueda
            </h6>
            @if (request()->hasAny(['programa_id', 'ciclo_id', 'docente_id', 'fecha_inicio', 'fecha_fin', 'filtro_especial']))
                <a href="{{ route('admin.asistencias.index') }}" class="btn btn-sm btn-link text-danger p-0">
                    <i class="fas fa-times-circle mr-1"></i> Limpiar filtros
                </a>
            @endif
        </div>
        <div class="card-body py-3">
            <form action="{{ route('admin.asistencias.index') }}" method="GET">
                <div class="row g-2">
                    {{-- Programa --}}
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Programa:</label>
                        <select name="programa_id" class="form-control form-control-sm">
                            <option value="">Todos los programas</option>
                            @foreach ($programas as $p)
                                <option value="{{ $p->id }}" {{ $programaId == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ciclo --}}
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Ciclo:</label>
                        <select name="ciclo_id" class="form-control form-control-sm">
                            <option value="">Todos los ciclos</option>
                            @foreach ($ciclos as $c)
                                <option value="{{ $c->id }}" {{ $cicloId == $c->id ? 'selected' : '' }}>
                                    Ciclo {{ $c->nombre }} ({{ $c->programa?->nombre ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Docente --}}
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Docente:</label>
                        <select name="docente_id" class="form-control form-control-sm">
                            <option value="">Todos los docentes</option>
                            @foreach ($docentes as $d)
                                <option value="{{ $d->id }}" {{ $docenteId == $d->id ? 'selected' : '' }}>
                                    {{ $d->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro Especial (Becas / Licencias / Alerta) --}}
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Condición especial:</label>
                        <select name="filtro_especial" class="form-control form-control-sm font-weight-bold text-primary">
                            <option value="todas" {{ $filtroEspecial === 'todas' ? 'selected' : '' }}>Todas las sesiones</option>
                            <option value="amantani" {{ $filtroEspecial === 'amantani' ? 'selected' : '' }}>★ Beca Amantani</option>
                            <option value="pronabec" {{ $filtroEspecial === 'pronabec' ? 'selected' : '' }}>★ PRONABEC / Beca 18</option>
                            <option value="beca" {{ $filtroEspecial === 'beca' ? 'selected' : '' }}>Cualquier Becario</option>
                            <option value="licencia" {{ $filtroEspecial === 'licencia' ? 'selected' : '' }}>En Licencia</option>
                            <option value="alerta" {{ $filtroEspecial === 'alerta' ? 'selected' : '' }}>Con Faltas / En Alerta</option>
                        </select>
                    </div>

                    {{-- Botón Filtrar --}}
                    <div class="col-md-2 col-sm-12 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Sesiones Registradas --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-alt mr-1"></i> Registros de Asistencia Tomada ({{ $sesiones->total() }})
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 120px;">Fecha</th>
                        <th>Curso & Ciclo</th>
                        <th>Docente</th>
                        <th>Tema de la Clase</th>
                        <th class="text-center" style="width: 190px;">Asistencia</th>
                        <th class="text-center" style="width: 110px;">% Asis.</th>
                        <th class="text-center" style="width: 150px;">Beca / Licencia</th>
                        <th class="text-right" style="width: 90px;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sesiones as $s)
                        @php
                            $pct = $s->porcentaje_asistencia;
                            $colorPct = $pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger');
                            $tieneAmantani = $s->detalles->contains(fn($d) => stripos($d->tipo_beca ?? '', 'amantani') !== false);
                            $tienePronabec = $s->detalles->contains(fn($d) => stripos($d->tipo_beca ?? '', 'pronabec') !== false || stripos($d->tipo_beca ?? '', 'beca 18') !== false);
                            $tieneLicencia = $s->detalles->contains(fn($d) => $d->en_licencia || $d->estado === 'licencia');
                        @endphp
                        <tr>
                            <td>
                                <span class="font-weight-bold text-dark d-block">
                                    {{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}
                                </span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($s->fecha)->isoFormat('dddd') }}</small>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $s->curso?->nombre ?? 'Curso no disponible' }}</div>
                                <small class="text-muted">
                                    Ciclo {{ $s->curso?->ciclo?->nombre ?? '—' }} · {{ $s->curso?->ciclo?->programa?->nombre ?? 'FID' }}
                                </small>
                            </td>
                            <td>
                                <div class="font-weight-bold text-secondary" style="font-size: 0.88rem;">
                                    <i class="fas fa-user-tie fa-xs mr-1 text-muted"></i> {{ $s->docente?->nombre ?? '—' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 260px;" title="{{ $s->tema }}">
                                    {{ $s->tema ?: '—' }}
                                </div>
                                @if ($s->observaciones)
                                    <small class="text-muted d-block text-truncate" style="max-width: 260px;" title="{{ $s->observaciones }}">
                                        {{ $s->observaciones }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                    <span class="stat-pill bg-success text-white" title="Presentes">P: {{ $s->presentes_count }}</span>
                                    <span class="stat-pill bg-warning text-dark" title="Tardanzas">T: {{ $s->tardanzas_count }}</span>
                                    <span class="stat-pill bg-danger text-white" title="Faltas">F: {{ $s->faltas_count }}</span>
                                    <span class="stat-pill text-white" style="background:#7c3aed;" title="Licencias">L: {{ $s->licencias_count }}</span>
                                </div>
                            </td>
                            <td class="text-center font-weight-bold {{ $colorPct }}">
                                {{ $pct }}%
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1 align-items-center">
                                    @if ($tieneAmantani)
                                        <span class="badge badge-amantani"><i class="fas fa-hand-holding-heart mr-1"></i> Amantani</span>
                                    @endif
                                    @if ($tienePronabec)
                                        <span class="badge badge-pronabec"><i class="fas fa-award mr-1"></i> Pronabec</span>
                                    @endif
                                    @if ($tieneLicencia)
                                        <span class="badge badge-licencia"><i class="fas fa-user-clock mr-1"></i> Licencia</span>
                                    @endif
                                    @if (! $tieneAmantani && ! $tienePronabec && ! $tieneLicencia)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.asistencias.show', $s->id) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle de alumnos">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-gray-300"></i>
                                <p class="font-weight-bold mb-1">No se encontraron sesiones de asistencia con los filtros seleccionados.</p>
                                <small>Prueba ajustando los parámetros de búsqueda o limpiando los filtros.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sesiones->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $sesiones->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
