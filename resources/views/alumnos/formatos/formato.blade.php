@extends('layouts.alumno')

@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 text-primary font-weight-bold">Formatos y Guías:</h3>
        </div>

        {{-- Sección: Trabajos de Investigación --}}
        <div class="mb-5">
            <h4 class="text-dark font-weight-bold mb-3">
                <i class="fas fa-flask text-primary mr-2"></i> Formatos de Trabajo de Investigación (TI)
            </h4>

            <div class="row">
                <div class="col-lg-3 col-6 text-center mb-3">
                    <div class="card">
                        <a href="{{ asset('pdf/FormatoTI.docx') }}" target="_blank">
                            <div style="height: 100px">
                               <img height="100%"
                                    src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                            <p class="text-center">Formato de TI</p>
                        </a>
                        <a href="{{ asset('pdf/FormatoTI.docx') }}" class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 col-6 text-center mb-3">
                    <div class="card">
                        <a href="{{ asset('pdf/CaratulaTI-2025.docx') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                            </div>
                            <p class="text-center">Caratula TI (FID)</p>
                        </a>
                        <a href="{{ asset('pdf/CaratulaTI-2025.docx') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 col-6 text-center mb-3">
                    <div class="card">
                        <a href="{{ asset('pdf/Guia-Trabajo-de-Investigacion.pdf') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%"
                                    src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                    alt="">
                            </div>
                            <p class="text-center">Guía de Investigación</p>
                        </a>
                        <a href="{{ asset('pdf/Guia-Trabajo-de-Investigacion.pdf') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 col-6 text-center mb-3">
                    <div class="card">
                        <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}" alt="">
                            </div>
                            <p class="text-center">Guía de Redacción</p>
                        </a>
                        <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>              
            </div>
        </div>

        {{-- Sección: Trabajos de Tesis --}}
        <div class="mb-5">
            <h4 class="text-dark font-weight-bold mb-3">
                <i class="fas fa-scroll text-danger mr-2"></i> Formatos Tesis
            </h4>

            <div class="row">
                <div class="col-lg-3 mb-3 col-6 text-center">
                    <div class="card">
                        <a href="{{ asset('pdf/FormatoTesis.docx') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%"
                                    src="{{ asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png') }}"
                                    alt="Ficha de Inscrición Pukllasunchis">
                            </div>
                            <p class="text-center">Formato de Tesis</p>
                        </a>
                        <a href="{{ asset('pdf/FormatoTesis.docx') }}" class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 mb-3 col-6 text-center">
                    <div class="card">
                        <a href="{{ asset('pdf/CaratulaTesis-2025.docx') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                            </div>
                            <p class="text-center">Caratula Tesis (FID)</p>
                        </a>
                        <a href="{{ asset('pdf/CaratulaTesis-2025.docx') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 mb-3 col-6 text-center">
                    <div class="card">
                        <a href="{{ asset('pdf/Guia-de-Elaboracion-de-Tesis.pdf') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}" alt="">
                            </div>
                            <p class="text-center">Guía de Elaboración de Tesis</p>
                        </a>
                        <a href="{{ asset('pdf/Guia-de-Elaboracion-de-Tesis.pdf') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
                <div class="col-lg-3 mb-3 col-6 text-center">
                    <div class="card">
                        <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}" target="_blank">
                            <div style="height: 100px">
                                <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                    alt="">
                            </div>
                            <p class="text-center">Guía de Redacción</p>
                        </a>
                        <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}"
                            class="btn btn-primary btn-sm mt-2">Descargar</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección de Videos --}}
        <div class="mt-5">
            <h4 class="text-dark font-weight-bold mb-3">
                <i class="fas fa-video text-secondary mr-2"></i> Videos explicativos
            </h4>
            <p class="text-muted">Aquí encontrarás videos paso a paso que te enseñarán cómo aplicar correctamente cada
                formato en tu trabajo.</p>

            <div class="row">
                {{-- Video ejemplo 1 --}}
                <div class="col-md-6 mb-4">
                    <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/FT-c2DfvNn8"
                            title="Guía para la Carátula de TI y Tesis | EESP Pukllasunchis" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-center font-weight-bold">Guóa 1: Carátula</p>
                </div>

                {{-- Video ejemplo 2 --}}
                <div class="col-md-6 mb-4">
                    <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/HqDAIgnL538"
                            title="Guía 2:  Formato General | EESP Pukllasunchis" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-center font-weight-bold">Guía 2: Formato General</p>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/d07cUzQLQwE"
                            title="Guía 3: Formato Resumen | EESP Pukllasunchis" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-center font-weight-bold">Guía 3: Resumen</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/sBopJwQav-o"
                            title="Guía 4: Títulos Subtítulos e Índice | EESP Pukllasunchis" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-center font-weight-bold">Guía 4: Títulos, Subtitulos e Índice</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/58ODcJy84h8"
                            title="Guía 5: Referencias | EESP Pukllasunchis" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-center font-weight-bold">Guía 5: Referencias</p>
                </div>
            </div>
        </div>
    </div>
@endsection
