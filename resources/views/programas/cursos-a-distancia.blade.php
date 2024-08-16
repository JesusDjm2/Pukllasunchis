@extends('layouts.home')
@section('metas')
    @php $titulo = 'Cursos a distancia'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Esta especialidad se centra en jóvenes interesados en el cuidado y desarrollo de niños pequeños, fomentando entornos de juego y creatividad. Busca cultivar habilidades sociales y sensibilidad, promoviendo la convivencia y la conexión con la naturaleza.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area inicial bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-3">
                <h3 class="linea-debajo">Programas</h3>
                <ul class="submenu2">
                    <li><a><i class="fa fa-caret-right fa-sm"></i> {{ $titulo }}</a></li>
                    <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Duración</a></li>
                    <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Requisitos</a></li>
                    <li><a href=""><i class="fa fa-caret-right fa-sm"></i> Contenidos</a></li>
                </ul>
            </div>
            <div class="col-lg-9">
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <p>
                    En virtud de nuestros fines y respondiendo a nuestra facultad para aportar a la educación a través del
                    desarrollo de programas de profesionalización, ofrecemos el Programa de Formación Continua, destinado a
                    diversos profesionales considerando para ello la Ley 30512, su reglamentación y las orientaciones
                    expedidas por el Ministerio a través del Oficio N° 00148-2022.<br><br>

                    El curso “Educación y Currículo Intercultural” es un curso que es parte de un programa de formación
                    continua que busca desarrollar competencias para una práctica pedagógica intercultural y situada en las
                    características sociales y culturales de la región Constituye un espacio de formación para una reflexión
                    conectada con la vida y la cultura y con las necesidades de nuestro tiempo. Ofrece, a los profesionales
                    e interesados de distintas áreas, la oportunidad para adquirir un conocimiento reflexivo sobre las bases
                    sobre las que se funda una pedagogía intercultural Desde esta perspectiva, este curso propone repensar
                    la educación de la región y el país e imaginar escenarios educativos de convivencia donde la
                    cooperación, el inter aprendizaje, el diálogo intercultural y el aprendizaje vivencial sean pilares para
                    desarrollar metodologías y didácticas interculturales que respondan a las realidades socioculturales de
                    los niños y niñas de diversos contextos.
                </p>
            </div>
        </div>
    </div>
@endsection
