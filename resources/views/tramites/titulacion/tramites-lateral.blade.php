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
                <a href="#" onclick="openPagoModal(event)" class="pk-nav-caja-cusco" title="Ver instructivo ilustrado de pago en Caja Cusco">
                    <i class="fa fa-credit-card"></i>
                    <span>¿Cómo pagar en <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong>?</span>
                    <span class="pk-caja-nav-tag">Instructivo</span>
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
/* Botón modal de Caja Cusco en el Menú Lateral */
.pegajoso .submenu2 li a.pk-nav-caja-cusco,
.pk-sidebar-menu li a.pk-nav-caja-cusco {
    margin-top: 8px !important;
    background: #fff8f8 !important;
    border: 1.5px solid #fecaca !important;
    border-left: 4px solid #d3141b !important;
    border-radius: 10px !important;
    color: #1f2937 !important;
    font-weight: 500 !important;
    padding: 10px 12px !important;
    display: flex !important;
    align-items: center !important;
    gap: 9px !important;
    transition: all 0.22s ease !important;
    box-shadow: 0 1px 4px rgba(211, 20, 27, 0.05) !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco i,
.pk-sidebar-menu li a.pk-nav-caja-cusco i {
    color: #d3141b !important;
    font-size: 14px !important;
    width: 16px !important;
    text-align: center !important;
    flex-shrink: 0 !important;
    transition: transform 0.22s ease !important;
}

.pk-caja-nav-tag {
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    color: #b91c1c !important;
    background: #fee2e2 !important;
    padding: 2px 7px !important;
    border-radius: 999px !important;
    margin-left: auto !important;
    flex-shrink: 0 !important;
    border: 1px solid #fca5a5 !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco:hover,
.pk-sidebar-menu li a.pk-nav-caja-cusco:hover {
    background: #fee2e2 !important;
    border-color: #f87171 !important;
    border-left: 4px solid #b91c1c !important;
    color: #7f1d1d !important;
    padding-left: 14px !important;
    box-shadow: 0 4px 14px rgba(211, 20, 27, 0.16) !important;
    transform: translateX(2px) !important;
}

.pegajoso .submenu2 li a.pk-nav-caja-cusco:hover i,
.pk-sidebar-menu li a.pk-nav-caja-cusco:hover i {
    color: #b91c1c !important;
    transform: scale(1.15) !important;
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

/* ── Overlay centrado perfecto ──────────────────────── */
.pago-overlay {
    display: none;
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.76);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99998;
    overflow-y: auto;
    padding: 24px 16px;
    box-sizing: border-box;
    align-items: center;
    justify-content: center;
}

/* ── Modal card contenido ────────────────────────────── */
.pago-modal {
    position: relative;
    margin: auto;
    width: 100%;
    max-width: 620px;
    max-height: min(88vh, 740px);
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 65px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.15);
    overflow: hidden;
    z-index: 99999;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

/* ── Header ──────────────────────────────────────────── */
.pago-modal__header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-top: 4px solid #d3141b;
    padding: 20px 24px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-shrink: 0;
}

.pago-modal__header-content {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.pago-modal__icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(211, 20, 27, 0.2);
    border: 1px solid rgba(211, 20, 27, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #ff6b6b;
    flex-shrink: 0;
}

.pago-modal__header-texts {
    min-width: 0;
}

.pago-modal__tag {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #fca5a5;
    margin-bottom: 2px;
    line-height: 1.2;
}

.pago-modal__header h4 {
    color: #ffffff;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    line-height: 1.3;
}

.pago-modal__close {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
    transition: all 0.2s ease;
    flex-shrink: 0;
    padding: 0;
}

.pago-modal__close:hover {
    background: #d3141b;
    border-color: #d3141b;
    color: #ffffff;
    transform: rotate(90deg);
}

/* ── Body ────────────────────────────────────────────── */
.pago-modal__body {
    padding: 22px 24px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    flex: 1 1 auto;
    background: #ffffff;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.pago-modal__body::-webkit-scrollbar {
    width: 5px;
}
.pago-modal__body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.pago-modal__alert {
    background: #fff8f8;
    border: 1px solid #fecaca;
    border-left: 4px solid #d3141b;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
    color: #4b5563;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 20px;
}

.pago-modal__alert i {
    color: #d3141b;
    flex-shrink: 0;
    font-size: 17px;
    margin-top: 1px;
}

/* ── Stepper ─────────────────────────────────────────── */
.pago-steps-container {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.pago-step-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
    padding-bottom: 18px;
}

.pago-step-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 16px;
    top: 34px;
    bottom: 0;
    width: 2px;
    background: #f1f5f9;
}

.pago-step-num {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d3141b 0%, #b91c1c 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 1;
    box-shadow: 0 2px 6px rgba(211, 20, 27, 0.3);
}

.pago-step-body {
    flex: 1;
    min-width: 0;
}

.pago-step-body h5 {
    font-size: 14.5px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 4px 0;
    line-height: 1.35;
}

.pago-step-body p {
    font-size: 13.5px;
    color: #4b5563;
    line-height: 20px;
    margin: 0;
}

.pago-step-triad {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 8px 0;
}

.pago-triad-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #fff5f5;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.pago-triad-pill i {
    color: #d3141b;
    font-size: 11px;
}

