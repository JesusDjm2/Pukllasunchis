@extends('layouts.admin')
@section('contenido')
    <style>
        #tablaCalificaciones thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: #fff;
            z-index: 10;
            pointer-events: none;
        }

        .table-responsive {
            max-height: 75vh;
            overflow-y: auto;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #aaa;
            border-radius: 4px;
        }

        td[rowspan] {
            border-bottom: 2px solid #000 !important;
        }

        .badge-calificacion {
            font-size: 0.85em;
            padding: 0.25em 0.6em;
        }

        .programa-header {
            background-color: #e9ecef;
            padding: 10px;
            border-left: 4px solid #17a2b8;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .btn-editar-registro {
            opacity: 0.55;
            transition: opacity .15s;
        }
        .btn-editar-registro:hover {
            opacity: 1;
        }
    </style>
    <div class="container-fluid bg-white pt-3">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0 text-primary font-weight-bold">
                <i class="fas fa-calendar-alt text-info"></i> Período PPD: {{ $periodo->nombre }}
                @if ($periodo->actual)
                    <span class="badge badge-success ml-2">ACTUAL</span>
                @endif
            </h4>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm">
                    <i class="fa fa-arrow-left fa-sm"></i> Volver
                </a>
                <a href="{{ route('periodos.admin.ppd.export', $periodo->id) }}" class="btn btn-success btn-sm shadow-sm">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>

        {{-- Alerta de precaución --}}
        {{-- <div class="alert border-0 mb-4" role="alert"
            style="background: #fff3cd; border-left: 5px solid #e0a800 !important; border-radius: 6px;">
            <div class="d-flex align-items-start">
                <span style="font-size: 1.4rem; line-height: 1; margin-right: 10px;">⚠️</span>
                <div>
                    <strong style="color: #856404; font-size: 0.92rem;">Registros sensibles — edite con precaución</strong>
                    <p class="mb-0 mt-1" style="color: #856404; font-size: 0.85rem;">
                        Las calificaciones de este período están vinculadas a los alumnos PPD.
                        Modificarlas manualmente puede generar <strong>inconsistencias con el sistema de notas</strong>.
                        Use el botón <i class="fa fa-edit fa-xs"></i> solo cuando sea estrictamente necesario.
                    </p>
                </div>
            </div>
        </div> --}}

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body">
                        <h6 class="text-primary">Total Registros</h6>
                        <h2 class="font-weight-bold">{{ $totalRegistros }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100">
                    <div class="card-body">
                        <h6 class="text-success">Promedio Calificación</h6>
                        <h2 class="font-weight-bold">{{ number_format($promedioCalificacion, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100">
                    <div class="card-body">
                        <h6 class="text-info">Total Alumnos</h6>
                        <h2 class="font-weight-bold">{{ $totalAlumnos }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-body">
                        <h6 class="text-warning">Total Cursos</h6>
                        <h2 class="font-weight-bold">{{ $totalCursos }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Búsqueda -->
        <div class="row mb-3">
            <div class="col-md-12">
                <input type="text" id="search" class="form-control form-control-sm"
                    placeholder="Buscar por apellidos, nombres, DNI, email, curso...">
            </div>
        </div>

        <!-- Listado por programas -->
        @foreach ($registrosPorPrograma as $programaNombre => $alumnosDelPrograma)
            <div class="programa-header" id="programa-{{ Str::slug($programaNombre) }}">
                <h5 class="font-weight-bold mb-0">
                    <i class="fas fa-graduation-cap text-info"></i> PROGRAMA: {{ $programaNombre }}
                    <small class="badge badge-info">{{ count($alumnosDelPrograma) }} alumnos</small>
                </h5>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover" id="tablaCalificaciones">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Ciclo</th>
                            <th style="text-align: center; width: 120px;">Cal. Curso</th>
                            <th style="text-align: center; width: 120px;">Cal. Sistema</th>
                            <th style="text-align: center; width: 110px;">Nv. Desempeño</th>
                            <th style="text-align: center; width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $contadorPrograma = 1; @endphp

                        @foreach ($alumnosDelPrograma as $alumnoData)
                            @php
                                $alumno  = $alumnoData['alumno'];
                                $cursos  = collect($alumnoData['cursos']);
                                $rowspan = $cursos->count();
                                $alumnoSearch = strtolower(trim(
                                    ($alumno->apellidos ?? '') . ' ' .
                                    ($alumno->nombres ?? ($alumno->name ?? '')) . ' ' .
                                    ($alumno->email ?? '') . ' ' .
                                    ($alumno->dni ?? '')
                                ));
                            @endphp

                            @foreach ($cursos as $i => $cursoData)
                                @php
                                    $curso      = $cursoData['curso'];
                                    $registro   = $cursoData['registro'];
                                    $cursoNombre = $curso ? $curso->nombre : 'Sin curso';
                                    $cicloNombre = $curso && $curso->ciclo ? $curso->ciclo->nombre : 'No asignado';
                                    $filaSearch  = strtolower(trim(
                                        $alumnoSearch . ' ' . $cursoNombre . ' ' . $cicloNombre . ' ' . $programaNombre
                                    ));
                                @endphp

                                <tr data-group="{{ $programaNombre }}-{{ $alumno->id }}"
                                    data-search="{{ $filaSearch }}"
                                    data-registro-id="{{ $registro?->id ?? '' }}"
                                    @if ($i === $rowspan - 1) style="border-bottom: 2px solid #dee2e6;" @endif>

                                    @if ($i === 0)
                                        <td rowspan="{{ $rowspan }}">{{ $contadorPrograma++ }}</td>
                                        <td rowspan="{{ $rowspan }}">
                                            <strong>{{ $alumno->apellidos ?? '' }},
                                                {{ $alumno->nombres ?? ($alumno->name ?? '') }}</strong>
                                            <ul style="padding-left: 1.2em; font-size: 14px; margin-bottom: 0;">
                                                <li>{{ $alumno->email ?? 'N/A' }}</li>
                                                <li>DNI: {{ $alumno->dni ?? 'N/A' }}</li>
                                                <li>ID: {{ $alumno->id }}</li>
                                            </ul>
                                        </td>
                                    @endif

                                    <td>{{ $cursoNombre }}</td>
                                    <td>{{ $cicloNombre }}</td>

                                    <td style="text-align: center;" class="celda-cal-curso">
                                        @if ($registro && $registro->calificacion_curso !== null)
                                            <span>{{ $registro->calificacion_curso }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;" class="celda-cal-sistema">
                                        @if ($registro && $registro->calificacion_sistema !== null)
                                            <span>{{ $registro->calificacion_sistema }}</span>
                                        @else
                                            <span class="text-muted">Sin datos</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;" class="celda-nivel">
                                        @if ($registro && $registro->nivel_desempeno !== null)
                                            <span>{{ $registro->nivel_desempeno }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center;">
                                        @if ($registro)
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning btn-editar-registro"
                                                title="Editar calificación"
                                                onclick="abrirModalEditar(
                                                    {{ $registro->id }},
                                                    '{{ addslashes($alumno->apellidos ?? '') }}, {{ addslashes($alumno->nombres ?? ($alumno->name ?? '')) }}',
                                                    '{{ addslashes($cursoNombre) }}',
                                                    {{ $registro->calificacion_curso ?? 'null' }},
                                                    {{ $registro->calificacion_sistema ?? 'null' }},
                                                    {{ $registro->nivel_desempeno ?? 'null' }},
                                                    this, null, null, null
                                                )">
                                                <i class="fa fa-edit fa-xs"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-editar-registro"
                                                title="Agregar calificación"
                                                onclick="abrirModalEditar(
                                                    null,
                                                    '{{ addslashes($alumno->apellidos ?? '') }}, {{ addslashes($alumno->nombres ?? ($alumno->name ?? '')) }}',
                                                    '{{ addslashes($cursoNombre) }}',
                                                    null, null, null,
                                                    this,
                                                    {{ $alumno->id }},
                                                    {{ $curso ? $curso->id : 'null' }},
                                                    {{ $periodo->id }}
                                                )">
                                                <i class="fa fa-plus fa-xs"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if (empty($registrosPorPrograma))
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No hay registros de calificaciones para este período.
            </div>
        @endif
    </div>

    {{-- Modal de edición de calificación --}}
    <div class="modal fade" id="modalEditarCalificacion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #fff3cd; border-bottom: 2px solid #e0a800;">
                    <h5 class="modal-title font-weight-bold" id="modalEditarTitulo" style="color: #856404;">
                        ⚠️ Editar calificación — precaución
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                        onclick="$('#modalEditarCalificacion').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="small mb-3" id="modalEditarAviso"
                        style="color: #856404; background:#fff3cd; border-radius:4px; padding:8px 10px;">
                        Estás modificando un registro de calificaciones vinculado al sistema de notas PPD.
                        <strong>Solo edita si tienes certeza absoluta del cambio.</strong>
                    </p>

                    <p class="mb-2 font-weight-bold" id="modalAlumnoNombre" style="font-size: 0.92rem;"></p>
                    <p class="mb-3 text-muted small" id="modalCursoNombre"></p>

                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Calificación Curso <span class="text-muted">(0–20)</span></label>
                        <input type="number" id="inputCalCurso" class="form-control form-control-sm"
                            min="0" max="20" step="0.01" placeholder="Ej: 14.5">
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Calificación Sistema <span class="text-muted">(0–20)</span></label>
                        <input type="number" id="inputCalSistema" class="form-control form-control-sm"
                            min="0" max="20" step="0.01" placeholder="Ej: 14">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Nivel Desempeño <span class="text-muted">(entero 0–4)</span></label>
                        <input type="number" id="inputNivelDesempeno" class="form-control form-control-sm"
                            min="0" max="4" step="1" placeholder="Ej: 3">
                    </div>

                    <div id="modalEditarError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"
                        onclick="$('#modalEditarCalificacion').modal('hide')">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-warning font-weight-bold" id="btnGuardarCalificacion">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ── Edición / creación de calificación ──────────────────────────────
        let _registroActualId = null;
        let _btnOrigen        = null;
        let _alumnoId         = null;
        let _cursoId          = null;
        let _periodoId        = null;
        const CSRF            = '{{ csrf_token() }}';
        const BASE_URL        = '{{ url("admin/periodo-ppd-registro") }}';

        function abrirModalEditar(registroId, alumno, curso, calCurso, calSistema, nivelDesempeno, btn, alumnoId, cursoId, periodoId) {
            _registroActualId = registroId;
            _btnOrigen        = btn;
            _alumnoId         = alumnoId  ?? null;
            _cursoId          = cursoId   ?? null;
            _periodoId        = periodoId ?? null;

            const esNuevo = registroId === null;
            document.getElementById('modalEditarTitulo').textContent = esNuevo
                ? 'Agregar calificación'
                : '⚠️ Editar calificación — precaución';
            document.getElementById('modalEditarAviso').style.display = esNuevo ? 'none' : '';

            document.getElementById('modalAlumnoNombre').textContent     = alumno;
            document.getElementById('modalCursoNombre').textContent      = 'Curso: ' + curso;
            document.getElementById('inputCalCurso').value               = calCurso    !== null ? calCurso    : '';
            document.getElementById('inputCalSistema').value             = calSistema  !== null ? calSistema  : '';
            document.getElementById('inputNivelDesempeno').value         = nivelDesempeno !== null ? nivelDesempeno : '';
            document.getElementById('modalEditarError').classList.add('d-none');

            $('#modalEditarCalificacion').modal('show');
        }

        document.getElementById('btnGuardarCalificacion').addEventListener('click', function () {
            const btn    = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…';

            const esNuevo = _registroActualId === null;
            const url     = esNuevo ? BASE_URL : BASE_URL + '/' + _registroActualId;
            const method  = esNuevo ? 'POST' : 'PATCH';

            const payload = {
                calificacion_curso:   document.getElementById('inputCalCurso').value    || null,
                calificacion_sistema: document.getElementById('inputCalSistema').value  || null,
                nivel_desempeno:      document.getElementById('inputNivelDesempeno').value || null,
            };
            if (esNuevo) {
                payload.alumno_id             = _alumnoId;
                payload.curso_id              = _cursoId;
                payload.periodo_actual_ppd_id = _periodoId;
            }

            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) throw new Error(data.message || 'Error desconocido');

                const fila = _btnOrigen.closest('tr');
                const cc   = data.calificacion_curso   !== null ? data.calificacion_curso   : null;
                const cs   = data.calificacion_sistema !== null ? data.calificacion_sistema : null;
                const nd   = data.nivel_desempeno      !== null ? data.nivel_desempeno      : null;

                fila.querySelector('.celda-cal-curso').innerHTML   = cc !== null ? '<span>' + cc + '</span>' : '<span class="text-muted">N/A</span>';
                fila.querySelector('.celda-cal-sistema').innerHTML = cs !== null ? '<span>' + cs + '</span>' : '<span class="text-muted">Sin datos</span>';
                fila.querySelector('.celda-nivel').innerHTML       = nd !== null ? '<span>' + nd + '</span>' : '<span class="text-muted">N/A</span>';

                // Si era creación, convertir el botón + en botón editar con el nuevo id
                if (esNuevo && data.id) {
                    _btnOrigen.className = 'btn btn-sm btn-outline-warning btn-editar-registro';
                    _btnOrigen.title     = 'Editar calificación';
                    const newId = data.id;
                    const alumnoNombre = document.getElementById('modalAlumnoNombre').textContent;
                    const cursoNombre  = document.getElementById('modalCursoNombre').textContent.replace('Curso: ', '');
                    _btnOrigen.setAttribute('onclick',
                        `abrirModalEditar(${newId}, '${alumnoNombre.replace(/'/g,"\\'")}', '${cursoNombre.replace(/'/g,"\\'")}', ${cc ?? 'null'}, ${cs ?? 'null'}, ${nd ?? 'null'}, this, null, null, null)`
                    );
                    _btnOrigen.innerHTML = '<i class="fa fa-edit fa-xs"></i>';
                }

                $('#modalEditarCalificacion').modal('hide');
            })
            .catch(err => {
                const errDiv = document.getElementById('modalEditarError');
                errDiv.textContent = err.message || 'Ocurrió un error al guardar.';
                errDiv.classList.remove('d-none');
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-save mr-1"></i> Guardar cambios';
            });
        });

        // ── Filtro de búsqueda en tiempo real ────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search');

            function buildGroups() {
                const allRows = Array.from(document.querySelectorAll('#tablaCalificaciones tbody tr'));
                const grupos = {};
                allRows.forEach(row => {
                    const groupId = row.dataset.group || 'no-group';
                    if (!grupos[groupId]) grupos[groupId] = { rows: [], searchText: '' };
                    grupos[groupId].rows.push(row);
                    grupos[groupId].searchText += ' ' + (row.dataset.search || '').toLowerCase();
                });
                return grupos;
            }

            let grupos = buildGroups();

            function filtrar() {
                const query = (searchInput.value || '').toLowerCase().trim();
                const programaHeaders = document.querySelectorAll('.programa-header');
                programaHeaders.forEach(h => h.style.display = 'block');

                Object.keys(grupos).forEach(groupId => {
                    const grupo = grupos[groupId];
                    const match = query === '' || grupo.searchText.includes(query);
                    grupo.rows.forEach(row => row.style.display = match ? '' : 'none');

                    if (!match) {
                        const programaNombre = groupId.split('-')[0];
                        const programaHeader = document.getElementById('programa-' + programaNombre.toLowerCase().replace(/\s+/g, '-'));
                        const otrosGrupos = Object.keys(grupos).filter(g => g.startsWith(programaNombre + '-') && g !== groupId);
                        const algunOtroVisible = otrosGrupos.some(id => grupos[id].rows.some(r => r.style.display !== 'none'));
                        if (programaHeader && !algunOtroVisible) {
                            const todosOcultos = Object.keys(grupos).filter(g => g.startsWith(programaNombre + '-')).every(gid => grupos[gid].rows.every(r => r.style.display === 'none'));
                            if (todosOcultos) programaHeader.style.display = 'none';
                        }
                    }
                });

                if (query === '') programaHeaders.forEach(h => h.style.display = 'block');
            }

            searchInput.addEventListener('input', filtrar);

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                searchInput.value = urlParams.get('search');
                filtrar();
            }
        });
    </script>
@endsection
