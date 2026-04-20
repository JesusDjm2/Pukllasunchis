@extends('layouts.docente')

@section('titulo', 'Blog — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page px-2 px-md-3">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Docente',
            'title' => $docente->nombre,
            'subtitle' => 'Contenido publicado en su espacio.',
            'backUrl' => route('vistaDocente', ['docente' => $docente->id]),
            'backLabel' => 'Mis cursos',
        ])

        <div class="card docente-ui-card">
            <div class="card-body p-3 p-md-4">
                <div class="docente-blog-content">
                    {!! $docente->blog !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .docente-blog-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
@endpush
