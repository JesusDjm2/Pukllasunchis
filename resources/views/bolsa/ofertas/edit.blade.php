@extends(auth()->check() && auth()->user()->hasRole('admin') ? 'layouts.admin' : 'layouts.bolsa')
@section('titulo')
    <title>Editar oferta — Bolsa de trabajo</title>
@endsection
@section('contenido')
    <div class="container-fluid bg-white pt-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <h4 class="text-primary font-weight-bold mb-0">Editar registro</h4>
            <a href="{{ route('bolsa-trabajo.ofertas.index', request()->only(['anio', 'mes'])) }}"
                class="btn btn-sm btn-secondary">Volver</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bolsa-trabajo.ofertas.update', $oferta) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if (request()->filled('anio'))
                <input type="hidden" name="anio" value="{{ request('anio') }}">
            @endif
            @if (request()->filled('mes'))
                <input type="hidden" name="mes" value="{{ request('mes') }}">
            @endif

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control form-control-sm" required
                    value="{{ old('nombre', $oferta->nombre) }}">
            </div>

            <div class="form-group">
                <label for="detalles">Detalles</label>
                <textarea name="detalles" id="detalles" class="form-control" rows="12">{{ old('detalles', $oferta->detalles) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_inicio">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" required
                        value="{{ old('fecha_inicio', $oferta->fecha_inicio?->format('Y-m-d')) }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="fecha_fin">Fecha fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" required
                        value="{{ old('fecha_fin', $oferta->fecha_fin?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen (JPG, PNG, WEBP — opcional, reemplaza la actual)</label>
                <input type="file" name="imagen" id="imagen" class="form-control-file form-control-sm"
                    accept=".jpg,.jpeg,.png,.webp">
                @if ($oferta->imagen)
                    <div class="mt-2">
                        <small class="text-muted d-block">Imagen actual:</small>
                        <img src="{{ asset($oferta->imagen) }}" alt="" class="img-fluid rounded" style="max-height: 160px;">
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#detalles',
            height: 360,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
            language: 'es',
            branding: false
        });
    </script>
@endsection
