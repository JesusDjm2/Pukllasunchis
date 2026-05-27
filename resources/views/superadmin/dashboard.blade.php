@extends('layouts.superadmin')
@section('titulo', 'Panel Super Admin')

@section('contenido')
<style>
    .curso-item { transition: background-color .3s ease; }
    .curso-item:hover { background-color: #e7e7e7; }
    .becado { background-color: #e6f4ea; }

    /* Botones de navegación activos */
    .btn-tabla-activo {
        box-shadow: 0 0 0 2px #7c3aed, 0 2px 6px rgba(124,58,237,.35) !important;
    }

    /* Stats cards compactas */
    .sa-stat-card {
        border-radius: .6rem;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .sa-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.12) !important;
    }
</style>

<div class="container-fluid bg-white pt-3 pb-4">
    {{-- ══ ENCABEZADO ══ --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div>            
            <small class="text-muted">
                {{ $totalRecords }} registros totales &nbsp;·&nbsp;
                {{ $alumnosConBeca }} beca{{ $alumnosConBeca != 1 ? 's' : '' }}
                @if($totalAlumnos > 0)
                    ({{ round(($alumnosConBeca / $totalAlumnos) * 100, 1) }}%)
                @endif
            </small>
        </div>
        <a href="{{ route('registerAdmin') }}"
           class="btn btn-sm shadow-sm"
           style="background:linear-gradient(90deg,#7c3aed,#06b6d4);color:#fff;border:none;border-radius:.5rem;">
            <i class="fa fa-plus fa-sm mr-1"></i>Nuevo Registro
        </a>
    </div>

    {{-- ══ TARJETAS DE ESTADÍSTICAS ══ --}}
    <div class="row mb-4">
        {{-- Alumnos FID --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-primary shadow sa-stat-card h-100 py-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">FID</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoAlumnos }}</div>
                        </div>
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- Alumnos PPD --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-secondary shadow sa-stat-card h-100 py-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">PPD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoPpd }}</div>
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- Docentes --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-info shadow sa-stat-card h-100 py-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Docentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoDocentes }}</div>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- Administradores --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-warning shadow sa-stat-card h-100 py-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoAdmin }}</div>
                        </div>
                        <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- Inhabilitados --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-danger shadow sa-stat-card h-100 py-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inhabilitados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoInhabilitados }}</div>
                        </div>
                        <i class="fas fa-ban fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- Super Admins --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card shadow sa-stat-card h-100 py-2" style="border-left: 4px solid #7c3aed;">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1"
                                 style="color:#7c3aed;">Super Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conteoSuperAdmin }}</div>
                        </div>
                        <i class="fas fa-crown fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ ALERTAS ══ --}}
    @if(Session::has('success'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ══ NAVEGACIÓN DE TABLAS + BUSCADOR ══ --}}
    <div class="row mb-3">
        <div class="col-lg-8 mb-2">
            <div class="d-flex flex-wrap" style="gap:.4rem;">
                <button class="btn btn-primary btn-sm" id="btn-admins" onclick="mostrarTabla('admins')">
                    <i class="fas fa-graduation-cap fa-xs mr-1"></i>Alumnos FID
                    <span class="badge badge-light ml-1">{{ $conteoAlumnos }}</span>
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-alumnosppd" onclick="mostrarTabla('alumnosppd')">
                    <i class="fas fa-user-graduate fa-xs mr-1"></i>PPD
                    <span class="badge badge-light ml-1">{{ $conteoPpd }}</span>
                </button>
                <button class="btn btn-info btn-sm" id="btn-docentes" onclick="mostrarTabla('docentes')">
                    <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i>Docentes
                    <span class="badge badge-light ml-1">{{ $conteoDocentes }}</span>
                </button>
                <button class="btn btn-danger btn-sm" id="btn-inhabilitados" onclick="mostrarTabla('inhabilitados')">
                    <i class="fas fa-ban fa-xs mr-1"></i>Inhabilitados
                    <span class="badge badge-light ml-1">{{ $conteoInhabilitados }}</span>
                </button>
                <button class="btn btn-warning btn-sm" id="btn-alumnos" onclick="mostrarTabla('alumnos')">
                    <i class="fas fa-users-cog fa-xs mr-1"></i>Admins
                    <span class="badge badge-light ml-1">{{ $conteoAdmin }}</span>
                </button>
                <button class="btn btn-sm" id="btn-superadmins"
                        style="background:linear-gradient(90deg,#7c3aed,#06b6d4);color:#fff;border:none;"
                        onclick="mostrarTabla('superadmins')">
                    <i class="fas fa-crown fa-xs mr-1"></i>Super Admins
                    <span class="badge badge-light ml-1">{{ $conteoSuperAdmin }}</span>
                </button>
                <button class="btn btn-dark btn-sm" id="btn-adminsB" onclick="mostrarTabla('adminsB')">
                    <i class="fas fa-briefcase fa-xs mr-1"></i>Bolsa
                </button>
                <button class="btn btn-success btn-sm" id="btn-tutores" onclick="mostrarTabla('tutores')">
                    <i class="fas fa-chalkboard fa-xs mr-1"></i>Tutores
                    <span class="badge badge-light ml-1">{{ $conteoTutores }}</span>
                </button>
            </div>
        </div>
        <div class="col-lg-4">
            <input type="text" id="search-box" class="form-control"
                   placeholder="🔍 Buscar usuarios...">
        </div>
    </div>

    {{-- ══ TABLAS ══ --}}
    {{-- Alumnos FID --}}
    <div class="table-responsive">
        <table id="admins-table" class="table table-hover table-sortable">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Editar Cursos</th>
                    <th>Foto</th>
                    <th width="30%">Cursos pendientes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('alumno'))
                        <tr class="alumno-row {{ $admin->beca == 1 ? 'becado' : '' }}">
                            <td>
                                <span class="font-weight-bold">{{ $admin->apellidos }}, {{ $admin->name }}</span>
                                <ul class="mb-0 mt-1" style="font-size:.82rem; padding-left:1rem;">
                                    <li>Beca: @if($admin->beca == 1) Sí @else No @endif</li>
                                    <li>Correo: {{ $admin->email }}</li>
                                    <li>{{ optional($admin->programa)->nombre ?? 'N/A' }} — Ciclo {{ optional($admin->ciclo)->nombre ?? 'N/A' }}</li>
                                    <li>DNI: {{ $admin->dni }}</li>
                                    <li>N°: {{ $admin->alumno?->numero ?? '—' }} | Ref: {{ $admin->alumno?->numero_referencia ?? '—' }}</li>
                                    <li class="text-muted">ID: {{ $admin->id }}</li>
                                </ul>
                            </td>
                            <td>
                                <a href="{{ route('asignar.cursos', ['id' => $admin->id]) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-books fa-xs mr-1"></i>Asignar Cursos
                                </a>
                            </td>
                            <td>
                                @if ($admin->foto)
                                    <img src="{{ asset('img/estudiantes/' . $admin->foto) }}"
                                         alt="Foto" loading="lazy" class="img-fluid"
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                @else
                                    <span class="text-muted">Sin foto</span>
                                @endif
                            </td>
                            <td>
                                @php $cursos = array_map('trim', explode(',', $admin->pendiente ?? '')); @endphp
                                @if (!empty($cursos[0]))
                                    <ol class="mb-0" style="padding-left:14px; font-size:.82rem;">
                                        @foreach ($cursos as $curso)<li>{{ $curso }}</li>@endforeach
                                    </ol>
                                @else
                                    <span class="text-muted">Sin pendientes</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm" title="Editar">
                                    <i class="fa fa-pen"></i>
                                </a>
                                <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                                   class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Alumnos PPD --}}
    <div class="table-responsive">
        <table id="alumnosppd" class="table table-hover table-sortable" style="display:none;">
            <thead class="thead-dark">
                <tr>
                    <th>Nombres PPD</th><th>Correo</th><th>Programa</th><th>Ciclo</th><th>DNI</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('alumnoB'))
                        <tr class="alumno-row">
                            <td>{{ $admin->apellidos }}, {{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ optional($admin->programa)->nombre ?? 'N/A' }}</td>
                            <td>{{ optional($admin->ciclo)->nombre ?? 'N/A' }}</td>
                            <td>{{ $admin->dni }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                                   class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Administradores --}}
    <div class="table-responsive">
        <table id="alumnos-table" class="table" style="display:none;">
            <thead class="thead-dark">
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('admin'))
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a href="{{ route('adminDestroy', ['id' => $admin->id]) }}"
                                   class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Super Admins --}}
    <div class="table-responsive">
        <table id="superadmins-table" class="table" style="display:none;">
            <thead class="thead-dark">
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('super-admin'))
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $admin->name }} {{ $admin->apellidos }}
                                <span class="badge badge-pill ml-1"
                                      style="background:linear-gradient(90deg,#7c3aed,#06b6d4);color:#fff;font-size:.7rem;">
                                    <i class="fas fa-crown fa-xs mr-1"></i>Super Admin
                                </span>
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a href="{{ route('adminDestroy', ['id' => $admin->id]) }}"
                                   class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Admins Bolsa --}}
    <div class="table-responsive">
        <table id="adminsB-table" class="table" style="display:none;">
            <thead class="thead-dark">
                <tr><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($admins as $key => $admin)
                    @if ($admin->hasRole('adminB'))
                        <tr>
                            <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a href="#" class="btn btn-danger btn-sm"
                                   data-toggle="modal" data-target="#confirmDeleteModal"
                                   data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Docentes --}}
    <div class="table-responsive">
        <table class="table table-hover" id="docentes-table" style="display:none;">
            <thead class="thead-dark">
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>DNI</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @php $docenteCount = 0; @endphp
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('docente'))
                        @php $docenteCount++; @endphp
                        <tr>
                            <td>{{ $docenteCount }}</td>
                            <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->dni }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-edit"></i> <i class="fa fa-user"></i></a>
                                <a href="#" class="btn btn-danger btn-sm"
                                   data-toggle="modal" data-target="#confirmDeleteModal"
                                   data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tutores --}}
    <div class="table-responsive">
        <table class="table table-hover" id="tutores-table" style="display:none;">
            <thead class="thead-dark">
                <tr><th>#</th><th>Nombre</th><th>Correo</th><th>DNI</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @php $tutorCount = 0; @endphp
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('tutor'))
                        @php $tutorCount++; @endphp
                        <tr>
                            <td>{{ $tutorCount }}</td>
                            <td>{{ $admin->apellidos }}, {{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->dni }}</td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a href="{{ route('admin.tutor.ciclos', $admin->id) }}"
                                   class="btn btn-success btn-sm" title="Asignar ciclos">
                                    <i class="fas fa-layer-group"></i>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm"
                                   data-toggle="modal" data-target="#confirmDeleteModal"
                                   data-href="{{ route('adminDestroy', ['id' => $admin->id]) }}">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Inhabilitados --}}
    <div class="table-responsive">
        <table class="table table-hover" id="inhabilitado-table" style="display:none;">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th><th>Editar Cursos</th><th>Foto</th>
                    <th width="30%">Cursos pendientes</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    @if ($admin->hasRole('inhabilitado'))
                        <tr class="alumno-row {{ $admin->beca == 1 ? 'becado' : '' }}">
                            <td>
                                <span class="font-weight-bold">{{ $admin->apellidos }}, {{ $admin->name }}</span>
                                <ul class="mb-0 mt-1" style="font-size:.82rem; padding-left:1rem;">
                                    <li>Beca: @if($admin->beca == 1) Sí @else No @endif</li>
                                    <li>Correo: {{ $admin->email }}</li>
                                    <li>{{ optional($admin->programa)->nombre ?? 'N/A' }} — Ciclo {{ optional($admin->ciclo)->nombre ?? 'N/A' }}</li>
                                    <li>DNI: {{ $admin->dni }}</li>
                                    <li>Inhabilitado por: {{ $admin->perfil }}</li>
                                </ul>
                            </td>
                            <td>
                                <a href="{{ route('asignar.cursos', ['id' => $admin->id]) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-books fa-xs mr-1"></i>Asignar
                                </a>
                            </td>
                            <td>
                                @if ($admin->foto)
                                    <img src="{{ asset('img/estudiantes/' . $admin->foto) }}"
                                         alt="Foto" loading="lazy" class="img-fluid"
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                @else
                                    <span class="text-muted">Sin foto</span>
                                @endif
                            </td>
                            <td>
                                @php $cursos = array_map('trim', explode(',', $admin->pendiente ?? '')); @endphp
                                @if (!empty($cursos[0]))
                                    <ol class="mb-0" style="padding-left:14px; font-size:.82rem;">
                                        @foreach ($cursos as $curso)<li>{{ $curso }}</li>@endforeach
                                    </ol>
                                @else
                                    <span class="text-muted">Sin pendientes</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                   class="btn btn-info btn-sm"><i class="fa fa-pen"></i></a>
                                <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                                   class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

