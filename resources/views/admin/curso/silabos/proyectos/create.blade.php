@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
            <h3 class="mb-0 font-weight-bold text-primary">Crear Nuevo Proyecto</h3>
            <a href="{{ route('proyectos.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <form action="{{ route('proyectos.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <label for="nombre">Nombre del Proyecto:</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required>
                </div>
                <div class="col-lg-12 mb-3">
                    <label for="producto">Producto del proyecto:</label>
                    <input type="text" name="producto" class="form-control form-control-sm" required>
                </div>
                <div class="col-lg-12 mb-3">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" class="form-control form-control-sm" required></textarea>
                </div>

                <!-- Relación con Ciclos (Checkboxes) -->
                <div class="col-lg-12 mb-3">
                    <label>Asociar ciclos:</label>
                    <div class="border rounded p-2">
                        @foreach($ciclos as $programa => $ciclosGrupo)
                            <strong class="d-block mt-2">{{ $programa }}</strong> <!-- Nombre del Programa -->
                            @foreach($ciclosGrupo as $ciclo)
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" 
                                        name="ciclos[]" 
                                        value="{{ $ciclo->id }}" 
                                        id="ciclo_{{ $ciclo->id }}"
                                        class="form-check-input">
                                    <label class="form-check-label" for="ciclo_{{ $ciclo->id }}">
                                        {{ $ciclo->nombre }}
                                    </label>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

            </div>
            <button type="submit" class="mt-3 mb-3 btn btn-sm btn-primary">Guardar Proyecto</button>
        </form>
    </div>
@endsection
