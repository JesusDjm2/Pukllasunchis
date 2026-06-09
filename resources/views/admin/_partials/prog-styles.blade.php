{{--
    Módulo Programas / Ciclos — Estilos compartidos
    Requiere wrapper: <div class="pg-wrap"> en cada vista que lo incluya.
--}}
<style>
/* ── Variables (light / dark / dim) ──────────────────────────── */
.pg-wrap {
    --pg-card:     #ffffff;
    --pg-border:   #e2e8f0;
    --pg-text:     #1e293b;
    --pg-text-2:   #475569;
    --pg-muted:    #64748b;
    --pg-input-bg: #f8fafc;
    --pg-shadow:   0 2px 12px rgba(0,0,0,.07);
    --pg-sh-blue:  0 10px 28px rgba(37,99,235,.18);
    --pg-sh-amber: 0 10px 28px rgba(245,158,11,.18);
}
.dark-mode .pg-wrap {
    --pg-card:     #1e293b;
    --pg-border:   #334155;
    --pg-text:     #e2e8f0;
    --pg-text-2:   #cbd5e1;
    --pg-muted:    #94a3b8;
    --pg-input-bg: #0f172a;
    --pg-shadow:   0 2px 12px rgba(0,0,0,.4);
    --pg-sh-blue:  0 10px 28px rgba(37,99,235,.35);
    --pg-sh-amber: 0 10px 28px rgba(245,158,11,.3);
}
.dim-mode .pg-wrap {
    --pg-card:     #243044;
    --pg-border:   #2d4060;
    --pg-text:     #d1d5db;
    --pg-text-2:   #9ca3af;
    --pg-muted:    #8899a6;
    --pg-input-bg: #1a2535;
    --pg-shadow:   0 2px 12px rgba(0,0,0,.3);
    --pg-sh-blue:  0 10px 28px rgba(37,99,235,.28);
    --pg-sh-amber: 0 10px 28px rgba(245,158,11,.22);
}

