@extends('layouts.bolsa')
@section('titulo')
    <title>Lista de postulantes registrados</title>
@endsection
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="row mb-3">
            <div class="col-lg-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <small>Postulantes registrados: {{ $cantidad }}</small>
                </h1>
            </div>
            <div class="col-lg-5">
                <button class="btn btn-danger btn-sm" onclick="filtrarPorPerfil(null)">Todos</button>
                <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Estudiante')">Estudiantes</button>
                <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Bachiller')">Bachilleres</button>
                <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Titulado')">Titulados</button>
                <button class="btn btn-info btn-sm" onclick="filtrarPorPerfil('Egresado')">Egresados</button>
            </div>
            <div class="col-lg-3">
                <a href="{{ route('trabajo.create') }}"
                    class="float-right d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                    Nuevo postulante <i class="fa fa-plus fa-sm"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
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
            <div class="col-lg-12 mb-3">
                <input type="text" id="search-box" class="form-control form-control-sm" placeholder="Buscar...">
            </div>
            <div class="col-lg-12 mb-3" id="perfiles">

            </div>
            <div class="col-12">
                <div class="table-responsive">
                    <table id="alumnos-table" class="table table-hover" style="font-size: 15px">
                        <thead class="thead-dark">
                            <tr>
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
                                <th>DNI
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="3">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th>Perfil
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="4">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>
                                <th class="text-center">Ciclo | Programa
                                    <button class="arrow-icon btn btn-link sort-btn" data-column="5">
                                        <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                    </button>
                                </th>                                
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($postulantes as $post)
                                <tr class="postulante-row">
                                    <td>{{ $post->apellidos }}, {{ $post->nombre }}</td>
                                    <td>{{ $post->email }}</td>
                                    <td>{{ $post->dni }}</td>
                                    <td class="perfil">{{ $post->user->perfil }}</td>
                                    <td> {{$post->user->ciclo->nombre}} <br>
                                        @if ($post->user->programa->nombre === 'Programa Primaria EIB')
                                            Programa EIB
                                        @else
                                            Inicial
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <a href="{{ route('postulante.edit', ['postulante' => $post->id]) }}"
                                            class="btn btn-success btn-sm" title="Editar como Postulante"><i
                                                class="fa fa-pen"></i>
                                        </a>
                                        <a href="{{ route('trabajo.edit', ['trabajo' => $post->user->id, 'origin' => 'listaPostulantes']) }}"
                                            class="btn btn-primary btn-sm" title="Editar como Usuario">
                                            <i class="fa fa-user"></i>
                                        </a>
                                        <a href="{{ route('postulante.show', ['postulante' => $post->id]) }}"
                                            class="btn btn-sm btn-info" title="Ver postulante"><i class="fa fa-eye"></i></a>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="mostrarModal('{{ route('postulante.destroy', ['postulante' => $post->id]) }}')">
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
                                                        Eliminado</h5>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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
    <script>
        $(document).ready(function() {
            $('#search-box').keyup(function() {
                var searchText = $(this).val().toLowerCase();
                $('.postulante-row').each(function() {
                    var textToMatch = $(this).text().toLowerCase();
                    if (textToMatch.indexOf(searchText) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
        });
    </script>
@endsection
