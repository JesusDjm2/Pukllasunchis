@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="text-primary font-weight-bold">Lista de Estándares</h4>
            <a href="{{ route('estandares.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">Crear Nuevo Estándar</a>
        </div>
        

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
            </div>
        @endif

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Estándar</th>
                    <th style="width: 15%">Ciclos por Programa</th>
                    <th>Competencias</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estandares as $estandar)
                    <tr>
                        <td>{{ $estandar->descripcion }}</td>
                        <td>
                            @php
                                $programas = $estandar->ciclos->groupBy('programa.nombre');
                            @endphp
                            @foreach ($programas as $programaNombre => $ciclos)
                                <strong>{{ $programaNombre }}</strong>
                                <ul style="padding-left: 0.2em">
                                    {{ $ciclos->pluck('nombre')->implode(', ') }}
                                </ul>
                            @endforeach
                        </td>
                        <td>
                            <ul style="padding-left: 0.8em">
                                @foreach ($estandar->competencias as $competencia)
                                    <li>{{ $competencia->nombre }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <a href="{{ route('estandares.edit', $estandar->id) }}" class="btn btn-info btn-sm mb-2">Editar</a>
                            <form action="{{ route('estandares.destroy', $estandar->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Estás seguro de eliminar este estándar?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
