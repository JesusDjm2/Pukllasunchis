@extends('layouts.admin')

@section('contenido')
<div class="container-fluid bg-white pt-3 pb-3">
    <h3 class="text-primary font-weight-bold mb-3">Editar Periodo</h3>

    <form action="{{ route('admin-fids.update', $adminFid) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <label>Nombre</label>
                <input 
                    type="text" 
                    name="nombre" 
                    class="form-control @error('nombre') is-invalid @enderror" 
                    value="{{ old('nombre', $adminFid->nombre) }}" 
                    required
                >
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label>Año</label>
                <input 
                    type="number" 
                    name="anio" 
                    class="form-control @error('anio') is-invalid @enderror" 
                    value="{{ old('anio', $adminFid->anio) }}" 
                    required
                >
                @error('anio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label>Fecha de inicio</label>
                <input 
                    type="date" 
                    name="fecha_inicio" 
                    class="form-control @error('fecha_inicio') is-invalid @enderror" 
                    value="{{ old('fecha_inicio', $adminFid->fecha_inicio) }}"
                >
                @error('fecha_inicio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label>Fecha de fin</label>
                <input 
                    type="date" 
                    name="fecha_fin" 
                    class="form-control @error('fecha_fin') is-invalid @enderror" 
                    value="{{ old('fecha_fin', $adminFid->fecha_fin) }}"
                >
                @error('fecha_fin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-12 mb-3 ml-3">
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        name="estado" 
                        value="1" 
                        class="form-check-input" 
                        id="estado"
                        {{ old('estado', $adminFid->estado) ? 'checked' : '' }}
                    >
                    <label for="estado" class="form-check-label">Periodo activo</label>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
