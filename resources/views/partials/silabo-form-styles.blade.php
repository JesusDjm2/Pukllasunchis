<style>
    /* ══ Sílabo: crear / editar ═════════════════════════════════════════════════
       Diseño del formulario largo (3–5 h de trabajo): índice lateral, secciones en
       tarjetas, tablas limpias, unidades numeradas y barra fija de guardado con el
       estado del borrador local.
       El bloque "I. Datos Generales" conserva su TABLA original (formato que exige el
       Ministerio de Educación y que se exporta a PDF): solo va dentro de una tarjeta. */

    .silabo-form {
        --sf-oro: #c78d40;
        --sf-oro-fuerte: #a8722c;
        --sf-oro-suave: #fbf3e6;
        --sf-oro-borde: #f0dfc2;
        --sf-texto: #2e3448;
        --sf-suave: #6b7185;
        --sf-borde: #e6e9f1;
        --sf-fondo: #fff;
        --sf-fondo-2: #f7f8fb;
        --sf-cab: #f2f4f9;
        --sf-cab-oscura: #2f3a4f;
        --sf-verde: #15936a;
        --sf-rojo: #c0392b;
    }
    body.dark-mode .silabo-form {
        --sf-texto: #e6e8ef; --sf-suave: #9aa3b5; --sf-borde: #30363d; --sf-fondo: #161b22;
        --sf-fondo-2: #0d1117; --sf-cab: #21262d; --sf-cab-oscura: #12161c; --sf-oro-suave: rgba(199, 141, 64, .14);
        --sf-oro-borde: rgba(199, 141, 64, .3);
    }
    body.dim-mode .silabo-form {
        --sf-texto: var(--lm-texto); --sf-suave: var(--lm-suave); --sf-borde: var(--lm-borde); --sf-fondo: var(--lm-superficie);
        --sf-fondo-2: var(--lm-superficie-2); --sf-cab: var(--lm-superficie-3); --sf-cab-oscura: var(--lm-bg-hondo);
        --sf-oro-suave: rgba(199, 141, 64, .16); --sf-oro-borde: rgba(199, 141, 64, .32);
    }

    /* sb-admin-2 pone overflow-x:hidden en #content-wrapper: eso lo vuelve el contenedor de
       desplazamiento de todo lo sticky de adentro y, como el que se desplaza es la página, el
       índice y la barra de guardado nunca se quedaban fijos. "clip" recorta igual en
       horizontal pero no rompe position:sticky. Solo en las páginas de sílabo. */
    body:has(.silabo-form) #wrapper #content-wrapper { overflow-x: clip; }

    /* ── Estructura: índice + contenido ── */
    .silabo-layout { display: grid; grid-template-columns: 236px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .silabo-layout-indice {
        position: sticky; top: calc(var(--sf-tope, 4.375rem) + 1rem); align-self: start;
        max-height: calc(100vh - var(--sf-tope, 4.375rem) - 2rem); overflow-y: auto; scrollbar-width: thin;
    }
    @media (max-width: 1199.98px) {
        .silabo-layout { grid-template-columns: minmax(0, 1fr); gap: .75rem; }
        .silabo-layout-indice { position: sticky; top: var(--sf-tope, 4.375rem); z-index: 1015; max-height: none; overflow: visible; }
    }

    /* ── Índice ── */
    .silabo-indice {
        background: var(--sf-fondo); border: 1px solid var(--sf-borde); border-radius: .75rem;
        box-shadow: 0 .1rem 1rem rgba(46, 52, 72, .06); padding: .9rem .6rem .8rem;
    }
    .silabo-indice-titulo {
        font-size: .66rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
        color: var(--sf-oro); padding: 0 .55rem .5rem;
    }
    .silabo-indice-lista { list-style: none; margin: 0; padding: 0; }
    .silabo-indice-lista a {
        display: flex; align-items: center; gap: .55rem; padding: .42rem .55rem; border-radius: .5rem;
        color: var(--sf-suave); font-size: .8rem; font-weight: 600; line-height: 1.25; text-decoration: none !important;
        transition: background-color .15s ease, color .15s ease;
    }
    .silabo-indice-lista a:hover { background: var(--sf-fondo-2); color: var(--sf-texto); }
    .silabo-indice-num {
        flex: none; min-width: 1.6rem; height: 1.6rem; border-radius: .45rem; display: inline-flex;
        align-items: center; justify-content: center; font-size: .66rem; font-weight: 800;
        background: var(--sf-fondo-2); color: var(--sf-suave); border: 1px solid var(--sf-borde);
    }
    .silabo-indice-nombre { flex: 1; min-width: 0; }
    .silabo-indice-estado { flex: none; width: .5rem; height: .5rem; border-radius: 50%; background: var(--sf-borde); }
    .silabo-indice-lista li.is-completa .silabo-indice-estado { background: var(--sf-verde); }
    .silabo-indice-lista li.is-info .silabo-indice-estado { background: transparent; }
    .silabo-indice-lista li.is-actual a { background: var(--sf-oro-suave); color: var(--sf-texto); }
    .silabo-indice-lista li.is-actual .silabo-indice-num { background: var(--sf-oro); border-color: var(--sf-oro); color: #fff; }
    .silabo-indice-progreso { padding: .75rem .55rem 0; margin-top: .5rem; border-top: 1px solid var(--sf-borde); }
    .silabo-indice-barra { height: 6px; border-radius: 999px; background: var(--sf-fondo-2); overflow: hidden; margin-bottom: .35rem; }
    .silabo-indice-barra span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, var(--sf-oro), var(--sf-verde)); transition: width .4s ease; }
    .silabo-indice-progreso small { color: var(--sf-suave); font-size: .7rem; }

    @media (max-width: 1199.98px) {
        .silabo-indice { padding: .45rem; border-radius: .6rem; display: flex; align-items: center; gap: .5rem; }
        .silabo-indice-titulo, .silabo-indice-progreso { display: none; }
        .silabo-indice-lista { display: flex; gap: .25rem; overflow-x: auto; scrollbar-width: thin; }
        .silabo-indice-lista a { white-space: nowrap; padding: .35rem .6rem; }
        .silabo-indice-nombre { max-width: 11rem; overflow: hidden; text-overflow: ellipsis; }
    }

    /* ── Cabecera compacta: Volver · curso · programa/ciclo/periodo · logo ── */
    .silabo-cabecera {
        display: flex; align-items: center; gap: 1.1rem;
        background: var(--sf-fondo); border: 1px solid var(--sf-borde); border-left: 4px solid var(--sf-oro);
        border-radius: .75rem; box-shadow: 0 .1rem 1rem rgba(46, 52, 72, .06);
        padding: .8rem 1.2rem; margin-bottom: 1.25rem;
    }
    .silabo-cabecera .docente-ui-volver { flex: none; width: auto; }
    .silabo-cabecera-texto { flex: 1; min-width: 0; }
    .silabo-cabecera-accion { font-size: .66rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--sf-oro); }
    .silabo-cabecera-curso {
        font-size: 1.2rem; font-weight: 800; color: var(--sf-texto); margin: .05rem 0 .35rem; line-height: 1.2;
        letter-spacing: -.01em;
    }
    .silabo-cabecera-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
    .silabo-cabecera-chips span {
        display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 600;
        color: var(--sf-suave); background: var(--sf-fondo-2); border: 1px solid var(--sf-borde);
        border-radius: 999px; padding: .15rem .6rem;
    }
    .silabo-cabecera-chips i { color: var(--sf-oro); font-size: .68rem; }
    .silabo-cabecera-logo { flex: none; height: 46px; width: auto; }
    body.dark-mode .silabo-cabecera-logo, body.dim-mode .silabo-cabecera-logo { filter: brightness(1.15); }
    @media (max-width: 767.98px) {
        .silabo-cabecera { flex-wrap: wrap; }
        .silabo-cabecera-logo { display: none; }
    }

    /* ── Secciones ── */
    .silabo-section {
        background: var(--sf-fondo); border: 1px solid var(--sf-borde); border-radius: .75rem;
        box-shadow: 0 .1rem 1rem rgba(46, 52, 72, .06);
        padding: 1.35rem 1.5rem 1.5rem; margin-bottom: 1.25rem; scroll-margin-top: calc(var(--sf-tope, 4.375rem) + 1rem);
    }
    @media (max-width: 1199.98px) { .silabo-section { scroll-margin-top: calc(var(--sf-tope, 4.375rem) + 4.25rem); } }
    @media (max-width: 575.98px) { .silabo-section { padding: 1rem; } }
    .silabo-section-header {
        display: flex; align-items: center; gap: .75rem;
        margin-bottom: 1.15rem; padding-bottom: .85rem; border-bottom: 1px solid var(--sf-borde);
    }
    .silabo-section-icon {
        flex: none; width: 38px; height: 38px; border-radius: .6rem; display: flex; align-items: center; justify-content: center;
        background: var(--sf-oro-suave); color: var(--sf-oro); font-size: 1rem; border: 1px solid var(--sf-oro-borde);
    }
    .silabo-section-header h4 { margin: 0; font-weight: 800; font-size: 1.05rem; color: var(--sf-texto) !important; letter-spacing: -.01em; }
    .silabo-section-header .silabo-section-eyebrow {
        display: block; font-size: .66rem; letter-spacing: .1em; text-transform: uppercase;
        color: var(--sf-oro); font-weight: 800; margin-bottom: .1rem;
    }

    /* ── Sección I (Datos generales): plegada por defecto y con su tabla ORIGINAL ── */
    .silabo-section-datos { padding-top: 1rem; padding-bottom: 1rem; }
    .silabo-section-datos .silabo-section-header { margin-bottom: 0; padding-bottom: 0; border-bottom: 0; }
    .silabo-section-datos:has(.silabo-datos-cuerpo:not([hidden])) .silabo-section-header {
        margin-bottom: 1rem; padding-bottom: .85rem; border-bottom: 1px solid var(--sf-borde);
    }
    .silabo-datos-toggle {
        margin-left: auto; display: inline-flex; align-items: center; gap: .4rem; cursor: pointer;
        background: var(--sf-fondo-2); color: var(--sf-texto); border: 1px solid var(--sf-borde);
        border-radius: 999px; font-size: .76rem; font-weight: 700; padding: .35rem .85rem;
        transition: background-color .15s ease;
    }
    .silabo-datos-toggle:hover { background: var(--sf-oro-suave); }
    .silabo-datos-toggle i { font-size: .65rem; color: var(--sf-oro); transition: transform .2s ease; }
    .silabo-datos-toggle[aria-expanded="true"] i { transform: rotate(180deg); }
    /* La tabla del Ministerio no lleva los estilos de tabla del resto del formulario. */
    .silabo-section.silabo-section-datos .table { border: 0 !important; border-radius: 0; overflow: visible; background: transparent; }
    .silabo-section.silabo-section-datos .table th, .silabo-section.silabo-section-datos .table td { border: 0 !important; }

    /* Etiquetas de campos sueltos (Sección III) */
    .silabo-section > .mb-3 > label, .silabo-section > .mb-0 > label {
        display: block; font-size: .78rem; font-weight: 700; color: var(--sf-texto); margin-bottom: .35rem;
    }
    .silabo-form .form-control {
        border-color: var(--sf-borde); border-radius: .45rem; color: var(--sf-texto); background-color: var(--sf-fondo);
        font-size: .85rem; transition: border-color .15s ease, box-shadow .15s ease;
    }
    .silabo-form .form-control:focus { border-color: var(--sf-oro); box-shadow: 0 0 0 .18rem rgba(199, 141, 64, .18); }
    .silabo-form .form-control[readonly] { background: var(--sf-fondo-2); color: var(--sf-suave); }
    .silabo-form textarea.form-control { line-height: 1.45; min-height: 3.2rem; }

    /* CKEditor dentro de la tarjeta */
    .silabo-section .cke { border-radius: .5rem; overflow: hidden; border-color: var(--sf-borde) !important; box-shadow: none !important; }

    /* ── Tablas (enfoques, matriz, unidades, rúbricas) ── */
    .silabo-section .table {
        border-collapse: separate; border-spacing: 0; border: 1px solid var(--sf-borde) !important;
        border-radius: .6rem; overflow: hidden; background: var(--sf-fondo); color: var(--sf-texto); margin-bottom: 1rem;
    }
    .silabo-section .table th, .silabo-section .table td {
        border: 0 !important; border-bottom: 1px solid var(--sf-borde) !important; border-right: 1px solid var(--sf-borde) !important;
        vertical-align: top; padding: .6rem .65rem;
    }
    .silabo-section .table tr > :last-child { border-right: 0 !important; }
    .silabo-section .table tbody tr:last-child > * { border-bottom: 0 !important; }
    .silabo-section .table thead th {
        background: var(--sf-cab); color: var(--sf-suave); font-size: .7rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; vertical-align: middle;
    }
    .silabo-section .table thead.thead-dark th { background: var(--sf-cab-oscura); color: #fff; }
    .silabo-section .table thead.bg-secondary th,
    .silabo-section .table thead.bg-secondary { background: var(--sf-cab) !important; color: var(--sf-suave) !important; }
    .silabo-section .table thead th .form-control { text-transform: none; letter-spacing: normal; font-weight: 600; font-size: .9rem; }
    .silabo-section .table td .form-control { border-color: transparent; background: var(--sf-fondo-2); }
    .silabo-section .table td .form-control:hover { border-color: var(--sf-borde); }
    .silabo-section .table td .form-control:focus { background: var(--sf-fondo); border-color: var(--sf-oro); }
    .silabo-section .table p { margin-bottom: .35rem; }
    /* La matriz y las unidades usan pares de tablas pegadas (cabecera + detalle). */
    .silabo-section .table + .table[style*="margin-top"] { margin-top: -1rem !important; border-top-left-radius: 0; border-top-right-radius: 0; }
    .silabo-section .table:has(+ .table[style*="margin-top"]) { border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-bottom: 0 !important; }

    /* ── Unidades como tarjetas numeradas ── */
    #contenedor-tablas { counter-reset: unidad; }
    #contenedor-tablas .unidad {
        counter-increment: unidad; position: relative; border: 1px solid var(--sf-oro-borde); border-radius: .7rem;
        padding: 2.4rem .9rem .8rem; margin-bottom: 1rem; background: var(--sf-oro-suave);
    }
    #contenedor-tablas .unidad::before {
        content: 'Unidad ' counter(unidad); position: absolute; top: .75rem; left: 1rem;
        font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--sf-oro-fuerte);
    }
    #contenedor-tablas .unidad .table { margin-bottom: .75rem; }
    #contenedor-tablas .unidad .btn-eliminar { position: absolute; top: .5rem; right: .75rem; margin: 0 !important; }

    /* ── Botones de agregar / eliminar ── */
    #btn-agregar, #btn-agregar-fila, #agregarEnfoque {
        display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
        background: transparent !important; color: var(--sf-oro-fuerte) !important; border: 1.5px dashed var(--sf-oro) !important;
        border-radius: .55rem; font-weight: 700; font-size: .82rem; padding: .5rem 1rem; box-shadow: none !important;
    }
    #btn-agregar, #btn-agregar-fila { width: 100%; }
    #btn-agregar:hover, #btn-agregar-fila:hover, #agregarEnfoque:hover { background: var(--sf-oro-suave) !important; }
    .silabo-form .btn-eliminar, .silabo-form .btn-eliminar-fila, .silabo-form .eliminarEnfoque {
        background: transparent !important; color: var(--sf-rojo) !important; border: 1px solid rgba(192, 57, 43, .35) !important;
        border-radius: .45rem; font-weight: 600; font-size: .74rem; padding: .25rem .6rem; box-shadow: none !important;
    }
    .silabo-form .btn-eliminar:hover, .silabo-form .btn-eliminar-fila:hover, .silabo-form .eliminarEnfoque:hover {
        background: rgba(192, 57, 43, .08) !important;
    }

    /* ── Aviso de borrador (sutil, no modal) ── */
    .silabo-aviso {
        display: flex; align-items: center; gap: .9rem; flex-wrap: wrap;
        background: var(--sf-oro-suave); border: 1px solid var(--sf-oro-borde); border-left: 4px solid var(--sf-oro);
        border-radius: .65rem; padding: .75rem 1rem; margin-bottom: 1.25rem; color: var(--sf-texto); font-size: .85rem;
    }
    .silabo-aviso[hidden] { display: none; }
    .silabo-aviso-icono { color: var(--sf-oro); font-size: 1.05rem; }
    .silabo-aviso-cuerpo { flex: 1; min-width: 14rem; }
    .silabo-aviso-cuerpo strong { display: block; font-size: .88rem; }
    .silabo-aviso-acciones { display: flex; gap: .35rem; align-items: center; }
    .silabo-btn-recuperar { background: var(--sf-oro); color: #fff !important; font-weight: 700; border-radius: .45rem; }
    .silabo-btn-recuperar:hover { background: var(--sf-oro-fuerte); }
    .silabo-btn-descartar { color: var(--sf-suave) !important; font-weight: 600; }

    /* ── Barra fija de guardado ── */
    .silabo-barra {
        position: sticky; bottom: 0; z-index: 1020; display: flex; align-items: center; gap: 1rem;
        margin: 1.5rem -.25rem 0; padding: .75rem 1rem; border-radius: .75rem .75rem 0 0;
        background: color-mix(in srgb, var(--sf-fondo) 92%, transparent); backdrop-filter: blur(8px);
        border: 1px solid var(--sf-borde); border-bottom: 0; box-shadow: 0 -.4rem 1.5rem rgba(46, 52, 72, .1);
    }
    .silabo-estado { display: inline-flex; align-items: center; gap: .5rem; font-size: .8rem; font-weight: 600; color: var(--sf-suave); min-width: 0; }
    .silabo-estado-texto { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .silabo-estado-punto { flex: none; width: .55rem; height: .55rem; border-radius: 50%; background: var(--sf-borde); }
    .silabo-estado.is-ok .silabo-estado-punto { background: var(--sf-verde); }
    .silabo-estado.is-pendiente .silabo-estado-punto { background: var(--sf-oro); animation: silaboLatido 1.2s ease-in-out infinite; }
    @@keyframes silaboLatido { 50% { opacity: .35; } }
    .silabo-barra-ayuda { font-size: .74rem; color: var(--sf-suave); }
    .silabo-barra-ayuda i { color: var(--sf-verde); }
    .silabo-btn-guardar {
        margin-left: auto; background: var(--sf-oro); color: #fff !important; font-weight: 800; border-radius: .55rem;
        padding: .55rem 1.4rem; box-shadow: 0 4px 12px rgba(199, 141, 64, .35); white-space: nowrap;
    }
    .silabo-btn-guardar:hover { background: var(--sf-oro-fuerte); }
    @media (prefers-reduced-motion: reduce) { .silabo-estado.is-pendiente .silabo-estado-punto { animation: none; } }
</style>
