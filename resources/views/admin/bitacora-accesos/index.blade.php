@extends('layouts.superadmin')
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-user-clock mr-2"></i> Bitácora de Accesos
            </h5>
            <small class="text-muted">Historial de inicios de sesión de todos los usuarios</small>
        </div>
    </div>

    @include('admin.bitacora-accesos._tabs', ['activo' => 'bitacora.index'])

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total registros</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sesiones->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($sesiones->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa fa-folder-open fa-3x mb-3"></i>
            <p>No hay accesos registrados todavía.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size: 13px">
                <thead class="thead-dark" style="position: sticky; top: 0; z-index: 2;">
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Entró</th>
                        <th>Salió / Estado</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sesiones as $sesion)
                        @php $conectado = $sesion->estaConectadoAhora(); @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($sesiones->currentPage() - 1) * $sesiones->perPage() }}</td>
                            <td>{{ $sesion->email_snapshot }}</td>
                            <td>{{ $sesion->rol_snapshot ?? '—' }}</td>
                            <td class="text-nowrap">{{ $sesion->login_at->format('d/m/Y H:i') }}</td>
                            <td class="text-nowrap">
                                @if ($conectado)
                                    <span class="badge badge-success"><i class="fa fa-circle" style="font-size:8px"></i> Conectado ahora</span>
                                @elseif ($sesion->logout_at)
                                    {{ $sesion->logout_at->format('d/m/Y H:i') }}
                                    @if ($sesion->logout_tipo === 'expirado')
                                        <span class="badge badge-secondary">expiró</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $sesion->duracion ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $sesiones->links() }}
        </div>
    @endif
</div>
@endsection
