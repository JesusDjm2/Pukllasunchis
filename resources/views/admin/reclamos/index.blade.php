@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-book mr-2 text-warning"></i> Libro de Reclamaciones
            </h5>
            <small class="text-muted">Reclamos y quejas registrados desde el formulario público</small>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total registrados</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reclamos->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($reclamos->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa fa-folder-open fa-3x mb-3"></i>
            <p>No hay reclamos registrados.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size: 13px">
                <thead class="thead-dark" style="position: sticky; top: 0; z-index: 2;">
                    <tr>
                        <th>#</th>
                        <th>N° Reclamo</th>
                        <th>Fecha</th>
                        <th>Reclamante</th>
                        <th>Condición</th>
                        <th>Tipo</th>
                        <th>Área involucrada</th>
                        <th class="text-center">Adjunto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reclamos as $r)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($reclamos->currentPage() - 1) * $reclamos->perPage() }}</td>
                            <td class="text-nowrap font-weight-bold">{{ $r->numero_reclamo }}</td>
                            <td class="text-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $r->nombre }}<br>
                                <small class="text-muted">DNI: {{ $r->dni }}</small>
                            </td>
                            <td>{{ $r->condicion_reclamante }}</td>
                            <td>
                                <span class="badge {{ $r->tipo_reclamacion === 'Reclamo' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $r->tipo_reclamacion }}
                                </span>
                            </td>
                            <td>{{ $r->area_involucrada }}</td>
                            <td class="text-center">
                                @if ($r->adjunto)
                                    <i class="fa fa-paperclip text-primary" title="Tiene archivo adjunto"></i>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.reclamos.show', $r) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $reclamos->links() }}
        </div>
    @endif
</div>
@endsection
