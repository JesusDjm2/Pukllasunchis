@extends('layouts.home')
@section('metas')
    @php $titulo = 'Trámites extraordinarios'; @endphp
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
                <h3 class="mt-5 linea-debajo" id="extraordinario" style="scroll-margin-top: 300px;">Trámites extraordinarios
                </h3>
                <p>Durante el proceso de investigación, en caso de ser necesario, se pueden realizar los siguientes
                    trámites:</p>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Aprobacion.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Formulario de aprobación de PTI<br> <small
                                        class="text-info">Aprobaciòn de PTI y Asesor <br> Codigo: 10 - Costo:
                                        s/50.00</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdA5VN9oAVh_aetqvoMwoZmuuez42ki3Y0xjQqXTgLygGkbrw/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/cambio.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Modificación de cambio de tema <br><small class="text-info">
                                        Modificación de tema de Invest. <br> Codigo: 12 - Costo: s/30.00</small></p>
                                <p></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdJizTDL5Z8DAEfiRWSt32GspxaDO3MVQ_JyvpC_Q3sQt7rvQ/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Cronograma-Pukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Extensión de plazo <br><small class="text-info">Extensión de plazo
                                        trabajo de Invest. <br> Codigo: 11 - Costo: s/50.00</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdmgoFlBMsEoZCkyvGFZqYGC301BkbyZ-wZpGHL5d52_tLbxg/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Disolucion-grupo.png') }}" alt="">
                                </div>
                                <p class="text-center">Disolución de grupo<br> <small class="text-info"> Disolución de grupo
                                        de Invest. <br> Codigo: 14 - Costo: s/30.00</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScCCO2WEtyl70Wk1WanX-uFDMjj3zTqyrc0BBmmNNvgvkCStA/viewform"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="90%" class="mt-1" src="{{ asset('img/min/anulacion-rd.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Anulación RD de PTI<br> <small class="text-info"> Anulación de RD de
                                        PTI <br> Codigo: 53 - Costo: s/20.00</small></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center mb-3">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform?edit_requested=true"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="90%" class="mt-1" src="{{ asset('img/min/Convocados.webp') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Asesoría adicional<br> <small class="text-info"> Asesoría adicional
                                        por sesión <br> Codigo: 52 - Costo: s/20.00</small></p>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- <ul class="listasCuerpo">
                    <li>Modificación o cambio de tema de investigación. (Costo: S/ 30.00)
                        <small>
                            <a href="https://forms.gle/BZkUAmeHVyDfWuvu7" target="_blank" class="text-primary"> Enlace <i
                                    class="fa fa-external-link"></i></a>
                        </small>
                    </li>
                    <li>Disolución del grupo de investigación. (Costo: S/ 30.00)
                        <small>
                            <a href="https://forms.gle/Ud4m3WAprGyAjRSj6" target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i></a>
                        </small>
                    </li>
                    <li>Extensión de plazo. (Costo: S/ 50.00)
                        <small>
                            <a href="https://forms.gle/nZFvLRoRLTXbmikp9" target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i></a>
                        </small>
                    </li>
                    <li>Anulacion de Resolución Directoral de PTI. (Costo: S/ 50.00)
                        <small>
                            <a href="https://forms.gle/caBr9kMvu5tStzTS8" target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i></a>
                        </small>
                    </li>
                    <li>Asesoría adicional.
                        <small><a
                                href="https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform?edit_requested=true"
                                target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i>
                            </a>
                        </small>
                    </li>
                     <li>Cambio de Asesor
                        <small><a
                                href="https://docs.google.com/forms/d/e/1FAIpQLSe95m3LlPs8Ii-ZtWGxCL7ZQktBcu7eEzeMmBFTbIHBl-NclA/viewform"
                                target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i>
                            </a>
                        </small>
                    </li> 
                    <li>Cambio de tema (Costo: s/30.00)
                        <small><a
                                href="https://docs.google.com/forms/d/e/1FAIpQLSdA5VN9oAVh_aetqvoMwoZmuuez42ki3Y0xjQqXTgLygGkbrw/viewform"
                                target="_blank" class="text-primary">
                                Enlace <i class="fa fa-external-link"></i>
                            </a>
                        </small>
                    </li>
                </ul> --}}


                {{-- <h2 class="mt-4 linea-debajo">Documentos para el proceso de aprobación de PTI</h2> --}}
                <!-- Pop-up Modal -->
                <div class="modal-overlay" id="modalOverlay"></div>
                <div class="modal" id="modal">
                    <div class="modal-header text-center">
                        <h4 class="mx-auto font-weight-bold">¿Cómo pagar en Caja Cusco?</h4>
                    </div>
                    <div class="modal-body">
                        <p class="font-weight-bold">Todos los pagos del proceso de titulación son pagos ordinarios. <small
                                class="text-danger">
                                <i class="font-weight-bold">(No se considera pago ordinario matriculas ni cuotas
                                    semestrales.)</i>
                            </small></p>

                        <ul>
                            <li> <span class="span">1</span>
                                Acercarse a las oficinas de <span class="text-danger font-weight-bold">Caja Cusco</span> e
                                indicar que
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
                                <strong>NO</strong> es necesario enviar el voucher ya que en el lapso de <strong>2 días
                                    hábiles</strong>
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
            </div>
        </div>
    </div>
@endsection
