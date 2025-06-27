@extends('layouts.home')
@section('metas')
    <title>Luis Pescetti - El Niño Caníbal</title>
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
                        <h3 class="tituloAnimado">Luis Pescetti - El Niño Caníbal</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> El Niño Caníbal</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="welcome_docmed_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 pt-2">
                    <iframe width="1007" height="737" src="https://www.youtube.com/embed/qTlXam7diNI"
                        title="Luis Pescetti - El Niño Caníbal (Virulo)" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
