@extends('layouts.home')
@section('metas')
    @php $titulo = 'Bienestar y Empleabilidad'; @endphp
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
                        {{-- <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Bienestar y
                                Empleabilidad</a></li> --}}
                        <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Investigación</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Práctica pre
                                profesional</a></li>
                        <li><a href="{{route('subvenciones')}}"><i class="fa fa-caret-right fa-sm"></i> Subvenciones y Becas</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">Bienestar y Empleabilidad</h2>
                <p>
                    El área de Bienestar y Empleabilidad es el órgano de línea responsable de la orientación profesional,
                    tutoría, consejería, bolsa de trabajo, bolsa de práctica pre-profesional y profesional, emprendimiento u
                    otros que coadyuven al tránsito de los estudiantes y egresados de la Escuela de Educación Superior
                    Pedagógica Privada Pukllasunchis al empleo. Depende de la Dirección General.
                </p>
                <h3 class="mt-3">Son funciones la Unidad de Bienestar y Empleabilidad:</h3>
                <ul class="listasCuerpo">
                    <li>Planificar, supervisar y evaluar las actividades que se orientan al desarrollo físico,
                        psicoafectivo, social y cultural de la comunidad académica institucional.</li>
                    <li>Desarrollar actividades de orientación profesional, tutoría, consejería, bolsa de trabajo, bolsa de
                        práctica preprofesional y profesional.</li>
                    <li>Propiciar acciones de Promoción Social mediante programas y proyectos que desarrollan los
                        estudiantes, a través de la práctica docente.</li>
                    <li>Proponer políticas de bienestar y salud integral de los estudiantes de la EESPM.</li>
                    <li>Diseñar, ejecutar y evaluar programas psicopedagógicos y de salud integral, prevención, nutrición,
                        oportunidades laborales y otros.</li>
                    <li>Desarrollar programas de tutorías y asesoramiento personal y académico a los estudiantes, durante su
                        proceso formativo. Participar en el proceso de admisión en la evaluación psicopedagógica de los
                        postulantes.</li>
                    <li>Administrar el Tópico Médico, brindando atención primaria de salud eficiente y oportuna al alumno,
                        docente y personal administrativo en general, contribuyendo al bienestar físico, mental y social de
                        nuestra comunidad educativa.</li>
                    <li>Gestionar convenios con organismos nacionales e internacionales para el apoyo y obtención de
                        recursos en los programas que establece la Unidad. </li>
                    <li>Sistematizar y difundir información referida a la realidad de los estudiantes.</li>
                    <li>Promover actividades deportivas y de promoción cultural que estimulen el desempeño y
                        desenvolvimiento personal y académico, contribuyendo al desarrollo de una formación académica y
                        personal saludable.</li>
                    <li>Normar, organizar y coordinar el servicio de comedor estudiantil, cautelando su funcionamiento en
                        condiciones de orden, limpieza e higiene. </li>
                    <li>Crear y promover una bolsa de trabajo y de prácticas a toda la comunidad estudiantil, para su
                        inmediata inserción al mundo laboral.</li>
                    <li>Conformar un Comité de Defensa del Estudiante, encargado de velar por el bienestar de los
                        estudiantes para la prevención y atención en casos de acoso, discriminación, entre otros.</li>
                    <li>Implementar un sistema de seguimiento a egresados que permita mantener actualizada la evolución
                        profesional y personal de los egresados y la relación que mantiene con la institución, así como
                        validar el perfil de egreso.</li>
                    <li>Otras funciones que le delegue la Dirección General.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
