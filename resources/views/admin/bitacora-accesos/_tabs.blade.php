{{-- Cabecera común de la Bitácora de accesos: hero + pestañas (enlaces a cada página). Va dentro de un .al-ui. --}}
@php $activo = $activo ?? request()->route()->getName(); @endphp
<section class="al-hero">
    <div class="al-hero-fila">
        <div class="al-hero-texto">
            <div class="al-kicker"><i class="fas fa-shield-alt"></i> Gestión interna · Seguridad</div>
            <h1>Bitácora de accesos</h1>
            <p class="al-hero-sub">{{ $sub }}</p>
        </div>
    </div>
</section>

<nav class="al-tabs" aria-label="Secciones de la bitácora">
    <a class="al-tab {{ $activo === 'bitacora.index' ? 'is-activa' : '' }}" href="{{ route('bitacora.index') }}" @if ($activo === 'bitacora.index') aria-current="page" @endif>
        <i class="fas fa-history"></i> Bitácora
    </a>
    <a class="al-tab {{ $activo === 'bitacora.activos' ? 'is-activa' : '' }}" href="{{ route('bitacora.activos') }}" @if ($activo === 'bitacora.activos') aria-current="page" @endif>
        <i class="fas fa-circle" style="font-size:.55rem;color:var(--al-ok)"></i> Conectados ahora
    </a>
    <a class="al-tab {{ $activo === 'bitacora.fallidos' ? 'is-activa' : '' }}" href="{{ route('bitacora.fallidos') }}" @if ($activo === 'bitacora.fallidos') aria-current="page" @endif>
        <i class="fas fa-exclamation-triangle"></i> Intentos fallidos
    </a>
</nav>
