@php
    $docente = $docente ?? auth()->user()?->docente;
    $cursosDocente = $docente?->cursos ?? collect();
    $cursosAsincronicos = $docente?->cursosEspeciales ?? collect();
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="David Jesús Miranda">
    <title>@yield('titulo', 'Docente — Pukllasunchis')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/darkmode.css') }}?v={{ filemtime(public_path('admin/css/darkmode.css')) }}">
    <style>
        .docente-panel #content-wrapper {
            background-color: #f8f9fc;
        }

        .docente-panel .table-docente-cursos {
            min-width: 920px;
        }

        .docente-panel .sidebar .collapse-inner {
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.04);
        }

        /* ── Sidebar: sticky + scrollable solo cuando está EXPANDIDO ──
           overflow-x/y en un mismo elemento crea un clipping context que corta
           los flyout menus (.collapse position:absolute) del sidebar colapsado.
           Con :not(.toggled) solo aplicamos overflow cuando SB Admin 2 NO muestra flyouts. ── */
        @media (min-width: 768px) {

            /* z-index en flex item → stacking context, sidebar siempre sobre #content-wrapper */
            #accordionSidebar {
                z-index: 100;
            }

            /* Expandido: sticky + scroll vertical para menús largos.
               SIN overflow-x:hidden — en Windows la barra de scroll ocupa ~17px y recortaría
               los chevrons ::after que flotan al extremo derecho del nav-link. */
            #accordionSidebar:not(.toggled) {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
            }

            /* Nav-links al 100% del ancho disponible (incluye espacio del scrollbar),
               evita que desborden horizontalmente cuando hay scrollbar clásico */
            #accordionSidebar:not(.toggled) .nav-item .nav-link {
                width: 100%;
            }

            /* Chevrons: forzar visibilidad en sidebar expandido */
            #accordionSidebar:not(.toggled) .nav-item .nav-link[data-toggle="collapse"]::after {
                display: inline-block !important;
                float: right;
                width: 1rem;
                text-align: center;
                content: '\f105';
                font-family: 'Font Awesome 5 Free';
                font-weight: 900;
                color: rgba(255, 255, 255, 0.55) !important;
            }

            #accordionSidebar:not(.toggled) .nav-item .nav-link[data-toggle="collapse"]:not(.collapsed)::after {
                content: '\f107';
                color: rgba(255, 255, 255, 0.85) !important;
            }

            /* Colapsado: SB Admin 2 maneja overflow:visible para los flyouts */
            #accordionSidebar.toggled {
                overflow: visible !important;
            }

            /* En modo colapsado el sidebar NO muestra chevrons (icono-only, SB Admin 2 by design) */
            #accordionSidebar.toggled .nav-item .nav-link[data-toggle="collapse"]::after {
                display: none !important;
            }
        }

        #accordionSidebar::-webkit-scrollbar {
            width: 4px;
        }

        #accordionSidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #accordionSidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        /* ── Topbar: horario siempre en la misma línea que tema/perfil ──
           El topbar nunca envuelve a una segunda fila: hamburguesa, botón de
           horario, cambio de tema y perfil conviven en una sola línea en
           cualquier ancho. El botón de horario se encoge y trunca su propio
           texto (en vez de forzar su propia fila, que antes desbordaba el
           topbar y quedaba superpuesto sobre el contenido). ── */
        .topbar {
            flex-wrap: nowrap !important;
        }

        #sidebarToggleTop {
            flex-shrink: 0;
        }

        .docente-topbar-horario {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }

        .docente-topbar-horario .btn,
        .docente-topbar-horario>span {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar .navbar-nav.ml-auto {
            flex-shrink: 0;
        }

        @media (max-width: 575.98px) {
            .docente-topbar-horario .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.72rem;
            }
        }

        /* Sistema visual unificado — panel docente */
        .docente-ui-page {
            padding: 0.5rem 0.5rem 2.5rem;
        }

        @media (min-width: 768px) {
            .docente-ui-page {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        .docente-ui-kicker {
            font-size: 0.68rem;
            letter-spacing: 0.045em;
            text-transform: uppercase;
            font-weight: 700;
            color: #858796;
        }

        .docente-ui-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #5a5c69;
            line-height: 1.3;
        }

        .docente-ui-subtitle {
            font-size: 0.875rem;
            color: #858796;
            max-width: 42rem;
        }

        .docente-ui-card {
            border: 0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12) !important;
            border-radius: 0.35rem;
            background: #fff;
        }

        .docente-ui-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
        }

        /* Tarjeta "hero" — mismo lenguaje visual (.docente-ui-card) más un
           acento sutil a la izquierda. Usada en el encabezado de TODAS las
           vistas del docente (vía ui-header.blade.php) para que compartan
           un único diseño. */
        .docente-ui-hero {
            border-left: 3px solid #4e73df;
        }

        .docente-ui-legenda {
            font-size: 0.8125rem;
            color: #5a5c69;
            line-height: 1.6;
        }

        .docente-ui-legenda .legenda-item {
            white-space: nowrap;
        }

        .docente-ui-accordion .card {
            border: 0;
            box-shadow: 0 0.1rem 1rem 0 rgba(58, 59, 69, 0.1);
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .docente-ui-accordion .card-header {
            background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 0;
        }

        .docente-ui-accordion .card-header .btn-link {
            color: #fff !important;
            text-decoration: none;
            width: 100%;
            text-align: left;
            padding: 0.85rem 1rem;
            white-space: normal;
            font-weight: 600;
        }

        .docente-ui-accordion .card-header .btn-link:hover,
        .docente-ui-accordion .card-header .btn-link:focus {
            color: #f8f9fc !important;
            text-decoration: none;
        }

        .docente-ui-accordion .card-body {
            padding: 0;
        }

        .docente-ui-accordion .table {
            margin-bottom: 0;
        }

        .docente-ui-accordion .table thead th {
            border-top: none;
        }

        /* Modal foto alumno (evita colisión con .modal de Bootstrap) */
        .docente-photo-modal-overlay {
            display: none;
            position: fixed;
            z-index: 1060;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.82);
            align-items: center;
            justify-content: center;
        }

        .docente-photo-modal-overlay.is-open {
            display: flex;
        }

        .docente-photo-modal-inner {
            position: relative;
            max-width: min(92vw, 720px);
            padding: 1rem;
        }

        .docente-photo-modal-inner img {
            max-width: 100%;
            height: auto;
            border-radius: 0.35rem;
            border: 4px solid #fff;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.35);
        }

        .docente-photo-modal-close {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            cursor: pointer;
            font-size: 1.75rem;
            line-height: 1;
            color: #fff;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            padding: 0.25rem 0.5rem;
            border: none;
            background: transparent;
        }

        .docente-photo-modal-close:hover {
            color: #f8f9fc;
        }

        .docente-ui-table-wide {
            min-width: 720px;
        }

    </style>
    {{-- Sistema de diseño compartido: tokens (colores por rol/tema) y menú lateral pk-sb --}}
    <link rel="stylesheet" href="{{ asset('css/pk-tokens.css') }}?v={{ filemtime(public_path('css/pk-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pk-sidebar.css') }}?v={{ filemtime(public_path('css/pk-sidebar.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/puklla-alert.css') }}?v={{ filemtime(public_path('css/puklla-alert.css')) }}">
