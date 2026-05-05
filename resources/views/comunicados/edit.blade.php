@extends('layouts.admin')
@section('titulo', 'Editar comunicado')
@section('contenido')
    <div class="container-fluid bg-white pt-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <h4 class="text-primary font-weight-bold mb-0">Editar comunicado</h4>
            <a href="{{ route('admin.comunicados.index') }}" class="btn btn-sm btn-secondary">Volver</a>
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

        <form action="{{ route('admin.comunicados.update', $comunicado) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="titulo">Titulo</label>
                    <input type="text" name="titulo" id="titulo" class="form-control form-control-sm"
                        value="{{ old('titulo', $comunicado->titulo) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="fecha_publicacion">Fecha de publicación</label>
                    <input type="date" name="fecha_publicacion" id="fecha_publicacion"
                        class="form-control form-control-sm"
                        value="{{ old('fecha_publicacion', $comunicado->fecha_publicacion?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="archivo">Archivo (imagen o PDF)</label>
                    <input type="file" name="archivo" id="archivo" class="form-control-file form-control-sm"
                        accept=".jpg,.jpeg,.png,.webp,.pdf">
                </div>
            </div>

            <div class="form-group mb-2">
                <label for="descripcion">Descripción (opcional)</label>
                <textarea name="descripcion" id="descripcion" class="form-control form-control-sm" rows="3">{{ old('descripcion', $comunicado->descripcion) }}</textarea>
            </div>

            <div class="mb-3">
                <small class="text-muted d-block">Archivo actual:</small>
                @if ($comunicado->esImagen())
                    <img src="{{ asset($comunicado->archivo) }}" alt="" class="img-fluid rounded border mt-1"
                        style="max-height: 180px;">
                @else
                    <a href="{{ asset($comunicado->archivo) }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-outline-danger btn-sm mt-1">
                        <i class="fas fa-file-pdf"></i> Ver PDF actual
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
        </form>
    </div>
@endsection
