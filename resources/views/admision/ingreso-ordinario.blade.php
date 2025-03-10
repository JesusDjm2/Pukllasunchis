@extends('layouts.home')
@section('metas')
    @php $titulo = 'Admisión Ordinario'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <style>
        /* Contenedor general */
        .steps-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f7f9fc;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Título de los pasos */
        .steps-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        /* Estilo de cada paso */
        .step {
            display: flex;
            align-items: normal;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s forwards;
        }

        /* Espaciado entre el icono y el contenido */
        .step+.step {
            margin-top: 30px;
        }

        /* Animación de entrada */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Icono del paso */
        .step-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #d59d52;
            color: white;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            margin-right: 20px;
            transition: background-color 0.3s ease;
        }

        .step-icon:hover {
            background-color: #bd7a1a;
        }

        /* Contenido del paso */
        .step-content h3 {
            font-size: 18px;
            color: #2c3e50;
            margin: 0 0 10px;
        }

        .step-content p {
            font-size: 14px;
            color: #7f8c8d;
            margin: 0;
        }

        /* Animación de los pasos */
        .step:nth-child(1) {
            animation-delay: 0.2s;
        }

        .step:nth-child(2) {
            animation-delay: 0.4s;
        }

        .step:nth-child(3) {
            animation-delay: 0.6s;
        }

        .step:nth-child(4) {
            animation-delay: 0.8s;
        }

        /* Estilo de los pasos completados */
        .step.completed .step-icon {
            background-color: #27ae60;
        }

        .step.completed .step-icon:hover {
            background-color: #2ecc71;
        }

        .step.completed .step-content h3 {
            color: #27ae60;
        }
    </style>
    <div class="bradcam_area admision bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a>Admisión / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso">
                    <h3 class="linea-debajo">Admisión</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('ordinario') }}"><i class="fa fa-caret-right fa-sm"></i> Ingreso Ordinario</a>
                        </li>
                        <li><a href="{{ route('exoneracion') }}"><i class="fa fa-caret-right fa-sm"></i> Por Exoneración</a>
                        </li>
                        <li><a href="{{ route('traslado') }}"><i class="fa fa-caret-right fa-sm"></i> Traslado Externo</a>
                        </li>
                        <li><a href="{{ route('resultados') }}"><i class="fa fa-caret-right fa-sm"></i> Resultados</a></li>
                        <li><a href="{{ route('formInscripcionRegular') }}"><i class="fa fa-caret-right fa-sm"></i>
                                Inscríbete acá</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <div class="row">
                    <div class="col-lg-7">
                        <p class="text-justify">
                            Si eres egresado de un colegio público o privado puedes participar de nuestro proceso de
                            admisión.<br><br>

                            La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza <strong>una vez
                                al
                                año</strong>, a
                            través de un proceso de <strong>evaluación</strong> (prueba- entrevista y evaluación de
                            aptitudes) que
                            se realiza todos
                            los años <strong>del mes de marzo.</strong>
                        </p>
                    </div>
                    <div class="col-lg-5 text-center">
                        <p class="text-center font-weight-bold">¡Inscríbete acá!</p>
                        <a href="{{ route('formInscripcionRegular') }}" target="_blank">
                            <img class="img-fluid" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                alt="Ingreso Ordinario" width="200px">
                        </a>
                    </div>
                </div>
                <h3 class="linea-debajo">Requisitos de inscripción:</h3>
                <p>
                    Para inscribirte y tener derecho al proceso de evaluación (en marzo) debes:
                </p>
                <ul class="listasCuerpo">
                    <li>
                        Hacer el pago de S/150 soles por derecho a la inscripción en las oficinas de Caja Cusco.
                    </li>
                    <li> Llenar FICHA DE INSCRIPCIÓN</li>
                    <li>
                        Adjuntar:
                        <ul>
                            <li>Partida de nacimiento</li>
                            <li>Certificado de estudios de Educación Secundaria</li>
                            <li>Copia simple del documento nacional de identidad (DNI)</li>
                            <li> Dos fotografías recientes e iguales, tamaño carné, de frente, a color y en fondo blanco.
                            </li>
                            <li> Comprobante de pago del derecho de inscripción. </li>
                        </ul>
                    </li>
                </ul>
                <h3 class="linea-debajo">Documentos para el proceso de admisión: </h3>
                <div class="row justify-content-center align-items-center fichas">
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="{{ route('formInscripcionRegular') }}"
                                target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Ficha de inscripción</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="card">
                            <a href="{{ asset('pdf/Prospecto-de-Admision-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Prospecto-Pukllasunchis.png') }}"
                                        alt="Prospecto Pukllasunchis">
                                </div>
                                <p class="text-center">Prospecto de Admisión</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Protologo.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Protocolo-Pukllasunchis.png') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>
                                <p class="text-center">Protocolo Pukllasunchis</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Cronograma-2024-I.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Cronograma-Pukllasunchis.png') }}"
                                        alt="Cronograma Pukllasunchis">
                                </div>
                                <p class="text-center">Cronograma Pukllasunchis</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="">
                                <img width="100%" src="{{ asset('img/min/Secuencia de Inscripcion.png') }}"
                                    alt="Secuencia de Inscripción Pukllasunchis">
                                <p class="text-center">Secuencia de Admisión</p>
                            </a>
                        </div>
                    </div>
                </div>
                {{-- pasoa inscripcion --}}
                <div class="steps-container">
                    <h2 class="steps-title">Pasos para la inscripción al examen de admisión</h2>

                    <div class="step" id="step1">
                        <div class="step-icon">
                            <span>1</span>
                        </div>
                        <div class="step-content">
                            <h3>Realiza el pago por Derecho de Inscripción</h3>
                            <p>s/150.00 (ciento cincuenta soles) en agentes de <span class="text-danger">Caja Cusco</span>
                            </p>
                            <p>Indicar concepto y código: EXAMEN DE ADMISION - 1 </p>
                        </div>
                    </div>

                    <div class="step" id="step2">
                        <div class="step-icon">
                            <span>2</span>
                        </div>
                        <div class="step-content">
                            <h3>Tomar una foto clara de los requisitos</h3>
                            <p>Partida de nacimiento</p>
                            <p>Certificado de Estudios de Secundaria</p>
                            <p>Copia simple de DNI</p>
                            <p>Fotografía (actual con fondo blanco)</p>
                            <p>Voucher de pago (Paso 1)</p>
                        </div>
                    </div>

                    <div class="step" id="step3">
                        <div class="step-icon">
                            <span>3</span>
                        </div>
                        <div class="step-content">
                            <h3>Completar el formulario de Inscripción</h3>
                            <p>Ingresa a nuestro formulario: <a class="text-primary"
                                    href="{{ route('formInscripcionRegular') }}"
                                    target="_blank">Formulario <i class="fa fa-external-link fa-sm"></i></a> </p>
                            <p>Lee atentamente los enunciados e ingresa los datos solicitados</p>
                        </div>
                    </div>

                    <div class="step" id="step4">
                        <div class="step-icon">
                            <span>4</span>
                        </div>
                        <div class="step-content">
                            <h3>Confirmación de inscripción</h3>
                            <p>Al finalizar tu inscripción recibirás tu <strong>Comprobante de inscripción</strong> en tu
                                correo.</p>
                        </div>
                    </div>
                </div>

                {{-- <h3 class="linea-debajo">Pago por Derecho de Inscripción </h3>
                <p>La inscripción para postulantes en cualquiera de las tres modalidades es de S/. 150.00 soles.<br>
                    La EESPP Pukllasunchis ha establecido el siguiente medio de pago:
                </p> --}}

               {{--  <ul class="listasCuerpo mb-4">
                    <li><strong>En ventanilla</strong> de las agencias de CMAC CUSCO a nivel nacional:
                        <ul>
                            <li>Cliente debe indicar: Pago de convenio</li>
                            <li>"Asociación Pukllasunchis - EESP" + Codigo o nombre del cliente/alumno</li>
                            <li>Ejemplo: <strong> <span class="text-danger"> Asociación Pukllsunchis -
                                        EESP</span>75050934</strong></li>
                        </ul>
                    </li>
                    <li><strong>En agentes corresponsables</strong> de la CMAC Cusco:
                        <ul>
                            <li>Clientes debe indicar: Pago de convenio</li>
                            <li>"Asociación Pukllasunchis - EESP" + Dictar: <strong> <span class="text-danger">523(prefijo
                                        Institucional)</span> Código de cliente/Alumno</strong></li>
                            <li>Ejemplo: <strong> <span class="text-danger"> 523</span>75050934</strong></li>
                        </ul>
                    </li>
                </ul> --}}
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
@endsection
