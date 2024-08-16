@extends('layouts.home')
@section('metas')
    @php $titulo = 'Política de Privacidad'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection
@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay">
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
            <div class="col-lg-12">
                <h2 class="linea-debajo">{{ $titulo }} y protección de datos personales </h2>
                <p>
                    En la EESP Pukllasunchis nos preocupamos por la confidencialidad y protección de tus datos personales.
                    Por ello, y para garantizarte su absoluta privacidad, empleamos altos estándares de seguridad, conforme
                    a lo establecido en la Ley de Protección de Datos Personales – Ley N° 29733 y su reglamento aprobado por
                    el Decreto Supremo N° 003-2013-JUS (“Las normas de protección de datos personales”).
                </p>
                <h3>Como usuario o interesado</h3>
                <p>
                    Otorgas tu consentimiento expreso para que los datos personales que facilite queden incorporados en el
                    Banco de Datos de la EESP y sean tratados para absolver sus consultas y realizar comunicaciones por
                    cualquier canal, enviarle contenidos académicos, brindarle información publicitaria de los productos y
                    servicios de la EESP, seguimiento de un eventual proceso de admisión o matrícula, análisis de perfiles,
                    publicidad y prospección comercial, fines estadísticos, históricos, científicos, atención de reclamos,
                    invitación a eventos, mantenimiento del registro de postulantes, realización de encuestas y emisión de
                    comprobantes electrónicos.<br><br>

                    El usuario autoriza a la EESP Pukllasunchis a mantener sus datos personales en el banco de datos
                    referido en tanto sean útiles para la finalidad indicada y excepcionalmente, podrá compartir la
                    información a terceros, a efectos de cumplir con los fines descritos, para lo que se adoptarán las
                    medidas de seguridad y protección de confidencialidad.
                </p>
                <h3>Como estudiante y egresado</h3>
                <p>
                    Si eres estudiante o egresado de nuestra EESP, utilizaremos tu información para los siguientes fines:
                </p>
                <ul class="listasDecimal">
                    <li>Gestión académica.</li>
                    <li>Prestación u ofrecimiento de servicios de la EESP (educativos, culturales, empresariales,
                        deportivos, entre otros) o de terceros con los que hubiéramos suscrito un convenio, en cuyo caso
                        podemos compartir tu información, exclusivamente, para este fin.</li>
                    <li>Invitación a eventos académicos, culturales, deportivos y similares, organizados por la EESP y sus
                        respectivas unidades y centros.</li>
                    <li>Atención de consultas y reclamos.</li>
                    <li>Actualización de tus datos.</li>
                    <li>Cobro de deudas por los servicios educativos prestados.</li>
                    <li>Realización de encuestas.</li>
                    <li>Evaluación de solicitudes de crédito educativo, becas propias o de terceros, y otros beneficios
                        proporcionados por la EESP o por terceros con los que hubiéramos suscrito un convenio, en cuyo caso
                        podemos compartir tu información, exclusivamente, para este fin.</li>
                    <li>Certificación, acreditación y licenciamiento de la EESP frente a entidades nacionales e
                        internacionales.</li>
                    <li>Gestión de oportunidades laborales en la bolsa de trabajo administrada por la EESP, para lo cual
                        podremos brindar tus datos de contacto e información registrada en nuestra Bolsa de Trabajo, a
                        empresas o entidades interesadas en contar con tus servicios profesionales.</li>
                    <li>Atención de servicios de salud y evaluaciones, para lo cual podemos compartir tu información con
                        terceros con los que hubiéramos suscrito un convenio, exclusivamente, para este fin.</li>
                    <li>Obtención de grados académicos, títulos profesionales y su registro ante la SUNEDU.</li>
                    <li>Contacto con la red de egresados y graduados de la EESP Pukllasunchis.</li>
                    <li>Exposiciones y presentaciones de proyectos e investigaciones en ferias y eventos a nivel nacional e
                        internacional. </li>
                    <li>Reporte a instituciones públicas por mandato de la ley.</li>
                    <li>En general, para el cumplimiento de cualquier finalidad conexa con tu relación como estudiante o
                        egresado de la EESP.</li>
                </ul>
                <p class="mt-4">
                    Adicionalmente, utilizaremos tus datos como nombres y apellidos, correo electrónico, fotografías y
                    grabaciones, unidad académica, grados académicos, títulos profesionales, distinciones, premios
                    obtenidos, publicaciones, producciones e investigaciones, de ser el caso, para difundirlos en nuestro
                    portal web.<br><br>

                    Como estudiante o egresado nos autorizas a mantener tus datos personales en nuestro banco de datos en
                    tanto sean útiles para los fines indicados.<br><br>

                    Excepcionalmente, nuestra EESP podrá compartir la información a terceros, a efectos de cumplir con los
                    fines descritos, para lo que se adoptarán las medidas de seguridad y protección de confidencialidad.
                </p>
                <h3>Como colaborador y docente</h3>
                <p>
                    Si eres colaborador de nuestra EESP, trataremos tus datos personales con la finalidad de garantizar la
                    adecuada ejecución de la relación contractual de la que eres parte y cumplir las obligaciones legales a
                    nuestro cargo. Entre las finalidades vinculadas a la gestión de recursos humanos están las siguientes:
                </p>
                <ul class="listasDecimal">
                    <li>Administración de beneficios laborales para los colaboradores y sus derechohabientes.</li>
                    <li>Evaluación de desempeño.</li>
                    <li>Registros de ingresos y salidas.</li>
                    <li>Evaluaciones de salud ocupacional.</li>
                    <li>Consulta y reporte a terceros de información referida a antecedentes personales, profesionales, de
                        desempeño, comportamiento crediticio y financiero del colaborador.</li>
                    <li>Compartición de su información con terceros que requieran para cumplir con los fines de la EESP o
                        que por ley estemos obligados.</li>
                    <li>Ofrecimiento y prestación de productos o servicios ofrecidos por la EESP o por terceros con los que
                        la EESP hubiera suscrito un contrato.</li>
                    <li>Estudios e investigaciones.</li>
                    <li>En general cualquier finalidad conexa con su relación como colaborador de la EESP.</li>
                </ul>
                <p class="mt-4">
                    Adicionalmente, utilizaremos tus datos como nombres y apellidos, correo electrónico, área u oficina,
                    estudios universitarios, teléfono, grados académicos, títulos profesionales, distinciones, premios
                    obtenidos, publicaciones, producciones e investigaciones, de ser el caso, para difundirlos en nuestro
                    portal web.<br><br>

                    Sin perjuicio de ello, otros datos personales que ingrese podrán ser difundidos en las plataformas antes
                    mencionadas según lo decida y proporcione.
                </p>
                <h3>Como docente:</h3>
                <p>
                    Si eres docente de la EESP Pukllasunchis o realiza actividades de apoyo a la labor docente como jefe de
                    práctica, utilizaremos su información para:
                </p>
                <ul class="listasDecimal">
                    <li>Realizar las finalidades listadas para los colaboradores.</li>
                    <li>Realizar evaluaciones de desempeño y encuestas académicas.</li>
                    <li>Certificar, acreditar o licenciar a la EESP frente a entidades internacionales y/o nacionales.</li>
                    <li>Atender requerimientos de información de entidades de la Administración Pública.</li>
                    <li>Poner a disposición de la comunidad educativa el historial de docencia y los resultados de las
                        encuestas de evaluación con relación a los cursos dictados.</li>
                    <li>Otros fines de índole académica que estén relacionados con la actividad académica y de servicio del
                        docente.</li>
                </ul>
                <p>
                    Adicionalmente, utilizaremos tus datos como nombres y apellidos, correo electrónico, área u oficina,
                    estudios universitarios, teléfono, grados académicos, títulos profesionales, distinciones, premios
                    obtenidos, publicaciones, producciones e investigaciones, de ser el caso, para difundirlos en nuestro
                    portal web, así como a través del buscador de personas que se encuentra en nuestras plataformas.
                    <br><br>

                    Como docente nos autorizas a mantener tus datos personales en nuestro banco de datos en tanto sean
                    útiles para los fines indicados.<br><br>

                    Excepcionalmente, nuestra institución podrá compartir la información a terceros, a efectos de cumplir
                    con los fines descritos, para lo que se adoptarán las medidas de seguridad y protección de
                    confidencialidad.<br><br>

                    Asimismo, como usuario, estudiante, egresado, colaborador y/o docente se podrán utilizar tus datos para
                    elaborar bases de datos que serán utilizadas para ofrecerte productos y/o servicios de la Asociación
                    Pukllasunchis y que pudieran ser de tu interés y/o de terceros que se promocionen o publiciten por tu
                    intermedio, así como su transmisión a terceros por cualquier medio que permita la Ley 29733 y demás
                    leyes que resulten aplicables.<br><br>

                    Como usuario, estudiante, egresado, colaborador y/o docente podrás ejercer tu derecho de acceso,
                    actualización, rectificación, inclusión, oposición y supresión o cancelación de datos personales
                    enviando tu solicitud mediante un correo electrónico a eespp@pukllasunchis.org o presentándola
                    físicamente en nuestra oficina de secretaría.
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
