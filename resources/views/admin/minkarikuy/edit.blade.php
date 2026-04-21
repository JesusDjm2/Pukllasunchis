@extends('layouts.admin')
@section('titulo', 'Editar Mink\'arikuy')
@section('contenido')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
        <h4 class="h4 mb-0 text-gray-800"><i class="fas fa-qrcode mr-2"></i>Editar Mink'arikuy</h4>
        <a href="{{ route('admin.minkarikuy.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.minkarikuy.update', $minkarikuy) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $minkarikuy->nombre) }}" required maxlength="200">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha">Fecha <span class="text-danger">*</span></label>
                            <input type="date" id="fecha" name="fecha"
                                class="form-control @error('fecha') is-invalid @enderror"
                                value="{{ old('fecha', $minkarikuy->fecha->format('Y-m-d')) }}" required>
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora">Hora <span class="text-danger">*</span></label>
                            <input type="time" id="hora" name="hora"
                                class="form-control @error('hora') is-invalid @enderror"
                                value="{{ old('hora', \Carbon\Carbon::createFromFormat('H:i:s', $minkarikuy->hora)->format('H:i')) }}"
                                required>
                            @error('hora')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen / QR</label>
                    @if ($minkarikuy->imagen)
                        <div class="mb-2">
                            <img src="{{ asset('img/minkarikuy/'.$minkarikuy->imagen) }}"
                                alt="QR actual" height="100" class="rounded border">
                            <small class="d-block text-muted mt-1">Imagen actual. Sube una nueva para reemplazarla.</small>
                        </div>
                    @endif
                    <input type="file" id="imagen" name="imagen"
                        class="form-control-file @error('imagen') is-invalid @enderror"
                        accept="image/*">
                    <small class="text-muted">Opcional. Máx. 4 MB.</small>
                    @error('imagen')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="activo" name="activo"
                            value="1" {{ old('activo', $minkarikuy->activo) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="activo">
                            Activo (visible en la página pública)
                        </label>
                    </div>
                    <small class="text-muted">Solo puede haber un registro activo a la vez.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>
                <a href="{{ route('admin.minkarikuy.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
