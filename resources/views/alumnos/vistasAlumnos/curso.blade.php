@extends('layouts.alumno')
@section('titulo', $curso->nombre)
@section('contenido')
    @include('partials.alumno-page-header', [
        'title'    => $curso->nombre,
        'subtitle' => $curso->ciclo->programa->nombre . ' — ' . $curso->ciclo->nombre,
        'actions'  =>
            '<a href="javascript:history.go(-1)" class="btn btn-outline-secondary btn-sm shadow-sm">' .
            '<i class="fas fa-arrow-left mr-1"></i> Volver</a>',
    ])

    {{-- Info principal --}}
    <div class="alumno-shell mb-3 p-0 overflow-hidden">
        <div class="alumno-ficha-section-header">
            <i class="fas fa-book-open mr-2"></i> Información del curso
        </div>
        <div class="px-4 py-3">
            <dl class="alumno-data-list">
                @if ($curso->sumilla)
                    <div class="alumno-data-row" style="grid-column: 1 / -1;">
                        <dt>Sumilla</dt>
                        <dd>{!! $curso->sumilla !!}</dd>
                    </div>
                @endif
                <div class="alumno-data-row">
                    <dt>Ciclo</dt>
                    <dd>{{ $curso->ciclo->nombre }}</dd>
                </div>
                <div class="alumno-data-row">
                    <dt>Componente curricular</dt>
                    <dd>{{ $curso->cc ?? '—' }}</dd>
                </div>
                <div class="alumno-data-row">
                    <dt>Horas semanales</dt>
                    <dd>{{ $curso->horas ?? '—' }}</dd>
                </div>
                <div class="alumno-data-row">
                    <dt>Créditos</dt>
                    <dd>{{ $curso->creditos ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Docentes --}}
    <div class="alumno-shell mb-3 p-0 overflow-hidden">
        <div class="alumno-ficha-section-header">
            <i class="fas fa-chalkboard-teacher mr-2"></i> Docente(s)
        </div>
        <div class="px-4 py-3">
            @if ($docentes->count() > 0)
                <ul class="alumno-curso-list m-0">
                    @foreach ($docentes as $docente)
                        <li class="alumno-curso-item">
                            <div class="alumno-curso-nombre">{{ $docente->nombre }}</div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted mb-0">No hay docente asignado.</p>
            @endif
        </div>
    </div>

    {{-- Sílabo y Classroom --}}
    <div class="alumno-shell mb-3 p-0 overflow-hidden">
        <div class="alumno-ficha-section-header">
            <i class="fas fa-link mr-2"></i> Recursos
        </div>
        <div class="px-4 py-3">
            <dl class="alumno-data-list">
                <div class="alumno-data-row">
                    <dt>Sílabo</dt>
                    <dd>
                        @if ($curso->silabo)
                            <a href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                               class="alumno-silabo-btn">
                                <i class="fa fa-eye mr-1"></i> Ver sílabo
                            </a>
                        @else
                            <span class="alumno-no-silabo">Sin sílabo asignado</span>
                        @endif
                    </dd>
                </div>
                <div class="alumno-data-row">
                    <dt>Classroom</dt>
                    <dd>
                        @if ($curso->classroom)
                            <a href="{{ $curso->classroom }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fab fa-google mr-1"></i> Abrir Classroom
                            </a>
                            @if ($curso->clave)
                                <span class="ml-2 text-muted"><i class="fas fa-key mr-1"></i>{{ $curso->clave }}</span>
                            @endif
                        @else
                            <span class="text-muted">Sin Classroom asignado</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Competencias --}}
    @if ($curso->competencias->isNotEmpty())
        <div class="alumno-shell mb-3 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-star mr-2"></i> Competencias
            </div>
            <div class="px-4 py-3">
                <ul class="alumno-curso-list m-0">
                    @foreach ($curso->competencias as $competencia)
                        <li class="alumno-curso-item">
                            <div class="alumno-curso-nombre">{{ $competencia->nombre }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

@endsection
