@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="mb-0 text-gray-800">Editar Programa</h4>
            <a href="{{ route('programa.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
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
                <form method="POST" action="{{ route('programa.update', ['programa' => $programa->id]) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="nombre">Nombre del Programa:</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre"
                            name="nombre" value="{{ old('nombre', $programa->nombre ?? '') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Programa</button>
                </form>
            </div>
        </div>
    </div>
@endsection
