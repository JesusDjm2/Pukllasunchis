@extends('layouts.home')
@section('metas')
    @php $titulo = 'Educación Primaria'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Esta especialidad se centra en jóvenes interesados en el cuidado y desarrollo de niños pequeños, fomentando entornos de juego y creatividad. Busca cultivar habilidades sociales y sensibilidad, promoviendo la convivencia y la conexión con la naturaleza.">
    <meta name="keywords" content="Educacio Inicial, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area inicial bradcam_overlay">
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
                        <li><a href="#ejes"><i class="fa fa-caret-right fa-sm"></i> Ejes Temáticos</a></li>
                        <li><a href="#plan"><i class="fa fa-caret-right fa-sm"></i> Plan de Estudios</a></li>
                        <li><a href="#perfil"><i class="fa fa-caret-right fa-sm"></i> Perfil del Egresado</a></li>
                    </ul>
                    <h3 class="linea-debajo mt-5">Más programas</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('inicial') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Inicial</a>
                        </li>
                        {{-- <li><a href="{{ route('primaria') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria</a></li> --}}
                        <li><a href="{{ route('primariaEIB') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria
                                EIB</a></li>
                        <li><a href="{{ route('formacion') }}"><i class="fa fa-caret-right fa-sm"></i> Formación
                                Continua</a></li>
                        <li><a href="{{ route('profesionalizacion') }}"><i class="fa fa-caret-right fa-sm"></i>
                                Profesionalización Docente</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    Esta especialidad está dirigida a jóvenes, interesados y con disposición para proponer alternativas
                    educativas que respondan a la realidad social y lingüística de los niños y niñas de primaria. Busca
                    desarrollar estrategias y experiencias que afirmen la identidad cultural y promuevan valores orientados
                    hacia la construcción de una ciudadanía intercultural.<br><br>

                    Además, los egresados serán capaces de:
                <ul class="listasCuerpo" style="padding-left: 10px">
                    <li>Desarrollar proyectos que incluyan a niños y niñas con capacidades diferentes.</li>
                    <li>Implementar mecanismos de gestión innovadores para organizar servicios educativos más pertinentes en
                        función de la edad, las familias y las características socioculturales del grupo al que están
                        dirigidos.</li>
                </ul>
                </p>
                <h3 class="mt-5 linea-debajo scroolOk" id="duracion">Duración</h3>

                <ul class="listasCuerpo" style="padding-left: 10px">
                    <li>El plan de estudios comprende diez ciclos académicos, con un total de 220 créditos.</li>
                    <li>Cada ciclo se desarrolla en dieciséis (16) semanas, con treinta (30) horas semanales. (DCBN 2019).
                    </li>
                    <li>Grado otorgado: Bachiller en Educación.</li>
                    <li>Título profesional: Licenciado en Educación Primaria.</li>
                </ul><br>

                <h3 class="linea-debajo scroolOk" id="ejes">Ejes Temáticos</h3>
                <p>
                    Los cursos de todas las carreras que el EESP ofrece, se organizan según seis ejes temáticos que expresan
                    la orientación de los cursos. Los ejes temáticos o campos de estudio son los siguientes:
                </p>
                <table class="table table-bordered table-hover">
                    <tbody>
                        <tr>
                            <th scope="row" class="align-middle">
                                Dinámicas socio-políticas, culturales y paradigmas de pensamiento en la educación y el
                                currículo.
                            </th>
                            <td>
                                Este eje reúne los cursos o áreas que permiten a los futuros docentes reflexionar sobre los
                                retos de la educación en el contexto de la crisis actual. Las asignaturas de esta área
                                colocan en perspectiva las dinámicas sociales y culturales de cada período histórico,
                                analizando cómo estas han influido y delineado los escenarios educativos y las orientaciones
                                curriculares.<br>

                                A partir de situaciones vivenciales y prácticas, los cursos proponen una reflexión crítica
                                sobre los paradigmas de pensamiento que impactan en la educación, siempre en diálogo con los
                                contextos sociales. Dicho análisis permitirá a los estudiantes plantear cambios viables y
                                pertinentes, tomando en cuenta la diversidad de factores que intervienen en dichos procesos.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Comunicación, lenguas y lenguajes expresivos</th>
                            <td>
                                Los cursos de este eje abordan los conceptos clave vinculados a los distintos códigos de
                                comunicación, promoviendo el desarrollo de las capacidades expresivas y el manejo de
                                diversos recursos. Entre estos se incluyen las tecnologías de la información y la
                                comunicación, orientadas a fortalecer los procesos educativos y a enriquecer la interacción
                                pedagógica.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Matemática y didáctica.</th>
                            <td>
                                Los cursos de este eje desarrollan las habilidades fundamentales para la comprensión del
                                lenguaje matemático y su aplicación en la resolución de diversas situaciones. Asimismo,
                                promueven el conocimiento de los conceptos curriculares de la matemática y fomentan la
                                comprensión de la lógica matemática en distintas culturas, favoreciendo una visión amplia e
                                intercultural de esta disciplina.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Aprendizaje, educación y gestión para la atención a la
                                diversidad.</th>
                            <td>
                                Este eje reúne los cursos que permiten a los estudiantes comprender los procesos de
                                aprendizaje de niños y niñas en diferentes contextos, considerando el impacto de los cambios
                                sociales en los ámbitos familiares y culturales. Asimismo, fomenta el desarrollo de
                                capacidades para diseñar propuestas didácticas y mecanismos de gestión que promuevan una
                                atención educativa participativa, inclusiva y pertinente.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Educación e investigación educativa.</th>
                            <td>
                                Los cursos de este eje buscan la apropiación de los recursos de observación,
                                problematización y análisis de situaciones a partir de las prácticas que se realizan desde
                                el primer año, en centros educativos urbanos y rurales y en comunidades. Incentivan la
                                adquisición de habilidades de investigación-acción como recurso para la mejora constante de
                                la práctica educativa.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Orientación y tutoría.</th>
                            <td>
                                Los cursos de esta área acompañan a los estudiantes en su desenvolvimiento socio-afectivo y
                                su avance académico teniendo como objetivo el potenciar su desarrollo como personas y como
                                parte de un colectivo.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <h3 class="linea-debajo scroolOk" id="plan">Plan de Estudios</h3>
                <p>Malla Curricular del DCBN del Programa de Estudios de Educación Inicial &nbsp;&nbsp;<a
                        href="{{ asset('pdf/plan-estudios-2020-educacion-primaria.pdf') }}" class="btn btn-primary"
                        target="_blank"> Descargar PDF</a>
                </p>
                <table class="table table-bordered text-center table-responsive">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="2">Año 1</th>
                            <th></th>
                            <th colspan="2">Año 2</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Ciclo I</th>
                            <th>Ciclo II</th>
                            <th class="table-active bg-light" rowspan="24"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">Proyecto Integrado 1</th>
                            <th>Ciclo III</th>
                            <th>Ciclo IV</th>
                            <th class="table-active bg-light" rowspan="24"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">Proyecto Integrado 2</th>
                        </tr>
                        <tr>
                            <td>Lectura y Escritura en la Escritura Superior</td>
                            <td>Comunicación Oral en la Educación Superior</td>
                            <td>Arte, Creatividad y Aprendizaje </td>
                            <td>Ciencia y Epistemologías</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td>Resolución de Problemas Matemáticos I </td>
                            <td>Resolución de Problemas Matemáticos II </td>
                            <td>Inglés para Principiantes I / Beginner English I (A1) </td>
                            <td>Inglés para Principiantes II / Beginner English II</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td>Desarrollo Personal I </td>
                            <td>Historia, Sociedad y Diversidad </td>
                            <td>Desarrollo Personal III </td>
                            <td>Deliberación y Participación</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background:#ffc10778!important">
                            <td>Práctica e Investigación I</td>
                            <td>Práctica e Investigación II</td>
                            <td>Práctica e Investigación III </td>
                            <td>Práctica e Investigación IV </td>
                        </tr>
                        <tr style="background:#ffc10778!important">
                            <td>6H-5 CR/4T- 2P</td>
                            <td>6H-5 CR/4T- 2P </td>
                            <td>6H-5 CR/4T- 2P </td>
                            <td>6H-5 CR/4T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr class="bg-warning">
                            <td>Desarrollo y Aprendizaje I </td>
                            <td>Desarrollo y Aprendizaje II</td>
                            <td>Corporeidad y Motricidad para el Aprendizaje y la Autonomía</td>
                            <td>Construcción de la Identidad y Ejercicio de la Ciudadanía</td>
                        </tr>
                        <tr class="bg-warning">
                            <td>6H-5 CR/4T- 2P</td>
                            <td>6H-5 CR/4T- 2P </td>
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr class="bg-warning">
                            <td>Fundamentos para la Educación Primaria</td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje I </td>
                            <td>Aprendizaje de las Matemáticas I </td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje II</td>
                        </tr>
                        <tr class="bg-warning">
                            <td>6H-5 CR/4T- 2P </td>
                            <td>6H-5 CR/4T- 2P </td>
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                        </tr>
                    </tbody>
                </table>

                <table class="table mt-4 table-bordered text-center table-responsive">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="2">Año 3</th>
                            <th></th>
                            <th colspan="2">Año 4</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Ciclo V</th>
                            <th>Ciclo VI</th>
                            <th class="table-active" rowspan="24"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">Proyecto Integrado 3</th>
                            <th>Ciclo VII </th>
                            <th>Ciclo VIII </th>
                            <th class="table-active" rowspan="24"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">Proyecto Integrado 4</th>
                        </tr>
                        <tr>
                            <td>Literatura y Sociedad en Contextos Diversos </td>
                            <td>Alfabetización científica</td>
                            <td>Ética y Filosofía en el Pensamiento Crítico </td>
                            <td style="background:#ffc10778!important">Práctica e Investigación III</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                            <td style="background:#ffc10778!important">12H-8CR/4T-8P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td>Inglés para Principiantes / Beginner English III A2</td>
                            <td>Inglés para Principiantes / Beginner English IV</td>
                            <td style="background:#ffc10778!important">Práctica e Investigación VII</td>
                            <td class="bg-warning">Espiritualidad y Manifestaciones Religiosas para el Aprendizaje</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                            <td style="background:#ffc10778!important">10H-7CR/4T-6P </td>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">Práctica e Investigación V</td>
                            <td style="background:#ffc10778!important">Práctica e Investigación VI</td>
                            <td class="bg-warning">Atención a la Diversidad y Nesecidades de Aprendizaje </td>
                            <td class="bg-warning">Aprendizaje de las Ciencias II </td>
                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P</td>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P </td>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">6H-5 CR/4T- 2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td class="bg-warning">Atención a la Diversidad y Nesecidades de Aprendizaje I </td>
                            <td class="bg-warning">Aprendizaje de las Ciencias Sociales</td>
                            <td class="bg-warning">Aprendizaje de las Matemáticas III </td>
                            <td class="bg-warning">Planificación por Competencias y Evaluación para el Aprendizaje III</td>
                        </tr>
                        <tr>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">4H-3 CR/2T- 2P </td>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">6H-3 CR/4T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td class="bg-warning">Aprendizaje de la Comunicación I</td>
                            <td class="bg-warning">Aprendizaje de las Ciencias I </td>
                            <td class="bg-warning">Aprendizaje de la Comunicación II </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">4H-3 CR/2T- 2P </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td class="bg-warning">Aprendizaje de las Matemáticas II </td>
                            <td class="bg-warning">Artes Integradas para el Aprendizaje </td>
                            <td> </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="bg-warning">4H-3 CR/2T- 2P</td>
                            <td class="bg-warning">4H-3 CR/2T- 2P </td>
                            <td></td>
                            <td> </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table mt-4 table-bordered text-center table-responsive">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="2">Año 5</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Ciclo IX</th>
                            <th>Ciclo X</th>
                            <th class="table-active" rowspan="8"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">
                                Informe de Investigación Destacada
                            </th>

                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">Práctica e Investigación IX</td>
                            <td style="background:#ffc10778!important">Práctica e Investigación X </td>
                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">4H-3 CR/2T-2P</td>
                            <td style="background:#ffc10778!important">4H-3 CR/2T-2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr class="bg-warning">
                            <td>Políticas y Gestión Educativa</td>
                            <td>Tutoría y Orientación Educativa</td>
                        </tr>
                        <tr class="bg-warning">
                            <td>2H-2 CR/2T</td>
                            <td>2H-2 CR/2T</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-sm table-warning text-center">
                    <tbody>
                        <tr>
                            <td>Electivo 1</td>
                            <td>Electivo 2</td>
                            <td>Electivo 3</td>
                            <td>Electivo 4</td>
                            <td>Electivo 5</td>
                            <td>Electivo 6</td>
                        </tr>
                        <tr>
                            <td>4H-3CR</td>
                            <td>4H-3CR</td>
                            <td>4H-3CR</td>
                            <td>4H-3CR</td>
                            <td>4H-3CR</td>
                            <td>4H-3CR</td>
                        </tr>
                    </tbody>
                </table>
                <br><br>
                <h3 class="linea-debajo scroolOk" id="perfil">Perfil del Egresado de Formación Inicial Docente</h3>
                <br><br>
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <a href="{{ asset('pdf/Perfil-educacion-primaria.pdf') }}" target="_blank"
                            class="btn btn-sm btn-danger">Descargar
                            PDF <i class="fa fa-file-pdf"></i></a>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i>Primero los niños y las niñas</h4>
                        <p>
                            Conoce y comprende las características de sus estudiantes, los contenidos disciplinares, los
                            enfóques y procesos pedagógicos,con el propósito de promover su formación integral.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Colaboración y trabajo en equipo</h4>
                        <p>
                            Establece relaciones de respeto, colaboración y corresponsabilidad con las familias, la
                            comunidad
                            y otras instituciones del Estado y la sociedad civil.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Articulación del Currículo</h4>
                        <p>
                            Planifica la enseñanza de forma colegiada, garantizando coherencia entre los aprendizajes, el
                            proceso
                            pedagógico, el uso de los recursos y la evaluación en una programación curricular en permanente
                            revisión.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Desarrolla habilidades socioemocionales</h4>
                        <p>
                            Reflexiona sobre su práctica y experiencia institucional y desarrolla procesos de aprendizaje
                            continuo
                            de modo individual y colectivo para construir y afirmar su identidad y responsabilidad
                            profesional.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Genera un ambiente de respeto</h4>
                        <p>
                            Crea un clima propicio para el aprendizaje, la convivencia democrática y la vivencia de la
                            diversidad en
                            todas sus expresiones con miras a formar ciudadanos críticos e interculturales.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Ética e Integridad</h4>
                        <p>
                            Ejerce la profesión desde una ética de respeto de los derechos fundamentales de las personas,
                            demostrando
                            honestidad, justicia, responsabilidad y compromiso con su función social.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Aprendizajes clave</h4>
                        <p>
                            Conduce el proceso de aprendizaje con un dominio de contenidos y el uso de estrategias y
                            recursos
                            pertinentes
                            para que sus estudiantes aprendan de manera reflexiva y crítica.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Formación Continua</h4>
                        <p>
                            Gestiona su desarrollo personal demostrando autoconocimiento y autorregulación, e interactúa de
                            manera asertiva y empática para trabajar colaborativamente en diversos contextos.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Evaluación permanente</h4>
                        <p>
                            Evalúa el aprendizaje de acuerdo a los objetivos institucionales para tomar decisiones y
                            retroalimentar
                            a sus estudiantes, teniendo en cuenta las diferencias individuales y los diversos contextos
                            culturales.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Habilidades Digitales</h4>
                        <p>
                            Gestiona los entornos digitales y los aprovecha para su desarrollo profesional y práctica
                            pedagógica, respondiendo a las necesidades de los estudiantes y de los contextos
                            socioculturales, y promoviendo el emprendimiento digital en la comunidad educativa.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-success">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Trabaja de manera colegiada</h4>
                        <p>
                            Participa activamente, con actitud democrática, crítica y colaborativa, en la gestión de la
                            escuela,
                            contribuyendo a la construcción y mejora continua del Proyecto Educativo Institucional.
                        </p>
                    </div>
                    <div class="col-lg-6 card p-3 border-info">
                        <h4><i class="fa fa-check fa-sm text-success"></i> Investigación e Innovación</h4>
                        <p>
                            Investiga aspectos críticos de la práctica docente utilizando diversos enfoques y metodologías
                            para promover una cultura de investigación e innovación.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
