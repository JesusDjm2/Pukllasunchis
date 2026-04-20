@extends(auth()->check() && auth()->user()->hasRole('admin') ? 'layouts.admin' : 'layouts.bolsa')
@section('titulo', 'Ofertas Bolsa de Trabajo')
@section('contenido')
    <div class="container-fluid bg-white pt-3 bolsa-admin-ofertas">
        <div class="d-sm-flex align-items-center justify-content-between mb-3 flex-wrap">
            <h4 class="text-primary font-weight-bold mb-2 mb-sm-0">Registros públicos (ofertas / convocatorias)</h4>
            <div>
                <a href="{{ route('bolsa') }}" class="btn btn-sm btn-info mr-1 mb-1" target="_blank"
                    rel="noopener noreferrer">Página bolsa de trabajo</a>
                <a href="{{ route('index') }}" class="btn btn-sm btn-secondary mb-1" target="_blank"
                    rel="noopener noreferrer">Inicio web</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @php
            $qsEditOferta = http_build_query(
                array_filter(request()->only(['anio', 'mes']), fn($v) => $v !== null && $v !== ''),
            );
        @endphp

        <form method="get" action="{{ route('bolsa-trabajo.ofertas.index') }}" class="form-inline flex-wrap mb-3">
            <label class="mr-2 small font-weight-bold">Año</label>
            <select name="anio" class="form-control form-control-sm mr-3 mb-2">
                <option value="">Todos</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}
                    </option>
                @endforeach
            </select>
            <label class="mr-2 small font-weight-bold">Mes</label>
            <select name="mes" class="form-control form-control-sm mr-3 mb-2">
                <option value="">Todos</option>
                @foreach ($mesesNombres as $num => $label)
                    <option value="{{ $num }}" {{ (int) request('mes') === $num ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm mb-2">Filtrar</button>
            @if (request()->hasAny(['anio', 'mes']))
                <a href="{{ route('bolsa-trabajo.ofertas.index') }}"
                    class="btn btn-outline-secondary btn-sm mb-2 ml-2">Limpiar</a>
            @endif
        </form>

        <style>
            .bolsa-admin-ofertas .bolsa-oferta-img-wrap {
                position: relative;
                width: 100%;
                max-width: 240px;
                height: 200px;
                overflow: hidden;
                cursor: zoom-in;
                background: #e9ecef;
                border-radius: 0.35rem;
            }

            .bolsa-admin-ofertas .bolsa-oferta-img-wrap:focus {
                outline: 2px solid #4e73df;
                outline-offset: 2px;
            }

            .bolsa-admin-ofertas .bolsa-oferta-img-wrap img.bolsa-oferta-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
                display: block;
            }

            .bolsa-admin-ofertas .bolsa-oferta-sin-img {
                width: 100%;
                max-width: 240px;
                height: 200px;
                min-height: 160px;
                background: linear-gradient(145deg, #eef1f5 0%, #dee3ea 100%);
                border: 1px dashed #ced4da;
                border-radius: 0.35rem;
                color: #6c757d;
            }

            .bolsa-admin-ofertas .contenido-rico-bolsa {
                font-size: 0.9rem;
                line-height: 1.45;
                max-height: 240px;
                overflow-y: auto;
                padding-right: 0.25rem;
            }

            .bolsa-admin-ofertas .contenido-rico-bolsa img {
                max-width: 100%;
                height: auto;
            }

            .bolsa-admin-ofertas .contenido-rico-bolsa p:last-child {
                margin-bottom: 0;
            }

            .bolsa-admin-ofertas-table td {
                vertical-align: top;
            }

            .bolsa-admin-ofertas-table .col-imagen {
                width: 10%;
                white-space: nowrap;
            }

            .bolsa-admin-ofertas-table .col-acciones {
                width: 1%;
                min-width: 130px;
            }

            #bolsaAdminLightboxOverlay {
                position: fixed;
                inset: 0;
                z-index: 20050;
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

            #bolsaAdminLightboxOverlay.is-open {
                background: rgba(0, 0, 0, 0.88);
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            #bolsaAdminLightboxOverlay img {
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

            #bolsaAdminLightboxOverlay.is-open img {
                transform: scale(1) translateY(0);
                opacity: 1;
            }

            #bolsaAdminLightboxClose {
                position: fixed;
                top: 0.75rem;
                right: 1rem;
                z-index: 20051;
                font-size: 2.75rem;
                line-height: 1;
                color: #fff;
                background: transparent;
                border: 0;
                cursor: pointer;
                padding: 0.25rem 0.5rem;
                text-shadow: 0 1px 4px #000;
            }

            #bolsaAdminLightboxClose:hover {
                color: #f8f9fa;
            }

            @media (prefers-reduced-motion: reduce) {

                #bolsaAdminLightboxOverlay,
                #bolsaAdminLightboxOverlay img {
                    transition: none !important;
                }
            }
        </style>
        
        <div id="bolsaAdminLightboxOverlay" role="dialog" aria-modal="true" aria-label="Vista ampliada de imagen"
            onclick="if (event.target === this) bolsaAdminLightboxClose();">
            <button type="button" id="bolsaAdminLightboxClose" onclick="bolsaAdminLightboxClose();"
                aria-label="Cerrar">&times;</button>
            <img id="bolsaAdminLightboxImg" src="" alt="">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover bolsa-admin-ofertas-table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="col-imagen">Imagen</th>
                        <th>Registro (vista previa)</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ofertas as $o)
                        <tr>
                            <td class="col-imagen p-2">
                                @if ($o->imagen)
                                    <div class="bolsa-oferta-img-wrap bolsa-admin-oferta-img-trigger" role="button"
                                        tabindex="0" data-full="{{ e(asset($o->imagen)) }}"
                                        title="Clic para ampliar">
                                        <img src="{{ asset($o->imagen) }}" class="bolsa-oferta-img"
                                            alt="{{ $o->nombre }}" loading="lazy">
                                    </div>
                                @else
                                    <div
                                        class="bolsa-oferta-sin-img d-flex flex-column align-items-center justify-content-center px-2">
                                        <i class="fas fa-image fa-2x mb-2 opacity-50" aria-hidden="true"></i>
                                        <span class="small font-weight-bold text-uppercase"
                                            style="letter-spacing: .05em;">Sin imagen</span>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                    <h5 class="mb-1 pr-2">{{ $o->nombre }}</h5>
                                    <span class="badge badge-info text-wrap">
                                        {{ $mesesNombres[$o->mes] ?? $o->mes }} {{ $o->anio }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <strong>Vigencia:</strong>
                                    {{ $o->fecha_inicio?->format('d/m/Y') }}
                                    —
                                    {{ $o->fecha_fin?->format('d/m/Y') }}
                                </p>
                                <div class="contenido-rico-bolsa border rounded bg-light p-2">
                                    {!! $o->detalles !!}
                                </div>
                            </td>
                            <td class="col-acciones p-2">
                                <a href="{{ route('bolsa-trabajo.ofertas.edit', $o) }}{{ $qsEditOferta !== '' ? '?' . $qsEditOferta : '' }}"
                                    class="btn btn-warning btn-sm btn-block mb-2">Editar</a>
                                <form action="{{ route('bolsa-trabajo.ofertas.destroy', $o) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este registro?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="anio" value="{{ request('anio') }}">
                                    <input type="hidden" name="mes" value="{{ request('mes') }}">
                                    <button type="submit" class="btn btn-danger btn-sm btn-block">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function() {
            var bolsaAdminLightboxCloseTimer = null;

            window.bolsaAdminLightboxOpen = function(src) {
                var overlay = document.getElementById('bolsaAdminLightboxOverlay');
                var img = document.getElementById('bolsaAdminLightboxImg');
                if (!overlay || !img) return;
                if (bolsaAdminLightboxCloseTimer) {
                    clearTimeout(bolsaAdminLightboxCloseTimer);
                    bolsaAdminLightboxCloseTimer = null;
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
            };

            window.bolsaAdminLightboxClose = function() {
                var overlay = document.getElementById('bolsaAdminLightboxOverlay');
                var img = document.getElementById('bolsaAdminLightboxImg');
                if (!overlay || !img) return;
                if (!overlay.classList.contains('is-open')) {
                    return;
                }
                overlay.classList.remove('is-open');
                bolsaAdminLightboxCloseTimer = setTimeout(function() {
                    img.removeAttribute('src');
                    img.alt = '';
                    document.body.style.overflow = '';
                    bolsaAdminLightboxCloseTimer = null;
                }, 400);
            };

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.bolsa-admin-oferta-img-trigger').forEach(function(el) {
                    el.addEventListener('click', function() {
                        bolsaAdminLightboxOpen(this.getAttribute('data-full'));
                    });
                    el.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            bolsaAdminLightboxOpen(this.getAttribute('data-full'));
                        }
                    });
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') bolsaAdminLightboxClose();
                });
            });
        })();
    </script>
@endsection
