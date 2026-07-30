@extends('layouts.home')

@section('metas')
    @php $titulo = 'Libro de Reclamaciones'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Registra tu reclamo o queja ante la EESP Pukllasunchis a través de nuestro Libro de Reclamaciones virtual.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasunchis, EESP Pukllasunchis, reclamos, quejas">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('contenido')
    <section class="lr-hero">
        <div class="container">
            <span class="lr-hero__eyebrow"><i class="fa-solid fa-book" aria-hidden="true"></i> Atención al usuario</span>
            <h1 class="lr-hero__title">Libro de Reclamaciones</h1>
            <p class="lr-hero__subtitle">
                {{ config('libro_reclamaciones.razon_social') }} pone a tu disposición este canal para registrar
                reclamos y quejas sobre nuestros servicios educativos y administrativos.
            </p>
            <div class="lr-hero__meta">
                <span><i class="fa-solid fa-building" aria-hidden="true"></i> RUC {{ config('libro_reclamaciones.ruc') }}</span>
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ config('libro_reclamaciones.direccion') }}</span>
            </div>
        </div>
    </section>

    <section class="lr-form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">

                    <div class="lr-topblock" data-lr-reveal>
                        @if (session('error'))
                            <div class="lr-alert lr-alert--danger" role="alert">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                <div>{{ session('error') }}</div>
                                <button type="button" class="lr-alert__close" onclick="this.parentElement.remove()" aria-label="Cerrar">&times;</button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="lr-alert lr-alert--danger" role="alert">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                <div>
                                    <strong>Por favor corrige los siguientes errores:</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="lr-alert__close" onclick="this.parentElement.remove()" aria-label="Cerrar">&times;</button>
                            </div>
                        @endif

                        <p class="lr-form-intro">
                            Completa el formulario con tus datos. Los campos marcados con
                            <span class="text-danger fw-bold">*</span> son obligatorios.
                        </p>
                    </div>

                    <form action="{{ route('reclamos.public.store') }}" method="POST" enctype="multipart/form-data" id="reclamoForm">
                        @csrf
                        <input type="hidden" name="ts_form" value="{{ now()->timestamp }}">
                        <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                            <label for="sitio_web">Sitio web (no llenar)</label>
                            <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
                        </div>

                        {{-- 1. Datos del reclamante --}}
                        <div class="lr-card" data-lr-reveal>
                            <div class="lr-card__head">
                                <span class="lr-card__badge">1</span>
                                <div>
                                    <h2 class="lr-card__title">Datos del reclamante</h2>
                                    <p class="lr-card__hint">Información de contacto para dar seguimiento a tu caso.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="lr-label">Nombre completo <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control lr-input @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="lr-label">DNI <span class="text-danger">*</span></label>
                                    <input type="text" name="dni" class="form-control lr-input @error('dni') is-invalid @enderror" value="{{ old('dni') }}">
                                    @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Domicilio <span class="text-danger">*</span></label>
                                    <input type="text" name="domicilio" class="form-control lr-input @error('domicilio') is-invalid @enderror" value="{{ old('domicilio') }}">
                                    @error('domicilio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="lr-label">Teléfono <span class="text-danger">*</span></label>
                                    <input type="text" name="telefono" class="form-control lr-input @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}">
                                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="lr-label">Correo <span class="text-danger">*</span></label>
                                    <input type="email" name="correo" class="form-control lr-input @error('correo') is-invalid @enderror" value="{{ old('correo') }}">
                                    @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Condición del reclamante <span class="text-danger">*</span></label>
                                    <select name="condicion_reclamante" id="selCondicion" class="form-select lr-input @error('condicion_reclamante') is-invalid @enderror">
                                        <option value="">— Seleccione —</option>
                                        @foreach (['Estudiante', 'Egresado', 'Postulante', 'Padre de familia', 'Público en general'] as $c)
                                            <option value="{{ $c }}" {{ old('condicion_reclamante') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                    @error('condicion_reclamante') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 2. Datos académicos --}}
                        <div class="lr-card d-none" id="bloqueAcademico" data-lr-reveal>
                            <div class="lr-card__head">
                                <span class="lr-card__badge">2</span>
                                <div>
                                    <h2 class="lr-card__title">Datos académicos</h2>
                                    <p class="lr-card__hint">Solo si tu reclamo está relacionado a un programa de estudios.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="lr-label">Programa</label>
                                    <select name="programa" class="form-select lr-input">
                                        <option value="">— Seleccione —</option>
                                        @foreach ($programas as $p)
                                            <option value="{{ $p->nombre }}" {{ old('programa') == $p->nombre ? 'selected' : '' }}>{{ $p->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="lr-label">Ciclo</label>
                                    <input type="text" name="ciclo" class="form-control lr-input" value="{{ old('ciclo') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="lr-label">Código de estudiante</label>
                                    <input type="text" name="codigo_estudiante" class="form-control lr-input" value="{{ old('codigo_estudiante') }}">
                                </div>
                            </div>
                        </div>

                        {{-- 3. Identificación del servicio --}}
                        <div class="lr-card" data-lr-reveal>
                            <div class="lr-card__head">
                                <span class="lr-card__badge">3</span>
                                <div>
                                    <h2 class="lr-card__title">Identificación del servicio</h2>
                                    <p class="lr-card__hint">Cuéntanos sobre qué servicio o trámite trata tu reclamo.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="lr-label">Tipo de servicio <span class="text-danger">*</span></label>
                                    <select name="tipo_servicio" class="form-select lr-input @error('tipo_servicio') is-invalid @enderror">
                                        <option value="">— Seleccione —</option>
                                        @foreach (['Servicio educativo', 'Trámite administrativo', 'Otro servicio'] as $t)
                                            <option value="{{ $t }}" {{ old('tipo_servicio') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipo_servicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="lr-label">Área involucrada <span class="text-danger">*</span></label>
                                    <select name="area_involucrada" class="form-select lr-input @error('area_involucrada') is-invalid @enderror">
                                        <option value="">— Seleccione —</option>
                                        @foreach ($areas as $a)
                                            <option value="{{ $a }}" {{ old('area_involucrada') == $a ? 'selected' : '' }}>{{ $a }}</option>
                                        @endforeach
                                    </select>
                                    @error('area_involucrada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Descripción del servicio</label>
                                    <textarea name="descripcion_servicio" rows="2" class="form-control lr-input">{{ old('descripcion_servicio') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Servicio contratado</label>
                                    <textarea name="servicio_contratado" rows="2" class="form-control lr-input"
                                        placeholder="Tipo y descripción del servicio contratado">{{ old('servicio_contratado') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Detalle de la reclamación --}}
                        <div class="lr-card" data-lr-reveal>
                            <div class="lr-card__head">
                                <span class="lr-card__badge">4</span>
                                <div>
                                    <h2 class="lr-card__title">Detalle de la reclamación</h2>
                                    <p class="lr-card__hint">Describe con claridad lo sucedido y qué solicitas.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="lr-label d-block">Tipo de reclamación <span class="text-danger">*</span></label>
                                    <div class="lr-radio-group">
                                        @foreach (['Reclamo', 'Queja'] as $tr)
                                            <label class="lr-radio">
                                                <input type="radio" name="tipo_reclamacion" value="{{ $tr }}" {{ old('tipo_reclamacion') == $tr ? 'checked' : '' }}>
                                                <span>{{ $tr }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('tipo_reclamacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Descripción de los hechos <span class="text-danger">*</span></label>
                                    <textarea name="descripcion_hechos" rows="5"
                                        class="form-control lr-input @error('descripcion_hechos') is-invalid @enderror"
                                        placeholder="Describe con detalle qué ocurrió, cuándo, dónde y cualquier otra información relevante…">{{ old('descripcion_hechos') }}</textarea>
                                    @error('descripcion_hechos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="lr-hint">Mínimo 10 caracteres.</small>
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">Pedido <span class="text-danger">*</span></label>
                                    <textarea name="pedido" rows="3"
                                        class="form-control lr-input @error('pedido') is-invalid @enderror"
                                        placeholder="¿Qué solicitas?">{{ old('pedido') }}</textarea>
                                    @error('pedido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="lr-label">
                                        Adjuntar evidencia
                                        <span class="lr-hint">(opcional — PDF, JPG o PNG, máx. 3 MB)</span>
                                    </label>
                                    <input type="file" name="adjunto" id="reclamoAdjunto" accept=".pdf,.jpg,.jpeg,.png"
                                        class="form-control lr-input @error('adjunto') is-invalid @enderror">
                                    <small class="lr-hint" id="reclamoAdjuntoLabel">Ningún archivo seleccionado.</small>
                                    @error('adjunto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 5. Consentimientos --}}
                        <div class="lr-card" data-lr-reveal>
                            <div class="lr-card__head">
                                <span class="lr-card__badge">5</span>
                                <div>
                                    <h2 class="lr-card__title">Consentimientos</h2>
                                    <p class="lr-card__hint">Protección de datos y conformidad.</p>
                                </div>
                            </div>

                            <div class="lr-check">
                                <input type="checkbox" name="declara_informacion_verdadera" id="chkVerdad" value="1"
                                    class="@error('declara_informacion_verdadera') is-invalid @enderror"
                                    {{ old('declara_informacion_verdadera') ? 'checked' : '' }}>
                                <label for="chkVerdad">Declaro que la información proporcionada es verdadera. <span class="text-danger">*</span></label>
                            </div>
                            @error('declara_informacion_verdadera') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                            <div class="lr-check">
                                <input type="checkbox" name="autoriza_tratamiento_datos" id="chkDatos" value="1"
                                    class="@error('autoriza_tratamiento_datos') is-invalid @enderror"
                                    {{ old('autoriza_tratamiento_datos') ? 'checked' : '' }}>
                                <label for="chkDatos">Autorizo el tratamiento de mis datos personales para la atención del reclamo. <span class="text-danger">*</span></label>
                            </div>
                            @error('autoriza_tratamiento_datos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                            <div class="lr-check">
                                <input type="checkbox" name="confirma_lectura_libro" id="chkLectura" value="1"
                                    class="@error('confirma_lectura_libro') is-invalid @enderror"
                                    {{ old('confirma_lectura_libro') ? 'checked' : '' }}>
                                <label for="chkLectura">Confirmo haber leído la información del Libro de Reclamaciones. <span class="text-danger">*</span></label>
                            </div>
                            @error('confirma_lectura_libro') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-center mt-4 mb-5" data-lr-reveal>
                            <button type="submit" class="lr-submit" id="reclamoSubmit">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enviar reclamación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .lr-hero {
            background: #33445a;
            color: #fff;
            padding: 4.5rem 0 5.5rem;
            text-align: center;
        }

        .lr-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            padding: .4rem .95rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }

        .lr-hero__title {
            font-size: clamp(1.9rem, 4vw, 2.6rem);
            font-weight: 700;
            margin-bottom: .75rem;
            color: #cd9244;
        }

        .lr-hero__subtitle {
            max-width: 640px;
            margin: 0 auto;
            font-size: 1rem;
            color: rgba(255, 255, 255, .85);
            line-height: 1.6;
        }

        .lr-hero__meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 1.75rem;
            font-size: .85rem;
            color: rgba(255, 255, 255, .85);
        }

        .lr-hero__meta span {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .lr-form-section {
            background: #f6f7fb;
            padding: 0 0 3rem;
        }

        .lr-topblock {
            margin: -2.75rem 0 1.75rem;
        }

        .lr-topblock .lr-alert {
            margin-bottom: 1rem;
        }

        .lr-form-intro {
            font-size: .9rem;
            color: #6a7386;
            margin: 0;
            background: #fff;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            box-shadow: 0 10px 30px rgba(20, 30, 55, .08);
        }

        .lr-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 34px rgba(20, 30, 55, .07);
            padding: 1.75rem 1.75rem 2rem;
            margin-bottom: 1.5rem;
        }

        .lr-card__head {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .lr-card__badge {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #cd9244, #b87a2a);
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(205, 146, 68, .35);
        }

        .lr-card__title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #26314a;
            margin-bottom: .15rem;
        }

        .lr-card__hint {
            font-size: .82rem;
            color: #8991a3;
            margin-bottom: 0;
        }

        .lr-label {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #52658c;
            margin-bottom: .4rem;
            display: inline-block;
        }

        .lr-hint {
            font-size: .78rem;
            color: #97a0b2;
            font-weight: 400;
            text-transform: none;
            letter-spacing: normal;
        }

        .lr-input {
            border: 1px solid #dfe2ea;
            border-radius: 10px;
            padding: .6rem .85rem;
            font-size: .92rem;
        }

        .lr-input:focus {
            border-color: #cd9244;
            box-shadow: 0 0 0 .2rem rgba(205, 146, 68, .15);
        }

        .lr-radio-group {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .lr-radio {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid #dfe2ea;
            border-radius: 999px;
            padding: .5rem 1.1rem;
            font-size: .88rem;
            font-weight: 600;
            color: #52658c;
            cursor: pointer;
            transition: all .18s ease;
        }

        .lr-radio:has(input:checked) {
            border-color: #cd9244;
            background: rgba(205, 146, 68, .08);
            color: #b87a2a;
        }

        .lr-check {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            margin-bottom: .85rem;
            font-size: .88rem;
            color: #3d4658;
        }

        .lr-check input {
            margin-top: .3rem;
        }

        .lr-check label {
            cursor: pointer;
        }

        .lr-submit {
            background: #33445a;
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            padding: .85rem 2.75rem;
            border-radius: 999px;
            box-shadow: 0 10px 26px rgba(51, 68, 90, .35);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .lr-submit:hover,
        .lr-submit:focus-visible {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(51, 68, 90, .45);
            color: #fff;
        }

        .lr-submit:disabled {
            opacity: .7;
            transform: none;
        }

        .lr-alert {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            border-radius: 14px;
            padding: 1rem 1.1rem;
            font-size: .88rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 26px rgba(20, 30, 55, .08);
        }

        .lr-alert--danger {
            background: #fdedee;
            color: #a3283a;
            border: 1px solid #f6cdd2;
        }

        .lr-alert__close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 1.1rem;
            line-height: 1;
            color: inherit;
            opacity: .6;
        }

        .lr-alert__close:hover {
            opacity: 1;
        }

        @media (max-width: 767.98px) {
            .lr-hero {
                padding: 3.25rem 0 4.5rem;
            }

            .lr-card {
                padding: 1.4rem 1.15rem 1.6rem;
            }

            .lr-topblock {
                margin-top: -2.25rem;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Reclamación registrada!',
                html: 'N° de reclamo: <strong>{{ session('success') }}</strong>.<br>Te enviamos una constancia en PDF a tu correo.',
                timer: 6000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif

        var selCondicion = document.getElementById('selCondicion');
        var bloqueAcademico = document.getElementById('bloqueAcademico');

        function toggleAcademico() {
            var mostrar = ['Estudiante', 'Egresado', 'Postulante'].indexOf(selCondicion.value) !== -1;
            bloqueAcademico.classList.toggle('d-none', !mostrar);
            if (!mostrar) {
                bloqueAcademico.querySelectorAll('input, select').forEach(function(campo) {
                    campo.value = '';
                });
            }
        }

        selCondicion.addEventListener('change', toggleAcademico);
        toggleAcademico();

        var inputAdjunto = document.getElementById('reclamoAdjunto');
        var adjuntoLabel = document.getElementById('reclamoAdjuntoLabel');
        inputAdjunto.addEventListener('change', function() {
            var file = this.files[0];
            adjuntoLabel.textContent = file ? file.name : 'Ningún archivo seleccionado.';
        });

        var form = document.getElementById('reclamoForm');
        var submitBtn = document.getElementById('reclamoSubmit');
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando…';
        });

        if (typeof gsap !== 'undefined') {
            gsap.from('.lr-hero__eyebrow, .lr-hero__title, .lr-hero__subtitle, .lr-hero__meta', {
                opacity: 0,
                y: 24,
                duration: .6,
                stagger: .1,
                ease: 'power2.out'
            });

            var reveals = document.querySelectorAll('[data-lr-reveal]');
            if (typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);
                reveals.forEach(function(el) {
                    gsap.from(el, {
                        opacity: 0,
                        y: 28,
                        duration: .55,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: el,
                            start: 'top 88%'
                        }
                    });
                });
            } else {
                gsap.from(reveals, {
                    opacity: 0,
                    y: 20,
                    duration: .5,
                    stagger: .08,
                    ease: 'power2.out'
                });
            }
        }
    })();
    </script>
@endsection
