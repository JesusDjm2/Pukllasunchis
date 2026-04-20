@extends('layouts.docente')

@section('titulo', 'Competencia — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Competencias',
            'title' => $competencia->nombre,
            'subtitle' => 'Descripción y capacidades asociadas.',
            'backUrl' => route('vistaDocente', ['docente' => $docente->id]),
            'backLabel' => 'Mis cursos',
        ])

        <div class="card docente-ui-card">
            <div class="card-body p-3 p-md-4">
                <h2 class="h5 font-weight-bold text-primary border-bottom pb-2 mb-3">Descripción</h2>
                <p class="text-gray-700 mb-4">{{ $competencia->descripcion }}</p>
                <h2 class="h5 font-weight-bold text-primary border-bottom pb-2 mb-3">Capacidades</h2>
                <div class="text-gray-700">{!! $competencia->capacidades !!}</div>
            </div>
        </div>
    </div>
@endsection
