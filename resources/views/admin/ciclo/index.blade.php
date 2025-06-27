@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4></h4>
            <a href="{{ route('ciclo.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Crear nuevo Ciclo <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
            </div>
        @endif

        <div class="accordion" id="accordionCiclos">
            @foreach ([1 => 'Programa Inicial', 2 => 'Programa Primaria EIB', 3 => 'Programa Inicial PPD', 4 => 'Programa Primaria PPD', 5 => 'Otro Programa'] as $programaId => $programaNombre)
                <div class="card">
                    <div class="card-header bg-secondary" id="heading{{ $programaId }}">
                        <h2 class="mb-0">
                            <button class="btn btn-link text-white font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse{{ $programaId }}" aria-expanded="true" aria-controls="collapse{{ $programaId }}">
                                {{ $programaNombre }}
                            </button>
                        </h2>
                    </div>
                    <div id="collapse{{ $programaId }}" class="collapse" aria-labelledby="heading{{ $programaId }}" data-parent="#accordionCiclos">
                        <div class="card-body">
                            <div class="row">
                                @foreach ($ciclos as $ciclo)
                                    @if ($ciclo->programa->id == $programaId)
                                        <div class="col-lg-2">
                                            <div class="card mt-2 mb-2 p-2 text-center">
                                                <h5 class="mb-4" style="font-family: emoji">
                                                    <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">{{ $ciclo->nombre }}</a>
                                                </h5>
                                                <p>
                                                    @if (in_array($programaId, [3, 4, 5]))
                                                        {{ $ciclo->alumnosB->count() }} Alumnos
                                                    @else
                                                        {{ $ciclo->alumnos_count }} Alumnos
                                                    @endif
                                                </p>
                                                <div class="row">
                                                    <div class="col-4">
                                                        <a class="btn btn-sm btn-info" href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                                            <i class="fa fa-eye fa-sm" title="Ver"></i>
                                                        </a>
                                                    </div>
                                                    <div class="col-4">
                                                        <a href="{{ route('ciclo.edit', ['ciclo' => $ciclo->id]) }}" class="btn btn-primary btn-sm" title="Editar">
                                                            <i class="fa fa-edit fa-sm"></i>
                                                        </a>
                                                    </div>
                                                    <div class="col-4">
                                                        <form action="{{ route('ciclo.destroy', ['ciclo' => $ciclo->id]) }}" method="POST" class="d-inline">
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
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
