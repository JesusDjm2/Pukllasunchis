{{--
    Barra superior compartida (pk-topbar) de los paneles docente, admin y super admin.
    Estilos en public/css/pk-topbar.css; el color de acento sale de pk-tokens.css según el rol
    (body.pk-rol-*). Conserva .topbar para que darkmode.css la pinte en los temas oscuro y tenue,
    y los ids darkModeToggle / darkModeIcon que usa el script de tema de cada layout.

    Parámetros:
      $area        texto del rol/área (p. ej. "Super Admin")
      $idLogout    id del <form> de cierre de sesión (por defecto "pk-tb-logout")
      $perfilUrl   (opcional) enlace a "Mi perfil"
      $conHorario  (opcional) muestra el botón de horario del periodo (panel docente)
--}}
@php
    $usuarioTb = auth()->user();
    $nombreTb = trim(($usuarioTb->name ?? '').' '.($usuarioTb->apellidos ?? ''));
    $nombreCortoTb = trim((explode(' ', trim((string) ($usuarioTb->name ?? '')))[0] ?? '').' '.(explode(' ', trim((string) ($usuarioTb->apellidos ?? '')))[0] ?? ''));
    $inicialesTb = collect(preg_split('/\s+/', $nombreTb))->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'U';
    $idLogout = $idLogout ?? 'pk-tb-logout';
    // Título de la página (el mismo @section('titulo') del <title>), sin sufijos tipo " — Docente"
    $tituloTb = trim(preg_replace('/\s+[—-]\s+(Docente|Super Admin.*|Admin.*)$/u', '', trim(View::yieldContent('titulo'))));
@endphp
<nav class="navbar navbar-expand navbar-light bg-white topbar static-top pk-topbar">
    <button id="sidebarToggleTop" type="button" class="btn btn-link d-md-none rounded-circle mr-1 pk-tb-hamburguesa" aria-label="Abrir menú">
        <i class="fa fa-bars"></i>
    </button>

    {{-- Contexto: área y página actual --}}
    <div class="pk-tb-contexto">
        <small>{{ $area }}</small>
        <strong>{{ $tituloTb !== '' ? $tituloTb : 'Inicio' }}</strong>
    </div>

    {{-- Acciones del área --}}
    <div class="pk-tb-acciones">
        @if (! empty($conHorario))
            @if (isset($periodoActual) && $periodoActual && $periodoActual->horario)
                <button type="button" class="pk-tb-btn is-destacado" data-toggle="modal" data-target="#modalHorario" title="Ver el horario del periodo">
                    <i class="far fa-calendar-alt"></i>
                    <span class="d-none d-sm-inline">Horario {{ $periodoActual->nombre }}</span>
                </button>
            @elseif (isset($periodoActual) && $periodoActual)
                <span class="pk-tb-nota d-none d-lg-inline"><i class="far fa-calendar-times"></i> Sin horario para {{ $periodoActual->nombre }}</span>
            @endif
        @endif
        <a href="{{ route('index') }}" class="pk-tb-btn d-none d-md-inline-flex" title="Ir a la página principal">
            <i class="fas fa-home"></i><span>Página principal</span>
        </a>
    </div>

    <ul class="navbar-nav ml-auto align-items-center pk-tb-derecha">
        <li class="nav-item d-flex align-items-center">
            <button id="darkModeToggle" type="button" title="Cambiar tema" aria-label="Cambiar tema">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
        </li>
        @include('docentes.partials.notificaciones-dropdown')
        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle pk-tb-usuario" href="#" id="pkTbUsuario" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ $nombreTb }}">
                <span class="pk-tb-avatar">{{ $inicialesTb }}</span>
                <span class="pk-tb-usuario-texto d-none d-lg-flex">
                    <b>{{ $nombreCortoTb }}</b>
                    <em>{{ $area }}</em>
                </span>
                <i class="fas fa-chevron-down pk-tb-flecha d-none d-lg-inline"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right animated--grow-in pk-tb-menu" aria-labelledby="pkTbUsuario">
                <div class="pk-tb-menu-cab">
                    <span class="pk-tb-avatar">{{ $inicialesTb }}</span>
                    <div>
                        <b>{{ $nombreTb }}</b>
                        <small>{{ $usuarioTb->email ?? '' }}</small>
                    </div>
                </div>
                @if (! empty($perfilUrl))
                    <a class="dropdown-item" href="{{ $perfilUrl }}"><i class="fas fa-id-card fa-fw"></i> Mi perfil</a>
                @endif
                <a class="dropdown-item" href="{{ route('index') }}"><i class="fas fa-home fa-fw"></i> Página principal</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item pk-tb-salir" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('{{ $idLogout }}').submit();">
                    <i class="fas fa-sign-out-alt fa-fw"></i> Cerrar sesión
                </a>
                <form id="{{ $idLogout }}" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>
