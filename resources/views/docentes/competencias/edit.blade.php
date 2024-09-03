@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <h4 class="font-weight-bold text-primary">Editar Competencia</h4>
        <form action="{{ route('competencias.update', $competencia->id) }}" method="POST">
            @csrf
            @method('PUT') <!-- Necesario para el método PUT -->

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $competencia->nombre }}" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm">{{ $competencia->descripcion }}</textarea>
            </div>
            <div class="form-group">
                <label for="capacidades">Capacidades</label>
                <textarea name="capacidades" class="form-control form-control-sm" id="capacidades-editor">{{ $competencia->capacidades }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('capacidades-editor');
    </script>
@endsection