.pago-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #1e3a8a;
    color: #ffffff !important;
    text-decoration: none !important;
    font-size: 12.5px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    margin-top: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(30, 58, 138, 0.22);
}

.pago-pdf-btn:hover {
    background: #172554;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(30, 58, 138, 0.35);
}

/* ── Footer ──────────────────────────────────────────── */
.pago-modal__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.pago-footer-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #4b5563;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.pago-footer-pdf-btn:hover {
    color: #d3141b;
}

.pago-modal__btn-ok {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #d3141b 0%, #b91c1c 100%);
    color: #ffffff;
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    font-family: "Poppins", sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(211, 20, 27, 0.25);
}

.pago-modal__btn-ok:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(211, 20, 27, 0.35);
}

@media (max-width: 575.98px) {
    .pago-modal__header {
        padding: 16px 18px;
    }
    .pago-modal__header h4 {
        font-size: 16px;
    }
    .pago-modal__body {
        padding: 16px 18px;
    }
    .pago-modal__footer {
        padding: 12px 18px;
        flex-direction: column-reverse;
        gap: 10px;
    }
    .pago-modal__btn-ok,
    .pago-footer-pdf-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Modal de Instrucciones de Pago en Caja Cusco (Optimizado y Centrado) -->
<div class="pago-overlay" id="pagoOverlay" role="dialog" aria-modal="true" aria-labelledby="pagoModalTitle">
    <div class="pago-modal" id="pagoModal">
        <div class="pago-modal__header">
            <div class="pago-modal__header-content">
                <div class="pago-modal__icon-badge">
                    <i class="fa fa-building-columns"></i>
                </div>
                <div class="pago-modal__header-texts">
                    <span class="pago-modal__tag">Instructivo Oficial · EESPP Pukllasunchis</span>
                    <h4 id="pagoModalTitle">¿Cómo pagar en <span class="caja-cusco-brand" style="color: #ff6b6b; font-weight: 700;">Caja Cusco</span>?</h4>
                </div>
            </div>
            <button type="button" class="pago-modal__close" onclick="closePagoModal()" aria-label="Cerrar modal">&times;</button>
        </div>

        <div class="pago-modal__body">
            <div class="pago-modal__alert">
                <i class="fa fa-triangle-exclamation"></i>
                <div>
                    <strong>Importante:</strong> Todos los pagos del proceso de titulación corresponden a <strong>PAGOS ORDINARIOS</strong> en ventanillas de <strong class="caja-cusco-brand" style="color: #d3141b;">Caja Cusco</strong> a nivel nacional. No se admiten transferencias directas ni depósitos en agentes no autorizados.
                </div>
            </div>

            <div class="pago-steps-container">
                <div class="pago-step-item">
                    <div class="pago-step-num">1</div>
                    <div class="pago-step-body">
                        <h5>Acércate a cualquier agencia de Caja Cusco</h5>
                        <p>En ventanilla a nivel nacional, indica que realizarás un <strong>PAGO ORDINARIO</strong> a nombre de la <strong>ASOCIACIÓN PUKLLASUNCHIS - EESPP</strong>.</p>
                    </div>
                </div>

                <div class="pago-step-item">
                    <div class="pago-step-num">2</div>
                    <div class="pago-step-body">
                        <h5>Dicta obligatoriamente los 3 datos de tu trámite:</h5>
                        <div class="pago-step-triad">
                            <span class="pago-triad-pill"><i class="fa fa-file-invoice"></i> 1. Concepto exacto</span>
                            <span class="pago-triad-pill"><i class="fa fa-barcode"></i> 2. Código numérico</span>
                            <span class="pago-triad-pill"><i class="fa fa-coins"></i> 3. Monto a pagar</span>
                        </div>
                        <a class="pago-pdf-btn" target="_blank" href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}">
                            <i class="fa fa-file-pdf"></i> Ver Tarifario Oficial y Códigos (PDF)
                        </a>
                    </div>
                </div>

                <div class="pago-step-item">
                    <div class="pago-step-num">3</div>
                    <div class="pago-step-body">
                        <h5>Identificación del Estudiante</h5>
                        <p>Dicta el número de <strong>DNI</strong> y los nombres y apellidos completos del estudiante graduando.</p>
                    </div>
                </div>

                <div class="pago-step-item">
                    <div class="pago-step-num">4</div>
                    <div class="pago-step-body">
                        <h5>Boleta Electrónica Oficial (¡No subir el voucher!)</h5>
                        <p>En el lapso de <strong>2 días hábiles</strong> recibirás la <strong>Boleta Electrónica Oficial (PDF)</strong> emitida por la EESPP en tu correo institucional (@pukllavirtual.edu.pe). Esa boleta es la que debes adjuntar a tu formulario virtual respectivo.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pago-modal__footer">
            <a href="{{ asset('pdf/Conceptos-ordinarios-caja-cusco-2026-2.pdf') }}" target="_blank" class="pago-footer-pdf-btn">
                <i class="fa fa-file-pdf text-danger"></i> Descargar Tarifario PDF
            </a>
            <button type="button" class="pago-modal__btn-ok" onclick="closePagoModal()">
                <i class="fa fa-check"></i> Entendido
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var openTl = null;

    function openPagoModal(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var overlay = document.getElementById('pagoOverlay');
        var modal   = document.getElementById('pagoModal');
        if (!overlay || !modal) return;

        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        var alertEl = modal.querySelector('.pago-modal__alert');
        var steps   = modal.querySelectorAll('.pago-step-item');

        if (typeof gsap !== 'undefined') {
            if (openTl) openTl.kill();
            gsap.set(overlay, { opacity: 0 });
            gsap.set(modal,   { opacity: 0, scale: 0.94, y: 16 });
            if (alertEl) gsap.set(alertEl, { opacity: 0, y: 8 });
            if (steps.length) gsap.set(steps, { opacity: 0, y: 8 });

            openTl = gsap.timeline()
                .to(overlay, { opacity: 1, duration: 0.22, ease: 'power2.out' }, 0)
                .to(modal,   { opacity: 1, scale: 1, y: 0, duration: 0.3, ease: 'back.out(1.2)' }, 0.05);

            if (alertEl) {
                openTl.to(alertEl, { opacity: 1, y: 0, duration: 0.2, ease: 'power2.out' }, 0.2);
            }
            if (steps.length) {
                openTl.to(steps, { opacity: 1, y: 0, duration: 0.2, ease: 'power2.out', stagger: 0.05 }, 0.25);
            }
        } else {
            overlay.style.opacity = '1';
            modal.style.opacity = '1';
        }
    }

    function closePagoModal() {
        var overlay = document.getElementById('pagoOverlay');
        var modal   = document.getElementById('pagoModal');
        if (!overlay || !modal) return;

        document.body.style.overflow = '';

        if (typeof gsap !== 'undefined') {
            if (openTl) openTl.kill();
            gsap.timeline()
                .to(modal,   { opacity: 0, scale: 0.94, y: 10, duration: 0.18, ease: 'power2.in' }, 0)
                .to(overlay, { opacity: 0, duration: 0.16, ease: 'power2.in' }, 0.02)
                .call(function () {
                    overlay.style.display = 'none';
                });
        } else {
            overlay.style.display = 'none';
        }
    }

    var overlayEl = document.getElementById('pagoOverlay');
    if (overlayEl) {
        overlayEl.addEventListener('click', function (e) {
            if (e.target === overlayEl) {
                closePagoModal();
            }
        });
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
