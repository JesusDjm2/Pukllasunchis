@extends('layouts.admin')
@section('titulo', 'Periodos')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <!-- Título -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-primary">
                Periodos registrados <small> ({{ $periodos->count() }})</small>
            </h1>
            <a href="{{ route('periodos.create') }}" class="btn btn-sm btn-primary shadow-sm">
                Nuevo Periodo <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>

        <!-- Alertas -->
        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Botones con los nombres de los períodos -->
        <div class="row">
            @forelse ($periodos as $periodo)
                <div class="col-md-3 mb-3">
                    <a href="{{ route('periodos.show', ['nombre' => $periodo->nombre]) }}" class="btn btn-block btn-outline-primary">
                        {{ $periodo->nombre }}
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center">No hay períodos registrados</p>
                </div>
            @endforelse
        </div>       
    </div>
@endsection
