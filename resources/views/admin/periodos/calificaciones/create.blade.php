@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-plus-circle mr-2" style="color:#4e73df;"></i>Crear Período de Calificaciones
                </h4>
                <small class="text-muted">Registra un nuevo período de evaluación académica</small>
            </div>
            <a href="{{ route('periodos.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
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

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <form method="POST" action="{{ route('periodos.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre del Período:</label>
                        <input type="text" class="form-control form-control-sm @error('nombre') is-invalid @enderror" id="nombre"
                            name="nombre" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Generar Periodo</button>
                </form>
            </div>
        </div>
    </div>
@endsection
