@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

@include('admin._partials.prog-styles')

<div class="pg-wrap">
    <div class="container-fluid py-3">

        {{-- ── Hero ── --}}
        <div class="pg-hero" id="prog-hero">
            <div class="d-flex align-items-start justify-content-between flex-wrap"
                style="gap:1rem; position:relative; z-index:1;">
                <div>
                    <div class="pg-hero-label">
                        <i class="fas fa-university"></i> Programa de estudios
                    </div>
                    <h2>{{ $programa->nombre }}</h2>
                    <p class="pg-hero-sub">
                        <i class="fas fa-layer-group mr-1"></i>
                        {{ $ciclos->count() }} {{ $ciclos->count() == 1 ? 'ciclo registrado' : 'ciclos registrados' }}
                    </p>
                </div>
                <a href="javascript:history.go(-1)" class="pg-hero-btn align-self-start">
                    <i class="fas fa-arrow-left fa-xs"></i> Volver
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
                        <div class="pg-stat-lbl">Registros</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-6 mb-3">
                <div class="pg-stat">
                    <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                        <i class="fas fa-graduation-cap" style="color:#16a34a;"></i>
                    </div>
                    <div>
                        <div class="pg-stat-val" style="font-size:.85rem; word-break:break-word;">
                            {{ Str::limit($programa->nombre, 18) }}</div>
                        <div class="pg-stat-lbl">Programa activo</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-6 mb-3">
                <div class="pg-stat">
                    <div class="pg-stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                        <i class="fas fa-school" style="color:#d97706;"></i>
                    </div>
                    <div>
                        <div class="pg-stat-val" style="font-size:.85rem;">EESP</div>
                        <div class="pg-stat-lbl">Pukllasunchis</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Ciclos ── --}}
        <div class="pg-section-lbl" id="prog-section-lbl">
            <i class="fas fa-layer-group"></i>Registros del Programa
        </div>

        @if ($ciclos->isEmpty())
            <div class="pg-empty">
                <i class="fas fa-inbox fa-3x mb-3" style="opacity:.25;"></i>
                <p class="font-weight-bold mb-1">Sin ciclos registrados</p>
                <small>Aún no se han creado ciclos para este programa.</small>
            </div>
        @else
            <div class="row" id="ciclos-grid">
                @foreach ($ciclos as $i => $ciclo)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                        <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}"
                           class="pg-card pg-card-std d-block text-decoration-none">
                            <div class="pg-card-top pg-top-blue"></div>
                            <div class="pg-card-body-center">
                                <div class="pg-icon pg-icon-blue mx-auto mb-2">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div class="pg-card-name" style="font-size:.82rem;">{{ $ciclo->nombre }}</div>
                                <div class="pg-ciclo-cta">
                                    <i class="fas fa-arrow-right fa-xs"></i> Ver detalle
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ── GSAP Animations ── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.fromTo('#prog-hero', { opacity: 0, y: -20, scale: .98 }, { opacity: 1, y: 0, scale: 1, duration: .5 })
          .fromTo('.pg-stat',   { opacity: 0, y: 20, scale: .93 },  { opacity: 1, y: 0, scale: 1, duration: .38, stagger: .08 }, '-=.18')
          .fromTo('#prog-section-lbl', { opacity: 0, x: -14 }, { opacity: 1, x: 0, duration: .3 }, '-=.12')
          .fromTo('.pg-card',  { opacity: 0, y: 28, scale: .9 },
            { opacity: 1, y: 0, scale: 1, duration: .42, stagger: { amount: .55, from: 'start' }, ease: 'back.out(1.3)' }, '-=.1');
    });
</script>

@endsection
