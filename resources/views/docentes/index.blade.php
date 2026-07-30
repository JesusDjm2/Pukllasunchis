@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <style>
        .quitarCurso {
            border: none;
            color: #e74a3b;
            background: none;
            font-size: 13px
        }

        .quitarCurso:hover {
            color: #e74a3b;
            transition: 0.4s ease;
            text-decoration: underline
        }

        /* ── Diferenciación FID / PPD (funciona en light, dim y dark) ── */
        .curso-fid-item {
            border-left: 4px solid #28a745;
            padding-left: 10px !important;
            border-radius: 0 4px 4px 0;
            background-color: rgba(40, 167, 69, 0.05);
            transition: background-color .2s;
        }
        .curso-ppd-item {
            border-left: 4px solid #f59e0b;
            padding-left: 10px !important;
            border-radius: 0 4px 4px 0;
            background-color: rgba(245, 158, 11, 0.07);
            transition: background-color .2s;
        }

        /* dark / dim mode: fondo un poco más visible */
        .dark-mode .curso-fid-item,
        .dim-mode  .curso-fid-item {
            background-color: rgba(40, 167, 69, 0.13);
        }
        .dark-mode .curso-ppd-item,
        .dim-mode  .curso-ppd-item {
            background-color: rgba(245, 158, 11, 0.15);
        }

        /* Badges FID / PPD */
        .badge-tipo {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            letter-spacing: .04em;
            vertical-align: middle;
        }
        .badge-fid {
            background-color: #28a745;
            color: #fff;
        }
        .badge-ppd {
            background-color: #f59e0b;
            color: #fff;
        }
        /* en modo oscuro los badges quedan con texto oscuro para contraste */
        .dark-mode .badge-ppd,
        .dim-mode  .badge-ppd {
            background-color: #fbbf24;
            color: #1a1a1a;
        }
        .dark-mode .badge-fid,
        .dim-mode  .badge-fid {
            background-color: #34d058;
            color: #1a1a1a;
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-2 pt-3 pb-1">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-chalkboard-teacher mr-2" style="color:#06b6d4;"></i>Lista de Docentes
                </h4>
                <small class="text-muted">{{ $docentes->count() }} docentes registrados &mdash; gestión de carga horaria e incidencias</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2" style="gap:8px">
                <a href="{{ route('admin.becas.calificaciones.export') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm"
                    title="Calificaciones en vivo (Parcial 1, Parcial 2, Desempeño) de alumnos becarios">
                    <i class="fa fa-graduation-cap fa-sm mr-1"></i> Exportar Calificaciones Becas
                </a>
                @if ($periodoActual)
                    <a href="{{ route('periodos.export', $periodoActual->id) }}"
                        class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"
                        title="Solo disponible una vez archivado el periodo (botón 'Crear' en Periodos)">
                        <i class="fa fa-file-excel fa-sm mr-1"></i> Exportar Excel ({{ $periodoActual->nombre }})
                    </a>
                    <form action="{{ route('periodos.export', $periodoActual->id) }}" method="GET"
                        class="d-none d-sm-flex align-items-center gap-1">
                        <select name="ciclo_id" class="form-select form-select-sm" style="width: auto;">
                            <option value="">-- Exportar por ciclo --</option>
                            @foreach ($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-info btn-sm shadow-sm">
                            <i class="fa fa-file-excel fa-sm"></i> Exportar
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.incidencias.todas') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                    <i class="fa fa-exclamation-triangle fa-sm mr-1"></i> Ver todas las incidencias →
                    {{ $totalIncidencias }}
                </a>
                <a href="{{ route('registerAdmin') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fa fa-plus fa-sm mr-1"></i> Crear nuevo Docente
                </a>
            </div>
            {{-- <a href="{{ route('calificaciones.eliminarTodas') }}" class="btn btn-sm btn-info"
                onclick="return confirm('¿Estás seguro de que deseas eliminar todas las calificaciones?')">
                <i class="fa fa-trash"></i> Eliminar calificaciones
            </a> --}}
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
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ Session::get('error') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="col-12 mb-4">
                <div class="row g-3">
                    <!-- Periodo 1 -->
                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 text-center">
                            <div class="card-body">
                                <h6 class="text-primary fw-bold">
                                    <i class="fa fa-calendar"></i> Periodo 1
                                </h6>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-eliminar"
                                    data-form="formPeriodo1" data-mensaje="Se eliminarán TODOS los datos del Periodo 1">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                                <form id="formPeriodo1" action="{{ route('periodouno.eliminar') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Periodo 2 -->
                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 text-center">
                            <div class="card-body">
                                <h6 class="text-info fw-bold">
                                    <i class="fa fa-calendar"></i> Periodo 2
                                </h6>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-eliminar"
                                    data-form="formPeriodo2" data-mensaje="Se eliminarán TODOS los datos del Periodo 2">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                                <form id="formPeriodo2" action="{{ route('periododos.eliminar') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Periodo 3 -->
                    <div class="col-md-3">
                        <div class="card shadow-sm h-100 text-center">
                            <div class="card-body">
                                <h6 class="text-success fw-bold">
                                    <i class="fa fa-chart-line"></i> Desempeño Final
                                </h6>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-eliminar"
                                    data-form="formPeriodo3" data-mensaje="Se eliminará el desempeño final">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                                <form id="formPeriodo3" action="{{ route('periodotres.eliminar') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Acción Global -->
                    <div class="col-md-3">
                        <div class="card border-danger shadow h-100 text-center">
                            <div class="card-body">
                                <h6 class="text-secondary fw-bold">
                                    <i class="fa fa-link"></i> Asignaciones FID
                                </h6>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2" data-form="formGlobal"
                                    data-mensaje="⚠️ Esto eliminará TODOS los cursos asignados de FID a TODOS los docentes">
                                    <i class="fa fa-unlink"></i> Quitar designación a docentes PPD
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-eliminar"
                                    data-form=""
                                    data-mensaje="⚠️ Esto eliminará TODOS los cursos asignados a TODOS los docentes">
                                    <i class="fa fa-unlink"></i> Quitar designación a docentes FID
                                </button>
                                <form id="formGlobal" action="{{ route('docente.cursos.eliminarTodosGlobal') }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    document.querySelectorAll('.btn-eliminar').forEach(btn => {

                        btn.addEventListener('click', function() {

                            let formId = this.dataset.form;
                            let mensaje = this.dataset.mensaje;

                            Swal.fire({
                                title: '¿Estás seguro?',
                                text: mensaje,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    document.getElementById(formId).submit();
                                }

                            });

                        });

                    });

                });
            </script>


            {{-- <div class="col-lg-12">
                <table class="table table-bordered">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>Periodo 1</th>
                            <th>Periodo 2</th>
                            <th>Desempeño Final</th>
                            <th><i class="fa fa-unlink text-danger"></i> Quitar Asignaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <form action="{{ route('periodouno.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 1?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Periodo 1</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('periododos.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 2?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Periodo 2</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('periodotres.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los datos de Periodo 3?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Eliminar Desempeño</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('docente.cursos.eliminarTodosGlobal') }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('⚠️ Esto quitará TODOS los cursos de TODOS los docentes. ¿Estás 100% seguro?')">
                                        Quitar TODOS los cursos asignados ss<i class="fa fa-unlink text-danger"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div> --}}

        </div>
        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Buscar por nombre, dni o email...">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive table-container">
                    <table class="table table-bordered" id="docentes-table">
                        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 2;">
                            <tr>
                                <th>ID</th>
                                <th>Docente</th>
                                <th>Cursos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $docentesOrdenados = $docentes->sortBy('nombre');
                                $docenteCount = 0;
                            @endphp
                            @foreach ($docentesOrdenados as $docente)
                                @php
                                    $docenteCount++;
                                @endphp
                                <tr style="border-bottom: 2.2px solid #919191 !important">
                                    <td>{{ $docente->id }}</td>
                                    <td>
                                        <div style="position: sticky; top:2em">
                                            <strong>{{ $docente->nombre }}</strong>
                                            @php
                                                $totalEnviadas = $docente->incidencias->count();
                                                $totalRecibidas = $docente->user?->hasRole('tutor')
                                                    ? $docente->user->tutorCiclos->sum(fn($c) => $c->incidencias->count())
                                                    : 0;
                                            @endphp
                                            <ul class="mt-1">
                                                <li>Email: {{ $docente->email }}</li>
                                                <li>DNI: {{ $docente->dni }}</li>
                                                @if ($docente->user?->hasRole('tutor'))
                                                    <li class="mt-1">
                                                        <span>
                                                            <i class="fa fa-user-shield fa-xs mr-1"></i> Tutor:
                                                        </span>
                                                        @foreach ($docente->user->tutorCiclos as $cicloTutor)
                                                            @php
                                                                $progNombre = $cicloTutor->programa->nombre ?? '';
                                                                $progAbrev = match(true) {
                                                                    str_contains($progNombre, 'Inicial') => 'INI',
                                                                    str_contains($progNombre, 'EIB')     => 'EIB',
                                                                    default => $progNombre,
                                                                };
                                                            @endphp
                                                            <span class="badge badge-pill badge-secondary ml-1"
                                                                style="font-size:10px">
                                                                @if ($progAbrev) {{ $progAbrev }} · @endif{{ $cicloTutor->nombre }}
                                                            </span>
                                                        @endforeach
                                                    </li>
                                                @endif
                                                <li>
                                                    @if ($totalEnviadas > 0)
                                                        <a href="{{ route('admin.docente.incidencias', $docente->id) }}">
                                                            <i class="fa fa-exclamation-triangle fa-xs mr-1"></i>
                                                            Incidencias enviadas: {{ $totalEnviadas }}                                                             
                                                        </a>
                                                    @else
                                                        <span>
                                                            <i class="fa fa-exclamation-triangle fa-xs mr-1"></i>
                                                            Incidencias enviadas: 0
                                                        </span>
                                                    @endif
                                                </li>
                                                @if ($docente->user?->hasRole('tutor'))
                                                    <li>
                                                        @if ($totalRecibidas > 0)
                                                            <a href="{{ route('admin.docente.incidencias', $docente->id) }}">
                                                                <i class="fa fa-inbox fa-xs mr-1"></i>
                                                                Incidencias recibidas: {{ $totalRecibidas }}
                                                            </a>
                                                        @else
                                                            <span>
                                                                <i class="fa fa-inbox fa-xs mr-1"></i>
                                                                Incidencias recibidas: 0
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($docente->cursos->count() > 0)
                                            <ul>
                                                @php
                                                    $cursosOrdenados = $docente->cursos->sortBy('nombre');
                                                @endphp
                                                @foreach ($cursosOrdenados as $curso)
                                                    @php
                                                        $esPPD = str_contains(
                                                            $curso->ciclo->programa->nombre ?? '',
                                                            'PPD',
                                                        );
                                                        $claseCurso = $esPPD ? 'curso-ppd-item' : 'curso-fid-item';
                                                        $rutaCompetencias = $esPPD
                                                            ? route('competencias.calificar.ppd', [
                                                                'docente' => $docente->id,
                                                                'curso' => $curso->id,
                                                            ])
                                                            : route('competencias.calificar', [
                                                                'docente' => $docente->id,
                                                                'curso' => $curso->id,
                                                            ]);
                                                    @endphp
                                                    <li class="mb-2 {{ $claseCurso }}">
                                                        <strong>{{ $curso->nombre }}</strong>
                                                        <span class="badge-tipo {{ $esPPD ? 'badge-ppd' : 'badge-fid' }}">
                                                            {{ $esPPD ? 'PPD' : 'FID' }}
                                                        </span>
                                                        (
                                                        {{ $curso->ciclo->programa->nombre }} -
                                                        {{ $curso->ciclo->nombre }})
                                                        <ul>
                                                            <li>
                                                                @if (str_contains($curso->cc, 'Extracurricular') === false)
                                                                    @if ($curso->relacionsilabo || $curso->silabo)
                                                                        @php
                                                                            $sílaboURL = $curso->relacionsilabo
                                                                                ? route(
                                                                                    'silabos.show',
                                                                                    $curso->relacionsilabo->id,
                                                                                )
                                                                                : asset(
                                                                                    'docentes/silabo/' . $curso->silabo,
                                                                                );
                                                                            $tipoSílabo = $curso->relacionsilabo
                                                                                ? 'Creado desde sistema'
                                                                                : 'Archivo subido en PDF';
                                                                        @endphp
                                                                        <a href="{{ $sílaboURL }}" class="mb-2"
                                                                            style="font-size: 13px" target="_blank">
                                                                            Ver Sílabo <i class="fa fa-pdf"></i>
                                                                        </a>
                                                                        <small
                                                                            class="text-muted">({{ $tipoSílabo }})</small>
                                                                    @else
                                                                        <span style="font-size: 13px">No hay sílabo
                                                                            disponible.</span>
                                                                    @endif
                                                                @else
                                                                    <span style="font-size: 13px">Sin sílabo por
                                                                        Extracurricular!</span>
                                                                @endif
                                                            </li>
                                                        </ul>
                                                        @if ($curso->competenciasSeleccionadas->count() > 0)
                                                            <ul>
                                                                <li>
                                                                    <a style="font-size: 13px"
                                                                        href="{{ route('curso.gestionar.competencias', $curso->id) }}"
                                                                        class="text-primary">
                                                                        Editar competencias a calificar <i
                                                                            class="fa fa-sm fa-tasks"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        @elseif ($curso->competencias->count() > 3)
                                                            <ul>
                                                                <li>
                                                                    <a href="{{ route('curso.gestionar.competencias', $curso->id) }}"
                                                                        class="text-danger">
                                                                        <small>Elegir competencias (max 3) <i
                                                                                class="fa fa-sm fa-tasks"></i></small>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        @endif
                                                        <ul>
                                                            <form action="{{ $rutaCompetencias }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="docente_id"
                                                                    value="{{ $docente->id }}">
                                                                <input type="hidden" name="curso_id"
                                                                    value="{{ $curso->id }}">

                                                                @if ($curso->competenciasSeleccionadas->isNotEmpty())
                                                                    <ul style="display:none">
                                                                        @foreach ($curso->competenciasSeleccionadas as $competencia)
                                                                            <li>{{ $competencia->nombre }}</li>
                                                                            <input type="hidden" name="competencias[]"
                                                                                value="{{ $competencia->id }}">
                                                                        @endforeach
                                                                    </ul>
                                                                @elseif ($curso->competencias->count() <= 3)
                                                                    <ul style="display:none">
                                                                        @foreach ($curso->competencias as $competencia)
                                                                            <li>{{ $competencia->nombre }}</li>
                                                                            <input type="hidden" name="competencias[]"
                                                                                value="{{ $competencia->id }}">
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                                <li>
                                                                    <button type="submit" class="text-primary"
                                                                        id="{{ $curso->id }}"
                                                                        style="background: none; border: none; font-size:14px; margin-left: -6px; ">
                                                                        Ver calificaciones
                                                                    </button>
                                                                    @if (!$esPPD)
                                                                        <ul>
                                                                            <li>
                                                                                <div class="d-flex align-items-center flex-wrap"
                                                                                    style="gap: 12px; width: 100%; max-width: 500px;">
                                                                                    <!-- Parcial 1 -->
                                                                                    <div class="d-flex align-items-center"
                                                                                        style="gap: 6px; flex: 1;">
                                                                                        <span class="fw-bold text-primary"
                                                                                            style="font-size: 12px; width: 70px;">Parcial
                                                                                            1:</span>
                                                                                        <div class="progress flex-fill"
                                                                                            style="height: 8px;">
                                                                                            <div class="progress-bar bg-primary"
                                                                                                role="progressbar"
                                                                                                style="width: {{ $curso->porcentajePeriodo(1) }}%;"
                                                                                                aria-valuenow="{{ $curso->porcentajePeriodo(1) }}"
                                                                                                aria-valuemin="0"
                                                                                                aria-valuemax="100">
                                                                                            </div>
                                                                                        </div>
                                                                                        <small
                                                                                            style="width: 40px; text-align: right;">
                                                                                            {{ number_format($curso->porcentajePeriodo(1), 2) }}%
                                                                                        </small>
                                                                                    </div>

                                                                                    <!-- Parcial 2 -->
                                                                                    <div class="d-flex align-items-center"
                                                                                        style="gap: 6px; flex: 1;">
                                                                                        <span class="fw-bold text-success"
                                                                                            style="font-size: 12px; width: 70px;">Parcial
                                                                                            2:</span>
                                                                                        <div class="progress flex-fill"
                                                                                            style="height: 8px;">
                                                                                            <div class="progress-bar bg-success"
                                                                                                role="progressbar"
                                                                                                style="width: {{ $curso->porcentajePeriodo(2) }}%;"
                                                                                                aria-valuenow="{{ $curso->porcentajePeriodo(2) }}"
                                                                                                aria-valuemin="0"
                                                                                                aria-valuemax="100">
                                                                                            </div>
                                                                                        </div>
                                                                                        <small
                                                                                            style="width: 40px; text-align: right;">
                                                                                            {{ number_format($curso->porcentajePeriodo(2), 2) }}%
                                                                                        </small>
                                                                                    </div>

                                                                                    <!-- Desempeño -->
                                                                                    <div class="d-flex align-items-center"
                                                                                        style="gap: 6px; flex: 1;">
                                                                                        <span class="fw-bold text-info"
                                                                                            style="font-size: 12px; width: 70px;">Desempeño:</span>
                                                                                        <div class="progress flex-fill"
                                                                                            style="height: 8px;">
                                                                                            <div class="progress-bar bg-info"
                                                                                                role="progressbar"
                                                                                                style="width: {{ $curso->porcentajePeriodo(3) }}%;"
                                                                                                aria-valuenow="{{ $curso->porcentajePeriodo(3) }}"
                                                                                                aria-valuemin="0"
                                                                                                aria-valuemax="100">
                                                                                            </div>
                                                                                        </div>
                                                                                        <small
                                                                                            style="width: 40px; text-align: right;">
                                                                                            {{ number_format($curso->porcentajePeriodo(3), 2) }}%
                                                                                        </small>
                                                                                    </div>
                                                                                </div>
                                                                            </li>
                                                                        </ul>
                                                                    @else
                                                                        @php $pctPPD = $curso->porcentajePPD(); @endphp
                                                                        <ul>
                                                                            <li>
                                                                                <div class="d-flex align-items-center"
                                                                                    style="gap: 6px; width: 100%; max-width: 400px;">
                                                                                    <span style="font-size: 12px; width: 80px; font-weight:600; color:#d97706;">Calificado:</span>
                                                                                    <div class="progress flex-fill" style="height: 8px;">
                                                                                        <div class="progress-bar"
                                                                                            role="progressbar"
                                                                                            style="width: {{ $pctPPD }}%; background-color: #f59e0b;"
                                                                                            aria-valuenow="{{ $pctPPD }}"
                                                                                            aria-valuemin="0"
                                                                                            aria-valuemax="100">
                                                                                        </div>
                                                                                    </div>
                                                                                    <small style="width: 48px; text-align: right; color:#d97706; font-weight:600;">
                                                                                        {{ number_format($pctPPD, 2) }}%
                                                                                    </small>
                                                                                </div>
                                                                            </li>
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            </form>
                                                        </ul>
                                                        <ul>
                                                            <li>
                                                                <form
                                                                    action="{{ route('docente.curso.eliminar', [$docente->id, $curso->id]) }}"
                                                                    method="POST" style="display:inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="quitarCurso"
                                                                        title="Quitar Curso asignado"
                                                                        style="margin-left: -3px"
                                                                        onclick="return confirm('¿Estás seguro de que deseas quitar este curso?')">
                                                                        Quitar Curso <i class="fa fa-sm fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">No hay cursos asignados</span>
                                        @endif
                                    </td>
                                    <td style="width: 160px">
                                        @if ($docente->user)
                                            <a href="{{ route('asignar', ['id' => $docente->user->id]) }}"
                                                class="btn btn-success btn-sm" title="Asignar Curso">
                                                <i class="fa fa-plus fa-sm"></i>
                                            </a>
                                        @else
                                            <span class="text-danger">No User Assigned</span>
                                        @endif
                                        <a href="{{ route('adminEdit', ['id' => $docente->user->id]) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="fa fa-pen fa-sm"></i>
                                        </a>
                                        {{-- <a href="{{ route('admin.docente.incidencias', $docente->id) }}"
                                            class="btn btn-warning btn-sm"
                                            title="Ver incidencias{{ $docente->user?->hasRole('tutor') ? ' (Tutor)' : '' }}">
                                            <i class="fa fa-exclamation-triangle fa-sm"></i>
                                        </a> --}}

                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                            data-target="#confirmDeleteModal">
                                            <i class="fa fa-sm fa-trash"></i>
                                        </button>

                                        <!-- Modal de confirmación -->
                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                                            aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar
                                                            eliminación</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Estás seguro de que deseas eliminar este docente?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <form action="{{ route('docente.destroy', $docente->id) }}"
                                                            method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger">Eliminar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var searchValue = normalizeString(this.value.toLowerCase());
            var searchTerms = searchValue.split(' ').filter(term => term.length >
                0);
            var tableRows = document.querySelectorAll('#docentes-table tbody tr');

            tableRows.forEach(function(row) {
                var docenteName = normalizeString(row.querySelector('td:nth-child(2) strong').innerText
                    .toLowerCase());
                var docenteEmail = normalizeString(row.querySelector('td:nth-child(2) ul li:nth-child(1)')
                    .innerText.toLowerCase());
                var docenteDni = normalizeString(row.querySelector('td:nth-child(2) ul li:nth-child(2)')
                    .innerText.toLowerCase());

                var cursosAsignados = Array.from(row.querySelectorAll('td:nth-child(3) ul li'))
                    .map(li => normalizeString(li.innerText.toLowerCase()))
                    .join(' ');

                var matches = searchTerms.every(term =>
                    docenteName.includes(term) || docenteEmail.includes(term) || docenteDni.includes(
                        term) || cursosAsignados.includes(term)
                );

                if (matches) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function normalizeString(str) {
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
    </script>
@endsection
