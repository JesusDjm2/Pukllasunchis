@extends('layouts.home')
@section('metas')
    @php $titulo = 'Área de Práctica Pre Profesional'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Tiene como finalidad que los estudiantes de los últimos ciclos del programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area lineas bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Líneas / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Líneas</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('tutoria') }}"><i class="fa fa-caret-right fa-sm"></i> Tutoria</a></li>
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Bienestar y
                                Empleabilidad</a></li>
                        <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Investigación</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Práctica pre
                                profesional</a></li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Subvenciones y Becas</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    El área de Práctica Profesional tiene como finalidad que los estudiantes de los últimos ciclos del
                    programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas y la
                    complejidad de cada una de ellas (cultural, lingüístico, geográfico, etc.) para que identifiquen,
                    analicen, reflexionen y optimicen roles, funciones y acciones inherentes al trabajo docente. Así mismo
                    que los futuros docentes reconceptualicen la teoría desde la práctica y viceversa, generen conocimiento
                    pedagógico a través de la investigación, consolidando el logro de las competencias profesionales de la
                    carrera docente.
                </p>
                <div class="row justify-content-center align-items-center fichas">
                    <div class="col-lg-6 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Protologo.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Convocados.webp') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>
                                <p class="text-center">
                                    Si eres estudiante o egresado, para ver las ofertas laborales disponibles haz clic en
                                    <span class="text-primary">Ver Vacantes.</span>
                                </p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScv-XydNmgyMDOEBUeyRpbPDXP4jTRAEYXDgyOyputHVx6n7A/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Institucional.webp') }}"
                                        alt="Cronograma Pukllasunchis">
                                </div>
                                <p class="text-center">Si eres representante
                                    de una institución educativa, para registrar
                                    las ofertas laborales de tu Institución haz clic en <span class="text-primary">
                                        Instituciones Educativas</span></p>
                            </a>
                        </div>
                    </div>
                </div>

                <table class="table table-hover table-bordered mt-4">
                    <thead>
                        <tr>
                            <th scope="col">ETAPAS DE LA PRÁCTICAS PROFESIONALES</th>
                            <th scope="col">DURACIÓN/CICLO</th>
                            <th scope="col">COMPONENTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1ra Etapa: Contacto con la realidad educativa e inicio de la
                                    sistematización.</strong><br>
                                Ser docente en el Perú: Reconocimiento de diversos contextos educativos, estudio de la
                                realidad de los niños y familias que asisten a un centro educativo. Descripción de la
                                diversidad cultural de un aula, tanto en zona urbana como rural. <br>
                                Pasantías programadas y guiadas a instituciones educativas públicas o privadas, con
                                características EIB, EBR, rurales, urbanas.</td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>I A IV CICLO</li>
                                    <li>36 horas semestrales</li>
                                </ul>
                            </td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>Facilitación del Aprendizaje.</li>
                                    <li>Gestión.</li>
                                    <li>Taller de sistematización.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>2da Etapa: Profundización y sistematización.</strong><br>
                                <ul class="listasCuerpo">
                                    <li>Análisis del Diseño Curricular Nacional y alternativo. </li>
                                    <li>Análisis las orientaciones metodológicas de cada área. </li>
                                    <li>Actividades de ayudantía en instituciones educativas públicas o privadas en zonas
                                        urbanas, urbano marginales y rurales.</li>
                                </ul>
                            </td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>V A VIII CICLO</li>
                                    <li>De 60 a 120 horas semestrales</li>
                                </ul>
                            </td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>Facilitación del Aprendizaje. </li>
                                    <li>Gestión</li>
                                    <li>Taller de sistematización.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>3ra Etapa: Practicas intensivas.</strong><br>
                                Desarrollo de experiencias profesionales en condiciones reales de trabajo, ahora a
                                distancia, es decir introducir al estudiante de los últimos ciclos, en instituciones
                                educativas públicas o privadas, en zonas urbanas, urbano-marginales y rurales.</td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>IX Y X CICLO </li>
                                    <li>350 horas semestrales aprox.</li>
                                </ul>
                            </td>
                            <td>
                                <ul class="listasCuerpo">
                                    <li>Facilitación del Aprendizaje. </li>
                                    <li>Gestión</li>
                                    <li>Taller de sistematización.</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
