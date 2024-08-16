@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="mb-0 text-gray-800">Crear Ciclo</h4>
            <a href="{{ route('ciclo.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <form method="POST" action="{{ route('ciclo.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="programa_id">Programa:</label>
                        <select class="form-control" id="programa_id" name="programa_id" required>
                            <option selected disabled>Seleccionar Programa</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre del Ciclo:</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre"
                            name="nombre" value="{{ old('nombre', $ciclo->nombre ?? '') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Crear Programa</button>
                </form>
            </div>
        </div>
    </div>
@endsection
