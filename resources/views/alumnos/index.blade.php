@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <style>
        .alumno-avatar-thumb {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            cursor: zoom-in;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .alumno-avatar-thumb:hover {
            transform: scale(1.06);
            border-color: #4e73df;
        }

        .alumno-avatar-empty {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f5;
            color: #9aa0a6;
            border: 2px solid #dee2e6;
        }

        #alumnoPhotoModal .modal-content {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        #alumnoPhotoModal .modal-header {
            border-bottom: 0;
            justify-content: center;
            padding-bottom: 0.5rem;
        }

        #alumnoPhotoModal .modal-title {
            width: 100%;
            text-align: center;
            color: #fff;
        }

        #alumnoPhotoModal .close {
            position: absolute;
            right: 0.5rem;
            top: 0.35rem;
            color: #fff;
            opacity: 0.9;
            text-shadow: none;
        }

        #alumnoPhotoModal .close:hover {
            color: #fff;
            opacity: 1;
        }
    </style>
    @php
        $qBase = array_filter(
            [
                'search' => request('search'),
                'search_page' => request('search_page'),
                'with_user' => request('with_user'),
                'solo_becas' => request('solo_becas'),
            ],
            fn($v) => $v !== null && $v !== '',
        );
        $exportHidden = array_filter(
            [
                'search' => request('search'),
                'search_page' => request('search_page'),
                'with_user' => request('with_user'),
                'periodo_id' => $periodoFiltroId,
                'solo_becas' => request('solo_becas'),
            ],
            fn($v) => $v !== null && $v !== '',
        );
        $limpiarFiltrosBase = array_filter(
            ['with_user' => request('with_user'), 'solo_becas' => request('solo_becas')],
            fn($v) => $v !== null && $v !== '',
        );
    @endphp
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-graduation-cap mr-2" style="color:#4e73df;"></i>Alumnos FID{{ $soloBecas ? ' &mdash; Solo Becas' : '' }}
                    @if($soloBecas)
                        <span class="badge badge-success ml-1" style="font-size:.65rem; vertical-align:middle;">Becas</span>
                    @endif
                </h4>
                <small class="text-muted">Formación Inicial Docente &nbsp;&middot;&nbsp; {{ $totalRecords }} registros</small>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                @role('admin')
                    @php
                        $becasToggleUrl = $soloBecas
                            ? route('adminAlumnos', array_filter(['with_user' => request('with_user'), 'periodo_id' => $periodoFiltroId], fn($v) => $v !== null && $v !== ''))
                            : route('adminAlumnos', array_filter(['with_user' => request('with_user'), 'periodo_id' => $periodoFiltroId, 'solo_becas' => '1'], fn($v) => $v !== null && $v !== ''));
                    @endphp
                    <a href="{{ $becasToggleUrl }}"
                       class="btn btn-sm {{ $soloBecas ? 'btn-success' : 'btn-outline-success' }} shadow-sm mr-2 mb-2 mb-sm-0"
                       title="{{ $soloBecas ? 'Ver todos los alumnos FID' : 'Filtrar solo alumnos becados' }}">
                        <i class="fas fa-star mr-1"></i>
                        {{ $soloBecas ? 'Ver todos' : 'Solo Becas' }}
                    </a>
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
                                <a href="{{ route('adminAlumnos', $limpiarFiltrosBase) }}"
                                    class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
                            @endif
                        </div>
                        <p class="small text-muted mb-2 mb-md-3">Programa y ciclo se aplican en el servidor; la tabla
                            sigue agrupada por ciclo.</p>
                        <div class="mb-3">
                            <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">
                                <i class="fas fa-calendar-alt mr-1"></i>Periodo de matrícula
                            </span>
                            <div class="btn-group flex-wrap mt-1" role="group">
                                @foreach ($todosLosPeriodos as $periodo)
                                    @php $qPer = array_merge($qBase, ['periodo_id' => $periodo->id]); @endphp
                                    <a href="{{ route('adminAlumnos', $qPer) }}"
                                        class="btn btn-sm {{ (int) $periodoFiltroId === (int) $periodo->id ? 'btn-dark' : 'btn-outline-dark' }}">
                                        {{ $periodo->nombre }}
                                        @if ($periodo->actual)
                                            <span class="badge badge-success ml-1">Actual</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Programa</span>
                            <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por programa">
                                <a href="{{ route('adminAlumnos', $qBase) }}"
                                    class="btn btn-sm {{ !request()->filled('programa_id') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
                                @foreach ($programasFiltro as $prog)
                                    @php $qProg = array_merge($qBase, ['programa_id' => $prog->id]); @endphp
                                    <a href="{{ route('adminAlumnos', $qProg) }}"
                                        class="btn btn-sm {{ (int) request('programa_id') === (int) $prog->id ? 'btn-primary' : 'btn-outline-primary' }}">{{ \Illuminate\Support\Str::limit($prog->nombre, 42) }}</a>
                                @endforeach
                            </div>
                        </div>
                        @if ($ciclosFiltro->isNotEmpty())
                            <div class="mb-0">
                                <span class="small font-weight-bold text-secondary mr-2 d-block d-sm-inline">Ciclo</span>
                                <div class="btn-group flex-wrap mt-1" role="group" aria-label="Filtrar por ciclo">
                                    @php $qSinCiclo = array_merge($qBase, ['programa_id' => request('programa_id')]); @endphp
                                    <a href="{{ route('adminAlumnos', $qSinCiclo) }}"
                                        class="btn btn-sm {{ !request()->filled('ciclo_id') ? 'btn-info' : 'btn-outline-info' }}">Todos los ciclos</a>
                                    @foreach ($ciclosFiltro as $cic)
                                        @php
                                            $qCic = array_merge($qBase, ['programa_id' => request('programa_id'), 'ciclo_id' => $cic->id]);
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
                            <p class="small text-muted mb-0">La búsqueda recorre <strong>todos</strong> los alumnos FID
                                (sin filtrar por programa ni ciclo).</p>
                            <p class="small text-info mb-0 mt-1" id="busquedaActivaFid"
                                @if(empty($busquedaActiva)) style="display:none" @endif>
                                <i class="fas fa-info-circle"></i> Filtros de programa/ciclo no aplican mientras haya texto en el buscador.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                @include('alumnos._tabla_fid')
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
                                <i class="fas fa-file-excel mr-2"></i>
                                Exportar alumnos FID{{ $soloBecas ? ' Becas' : '' }} a Excel
                                @if($soloBecas)
                                    <span class="badge badge-success ml-1" style="font-size:.65rem;">Solo Becas</span>
                                @endif
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <style>
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
                                <div class="alert alert-danger">{{ $errors->first('ciclo_ids') ?: $errors->first('ciclo_ids.*') }}</div>
                            @endif
                            <p class="small text-muted mb-2">
                                Marca los <strong>ciclos</strong> que incluirán filas en el archivo. Se respeta el filtro
                                de búsqueda y "con usuario" si los aplicaste arriba; no se usan los botones de
                                programa/ciclo de la tabla para acotar el Excel (solo lo que elijas aquí).
                            </p>
                            @if (!empty($busquedaActiva))
                                <p class="small text-info mb-2"><i class="fas fa-info-circle"></i> Búsqueda activa: el
                                    archivo solo traerá alumnos que coincidan con el texto; la vista previa numérica
                                    usa totales por ciclo (todos los FID de ese ciclo).</p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center mb-3 border-bottom pb-2">
                                <span class="small font-weight-bold text-secondary mr-2">Vista previa:</span>
                                <span class="badge badge-primary mr-2"><span id="exportPreviewCiclos">0</span> ciclos</span>
                                <span class="badge badge-secondary">~<span id="exportPreviewCount">0</span> alumnos FID en esos ciclos</span>
                            </div>
                            <div class="btn-group btn-group-sm mb-3" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="btnExportSelTodos">Todos los ciclos</button>
                                <button type="button" class="btn btn-outline-secondary" id="btnExportSelNinguno">Ninguno</button>
                                @if (request()->filled('programa_id'))
                                    <button type="button" class="btn btn-outline-primary" id="btnExportSelProgramaActual">Solo programa filtrado</button>
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
                                                        $nCicExport = optional($totalesPorCicloId->get($cicExport->id))->total;
                                                        $checked = (int) request('ciclo_id') === (int) $cicExport->id ||
                                                            ((int) request('programa_id') === (int) $cicExport->programa_id && !request()->filled('ciclo_id'));
                                                    @endphp
                                                    <div class="col-md-6 px-1 mb-1">
                                                        <div class="export-ciclo-fila border rounded bg-white h-100 px-2 py-1">
                                                            <div class="custom-control custom-checkbox my-0">
                                                                <input type="checkbox" class="custom-control-input ciclo-export-check"
                                                                    name="ciclo_ids[]" value="{{ $cicExport->id }}"
                                                                    id="ciclo_export_{{ $cicExport->id }}"
                                                                    data-total="{{ (int) ($nCicExport ?? 0) }}"
                                                                    data-programa-id="{{ (int) $cicExport->programa_id }}"
                                                                    {{ $checked ? 'checked' : '' }}>
                                                                <label class="custom-control-label small mb-0" for="ciclo_export_{{ $cicExport->id }}">
                                                                    {{ $cicExport->nombre }}
                                                                    <span class="badge badge-light text-dark ml-1">{{ (int) ($nCicExport ?? 0) }}</span>
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
                            <button type="submit" class="btn btn-sm btn-success" {{ $ciclosParaExportacion->isEmpty() ? 'disabled' : '' }}>
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
                var ciclos = 0, total = 0;
                $('.ciclo-export-check:checked').each(function() {
                    ciclos++;
                    total += parseInt($(this).attr('data-total'), 10) || 0;
                });
                $('#exportPreviewCiclos').text(ciclos);
                $('#exportPreviewCount').text(total);
            }
            $(document).on('change', '.ciclo-export-check', refreshExportPreview);
            $(document).on('click', '.export-ciclo-fila', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) return;
                if ($(e.target).closest('label').length) return;
                var $cb = $(this).find('.ciclo-export-check');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });
            $('#btnExportSelTodos').on('click', function() { $('.ciclo-export-check').prop('checked', true); refreshExportPreview(); });
            $('#btnExportSelNinguno').on('click', function() { $('.ciclo-export-check').prop('checked', false); refreshExportPreview(); });
            $('#btnExportSelProgramaActual').on('click', function() {
                var pid = {{ (int) request('programa_id', 0) }};
                $('.ciclo-export-check').each(function() { $(this).prop('checked', parseInt($(this).attr('data-programa-id'), 10) === pid); });
                refreshExportPreview();
            });
            $('#modalExportarAlumnosExcel').on('shown.bs.modal', refreshExportPreview);
            @if ($errors->has('ciclo_ids') || $errors->has('ciclo_ids.*'))
                $('#modalExportarAlumnosExcel').modal('show');
            @endif
            refreshExportPreview();

            $(document).on('click', '.relacionar-usuario', function() {
                var alumnoId = $(this).data('alumno-id');
                relacionarUsuario(alumnoId);
            });

            function relacionarUsuario(alumnoId) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('relacionarUsuario', ['alumno' => '__ALUMNO_ID__']) }}'.replace('__ALUMNO_ID__', alumnoId),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) { alert('Relación con usuario establecida correctamente.'); asignarRolAlumno(alumnoId); location.reload(); },
                    error: function(error) { alert('Error al establecer la relación con el usuario.'); }
                });
            }

            function asignarRolAlumno(alumnoId) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('asignarRolAlumno', ['alumno' => '__ALUMNO_ID__']) }}'.replace('__ALUMNO_ID__', alumnoId),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) { console.log('Rol asignado correctamente.'); },
                    error: function(error) { console.error('Error al asignar el rol.'); }
                });
            }

            $(document).on('click', '.js-open-photo-modal', function() {
                var src = $(this).data('photo-src');
                var name = $(this).data('photo-name') || 'Foto del estudiante';
                if (!src) return;
                $('#alumnoPhotoModalImg').attr('src', src);
                $('#alumnoPhotoModalLabel').text(name);
                $('#alumnoPhotoModal').modal('show');
            });

            $('#alumnoPhotoModal').on('hidden.bs.modal', function() {
                $('#alumnoPhotoModalImg').attr('src', '');
                $('#alumnoPhotoModalLabel').text('');
            });

            $(document).on('click', '#alumnoPhotoModalClose', function() { $('#alumnoPhotoModal').modal('hide'); });
        });
    </script>

    {{-- Buscador client-side — FID --}}
    <script>
    window.filtrarAlumnosFid = function filtrar(term) {
        var input      = document.getElementById('searchInput');
        var button     = document.getElementById('searchButton');
        var clearBtn   = document.getElementById('clearButton');
        var container  = document.getElementById('fid-tabla-responsive');
        var aviso      = document.getElementById('busquedaActivaFid');
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
        var container  = document.getElementById('fid-tabla-responsive');
        var aviso      = document.getElementById('busquedaActivaFid');
        if (!input || !container) return;

        if (input.value) window.filtrarAlumnosFid(input.value);

        input.addEventListener('input', function () { window.filtrarAlumnosFid(this.value); });

        if (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                window.filtrarAlumnosFid(input.value);
            });
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.filtrarAlumnosFid(input.value);
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = '';
                window.filtrarAlumnosFid('');
                input.focus();
            });
        }
    }());
    </script>

    <div class="modal fade" id="alumnoPhotoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="alumnoPhotoModalLabel">Foto del estudiante</h5>
                    <button type="button" class="close" id="alumnoPhotoModalClose" data-dismiss="modal"
                        aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="alumnoPhotoModalImg" src=""
                        style="max-width: 100%; max-height: 75vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
    <iframe name="carnetDownloadFrame" style="display:none;"></iframe>

    <script>
        document.querySelectorAll('.js-quitar-matricula').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var nombre = form.dataset.nombre;
                Swal.fire({
                    title: '¿Quitar matrícula?',
                    html: 'Se eliminará la matrícula del período actual de <strong>' + nombre + '</strong>.<br>Esta acción no elimina al alumno del sistema.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e67e22',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar',
                    cancelButtonText: 'Cancelar',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection