@extends('layouts.docente')

@section('titulo', 'Ciclo ' . $ciclo->nombre . ' — Tutor')

@push('styles')
@include('docentes.alumnos.partials.estilos')
<style>
    /* Ficha completa del alumno (tutor/partials/detalle-alumno) */
    .detalle-card {
        border: 1px solid #e7eaf3;
        border-radius: .5rem;
        background: #fff;
    }
    .detalle-card-title {
        font-size: .68rem;
        font-weight: 700;
        color: var(--al-acento, #4e73df);
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .detalle-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: .6rem;
        padding: .22rem 0;
        border-bottom: 1px dashed var(--al-borde, #eef0f5);
        font-size: .76rem;
        line-height: 1.25;
    }
    .detalle-row:last-child { border-bottom: none; }
    .detalle-k { color: var(--al-suave, #858796); font-weight: 600; flex: 0 0 auto; white-space: nowrap; }
    .detalle-v { color: var(--al-texto, #2f3441); text-align: right; word-break: break-word; }
</style>
@endpush

@section('contenido')
@php
    $docenteTutor = auth()->user()->docente;
    $nombreTutor = $docenteTutor?->nombre ?: trim(auth()->user()->name.' '.auth()->user()->apellidos);
    $contextoCiclo = (optional($ciclo->programa)->nombre ?? '').' · Ciclo '.$ciclo->nombre;
@endphp
<div class="container-fluid docente-ui-page pk-al px-2 px-md-3 pb-5">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Tutor · ' . (optional($ciclo->programa)->nombre ?? ''),
        'title'     => 'Ciclo ' . $ciclo->nombre,
        'subtitle'  => 'Cómo van tus alumnos en sus cursos, quién está desaprobando, y su contacto y ficha.',
        'backUrl'   => route('tutor.dashboard'),
        'backLabel' => 'Mis ciclos',
    ])

    @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $tipo)
        @if (session($clave))
            <div class="alert alert-{{ $tipo }} alert-dismissible fade show shadow-sm mb-3" role="alert">
                {{ session($clave) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            El mensaje no se envió: {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @include('alumnos.partials.ui')
    @php $enRiesgo = $seguimiento['resumen']['en_riesgo']; @endphp
    <div>
    <div class="al-ui al-tabs" role="tablist" aria-label="Secciones del ciclo" data-al-tabs>
        <button type="button" class="al-tab is-activa" role="tab" data-panel="seguimiento">
            <i class="fas fa-chart-line"></i> Seguimiento<span class="d-none d-sm-inline"> académico</span>
            @if ($enRiesgo) <span class="al-badge is-mal" title="Alumnos desaprobando algún curso">{{ $enRiesgo }}</span> @endif
        </button>
        <button type="button" class="al-tab" role="tab" data-panel="alumnos">
            <i class="fas fa-users"></i> Alumnos<span class="d-none d-sm-inline"> y contacto</span> <span class="al-badge is-muted">{{ $alumnos->count() }}</span>
        </button>
        <button type="button" class="al-tab" role="tab" data-panel="asistencia">
            <i class="fas fa-clipboard-check"></i> Asistencia<span class="d-none d-sm-inline"> del ciclo</span>
            @if (($resumenAsistencia['total_en_alerta'] ?? 0) > 0)
                <span class="al-badge is-mal" title="Alumnos en alerta por faltas">{{ $resumenAsistencia['total_en_alerta'] }}</span>
            @else
                <span class="al-badge is-muted">{{ $resumenAsistencia['total_sesiones'] ?? 0 }}</span>
            @endif
        </button>
    </div>

    <section class="al-ui al-panel" role="tabpanel" data-panel="seguimiento">
        @include('tutor.partials.seguimiento')
    </section>

    <section class="al-panel" role="tabpanel" data-panel="alumnos" hidden>
    @include('docentes.alumnos.partials.lista-panel', [
        'contactos' => $contactos->values(),
        'detalles'  => $alumnos->mapWithKeys(fn ($a) => ['fid-'.$a->id => $a])->all(),
        'titulo'    => 'Ciclo '.$ciclo->nombre,
        'subtitulo' => $contextoCiclo,
        'accion'    => route('tutor.mensaje', $ciclo->id),
        'firma'     => [
            'nombre' => mb_convert_case(mb_strtolower(explode(' ', trim($nombreTutor))[0]), MB_CASE_TITLE),
            'rol'    => 'tu tutor(a) del Ciclo '.$ciclo->nombre,
            'rolRef' => 'tutor(a)',
        ],
        'visible'   => true,
        'panelId'   => 'pkAlCiclo'.$ciclo->id,
    ])
    </section>

    <section class="al-panel" role="tabpanel" data-panel="asistencia" hidden>
        @include('tutor.partials.asistencia')
    </section>
    </div>{{-- /pestañas --}}
</div>

@include('docentes.alumnos.partials.modal-mensaje', [
    'remitenteNombre' => $nombreTutor,
    'remitenteCorreo' => $docenteTutor?->email ?: auth()->user()->email,
])

@if (session('activar_tab') === 'asistencia')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tab = document.querySelector('[data-al-tabs] [data-panel="asistencia"]');
    if (tab) tab.click();
});
</script>
@endif
@endsection
