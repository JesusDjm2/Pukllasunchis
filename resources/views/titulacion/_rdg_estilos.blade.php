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
