@extends('layouts.home')
@section('metas')
    @php $titulo = 'Admisión por Exoneración'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area admision bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio / </a>Admisión / {{ $titulo }}</p>
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
                        <li><a href="{{route('ordinario')}}"><i class="fa fa-caret-right fa-sm"></i> Ingreso Ordinario</a></li>
                        <li><a href="{{route('exoneracion')}}"><i class="fa fa-caret-right fa-sm"></i> Por Exoneración</a></li>
                        <li><a href="{{route('traslado')}}"><i class="fa fa-caret-right fa-sm"></i> Traslado Externo</a></li>
                        <li><a href="{{route('resultados')}}"><i class="fa fa-caret-right fa-sm"></i> Resultados</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p class="text-justify">
                    {{-- Bajo esta modalidad podrás ser exonerado de la prueba escrita.<br> --}}
                    Los postulantes bajo la modalidad por exoneración, adjuntarán a su expediente alguno de los siguientes
                    documentos:
                </p>
                <h3><i class="fa fa-caret-right" style="color:#cd9244"></i> Primer y segundo puesto de Educación Básica:
                </h3>
                <p>
                    Certificado de estudios en cuyo anverso deberá figurar el cuadro de méritos visado por la UGEL
                    correspondiente.
                </p>
                <h3 class="mt-3 mb-2"><i class="fa fa-caret-right" style="color:#cd9244"></i> Deportista calificado:</h3>
                <p>
                    Constancia emitida por la Dirección Nacional de Deporte de Afiliados del Instituto Peruano del Deporte,
                    que lo acredite como miembro de la federación nacional y/o constancia de haber participado en certámenes
                    nacionales e internacionales..
                </p>
                <h3 class="mt-3 mb-2"><i class="fa fa-caret-right" style="color:#cd9244"></i> Beneficiarios del Programa
                    Integral de Reparaciones en Educación:</h3>
                <p>
                    Certificado de acreditación de la inscripción en el registro único de víctimas.
                </p>
                <h3 class="mt-3 mb-2"><i class="fa fa-caret-right" style="color:#cd9244"></i> Artista calificado, Danza:
                </h3>
                <p>
                    Constancia de representación nacional o regional emitida por la Dirección General de Industrias
                    Culturales y Artes del Ministerio de Cultura, Escuelas Nacionales o Regionales Superior de Arte.
                </p>

                <h2 class="mt-2 linea-debajo">Requisitos de inscripción:</h2>
                <p>
                    Para inscribirte y tener derecho al proceso de evaluación (en marzo) debes:
                </p>
                <ul class="listasCuerpo">
                    <li>
                        Hacer el pago de S/150 soles por derecho a la inscripción en las oficinas de Scotiabank.
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
                <h2 class="linea-debajo">Documentos para el proceso de admisión: </h2>
                <div class="row mt-4 mb-4 justify-content-center align-items-center fichas">
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScJk4pgmf0BVyALamtGGpsm21L2J8FADrYlU_qoYH-EdiMROA/viewform?pli=1"
                                target="_blank">
                                <img width="80%" src="{{ asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p>Ficha de inscripción</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 text-center">
                        <div class="card">
                            <a href="{{ asset('pdf/Prospecto-de-Admision-2024.pdf') }}" target="_blank">
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
                                        alt="Protocolo Pukllsunchis">
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
                                    alt="Secuencia de Inscripción">
                                <p class="text-center">Secuencia de Admisión</p>
                            </a>
                        </div>
                    </div>
                </div>

                <h2 class="mt-2 linea-debajo">Requisitos de inscripción:</h2>
                <ul class="listasCuerpo mb-4">
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