</head>

<body id="page-top" class="docente-panel pk-rol-docente">
    <script>
        if (localStorage.getItem('puklla-theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
    <div id="wrapper">
        @php
            // ── Menú lateral docente: secciones por función, estado activo y roles del usuario ──
            $usuarioSb = auth()->user();
            $nombreSb = trim(($usuarioSb->name ?? '').' '.($usuarioSb->apellidos ?? '')) ?: ($docente->nombre ?? 'Docente');
            $inicialesSb = collect(preg_split('/\s+/', trim(($usuarioSb->name ?? '').' '.($usuarioSb->apellidos ?? ''))))
                ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'D';
            // Proyectos Integradores a cargo: solo cuando Taq'e está instalado (ruta presente)
            $proyectosSb = Route::has('taqe.proyecto.index') && $docente && method_exists($docente, 'proyectosIntegradores')
                ? $docente->proyectosIntegradores()->orderBy('nombre')->get()
                : collect();
            $esTutorSb = $usuarioSb?->hasRole('tutor') && Route::has('tutor.dashboard');
            $esInvestigadorSb = $usuarioSb?->hasRole('investigador') && Route::has('taqe.investigador.index');
            $esTitulacionSb = $usuarioSb?->hasRole('gestor-titulacion') && Route::has('titulacion.index');
            $esBolsaSb = $usuarioSb?->hasRole('adminB');
            $esSeguimientoSb = Route::has('seguimiento-egresados.index');
            $tieneFuncionesSb = $esTutorSb || $esInvestigadorSb || $esTitulacionSb || $esBolsaSb || $esSeguimientoSb || $proyectosSb->isNotEmpty();
        @endphp
        <ul class="navbar-nav sidebar sidebar-dark accordion pk-sb" id="accordionSidebar">
            <a class="pk-sb-marca" href="{{ route('index') }}" title="Ir a la página principal">
                <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Logo Pukllasunchis">
                <span class="pk-sb-area">Área docente</span>
            </a>

            {{-- Tarjeta del usuario: lleva al perfil --}}
            @if ($docente)
                <a class="pk-sb-perfil {{ request()->routeIs('docente.show') ? 'is-activo' : '' }}" href="{{ route('docente.show', $docente->id) }}" title="Mi perfil">
                    <span class="pk-sb-avatar">{{ $inicialesSb }}</span>
                    <span class="pk-sb-perfil-texto">
                        <strong>{{ $nombreSb }}</strong>
                        <small>Ver mi perfil</small>
                    </span>
                </a>
            @endif

            {{-- ── Docencia ── --}}
            @if ($docente)
            <div class="pk-sb-titulo">Docencia</div>
            <li class="nav-item">
                <a class="nav-link pk-sb-link {{ request()->routeIs('vistaDocente', 'competencias.calificar*', 'docente.curso.*', 'calificaciones.*', 'docente.asistencia.*') ? 'is-activo' : '' }}"
                    href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" title="Mis cursos">
                    <span class="pk-sb-icono"><i class="fas fa-book"></i></span>
                    <span class="pk-sb-texto">Mis cursos</span>
                    @if ($cursosDocente->count())
                        <span class="pk-sb-cuenta">{{ $cursosDocente->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link pk-sb-link {{ request()->routeIs('docente.alumnos*') ? 'is-activo' : '' }}"
                    href="{{ route('docente.alumnos', ['docente' => $docente->id]) }}" title="Mis alumnos">
                    <span class="pk-sb-icono"><i class="fas fa-user-graduate"></i></span>
                    <span class="pk-sb-texto">Mis alumnos</span>
                </a>
            </li>
            @if ($cursosAsincronicos->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link pk-sb-link {{ request()->routeIs('ce.docente.*') ? 'is-activo' : '' }}"
                        href="{{ route('ce.docente.index') }}" title="Cursos asincrónicos">
                        <span class="pk-sb-icono"><i class="fas fa-graduation-cap"></i></span>
                        <span class="pk-sb-texto">Cursos asincrónicos</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link pk-sb-link {{ request()->routeIs('repositorio') ? 'is-activo' : '' }}"
                    href="{{ route('repositorio', $docente->id) }}" title="Repositorio de sílabos">
                    <span class="pk-sb-icono"><i class="fas fa-file-pdf"></i></span>
                    <span class="pk-sb-texto">Repositorio de sílabos</span>
                </a>
            </li>
            @endif

            {{-- ── Funciones adicionales según los roles de la cuenta ── --}}
            @if ($tieneFuncionesSb)
                <div class="pk-sb-titulo">Mis funciones</div>
                @if ($esTutorSb)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link {{ request()->routeIs('tutor.*') ? 'is-activo' : '' }}" href="{{ route('tutor.dashboard') }}" title="Tutoría">
                            <span class="pk-sb-icono"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span class="pk-sb-texto">Tutoría</span>
                        </a>
                    </li>
                @endif
                @if ($esInvestigadorSb)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link {{ request()->routeIs('taqe.investigador.*') ? 'is-activo' : '' }}" href="{{ route('taqe.investigador.index') }}" title="Taq'e — Seguimiento">
                            <span class="pk-sb-icono"><i class="fas fa-microscope"></i></span>
                            <span class="pk-sb-texto">Taq'e — Seguimiento</span>
                        </a>
                    </li>
                @endif
                @foreach ($proyectosSb as $pi)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link {{ request()->routeIs('taqe.proyecto.index') && (int) optional(request()->route('proyecto'))->id === $pi->id ? 'is-activo' : '' }}"
                            href="{{ route('taqe.proyecto.index', $pi) }}" title="Proyecto Integrador: {{ $pi->nombre }}">
                            <span class="pk-sb-icono"><i class="fas fa-project-diagram"></i></span>
                            <span class="pk-sb-texto">
                                {{ \Illuminate\Support\Str::limit($pi->nombre, 26) }}
                                <small>{{ $pi->pivot->rol === 'encargado' ? 'Encargado' : 'Organizador' }} · Proyecto Integrador</small>
                            </span>
                        </a>
                    </li>
                @endforeach
                @if ($esTitulacionSb)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link {{ request()->routeIs('titulacion.*') ? 'is-activo' : '' }}" href="{{ route('titulacion.index') }}" title="Proceso de titulación">
                            <span class="pk-sb-icono"><i class="fas fa-award"></i></span>
                            <span class="pk-sb-texto">Proceso de titulación</span>
                        </a>
                    </li>
                @endif
                @if ($esBolsaSb)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link collapsed {{ request()->routeIs('bolsa-trabajo.*') ? 'is-activo' : '' }}" href="#" data-toggle="collapse" data-target="#sBolsaDocente"
                            aria-expanded="false" aria-controls="sBolsaDocente" title="Bolsa de trabajo">
                            <span class="pk-sb-icono"><i class="fas fa-briefcase"></i></span>
                            <span class="pk-sb-texto">Bolsa de trabajo</span>
                        </a>
                        <div id="sBolsaDocente" class="collapse" data-parent="#accordionSidebar">
                            <div class="py-2 collapse-inner rounded">
                                <h6 class="collapse-header">Ofertas y convocatorias</h6>
                                <a class="collapse-item" href="{{ route('bolsa-trabajo.ofertas.index') }}">
                                    <i class="fas fa-list fa-xs mr-1 text-muted"></i> Registros y filtros
                                </a>
                                <a class="collapse-item" href="{{ route('bolsa') }}" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt fa-xs mr-1 text-muted"></i> Ver página pública
                                </a>
                            </div>
                        </div>
                    </li>
                @endif
                @if ($esSeguimientoSb)
                    <li class="nav-item">
                        <a class="nav-link pk-sb-link {{ request()->routeIs('seguimiento-egresados.*') ? 'is-activo' : '' }}" href="{{ route('seguimiento-egresados.index') }}" title="Sistema de seguimiento de egresados">
                            <span class="pk-sb-icono"><i class="fas fa-user-graduate"></i></span>
                            <span class="pk-sb-texto">Sistema de seguimiento de egresados</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- ── Recursos ── --}}
            <div class="pk-sb-titulo">Recursos</div>
            <li class="nav-item">
                <a class="nav-link pk-sb-link" target="_blank" rel="noopener"
                    href="https://pukllavirtual.eesppukllasunchis.edu.pe/" title="Biblioteca virtual (abre en otra pestaña)">
                    <span class="pk-sb-icono"><i class="fas fa-book-reader"></i></span>
                    <span class="pk-sb-texto">Biblioteca virtual <small>Nueva</small></span>
                    <i class="fas fa-external-link-alt pk-sb-externo"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link pk-sb-link" target="_blank" rel="noopener"
                    href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio" title="Biblioteca anterior en Google Sites (abre en otra pestaña)">
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
                    onclick="event.preventDefault(); document.getElementById('logout-form-docente').submit();">
                    <span class="pk-sb-icono"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="pk-sb-texto">Cerrar sesión</span>
                </a>
                <div class="text-center d-none d-md-block">
                    <button class="rounded-circle border-0" id="sidebarToggle" type="button"
                        aria-label="Contraer menú lateral"></button>
                </div>
            </div>

            <form id="logout-form-docente" action="{{ route('logout') }}" method="POST" class="d-none"
                aria-hidden="true">
                @csrf
            </form>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav
                    class="navbar navbar-expand navbar-light bg-white topbar mb-3 mb-md-4 static-top shadow align-items-center py-2">
                    <button id="sidebarToggleTop" type="button" class="btn btn-link d-md-none rounded-circle mr-2"
                        aria-label="Abrir menú">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="navbar-nav flex-row align-items-center mr-2 docente-topbar-horario">
                        @if (isset($periodoActual) && $periodoActual && $periodoActual->horario)
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#modalHorario">
                                <i class="far fa-calendar-alt mr-1"></i>
                                <span class="docente-topbar-horario-label">Horario FID</span>
                                <span class="d-none d-md-inline"> — {{ $periodoActual->nombre }}</span>
                            </button>
                        @elseif(isset($periodoActual) && $periodoActual)
                            <span class="small text-muted my-1"><i class="far fa-calendar-times mr-1"></i>Sin horario
                                cargado para {{ $periodoActual->nombre }}</span>
                        @else
                            <span class="small text-muted my-1 d-none d-md-inline">Sin periodo académico activo</span>
                        @endif
                    </div>

                    <ul class="navbar-nav ml-auto flex-row align-items-center">
                        <li class="nav-item d-flex align-items-center">
                            <button id="darkModeToggle" type="button" title="Cambiar a modo oscuro"
                                aria-label="Cambiar tema claro/oscuro">
                                <i class="fas fa-moon" id="darkModeIcon"></i>
                            </button>
                        </li>
                        @include('docentes.partials.notificaciones-dropdown')
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle text-truncate docente-user-name" href="#"
                                id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" style="max-width: 14rem;">
                                @if (Auth::check() && Auth::user())
                                    <span class="d-none d-sm-inline">{{ Auth::user()->name }}
                                        {{ Auth::user()->apellidos }}</span>
                                    <span class="d-sm-none"><i
                                            class="fas fa-user-circle fa-lg text-gray-600"></i></span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                @if ($docente)
                                    <a class="dropdown-item" href="{{ route('docente.show', $docente->id) }}">
                                        <i class="fas fa-id-card fa-sm fa-fw mr-2 text-gray-400"></i> Mi perfil
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form-docente').submit();">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Cerrar sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                @if (isset($periodoActual) && $periodoActual && $periodoActual->horario)
                    <div class="modal fade" id="modalHorario" tabindex="-1" role="dialog"
                        aria-labelledby="modalHorarioLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header py-2">
                                    <h5 class="modal-title" id="modalHorarioLabel">Horario
                                        {{ $periodoActual->nombre }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center bg-light">
                                    <img src="{{ asset($periodoActual->horario) }}"
                                        alt="Horario {{ $periodoActual->nombre }}" class="img-fluid rounded shadow"
                                        loading="lazy">
                                </div>
                                <div class="modal-footer justify-content-center flex-wrap">
                                    <button type="button" class="btn btn-secondary btn-sm mb-1 mb-sm-0"
                                        data-dismiss="modal">Cerrar</button>
                                    <a href="{{ asset($periodoActual->horario) }}" target="_blank"
                                        rel="noopener noreferrer" class="btn btn-primary btn-sm">
                                        Ver en nueva pestaña
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @yield('contenido')
            </div>
            @include('layouts.partials.pie-intranet', ['area' => 'Área docente'])
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script>
        function openTextEditor() {
            document.getElementById("textEditorModalNew").style.display = "flex";
            CKEDITOR.replace('editorNew'); // Inicializa el editor de texto con el nuevo ID
        }

        function closeTextEditor() {
            document.getElementById("textEditorModalNew").style.display = "none";
            CKEDITOR.instances.editorNew.destroy(); // Destruye el editor al cerrar
        }

        function closeModalOnBackgroundClick(event) {
            // Verificar si el clic fue fuera del contenido del modal
            if (event.target.classList.contains('modal-editor')) {
                closeTextEditor();
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
    @include('partials.ckeditor-theme-sync')
    <script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-pie-demo.js') }}"></script>
    <script src="{{ asset('admin/js/djm.js') }}?v={{ filemtime(public_path('admin/js/djm.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/puklla-alert.js') }}?v={{ filemtime(public_path('js/puklla-alert.js')) }}"></script>
    @stack('scripts')
    
    <script>
        (function() {
            var THEME_KEY = 'puklla-theme';
            var body = document.body;
            var toggle = document.getElementById('darkModeToggle');
            var icon = document.getElementById('darkModeIcon');

            function syncUI() {
                var dark = body.classList.contains('dark-mode');
                if (icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
                if (toggle) toggle.setAttribute('title', dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            }

            syncUI();

            if (toggle) {
                toggle.addEventListener('click', function() {
                    var nowDark = body.classList.toggle('dark-mode');
                    localStorage.setItem(THEME_KEY, nowDark ? 'dark' : 'light');
                    syncUI();
                });
            }
        })();
    </script>

</body>

</html>
