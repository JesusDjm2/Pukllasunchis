@extends('layouts.admin')

@section('contenido')
<div class="container-fluid bg-white">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
        <h4 class="text-primary font-weight-bold">Crear Nuevo Estándar</h4>
        <a href="{{ route('estandares.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right">Volver</a>
    </div>

    <form action="{{ route('estandares.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label font-weight-bold">Descripción:</label>
            <textarea style="height: 120px" class="form-control" name="descripcion" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label font-weight-bold">Seleccionar Ciclos por Programa</label>
            @php
                $ciclosAgrupados = $ciclos->groupBy('programa.nombre');
            @endphp
        
            @foreach($ciclosAgrupados as $programaNombre => $ciclos)
                <div class="card mb-2">
                    <div class="card-header bg-dark text-white">
                        {{ $programaNombre }}
                    </div>
                    <div class="card-body">
                        @foreach($ciclos as $ciclo)
                            <div class="form-check d-inline-block me-4" style="margin-right: 2em;">
                                <input type="checkbox" name="ciclos[]" value="{{ $ciclo->id }}" class="form-check-input">
                                <label class="form-check-label">{{ $ciclo->nombre }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">Seleccionar Competencias</label>
            <div class="row">
                @foreach($competencias as $competencia)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="competencias[]" value="{{ $competencia->id }}" class="form-check-input">
                            <label class="form-check-label">{{ $competencia->nombre }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection
