@extends('layouts.home')
@section('metas')
    @php $titulo = 'Subvenciones y Becas'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Tiene como finalidad que los estudiantes de los últimos ciclos del programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area lineas bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Líneas / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Líneas</h3>
                    <ul class="submenu2">
                        <li><a href="{{ route('tutoria') }}"><i class="fa fa-caret-right fa-sm"></i> Tutoria</a></li>
                        <li><a href="{{ route('bienestar') }}"><i class="fa fa-caret-right fa-sm"></i> Bienestar y
                                Empleabilidad</a></li>
                        <li><a href="{{ route('investigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Investigación</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Práctica pre
                                profesional</a></li>
                        {{-- <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Subvenciones y Becas</a></li> --}}
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                   Estamos actualizando esta información, en breve estará disponible...
                </p>
            </div>
        </div>
    </div>
@endsection
