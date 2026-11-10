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
                        <i class="fa fa-file-signature"></i> Fase 1 · VIII Ciclo de Formación Docente
                    </span>
                </div>
                <h2 class="linea-debajo">Plan de Trabajo de Investigación (PTI)</h2>
                <p>
                    Es la propuesta de investigación que realizan los estudiantes al finalizar el VIII ciclo, cuyo planteamiento debe contribuir a la solución de un problema nacional, regional o local en el ámbito educativo. Este problema puede ser identificado durante el proceso de la práctica profesional y debe favorecer los procesos de educación donde se considere el respeto a la diversidad y el fomento de la identidad y la cultura, de preferencia desde los contextos andinos y amazónicos. Debe cumplir con la estructura solicitada por la escuela en su guía de investigación.
                </p>

                <!-- Deck de Formatos y Guías Oficiales -->
                <h3 class="mt-4">Documentos y Formatos Oficiales</h3>
                <div class="pk-downloads-deck">
                    <!-- Formato PTI -->
                    <a href="{{ asset('pdf/Plan-de-Trabajo-de-Investigación (PTI).docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Word (.docx)</span>
                        </div>
                        <h5>Formato Oficial PTI</h5>
                        <p>Estructura oficial para la formulación de la propuesta de investigación.</p>
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
                            <span class="pk-download-card__tag">PDF Institucional</span>
                        </div>
                        <h5>Guía de Investigación</h5>
                        <p>Lineamientos metodológicos, marco institucional y criterios de evaluación.</p>
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
                        <p>Normas APA 7ma edición, estilo editorial, tablas y directivas de citado.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Guía
                        </span>
                    </a>
                </div>

                <!-- Hitos Paso a Paso -->
                <h3 class="mt-4 linea-debajo">Requisitos para la Aprobación del PTI:</h3>
                <p>
                    Para solicitar la aprobación del Plan de Trabajo de Investigación debes seguir los siguientes pasos:
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
                                Realiza el abono como <strong>pago ordinario</strong> en ventanilla de cualquier agencia de <strong>Caja Cusco</strong> a nivel nacional a nombre de la <em>Asociación Pukllasunchis - EESPP</em>.
                            </p>
                            <div class="pk-milestone-notice">
                                <i class="fa fa-info-circle"></i>
                                <span>Indica en ventanilla: <strong>Concepto: Aprobación de PTI y Asesor | Código: 10</strong>. En un lapso de 2 días hábiles recibirás la boleta electrónica oficial en tu correo institucional.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hito 2 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">2</div>
                        <div class="pk-milestone-header">
                            <h4>Elaboración y Formulación del Documento</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-time"><i class="fa fa-file-word"></i> Formato Word</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Descarga el <strong>Formato PTI</strong> y redacta tu documento según las especificaciones metodológicas y temáticas aprobadas por la institución.
                            </p>
                            <ul>
                                <li>Planteamiento claro del problema detectado en la práctica profesional.</li>
                                <li>Objetivos, justificación, antecedentes y fundamentación teórica contextualizada.</li>
                                <li>Metodología propuesta, cronograma y recursos requeridos.</li>
                            </ul>
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
                                Ingresa al formulario institucional con tu cuenta de correo corporativo y adjunta los requisitos solicitados:
                            </p>
                            <ul>
                                <li>Plan de Trabajo de Investigación completo en formato PDF.</li>
                                <li>Comprobante de pago oficial por derecho de aprobación emitido por la institución.</li>
                            </ul>

                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeErc1rq886EWiuavn27wd5kpCkZ48ntkOrWgNS5uE2XKw5JA/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Abrir Formulario Oficial de PTI
                            </a>
                        </div>
                    </div>

                    <!-- Hito 4 -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">4</div>
                        <div class="pk-milestone-header">
                            <h4>Emisión de R.D. y Asignación de Docente Asesor</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-time"><i class="fa fa-envelope-circle-check"></i> Notificación</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Secretaría Académica y la Coordinación de Investigación revisarán la solicitud. Recibirás en tu correo institucional:
                            </p>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <div>
                                    <strong>Resultado del trámite:</strong>
                                    <ul class="mb-0 mt-1">
                                        <li>Resolución Directoral (R.D.) de Aprobación de PTI con la designación oficial de tu asesor/a.</li>
                                        <li>Boleta electrónica acreditando el pago de los derechos correspondientes.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Callout Especial: Carta de Aplicación en Intranet -->
                <h3 class="mt-5">Solicitud de Carta de Aplicación (Siguiente Hito Obligatorio)</h3>
                <p>
                    Después de que tengas tu PTI aprobado, recuerda solicitar la carta de aplicación de tu Trabajo de investigación para presentarla ante la I.E. donde realizarás tu trabajo de campo:
                </p>
                <ul class="listasCuerpo">
                    <li>Hacer el pago de S/ 10 soles como pago ordinario en las oficinas de Caja Cusco <small class="text-info">(Concepto: Carta de presentación para INV / Código: 31)</small>.</li>
                    <li>Llenar el Formulario de Carta de Aplicación ingresando a tu Intranet con tu correo institucional (@pukllavirtual.edu.pe).</li>
                    <li>Secretaría te responderá en 3 días hábiles. Como resultado de este trámite recibirás en tu correo institucional la Carta de Aplicación y el modelo de aceptación.</li>
                </ul>

                <div class="mt-3 mb-4">
                    @auth
                        <a href="{{ route('mi-titulacion.carta') }}" class="btn text-white fw-bold px-4 py-2" style="background: linear-gradient(135deg, #d59d52, #cd9244); border-radius: 8px;">
                            <i class="fa fa-file-text me-2"></i> Abrir formulario en mi Intranet
                        </a>
                    @else
                        <button type="button" class="btn text-white fw-bold px-4 py-2" style="background: linear-gradient(135deg, #d59d52, #cd9244); border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalCartaIntranet">
                            <i class="fa fa-sign-in me-2"></i> Iniciar sesión en Intranet para solicitar
                        </button>
                    @endauth
                </div>

                <!-- Caja Cusco Callout Card -->
                <div class="pk-caja-cusco-card">
                    <div class="pk-caja-cusco-header">
                        <div class="pk-caja-icon">
                            <i class="fa fa-university"></i>
                        </div>
                        <div>
                            <h3>Pagos Ordinarios en Caja Cusco</h3>
                            <p>Exclusivo para trámites académicos y derechos del proceso de titulación.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Los pagos ordinarios <strong>SOLO SE REALIZAN EN VENTANILLAS</strong> de Caja Cusco a nivel nacional.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica el concepto correspondiente: <strong>Aprobación de PTI y Asesor (Código: 10)</strong>.</p>
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
                        <i class="fa fa-info-circle me-1"></i> Recuerda adjuntar tu <strong>boleta electrónica oficial</strong> de Caja Cusco (S/ 10.00 / Concepto: 31), no el voucher impreso de ventanilla.
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
