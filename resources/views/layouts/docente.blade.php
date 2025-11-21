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


</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3" href="{{ route('index') }}">
                <div class="sidebar-brand-icon">
                    <img class="pt-3" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}"
                        alt="Logo Pukllasunchis" width="100%">
                </div>
                <div class="sidebar-brand-text mx-3">
                    <!---Contenido solo para estilo a logo--->
                </div>
            </a>
            <li class="nav-item mt-3">
                <a class="nav-link" href="{{ route('index') }}">
                    <i class="fa fa-sm fa-home"></i> <span>Ir a la web</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#perfil"
                    aria-expanded="true" aria-controls="perfil">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
                <div id="perfil" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Perfil Docente</h6>
                        <a class="collapse-item" href="{{ route('docente.show', $docente->id) }}">Ver mi perfil</a>
                        <a class="collapse-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span>Salir del sistema</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cursos"
                    aria-expanded="true" aria-controls="cursos">
                    <i class="fas fa-book"></i>
                    <span>Cursos</span>
                </a>
                <div id="cursos" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Gestionar Cursos:</h6>
                        <a class="collapse-item" href="{{ route('vistaDocente', ['docente' => $docente->id]) }}">Ver
                            mis
                            cursos</a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#alumnos"
                    aria-expanded="true" aria-controls="alumnos">
                    <i class="fas fa-book"></i>
                    <span>Alumnos</span>
                </a>
                <div id="alumnos" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Ver alumnos</h6>
                        <a class="collapse-item" href="{{ route('vistaAlumnos', ['docente' => $docente->id]) }}">
                            Alumnos FID
                        </a>
                        <a class="collapse-item" href="{{ route('alumnosppd2', $docente->id) }}">
                            Alumnos PPD
                        </a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-pen"></i>
                    <span>Calificar</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Calificaciones</h6>
                        <a class="collapse-item"
                            href="{{ route('calificar', ['id' => $docente->id]) }}">Calificaciones</a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            {{-- <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('index') }}">
                    <i class="fas fa-file-pdf"></i>
                    <span>Repositorio de sílabos</span>
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('repositorio', $docente->id) }}">
                    <i class="fas fa-file-pdf"></i>
                    <span>Repositorio de Sílabos</span>
                </a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link collapsed" target="_blank"
                    href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio">
                    <i class="fas fa-book-open"></i>
                    <span>Biblioteca</span>
                </a>
            </li>

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    @if (isset($periodoActual) && $periodoActual->horario)
                        <!-- Botón para abrir el modal -->
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalHorario">
                                Ver Horario {{ $periodoActual->nombre }}
                            </button>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="modalHorario" tabindex="-1" aria-labelledby="modalHorarioLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body text-center bg-light position-relative">
                                        <button type="button" class="btn btn-danger float-right"
                                            data-bs-dismiss="modal" aria-label="Cerrar">
                                            ✕
                                        </button>
                                        <img src="{{ asset($periodoActual->horario) }}"
                                            alt="Horario {{ $periodoActual->nombre }}"
                                            class="img-fluid rounded shadow" loading="lazy">
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Cerrar</button>
                                        <a href="{{ asset($periodoActual->horario) }}" target="_blank"
                                            class="btn btn-primary btn-sm">
                                            Ver en nueva pestaña
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p>Sin Horario asignado a este periodo</p>
                    @endif

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
                                rel="noopener noreferrer"> DJM2 </a> | Versión 2025.2</span>
                    </div>
                </div>
            </footer>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
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
