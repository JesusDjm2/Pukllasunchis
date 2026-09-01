@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@push('styles')
<style>
    .seg-tabs .nav-link { font-size: .85rem; font-weight: 600; color: #858796; }
    .seg-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; }

    .seg-card { border-radius: .85rem; overflow: hidden; }

    .seg-table { font-size: .82rem; margin-bottom: 0; }
    .seg-table thead th {
        background: #f8f9fc;
        color: #5a5c69;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 2px solid #e3e6f0;
        border-top: none;
        padding: .85rem 1rem;
        white-space: nowrap;
    }
    .seg-table tbody td {
        padding: .75rem 1rem;
        border-top: 1px solid #eef0f5;
        vertical-align: middle;
    }
    .seg-table tbody tr:hover { background-color: #f7f9fd; }
    .seg-table tbody tr.seg-row-warning { background-color: #fffaf0; }
    .seg-table tbody tr.seg-row-warning:hover { background-color: #fdf3df; }

    .seg-persona { display: flex; align-items: center; gap: .6rem; }
    .seg-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; min-width: 32px; border-radius: 50%;
        background: #eef1fc; color: #4e73df; font-weight: 700; font-size: .68rem;
    }
    .seg-avatar-muted { background: #f4f6f9; color: #b7b9c8; }

    .seg-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .3rem .7rem; border-radius: 20px; font-size: .7rem; font-weight: 700;
        white-space: nowrap;
    }
    .seg-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .seg-pill-ok { background: #e3f9ec; color: #17a673; }
    .seg-pill-pendiente { background: #f4f6f9; color: #858796; }
    .seg-pill-urg-Baja { background: #f4f6f9; color: #858796; }
    .seg-pill-urg-Media { background: #e6f8fb; color: #2596a6; }
    .seg-pill-urg-Alta { background: #fef6e3; color: #c69107; }
    .seg-pill-urg-Urgente { background: #fbeaea; color: #e0342a; }
    .seg-pill-warning { background: #fef1d9; color: #c07f06; }
    .seg-pill-anon { background: #f4f6f9; color: #858796; }

    .seg-ciclo-pill {
        display: inline-block; padding: .15rem .55rem; border-radius: 6px;
        background: #eef1fc; color: #4e73df; font-size: .7rem; font-weight: 700;
    }

    .seg-texto-largo { max-width: 280px; white-space: pre-wrap; color: #3a3b45; line-height: 1.4; }
    .seg-contacto { font-size: .76rem; color: #5a5c69; }
    .seg-contacto div + div { margin-top: .15rem; }

    .seg-actions { display: flex; gap: .4rem; justify-content: center; flex-wrap: wrap; }
    .seg-actions form { margin: 0; }
    .seg-btn-estado {
        border-radius: 20px; font-size: .72rem; font-weight: 600; padding: .32rem .85rem;
    }
    .seg-btn-eliminar {
        border-radius: 50%; width: 30px; height: 30px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
    }

    .seg-toolbar {
        display: flex; justify-content: flex-end; margin-bottom: .9rem;
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-clipboard-list mr-2 text-warning"></i> Seguimiento de Alumnos
            </h5>
            <small class="text-muted">Incidencias, tutorías individuales y sugerencias — todo en un solo lugar</small>
        </div>
        <a href="{{ route('docente.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver a Docentes
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs seg-tabs border-bottom mb-4" id="segTabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tabSegIncidencias">
                <i class="fas fa-exclamation-triangle mr-1"></i> Incidencias
                <span class="badge badge-secondary ml-1">{{ $incidencias->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabSegTutorias">
                <i class="fas fa-hands-helping mr-1"></i> Tutorías
                <span class="badge badge-danger ml-1">{{ $tutorias->where('estado', 'pendiente')->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabSegSugerencias">
                <i class="fas fa-comment-dots mr-1"></i> Sugerencias
                <span class="badge badge-info ml-1">{{ $sugerencias->total() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content pb-3">

        {{-- ── TAB INCIDENCIAS ── --}}
        <div class="tab-pane fade show active" id="tabSegIncidencias">
            @if ($incidencias->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                    <p>No hay incidencias registradas.</p>
                </div>
            @else
                <div class="card seg-card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table seg-table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Reportado por</th>
                                    <th>Alumno</th>
                                    <th>Ciclo / Programa</th>
                                    <th>Reporte</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($incidencias as $inc)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration + ($incidencias->currentPage() - 1) * $incidencias->perPage() }}</td>
                                        <td class="text-nowrap">{{ $inc->fecha ? $inc->fecha->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            @if ($inc->docente)
                                                {{ $inc->docente->nombre }}
                                            @elseif ($inc->nombre_docente)
                                                {{ $inc->nombre_docente }}
                                                <span class="seg-pill seg-pill-pendiente ml-1" style="font-size:.6rem;">externo</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($inc->alumno)
                                                <div class="seg-persona">
                                                    <span class="seg-avatar">{{ mb_strtoupper(mb_substr($inc->alumno->nombres, 0, 1).mb_substr($inc->alumno->apellidos, 0, 1)) }}</span>
                                                    <div>
                                                        <div>{{ $inc->alumno->apellidos }}, {{ $inc->alumno->nombres }}</div>
                                                        @if ($inc->alumno->ciclo)
                                                            <div class="small text-muted">{{ optional($inc->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $inc->alumno->ciclo->nombre }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($inc->ciclo)
                                                <span class="seg-ciclo-pill">Ciclo {{ $inc->ciclo->nombre }}</span>
                                                <div class="small text-muted mt-1">{{ $inc->ciclo->programa->nombre ?? '' }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="seg-texto-largo">{{ $inc->reporte }}</td>
                                        <td class="text-center text-nowrap">
                                            @if ($inc->estado === 'atendida')
                                                <span class="seg-pill seg-pill-ok"><span class="seg-pill-dot"></span>Atendida</span>
                                                @if ($inc->atendido_at)
                                                    <div class="small text-muted mt-1">{{ $inc->atendido_at->format('d/m/Y') }}</div>
                                                @endif
                                            @else
                                                <span class="seg-pill seg-pill-pendiente"><span class="seg-pill-dot"></span>Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="seg-actions">
                                                <form action="{{ route('admin.incidencias.estado', $inc->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm seg-btn-estado {{ $inc->estado === 'atendida' ? 'btn-outline-secondary' : 'btn-success' }}">
                                                        {{ $inc->estado === 'atendida' ? 'Marcar pendiente' : 'Marcar atendida' }}
                                                    </button>
                                                </form>
                                                @if (auth()->user()->hasAnyRole(['admin', 'super-admin']))
                                                    <form action="{{ route('admin.incidencias.destroy', $inc->id) }}" method="POST" class="seg-form-eliminar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger seg-btn-eliminar" title="Eliminar">
                                                            <i class="fa fa-trash fa-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $incidencias->links() }}
                </div>
            @endif
        </div>

        {{-- ── TAB TUTORÍAS ── --}}
        <div class="tab-pane fade" id="tabSegTutorias">
            <div class="seg-toolbar">
                <a href="{{ route('admin.seguimiento.qr') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-qrcode mr-1"></i> Ver códigos QR por ciclo
                </a>
            </div>
            @if ($tutorias->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                    <p>No hay solicitudes de tutoría registradas.</p>
                </div>
            @else
                <div class="card seg-card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table seg-table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Alumno</th>
                                    <th>Contacto</th>
                                    <th>Ciclo / Programa</th>
                                    <th>Prioridad</th>
                                    <th>Motivo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tutorias as $t)
                                    @php
                                        $tNoEsDeEsteCiclo = $t->alumno && $t->ciclo && $t->alumno->ciclo_id !== $t->ciclo_id;
                                    @endphp
                                    <tr class="{{ $tNoEsDeEsteCiclo ? 'seg-row-warning' : '' }}">
                                        <td class="text-center text-muted">{{ $loop->iteration + ($tutorias->currentPage() - 1) * $tutorias->perPage() }}</td>
                                        <td class="text-nowrap">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($t->alumno)
                                                <div class="seg-persona">
                                                    <span class="seg-avatar">{{ mb_strtoupper(mb_substr($t->alumno->nombres, 0, 1).mb_substr($t->alumno->apellidos, 0, 1)) }}</span>
                                                    <div>
                                                        <div>{{ $t->alumno->apellidos }}, {{ $t->alumno->nombres }}</div>
                                                        @if ($t->alumno->ciclo)
                                                            <div class="small text-muted">{{ optional($t->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $t->alumno->ciclo->nombre }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @elseif ($t->nombre_alumno)
                                                <div class="seg-persona">
                                                    <span class="seg-avatar">{{ mb_strtoupper(mb_substr($t->nombre_alumno, 0, 1)) }}</span>
                                                    <div>{{ $t->nombre_alumno }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                            @if ($tNoEsDeEsteCiclo)
                                                <div class="mt-1">
                                                    <span class="seg-pill seg-pill-warning" title="El alumno pertenece a otro ciclo">
                                                        <i class="fas fa-exclamation-triangle"></i> No es de este ciclo
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap seg-contacto">
                                            @if ($t->alumno?->email)
                                                <div><i class="fas fa-envelope fa-xs mr-1 text-muted"></i>{{ $t->alumno->email }}</div>
                                            @endif
                                            @if ($t->alumno?->numero)
                                                <div><i class="fas fa-phone fa-xs mr-1 text-muted"></i>{{ $t->alumno->numero }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($t->ciclo)
                                                <span class="seg-ciclo-pill">Ciclo {{ $t->ciclo->nombre }}</span>
                                                <div class="small text-muted mt-1">{{ $t->ciclo->programa->nombre ?? '' }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="seg-pill seg-pill-urg-{{ $t->urgencia }}"><span class="seg-pill-dot"></span>{{ $t->urgencia }}</span>
                                        </td>
                                        <td class="seg-texto-largo">{{ $t->motivo }}</td>
                                        <td class="text-center text-nowrap">
                                            @if ($t->estado === 'atendida')
                                                <span class="seg-pill seg-pill-ok"><span class="seg-pill-dot"></span>Atendida</span>
                                                @if ($t->atendido_at)
                                                    <div class="small text-muted mt-1">{{ $t->atendido_at->format('d/m/Y') }}</div>
                                                @endif
                                            @else
                                                <span class="seg-pill seg-pill-pendiente"><span class="seg-pill-dot"></span>Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="seg-actions">
                                                <form action="{{ route('admin.tutorias.estado', $t->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm seg-btn-estado {{ $t->estado === 'atendida' ? 'btn-outline-secondary' : 'btn-success' }}">
                                                        {{ $t->estado === 'atendida' ? 'Marcar pendiente' : 'Marcar atendida' }}
                                                    </button>
                                                </form>
                                                @if (auth()->user()->hasAnyRole(['admin', 'super-admin']))
                                                    <form action="{{ route('admin.tutorias.destroy', $t->id) }}" method="POST" class="seg-form-eliminar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger seg-btn-eliminar" title="Eliminar">
                                                            <i class="fa fa-trash fa-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $tutorias->links() }}
                </div>
            @endif
        </div>

        {{-- ── TAB SUGERENCIAS ── --}}
        <div class="tab-pane fade" id="tabSegSugerencias">
            <div class="seg-toolbar">
                <a href="{{ route('admin.seguimiento.qr') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-qrcode mr-1"></i> Ver códigos QR por ciclo
                </a>
            </div>
            @if ($sugerencias->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                    <p>No hay sugerencias registradas.</p>
                </div>
            @else
                <div class="card seg-card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table seg-table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Alumno</th>
                                    <th>Contacto</th>
                                    <th>Ciclo / Programa</th>
                                    <th>Sugerencia</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sugerencias as $s)
                                    @php
                                        $sNoEsDeEsteCiclo = $s->alumno && $s->ciclo && $s->alumno->ciclo_id !== $s->ciclo_id;
                                    @endphp
                                    <tr class="{{ $sNoEsDeEsteCiclo ? 'seg-row-warning' : '' }}">
                                        <td class="text-center text-muted">{{ $loop->iteration + ($sugerencias->currentPage() - 1) * $sugerencias->perPage() }}</td>
                                        <td class="text-nowrap">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($s->alumno)
                                                <div class="seg-persona">
                                                    <span class="seg-avatar">{{ mb_strtoupper(mb_substr($s->alumno->nombres, 0, 1).mb_substr($s->alumno->apellidos, 0, 1)) }}</span>
                                                    <div>
                                                        <div>{{ $s->alumno->apellidos }}, {{ $s->alumno->nombres }}</div>
                                                        @if ($s->alumno->ciclo)
                                                            <div class="small text-muted">{{ optional($s->alumno->ciclo->programa)->nombre ?? '—' }} · Ciclo {{ $s->alumno->ciclo->nombre }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @elseif ($s->nombre_alumno)
                                                <div class="seg-persona">
                                                    <span class="seg-avatar">{{ mb_strtoupper(mb_substr($s->nombre_alumno, 0, 1)) }}</span>
                                                    <div>{{ $s->nombre_alumno }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                            @if ($sNoEsDeEsteCiclo)
                                                <div class="mt-1">
                                                    <span class="seg-pill seg-pill-warning" title="El alumno pertenece a otro ciclo">
                                                        <i class="fas fa-exclamation-triangle"></i> No es de este ciclo
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap seg-contacto">
                                            @if ($s->alumno?->email)
                                                <div><i class="fas fa-envelope fa-xs mr-1 text-muted"></i>{{ $s->alumno->email }}</div>
                                            @endif
                                            @if ($s->alumno?->numero)
                                                <div><i class="fas fa-phone fa-xs mr-1 text-muted"></i>{{ $s->alumno->numero }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($s->ciclo)
                                                <span class="seg-ciclo-pill">Ciclo {{ $s->ciclo->nombre }}</span>
                                                <div class="small text-muted mt-1">{{ $s->ciclo->programa->nombre ?? '' }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="seg-texto-largo">{{ $s->mensaje }}</td>
                                        <td class="text-center text-nowrap">
                                            @if ($s->estado === 'revisada')
                                                <span class="seg-pill seg-pill-ok"><span class="seg-pill-dot"></span>Revisada</span>
                                                @if ($s->atendido_at)
                                                    <div class="small text-muted mt-1">{{ $s->atendido_at->format('d/m/Y') }}</div>
                                                @endif
                                            @else
                                                <span class="seg-pill seg-pill-pendiente"><span class="seg-pill-dot"></span>Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="seg-actions">
                                                <form action="{{ route('admin.sugerencias.estado', $s->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm seg-btn-estado {{ $s->estado === 'revisada' ? 'btn-outline-secondary' : 'btn-success' }}">
                                                        {{ $s->estado === 'revisada' ? 'Marcar pendiente' : 'Marcar revisada' }}
                                                    </button>
                                                </form>
                                                @if (auth()->user()->hasAnyRole(['admin', 'super-admin']))
                                                    <form action="{{ route('admin.sugerencias.destroy', $s->id) }}" method="POST" class="seg-form-eliminar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger seg-btn-eliminar" title="Eliminar">
                                                            <i class="fa fa-trash fa-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $sugerencias->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var params = new URLSearchParams(window.location.search);
        var tab = params.get('tab');
        var tabMap = { incidencias: '#tabSegIncidencias', tutorias: '#tabSegTutorias', sugerencias: '#tabSegSugerencias' };
        if (tabMap[tab]) {
            $('#segTabs a[href="' + tabMap[tab] + '"]').tab('show');
        }
    })();

    document.querySelectorAll('.seg-form-eliminar').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar este registro?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
