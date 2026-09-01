@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-file-alt mr-2" style="color:#4e73df;"></i>Sílabos
                </h4>
                <small class="text-muted">Gestión de sílabos académicos por curso y ciclo</small>
            </div>
            <a href="{{ route('silabos.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Crear nuevo Sílabo <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>

        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="col-12 mb-3">
                <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">
                    <i class="fas fa-calendar-alt mr-1"></i>Periodo
                </span>
                <div class="btn-group flex-wrap mt-1" role="group">
                    <a href="{{ route('silabos.index') }}"
                        class="btn btn-sm {{ !$periodoFiltroId ? 'btn-dark' : 'btn-outline-dark' }}">
                        Todos
                    </a>
                    @foreach ($todosLosPeriodos as $periodo)
                        <a href="{{ route('silabos.index', ['periodo_id' => $periodo->id]) }}"
                            class="btn btn-sm {{ (int) $periodoFiltroId === (int) $periodo->id ? 'btn-dark' : 'btn-outline-dark' }}">
                            {{ $periodo->nombre }}
                            @if ($periodo->actual)
                                <span class="badge badge-success ml-1">Actual</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-12" id="tablaSilabos">
                <div class="table-responsive table-bordered">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead class="thead-dark">
                            <tr>
                                <th>Sílabo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($silabos as $silabo)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold" style="font-size:.95rem;">{{ $silabo->curso->nombre }}</div>
                                        <ul class="pl-3 mb-1 small text-muted">
                                            <li><strong>Periodo:</strong> {{ optional($silabo->periodoActual)->nombre ?? $silabo->periodo }}</li>
                                            <li><strong>Programa:</strong> {{ optional($silabo->curso->ciclo)->programa->nombre ?? '—' }}</li>
                                            <li><strong>Ciclo:</strong> {{ optional($silabo->curso->ciclo)->nombre ?? '—' }}</li>
                                        </ul>
                                        <div class="small">
                                            <strong>Docente:</strong>
                                            @if ($silabo->curso->docentes->isNotEmpty())
                                                {{ $silabo->curso->docentes->pluck('nombre')->join(', ') }}
                                            @else
                                                <span class="text-muted font-italic">Sin docente asignado</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('silabos.show', ['silabo' => $silabo->id]) }}"
                                            class="btn btn-sm btn-info" title="Ver Sílabo">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('silabos.edit', $silabo->id) }}" class="btn btn-sm btn-primary"
                                            title="Editar Sílabo"><i class="fa fa-pen"></i></a>
                                        <form action="{{ route('silabos.destroy', $silabo->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este sílabo?')" title="Eliminar Sílabo">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