</div>{{-- /container-fluid --}}

{{-- ══ MODAL CONFIRMAR ELIMINACIÓN ══ --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">¿Estás seguro de que deseas eliminar este usuario?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a id="confirm-delete" class="btn btn-danger" href="#">Eliminar</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    /* Modal de eliminación */
    $('#confirmDeleteModal').on('show.bs.modal', function (e) {
        var url = $(e.relatedTarget).data('href');
        $(this).find('#confirm-delete').attr('href', url);
    });

    /* Buscador en tablas visibles */
    document.addEventListener('DOMContentLoaded', function () {
        var searchBox = document.getElementById('search-box');
        searchBox.addEventListener('keyup', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('.table').forEach(function (table) {
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        });
    });

    /* Todas las tablas y su botón asociado */
    var allTables = [
        'admins-table', 'alumnos-table', 'alumnosppd', 'adminsB-table',
        'docentes-table', 'inhabilitado-table', 'tutores-table', 'superadmins-table'
    ];

    var tabMap = {
        admins       : 'admins-table',
        alumnos      : 'alumnos-table',
        alumnosppd   : 'alumnosppd',
        adminsB      : 'adminsB-table',
        docentes     : 'docentes-table',
        inhabilitados: 'inhabilitado-table',
        tutores      : 'tutores-table',
        superadmins  : 'superadmins-table',
    };

    function mostrarTabla(tipo) {
        allTables.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        var target = tabMap[tipo];
        if (target) {
            var el = document.getElementById(target);
            if (el) el.style.display = 'table';
        }
    }
</script>
@endpush
@endsection
