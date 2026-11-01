@extends('ayllu.layout')

@section('ayllu_contenido')
{{-- Aviso claro de separación de planos (Restricción Innegociable #6) --}}
<div class="ayllu-vault-banner mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.75rem;">
        <div>
            <span class="ayllu-badge ayllu-badge-privado mb-2">
                <i class="fas fa-user-shield mr-1"></i> Plano 3 · Bóveda Académica y Administrativa Privada
            </span>
            <h2 class="h4 font-weight-bold text-white mb-1">
                Mi Espacio Privado — {{ $user->name }}
            </h2>
            <p class="small mb-0" style="color: rgba(255,255,255,0.85);">
                Esta sección es estrictamente confidencial y personal. Tus calificaciones oficiales, asistencias, ficha de matrícula y trámites nunca se publican en el muro comunitario ni se mezclan con los ensayos formativos.
            </p>
        </div>
        <div class="d-flex flex-wrap" style="gap: 0.45rem;">
            <a href="{{ $resumenPrivado['ruta_calificaciones'] }}" class="btn btn-sm btn-light font-weight-bold" style="border-radius: 999px; color: #161c22;">
                <i class="fas fa-award mr-1 text-warning"></i> Actas y Notas Oficiales
            </a>
            <a href="{{ $resumenPrivado['ruta_ficha'] }}" class="btn btn-sm btn-outline-light font-weight-bold" style="border-radius: 999px;">
                <i class="fas fa-id-card mr-1"></i> Ficha Oficial
            </a>
        </div>
    </div>
</div>

{{-- Resumen de accesos oficiales según perfil --}}
<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="ayllu-card p-3 h-100 mb-0">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small font-weight-bold text-uppercase text-muted">Calificaciones Oficiales</span>
                <i class="fas fa-file-signature text-warning"></i>
            </div>
            <div class="h5 font-weight-bold mb-1" style="color: #161c22;">
                {{ $resumenPrivado['calificaciones_registradas'] }} registros en período
            </div>
            <p class="small text-muted mb-2" style="font-size: 0.76rem;">
                Consulta tus valoraciones oficiales por competencia y cortes del semestre en el módulo académico oficial.
            </p>
            <a href="{{ $resumenPrivado['ruta_calificaciones'] }}" class="small font-weight-bold" style="color: #9e3d22;">
                Abrir módulo oficial de calificaciones <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="ayllu-card p-3 h-100 mb-0">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small font-weight-bold text-uppercase text-muted">Asistencia Registrada</span>
                <i class="fas fa-user-check text-success"></i>
            </div>
            <div class="h5 font-weight-bold mb-1" style="color: #161c22;">
                @if($resumenPrivado['asistencias_total'] > 0)
                    {{ $resumenPrivado['asistencias_presentes'] }} / {{ $resumenPrivado['asistencias_total'] }} sesiones
                @else
                    Control activo por curso
                @endif
            </div>
            <p class="small text-muted mb-2" style="font-size: 0.76rem;">
                Registro individual de puntualidad, asistencia y justificaciones por cada sesión de aprendizaje.
            </p>
            @if($esDocente && $docente)
                <a href="{{ route('vistaDocente', $docente->id) }}" class="small font-weight-bold" style="color: #9e3d22;">
                    Registrar asistencia de mis cursos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @else
                <span class="small text-muted" style="font-size: 0.74rem;">Detalle de sesiones disponible abajo</span>
            @endif
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="ayllu-card p-3 h-100 mb-0">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small font-weight-bold text-uppercase text-muted">Matrícula, Pagos y Tutoría</span>
                <i class="fas fa-clipboard-check text-primary"></i>
            </div>
            <div class="h5 font-weight-bold mb-1" style="color: #161c22;">
                {{ $resumenPrivado['estado_matricula'] }}
            </div>
            <p class="small text-muted mb-2" style="font-size: 0.76rem;">
                Acceso directo a ficha de matrícula, constancias, consejería de tutoría y seguimiento de titulación.
            </p>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                @if($esEstudiante)
                    <a href="{{ route('alumno.atencion') }}" class="small font-weight-bold" style="color: #33445a;">
                        <i class="fas fa-hands-helping mr-1"></i> Tutoría
                    </a>
                    <a href="{{ route('bolsa') }}" class="small font-weight-bold" style="color: #33445a;">
                        <i class="fas fa-briefcase mr-1"></i> Bolsa de trabajo
                    </a>
                @elseif($esTutor)
                    <a href="{{ route('tutor.dashboard') }}" class="small font-weight-bold" style="color: #33445a;">
                        <i class="fas fa-user-friends mr-1"></i> Panel de Tutoría
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Si es Docente: Sesiones de asistencia recientes registradas --}}
@if($esDocente && $docente && $sesionesDocente->isNotEmpty())
    <div class="ayllu-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                <i class="fas fa-chalkboard-teacher text-primary mr-1"></i>
                Últimas sesiones de asistencia registradas como Docente
            </h3>
            <a href="{{ route('vistaDocente', $docente->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 999px;">
                Gestionar asistencias
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Curso</th>
                        <th>Observación / Tema</th>
                        <th>Estudiantes</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sesionesDocente as $sesion)
                        <tr>
                            <td class="small font-weight-bold">{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}</td>
                            <td class="small">{{ $sesion->curso?->nombre ?? 'Curso' }}</td>
                            <td class="small text-muted">{{ $sesion->tema ?: ($sesion->observaciones ?: 'Sesión regular') }}</td>
                            <td class="small">{{ $sesion->detalles->count() }} registros</td>
                            <td>
                                @if($sesion->curso_id && Route::has('docente.asistencia.historial'))
                                    <a href="{{ route('docente.asistencia.historial', [$docente->id, $sesion->curso_id]) }}" class="btn btn-xs btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
                                        Ver historial
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Historial individual de Asistencia del Estudiante --}}
<div class="ayllu-card p-4">
    <h3 class="h6 font-weight-bold mb-3" style="color: #161c22;">
        <i class="fas fa-calendar-check text-success mr-1"></i>
        Mi Historial Individual de Asistencia Reciente
    </h3>

    @if($misAsistencias->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Fecha de sesión</th>
                        <th>Curso</th>
                        <th>Estado</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($misAsistencias as $det)
                        @php
                            $badgeEstado = [
                                'presente' => 'badge-success',
                                'tardanza' => 'badge-warning',
                                'falta' => 'badge-danger',
                                'justificado' => 'badge-info',
                            ][$det->estado] ?? 'badge-secondary';
                        @endphp
                        <tr>
                            <td class="small font-weight-bold">
                                {{ $det->asistencia?->fecha ? \Carbon\Carbon::parse($det->asistencia->fecha)->format('d/m/Y') : $det->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="small">{{ $det->asistencia?->curso?->nombre ?? 'Curso' }}</td>
                            <td>
                                <span class="badge {{ $badgeEstado }} text-uppercase" style="font-size: 0.68rem;">
                                    {{ $det->estado }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $det->observacion ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-3 rounded text-center" style="background: #f5f2ed;">
            <p class="small text-muted mb-0">
                Aún no tienes registros individuales de asistencia en las sesiones recientes de este período.
            </p>
        </div>
    @endif
</div>
@endsection
