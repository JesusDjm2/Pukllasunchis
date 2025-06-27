@extends('layouts.home')
@section('metas')
    <title>La Flor de la Canela</title>
    <meta name="description" content="Conoce más sobre nosotros, la Escuela de Educación Superior Pedagógica Pukllasunchis">
    <meta name="keywords" content="EESP Pukllasunchis, Pukllasunchis, Educación, Intercultural, EIB, Inicial, Primaria">
@endsection
@section('contenido')
    <style>
        .video-responsive {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .video-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
    <!-- bradcam_area_start  -->
    <div class="bradcam_area tramites bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">La Flor de la Canela</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> La Flor de la Canela</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="welcome_docmed_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 pt-2">
                    <iframe id="player" width="100%" src="https://www.youtube.com/embed/mWiMJd0aTmY"
                        title="La Flor de la Canela" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
