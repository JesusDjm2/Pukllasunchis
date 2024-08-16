@extends('layouts.home')
@section('metas')
    @php $titulo = 'Licencia de Estudios'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Trámites / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Trámites</h3>
                    <ul class="submenu2">
                        <li><a href="{{route('matricula')}}"><i class="fa fa-caret-right fa-sm"></i> Matrícula</a></li>
                        <li><a href="{{ route('Ttraslado') }}"><i class="fa fa-caret-right fa-sm"></i> Traslado</a></li>
                        {{-- <li><a href="{{ route('licencia') }}"><i class="fa fa-caret-right fa-sm"></i> Licencia de
                                Estudios</a></li> --}}
                        <li><a href="{{route('tramiteTitulacion')}}"><i class="fa fa-caret-right fa-sm"></i> Titulación</a></li>
                        <li><a href="{{route('partes')}}"><i class="fa fa-caret-right fa-sm"></i> Mesa de partes</a></li>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    El estudiante puede solicitar licencia de estudios una vez matriculado y por causas justificadas.
                    (motivos de índole personal o de salud). Los estudiantes podrán realizar una licencia de estudios hasta
                    por un periodo no mayor a cuatro (4) periodos académicos dentro de los plazos establecidos en el RI,
                    para ello la solicitud presentada debe de estar debidamente sustentada.<br><br>

                    Si el periodo de licencia finaliza sin que el estudiante se haya reincorporado o solicitado una
                    ampliación, siempre que esta no exceda de los cuatro (4) ciclos académicos, el estudiante es retirado de
                    la EESP.
                </p>
                <h3 class="mt-3">Requisitos</h3>
                <ul class="listasCuerpo">
                    <li>Solicitud dirigida al Director General de la EESP dentro del plazo establecido.</li>
                </ul>
                <h2 class="mt-4 linea-debajo">Proceso de licencia de estudios</h2>
                <p>
                    Para solicitar Licencia de estudios sigue los siguientes pasos:
                </p>
                <p class="mt-3"><strong>1. Matricularse en el periodo correspondiente</strong><br>
                    Para poder solicitar Licencia de estudios, primero debes estar matriculado en el periodo
                    actual.MATRICULA</p>
                <p class="mt-3"><strong>2. Presentar la solicitud de licencia</strong></p>
                <ul class="listasCuerpo">
                    <li>Descarga y completa el FUT.</li>
                    <li>Completa toda la información que se solicita en el Formulario de Licencia y adjunta el FUT que
                        llenaste previamente.</li>
                </ul>
                <div class="row justify-content-center align-items-center fichas mt-3">
                    <div class="col-lg-4 col-12">
                        <div class="card">
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Descargar FUT</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card">
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Formulario de Licencia</p>
                            </a>
                        </div>
                    </div>
                </div>
                <p class="mt-3"><strong>3. Esperar constancia del trámite</strong><br>
                    Espera en tu correo electrónico institucional la confirmación de tu trámite con lo siguiente:<br>
                    RD que indica el periodo de tu licencia de estudios.
                </p>
                <h2 class="mt-4 linea-debajo">Reincorporación </h2>
                <p>Para solicitar tu Reincorporación de estudios sigue los siguientes pasos: </p>
                <p><strong>1. Pagar por derecho de reincorporación</strong><br>
                    Deposita S/. 30.00 en SCOTIABANK por derecho de reincorporación de estudios.
                </p>
                <ul class="listasCuerpo">
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
                    <li>Por <strong><span class="text-danger">Aplicativo móvil WAYKI APP</span></strong> para clientes que
                        tienen una cuenta de ahorros y tarjeta en la Caja Cusco:
                        <ul>
                            <li>Revisar la guía de pago en <strong><span class="text-danger"><a
                                            href="https://www.cmac-cusco.com.pe/" rel="no-follow" title="Seguir enlace" target="_blank">
                                            www.cmac-cusco.com.pe</a></span></strong></li>
                        </ul>
                    </li>
                </ul>
                <p><strong>2. Escribir tus datos en el voucher</strong><br>
                    Escribe tus datos completos en el voucher de pago que te da el banco.
                </p>
                <ul class="listasCuerpo">
                    <li>Apellidos y nombres completos</li>
                    <li>Número de DNI</li>
                    <li>Programa de estudios (Educación Inicial / Educación Primaria EIB)</li>
                    <li>Concepto del pago (Reincorporación de estudios)</li>
                </ul>
                <p class="mt-3"><strong>3. Presentar la solicitud de reincorporación</strong> </p>
                <ul class="listasCuerpo">
                    <li>Descarga y completa el FUT.</li>
                    <li>Completa toda la información que se solicita en el Formulario de Licencia y adjunta el FUT que
                        llenaste previamente.</li>
                </ul>
                <div class="row justify-content-center align-items-center fichas mt-3">
                    <div class="col-lg-4 col-12">
                        <div class="card">
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Descargar FUT</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card">
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Formulario de Reincorporación</p>
                            </a>
                        </div>
                    </div>
                </div>
                <p class="mt-3"><strong>4. Matricularse en el periodo correspondiente</strong><br>
                    Después de solicitar tu reincorporación de estudios, debes matricularte en el periodo actual. <a
                        class="text-primary" href="{{ route('matricula') }}"> MATRICULA</a></a>
                </p>
            </div>
        </div>
    </div>
@endsection
