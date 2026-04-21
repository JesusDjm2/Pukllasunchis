@extends('layouts.admin')
@section('titulo', 'Mink\'arikuy')
@section('contenido')
<div class="container-fluid bg-white">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
        <h4 class="h4 mb-0 text-gray-800"><i class="fas fa-qrcode mr-2"></i>Mink'arikuy</h4>
        <a href="{{ route('admin.minkarikuy.create') }}"
            class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm mr-1"></i> Nuevo registro
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Imagen</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->nombre }}</td>
                        <td>{{ $item->fecha->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $item->hora)->format('H:i') }}</td>
                        <td>
                            @if ($item->imagen)
                                <a href="{{ asset('img/minkarikuy/'.$item->imagen) }}" target="_blank">
                                    <img src="{{ asset('img/minkarikuy/'.$item->imagen) }}"
                                        alt="QR" height="60" class="rounded">
                                </a>
                            @else
                                <span class="text-muted">Sin imagen</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($item->activo)
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.minkarikuy.edit', $item) }}"
                                class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.minkarikuy.destroy', $item) }}"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este registro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No hay registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
