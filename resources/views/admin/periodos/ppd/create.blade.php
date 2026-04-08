@extends('layouts.admin')
@section('contenido')
    <div class="container bg-white p-4">
        <h4 class="text-primary font-weight-bold mb-4">Crear Período PPD correcto</h4>
        <form action="{{ route('periodos.admin.ppd.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre') }}" required>
                @error('nombre')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Calendario (Imagen o PDF):</label>
                <input type="file" name="calendario"
                    class="form-control form-control-sm @error('calendario') is-invalid @enderror" accept="image/*,.pdf">
                @error('calendario')
                    <span class="text-danger d-block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Fecha Inicio --}}
            <div class="form-group">
                <label>Fecha Inicio:</label>
                <input type="date" name="fecha_inicio"
                    class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                    value="{{ old('fecha_inicio') }}">
                @error('fecha_inicio')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Fecha Cierre --}}
            <div class="form-group">
                <label>Fecha Cierre:</label>
                <input type="date" name="fecha_cierre"
                    class="form-control form-control-sm @error('fecha_cierre') is-invalid @enderror"
                    value="{{ old('fecha_cierre') }}">
                @error('fecha_cierre')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
            {{-- Actual --}}
            <div class="form-check mb-3">
                <input type="hidden" name="actual" value="0">
                <input type="checkbox" name="actual" value="1"
                    class="form-check-input @error('actual') is-invalid @enderror" id="actualCheck"
                    {{ old('actual') ? 'checked' : '' }}>
                <label class="form-check-label" for="actualCheck">
                    Marcar como período actual
                </label>
                @error('actual')
                    <span class="text-danger d-block">{{ $message }}</span>
                @enderror
            </div>

            <button class="btn btn-primary">Guardar</button>
            <a href="javascript:history.back()" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>
@endsection
