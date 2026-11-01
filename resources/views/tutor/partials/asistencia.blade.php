{{--
    Panel de Asistencia del Ciclo para el Tutor
    Variables requeridas: $resumenAsistencia (array de AsistenciaService::resumenCiclo)
--}}
<div class="pk-tutor-asistencia py-3">

    {{-- Tarjetas Resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <div class="text-primary font-weight-bold h4 mb-0">{{ $resumenAsistencia['total_sesiones'] }}</div>
                <small class="text-muted font-weight-bold">Sesiones Registradas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <div class="text-info font-weight-bold h4 mb-0">{{ $resumenAsistencia['total_becarios'] }}</div>
                <small class="text-muted font-weight-bold">
                    Becarios
                    @if ($resumenAsistencia['total_amantani'] > 0 || $resumenAsistencia['total_pronabec'] > 0)
                        <span class="d-block text-xs" style="font-size:0.7rem;">
                            ({{ $resumenAsistencia['total_amantani'] }} Amantani · {{ $resumenAsistencia['total_pronabec'] }} Pronabec)
                        </span>
                    @endif
                </small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <div class="text-warning font-weight-bold h4 mb-0">{{ $resumenAsistencia['total_licencias'] }}</div>
                <small class="text-muted font-weight-bold">En Licencia</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center {{ $resumenAsistencia['total_en_alerta'] > 0 ? 'bg-danger text-white' : 'bg-white' }}">
                <div class="{{ $resumenAsistencia['total_en_alerta'] > 0 ? 'text-white' : 'text-danger' }} font-weight-bold h4 mb-0">
                    {{ $resumenAsistencia['total_en_alerta'] }}
                </div>
                <small class="{{ $resumenAsistencia['total_en_alerta'] > 0 ? 'text-white' : 'text-muted' }} font-weight-bold">
                    En Alerta (≥ 3 Faltas)
                </small>
            </div>
        </div>
    </div>

    @if ($resumenAsistencia['total_en_alerta'] > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                <div>
                    <strong class="d-block">¡Alerta de Inasistencias en el Ciclo!</strong>
                    <span>Hay {{ $resumenAsistencia['total_en_alerta'] }} estudiante(s) con 3 o más faltas acumuladas o con baja asistencia. Se recomienda coordinar un seguimiento de tutoría oportuno.</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabla de Estudiantes del Ciclo con su Seguimiento de Asistencia --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-user-graduate text-primary mr-1"></i> Asistencia por Estudiante en este Ciclo
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Estudiante</th>
                        <th style="width: 200px;">Condición (Beca / Licencia)</th>
                        <th class="text-center" style="width: 100px;">Sesiones</th>
                        <th class="text-center" style="width: 90px;">Presente</th>
                        <th class="text-center" style="width: 90px;">Faltas</th>
                        <th class="text-center" style="width: 140px;">% Asistencia</th>
                        <th class="text-center" style="width: 110px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resumenAsistencia['alumnos'] as $index => $item)
                        @php
                            $pct = $item['porcentaje'];
                            $colorPct = $pct === null ? 'text-muted' : ($pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger'));
                        @endphp
                        <tr class="{{ $item['alerta'] ? 'table-warning' : '' }}">
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $item['nombre'] }}</div>
                                <small class="text-muted">DNI: {{ $item['dni'] }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    {{-- Beca --}}
                                    @if ($item['es_beca'])
                                        @if (stripos($item['tipo_beca'], 'amantani') !== false)
                                            <span class="badge badge-amantani"><i class="fas fa-hand-holding-heart mr-1"></i> Beca Amantani</span>
                                        @elseif (stripos($item['tipo_beca'], 'pronabec') !== false || stripos($item['tipo_beca'], 'beca 18') !== false)
                                            <span class="badge badge-pronabec"><i class="fas fa-award mr-1"></i> {{ $item['tipo_beca'] }}</span>
                                        @else
                                            <span class="badge badge-success"><i class="fas fa-graduation-cap mr-1"></i> {{ $item['tipo_beca'] }}</span>
                                        @endif
                                    @endif

                                    {{-- Licencia --}}
                                    @if ($item['en_licencia'])
                                        <span class="badge badge-licencia"><i class="fas fa-user-clock mr-1"></i> EN LICENCIA</span>
                                    @endif

                                    @if (! $item['es_beca'] && ! $item['en_licencia'])
                                        <span class="text-muted small">Regular</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center font-weight-bold">{{ $item['total_sesiones'] }}</td>
                            <td class="text-center text-success font-weight-bold">{{ $item['presentes'] }}</td>
                            <td class="text-center font-weight-bold {{ $item['faltas'] > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $item['faltas'] }}
                            </td>
                            <td class="text-center">
                                @if ($pct !== null)
                                    <div class="font-weight-bold {{ $colorPct }}">{{ $pct }}%</div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar {{ $pct >= 85 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                            style="width: {{ $pct }}%;"></div>
                                    </div>
                                @else
                                    <span class="text-muted small">Sin registros</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item['en_licencia'])
                                    <span class="badge badge-warning text-dark font-weight-bold">Licencia</span>
                                @elseif ($item['alerta'])
                                    <span class="badge badge-danger font-weight-bold"><i class="fas fa-bell mr-1"></i> Alerta</span>
                                @elseif ($pct !== null && $pct >= 85)
                                    <span class="badge badge-success font-weight-bold">Normal</span>
                                @else
                                    <span class="badge badge-light text-muted border">Regular</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No hay estudiantes registrados en este ciclo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Cursos del Ciclo y Estado de Asistencia --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-book text-primary mr-1"></i> Cursos del Ciclo
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Curso</th>
                        <th>Docente(s)</th>
                        <th class="text-center" style="width: 140px;">Sesiones Registradas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resumenAsistencia['cursos'] as $curso)
                        @php
                            $nSes = $resumenAsistencia['sesiones']->where('curso_id', $curso->id)->count();
                        @endphp
                        <tr>
                            <td class="font-weight-bold text-dark">{{ $curso->nombre }}</td>
                            <td>
                                @if ($curso->docentes->isNotEmpty())
                                    {{ $curso->docentes->pluck('nombre')->implode(', ') }}
                                @else
                                    <span class="text-muted small">Sin docente asignado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $nSes > 0 ? 'badge-primary' : 'badge-light text-muted border' }} px-3 py-1 font-weight-bold">
                                    {{ $nSes }} {{ $nSes === 1 ? 'sesión' : 'sesiones' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
