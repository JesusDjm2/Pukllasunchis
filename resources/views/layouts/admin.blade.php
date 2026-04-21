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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <div id="preloader">
            <div class="loader"></div>
        </div>
        @hasanyrole('admin|docente|adminB|tutor')
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            {{-- Logo --}}
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('index') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                        alt="Logo Pukllasunchis" class="sidebar-logo-img">
                </div>
            </a>

            {{-- ══════════ SECCIÓN ADMIN ══════════ --}}
            @role('admin')
            <hr class="sidebar-divider sidebar-logo-divider">
            <div class="sidebar-heading" style="font-size:.65rem;letter-spacing:.08em;opacity:.7;">
                <i class="fas fa-shield-alt fa-xs mr-1"></i> Administración
            </div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sGestion">
                    <i class="fas fa-fw fa-book"></i><span>Gestión académica</span>
                </a>
                <div id="sGestion" class="collapse" data-parent="#accordionSidebar">
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
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sPerfiles">
                    <i class="fas fa-fw fa-users-cog"></i><span>Perfiles</span>
                </a>
                <div id="sPerfiles" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Gestionar perfiles:</h6>
                        <a class="collapse-item" href="{{ route('admin') }}">Perfiles registrados</a>
                        <a class="collapse-item" href="{{ route('registerAdmin') }}">Registrar nuevo</a>
                        <a class="collapse-item" href="{{ route('docente.index') }}">Lista de docentes</a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sAlumnos">
                    <i class="fas fa-fw fa-graduation-cap"></i><span>Matriculados</span>
                </a>
                <div id="sAlumnos" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Gestionar alumnos:</h6>
                        <a class="collapse-item" href="{{ route('adminAlumnos') }}">Alumnos FID</a>
                        <a class="collapse-item" href="{{ route('alumnosppd') }}">Alumnos PPD</a>
                        <a class="collapse-item" href="{{ route('alumnos.demograficos') }}">Datos demográficos</a>
                        <a class="collapse-item" href="{{ route('filtro') }}">Filtrar por campos</a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sAdmision">
                    <i class="fas fa-fw fa-user-plus"></i><span>Admisión</span>
                </a>
                <div id="sAdmision" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Proceso de admisión:</h6>
                        <a class="collapse-item" href="{{ route('regulares.index') }}">Postulantes FID</a>
                        <a class="collapse-item" href="{{ route('postulantes.ppd.index') }}">Postulantes PPD</a>
                        <a class="collapse-item" href="{{ route('admin-fids.index') }}">Admisiones</a>
                    </div>
                </div>
            </li>
            @endrole

            {{-- ══════════ MINK'ARIKUY (solo admin) ══════════ --}}
            @role('admin')
            <hr class="sidebar-divider d-none d-md-block">
            <div class="sidebar-heading" style="font-size:.65rem;letter-spacing:.08em;opacity:.7;">
                <i class="fas fa-qrcode fa-xs mr-1"></i> Mink'arikuy
            </div>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.minkarikuy.index') }}">
                    <i class="fas fa-fw fa-qrcode"></i><span>Mink'arikuy</span>
                </a>
            </li>
            @endrole

            {{-- ══════════ SECCIÓN BOLSA (admin y/o adminB) ══════════ --}}
            @hasanyrole('admin|adminB')
            <hr class="sidebar-divider d-none d-md-block">
            <div class="sidebar-heading" style="font-size:.65rem;letter-spacing:.08em;opacity:.7;">
                <i class="fas fa-briefcase fa-xs mr-1"></i> Bolsa de Trabajo
            </div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sBolsa">
                    <i class="fas fa-fw fa-briefcase"></i><span>Bolsa de trabajo</span>
                </a>
                <div id="sBolsa" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @role('adminB')
                        <h6 class="collapse-header">Gestión de bolsa:</h6>
                        <a class="collapse-item" href="{{ route('trabajo.index') }}">Lista de registros</a>
                        <a class="collapse-item" href="{{ route('trabajo.create') }}">Registrar nuevo</a>
                        <a class="collapse-item" href="{{ route('listaPostulantes') }}">Postulantes</a>
                        @endrole
                        @role('admin')
                        <h6 class="collapse-header">Ofertas públicas:</h6>
                        <a class="collapse-item" href="{{ route('bolsa-trabajo.ofertas.index') }}">Registros y filtros</a>
                        <a class="collapse-item" href="{{ route('bolsa') }}" target="_blank" rel="noopener noreferrer">Ver página pública</a>
                        @endrole
                    </div>
                </div>
            </li>
            @endhasanyrole

            {{-- ══════════ SECCIÓN TUTOR ══════════ --}}
            @role('tutor')
            <hr class="sidebar-divider d-none d-md-block">
            <div class="sidebar-heading" style="font-size:.65rem;letter-spacing:.08em;opacity:.7;">
                <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i> Tutor
            </div>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('tutor.dashboard') }}">
                    <i class="fas fa-fw fa-chalkboard-teacher"></i><span>Panel de Tutor</span>
                </a>
            </li>
            @endrole

            {{-- ══════════ COMUNES ══════════ --}}
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link" href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio" target="_blank">
                    <i class="fas fa-fw fa-book-open"></i><span>Biblioteca</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        @endhasanyrole

        @role('alumno')
            <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center"
                    href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                            alt="Logo Pukllasunchis" class="sidebar-logo-img">
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
                <a class="sidebar-brand d-flex align-items-center justify-content-center"
                    href="{{ route('index') }}">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                            alt="Logo Pukllasunchis" class="sidebar-logo-img">
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

                    {{-- Título de rol (oculto en xs para no reventar el topbar) --}}
                    @hasanyrole('admin|adminB|tutor')
                    <span class="font-weight-bold text-primary d-none d-sm-inline ml-1" style="font-size:.9rem;">
                        @role('admin') Administrador @endrole
                        @role('adminB') Admin Bolsa @endrole
                        @role('tutor') Tutor @endrole
                    </span>
                    @endhasanyrole

                    {{-- Navegación derecha --}}
                    <ul class="navbar-nav ml-auto align-items-center">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                               id="userDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle fa-fw mr-1 text-gray-400 d-sm-none"></i>
                                <span class="d-none d-sm-inline mr-1" style="font-size:.85rem;">
                                    @if (Auth::check() && Auth::user())
                                        {{ Auth::user()->name }} {{ Auth::user()->apellidos }}
                                    @endif
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown">
                                <div class="dropdown-header d-sm-none text-truncate px-3 py-2" style="font-size:.8rem;">
                                    @if (Auth::check() && Auth::user())
                                        {{ Auth::user()->name }} {{ Auth::user()->apellidos }}
                                    @endif
                                </div>
                                <div class="dropdown-divider d-sm-none"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Cerrar sesión') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                {{-- @if (isset($periodoActual) && $periodoActual->horario)
                    <div class="modal fade" id="modalHorario" tabindex="-1" aria-labelledby="modalHorarioLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center bg-light position-relative">
                                    <button type="button" class="btn btn-danger float-right" data-bs-dismiss="modal"
                                        aria-label="Cerrar">✕</button>
                                    <img src="{{ asset($periodoActual->horario) }}"
                                        alt="Horario {{ $periodoActual->nombre }}" loading="lazy"
                                        class="img-fluid rounded shadow">
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <a href="{{ asset($periodoActual->horario) }}" target="_blank"
                                        class="btn btn-primary btn-sm">Ver en nueva pestaña</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (isset($periodoActualPpd) && $periodoActualPpd->calendario)
                    <div class="modal fade" id="modalHorarioPpd" tabindex="-1"
                        aria-labelledby="modalHorarioPpdLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center bg-light position-relative">
                                    <button type="button" class="btn btn-danger float-right" data-bs-dismiss="modal"
                                        aria-label="Cerrar">✕</button>
                                    @if (Str::endsWith($periodoActualPpd->calendario, ['.jpg', '.jpeg', '.png', '.webp']))
                                        <img src="{{ asset($periodoActualPpd->calendario) }}"
                                            alt="Calendario {{ $periodoActualPpd->nombre }}" loading="lazy"
                                            class="img-fluid rounded shadow">
                                    @else
                                        <iframe src="{{ asset($periodoActualPpd->calendario) }}" class="w-100"
                                            style="height: 80vh;" frameborder="0"></iframe>
                                    @endif
                                </div>

                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <a href="{{ asset($periodoActualPpd->calendario) }}" target="_blank"
                                        class="btn btn-primary btn-sm">Abrir en nueva pestaña</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif --}}
                @yield('contenido')
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ date('Y') }} | Hecho por <a class="text-primary"
                                href="https://www.facebook.com/DjmWebMaster" target="_blank"
                                rel="noopener noreferrer"> DJM2 </a> | Versión 2025.1</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) preloader.style.display = 'none';
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
    {{-- PORCENTAJE --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
