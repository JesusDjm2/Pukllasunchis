<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="David Jesús Miranda">
    <title>@yield('titulo') — Super Admin ⚡</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    {{-- Fuentes e iconos --}}
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
          rel="stylesheet">
    {{-- SB Admin 2 base --}}
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    {{-- Estilos globales + dark mode --}}
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/darkmode.css') }}?v={{ filemtime(public_path('admin/css/darkmode.css')) }}">
    {{-- Super Admin exclusivo (sin conflicto con admin.blade.php) --}}
    <link rel="stylesheet" href="{{ asset('admin/css/superadmin.css') }}">
    {{-- Sistema de diseño compartido: tokens (colores por rol/tema) y menú lateral pk-sb --}}
    <link rel="stylesheet" href="{{ asset('css/pk-tokens.css') }}?v={{ filemtime(public_path('css/pk-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pk-sidebar.css') }}?v={{ filemtime(public_path('css/pk-sidebar.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/puklla-alert.css') }}?v={{ filemtime(public_path('css/puklla-alert.css')) }}">
    @stack('styles')
</head>

<body id="page-top" class="pk-rol-superadmin">
{{-- Aplicar tema guardado antes del render para evitar flash --}}
<script>
    var _pt = localStorage.getItem('puklla-theme');
    if (_pt === 'dark') document.body.classList.add('dark-mode');
    else if (_pt === 'dim') document.body.classList.add('dim-mode');
</script>

<div id="wrapper">
    {{-- ══ PRELOADER ══ --}}
    <div id="preloader">
        <img class="pl-logo" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="Pukllasunchis">
        <p class="pl-name">Pukllasunchis</p>
        <p class="pl-tagline">Sistema de Gestión</p>
        <div class="pl-dots"><span></span><span></span><span></span></div>
        <div class="pl-bar"></div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         SIDEBAR — Solo para Super Admin
         Clase .sidebar-sa → sin conflicto con .bg-gradient-superadmin
    ══════════════════════════════════════════════════════════ --}}
    {{-- Menú lateral pk-sb compartido con layouts.admin (color por rol: pk-tokens.css) --}}
    @include('layouts.partials.menu-lateral-gestion', ['area' => 'Super Admin', 'iconoArea' => 'fa-crown'])
    {{-- ══ FIN SIDEBAR ══ --}}

    {{-- ══════════════════════════════════════════════════════
         CONTENT WRAPPER
    ══════════════════════════════════════════════════════════ --}}
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            {{-- ── TOPBAR ── --}}
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow topbar-sa">

                {{-- Hamburguesa (móvil) --}}
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-2">
                    <i class="fa fa-bars fa-fw"></i>
                </button>

                {{-- ── Botón Página principal ── visible desde md ── --}}
                <a href="{{ route('index') }}"
                   class="sa-home-btn d-none d-md-inline-flex ml-2"
                   title="Volver a la página principal">
                    <i class="fas fa-home fa-sm"></i>
                    <span>Página principal</span>
                </a>

                {{-- Navegación derecha --}}
                <ul class="navbar-nav ml-auto align-items-center">
                    {{-- Toggle de tema --}}
                    <li class="nav-item d-flex align-items-center">
                        <button id="darkModeToggle" type="button"
                                title="Cambiar a modo tenue"
                                aria-label="Cambiar tema claro/tenue/oscuro">
                            <i class="fas fa-moon" id="darkModeIcon"></i>
                        </button>
                    </li>
                    @include('docentes.partials.notificaciones-dropdown')
                    <div class="topbar-divider d-none d-sm-block"></div>

                    {{-- Dropdown de usuario --}}
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                           id="userDropdownSA" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle fa-fw mr-1 text-gray-400 d-sm-none"></i>
                            @if(Auth::check())
                                @php
                                    $saNombre   = explode(' ', trim((string) Auth::user()->name))[0]     ?? '';
                                    $saApellido = explode(' ', trim((string) Auth::user()->apellidos))[0] ?? '';
                                @endphp
                                <span class="d-none d-sm-inline mr-1" style="font-size:.85rem;">
                                    Hola {{ trim($saNombre . ' ' . $saApellido) }}!
                                </span>
                            @endif
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                             aria-labelledby="userDropdownSA">
                            {{-- Info móvil --}}
                            <div class="dropdown-header d-sm-none text-truncate px-3 py-2" style="font-size:.8rem;">
                                @if(Auth::check()) {{ trim($saNombre . ' ' . $saApellido) }} @endif
                            </div>
                            <div class="dropdown-divider d-sm-none"></div>

                            {{-- Indicador de rol --}}
                            <div class="dropdown-header px-3 py-1"
                                 style="font-size:.72rem; color:#7c3aed; font-weight:700; letter-spacing:.05em;">
                                <i class="fas fa-crown fa-xs mr-1"></i>SUPER ADMINISTRADOR
                            </div>
                            <div class="dropdown-divider"></div>

                            {{-- Cerrar sesión --}}
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                        document.getElementById('sa-logout-form').submit();">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Cerrar sesión
                            </a>
                            <form id="sa-logout-form" action="{{ route('logout') }}"
                                  method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>
            {{-- ── FIN TOPBAR ── --}}

            @yield('contenido')
        </div>

        {{-- ── FOOTER ── --}}
        @include('layouts.partials.pie-intranet', ['area' => 'Panel Super Admin'])
    </div>
