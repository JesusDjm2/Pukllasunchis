@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Periodos')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <!-- Título -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-star mr-2" style="color:#4e73df;"></i>Períodos de Calificaciones
                    <span class="text-muted" style="font-size:.85rem;">({{ $periodos->count() }})</span>
                </h4>
                <small class="text-muted">Períodos de evaluación académica registrados en el sistema</small>
            </div>
            <a href="{{ route('periodos.create') }}" class="btn btn-sm btn-primary shadow-sm">
                Nuevo Periodo <i class="fa fa-plus fa-sm"></i>
            </a>
            <a href="{{ route('periodos.create') }}" class="btn btn-sm btn-primary shadow-sm">
                Crear calificaciones del Periodo <i class="fa fa-plus fa-sm"></i>
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
