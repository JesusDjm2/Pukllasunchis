@extends('layouts.superadmin')
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-user-clock mr-2"></i> Bitácora de Accesos
            </h5>
            <small class="text-muted">Usuarios con una sesión activa en este momento</small>
        </div>
    </div>

    @include('admin.bitacora-accesos._tabs', ['activo' => 'bitacora.activos'])

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Conectados ahora</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conectados->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($conectados->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa fa-moon fa-3x mb-3"></i>
            <p>No hay ningún usuario conectado en este momento.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size: 13px">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Última actividad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conectados as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                @if ($item['usuario'])
                                    {{ $item['usuario']->name }} {{ $item['usuario']->apellidos }}
                                    <br><small class="text-muted">{{ $item['usuario']->email }}</small>
                                @else
                                    <span class="text-muted">Usuario eliminado</span>
                                @endif
                            </td>
                            <td>{{ $item['usuario']?->roles->pluck('name')->implode(', ') ?? '—' }}</td>
                            <td class="text-nowrap">{{ $item['ultima_actividad']->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
