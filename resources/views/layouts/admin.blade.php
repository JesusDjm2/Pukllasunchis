<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="David Jesús Miranda">
    @yield('titulo')
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
</head>

<body id="page-top">
    <div id="wrapper">
        @role('admin')
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3" href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img class="pt-3" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                            alt="Logo Pukllasunchis" width="100%">
                    </div>
                    <div class="sidebar-brand-text mx-3">
                        <!---Contenido solo para estilo a logo--->
                    </div>
                </a>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                        aria-expanded="true" aria-controls="collapseUtilities">
                        <i class="fas fa-book"></i>
                        <span>Programas</span>
                    </a>
                    <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar alumnos:</h6>
                            <a class="collapse-item" href="{{ route('programa.index') }}">Programas</a>
                            <a class="collapse-item" href="{{ route('ciclo.index') }}">Ciclos</a>
                            <a class="collapse-item" href="{{ route('curso.index') }}">Cursos</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                        aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-pen"></i>
                        <span>Registrados</span>
                    </a>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar adminis</h6>
                            <a class="collapse-item" href="{{ route('admin') }}">Perfiles registrados</a>
                            <a class="collapse-item" href="{{ route('registerAdmin') }}">Registrar nuevo</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDocentes"
                        aria-expanded="true" aria-controls="collapseDocentes">
                        <i class="fas fa-user-tie"></i>
                        <span>Docentes</span>
                    </a>
                    <div id="collapseDocentes" class="collapse" aria-labelledby="headingDocentes"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar Docentes:</h6>
                            <a class="collapse-item" href="">Cursos</a>
                            <a class="collapse-item" href="">Actualizar datos</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAlumnos"
                        aria-expanded="true" aria-controls="collapseAlumnos">
                        <i class="fas fa-fw fa-graduation-cap"></i>
                        <span>Matriculados</span>
                    </a>
                    <div id="collapseAlumnos" class="collapse" aria-labelledby="headingAlumnos"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar alumnos:</h6>
                            <a class="collapse-item" href="{{ route('adminAlumnos') }}">Alumnos</a>
                            <a class="collapse-item" href="{{ route('vistAlumno') }}">Ingresar nuevo</a>
                            <a class="collapse-item" href="{{ route('filtro') }}">Filtrar por campos</a>
                        </div>
                    </div>
                </li>
                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bolsa"
                        aria-expanded="true" aria-controls="bolsa">
                        <i class="fas fa-fw fa-dollar-sign"></i>
                        <span>Bolsa de trabajo</span>
                    </a>
                    <div id="bolsa" class="collapse" aria-labelledby="headingAlumnos"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar usuarios:</h6>
                            <a class="collapse-item" href="{{ route('listaPostulantes') }}">Registrados</a>
                            <a class="collapse-item" href="{{ route('trabajo.create') }}">Ingresar nuevo</a>
                        </div>
                    </div>
                </li>
                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
        @endrole
        @role('docente')
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3"
                    href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img class="pt-3" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                            alt="Logo Pukllasunchis" width="100%">
                    </div>
                    <div class="sidebar-brand-text mx-3">
                        <!---Contenido solo para estilo a logo--->
                    </div>
                </a>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                        aria-expanded="true" aria-controls="collapseUtilities">
                        <i class="fas fa-book"></i>
                        <span>Programas</span>
                    </a>
                    <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar alumnos:</h6>
                            <a class="collapse-item" href="{{ route('programa.index') }}">Programas</a>
                            <a class="collapse-item" href="{{ route('ciclo.index') }}">Ciclos</a>
                            <a class="collapse-item" href="{{ route('curso.index') }}">Cursos</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                        aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-pen"></i>
                        <span>Registrados</span>
                    </a>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar adminis</h6>
                            <a class="collapse-item" href="{{ route('admin') }}">Perfiles registrados</a>
                            <a class="collapse-item" href="{{ route('registerAdmin') }}">Registrar nuevo</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDocentes"
                        aria-expanded="true" aria-controls="collapseDocentes">
                        <i class="fas fa-user-tie"></i>
                        <span>Docentes</span>
                    </a>
                    <div id="collapseDocentes" class="collapse" aria-labelledby="headingDocentes"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar Docentes:</h6>
                            <a class="collapse-item" href="">Cursos</a>
                            <a class="collapse-item" href="">Actualizar datos</a>
                        </div>
                    </div>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAlumnos"
                        aria-expanded="true" aria-controls="collapseAlumnos">
                        <i class="fas fa-fw fa-graduation-cap"></i>
                        <span>Matriculados</span>
                    </a>
                    <div id="collapseAlumnos" class="collapse" aria-labelledby="headingAlumnos"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar alumnos:</h6>
                            <a class="collapse-item" href="{{ route('adminAlumnos') }}">Alumnos</a>
                            <a class="collapse-item" href="{{ route('vistAlumno') }}">Ingresar nuevo</a>
                            <a class="collapse-item" href="{{ route('filtro') }}">Filtrar por campos</a>
                        </div>
                    </div>
                </li>
                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bolsa"
                        aria-expanded="true" aria-controls="bolsa">
                        <i class="fas fa-fw fa-dollar-sign"></i>
                        <span>Bolsa de trabajo</span>
                    </a>
                    <div id="bolsa" class="collapse" aria-labelledby="headingAlumnos"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Gestionar usuarios:</h6>
                            <a class="collapse-item" href="{{ route('listaPostulantes') }}">Registrados</a>
                            <a class="collapse-item" href="{{ route('trabajo.create') }}">Ingresar nuevo</a>
                        </div>
                    </div>
                </li>
                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
        @endrole
        @role('alumno')
            <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3"
                    href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img class="pt-3" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                            alt="Logo Pukllasunchis" width="100%">
                    </div>
                    <div class="sidebar-brand-text mx-3">
                        <!---Contenido solo para estilo a logo--->
                    </div>
                </a>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">
                    Dashboard Alumno
                </div>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('admin') }}">
                        <i class="fas fa-fw fa-newspaper"></i>
                        <span>Ficha técnica</span>
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
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
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
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ date('Y') }} | Hecho por <a class="text-primary"
                                href="https://www.facebook.com/DjmWebMaster" target="_blank"
                                rel="noopener noreferrer"> DJM2 </a> | Versión 2024.2</span>
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
