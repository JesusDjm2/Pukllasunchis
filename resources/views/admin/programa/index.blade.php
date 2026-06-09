@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

@include('admin._partials.prog-styles')

<div class="pg-wrap">
    <div class="container-fluid py-3">

        {{-- ── Hero ── --}}
        <div class="pg-hero" id="prog-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap"
                style="gap:1rem; position:relative; z-index:1;">
                <div>
                    <div class="pg-hero-label">
                        <i class="fas fa-university"></i> EESP Pukllasunchis
                    </div>
                    <h4>Programas de Estudios</h4>
                    <p class="pg-hero-sub">
                        <i class="fas fa-graduation-cap mr-1"></i> Formación Inicial Docente (FID)
                        &nbsp;&middot;&nbsp;
                        <i class="fas fa-user-graduate mr-1"></i> Profesionalización Docente (PPD)
                    </p>
                </div>
                <a href="{{ route('programa.create') }}" class="pg-hero-btn align-self-start">
                    <i class="fa fa-plus fa-xs"></i> Nuevo Programa
                </a>
            </div>
        </div>

        {{-- ── Alerta éxito ── --}}
        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- ── Sección FID ── --}}
        <div class="pg-section-lbl" id="lbl-fid">
            <i class="fas fa-graduation-cap" style="color:#2563eb;"></i>
            Formación Inicial Docente — FID
        </div>
        <div class="row mb-4" id="grid-fid">
            @foreach ($programas as $programa)
                @if (in_array($programa->id, [1, 2]))
                    <div class="col-lg-4 col-md-6 mb-3 d-flex">
                        <div class="pg-card pg-card-fid w-100 h-100">
                            <div class="pg-card-top pg-top-blue"></div>
                            <div class="pg-card-body">
                                <div class="pg-icon pg-icon-blue">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="pg-card-name">{{ $programa->nombre }}</div>
                                <div class="d-flex flex-wrap" style="gap:.4rem;">
                                    <a href="{{ route('programa.show', ['programa' => $programa->id]) }}"
                                        class="pg-btn pg-btn-view">
                                        <i class="fas fa-layer-group fa-xs"></i> Ver ciclos
                                    </a>
                                    <a href="{{ route('programa.edit', ['programa' => $programa->id]) }}"
                                        class="pg-btn pg-btn-edit">
                                        <i class="fas fa-edit fa-xs"></i> Editar
                                    </a>
                                    <form action="{{ route('programa.destroy', ['programa' => $programa->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este programa? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="pg-btn pg-btn-del">
                                            <i class="fas fa-trash fa-xs"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- ── Sección PPD ── --}}
        <div class="pg-section-lbl" id="lbl-ppd">
            <i class="fas fa-user-graduate" style="color:#f59e0b;"></i>
            Profesionalización Docente — PPD
        </div>
        <div class="row" id="grid-ppd">
            @foreach ($programas as $programa)
                @if (in_array($programa->id, [3, 4, 5]))
                    <div class="col-lg-4 col-md-6 mb-3 d-flex">
                        <div class="pg-card pg-card-ppd w-100 h-100">
                            <div class="pg-card-top pg-top-amber"></div>
                            <div class="pg-card-body">
                                <div class="pg-icon pg-icon-amber">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="pg-card-name">{{ $programa->nombre }}</div>
                                <div class="d-flex flex-wrap" style="gap:.4rem;">
                                    <a href="{{ route('programa.show', ['programa' => $programa->id]) }}"
                                        class="pg-btn pg-btn-view">
                                        <i class="fas fa-layer-group fa-xs"></i> Ver ciclos
                                    </a>
                                    <a href="{{ route('programa.edit', ['programa' => $programa->id]) }}"
                                        class="pg-btn pg-btn-edit">
                                        <i class="fas fa-edit fa-xs"></i> Editar
                                    </a>
                                    <form action="{{ route('programa.destroy', ['programa' => $programa->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este programa? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="pg-btn pg-btn-del">
                                            <i class="fas fa-trash fa-xs"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

    </div>
</div>

{{-- ── GSAP ── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.fromTo('#prog-hero', { opacity: 0, y: -20, scale: .98 }, { opacity: 1, y: 0, scale: 1, duration: .5 })
          .fromTo('#lbl-fid',  { opacity: 0, x: -12 }, { opacity: 1, x: 0, duration: .3 }, '-=.1')
          .fromTo('#grid-fid .pg-card', { opacity: 0, y: 22, scale: .93 },
            { opacity: 1, y: 0, scale: 1, duration: .38, stagger: .1, ease: 'back.out(1.3)' }, '-=.1')
          .fromTo('#lbl-ppd',  { opacity: 0, x: -12 }, { opacity: 1, x: 0, duration: .3 }, '-=.05')
          .fromTo('#grid-ppd .pg-card', { opacity: 0, y: 22, scale: .93 },
            { opacity: 1, y: 0, scale: 1, duration: .38, stagger: .1, ease: 'back.out(1.3)' }, '-=.1');
    });
</script>

@endsection
