@extends('layouts.home')
@section('metas')
    @php $titulo = 'Mesa de partes'; @endphp
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
                        <li><a href="{{ route('licencia') }}"><i class="fa fa-caret-right fa-sm"></i> Licencia de
                                Estudios</a></li>
                        <li><a href="{{route('tramiteTitulacion')}}"><i class="fa fa-caret-right fa-sm"></i> Titulación</a></li>
                        {{-- <li><a href="{{route('partes')}}"><i class="fa fa-caret-right fa-sm"></i> Mesa de partes</a></li> --}}
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">¿Cómo realizar un trámite en la mesa de partes virutal?</h2>
                
                <ul class="listasCuerpo">
                    <li>Descarga el formulario Único de Trámite (FUT) </li>
                    <li>Completa todos los datos solicitados en el FUT.</li>
                    <li>Adjunta los documentos que ayuden a sustentar tu solicitud en formato WORD o PDF.</li>
                    <li>Envia por correo electrónico al siguiente correo: <a class="text-primary" href="mailto:eespp@pukllasunchis.org">eespp@pukllasunchis.org</a></li>
                    <li>No olvides escribir tus datos personales, nombres completos y DNI en el contenido del correo.</li>
                    <li>Podrás registrar tu trámite virtual de lunes a viernes en el horario: <strong>8:00am - 4:00pm</strong></li>
                    <li>Recuerda que todos los trámites tienen un plazo de atención mínima de 3 días hábiles.</li>
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
                            <a href="{{ asset('pdf/Flujograma-de-procesos.pdf') }}" target="_blank" class="text-center">
                                <img width="80%" src="{{ asset('img/min/flujigrama.webp') }}"
                                    alt="Flujograma de Porcesos Pukllasunchis">
                                <p class="text-center">Flujograma de Procesos</p>
                            </a>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
@endsection
