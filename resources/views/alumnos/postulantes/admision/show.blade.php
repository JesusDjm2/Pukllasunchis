@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-3 pb-3">
        <h3 class="text-primary font-weight-bold mb-3">Detalles del Periodo</h3>

        <ul class="list-group">
            <li class="list-group-item"><strong>Nombre:</strong> {{ $adminFid->nombre }}</li>
            <li class="list-group-item"><strong>Año:</strong> {{ $adminFid->anio }}</li>
            <li class="list-group-item"><strong>Fecha de inicio:</strong> {{ $adminFid->fecha_inicio }}</li>
            <li class="list-group-item"><strong>Fecha de fin:</strong> {{ $adminFid->fecha_fin }}</li>
            <li class="list-group-item">
                <strong>Estado:</strong>
                <span class="badge {{ $adminFid->estado ? 'bg-success' : 'bg-secondary' }}">
                    {{ $adminFid->estado ? 'Activo' : 'Inactivo' }}
                </span>
            </li>
        </ul>

        <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </div>
@endsection
