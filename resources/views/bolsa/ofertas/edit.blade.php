@php
    $__u = auth()->user();
    $__layout = $__u && $__u->hasRole("admin")
        ? "layouts.admin"
        : ($__u && $__u->hasRole("docente")
            ? "layouts.docente"
            : "layouts.bolsa");
@endphp
@extends($__layout)
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

            <div class="form-group">
                <label for="fecha_publicacion">Fecha de publicación</label>
                <input type="date" name="fecha_publicacion" id="fecha_publicacion"
                    class="form-control form-control-sm" required
                    value="{{ old('fecha_publicacion', optional($oferta->fecha_publicacion)->format('Y-m-d')) }}">
                <small class="form-text text-muted">
                    La oferta se muestra en la página pública hasta {{ \App\Models\BolsaTrabajoOferta::DIAS_VIGENCIA }}
                    días después de esta fecha (hasta el
                    {{ optional($oferta->fechaLimiteVigencia())->locale('es')->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}).
                    Estado actual:
                    <span class="badge {{ $oferta->vigente ? 'badge-success' : 'badge-secondary' }}">
                        {{ $oferta->vigente ? 'Vigente' : 'No vigente' }}
                    </span>
                </small>
            </div>

            <div class="form-group">
                <label for="nombre_publicador">Nombre de quién publica</label>
                <input type="text" name="nombre_publicador" id="nombre_publicador" class="form-control form-control-sm"
                    required value="{{ old('nombre_publicador', $oferta->nombre_publicador) }}">
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="telefono_publicador">Teléfono</label>
                    <input type="text" name="telefono_publicador" id="telefono_publicador"
                        class="form-control form-control-sm"
                        value="{{ old('telefono_publicador', $oferta->telefono_publicador) }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="relacion_publicador">Relación con la institución</label>
                    <select name="relacion_publicador" id="relacion_publicador" class="form-control form-control-sm">
                        <option value="">Seleccionar...</option>
                        @foreach (['Soy de la Asociación Pukllasunchis', 'Alumno de la EESPPP', 'Externo a la Asociación'] as $opcion)
                            <option value="{{ $opcion }}"
                                {{ old('relacion_publicador', $oferta->relacion_publicador) === $opcion ? 'selected' : '' }}>
                                {{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="numero_correo">N° de correo/oficio</label>
                <input type="text" name="numero_correo" id="numero_correo" class="form-control form-control-sm"
                    value="{{ old('numero_correo', $oferta->numero_correo) }}">
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
