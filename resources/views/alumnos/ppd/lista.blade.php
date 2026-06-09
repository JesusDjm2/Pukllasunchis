@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    @php
        $qBase = array_filter(
            ['search' => request('search'), 'search_page' => request('search_page')],
            fn($v) => $v !== null && $v !== '',
        );
        $exportHidden = array_filter(
            ['search' => request('search'), 'search_page' => request('search_page')],
            fn($v) => $v !== null && $v !== '',
        );
    @endphp

    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-graduate mr-2" style="color:#f59e0b;"></i>Alumnos PPD
                </h4>
                <small class="text-muted">Profesionalización Docente &nbsp;&middot;&nbsp; {{ $totalRecords }} registros</small>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                @role('admin')
                    <button type="button"
                        class="btn btn-sm shadow-sm text-white d-inline-flex align-items-center px-3 py-2 border-0 rounded-pill mr-2 mb-2 mb-sm-0"
                        style="background: linear-gradient(135deg, #1a4a8a 0%, #2563eb 45%, #3b82f6 100%); font-weight: 600; letter-spacing: 0.02em;"
                        data-toggle="modal" data-target="#modalExportarPpdExcel"
                        title="Elegir ciclos y vista previa antes de descargar Excel">
                        <i class="fas fa-file-excel mr-2" style="opacity: 0.95;"></i>
                        Exportar Excel
                    </button>
                @endrole
                <a href="{{ route('registerAdmin') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2 mb-sm-0">
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

            {{-- Filtros académicos --}}
            <div class="col-12 mb-3">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <h6 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-filter mr-1"></i>
                                Filtros académicos
                            </h6>
                            @if (request()->filled('programa_id') || request()->filled('ciclo_id') || request()->filled('search'))
                                <a href="{{ route('alumnosppd') }}" class="btn btn-sm btn-outline-secondary">
                                    Limpiar filtros
                                </a>
                            @endif
                        </div>
                        <p class="small text-muted mb-2 mb-md-3">Programa y ciclo se aplican en el servidor; la tabla
                            sigue agrupada por ciclo.</p>
                        <div class="mb-2">
                            <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Programa</span>
                            <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por programa">
                                <a href="{{ route('alumnosppd', $qBase) }}"
                                    class="btn btn-sm {{ !request()->filled('programa_id') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Todos
                                </a>
                                @foreach ($programasFiltro as $prog)
                                    @php $qProg = array_merge($qBase, ['programa_id' => $prog->id]); @endphp
                                    <a href="{{ route('alumnosppd', $qProg) }}"
                                        class="btn btn-sm {{ (int) request('programa_id') === (int) $prog->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ \Illuminate\Support\Str::limit($prog->nombre, 42) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @if ($ciclosFiltro->isNotEmpty())
                            <div class="mb-0">
                                <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Ciclo</span>
                                <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por ciclo">
                                    @php $qSinCiclo = array_merge($qBase, ['programa_id' => request('programa_id')]); @endphp
                                    <a href="{{ route('alumnosppd', $qSinCiclo) }}"
                                        class="btn btn-sm {{ !request()->filled('ciclo_id') ? 'btn-info' : 'btn-outline-info' }}">
                                        Todos los ciclos
                                    </a>
                                    @foreach ($ciclosFiltro as $cic)
                                        @php
                                            $qCic = array_merge($qBase, [
                                                'programa_id' => request('programa_id'),
                                                'ciclo_id' => $cic->id,
                                            ]);
                                            $nCiclo = optional($totalesPorCicloId->get($cic->id))->total;
                                        @endphp
                                        <a href="{{ route('alumnosppd', $qCic) }}"
                                            class="btn btn-sm {{ (int) request('ciclo_id') === (int) $cic->id ? 'btn-info' : 'btn-outline-info' }}">
                                            {{ $cic->nombre }}
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

            {{-- Buscador client-side --}}
            <div class="col-12 mb-2">
                <form id="searchForm" onsubmit="return false">
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm"
                            placeholder="Buscar por nombre, apellido o DNI..." id="searchInput"
                            value="{{ request('search') }}" autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search fa-xs mr-1"></i> Buscar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" type="button" id="clearButton"
                                style="{{ request('search') ? '' : 'display:none;' }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">La búsqueda recorre <strong>todos</strong> los alumnos PPD
                        (sin filtrar por programa ni ciclo).</p>
                    <p class="small text-info mb-0 mt-1" id="busquedaActivaPpd"
                        @if(empty($busquedaActiva)) style="display:none" @endif>
                        <i class="fas fa-info-circle"></i> Filtros de programa/ciclo no aplican mientras haya texto en el buscador.
                    </p>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="col-12">
                <div class="table-responsive" id="ppd-tabla-responsive">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">N°</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Detalles académicos</th>
                                <th scope="col">Matrícula</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grupoActual = null; $n = 1; @endphp
                            @forelse ($alumnos as $alumno)
                                @php
                                    $grupo =
                                        (optional($alumno->programa)->nombre
                                            ?? optional(optional($alumno->ciclo)->programa)->nombre
                                            ?? 'Sin programa') .
                                        ' — Ciclo ' .
                                        (optional($alumno->ciclo)->nombre ?? '?');
                                @endphp
                                @if ($grupo !== $grupoActual)
                                    @php
                                        $grupoActual = $grupo;
                                        $kGrupo =
                                            (string) ($alumno->programa_id ?? '0') .
                                            '|' .
                                            (string) ($alumno->ciclo_id ?? '0');
                                        $nGrupo = $conteoGrupoListado[$kGrupo] ?? 0;
                                        $nTotalCiclo = optional($totalesPorCicloId->get($alumno->ciclo_id))->total;
                                    @endphp
                                    <tr class="table-active">
                                        <td colspan="5" class="py-2">
                                            <strong>{{ $grupo }}</strong>
                                            <span class="badge badge-secondary ml-2">
                                                {{ $nGrupo }} {{ $nGrupo === 1 ? 'alumno' : 'alumnos' }}
                                            </span>
                                            @if ($nTotalCiclo !== null && (int) $nGrupo !== (int) $nTotalCiclo)
                                                <span class="text-muted small ml-2"
                                                    title="Total PPD en este ciclo (sin filtros de búsqueda)">
                                                    · {{ $nTotalCiclo }} en ciclo (total)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted font-weight-bold">{{ $n }}</td>
                                    <td>
                                        <strong>{{ $alumno->apellidos }}, {{ $alumno->name }}</strong>
                                        <ul class="mb-0 pl-3">
                                            <li>DNI: {{ $alumno->dni }}</li>
                                            <li>{{ $alumno->email }}</li>
                                            @if ($alumno->telefono)
                                                <li>Tel: {{ $alumno->telefono }}</li>
                                            @endif
                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="mb-0 pl-3">
                                            <li>{{ optional($alumno->programa)->nombre ?? optional(optional($alumno->ciclo)->programa)->nombre ?? '—' }}
                                                – {{ optional($alumno->ciclo)->nombre ?? '?' }}</li>
                                            @if ($alumno->lengua_1)
                                                <li>Lengua 1: {{ $alumno->lengua_1 }}</li>
                                            @endif
                                            @if ($alumno->fecha_nacimiento)
                                                <li>Nac.: {{ $alumno->fecha_nacimiento }}</li>
                                            @endif
                                        </ul>
                                    </td>
                                    <td>
                                        @if ($alumno->alumnoB)
                                            <span class="badge badge-success">✅ Completa</span>
                                            <br>
                                            <small class="text-muted">N°: {{ $alumno->alumnoB->numero ?? '—' }}</small>
                                        @else
                                            <span class="badge badge-secondary">❌ Sin matrícula</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($alumno->alumnoB)
                                            <a href="{{ route('ppd.show', $alumno->alumnoB->id) }}"
                                                class="btn btn-sm btn-primary" title="Ver">
                                                <i class="fa fa-eye fa-sm"></i>
                                            </a>
                                            <a href="{{ route('ppd.edit', $alumno->alumnoB->id) }}"
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fa fa-edit fa-sm"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('alumnos.edit', ['alumno' => $alumno->alumno->id ?? 0]) }}"
                                                class="btn btn-sm btn-warning" title="Editar usuario">
                                                <i class="fa fa-edit fa-sm"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmarEliminar('{{ route('adminDestroy', ['id' => $alumno->id]) }}', '{{ addslashes($alumno->apellidos . ', ' . $alumno->name) }}')"
                                            title="Eliminar">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                                @php $n++; @endphp
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No se encontraron alumnos PPD.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if (session('error'))
                        <span class="text-danger text-sm">{{ session('error') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal exportar Excel PPD --}}
    @role('admin')
        <div class="modal fade" id="modalExportarPpdExcel" tabindex="-1" role="dialog"
            aria-labelledby="modalExportarPpdExcelLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.alumnosppd.export-excel') }}"
                        id="formExportarPpdExcel">
                        @csrf
                        @foreach ($exportHidden as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="modal-header">
                            <h5 class="modal-title text-primary" id="modalExportarPpdExcelLabel">
                                <i class="fas fa-file-excel mr-2"></i>Exportar alumnos PPD a Excel
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <style>
                                #modalExportarPpdExcel .export-ciclos-scroll {
                                    max-height: min(58vh, 440px);
                                    overflow-y: auto;
                                    overflow-x: hidden;
                                    -webkit-overflow-scrolling: touch;
                                }
                                #modalExportarPpdExcel .export-ciclo-fila {
                                    cursor: pointer;
                                    transition: background-color .12s ease;
                                }
                                #modalExportarPpdExcel .export-ciclo-fila:hover {
                                    background-color: #e9ecef !important;
                                }
                            </style>
                            @if ($errors->has('ciclo_ids') || $errors->has('ciclo_ids.*'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('ciclo_ids') ?: $errors->first('ciclo_ids.*') }}
                                </div>
                            @endif
                            <p class="small text-muted mb-2">
                                Marca los <strong>ciclos</strong> que incluirán filas en el archivo. Se respeta el
                                filtro de búsqueda si lo aplicaste arriba.
                            </p>
                            @if (!empty($busquedaActiva))
                                <p class="small text-info mb-2">
                                    <i class="fas fa-info-circle"></i> Búsqueda activa: el archivo solo traerá alumnos
                                    que coincidan con el texto buscado.
                                </p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center mb-3 border-bottom pb-2">
                                <span class="small font-weight-bold text-secondary mr-2">Vista previa:</span>
                                <span class="badge badge-primary mr-2">
                                    <span id="ppdExportPreviewCiclos">0</span> ciclos
                                </span>
                                <span class="badge badge-secondary">
                                    ~<span id="ppdExportPreviewCount">0</span> alumnos PPD en esos ciclos
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm mb-3" role="group">
                                <button type="button" class="btn btn-outline-secondary"
                                    id="btnPpdExportSelTodos">Todos los ciclos</button>
                                <button type="button" class="btn btn-outline-secondary"
                                    id="btnPpdExportSelNinguno">Ninguno</button>
                                @if (request()->filled('programa_id'))
                                    <button type="button" class="btn btn-outline-primary"
                                        id="btnPpdExportSelProgramaActual">Solo programa filtrado</button>
                                @endif
                            </div>
                            @if ($ciclosParaExportacion->isEmpty())
                                <p class="text-muted mb-0">No hay ciclos con alumnos PPD para exportar.</p>
                            @else
                                <div class="export-ciclos-scroll border rounded bg-light px-2 py-2">
                                    @foreach ($ciclosParaExportacion->groupBy('programa_id') as $grupoCiclos)
                                        @php $primer = $grupoCiclos->first(); @endphp
                                        <div class="mb-2">
                                            <h6 class="small font-weight-bold text-dark mb-1 text-truncate border-left border-primary pl-2"
                                                style="border-width: 3px !important;"
                                                title="{{ $primer->programa->nombre ?? 'Programa #' . $primer->programa_id }}">
                                                {{ $primer->programa->nombre ?? 'Programa #' . $primer->programa_id }}
                                            </h6>
                                            <div class="row mx-n1">
                                                @foreach ($grupoCiclos as $cicExport)
                                                    @php
                                                        $nCicExport = optional($totalesPorCicloId->get($cicExport->id))->total;
                                                        $checked =
                                                            (int) request('ciclo_id') === (int) $cicExport->id ||
                                                            ((int) request('programa_id') === (int) $cicExport->programa_id &&
                                                                !request()->filled('ciclo_id'));
                                                    @endphp
                                                    <div class="col-md-6 px-1 mb-1">
                                                        <div class="export-ciclo-fila border rounded bg-white h-100 px-2 py-1">
                                                            <div class="custom-control custom-checkbox my-0">
                                                                <input type="checkbox"
                                                                    class="custom-control-input ppd-ciclo-export-check"
                                                                    name="ciclo_ids[]" value="{{ $cicExport->id }}"
                                                                    id="ppd_ciclo_export_{{ $cicExport->id }}"
                                                                    data-total="{{ (int) ($nCicExport ?? 0) }}"
                                                                    data-programa-id="{{ (int) $cicExport->programa_id }}"
                                                                    {{ $checked ? 'checked' : '' }}>
                                                                <label class="custom-control-label small mb-0"
                                                                    for="ppd_ciclo_export_{{ $cicExport->id }}">
                                                                    {{ $cicExport->nombre }}
                                                                    <span class="badge badge-light text-dark ml-1">
                                                                        {{ (int) ($nCicExport ?? 0) }}
                                                                    </span>
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
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-dismiss="modal">Cancelar</button>
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

    {{-- Modal confirmación eliminar --}}
    <div class="modal fade" id="confirmDeletePpdModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que quieres eliminar a <strong id="deleteNombrePpd"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a id="deleteLinkPpd" href="#" class="btn btn-sm btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmarEliminar(url, nombre) {
            document.getElementById('deleteLinkPpd').href = url;
            document.getElementById('deleteNombrePpd').textContent = nombre;
            $('#confirmDeletePpdModal').modal('show');
        }

        $(document).ready(function () {
            function refreshPpdPreview() {
                var ciclos = 0, total = 0;
                $('.ppd-ciclo-export-check:checked').each(function () {
                    ciclos++;
                    total += parseInt($(this).attr('data-total'), 10) || 0;
                });
                $('#ppdExportPreviewCiclos').text(ciclos);
                $('#ppdExportPreviewCount').text(total);
            }

            $(document).on('change', '.ppd-ciclo-export-check', refreshPpdPreview);

            $(document).on('click', '.export-ciclo-fila', function (e) {
                if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('label').length) return;
                var $cb = $(this).find('.ppd-ciclo-export-check');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });

            $('#btnPpdExportSelTodos').on('click', function () {
                $('.ppd-ciclo-export-check').prop('checked', true);
                refreshPpdPreview();
            });

            $('#btnPpdExportSelNinguno').on('click', function () {
                $('.ppd-ciclo-export-check').prop('checked', false);
                refreshPpdPreview();
            });

            $('#btnPpdExportSelProgramaActual').on('click', function () {
                var pid = {{ (int) request('programa_id', 0) }};
                $('.ppd-ciclo-export-check').each(function () {
                    $(this).prop('checked', parseInt($(this).attr('data-programa-id'), 10) === pid);
                });
                refreshPpdPreview();
            });

            $('#modalExportarPpdExcel').on('shown.bs.modal', refreshPpdPreview);

            @if ($errors->has('ciclo_ids') || $errors->has('ciclo_ids.*'))
                $('#modalExportarPpdExcel').modal('show');
            @endif

            refreshPpdPreview();
        });

        {{-- Buscador client-side PPD --}}
        window.filtrarAlumnosPpd = function filtrar(term) {
            var input      = document.getElementById('searchInput');
            var button     = document.getElementById('searchButton');
            var clearBtn   = document.getElementById('clearButton');
            var container  = document.getElementById('ppd-tabla-responsive');
            var aviso      = document.getElementById('busquedaActivaPpd');
            if (!input || !container) return;

            term = (term || '').toLowerCase().trim();
            var rows = container.querySelectorAll('tbody tr');
            var grupoActual = null, grupoVisible = false;
            rows.forEach(function (row) {
                if (row.classList.contains('table-active')) {
                    if (grupoActual) grupoActual.style.display = grupoVisible ? '' : 'none';
                    grupoActual = row; grupoVisible = false;
                } else {
                    var v = !term || row.textContent.toLowerCase().includes(term);
                    row.style.display = v ? '' : 'none';
                    if (v) grupoVisible = true;
                }
            });
            if (grupoActual) grupoActual.style.display = grupoVisible ? '' : 'none';
            if (aviso) aviso.style.display = term ? '' : 'none';
            if (clearBtn) clearBtn.style.display = term ? '' : 'none';
        };

        (function () {
            var input      = document.getElementById('searchInput');
            var button     = document.getElementById('searchButton');
            var clearBtn   = document.getElementById('clearButton');
            var container  = document.getElementById('ppd-tabla-responsive');
            var aviso      = document.getElementById('busquedaActivaPpd');
            if (!input || !container) return;

            if (input.value) window.filtrarAlumnosPpd(input.value);
            input.addEventListener('input', function () { window.filtrarAlumnosPpd(this.value); });

            if (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.filtrarAlumnosPpd(input.value);
                });
            }

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.filtrarAlumnosPpd(input.value);
                }
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    input.value = '';
                    window.filtrarAlumnosPpd('');
                    input.focus();
                });
            }
        }());
    </script>
@endsection