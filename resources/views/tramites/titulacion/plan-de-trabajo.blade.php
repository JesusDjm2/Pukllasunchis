@extends('layouts.home')
@section('metas')
    @php $titulo = 'Plan de Trabajo de Investigación (PTI)'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection
@section('contenido')
    <style>
        .listasNuevo {
            color: #5a5a5a;
            font-family: "Poppins", sans-serif;
        }

        .listasNuevo li {
            list-style: decimal;
            margin-left: 1.2em;
            margin-bottom: 0.5em;
        }

        .listasNuevo li ul li {
            list-style: disc;
        }

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
                <h2 class="linea-debajo">Plan de Trabajo de Investigación (PTI) </h2>
                <p>
                    Es la propuesta de investigación que realizan los estudiantes al finalizar el VIII ciclo, cuyo
                    planteamiento debe contribuir a la solución de un problema nacional, regional o local en el ámbito
                    educativo. Este problema puede ser identificado durante el proceso de la práctica profesional y debe
                    favorecer los procesos de educación donde se considere el respeto a la diversidad y el fomento de la
                    identidad y la cultura, de preferencia desde los contextos andinos y amazónicos. Debe cumplir con la
                    estructura solicitada por la escuela en su guía de investigación.
                </p>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Formato-PTI-2025.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="Formato PTI">
                                </div>
                                <p class="text-center">Formato PTI</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Guia-de-investigacion-2.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de Investigación</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de <br>Redacción</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                </div><br><br>

                <h3 class="mt-4 linea-debajo">Requisitos para la aprobación del PTI:</h3>
                <p>
                    Para solicitar la aprobación del Plan de Trabajo de Investigación debes:
                </p>
                <ul class="listasNuevo">
                    <li>Hacer el pago correspondiente por derecho de aprobación del PTI y asignación de asesor <strong>como
                            pago ordinario</strong> en las
                        oficinas de Caja Cusco.<small class="text-info">(Concepto: Aprobación de PTI Y Asesor / Codigo: 10 -
                            Costo: s/50.00)</small> </li>
                    <li>Descarga y completa el Formato del PTI. <a href="{{ asset('pdf/Formato-PTI-2025.docx') }}"
                            target="_blank"></li>
                    <li>Llenar <a
                            href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                            target="_blank" class="text-primary"> FORMULARIO DE APROBACIÓN DE PTI. <i
                                class="fa fa-external-link"></i></a></li>
                    <li>Adjuntar:
                        <ul>
                            <li>Plan de Trabajo de Investigación en formato PDF</li>
                            <li>Comprobante de pago por derecho de aprobación del PTI.</li>
                        </ul>
                    </li>
                    <li>Esperar en el correo electrónico institucional la confirmación del trámite con lo siguiente:
                        <ul>
                            <li>R.D. de Aprobación de PTI y asignación de asesor</li>
                            <li>Boleta por el pago que realizaste</li>
                        </ul>
                    </li>
                </ul>
                <h3 class="mt-5">Después de que tengas tu PTI aprobado, recuerda solicitar la carta de aplicación de tu
                    Trabajo de investigación, para ello debes seguir los siguientes pasos:</h3>
                <ul class="listasNuevo">
                    <li>Hacer el pago de S/ 10 soles como pago ordinario en las oficinas de Caja Cusco. <small
                            class="text-info">(Concepto: Carta de
                            presentación para INV / Codigo: 31 )</small>
                    </li>
                    <li> Llenar el formulario. <small><a
                                href="https://docs.google.com/forms/d/e/1FAIpQLSe9QSB57MVyK2xsyLRK5p7UAO_iWE0hYj4V_q0LQcZE32W3Qg/viewform"
                                target="_blank" class="text-primary"> Enlace. <i class="fa fa-external-link"></i></small>
                        </a>
                    </li>
                    <li>Como resultado de este trámite recibirás la carta de aplicación de Trabajo de Investigación y una
                        carta modelo de Aceptación de Trabajo de Investigación.
                    </li>
                </ul>
                
                
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <h3 class="mt-3 linea-debajo">Para pagos Ordinarios:</h3>
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
