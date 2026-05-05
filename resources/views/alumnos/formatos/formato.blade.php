@extends('layouts.alumno')
@section('titulo', 'Guías de TI y Tesis')

@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => 'Formatos y guías',
        'subtitle' =>
            'Plantillas Word/PDF y videos tutoriales para trabajo de investigación y tesis, según las normas institucionales.',
    ])

    {{-- TI --}}
    <section class="mb-5">
        <h2 class="alumno-section-heading">
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                style="width: 2rem; height: 2rem; font-size: 0.95rem;"><i class="fas fa-flask"></i></span>
            Trabajo de investigación (TI)
        </h2>
        <div class="row">
            @php
                $tiItems = [
                    [
                        'href' => asset('pdf/FormatoTI.docx'),
                        'img' => asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png'),
                        'title' => 'Formato de TI',
                    ],
                    [
                        'href' => asset('pdf/CaratulaTI-2025.docx'),
                        'img' => asset('img/min/caratula.png'),
                        'title' => 'Carátula TI (FID)',
                    ],
                    [
                        'href' => asset('pdf/Guia-Trabajo-de-Investigacion.pdf'),
                        'img' => asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png'),
                        'title' => 'Guía de investigación',
                    ],
                    [
                        'href' => asset('pdf/Guia-de-Investigacion-2025.pdf'),
                        'img' => asset('img/min/Tesis-Pukllasunchis.png'),
                        'title' => 'Guía de redacción',
                    ],
                ];
            @endphp
            @foreach ($tiItems as $item)
                <div class="col-lg-3 col-6 mb-4">
                    <div class="card h-100 alumno-formato-card">
                        <div class="card-thumb">
                            <img src="{{ $item['img'] }}" alt="">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="card-title-text">{{ $item['title'] }}</div>
                            <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer"
                                class="btn btn-primary btn-sm mt-auto"><i class="fas fa-download mr-1"></i>
                                Descargar</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Tesis --}}
    <section class="mb-5">
        <h2 class="alumno-section-heading">
            <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center"
                style="width: 2rem; height: 2rem; font-size: 0.95rem;"><i class="fas fa-scroll"></i></span>
            Tesis
        </h2>
        <div class="row">
            @php
                $tesisItems = [
                    [
                        'href' => asset('pdf/FormatoTesis.docx'),
                        'img' => asset('img/min/Trabajo-de-investigacion-EESPukllasunchis.png'),
                        'title' => 'Formato de tesis',
                    ],
                    [
                        'href' => asset('pdf/CaratulaTesis-2025.docx'),
                        'img' => asset('img/min/caratula.png'),
                        'title' => 'Carátula tesis (FID)',
                    ],
                    [
                        'href' => asset('pdf/Guia-de-Elaboracion-de-Tesis.pdf'),
                        'img' => asset('img/min/Tesis-Pukllasunchis.png'),
                        'title' => 'Guía de elaboración de tesis',
                    ],
                    [
                        'href' => asset('pdf/Guia-de-Investigacion-2025.pdf'),
                        'img' => asset('img/min/Bachillerato-Pukllasunchis.png'),
                        'title' => 'Guía de redacción',
                    ],
                ];
            @endphp
            @foreach ($tesisItems as $item)
                <div class="col-lg-3 col-6 mb-4">
                    <div class="card h-100 alumno-formato-card">
                        <div class="card-thumb">
                            <img src="{{ $item['img'] }}" alt="">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="card-title-text">{{ $item['title'] }}</div>
                            <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer"
                                class="btn btn-primary btn-sm mt-auto"><i class="fas fa-download mr-1"></i>
                                Descargar</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Videos --}}
    <section class="mt-4">
        <h2 class="alumno-section-heading">
            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                style="width: 2rem; height: 2rem; font-size: 0.95rem;"><i class="fas fa-video"></i></span>
            Videos explicativos
        </h2>
        <p class="text-muted mb-4">Pasos prácticos para aplicar cada formato en tu documento.</p>

        <div class="row">
            <div class="col-md-6 mb-4 alumno-video-card">
                <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/FT-c2DfvNn8"
                        title="Guía para la Carátula de TI y Tesis | EESP Pukllasunchis"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="mt-2 text-center font-weight-bold small">Guía 1: Carátula</p>
            </div>

            <div class="col-md-6 mb-4 alumno-video-card">
                <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/HqDAIgnL538"
                        title="Guía 2: Formato General | EESP Pukllasunchis"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="mt-2 text-center font-weight-bold small">Guía 2: Formato general</p>
            </div>

            <div class="col-md-4 mb-4 alumno-video-card">
                <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/d07cUzQLQwE"
                        title="Guía 3: Formato Resumen | EESP Pukllasunchis"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="mt-2 text-center font-weight-bold small">Guía 3: Resumen</p>
            </div>
            <div class="col-md-4 mb-4 alumno-video-card">
                <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/sBopJwQav-o"
                        title="Guía 4: Títulos Subtítulos e Índice | EESP Pukllasunchis"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="mt-2 text-center font-weight-bold small">Guía 4: Títulos, subtítulos e índice</p>
            </div>
            <div class="col-md-4 mb-4 alumno-video-card">
                <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/58ODcJy84h8"
                        title="Guía 5: Referencias | EESP Pukllasunchis"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="mt-2 text-center font-weight-bold small">Guía 5: Referencias</p>
            </div>
        </div>
    </section>
@endsection
