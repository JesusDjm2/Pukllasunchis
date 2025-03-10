@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h2 class="mb-0 font-weight-bold text-primary">Enfoques:</h2>
            <a href="{{ route('enfoques.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Crear nuevo Enfoque <i class="fa fa-plus fa-sm"></i>
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
            <div class="col-lg-12" id="tablaEnfoques">
                <div class="table-responsive table-bordered">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>¿Cuándo son observables en la EESPP?</th>
                                <th>¿En qué acciones concretas se observa?</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enfoques as $enfoque)
                                <tr>
                                    <td class="font-weight-bold">{{ $enfoque->nombre }}
                                    </td>
                                    <td>{{ $enfoque->descripcion }}</td>
                                    <td>{{ $enfoque->observables }}</td>
                                    <td>{{ $enfoque->concretas }}</td>
                                    <td>
                                        <a href="{{ route('enfoques.show', $enfoque->id) }}" class="mb-2 btn btn-sm btn-info"
                                            title="Ver Enfoque"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('enfoques.edit', $enfoque->id) }}" class="mb-2 btn btn-sm btn-primary"
                                            title="Editar Enfoque"><i class="fa fa-pen"></i></a>
                                        <form action="{{ route('enfoques.destroy', $enfoque->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este enfoque?')"
                                                title="Eliminar Enfoque">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
