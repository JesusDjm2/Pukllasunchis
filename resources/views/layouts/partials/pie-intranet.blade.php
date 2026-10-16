{{--
    Pie de página único de la intranet (admin, superadmin, docente, alumno, PPD, bolsa, postulante).
    Antes cada layout tenía su propia línea "Copyright © … | Hecho por DJM2 | Versión …", con
    versiones distintas entre sí. La versión sale de config('app.version') — un solo lugar.
    Opcional: $area (p. ej. "Área docente") para la píldora de contexto.

    Mantiene la clase .sticky-footer (márgenes de sb-admin-2 y fondo de los modos oscuro/tenue en
    darkmode.css). Sin <span> para el texto: darkmode.css fuerza el color de todo span del pie.
--}}
@php
    $version = config('app.version', '2026.4');
@endphp
<footer class="sticky-footer pk-pie">
    <div class="pk-pie-contenido">
        <div class="pk-pie-marca">
            <img src="{{ asset('img/logo-iesp-pukllasunchis-svg.svg') }}" alt="" class="pk-pie-logo" width="38" height="38">
            <div>
                <div class="pk-pie-nombre">EESP Pukllasunchis</div>
                <div class="pk-pie-sub">Escuela de Educación Superior Pedagógica Privada</div>
            </div>
        </div>

        <nav class="pk-pie-enlaces" aria-label="Enlaces de ayuda">
            <a href="https://wa.me/51984529158?text={{ rawurlencode('Buen día, necesito ayuda con la intranet de la EESP Pukllasunchis.') }}"
                target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i> Soporte técnico</a>
            <a href="{{ route('informacion') }}" target="_blank" rel="noopener"><i class="fas fa-university"></i> Institución</a>
        </nav>

        <div class="pk-pie-legal">
            <div class="pk-pie-copy">&copy; {{ date('Y') }} EESP Pukllasunchis · Todos los derechos reservados</div>
            <div class="pk-pie-meta">
                @if (!empty($area))
                    <b class="pk-pie-area">{{ $area }}</b>
                @endif
                <i class="pk-pie-version">Sistema académico V.{{ $version }}</i>
                <a class="pk-pie-autor" href="https://www.facebook.com/DjmWebMaster" target="_blank" rel="noopener noreferrer"
                    title="Diseño y desarrollo del sistema: DJM2"><i class="fas fa-code"></i> Diseño y desarrollo · <strong>DJM2</strong></a>
            </div>
        </div>
    </div>
</footer>

@once
<style>
    .pk-pie {
        --pie-fondo: #fff; --pie-borde: #e6e9f1; --pie-texto: #2e3448; --pie-suave: #7a8094; --pie-acento: #c78d40;
        --pie-chip: #f4f6fb;
        background: var(--pie-fondo); border-top: 1px solid var(--pie-borde);
        padding: 1.1rem 0 !important; flex-shrink: 0;
    }
    body.dark-mode .pk-pie { --pie-fondo: #161b22; --pie-borde: #30363d; --pie-texto: #c9d1d9; --pie-suave: #8b949e; --pie-chip: #21262d; }
    body.dim-mode .pk-pie { --pie-fondo: var(--lm-superficie); --pie-borde: var(--lm-borde); --pie-texto: var(--lm-texto); --pie-suave: var(--lm-suave); --pie-acento: var(--lm-acento); --pie-chip: var(--lm-superficie-2); }

    .pk-pie-contenido {
        display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1.25rem 2rem;
        /* La derecha queda libre para el botón fijo "subir al inicio" de sb-admin-2
           (.scroll-to-top: 2.75rem a 1rem del borde): antes tapaba el crédito y su enlace. */
        padding: 0 4.75rem 0 1.5rem; max-width: 100%;
    }
    .pk-pie-marca { display: flex; align-items: center; gap: .7rem; min-width: 0; }
    .pk-pie-logo { width: 38px; height: 38px; object-fit: contain; flex: none; }
    .pk-pie-nombre { font-weight: 800; font-size: .88rem; color: var(--pie-texto); letter-spacing: -.01em; line-height: 1.2; }
    .pk-pie-sub { font-size: .7rem; color: var(--pie-suave); line-height: 1.3; }

    .pk-pie-enlaces { display: flex; flex-wrap: wrap; justify-content: center; gap: .25rem .4rem; }
    .pk-pie-enlaces a {
        display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .65rem; border-radius: 999px;
        font-size: .74rem; font-weight: 600; color: var(--pie-suave) !important; text-decoration: none !important;
        transition: background-color .15s ease, color .15s ease;
    }
    .pk-pie-enlaces a i { font-size: .72rem; color: var(--pie-acento); }
    .pk-pie-enlaces a:hover { background: var(--pie-chip); color: var(--pie-texto) !important; }

    .pk-pie-legal { text-align: right; }
    .pk-pie-copy { font-size: .74rem; font-weight: 600; color: var(--pie-texto); }
    .pk-pie-meta { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: .35rem .6rem; margin-top: .25rem; }
    .pk-pie-area {
        font-weight: 700; font-size: .64rem; letter-spacing: .06em; text-transform: uppercase; color: var(--pie-acento);
        background: var(--pie-chip); border-radius: 999px; padding: .12rem .55rem;
    }
    .pk-pie-version { font-style: normal; font-size: .68rem; color: var(--pie-suave); }
    .pk-pie-autor {
        display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 600;
        color: var(--pie-texto) !important; text-decoration: none !important; padding: .2rem .7rem; border-radius: 999px;
        background: var(--pie-chip); border: 1px solid var(--pie-borde); transition: border-color .15s ease, color .15s ease, transform .15s ease;
    }
    .pk-pie-autor i { color: var(--pie-acento); font-size: .68rem; }
    .pk-pie-autor strong { color: var(--pie-acento); font-weight: 800; letter-spacing: .02em; }
    .pk-pie-autor:hover { border-color: var(--pie-acento); transform: translateY(-1px); }

    @media (max-width: 991.98px) {
        .pk-pie-contenido { grid-template-columns: 1fr; justify-items: center; text-align: center; gap: .8rem; padding: 0 1rem; padding-bottom: 3.25rem; }
        .pk-pie-legal { text-align: center; }
        .pk-pie-meta { justify-content: center; }
    }
</style>
@endonce
