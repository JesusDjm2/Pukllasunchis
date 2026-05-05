@extends('layouts.home')
@section('metas')
    @php $titulo = 'Bolsa de Trabajo'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Tiene como finalidad que los estudiantes de los últimos ciclos del programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area novedades bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Información / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">            
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <h2 class="linea-debajo">{{ $titulo }}</h2>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirFormBolsaPagina">
                        Registrar nueva oferta
                    </button>
                    <span class="text-muted small">El formulario se abre aquí mismo, sin salir de esta página.</span>
                </div>
                @include('partials.bolsa-ofertas-catalogo', ['bolsaRouteName' => 'bolsa'])
                <style>
                    #bolsaPaginaRegistroOverlay {
                        position: fixed;
                        inset: 0;
                        z-index: 10060;
                        display: flex;
                        align-items: flex-start;
                        justify-content: center;
                        padding: 2rem 1rem;
                        overflow-y: auto;
                        box-sizing: border-box;
                        background: rgba(0, 0, 0, 0);
                        opacity: 0;
                        visibility: hidden;
                        pointer-events: none;
                        transition: background 0.34s ease, opacity 0.34s ease, visibility 0.34s ease;
                    }

                    #bolsaPaginaRegistroOverlay.is-open {
                        background: rgba(0, 0, 0, 0.72);
                        opacity: 1;
                        visibility: visible;
                        pointer-events: auto;
                    }

                    .bolsa-pagina-registro-modal-panel {
                        position: relative;
                        width: 100%;
                        max-width: 720px;
                        margin: auto;
                        background: #fff;
                        border-radius: 12px;
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                        padding: 1.5rem 1.5rem 2rem;
                        transform: translateY(22px) scale(0.96);
                        opacity: 0;
                        transition: transform 0.42s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.36s ease,
                            box-shadow 0.42s ease;
                    }

                    #bolsaPaginaRegistroOverlay.is-open .bolsa-pagina-registro-modal-panel {
                        transform: translateY(0) scale(1);
                        opacity: 1;
                        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.28);
                    }

                    #bolsaPaginaRegistroModalClose {
                        position: absolute;
                        top: 0.5rem;
                        right: 0.75rem;
                        font-size: 2rem;
                        line-height: 1;
                        color: #333;
                        background: transparent;
                        border: 0;
                        cursor: pointer;
                        padding: 0.25rem 0.5rem;
                        z-index: 2;
                        opacity: 0.75;
                        transition: opacity 0.2s ease, color 0.2s ease, transform 0.2s ease;
                    }

                    #bolsaPaginaRegistroModalClose:hover {
                        color: #000;
                        opacity: 1;
                        transform: scale(1.06);
                    }

                    @media (prefers-reduced-motion: reduce) {

                        #bolsaPaginaRegistroOverlay,
                        .bolsa-pagina-registro-modal-panel,
                        #bolsaPaginaRegistroModalClose {
                            transition: none !important;
                        }

                        #bolsaPaginaRegistroOverlay.is-open .bolsa-pagina-registro-modal-panel {
                            transform: none;
                        }
                    }
                </style>

                <div id="bolsaPaginaRegistroOverlay" role="dialog" aria-modal="true"
                    aria-labelledby="bolsaPaginaRegistroTitulo"
                    onclick="if (event.target === this) bolsaPaginaRegistroModalClose();">
                    <div class="bolsa-pagina-registro-modal-panel" onclick="event.stopPropagation();">
                        <button type="button" id="bolsaPaginaRegistroModalClose" onclick="bolsaPaginaRegistroModalClose();"
                            aria-label="Cerrar">&times;</button>
                        <h4 id="bolsaPaginaRegistroTitulo" class="mb-3 pr-4">Nuevo registro — Bolsa de trabajo</h4>
                        @if ($errors->any() && old('form_context') === 'bolsa_oferta')
                            <div class="alert alert-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @include('partials.bolsa-oferta-registro-form', [
                            'prefix' => 'info_modal_bolsa',
                            'redirectTo' => 'bolsa',
                            'listadoAnio' => request('anio'),
                            'listadoMes' => request('mes'),
                        ])
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
                <script>
                    var bolsaPaginaTinyMceInited = false;
                    var bolsaPaginaRegistroCloseTimer = null;

                    function bolsaPaginaRegistroModalOpen() {
                        var overlay = document.getElementById('bolsaPaginaRegistroOverlay');
                        if (!overlay) return;
                        if (bolsaPaginaRegistroCloseTimer) {
                            clearTimeout(bolsaPaginaRegistroCloseTimer);
                            bolsaPaginaRegistroCloseTimer = null;
                        }
                        document.body.style.overflow = 'hidden';
                        if (!overlay.classList.contains('is-open')) {
                            overlay.classList.remove('is-open');
                            void overlay.offsetWidth;
                            requestAnimationFrame(function() {
                                requestAnimationFrame(function() {
                                    overlay.classList.add('is-open');
                                });
                            });
                        }
                        if (!bolsaPaginaTinyMceInited && typeof tinymce !== 'undefined') {
                            tinymce.init({
                                selector: '#info_modal_bolsa_detalles',
                                height: 280,
                                menubar: false,
                                plugins: 'lists link',
                                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
                                language: 'es',
                                branding: false,
                                promotion: false,
                            });
                            bolsaPaginaTinyMceInited = true;
                        }
                    }

                    function bolsaPaginaRegistroModalClose() {
                        var overlay = document.getElementById('bolsaPaginaRegistroOverlay');
                        if (!overlay) return;
                        if (!overlay.classList.contains('is-open')) {
                            document.body.style.overflow = '';

                            return;
                        }
                        overlay.classList.remove('is-open');
                        bolsaPaginaRegistroCloseTimer = setTimeout(function() {
                            document.body.style.overflow = '';
                            bolsaPaginaRegistroCloseTimer = null;
                        }, 420);
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        var formBolsa = document.getElementById('info_modal_bolsa_form');
                        if (formBolsa) {
                            formBolsa.addEventListener('submit', function() {
                                if (typeof tinymce === 'undefined') {
                                    return;
                                }
                                var ed = tinymce.get('info_modal_bolsa_detalles');
                                if (ed) {
                                    ed.save();
                                } else {
                                    tinymce.triggerSave();
                                }
                            });
                        }
                        var btn = document.getElementById('btnAbrirFormBolsaPagina');
                        if (btn) {
                            btn.addEventListener('click', function() {
                                bolsaPaginaRegistroModalOpen();
                            });
                        }
                        @if ($errors->any() && old('form_context') === 'bolsa_oferta')
                            bolsaPaginaRegistroModalOpen();
                        @endif
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape') {
                                var o = document.getElementById('bolsaPaginaRegistroOverlay');
                                if (o && o.classList.contains('is-open')) bolsaPaginaRegistroModalClose();
                            }
                        });
                    });
                </script>

                {{-- <h3 class="mt-3">¿Cómo funciona?</h3>
                <ul class="listasCuerpo">
                    <li>Recibimos tu Curriculum o solicitud de empleo.</li>
                    <li>Analizamos tus datos (experiencia laboral, logros, habilidades, intereses, etc).</li>
                    <li>Te vinculamos con las instituciones educativas acorde a tu perfil.</li>
                    <li>Le brindamos seguimiento a tu curriculum o solicitud, el cual permanecerá dentro de nuestra cartera
                        de talentos en un periodo de 6 meses.</li>
                    <li>Las instituciones educativas vinculadas se pondrán en contacto contigo para agendar una entrevista.
                    </li>
                </ul> --}}
                {{-- <div class="row justify-content-center align-items-center fichas">
                    <div class="col-lg-6 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Protologo.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Convocados.png') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>
                                <p class="text-center">
                                    Si eres estudiante o egresado, para ver las ofertas laborales disponibles haz clic en <a
                                        href="" class="text-primary"> Ver Vacantes</a></span>
                                </p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-6 text-center">
                        <div class="card p-3">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScv-XydNmgyMDOEBUeyRpbPDXP4jTRAEYXDgyOyputHVx6n7A/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Institucional.png') }}"
                                        alt="Cronograma Pukllasunchis">
                                </div>
                                <p class="text-center">Si eres representante
                                    de una institución educativa, para registrar
                                    las ofertas laborales de tu Institución haz clic en <span class="text-primary">
                                        Instituciones Educativas</span>
                                </p>
                            </a>
                        </div>
                    </div>
                </div> --}}


                {{-- Convocatorias laborales --}}
               {{--  <div class="container mt-5">
                    <h2 class="linea-debajo">Convocatorias Laborales</h2>
                    <div class="row justify-content-center align-items-center">
                        <div class="col-lg-6 col-md-6 col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Profesional en Educación Intercultural</h5>
                                    <p class="card-text">
                                        Programa: mejoramiento de la calidad de vida de las familias, niñeces y adolescentes
                                        de comunidades rurales en Acomayo, Anta y Quispicanchis.
                                        <br>
                                        Hasta el 31 de marzo
                                    </p>
                                    <p><strong>Contacto:</strong> <a
                                            href="mailto:edelgado@kallpa.org.pe">edelgado@kallpa.org.pe</a>
                                    </p>
                                        <a href="{{ route('conv2') }}" class="boton-puklla">Ver convocatoria</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Personal para el año Escolar 2025</h5>
                                    <p class="card-text">
                                        UNSAAC - Facultad de Educación, Institución Educativa de Aplicación Fortunato L.
                                        Herrera
                                        <br>
                                        Del 11 al 16 de febrero del 2025
                                    </p>
                                    <p><strong>Contacto:</strong> <a
                                            href="mailto:convocatoria1@institucion.com">facebook.com/UNSAACPag.Oficial</a>
                                    </p>
                                    <button class="boton-puklla" data-bs-toggle="modal"
                                        data-bs-target="#modalConvocatoria1">Ver Detalles</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Asistente de Investigación</h5>
                                    <p class="card-text">Convocatoria laboral tiempo
                                        completo para "Asistente de investigación".<br>
                                        <strong>Fin de convocatoria: viernes 21 de febrero</strong>
                                    </p>
                                    <p><strong>Contacto:</strong> <a
                                            href="mailto:u.investigacion.eesp@pukllavirtual.edu.pe">u.investigacion.eesp@pukllavirtual.edu.pe</a>
                                    </p>
                                    
                                    <a href="{{ route('conv') }}" class="boton-puklla">Ver convocatoria</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalConvocatoria1" tabindex="-1" aria-labelledby="modalConvocatoria1Label"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalConvocatoria1Label">Convocatoria Personal para el año
                                        Escolar 2025</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="{{ asset('img/convocatorias/Convocatoria-unsaac.jpg') }}" class="img-fluid"
                                        alt="Convocatoria 1 Detalles">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="modalConvocatoria2" tabindex="-1"
                        aria-labelledby="modalConvocatoria2Label" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalConvocatoria2Label">Convocatoria de Asistente
                                        Administrativo - Detalles</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="{{ asset('img/convocatorias/asistente-investigacion.jpg') }}"
                                        class="img-fluid" alt="Convocatoria 2 Detalles">
                                </div>
                            </div>
                        </div>
                    </div>

                </div> --}}


                {{-- <div class="row mt-3">
                    <div class="col-lg-12 mb-3">
                        <h2 class="linea-debajo">Postulantes Bolsa de trabajo</h2>
                        <div id="accordion" class="collapseBolsa">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                                            aria-expanded="true" aria-controls="collapseOne"
                                            onclick="toggleArrow('arrowOne')">
                                            <span id="arrowOne" class="arrow fa fa-caret-down fa-sm"></span>
                                            Formación Inicial:
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mt-2 mb-3 text-center">
                                                <button class="btn btn-info btn-sm mb-2"
                                                    onclick="filtrarPorPerfil('Estudiante')">Estudiantes</button>
                                                <button class="btn btn-info btn-sm mb-2"
                                                    onclick="filtrarPorPerfil('Bachiller')">Bachilleres</button>
                                                <button class="btn btn-info btn-sm mb-2"
                                                    onclick="filtrarPorPerfil('Titulado')">Titulados</button>
                                                <button class="btn btn-info btn-sm mb-2"
                                                    onclick="filtrarPorPerfil('Egresado')">Egresados</button>
                                            </div>
                                            @foreach ($postulantes1 as $postulante)
                                                <div
                                                    class="col-lg-4 mb-3 tarjeta-usuario {{ strtolower($postulante->user->perfil) }}">
                                                    <div class="card-postulantes">
                                                        <div class="img">
                                                            <img loading="lazy" src="{{ asset($postulante->img) }}"
                                                                alt="{{ $postulante->nombre }}">
                                                        </div>
                                                        <div class="texto">
                                                            <h5 class="text-center">{{ $postulante->nombre }}
                                                                {{ $postulante->apellidos }}</h5>
                                                            <p class="text-center"><strong>Teléfono:</strong>
                                                                {{ $postulante->numero }}</p>
                                                            <p class="text-center"><strong>Email:</strong>
                                                                {{ $postulante->email }}</p>
                                                            <p class="text-center"><strong>Perfil:</strong>
                                                                {{ $postulante->user->perfil }}</p>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    @if ($postulante->cv)
                                                                        <div class="col-lg-4 mb-3">
                                                                            <a href="{{ asset($postulante->cv) }}"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-danger">
                                                                                Ver CV &nbsp;<i
                                                                                    class="fas fa-file-pdf fa-1x"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-6">
                                                                    <a href="#"
                                                                        class="btn-postulantes text-center toggle-description"
                                                                        data-toggle="modal"
                                                                        data-target="#descripcionModal{{ $loop->iteration }}">
                                                                        Detalles <small><i
                                                                                class="fa fa-eye fa-1x"></i></small>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade modalPostulante"
                                                    id="descripcionModal{{ $loop->iteration }}" tabindex="-1"
                                                    role="dialog"
                                                    aria-labelledby="descripcionModalLabel{{ $loop->iteration }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg"
                                                        role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-body">
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true"
                                                                        style="color: red">&times;</span>
                                                                </button>
                                                                <div class="img text-center">
                                                                    <img loading="lazy"
                                                                        src="{{ asset($postulante->img) }}"
                                                                        alt="{{ $postulante->nombre }}">
                                                                </div>
                                                                <h5 class="modal-title text-center mt-2 mb-2"
                                                                    id="descripcionModalLabel{{ $loop->iteration }}">
                                                                    {{ $postulante->nombre }}
                                                                    {{ $postulante->apellidos }}<br>
                                                                    <small>{{ $postulante->programa->nombre }} |
                                                                        {{ $postulante->user->perfil }}</small><br>
                                                                    @if ($postulante && isset($postulante->cv))
                                                                        <a href="{{ asset($postulante->cv) }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-danger mt-2">
                                                                            Ver CV &nbsp;<i
                                                                                class="fas fa-file-pdf fa-1x"></i>
                                                                        </a>
                                                                    @endif
                                                                </h5>
                                                                <p class="text-justify">
                                                                    <strong>Descripción:</strong><br>{{ $postulante->descripcion }}
                                                                </p>
                                                                <p class="text-justify"><strong>Otros
                                                                        estudios:</strong><br>{{ $postulante->otros_estudios }}
                                                                </p>
                                                                <p><strong>Idioma(s):</strong> {{ $postulante->idioma }}
                                                                </p>
                                                                <p><strong>Datos personales:</strong><br>
                                                                <ul>
                                                                    <li>Edad: {{ $postulante->edad }}</li>
                                                                    <li>Email: {{ $postulante->email }}</li>
                                                                    <li>DNI: {{ $postulante->dni }}</li>
                                                                    <li>Teléfono: {{ $postulante->numero }}</li>
                                                                </ul>
                                                                </p>
                                                                <div class="row">
                                                                    <div class="col-lg-12 mb-2">
                                                                        <p><strong>Sociales:</strong></p>
                                                                    </div>
                                                                    @if ($postulante->facebook)
                                                                        <div class="col-6 mb-3 text-center">
                                                                            <a href="{{ $postulante->facebook }}"
                                                                                target="_blank"
                                                                                class="btn-primary btn-sm">
                                                                                FaceBook <i
                                                                                    class="fab fa-facebook-square"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                    @if ($postulante->linkedin)
                                                                        <div class="col-6 text-center">
                                                                            <a href="{{ $postulante->linkedin }}"
                                                                                target="_blank" class="btn-info btn-sm">
                                                                                LinkedIn <i class="fab fa-linkedin"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingTwo">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
                                            onclick="toggleArrow('arrowTwo')">
                                            <span id="arrowTwo" class="arrow fa fa-caret-down fa-sm"></span>
                                            Formación Primaria EIB
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mt-2 mb-3 text-center">
                                                <button class="btn btn-info btn-sm"
                                                    onclick="filtrarPorPerfil('Estudiante')">Estudiantes</button>
                                                <button class="btn btn-info btn-sm"
                                                    onclick="filtrarPorPerfil('Bachiller')">Bachilleres</button>
                                                <button class="btn btn-info btn-sm"
                                                    onclick="filtrarPorPerfil('Titulado')">Titulados</button>
                                                <button class="btn btn-info btn-sm"
                                                    onclick="filtrarPorPerfil('Egresado')">Egresados</button>
                                            </div>
                                            @foreach ($postulantes2 as $postulante)
                                                <div
                                                    class="col-lg-4 mb-3 tarjeta-usuario {{ strtolower($postulante->user->perfil) }}">
                                                    <div class="card-postulantes">
                                                        <div class="img">
                                                            <img src="{{ asset($postulante->img) }}"
                                                                alt="{{ $postulante->nombre }}">
                                                        </div>
                                                        <div class="texto">
                                                            <h5 class="text-center">{{ $postulante->nombre }}
                                                                {{ $postulante->apellidos }}</h5>
                                                            <p class="text-center"><strong>Teléfono:</strong>
                                                                {{ $postulante->numero }}</p>
                                                            <p class="text-center"><strong>Email:</strong>
                                                                {{ $postulante->email }}</p>
                                                            <p class="text-center"><strong>Perfil:</strong>
                                                                {{ $postulante->user->perfil }}</p>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    @if ($postulante->cv)
                                                                        <div class="col-lg-4 mb-3">
                                                                            <a href="{{ asset($postulante->cv) }}"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-danger">
                                                                                Ver CV &nbsp;<i
                                                                                    class="fas fa-file-pdf fa-1x"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-6">
                                                                    <a href="#"
                                                                        class="btn-postulantes text-center toggle-description"
                                                                        data-toggle="modal"
                                                                        data-target="#descripcionModal2{{ $loop->iteration }}">
                                                                        Detalles <small><i
                                                                                class="fa fa-eye fa-1x"></i></small>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade modalPostulante"
                                                    id="descripcionModal2{{ $loop->iteration }}" tabindex="-1"
                                                    role="dialog"
                                                    aria-labelledby="descripcionModalLabel2{{ $loop->iteration }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg"
                                                        role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-body">
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true"
                                                                        style="color: red">&times;</span>
                                                                </button>
                                                                <div class="img text-center">
                                                                    <img src="{{ asset($postulante->img) }}"
                                                                        alt="{{ $postulante->nombre }}">
                                                                </div>
                                                                <h5 class="modal-title text-center mt-2 mb-2"
                                                                    id="descripcionModalLabel2{{ $loop->iteration }}">
                                                                    {{ $postulante->nombre }}
                                                                    {{ $postulante->apellidos }}<br>
                                                                    <small>{{ $postulante->programa->nombre }} |
                                                                        {{ $postulante->user->perfil }}</small><br>
                                                                    @if ($postulante && isset($postulante->cv))
                                                                        <a href="{{ asset($postulante->cv) }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-danger mt-2">
                                                                            Ver CV &nbsp;<i
                                                                                class="fas fa-file-pdf fa-1x"></i>
                                                                        </a>
                                                                    @endif
                                                                </h5>
                                                                <p class="text-justify">
                                                                    <strong>Descripción:</strong><br>{{ $postulante->descripcion }}
                                                                </p>
                                                                <p class="text-justify"><strong>Otros
                                                                        estudios:</strong><br>{{ $postulante->otros_estudios }}
                                                                </p>
                                                                <p><strong>Idioma(s):</strong> {{ $postulante->idioma }}
                                                                </p>
                                                                <p><strong>Datos personales:</strong><br>
                                                                <ul>
                                                                    <li>Edad: {{ $postulante->edad }}</li>
                                                                    <li>Email: {{ $postulante->email }}</li>
                                                                    <li>DNI: {{ $postulante->dni }}</li>
                                                                    <li>Teléfono: {{ $postulante->numero }}</li>
                                                                </ul>
                                                                </p>
                                                                <div class="row">
                                                                    <div class="col-lg-12 mb-2">
                                                                        <p><strong>Sociales:</strong></p>
                                                                    </div>
                                                                    @if ($postulante->facebook)
                                                                        <div class="col-6 mb-3 text-center">
                                                                            <a href="{{ $postulante->facebook }}"
                                                                                target="_blank"
                                                                                class="btn-primary btn-sm">
                                                                                FaceBook <i
                                                                                    class="fab fa-facebook-square"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                    @if ($postulante->linkedin)
                                                                        <div class="col-6 text-center">
                                                                            <a href="{{ $postulante->linkedin }}"
                                                                                target="_blank" class="btn-info btn-sm">
                                                                                LinkedIn <i class="fab fa-linkedin"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <style>
        .boton-puklla {
            background: #cb8b39;
            color: #fff;
            border: 1px solid #cb8b39;
            padding: 5px 14px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
@endsection
