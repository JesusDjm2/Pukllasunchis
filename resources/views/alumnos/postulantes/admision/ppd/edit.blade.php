@extends('layouts.admin')

@section('contenido')
    <div class="container bg-white p-4">
        <h4 class="text-primary font-weight-bold mb-4">
            Editar Proceso Admisión PPD
        </h4>

        <form action="{{ route('admin.ppd.update', $admin_ppd) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre', $admin_ppd->nombre) }}" required>
                @error('nombre')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Año --}}
            <div class="form-group">
                <label>Año</label>
                <input type="number" name="anio"
                    class="form-control form-control-sm @error('anio') is-invalid @enderror"
                    value="{{ old('anio', $admin_ppd->anio) }}" required>
                @error('anio')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                    value="{{ old('fecha_inicio', $admin_ppd->fecha_inicio) }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                    value="{{ old('fecha_fin', $admin_ppd->fecha_fin) }}">
            </div>

            <div class="form-check mb-3">
                <input type="hidden" name="estado" value="0">
                <input type="checkbox" name="estado" value="1" class="form-check-input" id="estadoCheck"
                    {{ old('estado', $admin_ppd->estado) ? 'checked' : '' }}>
                <label class="form-check-label" for="estadoCheck">
                    Proceso activo
                </label>
            </div>

            <button class="btn btn-primary">Actualizar</button>
            <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>
@endsection
