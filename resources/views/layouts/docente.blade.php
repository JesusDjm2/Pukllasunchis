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

        @media (max-width: 767.98px) {
            .docente-topbar-horario {
                order: 3;
                width: 100%;
                margin-top: 0.25rem;
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
    @stack('styles')

</head>

<body id="page-top" class="docente-panel">
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

            <li class="nav-item mt-2">
                <a class="nav-link" href="{{ route('docente.show', $docente->id) }}">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="{{ route('vistaDocente', ['docente' => $docente->id]) }}">
                    <i class="fas fa-book"></i>
                    <span>Cursos</span>
                </a>
            </li>

            <li class="nav-item mt-2">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#alumnos"
                    aria-expanded="true" aria-controls="alumnos">
                    <i class="fas fa-user-graduate"></i>
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
            <li class="nav-item mt-2">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sIncidencias"
                    aria-expanded="false" aria-controls="sIncidencias">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Incidencias</span>
                </a>
                <div id="sIncidencias" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('docente.incidencias.index', $docente->id) }}">
                            <i class="fas fa-list fa-xs mr-1 text-muted"></i> Mis incidencias
                        </a>
                        <a class="collapse-item" href="{{ route('docente.incidencias.create', $docente->id) }}">
                            <i class="fas fa-plus fa-xs mr-1 text-muted"></i> Nueva incidencia
                        </a>
                    </div>
                </div>
            </li>
            {{-- <hr class="sidebar-divider d-none d-md-block"> --}}
            <li class="nav-item mt-2">
                <a class="nav-link" href="{{ route('calificar', ['id' => $docente->id]) }}">
                    <i class="fas fa-pen"></i>
                    <span>Calificar</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link collapsed" href="{{ route('repositorio', $docente->id) }}">
                    <i class="fas fa-file-pdf"></i>
                    <span>Repositorio de Sílabos</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link collapsed" target="_blank"
                    href="https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio">
                    <i class="fas fa-book-open"></i>
                    <span>Biblioteca</span>
                </a>
            </li>

            {{-- ── Bolsa de Trabajo ── --}}
            <hr class="sidebar-divider d-none d-md-block">
            <div class="sidebar-heading text-white-50 px-3 py-1"
                 style="font-size:0.65rem;letter-spacing:.08em;text-transform:uppercase;">
                Bolsa de Trabajo
            </div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sBolsaDocente"
                   aria-expanded="false" aria-controls="sBolsaDocente">
                    <i class="fas fa-briefcase"></i>
                    <span>Bolsa de trabajo</span>
                </a>
                <div id="sBolsaDocente" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Ofertas y convocatorias:</h6>
                        <a class="collapse-item" href="{{ route('bolsa-trabajo.ofertas.index') }}">
                            <i class="fas fa-list fa-xs mr-1 text-muted"></i> Registros y filtros
                        </a>
                        <a class="collapse-item" href="{{ route('bolsa') }}" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-external-link-alt fa-xs mr-1 text-muted"></i> Ver página pública
                        </a>
                    </div>
                </div>
            </li>

            @role('tutor')
                <hr class="sidebar-divider d-none d-md-block">
                <div class="sidebar-heading text-white-50 px-3 py-1"
                    style="font-size:0.65rem;letter-spacing:.08em;text-transform:uppercase;">
                    Tutor
                </div>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tutor.dashboard') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Panel de Tutor</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
            @endrole

            <li class="nav-item mt-3">
                <a class="nav-link" href="{{ route('index') }}">
                    <i class="fa fa-sm fa-home"></i> <span>Ir a la web</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">

            <form id="logout-form-docente" action="{{ route('logout') }}" method="POST" class="d-none"
                aria-hidden="true">
                @csrf
            </form>
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" type="button"
                    aria-label="Contraer menú lateral"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav
                    class="navbar navbar-expand navbar-light bg-white topbar mb-3 mb-md-4 static-top shadow flex-wrap align-items-center py-2">
                    <button id="sidebarToggleTop" type="button" class="btn btn-link d-md-none rounded-circle mr-2"
                        aria-label="Abrir menú">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div
                        class="navbar-nav flex-row flex-wrap flex-grow-1 align-items-center mr-2 docente-topbar-horario">
                        @if (isset($periodoActual) && $periodoActual && $periodoActual->horario)
                            <button type="button" class="btn btn-info btn-sm my-1 my-md-0" data-toggle="modal"
                                data-target="#modalHorario">
                                <i class="far fa-calendar-alt mr-1"></i>
                                <span class="d-none d-sm-inline">Ver horario</span><span
                                    class="d-sm-none">Horario</span>
                                <span class="d-none d-md-inline"> — {{ $periodoActual->nombre }}</span>
                            </button>

                            <div class="modal fade" id="modalHorario" tabindex="-1" role="dialog"
                                aria-labelledby="modalHorarioLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header py-2">
                                            <h5 class="modal-title" id="modalHorarioLabel">Horario
                                                {{ $periodoActual->nombre }}</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Cerrar">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center bg-light">
                                            <img src="{{ asset($periodoActual->horario) }}"
                                                alt="Horario {{ $periodoActual->nombre }}"
                                                class="img-fluid rounded shadow" loading="lazy">
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
                        @elseif(isset($periodoActual) && $periodoActual)
                            <span class="small text-muted my-1"><i class="far fa-calendar-times mr-1"></i>Sin horario
                                cargado para {{ $periodoActual->nombre }}</span>
                        @else
                            <span class="small text-muted my-1 d-none d-md-inline">Sin periodo académico activo</span>
                        @endif
                    </div>

                    <ul class="navbar-nav ml-auto flex-row align-items-center">
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
                                <a class="dropdown-item" href="{{ route('docente.show', $docente->id) }}">
                                    <i class="fas fa-id-card fa-sm fa-fw mr-2 text-gray-400"></i> Mi perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form-docente').submit();">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Cerrar sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                @yield('contenido')
            </div>
            <footer class="sticky-footer bg-white border-top">
                <div class="container my-auto px-2">
                    <div class="copyright text-center my-auto small text-muted">
                        <span>Copyright &copy; {{ date('Y') }} · <a class="text-primary"
                                href="https://www.facebook.com/DjmWebMaster" target="_blank"
                                rel="noopener noreferrer">DJM2</a> · v2025.2</span>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
    <script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('admin/js/demo/chart-pie-demo.js') }}"></script>
    <script src="{{ asset('admin/js/djm.js') }}"></script>
    @stack('scripts')

</body>

</html>
