@extends('layouts.alumno')
@section('titulo', 'Detalle de competencia')

@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => $competencia->nombre,
        'subtitle' => 'Descripción y capacidades asociadas.',
        'actions' =>
            '<a href="javascript:history.go(-1)" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>',
    ])

    <div class="card alumno-detail-card">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-muted font-weight-bold mb-3">Descripción</h2>
            <p class="mb-4">{{ $competencia->descripcion }}</p>
            <h2 class="h6 text-uppercase text-muted font-weight-bold mb-3">Capacidades</h2>
            <div class="competencia-capacidades alumno-prose">
                {!! $competencia->capacidades !!}
            </div>
        </div>
    </div>
@endsection
