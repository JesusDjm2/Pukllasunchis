<!doctype html>
<html class="no-js" lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    @yield('metas')
    <meta name="author" content="David Miranda">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/Icono-Puklla.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gijgo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/slicknav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Script para cargar archivos CSS de forma asíncrona -->
    <script>
        function loadAsyncCSS(url) {
            var cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = url;
            cssLink.media = 'print'; // Inicialmente carga de manera asíncrona, pero solo para medios impresos
            cssLink.onload = function() {
                this.media = 'all'; // Cambia el atributo media después de que se haya cargado el CSS
            };

            // Agrega la etiqueta link al head
            document.head.appendChild(cssLink);
        }

        // Llama a la función para cargar archivos CSS después de que la página se haya cargado
        document.addEventListener('DOMContentLoaded', function() {
            loadAsyncCSS('{{ asset('css/bootstrap.min.css') }}');
            loadAsyncCSS('{{ asset('css/owl.carousel.min.css') }}');
            loadAsyncCSS('{{ asset('css/magnific-popup.css') }}');
            loadAsyncCSS('{{ asset('css/themify-icons.css') }}');
            loadAsyncCSS('{{ asset('css/nice-select.css') }}');
            loadAsyncCSS('{{ asset('css/flaticon.css') }}');
            loadAsyncCSS('{{ asset('css/gijgo.css') }}');
            loadAsyncCSS('{{ asset('css/animate.css') }}');
            loadAsyncCSS('{{ asset('css/slicknav.css') }}');
            loadAsyncCSS('{{ asset('css/style.css') }}');
            loadAsyncCSS('{{ asset('css/estilos.css') }}');
            loadAsyncCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
            // Agrega más llamadas según sea necesario para cargar otros archivos CSS
        });
    </script>
</head>

