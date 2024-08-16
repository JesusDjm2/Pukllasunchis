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
                                    <li><a href="https://admin.pukllasunchis.startapps.com.pe/login" target="_blank"
                                            class="text-uppercase"><i class="fa fa-download"></i>
                                            Intranet</a></li>
                                    <li><a href="http://repositorio.pukllasunchis.org/xmlui/" target="_blank"
                                            class="text-uppercase"><i class="fa fa-file-pdf"></i>
                                            Repositorio</a></li>
                                    <li><a href="{{ route('admin') }}"> <i class="fa fa-user"></i>
                                            Matrícula</a>
                                    </li>
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
                                                <li><a href="{{ route('formacion') }}">Formación Continua</a></li>
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
                                                <li><a href="{{ route('resultados') }}">Resultados</a></li>
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
                                                <li><a href="{{ asset('pdf/TUPA-EESPP-2024-08022024-2.pdf') }}"
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
                                        alt="Lgo blanco Pukllasunchis" width="300px">
                                </a>
                            </div>
                            <ul>
                                <li>
                                    <p><span class="fa fa-map-marker"></span> Calle Siete Diablitos 222, San Blas,
                                        Cusco.
                                    </p>
                                </li>
                                <li>
                                    <p><span class="fa fa-phone"></span> +51 (84) 237918 -
                                        261431 - 243308</p>
                                </li>
                                <li><a href="mailto:corporate-mail@support.com" class="mail"><span
                                            class="fa-solid fa-envelope"></span> eespp@pukllasunchis.org</a>
                                </li>
                            </ul>
                            <div class="socail_links">
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
                                <li><a href="{{ route('admin') }}" target="_blank"><i class="fa fa-dollar-sign"></i>
                                        Bolsa de trabajo</a></li>
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


        {{-- <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button style="float:right" type="button" class="btn btn-danger btn-sm"
                            data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <img src="{{ asset('img/novedades/Tesis-empastada.webp') }}" width="100%">
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button style="float:right" type="button" class="btn btn-danger btn-sm"
                            data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <img src="{{ asset('img/novedades/Matricula-2024-II-2.webp') }}" width="100%">
                    </div>
                </div>
            </div>
        </div>


        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Función para mostrar el pop-up
                function mostrarComunicado() {
                    $('#exampleModal').modal('show');
                }

                // Mostrar el pop-up después de 2 segundos (2000 milisegundos) al cargar la página
                setTimeout(mostrarComunicado, 2000);
            });
        </script>

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
