<!-- ═════════════════════════════════════════════════════════════════════
     1. MENÚ LATERAL PARA ESCRITORIO (PANTALLAS GRANDES >= 992px)
     ═════════════════════════════════════════════════════════════════════ -->
<div class="col-lg-3 d-none d-lg-block">
    <div class="pegajoso desktop">
        <h3 class="linea-debajo">Trámites</h3>
        <ul class="submenu2 pk-sidebar-menu">
            <li>
                <a href="{{ route('tramiteTitulacion') }}"
                   class="{{ request()->routeIs('tramiteTitulacion') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Proceso de Titulación</span>
                </a>
            </li>
            <li>
                <a href="{{ route('plan') }}"
                   class="{{ request()->routeIs('plan') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Plan de Trabajo</span>
                </a>
            </li>
            <li>
                <a href="{{ route('tinvestigacion') }}"
                   class="{{ request()->routeIs('tinvestigacion') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Trabajo de Investigación</span>
                </a>
            </li>
            <li>
                <a href="{{ route('tesis') }}"
                   class="{{ request()->routeIs('tesis') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Tesis</span>
                </a>
            </li>
            <li>
                <a href="{{ route('tramites') }}"
                   class="{{ request()->routeIs('tramites') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Trámites presenciales</span>
                </a>
            </li>
            <li>
                <a href="{{ route('extraordinarios') }}"
                   class="{{ request()->routeIs('extraordinarios') ? 'active' : '' }}">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>Trámites Extraordinarios</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="openPagoModal(event)" class="pk-nav-caja-cusco">
                    <i class="fa fa-caret-right fa-sm"></i>
                    <span>¿Cómo pagar en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>?</span>
                </a>
            </li>
        </ul>

        <div class="pk-sidebar-support">
            <div class="pk-support-header">
                <h6><i class="fa fa-headset text-warning"></i> Mesa de Ayuda</h6>
                <span class="pk-support-time"><i class="fa fa-clock"></i> 8am - 5pm</span>
            </div>
            <div class="pk-support-links">
                <a href="https://wa.me/51972577170" target="_blank" rel="noopener" class="pk-support-link" title="Contactar por WhatsApp al Área de Investigación">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span class="pk-support-label">Investigación:</span>
                    <strong>972 577 170</strong>
                </a>
                <a href="https://wa.me/51969572566" target="_blank" rel="noopener" class="pk-support-link" title="Contactar por WhatsApp a Secretaría Académica">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span class="pk-support-label">Secretaría Acad.:</span>
                    <strong>969 572 566</strong>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════
     2. NAVEGACIÓN RESPONSIVE PARA CELULARES Y TABLETS (< 992px)
     Barra táctil de fases rápida con chips + menú desplegable completo
     ═════════════════════════════════════════════════════════════════════ -->
