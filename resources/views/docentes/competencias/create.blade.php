@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="font-weight-bold text-primary">Crear Nueva Competencia</h4>
            <a href="{{ route('competencias.index') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Volver 
            </a>
        </div>
        <form action="{{ route('competencias.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" class="form-control form-control-sm" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm"></textarea>
            </div>
            <div class="form-group">
                <label for="capacidades">Capacidades</label>
                <textarea name="capacidades" class="form-control form-control-sm" id="capacidades-editor"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.2/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('capacidades-editor');        
    </script>
@endsection
