@extends('layouts.home')

@section('metas')
    @php $titulo = 'Proceso de Titulación'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Guía oficial del proceso de titulación y grados académicos en la EESPP Pukllasunchis: Plan de Trabajo (PTI), Trabajo de Investigación (Bachillerato), Tesis (Licenciatura), trámites presenciales y extraordinarios.">
    <meta name="keywords" content="{{ $titulo }}, Titulación Pukllasunchis, Bachiller Educacion, Licenciatura Educacion, PTI, Tesis Pukllasunchis, Cusco">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area biblioteca bradcam_overlay pk-bradcam-titulacion">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <div class="pk-hero-badge">
                            <i class="fa fa-book-reader"></i> Guía Oficial de Grados y Títulos
                        </div>
                        <h1 class="pk-hero-title tituloAnimado">{{ $titulo }}</h1>
                        <p class="pk-hero-desc slideUp">
                            Conoce las 3 fases reglamentarias, requisitos, derechos de trámite y directivas oficiales de titulación de la EESPP Pukllasunchis.
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
                        <i class="fa fa-graduation-cap"></i> Portal Institucional de Titulación
                    </span>
                </div>
                <h2 class="linea-debajo">Ruta del Egresado: De la Investigación al Título Profesional</h2>
                <p class="pk-titula-lead">
                    Conoce las etapas formativas normadas por la Ley N° 30512 y las directivas de la EESPP Pukllasunchis para la elaboración de tu trabajo de investigación, la obtención del <strong>Grado de Bachiller</strong> y la sustentación del <strong>Título Profesional de Licenciado en Educación</strong>.
                </p>

                <!-- Visual Roadmap Stepper -->
                <div class="pk-roadmap-card">
                    <div class="pk-roadmap-title">
                        <h3><i class="fa fa-route text-warning"></i> Fases del Proceso de Graduación</h3>
                        <span class="badge-total"><i class="fa fa-circle-check text-success me-1"></i> 3 Fases Académicas</span>
                    </div>

                    <div class="pk-stepper-grid">
                        <!-- Paso 1 -->
                        <a href="{{ route('plan') }}" class="pk-step-box">
                            <div class="pk-step-box-header">
                                <div class="pk-step-num">1</div>
                                <span class="pk-step-tag">Fase 1 · Plan de Trabajo</span>
                            </div>
                            <h4>Plan de Trabajo (PTI)</h4>
                            <p>
                                Propuesta de investigación vinculada a la práctica docente, pago en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>, formulación y aprobación por R.D. con asignación de asesor.
                            </p>
                            <span class="pk-step-link">
                                Ver requisitos y formatos <i class="fa fa-arrow-right"></i>
                            </span>
                        </a>

                        <!-- Paso 2 -->
                        <a href="{{ route('tinvestigacion') }}" class="pk-step-box active">
                            <div class="pk-step-box-header">
                                <div class="pk-step-num">2</div>
                                <span class="pk-step-tag">Fase 2 · Grado de Bachiller</span>
                            </div>
                            <h4>Trabajo de Inv. (TI)</h4>
                            <p>
                                Redacción progresiva, reporte de originalidad Turnitin (máx. 20%), dictaminantes, exposición pública, repositorio y Grado de Bachiller SUNEDU.
                            </p>
                            <span class="pk-step-link">
                                Ver proceso de Bachillerato <i class="fa fa-arrow-right"></i>
                            </span>
                        </a>

                        <!-- Paso 3 -->
                        <a href="{{ route('tesis') }}" class="pk-step-box">
                            <div class="pk-step-box-header">
                                <div class="pk-step-num">3</div>
                                <span class="pk-step-tag">Fase 3 · Licenciatura</span>
                            </div>
                            <h4>Tesis Profesional</h4>
                            <p>
                                Profundización y rigor metodológico para bachilleres. Sustentación formal ante jurado calificador, emisión de diploma y registro oficial ante SUNEDU.
                            </p>
                            <span class="pk-step-link">
                                Ver proceso de Tesis <i class="fa fa-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Otros Trámites -->
                <h3 class="mt-4 mb-3">Otros Trámites</h3>

                <div class="pk-cards-grid">
                    <!-- Presenciales -->
                    <a href="{{ route('tramites') }}" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-building-columns"></i>
                            </div>
                            <span class="pk-card-tramite__badge">Presencial</span>
                        </div>
                        <h4>Trámites Presenciales</h4>
                        <p>
                            Armado de expediente de graduación (FID / PPD), descarga de FUT, certificados de estudios superiores y constancias de no deudor.
                        </p>
                        <div class="pk-card-tramite__action">
                            <span>Ver requisitos presenciales</span> <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Extraordinarios -->
                    <a href="{{ route('extraordinarios') }}" class="pk-card-tramite">
                        <div class="pk-card-tramite__top">
                            <div class="pk-card-tramite__icon">
                                <i class="fa fa-receipt"></i>
                            </div>
                            <span class="pk-card-tramite__badge">Extraordinarios</span>
                        </div>
                        <h4>Trámites Extraordinarios</h4>
                        <p>
                            Cambio de tema o asesor, separación o integración de grupo, ampliación de plazo, dejar sin efecto R.D. y asesorías adicionales.
                        </p>
                        <div class="pk-card-tramite__action">
                            <span>Ver conceptos y costos</span> <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>

                <!-- Marco Normativo y Requisitos Académicos -->
                <h2 class="mt-4 linea-debajo">Requisitos para el Grado de Bachiller:</h2>
                <ul class="listasCuerpo">
                    <li>Solicitud dirigida al Director General de la EESP.</li>
                    <li>Certificado que acredite estudios por un mínimo de diez (10) ciclos académicos y la aprobación de un mínimo de doscientos (200) créditos del PE o del PPD docente correspondiente.</li>
                    <li>Aprobar el trabajo de investigación es requisito para optar al grado de bachiller.</li>
                    <li>Documento que acredite conocimiento de un idioma extranjero (mínimo nivel A2 del MCER) o de una lengua originaria según los DCBN para Educación Inicial y Primaria Intercultural Bilingüe (EIB).</li>
                </ul>

                <p class="generic-blockquote mt-4">
                    <strong>Niveles de inglés A (usuario básico)</strong><br>
                    <strong>Test de inglés A2:</strong> Es capaz de comprender frases y expresiones de uso frecuente relacionadas con áreas de experiencia que le son especialmente relevantes. Sabe comunicarse a la hora de llevar a cabo tareas simples y cotidianas que no requieran más que intercambios sencillos y directos de información.<br>
                    <a href="https://tracktest.eu/es/niveles-de-ingles-mcer-cefr/#Ingl%C3%A9susuariobasico" target="_blank" style="color: #ff5e13">
                        <strong>Niveles de inglés MCER (CEFR)</strong>
                    </a>
                </p>

                <h2 class="mt-4 linea-debajo">Título Profesional de Licenciado en Educación</h2>
                <p>
                    Es el reconocimiento que obtiene el bachiller luego de haber aprobado una tesis o trabajo de suficiencia profesional ante el jurado calificador correspondiente.
                </p>

                <h3 class="mt-4">Requisitos para solicitar el Título de Licenciado:</h3>
                <ul class="listasCuerpo">
                    <li>Solicitud dirigida al Director General de la EESP.</li>
                    <li>Documento que acredite contar con el grado de bachiller en el Registro Nacional de Grados y Títulos de la SUNEDU.</li>
                    <li>Documento que acredite la aprobación de la sustentación de tesis o del trabajo de suficiencia profesional.</li>
                </ul>

                <h2 class="mt-4 linea-debajo">Título de Segunda Especialidad Profesional</h2>
                <p>
                    Es el reconocimiento que se obtiene al haber culminado un programa formativo de especialidad con una duración mínima de dos (2) semestres académicos y cuarenta (40) créditos.
                </p>

                <!-- Acordeón de Trámites Administrativos -->
                <h3 class="mt-4 linea-debajo">Procedimientos Administrativos de Grados y Títulos</h3>

                <div class="accordion pk-accordion" id="accordionTitulacionHub">
                    <!-- Duplicado -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDuplicado">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseDuplicado" aria-expanded="false" aria-controls="collapseDuplicado">
                                Duplicado de Grados y Títulos
                            </button>
                        </h2>
                        <div id="collapseDuplicado" class="accordion-collapse collapse" aria-labelledby="headingDuplicado"
                            data-bs-parent="#accordionTitulacionHub">
                            <div class="accordion-body">
                                <p>
                                    El duplicado del diploma anula de forma automática el diploma original. La institución tramita ante SUNEDU la anulación de la inscripción previa y la posterior actualización de datos consignados en el duplicado.
                                </p>
                                <h5>Requisitos:</h5>
                                <ul class="listasCuerpo">
                                    <li>Solicitud dirigida al Director General de la EESP.</li>
                                    <li>Declaración jurada de pérdida, robo y/o deterioro.</li>
                                    <li>Comprobante de pago por derechos de tramitación en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Emisión -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingEmision">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseEmision" aria-expanded="false" aria-controls="collapseEmision">
                                Emisión de Grados y Títulos
                            </button>
                        </h2>
                        <div id="collapseEmision" class="accordion-collapse collapse" aria-labelledby="headingEmision"
                            data-bs-parent="#accordionTitulacionHub">
                            <div class="accordion-body">
                                <p>
                                    La emisión de diplomas se rige por la normativa establecida por el MINEDU y SUNEDU. El egresado formaliza su solicitud mediante formulario virtual y acude de manera presencial para la firma del libro de actas correspondiente antes de la remisión del expediente a SUNEDU.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Rectificación -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRectificacion">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseRectificacion" aria-expanded="false" aria-controls="collapseRectificacion">
                                Rectificación de Diploma de Bachiller o Título de Licenciado
                            </button>
                        </h2>
                        <div id="collapseRectificacion" class="accordion-collapse collapse" aria-labelledby="headingRectificacion"
                            data-bs-parent="#accordionTitulacionHub">
                            <div class="accordion-body">
                                <p>
                                    Aplica para casos de rectificación de nombres, apellidos o correcciones de datos académicos:
                                </p>
                                <ul class="listasCuerpo">
                                    <li>Solicitud dirigida al Director General describiendo de forma clara el error a rectificar.</li>
                                    <li>Documento oficial que acredite el dato correcto por rectificar (partida de nacimiento o DNI).</li>
                                    <li>El pago por derechos de tramitación no aplica si la institución incurrió en error involuntario al emitir el diploma.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Constancias y Certificados -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCertificados">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseCertificados" aria-expanded="false" aria-controls="collapseCertificados">
                                Constancias y Certificados Oficiales
                            </button>
                        </h2>
                        <div id="collapseCertificados" class="accordion-collapse collapse" aria-labelledby="headingCertificados"
                            data-bs-parent="#accordionTitulacionHub">
                            <div class="accordion-body">
                                <p>
                                    <strong>Constancia de Egresado:</strong> Acredita la culminación satisfactoria de todos los periodos formativos del plan de estudios. Requiere solicitud formal, certificado de estudios y contar con la constancia de no deudor.
                                </p>
                                <p>
                                    <strong>Certificado de Estudios Superiores:</strong> Documento oficial emitido con base en las actas de evaluación académica de cada semestre cursado.
                                </p>
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
                            <h3>Instrucciones de Pago en <span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span></h3>
                            <p>Todos los derechos del proceso de titulación corresponden a pagos ordinarios en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span><strong>Importante:</strong> Los pagos ordinarios <strong>SOLO SE REALIZAN EN VENTANILLAS</strong> de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> a nivel nacional. No se consideran pagos ordinarios las matrículas ni cuotas semestrales.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Acércate a ventanilla en cualquier agencia a nivel nacional e indica <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica obligatoriamente los 3 datos: <strong>Concepto exacto</strong>, <strong>Código de pago</strong> y <strong>Monto</strong> de tu trámite (PTI, Turnitin, dictamen, exposición o repositorio).</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Proporciona el <strong>DNI</strong> y el nombre y apellidos completos del estudiante graduando.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p><strong>No necesitas enviar el voucher:</strong> en un lapso de 2 días hábiles recibirás tu boleta electrónica en tu correo institucional.</p>
                        </div>
                    </div>

                    <div class="pk-caja-footer">
                        <a href="javascript:void(0)" onclick="openPagoModal(event)" class="text-primary font-weight-bold" style="font-size: 14.5px;">
                            <i class="fa fa-circle-info me-1"></i> Ver instructivo ilustrado de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>
                        </a>
                        <a href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}" target="_blank" class="pk-btn-caja-pdf">
                            <i class="fa fa-file-pdf"></i> Descargar PDF de Conceptos Oficiales
                        </a>
                    </div>
                </div>

                <!-- Navegación de inicio de fases -->
                <div class="pk-etapas-nav mt-5">
                    <a href="{{ route('plan') }}" class="pk-etapas-nav-card pk-etapas-nav-next">
                        <div class="pk-etapas-nav-info">
                            <span class="pk-etapas-nav-badge">Paso 1 del Proceso</span>
                            <h4 class="pk-etapas-nav-title">Fase 1: Plan de Trabajo de Investigación (PTI)</h4>
                            <p class="pk-etapas-nav-desc">Inicia tu proceso formulando tu propuesta de investigación y solicitando docente asesor/a.</p>
                        </div>
                        <span class="pk-etapas-nav-btn">
                            <span>Comenzar con la Fase 1</span>
                            <i class="fa fa-arrow-right"></i>
                        </span>
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
