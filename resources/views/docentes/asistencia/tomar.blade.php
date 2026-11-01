@extends('layouts.docente')

@section('titulo', 'Tomar Asistencia — ' . $curso->nombre)

@push('styles')
<style>
    /* ── Estilos para Tomar Lista ── */
    .asis-hero {
        background: #fff;
        border-radius: 0.75rem;
        border-left: 4px solid #4f46e5;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }

    .asis-hero h1 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #2e3448;
        margin-bottom: 0.25rem;
    }

    .asis-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f1f3f9;
        color: #4b5563;
    }

    /* ── Barra de Controles de Sesión ── */
    .asis-toolbar {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.25rem 0 rgba(58, 59, 69, 0.08);
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        border: 1px solid #eef0f7;
    }

    /* ── Contador Flotante en Vivo ── */
    .asis-counters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        margin-top: 1rem;
    }

    .asis-counter-item {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
    }

    .asis-counter-item.c-total { background: #e2e8f0; color: #334155; }
    .asis-counter-item.c-p { background: #dcfce7; color: #166534; }
    .asis-counter-item.c-t { background: #fef3c7; color: #92400e; }
    .asis-counter-item.c-f { background: #fee2e2; color: #991b1b; }
    .asis-counter-item.c-j { background: #e0f2fe; color: #075985; }
    .asis-counter-item.c-l { background: #f3e8ff; color: #6b21a8; }

    /* ── Tabla de Alumnos ── */
    .asis-table-card {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.5rem 0 rgba(58, 59, 69, 0.1);
        border: 1px solid #eef0f7;
        overflow: hidden;
        margin-bottom: 5rem;
    }

    .asis-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .asis-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.75rem 0.85rem;
        vertical-align: middle;
    }

    .asis-table tbody td {
        padding: 0.75rem 0.85rem;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }

    .asis-table tbody tr:hover {
        background-color: #f8faff;
    }

    .asis-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        background: #e0e7ff;
        color: #4338ca;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    /* ── Selector de Estado (Segmented Pills) ── */
    .asis-btn-group {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 999px;
        gap: 2px;
        user-select: none;
    }

    .asis-opt {
        cursor: pointer;
        margin-bottom: 0;
    }

    .asis-opt input[type="radio"] {
        display: none;
    }

    .asis-opt-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.1rem;
        height: 2rem;
        padding: 0 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        color: #64748b;
        transition: all 0.15s ease-in-out;
    }

    /* Estado Activo para cada opción */
    .asis-opt input[type="radio"]:checked + .asis-opt-pill.opt-p {
        background: #16a34a;
        color: #fff;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.4);
    }

    .asis-opt input[type="radio"]:checked + .asis-opt-pill.opt-t {
        background: #d97706;
        color: #fff;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4);
    }

    .asis-opt input[type="radio"]:checked + .asis-opt-pill.opt-f {
        background: #dc2626;
        color: #fff;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
    }

    .asis-opt input[type="radio"]:checked + .asis-opt-pill.opt-j {
        background: #0284c7;
        color: #fff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.4);
    }

    .asis-opt input[type="radio"]:checked + .asis-opt-pill.opt-l {
        background: #7c3aed;
        color: #fff;
        box-shadow: 0 2px 6px rgba(124, 58, 237, 0.4);
    }

    /* ── Badges Especiales ── */
    .badge-amantani {
        background-color: #0d9488 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.25);
    }

    .badge-pronabec {
        background-color: #0284c7 !important;
        color: #fff !important;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.25);
    }

    .badge-licencia-destacada {
        background-color: #f59e0b !important;
        color: #1e293b !important;
        font-weight: 800;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        box-shadow: 0 1px 3px rgba(245, 158, 11, 0.3);
        animation: pulseWarning 2s infinite;
    }

    @keyframes pulseWarning {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.03); }
    }

    /* ── Barra Flotante Inferior de Guardado ── */
    .asis-sticky-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.12);
        padding: 0.75rem 1.5rem;
        z-index: 99;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 767.98px) {
        .asis-table thead { display: none; }
        .asis-table, .asis-table tbody, .asis-table tr, .asis-table td {
            display: block;
            width: 100%;
        }
        .asis-table tbody tr {
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem;
            background: #fff;
        }
        .asis-table tbody td {
            border-top: none;
            padding: 0.35rem 0.5rem;
        }
        .asis-btn-group {
            width: 100%;
            justify-content: space-between;
        }
        .asis-opt-pill {
            flex: 1;
        }
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- ── Cabecera Hero ── --}}
    <div class="asis-hero">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Cursos
                </a>
                <h1><i class="fas fa-clipboard-check text-primary mr-2"></i> Tomar Lista: {{ $curso->nombre }}</h1>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="asis-meta-pill">
                        <i class="fas fa-layer-group text-primary"></i> Ciclo {{ $curso->ciclo?->nombre ?? '—' }}
                    </span>
                    <span class="asis-meta-pill">
                        <i class="fas fa-graduation-cap text-success"></i> {{ $curso->ciclo?->programa?->nombre ?? 'FID' }}
                    </span>
                    <span class="asis-meta-pill">
                        <i class="fas fa-user-tie text-info"></i> Prof. {{ $docente->nombre }}
                    </span>
                    @if ($periodoActual)
                        <span class="asis-meta-pill">
                            <i class="fas fa-calendar-alt text-warning"></i> Periodo {{ $periodoActual->nombre }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('docente.asistencia.historial', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                    class="btn btn-outline-primary btn-sm font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Ver Historial ({{ $totalSesiones }})
                </a>
            </div>
        </div>
    </div>

    {{-- Alertas del sistema --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Formulario de Asistencia ── --}}
    <form action="{{ route('docente.asistencia.guardar', ['docente' => $docente->id, 'curso' => $curso->id]) }}" method="POST" id="formAsistencia">
        @csrf

        {{-- ── Barra de Selección de Fecha y Tema de Clase ── --}}
        <div class="asis-toolbar">
            <div class="row align-items-center">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark small mb-1"><i class="far fa-calendar-alt mr-1 text-primary"></i> Fecha de la sesión:</label>
                    <div class="input-group">
                        <input type="date" name="fecha" id="inputFecha" class="form-control form-control-sm font-weight-bold"
                            value="{{ $fecha }}" max="{{ now()->addDays(7)->toDateString() }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnCambiarFecha" title="Cargar sesión de esta fecha">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    @if ($asistenciaExistente)
                        <small class="text-success font-weight-bold d-block mt-1">
                            <i class="fas fa-check-double mr-1"></i> Ya existe registro para esta fecha (modo edición).
                        </small>
                    @else
                        <small class="text-muted d-block mt-1">Nueva sesión de asistencia.</small>
                    @endif
                </div>

                <div class="col-md-5 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-book-reader mr-1 text-indigo"></i> Tema / Sesión (Opcional):</label>
                    <input type="text" name="tema" class="form-control form-control-sm"
                        placeholder="p. ej. Sesión 5: Didáctica y estrategias comunicativas..."
                        value="{{ old('tema', $asistenciaExistente?->tema) }}">
                </div>

                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-comment-alt mr-1 text-muted"></i> Observaciones generales:</label>
                    <input type="text" name="observaciones" class="form-control form-control-sm"
                        placeholder="Notas de la clase (opcional)..."
                        value="{{ old('observaciones', $asistenciaExistente?->observaciones) }}">
                </div>
            </div>

            {{-- Botones de Acción Rápida y Contadores en Vivo --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 pt-3 border-top">
                <div class="d-flex flex-wrap gap-2 mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-sm" id="btnTodosPresentes">
                        <i class="fas fa-check-double mr-1"></i> Marcar todos PRESENTES
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetear">
                        <i class="fas fa-undo mr-1"></i> Restablecer valores
                    </button>
                </div>

                {{-- Leyenda y Contadores --}}
                <div class="asis-counters">
                    <span class="asis-counter-item c-total" title="Total matriculados"><i class="fas fa-users mr-1"></i> <span id="cntTotal">{{ $alumnos->count() }}</span></span>
                    <span class="asis-counter-item c-p" title="Presentes"><i class="fas fa-check mr-1"></i> <span id="cntP">0</span> P</span>
                    <span class="asis-counter-item c-t" title="Tardanzas"><i class="fas fa-clock mr-1"></i> <span id="cntT">0</span> T</span>
                    <span class="asis-counter-item c-f" title="Faltas"><i class="fas fa-times mr-1"></i> <span id="cntF">0</span> F</span>
                    <span class="asis-counter-item c-j" title="Justificados"><i class="fas fa-file-medical mr-1"></i> <span id="cntJ">0</span> J</span>
                    <span class="asis-counter-item c-l" title="Licencia"><i class="fas fa-user-clock mr-1"></i> <span id="cntL">0</span> L</span>
                </div>
            </div>
        </div>

        {{-- ── Tabla de Alumnos ── --}}
        <div class="asis-table-card">
            <div class="table-responsive">
                <table class="asis-table">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Estudiante</th>
                            <th style="width: 220px;">Condición (Beca / Licencia)</th>
                            <th style="width: 270px;" class="text-center">Asistencia</th>
                            <th style="width: 250px;">Observación del alumno</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alumnos as $index => $alumno)
                            @php
                                $userId = $alumno->user_id;
                                $detallePrevio = $userId ? $detallesPrevios->get($userId) : null;

                                // Si ya existe registro se usa ese estado; si no, si está en Licencia se preselecciona 'licencia', sino 'presente'
                                if ($detallePrevio) {
                                    $estadoSeleccionado = $detallePrevio->estado;
                                    $obsSeleccionada = $detallePrevio->observacion;
                                } elseif ($alumno->en_licencia) {
                                    $estadoSeleccionado = 'licencia';
                                    $obsSeleccionada = 'En Licencia';
                                } else {
                                    $estadoSeleccionado = 'presente';
                                    $obsSeleccionada = '';
                                }

                                $iniciales = mb_strtoupper(mb_substr($alumno->nombres, 0, 1) . mb_substr($alumno->apellidos, 0, 1));
                            @endphp
                            <tr class="asis-row {{ $alumno->en_licencia ? 'bg-light-warning' : '' }}" data-user-id="{{ $userId }}">
                                <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($alumno->foto)
                                            <img src="{{ $alumno->foto }}" alt="{{ $alumno->nombres }}" class="asis-avatar" loading="lazy">
                                        @else
                                            <span class="asis-avatar">{{ $iniciales }}</span>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold text-dark" style="font-size: 0.92rem;">
                                                {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                            </div>
                                            <small class="text-muted">
                                                DNI: {{ $alumno->dni }}
                                                @if (! $alumno->es_curso_ciclo)
                                                    · <span class="badge badge-light border">Ciclo {{ $alumno->ciclo_nombre }}</span>
                                                @endif
                                                @if ($alumno->es_inhabilitado && ! $alumno->en_licencia)
                                                    · <span class="badge badge-danger">Inhabilitado</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Campos ocultos para snapshot --}}
                                    <input type="hidden" name="alumnos[{{ $userId }}][alumno_id]" value="{{ $alumno->alumno_id }}">
                                    <input type="hidden" name="alumnos[{{ $userId }}][en_licencia]" value="{{ $alumno->en_licencia ? '1' : '0' }}">
                                    <input type="hidden" name="alumnos[{{ $userId }}][es_beca]" value="{{ $alumno->es_beca ? '1' : '0' }}">
                                    <input type="hidden" name="alumnos[{{ $userId }}][tipo_beca]" value="{{ $alumno->tipo_beca }}">
                                    <input type="hidden" name="alumnos[{{ $userId }}][origen_beca]" value="{{ $alumno->origen_beca }}">
                                </td>

                                {{-- Columna Condición (Licencia / Beca) --}}
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        {{-- Beca (Amantani, Pronabec u otra) --}}
                                        @if ($alumno->es_beca)
                                            <div>
                                                @if (stripos($alumno->tipo_beca, 'amantani') !== false)
                                                    <span class="badge badge-amantani" title="{{ $alumno->origen_beca }}">
                                                        <i class="fas fa-hand-holding-heart mr-1"></i> {{ $alumno->tipo_beca }}
                                                    </span>
                                                @elseif (stripos($alumno->tipo_beca, 'pronabec') !== false || stripos($alumno->tipo_beca, 'beca 18') !== false)
                                                    <span class="badge badge-pronabec" title="{{ $alumno->origen_beca }}">
                                                        <i class="fas fa-award mr-1"></i> {{ $alumno->tipo_beca }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-success" title="{{ $alumno->origen_beca }}">
                                                        <i class="fas fa-graduation-cap mr-1"></i> {{ $alumno->beca_label }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Licencia --}}
                                        @if ($alumno->en_licencia)
                                            <div>
                                                <span class="badge badge-licencia-destacada" title="{{ $alumno->motivo_licencia }}">
                                                    <i class="fas fa-user-clock mr-1"></i> EN LICENCIA
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Si no tiene beca ni licencia --}}
                                        @if (! $alumno->es_beca && ! $alumno->en_licencia)
                                            <span class="text-muted small">
                                                <i class="far fa-circle mr-1 text-muted"></i> Regular
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Selector Asistencia (P / T / F / J / L) --}}
                                <td class="text-center">
                                    <div class="asis-btn-group" role="group" aria-label="Asistencia para {{ $alumno->nombre_completo }}">
                                        {{-- Presente --}}
                                        <label class="asis-opt" title="Presente">
                                            <input type="radio" name="alumnos[{{ $userId }}][estado]" value="presente"
                                                class="asis-radio radio-p" {{ $estadoSeleccionado === 'presente' ? 'checked' : '' }}>
                                            <span class="asis-opt-pill opt-p">P</span>
                                        </label>

                                        {{-- Tardanza --}}
                                        <label class="asis-opt" title="Tardanza">
                                            <input type="radio" name="alumnos[{{ $userId }}][estado]" value="tardanza"
                                                class="asis-radio radio-t" {{ $estadoSeleccionado === 'tardanza' ? 'checked' : '' }}>
                                            <span class="asis-opt-pill opt-t">T</span>
                                        </label>

                                        {{-- Falta --}}
                                        <label class="asis-opt" title="Falta injustificada">
                                            <input type="radio" name="alumnos[{{ $userId }}][estado]" value="falta"
                                                class="asis-radio radio-f" {{ $estadoSeleccionado === 'falta' ? 'checked' : '' }}>
                                            <span class="asis-opt-pill opt-f">F</span>
                                        </label>

                                        {{-- Justificado --}}
                                        <label class="asis-opt" title="Falta justificada / Permiso">
                                            <input type="radio" name="alumnos[{{ $userId }}][estado]" value="justificado"
                                                class="asis-radio radio-j" {{ $estadoSeleccionado === 'justificado' ? 'checked' : '' }}>
                                            <span class="asis-opt-pill opt-j">J</span>
                                        </label>

                                        {{-- Licencia --}}
                                        <label class="asis-opt" title="En Licencia oficial">
                                            <input type="radio" name="alumnos[{{ $userId }}][estado]" value="licencia"
                                                class="asis-radio radio-l" {{ $estadoSeleccionado === 'licencia' ? 'checked' : '' }}>
                                            <span class="asis-opt-pill opt-l">L</span>
                                        </label>
                                    </div>
                                </td>

                                {{-- Observación del Alumno --}}
                                <td>
                                    <input type="text" name="alumnos[{{ $userId }}][observacion]"
                                        class="form-control form-control-sm asis-obs-input"
                                        placeholder="Nota / justificación..."
                                        value="{{ old('alumnos.'.$userId.'.observacion', $obsSeleccionada) }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-gray-300"></i>
                                    <p class="font-weight-bold mb-1">No hay alumnos registrados en este curso.</p>
                                    <small>Verifica las asignaciones del ciclo o del periodo actual.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Barra Flotante Inferior de Guardado ── --}}
        @if ($alumnos->isNotEmpty())
            <div class="asis-sticky-bar d-flex justify-content-between align-items-center">
                <div class="small text-muted d-none d-md-block">
                    <i class="fas fa-info-circle text-primary mr-1"></i>
                    Los registros quedarán disponibles para Dirección, Coordinación y Tutoría.
                </div>
                <div class="d-flex gap-2 ml-auto">
                    <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow" id="btnGuardar">
                        <i class="fas fa-save mr-2"></i> Guardar Asistencia
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var radios = document.querySelectorAll('.asis-radio');
    var cntP = document.getElementById('cntP');
    var cntT = document.getElementById('cntT');
    var cntF = document.getElementById('cntF');
    var cntJ = document.getElementById('cntJ');
    var cntL = document.getElementById('cntL');

    function actualizarContadores() {
        var p = 0, t = 0, f = 0, j = 0, l = 0;
        radios.forEach(function(r) {
            if (r.checked) {
                switch(r.value) {
                    case 'presente': p++; break;
                    case 'tardanza': t++; break;
                    case 'falta': f++; break;
                    case 'justificado': j++; break;
                    case 'licencia': l++; break;
                }
            }
        });
        if (cntP) cntP.textContent = p;
        if (cntT) cntT.textContent = t;
        if (cntF) cntF.textContent = f;
        if (cntJ) cntJ.textContent = j;
        if (cntL) cntL.textContent = l;
    }

    radios.forEach(function(r) {
        r.addEventListener('change', actualizarContadores);
    });

    actualizarContadores();

    // Marcar todos como presentes (excepto los que están en licencia)
    var btnTodos = document.getElementById('btnTodosPresentes');
    if (btnTodos) {
        btnTodos.addEventListener('click', function() {
            var rows = document.querySelectorAll('.asis-row');
            rows.forEach(function(row) {
                var esLicencia = row.querySelector('input[name*="[en_licencia]"]')?.value === '1';
                var targetValue = esLicencia ? 'licencia' : 'presente';
                var radio = row.querySelector('input[type="radio"][value="' + targetValue + '"]');
                if (radio) {
                    radio.checked = true;
                }
            });
            actualizarContadores();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Se marcaron los estudiantes como presentes',
                    showConfirmButton: false,
                    timer: 1800
                });
            }
        });
    }

    // Botón para recargar por fecha
    var btnCambiarFecha = document.getElementById('btnCambiarFecha');
    var inputFecha = document.getElementById('inputFecha');
    if (btnCambiarFecha && inputFecha) {
        btnCambiarFecha.addEventListener('click', function() {
            var val = inputFecha.value;
            if (val) {
                var url = new URL(window.location.href);
                url.searchParams.set('fecha', val);
                window.location.href = url.toString();
            }
        });
        inputFecha.addEventListener('change', function() {
            var val = inputFecha.value;
            if (val) {
                var url = new URL(window.location.href);
                url.searchParams.set('fecha', val);
                window.location.href = url.toString();
            }
        });
    }
});
</script>
@endpush
