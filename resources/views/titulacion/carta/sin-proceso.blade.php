{{-- Formulario de la carta abierto por un estudiante que todavía no tiene proceso de titulación registrado
     (CartaAceptacionController::sinProceso). En lugar de un error, explica qué falta y qué hacer. --}}
@extends($layout)
@section('titulo', 'Carta de aceptación')
@section('contenido')
@include('alumnos.partials.ui')
@php
    $contactos = collect(config('titulacion.contactos', []))
        ->map(fn ($c) => [$c[0], $c[1], \App\Support\Telefono::whatsapp($c[1] ?? null, 'Hola, quiero solicitar la carta de aceptación para mi investigación, pero el sistema dice que no tengo un proceso de titulación registrado. Mi correo es '.$user->email.'.')])
        ->filter(fn ($c) => $c[2]);
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi intranet</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación</span>
                <h1>Carta de aceptación de la Institución Educativa</h1>
                <p class="al-hero-sub">Con esta carta la Institución Educativa autoriza que realices tu investigación allí. Se solicita cuando tu Plan de Trabajo de Investigación (PTI) ya está aprobado.</p>
            </div>
        </div>
    </section>

    <div class="al-card">
        <div class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-route"></i></span>
            <div>
                <h2>Todavía no tienes un proceso de titulación registrado</h2>
                <p>Hola, {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim((string) $user->name))) }}. En cuanto el Área de Investigación registre tu proceso, este formulario se habilitará aquí mismo, con tus datos ya completados.</p>
            </div>
        </div>
        <div class="al-card-cuerpo">
            <ol class="cr-pasos">
                <li>
                    <span class="cr-pasos__num">1</span>
                    <div><strong>Presenta tu Plan de Trabajo de Investigación (PTI)</strong>
                        <small>Con tu grupo y tu asesor(a), siguiendo la guía de la página de trámites.</small></div>
                </li>
                <li>
                    <span class="cr-pasos__num">2</span>
                    <div><strong>Recibe la RD que aprueba tu PTI</strong>
                        <small>Llega a tu correo institucional; con ella tu proceso queda registrado en el sistema.</small></div>
                </li>
                <li>
                    <span class="cr-pasos__num">3</span>
                    <div><strong>Vuelve aquí y solicita tu carta</strong>
                        <small>Adjunta el comprobante de pago (S/ 10). Secretaría te responde en 3 días hábiles.</small></div>
                </li>
            </ol>

            @unless ($institucional)
                <div class="al-aviso is-warn mt-3 mb-0"><i class="fas fa-envelope"></i><div>
                    Ingresaste con <strong>{{ $user->email }}</strong>. La solicitud se hace con tu correo institucional
                    <strong>&#64;{{ \App\Models\Titulacion\CartaAceptacion::DOMINIO }}</strong>: si tienes otra cuenta con ese correo, ingresa con ella.
                </div></div>
            @endunless

            <div class="al-aviso is-info mt-3 mb-0"><i class="fas fa-question-circle"></i><div>
                <strong>¿Ya tienes tu PTI aprobado y ves este mensaje?</strong> Puede que tu proceso todavía no esté vinculado a tu cuenta. Escríbenos y lo revisamos.
                @if ($contactos->isNotEmpty())
                    <div class="cr-contactos">
                        @foreach ($contactos as [$area, $numero, $wa])
                            <a href="{{ $wa }}"
                               target="_blank" rel="noopener" class="al-btn al-btn-borde al-btn-sm"><i class="fab fa-whatsapp"></i> {{ $area }} · {{ $numero }}</a>
                        @endforeach
                    </div>
                @endif
            </div></div>
        </div>
        <div class="al-form-pie">
            <a href="{{ $volver }}" class="al-btn al-btn-borde">Ir a mi intranet</a>
            <a href="{{ route('plan') }}" class="al-btn al-btn-oro"><i class="fas fa-book-open"></i> Cómo presentar mi PTI</a>
        </div>
    </div>
</div>
@endsection
