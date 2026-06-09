@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

@include('admin._partials.prog-styles')

<div class="pg-wrap">
<div class="container-fluid py-3">

    {{-- ── Hero ── --}}
    <div class="pg-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap"
             style="gap:1rem; position:relative; z-index:1;">
            <div>
                <div class="pg-hero-label">
                    <i class="fas fa-university"></i> Programas
                </div>
                <h4><i class="fas fa-edit mr-2"></i>Editar Programa</h4>
                <p class="pg-hero-sub">{{ $programa->nombre }}</p>
            </div>
            <a href="{{ route('programa.index') }}" class="pg-hero-btn align-self-start">
                <i class="fas fa-arrow-left fa-xs"></i> Volver
            </a>
        </div>
    </div>

    {{-- ── Alertas ── --}}
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            @foreach ($errors->all() as $error) {{ $error }} @endforeach
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Formulario ── --}}
    <div class="pg-form-card">
        <div class="pg-form-head">
            <i class="fas fa-pen"></i> Datos del programa
        </div>
        <div class="pg-form-body">
            <form method="POST" action="{{ route('programa.update', ['programa' => $programa->id]) }}">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="pg-label" for="nombre">Nombre del Programa</label>
                    <input type="text" id="nombre" name="nombre"
                           class="pg-input @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $programa->nombre) }}"
                           required autofocus>
                    @error('nombre')
                        <div class="invalid-feedback d-block mt-1" style="font-size:.78rem;">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="pg-submit">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
                </button>
            </form>
        </div>
    </div>

</div>
</div>
@endsection
