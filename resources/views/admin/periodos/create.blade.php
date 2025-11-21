@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="mb-0 text-primary font-weight-bold">Crear Período Actual</h4>
            <a href="{{ route('periodoactual.index') }}" class="btn btn-sm btn-danger shadow-sm">Volver</a>
        </div>

        <form action="{{ route('periodoactual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="nombre">Nombre del período</label>
                        <input type="text" name="nombre" class="form-control form-control-sm"
                            value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                            value="{{ old('fecha_inicio') }}">
                        @error('fecha_inicio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="fecha_cierre">Fecha de Cierre</label>
                        <input type="date" name="fecha_cierre" class="form-control form-control-sm"
                            value="{{ old('fecha_cierre') }}">
                        @error('fecha_cierre')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="imagen">Imagen del Horario (JPG, PNG, WEBP - máx. 4MB)</label>
                        <input type="file" name="imagen" class="form-control form-control-sm"
                            accept=".jpg,.jpeg,.png,.webp">
                        @error('imagen')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-check mb-3">
                        <input type="checkbox" name="actual" id="actual" value="1" class="form-check-input"
                            {{ old('actual') ? 'checked' : '' }}>
                        <label for="actual" class="form-check-label">Marcar como período actual</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary ml-2">Guardar</button>
            </div>
        </form>

    </div>
@endsection
