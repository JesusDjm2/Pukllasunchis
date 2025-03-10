@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2" style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-0 font-weight-bold text-primary">Programas EESP Pukllasunchis</h3>
            <a href="{{ route('programa.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Crear nuevo Programa <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>
        <div class="row bg-white pt-4 pb-4">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
            {{-- @foreach ($programas as $programa)
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3 font-weight-bold">{{ $programa->nombre }}</h4>
                            <a href="{{ route('programa.edit', ['programa' => $programa->id]) }}"
                                class="btn btn-primary btn-sm" title="Editar">
                                <i class="fa fa-edit fa-sm"></i> Editar
                            </a> |
                            <a href="{{ route('programa.show', ['programa' => $programa->id]) }}"
                                class="btn btn-info btn-sm" title="Ver registro completo">
                                <i class="fa fa-eye fa-sm"></i> Mostrar
                            </a> |
                            <form action="{{ route('programa.destroy', ['programa' => $programa->id]) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fa fa-trash fa-sm"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>                
            @endforeach --}}
        </div>

        <div class="row">
            {{-- Formación Regular (FID) --}}
            <div class="col-12 mb-3">
                <h3>Formación Regular (FID)</h3>
                <div class="bg-primary" style="height: 3px; width: 40px;"></div>
            </div>
            @foreach ($programas as $programa)
                @if (in_array($programa->id, [1, 2]))
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3 font-weight-bold text-primary">{{ $programa->nombre }}</h4>
                                <a href="{{ route('programa.edit', ['programa' => $programa->id]) }}"
                                    class="btn btn-primary btn-sm" title="Editar">
                                    <i class="fa fa-edit fa-sm"></i> Editar
                                </a> |
                                <a href="{{ route('programa.show', ['programa' => $programa->id]) }}"
                                    class="btn btn-info btn-sm" title="Ver registro completo">
                                    <i class="fa fa-eye fa-sm"></i> Mostrar
                                </a> |
                                <form action="{{ route('programa.destroy', ['programa' => $programa->id]) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa fa-trash fa-sm"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
        <div class="row mt-4">
            {{-- Profesionalización Docente (PPD) --}}
            <div class="col-12 mb-3">
                <h3>Profesionalización Docente (PPD)</h3>
                <div class="bg-primary" style="height: 3px; width: 40px;"></div>
            </div>
            @foreach ($programas as $programa)
                @if (in_array($programa->id, [3, 4, 5]))
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3 font-weight-bold text-primary">{{ $programa->nombre }}</h4>
                                <a href="{{ route('programa.edit', ['programa' => $programa->id]) }}"
                                    class="btn btn-primary btn-sm" title="Editar">
                                    <i class="fa fa-edit fa-sm"></i> Editar
                                </a> |
                                <a href="{{ route('programa.show', ['programa' => $programa->id]) }}"
                                    class="btn btn-info btn-sm" title="Ver registro completo">
                                    <i class="fa fa-eye fa-sm"></i> Mostrar
                                </a> |
                                <form action="{{ route('programa.destroy', ['programa' => $programa->id]) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa fa-trash fa-sm"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
    </div>
@endsection
