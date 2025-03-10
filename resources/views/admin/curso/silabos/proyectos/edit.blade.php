@extends('layouts.admin')

@section('contenido')
    <style>
        .form-check-inline {
            margin-right: 1em;
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
            <h3 class="mb-0 font-weight-bold text-primary">Editar Proyecto</h3>
            <a href="{{ route('proyectos.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>

        <form action="{{ route('proyectos.update', $proyecto) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Nombre -->
                <div class="col-lg-12 mb-3">
                    <label for="nombre">Nombre del Proyecto:</label>
                    <input type="text" name="nombre"
                        class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $proyecto->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Producto -->
                <div class="col-lg-12 mb-3">
                    <label for="producto">Producto del Proyecto:</label>
                    <input type="text" name="producto"
                        class="form-control form-control-sm @error('producto') is-invalid @enderror"
                        value="{{ old('producto', $proyecto->producto) }}" required>
                    @error('producto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="col-lg-12 mb-3">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" class="form-control form-control-sm @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $proyecto->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Relación con Ciclos (Checkboxes) -->
                <div class="col-lg-12 mb-3">
                    <label>Asociar ciclos:</label>
                    <div class="border rounded p-2">
                        @foreach($ciclos as $programa => $ciclosGrupo)
                            <strong class="d-block mt-2">{{ $programa }}</strong> <!-- Nombre del Programa -->
                            @foreach($ciclosGrupo as $ciclo)
                                <div class="form-check form-check-inline" style="margin-right: 1em;">
                                    <input type="checkbox" 
                                        name="ciclos[]" 
                                        value="{{ $ciclo->id }}" 
                                        id="ciclo_{{ $ciclo->id }}"
                                        class="form-check-input"
                                        {{ in_array($ciclo->id, old('ciclos', $proyecto->ciclos->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ciclo_{{ $ciclo->id }}">
                                        {{ $ciclo->nombre }}
                                    </label>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                    @error('ciclos')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
            </div>

            <button type="submit" class="mt-3 mb-3 btn btn-sm btn-primary">Actualizar Proyecto</button>
        </form>
    </div>
@endsection
