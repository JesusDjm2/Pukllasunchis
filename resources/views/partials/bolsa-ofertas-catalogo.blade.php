{{--
    Listado dinámico de bolsa_trabajo_ofertas (misma lógica que la página pública).
    Variables: $ofertas, $aniosOfertas, $mesesNombres; opcional $bolsaRouteName (default bolsa).
--}}
@php($bolsaRouteName = isset($bolsaRouteName) ? $bolsaRouteName : 'bolsa')
<div class="mb-5 bolsa-ofertas-catalogo-wrap">
    <form method="get" action="{{ route($bolsaRouteName) }}"
        class="row align-items-end mb-4 alumno-filter-panel alumno-bolsa-filters">
        <div class="col-md-4 mb-2">
            <label for="filtro_anio_bolsa" class="small font-weight-bold d-block text-uppercase"
                style="letter-spacing: .04em;">Año</label>
            <select name="anio" id="filtro_anio_bolsa" class="form-control form-control-sm">
                <option value="">Todos los años</option>
                @foreach ($aniosOfertas as $a)
                    <option value="{{ $a }}" {{ (int) request('anio') === (int) $a ? 'selected' : '' }}>
                        {{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label for="filtro_mes_bolsa" class="small font-weight-bold d-block text-uppercase"
                style="letter-spacing: .04em;">Mes</label>
            <select name="mes" id="filtro_mes_bolsa" class="form-control form-control-sm">
                <option value="">Todos los meses</option>
                @foreach ($mesesNombres as $num => $label)
                    <option value="{{ $num }}" {{ (int) request('mes') === (int) $num ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-filter mr-1"></i> Aplicar</button>
            @if (request()->hasAny(['anio', 'mes']))
                <a href="{{ route($bolsaRouteName) }}" class="btn btn-outline-secondary btn-sm ml-1">Limpiar</a>
            @endif
        </div>
    </form>

    <style>
        .bolsa-ofertas-catalogo-wrap .bolsa-oferta-img-wrap {
            position: relative;
            height: 280px;
            max-height: 40vh;
            overflow: hidden;
            cursor: zoom-in;
            background: #e9ecef;
            border-radius: 0.25rem 0 0 0.25rem;
        }

        @media (max-width: 767.98px) {
            .bolsa-ofertas-catalogo-wrap .bolsa-oferta-img-wrap {
                border-radius: 0.25rem 0.25rem 0 0;
                height: 220px;
                max-height: 35vh;
            }

            .bolsa-ofertas-catalogo-wrap .bolsa-oferta-sin-img {
                border-radius: 0.25rem 0.25rem 0 0 !important;
            }
        }

        .bolsa-ofertas-catalogo-wrap .bolsa-oferta-img-wrap:focus {
            outline: 2px solid #4e73df;
            outline-offset: 2px;
        }

        .bolsa-ofertas-catalogo-wrap .bolsa-oferta-img-wrap img.bolsa-oferta-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .bolsa-ofertas-catalogo-wrap .bolsa-oferta-sin-img {
            height: 280px;
            max-height: 40vh;
            min-height: 200px;
            background: linear-gradient(145deg, #eef1f5 0%, #dee3ea 100%);
            border: 1px dashed #ced4da;
            border-radius: 0.25rem 0 0 0.25rem;
            color: #6c757d;
        }

        @media (max-width: 767.98px) {
            .bolsa-ofertas-catalogo-wrap .bolsa-oferta-sin-img {
                height: 220px;
                max-height: 35vh;
            }
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay {
            position: fixed;
            inset: 0;
            z-index: 10050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem 1rem;
            box-sizing: border-box;
            background: rgba(0, 0, 0, 0);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: background 0.38s ease, opacity 0.38s ease, visibility 0.38s ease;
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay.is-open {
            background: rgba(0, 0, 0, 0.88);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay img {
            max-width: 100%;
            max-height: 90vh;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.55);
            transform: scale(0.94) translateY(8px);
            opacity: 0;
            transition: transform 0.42s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.38s ease,
                box-shadow 0.42s ease;
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay.is-open img {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxClose {
            position: fixed;
            top: 0.75rem;
            right: 1rem;
            z-index: 10051;
            font-size: 2.75rem;
            line-height: 1;
            color: #fff;
            background: transparent;
            border: 0;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            text-shadow: 0 1px 4px #000;
        }

        .bolsa-ofertas-catalogo-wrap #bolsaLightboxClose:hover {
            color: #f8f9fa;
        }

        .bolsa-ofertas-catalogo-wrap .contenido-rico-bolsa {
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .bolsa-ofertas-catalogo-wrap .contenido-rico-bolsa img {
            max-width: 100%;
            height: auto;
        }

        .bolsa-ofertas-catalogo-wrap .contenido-rico-bolsa p:last-child {
            margin-bottom: 0;
        }

        @media (prefers-reduced-motion: reduce) {

            .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay,
            .bolsa-ofertas-catalogo-wrap #bolsaLightboxOverlay img {
                transition: none !important;
            }
        }
    </style>

    <div id="bolsaLightboxOverlay" role="dialog" aria-modal="true" aria-label="Vista ampliada de imagen"
        onclick="if(event.target===this) bolsaLightboxClose();">
        <button type="button" id="bolsaLightboxClose" onclick="bolsaLightboxClose();" aria-label="Cerrar">&times;</button>
        <img id="bolsaLightboxImg" src="" alt="">
    </div>

    @forelse ($ofertas as $oferta)
        <article class="card shadow-sm mb-4 overflow-hidden alumno-bolsa-card">
            <div class="row no-gutters">
                <div class="col-md-4 col-12">
                    @if ($oferta->imagen)
                        <div class="bolsa-oferta-img-wrap bolsa-oferta-img-trigger" role="button" tabindex="0"
                            data-full="{{ e(asset($oferta->imagen)) }}" title="Clic para ver imagen completa">
                            <img src="{{ asset($oferta->imagen) }}" class="bolsa-oferta-img"
                                alt="{{ $oferta->nombre }}" loading="lazy">
                        </div>
                    @else
                        <div
                            class="bolsa-oferta-sin-img d-flex flex-column align-items-center justify-content-center px-3">
                            <i class="fas fa-image fa-3x mb-2 opacity-50" aria-hidden="true"></i>
                            <span class="small font-weight-bold text-uppercase" style="letter-spacing: .05em;">Sin
                                imagen</span>
                            <span class="small text-center mt-1" style="max-width: 12rem;">Este registro no incluye
                                imagen.</span>
                        </div>
                    @endif
                </div>
                <div class="col-md-8 col-12 alumno-bolsa-card-content">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                            <h4 class="card-title mb-0 alumno-bolsa-card-title">{{ $oferta->nombre }}</h4>
                            <span class="badge badge-info alumno-bolsa-period-badge">
                                {{ $mesesNombres[$oferta->mes] ?? $oferta->mes }} {{ $oferta->anio }}
                            </span>
                        </div>
                        <p class="text-muted small mb-2 alumno-bolsa-vigencia">
                            <strong>Vigencia:</strong>
                            {{ $oferta->fecha_inicio?->translatedFormat('d M Y') }}
                            —
                            {{ $oferta->fecha_fin?->translatedFormat('d M Y') }}
                        </p>
                        <div class="contenido-rico-bolsa">
                            {!! $oferta->detalles !!}
                        </div>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card border-0 shadow-sm text-center text-muted py-5 px-3 mb-0"
            style="border-radius: 12px; background: #f8fafc;">
            <i class="fas fa-briefcase fa-3x mb-3 opacity-25 d-block" aria-hidden="true"></i>
            <p class="mb-0 font-weight-bold text-secondary">Sin ofertas con estos filtros</p>
            <p class="small mb-0 mt-2">Prueba con otro año o mes, o limpia los filtros.</p>
        </div>
    @endforelse

    <script>
        var bolsaLightboxCloseTimer = null;

        function bolsaLightboxOpen(src) {
            var overlay = document.getElementById('bolsaLightboxOverlay');
            var img = document.getElementById('bolsaLightboxImg');
            if (!overlay || !img) return;
            if (bolsaLightboxCloseTimer) {
                clearTimeout(bolsaLightboxCloseTimer);
                bolsaLightboxCloseTimer = null;
            }
            overlay.classList.remove('is-open');
            img.removeAttribute('src');
            img.alt = '';
            void overlay.offsetWidth;
            img.src = src;
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    overlay.classList.add('is-open');
                });
            });
        }

        function bolsaLightboxClose() {
            var overlay = document.getElementById('bolsaLightboxOverlay');
            var img = document.getElementById('bolsaLightboxImg');
            if (!overlay || !img) return;
            if (!overlay.classList.contains('is-open')) {
                return;
            }
            overlay.classList.remove('is-open');
            bolsaLightboxCloseTimer = setTimeout(function() {
                img.removeAttribute('src');
                img.alt = '';
                document.body.style.overflow = '';
                bolsaLightboxCloseTimer = null;
            }, 400);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.bolsa-oferta-img-trigger').forEach(function(el) {
                el.addEventListener('click', function() {
                    bolsaLightboxOpen(this.getAttribute('data-full'));
                });
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        bolsaLightboxOpen(this.getAttribute('data-full'));
                    }
                });
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') bolsaLightboxClose();
            });
        });
    </script>
</div>
