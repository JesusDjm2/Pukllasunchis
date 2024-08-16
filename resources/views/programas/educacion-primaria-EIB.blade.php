@extends('layouts.home')
@section('metas')
    @php $titulo = 'Educación Primaria EIB'; @endphp
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
                        <li><a href="{{ route('primaria') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria</a>
                        </li>
                        {{-- <li><a href="{{ route('primariaEIB') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria
                                EIB</a></li> --}}
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
                    Esta especialidad está dirigida a jóvenes varones y mujeres, con conocimiento de la lengua quechua,
                    interesados y con disposición para proponer alternativas educativas que respondan a la realidad social y
                    lingüística de las poblaciones quechuas en distintos contextos, afirmando el valor de las culturas
                    tradicionales y potenciando la diversidad.<br><br>

                    Por otro lado, además de impulsar estrategias de aprendizaje situadas en la realidad del país, los
                    egresados de esta especialidad serán capaces de desarrollar proyectos encaminados a la inclusión de
                    niños y niñas con habilidades diferentes y, mecanismos de gestión novedosos tendientes a organizar, de
                    manera alternativa, los servicios educativos para hacerlos más pertinentes a la edad, las familias y
                    características del grupo sociocultural al que van dirigidos.
                </p>
                <h3 class="mt-5 linea-debajo scroolOk" id="duracion">Duración</h3>
                <p>
                    El plan de estudios tiene diez ciclos académicos con un total de 250 créditos. Cada ciclo se desarrolla
                    en dieciséis (16) semanas, treinta (34) horas semanales. (DCBN 2019)
                </p>
                <ul class="listasCuerpo">
                    <li>Grado al que conduce: Bachiller en Educación.</li>
                    <li>Título al que conduce: Licenciado en Educación Primaria Intercultural Bilingüe.</li>
                </ul><br>

                <h3 class="linea-debajo scroolOk" id="ejes">Ejes Temáticos</h3>
                <p>
                    Para todos los programas de estudios que la EESP ofrece, los cursos u áreas se han organizado según seis
                    ejes temáticos que expresan la orientación de los cursos. Los ejes temáticos o campos de estudio en el
                    caso de todas las carreras, son los siguientes:
                </p>
                <table class="table table-bordered table-hover">
                    <tbody>
                        <tr>
                            <th scope="row" class="align-middle">
                                Dinámicas socio-políticas culturales y paradigmas de pensamiento en la educación y el
                                currículo.
                            </th>
                            <td>
                                Reúne los cursos o áreas que permiten a los futuros docentes, reflexionar sobre los retos de
                                la educación situándose en el escenario de crisis actual. Los cursos de esta área colocan en
                                perspectiva las dinámicas sociales y culturales de cada período histórico considerando cómo
                                éstas han influido y delineado los escenarios educativos y las orientaciones curriculares.
                                Desde situaciones vivenciales y prácticas, los cursos de este eje proponen reflexionar
                                críticamente sobre los paradigmas de pensamiento que influyen en la educación, considerando
                                los contextos sociales. Este análisis permitirá a los estudiantes, proponer cambios viables,
                                considerando todos los factores que influyen en los mismos.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Comunicación, lenguas y lenguajes expresivos.</th>
                            <td>
                                Los cursos correspondientes a este eje abordan los conceptos claves vinculados a los
                                distintos códigos de comunicación. Impulsan el despliegue de las capacidades expresivas y el
                                manejo de recursos diversos entre ellos, de las tecnologías de la información, para fines
                                educativos.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Matemática y didáctica.</th>
                            <td>
                                Los cursos de este eje desarrollan las habilidades básicas para la comprensión del lenguaje
                                matemático y su uso para la resolución de situaciones. Promueven el conocimiento de los
                                conceptos curriculares matemáticos y la comprensión de la lógica matemática en otras
                                culturas.
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">Aprendizaje, educación y gestión para la atención a la
                                diversidad.</th>
                            <td>
                                Reúne los cursos que permiten a los estudiantes acercarse a la comprensión de los procesos
                                de aprendizaje de niños y niñas de diferentes contextos considerando el impacto de los
                                cambios sociales en los ámbitos familiares y culturales, y desarrollando capacidades para
                                proponer didácticas y mecanismos de gestión que promuevan una atención educativa
                                participativa y pertinente.
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
                <p>Malla Curricular del DCBN del Programa de Estudios de Educación Inicial. &nbsp;&nbsp;<a
                        href="{{ asset('pdf/plan-estudios-2020-educacion-primaria-EIB.pdf') }}" class="btn btn-primary"
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
                            <td>Lectura y escritura en la escritura superior</td>
                            <td>Comunicación oral en la Educación Superior</td>
                            <td>Arte, creatividad y Aprendizaje </td>
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
                            <td>Desarrollo Personal II</td>
                            <td>Deliberación y Participación </td>
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
                            <td style="background:#ffc10778!important">Práctica e Investigación III </td>
                            <td style="background:#ffc10778!important">Práctica e Investigación IV</td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P</td>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P</td>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P/td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background:#ffc10778!important">
                            <td>Práctica e investigación I</td>
                            <td>Práctica e investigación II</td>
                            <td style="background: #acacff">Lengua Indigena u Originaria III</td>
                            <td style="background: #acacff">Lengua Indigena u Originaria III</td>
                        </tr>
                        <tr style="background:#ffc10778!important">
                            <td>6H-5 CR/4T- 2P</td>
                            <td>6H-5 CR/4T- 2P </td>
                            <td style="background: #acacff">4H-3CR/2T- 2P </td>
                            <td style="background: #acacff">4H-3CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Lengua Indígena u Originaria I </td>
                            <td>Lengua Indigena u Originaria II </td>
                            <td>Comunicación en Castellano I</td>
                            <td>Comunicación en Castellano II </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Desarrollo y Aprendizaje en Contextos Diversos I </td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje I </td>
                            <td>Corporeidad y moticidad para el aprendizaje y autonomía</td>
                            <td>Relaciones con la Familia y Comunidad </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Fundamentos para la Educación Primaria Intercultural Bilingüe </td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje en la EIB I</td>
                            <td>Aprendizaje de las Matemáticas I</td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje en la EIB II</td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #fdbcff">
                            <td>Electivo I </td>
                            <td>Electivo II</td>
                            <td>Electivo III</td>
                            <td>Electivo IV</td>
                        </tr>
                        <tr style="background: #fdbcff">
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                            <td>4H-3CR/2T- 2P </td>
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
                            <td>Literatura y Sociedad en contextos Diversos </td>
                            <td>Alfabetización científica</td>
                            <td>Ética y Filosofía en el pensamiento crítico </td>
                            <td>Inglés para principiantes / Beginner English II A1 </td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                            <td>4H-3 CR/2T-2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td>Inglés para principiantes / Beginner English A1</td>
                            <td>Inglés para principiantes / Beginner English II A1</td>
                            <td>Inglés para principiantes / Beginner English A2</td>
                            <td style="background:#ffc10778">Práctica e Investigación VIII </td>
                        </tr>
                        <tr>
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P</td>
                            <td>10H-7CR/4T-6P </td>
                            <td style="background:#ffc10778">12H-8 CR/4T- 8P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">Práctica e Investigación V</td>
                            <td style="background:#ffc10778!important">Práctica e investigación VI</td>
                            <td style="background:#ffc10778">Práctica e investigación VII</td>
                            <td style="background: #acacff">Aprendizaje de las Lenguas en Estudiantes Bilingües II </td>
                        </tr>
                        <tr>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P</td>
                            <td style="background:#ffc10778!important">6H-5 CR/4T- 2P </td>
                            <td style="background:#ffc10778">10H-7 CR/4T- 6P</td>
                            <td style="background: #acacff">4H-3Cr/ 2T-2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Lengua Indigena u Originaria V </td>
                            <td>Lengua Indigena u Originaria VI</td>
                            <td>Lengua Indigena u Originaria VII </td>
                            <td>Construcción e Interpretaciones Históricas y Territoriales</td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Desarrollo del Bilingüismo </td>
                            <td>Comunicación en Castellano III </td>
                            <td>Inclusió Educativa y Atención a las Necesidades a las Educativas
                                Especiales II</td>
                            <td>Aprendizaje de las Ciencias y Epistemologías II </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                            <td>4H-3 CR/2T- 2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Construcción de la Identidad y Ejercicio de Ciudadania </td>
                            <td>Inclusió Educativa y Atención a las Necesidades a las Educativas
                                Especiales I </td>
                            <td>Aprendizaje de las Matemáticas III </td>
                            <td>Planificación por Competencias y Evaluación para el Aprendizaje en la EIB III </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                            <td>6H-5CR/4T- 2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Aprendizaje de las Matemáticas II </td>
                            <td>Aprendizaje de las Ciencias y Epistemologías I </td>
                            <td>Artes Integradas para el Aprendizaje en la Diversidad </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3 CR/2T- 2P</td>
                            <td>4H-3 CR/2T- 2P </td>
                            <td>4H-3CR/2T- 2P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td style="background: #fdbcff">ELECTIVO V</td>
                            <td>Aprendizaje de las Lenguas en Estudiantes Bilingües I </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td style="background: #fdbcff">4H-3Cr/ 2T-2P </td>
                            <td>4H-3 CR/2T- 2P </td>
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
                            <th class="table-active" rowspan="9"
                                style="writing-mode: vertical-rl;transform: rotate(-180deg); ">
                                Informe de investigación Aplicada
                            </th>

                        </tr>
                        <tr style="background:#ffc10778">
                            <td>Práctica e Investigación IX</td>
                            <td>Práctica e Investigación X </td>
                        </tr>
                        <tr style="background:#ffc10778">
                            <td>26H-16CR/6T-20P</td>
                            <td>26H-16CR/6T-20P</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Políticas y Gestión Educativa en EIB </td>
                            <td>Cosmovisión, Esperitualidad y Manifestaciones Religiosas para el Aprendizaje </td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3 CR/2T-2P </td>
                            <td>4H-3 CR/2T-2P </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-active"></td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>Lengua Indigena u Originaria VIII </td>
                            <td>Comunicación en Castellano IV</td>
                        </tr>
                        <tr style="background: #acacff">
                            <td>4H-3CR/2T-2P </td>
                            <td>4H-3CR/2T-2P </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="3">Leyenda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>FG</td>
                            <td>Formación general</td>
                        </tr>
                        <tr style="background:#ffc10778">
                            <td>FPI </td>
                            <td>Formación en la práctica e investigación</td>
                        </tr>
                        <tr style="background:#acacff">
                            <td>FE</td>
                            <td>Formación específica</td>
                        </tr>
                        <tr style="background: #fdbcff">
                            <td>EL</td>
                            <td>Electivos </td>
                        </tr>
                        <tr>
                            <td>H</td>
                            <td>Horas semanales</td>
                        </tr>
                        <tr>
                            <td>CR</td>
                            <td>Créditos</td>
                        </tr>
                    </tbody>
                </table>
                <br><br>
            </div>
        </div>
    </div>
@endsection
