@php
    $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';
@endphp

@extends($layout)

@section('titulo', 'Detalle de Asistencia — ' . ($asistencia->curso?->nombre ?? 'Sesión'))

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
</style>
@endpush

@section('contenido')
<div class="container-fluid px-3 py-3">

    {{-- Botón Volver --}}
    <div class="mb-3">
        <a href="{{ route('admin.asistencias.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Control de Asistencias
        </a>
    </div>

    {{-- Tarjeta Cabecera --}}
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div>
                    <h2 class="h4 font-weight-bold text-gray-800 mb-1">
                        {{ $asistencia->curso?->nombre }}
                    </h2>
                    <div class="text-muted small mb-2">
                        <span class="mr-3"><i class="fas fa-layer-group text-primary mr-1"></i> Ciclo {{ $asistencia->curso?->ciclo?->nombre ?? '—' }}</span>
                        <span class="mr-3"><i class="fas fa-graduation-cap text-success mr-1"></i> {{ $asistencia->curso?->ciclo?->programa?->nombre ?? 'FID' }}</span>
                        <span class="mr-3"><i class="fas fa-user-tie text-info mr-1"></i> Prof. {{ $asistencia->docente?->nombre ?? '—' }}</span>
                        <span><i class="far fa-calendar-alt text-warning mr-1"></i> {{ \Carbon\Carbon::parse($asistencia->fecha)->isoFormat('dddd D [de] MMMM [de] YYYY') }}</span>
                    </div>
                    @if ($asistencia->tema)
                        <div class="alert alert-light border py-2 px-3 mb-0 small">
                            <i class="fas fa-bookmark text-primary mr-1"></i> <strong>Tema de la sesión:</strong> {{ $asistencia->tema }}
                            @if ($asistencia->observaciones)
                                <div class="mt-1 text-muted"><i class="far fa-comment-alt mr-1"></i> {{ $asistencia->observaciones }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Métricas de la sesión --}}
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap text-center">
                    <div class="px-3 py-2 bg-light rounded border">
                        <div class="h5 font-weight-bold text-success mb-0">{{ $asistencia->presentes_count }}</div>
                        <small class="text-muted font-weight-bold">Presentes</small>
                    </div>
                    <div class="px-3 py-2 bg-light rounded border">
                        <div class="h5 font-weight-bold text-warning mb-0">{{ $asistencia->tardanzas_count }}</div>
                        <small class="text-muted font-weight-bold">Tardanzas</small>
                    </div>
                    <div class="px-3 py-2 bg-light rounded border">
                        <div class="h5 font-weight-bold text-danger mb-0">{{ $asistencia->faltas_count }}</div>
                        <small class="text-muted font-weight-bold">Faltas</small>
                    </div>
                    <div class="px-3 py-2 bg-light rounded border">
                        <div class="h5 font-weight-bold text-purple mb-0" style="color: #7c3aed;">{{ $asistencia->licencias_count }}</div>
                        <small class="text-muted font-weight-bold">Licencias</small>
                    </div>
                    <div class="px-3 py-2 bg-light rounded border">
                        <div class="h5 font-weight-bold text-primary mb-0">{{ $asistencia->porcentaje_asistencia }}%</div>
                        <small class="text-muted font-weight-bold">Asistencia</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Estudiantes y su Asistencia --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users mr-1"></i> Lista de Estudiantes Registrados ({{ $asistencia->detalles->count() }})
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Estudiante</th>
                        <th style="width: 220px;">Condición (Beca / Licencia)</th>
                        <th style="width: 150px;" class="text-center">Estado Asistencia</th>
                        <th>Observación Registrada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($asistencia->detalles->sortBy(fn($d) => $d->nombre_completo) as $idx => $d)
                        @php
                            $user = $d->user;
                            $alumno = $d->alumno;
                            $foto = $user?->foto ? asset('img/estudiantes/'.$user->foto) : null;
                        @endphp
                        <tr class="{{ $d->estado === 'falta' ? 'table-danger' : ($d->en_licencia || $d->estado === 'licencia' ? 'table-warning' : '') }}">
                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $d->nombre_completo }}</div>
                                <small class="text-muted">DNI: {{ $alumno?->dni ?: ($user?->dni ?? '—') }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    {{-- Beca --}}
                                    @if ($d->es_beca)
                                        @if (stripos($d->tipo_beca, 'amantani') !== false)
                                            <span class="badge badge-amantani" title="{{ $d->origen_beca }}">
                                                <i class="fas fa-hand-holding-heart mr-1"></i> {{ $d->tipo_beca }}
                                            </span>
                                        @elseif (stripos($d->tipo_beca, 'pronabec') !== false || stripos($d->tipo_beca, 'beca 18') !== false)
                                            <span class="badge badge-pronabec" title="{{ $d->origen_beca }}">
                                                <i class="fas fa-award mr-1"></i> {{ $d->tipo_beca }}
                                            </span>
                                        @else
                                            <span class="badge badge-success" title="{{ $d->origen_beca }}">
                                                <i class="fas fa-graduation-cap mr-1"></i> {{ $d->tipo_beca }}
                                            </span>
                                        @endif
                                    @endif

                                    {{-- Licencia --}}
                                    @if ($d->en_licencia || $d->estado === 'licencia')
                                        <span class="badge badge-licencia">
                                            <i class="fas fa-user-clock mr-1"></i> EN LICENCIA
                                        </span>
                                    @endif

                                    @if (! $d->es_beca && ! $d->en_licencia && $d->estado !== 'licencia')
                                        <span class="text-muted small">Regular</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($d->estado === 'presente')
                                    <span class="badge badge-success px-3 py-1 font-weight-bold"><i class="fas fa-check mr-1"></i> Presente</span>
                                @elseif ($d->estado === 'tardanza')
                                    <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark"><i class="fas fa-clock mr-1"></i> Tardanza</span>
                                @elseif ($d->estado === 'falta')
                                    <span class="badge badge-danger px-3 py-1 font-weight-bold"><i class="fas fa-times mr-1"></i> Falta</span>
                                @elseif ($d->estado === 'justificado')
                                    <span class="badge badge-info px-3 py-1 font-weight-bold"><i class="fas fa-file-medical mr-1"></i> Justificado</span>
                                @elseif ($d->estado === 'licencia')
                                    <span class="badge text-white px-3 py-1 font-weight-bold" style="background: #7c3aed;"><i class="fas fa-user-clock mr-1"></i> Licencia</span>
                                @endif
                            </td>
                            <td>
                                @if ($d->observacion)
                                    <span class="text-dark small"><i class="far fa-comment-dots text-primary mr-1"></i> {{ $d->observacion }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
