{{--
    Panel de usuarios de /Administrador, compartido por admin/index (rol admin) y
    superadmin/dashboard (rol super-admin). Datos desde AdminController@index:
    $listas (por rol), $gruposEstudiantes (Programa → Ciclo), $esSuperAdmin.
--}}
@include('partials.foto-visor')
@php
    $tabs = [
        'estudiantes' => [
            'label' => 'Estudiantes', 'icon' => 'fa-user-graduate',
            'tabs' => [
                'fid' => ['FID', 'fa-graduation-cap'],
                'ppd' => ['PPD', 'fa-user-graduate'],
                'inhabilitados' => ['Inhabilitados', 'fa-ban'],
            ],
        ],
        'personal' => [
            'label' => 'Personal', 'icon' => 'fa-id-badge',
            'tabs' => array_filter([
                'docentes' => ['Docentes', 'fa-chalkboard-teacher'],
                'tutores' => ['Tutores', 'fa-user-shield'],
                'admins' => ['Admins', 'fa-users-cog'],
                'titulacion' => ['Titulación', 'fa-user-tie'],
                'superadmins' => $esSuperAdmin ? ['Super Admins', 'fa-crown'] : null,
                'bolsa' => ['Bolsa', 'fa-briefcase'],
            ]),
        ],
    ];
    $motivosCanonicos = ['Deuda', 'Sin matrícula', 'Licencia', 'Retirado'];
    // Grupo del directorio: un ciclo, o una promoción PPD de egresados (clave "programa-pAÑO")
    $infoGrupo = function ($clave, $g) use (&$etiquetaCiclo) {
        if (preg_match('/-p(\d{4})$/', (string) $clave, $m)) {
            return ['token' => 'p'.$m[1], 'etiqueta' => 'Promoción '.$m[1].' (egresados)'];
        }
        $c = $g->first()->ciclo;

        return ['token' => (string) ($c->id ?? ''), 'etiqueta' => $etiquetaCiclo($c)];
    };
    $etiquetaCiclo = fn ($ciclo) => $ciclo
        ? (preg_match('/^[IVX]+$/', trim($ciclo->nombre)) ? 'Ciclo ' . trim($ciclo->nombre) : $ciclo->nombre)
        : 'Sin ciclo';
@endphp

