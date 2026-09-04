@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <style>
        :root {
            --reg-navy: #1E3A5F;
            --reg-blue: #2E5C8A;
            --reg-border: #D9E2EC;
            --reg-zebra: #F4F7FB;
            --reg-ok: #0B954E;
            --reg-fail: #DC3545;
            --reg-pend: #997404;
        }

        .table-responsive {
            max-height: 60vh;
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

        .grupo-ciclo-tabla thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: #fff;
            z-index: 10;
            pointer-events: none;
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

        /* ── Barra de estadísticas generales ── */
        .reg-stats-bar {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        @media (max-width: 767.98px) {
            .reg-stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .reg-stat-card {
            background: #fff;
            border: 1px solid var(--reg-border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .reg-stat-card .num {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .reg-stat-card .lbl {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #6c757d;
        }

        .reg-stat-card.ok .num {
            color: var(--reg-ok);
        }

        .reg-stat-card.fail .num {
            color: var(--reg-fail);
        }

        .reg-stat-card.pend .num {
            color: var(--reg-pend);
        }

        /* ── Navegación rápida por programa ── */
        .reg-quicknav {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .reg-quicknav-pill {
            border: 1px solid var(--reg-border);
            background: #fff;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--reg-navy);
            cursor: pointer;
            transition: background .15s, color .15s, transform .1s;
        }

        .reg-quicknav-pill:hover {
            background: var(--reg-navy);
            color: #fff;
        }

        .reg-quicknav-pill:active {
            transform: scale(.97);
        }

        /* ── Acordeón Programa (nivel 1) ── */
        .grupo-programa {
            border: 1px solid var(--reg-border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #fff;
        }

        .grupo-programa-header {
            background: linear-gradient(135deg, var(--reg-navy), #274a73);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            flex-wrap: wrap;
        }

        .grupo-programa-header:hover {
            filter: brightness(1.08);
        }

        .grupo-programa-titulo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.02rem;
        }

        .grupo-chevron {
            transition: transform .3s ease;
            display: inline-block;
        }

        .grupo-programa-header[aria-expanded="false"] .grupo-chevron,
        .grupo-ciclo-header[aria-expanded="false"] .grupo-chevron {
            transform: rotate(-90deg);
        }

        .grupo-programa-body {
            overflow: hidden;
        }

        .grupo-programa-body-inner {
            padding: 12px;
        }

        /* ── Acordeón Ciclo (nivel 2) ── */
        .grupo-ciclo {
            border: 1px solid var(--reg-border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .grupo-ciclo:last-child {
            margin-bottom: 0;
        }

        .grupo-ciclo-header {
            background: var(--reg-blue);
            color: #fff;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            flex-wrap: wrap;
        }

        .grupo-ciclo-header:hover {
            filter: brightness(1.08);
        }

        .grupo-ciclo-titulo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: .92rem;
        }

        .grupo-ciclo-body {
            overflow: hidden;
        }

        .grupo-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .grupo-badge {
            background: rgba(255, 255, 255, .16);
            border-radius: 999px;
            padding: 2px 10px;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .grupo-badge.ok {
            background: rgba(11, 149, 78, .35);
        }

        .grupo-badge.fail {
            background: rgba(220, 53, 69, .4);
        }

        .estado-chip {
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            display: inline-block;
        }

        .estado-chip.ok {
            background: #d4edda;
            color: #0B954E;
        }

        .estado-chip.fail {
            background: #f8d7da;
            color: #DC3545;
        }

        .estado-chip.pend {
            background: #e2e3e5;
            color: #6c757d;
        }

        .grupo-oculto-busqueda {
            display: none !important;
        }

        #regSinResultados {
            display: none;
        }

        @media (max-width: 575.98px) {
            .grupo-programa-titulo {
                font-size: .92rem;
            }

            .grupo-ciclo-titulo {
                font-size: .84rem;
            }

            #search {
                width: 100% !important;
            }
        }
    </style>

    <div class="container-fluid bg-white pt-3">
        @php
            $periodoId = $periodoActual->id ?? request()->route('id');
            $esSuperAdmin = auth()->user()?->hasRole('super-admin');
        @endphp
        <div class="d-sm-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h4 class="mb-3 text-primary font-weight-bold">Registros de Calificaciones - {{ $nombre }}</h4>
            <div class="d-flex align-items-center flex-wrap gap-2">
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
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger shadow-sm">
                    <i class="fa fa-arrow-left fa-sm"></i> Volver
                </a>
            </div>
        </div>
        {{-- ── Resumen general del período ── --}}
        <div class="reg-stats-bar mb-3" id="regStatsBar">
            <div class="reg-stat-card">
                <span class="num">{{ $statsGenerales['alumnos'] }}</span>
                <span class="lbl">Alumnos evaluados</span>
            </div>
            <div class="reg-stat-card">
                <span class="num">{{ $statsGenerales['cursos'] }}</span>
                <span class="lbl">Registros curso</span>
            </div>
            <div class="reg-stat-card ok">
                <span class="num">{{ $statsGenerales['aprobados'] }}</span>
                <span class="lbl">Aprobados</span>
            </div>
            <div class="reg-stat-card fail">
                <span class="num">{{ $statsGenerales['desaprobados'] }}</span>
                <span class="lbl">Desaprobados</span>
            </div>
            <div class="reg-stat-card pend">
                <span class="num">{{ $statsGenerales['sinDatos'] }}</span>
                <span class="lbl">Sin datos</span>
            </div>
        </div>
        {{-- ── Búsqueda + navegación rápida + expandir/colapsar ── --}}
        <div class="row mb-2 align-items-center">
            <div class="col-md-6 mb-2">
                <input type="text" id="search" class="form-control form-control-sm"
                    placeholder="Buscar por alumno, DNI, correo, curso, ciclo o programa...">
            </div>
            <div class="col-md-6 mb-2 d-flex justify-content-md-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExpandirTodo">
                    <i class="fa fa-angles-down"></i> Expandir todo
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnColapsarTodo">
                    <i class="fa fa-angles-up"></i> Colapsar todo
                </button>
            </div>
        </div>
        <div class="reg-quicknav mb-3" id="regQuicknav">
            @foreach ($grupos as $programaNombre => $grupoPrograma)
                <span class="reg-quicknav-pill" data-target="#prog-{{ $loop->index }}">
                    {{ $programaNombre }} ({{ $grupoPrograma['stats']['alumnos'] }})
                </span>
            @endforeach
        </div>
        <div id="regSinResultados" class="alert alert-secondary text-center">
            <i class="fa fa-magnifying-glass mr-1"></i> No se encontraron registros para tu búsqueda.
        </div>
        {{-- ── Clasificación por Programa → Ciclo ── --}}
        <div id="regGrupos">
            @forelse ($grupos as $programaNombre => $grupoPrograma)
                <div class="grupo-programa" id="prog-{{ $loop->index }}" data-nombre="{{ strtolower($programaNombre) }}">
                    <div class="grupo-programa-header" role="button" tabindex="0" aria-expanded="true">
                        <div class="grupo-programa-titulo">
                            <i class="fa fa-chevron-down grupo-chevron"></i>
                            <i class="fa fa-building-columns"></i>
                            {{ $programaNombre }}
                        </div>
                        <div class="grupo-badges">
                            <span class="grupo-badge">{{ $grupoPrograma['stats']['alumnos'] }} alumnos</span>
                            <span class="grupo-badge ok">{{ $grupoPrograma['stats']['aprobados'] }} aprob.</span>
                            <span class="grupo-badge fail">{{ $grupoPrograma['stats']['desaprobados'] }} desaprob.</span>
                        </div>
                    </div>
                    <div class="grupo-programa-body">
                        <div class="grupo-programa-body-inner">
                            @foreach ($grupoPrograma['ciclos'] as $cicloNombre => $grupoCiclo)
                                <div class="grupo-ciclo" data-nombre="{{ strtolower($cicloNombre) }}">
                                    <div class="grupo-ciclo-header" role="button" tabindex="0" aria-expanded="true">
                                        <div class="grupo-ciclo-titulo">
                                            <i class="fa fa-chevron-down grupo-chevron"></i>
                                            <i class="fa fa-layer-group"></i>
                                            Ciclo {{ $cicloNombre }}
                                        </div>
                                        <div class="grupo-badges">
                                            <span class="grupo-badge">{{ $grupoCiclo['stats']['alumnos'] }} alumnos</span>
                                            <span class="grupo-badge ok">{{ $grupoCiclo['stats']['aprobados'] }} aprob.</span>
                                            <span class="grupo-badge fail">{{ $grupoCiclo['stats']['desaprobados'] }} desaprob.</span>
                                        </div>
                                    </div>
                                    <div class="grupo-ciclo-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0 grupo-ciclo-tabla">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th>Alumno</th>
                                                        <th>Curso</th>
                                                        <th style="text-align: center">Valoración Curso</th>
                                                        <th style="text-align: center">Calificación Curso</th>
                                                        <th style="text-align: center">Calificación Sistema</th>
                                                        <th style="text-align: center">Estado</th>
                                                        @if ($esSuperAdmin)
                                                            <th style="text-align: center; width: 60px;"></th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $contador = 1; @endphp
                                                    @foreach ($grupoCiclo['filas'] as $entry)
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
                                                                        ($alumno->dni ?? '') .
                                                                        ' ' .
                                                                        $programaNombre .
                                                                        ' ' .
                                                                        $cicloNombre,
                                                                ),
                                                            );
                                                        @endphp

                                                        @foreach ($cursos as $i => $curso)
                                                            @php
                                                                $cursoNombre = $curso ? $curso->nombre ?? 'Sin asignar' : '— Sin cursos asignados —';
                                                                $periodo = $curso ? $periodosAlumno->firstWhere('curso_id', $curso->id) : null;
                                                                $calSistema = $periodo->calificacion_sistema ?? null;
                                                                $bgColor = $periodo
                                                                    ? (is_null($calSistema)
                                                                        ? '#fff3cd'
                                                                        : ($calSistema > 11
                                                                            ? '#d4edda'
                                                                            : '#f8d7da'))
                                                                    : '#e2e3e5';
                                                                $estadoTipo = ! $periodo
                                                                    ? null
                                                                    : (is_null($calSistema)
                                                                        ? 'pend'
                                                                        : ($calSistema > 11 ? 'ok' : 'fail'));
                                                                $estadoTexto = $estadoTipo === 'ok'
                                                                    ? 'Aprobado'
                                                                    : ($estadoTipo === 'fail'
                                                                        ? 'Desaprobado'
                                                                        : 'Sin datos');
                                                                $searchFila = trim(strtolower($searchAlumno . ' ' . $cursoNombre));
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
                                                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-valoracion">
                                                                    {{ $periodo->valoracion_curso ?? ($curso ? 'N/A' : '—') }}
                                                                </td>
                                                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-cal-curso">
                                                                    {{ $periodo->calificacion_curso ?? ($curso ? 'N/A' : '—') }}
                                                                </td>
                                                                <td style="text-align: center; background-color: {{ $bgColor }}" class="celda-cal-sistema">
                                                                    {{ $calSistema ?? ($curso ? 'Sin datos' : '—') }}
                                                                </td>
                                                                <td style="text-align: center">
                                                                    @if ($estadoTipo)
                                                                        <span class="estado-chip {{ $estadoTipo }}">{{ $estadoTexto }}</span>
                                                                    @else
                                                                        —
                                                                    @endif
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
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-secondary text-center">No hay registros de calificaciones en este período.</div>
            @endforelse
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

    {{-- ── GSAP (con fallback sin animación si el CDN no carga) ── --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        (function() {
            'use strict';

            var hasGsap = typeof window.gsap !== 'undefined';

            /* ── Acordeón Programa/Ciclo con animación de altura ── */
            function abrirBody(header, body) {
                header.setAttribute('aria-expanded', 'true');
                body.dataset.collapsed = 'false';
                body.style.display = 'block';
                var alto = body.scrollHeight;

                if (hasGsap) {
                    gsap.killTweensOf(body);
                    gsap.fromTo(body, {
                        height: 0,
                        opacity: 0
                    }, {
                        height: alto,
                        opacity: 1,
                        duration: 0.35,
                        ease: 'power2.out',
                        onComplete: function() {
                            body.style.height = 'auto';
                        }
                    });
                } else {
                    body.style.height = 'auto';
                    body.style.opacity = 1;
                }
            }

            function cerrarBody(header, body) {
                header.setAttribute('aria-expanded', 'false');
                body.dataset.collapsed = 'true';
                var alto = body.scrollHeight;

                if (hasGsap) {
                    gsap.killTweensOf(body);
                    gsap.fromTo(body, {
                        height: alto,
                        opacity: 1
                    }, {
                        height: 0,
                        opacity: 0,
                        duration: 0.3,
                        ease: 'power2.in',
                        onComplete: function() {
                            body.style.display = 'none';
                        }
                    });
                } else {
                    body.style.display = 'none';
                }
            }

            function toggleBody(header, body) {
                if (body.dataset.collapsed === 'true') {
                    abrirBody(header, body);
                } else {
                    cerrarBody(header, body);
                }
            }

            function engancharAcordeon(headerSelector, bodySelectorRelativo) {
                document.querySelectorAll(headerSelector).forEach(function(header) {
                    var body = header.nextElementSibling;
                    if (!body) return;
                    body.dataset.collapsed = 'false';

                    function activar(e) {
                        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
                        e.preventDefault();
                        toggleBody(header, body);
                    }
                    header.addEventListener('click', activar);
                    header.addEventListener('keydown', activar);
                });
            }

            engancharAcordeon('.grupo-programa-header');
            engancharAcordeon('.grupo-ciclo-header');

            document.getElementById('btnExpandirTodo').addEventListener('click', function() {
                document.querySelectorAll('.grupo-programa-header, .grupo-ciclo-header').forEach(function(header) {
                    var body = header.nextElementSibling;
                    if (body && body.dataset.collapsed === 'true') abrirBody(header, body);
                });
            });

            document.getElementById('btnColapsarTodo').addEventListener('click', function() {
                document.querySelectorAll('.grupo-ciclo-header, .grupo-programa-header').forEach(function(header) {
                    var body = header.nextElementSibling;
                    if (body && body.dataset.collapsed !== 'true') cerrarBody(header, body);
                });
            });

            /* ── Navegación rápida: hace scroll y expande el programa objetivo ── */
            document.querySelectorAll('.reg-quicknav-pill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    var target = document.querySelector(pill.dataset.target);
                    if (!target) return;
                    var header = target.querySelector('.grupo-programa-header');
                    var body = header.nextElementSibling;
                    if (body && body.dataset.collapsed === 'true') abrirBody(header, body);
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            /* ── Animación de entrada de las secciones al cargar ── */
            if (hasGsap) {
                try {
                    gsap.from('.grupo-programa', {
                        opacity: 0,
                        y: 16,
                        duration: 0.45,
                        stagger: 0.08,
                        ease: 'power2.out',
                        clearProps: 'opacity,transform',
                    });
                } catch (e) {}
            }

            /* ── Búsqueda en tiempo real: filtra filas y oculta grupos vacíos ── */
            var searchInput = document.getElementById('search');
            var sinResultados = document.getElementById('regSinResultados');
            var estadoPrevioGuardado = false;

            function guardarEstadoPrevio() {
                document.querySelectorAll('.grupo-programa-header, .grupo-ciclo-header').forEach(function(header) {
                    var body = header.nextElementSibling;
                    if (body) body.dataset.collapsedPrevio = body.dataset.collapsed;
                });
                estadoPrevioGuardado = true;
            }

            function restaurarEstadoPrevio() {
                if (!estadoPrevioGuardado) return;
                document.querySelectorAll('.grupo-programa-header, .grupo-ciclo-header').forEach(function(header) {
                    var body = header.nextElementSibling;
                    if (!body) return;
                    var eraColapsado = body.dataset.collapsedPrevio === 'true';
                    if (eraColapsado && body.dataset.collapsed !== 'true') {
                        cerrarBody(header, body);
                    } else if (!eraColapsado && body.dataset.collapsed === 'true') {
                        abrirBody(header, body);
                    }
                });
                estadoPrevioGuardado = false;
            }

            function filtrar() {
                var q = (searchInput.value || '').toLowerCase().trim();

                if (q !== '' && !estadoPrevioGuardado) {
                    guardarEstadoPrevio();
                } else if (q === '' && estadoPrevioGuardado) {
                    restaurarEstadoPrevio();
                }

                var huboResultados = false;

                document.querySelectorAll('.grupo-programa').forEach(function(grupoPrograma) {
                    var visibleEnPrograma = false;

                    grupoPrograma.querySelectorAll('.grupo-ciclo').forEach(function(grupoCiclo) {
                        var visibleEnCiclo = false;

                        grupoCiclo.querySelectorAll('tbody tr').forEach(function(row) {
                            var match = q === '' ? true : (row.dataset.search || '').includes(q);
                            row.style.display = match ? '' : 'none';
                            if (match) visibleEnCiclo = true;
                        });

                        grupoCiclo.classList.toggle('grupo-oculto-busqueda', !visibleEnCiclo);
                        if (visibleEnCiclo) {
                            visibleEnPrograma = true;
                            if (q !== '') {
                                var cHeader = grupoCiclo.querySelector('.grupo-ciclo-header');
                                var cBody = cHeader.nextElementSibling;
                                if (cBody.dataset.collapsed === 'true') abrirBody(cHeader, cBody);
                            }
                        }
                    });

                    grupoPrograma.classList.toggle('grupo-oculto-busqueda', !visibleEnPrograma);
                    if (visibleEnPrograma) {
                        huboResultados = true;
                        if (q !== '') {
                            var pHeader = grupoPrograma.querySelector('.grupo-programa-header');
                            var pBody = pHeader.nextElementSibling;
                            if (pBody.dataset.collapsed === 'true') abrirBody(pHeader, pBody);
                        }
                    }
                });

                sinResultados.style.display = (q !== '' && !huboResultados) ? 'block' : 'none';
            }

            if (searchInput) {
                searchInput.addEventListener('input', filtrar);
            }
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
@endsection
