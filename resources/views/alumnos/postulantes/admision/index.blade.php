@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Periodos de Admisión</h3>
            <a href="{{ route('admin-fids.create') }}" class="btn btn-primary btn-sm">Nuevo periodo</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Éxito:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Año</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($adminFids as $key => $fid)
                        <tr>
                            <td>{{ $fid->id }}</td>
                            <td>{{ $fid->nombre }}</td>
                            <td>{{ $fid->anio }}</td>
                            <td>
                                {{ $fid->fecha_inicio ? \Carbon\Carbon::parse($fid->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') : '—' }}
                            </td>
                            <td>
                                {{ $fid->fecha_fin ? \Carbon\Carbon::parse($fid->fecha_fin)->translatedFormat('d \d\e F \d\e Y') : '—' }}
                            </td>
                            <td>
                                <span
                                    class="badge {{ $fid->estado ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $fid->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin-fids.edit', $fid) }}" class="btn btn-warning btn-sm"><i
                                        class="fa fa-pen fa-sm"></i> Editar</a>
                                <form action="{{ route('admin-fids.destroy', $fid) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar este periodo?')"><i
                                            class="fa fa-trash fa-sm"></i> Eliminar</button>
                                </form>
                                @if ($fid->postulantes_count == 0)
                                    <form action="{{ route('admin-fids.asociar-sin-relacion', $fid) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm"
                                            onclick="return confirm('¿Deseas asociar todos los postulantes sin periodo a este periodo?')">
                                            <i class="fa fa-book fa-sm"></i> Crear registros
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin-fids.ver-postulantes', $fid) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fa fa-users fa-sm"></i> Ver registros
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