<body>
    <button id="scrollToTopBtn"><i class="fa fa-arrow-up"></i></button>
    <a class="wasa fa-brands fa-whatsapp"
        href="https://wa.me/51984529158/?text=Buen%20día,%20me%20gustaría%20más%20información%20por%20favor."
        target="_blank"></a>
    <header>
        <div class="header-area ">
            <div class="header-top_area">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 ">
                            <div class="social_media_links">
                                <a href="https://www.facebook.com/eesp.pukllasunchis" target="_blank">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                                <a href="https://www.instagram.com/eesp.pukllasunchis/" target="_blank">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                                <a href="https://www.tiktok.com/@eesp.pukllasunchis?is_from_webapp=1&sender_device=pc"
                                    target="_blank">
                                    <i class="fa-brands fa-tiktok"></i>
                                </a>
                                <a href="https://www.youtube.com/@eesp.pukllasunchis" target="_blank">
                                    <i class="fa-brands fa-youtube"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-8 col-md-6">
                            <div class="short_contact_list">
                                <ul>
                                    @auth
                                        @php
                                            $user = auth()->user();
                                        @endphp
                                        @if ($user->hasRole('admin'))
                                            <li>
                                                <a href="{{ route('admin') }}">
                                                    <i class="fa fa-download"></i> Administrador
                                                </a>
                                            </li>
                                        @elseif ($user->hasRole('adminB'))
                                            <li><a href="{{ route('trabajo.index') }}"> <i class="fa fa-download"></i>
                                                    Intranet</a>
                                            </li>
                                        @elseif ($user->hasRole('alumnoB'))
                                            <li><a href="{{ route('ppd.index') }}"> <i class="fa fa-download"></i>
                                                    Intranet</a>
                                            </li>
                                        @elseif ($user->hasRole('alumno'))
                                            <li><a href="{{ route('alumnos.index') }}"> <i class="fa fa-download"></i>
                                                    Matrícula</a></li>
                                        @elseif ($user->hasRole('docente'))
                                            <li><a href="{{ route('vistaDocente', ['docente' => $user->docente->id]) }}">
                                                    <i class="fa fa-download"></i> Intranet</a>
                                            </li>
                                        @else
                                            <li><a href="{{ route('login') }}"> <i class="fa fa-download"></i> Intranet</a>
                                            </li>
                                        @endif
                                    @else
                                        <li><a href="{{ route('login') }}"> <i class="fa fa-download"></i> Intranet</a>
                                        </li>
                                    @endauth
                                    {{-- <li><a href="{{ route('login') }}" class="text-uppercase"><i
                                                class="fa fa-download"></i>
                                            Intranet</a></li> --}}
                                    <li><a href="http://repositorio.pukllasunchis.org/xmlui/" target="_blank"
                                            class="text-uppercase"><i class="fa fa-file-pdf"></i>
                                            Repositorio</a></li>
                                    @auth
                                        @php
                                            $user = auth()->user();
                                        @endphp
                                        @if ($user->hasRole('admin'))
                                            <li>
                                                <a href="{{ route('admin') }}">
                                                    <i class="fa fa-user"></i> Administrador
                                                </a>
                                            </li>
                                        @elseif ($user->hasRole('adminB'))
                                            <li><a href="{{ route('trabajo.index') }}"> <i class="fa fa-user"></i>
                                                    Matrícula</a>
                                            </li>
                                        @elseif ($user->hasRole('alumnoB'))
                                            <li><a href="{{ route('ppd.index') }}"> <i class="fa fa-user"></i>
                                                    Matrícula</a>
                                            </li>
                                        @elseif ($user->hasRole('alumno'))
                                            <li><a href="{{ route('alumnos.index') }}"> <i class="fa fa-user"></i>
                                                    Matrícula</a></li>
                                        @elseif ($user->hasRole('docente'))
                                            <li><a href="{{ route('vistaDocente', ['docente' => $user->docente->id]) }}">
                                                    <i class="fa fa-user"></i> Docente</a>
                                            </li>
                                        @else
                                            <li><a href="{{ route('login') }}"> <i class="fa fa-user"></i> Matricula</a>
                                            </li>
                                        @endif
                                    @else
                                        <li><a href="{{ route('login') }}"> <i class="fa fa-user"></i> Matrícula</a></li>
                                    @endauth
                                    <li><a
                                            href="https://wa.me/51984529158/?text=Buen%20día,%20me%20gustaría%20más%20información%20por%20favor.">
                                            <i class="fa fa-phone"></i> +51 984 529 158</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="sticky-header" class="main-header-area">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-2">
                            <div class="logo">
                                <a href="{{ route('index') }}">
                                    <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}"
                                        alt="Logo IESP PUkllasunchis" width="200px">
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-10">
                            <div class="main-menu  d-none d-lg-block">
                                <nav>
                                    <ul id="navigation">
                                        <li><a class="active" href="{{ route('nosotros') }}">Nosotros</a></li>
                                        <li><a style="cursor: pointer">Programas<i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="{{ route('inicial') }}">Educación Inicial</a></li>
                                                <li><a href="{{ route('primaria') }}">Educación Primaria</a></li>
                                                <li><a href="{{ route('primariaEIB') }}">Educación Primaria EIB</a>
                                                </li>
                                                {{--  <li><a href="{{ route('formacion') }}">Formación Continua</a></li> --}}
                                                {{-- <li><a href="#">Cursos a Distancia</a></li> --}}
                                                <li><a href="{{ route('profesionalizacion') }}">Profesionalización
                                                        Docente</a></li>
                                            </ul>
                                        </li>
                                        <li><a style="cursor: pointer">Admisión<i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="{{ route('ordinario') }}">Ingreso ordinario</a></li>
                                                <li><a href="{{ route('exoneracion') }}">Por exoneración</a></li>
                                                <li><a href="{{ route('traslado') }}">Traslado externo</a></li>
                                                {{-- <li><a href="{{ route('resultados') }}">Resultados</a></li> --}}
                                            </ul>
                                        </li>
                                        <li><a href="{{ route('tramiteTitulacion') }}"
                                                style="text-decoration: none">Titulación</a></li>
                                        <li><a style="cursor: pointer">Trámites<i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="{{ route('matricula') }}">Matrícula</a></li>
                                                <li><a href="{{ route('Ttraslado') }}">Traslado</a></li>
                                                <li><a href="{{ route('licencia') }}">Licencia de estudios</a></li>
                                                <li><a href="{{ route('partes') }}">Mesa de partes</a></li>
                                                <li><a href="{{ asset('pdf/TUPA-EESPP-2025-2-08022024.pdf') }}"
                                                        target="_blank">Pagos (TUPA)</a></li>
                                            </ul>
                                        </li>
                                        <li><a style="cursor: pointer">Líneas <i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="{{ route('tutoria') }}">Tutoría</a></li>
                                                <li><a href="{{ route('bienestar') }}">Bienestar y Empleabilidad</a>
                                                </li>
                                                <li><a href="{{ route('investigacion') }}">Investigación</a></li>
                                                <li><a href="{{ route('preProfesional') }}">Práctica pre
                                                        profesional</a></li>
                                                <li><a href="{{ route('subvenciones') }}">Subvenciones y becas</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li><a style="cursor: pointer">Información<i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="{{ route('novedades') }}">Novedades</a></li>
                                                <li><a href="{{ route('articulos') }}">Artículos</a></li>
                                                <li><a href="{{ route('proyectos') }}">Proyectos académicos</a></li>
                                                <li><a href="{{ route('innovaciones') }}">innovaciones</a></li>
                                                <li><a href="{{ route('bolsa') }}">Bolsa de trabajo</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mobile_menu d-block d-lg-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    @yield('contenido')
    <footer class="footer">
        <div class="footer_top">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="footer_widget">
                            <div class="footer_logo">
                                <a href="#">
                                    <img src="{{ asset('img/Logo-Pukllasunchis-blanco.png') }}"
                                        alt="Lgo blanco Pukllasunchis" width="280px">
                                </a>
                            </div>
                            <ul>
                                <li>
                                    <p><span class="fa fa-map-marker"></span> Calle Siete Diablitos 222, San Blas,
                                        Cusco.
                                    </p>
                                </li>
                                <li>
                                    <p><span class="fa fa-phone"></span> Informes: +51 984 529 158</p>
                                </li>
                                <li>
                                    <p><span class="fa-solid fa-user-graduate"></span> Secretaria: +51 969 572 566</p>
                                </li>
                                <li>
                                    <p><span class="fa fa-coins"></span> Cobranzas: +51 992 676 676</p>
                                </li>
                                <li><a href="mailto:corporate-mail@support.com" class="mail"><span
                                            class="fa-solid fa-envelope"></span> eespp@pukllasunchis.org</a>
                                </li>
                            </ul>
                            <div class="socail_links" style="margin-top: 30px">
                                <ul>
                                    <li> <a href="https://www.facebook.com/eesp.pukllasunchis" target="_blank">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/eesp.pukllasunchis/" target="_blank">
                                            <i class="fa-brands fa-instagram"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.tiktok.com/@eesp.pukllasunchis?is_from_webapp=1&sender_device=pc"
                                            target="_blank">
                                            <i class="fa-brands fa-tiktok"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/@eesp.pukllasunchis" target="_blank">
                                            <i class="fa-brands fa-youtube"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="footer_widget">
                            <h3 class="footer_title">
                                Proyectos Pukllasunchis
                            </h3>
                            <ul>
                                <li><a href="https://www.pukllasunchis.org/" target="_blank">Asociación
                                        Pukllasunchis</a>
                                </li>
                                <li><a href="https://www.pukllasunchis.org/radio/radio.html" target="_blank">Radio en
                                        la escuela</a></li>
                                <li><a href="https://www.pukllasunchis.org/sipaswayna/sipaswayna.html"
                                        target="_blank">Jóvenes
                                        "Sipas-Wayna"</a></li>
                                <li><a href="https://www.pukllasunchis.org/kawsay/kawsay.html" target="_blank">Kawsay
                                        Centro de Promoción Ambiental</a></li>
                                <li><a href="https://www.pukllasunchis.org/educacion-intercultural"
                                        target="_blank">Educación Intercultural Bilingüe</a></li>
                                <li><a href="https://www.pukllasunchis.org/iesp-pukllasunchis" target="_blank">EESP
                                        Pukllasunchis</a></li>
                                <li><a href="https://www.pukllasunchis.org/colegio-pukllasunchis"
                                        target="_blank">Colegio Pukllasunchis</a></li>
                                <li><a href="https://www.pukllasunchis.org/inclusion-escolar"
                                        target="_blank">Inclusión educativa</a></li>
                                <li><a href="https://www.pukllasunchis.org/participacion-ciudadana"
                                        target="_blank">Participación ciudadana</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="footer_widget">
                            <h3 class="footer_title">
                                Institución
                            </h3>
                            <ul>
                                <li><a href="{{ route('politica') }}"><i class="fa fa-bell"></i> Política de
                                        Privacidad</a></li>
                                <li><a href="terminos-y-condiciones"><i class="fa fa-file"></i> Términos y
                                        Condiciones</a></li>
                                <li><a href="{{ route('informacion') }}"><i class="fa fa-building-columns"></i>
                                        Información Institucional</a></li>
                                <li><a href="#"><i class="fa fa-book"></i> Libro de Reclamaciones</a></li>
                                <li><a href="https://admin.pukllasunchis.startapps.com.pe/login" target="_blank"><i
                                            class="fa fa-download"></i> Intranet</a></li>
                                <li><a href="http://repositorio.pukllasunchis.org/xmlui/" target="_blank"><i
                                            class="fa fa-file-pdf"></i> Repositorio</a></li>
                                <li><a href="{{ route('bolsa') }}" target="_blank"><i class="fa fa-dollar-sign"></i>
                                        Bolsa de trabajo</a></li>
                                <li><a href="{{ route('incidencias.public.create') }}" target="_blank"><i class="fas fa-clipboard-list"></i>
                                        Incidencias</a></li>
                                @php $__minka = \App\Models\Minkarikuy::activo(); @endphp
                                @if ($__minka)
                                <li>
                                    <a href="#" id="btnMinkarikuy">
                                        <i class="fas fa-qrcode"></i> Mink'arikuy
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copy-right_text">
            <div class="container">
                <div class="footer_border"></div>
                <div class="row">
                    <div class="col-xl-12">
                        <p class="copy_right text-center">
                            Copyright &copy;
                            <script>
                                document.write(new Date().getFullYear());
                            </script> Asociación Pukllasunchis. Todos los derechos reservados
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de Admisiones 2026 - VERSIÓN PERSONALIZADA (sin Bootstrap) -->
        <style>
            /* Estilos personalizados para el modal */
            .custom-modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 9999;
                justify-content: center;
                align-items: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .custom-modal-overlay.show {
                display: flex;
                opacity: 1;
            }

            .custom-modal {
                background: white;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                transform: scale(0.9);
                transition: transform 0.3s ease;
                display: flex;
                flex-direction: column;
            }

            .custom-modal-overlay.show .custom-modal {
                transform: scale(1);
            }

            .custom-modal-header {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                padding: 15px 20px;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .custom-modal-title {
                color: #be5c0c;
                font-weight: 600;
                font-size: clamp(1rem, 4vw, 1.5rem);
                margin: 0;
                text-align: center;
                flex: 1;
            }

            .custom-modal-close {
                background: none;
                border: none;
                font-size: 28px;
                cursor: pointer;
                color: #666;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s ease;
                margin-left: 10px;
                padding: 0;
                line-height: 1;
            }

            .custom-modal-close:hover {
                background-color: rgba(0, 0, 0, 0.1);
                color: #333;
                transform: rotate(90deg);
            }

            .custom-modal-close:active {
                transform: scale(0.95);
            }

            .custom-modal-body {
                overflow-y: auto;
                max-height: calc(90vh - 80px);
                padding: 0;
            }

            /* Contenido específico del modal FID */
            .fid-container {
                background: linear-gradient(135deg, #2E5397 0%, #4a7cd4 100%);
                padding: clamp(1rem, 5vw, 2rem);
            }

            .fid-title {
                color: white;
                font-weight: bold;
                text-align: center;
                margin-bottom: 1.5rem;
                font-size: clamp(1.2rem, 5vw, 1.8rem);
            }

            .fid-card {
                background-color: rgba(255, 255, 255, 0.95);
                border: none;
                border-radius: 12px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
                margin-bottom: 1.5rem;
            }

            .fid-card-body {
                padding: clamp(1rem, 4vw, 2rem);
            }

            .fid-card-title {
                color: #2E5397;
                font-weight: bold;
                text-align: center;
                margin-bottom: 1rem;
                font-size: clamp(1rem, 4vw, 1.25rem);
            }

            .fid-text {
                color: #333;
                font-weight: 500;
                text-align: center;
                margin-bottom: 1rem;
                font-size: clamp(0.9rem, 3.5vw, 1rem);
            }

            .fid-subtitle {
                text-align: center;
                margin-bottom: 1.5rem;
                font-size: clamp(0.9rem, 3.5vw, 1rem);
            }

            .fid-carreras {
                margin-bottom: 1.5rem;
            }

            .fid-carrera-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background-color: #e9f0ff;
                border-radius: 8px;
                margin-bottom: 1rem;
            }

            @media (min-width: 768px) {
                .fid-carrera-item {
                    flex-direction: row;
                }
            }

            .fid-carrera-icon {
                color: #2E5397;
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }

            @media (min-width: 768px) {
                .fid-carrera-icon {
                    margin-bottom: 0;
                    margin-right: 1rem;
                }
            }

            .fid-carrera-nombre {
                font-weight: bold;
                text-align: center;
            }

            @media (min-width: 768px) {
                .fid-carrera-nombre {
                    text-align: left;
                }
            }

            .fid-alert {
                background-color: #e3f2fd;
                color: #0c5460;
                border: none;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            .fid-alert-content {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            @media (min-width: 768px) {
                .fid-alert-content {
                    flex-direction: row;
                    text-align: left;
                }
            }

            .fid-alert-icon {
                color: #2E5397;
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }

            @media (min-width: 768px) {
                .fid-alert-icon {
                    margin-bottom: 0;
                    margin-right: 1rem;
                }
            }

            .fid-alert-text {
                font-weight: bold;
            }

            .fid-button-container {
                text-align: center;
            }

            .fid-button {
                background-color: #1e3a6b;
                color: white;
                font-weight: bold;
                padding: 0.75rem 2rem;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                font-size: clamp(0.9rem, 4vw, 1rem);
                transition: all 0.2s ease;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
                width: 100%;
            }

            @media (min-width: 768px) {
                .fid-button {
                    width: auto;
                }
            }

            .fid-button:hover {
                background-color: #2a4b8a;
                transform: translateY(-2px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            }

            .fid-button:active {
                transform: translateY(0);
            }
        </style>
        <!-- Modal personalizado
        <div class="custom-modal-overlay" id="customAdmisionesModal">
            <div class="custom-modal">
                <div class="custom-modal-header">
                    <h4 class="custom-modal-title">ADMISIONES EESP PUKLLASUNCHIS 2026</h4>
                    <button class="custom-modal-close" id="cerrarCustomModal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <div class="fid-container">
                        <div class="text-center mb-3">
                            <h3 class="fid-title">Formación Inicial Docente (FID)</h3>
                        </div>

                        <div class="fid-card">
                            <div class="fid-card-body">
                                <h5 class="fid-card-title">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Admisión 2026
                                </h5>
                                <p class="fid-text">
                                    Duración de 5 años con grado de Bachiller y título de Licenciado
                                </p>

                                <p class="fid-subtitle">
                                    <strong>Carreras disponibles:</strong>
                                </p>

                                <div class="fid-carreras">
                                    <div class="fid-carrera-item">
                                        <i class="fas fa-child fid-carrera-icon"></i>
                                        <div class="fid-carrera-nombre">
                                            <strong>Educación Inicial</strong>
                                        </div>
                                    </div>
                                    <div class="fid-carrera-item">
                                        <i class="fas fa-hands-helping fid-carrera-icon"></i>
                                        <div class="fid-carrera-nombre">
                                            <strong>Educación Primaria Intercultural (EIB)</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="fid-alert">
                                    <div class="fid-alert-content">
                                        <i class="fas fa-calendar-check fid-alert-icon"></i>
                                        <div>
                                            <span class="fid-alert-text">Inscripciones abiertas hasta el 17 de
                                                marzo</span><br>
                                            <span class="fid-alert-text">Examen: 19, 20 y 21 de marzo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fid-button-container">
                            <a href="https://eesppukllasunchis.edu.pe/formulario-de-inscrici%C3%B3n-regular"
                                target="_blank" class="fid-button">
                                <i class="fas fa-external-link-alt me-2"></i>
                                POSTULA AQUÍ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->
        <!-- Script para el modal personalizado -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalKey = 'customModalAdmisionesMostradoFecha';
                const ultimaFecha = localStorage.getItem(modalKey);
                const hoy = new Date().toISOString().split('T')[0];

                const modalOverlay = document.getElementById('customAdmisionesModal');
                const cerrarBtn = document.getElementById('cerrarCustomModal');

                // Función para mostrar el modal
                function mostrarModal() {
                    modalOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
                }

                // Función para cerrar el modal
                function cerrarModal() {
                    modalOverlay.classList.remove('show');
                    document.body.style.overflow = ''; // Restaurar scroll
                }

                // Mostrar modal si no se ha mostrado hoy
                if (ultimaFecha !== hoy && modalOverlay) {
                    setTimeout(function() {
                        mostrarModal();
                        localStorage.setItem(modalKey, hoy);
                    }, 1000);
                }

                // Cerrar con botón
                if (cerrarBtn) {
                    cerrarBtn.addEventListener('click', cerrarModal);
                }

                // Cerrar con ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modalOverlay.classList.contains('show')) {
                        cerrarModal();
                    }
                });

                // Cerrar haciendo clic fuera del modal
                modalOverlay.addEventListener('click', function(e) {
                    if (e.target === modalOverlay) {
                        cerrarModal();
                    }
                });

                // Evitar que el modal se cierre al hacer clic dentro del contenido
                const modalContent = document.querySelector('.custom-modal');
                if (modalContent) {
                    modalContent.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }

                // Prevenir scroll del body cuando el modal está abierto
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            if (modalOverlay.classList.contains('show')) {
                                document.body.style.overflow = 'hidden';
                            } else {
                                document.body.style.overflow = '';
                            }
                        }
                    });
                });

                observer.observe(modalOverlay, {
                    attributes: true
                });
            });
        </script>
        <!-- Font Awesome para íconos -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        @if (isset($__minka) && $__minka)
        {{-- Modal Mink'arikuy --}}
        <div class="custom-modal-overlay" id="minkarikuyModal">
            <div class="custom-modal" style="max-width:400px;">
                <div class="custom-modal-header">
                    <h4 class="custom-modal-title">
                        <i class="fas fa-qrcode mr-2"></i>Mink'arikuy
                    </h4>
                    <button class="custom-modal-close" id="cerrarMinkarikuy" aria-label="Cerrar">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <div style="background:#1a3a6b;padding:1.5rem;text-align:center;">
                        <p style="color:#fff;font-weight:600;font-size:1.1rem;margin-bottom:.5rem;">
                            {{ $__minka->nombre }}
                        </p>
                        <p style="color:#cde;margin-bottom:1rem;font-size:.95rem;">
                            {{ $__minka->fecha->format('d/m/Y') }}
                            &nbsp;—&nbsp;
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $__minka->hora)->format('H:i') }}
                        </p>
                        @if ($__minka->imagen)
                            <img src="{{ asset('img/minkarikuy/'.$__minka->imagen) }}"
                                alt="QR Mink'arikuy"
                                style="max-width:100%;max-height:320px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.4);">
                        @else
                            <p style="color:#acd;">Sin imagen disponible.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var btn   = document.getElementById('btnMinkarikuy');
                var modal = document.getElementById('minkarikuyModal');
                var cerrar = document.getElementById('cerrarMinkarikuy');

                function abrirMinka(e) {
                    e.preventDefault();
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
                function cerrarMinka() {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }

                if (btn) btn.addEventListener('click', abrirMinka);
                if (cerrar) cerrar.addEventListener('click', cerrarMinka);
                if (modal) {
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) cerrarMinka();
                    });
                }
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && modal && modal.classList.contains('show')) cerrarMinka();
                });
            });
        </script>
        @endif
    </footer>

    <script src="{{ asset('js/vendor/modernizr-3.5.0.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/ajax-form.js') }}"></script>
    <script src="{{ asset('js/waypoints.min.js') }}"></script>
    <script src="{{ asset('js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/scrollIt.js') }}"></script>
    <script src="{{ asset('js/jquery.scrollUp.min.js') }}"></script>
    <script src="{{ asset('js/wow.min.js') }}"></script>
    <script src="{{ asset('js/nice-select.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slicknav.min.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/gijgo.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <!--contact js-->
    {{-- <script src="{{ asset('js/contact.js') }}"></script>
    <script src="{{ asset('js/jquery.ajaxchimp.min.js') }}"></script>
    <script src="{{ asset('js/jquery.form.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/mail-script.js') }}"></script> --}}


</body>

</html>
