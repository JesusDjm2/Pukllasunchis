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
                    <div class="row justify-content-center mt-3">
                        <div class="col-md-8">
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <form method="POST" action="{{ route('ciclo.update', $ciclo->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="programa_id">Programa:</label>
                        <select class="form-control" id="programa_id" name="programa_id" required>
                            @foreach($programas as $programaOption)
                                <option value="{{ $programaOption->id }}" {{ $programaOption->id == $ciclo->programa_id ? 'selected' : '' }}>
                                    {{ $programaOption->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>                
                    <div class="form-group">
                        <label for="nombre">Nombre del Ciclo:</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                            value="{{ old('nombre', $ciclo->nombre ?? '') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>                           
                    <button type="submit" class="btn btn-primary">Actualizar Ciclo</button>
                </form>
                
            </div>
        </div>
    </div>
@endsection
