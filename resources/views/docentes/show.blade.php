@extends('layouts.docente')
@section('titulo', 'Mis cursos')
@section('contenido')
    @php
        $primerNombre = explode(' ', trim($docente->nombre))[0];
        $cursosPPD = $docente->cursos->filter(
            fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $otrosCursos = $docente->cursos->filter(
            fn($curso) => !str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
        );
        $cursosPPDCount = $cursosPPD->count();
        $cursosFidCount = $otrosCursos->count();
    @endphp

    <div class="container-fluid docente-ui-page docente-cursos-page px-2 px-md-3 pb-5">
        <div class="card border-0 shadow-sm mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <p class="text-uppercase text-muted small mb-1 font-weight-bold" style="letter-spacing: .04em;">
                            Área docente</p>
                        <h1 class="h4 font-weight-bold text-gray-800 mb-2">Hola, {{ $primerNombre }}</h1>
                        <p class="text-muted mb-2 small mb-lg-0">
                            Aquí gestionas tus cursos, sílabos y datos de Classroom. Usa los botones para alternar entre
                            FID y PPD.
                        </p>
                        @if (isset($periodoActual) && $periodoActual)
                            <span class="badge badge-primary font-weight-normal">Periodo:
                                {{ $periodoActual->nombre }}</span>
                        @endif
                    </div>
                    <div class="col-lg-4">
                        <div class="btn-group-vertical w-100" role="group" aria-label="Vista de cursos">
                            <button type="button" class="btn btn-success btn-sm text-left" id="btn-tab-fid"
                                onclick="mostrarTabla('fid')" aria-pressed="true">
                                <i class="fas fa-graduation-cap mr-2"></i>Cursos FID
                                @if ($cursosFidCount > 0)
                                    <span class="badge badge-light text-success float-right mt-1">{{ $cursosFidCount }}</span>
                                @endif
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm text-left" id="btn-tab-ppd"
                                onclick="mostrarTabla('ppd')" aria-pressed="false">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>Cursos PPD
                                @if ($cursosPPDCount > 0)
                                    <span class="badge badge-primary float-right mt-1">{{ $cursosPPDCount }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->has('silabo'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Atención, {{ $primerNombre }}:</strong> {{ $errors->first('silabo') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-12" id="tablafid">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h2 class="h5 mb-0 font-weight-bold text-primary d-flex align-items-center">
                            <i class="fas fa-graduation-cap mr-2 text-success"></i> Cursos FID
                        </h2>
                        <p class="small text-muted mb-0 mt-1 d-none d-md-block">Formación inicial docente y cursos fuera de
                            PPD.</p>
                    </div>
                    <div class="card-body p-0">
                @if ($otrosCursos->isEmpty())
                    <div class="text-center text-muted py-5 px-3">
                        <i class="fas fa-inbox fa-2x mb-3 text-gray-300"></i>
                        <p class="mb-0 font-weight-bold">No tiene cursos FID asignados</p>
                        <p class="small mb-0">Si cree que es un error, comuníquese con administración.</p>
                    </div>
                @else
                    <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0 table-docente-cursos">
                        <thead class="thead-dark">
                            <tr style="pointer-events:none">
                                <th>N°</th>
                                <th>Detalles del Curso</th>
                                <th>Competencias</th>
                                <th>Sílabo <br>
                                    <small>
                                        Es posible crear un sílabo desde sistema o subir un PDF al curso
                                    </small>
                                </th>
                                <th>ClassRoom</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $contador = 0;
                            @endphp
                            @foreach ($otrosCursos->values() as $index => $curso)
                                @php
                                    $contador++;
                                @endphp
                                <tr @if (str_contains($curso->ciclo->programa->nombre ?? '', 'PPD')) style="background-color: #e7f3ff" @endif>
                                    <td>
                                        {{ $contador }}
                                    </td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>
                                                    {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                    -
                                                    {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                </li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                <li>CC: {{ $curso->cc }}</li>
                                            </ul>
                                        </ul>
                                    </td>
                                    <td>
                                        @if ($curso->competencias->isEmpty())
                                            <span>No tiene asignada ninguna competencia.</span>
                                        @else
                                            @foreach ($curso->competencias as $competencia)
                                                <li style="list-style:none">
                                                    <a href="{{ route('competencias.show', $competencia->id) }}">
                                                        {{ $competencia->nombre }} </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        {{-- @if (!str_contains($curso->cc, 'Extracurricular'))
                                            @php
                                                $periodoActual = \App\Models\PeriodoActual::where(
                                                    'actual',
                                                    true,
                                                )->first();
                                                $silaboActual = $curso->silabos->firstWhere(
                                                    'periodo',
                                                    $periodoActual->nombre ?? null,
                                                );
                                                $silaboValido = $silaboActual !== null;
                                            @endphp

                                            @if (!$silaboValido && !$curso->silabo)
                                                Opción 1: Crear silabo.<br>
                                                <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-primary btn-sm mb-2">Crear Sílabo</a><br>
                                                Opción 2: Subir en PDF.<br>
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">Subir sílabo
                                                            en PDF</button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted"></div>
                                                </form>
                                            @elseif ($silaboValido)
                                                <a href="{{ route('silabo.pdf', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-danger btn-sm mb-2">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                                <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-success btn-sm mb-2">
                                                    <i class="fa fa-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('silabos.edit', [
                                                    'silabo' => $curso->relacionsilabo->id,
                                                    'curso_id' => $curso->id,
                                                    'docente_id' => $docente->id,
                                                ]) }}"
                                                    class="btn btn-warning btn-sm mb-2">
                                                    <i class="fa fa-edit"></i> Editar
                                                </a>
                                            @elseif ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                                                    title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span>Extracurricular permite sólo subir archivo en PDF.</span><br>
                                        @endif --}}
                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                            @php
                                                // Obtener periodo actual
                                                $periodoActual = \App\Models\PeriodoActual::where(
                                                    'actual',
                                                    true,
                                                )->first();

                                                // Buscar sílabo (tabla 'silabos') del periodo actual
                                                $silaboActual = $curso->silabos->firstWhere(
                                                    'periodo',
                                                    $periodoActual->nombre ?? null,
                                                );
                                                $silaboValido = $silaboActual !== null;

                                                // Buscar PDF (tabla 'silabo_pdf') del periodo actual
                                                $silaboPdf = $curso->silabosPdf
                                                    ->where('periodo_actual_id', $periodoActual->id ?? null)
                                                    ->first();
                                            @endphp

                                            {{-- ✅ Caso 1: No hay sílabo ni PDF del periodo actual --}}
                                            @if (!$silaboValido && !$silaboPdf && !$curso->silabo)
                                                <p class="text-muted mb-1">
                                                    No hay sílabo registrado para el periodo
                                                    <strong>{{ $periodoActual->nombre ?? 'actual' }}</strong>.
                                                </p>

                                                Opción 1: Crear sílabo.<br>
                                                <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-primary btn-sm mb-2">
                                                    Crear Sílabo
                                                </a><br>

                                                Opción 2: Subir en PDF.<br>
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Subir sílabo en PDF
                                                        </button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted"></div>
                                                </form>

                                                {{-- ✅ Caso 2: Existe sílabo (tabla silabos) --}}
                                            @elseif ($silaboValido)
                                                <a href="{{ route('silabo.pdf', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-danger btn-sm mb-2">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                                <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-success btn-sm mb-2">
                                                    <i class="fa fa-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('silabos.edit', [
                                                    'silabo' => $curso->relacionsilabo->id,
                                                    'curso_id' => $curso->id,
                                                    'docente_id' => $docente->id,
                                                ]) }}"
                                                    class="btn btn-warning btn-sm mb-2">
                                                    <i class="fa fa-edit"></i> Editar
                                                </a>

                                                {{-- ✅ Caso 3: Existe PDF (tabla silabo_pdf) --}}
                                            @elseif ($silaboPdf)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $silaboPdf->pdf) }}"
                                                    target="_blank" title="Ver Sílabo PDF">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>

                                                {{-- ✅ Caso 4: Campo antiguo (columna silabo en tabla cursos) --}}
                                            @elseif ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span>Extracurricular permite sólo subir archivo en PDF.</span><br>
                                        @endif


                                    </td>
                                    <td>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Classroom Link">
                                            </div>
                                            <div class="input-group mb-2">
                                                <input type="text" name="clave" class="form-control form-control-sm"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom">
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <button type="submit"
                                                    class="btn btn-primary btn-sm">{{ $buttonText }}</button>
                                                <button type="submit" class="btn btn-danger btn-sm" name="delete"
                                                    value="true"
                                                    onclick="return confirm('¿Estás seguro de eliminar estos campos?');">Eliminar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
                    </div>
                </div>
            </div>
            <div class="col-12 mb-5" id="tablappd" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h2 class="h5 mb-0 font-weight-bold text-primary d-flex align-items-center">
                            <i class="fas fa-chalkboard-teacher mr-2"></i> Cursos PPD
                        </h2>
                        <p class="small text-muted mb-0 mt-1 d-none d-md-block">Profesionalización pedagógica docente.</p>
                    </div>
                    <div class="card-body p-0">
                @if ($cursosPPD->isEmpty())
                    <div class="text-center text-muted py-5 px-3">
                        <i class="fas fa-folder-open fa-2x mb-3 text-gray-300"></i>
                        <p class="mb-0 font-weight-bold">No tiene cursos PPD asignados</p>
                        <p class="small mb-0">Los cursos PPD aparecerán aquí cuando estén asignados.</p>
                    </div>
                @else
                    <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0 table-docente-cursos">
                        <thead class="thead-dark">
                            <tr style="pointer-events:none">
                                <th>N°</th>
                                <th>Detalles del Curso</th>
                                <th>Competencias</th>
                                <th>Sílabo</th>
                                <th>ClassRoom</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $contador = 0;
                            @endphp
                            @foreach ($cursosPPD->values() as $index => $curso)
                                @php
                                    $contador++;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $contador }}
                                    </td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>
                                                    {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                    -
                                                    {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                </li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                <li>CC: {{ $curso->cc }}</li>
                                            </ul>
                                        </ul>
                                    </td>
                                    <td>
                                        @if ($curso->competencias->isEmpty())
                                            <span>No tiene asignada ninguna competencia.</span>
                                        @else
                                            @foreach ($curso->competencias as $competencia)
                                                <li>
                                                    <a href="{{ route('competencias.show', $competencia->id) }}">
                                                        {{ $competencia->nombre }} </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                            @if (!$curso->relacionsilabo && !$curso->silabo)
                                                <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-primary btn-sm mb-2">Crear Sílabo</a><br>

                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">Subir sílabo
                                                            en
                                                            PDF</button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted">
                                                    </div>
                                                </form>
                                            @elseif ($curso->relacionsilabo)
                                                <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-success btn-sm mb-2"><i class="fa fa-eye"></i> Ver
                                                    Sílabo</a>

                                                <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-warning btn-sm mb-2"><i class="fa fa-edit"></i> Editar
                                                    Sílabo</a>
                                            @elseif ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span>Extracurricular permite sólo subir archivo en PDF.</span><br>
                                            @if ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @else
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Subir sílabo en PDF
                                                        </button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Classroom Link">
                                            </div>
                                            <div class="input-group mb-2">
                                                <input type="text" name="clave" class="form-control form-control-sm"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom">
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <button type="submit"
                                                    class="btn btn-primary btn-sm">{{ $buttonText }}</button>
                                                <button type="submit" class="btn btn-danger btn-sm" name="delete"
                                                    value="true"
                                                    onclick="return confirm('¿Estás seguro de eliminar estos campos?');">Eliminar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
                    </div>
                </div>
            </div>


            <!-- Modal de Confirmación -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas eliminar este Sílabo?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" id="confirm-delete" class="btn btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function updateFileName(input, elementId) {
            var fileNameDisplay = document.getElementById(elementId);
            if (!fileNameDisplay) {
                return;
            }
            if (input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        function mostrarTabla(tipo) {
            var tablaPPD = document.getElementById('tablappd');
            var tablaFID = document.getElementById('tablafid');
            var btnFid = document.getElementById('btn-tab-fid');
            var btnPpd = document.getElementById('btn-tab-ppd');
            if (!tablaPPD || !tablaFID || !btnFid || !btnPpd) {
                return;
            }
            if (tipo === 'ppd') {
                tablaPPD.style.display = 'block';
                tablaFID.style.display = 'none';
                btnPpd.classList.remove('btn-outline-primary');
                btnPpd.classList.add('btn-primary');
                btnFid.classList.remove('btn-success');
                btnFid.classList.add('btn-outline-success');
                btnPpd.setAttribute('aria-pressed', 'true');
                btnFid.setAttribute('aria-pressed', 'false');
            } else if (tipo === 'fid') {
                tablaPPD.style.display = 'none';
                tablaFID.style.display = 'block';
                btnFid.classList.remove('btn-outline-success');
                btnFid.classList.add('btn-success');
                btnPpd.classList.remove('btn-primary');
                btnPpd.classList.add('btn-outline-primary');
                btnFid.setAttribute('aria-pressed', 'true');
                btnPpd.setAttribute('aria-pressed', 'false');
            }
        }

        $(document).ready(function() {
            $('#confirmDeleteModal').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var url = button.data('href');
                $(this).find('#confirm-delete').data('href', url);
            });

            $('#confirm-delete').on('click', function() {
                var url = $(this).data('href');
                if (!url) {
                    return;
                }
                var form = $('<form>', {
                    method: 'POST',
                    action: url
                }).append(
                    $('<input>', {
                        name: '_token',
                        value: $('meta[name="csrf-token"]').attr('content'),
                        type: 'hidden'
                    })
                ).append(
                    $('<input>', {
                        name: '_method',
                        value: 'DELETE',
                        type: 'hidden'
                    })
                );
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush
