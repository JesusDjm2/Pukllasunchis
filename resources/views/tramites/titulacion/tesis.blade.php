@extends('layouts.home')
@section('metas')
    @php $titulo = 'Trámites Tesis'; @endphp
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
            /* Fondo oscuro */
            z-index: 1999;
            /* Un poco menor que el modal */
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
            /* Necesario para ::after */
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
                <h2 class="mt-4 scroolOk linea-debajo" id="tesis">Tesis</h2>
                <p>
                    {{-- Es el documento final que el bachiller presenta para obtener la licenciatura. Muestra en detalle la
                    continuación de la aplicación del trabajo de investigación. Este documento y la sustentación son parte
                    de los requisitos que se presenta como trámites administrativos para la titulación. Mide las
                    competencias profesionales en torno a un área académica o disciplina determinada, en el que se
                    identifica un problema o conjunto de problemas referidos a situaciones educativas detectadas
                    preferentemente en la práctica docente o en otros escenarios de la realidad socioeducativa. Se debe
                    cumplir con la estructura solicitada por la escuela en su guía de investigación. --}}
                    La tesis es el documento culminante que llevas adelante como bachiller para alcanzar tu grado de
                    Licenciado. En ella demuestras la aplicación avanzada del Trabajo de Investigación: planteas un problema
                    educativo relevante (preferiblemente surgido de tu práctica docente) y lo analizas con rigor académico,
                    siguiendo la estructura establecida en nuestra Guía de Investigación. <br><br>
                    Tu tesis no solo valida tus competencias profesionales, sino que refleja tu compromiso con la mejora
                    educativa en escenarios reales. La sustentación de este trabajo es tan importante como el documento
                    mismo, ya que completa el proceso formal de titulación.
                </p>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-12 mb-4">
                    </div>
                    <div class="col-lg-4 mb-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/FormatoTesis.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Formato de Tesis</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/CaratulaTesis-2025.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                                </div>
                                <p class="text-center">Caratula Tesis (FID)</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/CaratulaTesis-2025-PPD.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                                </div>
                                <p class="text-center">Caratula Tesis (PPD)</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Guia-de-Elaboracion-de-Tesis.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de Elaboración<br> de Tesis</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de <br>Redacción</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                </div><br>




                <h3>Requisitos para la obtención del Título de Licenciado: </h3>
                <p>Una vez terminada la Tesis, para la obtención del Título de Licenciado se siguen estos pasos:
                </p>
                <ul class="listasDecimal">
                    <li> Solicitar revisión de originalidad (Turnitin):
                        <ul>
                            <li>Hacer el pago correspondiente por derecho de revisión de originalidad (Turnitin)
                                <strong>como pago ordinario</strong> en las
                                oficinas de Caja Cusco. <small class="text-info">(Concepto: REVISION TURNITIN DE TESIS /
                                    Codigo: 37 / Costo: s/30.00)</small>
                            </li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE REVISIÓN DE ORIGINALIDAD.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Versión final de la Tesis en formato WORD</li>
                                    <li>Comprobante de pago por derecho de revisión de originalidad</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional el Reporte de
                                originalidad (Turnitin), en un plazo no mayor a 10 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Solicitar la revisión de la Tesis (Dictaminantes):
                        <ul>
                            <li> Hacer el pago de S/100 soles por derecho de revisión de Tesis en las oficinas de
                                Caja Cusco. <small class="text-info">(Concepto: REVISIÓN DE TESIS /
                                    Codigo: 32 / Costo: s/100.00)</small></li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSfU1nSpAs5WnyxP6rcmsQ8DIZlwetYSLyWRTqJ1srFtVCo5HQ/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE REVISIÓN DE TESIS.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Tesis en formato WORD</li>
                                    <li>Reporte de originalidad Turnitin (con máximo 20% de similitud en Turnitin)</li>
                                    <li>Comprobante de pago por derecho de revisión de Tesis</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional el Informe de Tesis
                                apta, en un plazo no mayor a 60 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Solicitar sustentación de Tesis:
                        <ul>
                            <li> Hacer el pago por derecho de RD de sustentación de Tesis en las oficinas de
                                Caja Cusco. <small class="text-info">(Concepto: Fecha de sustentación de Tesis /
                                    Codigo: 21 / Costo: s/100.00)</small></li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSf4DRlwjTV-vK5FVud94tUxTHi8h61brejjn9bzxBpv7ouqpQ/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE ASIGNACIÓN DE FECHA DE
                                    SUSTENTACIÓN.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Informe de Tesis apta en formato WORD</li>
                                    <li>Reporte de originalidad Turnitin (con máximo 20% de similitud en Turnitin)</li>
                                    <li>Versión final de la Tesis en formato WORD</li>
                                    <li>Diapositivas para sustentación de Tesis en formato PPT</li>
                                    <li>Constancia de inscripción en SUNEDU del diploma de Bachiller en formato PDF
                                    </li>
                                    <li>Comprobante de pago por derecho de sustentación de Tesis</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional la RD de designación de
                                fecha y
                                hora para sustentación de Tesis, en un plazo no mayor a 5 días hábiles.</li>
                        </ul>
                    </li>
                    <li> Solicitar Resolución Directoral (RD) de aprobación de sustentación
                        <ul>
                            <li>Solicitar Resolución Directoral (RD) de aprobación de sustentación.
                                <small class="text-info">(Concepto: RD de aprobación de Tesis /
                                    Codigo: 33 / Costo: s/50.00)</small>
                            </li>
                            <li>Hacer el pago de S/50 soles por derecho de RD de aprobación de sustentación de Tesis en las
                                oficinas de Caja Cusco.</li>
                            <li>Llenar el formulario de <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSc8k02uEAuRt9OdrGpj1Xe6OIBdXXCZ2j7pNToBxhdktq_smQ/viewform"
                                    target="_blank" class="text-primary">Resolución de aprobación de Sustentación de
                                    Tesis</a>
                            </li>
                            <li> Acta de sustentación</li>
                            <li>Informe de apto</li>
                            <li>Comprobante de pago</li>
                        </ul>
                    </li>

                    </li>
                    <li>Solicitar registro de Tesis en repositorio institucional:
                        <ul>
                            <li>Hacer el pago por derecho de registro de Tesis en repositorio institucional en
                                las oficinas de Caja Cusco. <small class="text-info">(Concepto: Registro de Tesis en
                                    repositorio /
                                    Codigo: 34 / Costo: s/50.00)</small></li>
                            <li>Descargar, completar y firmar la <a href="{{ asset('pdf/Autorizacion-Puklla.docx') }}"
                                    target="_blank" class="text-primary"> AUTORIZACIÓN DE REGISTRO EN
                                    REPOSITORIO.</a></li>
                            <li>Descargar, completar y firmar la <a
                                    href="{{ asset('pdf/Declaración-jurada-de-autenticidad.docx') }}" target="_blank"
                                    class="text-primary"> Declaración jurada de autenticidad</a></li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform"
                                    target="_blank" class="text-primary">FORMULARIO DE REGISTRO EN REPOSITORIO.</a> </li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Enlace a la versión final del Tesis en formato PDF</li>
                                    <li>Formato de autorización de depósito en repositorio en formato PDF</li>
                                    <li>Declaración Jurada de autenticidad en formato PDF</li>
                                    <li>Comprobante de pago por derecho de registro en repositorio institucional</li>
                                </ul>
                            </li>
                            <li>Si es que el Tesis no tiene ninguna observación, recibirás en tu correo institucional el URL
                                de
                                registro del Tesis en el repositorio institucional</li>
                        </ul>
                    </li>
                    <li>Solicitar emisión de diploma de Licenciado previo pago de s/400.00 en las oficinas de <span
                            class="text-danger font-weight-bold">Caja
                            Cusco:</span>
                        <ul>
                            <li>Este trámite solo se realiza de manera presencial en la oficina de secretaría de la EESP
                                Pukllasunchis. <small class="text-info">(Concepto: Emisión título de Licenciado /
                                    Codigo: 35 / Costo: s/400.00)</small></li>
                        </ul>
                    </li>
                    <li>Solicitar registro e inscripción de Diploma de Licenciado previo pago de s/200.00 en las oficinas de
                        <span class="text-danger font-weight-bold">Caja Cusco:</span>
                        <p>
                            Este trámite permite que el Título de Licenciado sea reconocido oficialmente en SUNEDU. Se deben
                            entregar los siguientes documentos:
                        </p>
                        <ul>
                            <li>Pagar en Caja Cusco con el sigiente concepto: <small class="text-info">(Concepto:
                                    Inscripción título a SUNEDU /
                                    Codigo: 36 / Costo: s/200.00)</small></li>
                        </ul>


                        <ul class="listasCuerpo">
                            <li>FUT debidamente llenado. Puedes solicitarlo en la oficina de secretaría o descargarlo de la
                                página
                                web. <small><a class="text-primary" target="_blank"
                                        href="https://forms.gle/L7WHbS67NKYnWug69">Formulario <i
                                            class="fa fa-external-link"></i> (Solo permite llenar con correo
                                        institucional)</a></small></li>

                            <li>Comprobante de pago por Inscripción de Título de Licenciado.</li>
                        </ul>
                    </li>
                </ul>
                <h3 class="linea-debajo">Documentos para el proceso de obtención del Título de Licenciado </h3>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-12 mb-4">
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/revision.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Revisión de <br>Originalidad</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfU1nSpAs5WnyxP6rcmsQ8DIZlwetYSLyWRTqJ1srFtVCo5HQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Solicitud de Dictaminantes<br><small> Revisión de tesis</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSf4DRlwjTV-vK5FVud94tUxTHi8h61brejjn9bzxBpv7ouqpQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Solicitud de <br>Sustentación</p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-12 mb-4">
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSc8k02uEAuRt9OdrGpj1Xe6OIBdXXCZ2j7pNToBxhdktq_smQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Abrobacion.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">RD Aprobación <br> de Tesis</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Autorizacion-Puklla.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/flujigrama.webp') }}" alt="">
                                </div>
                                <p class="text-center">Autorización de <br>Repositorio</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Registro de <br>Repositorio</p>
                            </a>
                        </div>
                    </div>
                </div><br>

                <div class="row mb-3 mt-4">
                    <div class="col-lg-12">
                        <h3>Pagos para la Obtención del Título de Licenciado </h3>
                        <p>La EESPP Pukllasunchis ha establecido el siguiente medio de pago: </p>
                        <p>
                            Los pagos ordinarios <strong>SOLO SE PUEDEN PAGAR EN VENTANILLAS</strong> de cualquier agencia a
                            nivel nacional de CAJA CUSCO. <a class="text-primary" target="_blank"
                                href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2025-2.pdf') }}">Ver PDF para
                                pagos<small><i class="fa fa-eye"></i></small></a>
                        </p>
                    </div>
                </div>
                <p class="generic-blockquote">
                    <strong>OJO:</strong><br>
                    - Cliente debe indicar <span class="text-danger font-weight-bold">PAGO ORDINARIO</span> de la
                    <span class="text-danger font-weight-bold">ASOCIACIÓN PUKLLASUNCHIS - EESPP</span><br>
                    - Cliente debe indicar el concepto a pagar (ejemplo: Aprobación de PTI, Cambio de tema, cambio de
                    asesor, disolucion de grupo, extensión de plazo.<br>
                    - El representante del servicio realizará la búsqueda y le indicará el monto del concepto ordinario para
                    la validación del cliente.<br>
                    - Finalmente el Cliente deberá dictar el código (DNI), el nombre y apellidos completos del
                    alumno.
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
                <li> <span class="span">1</span>
                    Acercarse a las oficinas de <span class="text-danger font-weight-bold">Caja Cusco</span> e indicar que
                    realizarás un pago ordinario de la Asociación
                    Pukllasunchis.
                </li>
                <li><span class="span">2</span>
                    Indicar el concepto y código de pago.<a class="text-primary" target="_blank"
                        href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2025-2.pdf') }}"> Ver PDF para
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
