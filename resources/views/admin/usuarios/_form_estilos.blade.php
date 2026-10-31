{{-- Estilos compartidos del formulario de usuario (crear y editar: admin/create, admin/edit) --}}
<style>    
.reg-header { border-left:4px solid #4e73df; padding-left:1rem; }
.reg-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:.75rem;
    margin-bottom:1.25rem; overflow:hidden;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.reg-card-head {
    display:flex; align-items:center; gap:.6rem;
    padding:.6rem 1.25rem; background:#f8fafc;
    border-bottom:1px solid #e2e8f0;
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; color:#64748b;
}
.reg-card-body { padding:1.25rem; }

.reg-label {
    font-size:.78rem; font-weight:600; color:#374151;
    margin-bottom:.3rem; display:block;
}
.reg-input-wrap .input-group-text {
    background:#f8fafc; border-color:#d1d5db; color:#9ca3af;
    border-radius:.45rem 0 0 .45rem; font-size:.8rem;
}
.reg-input-wrap .form-control {
    border-radius:0 .45rem .45rem 0 !important;
    border-color:#d1d5db; font-size:.85rem;
    transition:border-color .2s, box-shadow .2s;
}
.reg-input-wrap .form-control:focus {
    border-color:#4e73df;
    box-shadow:0 0 0 .18rem rgba(78,115,223,.18);
}
.reg-input-solo {
    border-radius:.45rem !important; border-color:#d1d5db !important;
    font-size:.85rem !important; transition:border-color .2s, box-shadow .2s !important;
}
.reg-input-solo:focus {
    border-color:#4e73df !important;
    box-shadow:0 0 0 .18rem rgba(78,115,223,.18) !important;
}

.role-pill-wrap { display:flex; flex-wrap:wrap; gap:.5rem; }
.role-pill { position:relative; display:inline-flex; }
.role-pill input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
.role-pill label {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.35rem .9rem; border-radius:2rem; cursor:pointer;
    font-size:.78rem; font-weight:600;
    border:2px solid #e2e8f0; background:#f8fafc; color:#64748b;
    transition:all .18s; user-select:none;
}
.role-pill label:hover { border-color:#94a3b8; background:#f1f5f9; }
.role-pill input:checked + label {
    border-color:var(--rc); background:var(--rb); color:var(--rt);
    box-shadow:0 0 0 3px rgba(var(--rr),.18);
}
.rp-admin   { --rc:#f59e0b; --rb:#fef3c7; --rt:#92400e; --rr:245,158,11; }
.rp-docente { --rc:#06b6d4; --rb:#cffafe; --rt:#0e7490; --rr:6,182,212; }
.rp-tutor   { --rc:#10b981; --rb:#d1fae5; --rt:#065f46; --rr:16,185,129; }
.rp-investigador { --rc:#d97706; --rb:#fef3c7; --rt:#92400e; --rr:217,119,6; }
.rp-titulacion { --rc:#0d9488; --rb:#ccfbf1; --rt:#115e59; --rr:13,148,136; }
.rp-alumno  { --rc:#3b82f6; --rb:#dbeafe; --rt:#1d4ed8; --rr:59,130,246; }
.rp-alumnoB { --rc:#6366f1; --rb:#ede9fe; --rt:#4338ca; --rr:99,102,241; }
.rp-adminB  { --rc:#64748b; --rb:#f1f5f9; --rt:#334155; --rr:100,116,139; }
.rp-seguimiento { --rc:#832128; --rb:#fce8e9; --rt:#5c151b; --rr:131,33,40; }
.rp-inh     { --rc:#ef4444; --rb:#fee2e2; --rt:#b91c1c; --rr:239,68,68; }
.rp-sa      { --rc:#7c3aed; --rb:#ede9fe; --rt:#5b21b6; --rr:124,58,237; }

/* Sección académica / bolsa */
.reg-reveal {
    border:1px dashed #c7d2fe; border-radius:.65rem;
    background:#f5f3ff; padding:1.1rem 1.25rem; margin-top:.25rem;
}
.reg-reveal-blue {
    border-color:#a5b4fc; background:#eef2ff;
}
.reg-reveal .sec-title {
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; color:#6d28d9;
    display:flex; align-items:center; gap:.5rem;
    margin-bottom:.9rem; padding-bottom:.5rem;
    border-bottom:1px solid #ddd6fe;
}
.reg-reveal-blue .sec-title { color:#4338ca; border-color:#c7d2fe; }

/* Sidebar CTA */
.reg-submit {
    background:#4e73df; border:none; border-radius:.5rem;
    color:#fff; font-weight:700; font-size:.9rem;
    padding:.65rem 1.5rem; width:100%; cursor:pointer;
    transition:background .2s, transform .1s;
}
.reg-submit:hover { background:#3a5fd4; color:#fff; }
.reg-submit:active { transform:scale(.98); }

/* Solo en editar: foto y resumen del usuario en la barra lateral */
.reg-foto {
    width:96px; height:96px; border-radius:1rem; object-fit:cover;
    display:flex; align-items:center; justify-content:center;
    font-size:1.6rem; font-weight:800; color:#4338ca; background:#e0e7ff;
    margin:0 auto .75rem; border:3px solid #fff; box-shadow:0 2px 10px rgba(0,0,0,.08);
}
.reg-foto-btn {
    display:inline-flex; align-items:center; gap:.35rem; cursor:pointer;
    font-size:.76rem; font-weight:600; color:#4e73df;
    border:1px dashed #a5b4fc; border-radius:.45rem; padding:.3rem .7rem; background:#eef2ff;
}
.reg-foto-btn:hover { background:#e0e7ff; }
.reg-resumen-nombre { font-weight:800; color:#1e293b; text-align:center; line-height:1.25; }
.reg-resumen-sub { font-size:.76rem; color:#64748b; text-align:center; margin-top:.15rem; }
.reg-resumen-dato { display:flex; justify-content:space-between; gap:.5rem; font-size:.78rem; padding:.35rem 0; border-top:1px solid #f1f5f9; }
.reg-resumen-dato span:first-child { color:#64748b; }
.reg-resumen-dato span:last-child { color:#1e293b; font-weight:600; text-align:right; }
.reg-rol-mini {
    display:inline-block; font-size:.66rem; font-weight:700; padding:.1rem .5rem;
    border-radius:1rem; background:#f1f5f9; color:#475569; margin:.1rem;
}
.reg-hint { font-size:.72rem; color:#94a3b8; margin-top:.25rem; display:block; }
</style>
