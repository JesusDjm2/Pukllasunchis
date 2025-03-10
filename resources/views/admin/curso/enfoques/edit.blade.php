@extends('layouts.admin')

@section('contenido')
<div class="container-fluid bg-white">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
        <h2 class="mb-0 text-uppercase font-weight-bold text-primary">Editar Enfoque</h2>
    </div>

    <form action="{{ route('enfoques.update', $enfoque->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="curso_id" class="form-label">Curso</label>
                <select name="curso_id" class="form-control">
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ $curso->id == $enfoque->curso_id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ $enfoque->nombre }}" required>
            </div>

            <div class="col-md-12 mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ $enfoque->descripcion }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="observables" class="form-label">Observables</label>
                <textarea name="observables" class="form-control" rows="2">{{ $enfoque->observables }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="concretas" class="form-label">Concretas</label>
                <textarea name="concretas" class="form-control" rows="2">{{ $enfoque->concretas }}</textarea>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('enfoques.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>
</div>
@endsection