</div>

{{-- ── Scroll-to-top ── --}}
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

{{-- ══ SCRIPTS ══ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var pl = document.getElementById('preloader');
        if (!pl) return;
        pl.classList.add('pl-out');
        setTimeout(function () { pl.style.display = 'none'; }, 90);
    });
</script>
{{-- Bootstrap 5: lo usan vistas con layout dinámico (calificaciones, admisión,
     postulantes: new bootstrap.Modal / data-bs-*). No quitar aunque el resto
     del panel use Bootstrap 4 (data-toggle) del vendor de sb-admin-2. --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
@include('partials.ckeditor-theme-sync')
<script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
{{-- Solo se carga Bootstrap 4 (vendor, jQuery-based) — es el único que usan
     estas vistas (data-toggle/data-target/data-dismiss, sb-admin-2). El CDN
     de Bootstrap 5 que estaba acá antes no lo usaba ninguna vista de este
     layout (ninguna usa data-bs-*/btn-close/new bootstrap.X) y cargar los
     dos bundles juntos podía pisarse entre sí en algunos clics. --}}
<script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('admin/js/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('admin/js/demo/chart-pie-demo.js') }}"></script>
<script src="{{ asset('admin/js/djm.js') }}?v={{ filemtime(public_path('admin/js/djm.js')) }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/puklla-alert.js') }}?v={{ filemtime(public_path('js/puklla-alert.js')) }}"></script>

{{-- Dark Mode Cycle: light → tenue → oscuro --}}
<script>
(function () {
    var THEME_KEY = 'puklla-theme';
    var body   = document.body;
    var toggle = document.getElementById('darkModeToggle');
    var icon   = document.getElementById('darkModeIcon');
    var CYCLE  = ['light', 'dim', 'dark'];
    var ICONS  = { light: 'fas fa-moon', dim: 'fas fa-adjust', dark: 'fas fa-sun' };
    var TITLES = { light: 'Modo tenue', dim: 'Modo oscuro', dark: 'Modo claro' };

    function getTheme() {
        if (body.classList.contains('dark-mode')) return 'dark';
        if (body.classList.contains('dim-mode'))  return 'dim';
        return 'light';
    }

    function applyTheme(theme) {
        body.classList.remove('dark-mode', 'dim-mode');
        if (theme === 'dark') body.classList.add('dark-mode');
        if (theme === 'dim')  body.classList.add('dim-mode');
        localStorage.setItem(THEME_KEY, theme);
        if (icon)   icon.className = ICONS[theme];
        if (toggle) toggle.setAttribute('title', TITLES[theme]);
    }

    applyTheme(getTheme());

    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = CYCLE[(CYCLE.indexOf(getTheme()) + 1) % CYCLE.length];
            applyTheme(next);
        });
    }
})();
</script>

@stack('scripts')
</body>

</html>
