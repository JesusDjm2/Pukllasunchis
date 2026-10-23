@extends($layout)
@section('contenido')
@php
    $ficha = $u->alumno ?? $u->alumnoB;
    $esPpd = (bool) $u->programa?->esPpd();
    $iniciales = mb_strtoupper(mb_substr(trim((string) $u->name), 0, 1) . mb_substr(trim((string) $u->apellidos), 0, 1));
    $r = $historial['resumen'];
    $estilo = [
        'licencia' => ['amber', 'fa-pause-circle'],
        'retiro' => ['red', 'fa-door-open'],
        'inhabilitacion' => ['red', 'fa-ban'],
        'reincorporacion' => ['green', 'fa-undo-alt'],
        'cambio_ciclo' => ['blue', 'fa-level-up-alt'],
        'cambio_programa' => ['blue', 'fa-exchange-alt'],
        'egreso' => ['violet', 'fa-user-graduate'],
        'beca' => ['green', 'fa-award'],
        'sancion' => ['red', 'fa-gavel'],
        'observacion' => ['slate', 'fa-sticky-note'],
        'estado_inicial' => ['slate', 'fa-flag'],
    ];
    $pctLicencia = min(100, round($licencia['dias'] / max(1, $licencia['limite']) * 100));
    $ciclo = fn ($c) => $c ? (preg_match('/^[IVX]+$/', trim($c->nombre)) ? 'Ciclo ' . trim($c->nombre) : $c->nombre) : '—';
@endphp

