@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        {{--  ENCABEZADO + BOTONES PARA CAMBIAR TABLA --}}
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Periodos de Admisión</h3>
            <div>
                <button class="btn btn-success btn-sm" onclick="mostrarTabla('tablaFID')">FID</button>
                <button class="btn btn-info btn-sm" onclick="mostrarTabla('tablaPPD')">PPD</button>
            </div>
            <img src="{{ asset('img/Icono-Puklla.png') }}" width="40px" alt="" class="logo-rotando">
            <style>
                .logo-rotando {
                    animation: giro 4s linear infinite;
                }

                @keyframes giro {
                    from {
                        transform: rotate(0deg);
                    }

                    to {
                        transform: rotate(360deg);
                    }
                }
            </style>
        </div>

        {{-- ALERTAS --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Éxito:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div id="tablaFID" class="table-responsive">
            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                <h5 class="text-success font-weight-bold mb-0">Periodos FID</h5>
                <a href="{{ route('admin-fids.create') }}" class="btn btn-success btn-sm">
                    Nuevo periodo FID
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Año</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($adminFids as $fid)
                        <tr>
                            <td>{{ $fid->id }}</td>
                            <td>{{ $fid->nombre }}</td>
                            <td>{{ $fid->anio }}</td>
                            <td>{{ $fid->fecha_inicio ? \Carbon\Carbon::parse($fid->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') : '—' }}
                            </td>
                            <td>{{ $fid->fecha_fin ? \Carbon\Carbon::parse($fid->fecha_fin)->translatedFormat('d \d\e F \d\e Y') : '—' }}
                            </td>
                            <td>
                                <span
                                    class="badge {{ $fid->estado ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $fid->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin-fids.edit', $fid) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-pen fa-sm"></i> Editar
                                </a>

                                <form action="{{ route('admin-fids.destroy', $fid) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar este periodo?')">
                                        <i class="fa fa-trash fa-sm"></i> Eliminar
                                    </button>
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

        <div id="tablaPPD" class="table-responsive" style="display: none;">
            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                <h5 class="text-info font-weight-bold mb-0">Periodos PPD</h5>
                <a href="{{ route('admin.ppd.create') }}" class="btn btn-info btn-sm">
                    Nuevo periodo PPD
                </a>
            </div>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Año</th>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Cierre</th>
                        <th>Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminsppd as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>
                                {{ $p->anio }}
                            </td>
                            <td>{{ $p->fecha_inicio ? \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '-' }}
                            </td>
                            <td>{{ $p->fecha_cierre ? \Carbon\Carbon::parse($p->fecha_cierre)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <span class="badge {{ $p->estado ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $fid->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.ppd.edit', $p) }}" class="btn btn-sm btn-primary">
                                    Editar <small><i class="fa fa-pen"></i></small>
                                </a>
                                <form action="{{ route('admin.ppd.destroy', $p) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Seguro que deseas eliminar este período?')">
                                        Eliminar <small><i class="fa fa-trash"></i></small>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay períodos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SCRIPT PARA MOSTRAR/OCULTAR TABLAS --}}
    <script>
        function mostrarTabla(id) {
            document.getElementById('tablaFID').style.display = 'none';
            document.getElementById('tablaPPD').style.display = 'none';

            document.getElementById(id).style.display = 'block';
        }
    </script>
@endsection
