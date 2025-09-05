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
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 20px;
            z-index: 2000;
            width: 80%;
            margin: 1em auto;
            height: 80vh;
            overflow-y: auto;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1999;
        }

        .modal-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .modal-body {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .modal-body li {
            margin-bottom: 12px;
        }

        .modal-body .span {
            background: #cd9244;
            border-radius: 50%;
            padding: 3px 10px;
            color: #fff;
            margin-right: 5px;
            position: relative;
        }

        .modal-body .span::after {
            content: '';
            display: block;
            width: 2px;
            height: 20px;
            background: #cd9244;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .modal-body ul li:last-child .span::after {
            display: none;
        }

        .listanueva li {
            list-style: decimal !important;
            margin-left: 1.6em;
            line-height: 2em;
            font-weight: 300;
        }

        @media (max-width: 768px) {

            .modal-body .span::after {
                display: none;
            }
        }

        .modal-close {
            background-color: #38496b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
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
            @include('tramites.titulacion.tramites-lateral')
            <div class="col-lg-9">
                <h3 class="linea-debajo"> Trámites Presenciales</h3>
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
                </div><br>
                <h3 class="linea-debajo mb-4">Armado de expediente de graduación</h3>
                {{-- Prueba con Acordeon --}}
                <div id="accordion" class="collapseBolsa">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                                    aria-expanded="true" aria-controls="collapseOne" onclick="toggleArrow('arrowOne')">
                                    <span id="arrowOne" class="arrow fa fa-caret-down fa-sm"></span>
                                    Para estudiantes de formación inicial Docente (5 años).
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
                                    <h5>Documentos externos a presentar en la Institución: </h5>
                                    <ul class="listanueva">
                                        <li>Una fotocopia de DNI ampliado y legalizado. </li>
                                        <li>Partida de nacimiento original.</li>
                                        <li>Certificado de estudios de secundaria original.
											<ul>
												· Si este documento ya está en tu expediente de ingreso, no es necesario volver a presentarlo.
											</ul>
										</li>
                                        <li>8 Fotos tamaño pasaporte fondo blanco en papel mate.</li>
                                        <li>Documento que acredite conocimiento de idioma A2/ lengua originaria primaria EIB
                                        </li>
                                    </ul>
                                    <h5 class="mt-3">Documentos a tramitar con la Institución: </h5>
                                    <ul class="listanueva">
                                        <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o
                                            descargarlo de la página web.
                                            <small>
                                                <a href="{{ asset('pdf/fut.docx') }}" class="text-primary">
                                                    Descargar <i class="fa fa-arrow-down"></i>
                                                </a>
                                            </small>
                                        </li>
                                        <li>Una copia de RD de haber aprobado las prácticas pre profesionales. → s/50.00
                                        </li>
                                        <li>Carta de aceptación de la I.E. donde se realiza la investigación, original.
                                            <small class="text-info">Solicitar modelo en secretaría</small>
                                        </li>
                                        <li>Constancia de no deudor original.
                                            <small class="text-info">Codigo: 28 | Concepto: Constacia de no deudor | Monto:
                                                s/ 30.00</small>, luego de ello solicitar la constancia con el área de Cobranzas.
                                        </li>
                                        <li>Constancia de egresado original.
                                            <small class="text-info">Codigo: 9 | Concepto: Constacia de Egresado | Monto: s/
                                                50.00</small>
                                        </li>
                                        <li>Constancia de primera matrícula.
                                            <small class="text-info">Codigo: 29 | Concepto: Constacia de 1ra matrícula |
                                                Monto: s/ 50.00</small>
                                        </li>
                                        <li>Resolución Directoral de Aprobación de PTI y asignación de asesor original.
                                            <small class="text-info">Codigo: 10 | Concepto: Aprobación de PTI y asesor |
                                                Monto: s/ 50.00</small>
                                        </li>
                                        <li>Informe de Trabajo de Investigación Apto original.</li>
                                        <li>Resolución Directoral de aprobación de Trabajo de Investigación original.
                                            <small class="text-info">Codigo: 10 | Concepto: RD de Aprobación TI | Monto: s/
                                                50.00</small>
                                        </li>
                                        <li>Comprobante de pago por Derecho de obtención de grado de Bachiller original.
                                            <small class="text-info">Codigo: 18 | Concepto: Armado de expediente de
                                                Titulación | Monto: s/ 500.00</small>
                                        </li>
                                    </ul>
                                    <h5 class="mt-3">
                                        Como resultado de este trámite obtendrás una Carta de aceptación de Expediente de
                                        Graduación completo.
                                    </h5>
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
                                    Para estudiantes de Profesionalización Docente (1 año).
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <h5>Documentos externos a presentar en la Institución: </h5>
                                    <ul class="listanueva">
                                        <li>Una fotocopia de DNI ampliado y legalizado. </li>                                        
                                        <li>Cuatro (4) Fotos tamaño pasaporte fondo blanco en papel mate.</li>
                                        <li>Documento que acredite conocimiento de idioma A2/ lengua originaria primaria EIB
                                        </li>
                                    </ul>
                                    <h5 class="mt-3">Documentos a tramitar con la Institución: </h5>
                                    <ul class="listanueva">
                                        <li>Pago por derecho de expediente de de graduación. → s/500.00 </li>
                                        <li>Carta de aceptación de la I.E. donde se realiza la investigación, original.
                                            <small class="text-info">Solicitar modelo en secretaría</small>
                                        </li>
                                        <li>Constancia de no deudor original.
                                            <small class="text-info">Codigo: 28 | Concepto: Constacia de no deudor | Monto:
                                                s/ 30.00</small>, luego de ello solicitar la constancia con el área de Cobranzas.
                                        </li>
                                        <li>Constancia de egresado original.
                                            <small class="text-info">Codigo: 9 | Concepto: Constacia de Egresado | Monto: s/
                                                50.00</small>
                                        </li>
                                        <li>Constancia de primera matrícula.
                                            <small class="text-info">Codigo: 29 | Concepto: Constacia de 1ra matrícula |
                                                Monto: s/ 50.00</small>
                                        </li>
                                        <li>Certificado original de Estudios Superiores:
                                            <ul>
                                                <li style="list-style: disc!important">Para estudiantes provenientes de Institutos (IESP): Certificado
                                                    de Estudios original, visado por la DREC.
                                                </li>
                                                <li style="list-style: disc!important">Para estudiantes provenientes de Universidad. Legalizado por notaria o
                                                    Fedatado por la misma Universidad.</li>
                                            </ul>
                                        </li>
                                        <li>Resolución Directoral de Aprobación de PTI y asignación de asesor original.
                                            <small class="text-info">Codigo: 10 | Concepto: Aprobación de PTI y asesor |
                                                Monto: s/ 50.00</small>
                                        </li>
                                        <li>Informe de Trabajo de Investigación Apto original.</li>
                                        <li>Resolución Directoral de aprobación de Trabajo de Investigación original.
                                            <small class="text-info">Codigo: 10 | Concepto: RD de Aprobación TI | Monto: s/
                                                50.00</small>
                                        </li>
                                        <li>Comprobante de pago por Derecho de obtención de grado de Bachiller original.
                                            <small class="text-info">Codigo: 18 | Concepto: Armado de expediente de
                                                Titulación | Monto: s/ 500.00</small>
                                        </li>
                                    </ul>
                                    <h5 class="mt-">
                                        Como resultado de este trámite obtendrás una Carta de aceptación de Expediente de
                                        Graduación completo.
                                    </h5>                                    
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
                    <li>Tener expediente de Graduación completo.</li>
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
                    <!----<li>Copia de Acta de aprobación de sustentación.</li>-->
                    <li>Un ejemplar empastado por grupo y/o tema de investigación de la versión final de la Tesis.</li>
                    <li>Comprobante de pago por Emisión de Título de Licenciado.</li>
                </ul>
                <p>
                    Como resultado de este trámite obtendrás el Título de Licenciado.
                </p>

                <h3 class="mt-4">Pagos para la realización de los Trámites Presenciales </h3>
                <p>SOLO SE PUEDEN PAGAR EN VENTANILLAS de cualquier agencia a nivel nacional.</p>
                <ul class="listasDecimal">
                    <li>Cliente debe indicar PAGO ORDINARIO de la ASOCIACION PUKLLASUNCHIS – EESPP</li>
                    <li>Cliente debe indicar el concepto a pagar ejemplo: Aprobación de PTI, Cambio de tema, cambio de
                        asesor, disolucion de grupo, extensión de plazo.</li>
                    <li>El representante del servicio realizará la búsqueda y le indicará el monto del concepto ordinario
                        para la validación del cliente</li>
                    <li>Finalmente el Cliente deberá dictar el código(DNI), el nombre y apellidos completos del alumno.</li>
                </ul>
                <p>
                    Estos pagos ordinarios SOLO SE PUEDEN PAGAR EN VENTANILLAS de cualquier agencia a nivel nacional.
                </p>

            </div>
        </div>
    </div> 

    <!-- Pop-up Modal -->
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="modal" id="modal">
        <div class="modal-header text-center">
            <h4 class="mx-auto font-weight-bold">¿Cómo pagar en Caja Cusco?</h4>
        </div>
        <div class="modal-body">
            <p class="font-weight-bold">Todos los pagos del proceso de titulación son pagos ordinarios. <small
                    class="text-danger">
                    <i class="font-weight-bold">(No se considera pago ordinario matriculas ni cuotas semestrales.)</i>
                </small></p>

            <ul>
                <li><span class="span">1</span>
                    Acercarse a las oficinas de <span class="text-danger font-weight-bold">Caja Cusco</span> e indicar que
                    realizarás un pago ordinario de la Asociación
                    Pukllasunchis.
                </li>
                <li><span class="span">2</span>
                    Indicar el concepto y código de pago.<a class="text-primary" target="_blank"
                        href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2025.pdf') }}"> Ver PDF para
                        pagos<small><i class="fa fa-eye"></i></small></a>
                </li>
                <li><span class="span">3</span>
                    Indicar en ventanilla el número de DNI y nombre del estudiante.
                </li>
                <li><span class="span">4</span>
                    <strong>NO</strong> es necesario enviar el voucher ya que en el lapso de <strong>2 días hábiles</strong>
                    le llegará la boleta electrónica
                    emitida por la EESPP a tu correo institucional.
                </li>
                <li><span class="span">5</span>
                    Con esta boleta electrónica podrás realizar tu trámite correspondiente.
                </li>
            </ul>
            <div class="mx-auto text-center mt-3" style="width: 100%;">
                <button class="btn btn-sm btn-primary" onclick="closeModal()">Cerrar</button>
            </div>
        </div>
    </div>
    <script>
        // Función para abrir el modal
        function openModal() {
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        // Función para cerrar el modal
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
