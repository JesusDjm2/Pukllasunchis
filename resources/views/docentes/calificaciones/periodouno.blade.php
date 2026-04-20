@extends('layouts.docente')

@section('titulo', 'Periodo — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Calificaciones',
            'title' => 'Periodo parcial',
            'subtitle' => 'Vista reservada.',
            'backUrl' => auth()->user()->docente ? route('vistaDocente', ['docente' => auth()->user()->docente->id]) : route('index'),
            'backLabel' => 'Inicio',
        ])
        <div class="card docente-ui-card">
            <div class="card-body text-muted small">
                No hay contenido configurado para esta ruta.
            </div>
        </div>
    </div>
@endsection
