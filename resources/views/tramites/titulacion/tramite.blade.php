@extends('layouts.home')
@section('metas')
    @php $titulo = 'Procesos de trámites'; @endphp
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
                        <li><a href="{{ route('plan') }}"><i class="fa fa-caret-right fa-sm"></i> Plan de Trabajo</a></li>
                        <li><a href="{{ route('tinvestigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Trabajo de
                                Investigación</a></li>
                        <li><a href="{{ route('tesis') }}"><i class="fa fa-caret-right fa-sm"></i> Tesis</a></li>
                        <li><a href="{{ route('tramites') }}"><i class="fa fa-caret-right fa-sm"></i> Trámites
                                presenciales</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo" id="planTrabajo"> Trámites Presenciales</h2>
                <p>
                    Los siguientes trámites forman parte del proceso de titulación y se deben realizar de manera presencial
                    por los requisitos que se necesitan presentar.
                </p>
                <div class="row justify-content-center align-items-center fichas mt-4" id="derecho">
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Descargar FUT</p>
                            </a>
                        </div>
                    </div>
                </div><br><br>


                {{-- Prueba con Acordeon --}}
                <div id="accordion" class="collapseBolsa">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                                    aria-expanded="true" aria-controls="collapseOne" onclick="toggleArrow('arrowOne')">
                                    <span id="arrowOne" class="arrow fa fa-caret-down fa-sm"></span>
                                    Derecho de obtención de grado de Bachiller Carreras Regulares
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <p>
                                        Este trámite permite armar el Expediente de Graduación y tiene un costo de S/500
                                        soles. Se deben
                                        entregar los siguientes documentos:
                                    </p>
                                    <ul style="color:#4D4D4D" class="listasDecimal">
                                        <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o
                                            descargarlo de la página web. <small><a href="{{ asset('pdf/fut.docx') }}"
                                                    class="text-primary">Descargar <i
                                                        class="fa fa-arrow-down"></i></a></small>
                                        </li>
                                        <li>Una fotocopia de DNI ampliado y legalizado.</li>
                                        <li>Partida de nacimiento original y una copia legalizada.</li>
                                        <li>Certificado de estudios de secundaria original actualizado y una copia
                                            legalizada.</li>
                                        <li>Ocho (8) fotos tamaño pasaporte en fondo blanco y con terno.</li>
                                        <li>Una copia de RD de haber aprobado las prácticas pre profesionales. → s/50.00
                                        </li>
                                        <li>Carta de aceptación de la I.E. donde se realiza la investigación, original y una
                                            copia.</li>
                                        <li>Constancia de no deudor original y una copia.</li>
                                        <li>Constancia de egresado original y una copia.</li>
                                        <li>Una copia de Certificado de Estudios Superiores. </li>
                                        <li>D de Aprobación de PTI y asignación de asesor original y una copia.</li>
                                        <li>Informe de Trabajo de Investigación Apto original y una copia.</li>
                                        <li>RD de aprobación de Trabajo de Investigación original y una copia.</li>
                                        <li>Una copia del Certificado de conocimiento de idioma extranjero Nivel A2, para
                                            Inicial o Primaria.</li>
                                        <li>Una copia del Certificado de conocimiento de lengua originaria, para Primaria
                                            EIB.</li>
                                        <li>Comprobante de pago por Derecho de obtención de grado de Bachiller original y
                                            una copia.</li>
                                    </ul>
                                    <p class="mt-2">
                                        Como resultado de este trámite obtendrás una Carta de aceptación de Expediente de
                                        Graduación completo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo"
                                    aria-expanded="false" aria-controls="collapseTwo" onclick="toggleArrow('arrowTwo')">
                                    <span id="arrowTwo" class="arrow fa fa-caret-down fa-sm"></span>
                                    Derecho de obtención de grado de Bachiller para
                                        Profesionalización Docente
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <ul style="color:#4D4D4D;" class="listasDecimal">
                                        <li>Solicitar la aprobación del Plan de Trabajo de Investigación: s/50.00</li>
                                        <li>Solicitar revisión de originalidad (Turnitin): s/50.00</li>
                                        <li>Solicitar la revisión de TI: s/100.00</li>
                                        <li>Solicitar fecha de exposición del TI</li>
                                        <li>Después de la exposición del TI, solicitar RD de aprobación del TI: s/50.00</li>
                                        <li>Constancia de primera matrícula. → s/50.00</li>
                                        <li>Armar expediente de graduación. → s/500.00
                                            <ul class="listasCuerpo">
                                                <li>FUT debidamente llenado. Puedes solicitarlo en la oficina secretaría o
                                                    descargarlo de la
                                                    página web. <small><a href="{{ asset('pdf/fut.docx') }}"
                                                            class="text-primary">Descargar
                                                            <i class="fa fa-arrow-down"></i></a></small></li>
                                                <li>Una fotocopia de DNI ampliado y legalizado.</li>
                                                <li>Cuatro (4) fotos tamaño pasaporte en fondo blanco y con terno.</li>
                                                <li>Carta de aceptación de la I.E. donde se realiza la investigación,
                                                    original y una copia.</li>
                                                <li>Constancia de no deudor original y una copia.</li>
                                                <li>Constancia de egresado original y una copia.</li>

                                                <li>Una copia de Certificado de Estudios Superiores.
                                                    <ul>
                                                        <li>Para estudiantes provenientes de Institutos (IESP): Certificado
                                                            de Estudios
                                                            original, visado por la DREC.
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li>RD de Aprobación de PTI y asignación de asesor, original y una copia.
                                                </li>
                                                <li>Informe de Trabajo de Investigación Apto, original y una copia.</li>
                                                <li>RD de aprobación de Trabajo de Investigación original y una copia.</li>
                                                <li>Una copia legalizada de certificado de conocimiento de idioma extranjero
                                                    Nivel A2 ( solo
                                                    para egresados Educación Inicial o Primaria)</li>
                                                <li>Una copia del Certificado de conocimiento de lengua originaria (solo
                                                    para egresados de
                                                    Educación Primaria EIB.)</li>
                                                <li>Comprobantes de pago por Derecho de obtención de grado de Bachiller
                                                    original y una copia.
                                                </li>
                                            </ul>
                                        </li>
                                        <li>Solicitar registro de TI en repositorio institucional: s/50.00 (45 días
                                            hábiles).</li>
                                        <li>Solicitar emisión de diploma de grado de Bachiller: s/400.00</li>
                                        <li>Solicitar inscripción de pago por Derecho de obtención de grado de Bachiller,
                                            original y copia.</li>
                                    </ul>
                                    <p class="mt-2">
                                        Como resultado de este trámite obtendrás una Carta de aceptación de Expediente de
                                        Graduación completo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mt-4"> Carta de presentación para solicitar autorización de investigación:</h3>
                <p>Este trámite permite que el estudiante pueda solicitar la autorización de realizar la investigación en
                    una I.E. Tiene un costo de S/10 soles. Se deben entregar los siguientes documentos:</p>
                <p>
                    1. FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                    web.
                    <br>
                    En el FUT se deben especificar los siguientes datos:
                </p>
                <ul class="listasCuerpo">
                    <li>Nombre de la Institución Educativa</li>
                    <li>Nombre de Director de la I.E.</li>
                    <li>Ubicación de la Institución Educativa</li>
                    <li>Título de la Investigación</li>
                </ul>
                <p>2. Comprobante de pago por Carta de presentación.<br>
                    Como resultado de este trámite obtendrás una Carta de presentación para solicitar autorización de
                    investigación. La I.E. deberá entregarte una Carta de aceptación para realizar la investigación.</p>

                <h3 class="mt-4">Constancia de No Deudor:</h3>
                <p>Este trámite permite demostrar que el estudiante no tiene ninguna deuda, de pensiones de estudios,
                    libros, materiales u otros, con la institución. Tiene un costo de S/30 soles. Se deben entregar los
                    siguientes documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Comprobante de pago por Constancia de No Deudor.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás una Constancia de No Deudor.
                </p>
                <h3 class="mt-4">Constancia de Egresado:</h3>
                <p>
                    Este trámite permite demostrar que el estudiante ha culminado satisfactoriamente todos los ciclos
                    académicos. Tiene un costo de S/40 soles. Se deben entregar los siguientes documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Dos fotos tamaño carné, en fondo blanco y con terno.</li>
                    <li>Comprobante de pago por Constancia de Egresado.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás una Constancia de Egresado.
                </p>
                <h3 class="mt-4">Certificado de Estudios Superiores:</h3>
                <p>
                    Este trámite permite demostrar que el estudiante ha culminado satisfactoriamente todos los ciclos
                    académicos. Tiene un costo de S/50 soles por ciclo académico cursado. Se deben entregar los siguientes
                    documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Seis fotos tamaño pasaporte, en fondo blanco y con terno.</li>
                    <li>Comprobante de pago por Certificado de Estudios Superiores.</li>
                </ul>
                <p> Como resultado de este trámite obtendrás los Certificados de Estudios Superiores.</p>
                <h3 class="mt-4">Emisión de Diploma de Bachiller:</h3>
                <p>
                    Este trámite se realiza para la compra del formato del Diploma de Bachiller y el rotulado del mismo.
                    Tiene un costo de S/400 soles. Se deben entregar los siguientes documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Copia de Carta de aceptación de Expediente de Graduación completo.</li>
                    <li>Dos ejemplares anillados de la versión final del TI.</li>
                    <li>Comprobante de pago por Emisión de Diploma de Bachiller.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás el Diploma de Bachiller.
                </p>
                <h3 class="mt-4">Emisión de Título de Licenciado:</h3>
                <p>
                    Este trámite se realiza para la compra del formato del Título de Licenciado y el rotulado del mismo.
                    Tiene un costo de S/400 soles. Se deben entregar los siguientes documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Copia legalizada del Grado de Bachiller.</li>
                    <!----<li>Copia de Acta de aprobación de sustentación.</li>
                            <li>Copia de RD de aprobación de sustentación.</li>
                            <li>Constancia de inscripción en SUNEDU del Grado de Bachiller.</li>---->
                    <li>Un ejemplar empastado por grupo y/o tema de investigación de la versión final de la Tesis.</li>
                    <li>Comprobante de pago por Emisión de Título de Licenciado.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás el Título de Licenciado.
                </p>
                <h3 class="mt-4">Inscripción del Grado de Bachiller o Título de Licenciado en SUNEDU:</h3>
                <p>
                    Este trámite permite que el Grado de Bachiller o el Título de Licenciado sea reconocido oficialmente en
                    SUNEDU. Tiene un costo de S/200 soles. Se deben entregar los siguientes documentos:
                </p>
                <ul class="listasCuerpo">
                    <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la página
                        web.</li>
                    <li>Diploma de Bachiller o Título de Licenciado original.</li>
                    <li>Diploma de Bachiller o Título de Licenciado escaneado en anverso y reverso en archivo PDF con
                        resolución de 200 x 100 dpi.</li>
                    <li>Copia de Constancia de primera matrícula.</li>
                    <li>Copia de Constancia de Egresado.</li>
                    <li>Comprobante de pago por Inscripción del Grado de Bachiller o Título de Licenciado.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás el Diploma de Bachiller.
                </p>
                <p class="generic-blockquote mt-4">
                    <strong>Nota:</strong><br>
                    Los pagos se realizan por cualquier plataforma disponible de Caja Cusco al mismo número de cuenta.
                </p><br>
                <h3 class="mt-4">Pagos para la realización de los Trámites Presenciales </h3>
                <p>SOLO SE PUEDEN PAGAR EN VENTANILLAS de cualquier agencia a nivel nacional.</p>
                <ul class="listasDecimal">
                    <li>Cliente debe indicar PAGO ORDINARIO de la ASOCIACION PUKLLASUNCHIS – EESPP</li>
                    <li>Cliente debe indicar el concepto a pagar ejemplo: Examen de Admisión, Constancia de Matrícula,
                        Traslado etc.</li>
                    <li>El representante del servicio realizará la búsqueda y le indicará el monto del concepto ordinario
                        para la validación del cliente</li>
                    <li>Finalmente el Cliente deberá dictar el código(DNI), el nombre y apellidos completos del alumno.</li>
                </ul>
                <p>
                    Estos pagos ordinarios SOLO SE PUEDEN PAGAR EN VENTANILLAS de cualquier agencia a nivel nacional.
                </p>
                <p class="generic-blockquote">
                    <strong>OJO:</strong><br>
                    El estudiante tiene la OBLIGACIÓN de enviar, adjunto a la Ficha de Inscripción, una foto del comprobante
                    de depósito (voucher) entregado por el banco, con los siguientes datos escritos con letra clara: <br>
                    - Nombre y apellidos completos<br>
                    - Número de DNI<br>
                    - Programa de formación al que postula (Inicial / Primaria EIB / Profesionalización / 2da Especialidad)
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection