@extends('layouts.home')

@section('metas')
    @php $titulo = 'Trámites Presenciales y Armado de Expediente'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Requisitos y procedimientos para trámites presenciales, descarga del FUT y armado de expediente de graduación en la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Expediente Titulacion Pukllasunchis, FUT Pukllasunchis, Tramites Presenciales">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay pk-bradcam-titulacion">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <nav aria-label="Migas de pan" class="pk-breadcrumbs-nav">
                            <ol class="pk-breadcrumbs-list">
                                <li><a href="{{ route('index') }}"><i class="fa fa-home"></i> Inicio</a></li>
                                <li><i class="fa fa-chevron-right pk-breadcrumb-sep"></i></li>
                                <li><a href="{{ route('tramiteTitulacion') }}">Titulación</a></li>
                                <li><i class="fa fa-chevron-right pk-breadcrumb-sep"></i></li>
                                <li class="pk-breadcrumb-current" aria-current="page">Expediente Presencial</li>
                            </ol>
                        </nav>
                        <div class="pk-hero-badge">
                            <i class="fa fa-folder-open"></i> Secretaría Académica
                        </div>
                        <h1 class="pk-hero-title tituloAnimado">{{ $titulo }}</h1>
                        <p class="pk-hero-desc slideUp">
                            Documentación física y requisitos oficiales necesarios para la conformación del expediente presencial de titulación.
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
                        <i class="fa fa-building-columns"></i> Secretaría Académica · Trámites Presenciales
                    </span>
                </div>
                <h2 class="linea-debajo">Trámites Presenciales y Armado de Expediente</h2>
                <p>
                    Los siguientes procedimientos forman parte del proceso de graduación y titulación y se realizan de manera presencial en la Secretaría Académica de la Escuela para la validación y cotejo de documentos originales físicos.
                </p>

                <!-- Featured Download Card: FUT -->
                <div class="card border-0 mb-4" id="derecho" style="background: #fdfaf6; border: 1px solid var(--pk-slate-200) !important; border-radius: 8px; box-shadow: var(--pk-shadow-sm);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 48px; height: 48px; border-radius: 8px; background: #263552; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                                    <i class="fa fa-file-lines"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1" style="font-size: 18px; color: #1F1F1F;">
                                        Formulario Único de Trámite (FUT Oficial)
                                    </h4>
                                    <p class="mb-0" style="font-size: 14px; line-height: 22px; color: #666; text-align: left;">
                                        Documento base requerido para toda solicitud presencial o gestión administrativa en secretaría.
                                    </p>
                                </div>
                            </div>
                            <a href="{{ asset('pdf/fut.docx') }}" target="_blank" class="btn text-white px-4 py-2" style="background: #263552; border-radius: 6px; font-weight: 500; font-size: 14px;">
                                <i class="fa fa-download me-2"></i> Descargar FUT (.docx)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Armado de Expediente Accordion -->
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="mb-0" style="font-size: 22px;">
                            <i class="fa fa-folder-tree text-warning me-2"></i> Armado de Expediente de Graduación
                        </h3>
                        <span class="pk-badge-fee">
                            Costo: S/ 500.00 · Código: 18
                        </span>
                    </div>
                    <p>
                        Selecciona tu modalidad de estudios para revisar la relación completa de documentos externos e institucionales a presentar:
                    </p>

                    <div class="accordion pk-accordion" id="accordionExpediente">
                        <!-- Opción FID (5 años) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFID">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFID" aria-expanded="true" aria-controls="collapseFID">
                                    <i class="fa fa-graduation-cap me-2 text-primary"></i> Para Estudiantes de Formación Inicial Docente — FID (5 años)
                                </button>
                            </h2>
                            <div id="collapseFID" class="accordion-collapse collapse show" aria-labelledby="headingFID"
                                data-bs-parent="#accordionExpediente">
                                <div class="accordion-body">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <h5 class="pb-2 border-bottom">
                                                <i class="fa fa-id-card-clip text-warning me-1"></i> Documentos Externos a Presentar:
                                            </h5>
                                            <ul class="list-unstyled ms-2 mt-2" style="font-size: 14.5px; line-height: 24px;">
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Dos (2) fotocopias de DNI ampliado y legalizado notarialmente.</li>
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Partida de nacimiento original (vigente).</li>
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Certificado de estudios secundarios original.</li>
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Ocho (8) fotos tamaño pasaporte a color, fondo blanco, con terno y en papel mate (4 cm x 5 cm).</li>
                                                <li class="mb-2">
                                                    <i class="fa fa-check text-success me-2"></i> Documento acreditando dominio de idioma extranjero (mínimo nivel <strong>A2</strong> del MCER) o lengua originaria según DCBN EIB.
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <h5 class="pb-2 border-bottom">
                                                <i class="fa fa-building-circle-check text-primary me-1"></i> Documentos Tramitados en la Escuela:
                                            </h5>
                                            <ul class="list-unstyled ms-2 mt-2" style="font-size: 14.5px; line-height: 24px;">
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> FUT debidamente completado y firmado.</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Certificado de estudios superiores (30 días hábiles · Cód: 8 · S/ 50.00 por ciclo).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Copia de R.D. de aprobación de prácticas preprofesionales (Cód: 30 · S/ 50.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Carta de aceptación de la I.E. donde se ejecutó la investigación (original).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de no deudor original (Cód: 28 · S/ 30.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de egresado original (Cód: 9 · S/ 50.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de primera matrícula (Cód: 29 · S/ 50.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> R.D. de aprobación de PTI y asignación de asesor (original).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Informe de Trabajo de Investigación Apto (original).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Comprobante de pago original por derecho de obtención de grado de Bachiller (Cód: 18 · S/ 500.00).</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pk-milestone-result mt-3">
                                        <i class="fa fa-circle-check"></i>
                                        <span><strong>Resultado del trámite:</strong> Recepción de la <em>Carta de Aceptación de Expediente de Graduación Completo</em> para la prosecución de tu grado académico.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opción PPD (1 año) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPPD">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapsePPD" aria-expanded="false" aria-controls="collapsePPD">
                                    <i class="fa fa-user-check me-2 text-warning"></i> Para Estudiantes de Profesionalización Docente — PPD (1 año)
                                </button>
                            </h2>
                            <div id="collapsePPD" class="accordion-collapse collapse" aria-labelledby="headingPPD"
                                data-bs-parent="#accordionExpediente">
                                <div class="accordion-body">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <h5 class="pb-2 border-bottom">
                                                <i class="fa fa-id-card-clip text-warning me-1"></i> Documentos Externos a Presentar:
                                            </h5>
                                            <ul class="list-unstyled ms-2 mt-2" style="font-size: 14.5px; line-height: 24px;">
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Dos (2) fotocopias de DNI ampliado y legalizado.</li>
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Cuatro (4) fotos tamaño pasaporte a color, fondo blanco, terno y papel mate (4 cm x 5 cm).</li>
                                                <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Documento acreditando conocimiento de idioma A2 o lengua originaria.</li>
                                                <li class="mb-2">
                                                    <i class="fa fa-check text-success me-2"></i> Certificado original de estudios superiores:
                                                    <ul class="mt-1 ps-3 text-muted">
                                                        <li>Procedentes de IESP: original visado por la DREC.</li>
                                                        <li>Procedentes de Universidad: legalizado por notario o fedatado por la universidad de origen.</li>
                                                        <li><em>(Si ya obra en el expediente de ingreso, no es necesario volver a presentarlo).</em></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <h5 class="pb-2 border-bottom">
                                                <i class="fa fa-building-circle-check text-primary me-1"></i> Documentos Tramitados en la Escuela:
                                            </h5>
                                            <ul class="list-unstyled ms-2 mt-2" style="font-size: 14.5px; line-height: 24px;">
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> FUT debidamente llenado y firmado.</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Certificado de estudios superiores del programa PPD (Cód: 8 · S/ 50.00 por ciclo).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Carta de aceptación de la institución educativa sede de investigación (original).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de no deudor original (Cód: 28 · S/ 30.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de egresado original (Cód: 9 · S/ 50.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Constancia de primera matrícula (Cód: 29 · S/ 50.00).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> Informe de Trabajo de Investigación Apto (original).</li>
                                                <li class="mb-2"><i class="fa fa-check text-primary me-2"></i> R.D. de aprobación de Trabajo de Investigación (original).</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pk-milestone-result mt-3">
                                        <i class="fa fa-circle-check"></i>
                                        <span><strong>Resultado del trámite:</strong> Emisión de la <em>Carta de Aceptación de Expediente de Graduación Completo</em>.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Otros Trámites Presenciales Específicos -->
                <div class="mt-5">
                    <h3 class="mb-3" style="font-size: 22px;">
                        <i class="fa fa-stamp text-warning me-2"></i> Constancias y Certificados Individuales
                    </h3>

                    <div class="row g-3">
                        <!-- Carta de Presentación -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="mb-0" style="font-size: 16px;">Carta de Presentación para I.E.</h5>
                                    <span class="badge bg-light text-dark border">S/ 10.00</span>
                                </div>
                                <p class="mb-2" style="font-size: 14px; line-height: 22px;">
                                    Permite solicitar a la I.E. la autorización para realizar la investigación de campo.
                                </p>
                                <ul class="list-unstyled ms-1 text-muted" style="font-size: 13.5px; line-height: 20px;">
                                    <li>• Presentar FUT con datos de la I.E., Director, ubicación y título del TI.</li>
                                    <li>• Comprobante de pago ordinario en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Constancia de No Deudor -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="mb-0" style="font-size: 16px;">Constancia de No Deudor</h5>
                                    <span class="badge bg-light text-dark border">S/ 30.00 · Cód: 28</span>
                                </div>
                                <p class="mb-2" style="font-size: 14px; line-height: 22px;">
                                    Acredita no registrar deudas por pensiones, libros o materiales en la institución.
                                </p>
                                <ul class="list-unstyled ms-1 text-muted" style="font-size: 13.5px; line-height: 20px;">
                                    <li>• Presentar FUT debidamente completado.</li>
                                    <li>• Comprobante de pago y validación con el área de Cobranzas.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Constancia de Egresado -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="mb-0" style="font-size: 16px;">Constancia de Egresado</h5>
                                    <span class="badge bg-light text-dark border">S/ 50.00 · Cód: 9</span>
                                </div>
                                <p class="mb-2" style="font-size: 14px; line-height: 22px;">
                                    Acredita la culminación satisfactoria de todos los periodos formativos cursados.
                                </p>
                                <ul class="list-unstyled ms-1 text-muted" style="font-size: 13.5px; line-height: 20px;">
                                    <li>• Dos (2) fotos tamaño carné en fondo blanco con terno.</li>
                                    <li>• Constancia de no deudor aprobada previamente.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Certificados de Estudios Superiores -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="mb-0" style="font-size: 16px;">Certificado de Estudios Superiores</h5>
                                    <span class="badge bg-light text-dark border">S/ 50.00/ciclo · Cód: 8</span>
                                </div>
                                <p class="mb-2" style="font-size: 14px; line-height: 22px;">
                                    Documento oficial con notas por cada semestre académico (duración: 30 días hábiles).
                                </p>
                                <ul class="list-unstyled ms-1 text-muted" style="font-size: 13.5px; line-height: 20px;">
                                    <li>• Seis (6) fotos tamaño pasaporte con fondo blanco y terno.</li>
                                    <li>• FUT y comprobante de pago por cada ciclo académico a certificar.</li>
                                </ul>
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
                            <h3>Pagos para Trámites Presenciales en <span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span></h3>
                            <p>Realiza tus abonos exclusivamente mediante pago ordinario en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Los pagos ordinarios <strong>SOLO SE PAGAN EN VENTANILLAS</strong> de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> a nivel nacional.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica obligatoriamente los 3 datos: <strong>Concepto exacto</strong>, <strong>Código numérico</strong> y <strong>Monto</strong> a pagar (ejemplo: Armado de expediente de Titulación: Cód. 18 - S/ 500.00, Constancia no deudor: Cód. 28 - S/ 10.00, etc.).</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Dicta el número de <strong>DNI</strong> y los nombres y apellidos completos del alumno.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p>Adjunta la boleta electrónica emitida por la EESPP al momento de ingresar tu trámite presencial en Secretaría.</p>
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

            </div>
        </div>
    </div>
@endsection
