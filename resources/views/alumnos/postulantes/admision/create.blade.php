@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white pt-3 pb-3">
        <h3 class="text-primary font-weight-bold mb-3">Nuevo Periodo de Admisión</h3>

        {{-- Mostrar mensaje de éxito --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Mostrar errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin-fids.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label>Nombre del periodo</label>
                    <input type="text" name="nombre"
                        class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6 mb-3">
                    <label>Año</label>
                    <input type="number" name="anio"
                        class="form-control form-control-sm @error('anio') is-invalid @enderror"
                        value="{{ old('anio', date('Y')) }}" required>
                    @error('anio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6 mb-3">
                    <label>Fecha de inicio</label>
                    <input type="date" name="fecha_inicio"
                        class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                        value="{{ old('fecha_inicio') }}">
                    @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6 mb-3">
                    <label>Fecha de fin</label>
                    <input type="date" name="fecha_fin"
                        class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror"
                        value="{{ old('fecha_fin') }}">
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3 col-lg-12 ml-3">
                    <input type="checkbox" name="estado" value="1" class="form-check-input" id="estado"
                        {{ old('estado') ? 'checked' : '' }}>
                    <label for="estado" class="form-check-label">Activar este periodo como actual</label>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