<div class="col-12 d-lg-none pk-mobile-nav-wrapper">
    <div class="pk-mobile-nav-box">
        <div class="pk-mobile-nav-top">
            <div class="pk-mobile-nav-heading">
                <i class="fa fa-compass"></i>
                <div style="min-width: 0;">
                    <span class="pk-mobile-nav-tag">Fases de Titulación</span>
                    <strong class="pk-mobile-current-title">
                        @if(request()->routeIs('plan'))
                            Fase 1: Plan PTI
                        @elseif(request()->routeIs('tinvestigacion'))
                            Fase 2: Bachiller (TI)
                        @elseif(request()->routeIs('tesis'))
                            Fase 3: Tesis Licenciatura
                        @elseif(request()->routeIs('tramiteTitulacion'))
                            Guía Oficial de Grados y Títulos
                        @elseif(request()->routeIs('extraordinarios'))
                            Trámites Extraordinarios
                        @elseif(request()->routeIs('tramites'))
                            Trámites Presenciales
                        @else
                            Menú de Titulación
                        @endif
                    </strong>
                </div>
            </div>
            <button type="button" class="pk-mobile-menu-btn" onclick="pkToggleMobileTitulaMenu()" aria-expanded="false" id="pkMobileMenuBtn">
                <span id="pkMobileBtnText">Ver fases</span>
                <i class="fa fa-chevron-down" id="pkMobileBtnIcon"></i>
            </button>
        </div>

        <!-- Chips táctiles deslizables horizontalmente (1-tap directo) -->
        <div class="pk-mobile-chips-track">
            <a href="{{ route('tramiteTitulacion') }}" class="pk-mchip {{ request()->routeIs('tramiteTitulacion') ? 'active' : '' }}">
                <i class="fa fa-book-reader"></i> Guía
            </a>
            <a href="{{ route('plan') }}" class="pk-mchip {{ request()->routeIs('plan') ? 'active' : '' }}">
                <span class="pk-mchip-badge">1</span> Plan PTI
            </a>
            <a href="{{ route('tinvestigacion') }}" class="pk-mchip {{ request()->routeIs('tinvestigacion') ? 'active' : '' }}">
                <span class="pk-mchip-badge">2</span> Bachiller (TI)
            </a>
            <a href="{{ route('tesis') }}" class="pk-mchip {{ request()->routeIs('tesis') ? 'active' : '' }}">
                <span class="pk-mchip-badge">3</span> Tesis
            </a>
            <a href="{{ route('tramites') }}" class="pk-mchip {{ request()->routeIs('tramites') ? 'active' : '' }}">
                <i class="fa fa-folder-open"></i> Presencial
            </a>
            <a href="{{ route('extraordinarios') }}" class="pk-mchip {{ request()->routeIs('extraordinarios') ? 'active' : '' }}">
                <i class="fa fa-receipt"></i> Extraordinarios
            </a>
            <a href="#" onclick="openPagoModal(event)" class="pk-mchip pk-mchip-caja">
                <i class="fa fa-credit-card" style="color: #d3141b;"></i> Caja Cusco
            </a>
        </div>

        <!-- Panel desplegable completo con lista y mesa de ayuda -->
        <div class="pk-mobile-expand-panel" id="pkMobileExpandPanel" style="display: none;">
            <ul class="pk-mobile-list">
                <li>
                    <a href="{{ route('tramiteTitulacion') }}" class="{{ request()->routeIs('tramiteTitulacion') ? 'active' : '' }}">
                        <i class="fa fa-book-reader"></i>
                        <span>Proceso de Titulación (Visión General)</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('plan') }}" class="{{ request()->routeIs('plan') ? 'active' : '' }}">
                        <span class="pk-list-num">1</span>
                        <span>Fase 1: Plan de Trabajo de Investigación (PTI)</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tinvestigacion') }}" class="{{ request()->routeIs('tinvestigacion') ? 'active' : '' }}">
                        <span class="pk-list-num">2</span>
                        <span>Fase 2: Trabajo de Investigación (Grado de Bachiller)</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tesis') }}" class="{{ request()->routeIs('tesis') ? 'active' : '' }}">
                        <span class="pk-list-num">3</span>
                        <span>Fase 3: Tesis (Título Profesional de Licenciado)</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tramites') }}" class="{{ request()->routeIs('tramites') ? 'active' : '' }}">
                        <i class="fa fa-folder-open"></i>
                        <span>Trámites Presenciales y Expediente</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('extraordinarios') }}" class="{{ request()->routeIs('extraordinarios') ? 'active' : '' }}">
                        <i class="fa fa-receipt"></i>
                        <span>Trámites Extraordinarios y Tasas</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="openPagoModal(event)" class="pk-mobile-caja-link">
                        <i class="fa fa-credit-card" style="color: #d3141b;"></i>
                        <span>¿Cómo pagar en <strong>Caja Cusco</strong>?</span>
                    </a>
                </li>
            </ul>

            <div class="pk-mobile-support-box">
                <div class="pk-support-header">
                    <h6><i class="fa fa-headset text-warning"></i> Mesa de Ayuda por WhatsApp</h6>
                    <span class="pk-support-time"><i class="fa fa-clock"></i> 8am - 5pm</span>
                </div>
                <div class="pk-support-links">
                    <a href="https://wa.me/51972577170" target="_blank" rel="noopener" class="pk-support-link">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span class="pk-support-label">Investigación:</span>
                        <strong>972 577 170</strong>
                    </a>
                    <a href="https://wa.me/51969572566" target="_blank" rel="noopener" class="pk-support-link">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span class="pk-support-label">Secretaría Acad.:</span>
                        <strong>969 572 566</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Menú Lateral de Trámites (Animaciones Modernas y Sticky) ────── */
