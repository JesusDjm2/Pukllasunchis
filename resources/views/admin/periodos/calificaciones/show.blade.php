@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
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

        .btn-editar-registro {
            opacity: 0.55;
            transition: opacity .15s;
        }
        .btn-editar-registro:hover {
            opacity: 1;
        }
    </style>

    <div class="container-fluid bg-white pt-3">
        @php
            $periodoId = $periodoActual->id ?? request()->route('id');
            $esSuperAdmin = auth()->user()?->hasRole('super-admin');
        @endphp
        <div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <h4 class="mb-3 text-primary font-weight-bold">Registros de Calificaciones - {{ $nombre }}</h4>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @if ($periodoActual->actual)
                    <a href="{{ route('periodos.export', $periodoId) }}" class="btn btn-success btn-sm shadow-sm">
                        <i class="fa fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="{{ route('periodos.export', ['id' => $periodoId, 'solo_becas' => 1]) }}"
                        class="btn btn-warning btn-sm shadow-sm">
                        <i class="fa fa-graduation-cap"></i> Exportar Solo Becas
                    </a>
                    <form action="{{ route('periodos.export', $periodoId) }}" method="GET"
                        class="d-flex align-items-center gap-1">
                        <select name="ciclo_id" class="form-select form-select-sm" style="width: auto;">
                            <option value="">-- Exportar por ciclo --</option>
                            @foreach ($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-info btn-sm shadow-sm">
                            <i class="fa fa-file-excel"></i> Exportar
                        </button>
                    </form>
                @endif
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm">
                    <i class="fa fa-arrow-left fa-sm"></i> Volver
                </a>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 mb-2">
                <input type="text" id="search" class="form-control form-control-sm"
                    placeholder="Buscar por alumno, DNI, correo, curso o ciclo...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaCalificaciones">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Alumno</th>
                        <th>Curso</th>
                        <th>Ciclo</th>
                        <th style="text-align: center">Valoración Curso</th>
                        <th style="text-align: center">Calificación Curso</th>
                        <th style="text-align: center">Calificación Sistema</th>
                        @if ($esSuperAdmin)
                            <th style="text-align: center; width: 60px;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $contador = 1; @endphp

                    @foreach ($filas as $entry)
                        @php
                            $alumno = $entry['alumno'];
                            $cursos = collect($entry['cursos']);
                            $periodosAlumno = $entry['periodos'];
                            if ($cursos->isEmpty()) {
                                $cursos = collect([null]);
                            }
                            $rowspan = $cursos->count();
                            $searchAlumno = strtolower(
                                trim(
                                    ($alumno->apellidos ?? '') .
                                        ' ' .
                                        ($alumno->nombres ?? '') .
                                        ' ' .
                                        ($alumno->email ?? '') .
                                        ' ' .
                                        ($alumno->dni ?? ''),
                                ),
                            );
                        @endphp

                        @foreach ($cursos as $i => $curso)
                            @php
                                $cursoNombre = $curso ? $curso->nombre ?? 'Sin asignar' : '— Sin cursos asignados —';
                                $cicloNombre = $curso ? optional($curso->ciclo)->nombre ?? 'No asignado' : '';
                                $periodo = $curso ? $periodosAlumno->firstWhere('curso_id', $curso->id) : null;
                                $bgColor = $periodo
                                    ? (is_null($periodo->calificacion_sistema)
                                        ? '#fff3cd'
                                        : ($periodo->calificacion_sistema > 11
                                            ? '#d4edda'
                                            : '#f8d7da'))
                                    : '#e2e3e5';
                                $searchFila = trim(strtolower($searchAlumno . ' ' . $cursoNombre . ' ' . $cicloNombre));
                            @endphp

                            <tr data-group="{{ $alumno->id }}" data-search="{{ $searchFila }}"
                                @if ($i === $rowspan - 1) style="border-bottom: 2px solid #000000;" @endif>
                                @if ($i === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $contador++ }}</td>
                                    <td rowspan="{{ $rowspan }}">
                                        <strong>{{ $alumno->apellidos }} {{ $alumno->nombres }}</strong>
                                        <ul style="padding-left: 1.2em; font-size: 14px;">
                                            <li>{{ $alumno->email ?? 'N/A' }}</li>
                                            <li>DNI: {{ $alumno->dni ?? 'N/A' }}</li>
                                            <li>Programa: {{ $alumno->programa->nombre ?? 'N/A' }}</li>
                                            <li>Beca: {{ $alumno->user->beca == 1 ? 'Sí' : 'No' }}</li>
                                            <li>Id: {{ $alumno->id }}</li>
                                        </ul>
                                    </td>
                                @endif
                                <td>{{ $cursoNombre }}</td>
                                <td>{{ $cicloNombre }}</td>
                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-valoracion">
                                    {{ $periodo->valoracion_curso ?? ($curso ? 'N/A' : '—') }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-cal-curso">
                                    {{ $periodo->calificacion_curso ?? ($curso ? 'N/A' : '—') }}
                                </td>
                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-cal-sistema">
                                    {{ $periodo->calificacion_sistema ?? ($curso ? 'Sin datos' : '—') }}
                                </td>
                                @if ($esSuperAdmin)
                                    <td style="text-align: center">
                                        @if ($periodo)
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning btn-editar-registro"
                                                title="Editar registro"
                                                data-registro-id="{{ $periodo->id }}"
                                                data-alumno="{{ trim(($alumno->apellidos ?? '').', '.($alumno->nombres ?? '')) }}"
                                                data-curso="{{ $cursoNombre }}"
                                                data-valoracion="{{ $periodo->valoracion_curso ?? '' }}"
                                                data-cal-curso="{{ $periodo->calificacion_curso ?? '' }}"
                                                data-cal-sistema="{{ $periodo->calificacion_sistema ?? '' }}">
                                                <i class="fa fa-edit fa-xs"></i>
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($esSuperAdmin)
        {{-- Modal de edición de registro (solo super-admin) --}}
        <div class="modal fade" id="modalEditarRegistro" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: #f8d7da; border-bottom: 2px solid #dc3545;">
                        <h5 class="modal-title font-weight-bold" style="color: #721c24;">
                            🔒 Registro de un período ya guardado
                        </h5>
                        <button type="button" class="close" aria-label="Cerrar" onclick="cerrarModalRegistro()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    {{-- Paso 1: advertencia de riesgo, sin campos editables aún --}}
                    <div id="regAdvertenciaGate">
                        <div class="modal-body">
                            <div class="alert alert-danger mb-3" role="alert" style="border-left: 5px solid #dc3545;">
                                <p class="font-weight-bold mb-2">
                                    <i class="fas fa-exclamation-triangle"></i> Esta acción es de alto riesgo
                                </p>
                                <p class="mb-2">
                                    Estás a punto de modificar un dato de calificaciones que ya fue guardado
                                    y forma parte del historial académico oficial del alumno. Este registro
                                    puede estar vinculado a actas, reportes o exportes ya generados.
                                </p>
                                <p class="mb-0">
                                    <strong>En realidad, este dato no debería cambiarse si no es
                                        estrictamente necesario.</strong> Edítalo solo si tienes total
                                    certeza de que el cambio es correcto y autorizado.
                                </p>
                            </div>
                            <p class="mb-1 font-weight-bold" id="modalRegAlumnoNombre" style="font-size: 0.92rem;"></p>
                            <p class="mb-0 text-muted small" id="modalRegCursoNombre"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="cerrarModalRegistro()">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-sm btn-danger font-weight-bold"
                                id="btnConfirmarRiesgoRegistro">
                                Entiendo el riesgo, continuar de todos modos
                            </button>
                        </div>
                    </div>

                    {{-- Paso 2: campos editables, ocultos hasta confirmar el riesgo --}}
                    <div id="regCamposEdicion" style="display:none;">
                        <div class="modal-body">
                            <p class="small mb-3"
                                style="color: #856404; background:#fff3cd; border-radius:4px; padding:8px 10px;">
                                Modificando registro guardado. <strong>Verifica bien el valor antes de guardar.</strong>
                            </p>

                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Valoración Curso</label>
                                <input type="text" id="inputRegValoracion" class="form-control form-control-sm"
                                    maxlength="255" placeholder="Ej: A, B, AD...">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Calificación Curso</label>
                                <input type="text" id="inputRegCalCurso" class="form-control form-control-sm"
                                    maxlength="255" placeholder="Ej: 14.5">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Calificación Sistema</label>
                                <input type="text" id="inputRegCalSistema" class="form-control form-control-sm"
                                    maxlength="255" placeholder="Ej: 14">
                            </div>

                            <div id="modalEditarRegistroError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="cerrarModalRegistro()">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-sm btn-warning font-weight-bold"
                                id="btnGuardarRegistro">
                                <i class="fas fa-save mr-1"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let _regId = null;
            let _regBtnOrigen = null;
            let _regModalInstance = null;
            const REG_CSRF = '{{ csrf_token() }}';
            const REG_BASE_URL = '{{ url("admin/periodo-registro") }}';

            function _regModalEl() {
                return document.getElementById('modalEditarRegistro');
            }

            function mostrarModalRegistro() {
                const el = _regModalEl();
                // Esta página convive con dos versiones de Bootstrap (la del layout
                // admin y la propia de esta vista); "getOrCreateInstance" solo existe
                // en Bootstrap 5.2+, así que evitamos depender de él y reusamos una
                // única instancia construida con "new bootstrap.Modal", compatible
                // con Bootstrap 4 y 5.
                if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                    if (!_regModalInstance) {
                        _regModalInstance = new bootstrap.Modal(el);
                    }
                    _regModalInstance.show();
                } else if (window.jQuery) {
                    jQuery(el).modal('show');
                } else {
                    el.classList.add('show');
                    el.style.display = 'block';
                }
            }

            function cerrarModalRegistro() {
                const el = _regModalEl();
                if (_regModalInstance) {
                    _regModalInstance.hide();
                } else if (window.jQuery) {
                    jQuery(el).modal('hide');
                } else {
                    el.classList.remove('show');
                    el.style.display = 'none';
                }
            }

            function abrirModalEditarRegistro(id, alumno, curso, valoracion, calCurso, calSistema, btn) {
                _regId = id;
                _regBtnOrigen = btn;

                document.getElementById('modalRegAlumnoNombre').textContent = alumno;
                document.getElementById('modalRegCursoNombre').textContent = 'Curso: ' + curso;
                document.getElementById('inputRegValoracion').value = valoracion !== null ? valoracion : '';
                document.getElementById('inputRegCalCurso').value = calCurso !== null ? calCurso : '';
                document.getElementById('inputRegCalSistema').value = calSistema !== null ? calSistema : '';
                document.getElementById('modalEditarRegistroError').classList.add('d-none');

                // Siempre inicia en el paso de advertencia, nunca directo a los campos
                document.getElementById('regAdvertenciaGate').style.display = '';
                document.getElementById('regCamposEdicion').style.display = 'none';

                mostrarModalRegistro();
            }

            // Delegación de eventos: lee los datos desde atributos data-*
            // (evita construir JavaScript a mano con datos del alumno, que puede
            // romperse con apóstrofes/comillas en nombres o valores).
            document.querySelectorAll('.btn-editar-registro').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const ds = btn.dataset;
                    abrirModalEditarRegistro(
                        ds.registroId,
                        ds.alumno || '',
                        ds.curso || '',
                        ds.valoracion || null,
                        ds.calCurso !== '' ? ds.calCurso : null,
                        ds.calSistema !== '' ? ds.calSistema : null,
                        btn
                    );
                });
            });

            document.getElementById('btnConfirmarRiesgoRegistro').addEventListener('click', function() {
                document.getElementById('regAdvertenciaGate').style.display = 'none';
                document.getElementById('regCamposEdicion').style.display = '';
            });

            document.getElementById('btnGuardarRegistro').addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…';

                const payload = {
                    valoracion_curso: document.getElementById('inputRegValoracion').value || null,
                    calificacion_curso: document.getElementById('inputRegCalCurso').value || null,
                    calificacion_sistema: document.getElementById('inputRegCalSistema').value || null,
                };

                fetch(REG_BASE_URL + '/' + _regId, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': REG_CSRF,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.ok) throw new Error(data.message || 'Error desconocido');

                        const fila = _regBtnOrigen.closest('tr');
                        fila.querySelector('.celda-valoracion').textContent = data.valoracion_curso ?? 'N/A';
                        fila.querySelector('.celda-cal-curso').textContent = data.calificacion_curso ?? 'N/A';
                        fila.querySelector('.celda-cal-sistema').textContent = data.calificacion_sistema ??
                            'Sin datos';

                        // Actualizar también los data-* del botón: si se vuelve a
                        // abrir "editar" sin recargar la página, debe jalar el
                        // valor recién guardado, no el que tenía al cargar la página.
                        _regBtnOrigen.dataset.valoracion = data.valoracion_curso ?? '';
                        _regBtnOrigen.dataset.calCurso = data.calificacion_curso ?? '';
                        _regBtnOrigen.dataset.calSistema = data.calificacion_sistema ?? '';

                        cerrarModalRegistro();
                    })
                    .catch(err => {
                        const errDiv = document.getElementById('modalEditarRegistroError');
                        errDiv.textContent = err.message || 'Ocurrió un error al guardar.';
                        errDiv.classList.remove('d-none');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Guardar cambios';
                    });
            });
        </script>
    @endif

    {{-- JS: filtro por grupo (alumno) en tiempo real --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');

            function buildGroups() {
                const allRows = Array.from(document.querySelectorAll('#tablaCalificaciones tbody tr'));
                const grupos = {};

                allRows.forEach(row => {
                    const gid = row.dataset.group ?? 'no-group';
                    if (!grupos[gid]) grupos[gid] = {
                        rows: [],
                        searchText: ''
                    };
                    grupos[gid].rows.push(row);
                    grupos[gid].searchText += ' ' + ((row.dataset.search || '').toLowerCase());
                });

                return grupos;
            }

            let grupos = buildGroups();

            function filtrar() {
                const q = (searchInput.value || '').toLowerCase().trim();
                Object.values(grupos).forEach(gr => {
                    const match = q === '' ? true : gr.searchText.includes(q);
                    gr.rows.forEach(r => r.style.display = match ? '' : 'none');
                });
            }

            searchInput.addEventListener('input', filtrar);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
@endsection
