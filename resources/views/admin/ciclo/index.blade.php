@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

@include('admin._partials.prog-styles')
<style>
.pg-acc-toggle {
    display:flex; align-items:center; justify-content:space-between;
    padding:.8rem 1.15rem; cursor:pointer; user-select:none;
    background:var(--pg-card); transition:background .18s;
    text-decoration:none !important;
}
.pg-acc-toggle:hover { background:rgba(37,99,235,.04); }
.dark-mode .pg-acc-toggle:hover { background:rgba(255,255,255,.035); }
.pg-acc-chevron { transition:transform .28s; color:var(--pg-muted); font-size:.72rem; }
.pg-acc-toggle.collapsed .pg-acc-chevron { transform:rotate(-90deg); }
.pg-acc-body {
    border-top:1px solid var(--pg-border);
    padding:1rem 1rem .75rem;
    background:var(--pg-card);
}
</style>

<div class="pg-wrap">
<div class="container-fluid py-3">

    {{-- ── Hero ── --}}
    <div class="pg-hero" id="pg-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap"
             style="gap:1rem; position:relative; z-index:1;">
            <div>
                <div class="pg-hero-label">
                    <i class="fas fa-university"></i> EESP Pukllasunchis
                </div>
                <h4>Ciclos Académicos</h4>
                <p class="pg-hero-sub">Lista y gestión de ciclos por programa de estudios</p>
            </div>
            <a href="{{ route('ciclo.create') }}" class="pg-hero-btn align-self-start">
                <i class="fas fa-plus fa-xs"></i> Nuevo Ciclo
            </a>
        </div>
    </div>

    {{-- ── Stats ── --}}
    <div class="row mb-4">
        <div class="col-sm-4 col-6 mb-3">
            <div class="pg-stat">
                <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                    <i class="fas fa-layer-group" style="color:#2563eb;"></i>
                </div>
                <div>
                    <div class="pg-stat-val">{{ $ciclos->count() }}</div>
                    <div class="pg-stat-lbl">Total Ciclos</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-6 mb-3">
            <div class="pg-stat">
                <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                    <i class="fas fa-graduation-cap" style="color:#16a34a;"></i>
                </div>
                <div>
                    <div class="pg-stat-val">2</div>
                    <div class="pg-stat-lbl">Programas FID</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-6 mb-3">
            <div class="pg-stat">
                <div class="pg-stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                    <i class="fas fa-user-graduate" style="color:#d97706;"></i>
                </div>
                <div>
                    <div class="pg-stat-val">3</div>
                    <div class="pg-stat-lbl">Programas PPD</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Alertas ── --}}
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Acordeón por programa ── --}}
    @php
        $grupos = [
            1 => ['nombre' => 'Programa Inicial',       'tipo' => 'fid'],
            2 => ['nombre' => 'Programa Primaria EIB',  'tipo' => 'fid'],
            3 => ['nombre' => 'Programa Inicial PPD',   'tipo' => 'ppd'],
            4 => ['nombre' => 'Programa Primaria PPD',  'tipo' => 'ppd'],
            5 => ['nombre' => 'Otro Programa',          'tipo' => 'ppd'],
        ];
    @endphp

    @foreach ($grupos as $programaId => $grupo)
        @php
            $esFid       = $grupo['tipo'] === 'fid';
            $accentCls   = $esFid ? 'pg-top-blue'  : 'pg-top-amber';
            $cardCls     = $esFid ? 'pg-card-fid'  : 'pg-card-ppd';
            $iconCls     = $esFid ? 'pg-icon-blue' : 'pg-icon-amber';
            $faIcon      = $esFid ? 'fa-graduation-cap' : 'fa-user-graduate';
            $ciclosGrupo = $ciclos->filter(fn($c) => $c->programa->id == $programaId);
        @endphp

        <div class="mb-3" style="border:1px solid var(--pg-border);border-radius:.85rem;overflow:hidden;box-shadow:var(--pg-shadow);">

            {{-- Header acordeón --}}
            <a class="pg-acc-toggle collapsed" data-toggle="collapse"
               href="#group{{ $programaId }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center" style="gap:.75rem;">
                    <div class="pg-icon {{ $iconCls }}" style="margin-bottom:0; flex-shrink:0;">
                        <i class="fas {{ $faIcon }}"></i>
                    </div>
                    <div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--pg-text);">
                            {{ $grupo['nombre'] }}
                        </div>
                        <div style="font-size:.7rem;color:var(--pg-muted);">
                            {{ $ciclosGrupo->count() }} {{ $ciclosGrupo->count() == 1 ? 'ciclo' : 'ciclos' }} registrados
                        </div>
                    </div>
                </div>
                <i class="fas fa-chevron-down pg-acc-chevron"></i>
            </a>

            {{-- Cuerpo acordeón --}}
            <div id="group{{ $programaId }}" class="collapse">
                <div class="pg-acc-body">
                    @if ($ciclosGrupo->isEmpty())
                        <div class="pg-empty" style="padding:1.75rem 1rem;">
                            <i class="fas fa-inbox mb-2" style="opacity:.25;font-size:1.6rem;"></i>
                            <p class="mb-0" style="font-size:.82rem;">Sin ciclos para este programa</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach ($ciclos as $ciclo)
                                @if ($ciclo->programa->id == $programaId)
                                    @php
                                        $cnt = in_array($programaId, [3,4,5])
                                            ? $ciclo->alumnos_b_validos_count
                                            : $ciclo->alumnos_validos_count;
                                    @endphp
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                                        <div class="pg-card {{ $cardCls }}" style="opacity:1;">
                                            <div class="pg-card-top {{ $accentCls }}"></div>
                                            <div class="pg-card-body">
                                                <div class="pg-card-name" style="font-size:.82rem;margin-bottom:.55rem;">
                                                    {{ $ciclo->nombre }}
                                                </div>
                                                <div style="font-size:.72rem;color:var(--pg-muted);margin-bottom:.7rem;">
                                                    <i class="fas fa-users fa-xs mr-1"></i>{{ $cnt }} alumnos
                                                </div>
                                                <div class="d-flex flex-wrap" style="gap:.3rem;">
                                                    <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}"
                                                       class="pg-btn pg-btn-view">
                                                        <i class="fas fa-eye fa-xs"></i> Ver
                                                    </a>
                                                    <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}"
                                                       class="pg-btn pg-btn-edit">
                                                        <i class="fas fa-edit fa-xs"></i>
                                                    </a>
                                                    <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('¿Eliminar este ciclo?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="pg-btn pg-btn-del">
                                                            <i class="fas fa-trash fa-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    @endforeach

</div>
</div>

{{-- ── GSAP ── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    tl.fromTo('#pg-hero', { opacity: 0, y: -20, scale: .98 }, { opacity: 1, y: 0, scale: 1, duration: .5 })
      .fromTo('.pg-stat', { opacity: 0, y: 18, scale: .93 }, { opacity: 1, y: 0, scale: 1, duration: .35, stagger: .08 }, '-=.18');
});
</script>

@endsection
