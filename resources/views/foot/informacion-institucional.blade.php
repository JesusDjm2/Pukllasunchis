@extends("layouts.home")
@section("metas")
    @php $titulo = "Información Institucional"; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="La admisión en la Escuela de Educación Superior Privada Pukllasunchis se realiza una vez al año, a través de un proceso de evaluación.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection
@section("contenido")
    <div class="bradcam_area tramites bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route("index") }}">Inicio /</a> {{ $titulo }}</p>
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
                            <a href="{{ ASSET('pdf/RM-387-2020-MINEDU-Licenciamiento-22092020 2.pdf') }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Licenciamiento-Vigente.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Licenciamiento Vigente</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/PAT-2026.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/proyecto.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Plan anual de Trabajo 2025</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/PCI-2026.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/proyecto-curricular.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Proyecto Curricular Institucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/Manual-de-Procesos-Institucionales.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/manual.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Manual de procesos Institucionales</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/PE-2025-2030-2026.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/proyecto-educativo.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Proyecto Educativo Institucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/Conformacion-del-cuerpo docente-Directivos-y-Docentes.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/plana-docente-eesp-pukllasunchis.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Nuestra Plana Docente</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/Investigaciones-y-Gastos.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Trabajo-de-investigacion-EESPukllasunchis.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Investigaciones</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/Relacion-de-Becarios-EESP-Pukllasunchis-2026.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/lista-becas.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Relación de Becados</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>                    
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{ asset("pdf/Estudiantes-matriculados-postulantes-ingresantes-egresados-2.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/relacion-ingresantes.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Relación de Matriculados, <br>
                                    Postulantes, Ingresantes y Egresados.</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalProgramasHorarios">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Programas-de-estudio.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Programas de Estudio y Horarios</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card text-center pt-4">
                            <a href="{{asset('pdf/Plan-de-financiamiento-institucional .pdf')}}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/inversiones-2.png") }}"
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
                            <a href="{{ asset("pdf/Reglamento-Institucional-2026.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Institucional.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Reglamento Institucional</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset("pdf/RM-387-2020-MINEDU-Resolucion-licenciamiento.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Aprobacion.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Resolución de Licenciamiento <br>RM 387-2020 MINEDU </p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset("pdf/RD-059-ACTUALIZACION-TUPA.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Bachillerato-Pukllasunchis.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">TUPA</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset("pdf/Reglamento-del-proceso-de-investigacion-y-titulacion-RD-2025.pdf") }}"
                                target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Reglamento.png") }}"
                                        alt="Ficha de Inscrición Pukllasunchis">
                                </div>
                                <p class="text-center">Reglamento del proceso de investigación y titulación</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset("pdf/CaratulaTesis.docx") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/caratula.png") }}" alt="">
                                </div>
                                <p class="text-center">Caratula Tesis</p>
                                <p class="text-center"><i class="fa fa-arrow-circle-right fa-lg"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6 text-center p-2">
                        <div class="card">
                            <a href="{{ asset("pdf/GuiaInvest-tesis.pdf") }}" target="_blank">
                                <div style="height: 100px">
                                    <img height="100%" src="{{ asset("img/min/Tesis-Pukllasunchis.png") }}"
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


    {{-- Modal: Programas de Estudio y Horarios --}}
    <div class="modal fade" id="modalProgramasHorarios" tabindex="-1" role="dialog"
        aria-labelledby="modalProgramasHorariosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="modalProgramasHorariosLabel">
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        Programas de Estudio y Horarios
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <span class="fid-modal-eyebrow">
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            Formación Inicial Docente
                        </span>
                        <h4 class="mt-2">Calendario de Admisión y Matrícula</h4>
                        <p class="text-muted">Horarios, fechas de admisión, matrícula e inicio de clases para nuestros
                            programas de FID.</p>
                    </div>

                    <div class="fid-modal-tablewrap">
                        <table class="fid-modal-table">
                            <thead>
                                <tr>
                                    <th scope="col">Programa de Estudios</th>
                                    <th scope="col">Horario</th>
                                    <th scope="col">Admisión</th>
                                    <th scope="col">Matrícula ordinaria</th>
                                    <th scope="col">Matrícula extemporánea</th>
                                    <th scope="col">Inicio de clases</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-label="Programa de Estudios">
                                        <a href="{{ route('inicial') }}" class="fid-modal-link" target="_blank">
                                            Educación Inicial
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                    <td data-label="Horario">Lunes a Viernes
                                        <span class="fid-modal-hora">4:00 p.m. - 9:00 p.m.</span>
                                    </td>
                                    <td data-label="Admisión">Primera semana de marzo</td>
                                    <td data-label="Matrícula ordinaria">Segunda semana de marzo</td>
                                    <td data-label="Matrícula extemporanea">Tercera semana de marzo</td>
                                    <td data-label="Inicio de clases">última semana de marzo</td>
                                </tr>
                                <tr>
                                    <td data-label="Programa de Estudios">
                                        <a href="{{ route('primariaEIB') }}" class="fid-modal-link" target="_blank">
                                            Educación Primaria Intercultural Bilingue
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                    <td data-label="Horario">Lunes a Viernes
                                        <span class="fid-modal-hora">4:00 p.m. - 9:00 p.m.</span>
                                    </td>
                                    <td data-label="Admisiín">Primera semana de marzo</td>
                                    <td data-label="Matrícula ordinaria">Segunda semana de marzo</td>
                                    <td data-label="Matrícula extemporánea">Tercera semana de marzo</td>
                                    <td data-label="Inicio de clases">Última semana de marzo</td>
                                </tr>
                                <tr class="fid-modal-row--closed">
                                    <td data-label="Programa de Estudios">
                                        <a href="{{ route('primaria') }}" class="fid-modal-link fid-modal-link--muted" target="_blank">
                                            Educación Primaria
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                    <td data-label="Estado" colspan="5">
                                        <span class="fid-modal-badge">
                                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                            No se aperturan metas para este programa
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                   
                    <div class="text-center mt-4">
                        <button type="button" class="fid-modal-btn-infografia" data-bs-toggle="modal"
                            data-bs-target="#modalProcesoMatriculaInstitucional">
                            <span class="fid-modal-btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <line x1="3" y1="9" x2="21" y2="9" />
                                    <line x1="9" y1="21" x2="9" y2="9" />
                                </svg>
                            </span>
                            <span class="fid-modal-btn-text">Ver proceso de matrícula</span>
                            <span class="fid-modal-btn-arrow">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalProcesoMatriculaInstitucional" tabindex="-1" role="dialog"
        aria-labelledby="modalProcesoMatriculaInstitucionalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="modalProcesoMatriculaInstitucionalLabel">
                        Proceso de Matrícula
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="{{ asset('img/Proceso-de-matricula-EESP-Pukllasunchis.png') }}"
                        alt="Proceso de Matrícula EESP Pukllasunchis" class="img-fluid w-100"
                        style="display:block; border-radius:0 0 .5rem .5rem;">
                </div>
            </div>
        </div>
    </div>

    <style>
        /* -- Modal principal -- */
        #modalProgramasHorarios .modal-content {
            border: none;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(13, 33, 55, .25);
        }
        #modalProgramasHorarios .modal-header {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff;
            border: none;
            padding: .9rem 1.4rem;
            align-items: center;
        }
        #modalProgramasHorarios .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: .01em;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        #modalProgramasHorarios .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: .75;
        }
        #modalProgramasHorarios .modal-header .btn-close:hover {
            opacity: 1;
        }
        #modalProgramasHorarios .modal-body {
            background: #f8f9fc;
            padding: 2rem 1.5rem;
        }

        /* -- Eyebrow badge -- */
        .fid-modal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #314e98;
            background: rgba(49, 84, 152, .08);
            border: 1px solid rgba(49, 84, 152, .22);
            padding: .35rem .85rem;
            border-radius: 999px;
            margin-bottom: .5rem;
        }

        /* -- Tabla -- */
        .fid-modal-tablewrap {
            border-radius: 16px;
            overflow: hidden auto;
            background: #fff;
            box-shadow: 0 18px 50px rgba(40, 35, 25, .12), 0 2px 8px rgba(40, 35, 25, .06);
            border: 1px solid rgba(0, 0, 0, .05);
        }
        .fid-modal-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }
        .fid-modal-table thead th {
            background: linear-gradient(90deg, #314e98 0%, #3d63bd 100%);
            color: #fff;
            text-align: left;
            padding: 1rem 1.1rem;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .fid-modal-table tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            transition: background .2s ease;
        }
        .fid-modal-table tbody tr:last-child {
            border-bottom: 0;
        }
        .fid-modal-table tbody tr:nth-child(even) {
            background: rgba(49, 84, 152, .025);
        }
        .fid-modal-table tbody tr:hover {
            background: rgba(205, 146, 68, .08);
        }
        .fid-modal-table td {
            padding: 1rem 1.1rem;
            font-size: .92rem;
            color: #333;
            vertical-align: middle;
        }

        /* -- Links en tabla -- */
        .fid-modal-link {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            color: #fff !important;
            background: linear-gradient(135deg, #cd9244, #b87a2a);
            font-weight: 600;
            font-size: .88rem;
            line-height: 1.3;
            text-decoration: none !important;
            padding: .5rem 1rem;
            border-radius: 999px;
            box-shadow: 0 4px 12px rgba(205, 146, 68, .32);
            transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
        }
        .fid-modal-link i {
            font-size: .76rem;
            transition: transform .22s ease;
        }
        .fid-modal-link:hover,
        .fid-modal-link:focus-visible {
            background: linear-gradient(135deg, #314e98, #25407d);
            box-shadow: 0 6px 18px rgba(49, 84, 152, .35);
            transform: translateX(3px);
            color: #fff !important;
        }
        .fid-modal-link:hover i {
            transform: translateX(3px);
        }
        .fid-modal-link--muted {
            background: rgba(49, 84, 152, .08);
            color: #314e98 !important;
            box-shadow: none;
            border: 1px solid rgba(49, 84, 152, .25);
        }
        .fid-modal-link--muted:hover {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff !important;
            box-shadow: 0 6px 18px rgba(49, 84, 152, .3);
        }

        .fid-modal-hora {
            display: block;
            color: #6a5a4a;
            font-size: .8rem;
            margin-top: .15rem;
        }

        .fid-modal-row--closed {
            background: rgba(0, 0, 0, .025) !important;
        }
        .fid-modal-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(178, 40, 40, .08);
            color: #a83232;
            border: 1px solid rgba(178, 40, 40, .25);
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
        }

        
        .fid-modal-btn-infografia {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            background: #fff;
            border: 1.5px solid rgba(49, 84, 152, .25);
            color: #314e98;
            font-size: .92rem;
            font-weight: 600;
            padding: .7rem 1.6rem .7rem 1.4rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all .25s ease;
            box-shadow: 0 4px 14px rgba(49, 84, 152, .08);
            font-family: inherit;
            line-height: 1.4;
        }
        .fid-modal-btn-infografia:hover,
        .fid-modal-btn-infografia:focus-visible {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 22px rgba(49, 84, 152, .3);
            transform: translateY(-2px);
        }
        .fid-modal-btn-infografia:active {
            transform: translateY(0);
        }
        .fid-modal-btn-icon {
            display: inline-flex;
            align-items: center;
            opacity: .75;
            transition: opacity .2s;
        }
        .fid-modal-btn-infografia:hover .fid-modal-btn-icon {
            opacity: 1;
        }
        .fid-modal-btn-arrow {
            display: inline-flex;
            align-items: center;
            transition: transform .25s ease;
        }
        .fid-modal-btn-infografia:hover .fid-modal-btn-arrow {
            transform: translateX(3px);
        }

        
        #modalProcesoMatriculaInstitucional .modal-content {
            border: none;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(13, 33, 55, .25);
        }
        #modalProcesoMatriculaInstitucional .modal-header {
            background: linear-gradient(135deg, #314e98, #25407d);
            color: #fff;
            border: none;
            padding: .9rem 1.4rem;
            align-items: center;
        }
        #modalProcesoMatriculaInstitucional .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: .01em;
        }
        #modalProcesoMatriculaInstitucional .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: .75;
        }
        #modalProcesoMatriculaInstitucional .modal-header .btn-close:hover {
            opacity: 1;
        }
        #modalProcesoMatriculaInstitucional .modal-body {
            background: #f8f9fc;
            padding: 0;
        }

        
        @media (max-width: 767.98px) {
            .fid-modal-tablewrap {
                overflow: visible;
                background: transparent;
                box-shadow: none;
                border: none;
            }
            .fid-modal-table {
                min-width: 0;
            }
            .fid-modal-table thead {
                display: none;
            }
            .fid-modal-table,
            .fid-modal-table tbody,
            .fid-modal-table tr,
            .fid-modal-table td {
                display: block;
                width: 100%;
            }
            .fid-modal-table tbody tr {
                margin-bottom: 1rem;
                border: 1px solid rgba(0, 0, 0, .06);
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 4px 16px rgba(40, 35, 25, .08);
            }
            .fid-modal-table td {
                display: flex;
                align-items: center;
                padding: .6rem .9rem;
                font-size: .85rem;
                border-bottom: 1px solid rgba(0, 0, 0, .04);
                gap: .5rem;
            }
            .fid-modal-table td:last-child {
                border-bottom: 0;
            }
            .fid-modal-table td::before {
                content: attr(data-label);
                flex-shrink: 0;
                width: 38%;
                font-weight: 700;
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: #314e98;
            }
            .fid-modal-table td:first-child {
                justify-content: flex-start;
                background: linear-gradient(135deg, rgba(49, 84, 152, .07), rgba(205, 146, 68, .05));
            }
            .fid-modal-table td:first-child::before {
                content: none;
            }
            .fid-modal-table td[colspan] {
                justify-content: center;
                text-align: center;
            }
            .fid-modal-table td[colspan]::before {
                content: none;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var subModal = document.getElementById('modalProcesoMatriculaInstitucional');
            if (subModal) {
                subModal.addEventListener('show.bs.modal', function() {
                    var parentEl = document.getElementById('modalProgramasHorarios');
                    var parentModal = bootstrap.Modal.getInstance(parentEl);
                    if (parentModal) parentModal.hide();
                });
            }
        });
    </script>
@endsection

