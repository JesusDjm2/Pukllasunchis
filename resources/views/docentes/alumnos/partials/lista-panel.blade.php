{{-- Tarjeta con la lista de alumnos de un curso (docente) o de un ciclo (tutor).
     Espera: $contactos (Collection<ContactoAlumno>), $titulo, $subtitulo, $accion (URL de envío),
     $puedeEscribir, $firma (array, ver fila-contacto), $visible (bool), $panelId; opcional $etiquetaTipo (FID/PPD). --}}
@php
    $conCorreo = $contactos->filter(fn ($c) => $c->correo)->count();
    $conCelular = $contactos->filter(fn ($c) => $c->numero)->count();
@endphp
<div class="pk-al-card pk-al-panel {{ $visible ? '' : 'd-none' }}" id="{{ $panelId }}"
    data-accion="{{ $accion }}" data-titulo="{{ $titulo }}">
    <div class="pk-al-head">
        <h2 class="pk-al-titulo">
            {{ $titulo }}
            @if (!empty($etiquetaTipo))
                <span class="pk-al-tipo ml-1 {{ $etiquetaTipo === 'PPD' ? 'is-ppd' : 'is-fid' }}"><i class="fas {{ $etiquetaTipo === 'PPD' ? 'fa-chalkboard-teacher' : 'fa-graduation-cap' }}"></i> {{ $etiquetaTipo }}</span>
            @endif
        </h2>
        <p class="pk-al-subtitulo">{{ $subtitulo }}</p>
        <div class="pk-al-stats">
            <span class="pk-al-stat"><i class="fas fa-users"></i>{{ $contactos->count() }} {{ $contactos->count() === 1 ? 'alumno' : 'alumnos' }}</span>
            <span class="pk-al-stat"><i class="fas fa-envelope"></i>{{ $conCorreo }} con correo</span>
            <span class="pk-al-stat"><i class="fas fa-mobile-alt"></i>{{ $conCelular }} con celular</span>
        </div>
    </div>

    @if ($contactos->isEmpty())
        <div class="pk-al-sin-resultados">
            <i class="fas fa-user-slash"></i>
            <strong>No hay alumnos en esta lista</strong>
            <div class="small mt-1">Cuando haya alumnos matriculados aparecerán aquí.</div>
        </div>
    @else
        <div class="pk-al-toolbar">
            @if ($puedeEscribir)
                <label class="pk-al-todos mb-0 d-flex align-items-center" title="Elegir todos los visibles">
                    <input type="checkbox" class="pk-al-check-todos mr-2" style="width:1rem;height:1rem;accent-color:var(--al-acento);">
                    Todos
                </label>
            @endif
            <div class="pk-al-buscar">
                <i class="fas fa-search"></i>
                <input type="search" class="pk-al-buscar-input" placeholder="Buscar por nombre, correo o celular" aria-label="Buscar alumno">
            </div>
            @if ($puedeEscribir && $conCorreo > 0)
                <button type="button" class="btn btn-primary btn-sm ml-auto pk-al-btn-lista" onclick="pkAlEscribirLista(this)">
                    <i class="fas fa-paper-plane mr-1"></i>Escribir a todos
                </button>
            @endif
        </div>

        <div class="pk-al-lista">
            @foreach ($contactos as $c)
                @include('docentes.alumnos.partials.fila-contacto', ['c' => $c])
            @endforeach
            <div class="pk-al-sin-resultados pk-al-sin-coincidencias d-none">
                <i class="fas fa-search"></i>Ningún alumno coincide con la búsqueda.
            </div>
        </div>
    @endif
</div>
