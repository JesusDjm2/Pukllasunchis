@extends('layouts.home')

@section('metas')
    @php $titulo = 'Trabajo de Investigación (TI) - Bachillerato'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Ruta oficial y requisitos para el desarrollo del Trabajo de Investigación (TI) y la obtención del Grado de Bachiller en Educación en la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Bachiller Pukllasunchis, TI Pukllasunchis, Investigacion Educacion">
    <link rel="stylesheet" href="{{ asset('css/pk-tramites-titulacion.css') }}">
@endsection

@section('contenido')
    <div class="bradcam_area tramites bradcam_overlay pk-bradcam-titulacion">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <div class="pk-hero-badge">
                            <i class="fa fa-graduation-cap"></i> Fase 2 de 3 · Grado de Bachiller
                        </div>
                        <h1 class="pk-hero-title tituloAnimado">{{ $titulo }}</h1>
                        <p class="pk-hero-desc slideUp">
                            Ruta académica y requisitos oficiales para la culminación del trabajo y la obtención del Grado de Bachiller en Educación.
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
                        <i class="fa fa-book-bookmark"></i> Fase 2 · Grado de Bachiller en Educación
                    </span>
                </div>
                <h2 class="linea-debajo">Trabajo de Investigación (TI)</h2>
                <p>
                    Tiene relación con los principales ejes planteados en el plan de estudios, el graduando debe demostrar que domina los aspectos desarrollados en el currículo. El TI supone el desarrollo de aptitudes y habilidades relacionadas con el perfil de egreso y el planteamiento de un tema preciso a analizar relacionado con situaciones detectadas en la realidad educativa. El desarrollo del TI incluye la revisión de la literatura actualizada, una metodología de análisis de la información recolectada y una exposición de resultados. Conduce a la obtención del Grado de Bachiller en Educación. El TI se elabora de forma progresiva y se debe cumplir con la estructura solicitada por la escuela en su guía de investigación.
                </p>

                <!-- Hitos Paso a Paso TI -->
                <h3 class="mt-4 linea-debajo">Requisitos para la Obtención del Grado de Bachiller:</h3>
                <p>
                    Una vez terminado el Trabajo de Investigación, para la obtención del Grado de Bachiller se siguen estos pasos:
                </p>

                <div class="pk-milestones-timeline">
                    <!-- Hito 1: Turnitin -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">1</div>
                        <div class="pk-milestone-header">
                            <h4>Revisión de Originalidad (Turnitin)</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 25.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 16</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> Hasta 10 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Envía la versión culminada de tu Trabajo de Investigación para la verificación de originalidad y porcentaje de coincidencias.
                            </p>
                            <ul>
                                <li>Versión final del TI en formato editable <strong>WORD (.docx)</strong>.</li>
                                <li>Boleta electrónica oficial de la EESPP (no voucher impreso de ventanilla).</li>
                            </ul>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">Revisión Turnitin de TI</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">16</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 25.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> El voucher que te entrega el cajero <u>NO</u> se sube al formulario. En un lapso de <strong>2 días hábiles</strong> recibirás la <strong>Boleta Electrónica Oficial</strong> en tu correo institucional para adjuntar al formulario.
                                    </div>
                                </div>
                            </div>

                            <div class="pk-milestone-notice">
                                <i class="fa fa-circle-exclamation"></i>
                                <span><strong>Criterio de Aprobación:</strong> El porcentaje máximo de similitud permitido es del <strong>20%</strong>. Si supera dicho límite, deberás subsanar las citas y solicitar una nueva revisión.</span>
                            </div>
                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Recibirás el Reporte Oficial Turnitin en tu correo institucional en un plazo no mayor a 10 días hábiles. El pago cubre 1 revisión dentro de 5 días hábiles posteriores.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSc1Bam9QlzjkT66NYeKMxS-xyoU4MchYtYiGCMmtvKVbHiCjg/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Abrir Formulario de Turnitin
                            </a>
                        </div>
                    </div>

                    <!-- Hito 2: Revisión por Asesor / Dictaminante -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">2</div>
                        <div class="pk-milestone-header">
                            <h4>Revisión de TI por Asesor / Dictaminantes</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 100.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 15</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> Hasta 30 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Evaluación de fondo y coherencia teórico-metodológica realizada por el equipo de docentes dictaminantes designados.
                            </p>
                            <ul>
                                <li>Trabajo de Investigación en formato WORD.</li>
                                <li>Reporte de originalidad Turnitin adjunto (máx. 20% de similitud).</li>
                                <li>Boleta electrónica oficial emitida por la EESPP (no voucher).</li>
                            </ul>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">Revisión de Trabajo de Investigación</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">15</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 100.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> El voucher que te entrega el banco <u>NO</u> se sube al formulario. En un lapso de <strong>2 días hábiles</strong> recibirás la <strong>Boleta Electrónica Oficial</strong> en tu correo institucional.
                                    </div>
                                </div>
                            </div>

                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Como resultado, recibirás en tu correo institucional el <strong>Informe de TI Apto</strong> para pasar a la sustentación pública.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfr-AXQ-NLEpO_2PGr8E8MVvHp8Cw8ook1hfqVB_WFlYpRw1Q/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Abrir Formulario de Revisión de TI
                            </a>
                        </div>
                    </div>

                    <!-- Hito 3: Fecha de Exposición -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">3</div>
                        <div class="pk-milestone-header">
                            <h4>Solicitud de Fecha de Exposición Pública de TI</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 55</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Fijación formal de la fecha y hora de la exposición pública de resultados ante el jurado calificador.
                            </p>
                            <ul>
                                <li>Informe de TI Apto en formato PDF emitido por los dictaminantes.</li>
                                <li>Diapositivas para la exposición en formato PPT o PDF.</li>
                                <li>Boleta electrónica oficial de la EESPP (no voucher).</li>
                            </ul>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">Fecha de Exposición de TI</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">55</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 50.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> Espera <strong>2 días hábiles</strong> a recibir tu <strong>Boleta Electrónica Oficial</strong> emitida por la EESPP para registrar la solicitud.
                                    </div>
                                </div>
                            </div>

                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfyasqxxCR72v7aSnmtpISOedxYQzqV11igfLnegIr_biXIew/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Solicitar Fecha de Exposición
                            </a>
                        </div>
                    </div>

                    <!-- Hito 4: RD Aprobación TI -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">4</div>
                        <div class="pk-milestone-header">
                            <h4>Resolución Directoral (R.D.) de Aprobación de TI</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 17</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Luego de sustentar y aprobar la exposición, se gestiona la emisión de la R.D. institucional que formaliza la aprobación del Trabajo de Investigación.
                            </p>
                            <ul>
                                <li>Informe de TI Apto en formato PDF.</li>
                                <li>Acta de Aprobación de Exposición de TI suscrita por el jurado.</li>
                                <li>Boleta electrónica oficial de la EESPP (no voucher).</li>
                            </ul>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">RD de Aprobación de TI</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">17</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 50.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> Espera <strong>2 días hábiles</strong> tu <strong>Boleta Electrónica Oficial</strong> enviada a tu correo institucional.
                                    </div>
                                </div>
                            </div>

                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Recibirás la R.D. en tu correo institucional el día lunes de la semana siguiente a la presentación formal de la solicitud.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeGQ3XQAjWD2IPRUQnHIGfreOzNmgs-GceyKXgaGPsRg4EngQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Solicitar R.D. de Aprobación de TI
                            </a>
                        </div>
                    </div>

                    <!-- Hito 5: Armado de Expediente -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">5</div>
                        <div class="pk-milestone-header">
                            <h4>Armado de Expediente de Graduación (Presencial)</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 500.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 18</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Presentación física y verificación integral de la documentación del estudiante en la Secretaría Académica de la institución.
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
                                        <strong class="pk-pago-field__value">ARMADO DE EXPEDIENTE DE TITULA</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">18</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 500.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                            </div>

                            <div class="pk-milestone-notice">
                                <i class="fa fa-info-circle"></i>
                                <span>Este trámite es de naturaleza presencial en ventanilla de Secretaría. Incluye la revisión de certificados oficiales, partida de nacimiento, constancia de no deudor y fotografías tamaño pasaporte. Adjunta la boleta electrónica oficial.</span>
                            </div>
                            <a href="{{ route('tramites') }}#derecho" class="pk-btn-form-cta">
                                <i class="fa fa-building-columns"></i> Ver Requisitos de Expediente Presencial
                            </a>
                        </div>
                    </div>

                    <!-- Hito 6: Repositorio Institucional -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">6</div>
                        <div class="pk-milestone-header">
                            <h4>Registro en el Repositorio Institucional Digital</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 50.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Código: 19</span>
                                <span class="pk-badge-time"><i class="fa fa-clock"></i> 30 días hábiles</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Depósito oficial de la producción académica en el Repositorio Digital de Acceso Abierto de la EESPP Pukllasunchis.
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
                                <li>Enlace o archivo de la versión definitiva del TI en WORD.</li>
                                <li>Formato de Autorización de Depósito y Declaración Jurada firmados.</li>
                                <li>Boleta electrónica oficial emitida por la EESPP (no voucher).</li>
                            </ul>

                            <!-- Tarjeta Destacada de Datos de Pago en Caja Cusco -->
                            <div class="pk-pago-box">
                                <div class="pk-pago-box__header">
                                    <i class="fa fa-cash-register"></i>
                                    <span>Indica obligatoriamente al cajero en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>:</span>
                                </div>
                                <div class="pk-pago-box__grid">
                                    <div class="pk-pago-field pk-pago-field--concepto">
                                        <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> 1. Concepto Exacto</span>
                                        <strong class="pk-pago-field__value">REGISTRO DEL TI AL REPOSITORIO</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--codigo">
                                        <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> 2. Código</span>
                                        <strong class="pk-pago-field__value">19</strong>
                                    </div>
                                    <div class="pk-pago-field pk-pago-field--monto">
                                        <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> 3. Monto a Pagar</span>
                                        <strong class="pk-pago-field__value">S/ 50.00</strong>
                                    </div>
                                </div>
                                <div class="pk-pago-box__instrucciones">
                                    <p><i class="fa fa-building-columns text-muted me-1"></i> Tipo de pago: <strong>Pago Ordinario</strong> a nombre de <strong>Asociación Pukllasunchis - EESPP</strong>.</p>
                                    <p><i class="fa fa-id-card text-muted me-1"></i> Identificación: Brinda tu <strong>DNI</strong> y tus nombres completos.</p>
                                </div>
                                <div class="pk-pago-box__alerta-voucher">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    <div>
                                        <strong>¡Atención con el comprobante!</strong> Espera <strong>2 días hábiles</strong> tu <strong>Boleta Electrónica Oficial</strong> enviada a tu correo institucional.
                                    </div>
                                </div>
                            </div>

                            <div class="pk-milestone-result">
                                <i class="fa fa-check-circle"></i>
                                <span>Al no registrar observaciones, recibirás el identificador permanente (URL Handle) de registro oficial en el repositorio.</span>
                            </div>
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLScT8tet1JGXD9XT8psVS5XR15L4gdwZsVwGktL5Bbl4eSoRjQ/viewform"
                               target="_blank" class="pk-btn-form-cta">
                                <i class="fa fa-arrow-up-right-from-square"></i> Registrar en Repositorio
                            </a>
                        </div>
                    </div>

                    <!-- Hito 7: Emisión de Diploma e Inscripción SUNEDU -->
                    <div class="pk-milestone-item">
                        <div class="pk-milestone-item__bullet">7</div>
                        <div class="pk-milestone-header">
                            <h4>Emisión de Diploma e Inscripción en SUNEDU</h4>
                            <div class="pk-milestone-meta">
                                <span class="pk-badge-fee"><i class="fa fa-receipt"></i> S/ 400.00 + S/ 200.00</span>
                                <span class="pk-badge-time"><i class="fa fa-tag"></i> Códs: 22 y 20</span>
                            </div>
                        </div>
                        <div class="pk-milestone-content">
                            <p>
                                Fase final que culmina con la entrega de tu diploma oficial y su reconocimiento ante la Superintendencia Nacional de Educación Superior Universitaria (SUNEDU).
                            </p>

                            <!-- Sub-etapa A: Emisión Diploma -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <h5 class="font-weight-bold text-dark mb-1">
                                    <i class="fa fa-stamp text-primary me-2"></i> A. Emisión de Diploma de Grado de Bachiller
                                </h5>
                                <p class="text-muted mb-2" style="font-size: 14px;">
                                    Plazo: 15 días hábiles. Se te citará presencialmente para la firma del libro de actas.
                                </p>

                                <div class="pk-pago-box my-2">
                                    <div class="pk-pago-box__header">
                                        <i class="fa fa-cash-register"></i>
                                        <span>Pago en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> para Diploma de Bachiller:</span>
                                    </div>
                                    <div class="pk-pago-box__grid">
                                        <div class="pk-pago-field pk-pago-field--concepto">
                                            <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> Concepto Exacto</span>
                                            <strong class="pk-pago-field__value">Emisión Diploma de Bachiller</strong>
                                        </div>
                                        <div class="pk-pago-field pk-pago-field--codigo">
                                            <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> Código</span>
                                            <strong class="pk-pago-field__value">22</strong>
                                        </div>
                                        <div class="pk-pago-field pk-pago-field--monto">
                                            <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> Monto</span>
                                            <strong class="pk-pago-field__value">S/ 400.00</strong>
                                        </div>
                                    </div>
                                </div>

                                <a href="https://forms.gle/oVAax2L4GiaPGTWp9" target="_blank" class="btn btn-sm btn-outline-dark mt-2">
                                    <i class="fa fa-file-signature me-1"></i> Formulario de Solicitud de Emisión de Diploma
                                </a>
                            </div>

                            <!-- Sub-etapa B: Inscripción SUNEDU -->
                            <div class="p-3 bg-light rounded-3 border">
                                <h5 class="font-weight-bold text-dark mb-1">
                                    <i class="fa fa-award text-success me-2"></i> B. Registro e Inscripción ante SUNEDU
                                </h5>
                                <p class="text-muted mb-2" style="font-size: 14px;">
                                    Duración: 30 días hábiles posteriores a la firma presencial.
                                </p>

                                <div class="pk-pago-box my-2">
                                    <div class="pk-pago-box__header">
                                        <i class="fa fa-cash-register"></i>
                                        <span>Pago en ventanilla de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> para Registro SUNEDU:</span>
                                    </div>
                                    <div class="pk-pago-box__grid">
                                        <div class="pk-pago-field pk-pago-field--concepto">
                                            <span class="pk-pago-field__label"><i class="fa fa-tag text-primary"></i> Concepto Exacto</span>
                                            <strong class="pk-pago-field__value">REGISTRO E INSCRIPCION DE BACHILLER</strong>
                                        </div>
                                        <div class="pk-pago-field pk-pago-field--codigo">
                                            <span class="pk-pago-field__label"><i class="fa fa-barcode"></i> Código</span>
                                            <strong class="pk-pago-field__value">20</strong>
                                        </div>
                                        <div class="pk-pago-field pk-pago-field--monto">
                                            <span class="pk-pago-field__label"><i class="fa fa-money-bill-wave"></i> Monto</span>
                                            <strong class="pk-pago-field__value">S/ 200.00</strong>
                                        </div>
                                    </div>
                                </div>

                                <a href="https://forms.gle/L7WHbS67NKYnWug69" target="_blank" class="btn btn-sm btn-outline-dark mt-2">
                                    <i class="fa fa-globe me-1"></i> Formulario de Inscripción en SUNEDU (Correo institucional)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deck de Formatos y Guías Oficiales -->
                <h3 class="mt-5 linea-debajo">Formatos y Carátulas Oficiales</h3>
                <div class="pk-downloads-deck mb-4">
                    <!-- Formato TI -->
                    <a href="{{ asset('pdf/FormatoTI.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Word (.docx)</span>
                        </div>
                        <h5>Formato de TI Oficial</h5>
                        <p>Estructura general con capítulos, tablas e índices normalizados.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Formato
                        </span>
                    </a>

                    <!-- Carátula FID -->
                    <a href="{{ asset('pdf/CaratulaTI-2025.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Carátula FID</span>
                        </div>
                        <h5>Carátula TI (5 años)</h5>
                        <p>Para estudiantes de Formación Inicial Docente Regular.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Carátula
                        </span>
                    </a>

                    <!-- Carátula PPD -->
                    <a href="{{ asset('pdf/CaratulaTI-2025 PPD.docx') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon word">
                                <i class="fa fa-file-word"></i>
                            </div>
                            <span class="pk-download-card__tag">Carátula PPD</span>
                        </div>
                        <h5>Carátula TI (1 año)</h5>
                        <p>Para egresados del Programa de Profesionalización Docente.</p>
                        <span class="pk-download-card__btn">
                            <i class="fa fa-download"></i> Descargar Carátula
                        </span>
                    </a>

                    <!-- Guía de TI -->
                    <a href="{{ asset('pdf/Guia-Trabajo-de-Investigacion.pdf') }}" target="_blank" class="pk-download-card">
                        <div class="pk-download-card__icon-wrapper">
                            <div class="pk-download-card__icon pdf">
                                <i class="fa fa-file-pdf"></i>
                            </div>
                            <span class="pk-download-card__tag">PDF Guía</span>
                        </div>
                        <h5>Guía de Elaboración TI</h5>
                        <p>Criterios metodológicos, recolección de datos y análisis empírico.</p>
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
                        <p>Normas APA 7ma edición y directivas tipográficas de la institución.</p>
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
                            <h3>Pagos del Proceso de Bachillerato en <span class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</span></h3>
                            <p>Realiza tus abonos exclusivamente mediante pago ordinario en ventanilla.</p>
                        </div>
                    </div>

                    <div class="pk-caja-alert">
                        <i class="fa fa-triangle-exclamation text-warning"></i>
                        <span>Recuerda que para adjuntar tus comprobantes a los formularios debes esperar tu <strong>boleta electrónica institucional</strong> (llega en 2 días hábiles), no el voucher impreso de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>.</span>
                    </div>

                    <div class="pk-caja-points">
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">1</div>
                            <p>Indica en ventanilla: <strong>PAGO ORDINARIO</strong> de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">2</div>
                            <p>Indica obligatoriamente los 3 datos: <strong>Concepto exacto</strong>, <strong>Código numérico</strong> y <strong>Monto</strong> (Turnitin TI: Cód. 16 - S/ 25.00, Revisión TI: Cód. 15 - S/ 100.00, Exposición: Cód. 55 - S/ 50.00, RD Aprobación: Cód. 17 - S/ 50.00, Repositorio: Cód. 19 - S/ 50.00, Diploma Bachiller: Cód. 22 - S/ 400.00, Registro Bachiller: Cód. 20 - S/ 200.00).</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">3</div>
                            <p>Dicta el número de <strong>DNI</strong> y los nombres y apellidos completos del estudiante.</p>
                        </div>
                        <div class="pk-caja-point-item">
                            <div class="pk-point-num">4</div>
                            <p>Guarda la boleta electrónica PDF emitida por la EESPP que llegará a tu correo institucional.</p>
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

                <!-- Navegación entre etapas -->
                <div class="pk-etapas-nav mt-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('plan') }}" class="pk-etapas-nav-card pk-etapas-nav-prev">
                                <div class="pk-etapas-nav-info">
                                    <span class="pk-etapas-nav-badge">Etapa Anterior</span>
                                    <h4 class="pk-etapas-nav-title">Fase 1: Plan de Trabajo (PTI)</h4>
                                    <p class="pk-etapas-nav-desc">Requisitos iniciales y asignación de asesor institucional.</p>
                                </div>
                                <span class="pk-etapas-nav-btn-outline">
                                    <i class="fa fa-arrow-left"></i>
                                    <span>Volver a Fase 1</span>
                                </span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('tesis') }}" class="pk-etapas-nav-card pk-etapas-nav-next">
                                <div class="pk-etapas-nav-info">
                                    <span class="pk-etapas-nav-badge">Siguiente Etapa</span>
                                    <h4 class="pk-etapas-nav-title">Fase 3: Tesis Profesional</h4>
                                    <p class="pk-etapas-nav-desc">Ruta final para la obtención del Título Profesional de Licenciado.</p>
                                </div>
                                <span class="pk-etapas-nav-btn">
                                    <span>Ir a Fase 3</span>
                                    <i class="fa fa-arrow-right"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
