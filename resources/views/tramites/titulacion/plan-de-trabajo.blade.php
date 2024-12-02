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
                            <a href="{{ asset('pdf/Formato-PTI.docx') }}" target="_blank">
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
                            <a href="{{ asset('pdf/Guia-de-redaccion.pdf') }}" target="_blank">
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
                <h3 class="mt-4 scroolOk linea-debajo">Requisitos para la apobación del PTI:</h3>
                <p>
                    Para solicitar la aprobación del Plan de Trabajo de Investigación debes:
                </p>
                <ul class="listasCuerpo">
                    <li>Hacer el pago correspondiente por derecho de aprobación del PTI y asignación de asesor <strong>como pago ordinario</strong> en las
                        oficinas de Caja Cusco.<small class="text-info">(Concepto: Aprobación de PTI Y Asesor / Codigo: 10 - Costo: s/50.00)</small> </li>
                    <li>Descargar y completar el Formato del PTI.</li>
                    <li>Llenar <a
                            href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                            target="_blank" class="text-primary"> FORMULARIO DE APROBACIÓN DE PTI.</a></li>
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
                <h3 class="mt-4">Trámites extraordinarios</h3>
                <p>Durante el proceso de investigación, en caso de ser necesario, se pueden realizar los siguientes
                    trámites:</p>
                <ul class="listasCuerpo">
                    <li>Modificación o cambio de tema de investigación. (Costo: S/ 80.00)</li>
                    <li>Cambio de asesor de investigación. (Costo: S/ 50.00)</li>
                    <li>Disolución del grupo de investigación. (Costo: S/ 80.00)</li>
                    <li>Extensión de plazo. (Costo: S/ 50.00)</li>
                </ul>

                <h2 class="mt-4 scroolOk linea-debajo mt-3" id="investigacion">Documentos para el proceso de aprobación de
                    PTI
                </h2>

                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Formulario de<br> aprobación de PTI</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdA5VN9oAVh_aetqvoMwoZmuuez42ki3Y0xjQqXTgLygGkbrw/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/cambio.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Cambio de<br>tema</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSe95m3LlPs8Ii-ZtWGxCL7ZQktBcu7eEzeMmBFTbIHBl-NclA/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/cambio-asesor.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Cambio de <br>Asesor</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdmgoFlBMsEoZCkyvGFZqYGC301BkbyZ-wZpGHL5d52_tLbxg/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Disolucion-grupo.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Disolución <br>de grupo</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdJizTDL5Z8DAEfiRWSt32GspxaDO3MVQ_JyvpC_Q3sQt7rvQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Cronograma-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Extensión de <br>Plazo</p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 mt-4">
                    <div class="col-lg-12">
                        <h3 class="mt-3">Para pagos Ordinarios:</h3>
                        <p>
                            Los pagos ordinarios <strong>SOLO SE PUEDEN PAGAR EN VENTANILLAS</strong> de cualquier agencia a
                            nivel nacional de CAJA CUSCO. <a class="text-primary" target="_blank"
                                href="{{ asset('pdf/PAGOS-ORDINARIOS-CAJA-CUSCO-2.pdf') }}">Ver PDF para pagos<small><i
                                        class="fa fa-eye"></i></small></a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
