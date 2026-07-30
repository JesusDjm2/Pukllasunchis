{{--
    Popup global de "Publicar oferta" (bolsa de trabajo).
    Se incluye una sola vez en layouts.home para que esté disponible
    en cualquier vista pública (inicio, listado, etc.) sin duplicar
    markup/IDs ni recargar TinyMCE más de una vez.
--}}
<style>
    #bolsaGlobalRegistroOverlay {
        position: fixed;
        inset: 0;
        z-index: 10060;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 1rem;
        overflow-y: auto;
        box-sizing: border-box;
        background: rgba(0, 0, 0, 0);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: background 0.34s ease, opacity 0.34s ease, visibility 0.34s ease;
    }

    #bolsaGlobalRegistroOverlay.is-open {
        background: rgba(0, 0, 0, 0.72);
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .bolsa-global-registro-modal-panel {
        position: relative;
        width: 100%;
        max-width: 720px;
        margin: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        padding: 1.5rem 1.5rem 2rem;
        transform: translateY(22px) scale(0.96);
        opacity: 0;
        transition: transform 0.42s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.36s ease,
            box-shadow 0.42s ease;
    }

    #bolsaGlobalRegistroOverlay.is-open .bolsa-global-registro-modal-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.28);
    }

    #bolsaGlobalRegistroModalClose {
        position: absolute;
        top: 0.5rem;
        right: 0.75rem;
        font-size: 2rem;
        line-height: 1;
        color: #333;
        background: transparent;
        border: 0;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        z-index: 2;
        opacity: 0.75;
        transition: opacity 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    #bolsaGlobalRegistroModalClose:hover {
        color: #cd9244;
        opacity: 1;
        transform: scale(1.06);
    }

    @media (max-width: 575.98px) {
        #bolsaGlobalRegistroOverlay {
            padding: 1rem 0.5rem;
        }

        .bolsa-global-registro-modal-panel {
            padding: 1.25rem 1rem 1.5rem;
            border-radius: 10px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        #bolsaGlobalRegistroOverlay,
        .bolsa-global-registro-modal-panel,
        #bolsaGlobalRegistroModalClose {
            transition: none !important;
        }

        #bolsaGlobalRegistroOverlay.is-open .bolsa-global-registro-modal-panel {
            transform: none;
        }
    }
</style>

<div id="bolsaGlobalRegistroOverlay" role="dialog" aria-modal="true" aria-labelledby="bolsaGlobalRegistroTitulo"
    onclick="if (event.target === this) bolsaGlobalRegistroModalClose();">
    <div class="bolsa-global-registro-modal-panel" onclick="event.stopPropagation();">
        <button type="button" id="bolsaGlobalRegistroModalClose" onclick="bolsaGlobalRegistroModalClose();"
            aria-label="Cerrar">&times;</button>
        <h4 id="bolsaGlobalRegistroTitulo" class="mb-3 pr-4">Nuevo registro — Bolsa de trabajo</h4>
        @if ($errors->any() && old('form_context') === 'bolsa_oferta')
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('partials.bolsa-oferta-registro-form', [
            'prefix' => 'bolsa_global',
            'redirectTo' => 'bolsa',
            'listadoAnio' => request('anio'),
            'listadoMes' => request('mes'),
        ])
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
    var bolsaGlobalTinyMceInited = false;
    var bolsaGlobalRegistroCloseTimer = null;

    function bolsaGlobalRegistroModalOpen() {
        var overlay = document.getElementById('bolsaGlobalRegistroOverlay');
        if (!overlay) return;
        if (bolsaGlobalRegistroCloseTimer) {
            clearTimeout(bolsaGlobalRegistroCloseTimer);
            bolsaGlobalRegistroCloseTimer = null;
        }
        document.body.style.overflow = 'hidden';
        if (!overlay.classList.contains('is-open')) {
            overlay.classList.remove('is-open');
            void overlay.offsetWidth;
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    overlay.classList.add('is-open');
                });
            });
        }
        if (!bolsaGlobalTinyMceInited && typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#bolsa_global_detalles',
                height: 280,
                menubar: false,
                plugins: 'lists link',
                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
                language: 'es',
                branding: false,
                promotion: false,
            });
            bolsaGlobalTinyMceInited = true;
        }
    }

    function bolsaGlobalRegistroModalClose() {
        var overlay = document.getElementById('bolsaGlobalRegistroOverlay');
        if (!overlay) return;
        if (!overlay.classList.contains('is-open')) {
            document.body.style.overflow = '';
            return;
        }
        overlay.classList.remove('is-open');
        bolsaGlobalRegistroCloseTimer = setTimeout(function() {
            document.body.style.overflow = '';
            bolsaGlobalRegistroCloseTimer = null;
        }, 420);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var formBolsa = document.getElementById('bolsa_global_form');
        if (formBolsa) {
            formBolsa.addEventListener('submit', function() {
                if (typeof tinymce === 'undefined') {
                    return;
                }
                var ed = tinymce.get('bolsa_global_detalles');
                if (ed) {
                    ed.save();
                } else {
                    tinymce.triggerSave();
                }
            });
        }
        @if (($errors->any() && old('form_context') === 'bolsa_oferta') || request()->boolean('abrir_form'))
            bolsaGlobalRegistroModalOpen();
        @endif
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var o = document.getElementById('bolsaGlobalRegistroOverlay');
                if (o && o.classList.contains('is-open')) bolsaGlobalRegistroModalClose();
            }
        });
    });
</script>
