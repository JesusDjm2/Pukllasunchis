@extends('layouts.docente')

@section('titulo', $titulo . ' — Ciclo ' . $ciclo->nombre)

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Tutor · ' . (optional($ciclo->programa)->nombre ?? ''),
        'title'     => $titulo,
        'subtitle'  => 'Ciclo ' . $ciclo->nombre . ' — imprime y comparte este código con tus alumnos.',
        'backUrl'   => $volverA,
        'backLabel' => 'Volver a mis ciclos',
    ])

    @include('partials.qr-poster', [
        'tipo'        => $tipo,
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'url'         => $url,
        'contexto'    => $contexto,
        'tutor'       => $tutor ?? null,
    ])
</div>

@if (request('print'))
    @push('scripts')
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endpush
@endif
@endsection
