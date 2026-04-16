@extends('layouts.admin')
@section('contenido')
    @php
        $qBase = array_filter(
            [
                'search' => request('search'),
                'search_page' => request('search_page'),
                'with_user' => request('with_user'),
            ],
            fn($v) => $v !== null && $v !== '',
        );
        $exportHidden = array_filter(
            [
                'search' => request('search'),
                'search_page' => request('search_page'),
                'with_user' => request('with_user'),
            ],
            fn($v) => $v !== null && $v !== '',
        );
    @endphp
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 mb-sm-0 text-primary"><small>Alumnos Formación Inicial Docente(FID):</small> {{ $totalRecords }}</h3>
            <div class="d-flex flex-wrap align-items-center">
                @role('admin')
                    <button type="button"
                        class="btn btn-sm shadow-sm text-white d-inline-flex align-items-center px-3 py-2 border-0 rounded-pill mr-2 mb-2 mb-sm-0"
                        style="background: linear-gradient(135deg, #1e7e34 0%, #28a745 45%, #20c997 100%); font-weight: 600; letter-spacing: 0.02em;"
                        data-toggle="modal" data-target="#modalExportarAlumnosExcel"
                        title="Elegir ciclos y vista previa antes de descargar Excel">
                        <i class="fas fa-file-excel mr-2" style="opacity: 0.95;"></i>
                        Exportar Excel
                    </button>
                @endrole
                <a href="{{ route('registerAdmin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2 mb-sm-0">
                    Nuevo Alumno &nbsp;<i class="fa fa-plus fa-sm"></i>
                </a>
            </div>
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
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <h6 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-filter mr-1"></i>
                                Filtros académicos
                            </h6>
                            @if (request()->filled('programa_id') || request()->filled('ciclo_id') || request()->filled('search'))
                                <a href="{{ route('adminAlumnos', array_filter(['with_user' => request('with_user')])) }}"
                                    class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
                            @endif
                        </div>
                        <p class="small text-muted mb-2 mb-md-3">Programa y ciclo se aplican en el servidor; la tabla
                            sigue agrupada por ciclo.</p>
                        <div class="mb-2">
                            <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Programa</span>
                            <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por programa">
                                <a href="{{ route('adminAlumnos', $qBase) }}"
                                    class="btn btn-sm {{ !request()->filled('programa_id') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
                                @foreach ($programasFiltro as $prog)
                                    @php
                                        $qProg = array_merge($qBase, ['programa_id' => $prog->id]);
                                    @endphp
                                    <a href="{{ route('adminAlumnos', $qProg) }}"
                                        class="btn btn-sm {{ (int) request('programa_id') === (int) $prog->id ? 'btn-primary' : 'btn-outline-primary' }}">{{ \Illuminate\Support\Str::limit($prog->nombre, 42) }}</a>
                                @endforeach
                            </div>
                        </div>
                        @if ($ciclosFiltro->isNotEmpty())
                            <div class="mb-0">
                                <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Ciclo</span>
                                <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por ciclo">
                                    @php
                                        $qSinCiclo = array_merge($qBase, ['programa_id' => request('programa_id')]);
                                    @endphp
                                    <a href="{{ route('adminAlumnos', $qSinCiclo) }}"
                                        class="btn btn-sm {{ !request()->filled('ciclo_id') ? 'btn-info' : 'btn-outline-info' }}">Todos
                                        los ciclos</a>
                                    @foreach ($ciclosFiltro as $cic)
                                        @php
                                            $qCic = array_merge($qBase, [
                                                'programa_id' => request('programa_id'),
                                                'ciclo_id' => $cic->id,
                                            ]);
                                            $nCiclo = optional($totalesPorCicloId->get($cic->id))->total;
                                        @endphp
                                        <a href="{{ route('adminAlumnos', $qCic) }}"
                                            class="btn btn-sm {{ (int) request('ciclo_id') === (int) $cic->id ? 'btn-info' : 'btn-outline-info' }}">{{ $cic->nombre }}
                                            @if ($nCiclo !== null)
                                                <span class="badge badge-light text-dark ml-1">{{ $nCiclo }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(request()->filled('programa_id'))
                            <p class="small text-muted mb-0">No hay ciclos registrados para este programa.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12 mt-1">
                        <form id="searchForm" action="{{ route('adminAlumnos') }}" method="GET">
                            @if (request()->filled('with_user'))
                                <input type="hidden" name="with_user" value="{{ request('with_user') }}">
                            @endif
                            <div class="input-group mb-2">
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="Buscar por nombre, apellido o DNI..." name="search" id="searchInput"
                                    value="{{ request('search') }}">
                                <input type="hidden" name="search_page" value="true">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"
                                        id="searchButton">Buscar</button>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">La búsqueda recorre <strong>todos</strong> los alumnos FID
                                (sin filtrar por programa ni ciclo).</p>
                            @if (!empty($busquedaActiva))
                                <p class="small text-info mb-0 mt-1"><i class="fas fa-info-circle"></i> Filtros de
                                    programa/ciclo no aplican mientras haya texto en el buscador.</p>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Detalles académicos</th>
                                <th scope="col">DNI</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grupoActual = null; @endphp
                            @forelse ($alumnos as $alumno)
                                @php
                                    $grupo =
                                        ($alumno->programa->nombre ?? 'Sin programa') .
                                        ' — Ciclo ' .
                                        ($alumno->ciclo->nombre ?? '?');
                                @endphp
                                @if ($grupo !== $grupoActual)
                                    @php
                                        $grupoActual = $grupo;
                                        $kGrupo =
                                            (string) ($alumno->programa_id ?? '0') .
                                            '|' .
                                            (string) ($alumno->ciclo_id ?? '0');
                                        $nGrupo = $conteoGrupoListado[$kGrupo] ?? 0;
                                    @endphp
                                    <tr class="table-active">
                                        <td colspan="4" class="py-2">
                                            <strong>{{ $grupo }}</strong>
                                            <span class="badge badge-secondary ml-2">{{ $nGrupo }}
                                                {{ $nGrupo === 1 ? 'alumno' : 'alumnos' }}</span>
                                            @php
                                                $nTotalCiclo = optional($totalesPorCicloId->get($alumno->ciclo_id))
                                                    ->total;
                                            @endphp
                                            @if ($nTotalCiclo !== null && (int) $nGrupo !== (int) $nTotalCiclo)
                                                <span class="text-muted small ml-2"
                                                    title="Total FID en este ciclo (sin filtros de búsqueda)">·
                                                    {{ $nTotalCiclo }} en ciclo (total)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</strong>
                                        @if ($alumno->user && $alumno->user->hasRole('inhabilitado') && ($alumno->user->perfil ?? '') === 'Deuda')
                                            <span class="badge badge-warning ml-1 align-middle"
                                                title="Usuario inhabilitado por deuda">Deuda</span>
                                        @endif
                                        <ul>
                                            <li> Trabajas:
                                                @if ($alumno->trabajas === 1 || $alumno->trabajas === '1')
                                                    Sí
                                                @elseif ($alumno->trabajas === 0 || $alumno->trabajas === '0')
                                                    No
                                                @else
                                                    {{ $alumno->trabajas }}
                                                @endif
                                            </li>
                                            <li>Donde trabajas: {{ $alumno->donde_trabajas ?? 'NULL' }}</li>

                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            <li>{{ $alumno->programa->nombre }} - {{ $alumno->ciclo->nombre }}</li>
                                            <li>{{ $alumno->email }}</li>
                                            <li>Teléfono: {{ $alumno->numero }}</li>
                                            <li>Fecha de nacimiento:
                                                @php $fechaNacFmt = $alumno->fechaNacimientoResueltaFormateada(); @endphp
                                                @if ($fechaNacFmt !== '')
                                                    {{ $fechaNacFmt }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </li>
                                        </ul>
                                    </td>
                                    <td>{{ $alumno->dni }}</td>
                                    <td>
                                        <a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}"
                                            class="btn btn-primary btn-sm" title="Editar">
                                            <i class="fa fa-edit fa-sm"></i>
                                        </a> |
                                        <a href="{{ route('alumnos.show', ['alumno' => $alumno->id]) }}"
                                            class="btn btn-info btn-sm" title="Ver registro completo">
                                            <i class="fa fa-eye fa-sm"></i>
                                        </a> |
                                        <a href="{{ route('admin.alumnos.carnet', array_merge(['alumno' => $alumno->id], array_filter(request()->only(['search', 'search_page', 'with_user', 'programa_id', 'ciclo_id'])))) }}"
                                            class="btn btn-secondary btn-sm" title="Ver carnet y descargar imagen"
                                            target="_blank" rel="noopener noreferrer">
                                            <i class="fa fa-id-card fa-sm"></i> Carnet
                                        </a> |
                                        @if (!$alumno->user)
                                            <a class="btn btn-success btn-sm relacionar-usuario"
                                                data-alumno-id="{{ $alumno->id }}" title="Relacionar con Usuario">
                                                <i class="fa fa-user fa-sm"></i>
                                            </a>|
                                        @endif
                                        <form id="deleteForm"
                                            action="{{ route('alumnos.destroy', ['alumno' => $alumno->id]) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                                data-target="#confirmDeleteModal" title="Eliminar">
                                                <i class="fa fa-trash fa-sm"></i>
                                            </button>
                                        </form>

                                        <!-- Modal de confirmación de eliminación -->
                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                                            aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar
                                                            Eliminación</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Estás seguro de que quieres eliminar este registro?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="document.getElementById('deleteForm').submit()">Eliminar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No se encontraron alumnos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if (session('error'))
                        <span class="text-danger text-sm">
                            {{ session('error') }}
                        </span>
                    @endif
                    {{-- {{ $alumnos->appends(request()->except('page'))->links() }} --}}
                </div>
            </div>
        </div>
    </div>
    @role('admin')
        <div class="modal fade" id="modalExportarAlumnosExcel" tabindex="-1" role="dialog"
            aria-labelledby="modalExportarAlumnosExcelLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.alumnos.export-excel') }}" id="formExportarAlumnosExcel">
                        @csrf
                        @foreach ($exportHidden as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="modal-header">
                            <h5 class="modal-title text-primary" id="modalExportarAlumnosExcelLabel">
                                <i class="fas fa-file-excel mr-2"></i>Exportar alumnos FID a Excel
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <style>
                                /* Scroll estable (modal-dialog-scrollable falla con algunos layouts / BS4) */
                                #modalExportarAlumnosExcel .export-ciclos-scroll {
                                    max-height: min(58vh, 440px);
                                    overflow-y: auto;
                                    overflow-x: hidden;
                                    -webkit-overflow-scrolling: touch;
                                }

                                #modalExportarAlumnosExcel .export-ciclo-fila {
                                    cursor: pointer;
                                    transition: background-color .12s ease;
                                }

                                #modalExportarAlumnosExcel .export-ciclo-fila:hover {
                                    background-color: #e9ecef !important;
                                }
                            </style>
                            @if ($errors->has('ciclo_ids') || $errors->has('ciclo_ids.*'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('ciclo_ids') ?: $errors->first('ciclo_ids.*') }}
                                </div>
                            @endif
                            <p class="small text-muted mb-2">
                                Marca los <strong>ciclos</strong> que incluirán filas en el archivo. Se respeta el filtro
                                de búsqueda y “con usuario” si los aplicaste arriba; no se usan los botones de
                                programa/ciclo de la tabla para acotar el Excel (solo lo que elijas aquí).
                            </p>
                            @if (!empty($busquedaActiva))
                                <p class="small text-info mb-2"><i class="fas fa-info-circle"></i> Búsqueda activa: el
                                    archivo solo traerá alumnos que coincidan con el texto; la vista previa numérica
                                    usa totales por ciclo (todos los FID de ese ciclo).</p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center mb-3 border-bottom pb-2">
                                <span class="small font-weight-bold text-secondary mr-2">Vista previa:</span>
                                <span class="badge badge-primary mr-2"><span id="exportPreviewCiclos">0</span>
                                    ciclos</span>
                                <span class="badge badge-secondary">~<span id="exportPreviewCount">0</span> alumnos
                                    FID en esos ciclos</span>
                            </div>
                            <div class="btn-group btn-group-sm mb-3" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="btnExportSelTodos">Todos los
                                    ciclos</button>
                                <button type="button" class="btn btn-outline-secondary" id="btnExportSelNinguno">Ninguno</button>
                                @if (request()->filled('programa_id'))
                                    <button type="button" class="btn btn-outline-primary" id="btnExportSelProgramaActual">Solo
                                        programa filtrado</button>
                                @endif
                            </div>
                            @if ($ciclosParaExportacion->isEmpty())
                                <p class="text-muted mb-0">No hay ciclos con alumnos FID para exportar.</p>
                            @else
                                <div class="export-ciclos-scroll border rounded bg-light px-2 py-2">
                                    @foreach ($ciclosParaExportacion->groupBy('programa_id') as $grupoCiclos)
                                        @php $primer = $grupoCiclos->first(); @endphp
                                        <div class="mb-2 export-ciclo-grupo">
                                            <h6 class="small font-weight-bold text-dark mb-1 text-truncate border-left border-primary pl-2"
                                                style="border-width: 3px !important;"
                                                title="{{ $primer->programa->nombre ?? 'Programa #' . $primer->programa_id }}">
                                                {{ $primer->programa->nombre ?? 'Programa #' . $primer->programa_id }}
                                            </h6>
                                            <div class="row mx-n1">
                                                @foreach ($grupoCiclos as $cicExport)
                                                    @php
                                                        $nCicExport = optional(
                                                            $totalesPorCicloId->get($cicExport->id),
                                                        )->total;
                                                        $checked =
                                                            (int) request('ciclo_id') === (int) $cicExport->id ||
                                                            ((int) request('programa_id') ===
                                                                (int) $cicExport->programa_id &&
                                                                !request()->filled('ciclo_id'));
                                                    @endphp
                                                    <div class="col-md-6 px-1 mb-1">
                                                        <div
                                                            class="export-ciclo-fila border rounded bg-white h-100 px-2 py-1">
                                                            <div class="custom-control custom-checkbox my-0">
                                                                <input type="checkbox"
                                                                    class="custom-control-input ciclo-export-check"
                                                                    name="ciclo_ids[]" value="{{ $cicExport->id }}"
                                                                    id="ciclo_export_{{ $cicExport->id }}"
                                                                    data-total="{{ (int) ($nCicExport ?? 0) }}"
                                                                    data-programa-id="{{ (int) $cicExport->programa_id }}"
                                                                    {{ $checked ? 'checked' : '' }}>
                                                                <label class="custom-control-label small mb-0"
                                                                    for="ciclo_export_{{ $cicExport->id }}">
                                                                    {{ $cicExport->nombre }}
                                                                    <span
                                                                        class="badge badge-light text-dark ml-1">{{ (int) ($nCicExport ?? 0) }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-success"
                                {{ $ciclosParaExportacion->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-download mr-1"></i>Descargar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endrole
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function refreshExportPreview() {
                var ciclos = 0;
                var total = 0;
                $('.ciclo-export-check:checked').each(function() {
                    ciclos++;
                    total += parseInt($(this).attr('data-total'), 10) || 0;
                });
                $('#exportPreviewCiclos').text(ciclos);
                $('#exportPreviewCount').text(total);
            }
            $(document).on('change', '.ciclo-export-check', refreshExportPreview);
            $(document).on('click', '.export-ciclo-fila', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) {
                    return;
                }
                if ($(e.target).closest('label').length) {
                    return;
                }
                var $cb = $(this).find('.ciclo-export-check');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });
            $('#btnExportSelTodos').on('click', function() {
                $('.ciclo-export-check').prop('checked', true);
                refreshExportPreview();
            });
            $('#btnExportSelNinguno').on('click', function() {
                $('.ciclo-export-check').prop('checked', false);
                refreshExportPreview();
            });
            $('#btnExportSelProgramaActual').on('click', function() {
                var pid = {{ (int) request('programa_id', 0) }};
                $('.ciclo-export-check').each(function() {
                    $(this).prop('checked', parseInt($(this).attr('data-programa-id'), 10) === pid);
                });
                refreshExportPreview();
            });
            $('#modalExportarAlumnosExcel').on('shown.bs.modal', refreshExportPreview);
            @if ($errors->has('ciclo_ids') || $errors->has('ciclo_ids.*'))
                $('#modalExportarAlumnosExcel').modal('show');
            @endif
            refreshExportPreview();

            $('.relacionar-usuario').click(function() {
                var alumnoId = $(this).data('alumno-id');
                relacionarUsuario(alumnoId);
            });

            function relacionarUsuario(alumnoId) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('relacionarUsuario', ['alumno' => '__ALUMNO_ID__']) }}'.replace(
                        '__ALUMNO_ID__', alumnoId),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Relación con usuario establecida correctamente.');
                        asignarRolAlumno(alumnoId);
                        location.reload();
                    },
                    error: function(error) {
                        alert('Error al establecer la relación con el usuario.');
                    }
                });
            }

            function asignarRolAlumno(alumnoId) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('asignarRolAlumno', ['alumno' => '__ALUMNO_ID__']) }}'.replace(
                        '__ALUMNO_ID__', alumnoId),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Rol asignado correctamente.');
                    },
                    error: function(error) {
                        console.error('Error al asignar el rol.');
                    }
                });
            }
        });
    </script>
@endsection
