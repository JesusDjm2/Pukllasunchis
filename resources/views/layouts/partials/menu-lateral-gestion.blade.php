{{--
    Menú lateral (pk-sb) de los paneles de gestión: layouts.admin y layouts.superadmin.
    Mismo diseño que el del docente (public/css/pk-sidebar.css); el color lo pone el rol en
    <body class="pk-rol-…"> vía public/css/pk-tokens.css.
    Parámetros: $area (texto de la pastilla bajo el logo), $iconoArea (opcional, Font Awesome).
--}}
@php
    $usuarioSb = auth()->user();
    $nombreSb = trim(($usuarioSb->name ?? '').' '.($usuarioSb->apellidos ?? '')) ?: 'Usuario';
    $inicialesSb = collect(preg_split('/\s+/', $nombreSb))->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'U';
    $esTitulacionSb = $usuarioSb?->hasAnyRole(['gestor-titulacion', 'secretaria']) && Route::has('titulacion.index');
    $esTutorSb = $usuarioSb?->hasRole('tutor') && Route::has('tutor.dashboard');
@endphp
<ul class="navbar-nav sidebar sidebar-dark accordion pk-sb" id="accordionSidebar">
    <a class="pk-sb-marca" href="{{ route('index') }}" title="Ir a la página principal">
        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Logo Pukllasunchis">
        <span class="pk-sb-area">@if (! empty($iconoArea))<i class="fas {{ $iconoArea }} mr-1"></i>@endif{{ $area }}</span>
    </a>

    <div class="pk-sb-perfil" title="{{ $nombreSb }}">
        <span class="pk-sb-avatar">{{ $inicialesSb }}</span>
        <span class="pk-sb-perfil-texto">
            <strong>{{ $nombreSb }}</strong>
            <small>{{ $usuarioSb->email ?? '' }}</small>
        </span>
    </div>

    {{-- Menú de gestión (admin, super-admin, adminB): mismas opciones en ambos layouts --}}
    @include('layouts.partials.menu-gestion')

    {{-- ── Titulación (gestor-titulacion): un solo enlace; asesores, PTI por vencer y datos
         demográficos ya están en los botones e indicadores de la cabecera del módulo ── --}}
    @if ($esTitulacionSb)
        <div class="pk-sb-titulo">Titulación</div>
        <li class="nav-item">
            <a class="nav-link pk-sb-link {{ request()->routeIs('titulacion.*') ? 'is-activo' : '' }}"
                href="{{ route('titulacion.index') }}" title="Proceso de titulación">
                <span class="pk-sb-icono"><i class="fas fa-user-graduate"></i></span><span class="pk-sb-texto">Proceso de titulación</span>
            </a>
        </li>
    @endif

    {{-- ── Tutor ── --}}
    @if ($esTutorSb)
        <div class="pk-sb-titulo">Tutoría</div>
        <li class="nav-item">
            <a class="nav-link pk-sb-link {{ request()->routeIs('tutor.*') ? 'is-activo' : '' }}" href="{{ route('tutor.dashboard') }}" title="Panel de tutor">
                <span class="pk-sb-icono"><i class="fas fa-chalkboard-teacher"></i></span><span class="pk-sb-texto">Panel de tutor</span>
            </a>
        </li>
    @endif

    {{-- ── Recursos (mismos enlaces que el menú docente) ── --}}
    <div class="pk-sb-titulo">Recursos</div>
    <li class="nav-item">
        <a class="nav-link pk-sb-link" target="_blank" rel="noopener" href="https://pukllavirtual.eesppukllasunchis.edu.pe/" title="Biblioteca virtual (abre en otra pestaña)">
            <span class="pk-sb-icono"><i class="fas fa-book-reader"></i></span>
            <span class="pk-sb-texto">Biblioteca virtual <small>Nueva</small></span>
            <i class="fas fa-external-link-alt pk-sb-externo"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link pk-sb-link" target="_blank" rel="noopener" href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio" title="Biblioteca anterior en Google Sites (abre en otra pestaña)">
            <span class="pk-sb-icono"><i class="fas fa-book-open"></i></span>
            <span class="pk-sb-texto">Biblioteca <small>Google Sites</small></span>
            <i class="fas fa-external-link-alt pk-sb-externo"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link pk-sb-link" target="_blank" rel="noopener" href="{{ route('index') }}" title="Ir a la web (abre en otra pestaña)">
            <span class="pk-sb-icono"><i class="fas fa-globe-americas"></i></span>
            <span class="pk-sb-texto">Ir a la web</span>
            <i class="fas fa-external-link-alt pk-sb-externo"></i>
        </a>
    </li>

    {{-- ── Pie: cerrar sesión y contraer ── --}}
    <div class="pk-sb-pie nav-item">
        <a class="nav-link pk-sb-link pk-sb-salir" href="{{ route('logout') }}" title="Cerrar sesión"
            onclick="event.preventDefault(); document.getElementById('pk-sb-logout').submit();">
            <span class="pk-sb-icono"><i class="fas fa-sign-out-alt"></i></span>
            <span class="pk-sb-texto">Cerrar sesión</span>
        </a>
        <div class="text-center d-none d-md-block">
            <button class="rounded-circle border-0" id="sidebarToggle" type="button" aria-label="Contraer menú lateral"></button>
        </div>
    </div>
    <form id="pk-sb-logout" action="{{ route('logout') }}" method="POST" class="d-none" aria-hidden="true">
        @csrf
    </form>
</ul>
