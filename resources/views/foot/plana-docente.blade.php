@extends('layouts.home')
@section('metas')
    <title>Nuestra Plana Docente - EESPP Pukllasunchis</title>
    <meta name="description"
        content="Conoce al equipo de docentes de la Escuela de Educación Superior Pedagógica Privada Pukllasunchis.">
    <meta name="keywords" content="plana docente, docentes, EESP Pukllasunchis, educación intercultural">
@endsection
@section('contenido')
    <style>
        /* ── Breadcrumb ── */
        .bradcam_area.plana-docente-bg {
            background-image: url('{{ asset('img/fondos/plana-docente-2.png') }}') !important;
            background-size: cover !important;
            background-position: center !important;
        }

        /* ── Sección ── */
        .pd-section {
            padding: 5rem 0 6.5rem;
            background-color: #f8f9fc;
            background-image: radial-gradient(circle, rgba(13, 33, 55, .04) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Encabezado de sección */
        .pd-section-head {
            margin-bottom: 3.2rem;
        }

        .pd-section-tag {
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: #c9873f;
            background: rgba(201, 135, 63, .1);
            padding: .28rem 1rem;
            border-radius: 2rem;
            margin-bottom: .85rem;
        }

        .pd-section-title {
            font-size: clamp(1.55rem, 3vw, 2rem);
            font-weight: 800;
            color: #0d2137;
            line-height: 1.2;
            margin-bottom: .7rem;
        }

        .pd-section-sub {
            font-size: .93rem;
            color: #6b7280;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.65;
        }

        /* ── Card ── */
        .pd-card {
            background: #fff;
            border-radius: 1.1rem;
            box-shadow: 0 2px 16px rgba(13, 33, 55, .08);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: box-shadow .22s ease, transform .22s ease;
        }

        /* Barra dorada superior */
        .pd-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #c9873f, #e8b84b 60%, #c9873f);
            border-radius: 1.1rem 1.1rem 0 0;
            z-index: 2;
        }

        /* Franja lateral izquierda */
        .pd-card::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 0;
            width: 3px;
            bottom: 0;
            background: linear-gradient(180deg, #e8b84b 0%, #0d2137 100%);
            opacity: .18;
        }

        /* Body */
        .pd-body {
            padding: 1.8rem 1.5rem 1.5rem 1.6rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* Fila superior: avatar + nombre/chips */
        .pd-top-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: .9rem;
        }

        /* Avatar de iniciales */
        .pd-avatar {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d2137 0%, #1a4a6e 100%);
            border: 2.5px solid #e8b84b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            font-weight: 800;
            color: #e8b84b;
            letter-spacing: .04em;
            box-shadow: 0 3px 10px rgba(13, 33, 55, .2);
            will-change: transform;
        }

        .pd-title-col {
            flex: 1;
            min-width: 0;
        }

        .pd-nombre {
            font-size: 1rem;
            font-weight: 800;
            color: #0d2137;
            line-height: 1.3;
            margin-bottom: .42rem;
        }

        .pd-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            margin-bottom: 0;
        }

        .pd-chip {
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: .18rem .65rem;
            border-radius: 2rem;
        }

        .pd-chip-cargo {
            background: #e8f0fe;
            color: #1a56db;
        }

        .pd-chip-esp {
            background: #fef3c7;
            color: #92400e;
        }

        .pd-divider {
            border: none;
            border-top: 1px solid #f0f4f8;
            margin: 0 0 .9rem;
        }

        .pd-descripcion {
            font-size: .875rem;
            color: #4b5563;
            line-height: 1.7;
            flex: 1;
            margin-bottom: 1.3rem;
        }

        /* Botón CV */
        .pd-btn-cv {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            background: linear-gradient(135deg, #c9873f 0%, #e8b84b 100%);
            color: #0d2137 !important;
            font-weight: 800;
            font-size: .82rem;
            letter-spacing: .03em;
            padding: .62rem 1.4rem;
            border-radius: 3rem;
            border: none;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 4px 14px rgba(201, 135, 63, .28);
            transition: box-shadow .2s ease, filter .2s ease;
            text-decoration: none !important;
            position: relative;
            overflow: hidden;
        }

        .pd-btn-cv::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 55%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .26), transparent);
            transition: left .4s ease;
        }

        .pd-btn-cv:hover {
            filter: brightness(1.07);
            box-shadow: 0 6px 20px rgba(201, 135, 63, .4);
        }

        .pd-btn-cv:hover::after {
            left: 160%;
        }

        .pd-btn-cv.disabled-cv {
            background: #f3f4f6;
            color: #9ca3af !important;
            box-shadow: none;
            cursor: default;
            pointer-events: none;
        }

        /* ── Modal CV ── */
        #modalCV .modal-content {
            border-radius: .85rem;
            overflow: hidden;
            border: none;
        }

        #modalCV .modal-header {
            background: linear-gradient(135deg, #0d2137, #1a4a6e);
            color: #fff;
            border: none;
            padding: 1rem 1.5rem;
        }

        #modalCV .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        #modalCV .modal-header .close {
            color: #e8b84b;
            opacity: 1;
            text-shadow: none;
            font-size: 1.4rem;
        }

        #modalCV .modal-body {
            padding: 0;
            background: #f1f5f9;
        }

        #cv-iframe {
            width: 100%;
            height: 78vh;
            border: none;
            display: block;
        }

        #cv-no-disponible {
            display: none;
            padding: 3rem;
            text-align: center;
            color: #6b7280;
        }

        #cv-no-disponible svg {
            margin-bottom: 1rem;
        }
    </style>

    {{-- Breadcrumb --}}
    <div class="bradcam_area bradcam_overlay plana-docente-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">Nuestra Plana Docente</h3>
                        <p class="slideUp">
                            <a href="{{ route('index') }}">Inicio /</a>
                            <a href="{{ route('informacion') }}"> Información Institucional /</a>
                            Plana Docente
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de docentes --}}
    <section class="pd-section">
        <div class="container">

            {{-- Encabezado --}}
            <div class="pd-section-head text-center">
                <span class="pd-section-tag">Equipo Pedagógico</span>
                <h2 class="pd-section-title">Profesionales comprometidos<br>con la educación intercultural</h2>
                <p class="pd-section-sub">Nuestros docentes combinan experiencia académica y vocación pedagógica para formar
                    a los educadores del futuro.</p>
            </div>

            {{-- Cards --}}
            <div class="row justify-content-center pd-grid">
                @forelse ($docentes as $i => $doc)
                    @php
                        $iniciales = collect(explode(' ', $doc['nombre']))
                            ->map(fn($p) => strtoupper(substr($p, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-4 pd-card-col">
                        <div class="pd-card">
                            <div class="pd-body">

                                {{-- Fila superior: avatar + nombre/chips --}}
                                <div class="pd-top-row">
                                    <div class="pd-avatar">{{ $iniciales }}</div>
                                    <div class="pd-title-col">
                                        <h3 class="pd-nombre">{{ $doc['nombre'] }}</h3>
                                        <div class="pd-chips">
                                            <span class="pd-chip pd-chip-cargo">{{ $doc['cargo'] }}</span>
                                            <span class="pd-chip pd-chip-esp">{{ $doc['especialidad'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="pd-divider">
                                <p class="pd-descripcion">{{ $doc['descripcion'] }}</p>

                                @if (!empty($doc['cv']))
                                    <button class="pd-btn-cv"
                                        onclick="abrirCV('{{ asset($doc['cv']) }}', '{{ $doc['nombre'] }}')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                        </svg>
                                        Ver Curriculum Vitae
                                    </button>
                                @else
                                    <button class="pd-btn-cv disabled-cv" disabled>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                        </svg>
                                        CV no disponible
                                    </button>
                                @endif
                            </div>{{-- /.pd-body --}}
                        </div>{{-- /.pd-card --}}
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No hay docentes registrados.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Modal CV --}}
    <div class="modal fade" id="modalCV" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="modalCVLabel">Curriculum Vitae</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="cv-iframe" src="" title="CV Docente"></iframe>
                    <div id="cv-no-disponible">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <p>El CV de este docente aún no está disponible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            gsap.registerPlugin(ScrollTrigger);

            /* ── 1. Encabezado de sección ── */
            gsap.from('.pd-section-tag, .pd-section-title, .pd-section-sub', {
                y: 28,
                opacity: 0,
                duration: 0.7,
                stagger: 0.13,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.pd-section-head',
                    start: 'top 86%',
                },
            });

            /* ── 2. Cards: timeline individual por card ── */
            document.querySelectorAll('.pd-card-col').forEach(function(col) {
                var card = col.querySelector('.pd-card');
                var avatar = col.querySelector('.pd-avatar');
                if (!card) return;

                var tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: col,
                        start: 'top 88%',
                        once: true,
                    },
                });

                tl.from(card, {
                        y: 30,
                        opacity: 0,
                        duration: 0.55,
                        ease: 'power3.out',
                    })
                    .from(avatar, {
                        scale: 0,
                        duration: 0.45,
                        ease: 'elastic.out(1, 0.55)',
                    }, '-=0.2');

                /* ── 3. Hover GSAP ── */
                card.addEventListener('mouseenter', function() {
                    gsap.to(card, { y: -6, duration: 0.2, ease: 'power2.out' });
                    gsap.to(avatar, { scale: 1.08, duration: 0.2, ease: 'power2.out' });
                });
                card.addEventListener('mouseleave', function() {
                    gsap.to(card, { y: 0, duration: 0.2, ease: 'power2.out' });
                    gsap.to(avatar, { scale: 1, duration: 0.2, ease: 'power2.out' });
                });
            });

        }());

        /* ── Modal CV ── */
        function abrirCV(url, nombre) {
            document.getElementById('modalCVLabel').textContent = 'CV — ' + nombre;
            if (url) {
                document.getElementById('cv-iframe').src = url;
                document.getElementById('cv-iframe').style.display = 'block';
                document.getElementById('cv-no-disponible').style.display = 'none';
            } else {
                document.getElementById('cv-iframe').src = '';
                document.getElementById('cv-iframe').style.display = 'none';
                document.getElementById('cv-no-disponible').style.display = 'block';
            }
            $('#modalCV').modal('show');
        }

        document.getElementById('modalCV').addEventListener('hidden.bs.modal', function() {
            document.getElementById('cv-iframe').src = '';
        });
    </script>
    
@endsection
