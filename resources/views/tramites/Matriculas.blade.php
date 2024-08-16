@extends('layouts.home')
@section('metas')
    @php $titulo = 'Trámites Matrícula'; @endphp
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
                        {{-- <li><a href="{{route('matricula')}}"><i class="fa fa-caret-right fa-sm"></i> Matrícula</a></li> --}}
                        <li><a href="{{ route('Ttraslado') }}"><i class="fa fa-caret-right fa-sm"></i> Traslado</a></li>
                        <li><a href="{{ route('licencia') }}"><i class="fa fa-caret-right fa-sm"></i> Licencia de
                                Estudios</a></li>
                        <li><a href="{{ route('tramiteTitulacion') }}"><i class="fa fa-caret-right fa-sm"></i>
                                Titulación</a></li>
                        <li><a href="{{ route('partes') }}"><i class="fa fa-caret-right fa-sm"></i> Mesa de partes</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="row justify-content-center align-items-center fichas">
                    <div class="col-lg-6 col-6 text-center">
                        <div class="card">
                            <a href="{{ route('admin') }}" target="_blank" class="text-center">
                                <img width="200px"
                                    src="{{asset('img/min/Ficha-de-inscripcion-Pukllasunchis.png')}}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                                <p class="text-center">Ficha de Matrícula</p>
                            </a>
                        </div>
                    </div>
                </div>
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <h3>Requisitos de matrícula del primer (I) Ciclo: </h3>
                <ul class="listasCuerpo">
                    <li> Aprobar el examen de ingreso.</li>
                    <li> Llenar la ficha de matrícula establecida por la institución.</li>
                    <li>Presentar el Certificado de Estudios que acredite haber concluido la Educación Básica.</li>
                    <li>4 Fotos</li>
                </ul>
                <h3 class="mt-4">Requisitos matrícula del segundo (II) al séptimo (VII) ciclo </h3>
                <p>
                    Es requisito para la matrícula para los estudiantes del segundo (II) al séptimo (VII) ciclo académico:
                </p>
                <ul class="listasCuerpo">
                    <li>Haber aprobado como mínimo el setenta y cinco por ciento (75 %) de los créditos del ciclo inmediato
                        anterior.</li>
                    <li>No tener ninguna deuda con la ESCUELA (libros, materiales de lectura, pensiones).</li>
                </ul>
                <h3 class="mt-4">Matrícula para los estudiantes del VII al X ciclo académico: </h3>
                <ul class="listasCuerpo">
                    <li>Es requisito de matrícula haber aprobado el cien por ciento (100 %) de créditos del ciclo inmediato
                        anterior.</li>
                    <li>Tener aprobado el Plan de Trabajo de Investigación Pedagógica, para efectos de titulación. </li>
                    <li>No tener ninguna deuda con la ESCUELA (libros, materiales de lectura, pensiones).</li>
                </ul>
                <h4 class="mt-4">Reserva de matrícula</h4>
                <p>
                    A solicitud del estudiante, la EESP puede reservar su matrícula antes de iniciar el ciclo académico.
                    Para tal efecto, se requiere presentar la solicitud dirigida al Director General, debiendo resolver
                    mediante resolución antes de finalizado el proceso de matrícula.<br><br>

                    El estudiante de la EESP que no se matricule dentro de los veinte (20) días hábiles siguientes de
                    iniciado el proceso de matrícula y no realice la reserva de esta, pierde su condición de estudiante.
                    Para retomar sus estudios debe postular al PE y obtener una vacante, pudiendo solicitar la convalidación
                    de los estudios realizados.
                </p>
                <h2 class="mt-4 linea-debajo">Proceso de Matrícula</h2>
                <p>Para matricularte a la EESPP Pukllasunchis sigue los siguientes pasos: </p>
                <p><strong>1. Cancelar las deudas pendientes</strong><br>
                    Para consultar si tienes deudas pendientes, comunícate con el +51 992 676 676, envía un mensaje de
                    WhatsApp al
                    mismo número o escribe un correo electrónico a eespp@pukllasunchis.org.
                </p>
                <p><strong>2. Pagar por derecho de matrícula</strong><br>
                    El pago de <strong> Matrículas</strong> y <strong>Cuotas</strong> se realizará en la agencia de <strong
                        class="text-danger">Caja Cusco</strong> en las siguientes modalidades:
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
                {{-- <table class="table table-hover table-bordered mt-4">
                    <tbody>
                        <tr>
                            <th scope="row">ENTIDAD:</th>
                            <td>Scotiabank</td>
                        </tr>
                        <tr>
                            <th scope="row">TITULAR:</th>
                            <td>Asociación Pukllasunchis</td>
                        </tr>
                        <tr>
                            <th scope="row">RUC:</th>
                            <td>20116406218</td>
                        </tr>
                        <tr>
                            <th scope="row">LIBRETA DE AHORROS N°:</th>
                            <td>3660180989</td>
                        </tr>
                        <tr>
                            <th scope="row">CCI:</th>
                            <td>00936620366018098962</td>
                        </tr>
                    </tbody>
                </table> --}}
                {{-- <p><strong>3. Escribir tus datos en el voucher</strong><br>
                    Escribe tus datos completos en el voucher de pago que te da el banco.
                </p>
                <ul class="listasCuerpo">
                    <li>Apellidos y nombres completos</li>
                    <li>Número de DNI</li>
                    <li>Programa de estudios (Educación Inicial / Educación Primaria EIB)</li>
                    <li>Ciclo al que te matriculas</li>
                </ul>
                <p class="mt-2"><strong>4. Envía la constancia de pago</strong><br>
                    Escribe un correo electrónico a eespp@pukllasunchis.org y adjunta el voucher de pago por derecho de
                    matrícula (como se indica en el Paso 3).
                </p> --}}
                <p><strong>3. Completar la Ficha de Matrícula</strong><br>
                    Completa toda la información que se solicita en la Ficha de Matrícula.
                </p>
                <a href="{{ route('admin') }}" class="btn btn-primary" target="_blank">Ficha de matrícula</a>
                <p class="mt-3"><strong>4. Esperar constancia de matrícula</strong><br>
                    Espera en tu correo electrónico institucional la confirmación de tu matrícula con lo siguiente:
                <ul class="listasCuerpo">
                    <li>Constancia de matrícula</li>
                </ul>
                {{--  - Constancia de matrícula<br>
                    Para matricularse teniendo deudas anteriores, debes descargar y completar el<strong> Compromiso de
                        pago,</strong> luego enviarlo al correo: eespp@pukllasunchis.org.<br>
                    <a href="{{ asset('pdf/compromiso-pago.pdf') }}" class="btn btn-primary mt-2" target="_blank">Compromiso
                        de pago</a> --}}
                </p>
            </div>
        </div>
    </div>
@endsection
