    @extends('layouts.home')
    @section('metas')
        @php $titulo = 'Profesionalización Docente'; @endphp
        <title>{{ $titulo }} - EESPP Pukllasunchis </title>
        <meta name="description"
            content="Los programas de profesionalización buscan desarrollar competencias para una práctica pedagógica abierta, conectada con la vida y la cultura y con las necesidades de nuestro tiempo.">
        <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    @endsection
    @section('contenido')
        <style>
            .steps-container {
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background-color: #f7f9fc;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .steps-title {
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 30px;
            }

            .step {
                display: flex;
                align-items: normal;
                margin-bottom: 20px;
                opacity: 0;
                transform: translateY(20px);
                animation: fadeIn 0.6s forwards;
            }

            .step+.step {
                margin-top: 30px;
            }

            /* Animación de entrada */
            @keyframes fadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }

                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Icono del paso */
            .step-icon {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #d59d52;
                color: white;
                border-radius: 50%;
                font-size: 18px;
                font-weight: bold;
                margin-right: 20px;
                transition: background-color 0.3s ease;
            }

            .step-icon:hover {
                background-color: #bd7a1a;
            }

            /* Contenido del paso */
            .step-content h3 {
                font-size: 18px;
                color: #2c3e50;
                margin: 0 0 10px;
            }

            .step-content p {
                font-size: 14px;
                color: #7f8c8d;
                margin: 0;
            }

            /* Animación de los pasos */
            .step:nth-child(1) {
                animation-delay: 0.2s;
            }

            .step:nth-child(2) {
                animation-delay: 0.4s;
            }

            .step:nth-child(3) {
                animation-delay: 0.6s;
            }

            .step:nth-child(4) {
                animation-delay: 0.8s;
            }

            /* Estilo de los pasos completados */
            .step.completed .step-icon {
                background-color: #27ae60;
            }

            .step.completed .step-icon:hover {
                background-color: #2ecc71;
            }

            .step.completed .step-content h3 {
                color: #27ae60;
            }
        </style>
        <div class="bradcam_area docente bradcam_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="bradcam_text">
                            <h3 class="tituloAnimado">{{ $titulo }}</h3>
                            <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> {{ $titulo }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container pt-5 pb-5">
            <div class="row">
                <div class="col-lg-3">
                    <div class="pegajoso">
                        <h3 class="linea-debajo">Contenido</h3>
                        <ul class="submenu2">
                            <li><a href="#duracion"><i class="fa fa-caret-right fa-sm"></i> Duración</a></li>
                            <li><a href="#requisitos"><i class="fa fa-caret-right fa-sm"></i> Requisitos</a></li>
                            <li><a href="#contenido"><i class="fa fa-caret-right fa-sm"></i> Contenido</a></li>
                        </ul>
                        <h3 class="linea-debajo mt-5">Más programas</h3>
                        <ul class="submenu2">
                            <li><a href="{{ route('inicial') }}"><i class="fa fa-caret-right fa-sm"></i> Educación
                                    Inicial</a>
                            </li>
                            <li><a href="{{ route('primaria') }}"><i class="fa fa-caret-right fa-sm"></i> Educación
                                    Primaria</a>
                            </li>
                            <li><a href="{{ route('primariaEIB') }}"><i class="fa fa-caret-right fa-sm"></i> Educación
                                    Primaria
                                    EIB</a>
                            </li>
                            <li><a href="{{ route('formacion') }}"><i class="fa fa-caret-right fa-sm"></i> Formación
                                    Continua</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9">
                    <h2 class="linea-debajo">{{ $titulo }}</h2>

                    <p>
                        Los programas de Profesionalización de la Escuela de Educación Superior Pedagógica Pukllasunchis
                        buscan desarrollar competencias para una práctica pedagógica abierta, vinculada con la vida, la
                        cultura y las necesidades de nuestro tiempo.<br><br>

                        Estos programas ofrecen a los profesionales que desean convertirse en docentes la oportunidad de
                        repensar la educación de la región y del país, e imaginar escenarios educativos de convivencia donde
                        la cooperación, el interaprendizaje, el diálogo intercultural y el aprendizaje vivencial sean los
                        pilares para el desarrollo de metodologías y didácticas pertinentes que respondan a las realidades
                        socioculturales de niños y niñas en diversos contextos.
                    </p>

                    <h3 class="mt-5 linea-debajo scroolOk" id="duracion">Duración</h3>
                    <p>
                        El programa de profesionalización tiene una duración de un año repartidos en dos ciclos académicos
                        de 20 créditos en total cada uno.<br>

                    </p>
                    <ul class="listasCuerpo"><strong>Está dirigido a:</strong>
                        {{-- <li>Procedentes de programas de estudios distintos a Educación (carreras afines a nivel Inicial y
                            Primaria):
                            <ul>
                                <li>Bachilleres o titulados de universidades</li>
                                <li>Bachilleres o titulados de instituciones con rango universitario</li>
                            </ul>
                        </li> --}}
                        <li>Procedentes de carreras pedagógicas IESP o ISE:
                            <ul>
                                <li>Titulados como profesor del mismo nivel o especialidad del PPD al que desea acceder,
                                    Educación Inicial, Educación Primaria y Educación Primaria Intercultural (EIB)</li>
                            </ul>
                        </li>
                        {{-- <li>Procedentes de carreras pedagógicas de universidades o de instituciones con rango universitario:
                            <ul>
                                <li>Bachilleres o titulados en Educación</li>
                            </ul>
                        </li> --}}
                    </ul>



                    <h3 class="linea-debajo scroolOk mt-4" id="requisitos">Requisitos</h3>
                    <p>
                        ➤ Baucher de pago por inscripción (Pago en Caja Cusco).<br>
                        ➤ Copia de DNI ampliado<br>
                        ➤ Copia de Título legalizado o fedatado.<br>
                        ➤ 2 fotos tamaño pasaporte.<br>
                        ➤ Certificado de estudios superiores original.
                    </p>

                    <h3 class="linea-debajo scroolOk mt-3" id="requisitos">Plan de Estudios</h3>

                    <div class="row justify-content-center align-items-center mt-4">
                        <div class="col-lg-4 text-center">
                            <a href="{{ asset('pdf/Plan-de-Estudios-PPD-INICIAL-2026.pdf') }}" target="_blank">
                                <img class="pb-3 text-center" src="{{ asset('img/pdf-puklla.webp') }}" width="100px"
                                    alt="Profesionalizacion docente PDF"><br>
                                Plan de Estudios Educación Inicial
                            </a>
                        </div>
                        <div class="col-lg-4 text-center">
                            <a href="{{ asset('pdf/Plan-de-Estudios-PPD-PRIMARIA-EIB-2026.pdf') }}" target="_blank">
                                <img class="pb-3 text-center" src="{{ asset('img/pdf-puklla.webp') }}" width="100px"
                                    alt="Profesionalizacion docente PDF"><br>
                                Plan de Estudios Educación Primaria EBR
                            </a>
                        </div>
                        <div class="col-lg-4 text-center">
                            <a href="{{ asset('pdf/Plan-de-Estudios-PPD-PRIMARIA-EBR-2026.pdf') }}" target="_blank">
                                <img class="pb-3 text-center" src="{{ asset('img/pdf-puklla.webp') }}" width="100px"
                                    alt="Profesionalizacion docente PDF"><br>
                                Plan de Estudios Educación Primaria EIB
                            </a>
                        </div>
                        <!-- Nueva columna con infografía del proceso de admisión -->
                        {{-- <div class="col-lg-4 text-center">
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#infografiaModal">
                                <img src="{{ asset('img/Infografia-PPD.png') }}"
                                    alt="Infografía del Proceso de Admisión" class="img-fluid rounded shadow-sm"
                                    style="max-height: 180px; object-fit: cover;">
                                <p class="text-center mt-2">Ver Proceso de Admisión</p>
                            </a>
                        </div> --}}

                        @if ($periodoAdmisionActivo)
                            <div class="col-lg-4 text-center">
                                <a href="{{ route('postulantes.ppd.create') }}" target="_blank" class="text-center">
                                    <img width="160px" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                        alt="Ficha de Inscripción Pukllasunchis" class="img-fluid">
                                    <p class="text-center font-">¡Inscríbete acá!</p>
                                </a>
                            </div>
                        @endif


                    </div>

                    <!-- Modal para mostrar la infografía -->
                    <div class="modal fade" id="infografiaModal" tabindex="-1" aria-labelledby="infografiaModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="infografiaModalLabel">Proceso de Admisión</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="{{ asset('img/Infografia-PPD.png') }}"
                                        alt="Infografía del Proceso de Admisión" class="img-fluid">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <a href="{{ asset('img/Infografia-PPD.png') }}" class="btn btn-primary"
                                        download="Proceso-Admision-PPD.jpg">
                                        Descargar Infografía
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="steps-container">
                        <h2 class="steps-title">Pasos para la inscripción al examen de admisión</h2>
                        <div class="step" id="step1">
                            <div class="step-icon">
                                <span>1</span>
                            </div>
                            <div class="step-content">
                                <h3>Realiza el pago por Derecho de Inscripción</h3>
                                <p>s/150.00 (ciento cincuenta soles) en agentes de <span class="text-danger">Caja
                                        Cusco</span>
                                </p>
                                <p>Indicar concepto y código: EXAMEN DE ADMISION - 1 </p>
                            </div>
                        </div>

                        <div class="step" id="step2">
                            <div class="step-icon">
                                <span>2</span>
                            </div>
                            <div class="step-content">
                                <h3>Tomar una foto clara de los requisitos</h3>
                                <p>> Partida de nacimiento</p>
                                <p>> Certificado de Estudios de Secundaria</p>
                                <p>> Copia simple de DNI</p>
                                <p>> Fotografía (actual con fondo blanco)</p>
                                <p>> Voucher de pago (Paso 1)</p>
                            </div>
                        </div>

                        <div class="step" id="step3">
                            <div class="step-icon">
                                <span>3</span>
                            </div>
                            <div class="step-content">
                                <h3>Completar el formulario de Inscripción</h3>
                                @if ($periodoAdmisionActivo)
                                    <p>
                                        Ingresa a nuestro formulario:
                                        <a class="text-primary" href="{{ route('postulantes.ppd.create') }}"
                                            target="_blank">
                                            Formulario <i class="fa fa-external-link fa-sm"></i>
                                        </a>
                                    </p>
                                    <p>Lee atentamente los enunciados e ingresa los datos solicitados</p>
                                @else
                                    <p class="text-danger">
                                        No nos encontramos en periodo de Admisión.
                                        <br>Publicaremos próximas fechas de admisión por nuestras redes sociales
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="step" id="step4">
                            <div class="step-icon">
                                <span>4</span>
                            </div>
                            <div class="step-content">
                                <h3>Confirmación de inscripción</h3>
                                <p>Al finalizar tu inscripción recibirás tu <strong>Comprobante de inscripción</strong> en
                                    tu
                                    correo.</p>
                            </div>
                        </div>
                    </div>


                    <div class="row pt-3 justify-content-center align-items-center">
                        <div class="col-lg-12 pt-5">
                            {{-- <img src="{{ asset('img/banner/PPD-2025.webp') }}" width="100%"
                                alt="Programa de profesionalización Pukllasunchis"> --}}
                            <iframe width="100%" height="450" src="https://www.youtube.com/embed/hk5tG7aFQ9U"
                                title="Admisión Profesionalización Docente 2026 | EESP Pukllasunchis" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                    </div><br>



                    <div class="accordion mt-4" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Por la SUNEDU (para grados y títulos otorgados por universidades o instituciones con
                                    rango universitario).
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <ul class="listasCuerpo">
                                        <li>Copia del grado o título fedateada.</li>
                                        <li>Constancia del Grado de Bachiller o título profesional que esté debidamente
                                            registrado en SUNEDU.</li>
                                        <li>Certificados originales de estudios superiores emitidos por su UNIVERSIDAD.</li>
                                        <li>Constancia de trabajo o un documento que acredite labor como docente en
                                            ejercicio o la realización de alguna actividad pedagógica.</li>
                                        <li>Copia de DNI ampliado.</li>
                                        <li>2 fotos tamaño pasaporte.</li>
                                        <li>Pago por derecho de inscripción.</li>
                                    </ul>

                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    En la DRE correspondiente (para los títulos de profesor).
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <ul class="listasCuerpo">
                                        <li>Copia fedateada del Título Pedagógico por la Dirección Regional de Educación
                                            (DRE), con su copia respectiva de la Resolución Directoral de Inscripción en su
                                            DRE.</li>
                                        <li>Certificado de estudios superiores original.</li>
                                        <li>Pago por derecho de inscripción.</li>
                                        <li>Copia de DNI ampliado.</li>
                                        <li>2 fotos tamaño pasaporte.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <p>
                        Al respecto, se precisa que el literal a) del numeral 15 2 del artículo 15 de la Ley Nº 30512 no
                        recoge
                        de forma expresa alguna prohibición para que una persona que tenga grado de bachiller en Educación
                        pueda
                        acceder a un PPD (Informe 148 2022 sobre los programas de profesionalización docente)
                    </p>
                    {{-- <h3 class="linea-debajo scroolOk" id="contenido">Programa de profesionalización: módulos, horas y
                        créditos
                    </h3><br><br> --}}


                    {{-- <table class="table table-bordered text-center table-responsive border-primary">
                        <tbody style="background: #afa0c7!important">
                            <tr>
                                <th></th>
                                <th>Módulos</th>
                                <th>Cursos</th>
                                <th></th>
                                <th>Cr</th>
                                <th>HT</th>
                                <th>HP</th>
                            </tr>
                            <tr style="background: #fff">
                                <td colspan="7">Ciclo Ordinario</td>
                            </tr>
                            <tr>
                                <td class="align-middle" rowspan="3">Formación General: Marco Socio Político Cultural.
                                </td>
                                <td style="background:#fccdff" class="align-middle" rowspan="3">Cultura y Diversidad:
                                    Retos para una Educación Intercultural.</td>
                                <td style="background:#fccdff">Circulo Intercultural: Conceptos Básicos y Nudos
                                    Problemáticos
                                </td>
                                <td style="background:#fccdff">96</td>
                                <td style="background:#fccdff">3</td>
                                <td style="background:#fccdff">64</td>
                                <td style="background:#fccdff">32</td>
                            </tr>
                            <tr style="background:#fccdff">
                                <td style="background:#fccdff">Infancias y Familia: ¿Qué niño y niña educamos?</td>
                                <td style="background:#fccdff">64</td>
                                <td style="background:#fccdff">3</td>
                                <td style="background:#fccdff">32</td>
                                <td style="background:#fccdff">32</td>
                            </tr>
                            <tr>
                                <td style="background:#fccdff">Fundamentos y Condiciones para una Pedagogía Intercultural.
                                </td>
                                <td style="background:#fccdff">80</td>
                                <td style="background:#fccdff">3</td>
                                <td style="background:#fccdff">16</td>
                                <td style="background:#fccdff">64</td>
                            </tr>
                            <tr>
                                <td class="align-middle" rowspan="2">Formación Específica: Pedagogía Didáctica
                                    Intercultural
                                </td>
                                <td style="background:#fccdff" class="align-middle" rowspan="2">Educación
                                    Intercultural:
                                    Reflexiones para una Práctica Intercultural.</td>
                                <td style="background:#fccdff">Atención a la Diversidad en la Educación.</td>
                                <td style="background:#fccdff">96</td>
                                <td style="background:#fccdff">4</td>
                                <td style="background:#fccdff">32</td>
                                <td style="background:#fccdff">64</td>
                            </tr>
                            <tr>
                                <td style="background:#fccdff">Planificación de Competencias y Evaluación para el
                                    Aprendizaje</td>
                                <td style="background:#fccdff">80</td>
                                <td style="background:#fccdff">3</td>
                                <td style="background:#fccdff">16</td>
                                <td style="background:#fccdff">64</td>
                            </tr>
                            <tr>
                                <td>Formación en la Práctica Pedagógica y la Investigación Educativa.</td>
                                <td class="align-middle">Investigación e Innovación</td>
                                <td class="align-middle">Investigación/Observación y Práctica Reflexiva/Escenarios
                                    Alternativos Educación I</td>
                                <td class="align-middle">96</td>
                                <td class="align-middle">4</td>
                                <td class="align-middle">32</td>
                                <td class="align-middle">64</td>
                            </tr>
                            <tr style="background:#fccdff">
                                <td></td>
                                <td></td>
                                <td class="align-middle">Ciclo Ordinario</td>
                                <td class="align-middle">512</td>
                                <td class="align-middle">20</td>
                                <td class="align-middle">192</td>
                                <td class="align-middle">320</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered text-center table-responsive border-dark">
                        <tbody style="background: #e0cdff">
                            <tr>
                                <td class="align-middle" rowspan="5">Formación Específica: Pedagogía y Didáctica
                                    Intercultural</td>
                                <td style="background:#dcfdff" rowspan="2" class="align-middle">Metodologías y
                                    Proyectos de Aprendizaje Intercultural</td>
                                <td style="background:#dcfdff">Proyectos de Aprendizaje: Competencias Identidad/Convivencia
                                    y Participación</td>
                                <td style="background:#dcfdff">64</td>
                                <td style="background:#dcfdff">3</td>
                                <td style="background:#dcfdff">32</td>
                                <td style="background:#dcfdff">32</td>
                            </tr>
                            <tr style="background:#dcfdff">
                                <td>Desarrollo de Competencias Matemáticas y Científicas</td>
                                <td>64</td>
                                <td>3</td>
                                <td>32</td>
                                <td>32</td>
                            </tr>
                            <tr style="background:#dcfdff">
                                <td rowspan="3" class="align-middle">Competencias comunicativas</td>
                                <td>Desarrollo de competencias comunicativas: lectura, escritura y argumentación</td>
                                <td>64</td>
                                <td>3</td>
                                <td>32</td>
                                <td>32</td>
                            </tr>
                            <tr style="background:#dcfdff">
                                <td class="align-middle">Cuerpo y arte en la escuela primaria</td>
                                <td>80</td>
                                <td>3</td>
                                <td>16</td>
                                <td>64</td>
                            </tr>
                            <tr style="background:#fccdff">
                                <td>EL clima del aula y el desarrollo de espacios de diálogo para la vida comunitaria</td>
                                <td>80</td>
                                <td>3</td>
                                <td>16</td>
                                <td>64</td>
                            </tr>
                            <tr>
                                <td>Formación en la práctica pedagógica y la investigación educativa.</td>
                                <td class="align-middle">Investigación e innovación</td>
                                <td class="align-middle">Investigación/Desarrollo de proyectos de innovación educativa con
                                    perspectiva intercultural</td>
                                <td class="align-middle">128</td>
                                <td class="align-middle">5</td>
                                <td class="align-middle">32</td>
                                <td class="align-middle">96</td>
                            </tr>
                            <tr style="background:#fccdff">
                                <td colspan="2"></td>
                                <td class="align-middle">Ciclo Ordinario</td>
                                <td class="align-middle">480</td>
                                <td class="align-middle">20</td>
                                <td class="align-middle">160</td>
                                <td class="align-middle">320</td>
                            </tr>
                            <tr class="bg-white">
                                <td colspan="2"></td>
                                <td class="align-middle">Ciclo Ordinario</td>
                                <td class="align-middle">480</td>
                                <td class="align-middle">20</td>
                                <td class="align-middle">160</td>
                                <td class="align-middle">320</td>
                            </tr>
                        </tbody>
                    </table> --}}
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>
    @endsection
