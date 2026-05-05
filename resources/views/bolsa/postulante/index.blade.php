@extends('layouts.alumno')
@section('titulo', 'Bolsa de trabajo — Oportunidades')
@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => 'Bolsa de trabajo',
        'subtitle' =>
            'Oportunidades laborales publicadas por la institución. Misma información que la página pública; aquí puedes filtrar por año y mes.',
        'actions' =>
            '<a href="' .
            e(route('bolsa')) .
            '" class="btn btn-outline-primary btn-sm shadow-sm" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt mr-1"></i> Ver sitio institucional</a>',
    ])

    @if (session('success'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-0" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="alumno-shell alumno-bolsa-shell">
        <p class="alumno-bolsa-intro mb-3">
            Revisa las vacantes vigentes y usa filtros por periodo para encontrar oportunidades recientes.
        </p>
        @include('partials.bolsa-ofertas-catalogo', ['bolsaRouteName' => 'postulante.index'])
    </div>
@endsection