<style>
    .ex {
        --ex-card: #fff; --ex-soft: #f8fafc; --ex-border: #e5e7eb; --ex-text: #1e293b; --ex-muted: #64748b;
        --ex-accent: #4f46e5; --ex-shadow: 0 1px 2px rgba(15,23,42,.05), 0 4px 14px rgba(15,23,42,.04);
        --c-amber: #b45309; --c-amber-bg: rgba(217,119,6,.1);
        --c-red: #b91c1c; --c-red-bg: rgba(220,38,38,.09);
        --c-green: #15803d; --c-green-bg: rgba(22,163,74,.1);
        --c-blue: #1d4ed8; --c-blue-bg: rgba(37,99,235,.09);
        --c-violet: #6d28d9; --c-violet-bg: rgba(124,58,237,.1);
        --c-slate: #475569; --c-slate-bg: rgba(100,116,139,.12);
    }
    .dark-mode .ex, .dim-mode .ex {
        --ex-card: rgba(255,255,255,.035); --ex-soft: rgba(255,255,255,.03); --ex-border: rgba(255,255,255,.1);
        --ex-text: #e2e8f0; --ex-muted: #94a3b8; --ex-accent: #a5b4fc; --ex-shadow: none;
        --c-amber: #fcd34d; --c-red: #fca5a5; --c-green: #86efac; --c-blue: #93c5fd; --c-violet: #c4b5fd; --c-slate: #cbd5e1;
    }
    .ex [hidden] { display: none !important; }
    .ex-card { background: var(--ex-card); border: 1px solid var(--ex-border); border-radius: 14px; box-shadow: var(--ex-shadow); }
    .ex-back { font-size: .8rem; color: var(--ex-muted); text-decoration: none !important; }
    .ex-back:hover { color: var(--ex-accent); }

    /* Cabecera */
    .ex-head { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; padding: 1.1rem 1.25rem; }
    .ex-avatar { width: 64px; height: 64px; border-radius: 16px; object-fit: cover; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;
        color: #4338ca; background: #e0e7ff; }
    .dark-mode div.ex-avatar, .dim-mode div.ex-avatar { background: rgba(165,180,252,.15); color: #c7d2fe; }
    .ex-name { font-size: 1.25rem; font-weight: 800; color: var(--ex-text); line-height: 1.2; }
    .ex-meta { display: flex; flex-wrap: wrap; gap: .2rem .9rem; font-size: .8rem; color: var(--ex-muted); margin-top: .25rem; }
    .ex-chips { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
    .ex-head-actions { margin-left: auto; display: flex; gap: .4rem; flex-wrap: wrap; }

    .chip { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 700;
        padding: .14rem .55rem; border-radius: 999px; white-space: nowrap; border: 0; }
    @foreach (['amber','red','green','blue','violet','slate'] as $c)
        .chip.is-{{ $c }} { color: var(--c-{{ $c }}); background: var(--c-{{ $c }}-bg); }
    @endforeach

    .ex-btn { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 600;
        padding: .42rem .85rem; border-radius: 9px; border: 1px solid var(--ex-border); background: var(--ex-card);
        color: var(--ex-text); text-decoration: none !important; cursor: pointer; }
    .ex-btn:hover { border-color: var(--ex-accent); color: var(--ex-accent); }
    .ex-btn.is-primary { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .ex-btn.is-primary:hover { background: #4338ca; color: #fff; }

    /* KPIs */
    .ex-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .7rem; margin: .9rem 0; }
    .ex-kpi { padding: .85rem 1rem; }
    .ex-kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--ex-muted); }
    .ex-kpi-val { font-size: 1.45rem; font-weight: 800; color: var(--ex-text); line-height: 1.2; margin-top: .15rem; }
    .ex-kpi-val small { font-size: .78rem; font-weight: 600; color: var(--ex-muted); }
    .ex-kpi-val.is-bad { color: var(--c-red); }
    .ex-kpi-sub { font-size: .7rem; color: var(--ex-muted); margin-top: .15rem; }
    .ex-bar { height: 7px; border-radius: 999px; background: rgba(100,116,139,.15); overflow: hidden; margin-top: .45rem; }
    .ex-bar > div { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #f59e0b, #d97706); }
    .ex-bar.is-full > div { background: linear-gradient(90deg, #ef4444, #b91c1c); }

    /* Alertas */
    .ex-alert { display: flex; align-items: flex-start; gap: .6rem; padding: .6rem .9rem; border-radius: 10px;
        font-size: .82rem; margin-bottom: .45rem; color: var(--ex-text); }
    .ex-alert i { margin-top: .15rem; }
    .ex-alert.is-danger { background: var(--c-red-bg); } .ex-alert.is-danger i { color: var(--c-red); }
    .ex-alert.is-warning { background: var(--c-amber-bg); } .ex-alert.is-warning i { color: var(--c-amber); }
    .ex-alert.is-info { background: var(--c-blue-bg); } .ex-alert.is-info i { color: var(--c-blue); }

    /* Pestañas */
    .ex-tabs { display: inline-flex; padding: 3px; border-radius: 10px; background: var(--ex-soft); border: 1px solid var(--ex-border); margin-bottom: .8rem; }
    .ex-tab { border: 0; background: transparent; color: var(--ex-muted); font-size: .82rem; font-weight: 600;
        padding: .4rem .95rem; border-radius: 7px; cursor: pointer; display: inline-flex; gap: .4rem; align-items: center; }
    .ex-tab.is-active { background: var(--ex-card); color: var(--ex-text); box-shadow: 0 1px 3px rgba(15,23,42,.12); }
    .dark-mode .ex-tab.is-active, .dim-mode .ex-tab.is-active { background: rgba(255,255,255,.1); box-shadow: none; }

    /* Línea de tiempo */
    .ex-tl { position: relative; padding: .5rem 1.25rem 1rem 1.25rem; }
    .ex-tl-item { position: relative; display: grid; grid-template-columns: 96px 34px 1fr; gap: .6rem; padding: .45rem 0; }
    .ex-tl-item::before { content: ""; position: absolute; left: calc(96px + .6rem + 16px); top: 0; bottom: 0; width: 2px; background: var(--ex-border); }
    .ex-tl-item:first-child::before { top: 1.1rem; }
    .ex-tl-item:last-child::before { bottom: calc(100% - 1.1rem); }
    .ex-tl-date { text-align: right; font-size: .74rem; font-weight: 700; color: var(--ex-muted); padding-top: .45rem; }
    .ex-tl-date.is-missing { color: var(--c-amber); font-style: italic; font-weight: 600; }
    .ex-tl-dot { position: relative; z-index: 1; width: 34px; height: 34px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; font-size: .78rem; border: 3px solid var(--ex-card); }
    @foreach (['amber','red','green','blue','violet','slate'] as $c)
        .ex-tl-dot.is-{{ $c }} { color: var(--c-{{ $c }}); background: var(--c-{{ $c }}-bg); }
    @endforeach
    .ex-tl-body { padding: .35rem .8rem .55rem; border-radius: 10px; }
    .ex-tl-item:hover .ex-tl-body { background: var(--ex-soft); }
    .ex-tl-title { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; font-size: .88rem; font-weight: 700; color: var(--ex-text); }
    .ex-tl-detail { font-size: .78rem; color: var(--ex-muted); margin-top: .15rem; }
    .ex-tl-detail strong { color: var(--ex-text); font-weight: 600; }
    .ex-tl-motivo { font-size: .8rem; color: var(--ex-text); margin-top: .3rem; padding: .35rem .6rem; border-left: 3px solid var(--ex-border); background: var(--ex-soft); border-radius: 0 6px 6px 0; }
    .ex-tl-actions { margin-left: auto; display: flex; gap: .2rem; opacity: 0; transition: opacity .15s; }
    .ex-tl-item:hover .ex-tl-actions, .ex-tl-actions:focus-within { opacity: 1; }
    .ex-icon-btn { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px;
        border: 1px solid var(--ex-border); background: var(--ex-card); color: var(--ex-muted); font-size: .72rem; cursor: pointer; }
    .ex-icon-btn:hover { color: var(--ex-accent); border-color: var(--ex-accent); }
    .ex-icon-btn.is-danger:hover { color: #dc2626; border-color: #dc2626; }
    .ex-link-fecha { border: 0; background: none; padding: 0; color: var(--c-amber); font-weight: 700; font-size: .74rem; text-decoration: underline dotted; cursor: pointer; }

    /* Notas */
    .ex-per { margin-bottom: .7rem; }
    .ex-per-head { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .9rem; padding: .8rem 1.1rem; cursor: pointer; border: 0; background: none; width: 100%; text-align: left; color: var(--ex-text); border-radius: 14px; }
    .ex-per-head:hover { background: var(--ex-soft); }
    .ex-per-title { font-size: .95rem; font-weight: 800; }
    .ex-per-stats { margin-left: auto; display: flex; gap: 1.1rem; font-size: .76rem; color: var(--ex-muted); }
    .ex-per-stats strong { color: var(--ex-text); font-size: .9rem; }
    .ex-per-stats .is-bad strong { color: var(--c-red); }
    .ex-chev { color: var(--ex-muted); font-size: .7rem; transition: transform .2s; }
    .ex-per.is-open .ex-chev { transform: rotate(90deg); }
    .ex-per-body { display: none; border-top: 1px solid var(--ex-border); padding: .3rem .6rem .6rem; }
    .ex-per.is-open .ex-per-body { display: block; }
    .ex-asig { margin: 0 0 .75rem; padding: .55rem .75rem; border: 1px dashed var(--ex-border, #e5e7eb); border-radius: .6rem; font-size: .82rem; }
    .ex-asig-t { font-weight: 700; display: flex; align-items: center; gap: .4rem; margin-bottom: .35rem; }
    .ex-asig-v { color: var(--ex-muted, #64748b); }
    .ex-asig-lista { display: flex; flex-wrap: wrap; gap: .3rem; }
    .ex-asig-c { display: inline-block; padding: .15rem .55rem; border-radius: 999px; background: var(--ex-soft, #f1f5f9); font-size: .78rem; }
    .ex-asig-c.is-extra { background: rgba(217, 119, 6, .12); color: #b45309; font-weight: 600; }
    .dark-mode .ex-asig-c.is-extra, .dim-mode .ex-asig-c.is-extra { color: #fbbf24; }
    .ex-asig-antiguas { padding: .6rem .9rem; font-size: .82rem; }
    .ex-asig-antiguas summary { cursor: pointer; font-weight: 700; }
    .ex-asig-antiguas summary span { font-weight: 400; color: var(--ex-muted, #64748b); }
    .ex-table { width: 100%; font-size: .82rem; border-collapse: collapse; }
    .ex-table th { font-size: .66rem; text-transform: uppercase; letter-spacing: .05em; color: var(--ex-muted); font-weight: 700; padding: .5rem .6rem; text-align: left; border-bottom: 1px solid var(--ex-border); }
    .ex-table td { padding: .5rem .6rem; color: var(--ex-text); border-bottom: 1px solid var(--ex-border); }
    .ex-table tr:last-child td { border-bottom: 0; }
    .ex-table tr.is-bad td { background: var(--c-red-bg); }
    .ex-nota { font-weight: 800; font-size: .9rem; }
    .ex-nota.is-bad { color: var(--c-red); }
    .ex-parcial { font-weight: 700; color: var(--ex-muted); }
    .ex-parcial.is-bad { color: var(--c-red); }
    .ex-empty { text-align: center; padding: 2.5rem 1rem; color: var(--ex-muted); font-size: .86rem; }
    .ex-count { font-size: .66rem; font-weight: 800; padding: .02rem .42rem; border-radius: 999px; background: #4f46e5; color: #fff; }
    .ex-count.is-muted { background: var(--ex-border); color: var(--ex-muted); }

    /* Seguimiento */
    .ex-acc { margin-bottom: .6rem; border-left: 4px solid #22c55e; }
    .ex-acc.is-closed { border-left-color: var(--ex-border); opacity: .85; }
    .ex-acc.is-late { border-left-color: #ef4444; }
    .ex-acc-head { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; padding: .7rem 1rem .2rem; }
    .ex-acc-fecha { font-size: .74rem; font-weight: 800; color: var(--ex-muted); }
    .ex-acc-tipo { font-size: .9rem; font-weight: 800; color: var(--ex-text); }
    .ex-acc-actions { margin-left: auto; display: flex; gap: .2rem; }
    .ex-acc-body { padding: .2rem 1rem .8rem; }
    .ex-acc-text { font-size: .84rem; color: var(--ex-text); white-space: pre-line; }
    .ex-acc-label { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--ex-muted); margin-top: .5rem; }
    .ex-subtitle { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--ex-muted); margin: 1.2rem 0 .5rem; }

    /* Documentos */
    .ex-docs { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: .6rem; }
    .ex-doc { display: flex; align-items: center; gap: .8rem; padding: .75rem .9rem; }
    .ex-doc-icon { width: 42px; height: 42px; flex-shrink: 0; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; text-decoration: none !important; }
    @foreach (['red','blue','green','slate'] as $c)
        .ex-doc-icon.is-{{ $c }} { color: var(--c-{{ $c }}); background: var(--c-{{ $c }}-bg); }
    @endforeach
    .ex-doc-info { flex: 1; min-width: 0; }
    .ex-doc-title { display: block; font-size: .86rem; font-weight: 700; color: var(--ex-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ex-doc-title:hover { color: var(--ex-accent); }

    /* Diálogos */
    .ex-fd-fondo { position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 1040; }
    .ex-fd { position: fixed; top: 0; right: 0; bottom: 0; width: min(1080px, 100vw); z-index: 1041; display: flex; flex-direction: column;
        background: var(--ex-card, #fff); box-shadow: -20px 0 50px rgba(15,23,42,.25); transform: translateX(100%); transition: transform .25s ease; }
    .dark-mode .ex-fd { background: #0d1117; } .dim-mode .ex-fd { background: var(--lm-bg, #241a24); }
    .ex-fd.is-open { transform: none; }
    .ex-fd-h { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; padding: .75rem 1rem; border-bottom: 1px solid var(--ex-border, #e5e7eb); }
    .ex-fd-h h3 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--ex-text, #1e293b); min-width: 0; }
    .ex-fd-h h3 i { color: var(--ex-accent, #4f46e5); margin-right: .35rem; }
    .ex-fd-acciones { display: flex; gap: .4rem; flex-wrap: wrap; }
    .ex-fd-carga { padding: 2.5rem; text-align: center; color: var(--ex-muted, #64748b); }
    .ex-fd iframe { flex: 1; width: 100%; border: 0; background: transparent; }
    @media (prefers-reduced-motion: reduce) { .ex-fd { transition: none; } }
    .ex-dialog { border: 0; border-radius: 14px; padding: 0; width: min(500px, calc(100vw - 32px)); box-shadow: 0 25px 60px rgba(15,23,42,.3); color: #1e293b; }
    .ex-dialog::backdrop { background: rgba(15,23,42,.5); }
    .ex-dialog-body { padding: 1.2rem 1.35rem .5rem; }
    .ex-dialog h5 { font-size: 1rem; font-weight: 800; margin-bottom: .8rem; }
    .ex-dialog label { font-size: .76rem; font-weight: 700; color: #475569; margin-bottom: .2rem; }
    .ex-dialog-foot { display: flex; justify-content: flex-end; gap: .5rem; padding: .7rem 1.35rem 1.1rem; }
    .dark-mode .ex-dialog, .dim-mode .ex-dialog { background: #1f2937; color: #e2e8f0; }
    .dark-mode .ex-dialog label, .dim-mode .ex-dialog label { color: #cbd5e1; }

    @media (max-width: 1199.98px) { .ex-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) {
        .ex-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ex-head-actions { margin-left: 0; width: 100%; }
        .ex-tl { padding-left: .5rem; padding-right: .5rem; }
        .ex-tl-item { grid-template-columns: 30px 1fr; }
        .ex-tl-item::before { left: 15px; }
        .ex-tl-date { display: none; }
        .ex-tl-mobile-date { display: block !important; }
        .ex-tl-actions { opacity: 1; }
        .ex-per-stats { margin-left: 0; width: 100%; }
    }
    .ex-tl-mobile-date { display: none; font-size: .72rem; color: var(--ex-muted); }
</style>

<div class="container-fluid py-3 ex">
    @if ($puedeGestionar)
        <a href="{{ route('admin') }}#tab={{ $esPpd ? 'ppd' : ($u->hasRole('inhabilitado') ? 'inhabilitados' : 'fid') }}" class="ex-back">
            <i class="fas fa-arrow-left fa-xs mr-1"></i> Volver a usuarios
        </a>
    @elseif (Route::has('titulacion.index'))
        <a href="{{ route('titulacion.index') }}" class="ex-back"><i class="fas fa-arrow-left fa-xs mr-1"></i> Volver a Titulación</a>
    @endif

    @if (session('success'))
        <div class="alert alert-success mt-2 mb-0 py-2"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-2 mb-0 py-2"><i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mt-2 mb-0 py-2">{{ $errors->first() }}</div>
    @endif

    {{-- ── Cabecera ── --}}
    <div class="ex-card ex-head mt-2">
        @if ($u->foto)
            @include('partials.foto-visor')
            <button type="button" data-foto="{{ asset('img/estudiantes/' . $u->foto) }}" data-foto-titulo="{{ $u->apellidos }}, {{ $u->name }}"
                data-foto-sub="DNI {{ $u->dni ?: '—' }}" title="Ver foto">
                <img src="{{ asset('img/estudiantes/' . $u->foto) }}" alt="Foto de {{ $u->name }} {{ $u->apellidos }}" class="ex-avatar">
            </button>
        @else
            <div class="ex-avatar">{{ $iniciales }}</div>
        @endif
        <div style="min-width:0;">
            <div class="ex-name">{{ $u->apellidos }}, {{ $u->name }}</div>
            <div class="ex-meta">
                <span><i class="fas fa-id-card fa-xs"></i> DNI {{ $u->dni ?: '—' }}</span>
                <span><i class="fas fa-envelope fa-xs"></i> {{ $u->email }}</span>
                <span><i class="fas fa-graduation-cap fa-xs"></i> {{ $u->programa->nombre ?? 'Sin programa' }} · {{ $ciclo($u->ciclo) }}</span>
                @if ($ficha)
                    <span>N° {{ $ficha->numero ?? '—' }} · Ref {{ $ficha->numero_referencia ?? '—' }}</span>
                @endif
            </div>
            <div class="ex-chips">
                <span class="chip is-blue">{{ $esPpd ? 'PPD' : 'FID' }}</span>
                @if ($u->hasRole('inhabilitado'))
                    <span class="chip is-red"><i class="fas fa-ban"></i> Inhabilitado{{ $u->perfil ? ' · ' . $u->perfil : '' }}</span>
                @elseif ($u->perfil)
                    <span class="chip {{ $u->perfil === 'Estudiante' ? 'is-green' : 'is-amber' }}">{{ $u->perfil }}</span>
                @endif
                @if ($u->condicion)
                    <span class="chip {{ $u->condicion === 'Egresado' ? 'is-violet' : 'is-slate' }}">{{ $u->condicion }}</span>
                @endif
                @if ($u->beca == 1)
                    <span class="chip is-green"><i class="fas fa-award"></i> Beca</span>
                @endif
            </div>
        </div>
        @if ($puedeGestionar)
        @php
            // Ficha de matrícula (datos personales, educativos, laborales, salud, vivienda…):
            // FID en alumnos.show, PPD en ppd.show. Se abre en un panel, como en el Directorio.
            $fichaVer = $u->alumno ? route('alumnos.show', ['alumno' => $u->alumno->id]) : ($u->alumnoB ? route('ppd.show', $u->alumnoB->id) : null);
            $fichaEditar = $u->alumno ? route('alumnos.edit', ['alumno' => $u->alumno->id]) : ($u->alumnoB ? route('ppd.edit', $u->alumnoB->id) : null);
        @endphp
        <div class="ex-head-actions">
            @if ($fichaVer)
                <a href="{{ $fichaVer }}" class="ex-btn js-ficha" data-editar="{{ $fichaEditar }}" title="Datos personales, educativos, de trabajo, salud y vivienda">
                    <i class="fas fa-id-card-alt fa-xs"></i> Ficha completa
                </a>
            @endif
            <a href="{{ route('adminEdit', ['id' => $u->id]) }}" class="ex-btn"><i class="fas fa-pen fa-xs"></i> Editar datos</a>
            <button type="button" class="ex-btn" data-abrir="dlgDocumento"><i class="fas fa-paperclip fa-xs"></i> Subir documento</button>
            <button type="button" class="ex-btn" data-abrir="dlgAccion"><i class="fas fa-hands-helping fa-xs"></i> Registrar seguimiento</button>
            <button type="button" class="ex-btn is-primary" data-abrir="dlgNuevo"><i class="fas fa-plus fa-xs"></i> Registrar evento</button>
        </div>
        @elseif ($titulacion && $titulacion['url'])
        <div class="ex-head-actions">
            <a href="{{ $titulacion['url'] }}" class="ex-btn is-primary"><i class="fas fa-graduation-cap fa-xs"></i> Ficha de titulación</a>
        </div>
        @endif
    </div>

    {{-- ── Indicadores ── --}}
    <div class="ex-kpis">
        @if ($cursosPpd->isNotEmpty())
        {{-- PPD (programa de un año): avance del plan y promedio por ciclo, contando también las notas del año en curso --}}
        @php
            $todosPpd = $cursosPpd->flatMap(fn ($b) => $b->cursos);
            $conNotaPpd = $todosPpd->filter(fn ($c) => $c->nota !== null);
            $desapPpd = $todosPpd->where('desaprobado', true)->count();
            $pctPlan = $todosPpd->count() ? round($conNotaPpd->count() / $todosPpd->count() * 100) : 0;
        @endphp
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Avance del plan</div>
            <div class="ex-kpi-val">{{ $conNotaPpd->count() }} <small>/ {{ $todosPpd->count() }} cursos</small></div>
            <div class="ex-bar"><div style="width: {{ $pctPlan }}%;"></div></div>
            <div class="ex-kpi-sub">{{ $pctPlan }}% con nota</div>
        </div>
        @foreach ($cursosPpd->take(2) as $bloque)
            @php $notasCiclo = $bloque->cursos->pluck('nota')->filter(fn ($n) => $n !== null); $promCiclo = $notasCiclo->isNotEmpty() ? $notasCiclo->avg() : null; @endphp
            <div class="ex-card ex-kpi">
                <div class="ex-kpi-label">Promedio {{ $ciclo($bloque->ciclo) }}</div>
                <div class="ex-kpi-val {{ $promCiclo !== null && $promCiclo < 11 ? 'is-bad' : '' }}">{{ $promCiclo !== null ? number_format($promCiclo, 2) : '—' }}</div>
                <div class="ex-kpi-sub">{{ $notasCiclo->count() }} de {{ $bloque->cursos->count() }} cursos con nota</div>
            </div>
        @endforeach
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Desaprobados</div>
            <div class="ex-kpi-val {{ $desapPpd ? 'is-bad' : '' }}">{{ $desapPpd }}</div>
            <div class="ex-kpi-sub">nota menor a 11 · incluye el año en curso</div>
        </div>
        @else
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Promedio acumulado</div>
            <div class="ex-kpi-val {{ $r['promedio'] !== null && $r['promedio'] < 11 ? 'is-bad' : '' }}">{{ $r['promedio'] !== null ? number_format($r['promedio'], 2) : '—' }}</div>
            <div class="ex-kpi-sub">{{ $r['periodos'] }} {{ $r['periodos'] === 1 ? 'periodo' : 'periodos' }} con registro</div>
        </div>
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Cursos llevados</div>
            <div class="ex-kpi-val">{{ $r['cursos'] }}</div>
            <div class="ex-kpi-sub">desde 2024-II</div>
        </div>
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Desaprobados</div>
            <div class="ex-kpi-val {{ $r['desaprobados'] ? 'is-bad' : '' }}">{{ $r['desaprobados'] }}</div>
            <div class="ex-kpi-sub">nota menor a 11</div>
        </div>
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Repitencia</div>
            <div class="ex-kpi-val {{ $r['repitencias']->isNotEmpty() ? 'is-bad' : '' }}">{{ $r['repitencias']->count() }}</div>
            <div class="ex-kpi-sub">{{ $r['repitencias']->isNotEmpty() ? $r['repitencias']->map(fn ($p) => $ciclo($p->ciclo))->unique()->implode(', ') : 'ningún ciclo repetido' }}</div>
        </div>
        @endif
        <div class="ex-card ex-kpi">
            <div class="ex-kpi-label">Licencia acumulada</div>
            <div class="ex-kpi-val {{ $licencia['agotada'] ? 'is-bad' : '' }}">{{ $licencia['dias'] }} <small>/ {{ $licencia['limite'] }} días</small></div>
            <div class="ex-bar {{ $licencia['agotada'] ? 'is-full' : '' }}"><div style="width: {{ $pctLicencia }}%;"></div></div>
            <div class="ex-kpi-sub">
                @if ($licencia['agotada'])
                    Límite alcanzado
                @else
                    Le quedan {{ $licencia['restante'] }} días
                @endif
                @if ($licencia['sin_fecha'])
                    · {{ $licencia['sin_fecha'] }} sin fecha
                @endif
            </div>
        </div>
    </div>

    @foreach ($alertas as [$tipo, $icono, $texto])
        <div class="ex-alert is-{{ $tipo }}"><i class="fas {{ $icono }}"></i> <span>{{ $texto }}</span></div>
    @endforeach

    {{-- ── Pestañas ── --}}
    <div class="mt-3">
        <div class="ex-tabs" role="tablist">
            <button type="button" class="ex-tab is-active" data-tab="trayectoria" role="tab"><i class="fas fa-stream fa-xs"></i> Trayectoria</button>
            <button type="button" class="ex-tab" data-tab="notas" role="tab"><i class="fas fa-book-open fa-xs"></i> {{ $cursosPpd->isNotEmpty() ? 'Cursos por ciclo' : 'Notas por periodo' }}</button>
            @if ($puedeGestionar)
            <button type="button" class="ex-tab" data-tab="seguimiento" role="tab">
                <i class="fas fa-hands-helping fa-xs"></i> Seguimiento
                @php $abiertas = $acciones->where('estado', 'abierto')->count(); @endphp
                @if ($abiertas)<span class="ex-count">{{ $abiertas }}</span>@endif
            </button>
            <button type="button" class="ex-tab" data-tab="documentos" role="tab">
                <i class="fas fa-folder fa-xs"></i> Documentos
                @if ($documentos->isNotEmpty())<span class="ex-count is-muted">{{ $documentos->count() }}</span>@endif
            </button>
            @endif
            @if ($titulacion)
            <button type="button" class="ex-tab" data-tab="titulacion" role="tab"><i class="fas fa-graduation-cap fa-xs"></i> Titulación</button>
            @endif
        </div>

        {{-- Trayectoria --}}
        <section data-pane="trayectoria" class="ex-card">
            @if ($hitos->isEmpty())
                <div class="ex-empty">
                    <i class="fas fa-stream d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
                    Aún no hay eventos en su trayectoria. Se registran solos al cambiar su perfil, condición o ciclo,
                    o puedes registrarlos con <strong>Registrar evento</strong>.
                </div>
            @else
                <div class="ex-tl">
                    @foreach ($hitos as $h)
                        @if ($h->clase === 'matricula')
                            @php $p = $h->periodo; @endphp
                            <div class="ex-tl-item">
                                <div class="ex-tl-date">{{ $p->periodo }}</div>
                                <div class="ex-tl-dot is-blue"><i class="fas fa-clipboard-check"></i></div>
                                <div class="ex-tl-body">
                                    <div class="ex-tl-mobile-date">{{ $p->periodo }}</div>
                                    <div class="ex-tl-title">
                                        Matrícula {{ $p->periodo }}
                                        <span class="chip is-blue">{{ $ciclo($p->ciclo) }}</span>
                                        @if ($p->repite)
                                            <span class="chip is-red"><i class="fas fa-redo"></i> Repite ciclo</span>
                                        @endif
                                    </div>
                                    <div class="ex-tl-detail">
                                        {{ $p->ciclo->programa->nombre ?? '' }}
                                        @if ($p->condicion) · condición <strong>{{ $p->condicion }}</strong> @endif
                                        {{-- PPD: cursos y notas ya están en «Cursos por ciclo»; aquí solo el hecho de la matrícula --}}
                                        @if ($p->cursos->isNotEmpty() && $cursosPpd->isEmpty())
                                            · {{ $p->cursos->count() }} cursos · promedio <strong>{{ $p->promedio ?? '—' }}</strong>
                                            @if ($p->desaprobados) · <strong style="color:var(--c-red)">{{ $p->desaprobados }} desaprobado(s)</strong> @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $e = $h->evento;
                                [$color, $icono] = $estilo[$e->tipo] ?? ['slate', 'fa-circle'];
                                $periodoNombre = $e->periodoActual->nombre ?? $e->periodoActualPpd->nombre ?? null;
                                $jsonEvento = json_encode([
                                    'id' => $e->id, 'etiqueta' => $e->etiqueta,
                                    'fecha_inicio' => $e->fecha_inicio?->format('Y-m-d'), 'fecha_fin' => $e->fecha_fin?->format('Y-m-d'),
                                    'motivo' => $e->motivo, 'resolucion' => $e->resolucion,
                                ]);
                            @endphp
                            <div class="ex-tl-item">
                                <div class="ex-tl-date {{ $e->fecha_inicio ? '' : 'is-missing' }}">
                                    @if ($e->fecha_inicio)
                                        {{ $e->fecha_inicio->format('d/m/Y') }}
                                    @elseif ($puedeGestionar)
                                        <button type="button" class="ex-link-fecha js-editar" data-evento="{{ $jsonEvento }}">Sin fecha</button>
                                    @else
                                        Sin fecha
                                    @endif
                                </div>
                                <div class="ex-tl-dot is-{{ $color }}"><i class="fas {{ $icono }}"></i></div>
                                <div class="ex-tl-body">
                                    <div class="ex-tl-mobile-date">{{ $e->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}</div>
                                    <div class="ex-tl-title">
                                        {{ $e->etiqueta }}
                                        @if ($e->tipo === 'licencia')
                                            @if ($e->fecha_fin)
                                                <span class="chip is-slate">hasta {{ $e->fecha_fin->format('d/m/Y') }}
                                                    @if ($e->fecha_inicio) · {{ $e->fecha_inicio->diffInDays($e->fecha_fin) }} días @endif</span>
                                            @else
                                                <span class="chip is-amber"><i class="fas fa-circle" style="font-size:.45rem;"></i> En curso</span>
                                            @endif
                                        @endif
                                        @if ($e->origen === 'migracion')
                                            <span class="chip is-slate" title="Estado que ya tenía cuando empezó el registro de trayectoria (01/10/2026)">registro inicial</span>
                                        @elseif ($e->origen === 'manual')
                                            <span class="chip is-violet">registrado a mano</span>
                                        @endif
                                        @if ($puedeGestionar)
                                        <div class="ex-tl-actions">
                                            <button type="button" class="ex-icon-btn js-editar" title="Editar fechas y detalle"
                                                data-evento="{{ $jsonEvento }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            @if ($e->origen === 'manual')
                                                <button type="button" class="ex-icon-btn is-danger js-borrar" title="Eliminar"
                                                    data-url="{{ route('expediente.eventos.destroy', $e) }}" data-etiqueta="{{ $e->etiqueta }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ex-tl-detail">
                                        @if ($e->valor_anterior || $e->valor_nuevo)
                                            {{ $e->valor_anterior ?: '—' }} <i class="fas fa-long-arrow-alt-right fa-xs mx-1"></i> <strong>{{ $e->valor_nuevo ?: '—' }}</strong> ·
                                        @endif
                                        @if ($periodoNombre) periodo {{ $periodoNombre }} · @endif
                                        @if ($e->resolucion) <i class="fas fa-file-signature fa-xs"></i> {{ $e->resolucion }} · @endif
                                        {{ $e->registradoPor ? 'por ' . trim($e->registradoPor->name . ' ' . $e->registradoPor->apellidos) : 'por el sistema' }}
                                    </div>
                                    @if ($e->motivo)
                                        <div class="ex-tl-motivo">{{ $e->motivo }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Notas por periodo --}}
        <section data-pane="notas" hidden>
            @if ($cursosPpd->isNotEmpty())
            {{-- PPD: el programa dura un año → plan completo del programa por ciclo, en orden alfabético --}}
            @php $fmtNota = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
            @foreach ($cursosPpd as $bloque)
                @php
                    $conNota = $bloque->cursos->pluck('nota')->filter(fn ($n) => $n !== null);
                    $desap = $bloque->cursos->where('desaprobado', true)->count();
                @endphp
                <div class="ex-card ex-per is-open">
                    <button type="button" class="ex-per-head">
                        <i class="fas fa-chevron-right ex-chev"></i>
                        <span class="ex-per-title">{{ $ciclo($bloque->ciclo) }}</span>
                        @if ($u->ciclo_id === $bloque->ciclo->id)
                            <span class="chip is-blue">ciclo actual</span>
                        @endif
                        <span class="ex-per-stats">
                            <span>Cursos <strong>{{ $bloque->cursos->count() }}</strong></span>
                            <span>Con nota <strong>{{ $conNota->count() }}</strong></span>
                            <span>Promedio <strong>{{ $conNota->isNotEmpty() ? number_format($conNota->avg(), 2) : '—' }}</strong></span>
                            <span class="{{ $desap ? 'is-bad' : '' }}">Desaprobados <strong>{{ $desap }}</strong></span>
                        </span>
                    </button>
                    <div class="ex-per-body">
                        @if ($bloque->cursos->isEmpty())
                            <div class="ex-empty" style="padding:1.2rem;">Este ciclo no tiene cursos registrados.</div>
                        @else
                            <div class="table-responsive">
                                <table class="ex-table">
                                    <thead><tr>
                                        <th style="width:2.5rem;">#</th><th>Curso</th><th>Docente</th>
                                        <th>Logro</th><th class="text-right">Nota</th>
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($bloque->cursos as $c)
                                            <tr class="{{ $c->desaprobado && ! $c->en_curso ? 'is-bad' : '' }}">
                                                <td class="text-muted">{{ $loop->iteration }}</td>
                                                <td>{{ $c->curso->nombre }}</td>
                                                <td>{{ $c->docentes ?: '—' }}</td>
                                                <td>{{ $c->logro ?? '—' }}</td>
                                                <td class="text-right">
                                                    @if ($c->nota !== null)
                                                        <span class="ex-nota {{ $c->desaprobado ? 'is-bad' : '' }}">{{ $fmtNota($c->nota) }}</span>
                                                        @if ($c->periodo)<div class="text-muted" style="font-size:.7rem;">{{ $c->periodo }}</div>@endif
                                                    @else
                                                        <span class="chip is-slate">{{ $c->en_curso ? 'pendiente' : 'sin nota' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
            @else
            @forelse ($historial['periodos']->reverse() as $i => $p)
                <div class="ex-card ex-per {{ $loop->first ? 'is-open' : '' }}">
                    <button type="button" class="ex-per-head">
                        <i class="fas fa-chevron-right ex-chev"></i>
                        <span class="ex-per-title">{{ $p->periodo }}</span>
                        <span class="chip is-blue">{{ $p->modalidad }} · {{ $ciclo($p->ciclo) }}</span>
                        @if ($p->ciclo_fuente === 'notas')
                            <span class="chip is-slate" title="No hay matrícula registrada de ese periodo: el ciclo se deduce de los cursos que llevó">ciclo deducido</span>
                        @endif
                        @if ($p->repite)
                            <span class="chip is-red"><i class="fas fa-redo"></i> Repite ciclo</span>
                        @endif
                        @if ($p->en_curso)
                            <span class="chip is-amber" title="Notas que los docentes están registrando ahora; aún no son oficiales"><i class="fas fa-circle" style="font-size:.45rem;"></i> En curso</span>
                        @endif
                        <span class="ex-per-stats">
                            <span>Cursos <strong>{{ $p->cursos->count() }}</strong></span>
                            <span>Promedio <strong>{{ $p->promedio !== null ? number_format($p->promedio, 2) : '—' }}</strong></span>
                            <span class="{{ $p->desaprobados ? 'is-bad' : '' }}">Desaprobados <strong>{{ $p->desaprobados }}</strong></span>
                        </span>
                    </button>
                    <div class="ex-per-body">
                        {{-- Cursos asignados en este período ("Asignar cursos"): de otro ciclo = curso extra. --}}
                        @if ($p->modalidad === 'FID')
                            @php
                                $asig = $asignaciones[$p->periodo_id] ?? collect();
                                $esActual = $periodoActualFid && (int) $periodoActualFid->id === (int) $p->periodo_id;
                            @endphp
                            @if ($asig->isNotEmpty() || $esActual)
                                <div class="ex-asig">
                                    <div class="ex-asig-t">
                                        <i class="fas fa-clipboard-list fa-xs"></i> Cursos asignados en este período
                                        @if ($esActual) <span class="chip is-blue">período actual</span> @endif
                                    </div>
                                    @if ($asig->isEmpty())
                                        <div class="ex-asig-v">Sin asignación propia: lleva los cursos de su ciclo.</div>
                                    @else
                                        <div class="ex-asig-lista">
                                            @foreach ($asig as $cursoAsig)
                                                @php $extra = $p->ciclo && $cursoAsig->ciclo_id !== $p->ciclo->id; @endphp
                                                <span class="ex-asig-c {{ $extra ? 'is-extra' : '' }}" title="{{ $extra ? 'Curso de otro ciclo (extra)' : 'Curso de su ciclo' }}">
                                                    {{ $cursoAsig->nombre }}
                                                    @if ($extra) <small>· extra, {{ $ciclo($cursoAsig->ciclo) }}</small> @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                        @if ($p->cursos->isEmpty())
                            <div class="ex-empty" style="padding:1.2rem;">Aún no hay notas finales archivadas de este periodo.</div>
                        @else
                            <div class="table-responsive">
                                <table class="ex-table">
                                    @php $fmtNota = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
                                    <thead><tr>
                                        <th>Curso</th><th>Ciclo del curso</th>
                                        @if ($p->tiene_parciales)<th class="text-center">Parcial 1</th><th class="text-center">Parcial 2</th>@endif
                                        <th>Logro</th><th class="text-right">{{ $p->en_curso ? 'Desempeño' : 'Nota final' }}</th>
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($p->cursos as $c)
                                            <tr class="{{ $c->desaprobado && ! $c->en_curso ? 'is-bad' : '' }}">
                                                <td>{{ $c->curso->nombre ?? 'Curso eliminado' }}</td>
                                                <td>{{ $ciclo($c->curso?->ciclo) }}</td>
                                                @if ($p->tiene_parciales)
                                                    @foreach ([$c->p1, $c->p2] as $parcial)
                                                        <td class="text-center">
                                                            @if ($parcial !== null)
                                                                <span class="ex-parcial {{ $parcial < 11 ? 'is-bad' : '' }}">{{ $fmtNota($parcial) }}</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                @endif
                                                <td>{{ $c->logro ?? '—' }}</td>
                                                <td class="text-right">
                                                    @if ($c->nota !== null)
                                                        <span class="ex-nota {{ $c->desaprobado ? 'is-bad' : '' }}">{{ $fmtNota($c->nota) }}</span>
                                                    @else
                                                        <span class="chip is-slate">{{ $c->en_curso ? 'pendiente' : 'sin calificar' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="ex-card ex-empty">No hay notas finales archivadas para este estudiante (el registro empieza en 2024-II).</div>
            @endforelse
            @if (($asignaciones['sin'] ?? collect())->isNotEmpty())
                <details class="ex-card ex-asig-antiguas">
                    <summary><i class="fas fa-archive fa-xs"></i> {{ $asignaciones['sin']->count() }} asignación(es) antigua(s) sin período <span>· ya no hacen que figure en esos cursos</span></summary>
                    <div class="ex-asig-lista mt-2">
                        @foreach ($asignaciones['sin'] as $cursoAsig)
                            <span class="ex-asig-c">{{ $cursoAsig->nombre }} <small>· {{ $ciclo($cursoAsig->ciclo) }}</small></span>
                        @endforeach
                    </div>
                </details>
            @endif
            @endif
        </section>

        @if ($puedeGestionar)
        {{-- Seguimiento --}}
        <section data-pane="seguimiento" hidden>
            @forelse ($acciones as $a)
                @php
                    $jsonAccion = json_encode([
                        'id' => $a->id, 'tipo' => $a->tipo, 'fecha' => $a->fecha?->format('Y-m-d'),
                        'responsable_id' => $a->responsable_id, 'resumen' => $a->resumen, 'acuerdos' => $a->acuerdos,
                        'proxima_revision' => $a->proxima_revision?->format('Y-m-d'), 'confidencial' => $a->confidencial,
                        'estado' => $a->estado,
                    ]);
                @endphp
                <div class="ex-card ex-acc {{ $a->estado === 'cerrado' ? 'is-closed' : '' }} {{ $a->vencida ? 'is-late' : '' }}">
                    <div class="ex-acc-head">
                        <span class="ex-acc-fecha">{{ $a->fecha->format('d/m/Y') }}</span>
                        <span class="ex-acc-tipo">{{ $a->etiqueta }}</span>
                        @if ($a->confidencial)
                            <span class="chip is-red" title="Información sensible"><i class="fas fa-lock"></i> Confidencial</span>
                        @endif
                        <span class="chip {{ $a->estado === 'cerrado' ? 'is-slate' : 'is-green' }}">{{ $a->estado === 'cerrado' ? 'Cerrado' : 'Abierto' }}</span>
                        @if ($a->proxima_revision && $a->estado === 'abierto')
                            <span class="chip {{ $a->vencida ? 'is-red' : 'is-amber' }}">
                                <i class="fas fa-calendar-check"></i> {{ $a->vencida ? 'Revisión vencida' : 'Revisar' }} {{ $a->proxima_revision->format('d/m/Y') }}
                            </span>
                        @endif
                        <div class="ex-acc-actions">
                            <form method="POST" action="{{ route('expediente.acciones.toggle', $a) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="ex-icon-btn" title="{{ $a->estado === 'abierto' ? 'Cerrar' : 'Reabrir' }}">
                                    <i class="fas {{ $a->estado === 'abierto' ? 'fa-check' : 'fa-undo' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="ex-icon-btn js-editar-accion" title="Editar" data-accion="{{ $jsonAccion }}"><i class="fas fa-pen"></i></button>
                            <button type="button" class="ex-icon-btn is-danger js-borrar" title="Eliminar"
                                data-url="{{ route('expediente.acciones.destroy', $a) }}" data-etiqueta="{{ $a->etiqueta }} del {{ $a->fecha->format('d/m/Y') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="ex-acc-body">
                        <div class="ex-acc-text">{{ $a->resumen }}</div>
                        @if ($a->acuerdos)
                            <div class="ex-acc-label">Acuerdos / compromisos</div>
                            <div class="ex-acc-text">{{ $a->acuerdos }}</div>
                        @endif
                        <div class="ex-tl-detail mt-2">
                            @if ($a->responsable) Responsable: <strong>{{ trim($a->responsable->name . ' ' . $a->responsable->apellidos) }}</strong> · @endif
                            Registrado por {{ $a->registradoPor ? trim($a->registradoPor->name . ' ' . $a->registradoPor->apellidos) : '—' }}
                            @foreach ($a->documentos as $d)
                                · <a href="{{ route('expediente.documentos.ver', $d) }}" target="_blank"><i class="fas fa-paperclip fa-xs"></i> {{ $d->titulo }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="ex-card ex-empty">
                    <i class="fas fa-hands-helping d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
                    Sin acciones de seguimiento. Registra entrevistas, llamadas, derivaciones o compromisos con
                    <strong>Registrar seguimiento</strong>.
                </div>
            @endforelse

            @if ($incidencias->isNotEmpty() || $tutorias->isNotEmpty())
                <div class="ex-subtitle">Reportes del sistema</div>
                @foreach ($incidencias as $inc)
                    <div class="ex-card ex-acc">
                        <div class="ex-acc-head">
                            <span class="ex-acc-fecha">{{ \Carbon\Carbon::parse($inc->fecha ?? $inc->created_at)->format('d/m/Y') }}</span>
                            {{-- Las incidencias son reportes informativos del docente: no esperan atención, sin estado. --}}
                            <span class="ex-acc-tipo">Incidencia reportada por {{ $inc->docente?->nombre ?: ($inc->nombre_docente ?: 'un docente') }}</span>
                        </div>
                        <div class="ex-acc-body"><div class="ex-acc-text">{{ $inc->reporte }}</div>
                            @if ($inc->notas_atencion)<div class="ex-acc-label">Atención</div><div class="ex-acc-text">{{ $inc->notas_atencion }}</div>@endif
                        </div>
                    </div>
                @endforeach
                @foreach ($tutorias as $tut)
                    <div class="ex-card ex-acc">
                        <div class="ex-acc-head">
                            <span class="ex-acc-fecha">{{ $tut->created_at?->format('d/m/Y') }}</span>
                            <span class="ex-acc-tipo">Solicitud de tutoría</span>
                            @if ($tut->urgencia)<span class="chip is-amber">{{ $tut->urgencia_texto }}{{ $tut->pideOtroDocente() && $tut->docente_solicitado_nombre ? ': '.$tut->docente_solicitado_nombre : '' }}</span>@endif
                            @if ($tut->estado === 'atendida')
                                <span class="chip is-green"><i class="fas fa-check"></i> Atendida{{ $tut->atendido_at ? ' el '.$tut->atendido_at->format('d/m/Y') : '' }}</span>
                            @else
                                <span class="chip is-amber">Pendiente de atender</span>
                            @endif
                        </div>
                        <div class="ex-acc-body"><div class="ex-acc-text">{{ $tut->motivo ?: 'Sin mensaje adicional.' }}</div>
                            @if ($tut->notas_atencion)<div class="ex-acc-label">Atención</div><div class="ex-acc-text">{{ $tut->notas_atencion }}</div>@endif
                        </div>
                    </div>
                @endforeach
            @endif
        </section>

        {{-- Documentos --}}
        <section data-pane="documentos" hidden>
            @if ($documentos->isEmpty())
                <div class="ex-card ex-empty">
                    <i class="fas fa-folder-open d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
                    Sin documentos. Sube resoluciones, solicitudes de licencia o constancias con <strong>Subir documento</strong>.
                </div>
            @else
                <div class="ex-docs">
                    @foreach ($documentos as $d)
                        @php
                            $ext = strtolower(pathinfo($d->nombre_original, PATHINFO_EXTENSION));
                            $icono = match (true) {
                                $ext === 'pdf' => ['fa-file-pdf', 'red'],
                                $d->es_imagen => ['fa-file-image', 'blue'],
                                in_array($ext, ['doc', 'docx'], true) => ['fa-file-word', 'blue'],
                                in_array($ext, ['xls', 'xlsx'], true) => ['fa-file-excel', 'green'],
                                default => ['fa-file', 'slate'],
                            };
                        @endphp
                        <div class="ex-card ex-doc">
                            <a href="{{ route('expediente.documentos.ver', $d) }}" target="_blank" class="ex-doc-icon is-{{ $icono[1] }}"><i class="fas {{ $icono[0] }}"></i></a>
                            <div class="ex-doc-info">
                                <a href="{{ route('expediente.documentos.ver', $d) }}" target="_blank" class="ex-doc-title">{{ $d->titulo }}</a>
                                <div class="ex-tl-detail">
                                    {{ $d->nombre_original }} · {{ $d->tamano_legible }} · {{ $d->created_at->format('d/m/Y') }}
                                    @if ($d->evento) · <span class="chip is-slate">{{ $d->evento->etiqueta }}{{ $d->evento->fecha_inicio ? ' ' . $d->evento->fecha_inicio->format('d/m/Y') : '' }}</span> @endif
                                    @if ($d->accion) · <span class="chip is-slate">{{ $d->accion->etiqueta }} {{ $d->accion->fecha->format('d/m/Y') }}</span> @endif
                                </div>
                            </div>
                            <button type="button" class="ex-icon-btn is-danger js-borrar" title="Eliminar"
                                data-url="{{ route('expediente.documentos.destroy', $d) }}" data-etiqueta="{{ $d->titulo }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
        @endif

        @if ($titulacion)
            @include('admin.expediente._titulacion', ['t' => $titulacion])
        @endif
    </div>
</div>

@if ($puedeGestionar)
{{-- ── Diálogos ── --}}
{{-- Panel lateral con la ficha de matrícula (misma mecánica que el panel del Directorio). --}}
<div class="ex-fd-fondo" id="exFichaFondo" hidden></div>
<aside class="ex-fd" id="exFicha" aria-hidden="true" aria-labelledby="exFichaTitulo">
    <header class="ex-fd-h">
        <h3 id="exFichaTitulo"><i class="fas fa-id-card-alt"></i> Ficha de matrícula · {{ trim(($u->apellidos ?? '').', '.($u->name ?? ''), ', ') }}</h3>
        <div class="ex-fd-acciones">
            <button type="button" class="ex-btn" id="exFichaVer" hidden><i class="fas fa-eye fa-xs"></i> Ver ficha</button>
            <button type="button" class="ex-btn" id="exFichaEditar"><i class="fas fa-edit fa-xs"></i> Editar ficha</button>
            <a href="#" class="ex-btn" id="exFichaPestana" target="_blank" rel="noopener" title="Abrir en una pestaña nueva"><i class="fas fa-external-link-alt fa-xs"></i></a>
            <button type="button" class="ex-btn" id="exFichaCerrar" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
    </header>
    <div class="ex-fd-carga" id="exFichaCarga"><i class="fas fa-circle-notch fa-spin"></i> Cargando…</div>
    <iframe id="exFichaFrame" title="Ficha de matrícula"></iframe>
</aside>

<dialog class="ex-dialog" id="dlgNuevo">
    <form method="POST" action="{{ route('expediente.eventos.store', $u) }}">
        @csrf
        <input type="hidden" name="_form" value="dlgNuevo">
        <div class="ex-dialog-body">
            <h5><i class="fas fa-plus-circle text-primary mr-1"></i> Registrar evento en la trayectoria</h5>
            <p class="small text-muted mb-3">
                Para hechos que el sistema no detecta solo: una sanción, una observación, una licencia o retiro antiguo…
                Registrar aquí <strong>no cambia</strong> el perfil del alumno; para eso usa <em>Editar datos</em>.
            </p>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo" class="form-control form-control-sm" required>
                    @foreach ($tiposManuales as $k => $v)
                        <option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Fecha</label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}" value="{{ old('fecha_inicio') }}" required>
                </div>
                <div class="form-group col-6">
                    <label>Fecha de fin <span class="text-muted font-weight-normal">(licencias)</span></label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}" value="{{ old('fecha_fin') }}">
                </div>
            </div>
            <div class="form-group">
                <label>N° de resolución / documento</label>
                <input type="text" name="resolucion" class="form-control form-control-sm" maxlength="255" value="{{ old('resolucion') }}" placeholder="Ej. RD N° 045-2026-EESPP">
            </div>
            <div class="form-group mb-0">
                <label>Detalle</label>
                <textarea name="motivo" class="form-control form-control-sm" rows="3" maxlength="2000">{{ old('motivo') }}</textarea>
            </div>
        </div>
        <div class="ex-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Registrar</button>
        </div>
    </form>
</dialog>

<dialog class="ex-dialog" id="dlgEditar">
    <form method="POST" id="formEditar">
        @csrf
        @method('PUT')
        <div class="ex-dialog-body">
            <h5><i class="fas fa-pen text-primary mr-1"></i> Editar: <span id="editEtiqueta"></span></h5>
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" id="editInicio" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="form-group col-6">
                    <label>Fecha de fin</label>
                    <input type="date" name="fecha_fin" id="editFin" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            <small class="form-text text-muted mt-n2 mb-2">En una licencia, la fecha de fin vacía significa que sigue en curso.</small>
            <div class="form-group">
                <label>N° de resolución / documento</label>
                <input type="text" name="resolucion" id="editResolucion" class="form-control form-control-sm" maxlength="255">
            </div>
            <div class="form-group mb-0">
                <label>Detalle</label>
                <textarea name="motivo" id="editMotivo" class="form-control form-control-sm" rows="3" maxlength="2000"></textarea>
            </div>
        </div>
        <div class="ex-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar</button>
        </div>
    </form>
</dialog>

<dialog class="ex-dialog" id="dlgBorrar">
    <form method="POST" id="formBorrar">
        @csrf
        @method('DELETE')
        <div class="ex-dialog-body">
            <h5><i class="fas fa-exclamation-triangle text-danger mr-1"></i> Eliminar</h5>
            <p class="small mb-0">¿Eliminar «<strong id="borrarEtiqueta"></strong>» del expediente?</p>
        </div>
        <div class="ex-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-danger font-weight-bold"><i class="fas fa-trash mr-1"></i> Eliminar</button>
        </div>
    </form>
</dialog>

<dialog class="ex-dialog" id="dlgAccion">
    <form method="POST" id="formAccion" action="{{ route('expediente.acciones.store', $u) }}" data-url-nueva="{{ route('expediente.acciones.store', $u) }}">
        @csrf
        <input type="hidden" name="_method" id="accionMetodo" value="POST">
        <input type="hidden" name="_form" value="dlgAccion">
        <div class="ex-dialog-body">
            <h5><i class="fas fa-hands-helping text-primary mr-1"></i> <span id="accionTitulo">Registrar seguimiento</span></h5>
            <div class="form-row">
                <div class="form-group col-7">
                    <label>Tipo de acción</label>
                    <select name="tipo" id="accTipo" class="form-control form-control-sm" required>
                        @foreach (\App\Models\SeguimientoAccion::TIPOS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-5">
                    <label>Fecha</label>
                    <input type="date" name="fecha" id="accFecha" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Responsable</label>
                <select name="responsable_id" id="accResponsable" class="form-control form-control-sm">
                    <option value="">— Sin asignar —</option>
                    @foreach ($responsables as $r)
                        <option value="{{ $r->id }}" @selected($r->id === auth()->id())>{{ $r->apellidos }}, {{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Qué pasó</label>
                <textarea name="resumen" id="accResumen" class="form-control form-control-sm" rows="3" maxlength="5000" required placeholder="Motivo, lo conversado, situación encontrada…"></textarea>
            </div>
            <div class="form-group">
                <label>Acuerdos / compromisos <span class="text-muted font-weight-normal">(opcional)</span></label>
                <textarea name="acuerdos" id="accAcuerdos" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
            </div>
            <div class="form-row align-items-end">
                <div class="form-group col-6">
                    <label>Próxima revisión <span class="text-muted font-weight-normal">(opcional)</span></label>
                    <input type="date" name="proxima_revision" id="accRevision" class="form-control form-control-sm">
                </div>
                <div class="form-group col-6">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" name="confidencial" value="1" id="accConfidencial">
                        <label class="custom-control-label" for="accConfidencial"><i class="fas fa-lock fa-xs"></i> Confidencial</label>
                    </div>
                </div>
            </div>
            <div class="form-group mb-0" id="accEstadoGrupo" hidden>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="estado" value="cerrado" id="accCerrado">
                    <label class="custom-control-label" for="accCerrado">Seguimiento cerrado</label>
                </div>
            </div>
        </div>
        <div class="ex-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar</button>
        </div>
    </form>
</dialog>

<dialog class="ex-dialog" id="dlgDocumento">
    <form method="POST" action="{{ route('expediente.documentos.store', $u) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_form" value="dlgDocumento">
        <div class="ex-dialog-body">
            <h5><i class="fas fa-paperclip text-primary mr-1"></i> Subir documento</h5>
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" class="form-control form-control-sm" maxlength="255" required placeholder="Ej. Solicitud de licencia 2026-II" value="{{ old('titulo') }}">
            </div>
            <div class="form-group">
                <label>Archivo <span class="text-muted font-weight-normal">(PDF, imagen, Word o Excel · máx. 10 MB)</span></label>
                <input type="file" name="archivo" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required>
            </div>
            <div class="form-group mb-0">
                <label>Vincular a <span class="text-muted font-weight-normal">(opcional)</span></label>
                <select name="vinculo" class="form-control form-control-sm">
                    <option value="">— Documento general del expediente —</option>
                    @if ($eventos->isNotEmpty())
                        <optgroup label="Eventos de la trayectoria">
                            @foreach ($eventos->sortByDesc('fecha_inicio') as $e)
                                <option value="evento:{{ $e->id }}">{{ $e->etiqueta }}{{ $e->fecha_inicio ? ' · ' . $e->fecha_inicio->format('d/m/Y') : ' · sin fecha' }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($acciones->isNotEmpty())
                        <optgroup label="Seguimiento">
                            @foreach ($acciones as $a)
                                <option value="accion:{{ $a->id }}">{{ $a->etiqueta }} · {{ $a->fecha->format('d/m/Y') }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
        </div>
        <div class="ex-dialog-foot">
            <button type="button" class="btn btn-sm btn-light js-cerrar">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-upload mr-1"></i> Subir</button>
        </div>
    </form>
</dialog>
@endif

<script>
(function () {
    function abrir(d) { if (d.showModal) d.showModal(); else d.setAttribute('open', ''); }
    document.querySelectorAll('.ex-dialog').forEach(function (d) {
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });
    document.querySelectorAll('.js-cerrar').forEach(function (b) {
        b.addEventListener('click', function () { b.closest('dialog').close(); });
    });
    document.querySelectorAll('[data-abrir]').forEach(function (b) {
        b.addEventListener('click', function () { abrir(document.getElementById(b.dataset.abrir)); });
    });

    var urlEditar = '{{ url('expediente/eventos') }}/';
    document.querySelectorAll('.js-editar').forEach(function (b) {
        b.addEventListener('click', function () {
            var ev = JSON.parse(b.dataset.evento);
            document.getElementById('formEditar').action = urlEditar + ev.id;
            document.getElementById('editEtiqueta').textContent = ev.etiqueta;
            document.getElementById('editInicio').value = ev.fecha_inicio || '';
            document.getElementById('editFin').value = ev.fecha_fin || '';
            document.getElementById('editResolucion').value = ev.resolucion || '';
            document.getElementById('editMotivo').value = ev.motivo || '';
            abrir(document.getElementById('dlgEditar'));
            if (!ev.fecha_inicio) document.getElementById('editInicio').focus();
        });
    });
    document.querySelectorAll('.js-borrar').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('formBorrar').action = b.dataset.url;
            document.getElementById('borrarEtiqueta').textContent = b.dataset.etiqueta;
            abrir(document.getElementById('dlgBorrar'));
        });
    });

    // Pestañas (recuerda la última en la URL: #notas)
    var tabs = document.querySelectorAll('.ex-tab');
    function mostrar(tab) {
        tabs.forEach(function (t) { t.classList.toggle('is-active', t.dataset.tab === tab); });
        document.querySelectorAll('[data-pane]').forEach(function (p) { p.hidden = p.dataset.pane !== tab; });
        try { history.replaceState(null, '', tab === 'trayectoria' ? location.pathname : '#' + tab); } catch (e) {}
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { mostrar(t.dataset.tab); }); });
    var inicial = location.hash.replace('#', '');
    if (['notas', 'seguimiento', 'documentos', 'titulacion'].indexOf(inicial) !== -1 && document.querySelector('[data-pane="' + inicial + '"]')) mostrar(inicial);

    document.querySelectorAll('.ex-per-head').forEach(function (h) {
        h.addEventListener('click', function () { h.closest('.ex-per').classList.toggle('is-open'); });
    });

    // Seguimiento: el mismo diálogo sirve para registrar (POST) y editar (PUT)
    var formAccion = document.getElementById('formAccion');
    function prepararAccion(a) {
        var nueva = !a;
        a = a || {};
        formAccion.action = nueva ? formAccion.dataset.urlNueva : '{{ url('expediente/seguimiento') }}/' + a.id;
        document.getElementById('accionMetodo').value = nueva ? 'POST' : 'PUT';
        document.getElementById('accionTitulo').textContent = nueva ? 'Registrar seguimiento' : 'Editar seguimiento';
        document.getElementById('accTipo').value = a.tipo || 'entrevista';
        document.getElementById('accFecha').value = a.fecha || '{{ now()->format('Y-m-d') }}';
        document.getElementById('accResponsable').value = nueva ? '{{ auth()->id() }}' : (a.responsable_id || '');
        document.getElementById('accResumen').value = a.resumen || '';
        document.getElementById('accAcuerdos').value = a.acuerdos || '';
        document.getElementById('accRevision').value = a.proxima_revision || '';
        document.getElementById('accConfidencial').checked = !!a.confidencial;
        document.getElementById('accEstadoGrupo').hidden = nueva;
        document.getElementById('accCerrado').checked = a.estado === 'cerrado';
    }
    document.querySelectorAll('[data-abrir="dlgAccion"]').forEach(function (b) {
        b.addEventListener('click', function () { prepararAccion(null); }, true);
    });
    document.querySelectorAll('.js-editar-accion').forEach(function (b) {
        b.addEventListener('click', function () {
            prepararAccion(JSON.parse(b.dataset.accion));
            abrir(document.getElementById('dlgAccion'));
        });
    });

    // Si un formulario volvió con errores, se reabre ese mismo diálogo con lo que se escribió.
    @if ($errors->any() && old('_form'))
        @php $valoresViejos = collect(old())->except(['_token', '_method', '_form', 'archivo'])->all(); @endphp
        (function () {
            var d = document.getElementById({!! json_encode(old('_form')) !!});
            if (!d) return;
            var viejo = {!! json_encode($valoresViejos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            Object.keys(viejo).forEach(function (k) {
                var campo = d.querySelector('[name="' + k + '"]');
                if (!campo) return;
                if (campo.type === 'checkbox') campo.checked = !!viejo[k]; else campo.value = viejo[k];
            });
            abrir(d);
        })();
    @endif
})();
</script>
<script>
(function () {
    // Ficha de matrícula en panel lateral: carga la pantalla normal en un iframe y le oculta el
    // menú y la barra superior (igual que el Directorio). Si se guarda una edición, se recarga.
    var btn = document.querySelector('.js-ficha');
    if (!btn) return;
    var panel = document.getElementById('exFicha'), fondo = document.getElementById('exFichaFondo');
    var frame = document.getElementById('exFichaFrame'), carga = document.getElementById('exFichaCarga');
    var bVer = document.getElementById('exFichaVer'), bEditar = document.getElementById('exFichaEditar');
    var pestana = document.getElementById('exFichaPestana');
    var URL_VER = btn.href, URL_EDITAR = btn.dataset.editar;
    var estado = { enviado: false, ruta: null };
    var CSS = '#accordionSidebar,.sidebar,nav.topbar,.topbar,footer.sticky-footer,.scroll-to-top{display:none!important}'
        + '#content-wrapper{margin-left:0!important;width:100%!important}#wrapper{display:block!important}'
        + 'body{padding:0!important;background:transparent}#content{padding-top:.5rem}';

    function cargar(url, editando) {
        estado = { enviado: false, ruta: null };
        carga.hidden = false; frame.style.visibility = 'hidden';
        frame.src = url; pestana.href = url;
        bVer.hidden = !editando; bEditar.hidden = editando || !URL_EDITAR;
    }
    function abrir(editando) {
        cargar(editando ? URL_EDITAR : URL_VER, editando);
        fondo.hidden = false; panel.classList.add('is-open'); panel.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function cerrar() {
        panel.classList.remove('is-open'); panel.setAttribute('aria-hidden', 'true');
        fondo.hidden = true; document.body.style.overflow = '';
        setTimeout(function () { if (!panel.classList.contains('is-open')) frame.src = 'about:blank'; }, 300);
    }
    frame.addEventListener('load', function () {
        var doc, ruta;
        try { doc = frame.contentDocument; ruta = frame.contentWindow.location.pathname; } catch (e) { return; }
        if (!doc || frame.src === 'about:blank') return;
        if (estado.enviado && ruta !== estado.ruta) { cerrar(); setTimeout(function () { location.reload(); }, 300); return; }
        estado.enviado = false;
        var st = doc.createElement('style'); st.textContent = CSS;
        (doc.head || doc.documentElement).appendChild(st);
        // Dentro del panel se cierra con ✕: se ocultan "Volver"/"Regresar" y los enlaces "Cancelar".
        doc.querySelectorAll('a, button').forEach(function (el) {
            var t = (el.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase(), h = (el.getAttribute('href') || '').toLowerCase();
            if (/^(volver|regresar)\b/.test(t) || h.indexOf('history.back') !== -1 || (el.tagName === 'A' && /^cancelar\b/.test(t))) el.style.setProperty('display', 'none', 'important');
        });
        doc.addEventListener('submit', function (ev) { if (!ev.defaultPrevented) { estado.enviado = true; estado.ruta = frame.contentWindow.location.pathname; } });
        carga.hidden = true; frame.style.visibility = 'visible';
    });
    btn.addEventListener('click', function (e) {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return; // Ctrl/clic medio: pestaña nueva
        e.preventDefault(); abrir(false);
    });
    bEditar.addEventListener('click', function () { cargar(URL_EDITAR, true); });
    bVer.addEventListener('click', function () { cargar(URL_VER, false); });
    document.getElementById('exFichaCerrar').addEventListener('click', cerrar);
    fondo.addEventListener('click', cerrar);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('is-open')) cerrar(); });
})();
</script>
@endsection
