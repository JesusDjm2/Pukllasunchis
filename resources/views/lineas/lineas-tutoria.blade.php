@extends('layouts.home')
@section('metas')
    @php $titulo = 'Programa de Tutoria'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
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
                        {{-- <li><a href="{{ route('tutoria') }}"><i class="fa fa-caret-right fa-sm"></i> Tutoria</a></li> --}}
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Bienestar y
                                Empleabilidad</a></li>
                        <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Investigación</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Práctica pre
                                profesional</a></li>
                        <li><a href="{{route('subvenciones')}}"><i class="fa fa-caret-right fa-sm"></i> Subvenciones y Becas</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">Programa de Tutoría</h2>
                <p>
                    Los alumnos de la EESP Pukllasunchis cuentan con un servicio de Tutoría desde que ingresan a la
                    institución. Todos los alumnos tienen un tutor asignado y pasan por tutoría como mínimo una vez por
                    semestre y con carácter obligatorio. La atención es diferenciada de acuerdo con los requerimientos
                    académicos y personales del estudiante.
                </p>
                <h3 class="mt-3">Objetivo</h3>
                <p>
                    Acompañar la formación integral de los estudiantes ofreciéndoles herramientas para su desarrollo
                    personal, sociocultural y académico, generando proyección profesional, desde su integración a la EESPP
                    hasta la culminación de sus estudios, trabajando de la mano con el perfil de egreso, haciendo énfasis en
                    el fortalecimiento de su identidad y su apertura al diálogo intercultural.
                </p>
                <h3 class="mt-3">Funciones</h3>
                <p>
                    Los tutores tienen en su cargo la coordinación de un ciclo y la responsabilidad de hacer acompañamiento
                    a todos los estudiantes del ciclo asignado. El grupo de tutores debe reunirse, como mínimo, una vez cada
                    quince días para revisar los casos de cada ciclo, conversar, proponer y gestionar acciones para
                    favorecer el proceso de cada estudiante. Además, los tutores deben brindar a sus estudiantes a cargo un
                    acompañamiento con características de contención emocional, a través del cual los estudiantes se sientan
                    atendidos y escuchados ante situaciones adversas.
                </p>
                <h3 class="mt-3">Contención emocional </h3>
                <p>
                    Para garantizar que los docentes hagan un proceso de contención emocional básico adecuado, se han
                    diseñado jornadas de formación, a través de las cuales se capacita a los tutores para fortalecer sus
                    habilidades de escucha y comunicación asertiva, de tal manera que puedan identificar situaciones
                    particulares y atender aspectos básicos de este aspecto.
                </p>
                <h3 class="mt-3">Contención emocional continuada (CEC) </h3>
                <p>
                    La EESPP cuenta con una persona que brinda un apoyo más profesional para los casos que no puedan ser
                    atendidos por los tutores. A esta persona se derivan los casos que son identificados por los tutores de
                    ciclo como prioritarios o urgentes según sus características, esto con la intención de cuidar el
                    bienestar emocional de nuestros estudiantes, y que, a su vez, puedan continuar o fortalecer sus estudios
                    y alcanzar una estabilidad académica.
                </p>
                <h3 class="mt-3">Áreas del programa de tutoría: </h3>
                <ul class="listasCuerpo">
                    <li><strong>Área Personal:</strong> En esta área se revisa las dificultades o soporte de apoyo para los
                        estudiantes en el desarrollo de una personalidad sana y equilibrada, que les permita actuar con
                        confianza en su entorno social y académico.</li>
                    <li><strong>Á Área Académica: </strong> Acompaña, asesora y guía a los estudiantes en el ámbito
                        académico, para que obtengan un mejor rendimiento de sus actividades educativas y puedan prevenir o
                        superar posibles dificultades.</li>
                    <li><strong>Área de Salud Físico y Mental:</strong> Promueve la adquisición de estilos de vida saludable
                        en los estudiantes. Se revisa de manera conjunta los resultados obtenidos de la evaluación realizada
                        por PEMA (Programa de Evaluación Médica Anual del Estudiante)</li>
                    <li><strong>Área de Reglamento Académico:</strong>Se orienta al estudiante a poder establecer una
                        convivencia académica, contribuyendo a mantener una interrelación optima con sus pares, así como
                        también se busca contribuir a conocer el reglamento académico y disciplinario de los estudiantes en
                        el marco del respeto a las normas de convivencia académica.</li>
                    <li><strong>Área de Cultura y Actualidad:</strong> Promueve que el estudiante conozca y valore su
                        cultura, reflexione sobre temas de actualidad, involucrándose así con su entorno social.</li>
                </ul>
                <h2 class="mt-3 linea-debajo">Características del programa de tutoría </h2>
                <div class="row justify-content-center align-items-center fichas mt-3">
                    <div class="col-lg-12">
                        <img src="{{ asset('img/Pages/Caracteristicas-tutorias.svg') }}"
                            alt="Caracterpisticas del programa de tutoria" width="80%">
                    </div>
                </div>
                <h2 class="mt-3 linea-debajo">Apoyo y Beneficios para Estudiantes </h2>
                <p>
                    Debido a la coyuntura mundial que generó la pandemia, y comprendiendo las limitaciones económicas y
                    oportunidades escasas que presentan nuestros estudiantes, la EESPP ha diseñado tres beneficios al que
                    pueden acceder, siempre y cuando cumplan con características particulares que garanticen el
                    aprovechamiento académico de su formación.
                </p>
                <ul class="listasCuerpo">
                    <li>Préstamo de tablets</li>
                    <li>Bono de datos</li>
                    <li>Descuento económico</li>
                </ul>
                <p class="mt-3"><strong>NOTA:</strong> el acceso a estos beneficios está a disposición de los estudiantes
                    después que formalicen su
                    solicitud y garantizando un nivel académico mínimo durante el ciclo.</p>
                <h3 class="mt-3">PRONABEC</h3>
                <p>
                    De acuerdo a los lineamientos de PRONABEC, señalan que el sistema de tutoría está diseñado a la
                    necesidad de los estudiantes becarios, estableciendo una tutoría para población vulnerable (académico y
                    emocional)<br><br>

                    Se estableció criterios diagnóstico previo para la intervención del sistema de tutoría y consejería:
                </p>
                <h3 class="mt-3">Niveles de riesgo de becarios de PRONABEC</h3>
                <div class="row justify-content-center align-items-center fichas mt-3">
                    <div class="col-lg-12">
                        <img src="{{ asset('img/Pages/niveles-de-riesgo-pronabec.webp') }}"
                            alt="Caracterpisticas del programa de tutoria" width="100%">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
