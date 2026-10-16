@php
    // $vistaDm (opcional): la usa Titulación para mostrar solo su población (TitulacionController@demograficos).
    $vistaDm = $vistaDm ?? ['modo' => 'general'];
    $layout = $vistaDm['layout'] ?? (auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin');
@endphp
@extends($layout)
@section('titulo', $vistaDm['titulo'] ?? 'Datos demográficos')
@section('contenido')
{{--
    Datos demográficos de FID y PPD. App\Services\DatosDemograficos entrega las fichas ya
    normalizadas (una fila compacta por estudiante); aquí se filtra, cruza y grafica en el
    navegador, sin recargar. El estado de los filtros vive en la URL para poder compartirla.
--}}
<style>
    .dm { --dm-card:#fff; --dm-bg:#f6f7fb; --dm-text:#1e293b; --dm-muted:#64748b; --dm-border:#e5e7eb; --dm-soft:#f1f5f9; --dm-accent:#4f46e5; --dm-shadow:0 1px 2px rgba(15,23,42,.05),0 4px 14px rgba(15,23,42,.04); color:var(--dm-text); }
    .dark-mode .dm { --dm-card:#161b22; --dm-bg:#0d1117; --dm-text:#e6edf3; --dm-muted:#8b949e; --dm-border:rgba(255,255,255,.09); --dm-soft:rgba(255,255,255,.05); --dm-shadow:none; }
    .dim-mode .dm { --dm-card:var(--lm-superficie); --dm-bg:var(--lm-bg); --dm-text:var(--lm-texto); --dm-muted:var(--lm-suave); --dm-border:rgba(255,255,255,.09); --dm-soft:rgba(255,255,255,.05); --dm-shadow:none; }

    .dm-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:.75rem 1rem; margin-bottom:1rem; }
    .dm-title { font-size:1.35rem; font-weight:800; margin:0; color:var(--dm-text); }
    .dm-title i { color:var(--dm-accent); }
    .dm-sub { font-size:.84rem; color:var(--dm-muted); margin:.2rem 0 0; }
    .dm-actions { display:flex; gap:.5rem; }
    .dm-btn { display:inline-flex; align-items:center; gap:.4rem; height:34px; padding:0 .85rem; border-radius:.55rem; border:1px solid var(--dm-border); background:var(--dm-card); color:var(--dm-text); font-size:.8rem; font-weight:700; cursor:pointer; transition:background .15s; }
    .dm-btn:hover { background:var(--dm-soft); }
    .dm-btn.is-primary { background:var(--dm-accent); border-color:var(--dm-accent); color:#fff; }
    .dm-btn.is-primary:hover { filter:brightness(1.08); }

    /* Filtros */
    .dm-filtros { position:sticky; top:0; z-index:20; background:var(--dm-card); border:1px solid var(--dm-border); border-radius:14px; box-shadow:var(--dm-shadow); padding:.75rem .9rem; margin-bottom:1rem; }
    .dm-frow { display:flex; flex-wrap:wrap; align-items:flex-end; gap:.6rem .8rem; }
    .dm-field { display:flex; flex-direction:column; gap:.2rem; min-width:0; }
    .dm-field label { font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:var(--dm-muted); margin:0; }
    .dm-field select { height:34px; border:1px solid var(--dm-border); border-radius:.5rem; background:var(--dm-card); color:var(--dm-text); font-size:.82rem; padding:0 .5rem; max-width:260px; }
    .dm-seg { display:inline-flex; border:1px solid var(--dm-border); border-radius:.55rem; overflow:hidden; height:34px; }
    .dm-seg button { border:0; background:transparent; color:var(--dm-muted); font-size:.8rem; font-weight:700; padding:0 .85rem; cursor:pointer; }
    .dm-seg button + button { border-left:1px solid var(--dm-border); }
    .dm-seg button.is-on { background:var(--dm-accent); color:#fff; }
    .dm-chips { display:flex; flex-wrap:wrap; gap:.35rem; align-items:center; margin-top:.6rem; }
    .dm-chips:empty { display:none; }
    .dm-chip { display:inline-flex; align-items:center; gap:.35rem; font-size:.74rem; font-weight:700; padding:.2rem .3rem .2rem .6rem; border-radius:999px; background:color-mix(in srgb, var(--dm-accent) 12%, transparent); color:var(--dm-accent); }
    .dark-mode .dm-chip, .dim-mode .dm-chip { color:#c7d2fe; }
    .dm-chip button { border:0; background:transparent; color:inherit; width:18px; height:18px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:.7rem; }
    .dm-chip button:hover { background:rgba(0,0,0,.08); }
    .dm-limpiar { border:0; background:none; color:var(--dm-muted); font-size:.74rem; font-weight:700; text-decoration:underline; cursor:pointer; }

    /* Indicadores */
    .dm-aviso { background:var(--dm-card); border:1px solid var(--dm-border); border-radius:12px; padding:.6rem .9rem; font-size:.84rem; color:var(--dm-muted); }
    .dm-aviso summary { cursor:pointer; }
    .dm-aviso__ver { color:var(--dm-accent); font-weight:700; margin-left:.25rem; }
    .dm-aviso__lista { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:.2rem .9rem; margin-top:.5rem; color:var(--dm-text); }
    .dm-kpis { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:.75rem; margin-bottom:1rem; }
    .dm-kpi { text-align:left; background:var(--dm-card); border:1px solid var(--dm-border); border-radius:14px; box-shadow:var(--dm-shadow); padding:.85rem 1rem; display:flex; gap:.75rem; align-items:flex-start; color:var(--dm-text); }
    button.dm-kpi { cursor:pointer; transition:border-color .15s, transform .15s; }
    button.dm-kpi:hover { border-color:var(--k); transform:translateY(-1px); }
    .dm-kpi-ico { width:36px; height:36px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.9rem; color:var(--k); background:color-mix(in srgb, var(--k) 13%, transparent); }
    .dm-kpi-val, .dm-kpi-lbl, .dm-kpi-sub { display:block; }
    .dm-kpi-val { font-size:1.4rem; font-weight:800; line-height:1.1; }
    .dm-kpi-lbl { font-size:.76rem; font-weight:700; color:var(--dm-text); margin-top:.1rem; }
    .dm-kpi-sub { font-size:.7rem; color:var(--dm-muted); margin-top:.1rem; }

    /* Navegación por secciones */
    .dm-nav { display:flex; gap:.35rem; overflow-x:auto; padding:.1rem 0 .6rem; margin-bottom:.4rem; scrollbar-width:thin; }
    .dm-nav a { flex-shrink:0; display:inline-flex; align-items:center; gap:.4rem; padding:.38rem .75rem; border-radius:999px; border:1px solid var(--dm-border); background:var(--dm-card); color:var(--dm-muted); font-size:.76rem; font-weight:700; text-decoration:none; }
    .dm-nav a:hover { color:var(--dm-text); text-decoration:none; }
    .dm-nav a.is-on { background:var(--s); border-color:var(--s); color:#fff; }

    /* Secciones y tarjetas */
    .dm-sec { scroll-margin-top:140px; margin-bottom:1.6rem; }
    .dm-sec-h { display:flex; align-items:center; gap:.55rem; margin:0 0 .7rem; font-size:1rem; font-weight:800; color:var(--dm-text); }
    .dm-sec-h .ico { width:30px; height:30px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; font-size:.8rem; color:var(--s); background:color-mix(in srgb, var(--s) 13%, transparent); }
    .dm-sec-h small { font-size:.74rem; font-weight:600; color:var(--dm-muted); }
    .dm-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(330px, 1fr)); gap:.85rem; }
    .dm-card { background:var(--dm-card); border:1px solid var(--dm-border); border-radius:14px; box-shadow:var(--dm-shadow); padding:.9rem 1rem .8rem; display:flex; flex-direction:column; min-width:0; }
    .dm-card.is-wide { grid-column:span 2; }
    @media (max-width: 760px) { .dm-card.is-wide { grid-column:auto; } }
    .dm-card-h { display:flex; align-items:flex-start; gap:.5rem; }
    .dm-card-t { font-size:.88rem; font-weight:800; color:var(--dm-text); line-height:1.3; }
    .dm-card-q { font-size:.72rem; color:var(--dm-muted); margin-top:.1rem; line-height:1.35; }
    .dm-card-tools { margin-left:auto; display:flex; gap:.2rem; flex-shrink:0; }
    .dm-ico-btn { width:28px; height:28px; border-radius:.45rem; border:1px solid transparent; background:transparent; color:var(--dm-muted); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:.75rem; }
    .dm-ico-btn:hover, .dm-ico-btn.is-on { background:var(--dm-soft); color:var(--dm-text); border-color:var(--dm-border); }
    .dm-cob { display:flex; align-items:center; gap:.5rem; margin:.55rem 0 .35rem; font-size:.7rem; color:var(--dm-muted); }
    .dm-cob-bar { flex:1; height:4px; border-radius:4px; background:var(--dm-soft); overflow:hidden; max-width:120px; }
    .dm-cob-bar span { display:block; height:100%; background:var(--s); opacity:.7; }
    .dm-cob.is-low { color:#b45309; }
    .dark-mode .dm-cob.is-low, .dim-mode .dm-cob.is-low { color:#fbbf24; }
    .dm-insight { font-size:.76rem; color:var(--dm-text); margin-bottom:.4rem; }
    .dm-insight strong { color:var(--s); }
    .dm-chart { position:relative; flex:1; }
    .dm-chart canvas { cursor:pointer; }
    .dm-vacio { text-align:center; color:var(--dm-muted); font-size:.8rem; padding:1.6rem .5rem; }
    .dm-tabla { width:100%; font-size:.78rem; border-collapse:collapse; }
    .dm-tabla th { font-size:.66rem; text-transform:uppercase; letter-spacing:.04em; color:var(--dm-muted); font-weight:800; padding:.35rem .4rem; border-bottom:1px solid var(--dm-border); text-align:left; }
    .dm-tabla td { padding:.35rem .4rem; border-bottom:1px solid var(--dm-border); color:var(--dm-text); }
    .dm-tabla tr:last-child td { border-bottom:0; }
    .dm-tabla .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .dm-tabla tbody tr { cursor:pointer; }
    .dm-tabla tbody tr:hover td { background:var(--dm-soft); }

    /* Cruce */
    .dm-cruce-ctrl { display:flex; flex-wrap:wrap; gap:.6rem .8rem; align-items:flex-end; margin-bottom:.8rem; }
    .dm-cruce-wrap { overflow-x:auto; }
    .dm-cruce { width:auto; }
    .dm-cruce th:first-child, .dm-cruce td:first-child { min-width:170px; }
    .dm-cruce td.cel { text-align:center; min-width:74px; cursor:pointer; }
    .dm-cruce td.cel small { display:block; font-size:.66rem; color:var(--dm-muted); }
    .dm-cruce th.col { text-align:center; }
    .dm-cruce td.tot, .dm-cruce th.tot { font-weight:800; background:var(--dm-soft); text-align:center; }

    /* Calidad de datos */
    .dm-cal { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:.3rem 1.2rem; }
    .dm-cal-row { display:flex; align-items:center; gap:.5rem; font-size:.76rem; padding:.2rem 0; }
    .dm-cal-row span:first-child { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .dm-cal-bar { width:90px; height:6px; border-radius:6px; background:var(--dm-soft); overflow:hidden; flex-shrink:0; }
    .dm-cal-bar i { display:block; height:100%; }
    .dm-cal-pct { width:38px; text-align:right; font-weight:700; font-variant-numeric:tabular-nums; }

    /* Panel lateral de estudiantes */
    .dm-fondo { position:fixed; inset:0; background:rgba(15,23,42,.35); z-index:1040; opacity:0; pointer-events:none; transition:opacity .2s; }
    .dm-fondo.is-open { opacity:1; pointer-events:auto; }
    .dm-drawer { position:fixed; top:0; right:0; bottom:0; width:min(440px, 100vw); z-index:1041; background:var(--dm-card); color:var(--dm-text); border-left:1px solid var(--dm-border); box-shadow:-12px 0 40px rgba(15,23,42,.18); transform:translateX(100%); transition:transform .22s ease; display:flex; flex-direction:column; }
    .dm-drawer.is-open { transform:none; }
    .dm-dr-h { padding:1rem 1.1rem .8rem; border-bottom:1px solid var(--dm-border); }
    .dm-dr-h h3 { font-size:1rem; font-weight:800; margin:0; color:var(--dm-text); }
    .dm-dr-h p { font-size:.76rem; color:var(--dm-muted); margin:.15rem 0 0; }
    .dm-dr-tools { display:flex; gap:.5rem; margin-top:.7rem; }
    .dm-dr-tools input { flex:1; height:34px; border:1px solid var(--dm-border); border-radius:.5rem; background:var(--dm-card); color:var(--dm-text); font-size:.82rem; padding:0 .65rem; }
    .dm-dr-list { flex:1; overflow-y:auto; padding:.3rem 0; }
    .dm-dr-item { display:flex; align-items:center; gap:.6rem; padding:.55rem 1.1rem; border-bottom:1px solid var(--dm-border); color:var(--dm-text); text-decoration:none; }
    .dm-dr-item:hover { background:var(--dm-soft); text-decoration:none; color:var(--dm-text); }
    .dm-dr-item b { font-size:.82rem; display:block; }
    .dm-dr-item small { font-size:.7rem; color:var(--dm-muted); }
    .dm-dr-item i.fa-chevron-right { margin-left:auto; color:var(--dm-muted); font-size:.65rem; }
    .dm-tag { font-size:.6rem; font-weight:800; padding:.05rem .4rem; border-radius:999px; margin-left:.3rem; vertical-align:middle; }
    .dm-tag.fid { background:rgba(37,99,235,.1); color:#1d4ed8; }
    .dm-tag.ppd { background:rgba(217,119,6,.12); color:#b45309; }
    .dark-mode .dm-tag.fid, .dim-mode .dm-tag.fid { color:#93c5fd; }
    .dark-mode .dm-tag.ppd, .dim-mode .dm-tag.ppd { color:#fcd34d; }
    .dm-dr-close { position:absolute; top:.75rem; right:.75rem; }

    .dm-empty-all { text-align:center; padding:3rem 1rem; color:var(--dm-muted); background:var(--dm-card); border:1px dashed var(--dm-border); border-radius:14px; }

    @media print {
        .dm-filtros, .dm-nav, .dm-actions, .dm-card-tools, .dm-drawer, .dm-fondo, #accordionSidebar, .sidebar, nav.topbar, .scroll-to-top, footer { display:none !important; }
        .dm-card, .dm-kpi { break-inside:avoid; box-shadow:none; }
        .dm-sec { break-inside:auto; }
        #content-wrapper, .container-fluid { padding:0 !important; margin:0 !important; }
    }
</style>

<div class="container-fluid py-3 dm" id="dm">
    <div class="dm-head">
        <div>
            <h1 class="dm-title"><i class="fas fa-chart-pie mr-2"></i>{{ $vistaDm['titulo'] ?? 'Datos demográficos' }}</h1>
            <p class="dm-sub" id="dmResumen">Cargando…</p>
        </div>
        <div class="dm-actions">
            @isset($vistaDm['volver'])
                <a href="{{ $vistaDm['volver'] }}" class="dm-btn"><i class="fas fa-arrow-left"></i> Volver a Titulación</a>
            @endisset
            <button type="button" class="dm-btn" id="dmImprimir"><i class="fas fa-print"></i> Imprimir</button>
            <button type="button" class="dm-btn is-primary" id="dmExportar"><i class="fas fa-file-csv"></i> Exportar lo que ves</button>
        </div>
    </div>

    @if (($vistaDm['modo'] ?? '') === 'titulacion' && ($vistaDm['sinFicha'] ?? collect())->isNotEmpty())
        <details class="dm-aviso mb-3">
            <summary>
                <i class="fas fa-info-circle mr-1"></i>
                <strong>{{ $vistaDm['sinFicha']->count() }}</strong> de {{ $vistaDm['total'] }} personas del proceso no tienen ficha socioeconómica
                (FID ni PPD) y no entran en los gráficos. <span class="dm-aviso__ver">Ver quiénes</span>
            </summary>
            <div class="dm-aviso__lista">
                @foreach ($vistaDm['sinFicha'] as $u)
                    <span>{{ $u->apellidos }}, {{ $u->name }}@if ($u->condicion === 'Egresado') <small>(egresado/a)</small>@endif</span>
                @endforeach
            </div>
        </details>
    @endif

    <div class="dm-filtros" id="dmFiltros">
        <div class="dm-frow">
            <div class="dm-field">
                <label>Modalidad</label>
                <div class="dm-seg" role="group" aria-label="Modalidad" id="dmModalidad">
                    <button type="button" data-v="todos">Todos</button>
                    <button type="button" data-v="fid">FID</button>
                    <button type="button" data-v="ppd">PPD</button>
                </div>
            </div>
            <div class="dm-field">
                <label for="dmPoblacion">Población</label>
                <select id="dmPoblacion"></select>
            </div>
            <div class="dm-field">
                <label for="dmPrograma">Programa</label>
                <select id="dmPrograma" data-dim="programa"></select>
            </div>
            <div class="dm-field">
                <label for="dmCiclo">Ciclo</label>
                <select id="dmCiclo" data-dim="ciclo"></select>
            </div>
            <div class="dm-field">
                <label for="dmGenero">Género</label>
                <select id="dmGenero" data-dim="genero"></select>
            </div>
        </div>
        <div class="dm-chips" id="dmChips"></div>
    </div>

    <div class="dm-kpis" id="dmKpis"></div>
    <nav class="dm-nav" id="dmNav" aria-label="Secciones"></nav>
    <div id="dmSecciones"></div>

    <section class="dm-sec" id="sec-cruce" style="--s:#4f46e5">
        <h2 class="dm-sec-h"><span class="ico"><i class="fas fa-th"></i></span> Cruzar variables <small>· compara dos preguntas entre sí</small></h2>
        <div class="dm-card">
            <div class="dm-cruce-ctrl">
                <div class="dm-field"><label for="dmCruceA">Filas</label><select id="dmCruceA"></select></div>
                <div class="dm-field"><label for="dmCruceB">Columnas</label><select id="dmCruceB"></select></div>
                <div class="dm-field"><label for="dmCruceModo">Mostrar</label>
                    <select id="dmCruceModo">
                        <option value="fila">% por fila</option>
                        <option value="total">% del total</option>
                        <option value="n">Solo cantidades</option>
                    </select>
                </div>
            </div>
            <div class="dm-cruce-wrap"><table class="dm-tabla dm-cruce" id="dmCruce"></table></div>
            <p class="dm-card-q mt-2 mb-0">Haz clic en una celda para ver a esos estudiantes. Solo cuenta a quienes respondieron ambas preguntas.</p>
        </div>
    </section>

    <section class="dm-sec" id="sec-calidad" style="--s:#64748b">
        <h2 class="dm-sec-h"><span class="ico"><i class="fas fa-clipboard-check"></i></span> Calidad de los datos <small>· qué tan completas están las fichas</small></h2>
        <div class="dm-card">
            <div id="dmCalidadResumen" class="dm-insight"></div>
            <div class="dm-cal" id="dmCalidad"></div>
            <p class="dm-card-q mt-2 mb-0">
                Respuestas vacías, placeholders («Elegir») y valores imposibles (edad fuera de 15–80, 999 hijos…) se cuentan como «sin dato».
                Las respuestas escritas de forma distinta (códigos antiguos y frases, mayúsculas, «1»/«Sí») se unifican.
            </p>
        </div>
    </section>

    <div class="dm-fondo" id="dmFondo"></div>
    <aside class="dm-drawer" id="dmDrawer" aria-hidden="true" aria-labelledby="dmDrTitulo">
        <div class="dm-dr-h">
            <button type="button" class="dm-ico-btn dm-dr-close" id="dmDrCerrar" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            <h3 id="dmDrTitulo"></h3>
            <p id="dmDrSub"></p>
            <div class="dm-dr-tools">
                <input type="search" id="dmDrBuscar" placeholder="Buscar por nombre o DNI…" autocomplete="off">
                <button type="button" class="dm-btn is-primary" id="dmDrFiltrar"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
        </div>
        <div class="dm-dr-list" id="dmDrLista"></div>
    </aside>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // El layout vuelve a cargar Chart.js (otra versión) al final: se guarda esta referencia.
    window.DmChart = window.Chart;
    window.DM = {
        datos: @json($datos),
        expediente: @json(route('expediente.show', ['user' => '__ID__'])),
        modo: @json($vistaDm['modo'] ?? 'general'),
    };
</script>
@verbatim
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var Chart = window.DmChart;
    var D = window.DM.datos;
    var DIMS = D.dims, FILAS = D.filas;
    var IDX = {};
    DIMS.forEach(function (d, i) { d.i = i; IDX[d.key] = d; });

    var COLOR_SEC = { titulacion:'#4e73df', perfil:'#6366f1', origen:'#0ea5e9', identidad:'#8b5cf6', economia:'#d97706', vivienda:'#059669', conectividad:'#0891b2', salud:'#e11d48', formacion:'#7c3aed', cultura:'#db2777' };
    var PALETA = ['#6366f1','#0ea5e9','#f59e0b','#10b981','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899','#84cc16','#64748b'];
    var MAX_BARRAS = 10;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]; }); };
    var pct = function (a, b) { return b ? Math.round(a * 1000 / b) / 10 : 0; };
    var fmtPct = function (p) { return (Math.round(p * 10) / 10).toLocaleString('es-PE') + ' %'; };
    var norm = function (s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };
    function css(nombre) { return getComputedStyle($('dm')).getPropertyValue(nombre).trim(); }
    // Etiqueta larga → hasta 2 líneas (Chart.js acepta un arreglo), en vez de cortarse en el borde.
    function partir(texto, max) {
        if (texto.length <= max) return texto;
        var lineas = [''];
        texto.split(' ').forEach(function (p) {
            var i = lineas.length - 1;
            if (lineas[i] && (lineas[i] + ' ' + p).length > max) lineas.push(p); else lineas[i] = lineas[i] ? lineas[i] + ' ' + p : p;
        });
        if (lineas.length > 2) lineas = [lineas[0], lineas.slice(1).join(' ')];
        if (lineas[1] && lineas[1].length > max) lineas[1] = lineas[1].slice(0, max - 1) + '…';
        return lineas;
    }

    // ── Estado (y su reflejo en la URL) ─────────────────────────────────────────────
    var periodoActual = D.periodos.filter(function (p) { return p.actual; }).map(function (p) { return p.id; });
    // En Titulación la población es el propio proceso (no hay "período actual" que aplique).
    var MODO_TIT = window.DM.modo === 'titulacion';
    var POB_DEF = MODO_TIT ? 'todos' : 'actual';
    var estado = { mod: 'todos', pob: POB_DEF, f: {} };   // f: { dimKey: índiceCategoría }

    (function leerUrl() {
        var q = new URLSearchParams(location.search);
        // Enlaces antiguos: ?tab=fid_periodo&periodo_id=8 / ppd_todos …
        var tab = q.get('tab');
        if (tab) {
            estado.mod = tab.indexOf('ppd') === 0 ? 'ppd' : 'fid';
            if (/_todos$/.test(tab)) estado.pob = 'todos';
            else if (q.get('periodo_id')) estado.pob = 'f' + q.get('periodo_id');
            else if (q.get('periodo_ppd_id')) estado.pob = 'p' + q.get('periodo_ppd_id');
        }
        if (q.get('mod')) estado.mod = q.get('mod');
        if (q.get('pob')) estado.pob = q.get('pob');
        q.getAll('f').forEach(function (par) {
            var k = par.slice(0, par.indexOf(':')), v = par.slice(par.indexOf(':') + 1);
            var d = IDX[k];
            if (d && d.cats.indexOf(v) !== -1) estado.f[k] = d.cats.indexOf(v);
        });
        if (!/^(todos|fid|ppd)$/.test(estado.mod)) estado.mod = 'todos';
    })();

    function guardarUrl() {
        var q = new URLSearchParams();
        if (estado.mod !== 'todos') q.set('mod', estado.mod);
        if (estado.pob !== POB_DEF) q.set('pob', estado.pob);
        Object.keys(estado.f).forEach(function (k) { q.append('f', k + ':' + IDX[k].cats[estado.f[k]]); });
        var s = q.toString();
        history.replaceState(null, '', location.pathname + (s ? '?' + s : '') + location.hash);
    }

    // ── Filtrado ────────────────────────────────────────────────────────────────────
    var iSituacion = IDX.situacion.i, catActivo = IDX.situacion.cats.indexOf('Activo');

    function enPoblacion(r) {
        if (estado.mod === 'fid' && r.t !== 'FID') return false;
        if (estado.mod === 'ppd' && r.t !== 'PPD') return false;
        var pob = estado.pob;
        if (pob === 'todos') return true;
        if (pob.indexOf('c:') === 0) return r.v[IDX.tit_condicion.i] === +pob.slice(2);
        if (pob === 'activos') return r.v[iSituacion] === catActivo;
        if (pob === 'actual') return r.m.some(function (m) { return periodoActual.indexOf(m) !== -1; });
        return r.m.indexOf(pob) !== -1;
    }
    function tiene(r, d, cat) {
        var v = r.v[d.i];
        if (v == null) return false;
        return d.tipo === 'multi' ? v.indexOf(cat) !== -1 : v === cat;
    }
    function filtrar(excluir) {
        return FILAS.filter(function (r) {
            if (!enPoblacion(r)) return false;
            for (var k in estado.f) {
                if (k !== excluir && !tiene(r, IDX[k], estado.f[k])) return false;
            }
            return true;
        });
    }
    function contar(filas, d) {
        var c = d.cats.map(function () { return 0; }), n = 0;
        filas.forEach(function (r) {
            var v = r.v[d.i];
            if (v == null) return;
            n++;
            if (d.tipo === 'multi') v.forEach(function (x) { c[x]++; }); else c[v]++;
        });
        return { c: c, n: n };
    }

    // ── Controles de filtro ─────────────────────────────────────────────────────────
    function llenarPoblacion() {
        var sel = $('dmPoblacion');
        if (MODO_TIT) {
            var hT = '<option value="todos">Todos en el proceso de titulación</option>';
            IDX.tit_condicion.cats.forEach(function (c, i) { hT += '<option value="c:' + i + '">' + esc(c) + '</option>'; });
            sel.innerHTML = hT;
            if (!sel.querySelector('option[value="' + estado.pob + '"]')) estado.pob = POB_DEF;
            sel.value = estado.pob;
            sel.addEventListener('change', function () { estado.pob = sel.value; aplicar(); });
            return;
        }
        var nomActual = D.periodos.filter(function (p) { return p.actual; }).map(function (p) { return p.nombre.replace(' (año completo)', ''); }).join(' · ');
        var h = '<optgroup label="General">'
            + '<option value="actual">Matriculados en el período actual' + (nomActual ? ' (' + esc(nomActual) + ')' : '') + '</option>'
            + '<option value="activos">Estudiantes activos (sin egresados ni retirados)</option>'
            + '<option value="todos">Histórico: todas las fichas</option></optgroup>';
        ['FID', 'PPD'].forEach(function (tipo) {
            var ps = D.periodos.filter(function (p) { return p.tipo === tipo; });
            if (!ps.length) return;
            h += '<optgroup label="Matriculados en un período ' + tipo + '">';
            ps.forEach(function (p) { h += '<option value="' + p.id + '">' + esc(p.nombre) + (p.actual ? ' ★' : '') + '</option>'; });
            h += '</optgroup>';
        });
        sel.innerHTML = h;
        if (!sel.querySelector('option[value="' + estado.pob + '"]')) estado.pob = POB_DEF;
        sel.value = estado.pob;
        sel.addEventListener('change', function () {
            estado.pob = sel.value;
            // Un período FID/PPD concreto implica su modalidad.
            if (/^f\d/.test(estado.pob)) estado.mod = 'fid';
            if (/^p\d/.test(estado.pob)) estado.mod = 'ppd';
            aplicar();
        });
    }
    function llenarSelectDim(sel) {
        var d = IDX[sel.dataset.dim];
        var base = filtrar(d.key), cnt = contar(base, d);
        var h = '<option value="">Todos</option>';
        d.cats.forEach(function (c, i) {
            if (cnt.c[i] || estado.f[d.key] === i) h += '<option value="' + i + '">' + esc(c) + ' (' + cnt.c[i] + ')</option>';
        });
        sel.innerHTML = h;
        sel.value = estado.f[d.key] != null ? String(estado.f[d.key]) : '';
    }
    ['dmPrograma', 'dmCiclo', 'dmGenero'].forEach(function (id) {
        $(id).addEventListener('change', function () {
            var k = this.dataset.dim;
            if (this.value === '') delete estado.f[k]; else estado.f[k] = +this.value;
            aplicar();
        });
    });
    $('dmModalidad').addEventListener('click', function (e) {
        var b = e.target.closest('button');
        if (!b) return;
        estado.mod = b.dataset.v;
        if ((estado.mod === 'fid' && /^p\d/.test(estado.pob)) || (estado.mod === 'ppd' && /^f\d/.test(estado.pob))) estado.pob = POB_DEF;
        aplicar();
    });
    function pintarChips() {
        var enBarra = ['programa', 'ciclo', 'genero'];
        var h = '';
        Object.keys(estado.f).forEach(function (k) {
            if (enBarra.indexOf(k) !== -1) return;
            h += '<span class="dm-chip">' + esc(IDX[k].titulo) + ': ' + esc(IDX[k].cats[estado.f[k]])
                + '<button type="button" data-quitar="' + k + '" aria-label="Quitar filtro"><i class="fas fa-times"></i></button></span>';
        });
        if (Object.keys(estado.f).length) h += '<button type="button" class="dm-limpiar" data-quitar="*">Limpiar filtros</button>';
        $('dmChips').innerHTML = h;
    }
    $('dmChips').addEventListener('click', function (e) {
        var b = e.target.closest('[data-quitar]');
        if (!b) return;
        if (b.dataset.quitar === '*') estado.f = {}; else delete estado.f[b.dataset.quitar];
        aplicar();
    });

    // ── Indicadores ─────────────────────────────────────────────────────────────────
    var KPIS = [
        { ico: 'fa-users', color: '#4f46e5', lbl: 'Estudiantes', total: true },
        { ico: 'fa-venus', color: '#db2777', lbl: 'Mujeres', dim: 'genero', cats: ['Femenino'] },
        { ico: 'fa-birthday-cake', color: '#059669', lbl: 'Edad mediana', edad: true },
        { ico: 'fa-language', color: '#8b5cf6', lbl: 'Lengua materna quechua', sub: 'quechua o bilingüe', dim: 'lengua_materna', cats: ['Quechua', 'Castellano y quechua'] },
        { ico: 'fa-feather-alt', color: '#7c3aed', lbl: 'Pueblo originario', sub: 'se autoidentifican', dim: 'autoidentificacion', cats: ['Quechua', 'Aimara', 'Otro pueblo indígena', 'Nativo de la Amazonía'] },
        { ico: 'fa-briefcase', color: '#d97706', lbl: 'Trabajan', dim: 'trabaja', cats: ['Sí'] },
        { ico: 'fa-coins', color: '#dc2626', lbl: 'Ingreso familiar < S/ 500', dim: 'ingreso', cats: ['Menos de S/ 500'] },
        { ico: 'fa-wifi', color: '#0891b2', lbl: 'Sin internet en casa', dim: 'internet_casa', cats: ['No'] },
    ];
    function pintarKpis(filas) {
        var h = '';
        KPIS.forEach(function (k, i) {
            var val, sub, clic = false;
            if (k.total) {
                var nf = filas.filter(function (r) { return r.t === 'FID'; }).length;
                val = filas.length.toLocaleString('es-PE');
                sub = 'FID ' + nf + ' · PPD ' + (filas.length - nf);
            } else if (k.edad) {
                var e = filas.map(function (r) { return r.e; }).filter(function (x) { return x != null; }).sort(function (a, b) { return a - b; });
                var q = function (p) { return e[Math.min(e.length - 1, Math.floor(p * e.length))]; };
                val = e.length ? q(.5) + ' años' : '—';
                sub = e.length ? 'la mitad entre ' + q(.25) + ' y ' + q(.75) + ' años' : 'sin fechas de nacimiento';
            } else {
                var d = IDX[k.dim], ids = k.cats.map(function (c) { return d.cats.indexOf(c); });
                var resp = 0, si = 0;
                filas.forEach(function (r) { var v = r.v[d.i]; if (v == null) return; resp++; if (ids.indexOf(v) !== -1) si++; });
                val = resp ? fmtPct(pct(si, resp)) : '—';
                sub = (k.sub ? k.sub + ' · ' : '') + si + ' de ' + resp + ' con dato';
                clic = si > 0;
            }
            h += '<' + (clic ? 'button type="button" data-kpi="' + i + '"' : 'div') + ' class="dm-kpi" style="--k:' + k.color + '">'
                + '<span class="dm-kpi-ico"><i class="fas ' + k.ico + '"></i></span>'
                + '<span><span class="dm-kpi-val">' + val + '</span><span class="dm-kpi-lbl">' + k.lbl + '</span><span class="dm-kpi-sub">' + esc(sub) + '</span></span>'
                + '</' + (clic ? 'button' : 'div') + '>';
        });
        $('dmKpis').innerHTML = h;
    }
    $('dmKpis').addEventListener('click', function (e) {
        var b = e.target.closest('[data-kpi]');
        if (!b) return;
        var k = KPIS[+b.dataset.kpi], d = IDX[k.dim];
        var ids = k.cats.map(function (c) { return d.cats.indexOf(c); });
        abrirPanel({
            titulo: k.lbl,
            sub: d.titulo + ': ' + k.cats.join(', '),
            filas: ultimo.filas.filter(function (r) { return ids.indexOf(r.v[d.i]) !== -1; }),
            filtro: ids.length === 1 ? { dim: d.key, cat: ids[0] } : null,
        });
    });

    // ── Secciones y tarjetas ────────────────────────────────────────────────────────
    var tarjetas = [];   // { d, el, chart, modo: 'grafico'|'tabla', datos }
    var EXCLUIR_TARJETA = { modalidad: 1 };
    // Preguntas que solo existen en la ficha PPD: su cobertura se mide sobre los PPD.
    var SOLO_PPD = { gestion_procedencia: 1, carrera_procedencia: 1 };
    function universo(d, filas) { return SOLO_PPD[d.key] ? filas.filter(function (r) { return r.t === 'PPD'; }) : filas; }

    (function construirSecciones() {
        var nav = '', h = '';
        D.secciones.forEach(function (s) {
            var color = COLOR_SEC[s.key];
            nav += '<a href="#sec-' + s.key + '" style="--s:' + color + '"><i class="fas ' + s.icono + '"></i>' + esc(s.titulo) + '</a>';
            h += '<section class="dm-sec" id="sec-' + s.key + '" style="--s:' + color + '">'
                + '<h2 class="dm-sec-h"><span class="ico"><i class="fas ' + s.icono + '"></i></span>' + esc(s.titulo) + ' <small data-sec-n></small></h2>'
                + '<div class="dm-grid"></div></section>';
        });
        nav += '<a href="#sec-cruce" style="--s:#4f46e5"><i class="fas fa-th"></i>Cruzar variables</a>'
            + '<a href="#sec-calidad" style="--s:#64748b"><i class="fas fa-clipboard-check"></i>Calidad de datos</a>';
        $('dmNav').innerHTML = nav;
        $('dmSecciones').innerHTML = h;

        DIMS.forEach(function (d) {
            if (EXCLUIR_TARJETA[d.key]) return;
            var grid = document.querySelector('#sec-' + d.seccion + ' .dm-grid');
            var el = document.createElement('article');
            el.className = 'dm-card' + (d.grafico === 'barra' && d.cats.length > 6 ? ' is-wide' : '');
            el.innerHTML = '<div class="dm-card-h"><div><div class="dm-card-t">' + esc(d.titulo) + '</div>'
                + (d.pregunta ? '<div class="dm-card-q">' + esc(d.pregunta) + '</div>' : '') + '</div>'
                + '<div class="dm-card-tools"><button type="button" class="dm-ico-btn" data-modo title="Ver como tabla" aria-label="Alternar tabla y gráfico"><i class="fas fa-table"></i></button></div></div>'
                + '<div class="dm-cob"></div><div class="dm-insight"></div><div class="dm-chart"><canvas></canvas></div>';
            grid.appendChild(el);
            var t = { d: d, el: el, chart: null, modo: 'grafico', datos: null, visible: false };
            tarjetas.push(t);
            el.querySelector('[data-modo]').addEventListener('click', function () {
                t.modo = t.modo === 'grafico' ? 'tabla' : 'grafico';
                this.classList.toggle('is-on', t.modo === 'tabla');
                this.title = t.modo === 'tabla' ? 'Ver como gráfico' : 'Ver como tabla';
                this.innerHTML = '<i class="fas ' + (t.modo === 'tabla' ? 'fa-chart-bar' : 'fa-table') + '"></i>';
                if (t.chart) { t.chart.destroy(); t.chart = null; }
                pintarTarjeta(t);
            });
        });
    })();

    // Las tarjetas se dibujan al acercarse a la pantalla: ~60 gráficos a la vez serían lentos.
    var observador = 'IntersectionObserver' in window ? new IntersectionObserver(function (ents) {
        ents.forEach(function (en) {
            var t = tarjetas.find(function (x) { return x.el === en.target; });
            if (en.isIntersecting && t && !t.visible) { t.visible = true; pintarTarjeta(t); }
        });
    }, { rootMargin: '300px' }) : null;
    tarjetas.forEach(function (t) { if (observador) observador.observe(t.el); else t.visible = true; });

    function datosTarjeta(t, filas) {
        var d = t.d;
        filas = universo(d, filas);
        var cnt = contar(filas, d);
        var items = d.cats.map(function (c, i) { return { cat: i, lbl: c, n: cnt.c[i] }; }).filter(function (x) { return x.n > 0 || estado.f[d.key] === x.cat; });
        if (!d.ordinal) items.sort(function (a, b) { return b.n - a.n; });
        return { items: items, resp: cnt.n, total: filas.length };
    }

    function pintarTarjeta(t) {
        var d = t.d, x = t.datos;
        if (!x) return;
        var el = t.el, area = el.querySelector('.dm-chart');
        var cob = pct(x.resp, x.total);
        el.querySelector('.dm-cob').className = 'dm-cob' + (x.total && cob < 60 ? ' is-low' : '');
        el.querySelector('.dm-cob').innerHTML = x.total
            ? '<span>Respondieron <strong>' + x.resp + '</strong> de ' + x.total + '</span><span class="dm-cob-bar"><span style="width:' + cob + '%"></span></span><span>' + fmtPct(cob) + '</span>'
            : '';
        var top = x.items.slice().sort(function (a, b) { return b.n - a.n; })[0];
        var base = x.resp;
        el.querySelector('.dm-insight').innerHTML = top && top.n
            ? 'Más frecuente: <strong>' + esc(top.lbl) + '</strong> · ' + fmtPct(pct(top.n, base)) + (d.tipo === 'multi' ? ' <span class="dm-card-q">(respuesta múltiple: los % no suman 100)</span>' : '')
            : '';

        if (!x.resp) {
            if (t.chart) { t.chart.destroy(); t.chart = null; }
            area.innerHTML = '<div class="dm-vacio"><i class="fas fa-inbox mr-1"></i> Nadie respondió esta pregunta en la selección actual.</div>';
            return;
        }

        if (t.modo === 'tabla') {
            var h = '<table class="dm-tabla"><thead><tr><th>' + esc(d.titulo) + '</th><th class="num">Estudiantes</th><th class="num">%</th></tr></thead><tbody>';
            x.items.forEach(function (it) {
                h += '<tr data-cat="' + it.cat + '"><td>' + esc(it.lbl) + '</td><td class="num">' + it.n + '</td><td class="num">' + fmtPct(pct(it.n, base)) + '</td></tr>';
            });
            area.innerHTML = h + '</tbody></table>';
            area.querySelectorAll('tr[data-cat]').forEach(function (tr) {
                tr.addEventListener('click', function () { abrirSegmento(d, +tr.dataset.cat); });
            });
            return;
        }

        // Gráfico: barras con top 10 (+ "Otros") para variables con muchas categorías.
        var items = x.items;
        var otros = null;
        if (!d.ordinal && d.grafico !== 'dona' && items.length > MAX_BARRAS) {
            var resto = items.slice(MAX_BARRAS - 1);
            otros = { cat: -1, lbl: 'Otros (' + resto.length + ')', n: resto.reduce(function (s, i) { return s + i.n; }, 0) };
            items = items.slice(0, MAX_BARRAS - 1).concat([otros]);
        }
        var color = getComputedStyle(el).getPropertyValue('--s').trim() || '#4f46e5';
        var filtroCat = estado.f[d.key];
        var colores = items.map(function (it, i) {
            if (d.grafico === 'dona') return PALETA[i % PALETA.length];
            if (it.cat === -1) return '#94a3b8';
            return filtroCat != null && filtroCat !== it.cat ? color + '40' : color;
        });
        var labels = items.map(function (it) { return it.lbl; });
        var valores = items.map(function (it) { return it.n; });
        var texto = css('--dm-text'), muted = css('--dm-muted'), borde = css('--dm-border'), fondo = css('--dm-card');
        var horizontal = d.grafico === 'barra';
        var alto = d.grafico === 'dona' ? 210 : horizontal ? Math.max(120, items.length * 34 + 20) : 230;

        if (t.chart && t.chart.config.type === (d.grafico === 'dona' ? 'doughnut' : 'bar')) {
            t.chart.data.labels = labels;
            t.chart.data.datasets[0].data = valores;
            t.chart.data.datasets[0].backgroundColor = colores;
            t.chart.$items = items; t.chart.$base = base;
            area.style.height = alto + 'px';
            t.chart.update();
            return;
        }
        if (t.chart) t.chart.destroy();
        area.innerHTML = '<canvas role="img" aria-label="' + esc(d.titulo) + '"></canvas>';
        area.style.height = alto + 'px';
        var ctx = area.querySelector('canvas');

        var etiquetas = {
            id: 'dmEtiquetas',
            afterDatasetsDraw: function (chart) {
                if (chart.config.type !== 'bar') return;
                var c = chart.ctx, meta = chart.getDatasetMeta(0);
                c.save();
                c.font = '600 11px Nunito, system-ui, sans-serif';
                c.fillStyle = muted;
                meta.data.forEach(function (bar, i) {
                    var n = chart.data.datasets[0].data[i];
                    if (!n) return;
                    var txt = n + ' · ' + fmtPct(pct(n, chart.$base));
                    if (horizontal) { c.textAlign = 'left'; c.textBaseline = 'middle'; c.fillText(txt, bar.x + 6, bar.y); }
                    else { c.textAlign = 'center'; c.textBaseline = 'bottom'; c.fillText(n, bar.x, bar.y - 4); }
                });
                c.restore();
            },
        };

        var opciones = {
            responsive: true, maintainAspectRatio: false, animation: { duration: 300 },
            layout: { padding: horizontal ? { right: 78 } : { top: 18 } },
            onClick: function (ev, els, chart) {
                if (!els.length) return;
                var it = chart.$items[els[0].index];
                // "Otros" agrupa varias categorías: se muestra la tabla completa.
                if (it.cat === -1) { el.querySelector('[data-modo]').click(); return; }
                abrirSegmento(d, it.cat);
            },
            onHover: function (ev, els) { ev.native.target.style.cursor = els.length ? 'pointer' : 'default'; },
            plugins: {
                legend: d.grafico === 'dona' ? { position: 'right', labels: { color: texto, boxWidth: 10, boxHeight: 10, font: { size: 11 }, generateLabels: function (chart) {
                    var ds = chart.data.datasets[0];
                    return chart.data.labels.map(function (l, i) {
                        return { text: l + '  ' + fmtPct(pct(ds.data[i], chart.$base)), fillStyle: ds.backgroundColor[i], strokeStyle: ds.backgroundColor[i], fontColor: texto, index: i, hidden: false };
                    });
                } } } : { display: false },
                tooltip: { callbacks: { label: function (c) { return ' ' + c.raw + ' estudiantes · ' + fmtPct(pct(c.raw, c.chart.$base)); } } },
            },
        };
        if (d.grafico !== 'dona') {
            var ejeValor = { beginAtZero: true, ticks: { precision: 0, color: muted, font: { size: 10 } }, grid: { color: borde }, border: { display: false } };
            var ejeCat = { ticks: { color: texto, font: { size: 11 }, autoSkip: false, callback: function (v) { return partir(this.getLabelForValue(v), horizontal ? 22 : 14); } }, grid: { display: false }, border: { display: false } };
            opciones.indexAxis = horizontal ? 'y' : 'x';
            opciones.scales = horizontal ? { x: Object.assign(ejeValor, { display: false }), y: ejeCat } : { y: ejeValor, x: ejeCat };
        } else {
            opciones.cutout = '62%';
        }
        t.chart = new Chart(ctx, {
            type: d.grafico === 'dona' ? 'doughnut' : 'bar',
            data: { labels: labels, datasets: [{ data: valores, backgroundColor: colores, borderRadius: d.grafico === 'dona' ? 0 : 6, borderWidth: d.grafico === 'dona' ? 2 : 0, borderColor: fondo, maxBarThickness: 28 }] },
            options: opciones,
            plugins: [etiquetas],
        });
        t.chart.$items = items; t.chart.$base = base;
    }

    function abrirSegmento(d, cat) {
        var filas = ultimo.filas.filter(function (r) { return tiene(r, d, cat); });
        abrirPanel({ titulo: d.cats[cat], sub: d.titulo, filas: filas, filtro: { dim: d.key, cat: cat } });
    }

    // ── Panel lateral con los estudiantes de un segmento ────────────────────────────
    var panel = { filas: [], filtro: null };
    function nombreCat(r, key) { var d = IDX[key], v = r.v[d.i]; return v == null ? null : d.cats[v]; }
    function pintarLista() {
        var q = norm($('dmDrBuscar').value);
        var h = '';
        var filas = panel.filas.filter(function (r) { return !q || norm(r.n + ' ' + r.d).indexOf(q) !== -1; });
        filas.slice(0, 400).forEach(function (r) {
            var meta = [r.d ? 'DNI ' + r.d : null, nombreCat(r, 'programa'), nombreCat(r, 'ciclo'), nombreCat(r, 'situacion') !== 'Activo' ? nombreCat(r, 'situacion') : null].filter(Boolean).join(' · ');
            h += '<a class="dm-dr-item" href="' + window.DM.expediente.replace('__ID__', r.i) + '" target="_blank" rel="noopener">'
                + '<span><b>' + esc(r.n) + '<span class="dm-tag ' + r.t.toLowerCase() + '">' + r.t + '</span></b><small>' + esc(meta) + '</small></span>'
                + '<i class="fas fa-chevron-right"></i></a>';
        });
        if (filas.length > 400) h += '<div class="dm-vacio">Se muestran 400 de ' + filas.length + '. Usa el buscador para acotar.</div>';
        $('dmDrLista').innerHTML = h || '<div class="dm-vacio">Sin coincidencias.</div>';
    }
    function abrirPanel(o) {
        panel = o;
        o.filas.sort(function (a, b) { return a.n.localeCompare(b.n, 'es'); });
        $('dmDrTitulo').textContent = o.titulo;
        $('dmDrSub').textContent = o.sub + ' · ' + o.filas.length + ' estudiante(s) · clic para abrir su expediente';
        $('dmDrFiltrar').style.display = o.filtro ? '' : 'none';
        $('dmDrBuscar').value = '';
        pintarLista();
        $('dmDrawer').classList.add('is-open');
        $('dmDrawer').setAttribute('aria-hidden', 'false');
        $('dmFondo').classList.add('is-open');
        setTimeout(function () { $('dmDrBuscar').focus(); }, 200);
    }
    function cerrarPanel() {
        $('dmDrawer').classList.remove('is-open');
        $('dmDrawer').setAttribute('aria-hidden', 'true');
        $('dmFondo').classList.remove('is-open');
    }
    $('dmDrBuscar').addEventListener('input', pintarLista);
    $('dmDrCerrar').addEventListener('click', cerrarPanel);
    $('dmFondo').addEventListener('click', cerrarPanel);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarPanel(); });
    $('dmDrFiltrar').addEventListener('click', function () {
        if (!panel.filtro) return;
        estado.f[panel.filtro.dim] = panel.filtro.cat;
        cerrarPanel();
        aplicar();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ── Cruce de variables ──────────────────────────────────────────────────────────
    (function () {
        var h = '';
        D.secciones.forEach(function (s) {
            h += '<optgroup label="' + esc(s.titulo) + '">';
            DIMS.filter(function (d) { return d.seccion === s.key && d.tipo === 'cat'; }).forEach(function (d) { h += '<option value="' + d.key + '">' + esc(d.titulo) + '</option>'; });
            h += '</optgroup>';
        });
        $('dmCruceA').innerHTML = h; $('dmCruceB').innerHTML = h;
        $('dmCruceA').value = 'programa'; $('dmCruceB').value = 'lengua_materna';
        ['dmCruceA', 'dmCruceB', 'dmCruceModo'].forEach(function (id) { $(id).addEventListener('change', pintarCruce); });
    })();

    function pintarCruce() {
        var A = IDX[$('dmCruceA').value], B = IDX[$('dmCruceB').value], modo = $('dmCruceModo').value;
        var filas = ultimo.filas.filter(function (r) { return r.v[A.i] != null && r.v[B.i] != null; });
        var m = A.cats.map(function () { return B.cats.map(function () { return 0; }); });
        filas.forEach(function (r) { m[r.v[A.i]][r.v[B.i]]++; });
        var totF = m.map(function (f) { return f.reduce(function (s, x) { return s + x; }, 0); });
        var totC = B.cats.map(function (c, j) { return m.reduce(function (s, f) { return s + f[j]; }, 0); });
        var cols = B.cats.map(function (c, j) { return j; }).filter(function (j) { return totC[j]; });
        var rows = A.cats.map(function (c, i) { return i; }).filter(function (i) { return totF[i]; });
        if (!rows.length) { $('dmCruce').innerHTML = '<tr><td class="dm-vacio">Nadie respondió ambas preguntas en la selección actual.</td></tr>'; return; }
        var acc = '79,70,229';
        var h = '<thead><tr><th>' + esc(A.titulo) + '</th>';
        cols.forEach(function (j) { h += '<th class="col">' + esc(B.cats[j]) + '</th>'; });
        h += '<th class="tot">Total</th></tr></thead><tbody>';
        rows.forEach(function (i) {
            h += '<tr><td><strong>' + esc(A.cats[i]) + '</strong></td>';
            cols.forEach(function (j) {
                var n = m[i][j], p = modo === 'total' ? pct(n, filas.length) : pct(n, totF[i]);
                var a = modo === 'n' ? (n / Math.max.apply(null, totF)) : p / 100;
                h += '<td class="cel" data-a="' + i + '" data-b="' + j + '" style="background:rgba(' + acc + ',' + (0.06 + a * 0.55).toFixed(2) + ')">'
                    + '<strong>' + n + '</strong>' + (modo === 'n' ? '' : '<small>' + fmtPct(p) + '</small>') + '</td>';
            });
            h += '<td class="tot">' + totF[i] + '</td></tr>';
        });
        h += '<tr><td class="tot">Total</td>';
        cols.forEach(function (j) { h += '<td class="tot">' + totC[j] + '</td>'; });
        h += '<td class="tot">' + filas.length + '</td></tr></tbody>';
        $('dmCruce').innerHTML = h;
    }
    $('dmCruce').addEventListener('click', function (e) {
        var td = e.target.closest('td.cel');
        if (!td) return;
        var A = IDX[$('dmCruceA').value], B = IDX[$('dmCruceB').value], a = +td.dataset.a, b = +td.dataset.b;
        abrirPanel({
            titulo: A.cats[a] + ' × ' + B.cats[b],
            sub: A.titulo + ' y ' + B.titulo,
            filas: ultimo.filas.filter(function (r) { return r.v[A.i] === a && r.v[B.i] === b; }),
            filtro: null,
        });
    });

    // ── Calidad de datos ────────────────────────────────────────────────────────────
    var BASICOS = ['genero', 'edad', 'ingreso', 'permanencia', 'departamento'];
    function pintarCalidad(filas) {
        var lista = DIMS.filter(function (d) { return !EXCLUIR_TARJETA[d.key] && d.key !== 'situacion' && d.key !== 'lugar_conexion'; })
            .map(function (d) { var u = universo(d, filas); return { d: d, n: contar(u, d).n, total: u.length }; })
            .filter(function (x) { return x.total > 0; })
            .sort(function (a, b) { return a.n / a.total - b.n / b.total; });
        var h = '';
        lista.forEach(function (x) {
            var p = pct(x.n, x.total), col = p >= 90 ? '#16a34a' : p >= 60 ? '#d97706' : '#dc2626';
            h += '<div class="dm-cal-row"><span title="' + esc(x.d.titulo) + '">' + esc(x.d.titulo) + '</span><span class="dm-cal-bar"><i style="width:' + p + '%;background:' + col + '"></i></span><span class="dm-cal-pct">' + Math.round(p) + '%</span></div>';
        });
        $('dmCalidad').innerHTML = h;
        var incompletas = filas.filter(function (r) {
            return BASICOS.filter(function (k) { return r.v[IDX[k].i] != null; }).length < 3;
        });
        $('dmCalidadResumen').innerHTML = incompletas.length
            ? '<button type="button" class="dm-btn" id="dmIncompletas"><i class="fas fa-user-edit"></i> ' + incompletas.length + ' ficha(s) con datos básicos incompletos</button>'
              + ' <span class="dm-card-q">les faltan al menos 3 de: género, edad, ingreso, permanencia y departamento. Conviene pedirles que completen su ficha.</span>'
            : '<i class="fas fa-check-circle" style="color:#16a34a"></i> Todas las fichas de la selección tienen sus datos básicos.';
        var b = $('dmIncompletas');
        if (b) b.addEventListener('click', function () {
            abrirPanel({ titulo: 'Fichas incompletas', sub: 'Datos básicos', filas: incompletas, filtro: null });
        });
    }

    // ── Exportar e imprimir ─────────────────────────────────────────────────────────
    function descripcionFiltros() {
        var partes = [{ todos: 'FID y PPD', fid: 'FID', ppd: 'PPD' }[estado.mod], $('dmPoblacion').selectedOptions[0].text];
        Object.keys(estado.f).forEach(function (k) { partes.push(IDX[k].titulo + ': ' + IDX[k].cats[estado.f[k]]); });
        return partes.join(' · ');
    }
    $('dmExportar').addEventListener('click', function () {
        var filas = ultimo.filas, sep = ';';
        var c = function (v) { v = String(v); return /[;"\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
        var out = ['Datos demográficos', 'Selección' + sep + c(descripcionFiltros()), 'Estudiantes' + sep + filas.length, 'Generado' + sep + new Date().toLocaleString('es-PE'), '',
            ['Sección', 'Variable', 'Categoría', 'Estudiantes', '% de quienes respondieron', 'Respondieron'].join(sep)];
        var secs = {}; D.secciones.forEach(function (s) { secs[s.key] = s.titulo; });
        DIMS.forEach(function (d) {
            if (EXCLUIR_TARJETA[d.key]) return;
            var x = datosTarjeta({ d: d }, filas);
            if (!x.resp) return;
            x.items.forEach(function (it) {
                out.push([secs[d.seccion], d.titulo, it.lbl, it.n, String(pct(it.n, x.resp)).replace('.', ','), x.resp].map(c).join(sep));
            });
        });
        var blob = new Blob(['﻿' + out.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'datos-demograficos-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    });
    $('dmImprimir').addEventListener('click', function () {
        // Para imprimir, todas las tarjetas deben estar dibujadas.
        tarjetas.forEach(function (t) { if (!t.visible) { t.visible = true; pintarTarjeta(t); } });
        setTimeout(function () { window.print(); }, 400);
    });

    // ── Navegación: resaltar la sección visible ─────────────────────────────────────
    if ('IntersectionObserver' in window) {
        var espia = new IntersectionObserver(function (ents) {
            ents.forEach(function (en) {
                if (!en.isIntersecting) return;
                document.querySelectorAll('#dmNav a').forEach(function (a) { a.classList.toggle('is-on', a.getAttribute('href') === '#' + en.target.id); });
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        document.querySelectorAll('.dm-sec').forEach(function (s) { espia.observe(s); });
    }

    // ── Aplicar ─────────────────────────────────────────────────────────────────────
    var ultimo = { filas: [] };
    function aplicar() {
        var filas = filtrar();
        ultimo.filas = filas;
        document.querySelectorAll('#dmModalidad button').forEach(function (b) { b.classList.toggle('is-on', b.dataset.v === estado.mod); });
        $('dmPoblacion').value = estado.pob;
        ['dmPrograma', 'dmCiclo', 'dmGenero'].forEach(function (id) { llenarSelectDim($(id)); });
        pintarChips();
        $('dmResumen').textContent = filas.length.toLocaleString('es-PE') + ' estudiante(s) · ' + descripcionFiltros();
        pintarKpis(filas);

        var vacio = !filas.length;
        document.querySelectorAll('.dm-sec').forEach(function (s) { s.style.display = vacio ? 'none' : ''; });
        var aviso = $('dmVacio');
        if (vacio && !aviso) {
            aviso = document.createElement('div');
            aviso.id = 'dmVacio'; aviso.className = 'dm-empty-all';
            $('dmSecciones').before(aviso);
        }
        if (aviso) {
            aviso.style.display = vacio ? '' : 'none';
            aviso.innerHTML = '<i class="fas fa-inbox fa-2x mb-2 d-block"></i><strong>No hay estudiantes en esta selección.</strong><br><span class="small">'
                + (estado.pob === 'actual' ? 'Puede que aún no haya matrículas en el período actual: prueba con «Estudiantes activos» o «Histórico».' : 'Prueba quitando algún filtro.') + '</span>';
        }

        tarjetas.forEach(function (t) {
            t.datos = datosTarjeta(t, filas);
            // Una tarjeta sin respuestas en la selección (p. ej. datos solo PPD viendo FID) se oculta.
            t.el.style.display = t.datos.resp || estado.f[t.d.key] != null ? '' : 'none';
            if (t.visible) pintarTarjeta(t);
        });
        D.secciones.forEach(function (s) {
            var sec = $('sec-' + s.key);
            var visibles = tarjetas.filter(function (t) { return t.d.seccion === s.key && t.el.style.display !== 'none'; }).length;
            sec.querySelector('[data-sec-n]').textContent = '· ' + visibles + ' indicador(es)';
            if (!vacio) sec.style.display = visibles ? '' : 'none';
        });
        if (!vacio) { pintarCruce(); pintarCalidad(filas); }
        guardarUrl();
    }

    // Cambio de tema: los colores de los gráficos se leen de las variables CSS.
    new MutationObserver(function () {
        tarjetas.forEach(function (t) { if (t.chart) { t.chart.destroy(); t.chart = null; } if (t.visible) pintarTarjeta(t); });
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });

    llenarPoblacion();
    aplicar();
});
</script>
@endverbatim
@endsection
