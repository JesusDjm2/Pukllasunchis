@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('contenido')
{{--
    Incidencias y tutorías: incidencias que reportan los docentes, solicitudes de tutoría
    individual y sugerencias del buzón QR. Mismo lenguaje visual que «Alertas académicas»
    (admin/expediente/panel): tarjetas resumen que eligen la lista y filas por persona.
    ?tab=incidencias|tutorias|sugerencias abre esa lista (y las acciones vuelven a ella).
--}}
@php
    $esAdmin = auth()->user()->hasAnyRole(['admin', 'super-admin']);
    $listasSeg = [
        'incidencias' => ['Incidencias', 'fa-exclamation-triangle', 'amber', $conteos['incidencias'], 'registradas'],
        'tutorias' => ['Tutorías', 'fa-hands-helping', 'violet', $conteos['tutorias_pendientes'], 'pendientes de atender'],
        'sugerencias' => ['Sugerencias', 'fa-comment-dots', 'green', $conteos['sugerencias_pendientes'], 'pendientes de revisar'],
    ];
    $tabInicial = array_key_exists(request('tab'), $listasSeg) ? request('tab') : 'incidencias';
    $iniciales = fn ($a, $b = '') => mb_strtoupper(mb_substr(trim((string) $a), 0, 1).mb_substr(trim((string) $b), 0, 1));
    $buscable = fn (...$t) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', array_filter($t))));
    $ciclo = fn ($c) => $c ? (preg_match('/^[IVX]+$/', trim($c->nombre)) ? 'Ciclo '.trim($c->nombre) : $c->nombre) : null;
@endphp

