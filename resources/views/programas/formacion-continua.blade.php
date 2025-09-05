@extends('layouts.home')
@section('metas')
    @php $titulo = 'Formación Continua'; @endphp
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
                        <li><a href="#requisitos"><i class="fa fa-caret-right fa-sm"></i> Requisitos</a></li>
                        <li><a href="#contenido"><i class="fa fa-caret-right fa-sm"></i> Contenido</a></li>
                    </ul>
                    <h3 class="linea-debajo mt-5">Más programas</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('inicial') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Inicial</a>
                        </li>
                        <li><a href="{{ route('primaria') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria</a>
                        </li>
                        <li><a href="{{ route('primariaEIB') }}"><i class="fa fa-caret-right fa-sm"></i> Educación Primaria
                                EIB</a></li>
                        {{--  <li><a href="{{ route('formacion') }}"><i class="fa fa-caret-right fa-sm"></i> Formación
                                Continua</a></li> --}}
                        <li><a href="{{ route('profesionalizacion') }}"><i class="fa fa-caret-right fa-sm"></i>
                                Profesionalización Docente</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    En virtud de nuestros fines y en el marco de nuestra facultad para aportar a la educación mediante el
                    desarrollo de programas de profesionalización, ofrecemos el Programa de Formación Continua, dirigido a
                    diversos profesionales y en concordancia con lo establecido en la Ley 30512, su reglamentación y las
                    orientaciones expedidas por el Ministerio a través del Oficio N.° 00148-2022.<br><br>

                    El curso “Educación y Currículo Intercultural” forma parte de este programa y busca desarrollar
                    competencias para una práctica pedagógica intercultural, situada en las características sociales y
                    culturales de la región. Constituye un espacio de formación orientado a la reflexión vinculada con la
                    vida, la cultura y las necesidades de nuestro tiempo.<br><br>

                    Este curso ofrece a profesionales e interesados de distintas áreas la oportunidad de adquirir un
                    conocimiento reflexivo sobre las bases que sustentan una pedagogía intercultural. Desde esta
                    perspectiva, invita a repensar la educación de la región y del país, e imaginar escenarios educativos de
                    convivencia donde la cooperación, el interaprendizaje, el diálogo intercultural y el aprendizaje
                    vivencial se conviertan en pilares para desarrollar metodologías y didácticas interculturales que
                    respondan a las realidades socioculturales de niños y niñas en diversos contextos.
                </p>
                <h3 class="mt-5 linea-debajo scroolOk" id="duracion">Duración</h3>
                <p>
                    El programa de profesionalización tiene una duración de un año repartidos en dos ciclos académicos de 20
                    créditos en total cada uno.<br>
                    <strong>Está dirigido a:</strong>
                </p>
                <ul class="listasCuerpo">
                    <li>Procedentes de programas de estudios distintos a Educación:
                        <ul>
                            <li>Bachilleres o titulados de universidades</li>
                            <li>Bachilleres o titulados de instituciones con rango universitario</li>
                        </ul>
                    </li>
                    <li>Procedentes de carreras pedagógicas IESP o ISE:
                        <ul>
                            <li>Titulados como profesor del mismo nivel o especialidad del PPD al que desea acceder</li>
                        </ul>
                    </li>
                    <li>Procedentes de carreras pedagógicas de universidades o de instituciones con rango universitario:
                        <ul>
                            <li>Bachilleres o titulados en Educación</li>
                        </ul>
                    </li>
                    <li>Procedentes de carreras tecnológicas:
                        <ul>
                            <li>Graduados como bachiller técnico</li>
                            <li>Titulados con título profesional técnico</li>
                        </ul>
                    </li>
                </ul><br>

                <h3 class="linea-debajo scroolOk" id="requisitos">Requisitos</h3>
                <p>
                    a exigencia mínima para acceder a un PPD es contar con un grado de bachiller o un título profesional en
                    un programa de estudios distinto a Educación. Por lo tanto, si una persona posee un grado de bachiller
                    en Educación, supera dicha exigencia mínima.<br><br>

                    Asimismo, si una persona cumple con el requisito de contar con un grado de bachiller o un título
                    profesional en un programa de estudios distinto a Educación, puede acceder a un PPD para la obtención
                    del grado de bachiller en Pedagogía, sin perjuicio de que, adicionalmente, cuente con un grado de
                    bachiller en Educación.<br><br>

                    Es importante tener presente que los grados y/o títulos que constituyen requisitos para la obtención del
                    grado de bachiller mediante un PPD deben estar previamente registrados.
                </p>
                <ul class="listasCuerpo">
                    <li>Para estudiantes provenientes de Universidades, por la SUNEDU (para grados y títulos otorgados por
                        universidades o instituciones con rango universitario).
                    </li>
                    <li>Para estudiantes provenientes de Instituciones de Educación Superior Pedagógica, en la DRE
                        correspondiente (para los títulos de profesor).</li>
                </ul>
                <p>
                    Al respecto, se precisa que el literal a) del numeral 15 2 del artículo 15 de la Ley Nº 30512 no recoge
                    de forma expresa alguna prohibición para que una persona que tenga grado de bachiller en Educación pueda
                    acceder a un PPD (Informe 148 2022 sobre los programas de profesionalización docente)
                </p>
                <h3 class="linea-debajo scroolOk" id="contenido">Programa de profesionalización: módulos, horas y créditos
                </h3>
                <table class="table table-bordered text-center table-responsive border-primary">
                    <tbody style="background: #e0cdff">
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
                            <td class="align-middle" rowspan="3">Formación General: Marco Socio Político Cultural.</td>
                            <td style="background:#fccdff" class="align-middle" rowspan="3">Cultura y Diversidad: Retos
                                para una Educación Intercultural.</td>
                            <td style="background:#fccdff">Circulo Intercultural: Conceptos Básicos y Nudos Problemáticos
                            </td>
                            <td style="background:#fccdff">96</td>
                            <td style="background:#fccdff">3</td>
                            <td style="background:#fccdff">64</td>
                            <td style="background:#fccdff">32</td>
                        </tr>
                        <tr style="background:#fccdff">
                            <td>Infancias y Familia: ¿Qué niño y niña educamos?</td>
                            <td>64</td>
                            <td>3</td>
                            <td>32</td>
                            <td>32</td>
                        </tr>
                        <tr style="background:#fccdff">
                            <td>Fundamentos y Condiciones para una Pedagogía Intercultural.</td>
                            <td>80</td>
                            <td>3</td>
                            <td>16</td>
                            <td>64</td>
                        </tr>
                        <tr>
                            <td class="align-middle" rowspan="2">Formación Específica: Pedagogía Didáctica Intercultural
                            </td>
                            <td style="background:#fccdff" class="align-middle" rowspan="2">Educación Intercultural:
                                Reflexiones para una Práctica Intercultural.</td>
                            <td style="background:#fccdff">Atención a la Diversidad en la Educación.</td>
                            <td style="background:#fccdff">96</td>
                            <td style="background:#fccdff">4</td>
                            <td style="background:#fccdff">32</td>
                            <td style="background:#fccdff">64</td>
                        </tr>
                        <tr style="background:#fccdff">
                            <td>Planificación de Competencias y Evaluación para el Aprendizaje</td>
                            <td>80</td>
                            <td>3</td>
                            <td>16</td>
                            <td>64</td>
                        </tr>
                        <tr>
                            <td>Formación en la Práctica Pedagógica y la Investigación Educativa.</td>
                            <td class="align-middle">Investigación e Innovación</td>
                            <td class="align-middle">Investigación/Observación y Práctica Reflexiva/Escenarios Alternativos
                                Educación I</td>
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
                                intercultural</td>
                            <td style="background:#dcfdff" rowspan="2" class="align-middle">Metodologías y Proyectos de
                                Aprendizaje Intercultural</td>
                            <td style="background:#dcfdff">Proyectos de Aprendizaje: Competencias Identidad/Convivencia y
                                Participación</td>
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
                            <td rowspan="3" class="align-middle">Competencias Comunicativas</td>
                            <td>Desarrollo de Competencias Comunicativas: Lectura, Escritura y Argumentación</td>
                            <td>64</td>
                            <td>3</td>
                            <td>32</td>
                            <td>32</td>
                        </tr>
                        <tr style="background:#dcfdff">
                            <td class="align-middle">Cuerpo y Arte en la Escuela Primaria</td>
                            <td>80</td>
                            <td>3</td>
                            <td>16</td>
                            <td>64</td>
                        </tr>
                        <tr style="background:#fccdff">
                            <td>EL Clima del Aula y el Desarrollo de Espacios de Diálogo para la Vida Comunitaria</td>
                            <td>80</td>
                            <td>3</td>
                            <td>16</td>
                            <td>64</td>
                        </tr>
                        <tr>
                            <td>Formación en la Práctica Pedagógica y la Investigación Educativa.</td>
                            <td class="align-middle">Investigación e Innovación</td>
                            <td class="align-middle">Investigación/Desarrollo de Proyectos de Innovación Educativa con
                                Perspectiva Intercultural</td>
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
                </table>
            </div>
        </div>
    </div>
@endsection
