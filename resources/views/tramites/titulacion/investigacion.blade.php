@extends('layouts.home')
@section('metas')
    @php $titulo = 'Trabajo de Investigación (TI)'; @endphp
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
                <h2 class="linea-debajo" id="planTrabajo">{{ $titulo }}
                </h2>
                <p>
                    Tiene relación con los principales ejes planteados en el plan de estudios, el graduando debe demostrar
                    que domina los aspectos desarrollados en el currículo. El TI supone el desarrollo de aptitudes y
                    habilidades relacionadas con el perfil de egreso y el planteamiento de un tema preciso a analizar
                    relacionado con situaciones detectadas en la realidad educativa. El desarrollo del TI incluye la
                    revisión de la literatura actualizada, una metodología de análisis de la información recolectada y una
                    exposición de resultados. Conduce a la obtención del Grado de Bachiller en Educación. El TI se elabora
                    de forma progresiva desde el IX ciclo y se debe cumplir con la estructura solicitada por la escuela en
                    su guía de investigación.
                </p>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/FormatoTI.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Formato de TI</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/CaratulaTI.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                                </div>
                                <p class="text-center">Caratula TI</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Guia-Trabajo-de-Investigacion.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de <br>Investigación</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 text-center">
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
                </div>
                <br><br>
                <h3 class="mt-4 scroolOk linea-debajo">Requisitos para la obtención del Grado de Bachiller:</h3>
                <p>
                    Una vez terminado el Trabajo de Investigación, para la obtención del Grado de Bachiller se siguen estos
                    pasos:
                </p>
                <ul class="listasDecimal">
                    <li>
                        Para la Aplicación de Trabajo de Investigación
                        <ul>
                            <li>Llenar el Formato Único de Trámite (FUT):</li>
                            <ul>
                                <li>Hacer el pago de S/ 10 soles.</li>
                                <li>Presentar FUT en la Secretaría o llenar el formulario.</li>
                                <li>o Como resultado de este trámite recibirás la carta de aplicación de Trabajo de
                                    Investigación y una carta modelo de Aceptación de Trabajo de Investigación. </li>                                
                            </ul>
                        </ul>
                        <p class="text-primary">Nota : ¿Para qué sirve? La Carta de Aceptación emitida por la institución les ayudará a completar su expediente para obtener el grado de bachiller.</p>
                    </li>
                    <li>Solicitar revisión de originalidad (Turnitin):
                        <ul>
                            <li>Hacer el pago correspondiente por derecho de revisión de originalidad ( Turnitin) en las
                                oficinas de Caja Cusco.</li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE REVISIÓN DE ORIGINALIDAD.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Versión final del TI en formato WORD</li>
                                    <li>Comprobante de pago por derecho de revisión de originalidad</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional el Reporte de
                                originalidad (Turnitin), en un plazo no mayor a 10 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Solicitar la revisión del TI:
                        <ul>
                            <li>Hacer el pago de S/100 soles por derecho de revisión del TI en las oficinas de Caja Cusco.
                            </li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSfr-AXQ-NLEpO_2PGr8E8MVvHp8Cw8ook1hfqVB_WFlYpRw1Q/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE REVISIÓN DE TI.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Trabajo de Investigación en formato WORD</li>
                                    <li>Reporte de originalidad Turnitin (con máximo 20% de similitud en Turnitin)</li>
                                    <li>Comprobante de pago por derecho de revisión del TI</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional el Informe de TI apto,
                                en un plazo no mayor a 30 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Solicitar fecha de exposición de TI:
                        <ul>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSfyasqxxCR72v7aSnmtpISOedxYQzqV11igfLnegIr_biXIew/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE SOLICITUD DE FECHA DE EXPOSICIÓN DE
                                    TI.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Informe de TI apto en formato PDF</li>
                                    <li>Diapositivas para exposición de TI en formato PPT</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional la RD de asignación de
                                fecha de exposición de TI, en un plazo no mayor a 3 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Después de la exposición del TI, solicitar RD de aprobación del TI:
                        <ul>
                            <li>Hacer el pago de S/50 soles por derecho de RD de aprobación del TI en las oficinas de
                                Caja Cusco.</li>
                            <li>Llenar <a
                                    href="https://docs.google.com/forms/d/e/1FAIpQLSeGQ3XQAjWD2IPRUQnHIGfreOzNmgs-GceyKXgaGPsRg4EngQ/viewform"
                                    target="_blank" class="text-primary"> FORMULARIO DE APROBACIÓN DE TI.</a></li>
                            <li>Adjuntar:
                                <ul>
                                    <li>Informe de TI apto en formato PDF</li>
                                    <li>Acta de aprobación de TI en formato PDF</li>
                                    <li>Comprobante de pago por derecho de RD de aprobación del TI</li>
                                </ul>
                            </li>
                            <li>Como resultado de este trámite, recibirás en tu correo institucional la RD de aprobación del
                                TI,
                                en un plazo no mayor a 3 días hábiles.</li>
                        </ul>
                    </li>
                    <li>Armar expediente de graduación:
                        <ul>
                            <li>Este trámite solo se realiza de manera presencial en la oficina de secretaría de la EESP
                                Pukllasunchis. <a href="{{ route('tramites') }}#derecho"> <small class="text-primary">Ir a
                                        trámite
                                        <i class="fa fa-arrow-right"></i> </small></a></li>
                        </ul>
                    </li>
                    <li>Solicitar registro de TI en repositorio institucional:
                        <ul>
                            <li>Hacer el pago de S/50 soles por derecho de registro de TI en repositorio institucional en
                                las oficinas de Caja Cusco.</li>
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
                                    <li>Enlace a la versión final del TI en formato PDF</li>
                                    <li>Formato de autorización de depósito en repositorio en formato PDF</li>
                                    <li>Declaración Jurada de autenticidad en formato PDF</li>
                                    <li>Comprobante de pago por derecho de registro en repositorio institucional</li>
                                </ul>
                            </li>
                            <li>Si es que el TI no tiene ninguna observación, recibirás en tu correo institucional el URL de
                                registro del TI en el repositorio institucional</li>
                        </ul>
                    </li>
                    <li>Solicitar emisión de diploma de grado de bachiller previo pago de s/400.00 en las oficinas de <span
                            class="text-danger font-weight-bold">Caja Cusco:</span>
                        <ul>
                            <li>Este trámite solo se realiza de manera presencial en la oficina de secretaría de la EESP
                                Pukllasunchis.</li>
                        </ul>
                    </li>
                    <li>Solicitar inscripción de grado de bachiller en SUNEDU previo pago de s/200.00 en las oficinas de
                        <span class="text-danger font-weight-bold">Caja Cusco:</span>:
                        <ul>
                            <li>Este trámite solo se realiza de manera presencial en la oficina de secretaría de la EESP
                                Pukllasunchis</li>
                        </ul>
                    </li>
                </ul>

                <p class="generic-blockquote mt-4">
                    <strong>Nota:</strong><br>
                    Los pagos se realizan por cualquier plataforma disponible de Caja Cusco al mismo número de cuenta.
                </p><br><br>

                <h2 class="mt-4 scroolOk linea-debajo" id="investigacion">Documentos para el proceso de obtención del
                    Grado
                    de Bachiller </h2>
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
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfr-AXQ-NLEpO_2PGr8E8MVvHp8Cw8ook1hfqVB_WFlYpRw1Q/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%"
                                        src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Formulario de<br><small> Revisión de TI</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfyasqxxCR72v7aSnmtpISOedxYQzqV11igfLnegIr_biXIew/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Solicitud de <br>Exposición</p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-12 mb-4">
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeGQ3XQAjWD2IPRUQnHIGfreOzNmgs-GceyKXgaGPsRg4EngQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Abrobacion.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Formulario de <br> aprobación de TI</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/CaratulaTesis.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/flujigrama.webp') }}" alt="">
                                </div>
                                <p class="text-center">Autorización de <br>depósito en RI</p>
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

                </div>
                <div class="row mb-3 mt-4">
                    <div class="col-lg-12">
                        <h2 class="mt-3">Para pagos Ordinarios:</h2>
                        <p>
                            Los pagos ordinarios <strong>SOLO SE PUEDEN PAGAR EN VENTANILLAS</strong> de cualquier agencia a
                            nivel nacional de CAJA CUSCO. <a class="text-primary" target="_blank"
                                href="../../../../public/pdf/Conceptos-ordinarios-caja-cusco.pdf">Ver PDF para
                                pagos<small><i class="fa fa-eye"></i></small></a>
                        </p>
                    </div>
                </div>

                <p class="generic-blockquote">
                    <strong>OJO:</strong><br>
                    - Cliente debe indicar <span class="text-danger font-weight-bold">PAGO ORDINARIO</span> de la
                    <span class="text-danger font-weight-bold">ASOCIACIÓN PUKLLASUNCHIS - EESPP</span><br>
                    - Cliente debe indicar el concepto a pagar (ejemplo: Examen de admisión, Constancia de
                    Matrpicula, Traslado, etc.)<br>
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
