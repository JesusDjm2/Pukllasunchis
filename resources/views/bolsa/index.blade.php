@extends('layouts.bolsa')
@section('titulo')
    <title>Administrar Bolsa de Trabajo Pukllasunchis</title>
@endsection
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4></h4>
            <a href="{{ route('trabajo.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                Nuevo Postulante <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>
        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-5">
                        <h5> Postulantes con acceso: {{ $cant }}</h5>
                    </div>
                    <div class="col-lg-7 mb-3" style="float: right">
                        <input type="text" id="search-box"0 class="form-control form-control-sm"
                            placeholder="Busca por acá..">
                    </div>
                    <div class="col-lg-5 mb-3">
                        <button class="btn btn-primary btn-sm" onclick="mostrarTabla('alumnosB')">Postulantes</button>
                        <button class="btn btn-primary btn-sm" onclick="mostrarTabla('adminsB')">Administradores</button>
                    </div>
                    <div class="col-lg-7 mb-3" id="perfiles">
                        <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil(null)">Todos</button>
                        <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Estudiante')">Estudiantes</button>
                        <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Bachiller')">Bachilleres</button>
                        <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Titulado')">Titulados</button>
                        <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Egresado')">Egresados</button>
                    </div>

                </div>
                <div class="table-responsive">
                    <table id="alumnos-table" class="table table-hover">
                        <thead class="thead-dark">
                            <tr style="font-size: 14px">
                                <th>Nombre
                                    <button class="arrow-icon btn btn-link" data-column="1">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>Correo
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="2">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>Programa | Ciclo
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="3">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>DNI
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="4">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>Perfil
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="5">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px">
                            @foreach ($alumnos as $admin)
                                <tr class="alumno-row">
                                    <td>{{ $admin->apellidos }}, {{ $admin->name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td class="text-center">{{$admin->ciclo ? $admin->ciclo->nombre : 'n/a'}}<br>{{ str_replace('Primaria ', '', $admin->programa->nombre) }} </td>
                                    <td>{{ $admin->dni }}</td>
                                    <td class="perfil">{{ $admin->perfil }}</td>
                                    <td>
                                        <a href="{{ route('trabajo.edit', ['trabajo' => $admin->id]) }}"
                                            class="btn btn-success btn-sm" title="Editar"><i class="fa fa-pen"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="mostrarModal('{{ route('trabajo.destroy', ['trabajo' => $admin->id]) }}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
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
                                                    ¿Estás seguro de que deseas eliminar al usuario?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        data-dismiss="modal">Cancelar</button>
                                                    <form id="deleteForm" action="" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function mostrarModal(url) {
                                            $('#confirmDeleteModal').modal('show');
                                            $('#deleteForm').attr('action', url);
                                        }
                                    </script>
                                    <script>
                                        function confirmarEliminacion(url) {
                                            $('#confirmDeleteModal').modal('show');
                                            $('#confirmDeleteBtn').on('click', function() {
                                                window.location.href = url;
                                            });
                                        }
                                    </script>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <script>
                    function filtrarPorPerfil(perfil) {
                        var filas = document.querySelectorAll('#alumnos-table tbody tr');

                        filas.forEach(function(fila) {
                            var perfilAlumno = fila.querySelector('.perfil').textContent.trim();
                            if (perfil === null || perfil === perfilAlumno) {
                                fila.style.display = 'table-row';
                            } else {
                                fila.style.display = 'none';
                            }
                        });
                    }
                </script>
                <div class="table-responsive">
                    <table id="adminsB-table" class="table" style="display: none">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>DNI</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($adminsB as $key => $admin)
                                @if ($admin->hasRole('adminB'))
                                    <tr>
                                        <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->dni }}</td>
                                        <td>
                                            <a href="{{ route('trabajo.edit', ['trabajo' => $admin->id]) }}"
                                                class="btn btn-info btn-sm" title="Editar"><i class="fa fa-pen"></i></a>
                                            <a onclick="confirmarEliminacion('{{ route('trabajo.destroy', ['trabajo' => $admin->id]) }}')"
                                                class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        function filterTable() {
            var searchText = $('#search-box').val().toLowerCase();
            $('.alumno-row').each(function() {
                var row = $(this);
                var found = false;
                row.find('td').each(function() {
                    if ($(this).text().toLowerCase().indexOf(searchText) !== -1) {
                        found = true;
                        return false; // Para salir del bucle each una vez que se haya encontrado una coincidencia
                    }
                });
                row.toggle(found);
            });
        }

        $('#search-box').on('input', function() {
            filterTable();
        });
    </script>
    <script>
        function mostrarTabla(tipo) {
            if (tipo === 'alumnosB') {
                document.getElementById('alumnos-table').style.display = 'table';
                document.getElementById('adminsB-table').style.display = 'none';
                document.getElementById('perfiles').style.display = 'block';
            } else if (tipo === 'adminsB') {
                document.getElementById('alumnos-table').style.display = 'none';
                document.getElementById('adminsB-table').style.display = 'table';
                document.getElementById('perfiles').style.display = 'none';
            }
        }
    </script>
@endsection
