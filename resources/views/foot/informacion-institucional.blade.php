@extends('layouts.home')
@section('metas')
    @php $titulo = 'Información Institucional'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection
@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay">
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
            <div class="col-lg-12">
                <h2 class="linea-debajo">Transparencia Institucional</h2>
                <div class="row justify-content-center align-items-center fichas mt-4">
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Licenciamiento-Vigente.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Licenciamiento Vigente</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset('pdf/Plan-Anual-de-trabajo-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/proyecto.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Plan anual de Trabajo 2025</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset('pdf/Proyecto-Curricular-Institucional-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/proyecto-curricular.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Proyecto Curricular Isntitucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset('pdf/Manual-de-Procesos-Institucionales.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/manual.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Manual de procesos Institucionales</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset('pdf/Proyecto-Educativo-Institucional-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/proyecto-educativo.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Proyecto Educativo Institucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset('pdf/Relacion-de-Becarios-EESP-Pukllasunchis.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/lista-becas.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Relación de Becados</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div> 
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/plana-docente-eesp-pukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Nuestra Plana Docente</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/relacion-ingresantes.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Relación de Ingresantes 2025-I</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Egresados-Pukllas.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Relación de Egresantes 2025-I</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Programas-de-estudio.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Programas de Estudio y Horarios</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/inversiones-2.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Inversiones, donaciones y recursos</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                </div>
                <h2 class="linea-debajo">{{ $titulo }}</h2>
                <div class="row justify-content-center align-items-center fichas mt-4">                    
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/MV-Reglamento Institucional-2025.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Institucional.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Reglamento Institucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/RM-387-2020-MINEDU-Resolucion-licenciamiento.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Aprobacion.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Resolución de Licenciamiento <br>RM 387-2020 MINEDU </p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/TUPA-EESPP-2025-08022024-jul.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Bachillerato-Pukllasunchis.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">TUPA</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/Reglamento-del-proceso-de-investigacion-y-titulacion-RD-2025.pdf') }}"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Reglamento.png') }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Reglamento del proceso de investigación y titulación</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/CaratulaTesis.docx') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/caratula.png') }}" alt="">
                                </div>
                                <p class="text-center">Caratula Tesis</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset('pdf/GuiaInvest-tesis.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset('img/min/Tesis-Pukllasunchis.png') }}"
                                        alt="">
                                </div>
                                <p class="text-center">Guía de <br>Investigación</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                </div><br>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
