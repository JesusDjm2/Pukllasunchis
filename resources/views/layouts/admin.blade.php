<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="David Jesús Miranda">
    <title>@yield('titulo')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/darkmode.css') }}?v={{ filemtime(public_path('admin/css/darkmode.css')) }}">
    {{-- Sistema de diseño compartido: tokens (colores por rol/tema) y menú lateral pk-sb --}}
    <link rel="stylesheet" href="{{ asset('css/pk-tokens.css') }}?v={{ filemtime(public_path('css/pk-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pk-sidebar.css') }}?v={{ filemtime(public_path('css/pk-sidebar.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/puklla-alert.css') }}?v={{ filemtime(public_path('css/puklla-alert.css')) }}">
</head>

<body id="page-top" class="{{ auth()->user()?->hasRole('super-admin') ? 'pk-rol-superadmin' : (auth()->user()?->hasAnyRole(['admin', 'adminB', 'gestor-titulacion', 'tutor']) ? 'pk-rol-admin' : (auth()->user()?->hasRole('docente') ? 'pk-rol-docente' : '')) }}">
    <script>
        var _pt = localStorage.getItem('puklla-theme');
        if (_pt === 'dark') document.body.classList.add('dark-mode');
        else if (_pt === 'dim') document.body.classList.add('dim-mode');
    </script>
    <div id="wrapper">
        <div id="preloader">
            <img class="pl-logo" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Pukllasunchis">
            <p class="pl-name">Pukllasunchis</p>
            <p class="pl-tagline">Sistema de Gestión</p>
            <div class="pl-dots"><span></span><span></span><span></span></div>
            <div class="pl-bar"></div>
        </div>
        @hasanyrole('super-admin|admin|docente|adminB|tutor|gestor-titulacion')
            {{-- Menú lateral pk-sb compartido con layouts.superadmin (color por rol: pk-tokens.css) --}}
            @php
                $uSb = auth()->user();
                [$areaSb, $iconoSb] = match (true) {
                    $uSb->hasRole('super-admin') => ['Super Admin', 'fa-crown'],
                    $uSb->hasRole('admin') => ['Administración', 'fa-shield-alt'],
                    $uSb->hasRole('gestor-titulacion') => ['Titulación', 'fa-user-graduate'],
                    $uSb->hasRole('adminB') => ['Bolsa de trabajo', 'fa-briefcase'],
                    $uSb->hasRole('tutor') => ['Tutoría', 'fa-chalkboard-teacher'],
                    default => ['Área docente', null],
                };
            @endphp
            @include('layouts.partials.menu-lateral-gestion', ['area' => $areaSb, 'iconoArea' => $iconoSb])
        @endhasanyrole

        @role('alumno')
            <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Logo Pukllasunchis"
                            class="sidebar-logo-img">
                    </div>
                </a>
                <hr class="sidebar-divider sidebar-logo-divider">
                <div class="sidebar-heading">
                    Dashboard Alumno
                </div>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('alumnos.index') }}">
                        <i class="fas fa-fw fa-newspaper"></i>
                        <span>Ficha técnica</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('calificaciones', $alumno->id) }}">
                        <i class="fas fa-fw fa-newspaper"></i>
                        <span>Calificaciones</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('alumno.comunicados') ? 'active' : '' }}">
                    <a class="nav-link collapsed" href="{{ route('alumno.comunicados') }}">
                        <i class="fas fa-fw fa-bullhorn"></i>
                        <span>Comunicados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('postulante.index') }}">
                        <i class="fas fa-fw fa-money-bill"></i>
                        <span>Bolsa de trabajo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('index') }}">
                        <i class="fas fa-fw fa-graduation-cap"></i>
                        <span>Regresar a la página</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
        @endrole
        @role('alumnoB')
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Logo Pukllasunchis"
                            class="sidebar-logo-img">
                    </div>
                </a>
                <hr class="sidebar-divider sidebar-logo-divider">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('ppd.index') }}">
                        <i class="fas fa-cog"></i>
                        <span>Ficha Técnica</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                @if ($alumno)
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('calificacionesppd', $alumno->id) }}">
                            <i class="fas fa-fw fa-book"></i>
                            <span>Calificaciones</span>
                        </a>
                    </li>
                    <hr class="sidebar-divider d-none d-md-block">
                @endif
                <li class="nav-item">
                    <a class="nav-link collapsed" target="_blank"
                        href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio">
                        <i class="fas fa-book-open"></i>
                        <span>Biblioteca</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">

                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('index') }}">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Volver a la página</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
        @endrole
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    {{-- Hamburguesa (móvil) --}}
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-2">
                        <i class="fa fa-bars fa-fw"></i>
                    </button>

                    {{-- Acceso rápido a la web pública (oculto en xs) --}}
                    @role('super-admin')
                        {{-- Super Admin: botón premium con identidad visual SA --}}
                        <a href="{{ route('index') }}" class="sa-home-btn d-none d-md-inline-flex ml-2"
                            title="Volver a la página principal">
                            <i class="fas fa-home fa-sm"></i>
                            <span>Página principal</span>
                        </a>
                    @else
                        @hasanyrole('admin|adminB|tutor')
                            {{-- Admin: botón con identidad visual azul --}}
                            <a href="{{ route('index') }}" class="adm-home-btn d-none d-md-inline-flex ml-2"
                                title="Volver a la página principal">
                                <i class="fas fa-home fa-sm"></i>
                                <span>Página principal</span>
                            </a>
                        @endhasanyrole
                    @endrole

                    {{-- Navegación derecha --}}
                    <ul class="navbar-nav ml-auto align-items-center">
                        <li class="nav-item d-flex align-items-center">
                            <button id="darkModeToggle" type="button" title="Cambiar a modo oscuro"
                                aria-label="Cambiar tema claro/oscuro">
                                <i class="fas fa-moon" id="darkModeIcon"></i>
                            </button>
                        </li>
                        @include('docentes.partials.notificaciones-dropdown')
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fas fa-user-circle fa-fw mr-1 text-gray-400 d-sm-none"></i>
                                <span class="d-none d-sm-inline mr-1" style="font-size:.85rem;">
                                    @if (Auth::check() && Auth::user())
                                        @php
                                            $primerNombre = explode(' ', trim((string) Auth::user()->name))[0] ?? '';
                                            $primerApellido =
                                                explode(' ', trim((string) Auth::user()->apellidos))[0] ?? '';
                                        @endphp
                                        Hola {{ trim($primerNombre . ' ' . $primerApellido) }}!
                                    @endif
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown">
                                <div class="dropdown-header d-sm-none text-truncate px-3 py-2"
                                    style="font-size:.8rem;">
                                    @if (Auth::check() && Auth::user())
                                        @php
                                            $primerNombre = explode(' ', trim((string) Auth::user()->name))[0] ?? '';
                                            $primerApellido =
                                                explode(' ', trim((string) Auth::user()->apellidos))[0] ?? '';
                                        @endphp
                                        Hola {{ trim($primerNombre . ' ' . $primerApellido) }}!
                                    @endif
                                </div>
                                <div class="dropdown-divider d-sm-none"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Cerrar sesión') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                @yield('contenido')
            </div>
            @include('layouts.partials.pie-intranet', ['area' => 'Panel de administración'])
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pl = document.getElementById('preloader');
            if (!pl) return;
            pl.classList.add('pl-out');
            setTimeout(function() {
                pl.style.display = 'none';
            }, 90);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    {{-- PORCENTAJE --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/puklla-alert.js') }}?v={{ filemtime(public_path('js/puklla-alert.js')) }}"></script>
    <script>
        (function() {
            var THEME_KEY = 'puklla-theme';
            var body = document.body;
            var toggle = document.getElementById('darkModeToggle');
            var icon = document.getElementById('darkModeIcon');

            var CYCLE = ['light', 'dim', 'dark'];
            var ICONS = {
                light: 'fas fa-moon',
                dim: 'fas fa-adjust',
                dark: 'fas fa-sun'
            };
            var TITLES = {
                light: 'Modo tenue',
                dim: 'Modo oscuro',
                dark: 'Modo claro'
            };

            function getTheme() {
                if (body.classList.contains('dark-mode')) return 'dark';
                if (body.classList.contains('dim-mode')) return 'dim';
                return 'light';
            }

            function applyTheme(theme) {
                body.classList.remove('dark-mode', 'dim-mode');
                if (theme === 'dark') body.classList.add('dark-mode');
                if (theme === 'dim') body.classList.add('dim-mode');
                localStorage.setItem(THEME_KEY, theme);
                if (icon) icon.className = ICONS[theme];
                if (toggle) toggle.setAttribute('title', TITLES[theme]);
            }

            applyTheme(getTheme());

            if (toggle) {
                toggle.addEventListener('click', function() {
                    var next = CYCLE[(CYCLE.indexOf(getTheme()) + 1) % CYCLE.length];
                    applyTheme(next);
                });
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>