<style>
    .sg {
        --pn-card: #fff; --pn-soft: #f8fafc; --pn-border: #e5e7eb; --pn-text: #1e293b; --pn-muted: #64748b;
        --pn-accent: #4f46e5; --pn-shadow: 0 1px 2px rgba(15,23,42,.05), 0 4px 14px rgba(15,23,42,.04);
        --c-amber: #b45309; --c-amber-bg: rgba(217,119,6,.1);
        --c-red: #b91c1c; --c-red-bg: rgba(220,38,38,.09);
        --c-green: #15803d; --c-green-bg: rgba(22,163,74,.1);
        --c-violet: #6d28d9; --c-violet-bg: rgba(124,58,237,.1);
        --c-cyan: #0e7490; --c-cyan-bg: rgba(8,145,178,.1);
        --c-slate: #475569; --c-slate-bg: rgba(100,116,139,.12);
    }
    .dark-mode .sg, .dim-mode .sg {
        --pn-card: rgba(255,255,255,.035); --pn-soft: rgba(255,255,255,.03); --pn-border: rgba(255,255,255,.1);
        --pn-text: #e2e8f0; --pn-muted: #94a3b8; --pn-accent: #a5b4fc; --pn-shadow: none;
        --c-amber: #fcd34d; --c-red: #fca5a5; --c-green: #86efac; --c-violet: #c4b5fd; --c-cyan: #67e8f9; --c-slate: #cbd5e1;
    }
    .sg [hidden] { display: none !important; }
    .sg-title { font-size: 1.3rem; font-weight: 800; color: var(--pn-text); margin: 0; }
    .sg-sub { font-size: .82rem; color: var(--pn-muted); }
    .sg-acciones { display: flex; flex-wrap: wrap; gap: .5rem; margin-left: auto; }
    .sg-btn { display: inline-flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 600; padding: .38rem .8rem;
        border-radius: 8px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-text); text-decoration: none !important; cursor: pointer; }
    .sg-btn:hover { border-color: var(--pn-accent); color: var(--pn-accent); }

    .sg-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .7rem; margin: 1rem 0; }
    .sg-kpi { display: flex; align-items: center; gap: .8rem; padding: .85rem 1rem; text-align: left; width: 100%;
        background: var(--pn-card); border: 1px solid var(--pn-border); border-radius: 14px; box-shadow: var(--pn-shadow);
        cursor: pointer; transition: border-color .15s, transform .15s; color: var(--pn-text); }
    .sg-kpi:hover { transform: translateY(-1px); border-color: var(--pn-accent); }
    .sg-kpi.is-active { border-color: var(--pn-accent); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
    .sg-kpi-icon { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    @foreach (['amber', 'red', 'green', 'violet', 'cyan', 'slate'] as $c)
        .sg-kpi-icon.is-{{ $c }}, .sg-pill.is-{{ $c }} { color: var(--c-{{ $c }}); background: var(--c-{{ $c }}-bg); }
    @endforeach
    .sg-kpi-num { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
    .sg-kpi-label { font-size: .74rem; color: var(--pn-muted); font-weight: 600; line-height: 1.25; }

    .sg-card { background: var(--pn-card); border: 1px solid var(--pn-border); border-radius: 14px; box-shadow: var(--pn-shadow); }
    .sg-list-head { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-bottom: 1px solid var(--pn-border); }
    .sg-list-title { font-size: 1rem; font-weight: 800; color: var(--pn-text); }
    .sg-herr { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-left: auto; }
    .sg-search { height: 32px; font-size: .8rem; padding: 0 .7rem; border-radius: 8px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-text); width: 230px; }
    .sg-seg { display: inline-flex; border: 1px solid var(--pn-border); border-radius: 8px; overflow: hidden; }
    .sg-seg button { border: 0; background: transparent; color: var(--pn-muted); font-size: .76rem; font-weight: 700; padding: .32rem .7rem; cursor: pointer; }
    .sg-seg button + button { border-left: 1px solid var(--pn-border); }
    .sg-seg button.is-on { background: var(--pn-accent); color: #fff; }

    .sg-row { display: flex; align-items: flex-start; gap: .8rem; padding: .75rem 1.1rem; border-bottom: 1px solid var(--pn-border); color: var(--pn-text); }
    .sg-row:last-child { border-bottom: 0; }
    .sg-row:hover { background: var(--pn-soft); }
    .sg-row.is-aviso { box-shadow: inset 3px 0 0 var(--c-amber); }
    .sg-avatar { width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 800; color: #4338ca; background: #e0e7ff; }
    .sg-avatar.is-muted { color: #94a3b8; background: #f1f5f9; }
    .dark-mode .sg-avatar, .dim-mode .sg-avatar { background: rgba(165,180,252,.15); color: #c7d2fe; }
    .sg-quien { min-width: 0; flex: 0 0 15rem; }
    .sg-name { font-size: .86rem; font-weight: 700; color: var(--pn-text); }
    a.sg-name:hover { color: var(--pn-accent); text-decoration: none; }
    .sg-meta { font-size: .72rem; color: var(--pn-muted); line-height: 1.4; }
    .sg-texto { flex: 1 1 auto; min-width: 0; font-size: .82rem; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
    .sg-texto-meta { display: block; font-size: .72rem; color: var(--pn-muted); margin-top: .25rem; white-space: normal; }
    .sg-lado { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; text-align: right; }
    .sg-pills { display: flex; flex-wrap: wrap; gap: .3rem; justify-content: flex-end; }
    .sg-pill { display: inline-flex; align-items: center; gap: .3rem; padding: .18rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .sg-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .sg-mini { font-size: .68rem; color: var(--pn-muted); }
    .sg-row-acc { display: flex; gap: .35rem; align-items: center; }
    .sg-row-acc form { margin: 0; }
    .sg-accion { font-size: .72rem; font-weight: 700; padding: .28rem .7rem; border-radius: 999px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-text); cursor: pointer; white-space: nowrap; }
    .sg-accion.is-ok { background: var(--c-green-bg); color: var(--c-green); border-color: transparent; }
    .sg-accion:hover { filter: brightness(.96); border-color: var(--pn-accent); }
    .sg-icono { width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--pn-border); background: var(--pn-card); color: var(--pn-muted);
        display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; cursor: pointer; padding: 0; }
    .sg-icono:hover { color: var(--pn-accent); border-color: var(--pn-accent); }
    .sg-icono.is-danger:hover { color: var(--c-red); border-color: var(--c-red); }
    .sg-tutores { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .3rem; }
    .sg-tutor { font-size: .68rem; padding: .1rem .5rem; border-radius: 999px; background: var(--c-slate-bg); color: var(--c-slate); }
    .sg-empty { text-align: center; padding: 2.5rem 1rem; color: var(--pn-muted); font-size: .86rem; }
    .sg-pag { display: flex; justify-content: center; padding: .75rem; border-top: 1px solid var(--pn-border); }
    .sg-pag .pagination { margin: 0; }

    .sg-foto { display: none; position: fixed; z-index: 1060; inset: 0; background: rgba(0,0,0,.82); align-items: center; justify-content: center; }
    .sg-foto.is-open { display: flex; }
    .sg-foto-in { position: relative; max-width: min(92vw, 720px); padding: 1rem; }
    .sg-foto-in img { max-width: 100%; height: auto; border-radius: .35rem; border: 4px solid #fff; box-shadow: 0 .25rem 1rem rgba(0,0,0,.35); }
    .sg-foto-x { position: absolute; top: .25rem; right: .25rem; font-size: 1.75rem; line-height: 1; color: #fff; font-weight: 700; border: 0; background: transparent; cursor: pointer; }

    @media (max-width: 991.98px) {
        .sg-row { flex-wrap: wrap; }
        .sg-quien { flex: 1 1 calc(100% - 3rem); }
        .sg-texto { flex-basis: 100%; padding-left: 2.9rem; }
        .sg-lado { flex-basis: 100%; align-items: flex-start; padding-left: 2.9rem; text-align: left; }
        .sg-pills { justify-content: flex-start; }
    }
    @media (max-width: 767.98px) {
        .sg-grid { grid-template-columns: 1fr; }
        .sg-herr { margin-left: 0; width: 100%; }
        .sg-search { flex: 1; width: auto; }
    }
</style>

<div class="container-fluid py-3 sg">
    <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
        <div>
            <h4 class="sg-title"><i class="fas fa-hands-helping mr-2" style="color:#4f46e5;"></i>Incidencias y tutorías</h4>
            <div class="sg-sub">Incidencias reportadas por docentes, solicitudes de tutoría individual y sugerencias del buzón QR · clic en un nombre para abrir su expediente</div>
        </div>
        <div class="sg-acciones">
            <a href="{{ route('admin.seguimiento.qr') }}" class="sg-btn"><i class="fas fa-qrcode"></i> Códigos QR por ciclo</a>
            <a href="{{ route('docente.index') }}" class="sg-btn"><i class="fas fa-arrow-left"></i> Docentes</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mt-3 mb-0 js-flash-sg" role="alert" data-toast="{{ session('success') }}">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="sg-grid">
        @foreach ($listasSeg as $k => [$label, $icono, $color, $num, $detalle])
            <button type="button" class="sg-kpi {{ $k === $tabInicial ? 'is-active' : '' }}" data-lista="{{ $k }}">
                <span class="sg-kpi-icon is-{{ $color }}"><i class="fas {{ $icono }}"></i></span>
                <span>
                    <span class="sg-kpi-num d-block">{{ $num }}</span>
                    <span class="sg-kpi-label d-block">{{ $label }} · {{ $detalle }}</span>
                </span>
            </button>
        @endforeach
    </div>

    {{-- ── Incidencias ── --}}
    <section class="sg-card" data-panel="incidencias" @if ($tabInicial !== 'incidencias') hidden @endif>
        <div class="sg-list-head">
            <span class="sg-list-title"><i class="fas fa-exclamation-triangle mr-1" style="color:var(--c-amber);"></i> Incidencias</span>
            <span class="sg-sub">{{ $incidencias->total() }}</span>
            @if ($incidencias->isNotEmpty())
                <div class="sg-herr">
                    <input type="search" class="sg-search js-buscar" placeholder="Filtrar: alumno, docente, texto…" aria-label="Filtrar incidencias">
                </div>
            @endif
        </div>
        @forelse ($incidencias as $inc)
            @php $al = $inc->alumno; @endphp
            <div class="sg-row" data-buscar="{{ $buscable($al?->apellidos, $al?->nombres, $al?->dni, $inc->docente?->nombre, $inc->nombre_docente, $inc->reporte) }}">
                <span class="sg-avatar {{ $al ? '' : 'is-muted' }}">{{ $al ? $iniciales($al->nombres, $al->apellidos) : '—' }}</span>
                <div class="sg-quien">
                    @if ($al?->user_id)
                        <a href="{{ route('expediente.show', $al->user_id) }}" class="sg-name d-block">{{ $al->apellidos }}, {{ $al->nombres }}</a>
                    @else
                        <span class="sg-name d-block">{{ $al ? $al->apellidos.', '.$al->nombres : 'Sin alumno' }}</span>
                    @endif
                    <span class="sg-meta d-block">
                        {{ $inc->ciclo?->programa?->nombre ?? 'Sin programa' }}@if ($inc->ciclo) · {{ $ciclo($inc->ciclo) }}@endif
                    </span>
                    @if ($inc->ciclo)
                        @if ($inc->ciclo->tutores->isNotEmpty())
                            <span class="sg-tutores" title="Tutor(es) del ciclo">
                                @foreach ($inc->ciclo->tutores as $tutor)
                                    <span class="sg-tutor"><i class="fas fa-user-shield fa-xs mr-1"></i>{{ $tutor->nombreCorto() }}</span>
                                @endforeach
                            </span>
                        @else
                            <span class="sg-mini d-block mt-1"><i>Sin tutor asignado</i></span>
                        @endif
                    @endif
                </div>
                <div class="sg-texto">{{ $inc->reporte }}<span class="sg-texto-meta">
                    <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i>
                    @if ($inc->docente) {{ $inc->docente->nombre }}
                    @elseif ($inc->nombre_docente) {{ $inc->nombre_docente }} <span class="sg-pill is-slate">externo</span>
                    @else — @endif
                </span></div>
                <div class="sg-lado">
                    <span class="sg-mini"><i class="far fa-calendar fa-xs mr-1"></i>{{ $inc->fecha?->format('d/m/Y') ?? '—' }}</span>
                    <div class="sg-row-acc">
                        @if ($inc->imagen)
                            <button type="button" class="sg-icono" title="Ver imagen adjunta"
                                    onclick="segOpenPhotoModal('{{ asset('img/incidencias/'.$inc->imagen) }}', 'Imagen adjunta')"><i class="fas fa-image"></i></button>
                        @endif
                        @if ($esAdmin)
                            <form action="{{ route('admin.incidencias.destroy', $inc->id) }}" method="POST"
                                  data-confirm="Se eliminará esta incidencia." data-confirm-danger data-confirm-titulo="¿Eliminar la incidencia?">
                                @csrf @method('DELETE')
                                <button type="submit" class="sg-icono is-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="sg-empty"><i class="fas fa-check-circle d-block mb-2" style="font-size:1.4rem;color:var(--c-green);"></i> No hay incidencias registradas.</div>
        @endforelse
        @if ($incidencias->hasPages())
            <div class="sg-pag">{{ $incidencias->links() }}</div>
        @endif
    </section>

    {{-- ── Tutorías y Sugerencias (misma forma) ── --}}
    @foreach ([
        'tutorias' => [$tutorias, 'Tutorías', 'fa-hands-helping', 'violet', 'atendida', 'Atendida', 'Marcar atendida', 'admin.tutorias.estado', 'admin.tutorias.destroy', 'motivo', 'solicitud de tutoría'],
        'sugerencias' => [$sugerencias, 'Sugerencias', 'fa-comment-dots', 'green', 'revisada', 'Revisada', 'Marcar revisada', 'admin.sugerencias.estado', 'admin.sugerencias.destroy', 'mensaje', 'sugerencia'],
    ] as $k => [$lista, $titulo, $icono, $color, $estadoHecho, $etiquetaHecho, $accionHecho, $rutaEstado, $rutaBorrar, $campoTexto, $nombreCosa])
        <section class="sg-card" data-panel="{{ $k }}" @if ($tabInicial !== $k) hidden @endif>
            <div class="sg-list-head">
                <span class="sg-list-title"><i class="fas {{ $icono }} mr-1" style="color:var(--c-{{ $color }});"></i> {{ $titulo }}</span>
                <span class="sg-sub">{{ $lista->total() }}</span>
                @if ($lista->isNotEmpty())
                    <div class="sg-herr">
                        <div class="sg-seg js-estado" role="group" aria-label="Filtrar por estado">
                            <button type="button" class="is-on" data-v="">Todas</button>
                            <button type="button" data-v="pendiente">Pendientes</button>
                            <button type="button" data-v="hecho">{{ $etiquetaHecho }}s</button>
                        </div>
                        <input type="search" class="sg-search js-buscar" placeholder="Filtrar: alumno, DNI, texto…" aria-label="Filtrar {{ $titulo }}">
                    </div>
                @endif
            </div>
            @forelse ($lista as $r)
                @php
                    $al = $r->alumno;
                    $hecho = $r->estado === $estadoHecho;
                    $otroCiclo = $al && $r->ciclo && $al->ciclo_id !== $r->ciclo_id;
                    $nombre = $al ? $al->apellidos.', '.$al->nombres : ($r->nombre_alumno ?: 'Anónimo');
                @endphp
                <div class="sg-row {{ $otroCiclo ? 'is-aviso' : '' }}" data-estado="{{ $hecho ? 'hecho' : 'pendiente' }}"
                     data-buscar="{{ $buscable($nombre, $al?->dni, $al?->email, $r->{$campoTexto}) }}">
                    <span class="sg-avatar {{ $al ? '' : 'is-muted' }}">{{ $al ? $iniciales($al->nombres, $al->apellidos) : $iniciales($r->nombre_alumno ?: '?') }}</span>
                    <div class="sg-quien">
                        @if ($al?->user_id)
                            <a href="{{ route('expediente.show', $al->user_id) }}" class="sg-name d-block">{{ $nombre }}</a>
                        @else
                            <span class="sg-name d-block">{{ $nombre }}</span>
                        @endif
                        <span class="sg-meta d-block">
                            {{ $r->ciclo?->programa?->nombre ?? $al?->ciclo?->programa?->nombre ?? 'Sin programa' }}
                            @if ($r->ciclo) · {{ $ciclo($r->ciclo) }} @elseif ($al?->ciclo) · {{ $ciclo($al->ciclo) }} @endif
                        </span>
                        @if ($al?->email || $al?->numero)
                            <span class="sg-meta d-block">
                                @if ($al->email)<i class="fas fa-envelope fa-xs mr-1"></i>{{ $al->email }}@endif
                                @if ($al->numero) · <i class="fas fa-phone fa-xs mr-1"></i>{{ $al->numero }}@endif
                            </span>
                        @endif
                        @if ($otroCiclo)
                            <span class="sg-pill is-amber mt-1" title="El alumno pertenece a {{ $ciclo($al->ciclo) }}"><i class="fas fa-exclamation-triangle"></i> No es de este ciclo</span>
                        @endif
                    </div>
                    <div class="sg-texto">{{ $r->{$campoTexto} }}</div>
                    <div class="sg-lado">
                        <div class="sg-pills">
                            @if ($k === 'tutorias' && $r->urgencia)
                                <span class="sg-pill is-{{ $r->urgencia_color }}" title="¿Cómo quiere conversar?"><span class="sg-pill-dot"></span>{{ $r->urgencia_texto }}</span>
                                @if ($r->pideOtroDocente() && $r->docente_solicitado_nombre)
                                    <span class="sg-pill is-slate" title="Docente con quien desea hablar"><i class="fas fa-chalkboard-teacher"></i> {{ $r->docente_solicitado_nombre }}</span>
                                @endif
                            @endif
                            <span class="sg-pill is-{{ $hecho ? 'green' : 'slate' }}"><span class="sg-pill-dot"></span>{{ $hecho ? $etiquetaHecho : 'Pendiente' }}</span>
                        </div>
                        <span class="sg-mini">
                            <i class="far fa-calendar fa-xs mr-1"></i>{{ $r->created_at->format('d/m/Y H:i') }}
                            @if ($hecho && $r->atendido_at) · {{ strtolower($etiquetaHecho) }} el {{ $r->atendido_at->format('d/m/Y') }} @endif
                        </span>
                        <div class="sg-row-acc">
                            <form action="{{ route($rutaEstado, $r->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="sg-accion {{ $hecho ? '' : 'is-ok' }}">{{ $hecho ? 'Marcar pendiente' : $accionHecho }}</button>
                            </form>
                            @if ($esAdmin)
                                <form action="{{ route($rutaBorrar, $r->id) }}" method="POST"
                                      data-confirm="Se eliminará esta {{ $nombreCosa }}." data-confirm-danger data-confirm-titulo="¿Eliminar la {{ $nombreCosa }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="sg-icono is-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="sg-empty"><i class="fas fa-check-circle d-block mb-2" style="font-size:1.4rem;color:var(--c-green);"></i> No hay {{ strtolower($titulo) }} registradas.</div>
            @endforelse
            <div class="sg-empty js-sin-resultados" hidden>Nada coincide con el filtro en esta página.</div>
            @if ($lista->hasPages())
                <div class="sg-pag">{{ $lista->links() }}</div>
            @endif
        </section>
    @endforeach
</div>

{{-- Imagen adjunta de una incidencia --}}
<div id="segPhotoModal" class="sg-foto" role="dialog" aria-modal="true" onclick="segClosePhotoModal()">
    <div class="sg-foto-in" onclick="event.stopPropagation();">
        <button type="button" class="sg-foto-x" onclick="segClosePhotoModal()" aria-label="Cerrar">&times;</button>
        <img id="segPhotoModalImg" src="" alt="" oncontextmenu="return false;">
        <p id="segPhotoModalName" class="text-white text-center mt-2 mb-0 font-weight-bold" style="font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.6);"></p>
    </div>
</div>

<script>
(function () {
    // Lista visible: tarjeta resumen elegida; queda en la URL (?tab=) para que las acciones
    // (marcar, eliminar) y la paginación vuelvan a la misma lista.
    function mostrar(k) {
        document.querySelectorAll('.sg-kpi').forEach(function (b) { b.classList.toggle('is-active', b.dataset.lista === k); });
        document.querySelectorAll('[data-panel]').forEach(function (s) { s.hidden = s.dataset.panel !== k; });
        var q = new URLSearchParams(location.search);
        if (q.get('tab') !== k) {
            q.set('tab', k);
            ['incidencias_page', 'tutorias_page', 'sugerencias_page'].forEach(function (p) { if (p !== k + '_page') q.delete(p); });
            history.replaceState(null, '', location.pathname + '?' + q.toString());
        }
    }
    document.querySelectorAll('.sg-kpi').forEach(function (b) {
        b.addEventListener('click', function () { mostrar(b.dataset.lista); });
    });

    // Filtro por texto y por estado (sobre la página visible).
    function filtrar(panel) {
        var inp = panel.querySelector('.js-buscar');
        var t = inp ? inp.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim() : '';
        var on = panel.querySelector('.js-estado .is-on');
        var est = on ? on.dataset.v : '';
        var visibles = 0;
        panel.querySelectorAll('.sg-row').forEach(function (r) {
            var ok = (!t || r.dataset.buscar.indexOf(t) !== -1) && (!est || r.dataset.estado === est);
            r.hidden = !ok;
            if (ok) visibles++;
        });
        var nada = panel.querySelector('.js-sin-resultados');
        if (nada) nada.hidden = visibles > 0 || !panel.querySelector('.sg-row');
    }
    document.querySelectorAll('.js-buscar').forEach(function (inp) {
        inp.addEventListener('input', function () { filtrar(inp.closest('[data-panel]')); });
    });
    document.querySelectorAll('.js-estado').forEach(function (g) {
        g.addEventListener('click', function (e) {
            var b = e.target.closest('button');
            if (!b) return;
            g.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); });
            filtrar(g.closest('[data-panel]'));
        });
    });

    // "Guardado" como aviso pequeño, si las alertas del sistema están cargadas.
    document.addEventListener('DOMContentLoaded', function () {
        var f = document.querySelector('.js-flash-sg');
        if (f && window.PkAlert) { PkAlert.toast(f.dataset.toast); f.remove(); }
    });
})();

function segOpenPhotoModal(src, name) {
    document.getElementById('segPhotoModalImg').src = src;
    document.getElementById('segPhotoModalName').textContent = name || '';
    document.getElementById('segPhotoModal').classList.add('is-open');
}
function segClosePhotoModal() {
    document.getElementById('segPhotoModal').classList.remove('is-open');
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') segClosePhotoModal(); });
</script>
@endsection
