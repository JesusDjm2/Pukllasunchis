@extends('layouts.admin')
@section('contenido')
<div class="container-fluid bg-white">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
        <h4 class="mb-0 text-primary font-weight-bold">Editar Período Actual</h4>
        <a href="{{ route('periodoactual.index') }}" class="btn btn-sm btn-danger shadow-sm">Volver</a>
    </div>

    <form action="{{ route('periodoactual.update', $periodoactual->id) }}" method="POST">
    @csrf
    @method('PUT')
        <div class="form-group">
            <label for="nombre">Nombre del período</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $periodoactual->nombre) }}" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="actual" id="actual" value="1" class="form-check-input" {{ $periodoactual->actual ? 'checked' : '' }}>
            <label for="actual" class="form-check-label">Marcar como período actual</label>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
