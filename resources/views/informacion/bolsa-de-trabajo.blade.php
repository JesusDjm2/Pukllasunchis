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
            <div class="col-lg-3">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Información</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('novedades') }}"><i class="fa fa-caret-right fa-sm"></i> Novedades</a></li>
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Artículos</a></li>
                        <li><a href="{{ route('proyectos') }}"><i class="fa fa-caret-right fa-sm"></i> Proyectos
                                Académicos</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Innovaciones</a>
                        </li>
                        <li><a href="{{ route('bolsa') }}"><i class="fa fa-caret-right fa-sm"></i> Bolsa de Trabajo</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <h3 class="mt-3">¿Cómo funciona?</h3>
                <ul class="listasCuerpo">
                    <li>Recibimos tu Curriculum o solicitud de empleo.</li>
                    <li>Analizamos tus datos (experiencia laboral, logros, habilidades, intereses, etc).</li>
                    <li>Te vinculamos con las instituciones educativas acorde a tu perfil.</li>
                    <li>Le brindamos seguimiento a tu curriculum o solicitud, el cual permanecerá dentro de nuestra cartera
                        de talentos en un periodo de 6 meses.</li>
                    <li>Las instituciones educativas vinculadas se pondrán en contacto contigo para agendar una entrevista.
                    </li>
                </ul>
                <div class="row justify-content-center align-items-center fichas">
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
                </div>


                {{-- Convocatorias laborales --}}
                <div class="container mt-5">
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
                                    {{-- <button class="boton-puklla" data-bs-toggle="modal"
                                        data-bs-target="{{ route('conv2') }}">Ver Detalles</button> --}}
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

                        <!-- Card 2 -->
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

                    <!-- Modal 1 -->
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

                    <!-- Modal 2 -->
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

                </div>


                <div class="row mt-3">
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
                </div>
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
