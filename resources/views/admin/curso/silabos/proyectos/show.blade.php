@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
            <h3 class="mb-0 font-weight-bold text-primary">Detalle del Proyecto</h3>
            <a href="{{ route('proyectos.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>

        <table class="table">
            <tr>
                <th>Nombre:</th>
                <td>{{ $proyecto->nombre }}</td>
            </tr>
            <tr>
                <th>Producto:</th>
                <td>{{ $proyecto->producto }}</td>
            </tr>
            <tr>
                <th>Propósito:</th>
                <td>{{ $proyecto->descripcion }}</td>
            </tr>
        </table>
    </div>
@endsection
