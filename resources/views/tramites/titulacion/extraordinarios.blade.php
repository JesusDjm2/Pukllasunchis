@extends('layouts.home')

@section('metas')
    @php $titulo = 'Trámites Extraordinarios de Titulación'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Procedimientos, costos y formularios virtuales para trámites extraordinarios del proceso de investigación y titulación en la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Tramites Extraordinarios Titulacion, Cambio Asesor Pukllasunchis, Disolucion Grupo">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area admision bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp">
                            <a href="{{ route('index') }}">Inicio /</a>
                            <a href="{{ route('tramiteTitulacion') }}">Trámites /</a>
                            {{ $titulo }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pt-5 pb-5 pk-titula-wrapper">
        <div class="row">
            @include('tramites.titulacion.tramites-lateral')

            <div class="col-lg-9">
                <!-- Título Oficial con Estilo del Sitio -->
                <div class="pk-badge-wrapper">
                    <span class="pk-badge-subtitulo">
                        <i class="fa fa-receipt"></i> Casos Especiales e Incidencias Académicas
                    </span>
                </div>
                <h2 class="linea-debajo">Trámites Extraordinarios de Titulación</h2>
                <p>
                    Durante el proceso de investigación y titulación pueden suscitarse imprevistos de orden metodológico, temporal o de conformación de equipos. La normativa institucional contempla los siguientes procedimientos extraordinarios para regularizar tu situación académica mediante formularios virtuales y abonos ordinarios en Caja Cusco.
                </p>

                <!-- Tabla Resumen de Conceptos -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="mb-0" style="font-size: 21px;">
                        <i class="fa fa-table-list text-warning me-2"></i> Tarifario y Conceptos Ordinarios (Caja Cusco)
                    </h3>
                    <small class="text-muted">Actualizado Periodo 2026</small>
                </div>

                <div class="pk-table-responsive">
                    <table class="pk-table-tramites">
                        <thead>
                            <tr>
                                <th>Trámite Extraordinario</th>
                                <th class="text-center">Código</th>
                                <th class="text-center">Costo</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Aprobación de PTI y Asesor</strong>
                                    <div class="text-muted" style="font-size: 13px;">Inscripción inicial o reaprobación tras cambio sustancial</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">10</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 50.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Cambios en la R.D. de PTI</strong>
                                    <div class="text-muted" style="font-size: 13px;">Modificación de título, reajuste temático o metodológico</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">12</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 20.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSc7zZ9k9o1x8hKk6qGvVzU3wX8yL8q_example/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Cambio de Asesor/a</strong>
                                    <div class="text-muted" style="font-size: 13px;">Reasignación justificada de docente asesor</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">13</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 30.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/1m7kWkj6C_Uoz252CHr_IsM1xkRhygy5DsCYKl_bH36E/edit"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Disolución de Grupo de Investigación</strong>
                                    <div class="text-muted" style="font-size: 13px;">Separación de integrantes por motivos justificados (costo por persona)</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">14</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 50.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/13N25FvL4V7142Hq_gP0FjY37R9dD6xK09GvJmQ7a/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Aprobación de TI y Asignación de Dictaminantes</strong>
                                    <div class="text-muted" style="font-size: 13px;">Revisión formal y nombramiento de jurado calificador</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">15</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 50.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdp5y0sD40jM4oXWq6aI_kP15y9example/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Ampliación de Plazo de TI</strong>
                                    <div class="text-muted" style="font-size: 13px;">Prórroga excepcional para concluir el trabajo de campo o redacción</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">16</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 20.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSciR5L299xLpI5OQ_ample/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Asesoría Adicional (por sesión)</strong>
                                    <div class="text-muted" style="font-size: 13px;">Acompañamiento especializado fuera de cronograma lectivo</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">52</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 20.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform?edit_requested=true"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tarjetas Detalladas para cada Trámite -->
                <h3 class="mt-4 mb-3" style="font-size: 21px;">
                    <i class="fa fa-folder-open text-warning me-2"></i> Acceso Directo a los Formularios Virtuales
                </h3>

                <div class="pk-cards-grid">
                    <!-- Card 1: Aprobación PTI -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-file-circle-check"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 50.00 · Cód: 10</span>
                        </div>
                        <h4>Aprobación de PTI y Asesor</h4>
                        <p>Inscripción formal del PTI y designación o ratificación de asesor mediante R.D.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 2: Cambios en R.D. PTI -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSc7zZ9k9o1x8hKk6qGvVzU3wX8yL8q_example/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-pen-ruler"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 20.00 · Cód: 12</span>
                        </div>
                        <h4>Cambios en R.D. de PTI</h4>
                        <p>Modificación del título de investigación, objetivos o reajuste metodológico aprobado.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 3: Disolución de Grupo -->
                    <a href="https://docs.google.com/forms/d/13N25FvL4V7142Hq_gP0FjY37R9dD6xK09GvJmQ7a/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-users-slash"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 50.00 · Cód: 14</span>
                        </div>
                        <h4>Disolución de Grupo de Investigación</h4>
                        <p>Solicitud de separación y continuidad individual del proceso de investigación.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 4: Aprobación TI y Dictaminantes -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdp5y0sD40jM4oXWq6aI_kP15y9example/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-user-graduate"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 50.00 · Cód: 15</span>
                        </div>
                        <h4>Aprobación de TI y Dictaminantes</h4>
                        <p>Trámite para designación del jurado dictaminante del Trabajo de Investigación.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 5: Ampliación de Plazo -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSciR5L299xLpI5OQ_ample/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-clock-rotate-left"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 20.00 · Cód: 16</span>
                        </div>
                        <h4>Ampliación de Plazo TI</h4>
                        <p>Prórroga extraordinaria fundamentada para la culminación y entrega del informe final.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 6: Asesoría Adicional -->
                    <a href="https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform?edit_requested=true" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-comments"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 20.00 · Cód: 52</span>
                        </div>
                        <h4>Asesoría Adicional</h4>
                        <p>Solicitar sesiones complementarias de acompañamiento con docente especialista por sesión.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 7: Cambio de Asesor -->
                    <a href="https://docs.google.com/forms/d/1m7kWkj6C_Uoz252CHr_IsM1xkRhygy5DsCYKl_bH36E/edit" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-user-pen"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 30.00 · Cód: 13</span>
                        </div>
                        <h4>Cambio de Asesor/a</h4>
                        <p>Llenar este formulario para solicitar la reasignación fundamentada de asesor/a.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>
                </div>

                <!-- Caja Cusco Callout Card -->
                <div class="pk-caja-cusco-card">
                    <div class="pk-caja-cusco-header">
                        <div class="pk-caja-icon">
                            <i class="fa fa-university"></i>
                        </div>
                        <div>
                            <h3>Pagos para Trámites Extraordinarios en Caja Cusco</h3>
                            <p>Realiza tus abonos exclusivamente mediante pago ordinario en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Los pagos ordinarios <strong>SOLO SE PAGAN EN VENTANILLAS</strong> de cualquier agencia a nivel nacional de Caja Cusco.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica el concepto específico (ejemplo: Cambio de asesor: Cód. 13, Disolución de grupo: Cód. 14, etc.).</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Dicta el número de <strong>DNI</strong> y los nombres y apellidos completos del estudiante.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p>Adjunta la boleta electrónica recibida en tu correo institucional dentro del formulario de solicitud virtual.</p>
                        </div>
                    </div>

                    <div class="pk-caja-footer">
                        <a href="javascript:void(0)" onclick="openPagoModal(event)" class="text-primary font-weight-bold" style="font-size: 14px;">
                            <i class="fa fa-circle-info me-1"></i> Ver instructivo ilustrado de Caja Cusco
                        </a>
                        <a href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}" target="_blank" class="pk-btn-caja-pdf">
                            <i class="fa fa-file-pdf"></i> Ver PDF de conceptos de pago
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