html:has(.pk-titula-wrapper),
body:has(.pk-titula-wrapper),
html:has(.pegajoso),
body:has(.pegajoso) {
    overflow-x: clip !important;
}

@supports not (selector(:has(*))) {
    html, body {
        overflow-x: clip !important;
    }
}

@media (min-width: 992px) {
    .pk-titula-wrapper .col-lg-3 {
        position: relative;
    }

    .pegajoso.desktop {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 6.5rem !important;
        z-index: 20 !important;
        max-height: calc(100vh - 7.5rem);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(205, 146, 68, 0.4) transparent;
        padding-right: 4px;
    }

    .pegajoso.desktop::-webkit-scrollbar {
        width: 4px;
    }

    .pegajoso.desktop::-webkit-scrollbar-thumb {
        background: rgba(205, 146, 68, 0.4);
        border-radius: 4px;
    }
}

@media (max-width: 991.98px) {
    .pegajoso.desktop {
        position: static !important;
        max-height: none !important;
        overflow-y: visible !important;
    }
}

.pegajoso .submenu2,
.pk-sidebar-menu {
    list-style: none !important;
    padding: 0 !important;
    margin: 1.25rem 0 2rem 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
}

.pegajoso .submenu2 li,
.pk-sidebar-menu li {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.3 !important;
}

.pegajoso .submenu2 li a,
.pk-sidebar-menu li a {
    display: flex !important;
    align-items: center !important;
    gap: 11px !important;
    padding: 10px 14px !important;
    border-radius: 10px !important;
    font-family: "Poppins", sans-serif !important;
    font-size: 14.5px !important;
    font-weight: 500 !important;
    color: #4b5563 !important;
    text-decoration: none !important;
    background: transparent !important;
    border-left: 3.5px solid transparent !important;
    border-bottom: none !important; /* Elimina la línea punteada antigua */
    border-top: none !important;
    border-right: none !important;
    box-sizing: border-box !important;
    transition: all 0.22s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    position: relative !important;
    will-change: transform, padding-left, background-color;
}

.pegajoso .submenu2 li a i,
.pk-sidebar-menu li a i {
    color: #9ca3af !important;
    font-size: 13px !important;
    width: 14px !important;
    text-align: center !important;
    flex-shrink: 0 !important;
    transition: transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1), color 0.2s ease !important;
}

/* Efecto hover suave y moderno */
.pegajoso .submenu2 li a:hover,
.pk-sidebar-menu li a:hover {
    color: #925816 !important;
    background: #fbf5eb !important;
    border-left: 3.5px solid #cd9244 !important;
    border-bottom: none !important;
    padding-left: 18px !important;
    box-shadow: 0 3px 10px rgba(205, 146, 68, 0.1) !important;
}

.pegajoso .submenu2 li a:hover i,
.pk-sidebar-menu li a:hover i {
    color: #cd9244 !important;
    transform: translateX(4px) !important;
}

