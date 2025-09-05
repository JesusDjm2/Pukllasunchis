@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #2252f178">
            <h4 class="mb-0 text-primary font-weight-bold">
                Alumnos PPD: <span id="totalRecords">{{ $alumnos->count() }}</span>
                <small>(Mostrando: <span id="filteredRecords">{{ $alumnos->count() }}</span>)</small>
            </h4>
            <div>
                <button class="btn btn-primary btn-sm filter-button" data-programa="3">
                    @if ($counts['Inicial'] > 0)
                        Inicial <small>{{ $counts['Inicial'] }}</small>
                    @else
                        <em>Sin registros</em>
                    @endif
                </button>
                <button class="btn btn-success btn-sm filter-button" data-programa="4">
                    Primaria @if ($counts['Primaria'] > 0)
                        <small>{{ $counts['Primaria'] }}</small>
                    @else
                        <em>Sin registro</em>
                    @endif
                </button>
                <button class="btn btn-danger btn-sm filter-button" data-programa="5">
                    Primaria EIB
                    @if ($counts['Primaria EIB'] > 0)
                        <small>{{ $counts['Primaria EIB'] }}</small>
                    @else
                        <small>(0)</small>
                    @endif
                </button>
            </div>
        </div>

        <!-- Buscador en tiempo real -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                placeholder="Buscar por nombre, apellido o DNI...">
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

        <!-- Tabla de alumnos -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Programa</th>
                        {{-- <th>DNI</th> --}}
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="alumnosTable">
                    @foreach ($alumnos as $alumno)
                        <tr data-programa="{{ $alumno->ciclo->programa->id }}">
                            <td>
                                <strong>{{ $alumno->apellidos }}, {{ $alumno->name }}</strong>
                                <br>
                                <li class="pl-2">{{ $alumno->email }}</li>
                                <li class="pl-2">DNI: {{ $alumno->dni }}</li>

                                <li class="pl-2">
                                    @if ($alumno->alumnoB)
                                        Completó matrícula: <small>✅</small>
                                    @else
                                        Completó matrícula: <small>❌</small>
                                    @endif
                                </li>
                                <li class="pl-2">
                                    @if ($alumno->alumnoB)
                                        Número: {{ $alumno->alumnoB->numero }}
                                    @else
                                        <em>Sin número</em>
                                    @endif
                                </li>
                            </td>
                            <td>{{ $alumno->ciclo->programa->nombre }} - {{ $alumno->ciclo->nombre }}</td>

                            <td>
                                {{-- @if ($alumno->alumnoB)
                                    <a href="{{ route('alumnos.show', $alumno->alumnoB->id) }}"
                                        class="btn btn-sm btn-primary" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('alumnos.edit', $alumno->alumnoB->id) }}"
                                        class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmDelete('{{ route('alumnos.destroy', $alumno->alumnoB->id) }}', '{{ $alumno->apellidos }}, {{ $alumno->nombres }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @else
                                    <em>Sin matrícula</em>
                                @endif --}}
                                @if ($alumno->alumnoB)
                                    <a href="{{ route('ppd.show', $alumno->alumnoB->id) }}" class="btn btn-sm btn-primary"
                                        title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('ppd.edit', $alumno->alumnoB->id) }}" class="btn btn-sm btn-warning"
                                        title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmDelete('{{ route('alumnos.destroy', $alumno->id) }}', '{{ $alumno->apellidos }}, {{ $alumno->nombres }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @else
                                    <em>Sin matrícula</em>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('#alumnosTable tr');
            const filteredRecords = document.getElementById('filteredRecords');
            let activeFilter = null;

            function filtrarTabla() {
                let searchText = searchInput.value.trim().toLowerCase();
                let visibleRows = 0;

                rows.forEach(row => {
                    let nombre = row.cells[0].textContent.toLowerCase();
                    let correo = row.cells[1].textContent.toLowerCase();
                    let dni = row.cells[2].textContent.toLowerCase();

                    let perteneceFiltro = !activeFilter || row.getAttribute('data-programa') ===
                        activeFilter;
                    let coincideBusqueda = nombre.includes(searchText) || correo.includes(searchText) || dni
                        .includes(searchText);

                    if (perteneceFiltro && coincideBusqueda) {
                        row.style.display = "";
                        visibleRows++;
                    } else {
                        row.style.display = "none";
                    }
                });

                filteredRecords.textContent = visibleRows; // Solo se actualiza la cantidad filtrada
            }

            searchInput.addEventListener('input', filtrarTabla);

            document.querySelectorAll('.filter-button').forEach(button => {
                button.addEventListener('click', function() {
                    activeFilter = this.getAttribute('data-programa');
                    searchInput.value = ''; // Limpiar búsqueda al cambiar de filtro
                    filtrarTabla();
                });
            });
        });
    </script>
@endsection
