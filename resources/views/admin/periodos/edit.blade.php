@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="mb-0 text-primary font-weight-bold">Editar Período Actual</h4>
            <a href="{{ route('periodoactual.index') }}" class="btn btn-sm btn-danger shadow-sm">Volver</a>
        </div>

        <form action="{{ route('periodoactual.update', $periodoactual->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6">
                    <label for="nombre">Nombre del período</label>
                    <input type="text" name="nombre" class="form-control form-control-sm"
                        value="{{ old('nombre', $periodoactual->nombre) }}" required>
                </div>
                <div class="col-lg-6">
                    <label for="horario">Imagen del Horario (JPG, PNG, WEBP - máx. 4MB)</label>
                    <input type="file" name="horario" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp">
                    @if ($periodoactual->horario)
                        <div class="mt-2">
                            <p>Imagen actual:</p>
                            <img src="{{ asset($periodoactual->horario) }}" alt="Horario actual" class="img-fluid rounded"
                                width="200">
                        </div>
                    @endif
                    @error('horario')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-lg-6 mt-3">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                            value="{{ old('fecha_inicio', $periodoactual->fecha_inicio) }}">
                    </div>
                </div>
                <div class="col-lg-6 mt-3">
                    <div class="form-group">
                        <label for="fecha_cierre">Fecha de Cierre</label>
                        <input type="date" name="fecha_cierre" class="form-control form-control-sm"
                            value="{{ old('fecha_cierre', $periodoactual->fecha_cierre) }}">
                    </div>
                </div>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="actual" id="actual" value="1" class="form-check-input"
                    {{ $periodoactual->actual ? 'checked' : '' }}>
                <label for="actual" class="form-check-label">Marcar como período actual</label>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection
