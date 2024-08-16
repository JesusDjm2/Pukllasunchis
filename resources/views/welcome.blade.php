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
                                <a href="{{ route('admin') }}" class="boxed-btn3">Leer más</a>
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
                <div class="col-xl-6 col-lg-6">
                    <div class="welcome_docmed_info">
                        <h2>¿Quienes somos?</h2>
                        <h3>EESP PUKLLASUNCHIS</h3>
                        <p class="text-justify">
                            La EESP Pukllasunchis es una institución que asume un serio compromiso con la educación del Perú
                            y es sensible a las diferencias culturales, principalmente de la región y el país. Es un
                            proyecto alternativo que busca revisar los paradigmas tradicionales para reconstruir una
                            educación que sea inclusiva e intercultural. Desde esa perspectiva atendemos a una población
                            diversa entre la que se incluye a estudiantes de beca 18 procedentes de distintas comunidades
                            andinas.
                        </p>
                        <a href="{{ route('nosotros') }}" class="boxed-btn3-white-2">Leer más</a>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mt-4" style="width: 100%; height: 380px; background: #d59d52; padding:0.2em">
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/74k-QFuO2oo"
                        title="EESP Pukllasunchis" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap mensajes">
        <div class="seccion-left p-5">
            <div class="text-left">
                <!-- Contenido de la sección derecha -->
                <h3>Para Realizar las Prácticas Pre Profesionales </h3><br>
                <ul>
                    <li><strong> 1.- Llenar el Formato Único de Trámite (FUT):</strong>
                        <ul>
                            <li>• Presentar FUT en la Secretaría o llenar el formulario</li>
                            <li>• Costo: Sin costo.</li>
                            <li>• Como resultado de este trámite recibirás el oficio de presentación de prácticas y una
                                carta modelo de Aceptación.</li>
                            <li class="font-weight-bold">
                                Nota: Esta carta (modelo) es prueba de que la institución acepta su práctica- entregar en
                                secretaria
                            </li>
                        </ul>
                    </li><br>
                    <li><strong>2.- Solicitar la Resolución de Aprobación de Prácticas </strong>
                        <ul>
                            <li>• Hacer el pago de S/. 50 soles por derecho de RD de aprobación del practicas en Caja Cusco
                                como pago ordinario.</li>
                            <li>• Presentar FUT en la Secretaría:
                                <ul>
                                    <li>• Adjuntando Carta de aceptación de Practicas</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="seccion-right p-5">
            <div class="text-left">
                <!-- Contenido de la sección izquierda -->
                <h3>Para la Aplicación de Trabajo de Investigación</h3><br>
                <ul>
                    <li><strong> 1. Llenar el Formato Único de Trámite (FUT):</strong></li>
                    <ul>
                        <li>• Hacer el pago de S/ 10 soles.</li>
                        <li>• Presentar FUT en la Secretaría o llenar el formulario.</li>
                        <li>• Como resultado de este trámite recibirás la carta de aplicación de Trabajo de Investigación y
                            una carta modelo de Aceptación de Trabajo de Investigación. </li>
                    </ul><br>
                    <p class="font-weight-bold">Nota : ¿Para qué sirve? La Carta de Aceptación emitida por la institución
                        les ayudará a
                        completar su expediente para obtener el grado de bachiller.</p>

                </ul>
                <a href="{{ route('tinvestigacion') }}" class="boxed-btn3">Requisitos grado de  Bachiller</a>
            </div>
        </div>
    </div>

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
                            aula o desde otros ámbitos de trabajo al desarrollo de nuevos escenarios educativos. </p>
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
