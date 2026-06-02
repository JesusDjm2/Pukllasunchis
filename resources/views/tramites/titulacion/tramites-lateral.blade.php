<div class="col-lg-3">
    <div class="pegajoso">
        <h3 class="linea-debajo">Trámites</h3>
        <ul class="submenu2">
            <li><a href="{{ route('plan') }}"><i class="fa fa-caret-right fa-sm"></i> Plan de Trabajo</a></li>
            <li><a href="{{ route('tinvestigacion') }}"><i class="fa fa-caret-right fa-sm"></i> Trabajo de
                    Investigación</a></li>
            <li><a href="{{ route('tesis') }}"><i class="fa fa-caret-right fa-sm"></i> Tesis</a></li>
            <li><a href="{{ route('tramites') }}"><i class="fa fa-caret-right fa-sm"></i> Trámites
                    presenciales</a></li>
            <li><a href="{{ route('extraordinarios') }}"><i class="fa fa-caret-right fa-sm"></i> Trámites
                    Extraordinarios</a></li>
            <li>
                <a href="#" onclick="openPagoModal(event)">
                    <i class="fa fa-caret-right fa-sm"></i> ¿Cómo pagar en Caja Cusco?
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
/* ── Overlay ─────────────────────────────────────────── */
.pago-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 20, 40, 0.68);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 1999;
}

/* ── Modal card ──────────────────────────────────────── */
.pago-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(600px, 94vw);
    max-height: 90vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 32px 72px rgba(0, 0, 0, 0.24), 0 4px 18px rgba(0, 0, 0, 0.1);
    z-index: 2000;
    scrollbar-width: thin;
    scrollbar-color: #e5c99a #f9f6f0;
}
.pago-modal::-webkit-scrollbar { width: 5px; }
.pago-modal::-webkit-scrollbar-track { background: #f9f6f0; }
.pago-modal::-webkit-scrollbar-thumb { background: #e5c99a; border-radius: 4px; }

/* ── Header ──────────────────────────────────────────── */
.pago-modal__header {
    background: linear-gradient(135deg, #c98838 0%, #8b5e22 100%);
    padding: 26px 28px 22px;
    border-radius: 18px 18px 0 0;
    position: relative;
}
.pago-modal__header h4 {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    padding-right: 40px;
    letter-spacing: 0.015em;
    line-height: 1.35;
}
.pago-modal__header .pago-modal__subtitle {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.75rem;
    margin-top: 5px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

/* ── Icon badge in header ────────────────────────────── */
.pago-modal__icon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    margin-bottom: 10px;
    font-size: 1.1rem;
    color: #fff;
}

/* ── Close button ────────────────────────────────────── */
.pago-modal__close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
    transition: background 0.2s, transform 0.15s;
    padding: 0;
    line-height: 1;
}
.pago-modal__close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* ── Body ────────────────────────────────────────────── */
.pago-modal__body { padding: 24px 28px 28px; }

/* ── Alert note ──────────────────────────────────────── */
.pago-modal__alert {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 10px 10px 0;
    padding: 11px 14px;
    font-size: 0.8rem;
    color: #7c5a00;
    margin-bottom: 22px;
    display: flex;
    align-items: flex-start;
    gap: 9px;
}
.pago-modal__alert i {
    margin-top: 1px;
    color: #f59e0b;
    flex-shrink: 0;
    font-size: 0.85rem;
}

/* ── Steps ───────────────────────────────────────────── */
.pago-steps {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pago-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
}

.pago-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 18px;
    top: 38px;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, rgba(201, 136, 56, 0.5) 60%, rgba(201, 136, 56, 0.05));
}

.pago-step__num {
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c98838 0%, #a8722e 100%);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(201, 136, 56, 0.38);
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}

.pago-step__content {
    padding: 7px 0 22px;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.6;
    flex: 1;
}
.pago-step:last-child .pago-step__content { padding-bottom: 0; }

/* ── PDF CTA ─────────────────────────────────────────── */
.pago-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff !important;
    text-decoration: none !important;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 20px;
    margin-top: 6px;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}
.pago-pdf-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 16px rgba(37, 99, 235, 0.42);
    color: #fff !important;
}

/* ── Footer ──────────────────────────────────────────── */
.pago-modal__footer {
    display: flex;
    justify-content: center;
    padding-top: 20px;
    margin-top: 16px;
    border-top: 1px solid #f3f4f6;
}
.pago-modal__btn-ok {
    background: linear-gradient(135deg, #38496b 0%, #263552 100%);
    color: #fff;
    border: none;
    padding: 10px 36px;
    border-radius: 28px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.02em;
    box-shadow: 0 3px 10px rgba(56, 73, 107, 0.3);
    transition: transform 0.15s, box-shadow 0.15s;
}
.pago-modal__btn-ok:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(56, 73, 107, 0.42);
}

