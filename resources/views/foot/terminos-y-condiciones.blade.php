@extends('layouts.home')
@section('metas')
    @php $titulo = 'Términos & Condiciones'; @endphp
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
                <h2 class="linea-debajo">{{ $titulo }} </h2>
                <p>
                    El acceso a este sitio web y páginas relacionadas implica que aceptas expresamente los términos y
                    condiciones generales descritos a continuación. En caso de no aceptarlos, te solicitamos no acceder ni
                    manipular la información de los servicios ofrecidos por este sitio pues podrías estar haciendo un uso
                    indebido del mismo.<br><br>

                    Este sitio web está dirigido a nuestros estudiantes, docentes, colaboradores; así como a terceros no
                    vinculados con la Asociación Pukllasunchis.
                </p>
                <h3>1. Derechos de propiedad intelectual</h3>
                <p>
                    Salvo se indique de manera distinta en este sitio web o en sus páginas relacionadas, la Asociación
                    Pukllasunchis es propietaria, o de otra manera titular, de los derechos de propiedad intelectual que se
                    muestran en esta página. Queda prohibido el uso de cualquier derecho de propiedad intelectual sin contar
                    con el consentimiento previo, expreso y por escrito de la Asociación Pukllasunchis.
                </p>
                <h3>2. Derechos de autor</h3>
                <p>
                    Este sitio se encuentra protegido por la normativa vigente sobre derechos de autor. Todos los derechos
                    involucrados, como por ejemplo su contenido y su diseño visual, son de titularidad de la Asociación
                    Pukllasunchis. Por ello, se encuentra estrictamente prohibido su empleo, modificación, reproducción,
                    distribución, transmisión o comercialización de los derechos involucrados sin el permiso previo, expreso
                    y por escrito; salvo los casos de derechos de cita, empleo para finalidades educativas y los demás usos
                    honrados reconocidos.
                </p>
                {{-- <ul class="listasDecimal">
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
                </ul> --}}
                <h3>3. Signos distintivos y patentes</h3>
                <p class="mt-4">
                    A menos que se señale algo distinto en nuestro portal, los signos distintivos y patentes registrados de
                    acuerdo a la legislación nacional y supranacional son propiedad de la Asociación Pukllasunchis. Su uso
                    está prohibido sin el consentimiento previo, expreso y por escrito de la Asociación Pukllasunchis o de
                    su titular.
                </p>
                <h3>4. Obligaciones de los usuarios registrados</h3>
                <p>
                    Si te encuentras registrado en la EESP Pukllasunchis como alumno, docente, colaborador o cualquier otra
                    manera, ya eres un usuario registrado. El acceso a los servicios a través de tu nombre de usuario y
                    contraseña es de tu plena y exclusiva responsabilidad, por ello debes mantenerlos en estricta
                    confidencialidad. Si presumes que algún tercero estaría utilizando tu nombre de usuario y contraseña,
                    notifica de inmediato esta circunstancia a la Mesa de Ayuda (eespp@pukllasunchis.org) para prevenir
                    cualquier acceso no autorizado con tu identidad. Toda transacción realizada con tu nombre de usuario y
                    contraseña será vigilada para evitar malos usos de los servicios que se les brinda. No debes usar tu
                    nombre de usuario y contraseña para ningún propósito ilegal o reñido contra la moral o las buenas
                    costumbres.
                </p>
                <h3>5. Obligaciones de los usuarios en general</h3>
                <p class="mt-4">
                    El acceso de los servicios web es de carácter público. Como usuario estás obligado a utilizar los
                    servicios y contenidos que te proporciona este sitio web conforme a las leyes de la República del Perú y
                    a los principios de buena fe, y usos generalmente aceptados y a no contravenir con su actuación a través
                    de la web. Mediante el uso de nuestros servicios, a través de este sitio web, declaras bajo juramento
                    que la información que ingresas es verdadera y exacta.
                </p>
                <h3>6. Modificaciones a los Términos y Condiciones</h3>
                <p>
                    Nos reservamos el derecho de efectuar en cualquier momento modificaciones o actualizaciones a nuestros
                    términos y condiciones de uso para la atención de novedades legislativas, políticas internas o nuevos
                    requerimientos para la prestación u ofrecimiento de nuestros servicios o productos. Estas modificaciones
                    estarán disponibles al público a través de nuestras páginas de internet.
                </p>
                <h3>7. Delimitación de responsabilidad</h3>
                <p>
                    Nuestro compromiso contigo es mantener actualizado este sitio web de manera permanente. Sin embargo,
                    podemos incurrir en imprecisiones u omisiones. En cualquier de estos casos, la Asociación Pukllasunchis
                    no asume responsabilidad por cualquier daño, perjuicio directo, indirecto, incidental, consecuente o
                    especial que se derive del uso de la información contenida en este sitio web o en las páginas web que
                    aquí se refieran. No podemos garantizar un servicio libre e ininterrumpido de este sitio web, pero te
                    manifestamos que es nuestra voluntad realizar nuestros mayores esfuerzos para que sea así.
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
