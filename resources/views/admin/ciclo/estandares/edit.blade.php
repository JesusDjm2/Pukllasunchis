@extends('layouts.admin')

@section('contenido')
<div class="container-fluid bg-white">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
        <h4 class="text-primary font-weight-bold">Editar Estándar</h4>
        <a href="{{ route('estandares.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right">Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('estandares.update', $estandar) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label font-weight-bold">Descripción:</label>
            <textarea style="height: 120px" class="form-control" name="descripcion" required>{{ $estandar->descripcion }}</textarea>
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
                                <input type="checkbox" name="ciclos[]" value="{{ $ciclo->id }}" class="form-check-input"
                                    {{ in_array($ciclo->id, $estandar->ciclos->pluck('id')->toArray()) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $ciclo->nombre }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label font-weight-bold">Seleccionar Competencias</label>
            <div class="row">
                @foreach($competencias as $competencia)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="competencias[]" value="{{ $competencia->id }}" class="form-check-input"
                                {{ in_array($competencia->id, $estandar->competencias->pluck('id')->toArray()) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $competencia->nombre }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Actualizar</button>
    </form>
</div>
@endsection
