@extends('layouts.home')
@section('metas')
    <title>EESPP Pukllasunchis - Pedagógico Pukllasunchis</title>
    <meta name="description" content="Escuela de Educación Superior Pedagógica Pukllasunchis">
    <meta name="keywords" content="EESP Pukllasunchis, Pukllasunchis, Educación, Intercultural, EIB, Inicial, Primaria">
@endsection
@section('contenido')
    <div class="slider_area">
        <div class="slider_active owl-carousel">
            <div class="single_slider  d-flex align-items-center">
                <div class="slidePuklla1"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="slider_text ">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <h3>Currículo y <br>
                                    Educación <span>Intercultural</span> </h3>
                                <a href="#" class="boxed-btn3">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider  d-flex align-items-center">
                <div class="slidePuklla5"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="slider_text ">
                                <h3> Bolsa de <br>
                                    <span>Trabajo</span>
                                </h3>
                                <a href="{{ route('bolsa') }}" class="boxed-btn3">Ver ofertas</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider  d-flex align-items-center">
                <div class="slidePuklla2"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="slider_text ">
                                <h3> Investigación, <br>
                                    Arte y <span>Acción</span></h3>
                                <a href="#" class="boxed-btn3">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider  d-flex align-items-center">
                <div class="slidePuklla3"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="slider_text ">
                                <h3> Explorando <br>
                                    nuestra <span>identidad</span></h3>
                                <a href="#" class="boxed-btn3">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider  d-flex align-items-center">
                <div class="slidePuklla4"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="slider_text ">
                                <h3> Compromiso con la <br>
                                    educación del <span>Perú</span></h3>
                                <a href="#" class="boxed-btn3">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="welcome_docmed_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-7 col-lg-7">
                    <div class="welcome_docmed_info">
                        <h2>¿Quiénes somos?</h2>
                        <h3>EESP PUKLLASUNCHIS</h3>
                        <p class="text-justify">
                            La EESP Pukllasunchis es una institución que asume un serio compromiso con la educación del Perú
                            y es sensible a las diferencias culturales, principalmente de la región y el país. Es un
                            proyecto alternativo que busca revisar los paradigmas tradicionales para reconstruir una
                            educación que sea inclusiva e intercultural. Desde esa perspectiva atendemos a una población
                            diversa entre la que incluye a estudiantes de Beca 18 procedentes de distintas comunidades
                            andinas.
                        </p>

                        <!-- Nuevo bloque agregado -->
                        <div class="row mb-3">
                            <div class="col-2">
                                <img width="100%" src="{{ asset('img/min/Icono-discapacidad.jpg') }}" alt="">
                            </div>
                            <div class="col-10">
                                <p>
                                    Brindamos apoyo a personas con discapacidad. Si necesitas asistencia para tu ingreso
                                    a la escuela, contáctanos al
                                    <a class="fa-brands fa-whatsapp" style="font-size:12px;color: #128C7E;"
                                        href="https://wa.me/51984529158/?text=Buen%20día,%20me%20gustaría%20más%20información%20por%20favor."
                                        target="_blank"> +51 984 529 158.</a>
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('nosotros') }}" class="boxed-btn3-white-2">Leer más</a>
                        <a href="{{ route('ordinario') }}" class="boxed-btn3-white-2">Postular</a>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-5 mt-4">
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/74k-QFuO2oo"
                        title="EESP Pukllasunchis" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid p-0 mensajes">
        <div class="row no-gutters">
            <div class="col-12 col-lg-6 d-flex align-items-center"
                style="min-height: 560px; background-image: linear-gradient(90deg, rgba(49, 84, 152, 1) 0%, rgba(49, 84, 152, 0.92) 34%, rgba(49, 84, 152, 0.62) 56%, rgba(49, 84, 152, 0.24) 74%, rgba(49, 84, 152, 0) 100%), url('{{ asset('img/Pages/Nuevo-FID-2026.jpg') }}'); background-size: cover; background-position: center;">
                <div class="w-100 p-4 p-md-5 p-lg-5 text-white">
                    <span class="d-inline-block mb-3 px-3 py-2 rounded-pill"
                        style="background: #fff;color:#000; border: 1px solid rgba(255,255,255,0.4); font-weight: 600;">FID</span>
                    <h3 class="text-white mb-3">Formacion Inicial Docente</h3>
                    <p class="mb-4 text-white" style="max-width: 50%;">
                        La FID abarca los programas de Educacion Inicial y Educacion Primaria EIB, orientados a la
                        formacion de docentes con enfoque intercultural, inclusivo y compromiso con la realidad local.
                    </p>
                    <div class="d-flex flex-wrap" style="gap: .65rem;">
                        <a href="{{ route('inicial') }}" class="btn px-4 py-2"
                            style="background:#ffffff; color:rgb(49, 84, 152); border:1px solid #ffffff; font-weight:600;">Educacion
                            Inicial</a>
                        <a href="{{ route('primariaEIB') }}" class="btn text-white px-4 py-2"
                            style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.85); font-weight:600;">Primaria
                            EIB</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 d-flex align-items-center"
                style="min-height: 560px; background-image: linear-gradient(90deg, rgba(200, 143, 66, 1) 0%, rgba(200, 143, 66, 0.92) 34%, rgba(200, 143, 66, 0.62) 56%, rgba(200, 143, 66, 0.24) 74%, rgba(200, 143, 66, 0) 100%), url('{{ asset('img/Pages/PPD-2026.jpg') }}'); background-size: cover; background-position: center;">
                <div class="w-100 p-4 p-md-5 p-lg-5 text-white">
                    <span class="d-inline-block mb-3 px-3 py-2 rounded-pill"
                        style="background: #fff;color:#000; border: 1px solid rgba(255,255,255,0.4); font-weight: 600;">PPD</span>
                    <h3 class="text-white mb-3">Programa de Profesionalizacion Docente</h3>
                    <p class="mb-4 text-white" style="max-width: 50%;">
                        Programa de 1 año dirigido a docentes con titulo de profesor y egresados de IESP, enfocado en
                        fortalecer la practica pedagogica y responder a las demandas actuales del sistema educativo.
                    </p>
                    <a href="{{ route('profesionalizacion') }}" class="btn px-4 py-2"
                        style="background:#ffffff; color:rgb(200, 143, 66); border:1px solid #ffffff; font-weight:600;">Conocer
                        programa PPD</a>
                </div>
            </div>
        </div>
    </div>

    <section class="fid-calendario_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="section_title text-center mb-55">
                        <span class="fid-calendario-eyebrow">
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            Formación Inicial Docente
                        </span>
                        <h3>Calendario de Admisión y Matrícula</h3>
                        <p class="text-center">Horarios, fechas de admisión, matrícula e inicio de clases para nuestros
                            programas de FID.
                        </p>
                    </div>
                </div>
            </div>
            <div class="fid-calendario-tablewrap">
                <table class="fid-calendario-table">
                    <thead>
                        <tr>
                            <th scope="col">Programa de Estudios</th>
                            <th scope="col">Horario</th>
                            <th scope="col">Admisión</th>
                            <th scope="col">Matrícula ordinaria</th>
                            <th scope="col">Matrícula extemporánea</th>
                            <th scope="col">Inicio de clases</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Programa de Estudios">
                                <a href="{{ route('inicial') }}" class="fid-calendario-link">
                                    Educación Inicial
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td data-label="Horario">Lunes a Viernes
                                <span class="fid-calendario-hora">4:00 p.m. – 9:00 p.m.</span>
                            </td>
                            <td data-label="Admisión">Primera semana de marzo</td>
                            <td data-label="Matrícula ordinaria">Segunda semana de marzo</td>
                            <td data-label="Matrícula extemporánea">Tercera semana de marzo</td>
                            <td data-label="Inicio de clases">Última semana de marzo</td>
                        </tr>
                        <tr>
                            <td data-label="Programa de Estudios">
                                <a href="{{ route('primariaEIB') }}" class="fid-calendario-link">
                                    Educación Primaria Intercultural Bilingüe
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td data-label="Horario">Lunes a Viernes
                                <span class="fid-calendario-hora">4:00 p.m. – 9:00 p.m.</span>
                            </td>
                            <td data-label="Admisión">Primera semana de marzo</td>
                            <td data-label="Matrícula ordinaria">Segunda semana de marzo</td>
                            <td data-label="Matrícula extemporánea">Tercera semana de marzo</td>
                            <td data-label="Inicio de clases">Última semana de marzo</td>
                        </tr>
                        <tr class="fid-calendario-row--closed">
                            <td data-label="Programa de Estudios">
                                <a href="{{ route('primaria') }}" class="fid-calendario-link fid-calendario-link--muted">
                                    Educación Primaria
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td data-label="Estado" colspan="5">
                                <span class="fid-calendario-badge">
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                    No se aperturan metas para este programa
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="container">

                {{-- Botón infografía proceso de matrícula --}}
                <div class="fid-proceso-matricula-btn text-center mt-4">
                    <button type="button" class="pd-btn-infografia" data-toggle="modal"
                        data-target="#modalProcesoMatricula">
                        <span class="fid-btn-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <line x1="3" y1="9" x2="21" y2="9" />
                                <line x1="9" y1="21" x2="9" y2="9" />
                            </svg>
                        </span>
                        <span class="fid-btn-text">Ver proceso de matrícula</span>
                        <span class="fid-btn-arrow">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </span>
                    </button>
                </div>

                {{-- Modal infografía proceso de matrícula --}}
                <div class="modal fade" id="modalProcesoMatricula" tabindex="-1" role="dialog"
                    aria-labelledby="modalProcesoMatriculaLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-white" id="modalProcesoMatriculaLabel">
                                    Proceso de Matrícula
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="border: none; color:#fff; background: transparent;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body text-center p-0">
                                <img src="{{ asset('img/Proceso-de-matricula-EESP-Pukllasunchis-2.png') }}"
                                    alt="Proceso de Matrícula EESP Pukllasunchis" class="img-fluid w-100"
                                    style="display:block; border-radius:0 0 .5rem .5rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .fid-calendario_area {
            padding: 4rem 0 4.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #faf8f5 100%);
        }

        .fid-calendario-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #314e98;
            background: rgba(49, 84, 152, .08);
            border: 1px solid rgba(49, 84, 152, .22);
            padding: .35rem .85rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }

        .fid-calendario-tablewrap {
            border-radius: 16px;
            overflow: hidden auto;
            background: #fff;
            box-shadow: 0 18px 50px rgba(40, 35, 25, .12), 0 2px 8px rgba(40, 35, 25, .06);
            border: 1px solid rgba(0, 0, 0, .05);
        }

        .fid-calendario-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        .fid-calendario-table thead th {
            background: linear-gradient(90deg, #314e98 0%, #3d63bd 100%);
            color: #fff;
            text-align: left;
            padding: 1rem 1.1rem;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .fid-calendario-table tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            transition: background .2s ease;
        }

        .fid-calendario-table tbody tr:last-child {
            border-bottom: 0;
        }

        .fid-calendario-table tbody tr:nth-child(even) {
            background: rgba(49, 84, 152, .025);
        }

        .fid-calendario-table tbody tr:hover {
            background: rgba(205, 146, 68, .08);
        }

        .fid-calendario-table td {
            padding: 1rem 1.1rem;
            font-size: .92rem;
            color: #333;
            vertical-align: middle;
        }

        .fid-calendario-link {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            color: #fff !important;
            background: linear-gradient(135deg, #cd9244, #b87a2a);
            font-weight: 600;
            font-size: .88rem;
            line-height: 1.3;
            text-decoration: none !important;
            padding: .5rem 1rem;
            border-radius: 999px;
            box-shadow: 0 4px 12px rgba(205, 146, 68, .32);
            transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
        }

        .fid-calendario-link i {
            font-size: .76rem;
            transition: transform .22s ease;
        }

        .fid-calendario-link:hover,
        .fid-calendario-link:focus-visible {
            background: linear-gradient(135deg, #314e98, #25407d);
            box-shadow: 0 6px 18px rgba(49, 84, 152, .35);
            transform: translateX(3px);
        }

        .fid-calendario-link:hover i {
            transform: translateX(3px);
        }

        .fid-calendario-link--muted {
            background: rgba(49, 84, 152, .08);
            color: #314e98 !important;
            box-shadow: none;
            border: 1px solid rgba(49, 84, 152, .25);
        }

        .fid-calendario-link--muted:hover {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff !important;
            box-shadow: 0 6px 18px rgba(49, 84, 152, .3);
        }

        .fid-calendario-hora {
            display: block;
            color: #6a5a4a;
            font-size: .8rem;
            margin-top: .15rem;
        }

        .fid-calendario-row--closed {
            background: rgba(0, 0, 0, .025);
        }

        .fid-calendario-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(178, 40, 40, .08);
            color: #a83232;
            border: 1px solid rgba(178, 40, 40, .25);
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
        }

        /* ── Responsive: tabla → tarjetas apiladas en móvil ── */
        @media (max-width: 767.98px) {
            .fid-calendario-tablewrap {
                overflow: visible;
                background: transparent;
                box-shadow: none;
                border: 0;
            }

            .fid-calendario-table {
                min-width: 0;
            }

            .fid-calendario-table thead {
                display: none;
            }

            .fid-calendario-table,
            .fid-calendario-table tbody,
            .fid-calendario-table tr,
            .fid-calendario-table td {
                display: block;
                width: 100%;
            }

            .fid-calendario-table tbody tr {
                margin-bottom: 1rem;
                border: 1px solid rgba(0, 0, 0, .06);
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 8px 22px rgba(40, 35, 25, .08);
                background: #fff;
            }

            .fid-calendario-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                text-align: right;
                padding: .65rem .9rem;
                border-bottom: 1px solid rgba(0, 0, 0, .05);
            }

            .fid-calendario-table td:last-child {
                border-bottom: 0;
            }

            .fid-calendario-table td::before {
                content: attr(data-label);
                flex-shrink: 0;
                text-align: left;
                font-size: .72rem;
                font-weight: 700;
                color: #314e98;
                text-transform: uppercase;
                letter-spacing: .03em;
            }

            .fid-calendario-table td:first-child {
                justify-content: flex-start;
                background: linear-gradient(135deg, rgba(49, 84, 152, .07), rgba(205, 146, 68, .05));
                font-size: 1rem;
            }

            .fid-calendario-table td:first-child::before {
                content: none;
            }

            .fid-calendario-table td[colspan] {
                justify-content: center;
                text-align: center;
            }

            .fid-calendario-table td[colspan]::before {
                content: none;
            }
        }

        /* ── Botón infografía proceso de matrícula ── */
        .fid-proceso-matricula-btn {
            margin-top: 1.8rem !important;
        }

        .pd-btn-infografia {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            background: #fff;
            border: 1.5px solid rgba(49, 84, 152, .25);
            color: #314e98;
            font-size: .92rem;
            font-weight: 600;
            padding: .7rem 1.6rem .7rem 1.4rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all .25s ease;
            box-shadow: 0 4px 14px rgba(49, 84, 152, .08);
            font-family: inherit;
            line-height: 1.4;
        }

        .pd-btn-infografia:hover,
        .pd-btn-infografia:focus-visible {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 22px rgba(49, 84, 152, .3);
            transform: translateY(-2px);
        }

        .pd-btn-infografia:active {
            transform: translateY(0);
        }

        .fid-btn-icon {
            display: inline-flex;
            align-items: center;
            opacity: .75;
            transition: opacity .2s;
        }

        .pd-btn-infografia:hover .fid-btn-icon {
            opacity: 1;
        }

        .fid-btn-arrow {
            display: inline-flex;
            align-items: center;
            transition: transform .25s ease;
        }

        .pd-btn-infografia:hover .fid-btn-arrow {
            transform: translateX(4px);
        }

        /* ── Modal infografía ── */
        #modalProcesoMatricula .modal-content {
            border: none;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(13, 33, 55, .25);
        }

        #modalProcesoMatricula .modal-header {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff;
            border: none;
            padding: .9rem 1.4rem;
        }

        #modalProcesoMatricula .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: .01em;
        }

        #modalProcesoMatricula .modal-header .close {
            color: #fff;
            opacity: .75;
            text-shadow: none;
            font-size: 1.5rem;
            transition: opacity .2s;
        }

        #modalProcesoMatricula .modal-header .close:hover {
            opacity: 1;
        }

        #modalProcesoMatricula .modal-body {
            background: #f8f9fc;
            padding: 0;
        }
    </style>


    <div class="our_department_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="section_title text-center mb-55">
                        <h3>Nuestros Programas de Estudios </h3>
                        <h4 class="mt-4">Grado de Bachiller en Educación - Título Profesional de Licenciado en Educación
                        </h4>
                        <p>Nuestra finalidad es desarrollar un programa alternativo e interesante, con un enfoque inclusivo
                            e intercultural, que contribuya a la formación de profesionales capaces de aportar, desde el
                            aula o desde otros ámbitos de trabajo al desarrollo de nuevos escenarios educativos.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/inicial-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="{{ route('inicial') }}">Educación Inicial</a></h3>
                            <p>Esta especialidad está dirigida a jóvenes, varones y mujeres que se interesan en los niños
                                pequeños y que tienen disposición para generar espacios de juego, creatividad e
                                imaginación...</p>
                            <a href="{{ route('inicial') }}" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/primaria-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="{{ route('primariaEIB') }}">Educación Primaria EIB</a></h3>
                            <p>Esta especialidad está dirigida a jóvenes varones y mujeres, con conocimiento de la lengua
                                quechua, interesados y con disposición para proponer alternativas...</p>
                            <a href="{{ route('primariaEIB') }}" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/primaria-eib-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="{{ route('primaria') }}">Educación Primaria</a></h3>
                            <p>Esta especialidad está dirigida a jóvenes varones y mujeres, interesados y con disposición
                                para: proponer alternativas educativas que respondan a la realidad social y lingüística de
                                los niños y niña...</p>
                            <a href="{{ route('primaria') }}" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/profesionalizacion-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="{{ route('formacion') }}">Formación Continua</a></h3>
                            <p>El programa de formación continua busca desarrollar competencias para una práctica pedagógica
                                intercultural y situada en las características sociales y culturales de la región.
                                Constituye un espacio de...</p>
                            <a href="{{ route('formacion') }}" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/cursos-a-distancia-puklla-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="#">Cursos a Distancia</a></h3>
                            <p>La educación a distancia es un sistema de enseñanza-aprendizaje que se desarrolla parcial o
                                totalmente a través de las tecnologías de la información y comunicación (TIC), bajo un
                                esquema ...</p>
                            <a href="#" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_department">
                        <div class="department_thumb">
                            <img src="{{ asset('img/min/formacion-continua-pukllasunchis-01.webp') }}" alt="">
                        </div>
                        <div class="department_content">
                            <h3><a href="{{ route('profesionalizacion') }}">Profesionalización Docente</a></h3>
                            <p>Los programas de profesionalización buscan desarrollar competencias para una práctica
                                pedagógica abierta, conectada con la vida y la cultura y con las necesidades de nuestro
                                tiempo...</p>
                            <a href="{{ route('profesionalizacion') }}" class="learn_more">Leer más</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="testmonial_area">
        <div class="testmonial_active owl-carousel">
            <div class="single-testmonial testmonial_bg_1 overlay2">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-10 offset-xl-1">
                            <div class="testmonial_info text-center">
                                <div class="quote">
                                    <i class="flaticon-straight-quotes"></i>
                                </div>
                                <p class="text-center">Educar a un niño no es hacerle aprender algo que no sabía, sino
                                    hacer
                                    de él alguien que no existía
                                </p>
                                <div class="testmonial_author">
                                    <h4>John Ruskin</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single-testmonial testmonial_bg_2 overlay2">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-10 offset-xl-1">
                            <div class="testmonial_info text-center">
                                <div class="quote">
                                    <i class="flaticon-straight-quotes"></i>
                                </div>
                                <p class="text-center">
                                    El arte supremo del maestro es despertar el placer de la expresión
                                    creativa y el conocimiento
                                </p>
                                <div class="testmonial_author">
                                    <h4>Albert Einstein </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single-testmonial testmonial_bg_3 overlay2">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-10 offset-xl-1">
                            <div class="testmonial_info text-center">
                                <div class="quote">
                                    <i class="flaticon-straight-quotes"></i>
                                </div>
                                <p class="text-center">
                                    Cuando eres un educador siempre estás en el lugar apropiado a su
                                    debido tiempo. No hay horas malas para aprender.
                                </p>
                                <div class="testmonial_author">
                                    <h4>Betty B. Anderson</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="business_expert_area">
        <div class="business_tabs_area">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <ul class="nav" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home"
                                    role="tab" aria-controls="home" aria-selected="true">Admisión</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile" aria-selected="false">Programas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                    aria-controls="contact" aria-selected="false">Titulación</a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        <div class="container">
            <div class="border_bottom">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row align-items-center">
                            <div class="col-xl-6 col-md-6">
                                <div class="business_info">
                                    <h3>EESPP:</h3>
                                    <p class="text-justify">Somos una escuela de educación superior particular certificada
                                        por la SUNEDU,
                                        contamos con los programas de:</p><br>
                                    <ul>
                                        <li><a href="{{ route('ordinario') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Admisión Ordinario</a></li>
                                        <li><a href="{{ route('exoneracion') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Admisión por Exoneración</a></li>
                                        <li><a href="{{ route('traslado') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Admisión por traslado</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="business_thumb">
                                    <img src="{{ asset('img/min/Admision-Pukllasunchis-01.webp') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row align-items-center">
                            <div class="col-xl-6 col-md-6">
                                <div class="business_info">
                                    <h3>EESPP:</h3>
                                    <p class="text-justify">Nuestra finalidad es desarrollar un programa alternativo e
                                        interesante, con un
                                        enfoque inclusivo e intercultural, que contribuya a la formación de profesionales
                                        capaces de aportar, desde el aula o desde otros ámbitos de trabajo al desarrollo de
                                        nuevos escenarios educativos.</p><br>
                                    <ul>
                                        <li><a href="{{ route('primaria') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Educación Primaria</a></li>
                                        <li><a href="{{ route('primariaEIB') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Educación Primaria Intercultural (EIB)</a></li>
                                        <li><a href="{{ route('profesionalizacion') }}"><i class="fa fa-caret-right"
                                                    style="font-size: 10px"></i>
                                                Profesionalización Docente</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="business_thumb">
                                    <img src="{{ asset('img/min/Programas-Pukllasunchis-01.webp') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="row align-items-center">
                            <div class="col-xl-6 col-md-6">
                                <div class="business_info">
                                    <h3>Grado de Bachiller</h3>
                                    <p class="text-justify">
                                        Grado de bachiller
                                        El grado de bachiller es el reconocimiento de la formación educativa y académica que
                                        se otorga al egresado de una EESP al haber culminado un PE o un PPD de manera
                                        satisfactoria y cumplido con los requisitos establecidos para tal fin.
                                    </p>
                                    <h3>Título profesional de licenciado en educación </h3>
                                    <p class="text-justify">
                                        Grado de bachiller
                                        El grado de bachiller es el reconocimiento de la formación educativa y académica que
                                        se otorga al egresado de una EESP al haber culminado un PE o un PPD de manera
                                        satisfactoria y cumplido con los requisitos establecidos para tal fin.
                                    </p>
                                    <h3>Título de segunda Especialidad</h3>
                                    <p class="text-justify">
                                        Título de segunda especialidad profesional
                                        Es el reconocimiento que se obtiene al haber realizado una especialidad profesional.
                                    </p>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="business_thumb">
                                    <img src="{{ asset('img/min/Titulacion-Pukllasunchis-01.webp') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid parallax">
        <div class="row d-flex justify-content-center align-items-center text-center">
            <div class="col-lg-12 mx-auto">
                <h2 class="text-white">Proyectos </h2>
                <div class="linea-white"></div>
                <p class="text-white">
                    Los estudiantes, además de impulsar estrategias de aprendizaje situadas en la realidad del país,
                    desarrollan proyectos académicos para la inclusión de niños y niñas con habilidades diferentes, y
                    mecanismos para una gestión novedosa de los servicios de modo que sean pertinentes a la edad, a las
                    características de las familias y del grupo sociocultural a los que van dirigidos.
                </p>
            </div>
            <div class="row mt-4">
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>1.</span>
                            <h5 class="card-title text-white"> Taller de cerámica</h5>
                            <p class="card-text text-white">
                                En el taller de cerámica tratamos temas de territorialidad, tomando como referencia la
                                cuatripartición...
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>2.</span>
                            <h5 class="card-title text-white"> Taller de radio</h5>
                            <p class="card-text text-white">
                                El taller radio contribuye a que los estudiantes descubran las múltiples intersecciones de
                                sus identidades...
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>3.</span>
                            <h5 class="card-title text-white"> Explorando nuestra identidad </h5>
                            <p class="card-text text-white">
                                Nuestras raíces son importantes, revelan nuestra identidad. Los estudiantes de I Ciclo se
                                identificaron...
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>4.</span>
                            <h5 class="card-title text-white">Currículo y educación intercultural</h5>
                            <p class="card-text text-white">
                                Visibilizar el potencial de la diversidad de nuestro país hará que enriquezcamos nuestras
                                miradas, nuestros...
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>5.</span>
                            <h5 class="card-title text-white"> Fortaleciendo nuestra lengua, nuestra cultura</h5>
                            <p class="card-text text-white">
                                En el taller de cerámica tratamos temas de territorialidad, tomando como referencia la
                                cuatripartición...
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mb-3">
                    <div class="card bg-transparent border-white mx-auto" style="width: 18rem;">
                        <div class="card-body">
                            <span>6.</span>
                            <h5 class="card-title text-white"> Identidad, ciudadanía e interculturalidad</h5>
                            <p class="card-text text-white">
                                El curso coloca a los estudiantes en el centro del proceso educativo para potenciar la
                                afirmación de sus...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="bolsa-registro" class="department_area bolsa-registro-section">
        <div class="container py-5">
            <div class="row g-4 g-lg-5 align-items-stretch">
                <div class="col-lg-6 d-flex">
                    <div class="bolsa-registro-intro w-100">
                        <span class="bolsa-registro-eyebrow">
                            <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                            Vinculación laboral
                        </span>
                        <h2 class="bolsa-registro-title">Bolsa de trabajo</h2>
                        <p class="bolsa-registro-lead text-justify">
                            ¿Necesitas encontrar a alguien para tu equipo? <strong>Publica tu oferta</strong> aquí y
                            aparecerá en nuestra
                            bolsa de trabajo, donde muchas personas podrán verla.
                        </p>
                        <div class="bolsa-registro-actions d-flex flex-wrap align-items-center gap-3 mt-4">
                            <a href="{{ route('bolsa') }}" class="boxed-btn3 bolsa-registro-btn-primary">
                                <i class="fa-solid fa-arrow-right-to-bracket me-2" aria-hidden="true"></i>
                                Ver ofertas publicadas
                            </a>
                            {{-- <button type="button" class="bolsa-registro-btn-outline" id="btnAbrirFormBolsa">
                                <i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>
                                Publicar oferta
                            </button> --}}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex">
                    <div class="bolsa-registro-card w-100">
                        <div class="bolsa-registro-card-accent" aria-hidden="true"></div>
                        <div class="bolsa-registro-card-inner">
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bolsa-registro-card-icon" aria-hidden="true">
                                    <i class="fa-solid fa-file-circle-plus"></i>
                                </div>
                                <div>
                                    <h3 class="bolsa-registro-card-heading h4 mb-1">Publicar oportunidad laboral</h3>
                                    <p class="bolsa-registro-card-sub text-muted mb-0">
                                        Completa el formulario y tu convocatoria se publicará en pocos pasos.
                                    </p>
                                </div>
                            </div>
                            <ul class="bolsa-registro-features list-unstyled mb-4">
                                <li>
                                    <span class="bolsa-registro-feature-icon"><i class="fa-solid fa-globe"
                                            aria-hidden="true"></i></span>
                                    <span>Visible para todos en la bolsa de trabajo.</span>
                                </li>
                                <li>
                                    <span class="bolsa-registro-feature-icon"><i class="fa-solid fa-filter"
                                            aria-hidden="true"></i></span>
                                    <span>Consulta ofertas filtradas por año y mes.</span>
                                </li>
                                {{--  <li>
                                    <span class="bolsa-registro-feature-icon"><i class="fa-solid fa-bolt"
                                            aria-hidden="true"></i></span>
                                    <span>Proceso guiado sin salir de esta página.</span>
                                </li> --}}
                            </ul>
                            <button type="button" class="boxed-btn3 bolsa-registro-card-cta w-100"
                                id="btnAbrirFormBolsaCard">
                                <i class="fa-solid fa-window-restore me-2" aria-hidden="true"></i>
                                Abrir formulario
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bolsa-registro-section {
            position: relative;
            padding: 4.5rem 0;
            overflow: hidden;
            background: linear-gradient(145deg, #faf7f2 0%, #f3ede4 45%, #ebe3d6 100%);
        }

        .bolsa-registro-section::before {
            content: "";
            position: absolute;
            top: -20%;
            right: -10%;
            width: 55%;
            max-width: 520px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(205, 146, 68, 0.14) 0%, transparent 70%);
            pointer-events: none;
        }

        .bolsa-registro-intro {
            position: relative;
            z-index: 1;
            padding: 0.5rem 0;
        }

        .bolsa-registro-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #8b6914;
            background: rgba(205, 146, 68, 0.12);
            border: 1px solid rgba(205, 146, 68, 0.28);
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }

        .bolsa-registro-title {
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
            position: relative;
            padding-bottom: 0.65rem;
        }

        .bolsa-registro-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 56px;
            height: 4px;
            border-radius: 2px;
            background: linear-gradient(90deg, #cd9244, #e0b366);
        }

        .bolsa-registro-lead {
            color: #444;
            font-size: 1.02rem;
            line-height: 1.65;
            margin-bottom: 0;
        }

        .bolsa-registro-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            box-shadow: 0 8px 24px rgba(205, 146, 68, 0.35);
        }

        .bolsa-registro-btn-primary:hover {
            box-shadow: 0 6px 18px rgba(205, 146, 68, 0.25);
        }

        .bolsa-registro-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            font-size: 15px;
            font-weight: 500;
            padding: 16px 28px;
            border-radius: 4px;
            border: 2px solid #cd9244;
            background: #fff;
            color: #cd9244 !important;
            cursor: pointer;
            text-transform: capitalize;
            transition: color 0.35s ease, background 0.35s ease, border-color 0.35s ease, transform 0.2s ease;
        }

        .bolsa-registro-btn-outline:hover {
            background: #cd9244;
            color: #fff !important;
            border-color: #cd9244;
        }

        .bolsa-registro-btn-outline:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(205, 146, 68, 0.35);
        }

        .bolsa-registro-card {
            position: relative;
            z-index: 1;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(40, 35, 25, 0.12), 0 2px 8px rgba(40, 35, 25, 0.06);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .bolsa-registro-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 60px rgba(40, 35, 25, 0.14), 0 4px 12px rgba(40, 35, 25, 0.08);
        }

        .bolsa-registro-card-accent {
            height: 5px;
            border-radius: 16px 16px 0 0;
            background: linear-gradient(90deg, #cd9244, #d9a85c, #b87a2a);
        }

        .bolsa-registro-card-inner {
            padding: 1.75rem 1.75rem 2rem;
        }

        @media (min-width: 992px) {
            .bolsa-registro-card-inner {
                padding: 2rem 2.25rem 2.25rem;
            }
        }

        .bolsa-registro-card-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(145deg, rgba(205, 146, 68, 0.18), rgba(205, 146, 68, 0.06));
            color: #a67428;
            font-size: 1.35rem;
        }

        .bolsa-registro-card-heading {
            font-weight: 700;
            color: #222;
        }

        .bolsa-registro-features li {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 0.55rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            font-size: 0.95rem;
            color: #3d3d3d;
            line-height: 1.5;
        }

        .bolsa-registro-features li:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .bolsa-registro-feature-icon {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(205, 146, 68, 0.12);
            color: #b07d2e;
            font-size: 0.85rem;
        }

        .bolsa-registro-card-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            padding-top: 16px;
            padding-bottom: 16px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var openers = document.querySelectorAll('#btnAbrirFormBolsa, #btnAbrirFormBolsaCard');
            openers.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    bolsaGlobalRegistroModalOpen();
                });
            });
            if (window.location.hash === '#bolsa-registro') {
                bolsaGlobalRegistroModalOpen();
            }
        });
    </script>
    <section class="fondoLogo">
        <div class="container pt-4 pb-4">
            <div class="row pt-5 pb-5">
                <div class="col-lg-12 text-center mb-4">
                    <h2>Solicitar informes</h2>
                    <div class="linea-medio mb-4"></div>
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="#">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <input type="text" class="form-control" id="name"
                                    placeholder="Ingrese su nombre:*" required>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="email" class="form-control" id="email"
                                    placeholder="Escriba su email:*" required>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="number" class="form-control" id="phone"
                                    placeholder="Número WhatsApp:*" required>
                            </div>
                            <div class="form-group col-md-6">
                                <select class="form-control" id="program">
                                    <option value="" disabled selected>Seleccione un programa</option>
                                    <option value="AdmisionOrdinario">Admisión Ordinario</option>
                                    <option value="AdmisionTraslado">Admisión Traslado</option>
                                    <option value="AdmisionBeca">Admisión Beca</option>
                                    <option value="ProfesionalizacionDocente">Profesionalización Docente</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-12">
                                <textarea name="mensaje" id="mensaje" class="form-control" placeholder="Escriba acá su consulta..."></textarea>
                            </div>
                            <div class="col-lg-12">
                                <small class="text-danger">Los campos con * son obligatorios.</small>
                            </div>
                            <div class="col-md-12 text-center mb-5">
                                {{-- <button type="submit" class="btn-puklla">Enviar</button> --}}
                                <button class="btn-puklla">
                                    Enviar
                                    <div class="puklla__horizontal"></div>
                                    <div class="puklla__vertical"></div>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="col-lg-6">
                    <iframe
                        src="https://www.google.com/maps/d/u/0/embed?mid=1LlYZJvOMKBdFofhf2HrIfrFAy9l8r2c&ehbc=2E312F&noprof=1"
                        width="100%" height="280"></iframe>
                </div>
            </div>
        </div>
    </section>
@endsection
