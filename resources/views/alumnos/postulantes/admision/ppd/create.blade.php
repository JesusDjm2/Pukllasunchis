@extends('layouts.admin')
@section('contenido')
    <div class="container bg-white p-4">
        <h4 class="text-primary font-weight-bold mb-4">Crear Nuevo Proceso de admisión PPD</h4>

        <form action="{{ route('admin.ppd.store') }}" method="POST">
            @csrf

            {{-- Nombre --}}
            <div class="form-group mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre') }}" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Año --}}
            <div class="form-group mb-3">
                <label>Año</label>
                <input type="number" name="anio" min="2000" max="2100"
                    class="form-control form-control-sm @error('anio') is-invalid @enderror"
                    value="{{ old('anio', now()->year) }}" required>
                @error('anio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Fecha Inicio --}}
            <div class="form-group mb-3">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio"
                    class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                    value="{{ old('fecha_inicio') }}">
                @error('fecha_inicio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Fecha Fin --}}
            <div class="form-group mb-3">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_cierre"
                    class="form-control form-control-sm @error('fecha_cierre') is-invalid @enderror"
                    value="{{ old('fecha_cierre') }}">
                @error('fecha_cierre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Estado --}}
            <div class="form-check mb-4">
                <input type="hidden" name="estado" value="0">
                <input type="checkbox" name="estado" value="1" class="form-check-input" id="estadoCheck"
                    {{ old('estado') ? 'checked' : '' }}>
                <label class="form-check-label" for="estadoCheck">
                    Proceso activo
                </label>
            </div>

            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
