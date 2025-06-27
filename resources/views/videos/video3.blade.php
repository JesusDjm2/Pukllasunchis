@extends('layouts.home')
@section('metas')
    <title>Cantoalegre - El negrito aquel</title>
    <meta name="description" content="Conoce más sobre nosotros, la Escuela de Educación Superior Pedagógica Pukllasunchis">
    <meta name="keywords" content="EESP Pukllasunchis, Pukllasunchis, Educación, Intercultural, EIB, Inicial, Primaria">
@endsection
@section('contenido')
    <!-- bradcam_area_start  -->
    <div class="bradcam_area tramites bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">Luis Pescetti - Cantoalegre - El negrito aquel</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Cantoalegre - El negrito aquel</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="welcome_docmed_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 pt-2">
                    <iframe width="1310" height="737" src="https://www.youtube.com/embed/UXuLsRmS8Bk"
                        title="Cantoalegre - El negrito aquel (Canciones para leer)" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
