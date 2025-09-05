<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="David Jesús Miranda">
    @yield('titulo')
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center mb-4" href="{{ route('index') }}">
                <div class="sidebar-brand-icon">
                    <img class="pt-4" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                        alt="Logo Pukllasunchis" width="100%">
                </div>
                <div class="sidebar-brand-text mx-3">
                </div>
            </a>

            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('ppd.index') }}">
                    <i class="fas fa-cog"></i>
                    <span>Ficha Técnica</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            {{-- <a style="color: rgba(255, 255, 255, .8); margin-left: 1em; font-size: .85rem" class="nav-link collapsed {{ !$alumno ? 'disabled' : '' }}"
                @if ($alumno) href="{{ route('calificacionesppd', $alumno->id) }}" @endif>
                <i class="fas fa-fw fa-book"></i>
                <span>Calificaciones</span>
            </a> --}}
            {{-- @if ($alumno)
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('calificacionesppd', $alumno->id) }}">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Calificaciones</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
            @endif --}}
            @if ($alumno)
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('calificacionesppd', $alumno->id) }}">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Calificaciones</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
            @endif

            {{-- <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('postulante.index') }}">
                    <i class="fas fa-fw fa-money-bill"></i>
                    <span>Bolsa de trabajo</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block"> --}}
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
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    {{-- <h5 class="text-primary font-weight-bold">Programa de PPD Presencial y Semipresencial
                        {{ date('Y') }}</h5> --}}
                    <h5 class="text-primary font-weight-bold d-none d-md-block">
                        Programa de PPD Presencial y Semipresencial {{ date('Y') }}
                    </h5>
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @if (Auth::check() && Auth::user())
                                    {{ Auth::user()->name }} {{ Auth::user()->apellidos }}
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                @yield('contenido')
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ date('Y') }} | Hecho por <a class="text-primary"
                                href="https://www.facebook.com/DjmWebMaster" target="_blank" rel="noopener noreferrer">
                                DJM2</a></span>
                    </div>
                </div>
            </footer>
        </div>

    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-pie-demo.js') }}"></script>
    <script src="{{ asset('admin/js/djm.js') }}"></script>

</body>

</html>
