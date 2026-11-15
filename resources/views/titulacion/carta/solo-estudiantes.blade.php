{{-- El formulario de la carta abierto por personal de la institución (admin, docente, tutor, Secretaría,
     Investigación…) sin proceso de titulación propio (CartaAceptacionController::sinProceso). Página simple:
     explica para quién es y lo devuelve a su panel; quien atiende las cartas ve también el acceso a Registros. --}}
@extends('titulacion.carta._simple')
@section('titulo', 'Carta de aceptación')
@section('contenido')
@include('alumnos.partials.ui')
<div class="al-ui cr cr-solo">
    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación</span>
                <h1>Carta de aceptación de la Institución Educativa</h1>
                <p class="al-hero-sub">Formulario con el que los estudiantes, con su PTI aprobado, solicitan a Secretaría la carta para la Institución Educativa donde harán su investigación.</p>
            </div>
        </div>
    </section>

    <div class="al-card">
        <div class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-user-graduate"></i></span>
            <div>
                <h2>Este formulario es para estudiantes</h2>
                <p>Hola, {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim((string) $user->name))) }}. Ingresaste con una cuenta del personal de la institución, que no tiene un proceso de titulación propio.</p>
            </div>
        </div>
        <div class="al-card-cuerpo">
            <div class="al-aviso is-info mb-0"><i class="fas fa-info-circle"></i><div>
                Cada estudiante lo llena con <strong>su cuenta institucional</strong> después de que su Plan de Trabajo de Investigación tiene la RD de aprobación.
                @if ($puedeRegistros)
                    Las solicitudes que envían llegan a <strong>Registros de cartas</strong>, donde Secretaría las revisa y responde.
                @else
                    Si un estudiante te consulta, compártele el enlace de esta página.
                @endif
            </div></div>
        </div>
        <div class="al-form-pie">
            @if ($puedeRegistros)
                <a href="{{ route('titulacion.cartas.index') }}" class="al-btn al-btn-borde"><i class="fas fa-list-ul"></i> Ver solicitudes de carta</a>
            @endif
            <a href="{{ $panel }}" class="al-btn al-btn-oro"><i class="fas fa-arrow-left"></i> Volver a mi panel</a>
        </div>
    </div>
</div>
@endsection
