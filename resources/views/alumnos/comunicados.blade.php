@extends('layouts.alumno')
@section('titulo', 'Comunicados recientes')
@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => 'Comunicados recientes',
        'subtitle' => 'Últimos 5 comunicados publicados, ordenados por fecha.',
    ])

    <div class="alumno-shell">
        @if ($comunicados->isEmpty())
            <div class="alert alert-info mb-0">
                No hay comunicados publicados por el momento.
            </div>
        @else
            <div class="row">
                @foreach ($comunicados as $comunicado)
                    <div class="col-lg-6 mb-4">
                        <div class="card alumno-detail-card alumno-comunicado-card h-100">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-1 alumno-comunicado-title">{{ $comunicado->titulo }}</h6>
                                <p class="text-muted small mb-3 alumno-comunicado-date">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ $comunicado->fecha_publicacion?->format('d/m/Y') }}
                                </p>
                                @if (!empty($comunicado->descripcion))
                                    <p class="mb-3 alumno-comunicado-desc">{{ $comunicado->descripcion }}</p>
                                @endif
                                <a href="{{ asset($comunicado->archivo) }}" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-primary btn-sm alumno-comunicado-btn">
                                    <i class="fas fa-eye mr-1"></i> Ver comunicado
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
