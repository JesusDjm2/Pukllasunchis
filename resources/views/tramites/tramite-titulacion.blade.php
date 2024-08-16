@extends('layouts.home')
@section('metas')
    @php $titulo = 'Proceso de Titulación'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection
@section('contenido')
    <div class="bradcam_area admision bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a>Trámites / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso">
                    <h3 class="linea-debajo">Trámites</h3>
                    <ul class="submenu2">
                        <li><a href="{{route('plan')}}"><i class="fa fa-caret-right fa-sm"></i> Plan de Trabajo</a></li>
                        <li><a href="{{route('tinvestigacion')}}"><i class="fa fa-caret-right fa-sm"></i> Trabajo de Investigación</a></li>
                        <li><a href="{{route('tesis')}}"><i class="fa fa-caret-right fa-sm"></i> Tesis</a></li>
                        <li><a href="{{route('tramites')}}"><i class="fa fa-caret-right fa-sm"></i> Trámites presenciales</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <h3>Grado de bachiller</h3>
                <p>El grado de bachiller es el reconocimiento de la formación educativa y académica que se otorga al
                    egresado de una EESP al haber culminado un PE o un PPD de manera satisfactoria y cumplido con los
                    requisitos establecidos para tal fin.
                </p>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-12 mb-4">
                        <h3>Inicia tu trámite aquí:</h3>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{route('plan')}}"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Plan de trabajo de <br>investigación</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ route('tinvestigacion') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Bachillerato <br>Trabajo de Investigación</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ route('tesis') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Tesis <br>Licenciatura</p>
                            </a>
                        </div>
                    </div>
                </div>
                <h3 class="mt-5">Requisitos para solicitar el grado de bachiller:</h3>
                <ul class="listasCuerpo">
                    <li>Solicitud dirigida al Director General de la EESP.</li>
                    <li>Certificado que acredite estudios por un mínimo de diez (10) ciclos académicos y la aprobación de un
                        mínimo de doscientos (200) créditos del PE o del PPD docente correspondiente</li>
                    <li>Documento que acredite la aprobación del trabajo de investigación para optar el grado de bachiller.
                    </li>
                    <li>Documento que acredite conocimiento de un idioma extranjero o de una lengua originaria:
                        En caso de un idioma extranjero, el egresado debe acreditar mediante un certificado expedido por una
                        institución oficial nacional o internacional como mínimo el nivel A2 del MCER (básico).</li>
                </ul>
                <p class="generic-blockquote mt-4">
                    <strong>Niveles de inglés A (usuario básico)</strong><br>
                    <strong>Test de inglés A2:</strong> Es capaz de comprender frases y expresiones de uso frecuente
                    relacionadas con áreas de experiencia que le son especialmente relevantes (información básica sobre sí
                    mismo y su familia, compras, lugares de interés, ocupaciones, etc.). Sabe comunicarse a la hora de
                    llevar a cabo tareas simples y cotidianas que no requieran más que intercambios sencillos y directos de
                    información sobre cuestiones que le son conocidas o habituales. Sabe describir en términos sencillos
                    aspectos de su pasado y su entorno, así como cuestiones relacionadas con sus necesidades inmediatas.<br>
                    <a href="https://tracktest.eu/es/niveles-de-ingles-mcer-cefr/#Ingl%C3%A9susuariobasico" target="_blanck"
                        style="color: #ff5e13"><strong>Niveles de inglés MCER (CEFR)</strong></a>
                </p>
                <p>
                    En caso de una lengua originaria, para la obtención del bachiller es necesario remitirse a los niveles
                    de desarrollo de competencias comunicativas de los DCBN de Educación Inicial Intercultural Bilingüe y
                    Educación Primaria Intercultural Bilingüe.
                </p>
                <h3 class="mt-4">Trabajo de investigación para obtener el grado de bachiller</h3>
                <p>La aprobación del trabajo de investigación es una condición para optar el grado de bachiller.
                    <br> <br>
                    El trabajo de investigación para grado de bachiller es un trabajo que tiene estrecha relación con los
                    principales ejes planteados en el plan de estudios académico.<br> <br>
                    A través de este trabajo, el graduando debe demostrar que domina, de manera general, los aspectos
                    centrales desarrollados en el currículo. Dicho trabajo supone el desarrollo de aptitudes y habilidades
                    relacionadas con el perfil de egreso, de forma previa a la exigencia profesional, y supone el
                    planteamiento de un tema preciso a analizar relacionado con situaciones detectadas en la realidad
                    educativa, preferentemente relacionadas con el programa de estudios cursado.<br> <br>
                    Las actividades para el desarrollo del trabajo incluyen:<br>
                    - La revisión de la literatura actualizada<br>
                    Desarrollo de una metodología de análisis de la información recolectada<br>
                    - Exposición de resultados.<br><br>
                    El trabajo de investigación es de elaboración progresiva por parte del estudiante. En el caso de la FID,
                    se desarrolla en el marco del componente curricular de práctica e investigación de los planes de
                    estudios. La institución debe designar un docente idóneo para el acompañamiento del estudiante en la
                    elaboración del trabajo de investigación.
                </p>
                <h2 class="mt-4 linea-debajo">Título profesional de licenciado en educación </h2>
                <p>
                    Es el reconocimiento que obtiene el bachiller luego de haber aprobado una tesis o trabajo de suficiencia
                    profesional.
                </p>
                <h3 class="mt-4">Requisitos para solicitar el Título de licenciado en educación: </h3>
                <ul class="listasCuerpo">
                    <li>Solicitud dirigida al Director General de la EESP.</li>
                    <li>Documento que acredite contar con el grado de bachiller en el Registro Nacional de Grados y Títulos
                        de la SUNEDU.</li>
                    <li>Documento que acredite la aprobación de la sustentación de tesis o del trabajo de suficiencia
                        profesional.</li>
                </ul>
                <h2 class="linea-debajo">Título de segunda especialidad profesional </h2>
                <p>Es el reconocimiento que se obtiene al haber realizado una especialidad profesional.
                </p>
                <h3 class="mt-4">Requisitos</h3>
                <ul class="listasCuerpo">
                    <li> Solicitud dirigida al Director General de la EESP.</li>
                    <li>Documento que acredite contar con el grado de bachiller en el Registro Nacional de Grados y Títulos
                        de la SUNEDU.</li>
                    <li>Documento que acredite la aprobación de la sustentación de tesis o del trabajo de suficiencia
                        profesional.</li>
                    <li>Documento que acredite contar con título de licenciado u otro título profesional debidamente
                        registrado y que sea afín o equivalente a la especialidad. La equivalencia la determina la EESP en
                        su RI.</li>
                    <li>Certificado de estudios que acredite una duración mínima de dos (2) semestres académicos y un
                        contenido mínimo de cuarenta (40) créditos.</li>
                    <li>Documento que acredite la aprobación de la sustentación de tesis o del trabajo o académico.</li>
                </ul>

                <!-----Acordion ejemplo----------->
                <div class="accordion mt-4" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="duplicado">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseDuplicado" aria-expanded="true" aria-controls="collapseDuplicado">
                                Duplicado de Grado y Títulos
                            </button>
                        </h2>
                        <div id="collapseDuplicado" class="accordion-collapse collapse show" aria-labelledby="duplicado"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Previo a la entrega del diploma y cumplidos todos los requisitos para optar el grado o
                                    título, el interesado podrá solicitar a la EESP la expedición de una constancia en
                                    trámite de dicho documento.
                                </p>
                                <h3 class="mt-4">Requisito:</h3>
                                <ul class="listasCuerpo">
                                    <li> Solicitud dirigida al Director General de la EES.</li>
                                    <li>Declaración jurada de pérdida, robo y/o deterioro.</li>
                                </ul>
                                <p>
                                    El duplicado del diploma de grado académico o título profesional anula automáticamente
                                    el diploma
                                    original.<br><br>
                                    Las instituciones realizan ante la SUNEDU los procedimientos de anulación de la
                                    inscripción en el
                                    Registro Nacional y la posterior inscripción de datos consignados en el duplicado del
                                    diploma, de
                                    acuerdo con la normativa emitida para tal fin.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Emisión de Grados y Títulos
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>En lo correspondiente a la emisión de duplicado de grado, esta se regirá por la normativa
                                    establecida por
                                    el MINEDU. Los requisitos para la emisión de títulos se detallan:
                                </p>
                                <ul class="listasCuerpo">
                                    <li> Solicitud dirigida al Director General de la EES.</li>
                                    <li>Declaración jurada de pérdida, robo y/o deterioro.</li>
                                </ul>
                                <p>
                                    El duplicado del diploma de grado académico o título profesional anula automáticamente
                                    el diploma
                                    original.<br><br>
                                    Las instituciones realizan ante la SUNEDU los procedimientos de anulación de la
                                    inscripción en el
                                    Registro Nacional y la posterior inscripción de datos consignados en el duplicado del
                                    diploma, de
                                    acuerdo con la normativa emitida para tal fin.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Rectificación del Diploma de Bachiller, Título de Licenciado
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>
                                    Los requisitos para el caso de rectificación de nombres o apellidos en diploma de
                                    bachiller, título de
                                    licenciado:
                                </p>
                                <ul class="listasCuerpo">
                                    <li>Solicitud dirigida al Director General del instituto, describiendo el error que se
                                        desea rectificar.
                                    </li>
                                    <li>Documento que acredite el nombre o apellido y/o dato académico por ser rectificado.
                                    </li>
                                </ul>
                                <p>
                                    La rectificación por causal de error en dato académico se regula en el RI de la
                                    institución.<br><br>
                                    El pago por los derechos de tramitación no aplica para los casos en los que institución
                                    incurra en error
                                    al momento de emitir el diploma de bachiller o título de licenciado.<br><br>
                                    La rectificación del diploma de bachiller o título profesional de licenciado se aprueba
                                    mediante
                                    resolución directoral.<br><br>
                                    Las instituciones realizan ante la SUNEDU los procedimientos de corrección de datos en
                                    el Registro
                                    Nacional de Grados y Títulos producidos por diferente causal, de acuerdo con la
                                    normativa emitida para
                                    tal fin.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Constancias y Certificados
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>
                                    La certificación es el proceso mediante el cual el interesado es informado sobre su
                                    situación académica
                                    al término de un periodo de estudios.<br><br>

                                    La constancia de egresado es el documento expedido por la institución que acredita la
                                    culminación de un
                                    Período Formativo.<br><br>

                                    Requisitos para solicitar la constancia de egresado:
                                </p>
                                <ul class="listasCuerpo">
                                    <li>Solicitud dirigida al Director General de la institución, la cual debe contener:
                                        Nombres y apellidos
                                        completos - Número de DNI o carné de extranjería - Denominación del PF - Periodo de
                                        ejecución -
                                        Fecha de culminación del programa cursado.
                                    </li>
                                    <li>Certificado de estudios de programas formativos.</li>
                                </ul>
                                <p>
                                    La rectificación por causal de error en dato académico se regula en el RI de la
                                    institución.<br><br>

                                    El pago por los derechos de tramitación no aplica para los casos en los que institución
                                    incurra en error
                                    al momento de emitir el diploma de bachiller o título de licenciado.<br><br>

                                    La rectificación del diploma de bachiller o título profesional de licenciado se aprueba
                                    mediante
                                    resolución directoral.<br><br>

                                    Las instituciones realizan ante la SUNEDU los procedimientos de corrección de datos en
                                    el Registro
                                    Nacional de Grados y Títulos producidos por diferente causal, de acuerdo con la
                                    normativa emitida para
                                    tal fin.<br><br>

                                    El certificado de estudios es el documento emitido por la institución a solicitud del
                                    interesado.
                                    Contiene los resultados del proceso de evaluación realizado durante el periodo
                                    formativo.<br><br>

                                    Requisitos para solicitar el certificado de estudios:<br><br>
                                </p>
                                <ul class="listasCuerpo">
                                    <li>Solicitud dirigida al Director General de la institución, la cual debe contener:
                                        Nombres y apellidos
                                        completos - Número de DNI o carné de extranjería - Denominación del PF - Periodo de
                                        ejecución -
                                        Fecha de culminación del programa cursado.</li>
                                </ul>
                                <p>
                                    La institución emite los certificados de estudios con base en la información contenida
                                    en las actas de
                                    evaluación respectivas.<br><br>

                                    La certificación se tramita y recaba en la institución en la que se han realizado los
                                    estudios. En caso
                                    de que la institución se encuentre cerrada, el interesado solicita el certificado de
                                    estudios a la DRE o
                                    la que haga sus veces para su atención.<br><br>

                                    La rectificación del certificado de estudios se rige por lo establecido en el artículo
                                    45 del Reglamento
                                    de la Ley N° 30512. El certificado de estudios es generado por el SIA.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>




            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