/* Estado activo (página actual) */
.pegajoso .submenu2 li a.active,
.pk-sidebar-menu li a.active {
    color: #8b500e !important;
    font-weight: 600 !important;
    background: linear-gradient(90deg, #fff7ea 0%, #ffffff 100%) !important;
    border-left: 4px solid #cd9244 !important;
    border-bottom: none !important;
    padding-left: 16px !important;
    box-shadow: 0 2px 8px rgba(205, 146, 68, 0.12) !important;
}

.pegajoso .submenu2 li a.active i,
.pk-sidebar-menu li a.active i {
    color: #cd9244 !important;
    transform: translateX(2px) !important;
}

.pegajoso .submenu2 li a.active:hover,
.pk-sidebar-menu li a.active:hover {
    background: #fef0d7 !important;
    padding-left: 19px !important;
}

.pegajoso .submenu2 li a.active:hover i,
.pk-sidebar-menu li a.active:hover i {
    transform: translateX(5px) !important;
}

/* Botón modal de Caja Cusco */
.pegajoso .submenu2 li a.pk-nav-caja-cusco,
.pk-sidebar-menu li a.pk-nav-caja-cusco {
    margin-top: 6px !important;
    background: #f8fafc !important;
    border: 1px dashed #cbd5e1 !important;
    border-left: 3.5px solid #263552 !important;
    color: #263552 !important;
    font-weight: 500 !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco i,
.pk-sidebar-menu li a.pk-nav-caja-cusco i {
    color: #263552 !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco:hover,
.pk-sidebar-menu li a.pk-nav-caja-cusco:hover {
    background: #263552 !important;
    color: #ffffff !important;
    border-color: #263552 !important;
    border-left: 3.5px solid #cd9244 !important;
    padding-left: 18px !important;
    box-shadow: 0 4px 14px rgba(38, 53, 82, 0.22) !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco:hover i,
.pk-sidebar-menu li a.pk-nav-caja-cusco:hover i {
    color: #f3c27e !important;
    transform: translateX(4px) !important;
}

/* Tarjeta Mesa de Ayuda Compacta */
.pk-sidebar-support {
    background: #fdfaf6;
    border: 1px solid #ebd9c0;
    border-radius: 11px;
    padding: 10px 12px;
    margin-top: 14px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.pk-sidebar-support:hover {
    border-color: #cd9244;
    box-shadow: 0 3px 10px rgba(205, 146, 68, 0.08);
}

.pk-support-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 7px;
}

.pk-sidebar-support h6 {
    font-family: "Poppins", sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: #2c251e;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.pk-support-time {
    font-size: 10.5px;
    font-weight: 500;
    color: #8c7866;
    background: #f5ece0;
    padding: 2px 7px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.pk-support-links {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.pk-support-link {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 6px 9px;
    background: #ffffff;
    border: 1px solid #ebd9c0;
    border-radius: 7px;
    text-decoration: none !important;
    font-family: "Poppins", sans-serif;
    font-size: 11.5px;
    color: #4b5563;
    transition: all 0.18s ease;
    line-height: 1.2;
}

.pk-support-link i {
    color: #25d366;
    font-size: 13.5px;
    flex-shrink: 0;
}

.pk-support-label {
    color: #6b7280;
    font-weight: 500;
}

.pk-support-link strong {
    color: #1f2937;
    font-weight: 600;
    margin-left: auto;
}

.pk-support-link:hover {
    background: #f0fdf4;
    border-color: #86efac;
    color: #166534;
    transform: translateX(2px);
}

.pk-support-link:hover strong {
    color: #15803d;
}

/* ── Overlay ─────────────────────────────────────────── */
.pago-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 20, 40, 0.68);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 2999;
}

/* ── Modal card ──────────────────────────────────────── */
.pago-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(620px, 94vw);
    max-height: 90vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 32px 72px rgba(0, 0, 0, 0.28);
    z-index: 3000;
    font-family: "Poppins", sans-serif;
}

/* ── Header ──────────────────────────────────────────── */
.pago-modal__header {
    background: linear-gradient(135deg, #c98838 0%, #8b5e22 100%);
    padding: 22px 26px 18px;
    border-radius: 16px 16px 0 0;
    position: relative;
}
.pago-modal__header h4 {
    color: #fff;
    font-size: 19px;
    font-weight: 600;
    margin: 0;
    padding-right: 36px;
    line-height: 1.35;
}
.pago-modal__header .pago-modal__subtitle {
    color: rgba(255, 255, 255, 0.85);
    font-size: 13px;
    margin-top: 4px;
    text-transform: uppercase;
}

.pago-modal__icon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    margin-bottom: 8px;
    font-size: 18px;
    color: #fff;
}

.pago-modal__close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.35);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    transition: background 0.2s;
    padding: 0;
}
.pago-modal__close:hover {
    background: rgba(255, 255, 255, 0.35);
}

.pago-modal__body { padding: 22px 26px; }

.pago-modal__alert {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 8px 8px 0;
    padding: 11px 14px;
    font-size: 13.5px;
    color: #7c5a00;
    margin-bottom: 18px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 20px;
}
.pago-modal__alert i {
    margin-top: 2px;
    color: #f59e0b;
    flex-shrink: 0;
    font-size: 16px;
}

.pago-steps {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pago-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    position: relative;
}

.pago-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 17px;
    top: 36px;
    bottom: 0;
    width: 2px;
    background: #f0d5a8;
}

