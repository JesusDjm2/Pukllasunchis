@extends('layouts.docente')

@section('contenido')
    <div class="container">
        <h1>Editar Docente</h1>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('docente.update', $docente->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" class="form-control"
                    value="{{ old('nombre', $docente->nombre) }}" readonly>
            </div>

            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" name="dni" id="dni" class="form-control"
                    value="{{ old('dni', $docente->dni) }}" readonly>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control"
                    value="{{ old('email', $docente->email) }}" readonly>
            </div>

            <div class="form-group">
                <label for="foto">Foto (opcional):</label>
                <input type="file" name="foto" id="foto" class="form-control">

                <!-- Preview current photo if exists -->
                @if ($docente->foto)
                    <div class="mt-2">
                        <label>Foto actual:</label>
                        <img src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto actual" class="img-thumbnail mt-2" style="max-height: 200px;">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción (opcional):</label>
                <textarea name="descripcion" id="descripcion" class="form-control">{{ old('descripcion', $docente->descripcion) }}</textarea>
            </div>

            <div class="form-group">
                <label for="password">Nueva Contraseña (opcional):</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Nueva contraseña">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña (opcional):</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                    placeholder="Confirmar contraseña">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection
