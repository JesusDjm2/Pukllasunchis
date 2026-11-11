@extends('layouts.home')

@section('metas')
    @php $titulo = 'Trámites Extraordinarios de Titulación'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Procedimientos, costos y formularios virtuales para trámites extraordinarios del proceso de investigación y titulación en la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Tramites Extraordinarios Titulacion, Cambio Asesor Pukllasunchis, Separacion Grupo, Integracion Grupo">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area admision bradcam_overlay pk-bradcam-titulacion">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <div class="pk-hero-badge">
                            <i class="fa fa-receipt"></i> Derechos Administrativos
                        </div>
                        <h1 class="pk-hero-title tituloAnimado">{{ $titulo }}</h1>
                        <p class="pk-hero-desc slideUp">
                            Tasas oficiales, conceptos y códigos para prórrogas, dictámenes, cambios de tema y solicitudes extemporáneas.
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
                    Durante el proceso de investigación y titulación pueden suscitarse imprevistos de orden metodológico, temporal o de conformación de equipos. La normativa institucional contempla los siguientes procedimientos extraordinarios para regularizar tu situación académica mediante formularios virtuales y abonos ordinarios en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>.
                </p>

                <!-- Tabla Resumen de Conceptos -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="mb-0" style="font-size: 21px;">
                        <i class="fa fa-table-list text-warning me-2"></i> Tarifario y Conceptos Ordinarios (<span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span>)
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
                                    <div class="text-muted" style="font-size: 13px;">Inscripción formal inicial o reaprobación del plan</div>
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
                                    <strong>Modificación o Cambio de Tema de Investigación</strong>
                                    <div class="text-muted" style="font-size: 13px;">Modificación del título, reajuste temático o metodológico</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">12</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 30.00</td>
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
                                    <strong>Separación del Grupo de Investigación (solo uno se va)</strong>
                                    <div class="text-muted" style="font-size: 13px;">Separación voluntaria o justificada de un integrante del equipo</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">43</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 30.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/13N25FvL4V7142Hq_gP0FjY37R9dD6xK09GvJmQ7a/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Integración al Grupo de Investigación</strong>
                                    <div class="text-muted" style="font-size: 13px;">Incorporación o adhesión de un nuevo integrante a un grupo de investigación</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">47</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 30.00</td>
                                <td class="text-end">
                                    <span class="badge bg-light text-muted border" style="font-size: 12px; font-weight: 500;">
                                        FUT Presencial
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Extensión de Plazo para Sustentación</strong>
                                    <div class="text-muted" style="font-size: 13px;">Prórroga excepcional para concluir el trabajo de campo, redacción o sustentación</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">11</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 50.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSciR5L299xLpI5OQ_ample/viewform"
                                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 13px; font-weight: 500;">
                                        Formulario <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Revisión de Trabajo de Investigación (Dictamen)</strong>
                                    <div class="text-muted" style="font-size: 13px;">Revisión formal del TI y designación de dictaminantes</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">15</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 100.00</td>
                                <td class="text-end">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdp5y0sD40jM4oXWq6aI_kP15y9example/viewform"
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
                            <tr>
                                <td>
                                    <strong>Dejar sin efecto la R.D. de PTI</strong>
                                    <div class="text-muted" style="font-size: 13px;">Dejar sin efecto la resolución directoral de plan de trabajo de investigación</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">14</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 30.00</td>
                                <td class="text-end">
                                    <span class="badge bg-light text-muted border" style="font-size: 12px; font-weight: 500;">
                                        FUT Presencial
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Rectificación de Nombre</strong>
                                    <div class="text-muted" style="font-size: 13px;">Corrección formal de datos personales o nombres en registros oficiales</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">24</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 100.00</td>
                                <td class="text-end">
                                    <span class="badge bg-light text-muted border" style="font-size: 12px; font-weight: 500;">
                                        FUT Presencial
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Duplicado de Diploma (Bachiller / Título)</strong>
                                    <div class="text-muted" style="font-size: 13px;">Por pérdida, deterioro o robo (anula el diploma anterior)</div>
                                </td>
                                <td class="text-center"><span class="pk-badge-code">23</span></td>
                                <td class="text-center font-weight-bold text-dark">S/ 800.00</td>
                                <td class="text-end">
                                    <span class="badge bg-light text-muted border" style="font-size: 12px; font-weight: 500;">
                                        FUT Presencial
                                    </span>
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

                    <!-- Card 2: Cambio de Tema -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSc7zZ9k9o1x8hKk6qGvVzU3wX8yL8q_example/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-pen-ruler"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 30.00 · Cód: 12</span>
                        </div>
                        <h4>Cambio de Tema de Investigación</h4>
                        <p>Modificación del título de investigación, objetivos o reajuste metodológico aprobado.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 3: Cambio de Asesor -->
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

                    <!-- Card 4: Separación de Grupo -->
                    <a href="https://docs.google.com/forms/d/13N25FvL4V7142Hq_gP0FjY37R9dD6xK09GvJmQ7a/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-users-slash"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 30.00 · Cód: 43</span>
                        </div>
                        <h4>Separación del Grupo de Investigación</h4>
                        <p>Solicitud de retiro de un integrante y continuidad individual del proceso de investigación.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 4b: Integración al Grupo -->
                    <div class="pk-card-tramite" style="cursor: default;">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 30.00 · Cód: 47</span>
                        </div>
                        <h4>Integración al Grupo de Investigación</h4>
                        <p>Incorporación o adhesión de un nuevo integrante a un grupo de investigación.</p>
                        <div class="pk-card-tramite__action text-muted">
                            <span>Vía FUT en Secretaría</span> <i class="fa fa-building-columns"></i>
                        </div>
                    </div>

                    <!-- Card 5: Extensión de Plazo -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSciR5L299xLpI5OQ_ample/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-clock-rotate-left"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 50.00 · Cód: 11</span>
                        </div>
                        <h4>Extensión de Plazo para Sustentación</h4>
                        <p>Prórroga extraordinaria fundamentada para la culminación y entrega del informe final.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 6: Revisión TI / Dictaminantes -->
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdp5y0sD40jM4oXWq6aI_kP15y9example/viewform" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-user-graduate"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 100.00 · Cód: 15</span>
                        </div>
                        <h4>Revisión de TI y Dictaminantes</h4>
                        <p>Trámite para designación del jurado dictaminante del Trabajo de Investigación.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 7: Asesoría Adicional -->
                    <a href="https://docs.google.com/forms/d/18yuH2gbrM5GVr8DHGvYeA7t5hrBIx9aXyIsbAcEdIAs/viewform?edit_requested=true" target="_blank" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-comments"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 20.00 · Cód: 52</span>
                        </div>
                        <h4>Asesoría Adicional (por sesión)</h4>
                        <p>Solicitar sesiones complementarias de acompañamiento con docente especialista por sesión.</p>
                        <div class="pk-card-tramite__action">
                            <span>Abrir Formulario</span> <i class="fa fa-external-link"></i>
                        </div>
                    </a>

                    <!-- Card 8: Dejar sin efecto RD PTI -->
                    <div class="pk-card-tramite" style="cursor: default;">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-ban"></i>
                            </div>
                            <span class="pk-card-tramite__badge">S/ 30.00 · Cód: 14</span>
                        </div>
                        <h4>Dejar sin efecto R.D. de PTI</h4>
                        <p>Dejar sin efecto la resolución directoral de aprobación de Plan de Trabajo.</p>
                        <div class="pk-card-tramite__action text-muted">
                            <span>Vía FUT en Secretaría</span> <i class="fa fa-building-columns"></i>
                        </div>
                    </div>
                </div>

                <!-- Caja Cusco Callout Card -->
                <div class="pk-caja-cusco-card">
                    <div class="pk-caja-cusco-header">
                        <div class="pk-caja-icon">
                            <i class="fa fa-university"></i>
                        </div>
                        <div>
                            <h3>Pagos para Trámites Extraordinarios en <span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span></h3>
                            <p>Realiza tus abonos exclusivamente mediante pago ordinario en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Los pagos ordinarios <strong>SOLO SE PAGAN EN VENTANILLAS</strong> de cualquier agencia a nivel nacional de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica obligatoriamente los 3 datos: <strong>Concepto exacto</strong>, <strong>Código numérico</strong> y <strong>Monto</strong> (ejemplo: Cambio de asesor: Cód. 13 - S/ 30.00, Separación de grupo: Cód. 43 - S/ 30.00, Integración a grupo: Cód. 47 - S/ 30.00, Dejar sin efecto RD: Cód. 14 - S/ 30.00, Extensión de plazo: Cód. 11 - S/ 50.00, etc.).</p>
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
                            <i class="fa fa-circle-info me-1"></i> Ver instructivo ilustrado de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>
                        </a>
                        <a href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}" target="_blank" class="pk-btn-caja-pdf">
                            <i class="fa fa-file-pdf"></i> Ver PDF de conceptos de pago
                        </a>
                    </div>
                </div>

                <!-- Navegación a la guía general -->
                <div class="pk-etapas-nav mt-5">
                    <a href="{{ route('tramiteTitulacion') }}" class="pk-etapas-nav-card pk-etapas-nav-next">
                        <div class="pk-etapas-nav-info">
                            <span class="pk-etapas-nav-badge">Guía Principal</span>
                            <h4 class="pk-etapas-nav-title">Guía Oficial del Proceso de Titulación</h4>
                            <p class="pk-etapas-nav-desc">Revisa el flujo completo de 3 fases reglamentarias para la obtención de tu Grado y Título Profesional.</p>
                        </div>
                        <span class="pk-etapas-nav-btn">
                            <span>Ver Guía General</span>
                            <i class="fa fa-arrow-right"></i>
                        </span>
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