.pago-step__num {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #cd9244;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.pago-step__content {
    padding: 6px 0 18px;
    font-size: 14.5px;
    color: #4D4D4D;
    line-height: 22px;
    flex: 1;
}
.pago-step:last-child .pago-step__content { padding-bottom: 0; }

.pago-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #2563eb;
    color: #fff !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 500;
    padding: 5px 14px;
    border-radius: 20px;
    margin-top: 6px;
    transition: background 0.2s;
}
.pago-pdf-btn:hover {
    background: #1d4ed8;
}

.pago-modal__footer {
    display: flex;
    justify-content: center;
    padding-top: 16px;
    margin-top: 14px;
    border-top: 1px solid #f3f4f6;
}
.pago-modal__btn-ok {
    background: #263552;
    color: #fff;
    border: none;
    padding: 8px 32px;
    border-radius: 24px;
    font-size: 14.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.pago-modal__btn-ok:hover {
    background: #1b263b;
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
        <h4 id="pagoModalTitle">¿Cómo pagar en <span style="color: #ffcccc; font-weight: 700;">Caja Cusco</span>?</h4>
        <p class="pago-modal__subtitle">Proceso de pago ordinario &mdash; Titulación</p>
    </div>

    <div class="pago-modal__body">
        <div class="pago-modal__alert">
            <i class="fa fa-exclamation-triangle"></i>
            <span>Todos los pagos del proceso de titulación son <strong>pagos ordinarios</strong> en ventanillas de <strong class="caja-cusco-brand" style="color:#d3141b;">Caja Cusco</strong>. Para evitar confusiones en ventanilla, debes indicar obligatoriamente: <strong>Concepto exacto</strong>, <strong>Código</strong> y <strong>Monto</strong>.</span>
        </div>

        <ul class="pago-steps">
            <li class="pago-step">
                <div class="pago-step__num">1</div>
                <div class="pago-step__content">
                    Acércate a las oficinas de <strong class="caja-cusco-brand" style="color:#d3141b">Caja Cusco</strong> (cualquier agencia a nivel nacional)
                    e indica en ventanilla que realizarás un <strong>pago ordinario</strong> a nombre de la <strong>Asociación Pukllasunchis - EESPP</strong>.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">2</div>
                <div class="pago-step__content">
                    Indica obligatoriamente al cajero los 3 datos de tu trámite: <strong>1. Concepto exacto</strong>, <strong>2. Código numérico</strong> y <strong>3. Monto a pagar</strong>.
                    <br>
                    <a class="pago-pdf-btn" target="_blank"
                        href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}">
                        <i class="fa fa-file-pdf"></i> Ver PDF oficial de conceptos de pago
                    </a>
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">3</div>
                <div class="pago-step__content">
                    Proporciona en ventanilla tu <strong>número de DNI</strong>
                    y los nombres y apellidos completos del estudiante.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">4</div>
                <div class="pago-step__content">
                    <strong>No subas el voucher impreso al formulario.</strong>
                    En el lapso de <strong>2 días hábiles</strong> recibirás la <strong>Boleta Electrónica Oficial</strong>
                    emitida por la EESPP en tu correo institucional.
                </div>
            </li>
            <li class="pago-step">
                <div class="pago-step__num">5</div>
                <div class="pago-step__content">
                    Con esa <strong>boleta electrónica</strong> oficial podrás iniciar o continuar
                    tu trámite subiéndola al formulario virtual respectivo.
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
        if (!overlay || !modal) return;

        var alertEl = modal.querySelector('.pago-modal__alert');
        var steps   = modal.querySelectorAll('.pago-step');

        overlay.style.display = 'block';
        modal.style.display   = 'block';

        if (typeof gsap !== 'undefined') {
            if (openTl) openTl.kill();
            gsap.set(modal,   { opacity: 0, scale: 0.9, y: 20 });
            gsap.set(overlay, { opacity: 0 });
            gsap.set([alertEl, ...steps], { opacity: 0, x: -15 });

            openTl = gsap.timeline()
                .to(overlay, { opacity: 1, duration: 0.25, ease: 'power2.out' }, 0)
                .to(modal,   { opacity: 1, scale: 1, y: 0, duration: 0.35, ease: 'back.out(1.4)' }, 0.05)
                .to(alertEl, { opacity: 1, x: 0, duration: 0.25, ease: 'power2.out' }, 0.25)
                .to(steps,   { opacity: 1, x: 0, duration: 0.22, ease: 'power2.out', stagger: 0.06 }, 0.35);
        } else {
            overlay.style.opacity = '1';
            modal.style.opacity = '1';
        }
    }

    function closePagoModal() {
        var overlay = document.getElementById('pagoOverlay');
        var modal   = document.getElementById('pagoModal');
        if (!overlay || !modal) return;

        if (typeof gsap !== 'undefined') {
            if (openTl) openTl.kill();
            gsap.timeline()
                .to(modal,   { opacity: 0, scale: 0.92, y: 15, duration: 0.2, ease: 'power2.in' }, 0)
                .to(overlay, { opacity: 0, duration: 0.18, ease: 'power2.in' }, 0.03)
                .call(function () {
                    modal.style.display   = 'none';
                    overlay.style.display = 'none';
                });
        } else {
            modal.style.display   = 'none';
            overlay.style.display = 'none';
        }
    }

    var overlayEl = document.getElementById('pagoOverlay');
    if (overlayEl) {
        overlayEl.addEventListener('click', closePagoModal);
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePagoModal();
    });

    function pkToggleMobileTitulaMenu() {
        var panel = document.getElementById('pkMobileExpandPanel');
        var btn   = document.getElementById('pkMobileMenuBtn');
        var text  = document.getElementById('pkMobileBtnText');
        var icon  = document.getElementById('pkMobileBtnIcon');
        if (!panel) return;

        var isHidden = (panel.style.display === 'none' || panel.style.display === '');
        if (isHidden) {
            panel.style.display = 'block';
            if (text) text.innerText = 'Cerrar';
            if (icon) { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up'); }
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            panel.style.display = 'none';
            if (text) text.innerText = 'Ver fases';
            if (icon) { icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down'); }
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    }

    // Centrar suavemente el chip activo en el scroll horizontal de móviles
    try {
        var activeChip = document.querySelector('.pk-mobile-chips-track .pk-mchip.active');
        if (activeChip && typeof activeChip.scrollIntoView === 'function') {
            setTimeout(function () {
                activeChip.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }, 150);
        }
    } catch (e) {}

    window.pkToggleMobileTitulaMenu = pkToggleMobileTitulaMenu;
    window.openPagoModal  = openPagoModal;
    window.closePagoModal = closePagoModal;
}());
</script>
