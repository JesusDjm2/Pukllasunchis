@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Lista de Capacidades:</h4>
            <a href="{{ route('capacidades.create') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Crear nueva Capacidad <i class="fa fa-plus fa-sm"></i>
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

        @php
            $currentSort = request()->query('sort', 'asc'); // Orden predeterminado: ascendente
            $nextSort = $currentSort === 'asc' ? 'desc' : 'asc';
            $sortedCapacidades = $capacidades->sortBy('nombre', SORT_REGULAR, $currentSort === 'desc');
        @endphp

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="capacidades-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>Competencia</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sortedCapacidades as $capacidad)
                                <tr>
                                    <td>{{-- {{ $capacidad->competencia->nombre }} --}}
                                        <a href="{{ route('competencias.show', $capacidad->competencia->id) }}"
                                            class="text-primary">
                                            {{ $capacidad->competencia->nombre }}
                                        </a>
                                    </td>
                                    <td class="text-justify">{{ $capacidad->descripcion }}</td>

                                    <td style="width: 160px">
                                        <a href="{{ route('capacidades.edit', $capacidad->id) }}"
                                            class="btn btn-info btn-sm"><i class="fa fa-pen fa-sm" title="Editar"></i>
                                        </a>
                                        <form action="{{ route('capacidades.destroy', $capacidad->id) }}" title="Eliminar"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esta capacidad?')">
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
