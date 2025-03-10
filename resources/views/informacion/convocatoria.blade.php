@extends('layouts.home')
@section('metas')
    @php $titulo = 'Bolsa de Trabajo'; @endphp
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
        {{-- <div class="row">
            <img src="{{ asset('img/convocatorias/Convocatoria-unsaac.jpg') }}" alt="Convocatorias Pukllasunchis">
        </div> --}}
        <div class="row align-items-center">
            <!-- Imagen de la convocatoria -->
            

            <!-- Información de la convocatoria -->
            <div class="col-lg-7">
                <h3 class="mb-3">Asistente de Investigación</h3>
                <p class="text-muted">
                    La Escuela Pukllasunchis está en busca de un <strong>Asistente de Investigación</strong> a tiempo completo. Si te
                    apasiona el análisis, la exploración de datos y la generación de conocimiento, esta es tu oportunidad
                    para formar parte de un equipo comprometido con la educación y la innovación.<br><br>

                    <strong>¿A quién buscamos?</strong><br>
                </p>
                <ul style="font-weight: 400; color:#6c757d!important; padding-left: 20px;">
                    <li style="list-style: disc;">Profesionales con habilidades en investigación y análisis de datos.</li>
                    <li style="list-style: disc;">Capacidad de organización, redacción académica y pensamiento crítico.</li>
                    <li style="list-style: disc;">Ganas de contribuir al desarrollo educativo y social.</li>
                </ul>                
                <a href="mailto:u.investigacion.eesp@pukllavirtual.edu.pe" class="btn btn-primary btn-sm mt-4">Contactar</a>
            </div>
            <div class="col-lg-5 text-center">
                <img src="{{ asset('img/convocatorias/asistente-investigacion.jpg') }}" alt="Convocatoria UNSAAC"
                    class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <style>
        .boton-puklla {
            background: #cb8b39;
            color: #fff;
            border: 1px solid #cb8b39;
            padding: 5px 14px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
@endsection