/* ── Hero ────────────────────────────────────────────────────── */
.pg-hero {
    background: linear-gradient(130deg, #1e3a5f 0%, #2563eb 55%, #0ea5e9 100%);
    border-radius: 1.1rem;
    padding: 1.75rem 2.25rem;
    margin-bottom: 1.75rem;
    position: relative; overflow: hidden;
}
.dark-mode .pg-hero { background: linear-gradient(130deg, #0d1b36 0%, #1d4ed8 55%, #0284c7 100%); }
.dim-mode  .pg-hero { background: linear-gradient(130deg, #12263f 0%, #1e40af 55%, #0369a1 100%); }
.pg-hero::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:220px; height:220px; border-radius:50%;
    background:rgba(255,255,255,.06); pointer-events:none;
}
.pg-hero::after {
    content:''; position:absolute; bottom:-70px; right:80px;
    width:160px; height:160px; border-radius:50%;
    background:rgba(255,255,255,.035); pointer-events:none;
}
.pg-hero-label {
    display:inline-flex; align-items:center; gap:.4rem;
    background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.22);
    border-radius:2rem; padding:.28rem .9rem;
    font-size:.7rem; font-weight:700; color:#fff;
    text-transform:uppercase; letter-spacing:.08em; margin-bottom:.6rem;
}
.pg-hero h2,
.pg-hero h4 { color:#fff; font-weight:800; margin-bottom:.25rem; line-height:1.25; }
.pg-hero h2 { font-size:1.55rem; }
.pg-hero h4 { font-size:1.35rem; }
.pg-hero-sub { color:rgba(255,255,255,.72); margin:0; font-size:.85rem; }
.pg-hero-btn {
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3);
    color:#fff !important; border-radius:.5rem;
    padding:.38rem .9rem; font-size:.78rem; font-weight:600;
    text-decoration:none !important; transition:background .2s;
    display:inline-flex; align-items:center; gap:.3rem;
}
.pg-hero-btn:hover { background:rgba(255,255,255,.24); }

/* ── Section label ───────────────────────────────────────────── */
.pg-section-lbl {
    display:flex; align-items:center; gap:.55rem;
    font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.08em;
    color:var(--pg-muted);
    margin-bottom:1.1rem; padding-bottom:.5rem;
    border-bottom:1px solid var(--pg-border);
    opacity:0;
}

/* ── Stat cards ──────────────────────────────────────────────── */
.pg-stat {
    background:var(--pg-card); border:1px solid var(--pg-border);
    border-radius:.85rem; padding:.9rem 1.1rem; box-shadow:var(--pg-shadow);
    display:flex; align-items:center; gap:.8rem;
    transition:transform .22s, box-shadow .22s; opacity:0;
}
.pg-stat:hover { transform:translateY(-3px); box-shadow:var(--pg-sh-blue); }
.pg-stat-icon {
    width:44px; height:44px; border-radius:.65rem; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.pg-stat-val { font-size:1.35rem; font-weight:800; line-height:1; color:var(--pg-text); }
.pg-stat-lbl { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--pg-muted); margin-top:.15rem; }

/* ── Card base ───────────────────────────────────────────────── */
.pg-card {
    background:var(--pg-card); border:1px solid var(--pg-border);
    border-radius:1rem; overflow:hidden; box-shadow:var(--pg-shadow);
    transition:transform .25s cubic-bezier(.34,1.36,.64,1), box-shadow .25s, border-color .25s;
    opacity:0;
}
.pg-card-fid:hover { transform:translateY(-5px); box-shadow:var(--pg-sh-blue);  border-color:#2563eb; }
.pg-card-ppd:hover { transform:translateY(-5px); box-shadow:var(--pg-sh-amber); border-color:#f59e0b; }
.pg-card-std:hover { transform:translateY(-5px); box-shadow:var(--pg-sh-blue);  border-color:#3b82f6; }

.pg-card-top  { height:5px; }
.pg-top-blue  { background:linear-gradient(90deg,#2563eb,#0ea5e9); }
.pg-top-amber { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.pg-top-cyan  { background:linear-gradient(90deg,#06b6d4,#0ea5e9); }
.pg-top-green { background:linear-gradient(90deg,#16a34a,#4ade80); }

.pg-card-body { padding:1.15rem 1.1rem 1.05rem; }
.pg-card-body-center { padding:1.15rem .9rem 1.05rem; text-align:center; }

.pg-card-name {
    font-size:.93rem; font-weight:700;
    color:var(--pg-text); line-height:1.3; margin-bottom:.8rem;
}

/* ── Icono de card ───────────────────────────────────────────── */
.pg-icon {
    width:40px; height:40px; border-radius:.6rem; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.95rem; margin-bottom:.75rem;
}
.pg-icon-blue  { background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8; }
.pg-icon-amber { background:linear-gradient(135deg,#fef9c3,#fde68a); color:#b45309; }
.pg-icon-green { background:linear-gradient(135deg,#dcfce7,#bbf7d0); color:#15803d; }
.pg-icon-cyan  { background:linear-gradient(135deg,#cffafe,#a5f3fc); color:#0e7490; }
.dark-mode .pg-icon-blue  { background:rgba(37,99,235,.25);  color:#93c5fd; }
.dark-mode .pg-icon-amber { background:rgba(245,158,11,.2);  color:#fcd34d; }
.dark-mode .pg-icon-green { background:rgba(22,163,74,.2);   color:#86efac; }
.dark-mode .pg-icon-cyan  { background:rgba(6,182,212,.2);   color:#67e8f9; }
.dim-mode  .pg-icon-blue  { background:rgba(37,99,235,.2);   color:#93c5fd; }
.dim-mode  .pg-icon-amber { background:rgba(245,158,11,.15); color:#fcd34d; }
.dim-mode  .pg-icon-green { background:rgba(22,163,74,.15);  color:#86efac; }
.dim-mode  .pg-icon-cyan  { background:rgba(6,182,212,.15);  color:#67e8f9; }
.pg-card-fid:hover .pg-icon-blue  { background:linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff; }
.pg-card-ppd:hover .pg-icon-amber { background:linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff; }

/* ── Número de ciclo (burbuja) ───────────────────────────────── */
.pg-ciclo-num {
    width:40px; height:40px; border-radius:50%;
    background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8;
    font-size:.82rem; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto .7rem;
    transition:background .25s, color .25s;
}
.dark-mode .pg-ciclo-num { background:rgba(37,99,235,.28); color:#93c5fd; }
.dim-mode  .pg-ciclo-num { background:rgba(37,99,235,.22); color:#93c5fd; }
.pg-card:hover .pg-ciclo-num { background:linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff; }
.pg-ciclo-cta {
    font-size:.68rem; font-weight:600; color:#3b82f6;
    text-transform:uppercase; letter-spacing:.06em;
    opacity:0; transform:translateY(4px);
    transition:opacity .2s, transform .2s;
    display:flex; align-items:center; justify-content:center; gap:.3rem;
}
.pg-card:hover .pg-ciclo-cta { opacity:1; transform:translateY(0); }

/* ── Botones de acción ───────────────────────────────────────── */
.pg-btn {
    display:inline-flex; align-items:center; gap:.28rem;
    padding:.3rem .72rem; border-radius:.5rem;
    font-size:.71rem; font-weight:600;
    text-decoration:none !important;
    transition:opacity .15s, transform .15s;
    border:none; cursor:pointer; line-height:1.4;
}
.pg-btn:hover { opacity:.85; transform:translateY(-1px); }
.pg-btn-view  { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.pg-btn-edit  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.pg-btn-del   { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }
.pg-btn-cycle { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; }
.dark-mode .pg-btn-view  { background:rgba(37,99,235,.18);  color:#93c5fd; border-color:rgba(37,99,235,.3); }
.dark-mode .pg-btn-edit  { background:rgba(22,163,74,.15);  color:#86efac; border-color:rgba(22,163,74,.3); }
.dark-mode .pg-btn-del   { background:rgba(190,18,60,.15);  color:#fda4af; border-color:rgba(190,18,60,.3); }
.dark-mode .pg-btn-cycle { background:rgba(109,40,217,.18); color:#c4b5fd; border-color:rgba(109,40,217,.3); }
.dim-mode  .pg-btn-view  { background:rgba(37,99,235,.14);  color:#93c5fd; border-color:rgba(37,99,235,.25); }
.dim-mode  .pg-btn-edit  { background:rgba(22,163,74,.12);  color:#86efac; border-color:rgba(22,163,74,.25); }
.dim-mode  .pg-btn-del   { background:rgba(190,18,60,.12);  color:#fda4af; border-color:rgba(190,18,60,.25); }
.dim-mode  .pg-btn-cycle { background:rgba(109,40,217,.14); color:#c4b5fd; border-color:rgba(109,40,217,.25); }

/* ── Formulario card ─────────────────────────────────────────── */
.pg-form-card {
    background:var(--pg-card); border:1px solid var(--pg-border);
    border-radius:1rem; box-shadow:var(--pg-shadow); overflow:hidden;
    max-width:560px;
}
.pg-form-head {
    padding:.7rem 1.25rem; border-bottom:1px solid var(--pg-border);
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em;
    color:var(--pg-muted); display:flex; align-items:center; gap:.5rem;
}
.pg-form-body { padding:1.5rem 1.25rem; }
.pg-label { font-size:.78rem; font-weight:600; color:var(--pg-text); margin-bottom:.3rem; display:block; }
.pg-input, .pg-select {
    width:100% !important;
    background:var(--pg-input-bg) !important;
    border:1px solid var(--pg-border) !important;
    border-radius:.5rem !important;
    color:var(--pg-text) !important;
    font-size:.88rem;
    transition:border-color .2s, box-shadow .2s;
}
.pg-input  { padding:.55rem .85rem; }
.pg-select { padding:.45rem .85rem; }
.pg-input:focus, .pg-select:focus {
    border-color:#3b82f6 !important;
    box-shadow:0 0 0 .18rem rgba(59,130,246,.2) !important;
    outline:none;
}
.pg-submit {
    background:linear-gradient(90deg,#2563eb,#0ea5e9);
    border:none; color:#fff; font-weight:700; border-radius:.5rem;
    padding:.55rem 1.5rem; font-size:.88rem; cursor:pointer;
    transition:opacity .2s, transform .1s; display:inline-flex; align-items:center; gap:.35rem;
}
.pg-submit:hover { opacity:.9; }
.pg-submit:active { transform:scale(.98); }

/* ── Tabla del módulo ────────────────────────────────────────── */
.pg-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.84rem; }
.pg-table thead th {
    background:var(--pg-card) !important;
    color:var(--pg-muted);
    font-size:.69rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    border-bottom:2px solid var(--pg-border) !important;
    padding:.6rem .75rem; white-space:nowrap;
}
.pg-table tbody td {
    color:var(--pg-text);
    border-bottom:1px solid var(--pg-border) !important;
    border-top:none !important;
    padding:.55rem .75rem; vertical-align:middle; background:transparent;
}
.pg-table tbody tr:hover td { background:rgba(37,99,235,.035) !important; }
.dark-mode .pg-table tbody tr:hover td { background:rgba(255,255,255,.03) !important; }
.pg-table-warn td { background:rgba(239,68,68,.06) !important; }
.dark-mode .pg-table-warn td { background:rgba(239,68,68,.12) !important; }
.dim-mode  .pg-table-warn td { background:rgba(239,68,68,.1) !important; }

/* ── Empty state ─────────────────────────────────────────────── */
.pg-empty {
    text-align:center; padding:3.5rem 1rem; color:var(--pg-muted);
    background:var(--pg-card); border:1px dashed var(--pg-border); border-radius:1rem;
}
</style>
