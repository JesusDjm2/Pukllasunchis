@extends('layouts.home')

@section('metas')
    @php $titulo = 'Tesis - Licenciatura en Educación'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Ruta oficial y requisitos para el desarrollo de la Tesis Profesional y la obtención del Título de Licenciado en Educación en la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Tesis Pukllasunchis, Titulo Licenciado Educacion, Sustentacion Tesis Cusco">
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
                        <i class="fa fa-award"></i> Fase 3 · Título Profesional de Licenciado en Educación
                    </span>
                </div>
                <h2 class="linea-debajo">Tesis de Licenciatura</h2>
                <p>
                    La tesis es el documento culminante que llevas adelante como bachiller para alcanzar tu grado de Licenciado. En ella demuestras la aplicación avanzada del Trabajo de Investigación: planteas un problema educativo relevante (preferiblemente surgido de tu práctica docente) y lo analizas con rigor académico, siguiendo la estructura establecida en nuestra Guía de Investigación. La sustentación de este trabajo es tan importante como el documento mismo, ya que completa el proceso formal de titulación.
                </p>

                <!-- Deck de Formatos y Guías Oficiales -->
                <h3 class="mt-4">Formatos y Carátulas Oficiales de Tesis</h3>
                <div class="pk-downloads-deck">
                    <!-- Formato Tesis -->
                    <a href="{{ asset('pdf/FormatoTesis.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Word (.docx)</span>
                        </div>
                        <h5>Formato Oficial de Tesis</h5>
                        <p>Estructura normalizada con preliminares, cuerpo y anexos.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Formato
                        </span>
                    </a>

                    <!-- Carátula FID -->
                    <a href="{{ asset('pdf/CaratulaTesis-2025.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Carátula FID</span>
                        </div>
                        <h5>Carátula Tesis (5 años)</h5>
                        <p>Para egresados de Formación Inicial Docente Regular.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Carátula
                        </span>
                    </a>

                    <!-- Carátula PPD -->
                    <a href="{{ asset('pdf/CaratulaTesis-2025-PPD.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Carátula PPD</span>
                        </div>
                        <h5>Carátula Tesis (1 año)</h5>
                        <p>Para egresados de Profesionalización Docente.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Carátula
                        </span>
                    </a>

                    <!-- Guía de Tesis -->
                    <a href="{{ asset('pdf/Guia-de-Elaboracion-de-Tesis.pdf') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon pdf">
                                <i class="fa fa-file-pdf"></i>
                            </div>
                            <span class="pk-download-card__tag">PDF Guía</span>
                        </div>
                        <h5>Guía de Elaboración de Tesis</h5>
                        <p>Lineamientos de investigación, sustento empírico y jurado.</p>
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
                        <p>Normas APA 7ma edición y directivas de edición institucional.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Guía
                        </span>
                    </a>
                </div>

                <!-- Hitos Paso a Paso Tesis -->
                <h3 class="mt-4 linea-debajo">Requisitos para la Obtención del Título de Licenciado:</h3>
                <p>
                    Una vez terminada la Tesis, para la obtención del Título de Licenciado se siguen estos pasos:
                </p>

                <div class="pk-milestones-timeline">
                    <!-- Hito 1: Turnitin Tesis -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">1</div>
                        <div class="pk-milestone-header">
                            <h4>Revisión de Originalidad (Turnitin de Tesis)</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 25.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 37</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> Hasta 10 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Evaluación de similitud y originalidad académica de la versión culminada de la tesis profesional.
                            </p>
                            <ul>
                                <li>Versión final de la Tesis en formato <strong>WORD (.docx)</strong>.</li>
                                <li>Comprobante de pago (Concepto: <em>REVISION TURNITIN DE TESIS | Código: 37</em>).</li>
                            </ul>
                            <div class="pk-milestone-notice">
                                <i class="fa fa-circle-exclamation"></i>
                                <span><strong>Regla de Exclusión (Art. 94):</strong> Si tu tesis es continuidad del TI con el que obtuviste tu bachiller, para la revisión preliminar de originalidad <strong>deberás excluir únicamente la sección "Base Teórica"</strong>. La versión oficial definitiva deberá presentarse íntegra en las etapas posteriores.</span>
                            </div>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Porcentaje máximo permitido: <strong>20%</strong>. Recibirás tu reporte oficial por correo en un plazo no mayor a 10 días hábiles.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Formulario de Turnitin Tesis
                            </a>
                        </div>
                    </div>

                    <!-- Hito 2: Dictaminantes -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">2</div>
                        <div class="pk-milestone-header">
                            <h4>Revisión de Tesis por Docentes Dictaminantes</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 100.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 32</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> Hasta 60 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Dictamen de contenido y rigurosidad científica a cargo de la comisión de docentes evaluadores.
                            </p>
                            <ul>
                                <li>Tesis completa en formato WORD.</li>
                                <li>Reporte de originalidad Turnitin adjunto (≤ 20% de similitud).</li>
                                <li>Comprobante de pago (Concepto: <em>REVISIÓN DE TESIS | Código: 32</em>).</li>
                            </ul>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Como resultado, recibirás en tu correo institucional el <strong>Informe de Tesis Apta</strong> para solicitar fecha de sustentación.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfU1nSpAs5WnyxP6rcmsQ8DIZlwetYSLyWRTqJ1srFtVCo5HQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Solicitar Revisión de Dictaminantes
                            </a>
                        </div>
                    </div>

                    <!-- Hito 3: Sustentación -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">3</div>
                        <div class="pk-milestone-header">
                            <h4>Asignación de Fecha y Hora de Sustentación</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 100.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 21</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Fijación formal de la fecha del acto público de defensa de tesis ante el jurado calificador designado.
                            </p>
                            <ul>
                                <li>Informe de Tesis Apta en formato WORD o PDF.</li>
                                <li>Reporte Turnitin aprobado (máximo 20% de similitud).</li>
                                <li>Versión final de la Tesis en WORD y Diapositivas en PPT.</li>
                                <li><strong>Constancia de inscripción en SUNEDU del diploma de Bachiller en formato PDF.</strong></li>
                                <li>Comprobante de pago (Concepto: <em>Fecha de sustentación de Tesis | Código: 21</em>).</li>
                            </ul>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSf4DRlwjTV-vK5FVud94tUxTHi8h61brejjn9bzxBpv7ouqpQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Solicitar Fecha de Sustentación
                            </a>
                        </div>
                    </div>

                    <!-- Hito 4: RD Aprobación Sustentación -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">4</div>
                        <div class="pk-milestone-header">
                            <h4>Resolución Directoral (R.D.) de Aprobación de Sustentación</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 33</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Formalización legal e institucional de la aprobación de la defensa de la tesis profesional.
                            </p>
                            <ul>
                                <li>Acta oficial de sustentación firmada por los miembros del jurado.</li>
                                <li>Informe de tesis apta.</li>
                                <li>Comprobante de pago (Concepto: <em>RD de aprobación de Tesis | Código: 33</em>).</li>
                            </ul>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Recibirás tu Resolución Directoral en el correo institucional el día lunes de la semana siguiente a la presentación de la solicitud.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSc8k02uEAuRt9OdrGpj1Xe6OIBdXXCZ2j7pNToBxhdktq_smQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Solicitar R.D. de Sustentación
                            </a>
                        </div>
                    </div>

                    <!-- Hito 5: Repositorio Digital -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">5</div>
                        <div class="pk-milestone-header">
                            <h4>Depósito de Tesis en Repositorio Institucional</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 34</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> 30 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Indexación y preservación digital de la tesis en el Repositorio de la Escuela y la Red Nacional ALICIA-CONCYTEC.
                            </p>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <a href="{{ asset('pdf/Autorizacion-Puklla.docx') }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 text-start py-2">
                                        <i class="fa fa-download me-1"></i> Autorización de Repositorio (.docx)
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="{{ asset('pdf/Declaración-jurada-de-autenticidad.docx') }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 text-start py-2">
                                        <i class="fa fa-download me-1"></i> Declaración de Autenticidad (.docx)
                                    </a>
                                </div>
                            </div>
                            <ul>
                                <li>Versión final corregida de la tesis en WORD.</li>
                                <li>Autorización de depósito y Declaración Jurada firmadas.</li>
                                <li>Comprobante de pago (Concepto: <em>Registro de Tesis en repositorio | Código: 34</em>).</li>
                            </ul>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Al no registrar observaciones metodológicas o formales, recibirás el enlace permanente (URL Handle) de registro institucional.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Registrar Tesis en Repositorio
                            </a>
                        </div>
                    </div>

                    <!-- Hito 6 y 7: Emisión e Inscripción SUNEDU -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">6</div>
                        <div class="pk-milestone-header">
                            <h4>Emisión de Título de Licenciado e Inscripción ante SUNEDU</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 400.00 + S/ 200.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Códs: 35 y 36</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Procedimiento final de emisión del diploma oficial de Licenciado en Educación y su reconocimiento en el Registro Nacional de SUNEDU.
                            </p>

                            <!-- Sub A: Emisión -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <h5 class="font-weight-bold text-dark mb-1">
                                    <i class="fa fa-stamp text-primary me-2"></i> A. Solicitud de Emisión de Diploma de Licenciado
                                </h5>
                                <p class="text-muted mb-2" style="font-size: 14px;">
                                    Pago ordinario de <strong>S/ 400.00</strong> (Concepto: <em>Emisión título de Licenciado | Código: 35</em>). Plazo: 15 días hábiles. Se te notificará la fecha para acudir a la firma presencial del libro de títulos.
                                </p>
                                <a href="https://forms.gle/oVAax2L4GiaPGTWp9" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="fa fa-file-signature me-1"></i> Formulario de Emisión de Diploma
                                </a>
                            </div>

                            <!-- Sub B: SUNEDU -->
                            <div class="p-3 bg-light rounded-3 border">
                                <h5 class="font-weight-bold text-dark mb-1">
                                    <i class="fa fa-globe text-success me-2"></i> B. Registro e Inscripción de Título en SUNEDU
                                </h5>
                                <p class="text-muted mb-2" style="font-size: 14px;">
                                    Pago ordinario de <strong>S/ 200.00</strong> (Concepto: <em>Inscripción título a SUNEDU | Código: 36</em>). Culmina en aprox. 30 días hábiles tras la remisión del expediente por Secretaría.
                                </p>
                                <a href="https://forms.gle/L7WHbS67NKYnWug69" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="fa fa-award me-1"></i> Formulario de Inscripción en SUNEDU (Correo institucional)
                                </a>
                            </div>
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
                            <h3>Pagos del Proceso de Licenciatura en Caja Cusco</h3>
                            <p>Realiza tus abonos exclusivamente mediante pago ordinario en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Recuerda que para adjuntar tus comprobantes a los formularios debes esperar tu <strong>boleta electrónica institucional</strong> (llega en 2 días hábiles), no el voucher impreso del banco.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica el concepto exacto y código (Turnitin Tesis: 37, Dictamen: 32, Sustentación: 21, RD: 33, Repositorio: 34).</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Dicta el número de <strong>DNI</strong> y los nombres y apellidos completos del estudiante graduando.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p>Guarda la boleta electrónica PDF oficial emitida por la EESPP que llegará a tu correo institucional.</p>
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
@endsection