<style>
    .up {
        --up-card: #fff;
        --up-soft: #f8fafc;
        --up-border: #e5e7eb;
        --up-text: #1e293b;
        --up-muted: #64748b;
        --up-accent: #4f46e5;
        --up-accent-soft: rgba(79, 70, 229, .08);
        --up-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 4px 14px rgba(15, 23, 42, .04);
        background: inherit;
    }

    .dark-mode .up,
    .dim-mode .up {
        --up-card: rgba(255, 255, 255, .035);
        --up-soft: rgba(255, 255, 255, .03);
        --up-border: rgba(255, 255, 255, .1);
        --up-text: #e2e8f0;
        --up-muted: #94a3b8;
        --up-accent: #a5b4fc;
        --up-accent-soft: rgba(165, 180, 252, .1);
        --up-shadow: none;
    }

    /* "hidden" siempre gana: antes un d-flex (con !important) dejaba los filtros de
       inhabilitados visibles en todas las pestañas. */
    .up [hidden] { display: none !important; }

    /* ── Navegación ── */
    .up-nav {
        position: sticky;
        top: 0;
        z-index: 6;
        background: inherit;
        padding: .6rem 0 .5rem;
        border-bottom: 1px solid var(--up-border);
        margin-bottom: .75rem;
    }

    .up-nav-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .6rem;
    }

    .up-seg {
        display: inline-flex;
        padding: 3px;
        border-radius: 10px;
        background: var(--up-soft);
        border: 1px solid var(--up-border);
    }

    .up-seg button,
    .up-tab {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border: 0;
        background: transparent;
        color: var(--up-muted);
        font-size: .8rem;
        font-weight: 600;
        padding: .38rem .85rem;
        border-radius: 7px;
        cursor: pointer;
        white-space: nowrap;
        transition: background .15s, color .15s, box-shadow .15s;
    }

    .up-seg button:hover,
    .up-tab:hover { color: var(--up-text); }

    .up-seg button.is-active {
        background: var(--up-card);
        color: var(--up-text);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
    }

    .dark-mode .up-seg button.is-active,
    .dim-mode .up-seg button.is-active { background: rgba(255, 255, 255, .1); box-shadow: none; }

    .up-count {
        font-size: .68rem;
        font-weight: 700;
        padding: .05rem .45rem;
        border-radius: 999px;
        background: var(--up-border);
        color: var(--up-muted);
    }

    .up-count.is-hit { background: var(--up-accent); color: #fff; }

    .up-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .2rem;
        margin-top: .5rem;
    }

    .up-tab { border-radius: 999px; padding: .32rem .8rem; }
    .up-tab.is-active { background: var(--up-accent-soft); color: var(--up-accent); }
    .up-tab.is-active .up-count { background: var(--up-accent); color: #fff; }
    .up-tab.is-empty { opacity: .45; }

    .up-search {
        position: relative;
        flex: 1 1 240px;
        max-width: 380px;
        margin-left: auto;
    }

    .up-search i {
        position: absolute;
        left: .7rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--up-muted);
        font-size: .78rem;
    }

    .up-search input,
    .up-select {
        height: 34px;
        font-size: .82rem;
        color: var(--up-text);
        background: var(--up-card);
        border: 1px solid var(--up-border);
        border-radius: 8px;
    }

    .up-search input { width: 100%; padding: 0 .75rem 0 2rem; }
    .up-select { padding: 0 .5rem; max-width: 220px; }

    .up-search input:focus,
    .up-select:focus {
        outline: none;
        border-color: var(--up-accent);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
    }

    .dark-mode .up-select option,
    .dim-mode .up-select option { background: #1f2937; }

    /* ── Filtros de la pestaña ── */
    .up-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .4rem;
        margin-bottom: .7rem;
    }

    .up-filters[hidden] { display: none; }

    .up-fchip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid var(--up-border);
        background: var(--up-card);
        color: var(--up-muted);
        font-size: .76rem;
        font-weight: 600;
        padding: .3rem .7rem;
        border-radius: 999px;
        cursor: pointer;
    }

    .up-fchip.is-active { border-color: var(--up-accent); color: var(--up-accent); background: var(--up-accent-soft); }
    .up-fchip.is-red.is-active { border-color: #dc2626; color: #dc2626; background: rgba(220, 38, 38, .07); }
    .dark-mode .up-fchip.is-red.is-active,
    .dim-mode .up-fchip.is-red.is-active { color: #fca5a5; border-color: #fca5a5; }

    .up-fsep { width: 1px; height: 22px; background: var(--up-border); margin: 0 .2rem; }

    /* ── Barra de matrícula ── */
    .up-matbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem .6rem;
        padding: .6rem .75rem;
        margin-bottom: .6rem;
        background: var(--up-card);
        border: 1px solid var(--up-border);
        border-radius: 12px;
        box-shadow: var(--up-shadow);
    }

    .up-matbar-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--up-muted); }
    .up-matchips { display: flex; flex-wrap: wrap; gap: .3rem; }
    .up-matbar-end { margin-left: auto; display: flex; align-items: center; gap: .6rem; }
    .up-selall { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; color: var(--up-muted); margin: 0; cursor: pointer; white-space: nowrap; }
    .up-selcount { font-size: .74rem; font-weight: 700; color: var(--up-accent); white-space: nowrap; }
    .up-check { width: 16px; height: 16px; flex-shrink: 0; accent-color: #4f46e5; cursor: pointer; }
    .up-row.is-selected { background: var(--up-accent-soft); border-radius: 8px; }

    .up-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .78rem;
        font-weight: 700;
        padding: .38rem .8rem;
        border-radius: 8px;
        border: 0;
        background: #16a34a;
        color: #fff;
        cursor: pointer;
        white-space: nowrap;
    }

    .up-btn-primary:hover { background: #15803d; }
    .up-fchip.is-green.is-active { border-color: #16a34a; color: #15803d; background: rgba(22, 163, 74, .08); }
    .up-fchip.is-amber.is-active { border-color: #d97706; color: #b45309; background: rgba(217, 119, 6, .08); }
    .dark-mode .up-fchip.is-green.is-active, .dim-mode .up-fchip.is-green.is-active { color: #86efac; border-color: #86efac; }
    .dark-mode .up-fchip.is-amber.is-active, .dim-mode .up-fchip.is-amber.is-active { color: #fcd34d; border-color: #fcd34d; }
    .up-chip.is-outline { background: transparent; box-shadow: inset 0 0 0 1px currentColor; }

    /* Menú: título de sección y opciones deshabilitadas */
    .up-menu-label { font-size: .64rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--up-muted); padding: .25rem .6rem .1rem; }
    .up-menu button:disabled { opacity: .45; cursor: not-allowed; }

    /* Exportar */
    .up-exp-label { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: .35rem; }
    .up-exp-opt { display: flex; align-items: flex-start; gap: .55rem; padding: .45rem .6rem; border: 1px solid #e5e7eb; border-radius: 9px; margin-bottom: .35rem; cursor: pointer; font-size: .84rem; }
    .up-exp-opt:has(input:checked) { border-color: #4f46e5; background: rgba(79, 70, 229, .06); }
    .up-exp-opt input { margin-top: .2rem; accent-color: #4f46e5; }
    .up-exp-opt[hidden] { display: none; }
    .dark-mode .up-exp-opt, .dim-mode .up-exp-opt { border-color: rgba(255, 255, 255, .12); }

    /* Aviso breve */
    .up-toast {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 1080;
        max-width: 360px;
        padding: .7rem 1rem;
        border-radius: 10px;
        background: #1e293b;
        color: #fff;
        font-size: .84rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .3);
    }

    .up-toast.is-error { background: #b91c1c; }

    /* Matrícula en la fila: chip + siguiente acción + fecha de envío */
    .up-mat { display: inline-flex; flex-wrap: wrap; align-items: center; gap: .3rem; }
    .up-mat-btn {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .68rem; font-weight: 700; padding: .14rem .55rem; border-radius: 999px;
        border: 1px solid var(--up-border); background: var(--up-card); color: var(--up-text); cursor: pointer;
    }
    .up-mat-btn:hover { border-color: var(--up-accent); color: var(--up-accent); }
    .up-mat-btn.is-primary { background: #0891b2; border-color: #0891b2; color: #fff; }
    .up-mat-btn.is-primary:hover { background: #0e7490; color: #fff; }
    .up-mat-btn:disabled { opacity: .5; cursor: progress; }
    .up-mat-fecha { font-size: .66rem; color: var(--up-muted); white-space: nowrap; }

    /* Panel lateral */
    .up-drawer-fondo { position: fixed; inset: 0; z-index: 1060; background: rgba(15, 23, 42, .45); }
    .up-drawer {
        position: fixed; top: 0; right: 0; bottom: 0; z-index: 1070;
        width: min(1100px, 92vw); display: flex; flex-direction: column;
        background: #fff; box-shadow: -20px 0 50px rgba(15, 23, 42, .25);
        transform: translateX(100%); transition: transform .25s ease; visibility: hidden;
    }
    .up-drawer.is-open { transform: none; visibility: visible; }
    .up-drawer { --up-card: #fff; --up-border: #e5e7eb; --up-text: #1e293b; --up-muted: #64748b; --up-accent: #4f46e5; }
    .dark-mode .up-drawer, .dim-mode .up-drawer { --up-card: rgba(255,255,255,.035); --up-border: rgba(255,255,255,.1); --up-text: #e2e8f0; --up-muted: #94a3b8; --up-accent: #a5b4fc; }
    .dark-mode .up-drawer, .dim-mode .up-drawer { background: #1f2937; }
    .up-drawer-head {
        display: flex; align-items: center; gap: .4rem; padding: .7rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .dark-mode .up-drawer-head, .dim-mode .up-drawer-head { border-color: rgba(255, 255, 255, .1); }
    .up-drawer-head h5 { flex: 1; margin: 0; font-size: .95rem; font-weight: 800; color: var(--up-text, #1e293b); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .up-drawer iframe { flex: 1; width: 100%; border: 0; background: transparent; }
    .up-drawer-carga { padding: 2rem; text-align: center; color: #64748b; font-size: .85rem; }
    @media (max-width: 767.98px) { .up-drawer { width: 100vw; } }

    .up-filters-end {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .76rem;
        color: var(--up-muted);
    }

    .up-link {
        border: 0;
        background: none;
        color: var(--up-accent);
        font-size: .76rem;
        font-weight: 600;
        padding: .2rem .3rem;
        cursor: pointer;
    }

    /* ── Grupos Programa → Ciclo ── */
    .up-group {
        background: var(--up-card);
        border: 1px solid var(--up-border);
        border-radius: 12px;
        box-shadow: var(--up-shadow);
        margin-bottom: .55rem;
    }

    .up-group-head {
        display: flex;
        align-items: center;
        gap: .6rem;
        width: 100%;
        border: 0;
        background: transparent;
        padding: .7rem 1rem;
        text-align: left;
        cursor: pointer;
        border-radius: 12px;
        color: var(--up-text);
    }

    .up-group-head:hover { background: var(--up-soft); }

    .up-group-chev {
        color: var(--up-muted);
        font-size: .7rem;
        transition: transform .2s;
        width: 12px;
    }

    .up-group.is-open .up-group-chev { transform: rotate(90deg); }

    .up-group-title { font-size: .86rem; font-weight: 700; }
    .up-group-prog { color: var(--up-muted); font-weight: 600; }
    .up-group-count { margin-left: auto; }

    .up-group-body {
        display: none;
        border-top: 1px solid var(--up-border);
        padding: .25rem .5rem .4rem;
    }

    .up-group.is-open .up-group-body { display: block; }

    .up-flat {
        background: var(--up-card);
        border: 1px solid var(--up-border);
        border-radius: 12px;
        box-shadow: var(--up-shadow);
        padding: .25rem .5rem .4rem;
    }

    /* ── Fila ── */
    .up-row + .up-row { border-top: 1px solid var(--up-border); }

    .up-row-main {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .55rem .5rem;
    }

    .up-avatar {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: 10px;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        color: #4338ca;
        background: #e0e7ff;
    }

    .up-avatar.is-red { color: #b91c1c; background: #fee2e2; }
    .up-avatar.is-violet { color: #6d28d9; background: #ede9fe; }
    .up-avatar.is-amber { color: #b45309; background: #fef3c7; }
    .up-avatar.is-cyan { color: #0e7490; background: #cffafe; }
    .up-avatar.is-green { color: #15803d; background: #dcfce7; }

    .dark-mode div.up-avatar,
    .dim-mode div.up-avatar { background: rgba(255, 255, 255, .08); color: var(--up-text); }

    .up-id { flex: 1 1 auto; min-width: 0; }
    .up-avatar-btn { flex-shrink: 0; border-radius: 10px; transition: transform .12s ease, box-shadow .12s ease; }
    .up-avatar-btn:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(15, 23, 42, .25); }
    .up-ref { max-width: 22ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .up-name {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem;
        font-size: .86rem;
        font-weight: 700;
        color: var(--up-text);
    }

    .up-name-link { color: inherit; text-decoration: none !important; }
    .up-name-link:hover { color: var(--up-accent); text-decoration: underline !important; }

    .up-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .1rem .7rem;
        font-size: .72rem;
        color: var(--up-muted);
        margin-top: .1rem;
    }

    .up-muted { color: var(--up-muted); opacity: .85; }

    .up-side {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .3rem;
        max-width: 40%;
    }

    .up-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .68rem;
        font-weight: 700;
        padding: .12rem .5rem;
        border-radius: 999px;
        border: 0;
        white-space: nowrap;
        line-height: 1.5;
        background: var(--up-soft);
        color: var(--up-muted);
    }

    button.up-chip { cursor: pointer; }
    .up-chip i { font-size: .62rem; }
    .up-chip.is-green { background: rgba(22, 163, 74, .1); color: #15803d; }
    .up-chip.is-amber { background: rgba(217, 119, 6, .1); color: #b45309; }
    .up-chip.is-red { background: rgba(220, 38, 38, .1); color: #b91c1c; }
    .up-chip.is-violet { background: rgba(124, 58, 237, .1); color: #6d28d9; }
    .up-chip.is-cyan { background: rgba(8, 145, 178, .1); color: #0e7490; }

    .dark-mode .up-chip.is-green, .dim-mode .up-chip.is-green { color: #86efac; }
    .dark-mode .up-chip.is-amber, .dim-mode .up-chip.is-amber { color: #fcd34d; }
    .dark-mode .up-chip.is-red, .dim-mode .up-chip.is-red { color: #fca5a5; }
    .dark-mode .up-chip.is-violet, .dim-mode .up-chip.is-violet { color: #c4b5fd; }
    .dark-mode .up-chip.is-cyan, .dim-mode .up-chip.is-cyan { color: #67e8f9; }

    .up-chev { transition: transform .2s; }
    .up-row.is-detail .up-chev { transform: rotate(180deg); }

    .up-detail {
        display: none;
        margin: 0 .5rem .6rem 3.4rem;
        padding: .5rem .8rem .5rem 1.8rem;
        font-size: .76rem;
        color: var(--up-text);
        background: var(--up-soft);
        border-radius: 8px;
    }

    .up-detail.is-plain { list-style: none; padding-left: .8rem; }
    .up-detail li + li { margin-top: .2rem; }
    .up-row.is-detail .up-detail { display: block; }

    .up-actions {
        display: flex;
        gap: .3rem;
        flex-shrink: 0;
    }

    .up-icon-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--up-border);
        background: var(--up-card);
        color: var(--up-muted);
        font-size: .75rem;
        cursor: pointer;
        text-decoration: none !important;
    }

    .up-icon-btn:hover { color: var(--up-accent); border-color: var(--up-accent); }

    .up-menu-wrap { position: relative; }

    .up-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        z-index: 20;
        min-width: 200px;
        padding: .3rem;
        background: #fff;
        border: 1px solid var(--up-border);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .15);
    }

    .dark-mode .up-menu,
    .dim-mode .up-menu { background: #1f2937; }

    .up-menu.is-open { display: block; }
    .up-menu.is-up { top: auto; bottom: calc(100% + 4px); }

    .up-menu a,
    .up-menu button {
        display: flex;
        align-items: center;
        gap: .55rem;
        width: 100%;
        padding: .45rem .6rem;
        font-size: .8rem;
        color: var(--up-text);
        background: none;
        border: 0;
        border-radius: 6px;
        text-align: left;
        text-decoration: none !important;
        cursor: pointer;
    }

    .up-menu a:hover,
    .up-menu button:hover { background: var(--up-soft); }
    .up-menu i { width: 14px; color: var(--up-muted); }
    .up-menu .is-danger,
    .up-menu .is-danger i { color: #dc2626; }
    .up-menu-sep { height: 1px; background: var(--up-border); margin: .3rem 0; }

    .up-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--up-muted);
        font-size: .85rem;
    }

    /* ── Diálogos nativos (no dependen de Bootstrap) ── */
    .up-dialog {
        border: 0;
        border-radius: 14px;
        padding: 0;
        width: min(440px, calc(100vw - 32px));
        box-shadow: 0 25px 60px rgba(15, 23, 42, .3);
        color: #1e293b;
    }

    .up-dialog::backdrop { background: rgba(15, 23, 42, .5); }
    .up-dialog-body { padding: 1.25rem 1.35rem 1rem; }
    .up-dialog h5 { font-size: 1rem; font-weight: 700; margin-bottom: .5rem; }
    .up-dialog p { font-size: .88rem; color: #475569; margin-bottom: 0; }

    .up-dialog-foot {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: .8rem 1.35rem 1.1rem;
    }

    .dark-mode .up-dialog,
    .dim-mode .up-dialog { background: #1f2937; color: #e2e8f0; }
    .dark-mode .up-dialog p,
    .dim-mode .up-dialog p { color: #cbd5e1; }

    @media (max-width: 767.98px) {
        .up-row-main { flex-wrap: wrap; }
        .up-id { flex-basis: calc(100% - 90px); }
        .up-side { order: 3; max-width: 100%; justify-content: flex-start; padding-left: 2.9rem; }
        .up-search { margin-left: 0; max-width: none; }
        .up-filters-end { margin-left: 0; width: 100%; justify-content: space-between; }
        .up-detail { margin-left: .5rem; }
    }
</style>

<div class="up" id="up">
    {{-- ── Navegación en dos niveles + buscador global ── --}}
    <div class="up-nav">
        <div class="up-nav-top">
            <div class="up-seg" role="tablist" aria-label="Tipo de usuario">
                @foreach ($tabs as $grupoKey => $grupo)
                    <button type="button" data-grupo="{{ $grupoKey }}" role="tab">
                        <i class="fas {{ $grupo['icon'] }} fa-xs"></i> {{ $grupo['label'] }}
                        <span class="up-count" data-count-grupo="{{ $grupoKey }}">
                            {{ collect(array_keys($grupo['tabs']))->sum(fn ($t) => $listas[$t]->count()) }}
                        </span>
                    </button>
                @endforeach
            </div>
            <div class="up-search">
                <i class="fas fa-search"></i>
                <input type="search" id="upBuscar" placeholder="Buscar en todos: nombre, DNI, email, N°…" autocomplete="off">
            </div>
        </div>

        @foreach ($tabs as $grupoKey => $grupo)
            <div class="up-tabs" data-tabs-de="{{ $grupoKey }}" role="tablist" hidden>
                @foreach ($grupo['tabs'] as $tabKey => [$label, $icon])
                    <button type="button" class="up-tab" data-tab="{{ $tabKey }}" role="tab">
                        <i class="fas {{ $icon }} fa-xs"></i> {{ $label }}
                        <span class="up-count" data-count-tab="{{ $tabKey }}" data-total="{{ $listas[$tabKey]->count() }}">{{ $listas[$tabKey]->count() }}</span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- ── Pestañas de estudiantes: agrupadas por Programa → Ciclo ── --}}
    @foreach ($gruposEstudiantes as $tabKey => $grupos)
        @php
            $cm = $conteoMatricula[$tabKey];
            $esPpdTab = $tabKey === 'ppd';
            $periodosTab = $esPpdTab ? $periodosPpd : $periodosFid;
            $periodoTab = $esPpdTab ? $periodoPpd : $periodoFid;
        @endphp
        <section class="up-pane" data-pane="{{ $tabKey }}" hidden>
            {{-- Matrícula del periodo: reemplaza a las antiguas pantallas "Matrículas FID / PPD" --}}
            <div class="up-matbar">
                <span class="up-matbar-label"><i class="fas fa-clipboard-check"></i> Matrícula</span>
                <select class="up-select js-periodo" data-param="{{ $esPpdTab ? 'periodo_ppd' : 'periodo' }}" aria-label="Periodo de matrícula">
                    @foreach ($periodosTab as $p)
                        @if ($esPpdTab)
                            {{-- PPD: periodo anual, matrícula por semestre → una opción por semestre (valor "periodo|semestre") --}}
                            @foreach ([1 => '1.er semestre', 2 => '2.º semestre'] as $sem => $etqSem)
                                <option value="{{ $p->id }}|{{ $sem }}" @selected($periodoTab?->id === $p->id && $semestrePpd === $sem)>
                                    {{ $p->nombre }} · {{ $etqSem }}{{ $p->actual && ($p->semestre_matricula ?? 1) == $sem ? ' (matrícula abierta)' : '' }}
                                </option>
                            @endforeach
                        @else
                            <option value="{{ $p->id }}" @selected($periodoTab?->id === $p->id)>{{ $p->nombre }}{{ $p->actual ? ' (vigente)' : '' }}</option>
                        @endif
                    @endforeach
                </select>
                <div class="up-matchips" role="group" aria-label="Estado de matrícula">
                    <button type="button" class="up-fchip js-f-mat" data-mat="">Todos</button>
                    <button type="button" class="up-fchip is-green js-f-mat" data-mat="matriculado"><i class="fas fa-check fa-xs"></i> Matriculados <span class="up-count">{{ $cm['matriculado'] }}</span></button>
                    <button type="button" class="up-fchip is-red js-f-mat" data-mat="falta" title="Sin matrícula en el periodo, incluidos quienes no llenaron la ficha"><i class="fas fa-user-clock fa-xs"></i> Faltan matricularse <span class="up-count">{{ $cm['sin_matricula'] + $cm['sin_ficha'] }}</span></button>
                    <button type="button" class="up-fchip is-amber js-f-mat" data-mat="por_verificar"><i class="fas fa-receipt fa-xs"></i> Voucher por verificar <span class="up-count">{{ $cm['por_verificar'] }}</span></button>
                    <button type="button" class="up-fchip js-f-mat" data-mat="sin_enviar"><i class="fas fa-paper-plane fa-xs"></i> Ficha sin enviar <span class="up-count">{{ $cm['sin_enviar'] }}</span></button>
                    <button type="button" class="up-fchip is-red js-f-mat" data-mat="sin_ficha" title="No llenaron la ficha de matrícula: no pueden matricularse"><i class="fas fa-file-excel fa-xs"></i> Sin ficha <span class="up-count">{{ $cm['sin_ficha'] }}</span></button>
                </div>
                <div class="up-matbar-end">
                    <label class="up-selall"><input type="checkbox" class="up-check js-sel-todos"> Seleccionar visibles</label>
                    <span class="up-selcount js-selcount" hidden></span>
                    <button type="button" class="up-btn-primary js-exportar"><i class="fas fa-file-excel"></i> Exportar</button>
                </div>
            </div>
            <div class="up-filters">
                <select class="up-select js-f-programa" aria-label="Filtrar por programa">
                    <option value="">Todos los programas</option>
                    @foreach ($grupos->map(fn ($g) => $g->first()->programa)->filter()->unique('id') as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->nombre }}</option>
                    @endforeach
                </select>
                <select class="up-select js-f-ciclo" aria-label="Filtrar por ciclo">
                    <option value="">Todos los ciclos</option>
                    @foreach ($grupos as $claveG => $g)
                        @php $ig = $infoGrupo($claveG, $g); @endphp
                        @if ($ig['token'] !== '')
                            <option value="{{ $ig['token'] }}" data-programa="{{ $g->first()->programa_id }}">
                                {{ $g->first()->programa->nombre ?? '' }} · {{ $ig['etiqueta'] }}
                            </option>
                        @endif
                    @endforeach
                </select>

                @if ($tabKey === 'fid')
                    <span class="up-fsep"></span>
                    <button type="button" class="up-fchip js-f-chip" data-chip="beca"><i class="fas fa-award fa-xs"></i> Con beca</button>
                @endif
                @if (in_array($tabKey, ['fid', 'inhabilitados'], true))
                    <button type="button" class="up-fchip js-f-chip" data-chip="pend"><i class="fas fa-hourglass-half fa-xs"></i> Con pendientes</button>
                @endif
                @if (in_array($tabKey, ['fid', 'ppd'], true) && $listas[$tabKey]->contains('condicion', 'Egresado'))
                    <button type="button" class="up-fchip js-f-chip" data-chip="sinegr"><i class="fas fa-eye-slash fa-xs"></i> Ocultar egresados</button>
                @endif
                @if (in_array($tabKey, ['fid', 'ppd'], true))
                    {{-- Alumnos que además tienen el rol Inhabilitado (p. ej. Licencia conserva el rol de alumno) --}}
                    @php $nInhab = $listas[$tabKey]->filter(fn ($u) => $u->roles->contains('name', 'inhabilitado'))->count(); @endphp
                    @if ($nInhab > 0)
                        <button type="button" class="up-fchip is-red js-f-chip" data-chip="inhab"><i class="fas fa-ban fa-xs"></i> Inhabilitados <span class="up-count">{{ $nInhab }}</span></button>
                    @endif
                @endif

                @if ($tabKey === 'inhabilitados')
                    @php
                        $porMotivo = collect($motivosCanonicos)->mapWithKeys(
                            fn ($m) => [$m => $listas['inhabilitados']->where('perfil', $m)->count()]
                        );
                        $otros = $listas['inhabilitados']->reject(fn ($u) => in_array($u->perfil, $motivosCanonicos, true))->count();
                    @endphp
                    <span class="up-fsep"></span>
                    <span class="up-muted" style="font-size:.74rem;"><i class="fas fa-filter fa-xs"></i> Motivo:</span>
                    @foreach ($motivosCanonicos as $m)
                        <button type="button" class="up-fchip is-red js-f-motivo" data-motivo="{{ $m }}">
                            {{ $m }} <span class="up-count">{{ $porMotivo[$m] }}</span>
                        </button>
                    @endforeach
                    @if ($otros > 0)
                        <button type="button" class="up-fchip is-red js-f-motivo" data-motivo="otro">
                            Otro / sin definir <span class="up-count">{{ $otros }}</span>
                        </button>
                    @endif
                @endif

                <div class="up-filters-end">
                    <span><strong class="js-visibles">{{ $listas[$tabKey]->count() }}</strong> de {{ $listas[$tabKey]->count() }}</span>
                    <button type="button" class="up-link js-toggle-grupos">Expandir todo</button>
                    <button type="button" class="up-link js-limpiar">Limpiar</button>
                </div>
            </div>

            @forelse ($grupos as $claveG => $g)
                @php $primero = $g->first(); $ig = $infoGrupo($claveG, $g); @endphp
                <div class="up-group" data-programa="{{ $primero->programa_id ?? '' }}" data-ciclo="{{ $ig['token'] }}">
                    <button type="button" class="up-group-head" aria-expanded="false">
                        <i class="fas fa-chevron-right up-group-chev"></i>
                        <span class="up-group-title">
                            <span class="up-group-prog">{{ $primero->programa->nombre ?? 'Sin programa' }} ·</span>
                            {{ $ig['etiqueta'] }}
                        </span>
                        <span class="up-count up-group-count" data-total="{{ $g->count() }}">{{ $g->count() }}</span>
                    </button>
                    <div class="up-group-body">
                        @foreach ($g as $u)
                            @include('admin.usuarios._fila', ['u' => $u, 'tipo' => $tabKey])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="up-empty">No hay registros en esta pestaña.</div>
            @endforelse
            <div class="up-empty js-sin-resultados" hidden>Ningún registro coincide con los filtros.</div>
        </section>
    @endforeach

    {{-- ── Pestañas de personal: lista simple ── --}}
    @foreach ($tabs['personal']['tabs'] as $tabKey => $_)
        <section class="up-pane" data-pane="{{ $tabKey }}" hidden>
            <div class="up-filters">
                <div class="up-filters-end">
                    <span><strong class="js-visibles">{{ $listas[$tabKey]->count() }}</strong> de {{ $listas[$tabKey]->count() }}</span>
                </div>
            </div>
            @if ($listas[$tabKey]->isEmpty())
                <div class="up-empty">No hay registros en esta pestaña.</div>
            @else
                <div class="up-flat">
                    @foreach ($listas[$tabKey] as $u)
                        @include('admin.usuarios._fila', ['u' => $u, 'tipo' => $tabKey])
                    @endforeach
                </div>
            @endif
            <div class="up-empty js-sin-resultados" hidden>Ningún registro coincide con la búsqueda.</div>
        </section>
    @endforeach
</div>

{{-- ── Diálogos ── --}}
<dialog class="up-dialog" id="upDlgEliminar">
    <div class="up-dialog-body">
        <h5><i class="fas fa-exclamation-triangle text-danger mr-1"></i> Eliminar usuario</h5>
        <p>¿Seguro que deseas eliminar a <strong id="upDlgEliminarNombre"></strong>? Esta acción no se puede deshacer.</p>
    </div>
    <div class="up-dialog-foot">
        <button type="button" class="btn btn-sm btn-light js-cerrar-dlg">Cancelar</button>
        <a href="#" id="upDlgEliminarOk" class="btn btn-sm btn-danger font-weight-bold"><i class="fas fa-trash mr-1"></i> Eliminar</a>
    </div>
</dialog>

<dialog class="up-dialog" id="upDlgMotivo">
    <form method="POST" id="upFormMotivo">
        @csrf
        @method('PUT')
        <input type="hidden" name="volver" id="upDlgMotivoVolver" value="inhabilitados">
        <div class="up-dialog-body">
            <h5><i class="fas fa-ban text-danger mr-1"></i> Cambiar motivo de inhabilitación</h5>
            <p class="mb-3">Alumno: <strong id="upDlgMotivoNombre"></strong></p>
            <select name="perfil" id="upDlgMotivoSelect" class="form-control form-control-sm" required>
                @foreach ($motivosCanonicos as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">El rol Inhabilitado no cambia: solo se reclasifica el motivo.</small>
        </div>
        <div class="up-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar-dlg">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar</button>
        </div>
    </form>
</dialog>

<dialog class="up-dialog" id="upDlgExportar">
    <form method="POST" action="{{ route('admin.estudiantes.exportar') }}" id="upFormExportar">
        @csrf
        <input type="hidden" name="tab" id="upExpTab">
        <input type="hidden" name="periodo" value="{{ $periodoFid?->id }}">
        <input type="hidden" name="periodo_ppd" value="{{ $periodoPpd?->id }}">
        <input type="hidden" name="semestre_ppd" value="{{ $semestrePpd }}">
        <div id="upExpIds"></div>
        <div class="up-dialog-body">
            <h5><i class="fas fa-file-excel text-success mr-1"></i> Exportar a Excel</h5>
            <div class="up-exp-label">Qué estudiantes</div>
            <label class="up-exp-opt"><input type="radio" name="_alcance" value="visibles" checked>
                <span><strong>Lo que estás viendo</strong> <span class="up-muted" id="upExpVisibles"></span><br>
                <small class="up-muted">Pestaña, periodo, filtros y búsqueda actuales.</small></span></label>
            <label class="up-exp-opt" id="upExpOptSel"><input type="radio" name="_alcance" value="seleccionados">
                <span><strong>Solo los seleccionados</strong> <span class="up-muted" id="upExpSeleccionados"></span></span></label>

            <div class="up-exp-label mt-3">Qué columnas</div>
            @foreach ([
                'basico' => ['Básico', 'Nombre, DNI, programa, ciclo, N°, condición, perfil y beca.'],
                'matricula' => ['Matrícula', 'Estado en el periodo, N° de voucher, verificación, ficha enviada y fecha.'],
                'contacto' => ['Contacto', 'Correo y teléfono.'],
                'ficha' => ['Ficha completa', 'Todos los datos de la ficha (socioeconómicos, vivienda, salud…). Solo estudiantes con ficha.'],
            ] as $k => [$titulo, $desc])
                <label class="up-exp-opt"><input type="radio" name="preset" value="{{ $k }}" @checked($k === 'matricula')>
                    <span><strong>{{ $titulo }}</strong><br><small class="up-muted">{{ $desc }}</small></span></label>
            @endforeach
        </div>
        <div class="up-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar-dlg">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-success font-weight-bold"><i class="fas fa-download mr-1"></i> Descargar Excel</button>
        </div>
    </form>
</dialog>

<dialog class="up-dialog" id="upDlgQuitarMat">
    <form method="POST" id="upFormQuitarMat">
        @csrf
        @method('DELETE')
        <div class="up-dialog-body">
            <h5><i class="fas fa-user-minus text-danger mr-1"></i> Quitar matrícula</h5>
            <p>¿Quitar la matrícula del periodo a <strong id="upQuitarMatNombre"></strong>? Su ficha y su cuenta se conservan.</p>
        </div>
        <div class="up-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar-dlg">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-danger font-weight-bold">Quitar matrícula</button>
        </div>
    </form>
</dialog>


{{-- Panel lateral: cuenta y roles / ficha de matrícula sin salir del directorio --}}
<div class="up-drawer-fondo" id="upDrawerFondo" hidden></div>
<aside class="up-drawer" id="upDrawer" aria-hidden="true" aria-labelledby="upDrawerTitulo" role="dialog">
    <header class="up-drawer-head">
        <h5 id="upDrawerTitulo">Detalle</h5>
        <button type="button" class="up-icon-btn" id="upDrawerCerrar" title="Cerrar (Esc)"><i class="fas fa-times"></i></button>
    </header>
    <div class="up-drawer-carga" id="upDrawerCarga"><i class="fas fa-circle-notch fa-spin"></i> Cargando…</div>
    <iframe id="upDrawerFrame" title="Detalle del usuario"></iframe>
</aside>
<div class="up-toast" id="upToast" role="status" aria-live="polite" hidden></div>

@php
    // Plantillas de URL del menú de matrícula (__F__ = id de ficha, __M__ = id de matrícula)
    $rutasMatricula = [
        'fid' => [
            'ficha' => route('alumnos.show', ['alumno' => '__F__']),
            'editar' => route('alumnos.edit', ['alumno' => '__F__']),
            'voucher' => route('matriculas.verificarVoucher', '__M__'),
            'enviar' => route('matriculas.enviarFicha', '__M__'),
            'quitar' => route('matriculas.quitar', '__M__'),
            'carnet' => route('admin.alumnos.carnet', ['alumno' => '__F__']),
        ],
        'ppd' => [
            'ficha' => route('ppd.show', '__F__'),
            'editar' => route('ppd.edit', '__F__'),
            'voucher' => route('matriculasppd.verificarVoucher', '__M__'),
            'enviar' => route('matriculasppd.enviarFicha', '__M__'),
            'quitar' => route('matriculasppd.quitar', '__M__'),
        ],
    ];
@endphp
<script>
(function () {
    var root = document.getElementById('up');
    if (!root) return;

    var GRUPO_DE = {};
    root.querySelectorAll('[data-tabs-de]').forEach(function (bar) {
        bar.querySelectorAll('[data-tab]').forEach(function (t) { GRUPO_DE[t.dataset.tab] = bar.dataset.tabsDe; });
    });
    // Nombres de pestaña antiguos (#inhabilitados ya lo usa el redirect de "cambiar motivo")
    var ALIAS = { admins: 'fid', alumnosppd: 'ppd', alumnos: 'admins', adminsB: 'bolsa' };
    var CLAVE = 'adminUsuarios.estado';

    var estado = { tab: 'fid', q: '', programa: '', ciclo: '', beca: false, pend: false, sinegr: false, inhab: false, motivo: '', mat: '' };
    var buscar = document.getElementById('upBuscar');
    var espera;

    function norm(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
    function pane(tab) { return root.querySelector('[data-pane="' + tab + '"]'); }

    // ── Estado en la URL (#tab=…&programa=…) y en sessionStorage, para volver al mismo
    //    lugar tras editar (el redirect del edit vuelve a /Administrador sin hash) ──
    function leerEstado() {
        var h = location.hash.replace(/^#/, '');
        var guardado = null;
        try { guardado = JSON.parse(sessionStorage.getItem(CLAVE) || 'null'); } catch (e) {}
        if (h && h.indexOf('=') === -1) {
            var tab = ALIAS[h] || h;
            if (GRUPO_DE[tab]) estado.tab = tab;
            return;
        }
        if (h) {
            var p = new URLSearchParams(h);
            Object.keys(estado).forEach(function (k) {
                if (!p.has(k)) return;
                estado[k] = (k === 'beca' || k === 'pend' || k === 'sinegr' || k === 'inhab') ? p.get(k) === '1' : p.get(k);
            });
        } else if (guardado) {
            Object.keys(estado).forEach(function (k) { if (k in guardado) estado[k] = guardado[k]; });
        }
        if (!GRUPO_DE[estado.tab]) estado.tab = 'fid';
    }

    function guardarEstado() {
        var p = new URLSearchParams();
        Object.keys(estado).forEach(function (k) {
            var v = estado[k];
            if (v === true) p.set(k, '1');
            else if (v && v !== 'fid') p.set(k, v);
            else if (k === 'tab') p.set(k, v);
        });
        try { history.replaceState(null, '', '#' + p.toString()); } catch (e) {}
        try { sessionStorage.setItem(CLAVE, JSON.stringify(estado)); } catch (e) {}
    }

    // ── Filtrado ──
    function coincideFila(row, terminos, filtrosPestana, conFiltros) {
        if (terminos.length && !terminos.every(function (t) { return row.dataset.buscar.indexOf(t) !== -1; })) return false;
        if (!conFiltros) return true;
        if (filtrosPestana.beca && row.dataset.beca !== '1') return false;
        if (filtrosPestana.pend && row.dataset.pend === '0') return false;
        if (filtrosPestana.sinegr && row.dataset.egresado === '1') return false;
        if (filtrosPestana.inhab && row.dataset.inhab !== '1') return false;
        if (filtrosPestana.motivo && row.dataset.motivo !== filtrosPestana.motivo) return false;
        if (filtrosPestana.mat && !coincideMatricula(row, filtrosPestana.mat)) return false;
        return true;
    }

    function aplicar() {
        var terminos = norm(estado.q).split(/\s+/).filter(Boolean);
        var totalGrupo = {};

        root.querySelectorAll('.up-pane').forEach(function (p) {
            var tab = p.dataset.pane;
            var activa = tab === estado.tab;
            // Los filtros de programa/ciclo/beca/motivo solo aplican a la pestaña visible;
            // la búsqueda aplica a todas para poder mostrar coincidencias por pestaña.
            var f = activa ? estado : {};
            var visibles = 0;

            p.querySelectorAll('.up-group').forEach(function (g) {
                var okGrupo = (!f.programa || g.dataset.programa === f.programa) && (!f.ciclo || g.dataset.ciclo === f.ciclo);
                var n = 0;
                g.querySelectorAll('.up-row').forEach(function (r) {
                    var ok = okGrupo && coincideFila(r, terminos, f, activa);
                    r.hidden = !ok;
                    if (ok) n++;
                });
                g.hidden = n === 0;
                var c = g.querySelector('.up-group-count');
                c.textContent = (n === +c.dataset.total) ? n : n + ' / ' + c.dataset.total;
                if (activa && (terminos.length || f.ciclo)) abrirGrupo(g, n > 0);
                visibles += n;
            });

            p.querySelectorAll('.up-flat > .up-row').forEach(function (r) {
                var ok = coincideFila(r, terminos, f, activa);
                r.hidden = !ok;
                if (ok) visibles++;
            });

            var vis = p.querySelector('.js-visibles');
            if (vis) vis.textContent = visibles;
            var sin = p.querySelector('.js-sin-resultados');
            if (sin) sin.hidden = visibles > 0 || !p.querySelector('.up-row');

            var badge = root.querySelector('[data-count-tab="' + tab + '"]');
            var n = terminos.length ? visibles : +badge.dataset.total;
            if (activa && !terminos.length) n = +badge.dataset.total;
            badge.textContent = n;
            badge.classList.toggle('is-hit', terminos.length > 0 && n > 0);
            badge.closest('.up-tab').classList.toggle('is-empty', terminos.length > 0 && n === 0);
            totalGrupo[GRUPO_DE[tab]] = (totalGrupo[GRUPO_DE[tab]] || 0) + n;

            p.hidden = !activa;
        });

        Object.keys(totalGrupo).forEach(function (g) {
            var b = root.querySelector('[data-count-grupo="' + g + '"]');
            b.textContent = totalGrupo[g];
            b.classList.toggle('is-hit', terminos.length > 0 && totalGrupo[g] > 0);
        });

        pintarNavegacion();
        guardarEstado();
    }

    function pintarNavegacion() {
        var grupo = GRUPO_DE[estado.tab];
        root.querySelectorAll('[data-grupo]').forEach(function (b) {
            var on = b.dataset.grupo === grupo;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-selected', on);
        });
        root.querySelectorAll('[data-tabs-de]').forEach(function (bar) { bar.hidden = bar.dataset.tabsDe !== grupo; });
        root.querySelectorAll('.up-tab').forEach(function (t) {
            var on = t.dataset.tab === estado.tab;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on);
        });

        // Sincroniza los controles de la pestaña activa con el estado
        var p = pane(estado.tab);
        var selP = p.querySelector('.js-f-programa'), selC = p.querySelector('.js-f-ciclo');
        if (selP) selP.value = estado.programa;
        if (selC) {
            selC.querySelectorAll('option[data-programa]').forEach(function (o) {
                o.hidden = !!estado.programa && o.dataset.programa !== estado.programa;
            });
            selC.value = estado.ciclo;
        }
        p.querySelectorAll('.js-f-chip').forEach(function (c) { c.classList.toggle('is-active', !!estado[c.dataset.chip]); });
        p.querySelectorAll('.js-f-motivo').forEach(function (c) { c.classList.toggle('is-active', c.dataset.motivo === estado.motivo); });
        p.querySelectorAll('.js-f-mat').forEach(function (c) { c.classList.toggle('is-active', c.dataset.mat === (estado.mat || '')); });
        actualizarSeleccion();
    }

    function abrirGrupo(g, abierto) {
        g.classList.toggle('is-open', abierto);
        g.querySelector('.up-group-head').setAttribute('aria-expanded', abierto);
    }

    function cambiarTab(tab) {
        if (tab === estado.tab) return;
        estado.tab = tab;
        estado.programa = estado.ciclo = estado.motivo = '';
        estado.beca = estado.pend = estado.sinegr = estado.inhab = false;
        estado.mat = '';
        aplicar();
    }

    // Atajos desde fuera del panel (p. ej. el indicador de becas del encabezado):
    // <a data-up-filtro="tab=fid&beca=1&sinegr=1"> cambia de pestaña y aplica esos filtros.
    document.addEventListener('click', function (e) {
        var a = e.target.closest('[data-up-filtro]');
        if (!a) return;
        e.preventDefault();
        var p = new URLSearchParams(a.dataset.upFiltro);
        if (p.get('tab') && GRUPO_DE[p.get('tab')]) cambiarTab(p.get('tab'));
        p.forEach(function (v, k) {
            if (k !== 'tab' && k in estado) estado[k] = (k === 'beca' || k === 'pend' || k === 'sinegr' || k === 'inhab') ? v === '1' : v;
        });
        aplicar();
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // ── Eventos ──
    root.querySelectorAll('[data-grupo]').forEach(function (b) {
        b.addEventListener('click', function () {
            var primera = root.querySelector('[data-tabs-de="' + b.dataset.grupo + '"] [data-tab]');
            if (GRUPO_DE[estado.tab] !== b.dataset.grupo) cambiarTab(primera.dataset.tab);
        });
    });
    root.querySelectorAll('.up-tab').forEach(function (t) {
        t.addEventListener('click', function () { cambiarTab(t.dataset.tab); });
    });

    buscar.addEventListener('input', function () {
        clearTimeout(espera);
        espera = setTimeout(function () { estado.q = buscar.value; aplicar(); }, 130);
    });

    root.querySelectorAll('.up-pane').forEach(function (p) {
        var selP = p.querySelector('.js-f-programa'), selC = p.querySelector('.js-f-ciclo');
        if (selP) selP.addEventListener('change', function () {
            estado.programa = selP.value;
            var op = selC.querySelector('option[value="' + estado.ciclo + '"]');
            if (estado.ciclo && op && op.dataset.programa !== estado.programa && estado.programa) estado.ciclo = '';
            aplicar();
        });
        if (selC) selC.addEventListener('change', function () {
            estado.ciclo = selC.value;
            var op = selC.selectedOptions[0];
            if (op && op.dataset.programa) estado.programa = op.dataset.programa;
            aplicar();
        });
        p.querySelectorAll('.js-f-chip').forEach(function (c) {
            c.addEventListener('click', function () { estado[c.dataset.chip] = !estado[c.dataset.chip]; aplicar(); });
        });
        p.querySelectorAll('.js-f-motivo').forEach(function (c) {
            c.addEventListener('click', function () {
                estado.motivo = estado.motivo === c.dataset.motivo ? '' : c.dataset.motivo;
                aplicar();
            });
        });
        var limpiar = p.querySelector('.js-limpiar');
        if (limpiar) limpiar.addEventListener('click', function () {
            estado.programa = estado.ciclo = estado.motivo = estado.q = '';
            estado.beca = estado.pend = estado.sinegr = estado.inhab = false;
        estado.mat = '';
            buscar.value = '';
            aplicar();
        });
        var toggle = p.querySelector('.js-toggle-grupos');
        if (toggle) toggle.addEventListener('click', function () {
            var expandir = toggle.textContent.trim() === 'Expandir todo';
            p.querySelectorAll('.up-group:not([hidden])').forEach(function (g) { abrirGrupo(g, expandir); });
            toggle.textContent = expandir ? 'Contraer todo' : 'Expandir todo';
        });
        p.querySelectorAll('.up-group-head').forEach(function (h) {
            h.addEventListener('click', function () {
                var g = h.closest('.up-group');
                abrirGrupo(g, !g.classList.contains('is-open'));
            });
        });
    });

    // Detalle de pendientes / cursos
    root.addEventListener('click', function (e) {
        var det = e.target.closest('.js-detalle');
        if (det) {
            var row = det.closest('.up-row');
            row.classList.toggle('is-detail');
            det.setAttribute('aria-expanded', row.classList.contains('is-detail'));
        }
    });

    // Menú "⋯" (vanilla: el layout carga dos Bootstrap y sus dropdowns no son fiables)
    function cerrarMenus(excepto) {
        root.querySelectorAll('.up-menu.is-open').forEach(function (m) {
            if (m === excepto) return;
            m.classList.remove('is-open');
            m.previousElementSibling.setAttribute('aria-expanded', 'false');
        });
    }
    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-menu');
        if (btn) {
            var menu = btn.nextElementSibling;
            cerrarMenus(menu);
            var abrir = !menu.classList.contains('is-open');
            menu.classList.toggle('is-open', abrir);
            btn.setAttribute('aria-expanded', abrir);
            if (abrir) {
                menu.classList.remove('is-up');
                if (menu.getBoundingClientRect().bottom > window.innerHeight - 8) menu.classList.add('is-up');
            }
            return;
        }
        if (!e.target.closest('.up-menu')) cerrarMenus();
    });
    document.addEventListener('click', function (e) { if (!root.contains(e.target)) cerrarMenus(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarMenus(); });

    // ── Diálogos ──
    var dlgEliminar = document.getElementById('upDlgEliminar');
    var dlgMotivo = document.getElementById('upDlgMotivo');
    function abrirDialogo(d) { if (d.showModal) d.showModal(); else d.setAttribute('open', ''); }
    document.querySelectorAll('.js-cerrar-dlg').forEach(function (b) {
        b.addEventListener('click', function () { b.closest('dialog').close(); });
    });
    [dlgEliminar, dlgMotivo].forEach(function (d) {
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });

    root.addEventListener('click', function (e) {
        var del = e.target.closest('.js-eliminar');
        if (del) {
            cerrarMenus();
            document.getElementById('upDlgEliminarNombre').textContent = del.dataset.nombre;
            document.getElementById('upDlgEliminarOk').href = del.dataset.url;
            abrirDialogo(dlgEliminar);
            return;
        }
        var mot = e.target.closest('.js-motivo');
        if (mot) {
            cerrarMenus();
            document.getElementById('upDlgMotivoNombre').textContent = mot.dataset.nombre;
            document.getElementById('upDlgMotivoSelect').value = mot.dataset.perfil || 'Deuda';
            document.getElementById('upFormMotivo').action = '{{ url('admin/inhabilitados') }}/' + mot.dataset.id + '/perfil';
            // Al guardar, volver a la pestaña desde donde se abrió (FID, PPD o Inhabilitados).
            document.getElementById('upDlgMotivoVolver').value = estado.tab;
            abrirDialogo(dlgMotivo);
        }
    });

    // ══ Matrícula (antes pantallas aparte "Matrículas FID / PPD") ══════════════════════
    // Plantillas de URL (una sola vez para toda la página): __F__ = id de ficha, __M__ = id de matrícula.
    var RUTAS = {!! json_encode($rutasMatricula, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!};
    var ES_SUPERADMIN = {{ $esSuperAdmin ? 'true' : 'false' }};

    function url(plantilla, ficha, mat) {
        return plantilla.replace('__F__', encodeURIComponent(ficha)).replace('__M__', encodeURIComponent(mat));
    }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    function armarMenuMatricula(cont) {
        if (cont.dataset.listo) return;
        var r = RUTAS[cont.dataset.mod], f = cont.dataset.ficha, m = cont.dataset.matid;
        var h = '<div class="up-menu-sep"></div><div class="up-menu-label">Matrícula</div>'
            + '<a role="menuitem" class="js-drawer" data-titulo="Ficha de matrícula · ' + esc(cont.dataset.nombre) + '" href="' + url(r.ficha, f, m) + '"><i class="fas fa-id-card-alt"></i> Ver ficha de matrícula</a>'
            + '<a role="menuitem" class="js-drawer" data-titulo="Editar ficha · ' + esc(cont.dataset.nombre) + '" href="' + url(r.editar, f, m) + '"><i class="fas fa-edit"></i> Editar ficha de matrícula</a>';
        if (m) {
            h += '<button type="button" role="menuitem" class="js-voucher" data-url="' + url(r.voucher, f, m) + '"><i class="fas fa-check-circle"></i> <span class="js-voucher-txt"></span></button>'
               + '<button type="button" role="menuitem" class="js-enviar" data-url="' + url(r.enviar, f, m) + '"><i class="fas fa-paper-plane"></i> <span class="js-enviar-txt"></span></button>';
        }
        if (r.carnet && cont.dataset.foto === '1') {
            // La pantalla de carnets ya trae sus botones "Descargar PNG": se abre en el panel.
            h += '<a role="menuitem" class="js-drawer" data-titulo="Carnets · ' + esc(cont.dataset.nombre) + '" href="' + url(r.carnet, f, m) + '"><i class="fas fa-address-card"></i> Ver y descargar carnets</a>';
        }
        if (m && ES_SUPERADMIN) {
            h += '<button type="button" role="menuitem" class="is-danger js-quitar-mat" data-url="' + url(r.quitar, f, m) + '" data-nombre="' + esc(cont.dataset.nombre) + '"><i class="fas fa-user-minus"></i> Quitar matrícula del periodo</button>';
        }
        cont.innerHTML = h + '<div class="up-menu-sep"></div>';
        cont.dataset.listo = '1';
        pintarMatricula(cont.closest('.up-row'));
    }
    // Se arma justo antes de que el menú se abra (fase de captura: corre antes que el toggle).
    root.addEventListener('click', function (e) {
        var b = e.target.closest('.js-menu');
        if (!b) return;
        var cont = b.nextElementSibling.querySelector('.js-menu-mat');
        if (cont) armarMenuMatricula(cont);
    }, true);

    function coincideMatricula(row, filtro) {
        var m = row.dataset.mat, v = row.dataset.voucher, e = row.dataset.enviada;
        switch (filtro) {
            case 'matriculado': return m === 'matriculado';
            case 'falta': return m === 'sin_matricula' || m === 'sin_ficha';
            case 'por_verificar': return m === 'matriculado' && v === '0';
            case 'sin_enviar': return m === 'matriculado' && v === '1' && e === '0';
            case 'sin_ficha': return m === 'sin_ficha';
        }
        return true;
    }

    // Estado de matrícula en la fila: chip + botón de la SIGUIENTE acción + fecha del último
    // envío. Se pinta aquí (y no en Blade) para repintarlo igual tras cada acción.
    function pintarMatricula(row) {
        var cont = row.querySelector('.js-mat');
        if (!cont) return;
        var m = row.dataset.mat, v = row.dataset.voucher === '1', e = row.dataset.enviada === '1';
        var r = RUTAS[row.dataset.mod] || {}, id = row.dataset.matid;
        var c = { cls: 'is-red', ico: 'fa-user-clock', txt: 'Sin matrícula', tip: 'Tiene ficha pero no se matriculó en este periodo' };
        var accion = '', fecha = '';
        if (m === 'sin_ficha') c = { cls: 'is-red is-outline', ico: 'fa-file-excel', txt: 'Sin ficha', tip: 'No llenó la ficha de matrícula: no puede matricularse' };
        if (m === 'matriculado') {
            if (!v) {
                c = { cls: 'is-amber', ico: 'fa-receipt', txt: 'Voucher por verificar', tip: 'Matriculado; falta verificar el voucher' };
                accion = '<button type="button" class="up-mat-btn js-voucher" data-url="' + url(r.voucher, '', id) + '" title="Marcar el voucher como verificado"><i class="fas fa-check"></i> Verificar</button>';
            } else if (!e) {
                c = { cls: 'is-cyan', ico: 'fa-paper-plane', txt: 'Ficha sin enviar', tip: 'Voucher verificado; falta enviar la ficha por correo' };
                accion = '<button type="button" class="up-mat-btn is-primary js-enviar" data-url="' + url(r.enviar, '', id) + '" title="Enviar la ficha de matrícula por correo"><i class="fas fa-paper-plane"></i> Enviar</button>';
            } else {
                c = { cls: 'is-green', ico: 'fa-check', txt: 'Matriculado', tip: 'Voucher verificado y ficha enviada' };
                accion = '<button type="button" class="up-mat-btn js-enviar" data-url="' + url(r.enviar, '', id) + '" title="Volver a enviar la ficha por correo"><i class="fas fa-redo"></i> Reenviar</button>';
            }
            if (row.dataset.enviadaAt) fecha = '<span class="up-mat-fecha" title="Último envío de la ficha por correo"><i class="fas fa-envelope"></i> ' + esc(row.dataset.enviadaAt) + '</span>';
        }
        cont.innerHTML = '<span class="up-chip ' + c.cls + '" title="' + c.tip + '"><i class="fas ' + c.ico + '"></i> ' + c.txt + '</span>' + accion + fecha;

        // Opciones equivalentes dentro del menú ⋯ (si ya se armó)
        var menu = row.querySelector('.up-menu');
        var bEnviar = menu && menu.querySelector('.js-enviar');
        if (bEnviar) {
            bEnviar.disabled = !v;
            bEnviar.title = v ? '' : 'Primero marca el voucher como verificado';
            bEnviar.querySelector('.js-enviar-txt').textContent = e ? 'Reenviar ficha por correo' : 'Enviar ficha por correo';
        }
        var bVoucher = menu && menu.querySelector('.js-voucher-txt');
        if (bVoucher) bVoucher.textContent = v ? 'Quitar verificación del voucher' : 'Marcar voucher como verificado';
    }
    root.querySelectorAll('.up-row[data-mat]').forEach(pintarMatricula);

    root.querySelectorAll('.js-f-mat').forEach(function (c) {
        c.addEventListener('click', function () {
            estado.mat = estado.mat === c.dataset.mat ? '' : c.dataset.mat;
            aplicar();
        });
    });

    // Cambiar de periodo recarga con ?periodo= (o ?periodo_ppd=) y conserva pestaña y filtros (#…)
    root.querySelectorAll('.js-periodo').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var params = new URLSearchParams(location.search);
            var partes = sel.value.split('|'); // PPD: "periodo|semestre"
            params.set(sel.dataset.param, partes[0]);
            if (partes[1]) params.set('semestre_ppd', partes[1]);
            location.href = location.pathname + '?' + params.toString() + location.hash;
        });
    });

    // ── Aviso breve (toast) ──
    var toast = document.getElementById('upToast'), toastT;
    function avisar(texto, error) {
        toast.textContent = texto;
        toast.className = 'up-toast' + (error ? ' is-error' : '');
        toast.hidden = false;
        clearTimeout(toastT);
        toastT = setTimeout(function () { toast.hidden = true; }, 3500);
    }

    // ── Acciones en línea (sin recargar): verificar voucher / enviar ficha ──
    var csrf = '{{ csrf_token() }}';
    function post(url) {
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { j._ok = r.ok; return j; }); });
    }
    root.addEventListener('click', function (e) {
        var b = e.target.closest('.js-voucher, .js-enviar');
        if (!b || b.disabled) return;
        var row = b.closest('.up-row');
        cerrarMenus();
        b.disabled = true;
        var esVoucher = b.classList.contains('js-voucher');
        post(b.dataset.url).then(function (j) {
            if (!j._ok || j.success === false) { avisar(j.message || 'No se pudo completar la acción.', true); return; }
            if (esVoucher) row.dataset.voucher = row.dataset.voucher === '1' ? '0' : '1';
            else { row.dataset.enviada = '1'; if (j.ficha_enviada_at) row.dataset.enviadaAt = j.ficha_enviada_at; }
            pintarMatricula(row);
            avisar(j.message || (esVoucher ? 'Voucher actualizado.' : 'Ficha enviada.'));
            aplicar();
        }).catch(function () { avisar('Error de conexión. Intenta de nuevo.', true); })
          .finally(function () { b.disabled = false; pintarMatricula(row); });
    });

    // ══ Panel lateral: Cuenta y roles / Ficha de matrícula sin salir del directorio ══════
    // Carga la pantalla de siempre en un iframe (mismo origen) y le oculta el menú lateral y la
    // barra superior. Al GUARDAR (submit que termina en otra página) se cierra y se recarga el
    // directorio; si el guardado vuelve a la misma página es un error de validación: se queda.
    var drawer = document.getElementById('upDrawer'), fondo = document.getElementById('upDrawerFondo');
    var frame = document.getElementById('upDrawerFrame'), carga = document.getElementById('upDrawerCarga');
    var drawerEstado = { enviado: false, rutaAlEnviar: null };
    var CSS_EMBEBIDO = '#accordionSidebar,.sidebar,nav.topbar,.topbar,footer.sticky-footer,.scroll-to-top{display:none!important}'
        + '#content-wrapper{margin-left:0!important;width:100%!important}#wrapper{display:block!important}'
        + 'body{padding:0!important;background:transparent}#content{padding-top:.5rem}';

    function abrirDrawer(href, titulo) {
        drawerEstado = { enviado: false, rutaAlEnviar: null };
        document.getElementById('upDrawerTitulo').textContent = titulo || 'Detalle';
        carga.hidden = false;
        frame.style.visibility = 'hidden';
        frame.src = href;
        fondo.hidden = false;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    // Dentro del panel basta con su botón de cerrar (✕): se ocultan los "Volver" / "Regresar" de
    // cada pantalla (usan el historial del navegador y dejaban el panel en blanco) y los enlaces
    // "Cancelar". Solo enlaces <a> para Cancelar: los botones "Cancelar" de modales internos siguen.
    function prepararEmbebido(doc) {
        doc.querySelectorAll('a, button').forEach(function (el) {
            var txt = (el.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            var href = (el.getAttribute('href') || '').toLowerCase();
            var esVolver = /^(volver|regresar)\b/.test(txt) || href.indexOf('history.back') !== -1 || href.indexOf('history.go') !== -1;
            var esCancelar = el.tagName === 'A' && /^cancelar\b/.test(txt);
            if (esVolver || esCancelar) el.style.setProperty('display', 'none', 'important');
        });
    }

    function cerrarDrawer() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        fondo.hidden = true;
        document.body.style.overflow = '';
        setTimeout(function () { if (!drawer.classList.contains('is-open')) frame.src = 'about:blank'; }, 300);
    }
    frame.addEventListener('load', function () {
        var doc, ruta;
        try { doc = frame.contentDocument; ruta = frame.contentWindow.location.pathname; } catch (e) { return; }
        if (!doc || ruta === 'blank' || frame.src === 'about:blank') return;

        // ¿Terminó un guardado? (envió un formulario y la respuesta es OTRA página)
        if (drawerEstado.enviado) {
            if (ruta !== drawerEstado.rutaAlEnviar) {
                cerrarDrawer();
                avisar('Cambios guardados. Actualizando la lista…');
                setTimeout(function () { location.reload(); }, 400);
                return;
            }
            drawerEstado.enviado = false; // misma página: volvió con errores de validación
        }
        // Si navegó de vuelta al directorio (p. ej. "Volver") se cierra sin más.
        if (ruta === location.pathname) { cerrarDrawer(); return; }

        var st = doc.createElement('style');
        st.textContent = CSS_EMBEBIDO;
        (doc.head || doc.documentElement).appendChild(st);
        prepararEmbebido(doc);
        doc.addEventListener('submit', function (ev) {
            if (ev.defaultPrevented) return;
            drawerEstado.enviado = true;
            drawerEstado.rutaAlEnviar = frame.contentWindow.location.pathname;
        });
        carga.hidden = true;
        frame.style.visibility = 'visible';
    });
    document.addEventListener('click', function (e) {
        var a = e.target.closest('.js-drawer');
        if (!a || e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return; // Ctrl/clic medio: pestaña nueva
        e.preventDefault();
        cerrarMenus();
        abrirDrawer(a.href, a.dataset.titulo);
    });
    document.getElementById('upDrawerCerrar').addEventListener('click', cerrarDrawer);
    fondo.addEventListener('click', cerrarDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.classList.contains('is-open')) cerrarDrawer(); });

    // Reenviar la ficha manda otro correo al alumno: se confirma.
    root.addEventListener('click', function (e) {
        var b = e.target.closest('.js-enviar');
        var row = b && b.closest('.up-row');
        if (!b || !row || row.dataset.enviada !== '1') return;
        // Segundo clic, ya confirmado: deja pasar al manejador que envía.
        if (b.dataset.pkConfirmado === '1') { delete b.dataset.pkConfirmado; return; }
        var texto = 'La ficha ya se envió el ' + (row.dataset.enviadaAt || '—') + '.';
        e.stopImmediatePropagation();
        e.preventDefault();
        if (!window.PkAlert) {
            if (confirm(texto + ' ¿Reenviarla por correo?')) { b.dataset.pkConfirmado = '1'; b.click(); }
            return;
        }
        PkAlert.confirmar({ icon: 'question', title: '¿Reenviar la ficha por correo?', text: texto + ' El estudiante recibirá otro correo.', confirmButtonText: 'Sí, reenviar' })
            .then(function (ok) { if (ok) { b.dataset.pkConfirmado = '1'; b.click(); } });
    }, true);

    // ── Quitar matrícula (solo super-admin) ──
    var dlgQuitar = document.getElementById('upDlgQuitarMat');
    root.addEventListener('click', function (e) {
        var b = e.target.closest('.js-quitar-mat');
        if (!b) return;
        cerrarMenus();
        document.getElementById('upFormQuitarMat').action = b.dataset.url;
        document.getElementById('upQuitarMatNombre').textContent = b.dataset.nombre;
        abrirDialogo(dlgQuitar);
    });
    dlgQuitar.addEventListener('click', function (e) { if (e.target === dlgQuitar) dlgQuitar.close(); });

    // ── Selección y exportación de "lo que ves" ──
    function filasVisibles(p) {
        return Array.prototype.filter.call(p.querySelectorAll('.up-row'), function (r) {
            return !r.hidden && !(r.closest('.up-group') || {}).hidden;
        });
    }
    function actualizarSeleccion() {
        var p = pane(estado.tab);
        if (!p) return;
        var marcadas = p.querySelectorAll('.js-sel:checked').length;
        var cont = p.querySelector('.js-selcount');
        if (cont) { cont.hidden = !marcadas; cont.textContent = marcadas + (marcadas === 1 ? ' seleccionado' : ' seleccionados'); }
        var todos = p.querySelector('.js-sel-todos');
        if (todos) {
            var vis = filasVisibles(p);
            var marcadasVis = vis.filter(function (r) { return r.querySelector('.js-sel').checked; }).length;
            todos.checked = vis.length > 0 && marcadasVis === vis.length;
            todos.indeterminate = marcadasVis > 0 && marcadasVis < vis.length;
        }
    }
    root.addEventListener('change', function (e) {
        if (e.target.classList.contains('js-sel')) {
            e.target.closest('.up-row').classList.toggle('is-selected', e.target.checked);
            actualizarSeleccion();
        }
        if (e.target.classList.contains('js-sel-todos')) {
            filasVisibles(e.target.closest('.up-pane')).forEach(function (r) {
                var cb = r.querySelector('.js-sel');
                cb.checked = e.target.checked;
                r.classList.toggle('is-selected', cb.checked);
            });
            actualizarSeleccion();
        }
    });

    var dlgExp = document.getElementById('upDlgExportar');
    var formExp = document.getElementById('upFormExportar');
    root.addEventListener('click', function (e) {
        if (!e.target.closest('.js-exportar')) return;
        var p = pane(estado.tab);
        var vis = filasVisibles(p).length, sel = p.querySelectorAll('.js-sel:checked').length;
        document.getElementById('upExpVisibles').textContent = '(' + vis + ')';
        document.getElementById('upExpSeleccionados').textContent = '(' + sel + ')';
        document.getElementById('upExpOptSel').hidden = sel === 0;
        formExp.querySelector('[name="_alcance"][value="' + (sel ? 'seleccionados' : 'visibles') + '"]').checked = true;
        document.getElementById('upExpTab').value = estado.tab;
        abrirDialogo(dlgExp);
    });
    formExp.addEventListener('submit', function (e) {
        var p = pane(estado.tab);
        var soloSel = formExp.querySelector('[name="_alcance"]:checked').value === 'seleccionados';
        var filas = soloSel ? Array.prototype.map.call(p.querySelectorAll('.js-sel:checked'), function (c) { return c.closest('.up-row'); }) : filasVisibles(p);
        if (!filas.length) { e.preventDefault(); avisar('No hay estudiantes para exportar con los filtros actuales.', true); return; }
        var cont = document.getElementById('upExpIds');
        cont.innerHTML = '';
        filas.forEach(function (r) {
            var i = document.createElement('input');
            i.type = 'hidden'; i.name = 'ids[]'; i.value = r.dataset.id;
            cont.appendChild(i);
        });
        setTimeout(function () { dlgExp.close(); avisar('Preparando el Excel con ' + filas.length + ' estudiante(s)…'); }, 50);
    });
    dlgExp.addEventListener('click', function (e) { if (e.target === dlgExp) dlgExp.close(); });

    // ── Inicio ──
    leerEstado();
    buscar.value = estado.q || '';
    aplicar();
})();
</script>
