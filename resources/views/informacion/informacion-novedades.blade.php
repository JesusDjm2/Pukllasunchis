@extends('layouts.home')
@section('metas')
    @php $titulo = 'Novedades Pukllasunchis'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Tiene como finalidad que los estudiantes de los últimos ciclos del programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area novedades bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Información / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Información</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('novedades') }}"><i class="fa fa-caret-right fa-sm"></i> Novedades</a></li>
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Artículos</a></li>
                        <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Proyectos
                                Académicos</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Innovaciones</a>
                        </li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Bolsa de Trabajo</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <div class="row justify-content-center mt-4 novedadesCard">
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#fit-013">
                                <p class="text-center mt-3">Entrega de documentos de Ingreso</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 10/09/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/Comunicado-Fit-013.webp') }}"
                                        alt="Matriculas Pukllasunchis 2024 II">
                                </div>
                            </a>
                        </div>
                    </div>
					<div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#matriculasSegundo">
                                <p class="text-center mt-3">Matriculas 2024 II</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 17/07/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/Anuncio-Puklla-08.webp') }}"
                                        alt="Matriculas Pukllasunchis 2024 II">
                                </div>
                            </a>
                        </div>
                    </div>                    
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#importanteObligatorio">
                                <p class="text-center mt-3">Importante y Obligatorio</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 09/05/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/importante-obligatorio.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#lugaresPago">
                                <p class="text-center mt-3">Lugares de Pago</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 03/05/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/lugares-pago.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#carnetMedio">
                                <p class="text-center mt-3">Carnet medio pasaje y Carnet de identificación</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 24/04/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/carnet-medio-pasj.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>				
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#matriculas">
                                <p class="text-center mt-3">Matriculas 2024</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 22/04/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/Matriculas-2024.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a href="{{asset('pdf/Comunicados/COMUNICADO-FID-004-CICLO-I.pdf')}}" target="_blank">
                                <p class="text-center mt-3">Protocolo de pago Ciclo I</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 27/03/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/Comunicados-ciclo-I.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>                    
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#protocoloPago">
                                <p class="text-center mt-3">Protocolo de pago por derecho de enseñanza</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 19/03/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/Comunicado-004-2_1.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#politicaPagos">
                                <p class="text-center mt-3">Politica de Pagos</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 08/03/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" style="height: 100%"
                                        src="{{ asset('img/novedades/politica-pagos.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#pasosBach">
                                <p class="text-center mt-3">Pasos para Bachillerato y Licenciatura</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 01/02/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/Pasos-Bachillerato.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#criterios">
                                <p class="text-center mt-3">Criterios de Evaluación para la entrevista personal</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 12/02/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/Comunicado-05-PPD.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#examen">
                                <p class="text-center mt-3">Les informamos sobre una modificación en la fecha del Examen de
                                    Admisión</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 01/02/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/examen-17-de-marzo.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#popup1">
                                <p class="text-center mt-3">Procesos de Admisión 2024 para Preseleccionados de beca 18</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 05/01/2024</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid" src="{{ asset('img/novedades/beca18-2024.webp') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>

                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#popup2">
                                <p class="text-center mt-3">Prospecto de Admisión</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 28/12/2023</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/admision-2024.webp') }}"
                                        alt="Prospecto Pukllasunchis">
                                </div>

                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#popup3">
                                <p class="text-center mt-3">Programa de profesionalización Docente 2024</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 28/12/2023</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/profesionalizacion-2024.webp') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>

                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#popup4">
                                <p class="text-center mt-3">Comunicado para egresados del IESP Ricardo Palma</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 10/12/2023</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/Comunicado-01.webp') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>

                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <a type="button" data-toggle="modal" data-target="#popup5">
                                <p class="text-center mt-3">Comunicado para interesados en trasladarse a la EESP
                                    PUKLLASUNCHIS</p>
                                <p class="text-center"><i class="fa fa-calendar"></i> 20/11/2023</p>
                                <div class="overflow">
                                    <img class="text-center img-fluid"
                                        src="{{ asset('img/novedades/Comunicado-02.webp') }}"
                                        alt="Protocolo Pukllasunchis">
                                </div>

                            </a>
                        </div>
                    </div>
                </div>
                <!------modales-------------->
                <!-- Modal -->
                <div class="modal" id="fit-013">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Comunicado-Fit-013.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
				<div class="modal" id="matriculasSegundo">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Anuncio-Puklla-08.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Politica de pagos --}}
                <div class="modal" id="politicaPagos">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/politica-pagos.webp') }}"
                                    alt="Polìtica de Pagos">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="lugaresPago">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/lugares-pago.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="importanteObligatorio">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/importante-obligatorio.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="matriculas">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Matriculas-2024.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="carnetMedio">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/carnet-medio-pasj.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="protocoloPago">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Comunicado-004-2_1.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="pasosBach">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Pasos-Bachillerato.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="criterios">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Comunicado-05-PPD.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="examen">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/examen-17-de-marzo.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="popup1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button> 
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/beca18-2024.webp') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <!--------modal 2------------>
                <div class="modal" id="popup2">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button> 
                                <img class="text-center img-fluid" height="100%"
                                    src="{{ asset('img/novedades/admision-2024.webp') }}" alt="Prospecto Pukllasunchis">
                                <a href="https://docs.google.com/forms/d/e/1FAIpQLScJk4pgmf0BVyALamtGGpsm21L2J8FADrYlU_qoYH-EdiMROA/viewform"
                                    class="btn btn-primary mt-3" target="_blank">¡Incríbete ya!</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--------modal 3------------>
                <div class="modal" id="popup3">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button> 
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/profesionalizacion-2024.webp') }}"
                                    alt="Protocolo Pukllasunchis">
                                <a href="{{ asset('pdf/PPD.pdf') }}" class="btn btn-primary mt-3">Descargar PDF del
                                    PPD</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--------modal 4------------>
                <div class="modal" id="popup4">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button> 
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Comunicado-01.webp') }}" alt="Protocolo Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
                <!--------modal 5------------>
                <div class="modal" id="popup5">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button> 
                                <img class="text-center img-fluid" width="100%"
                                    src="{{ asset('img/novedades/Comunicado-02.webp') }}" alt="Protocolo Pukllasunchis">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
@endsection
