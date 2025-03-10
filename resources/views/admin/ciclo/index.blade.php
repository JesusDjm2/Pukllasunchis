@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4></h4>
            <a href="{{ route('ciclo.create') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Crear nuevo Ciclo <i class="fa fa-plus fa-sm"></i>
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
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h4 class="font-weight-bold text-primary mb-0">Ciclos Programa Inicial:</h4>
                <div style="width: 40px; height: 4px; background-color: #4e73df; border-radius: 5px; margin-bottom: 10px">
                </div>

            </div>
            @foreach ($ciclos as $ciclo)
                @if ($ciclo->programa->id == 1)
                    <div class="col-lg-2">
                        <div class="card mt-2 mb-2 p-2 text-center">
                            <h5 class="mb-4" style="font-family: emoji">
                                <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">{{ $ciclo->nombre }}</a>
                            </h5>
                            <p>{{ $ciclo->alumnos_count }} Alumnos</p>
                            <div class="row">
                                <div class="col-4">
                                    <a class="btn btn-sm btn-info"
                                        href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                        <i class="fa fa-eye fa-sm" title="Ver"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}"
                                        class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fa fa-edit fa-sm"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="col-lg-12 mt-4">
                <h4 class="font-weight-bold text-primary mb-0 mt-4">Ciclos Programa Primaria EIB:</h4>
                <div style="width: 40px; height: 4px; background-color: #4e73df; border-radius: 5px; margin-bottom: 10px">
                </div>
            </div>
            @foreach ($ciclos as $ciclo)
                @if ($ciclo->programa->id == 2)
                    <div class="col-lg-2">
                        <div class="card mt-2 mb-2 p-2 text-center">
                            <h5 class="mb-4" style="font-family: emoji">
                                <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">{{ $ciclo->nombre }}</a>
                            </h5>
                            <p>{{ $ciclo->alumnos_count }} Alumnos</p>
                            <div class="row">
                                <div class="col-4">
                                    <a class="btn btn-sm btn-info"
                                        href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                        <i class="fa fa-eye fa-sm" title="Ver"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}"
                                        class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fa fa-edit fa-sm"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="col-lg-12 mt-4">
                <h4 class="font-weight-bold text-primary mb-0 mt-4">Programa Inicial PPD:</h4>
                <div style="width: 40px; height: 4px; background-color: #4e73df; border-radius: 5px; margin-bottom: 10px">
                </div>
            </div>
            @foreach ($ciclos as $ciclo)               
                @if ($ciclo->programa->id == 3)
                    <div class="col-lg-2">
                        <div class="card mt-2 mb-2 p-2 text-center">
                            <h5 class="mb-4" style="font-family: emoji">
                                <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">{{ $ciclo->nombre }}</a>
                            </h5>
                            <p>{{ $ciclo->alumnosB->count() }} Alumnos</p>
                            <div class="row">
                                <div class="col-4">
                                    <a class="btn btn-sm btn-info"
                                        href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                        <i class="fa fa-eye fa-sm" title="Ver"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}"
                                        class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fa fa-edit fa-sm"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="col-lg-12 mt-4">
                <h4 class="font-weight-bold text-primary mb-0 mt-4">Programa Primaria PPD:</h4>
                <div style="width: 40px; height: 4px; background-color: #4e73df; border-radius: 5px; margin-bottom: 10px">
                </div>
            </div>
            @foreach ($ciclos as $ciclo)
                @if ($ciclo->programa->id == 4)
                    <div class="col-lg-2">
                        <div class="card mt-2 mb-2 p-2 text-center">
                            <h5 class="mb-4" style="font-family: emoji">
                                <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">{{ $ciclo->nombre }}</a>
                            </h5>
                            <p>{{ $ciclo->alumnosB->count() }} Alumnos</p>
                            <div class="row">
                                <div class="col-4">
                                    <a class="btn btn-sm btn-info"
                                        href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                        <i class="fa fa-eye fa-sm" title="Ver"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}"
                                        class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fa fa-edit fa-sm"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
