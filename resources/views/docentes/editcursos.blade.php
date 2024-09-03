@extends('layouts.admin')
@section('contenido')
    <div class="container">
        <h2>Editar Cursos Asignados para {{ $docente->nombre }}</h2>
        <form action="{{ route('docente.updateCursos', ['id' => $docente->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="cursos">Seleccionar Cursos:</label>
                <select name="cursos[]" id="cursos" class="form-control" multiple>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ $docente->cursos->contains($curso) ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('cursos')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Cursos</button>
            <a href="{{ route('docente.show', ['id' => $docente->id]) }}" class="btn btn-secondary">Volver</a>
        </form>
    </div>
@endsection