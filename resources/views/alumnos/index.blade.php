@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h1 class="h3 mb-0 text-gray-800"><small>Alumnos:</small> {{ $totalRecords }}</h1>
            <a href="{{ route('registerAdmin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Nuevo Alumno &nbsp;<i class="fa fa-plus fa-sm"></i>
            </a>
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
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-4 mt-3">
                        <form action="{{ route('adminAlumnos') }}" method="GET" class="form-inline mt-2">
                            <label for="perPageSelect" class="mr-2">Resultados:</label>
                            <select name="perPage" id="perPageSelect" class="form-control form-control-sm"
                                onchange="this.form.submit()">
                                <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('perPage') == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-lg-4 mt-3">
                        <a href="{{ route('adminAlumnos') }}" class="btn btn-sm btn-primary">Todos</a>
                        <a href="{{ route('adminAlumnos', ['with_user' => 1]) }}" class="btn btn-sm btn-success">Con
                            Login</a>
                        <a href="{{ route('adminAlumnos', ['with_user' => 0]) }}" class="btn btn-sm btn-danger">Sin
                            Login</a>
                    </div>
                    <div class="col-lg-4 mt-3">
                        <form id="searchForm" action="{{ route('adminAlumnos') }}" method="GET">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-sm" placeholder="Buscar..."
                                    name="search" id="searchInput" value="{{ request('search') }}">
                                <input type="hidden" name="search_page" value="true">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"
                                        id="searchButton">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="miTabla" class="table table-hover" style="font-size: 14px">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Nombre
                                    <button class="arrow-icon btn btn-link" data-column="1">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th scope="col" id="fieldHeader">Correo
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="2">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th scope="col" id="fieldHeader">Ciclo
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="3">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th scope="col">Programa
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="4">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th scope="col">DNI
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="5">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alumnos as $alumno)
                                <tr>
                                    <td>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</td>
                                    <td>{{ $alumno->email }}</td>
                                    <td>{{ $alumno->ciclo->nombre }}</td>
                                    <td>{{ $alumno->programa->nombre }}</td>
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
                                    <td colspan="5" class="text-center">No se encontraron alumnos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if (session('error'))
                        <span class="text-danger text-sm">
                            {{ session('error') }}
                        </span>
                    @endif
                    {{ $alumnos->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
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
