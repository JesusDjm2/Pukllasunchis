@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        @role('admin')
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <small>Alumnos registrados: {{ $totalAlumnos }}</small>
                </h1>
                <a href="{{ route('registerAdmin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    Nuevo Administrador <i class="fa fa-plus fa-sm"></i>
                </a>
            </div>
        @endrole
        @role('alumno')
            <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
                style="border-bottom: 1px dashed #80808078">
                <h3 class="mb-2 text-gray-800">Ficha Técnica: </h3>
                @if (auth()->user()->alumno)
                    @if (!Session::has('mostrar_contenido'))
                        <button type="button" class="btn btn-info btn-sm mb-2" id="mostrar-contenido">
                            Notificar que he terminado con mi registro. <i class="fa fa-smile"></i>
                        </button>
                    @endif
                    {{-- <a href="{{ route('ficha-matricula', ['alumno' => $alumno->id]) }}" class="btn btn-sm btn-info">Descargar ficha de matricula.</a> --}}
                    <span>
                        <a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}"
                            class="btn btn-sm btn-primary">Actualizar
                            Matricula
                        </a>
                    </span>
                @endif
            </div>
        @endrole
        @role('admin')
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
                        <div class="col-lg-6 mb-3">
                            <button class="btn btn-primary btn-sm" onclick="mostrarTabla('admins')">Alumnos</button>
                            <button class="btn btn-info btn-sm" onclick="mostrarTabla('docentes')">Docentes</button>
                            <button class="btn btn-danger btn-sm" onclick="mostrarTabla('inhabilitados')">Inhabilitados
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="mostrarTabla('alumnos')">Administradores</button>
                            <button class="btn btn-success btn-sm" onclick="mostrarTabla('adminsB')">Admins Bolsa</button>
                        </div>
                        <div class="col-lg-6 mb-3" style="float: right">
                            <input type="text" id="search-box"0 class="form-control" placeholder="Buscar Alumnos">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="admins-table" class="table table-hover">
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
                                    <th>Programa
                                        <button class="arrow-icon btn btn-link sort-btn" data-column="3">
                                            <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                        </button>
                                    </th>
                                    <th>Ciclo
                                        <button class="arrow-icon btn btn-link sort-btn" data-column="4">
                                            <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                        </button>
                                    </th>
                                    <th>DNI
                                        <button class="arrow-icon btn btn-link sort-btn" data-column="6">
                                            <span class="fa fa-caret-up text-white" style="font-size: 12px"></span>
                                        </button>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $key => $admin)
                                    @if ($admin->hasRole('alumno'))
                                        <tr class="alumno-row">
                                            <td>{{ $admin->apellidos }}, {{ $admin->name }}</td>
                                            <td>{{ $admin->email }}</td>
                                            <td>{{ optional($admin->programa)->nombre ?? 'N/A' }}</td>
                                            <td style="font-family: serif">{{ optional($admin->ciclo)->nombre ?? 'N/A' }}</td>
                                            {{-- <td>@if ($admin->beca == 1)
                                                Sí
                                            @else
                                                No
                                            @endif</td> --}}
                                            <td>{{ $admin->dni }}</td>
                                            <td>
                                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                                    class="btn btn-info btn-sm" title="Editar"><i class="fa fa-pen"></i></a>
                                                <a onclick="confirmarEliminacion('{{ route('adminDestroy', ['id' => $admin->id]) }}')"
                                                    class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                            </td>
                                            <script>
                                                function confirmarEliminacion(url) {
                                                    if (confirm('¿Estás seguro de que deseas eliminar este administrador?')) {
                                                        window.location.href = url;
                                                    }
                                                }
                                            </script>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <table id="alumnos-table" class="table" style="display: none">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
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
                    <div class="table-responsive">
                        <table id="adminsB-table" class="table" style="display: none">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Acciones</th>
                                </tr>
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
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
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
                    <div class="table-responsive">
                        <table class="table table-hover" id="docentes-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>DNI</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $docenteCount = 0;
                                @endphp
                                @foreach ($admins as $admin)
                                    @if ($admin->hasRole('docente'))
                                        @php
                                            $docenteCount++;
                                        @endphp
                                        <tr>
                                            <td>{{ $docenteCount }}</td>
                                            <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                                            <td>{{ $admin->email }}</td>
                                            <td>{{ $admin->dni }}</td>
                                            {{-- <td>
                                                <a href="{{ route('asignar', ['id' => $admin->id]) }}"
                                                    class="btn btn-success btn-sm" title="Asignar Curso"><i
                                                        class="fa fa-plus fa-sm"></i>
                                                </a>
                                                <a href="{{ route('editcursosasignados', ['id' => $admin->id]) }}"
                                                    class="btn btn-warning btn-sm" title="Editar Cursos Asignados">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td> --}}
                                            <td>
                                                <a href="{{ route('adminEdit', ['id' => $admin->id]) }}"
                                                    class="btn btn-info btn-sm" title="Editar Docente">
                                                    <i class="fa fa-edit"></i> <i class="fa fa-user"></i>
                                                </a>
                                                {{-- <a href="{{ route('adminDestroy', ['id' => $admin->id]) }}"
                                                    class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                                                </a> --}}
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
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
                    <div class="table-responsive">
                        <table class="table table-hover" id="inhabilitado-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>DNI</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $docenteCount = 0;
                                @endphp
                                @foreach ($admins as $admin)
                                    @if ($admin->hasRole('inhabilitado'))
                                        @php
                                            $docenteCount++;
                                        @endphp
                                        <tr>
                                            <td>{{ $docenteCount }}</td> <!-- Enumerator -->
                                            <td>{{ $admin->name }} {{ $admin->apellidos }}</td>
                                            <td>{{ $admin->email }}</td>
                                            <td>{{ $admin->dni }}</td>
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
                </div>
            </div>
        @endrole

        @role('alumno')
            <div class="row bg-white" id="contenido-alumno">
                <div class="col-12">
                    @if (Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                            {{ Session::get('success') }}
                            <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </a>
                        </div>
                    @endif
                    @if (auth()->user()->alumno)
                        <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                            {{ Session::get('success') }} Botón de actualización de datos de ficha de matricula habilitado
                            hasta el 16 de agosto, de no tener datos para
                            actualizar solo llenar el <strong> Número de boleta electrónica emitida por la EES para el ciclo
                                2024 II y guardar el registro. De ser becado, solo guardar registro. </strong>
                            <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </a>
                        </div>
                    @endif
                </div>
                @if (auth()->user()->alumno)
                    <div class="col-lg-12">
                        <div class="p-2 table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="table-dark">Carrera</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Programa:</th>
                                        <td>
                                            <ul>
                                                <li>
                                                    {{ $alumno->programa->nombre }}
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Ciclo:</th>
                                        <td>
                                            @if ($alumno->ciclo)
                                                <ul>
                                                    <li
                                                        style="font-family: 'Courier New', Courier, monospace; font-weight:600">
                                                        {{ $alumno->ciclo->nombre }}
                                                    </li>
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Cursos del semestre:</td>
                                        <td>
                                            @foreach ($alumno->ciclo->cursos as $curso)
                                                <li class="d-flex align-items-center justify-content-between curso-item"
                                                    style="border-bottom: 1px dashed rgba(128, 128, 128, 0.452)">
                                                    <a href="{{ route('curso.show', $curso->id) }}"
                                                        class="mr-2">{{ $curso->nombre }}</a>
                                                    <div class="d-flex align-items-center">
                                                        @if ($curso->docente)
                                                            <small class="mr-2">
                                                                <strong>Docente:</strong> {{ $curso->docente->nombre }}
                                                            </small>
                                                        @endif |
                                                        @if ($curso->silabo)
                                                            <a class="btn btn-success btn-sm d-inline-block"
                                                                href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                target="_blank" title="Ver Sílabo">
                                                                Ver sílabo <i class="fa fa-eye fa-sm"></i>
                                                            </a>
                                                        @endif |
                                                        @if ($curso->classroom)
                                                            <div class="ml-2">
                                                                <a href="{{ $curso->classroom }}" target="_blank">Link
                                                                    <small><i
                                                                            class="fa fa-sm fa-external-link-alt"></i></small></a>
                                                                →
                                                                {{ $curso->clave }} <i class="fa fa-sm fa-lock"></i>
                                                            </div>
                                                        @else
                                                            <div class="ml-2">
                                                                No tiene asignado un Classroom
                                                            </div>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </td>
                                    </tr>

                                    @if (isset($alumno->user->pendiente))
                                        <tr class="bg-danger text-white">
                                            <td>Curso(s) a cargo: <br> <small>*Es responsabilidad del estudiante solicitar la
                                                    subsanación de cursos pendientes.</small></td>
                                            <td>
                                                @php
                                                    $cursos = explode(',', $alumno->user->pendiente);
                                                @endphp
                                                @foreach ($cursos as $curso)
                                                    <li>{{ trim($curso) }}</li>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="table-dark">Datos Personales</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Nombre Completo:</th>
                                        <td>{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">DNI:</th>
                                        <td>{{ $alumno->dni }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Correo:</th>
                                        <td>{{ $alumno->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Número:</th>
                                        <td>{{ $alumno->numero }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Número de referencia:</th>
                                        <td>{{ $alumno->numero_referencia }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Domicilio:</th>
                                        <td>{{ $alumno->direccion }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="col-lg-12">
                        <a class="btn btn-primary" href="{{ route('vistAlumno') }}">Por favor completa tu formulario</a>
                    </div>
                @endif
            </div>
        @endrole

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
                    ¿Estás seguro de que deseas eliminar este administrador?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a id="confirm-delete" class="btn btn-danger" href="#">Eliminar</a>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $('#confirmDeleteModal').on('show.bs.modal', function(e) {
            var button = $(e.relatedTarget); // Botón que activó el modal
            var url = button.data('href'); // Obtiene la URL del atributo data-href
            var modal = $(this);
            modal.find('#confirm-delete').attr('href', url); // Configura el enlace de eliminación
        });
    </script>

    {{-- Script para mostrar contenido de alumnos --}}
    <script>
        document.getElementById('mostrar-contenido')?.addEventListener('click', function() {

            this.style.display = 'none';
            fetch("{{ route('mostrar-contenido') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    @if (auth()->user()->alumno)
                        alumno_id: {{ $alumno->id }}
                    @endif
                }),
            }).then(response => {
                if (response.ok) {
                    alert('Correo enviado correctamente.');
                } else {
                    alert('Error al enviar el correo.');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error al enviar el correo.');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('search-box');
            const tables = document.querySelectorAll('.table');

            searchBox.addEventListener('keyup', function() {
                const searchTerm = searchBox.value.toLowerCase();

                tables.forEach(table => {
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                    for (let i = 0; i < rows.length; i++) {
                        let rowText = rows[i].innerText.toLowerCase();
                        if (rowText.includes(searchTerm)) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>

    <script>
        function mostrarTabla(tipo) {
            if (tipo === 'admins') {
                document.getElementById('admins-table').style.display = 'table';
                document.getElementById('alumnos-table').style.display = 'none';
                document.getElementById('adminsB-table').style.display = 'none';
                document.getElementById('docentes-table').style.display = 'none';
                document.getElementById('inhabilitado-table').style.display = 'none';
            } else if (tipo === 'alumnos') {
                document.getElementById('admins-table').style.display = 'none';
                document.getElementById('alumnos-table').style.display = 'table';
                document.getElementById('adminsB-table').style.display = 'none';
                document.getElementById('docentes-table').style.display = 'none';
                document.getElementById('inhabilitado-table').style.display = 'none';
            } else if (tipo === 'adminsB') {
                document.getElementById('admins-table').style.display = 'none';
                document.getElementById('alumnos-table').style.display = 'none';
                document.getElementById('adminsB-table').style.display = 'table';
                document.getElementById('docentes-table').style.display = 'none';
                document.getElementById('inhabilitado-table').style.display = 'none';
            } else if (tipo === 'docentes') {
                document.getElementById('admins-table').style.display = 'none';
                document.getElementById('alumnos-table').style.display = 'none';
                document.getElementById('adminsB-table').style.display = 'none';
                document.getElementById('docentes-table').style.display = 'table';
                document.getElementById('inhabilitado-table').style.display = 'none';
            } else if (tipo === 'inhabilitados') {
                document.getElementById('admins-table').style.display = 'none';
                document.getElementById('alumnos-table').style.display = 'none';
                document.getElementById('adminsB-table').style.display = 'none';
                document.getElementById('docentes-table').style.display = 'none';
                document.getElementById('inhabilitado-table').style.display = 'table';
            }
        }
    </script>
    <style>
        .curso-item {
            transition: background-color 0.3s ease;
        }

        .curso-item:hover {
            background-color: #e7e7e7;
        }
    </style>
@endsection
