@extends('layouts.admin')
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                Incidencias de <span class="text-dark">{{ $docente->nombre }}</span>
            </h5>
            <small class="text-muted">{{ $docente->email }}</small>
        </div>
        <a href="{{ route('docente.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver a Docentes
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total incidencias</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $incidencias->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($incidencias->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa fa-folder-open fa-3x mb-3"></i>
            <p>Este docente no tiene incidencias registradas.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size: 13px">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Reportado por</th>
                        <th>Alumno</th>
                        <th>Ciclo / Programa</th>
                        <th>Reporte</th>
                        <th>Imagen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidencias as $inc)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($incidencias->currentPage() - 1) * $incidencias->perPage() }}</td>
                            <td class="text-nowrap">
                                {{ $inc->fecha ? $inc->fecha->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                @if ($inc->docente)
                                    <strong>{{ $inc->docente->nombre }}</strong>
                                @elseif ($inc->nombre_docente)
                                    {{ $inc->nombre_docente }}
                                    <span class="badge badge-secondary" style="font-size:10px">externo</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($inc->alumno)
                                    {{ $inc->alumno->apellidos }}, {{ $inc->alumno->nombres }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($inc->ciclo)
                                    <strong>{{ $inc->ciclo->nombre }}</strong><br>
                                    <small class="text-muted">{{ $inc->ciclo->programa->nombre ?? '' }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width: 350px; white-space: pre-wrap;">{{ $inc->reporte }}</td>
                            <td class="text-center">
                                @if ($inc->imagen)
                                    <a href="{{ asset('img/incidencias/' . $inc->imagen) }}" target="_blank">
                                        <img src="{{ asset('img/incidencias/' . $inc->imagen) }}"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:4px;"
                                            alt="imagen">
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $incidencias->links() }}
        </div>
    @endif
</div>
@endsection
