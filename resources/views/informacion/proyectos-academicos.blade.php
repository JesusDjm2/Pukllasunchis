@extends('layouts.home')
@section('metas')
    @php $titulo = 'Proyectos Académicos'; @endphp
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
                        <li><a href="{{ route('proyectos') }}"><i class="fa fa-caret-right fa-sm"></i> Proyectos Académicos</a>
                        </li>
                        <li><a href="{{ route('preProfesional') }}"><i class="fa fa-caret-right fa-sm"></i> Innovaciones</a></li>
                        <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Bolsa de Trabajo</a></li>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
@endsection
