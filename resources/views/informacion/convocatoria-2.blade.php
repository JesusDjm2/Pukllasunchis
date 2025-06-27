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
        <div class="row align-items-center">
            <div class="col-lg-5">
                <h3 class="mb-3">Profesional en Educación Intercultural</h3>
                <p class="text-muted">
                    Programa de mejoramiento de la calidad de vida de las familias, niñeces y adolescentes de comunidades de
                    Acomayo, Anta y Quispicanchi
                </p>
                <p>Se requiere profesional en Educación Intercultural con experiencia en:</p>
                <ul style="font-weight: 400; color:#6c757d!important; padding-left: 20px;">
                    <li style="list-style: disc;">Alfabetización escolar y comunitaria.</li>
                    <li style="list-style: disc;">Metodologías alternativas de alfabetización con diálogo de saberes
                        integeneracionales.</li>
                    <li style="list-style: disc;">Interlocución con comunidades rurales quechuas e inglesas.</li>
                    <li style="list-style: disc;">Alto niveld e compromiso con los resultados del programa.</li>
                </ul>
                <p>
                    <strong>NOTA:</strong> Dominio de nivel avanzado de los idiomas inglés y quechua(oral y escrito).
                </p>
                <p><strong>Primera Selección:</strong> Mes de abril, enviarsus hojas de vida no documentadas al correo <a
                        class="text-primary" href="mailto:edelgado@kallpa.org.pe">edelgado@kallpa.org.pe</a> hasta el 31 de marzo 2025.</p>

            </div>
            <div class="col-lg-7 text-center">
                <img src="{{ asset('img/convocatorias/Profesional-intercultural.jpg') }}" alt="Convocatoria Pukllasunchis 2"
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
