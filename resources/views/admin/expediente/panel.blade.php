@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
@php
    $listas = $panel['listas'];
    $primera = collect(array_keys($listas))->first(fn ($k) => $listas[$k]->isNotEmpty()) ?? 'licencias';
    $query = array_filter($filtros);
@endphp

<style>
    .pn {
        --pn-card: #fff; --pn-soft: #f8fafc; --pn-border: #e5e7eb; --pn-text: #1e293b; --pn-muted: #64748b;
        --pn-accent: #4f46e5; --pn-shadow: 0 1px 2px rgba(15,23,42,.05), 0 4px 14px rgba(15,23,42,.04);
        --c-amber: #b45309; --c-amber-bg: rgba(217,119,6,.1);
        --c-red: #b91c1c; --c-red-bg: rgba(220,38,38,.09);
        --c-green: #15803d; --c-green-bg: rgba(22,163,74,.1);
        --c-violet: #6d28d9; --c-violet-bg: rgba(124,58,237,.1);
        --c-slate: #475569; --c-slate-bg: rgba(100,116,139,.12);
    }
    .dark-mode .pn, .dim-mode .pn {
        --pn-card: rgba(255,255,255,.035); --pn-soft: rgba(255,255,255,.03); --pn-border: rgba(255,255,255,.1);
        --pn-text: #e2e8f0; --pn-muted: #94a3b8; --pn-accent: #a5b4fc; --pn-shadow: none;
        --c-amber: #fcd34d; --c-red: #fca5a5; --c-green: #86efac; --c-violet: #c4b5fd; --c-slate: #cbd5e1;
    }
    .pn [hidden] { display: none !important; }
    .pn-title { font-size: 1.3rem; font-weight: 800; color: var(--pn-text); margin: 0; }
    .pn-sub { font-size: .82rem; color: var(--pn-muted); }
    .pn-filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-left: auto; }
    .pn-select { height: 34px; font-size: .82rem; color: var(--pn-text); background: var(--pn-card); border: 1px solid var(--pn-border); border-radius: 8px; padding: 0 .5rem; max-width: 240px; }
    .dark-mode .pn-select option, .dim-mode .pn-select option { background: #1f2937; }

    .pn-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .7rem; margin: 1rem 0; }
    .pn-kpi { display: flex; align-items: center; gap: .8rem; padding: .85rem 1rem; text-align: left; width: 100%;
        background: var(--pn-card); border: 1px solid var(--pn-border); border-radius: 14px; box-shadow: var(--pn-shadow);
        cursor: pointer; transition: border-color .15s, transform .15s; color: var(--pn-text); }
    .pn-kpi:hover { transform: translateY(-1px); border-color: var(--pn-accent); }
    .pn-kpi.is-active { border-color: var(--pn-accent); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
    .pn-kpi.is-zero { opacity: .55; }
    .pn-kpi-icon { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    @foreach (['amber','red','green','violet','slate'] as $c)
        .pn-kpi-icon.is-{{ $c }} { color: var(--c-{{ $c }}); background: var(--c-{{ $c }}-bg); }
    @endforeach
    .pn-kpi-num { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
    .pn-kpi-label { font-size: .74rem; color: var(--pn-muted); font-weight: 600; line-height: 1.25; }

    .pn-card { background: var(--pn-card); border: 1px solid var(--pn-border); border-radius: 14px; box-shadow: var(--pn-shadow); }
    .pn-list-head { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-bottom: 1px solid var(--pn-border); }
    .pn-list-title { font-size: 1rem; font-weight: 800; color: var(--pn-text); }
    .pn-btn { display: inline-flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 600; padding: .38rem .8rem;
        border-radius: 8px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-text); text-decoration: none !important; margin-left: auto; }
    .pn-btn:hover { border-color: var(--pn-accent); color: var(--pn-accent); }
    .pn-row { display: flex; align-items: center; gap: .8rem; padding: .6rem 1.1rem; border-bottom: 1px solid var(--pn-border); text-decoration: none !important; color: var(--pn-text); }
    .pn-row:last-child { border-bottom: 0; }
    .pn-row:hover { background: var(--pn-soft); }
    .pn-avatar { width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 800; color: #4338ca; background: #e0e7ff; }
    .dark-mode .pn-avatar, .dim-mode .pn-avatar { background: rgba(165,180,252,.15); color: #c7d2fe; }
    .pn-name { font-size: .86rem; font-weight: 700; }
    .pn-meta { font-size: .72rem; color: var(--pn-muted); }
    .pn-detalle { margin-left: auto; font-size: .78rem; color: var(--pn-text); text-align: right; max-width: 55%; }
    .pn-empty { text-align: center; padding: 2.5rem 1rem; color: var(--pn-muted); font-size: .86rem; }
    .pn-search { height: 32px; font-size: .8rem; padding: 0 .7rem; border-radius: 8px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-text); width: 220px; }

    @media (max-width: 1199.98px) { .pn-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) {
        .pn-grid { grid-template-columns: 1fr; }
        .pn-row { flex-wrap: wrap; }
        .pn-detalle { margin-left: 2.9rem; max-width: 100%; text-align: left; }
        .pn-filtros { margin-left: 0; width: 100%; }
    }
</style>

<div class="container-fluid py-3 pn">
    <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
        <div>
            <h4 class="pn-title"><i class="fas fa-bell mr-2" style="color:#4f46e5;"></i>Alertas académicas</h4>
            <div class="pn-sub">Licencias, retiros, reincorporaciones, desaprobados y repitencia · {{ $panel['universo'] }} estudiantes (sin egresados){{ $query ? ' con los filtros aplicados' : '' }} · clic en un nombre para abrir su expediente</div>
        </div>
        <form method="GET" class="pn-filtros" id="pnFiltros">
            <select name="programa_id" class="pn-select" onchange="this.form.ciclo_id.value='';this.form.submit()" aria-label="Programa">
                <option value="">Todos los programas</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->id }}" @selected(($filtros['programa_id'] ?? '') == $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
            <select name="ciclo_id" class="pn-select" onchange="this.form.submit()" aria-label="Ciclo">
                <option value="">Todos los ciclos</option>
                @foreach ($ciclos->when($filtros['programa_id'] ?? null, fn ($c, $p) => $c->where('programa_id', $p)) as $c)
                    <option value="{{ $c->id }}" @selected(($filtros['ciclo_id'] ?? '') == $c->id)>{{ $c->programa->nombre ?? '' }} · {{ $c->nombre }}</option>
                @endforeach
            </select>
            @if ($query)
                <a href="{{ route('expediente.panel') }}" class="small">Quitar filtros</a>
            @endif
        </form>
    </div>

    <div class="pn-grid">
        @foreach (\App\Services\PanelInstitucional::LISTAS as $k => [$label, $icono, $color])
            <button type="button" class="pn-kpi {{ $k === $primera ? 'is-active' : '' }} {{ $listas[$k]->isEmpty() ? 'is-zero' : '' }}" data-lista="{{ $k }}">
                <span class="pn-kpi-icon is-{{ $color }}"><i class="fas {{ $icono }}"></i></span>
                <span>
                    <span class="pn-kpi-num d-block">{{ $listas[$k]->count() }}</span>
                    <span class="pn-kpi-label d-block">{{ $label }}</span>
                </span>
            </button>
        @endforeach
    </div>

    @foreach (\App\Services\PanelInstitucional::LISTAS as $k => [$label, $icono, $color])
        <section class="pn-card" data-panel="{{ $k }}" @if ($k !== $primera) hidden @endif>
            <div class="pn-list-head">
                <span class="pn-list-title"><i class="fas {{ $icono }} mr-1" style="color:var(--c-{{ $color }});"></i> {{ $label }}</span>
                <span class="pn-sub">{{ $listas[$k]->count() }}</span>
                @if ($listas[$k]->count() > 8)
                    <input type="search" class="pn-search js-buscar" placeholder="Filtrar esta lista…" aria-label="Filtrar">
                @endif
                @if ($listas[$k]->isNotEmpty())
                    <a href="{{ route('expediente.panel.exportar', ['lista' => $k] + $query) }}" class="pn-btn"><i class="fas fa-file-excel"></i> Exportar</a>
                @endif
            </div>
            @forelse ($listas[$k] as $f)
                <a href="{{ route('expediente.show', $f->u) }}" class="pn-row" data-buscar="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($f->u->apellidos . ' ' . $f->u->name . ' ' . $f->u->dni)) }}">
                    <span class="pn-avatar">{{ mb_strtoupper(mb_substr(trim((string) $f->u->name), 0, 1) . mb_substr(trim((string) $f->u->apellidos), 0, 1)) }}</span>
                    <span>
                        <span class="pn-name d-block">{{ $f->u->apellidos }}, {{ $f->u->name }}</span>
                        <span class="pn-meta d-block">{{ $f->u->programa->nombre ?? 'Sin programa' }} · {{ $f->u->ciclo->nombre ?? 'sin ciclo' }} · DNI {{ $f->u->dni }}</span>
                    </span>
                    <span class="pn-detalle">{{ $f->detalle }}</span>
                </a>
            @empty
                <div class="pn-empty"><i class="fas fa-check-circle d-block mb-2" style="font-size:1.4rem;color:var(--c-green);"></i> Nadie en esta lista.</div>
            @endforelse
        </section>
    @endforeach
</div>

<script>
(function () {
    var CLAVE = 'seguimientoEstudiantil.lista';
    function mostrar(k) {
        document.querySelectorAll('.pn-kpi').forEach(function (b) { b.classList.toggle('is-active', b.dataset.lista === k); });
        document.querySelectorAll('[data-panel]').forEach(function (s) { s.hidden = s.dataset.panel !== k; });
        try { sessionStorage.setItem(CLAVE, k); } catch (e) {}
    }
    document.querySelectorAll('.pn-kpi').forEach(function (b) {
        b.addEventListener('click', function () { mostrar(b.dataset.lista); });
    });
    try {
        var guardada = sessionStorage.getItem(CLAVE);
        if (guardada && document.querySelector('[data-panel="' + guardada + '"]')) mostrar(guardada);
    } catch (e) {}

    document.querySelectorAll('.js-buscar').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var t = inp.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            inp.closest('[data-panel]').querySelectorAll('.pn-row').forEach(function (r) {
                r.hidden = t && r.dataset.buscar.indexOf(t) === -1;
            });
        });
    });
})();
</script>
@endsection
