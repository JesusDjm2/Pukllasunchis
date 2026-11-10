{{-- Estilos de las pantallas de trámite del módulo (generar RD, iniciar proceso): tarjetas numeradas,
     integrantes como tarjetas + buscador, barra de acciones fija. Claro / oscuro / tenue. --}}
@once
<style>
    .rdg { --rdg-card:#fff; --rdg-border:#e5e7eb; --rdg-text:#1e293b; --rdg-muted:#64748b; --rdg-soft:#f8fafc; color:var(--rdg-text); max-width:1100px; }
    .dark-mode .rdg { --rdg-card:#161b22; --rdg-border:rgba(255,255,255,.1); --rdg-text:#e6edf3; --rdg-muted:#8b949e; --rdg-soft:rgba(255,255,255,.04); }
    .dim-mode .rdg { --rdg-card:var(--lm-superficie); --rdg-border:var(--lm-borde); --rdg-text:var(--lm-texto); --rdg-muted:var(--lm-suave); --rdg-soft:rgba(255,255,255,.04); }
    .rdg-card { background:var(--rdg-card); border:1px solid var(--rdg-border); border-radius:14px; padding:1rem 1.15rem; margin-bottom:1rem; }
    .rdg-h { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:var(--rdg-muted); margin-bottom:.75rem; display:flex; align-items:center; gap:.5rem; }
    .rdg-h .n { width:22px; height:22px; border-radius:50%; background:#4e73df; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:.7rem; }
    .rdg label { font-size:.76rem; font-weight:700; margin-bottom:.2rem; color:var(--rdg-text); }
    .rdg .form-control { background:var(--rdg-card); color:var(--rdg-text); border-color:var(--rdg-border); }
    .rdg-ayuda { font-size:.72rem; color:var(--rdg-muted); }
    .rdg-lista { max-height:270px; overflow-y:auto; border:1px solid var(--rdg-border); border-radius:10px; }
    .rdg-persona { display:flex; align-items:center; gap:.6rem; padding:.45rem .7rem; border-bottom:1px solid var(--rdg-border); margin:0; cursor:pointer; font-weight:400; }
    .rdg-persona:last-child { border-bottom:0; }
    .rdg-persona:hover { background:var(--rdg-soft); }
    .rdg-persona input { width:16px; height:16px; flex-shrink:0; }
    .rdg-persona small { color:var(--rdg-muted); }
    .rdg-persona .rdg-aviso { margin-left:auto; font-size:.68rem; font-weight:700; color:#b45309; white-space:nowrap; }
    .rdg-elegidos { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:.5rem; margin-bottom:.75rem; }
    .rdg-chip { display:flex; align-items:flex-start; gap:.55rem; padding:.55rem .65rem; border:1px solid var(--rdg-border); border-radius:10px; background:var(--rdg-soft); }
    .rdg-chip.is-titular { border-color:#4f46e5; box-shadow:inset 3px 0 0 #4f46e5; }
    .rdg-chip-ini { width:30px; height:30px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:800; color:#4338ca; background:#e0e7ff; }
    .rdg-chip-txt { flex:1; min-width:0; font-size:.82rem; line-height:1.3; }
    .rdg-chip-txt small { display:block; color:var(--rdg-muted); font-size:.7rem; }
    .rdg-chip-txt .rdg-aviso, .rdg-res .rdg-aviso { display:inline-block; margin-top:.2rem; font-size:.68rem; font-weight:700; color:#b45309; }
    .rdg-chip button { border:0; background:transparent; width:24px; height:24px; border-radius:50%; font-size:1.1rem; line-height:1; color:var(--rdg-muted); cursor:pointer; flex-shrink:0; }
    .rdg-chip button:hover { background:rgba(220,38,38,.1); color:#dc2626; }
    .rdg-chip-lock { color:var(--rdg-muted); font-size:.75rem; margin-top:.3rem; }
    .rdg-agregar { position:relative; max-width:560px; }
    .rdg-resultados { position:absolute; z-index:20; left:0; right:0; top:100%; margin-top:.25rem; background:var(--rdg-card); border:1px solid var(--rdg-border); border-radius:10px; box-shadow:0 10px 30px rgba(15,23,42,.15); overflow:hidden; }
    .rdg-res { display:block; width:100%; text-align:left; border:0; border-bottom:1px solid var(--rdg-border); background:transparent; padding:.5rem .7rem; font-size:.82rem; color:var(--rdg-text); cursor:pointer; }
    .rdg-res:last-child { border-bottom:0; }
    .rdg-res:hover:not(:disabled) { background:var(--rdg-soft); }
    .rdg-res small { color:var(--rdg-muted); }
    .rdg-res.is-bloqueado { opacity:.6; cursor:not-allowed; }
    .rdg-res-falta { display:block; font-size:.7rem; color:#b91c1c; margin-top:.15rem; }
    .rdg-res-vacio { padding:.6rem .75rem; font-size:.8rem; color:var(--rdg-muted); }
    .dark-mode .rdg-chip-ini, .dim-mode .rdg-chip-ini { background:rgba(165,180,252,.15); color:#c7d2fe; }
    .rdg-numero { display:flex; align-items:center; gap:.35rem; }
    .rdg-numero span { font-size:.82rem; color:var(--rdg-muted); white-space:nowrap; }
    .rdg-acciones { position:sticky; bottom:0; display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; padding:.8rem 1.15rem; background:var(--rdg-card); border:1px solid var(--rdg-border); border-radius:14px; box-shadow:0 -6px 20px rgba(15,23,42,.08); }

    /* RD anteriores (pantalla de generar RD): pestaña flotante a media altura + panel lateral (drawer). */
    .rdp-pestana, .rdp-panel { --rdp-card:#fff; --rdp-soft:#f8fafc; --rdp-border:#e5e7eb; --rdp-text:#1e293b; --rdp-muted:#64748b; --rdp-acento:#4e73df; }
    .dark-mode .rdp-pestana, .dark-mode .rdp-panel { --rdp-card:#161b22; --rdp-soft:rgba(255,255,255,.04); --rdp-border:rgba(255,255,255,.1); --rdp-text:#e6edf3; --rdp-muted:#8b949e; --rdp-acento:#818cf8; }
    .dim-mode .rdp-pestana, .dim-mode .rdp-panel { --rdp-card:var(--lm-superficie); --rdp-soft:rgba(255,255,255,.04); --rdp-border:var(--lm-borde); --rdp-text:var(--lm-texto); --rdp-muted:var(--lm-suave); --rdp-acento:var(--lm-lavanda-claro, #a5b4fc); }

    .rdp-pestana { position:fixed; right:0; top:50vh; transform:translateY(-50%); z-index:1040;
        display:flex; flex-direction:column; align-items:center; gap:.55rem; padding:.9rem .5rem .75rem;
        border:0; border-radius:14px 0 0 14px; cursor:pointer; color:#fff;
        background:linear-gradient(180deg, #5a7ff0 0%, #4e73df 55%, #3b5bc4 100%);
        box-shadow:-6px 8px 24px rgba(30,58,138,.28), inset 1px 0 0 rgba(255,255,255,.18);
        transition:padding-right .18s ease, box-shadow .18s ease, opacity .2s; }
    .rdp-pestana:hover, .rdp-pestana:focus-visible { padding-right:.85rem; box-shadow:-10px 10px 30px rgba(30,58,138,.38), inset 1px 0 0 rgba(255,255,255,.22); outline:none; }
    .rdp-pestana:focus-visible { box-shadow:0 0 0 3px rgba(78,115,223,.35), -10px 10px 30px rgba(30,58,138,.38); }
    .rdp-pestana i { font-size:.95rem; }
    .rdp-pestana-txt { writing-mode:vertical-rl; transform:rotate(180deg); font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .rdp-pestana-n { min-width:22px; height:22px; padding:0 .3rem; border-radius:999px; background:#fff; color:#3b5bc4; font-size:.7rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; }
    body.rdp-abierto .rdp-pestana { opacity:0; pointer-events:none; }

    .rdp-fondo { position:fixed; inset:0; z-index:1060; background:rgba(15,23,42,.45); opacity:0; transition:opacity .25s ease; }
    .rdp-fondo.is-open { opacity:1; }
    .rdp-panel { position:fixed; top:0; right:0; bottom:0; z-index:1070; width:min(480px, 94vw);
        display:flex; flex-direction:column; background:var(--rdp-card); color:var(--rdp-text);
        box-shadow:-20px 0 50px rgba(15,23,42,.25); transform:translateX(100%); visibility:hidden;
        transition:transform .25s ease, width .25s ease, visibility 0s linear .25s; }
    .rdp-panel.is-open { transform:none; visibility:visible; transition:transform .25s ease, width .25s ease; }
    .rdp-panel.is-visor { width:min(1000px, 94vw); }
    .rdp-head { display:flex; align-items:center; gap:.5rem; padding:.8rem 1rem; border-bottom:1px solid var(--rdp-border); }
    .rdp-head-txt { flex:1; min-width:0; }
    .rdp-head h5 { margin:0; font-size:.98rem; font-weight:800; color:var(--rdp-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .rdp-head small { display:block; font-size:.72rem; color:var(--rdp-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .rdp-icono { width:32px; height:32px; flex-shrink:0; border:0; border-radius:8px; background:transparent; color:var(--rdp-muted); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; text-decoration:none; }
    .rdp-icono:hover { background:var(--rdp-soft); color:var(--rdp-text); text-decoration:none; }
    .rdp-icono[hidden] { display:none; }
    .rdp-cuerpo { flex:1; overflow-y:auto; padding:.75rem; display:flex; flex-direction:column; gap:.55rem; }
    .rdp-item { display:flex; align-items:flex-start; gap:.7rem; padding:.7rem .75rem; border:1px solid var(--rdp-border); border-radius:12px; background:var(--rdp-soft); }
    .rdp-item-ico { width:34px; height:34px; flex-shrink:0; border-radius:9px; display:flex; align-items:center; justify-content:center; background:var(--rdp-card); border:1px solid var(--rdp-border); }
    .rdp-item-txt { flex:1; min-width:0; font-size:.84rem; line-height:1.3; }
    .rdp-item-txt strong { display:block; }
    .rdp-item-num { display:inline-block; max-width:100%; margin:.2rem 0 .15rem; padding:.05rem .45rem; border-radius:6px; background:rgba(78,115,223,.1); color:var(--rdp-acento); font-size:.72rem; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:top; }
    .rdp-item-txt small { display:block; color:var(--rdp-muted); font-size:.72rem; }
    .rdp-item-accion { flex-shrink:0; align-self:center; }
    .rdp-item-accion .btn { white-space:nowrap; }
    .rdp-sin { font-size:.72rem; color:var(--rdp-muted); font-style:italic; }
    .rdp-vacio { margin:auto; text-align:center; color:var(--rdp-muted); font-size:.85rem; padding:2rem 1rem; }
    .rdp-vacio i { display:block; font-size:1.8rem; margin-bottom:.5rem; opacity:.5; }
    .rdp-visor { flex:1; position:relative; display:flex; background:#525659; }
    .rdp-visor[hidden] { display:none; }
    .rdp-visor iframe { flex:1; width:100%; border:0; }
    .rdp-carga { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#e5e7eb; font-size:.85rem; }
    .rdp-carga[hidden] { display:none; }
    .rdp-pie { display:flex; flex-wrap:wrap; gap:.3rem 1rem; padding:.55rem 1rem; border-top:1px solid var(--rdp-border); font-size:.7rem; color:var(--rdp-muted); }
    .rdp-pie[hidden] { display:none; }
    @media (max-width: 575.98px) { .rdp-panel, .rdp-panel.is-visor { width:100vw; } }
    @media (prefers-reduced-motion: reduce) { .rdp-panel, .rdp-fondo, .rdp-pestana { transition:none; } }

    /* Calendario de fechas (flatpickr): campo con icono, botón «Hoy» y temas oscuro / tenue. */
    .rdg .rdg-fecha-visible { cursor:pointer; padding-right:2rem; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512' fill='%2364748b'%3E%3Cpath d='M152 64h144V24a24 24 0 0 1 48 0v40h40a64 64 0 0 1 64 64v320a64 64 0 0 1-64 64H64a64 64 0 0 1-64-64V128a64 64 0 0 1 64-64h40V24a24 24 0 0 1 48 0zM48 192v256a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V192z'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .6rem center; background-size:.85rem; }
    .flatpickr-calendar { font-family:inherit; }
    .flatpickr-day.today { border-color:#4e73df; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background:#4e73df; border-color:#4e73df; }
    .rdg-fp-hoy { display:block; width:calc(100% - 1rem); margin:.1rem .5rem .5rem; padding:.3rem; border:1px solid #c7d2fe; border-radius:6px; background:#eef2ff; color:#4338ca; font-size:.8rem; font-weight:700; cursor:pointer; }
    .rdg-fp-hoy:hover { background:#e0e7ff; }
    .dark-mode .flatpickr-calendar { background:#1c2128; box-shadow:0 8px 24px rgba(0,0,0,.45); }
    .dim-mode .flatpickr-calendar { background:var(--lm-superficie-2); box-shadow:0 8px 24px rgba(0,0,0,.45); }
    .dark-mode .flatpickr-calendar.arrowTop:after, .dark-mode .flatpickr-calendar.arrowTop:before { border-bottom-color:#1c2128; }
    .dim-mode .flatpickr-calendar.arrowTop:after, .dim-mode .flatpickr-calendar.arrowTop:before { border-bottom-color:var(--lm-superficie-2); }
    .dark-mode .flatpickr-months .flatpickr-month, .dark-mode .flatpickr-current-month .flatpickr-monthDropdown-months, .dark-mode span.flatpickr-weekday,
    .dark-mode .flatpickr-months .flatpickr-prev-month, .dark-mode .flatpickr-months .flatpickr-next-month, .dark-mode .flatpickr-current-month input.cur-year { color:#e6edf3; fill:#e6edf3; background:transparent; }
    .dim-mode .flatpickr-months .flatpickr-month, .dim-mode .flatpickr-current-month .flatpickr-monthDropdown-months, .dim-mode span.flatpickr-weekday,
    .dim-mode .flatpickr-months .flatpickr-prev-month, .dim-mode .flatpickr-months .flatpickr-next-month, .dim-mode .flatpickr-current-month input.cur-year { color:var(--lm-texto); fill:var(--lm-texto); background:transparent; }
    .dark-mode .flatpickr-current-month .flatpickr-monthDropdown-months option { background:#1c2128; }
    .dim-mode .flatpickr-current-month .flatpickr-monthDropdown-months option { background:var(--lm-superficie-2); }
    .dark-mode .flatpickr-months .flatpickr-prev-month svg, .dark-mode .flatpickr-months .flatpickr-next-month svg { fill:#e6edf3; }
    .dim-mode .flatpickr-months .flatpickr-prev-month svg, .dim-mode .flatpickr-months .flatpickr-next-month svg { fill:var(--lm-texto); }
    .dark-mode .flatpickr-day { color:#e6edf3; }
    .dim-mode .flatpickr-day { color:var(--lm-texto); }
    .dark-mode .flatpickr-day.prevMonthDay, .dark-mode .flatpickr-day.nextMonthDay, .dark-mode .flatpickr-day.flatpickr-disabled { color:#6e7681; }
    .dim-mode .flatpickr-day.prevMonthDay, .dim-mode .flatpickr-day.nextMonthDay, .dim-mode .flatpickr-day.flatpickr-disabled { color:var(--lm-suave-2); }
    .dark-mode .flatpickr-day:hover, .dark-mode .flatpickr-day:focus { background:#2d333b; border-color:#2d333b; }
    .dim-mode .flatpickr-day:hover, .dim-mode .flatpickr-day:focus { background:var(--lm-superficie-3); border-color:var(--lm-superficie-3); }
    .dark-mode .rdg-fp-hoy { background:rgba(99,102,241,.15); border-color:rgba(165,180,252,.35); color:#c7d2fe; }
    .dim-mode .rdg-fp-hoy { background:var(--lm-lavanda-fondo); border-color:var(--lm-borde-fuerte); color:var(--lm-lavanda-claro); }
</style>
@endonce
