@extends('layouts.home')
@section('metas')
    @php $titulo = 'Trámites de Traslado'; @endphp
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
                    <h3 class="linea-debajo">Programas</h3>
                    <ul class="submenu2">
                        <li><a href="{{route('matricula')}}"><i class="fa fa-caret-right fa-sm"></i> Matrícula</a></li>
                        {{-- <li><a href="{{ route('Ttraslado') }}"><i class="fa fa-caret-right fa-sm"></i> Traslado</a></li> --}}
                        <li><a href="{{ route('licencia') }}"><i class="fa fa-caret-right fa-sm"></i> Licencia de
                                Estudios</a></li>
                        <li><a href="{{route('tramiteTitulacion')}}"><i class="fa fa-caret-right fa-sm"></i> Titulación</a></li>
                        <li><a href="{{route('partes')}}"><i class="fa fa-caret-right fa-sm"></i> Mesa de partes</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <h3>Requisitos de matrícula del primer (I) Ciclo: </h3>
                <p>
                    Para solicitar el traslado, el estudiante debe haber concluido y aprobado por lo menos el primer ciclo
                    académico en la EESP o institución de educación superior de procedencia y debe existir vacante
                    disponible en el PE de destino. La solicitud de traslado debe realizarse antes de culminado el proceso
                    de matrícula correspondiente
                </p>
                <h3 class="mt-4">Requisitos</h3>
                <ul class="listasCuerpo">
                    <li>Solicitud de traslado dirigida al Director General de la EESP (presentado antes de culminado el
                        proceso de matrícula correspondiente).</li>
                    <li>Certificado de estudios del IES o EES de procedencia, en caso del traslado externo.</li>
                </ul>
                <h3 class="mt-4">Convalidación</h3>
                <p>
                    La convalidación podrá otorgarse cuando el curso a convalidar tenga una similitud en contenido, en por
                    lo menos el 70 %, al curso de destino.<br><br>

                    La convalidación es aplicable a los procesos de traslado y reincorporación y otros procesos previstos en
                    el RI, y procede siempre que el tiempo de interrupción de los estudios no exceda de los cinco (5)
                    años.<br><br>

                    En caso de reingreso a la EESP a través del proceso de admisión, el ingresante puede solicitar la
                    convalidación de estudios previo a la finalización del proceso de matrícula y siempre que cumpla con lo
                    establecido en el párrafo precedente.
                </p>
                <h2 class="mt-4 linea-debajo">Proceso de traslado y convalidación </h2>
                <p>Si eres estudiante de un instituto pedagógico, universidad o tienes estudios superiores en otra
                    institución es importante que sepas que no procede el traslado, sino un proceso de
                    CONVALIDACIÓN.<br><br>

                    Para ello, debes seguir los siguientes pasos:</p>
                <p><strong>1. Postular e ingresar a la EESP Pukllasunchis</strong><br>
                    Debes seguir el proceso regular de <a href="{{ route('traslado') }}" class="text-primary"
                        target="_blank">ADMISIÓN.</a>
                </p>
                <p><strong>2. Solicitar a la EESP la convalidación de cursos</strong> </p>

                <ul class="listasCuerpo">
                    <li>Descarga y completa el FUT.</li>
                    <li>Solicitud de convalidación dirigida al Director General de la EESP.</li>
                    <li>Adjunta el Certificado de estudios original que acredite la aprobación de los cursos por convalidar.
                    </li>
                    <li>Adjunta los Sílabos originales de los cursos que se desea convalidar.</li>
                    <li>Adjunta Ficha de seguimiento o Boleta de notas de los ciclos cursados.</li>
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
                </div>
                <p class="generic-blockquote mt-4">
                    <strong> OJO:</strong> Este trámite solo se realiza de forma presencial, para sacar una cita comunícate
                    con el 984529158.
                </p>
            </div>
        </div>
    </div>
@endsection
