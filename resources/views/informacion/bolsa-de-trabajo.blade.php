@extends('layouts.home')
@section('metas')
    @php $titulo = 'Bolsa de Trabajo'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Tiene como finalidad que los estudiantes de los últimos ciclos del programa, entren en contacto directo y de manera progresiva, con diversas realidades educativas.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
@endsection
@section('contenido')
    <div class="bradcam_area biblioteca bradcam_overlay">
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
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <h2 class="linea-debajo">{{ $titulo }}</h2>

                <div class="bolsa-cta-bar d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div class="bolsa-cta-text mb-3 mb-md-0">
                        <h5 class="bolsa-cta-title mb-1">¿Tienes una oportunidad laboral para ofrecer?</h5>
                        <p class="mb-0 text-muted small">Publica tu convocatoria y llegará a nuestra comunidad
                            educativa. El formulario se abre aquí mismo, sin salir de esta página.</p>
                    </div>
                    <button type="button" class="boxed-btn3 bolsa-cta-btn" onclick="bolsaGlobalRegistroModalOpen();">
                        <i class="fa-solid fa-file-circle-plus mr-2" aria-hidden="true"></i>
                        Registrar nueva oferta
                    </button>
                </div>
                @include('partials.bolsa-ofertas-catalogo', ['bolsaRouteName' => 'bolsa'])
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

        .bolsa-cta-bar {
            background: linear-gradient(145deg, rgba(205, 146, 68, 0.09) 0%, rgba(205, 146, 68, 0.03) 100%);
            border: 1px solid rgba(205, 146, 68, 0.25);
            border-radius: 10px;
            padding: 1.5rem 1.75rem;
            gap: 1rem 1.5rem;
        }

        .bolsa-cta-title {
            color: #1f1f1f;
            font-weight: 700;
        }

        .bolsa-cta-btn {
            flex-shrink: 0;
            white-space: nowrap;
            padding: 12px 28px;
            font-size: 14px;
        }

        .bolsa-cta-btn:hover,
        .bolsa-cta-btn:focus {
            box-shadow: 0 6px 18px rgba(205, 146, 68, 0.28);
        }

        @media (max-width: 575.98px) {
            .bolsa-cta-bar {
                padding: 1.15rem 1.25rem;
            }

            .bolsa-cta-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endsection
