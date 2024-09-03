@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3" style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Lista de Competencias:</h4>
            <a href="{{ route('competencias.create') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Crear nueva Competencia <i class="fa fa-plus fa-sm"></i>
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
            $currentSort = request()->query('sort', 'asc'); // Valor por defecto: ascendente
            $nextSort = $currentSort === 'asc' ? 'desc' : 'asc';
            $sortedCompetencias = $competencias->sortBy('nombre', SORT_REGULAR, $currentSort === 'desc');
        @endphp
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="competencias-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>
                                    Nombre
                                    <a href="{{ route('competencias.index', ['sort' => $nextSort]) }}" class="text-white">
                                        @if ($currentSort === 'asc')
                                            <i class="fa fa-arrow-up fa-sm"></i>
                                        @else
                                            <i class="fa fa-arrow-down fa-sm"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Descripción</th>
                                <th>Capacidades</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sortedCompetencias as $competencia)
                                <tr>
                                    <td>{{ $competencia->nombre }}</td>
                                    <td class="text-justify">{{ $competencia->descripcion }}</td>
                                    <td class="text-justify">{!! $competencia->capacidades !!}</td>
                                    <td style="width: 160px">
                                        <a href="{{ route('competencias.edit', $competencia->id) }}"
                                            class="btn btn-info btn-sm"><i class="fa fa-pen fa-sm" title="Editar"></i>
                                        </a>
                                        <a href="{{ route('competencias.show', $competencia->id) }}" title="Ver Competencia" class="btn btn-sm btn-success">
                                            <i
                                                class="fa fa-eye fa-sm"></i></a>
                                        <form action="{{ route('competencias.destroy', $competencia->id) }}" title="Eliminar" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esta competencia?')">
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
