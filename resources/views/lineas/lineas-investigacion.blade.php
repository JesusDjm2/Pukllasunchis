@extends('layouts.home')
@section('metas')
    @php $titulo = 'Área de Investigación'; @endphp
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
                        <li><a href="{{ route('tutoria') }}"><i class="fa fa-caret-right fa-sm"></i> Tutoria</a></li>
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Bienestar y
                                Empleabilidad</a></li>
                        {{-- <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Investigación</a>
                        </li> --}}
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Práctica pre
                                profesional</a></li>
                        <li><a href="{{route('subvenciones')}}"><i class="fa fa-caret-right fa-sm"></i> Subvenciones y Becas</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    El área de Investigación propicia el desarrollo de investigación aplicada e innovación educativa
                    articulando la práctica en educación básica durante el proceso de formación profesional. Asimismo,
                    gestiona trabajos de investigación con fines de graduación y titulación según normativa institucional.
                </p>
                <h3 class="mt-3">Son funciones la Unidad de Investigación:</h3>
                <ul class="listasCuerpo">
                    <li>Establecer las líneas de investigación pertinentes con las necesidades nacionales e institucionales
                        de la actividad docente.</li>
                    <li>Planificar la actividad de investigación con la participación de docentes y estudiantes.</li>
                    <li>Organizar el desarrollo de actividades de investigación: recursos, líneas de investigación,
                        metodologías, asignación de responsabilidades.</li>
                    <li>Supervisar el desarrollo de las actividades de investigación. e) Evaluar las líneas y trabajos de
                        investigación, incluyendo sus recursos, procesos y resultados.</li>
                    <li>Impulsar, proponer y facilitar líneas de investigación institucionales a cargo de los docentes
                        investigadores de la Escuela.</li>
                    <li>Diseñar, evaluar y coordinar políticas y lineamientos de investigación realizadas en todas las áreas
                        académicas.</li>
                    <li>Desarrollar investigación aplicada e innovación que generen conocimientos para la mejora del proceso
                        formativo y productivo. </li>
                    <li>Vincular la investigación a la práctica pedagógica como parte del proceso formativo.</li>
                    <li>Supervisar y apoyar proyectos y acciones de investigación derivadas de convenios y contratos
                        suscritos con entidades nacionales e internacionales.</li>
                </ul>
                <h3 class="mt-3">Líneas de Investigación </h3>
                <p>
                    Conciben el trabajo interdisciplinario e intradisciplinario y orientan la investigación de estudiantes y
                    docentes cuyo asunto de estudio es la EESPP. Cada línea de investigación considera conocimientos,
                    prácticas y perspectivas de análisis para el desarrollo de proyectos y productos de investigación
                    (tesina, tesis, proyectos de investigación e innovación) construidos de manera sistemática alrededor de
                    un tema de estudio.<br><br>

                    Las líneas de investigación institucional permiten orientar la Investigación Aplicada e Innovación
                    Educativa propuestas en los trabajos de investigación con fines de graduación y titulación, así como al
                    programar la articulación Investigación-Práctica como eje transversal en formación profesional. Permiten
                    organizar las investigaciones realizadas por personas e instituciones y la recopilación de resultados
                    obtenidos con fines institucionales.
                </p>
            </div>
        </div>
    </div>
@endsection
