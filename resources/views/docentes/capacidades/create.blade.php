@extends('layouts.admin')
@section('contenido')
    <div class="container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="font-weight-bold text-primary">Crear Nueva Capacidad</h4>
            <a href="{{ route('capacidades.index') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Volver 
            </a>
        </div>

        <form action="{{ route('capacidades.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="competencia_id">Competencia</label>
                <select name="competencia_id" class="form-control form-control-sm" required>
                    <option value="">Seleccione una competencia</option>
                    @foreach ($competencias as $competencia)
                        <option value="{{ $competencia->id }}">{{ $competencia->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Crear</button>
        </form>
    </div>
@endsection
