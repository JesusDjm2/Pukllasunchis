@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="mb-0 text-primary font-weight-bold">Listado de Periodos </h4>
            <a href="{{ route('periodoactual.create') }}" class="btn btn-sm btn-primary shadow-sm">
                Nuevo Periodo
            </a>
        </div>
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
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Cierre</th>
                        <th>Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodoactuales as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->fecha_inicio ? \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->fecha_cierre ? \Carbon\Carbon::parse($p->fecha_cierre)->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if ($p->actual)
                                    <span class="badge badge-success">Sí</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('periodoactual.edit', $p) }}" class="btn btn-sm btn-primary">
                                    Editar <small><i class="fa fa-pen"></i></small>
                                </a>
                                <form action="{{ route('periodoactual.destroy', $p) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Seguro que deseas eliminar este período actual?')">
                                        Eliminar <small><i class="fa fa-trash"></i></small>
                                    </button>
                                </form>
                                @if ($p->periodos->isEmpty())
                                    <form action="{{ route('periodoactual.crearCalificaciones', $p->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success shadow-sm">
                                            Crear calificaciones <small><i class="fa fa-book fa-sm"></i></small>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('periodoactual.showRegistros', $p->id) }}"
                                        class="btn btn-sm btn-info shadow-sm">
                                        Ver registros <small><i class="fa fa-list"></i></small>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay períodos actuales registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
