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
    <link rel="stylesheet" href="{{ asset('admin/css/darkmode.css') }}">
    {{-- Super Admin exclusivo (sin conflicto con admin.blade.php) --}}
    <link rel="stylesheet" href="{{ asset('admin/css/superadmin.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @stack('styles')
</head>

<body id="page-top">
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
    <ul class="navbar-nav sidebar sidebar-dark accordion sidebar-sa" id="accordionSidebar">

        {{-- ── Logo ── --}}
        <div class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="sidebar-brand-icon">
                <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                     alt="Logo Pukllasunchis" class="sidebar-logo-img">
            </div>
        </div>

        {{-- ── Badge de rol ── --}}
        <div class="text-center mb-2 px-3">
            <span class="sa-crown-badge">
                <i class="fas fa-crown fa-xs"></i>&nbsp;Super Admin
            </span>
        </div>

        <hr class="sidebar-divider sidebar-logo-divider">

        {{-- ══ SECCIÓN: ADMINISTRACIÓN ══ --}}
        <div class="sidebar-heading">
            <i class="fas fa-shield-alt fa-xs"></i> Administración
        </div>

        {{-- Gestión académica --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#saGestion"
               aria-expanded="false" aria-controls="saGestion">
                <i class="fas fa-fw fa-book"></i><span>Gestión académica</span>
            </a>
            <div id="saGestion" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Programas y estructura:</h6>
                    <a class="collapse-item" href="{{ route('programa.index') }}">Programas</a>
                    <a class="collapse-item" href="{{ route('ciclo.index') }}">Ciclos</a>
                    <a class="collapse-item" href="{{ route('curso.index') }}">Cursos</a>
                    <a class="collapse-item" href="{{ route('competencias.index') }}">Competencias</a>
                    <a class="collapse-item" href="{{ route('capacidades.index') }}">Capacidades</a>
                    <a class="collapse-item" href="{{ route('estandares.index') }}">Estándares</a>
                    <a class="collapse-item" href="{{ route('enfoques.index') }}">Enfoques</a>
                    <a class="collapse-item" href="{{ route('proyectos.index') }}">Proyectos integradores</a>
                    <a class="collapse-item" href="{{ route('periodoactual.index') }}">Periodos</a>
                </div>
            </div>
        </li>

        {{-- Perfiles --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin') ? 'active' : '' }}"
               href="{{ route('admin') }}">
                <i class="fas fa-fw fa-users-cog"></i><span>Perfiles</span>
            </a>
        </li>

        {{-- Docentes --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('docente.index') ? 'active' : '' }}"
               href="{{ route('docente.index') }}">
                <i class="fas fa-fw fa-chalkboard-teacher"></i><span>Docentes</span>
            </a>
        </li>

        {{-- Matriculados --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#saAlumnos"
               aria-expanded="false" aria-controls="saAlumnos">
                <i class="fas fa-fw fa-graduation-cap"></i><span>Matriculados</span>
            </a>
            <div id="saAlumnos" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Gestionar alumnos:</h6>
                    <a class="collapse-item" href="{{ route('adminAlumnos') }}">Alumnos FID</a>
                    <a class="collapse-item" href="{{ route('alumnosppd') }}">Alumnos PPD</a>
                    <a class="collapse-item" href="{{ route('alumnos.demograficos') }}">Datos demográficos</a>
                </div>
            </div>
        </li>

        {{-- Admisión --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#saAdmision"
               aria-expanded="false" aria-controls="saAdmision">
                <i class="fas fa-fw fa-user-plus"></i><span>Admisión</span>
            </a>
            <div id="saAdmision" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Proceso de admisión:</h6>
                    <a class="collapse-item" href="{{ route('regulares.index') }}">Postulantes FID</a>
                    <a class="collapse-item" href="{{ route('postulantes.ppd.index') }}">Postulantes PPD</a>
                    <a class="collapse-item" href="{{ route('admin-fids.index') }}">Admisiones</a>
                </div>
            </div>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        {{-- ══ SECCIÓN: BOLSA Y COMUNICADOS ══ --}}
        <div class="sidebar-heading">
            <i class="fas fa-bullhorn fa-xs"></i> publicaciones internas
        </div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bolsa-trabajo.*', 'admin.comunicados.*', 'admin.minkarikuy.*') ? '' : 'collapsed' }}"
               href="#" data-toggle="collapse" data-target="#saBolsa"
               aria-expanded="{{ request()->routeIs('bolsa-trabajo.*', 'admin.comunicados.*', 'admin.minkarikuy.*') ? 'true' : 'false' }}"
               aria-controls="saBolsa">
                <i class="fas fa-fw fa-briefcase"></i><span>Bolsa y Comunicados</span>
            </a>
            <div id="saBolsa" class="collapse {{ request()->routeIs('bolsa-trabajo.*', 'admin.comunicados.*', 'admin.minkarikuy.*') ? 'show' : '' }}"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item {{ request()->routeIs('bolsa-trabajo.*') ? 'active' : '' }}"
                       href="{{ route('bolsa-trabajo.ofertas.index') }}">Bolsa de Trabajo</a>
                    <a class="collapse-item {{ request()->routeIs('admin.comunicados.*') ? 'active' : '' }}"
                       href="{{ route('admin.comunicados.index') }}">Comunicados</a>
                    <a class="collapse-item {{ request()->routeIs('admin.minkarikuy.*') ? 'active' : '' }}"
                       href="{{ route('admin.minkarikuy.index') }}">
                        <i class="fas fa-qrcode fa-xs mr-1"></i>Mink'arikuy
                    </a>
                </div>
            </div>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        {{-- ══ SECCIÓN: CURSOS ESPECIALES ══ --}}
        <div class="sidebar-heading">
            <i class="fas fa-play-circle fa-xs"></i> Formación Asincrónica
        </div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ce.cursos.*') ? 'active' : '' }}"
               href="{{ route('ce.cursos.index') }}">
                <i class="fas fa-fw fa-graduation-cap"></i><span>Cursos Asincrónicos</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        {{-- ══ SECCIÓN: RECURSOS ══ --}}
        <div class="sidebar-heading">
            <i class="fas fa-book-open fa-xs"></i> Recursos
        </div>
        <li class="nav-item">
            <a class="nav-link"
               href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio"
               target="_blank" rel="noopener">
                <i class="fas fa-fw fa-book-open"></i><span>Biblioteca</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
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
                            <i class="fas fa-crown fa-xs text-warning ml-1" title="Super Admin"></i>
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
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>
                        Copyright &copy; {{ date('Y') }}
                        &nbsp;|&nbsp;<i class="fas fa-crown fa-xs text-warning"></i> Panel Super Admin
                        &nbsp;|&nbsp;Hecho por
                        <a class="text-primary" href="https://www.facebook.com/DjmWebMaster"
                           target="_blank" rel="noopener noreferrer">DJM2</a>
                        &nbsp;|&nbsp;Versión 2026.2
                    </span>
                </div>
            </div>
        </footer>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('admin/js/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('admin/js/demo/chart-pie-demo.js') }}"></script>
<script src="{{ asset('admin/js/djm.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
