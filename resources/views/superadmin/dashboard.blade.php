@extends('layouts.superadmin')
@section('titulo', 'Panel Super Admin')

@section('contenido')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap:.75rem;">
        <div>
            <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                Panel Super Administrador
            </h4>
            <small class="text-muted">
                {{ $totalRecords }} registros totales
                &nbsp;·&nbsp; {{ $alumnosConBeca }} beca{{ $alumnosConBeca != 1 ? 's' : '' }}
                @if($totalAlumnos > 0)
                    ({{ round(($alumnosConBeca / $totalAlumnos) * 100, 1) }}%)
                @endif
            </small>
        </div>
        <a href="{{ route('registerAdmin') }}"
           class="btn btn-sm shadow-sm"
           style="background:linear-gradient(90deg,#7c3aed,#06b6d4);color:#fff;border:none;border-radius:.5rem;font-weight:600;">
            <i class="fa fa-plus fa-xs mr-1"></i> Nuevo Registro
        </a>
    </div>

    {{-- ── Stat cards ── --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-primary shadow-sm adm-stat-card h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">FID</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoAlumnos }}</div>
                        </div>
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-secondary shadow-sm adm-stat-card h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">PPD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoPpd }}</div>
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-info shadow-sm adm-stat-card h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Docentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoDocentes }}</div>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-warning shadow-sm adm-stat-card h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoAdmin }}</div>
                        </div>
                        <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-danger shadow-sm adm-stat-card h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inhabilitados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoInhabilitados }}</div>
                        </div>
                        <i class="fas fa-ban fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card shadow-sm adm-stat-card h-100" style="border-left:4px solid #7c3aed;">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#7c3aed;">Super Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoSuperAdmin }}</div>
                        </div>
                        <i class="fas fa-crown fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Tab nav + search ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:.5rem;">
        <div class="adm-tabs">
            <button class="adm-tab adm-tab-fid active" id="btn-admins" onclick="mostrarTabla('admins')">
                <i class="fas fa-graduation-cap fa-xs"></i> FID
                <span class="adm-badge">{{ $conteoAlumnos }}</span>
            </button>
            <button class="adm-tab adm-tab-ppd" id="btn-alumnosppd" onclick="mostrarTabla('alumnosppd')">
                <i class="fas fa-user-graduate fa-xs"></i> PPD
                <span class="adm-badge">{{ $conteoPpd }}</span>
            </button>
            <button class="adm-tab adm-tab-doc" id="btn-docentes" onclick="mostrarTabla('docentes')">
                <i class="fas fa-chalkboard-teacher fa-xs"></i> Docentes
                <span class="adm-badge">{{ $conteoDocentes }}</span>
            </button>
            <button class="adm-tab adm-tab-inh" id="btn-inhabilitados" onclick="mostrarTabla('inhabilitados')">
                <i class="fas fa-ban fa-xs"></i> Inhabilitados
                <span class="adm-badge">{{ $conteoInhabilitados }}</span>
            </button>
            <button class="adm-tab adm-tab-adm" id="btn-alumnos" onclick="mostrarTabla('alumnos')">
                <i class="fas fa-users-cog fa-xs"></i> Admins
                <span class="adm-badge">{{ $conteoAdmin }}</span>
            </button>
            <button class="adm-tab adm-tab-sa" id="btn-superadmins" onclick="mostrarTabla('superadmins')">
                <i class="fas fa-crown fa-xs"></i> Super Admins
                <span class="adm-badge">{{ $conteoSuperAdmin }}</span>
            </button>
            <button class="adm-tab adm-tab-bolsa" id="btn-adminsB" onclick="mostrarTabla('adminsB')">
                <i class="fas fa-briefcase fa-xs"></i> Bolsa
            </button>
            <button class="adm-tab adm-tab-tutor" id="btn-tutores" onclick="mostrarTabla('tutores')">
                <i class="fas fa-chalkboard fa-xs"></i> Tutores
                <span class="adm-badge">{{ $conteoTutores }}</span>
            </button>
        </div>
        <div class="adm-search-wrap mt-2 mt-md-0" style="min-width:220px; flex:1; max-width:300px;">
            <i class="fas fa-search adm-search-icon fa-sm"></i>
            <input type="text" id="search-box" class="form-control form-control-sm"
                   placeholder="Buscar por nombre, DNI o correo...">
        </div>
    </div>

    {{-- ── Tabla: Alumnos FID ── --}}
    <div class="table-responsive">
        <table id="admins-table" class="table adm-table mb-0">
            <thead>
                <tr><th>Usuario</th><th>Cursos</th><th>Pendientes</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('alumno'))
                    <tr class="alumno-row {{ $admin->beca == 1 ? 'becado' : '' }}">
                        <td>
                            <div class="d-flex align-items-center" style="gap:.75rem;">
                                @if ($admin->foto)
                                    <img src="{{ asset('img/estudiantes/' . $admin->foto) }}" alt="" class="adm-avatar">
                                @else
                                    <div class="adm-avatar-ph">
                                        {{ strtoupper(substr($admin->name,0,1)) }}{{ strtoupper(substr($admin->apellidos,0,1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="adm-uname">{{ $admin->apellidos }}, {{ $admin->name }}</div>
                                    <div class="adm-umeta">
                                        <i class="fas fa-envelope fa-xs mr-1"></i>{{ $admin->email }}
                                    </div>
                                    <div class="adm-umeta">
                                        {{ optional($admin->programa)->nombre ?? 'N/A' }}
                                        &nbsp;·&nbsp; Ciclo {{ optional($admin->ciclo)->nombre ?? 'N/A' }}
                                        &nbsp;·&nbsp; DNI {{ $admin->dni }}
                                        @if($admin->beca == 1)
                                            <span class="chip chip-green ml-1">Beca</span>
                                        @endif
                                    </div>
                                    <div class="adm-umeta text-muted">
                                        ID {{ $admin->id }}
                                        &nbsp;·&nbsp; N° {{ $admin->alumno?->numero ?? '—' }}
                                        &nbsp;·&nbsp; Ref {{ $admin->alumno?->numero_referencia ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('asignar.cursos', ['id' => $admin->id]) }}" class="adm-btn adm-btn-assign">
                                <i class="fa fa-books fa-xs"></i> Asignar
                            </a>
                        </td>
                        <td>
                            @php $cursos = array_map('trim', explode(',', $admin->pendiente ?? '')); @endphp
                            @if (!empty($cursos[0]))
                                <ol class="adm-pending-list mb-0">
                                    @foreach ($cursos as $c)<li>{{ $c }}</li>@endforeach
                                </ol>
                            @else
                                <span class="text-muted small">Sin pendientes</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit">
                                <i class="fa fa-pen"></i>
                            </a>
                            <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                               class="adm-btn adm-btn-del ml-1">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Alumnos PPD --}}
    <div class="table-responsive">
        <table id="alumnosppd" class="table adm-table mb-0" style="display:none;">
            <thead>
                <tr><th>Usuario</th><th>Programa</th><th>Ciclo</th><th>DNI</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('alumnoB'))
                    <tr class="alumno-row">
                        <td>
                            <div class="adm-uname">{{ $admin->apellidos }}, {{ $admin->name }}</div>
                            <div class="adm-umeta"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $admin->email }}</div>
                            <div class="adm-umeta text-muted">
                                N° {{ optional($admin->alumnoB)->numero ?? '—' }}
                                &nbsp;·&nbsp; Ref {{ optional($admin->alumnoB)->numero_referencia ?? '—' }}
                            </div>
                        </td>
                        <td><span class="small">{{ optional($admin->programa)->nombre ?? 'N/A' }}</span></td>
                        <td><span class="small">{{ optional($admin->ciclo)->nombre ?? 'N/A' }}</span></td>
                        <td><span class="small">{{ $admin->dni }}</span></td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                               class="adm-btn adm-btn-del ml-1"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Administradores --}}
    <div class="table-responsive">
        <table id="alumnos-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('admin'))
                    <tr>
                        <td class="text-muted small">{{ $key + 1 }}</td>
                        <td>
                            <div class="adm-uname">{{ $admin->name }} {{ $admin->apellidos }}</div>
                            <div class="adm-umeta text-muted">DNI {{ $admin->dni }}</div>
                        </td>
                        <td><span class="small text-muted">{{ $admin->email }}</span></td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a href="{{ route('adminDestroy', ['id' => $admin->id]) }}" class="adm-btn adm-btn-del ml-1"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Super Admins --}}
    <div class="table-responsive">
        <table id="superadmins-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('super-admin'))
                    <tr>
                        <td class="text-muted small">{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center" style="gap:.65rem;">
                                <div class="adm-avatar-ph ph-violet">
                                    {{ strtoupper(substr($admin->name,0,1)) }}{{ strtoupper(substr($admin->apellidos,0,1)) }}
                                </div>
                                <div>
                                    <div class="adm-uname">
                                        {{ $admin->name }} {{ $admin->apellidos }}
                                        <span class="sa-badge ml-1"><i class="fas fa-crown fa-xs"></i> SA</span>
                                    </div>
                                    <div class="adm-umeta text-muted">DNI {{ $admin->dni }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="small text-muted">{{ $admin->email }}</span></td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a href="{{ route('adminDestroy', ['id' => $admin->id]) }}" class="adm-btn adm-btn-del ml-1"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Admins Bolsa --}}
    <div class="table-responsive">
        <table id="adminsB-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('adminB'))
                    <tr>
                        <td>
                            <div class="adm-uname">{{ $admin->name }} {{ $admin->apellidos }}</div>
                            <div class="adm-umeta text-muted">DNI {{ $admin->dni }}</div>
                        </td>
                        <td><span class="small text-muted">{{ $admin->email }}</span></td>
                        <td>
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a href="#" class="adm-btn adm-btn-del ml-1" data-toggle="modal"
                               data-target="#confirmDeleteModal"
                               data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Docentes --}}
    <div class="table-responsive">
        <table id="docentes-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>#</th><th>Docente</th><th>Cursos asignados</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @php $dc = 0; @endphp
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('docente'))
                    @php $dc++; @endphp
                    <tr>
                        <td class="text-muted small">{{ $dc }}</td>
                        <td>
                            <div class="adm-uname">{{ $admin->name }} {{ $admin->apellidos }}</div>
                            <ul class="mb-0 small text-muted pl-3">
                                <li>{{ $admin->email }}</li>
                                <li>DNI: {{ $admin->dni }}</li>
                            </ul>
                        </td>
                        <td>
                            @if (optional($admin->docente)->cursos && $admin->docente->cursos->isNotEmpty())
                                <ul class="mb-0 small pl-3">
                                    @foreach ($admin->docente->cursos as $curso)
                                        <li>{{ $curso->nombre }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="small text-muted font-italic">Sin cursos asignados</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-edit"></i></a>
                            <a href="#" class="adm-btn adm-btn-del ml-1" data-toggle="modal"
                               data-target="#confirmDeleteModal"
                               data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Tutores --}}
    <div class="table-responsive">
        <table id="tutores-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>#</th><th>Tutor</th><th>Ciclos asignados</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @php $tc = 0; @endphp
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('tutor'))
                    @php $tc++; @endphp
                    <tr>
                        <td class="text-muted small">{{ $tc }}</td>
                        <td>
                            <div class="adm-uname">{{ $admin->name }} {{ $admin->apellidos }}</div>
                            <ul class="mb-0 small text-muted pl-3">
                                <li>{{ $admin->email }}</li>
                                <li>DNI: {{ $admin->dni }}</li>
                            </ul>
                        </td>
                        <td>
                            @if ($admin->tutorCiclos->isEmpty())
                                <span class="small text-muted font-italic">Sin ciclos asignados</span>
                            @else
                                <ul class="mb-0 small pl-3">
                                    @foreach ($admin->tutorCiclos as $cicloAsignado)
                                        <li>
                                            {{ optional($cicloAsignado->programa)->nombre ? $cicloAsignado->programa->nombre . ' — ' : '' }}Ciclo {{ $cicloAsignado->nombre }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a href="{{ route('admin.tutor.ciclos', $admin->id) }}" class="adm-btn adm-btn-ciclos ml-1" title="Asignar ciclos"><i class="fas fa-layer-group"></i></a>
                            <a href="#" class="adm-btn adm-btn-del ml-1" data-toggle="modal"
                               data-target="#confirmDeleteModal"
                               data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla: Inhabilitados --}}
    <div class="table-responsive">
        <table id="inhabilitado-table" class="table adm-table" style="display:none;">
            <thead>
                <tr><th>Usuario</th><th>Cursos</th><th>Pendientes</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('inhabilitado'))
                    <tr class="alumno-row">
                        <td>
                            <div class="d-flex align-items-center" style="gap:.75rem;">
                                @if ($admin->foto)
                                    <img src="{{ asset('img/estudiantes/' . $admin->foto) }}" alt="" class="adm-avatar">
                                @else
                                    <div class="adm-avatar-ph ph-red">
                                        {{ strtoupper(substr($admin->name,0,1)) }}{{ strtoupper(substr($admin->apellidos,0,1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="adm-uname">{{ $admin->apellidos }}, {{ $admin->name }}</div>
                                    <div class="adm-umeta"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $admin->email }}</div>
                                    <div class="adm-umeta">
                                        {{ optional($admin->programa)->nombre ?? 'N/A' }}
                                        &nbsp;·&nbsp; Ciclo {{ optional($admin->ciclo)->nombre ?? 'N/A' }}
                                        &nbsp;·&nbsp; DNI {{ $admin->dni }}
                                        <span class="chip chip-red ml-1">{{ $admin->perfil ?? 'Inhabilitado' }}</span>
                                    </div>
                                    <div class="adm-umeta text-muted">ID {{ $admin->id }}
                                        &nbsp;·&nbsp; N° {{ optional($admin->alumno ?? $admin->alumnoB)->numero ?? '—' }}
                                        &nbsp;·&nbsp; Ref {{ optional($admin->alumno ?? $admin->alumnoB)->numero_referencia ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('asignar.cursos', ['id' => $admin->id]) }}" class="adm-btn adm-btn-assign">
                                <i class="fa fa-books fa-xs"></i> Asignar
                            </a>
                        </td>
                        <td>
                            @php $cursos = array_map('trim', explode(',', $admin->pendiente ?? '')); @endphp
                            @if (!empty($cursos[0]))
                                <ol class="adm-pending-list mb-0">
                                    @foreach ($cursos as $c)<li>{{ $c }}</li>@endforeach
                                </ol>
                            @else
                                <span class="text-muted small">Sin pendientes</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('adminEdit', ['id' => $admin->id]) }}" class="adm-btn adm-btn-edit"><i class="fa fa-pen"></i></a>
                            <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                               class="adm-btn adm-btn-del ml-1"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

