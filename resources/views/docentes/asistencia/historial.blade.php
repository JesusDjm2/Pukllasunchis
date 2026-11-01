@extends('layouts.docente')

@section('titulo', 'Historial de Asistencia — ' . $curso->nombre)

@push('styles')
<style>
    .hist-card {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
        border: 1px solid #eef0f7;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .hist-header {
        background: #fff;
        border-left: 4px solid #4f46e5;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    .stat-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- Cabecera --}}
    <div class="hist-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Cursos
                </a>
                <h1 class="h4 font-weight-bold text-dark mb-1">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i> Historial de Asistencias: {{ $curso->nombre }}
                </h1>
                <div class="text-muted small">
                    {{ $curso->ciclo?->programa?->nombre ?? 'FID' }} · Ciclo {{ $curso->ciclo?->nombre ?? '—' }} · Prof. {{ $docente->nombre }}
                </div>
            </div>
            <div>
                <a href="{{ route('docente.asistencia.tomar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                    class="btn btn-primary font-weight-bold shadow-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Tomar nueva lista
                </a>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Estadísticas Acumuladas --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Sesiones registradas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSesiones }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Asistencia Promedio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $porcentajeGlobal }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Faltas Injustificadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalFaltas }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tardanzas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTardanzas }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Sesiones --}}
    <div class="hist-card">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i> Sesiones de Asistencia Registradas</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 130px;">Fecha</th>
                        <th>Tema / Observación</th>
                        <th class="text-center" style="width: 100px;">Presentes</th>
                        <th class="text-center" style="width: 90px;">Tardanzas</th>
                        <th class="text-center" style="width: 90px;">Faltas</th>
                        <th class="text-center" style="width: 90px;">Licencias</th>
                        <th class="text-center" style="width: 120px;">% Asistencia</th>
                        <th class="text-right" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sesiones as $sesion)
                        @php
                            $pct = $sesion->porcentaje_asistencia;
                            $colorPct = $pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger');
                        @endphp
                        <tr>
                            <td>
                                <span class="font-weight-bold text-dark d-block">
                                    {{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}
                                </span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($sesion->fecha)->isoFormat('dddd') }}</small>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $sesion->tema ?: 'Sin tema especificado' }}</div>
                                @if ($sesion->observaciones)
                                    <small class="text-muted"><i class="far fa-comment mr-1"></i> {{ $sesion->observaciones }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="stat-badge bg-success text-white">{{ $sesion->presentes_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="stat-badge bg-warning text-dark">{{ $sesion->tardanzas_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="stat-badge bg-danger text-white">{{ $sesion->faltas_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="stat-badge text-white" style="background:#7c3aed;">{{ $sesion->licencias_count }}</span>
                            </td>
                            <td class="text-center font-weight-bold {{ $colorPct }}">
                                {{ $pct }}%
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ route('docente.asistencia.tomar', ['docente' => $docente->id, 'curso' => $curso->id, 'fecha' => $sesion->fecha->format('Y-m-d')]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Editar o ver lista de esta fecha">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('docente.asistencia.eliminar', ['docente' => $docente->id, 'curso' => $curso->id, 'asistencia' => $sesion->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar el registro de asistencia del {{ $sesion->fecha->format('d/m/Y') }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar sesión">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-gray-300"></i>
                                <p class="font-weight-bold mb-1">Aún no se ha registrado ninguna asistencia para este curso.</p>
                                <a href="{{ route('docente.asistencia.tomar', ['docente' => $docente->id, 'curso' => $curso->id]) }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus mr-1"></i> Tomar primera lista
                                </a>
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
