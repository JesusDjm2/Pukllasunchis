@extends('layouts.home')
@section('metas')
    @php $titulo = 'Resultados de Admisión'; @endphp
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
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Admisión / {{ $titulo }}</p>
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
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Ingreso Ordinario</a></li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Por Exoneración</a></li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Traslado Externo</a></li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Resultados</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }} 2024 - I</h2>
                <p class="text-justify">
                    Resultados del proceso de admisión 2024 - I
                </p>
            </div>
        </div>
    </div>
@endsection
