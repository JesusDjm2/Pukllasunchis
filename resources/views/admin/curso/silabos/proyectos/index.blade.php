@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
            <h3 class="mb-0 font-weight-bold text-primary">Lista de Proyectos Integradores:</h3>
            <a href="{{ route('proyectos.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Nuevo Proyecto
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Proyecto</th>
                    <th>Producto</th>
                    <th>Propósito</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($proyectos as $proyecto)
                    <tr>
                        <td style="width: 15%">
                            <small>➤</small> <strong class="text-primary">{{ $proyecto->nombre }}</strong>
                            <br>                        
                            @if ($proyecto->ciclos->isNotEmpty())
                                <span class="font-weight-bold">Ciclos Asociados:</span> 
                                <br>
                                <ul class="list-unstyled mb-0">
                                    @foreach ($proyecto->ciclos->groupBy('programa.nombre') as $programaNombre => $ciclosGrupo)
                                        <li >{{ $programaNombre }}:</li>
                                        @foreach ($ciclosGrupo as $ciclo)
                                            <li class="badge badge-info p-2 m-1">{{ $ciclo->nombre }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No tiene ciclos asignados</span>
                            @endif
                        </td>
                        
                        <td>{{ $proyecto->producto }}</td>
                        <td>
                            @php
                                $descripcionCorta = implode(' ', array_slice(explode(' ', $proyecto->descripcion), 0, 120)) . '...';
                            @endphp
                            {{ $descripcionCorta }}
                        </td>
                        
                        <td>
                            <a href="{{ route('proyectos.edit', $proyecto) }}"
                                class="btn btn-warning btn-sm mb-2">Editar</a>
                            <form action="{{ route('proyectos.destroy', $proyecto) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Eliminar este proyecto?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