</div>{{-- /container --}}

{{-- Modal --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#1e293b;">
                <h6 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>Confirmar Eliminación
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancelar</button>
                <a id="confirm-delete" class="btn btn-sm btn-danger font-weight-bold" href="#">
                    <i class="fa fa-trash mr-1"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $('#confirmDeleteModal').on('show.bs.modal', function(e) {
        $(this).find('#confirm-delete').attr('href', $(e.relatedTarget).data('href'));
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('search-box').addEventListener('keyup', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('.table').forEach(function (t) {
                t.querySelectorAll('tbody tr').forEach(function (r) {
                    r.style.display = r.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        });
    });

    var allTables = [
        'admins-table','alumnos-table','alumnosppd','adminsB-table',
        'docentes-table','inhabilitado-table','tutores-table','superadmins-table'
    ];
    var tabMap = {
        admins:        { table:'admins-table',      btn:'btn-admins' },
        alumnosppd:    { table:'alumnosppd',         btn:'btn-alumnosppd' },
        docentes:      { table:'docentes-table',     btn:'btn-docentes' },
        inhabilitados: { table:'inhabilitado-table', btn:'btn-inhabilitados' },
        alumnos:       { table:'alumnos-table',      btn:'btn-alumnos' },
        superadmins:   { table:'superadmins-table',  btn:'btn-superadmins' },
        adminsB:       { table:'adminsB-table',      btn:'btn-adminsB' },
        tutores:       { table:'tutores-table',      btn:'btn-tutores' },
    };

    function mostrarTabla(tipo) {
        allTables.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        Object.keys(tabMap).forEach(function(k) {
            var b = document.getElementById(tabMap[k].btn);
            if (b) b.classList.remove('active');
        });
        var cfg = tabMap[tipo];
        if (cfg) {
            var el = document.getElementById(cfg.table);
            if (el) el.style.display = 'table';
            var btn = document.getElementById(cfg.btn);
            if (btn) btn.classList.add('active');
        }
    }

    function confirmarEliminacion(url) {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            window.location.href = url;
        }
    }
</script>
@endpush
@endsection
