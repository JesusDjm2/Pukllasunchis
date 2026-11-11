@extends('layouts.home')

@section('metas')
    @php $titulo = 'Plan de Trabajo de Investigación (PTI)'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Guía y requisitos oficiales para la elaboración y aprobación del Plan de Trabajo de Investigación (PTI) en la EESPP Pukllasunchis. Descarga de formatos y formulario de postulación.">
    <meta name="keywords" content="{{ $titulo }}, PTI Pukllasunchis, Investigacion Educacion Cusco, Formato PTI">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay">
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
                        <i class="fa fa-file-signature"></i> Fase 1 · Plan de Trabajo de Investigación
                    </span>
                </div>
                <h2 class="linea-debajo">Plan de Trabajo de Investigación (PTI)</h2>
                <p>
                    Propuesta formal de investigación que formulan los estudiantes sobre una problemática educativa identificada en la práctica profesional, priorizando contextos andinos y amazónicos. Es el primer paso indispensable para iniciar la titulación y obtener asesor/a institucional.
                </p>

                <!-- Hitos Paso a Paso (Primero Requisitos para la Aprobación del PTI) -->
                <h3 class="mt-4 linea-debajo">Requisitos para la Aprobación del PTI:</h3>
                <p>
                    Para solicitar la aprobación oficial y asignación de asesor, completa los siguientes 4 pasos:
                </p>

                <div class="pk-milestones-timeline">
                    <!-- Hito 1 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">1</div>
                        <div class="pk-milestone-header">
                            <h4>Pago por Derecho de Aprobación de PTI y Asesor</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 10</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Realiza el abono como <strong>pago ordinario</strong> en ventanilla de cualquier agencia de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> a nivel nacional a nombre de la <em>Asociación Pukllasunchis - EESPP</em>.
                            </p>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">Aprobación de PTI y Asesor</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">10</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 50.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu número de <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> El voucher de ventanilla que te entrega el banco <u>NO</u> se sube al formulario. En un lapso de <strong>2 días hábiles</strong> recibirás la <strong>Boleta Electrónica Oficial</strong> en tu correo institucional, la cual deberás adjuntar obligatoriamente en el formulario.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hito 2 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">2</div>
                        <div class="pk-milestone-header">
                            <h4>Elaboración y Formulación del Documento</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-time"><i class="fa fa-file-word"></i> Formato Word Oficial</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Descarga la plantilla oficial editable y redacta tu propuesta (individual o grupal) según la estructura institucional:
                            </p>
                            <ul>
                                <li>Planteamiento claro del problema detectado en la práctica profesional docente.</li>
                                <li>Objetivos, justificación y sustentación teórica contextualizada.</li>
                                <li>Metodología propuesta, cronograma y recursos requeridos.</li>
                            </ul>

                            <!-- Descargables Directos del Hito 2 -->
                            <div class="pk-milestone-actions mt-3 d-flex flex-wrap gap-2">
                                <a href="{{ asset('pdf/Plan-de-Trabajo-de-Investigacion-PTI.docx') }}" download="Plan-de-Trabajo-de-Investigacion-PTI.docx" class="pk-btn-download-action word">
                                    <i class="fa fa-file-word"></i> Descargar Formato PTI (.docx)
                                </a>
                                <a href="{{ asset('pdf/Plan-de-Trabajo-de-Investigacion-PTI.pdf') }}" target="_blank" class="pk-btn-download-action pdf">
                                    <i class="fa fa-file-pdf"></i> Ver Modelo Referencial (.pdf)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Hito 3 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">3</div>
                        <div class="pk-milestone-header">
                            <h4>Registro en el Formulario Virtual de Aprobación</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-time"><i class="fa fa-globe"></i> Mesa Virtual</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Con tu boleta electrónica y tu archivo finalizado, ingresa al formulario oficial con tu cuenta institucional:
                            </p>

                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-external-link me-1"></i> Abrir Formulario Oficial de PTI
                            </a>
                        </div>
                    </div>

                    <!-- Hito 4 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">4</div>
                        <div class="pk-milestone-header">
                            <h4>Emisión de R.D. y Asignación de Docente Asesor</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-time"><i class="fa fa-envelope-circle-check"></i> Notificación Oficial</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Secretaría Académica y la Coordinación de Investigación evaluarán la solicitud. Recibirás en tu correo institucional:
                            </p>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <div>
                                    <strong>Resolución Directoral (R.D.)</strong> de Aprobación de PTI con la designación formal de tu docente asesor/a para iniciar el acompañamiento.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Callout Especial: Carta de Aplicación en Intranet -->
                <div class="card border-0 shadow-sm p-4 mt-4 mb-4" style="background: #fdfaf6; border-radius: 14px; border: 1px solid #f0d5a8 !important;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span style="width: 42px; height: 42px; border-radius: 50%; background: #fff8eb; display: inline-flex; align-items: center; justify-content: center; color: #cd9244; font-size: 19px; border: 1px solid #f0d5a8;">
                            <i class="fa fa-envelope-open-text"></i>
                        </span>
                        <div>
                            <h4 class="mb-0" style="font-size: 17px; font-weight: 600; color: #1F1F1F;">Siguiente Hito: Solicitud de Carta de Aplicación</h4>
                            <small class="text-muted">Requerida para la I.E. donde realizarás tu trabajo de campo</small>
                        </div>
                    </div>
                    <p class="mb-3 mt-2" style="font-size: 14.5px; line-height: 24px; color: #4D4D4D;">
                        Con tu PTI aprobado, realiza el pago ordinario de <strong>S/ 10.00</strong> en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> indicando obligatoriamente los datos de ventanilla:
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark border p-2" style="font-size: 13.5px;"><i class="fa fa-tag text-primary me-1"></i> Concepto: <strong>Carta de presentación para INV</strong></span>
                        <span class="badge bg-danger text-white p-2" style="font-size: 13.5px;"><i class="fa fa-barcode me-1"></i> Código: <strong>31</strong></span>
                        <span class="badge bg-success text-white p-2" style="font-size: 13.5px;"><i class="fa fa-money-bill-wave me-1"></i> Monto: <strong>S/ 10.00</strong></span>
                    </div>
                    <p class="mb-3" style="font-size: 13.5px; color: #6b7280; line-height: 20px;">
                        Luego solicita tu carta desde la Intranet adjuntando tu boleta electrónica institucional (esperar 2 días hábiles tras el pago). Secretaría te responderá en un plazo de 3 días hábiles.
                    </p>
                    <div>
                        @auth
                            <a href="{{ route('mi-titulacion.carta') }}" class="btn text-white fw-bold px-4 py-2" style="background: linear-gradient(135deg, #d59d52, #cd9244); border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-file-text me-2"></i> Solicitar Carta en mi Intranet
                            </a>
                        @else
                            <button type="button" class="btn text-white fw-bold px-4 py-2" style="background: linear-gradient(135deg, #d59d52, #cd9244); border-radius: 8px; font-size: 14px;" data-bs-toggle="modal" data-bs-target="#modalCartaIntranet">
                                <i class="fa fa-sign-in me-2"></i> Iniciar sesión en Intranet para solicitar
                            </button>
                        @endauth
                    </div>
                </div>

                <!-- Deck de Formatos y Guías Oficiales (Al final) -->
                <h3 class="mt-5 linea-debajo">Documentos y Formatos Oficiales</h3>
                <p class="text-muted" style="font-size: 14.5px;">Descarga la plantilla editable del PTI y las guías institucionales metodológicas y de redacción:</p>
                <div class="pk-downloads-deck mb-5">
                    <!-- Formato PTI -->
                    <a href="{{ asset('pdf/Plan-de-Trabajo-de-Investigacion-PTI.docx') }}" download="Plan-de-Trabajo-de-Investigacion-PTI.docx" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Word (.docx)</span>
                        </div>
                        <h5>Formato Oficial PTI</h5>
                        <p>Plantilla oficial editable para formular la propuesta de investigación.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Formato
                        </span>
                    </a>

                    <!-- Guía de Investigación -->
                    <a href="{{ asset('pdf/Guia-de-investigacion-2.pdf') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon pdf">
                                <i class="fa fa-file-pdf"></i>
                            </div>
                            <span class="pk-download-card__tag">PDF Oficial</span>
                        </div>
                        <h5>Guía de Investigación</h5>
                        <p>Lineamientos metodológicos y criterios de evaluación de la escuela.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Guía
                        </span>
                    </a>

                    <!-- Guía de Redacción -->
                    <a href="{{ asset('pdf/Guia-de-Investigacion-2025.pdf') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon pdf">
                                <i class="fa fa-file-pdf"></i>
                            </div>
                            <span class="pk-download-card__tag">PDF 2025-2026</span>
                        </div>
                        <h5>Guía de Redacción Académica</h5>
                        <p>Normas APA 7ma edición, directivas de citado y estilo editorial.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Guía
                        </span>
                    </a>
                </div>

                <!-- Caja Cusco Callout Card -->
                <div class="pk-caja-cusco-card">
                    <div class="pk-caja-cusco-header">
                        <div class="pk-caja-icon">
                            <i class="fa fa-university"></i>
                        </div>
                        <div>
                            <h3>Pagos Ordinarios en <span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span></h3>
                            <p>Exclusivo para trámites académicos y derechos del proceso de titulación.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Los pagos ordinarios <strong>SOLO SE REALIZAN EN VENTANILLAS</strong> de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> a nivel nacional.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica los 3 datos obligatorios: <strong>Concepto: Aprobación de PTI y Asesor</strong> | <strong>Código: 10</strong> | <strong>Monto: S/ 50.00</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Brinda tu número de <strong>DNI</strong> y tus nombres completos para la emisión de la boleta electrónica.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p>Espera <strong>2 días hábiles</strong> para recibir tu comprobante electrónico en el correo institucional antes de llenar el formulario.</p>
                        </div>
                    </div>

                    <div class="pk-caja-footer">
                        <a href="javascript:void(0)" onclick="openPagoModal(event)" class="text-primary font-weight-bold" style="font-size: 14.5px;">
                            <i class="fa fa-circle-info me-1"></i> Ver instructivo ilustrado de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>
                        </a>
                        <a href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}" target="_blank" class="pk-btn-caja-pdf">
                            <i class="fa fa-file-pdf"></i> Ver PDF de conceptos de pago
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal informativo previo al login para la Carta de Aplicación --}}
    <div class="modal fade" id="modalCartaIntranet" tabindex="-1" aria-labelledby="modalCartaIntranetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,0.25); overflow: hidden; font-family: 'Poppins', sans-serif;">
                <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #364651, #243038); padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center gap-2 mb-0" id="modalCartaIntranetLabel" style="font-size: 1.15rem; font-weight: 600; color: #fff;">
                        <i class="fa fa-envelope-open-text" style="color: #cd9244;"></i> Solicitud de Carta de Aplicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span style="display: inline-flex; width: 64px; height: 64px; border-radius: 50%; background: #fff8eb; align-items: center; justify-content: center; color: #cd9244; font-size: 28px;">
                            <i class="fa fa-id-card"></i>
                        </span>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Ingreso a la Intranet Institucional</h5>
                    <p class="text-muted mb-3" style="font-size: 15px; line-height: 24px;">
                        El <strong>Formulario de Carta de Aplicación</strong> debe ser completado exclusivamente desde tu <strong>Intranet</strong> utilizando tu <strong>correo institucional (@pukllavirtual.edu.pe)</strong>.
                    </p>
                    <div class="alert alert-warning text-start mb-0 py-2 px-3" style="font-size: 14px; border-radius: 8px; border-left: 4px solid #cd9244; background-color: #fff9ed; color: #856404;">
                        <i class="fa fa-info-circle me-1"></i> Recuerda adjuntar tu <strong>boleta electrónica oficial</strong> de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> (Concepto: Carta de presentación para INV | Código: 31 | Monto: S/ 10.00), no el voucher impreso de ventanilla.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 14px;">
                        Cerrar
                    </button>
                    <a href="{{ route('mi-titulacion.carta') }}" class="btn text-white fw-bold px-4 py-2" style="background: linear-gradient(135deg, #d59d52, #cd9244); border-radius: 8px; font-size: 14px;">
                        <i class="fa fa-sign-in me-1"></i> Ir al Login de la Intranet
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