/* ── Mobile tweaks ───────────────────────────────────── */
@media (max-width: 576px) {
    .pago-modal__header,
    .pago-modal__body { padding-left: 18px; padding-right: 18px; }
    .pago-step:not(:last-child)::after { left: 16px; }
}
</style>

<!-- Backdrop -->
<div class="pago-overlay" id="pagoOverlay"></div>

<!-- Modal -->
<div class="pago-modal" id="pagoModal" role="dialog" aria-modal="true" aria-labelledby="pagoModalTitle">

    <div class="pago-modal__header">
        <button class="pago-modal__close" onclick="closePagoModal()" aria-label="Cerrar">&#10005;</button>
        <div class="pago-modal__icon-badge">
            <i class="fa fa-university"></i>
        </div>
        <h4 id="pagoModalTitle">¿Cómo pagar en Caja Cusco?</h4>
        <p class="pago-modal__subtitle">Proceso de pago ordinario &mdash; Titulación</p>
    </div>

    <div class="pago-modal__body">

        <div class="pago-modal__alert">
            <i class="fa fa-exclamation-triangle"></i>
            <span>Todos los pagos del proceso de titulación son <strong>pagos ordinarios</strong>.
                No se considera pago ordinario matrículas ni cuotas semestrales.</span>
        </div>

        <ul class="pago-steps">
            <li class="pago-step">
                <div class="pago-step__num">1</div>
                <div class="pago-step__content">
                    Acércate a las oficinas de <strong style="color:#c0392b">Caja Cusco</strong>
                    e indica que realizarás un <strong>pago ordinario</strong> de la Asociación Pukllasunchis.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">2</div>
                <div class="pago-step__content">
                    Indica el <strong>concepto y código de pago</strong>.
                    <br>
                    <a class="pago-pdf-btn" target="_blank"
                        href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}">
                        <i class="fa fa-file-pdf-o"></i> Ver PDF de conceptos de pago
                    </a>
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">3</div>
                <div class="pago-step__content">
                    Proporciona en ventanilla tu <strong>número de DNI</strong>
                    y el nombre completo del estudiante.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">4</div>
                <div class="pago-step__content">
                    <strong>No es necesario enviar el voucher.</strong>
                    En el lapso de <strong>2 días hábiles</strong> recibirás la boleta electrónica
                    emitida por la EESPP en tu correo institucional.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">5</div>
                <div class="pago-step__content">
                    Con esa boleta electrónica podrás iniciar o continuar
                    tu <strong>trámite correspondiente</strong>.
                </div>
            </li>
        </ul>

        <div class="pago-modal__footer">
            <button class="pago-modal__btn-ok" onclick="closePagoModal()">Entendido</button>
        </div>

    </div>
</div>

<script>
(function () {
    var openTl = null;

    function openPagoModal(e) {
        if (e) e.preventDefault();
        var overlay = document.getElementById('pagoOverlay');
        var modal   = document.getElementById('pagoModal');
        var alertEl = modal.querySelector('.pago-modal__alert');
        var steps   = modal.querySelectorAll('.pago-step');

        overlay.style.display = 'block';
        modal.style.display   = 'block';

        if (openTl) openTl.kill();

        gsap.set(modal,   { opacity: 0, scale: 0.86, y: 28 });
        gsap.set(overlay, { opacity: 0 });
        gsap.set([alertEl, ...steps], { opacity: 0, x: -20 });

        openTl = gsap.timeline()
            .to(overlay, { opacity: 1, duration: 0.28, ease: 'power2.out' }, 0)
            .to(modal,   { opacity: 1, scale: 1, y: 0, duration: 0.38, ease: 'back.out(1.5)' }, 0.06)
            .to(alertEl, { opacity: 1, x: 0, duration: 0.28, ease: 'power2.out' }, 0.3)
            .to(steps,   { opacity: 1, x: 0, duration: 0.24, ease: 'power2.out', stagger: 0.07 }, 0.42);
    }

    function closePagoModal() {
        var overlay = document.getElementById('pagoOverlay');
        var modal   = document.getElementById('pagoModal');

        if (openTl) openTl.kill();

        gsap.timeline()
            .to(modal,   { opacity: 0, scale: 0.9, y: 18, duration: 0.22, ease: 'power2.in' }, 0)
            .to(overlay, { opacity: 0, duration: 0.2, ease: 'power2.in' }, 0.04)
            .call(function () {
                modal.style.display   = 'none';
                overlay.style.display = 'none';
            });
    }

    document.getElementById('pagoOverlay').addEventListener('click', closePagoModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePagoModal();
    });

    window.openPagoModal  = openPagoModal;
    window.closePagoModal = closePagoModal;
}());
</script>
