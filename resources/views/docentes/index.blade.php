@extends('layouts.admin')
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
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-2 pt-3 pb-1">
            <h4 class="font-weight-bold text-primary">Lista de Docentes: <small>{{ $docentes->count() }} docentes registrados</small></h4>
            <a href="{{ route('registerAdmin') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Crear nuevo Docente <i class="fa fa-plus fa-sm"></i>
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
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="docentes-table">
                        <thead class="thead-dark">
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
                                <tr>
                                    <td>{{ $docente->id }}</td>
                                    <td>
                                        <strong>{{ $docente->nombre }}</strong>
                                        <ul>
                                            <li>{{ $docente->email }}</li>
                                            <li>{{ $docente->dni }}</li>
                                        </ul>
                                    </td>
                                    <td>
                                        @if ($docente->cursos->count() > 0)
                                            <ul>
                                                @php
                                                    $cursosOrdenados = $docente->cursos->sortBy('nombre');
                                                @endphp
                                                @foreach ($cursosOrdenados as $curso)
                                                    <li>
                                                        <strong>{{ $curso->nombre }}</strong> (
                                                        {{ $curso->ciclo->programa->nombre }} -
                                                        {{ $curso->ciclo->nombre }})

                                                        <ul>
                                                            <li>
                                                                @if ($curso->silabo)
                                                                    <a style="font-size: 13px"
                                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                        target="_blank" title="Ver Sílabos">
                                                                        Ver sílabo <i class="fa fa-file-pdf"></i>
                                                                    </a>
                                                                @else
                                                                    <small class="text-muted">Sin Sílabo</small>
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

                                                            @if ($curso->calificaciones->count() > 0)
                                                                <form
                                                                    action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                                                    method="POST">
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
                                                                            style="background: none; border: none; font-size:14px; margin-left: -6px">
                                                                            Ver calificaciones
                                                                        </button>
                                                                    </li>
                                                                </form>
                                                            @else
                                                            <li>
                                                                <small class="text-muted">Sin calificaciones</small>
                                                            </li>                                                                
                                                            @endif
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
