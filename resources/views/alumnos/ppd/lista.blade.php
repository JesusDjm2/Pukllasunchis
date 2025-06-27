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
                <button class="btn btn-primary btn-sm filter-button" data-programa="3">Inicial</button>
                <button class="btn btn-success btn-sm filter-button" data-programa="4">Primaria</button>
                <button class="btn btn-danger btn-sm filter-button" data-programa="5">Primaria EIB</button>
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
                        <th>Correo</th>
                        <th>DNI</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="alumnosTable">
                    @foreach ($alumnos as $alumno)
                        <tr data-programa="{{ $alumno->ciclo->programa->id }}">
                            <td>
                                <strong>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</strong>
                                <br>
                                <li class="pl-2">{{ $alumno->ciclo->programa->nombre }} - Ciclo {{ $alumno->ciclo->nombre }}</li>
                                <li class="pl-2">{{$alumno->numero}}</li>
                            </td>
                            <td>{{ $alumno->email }}</td>
                            <td>{{ $alumno->dni }}</td>
                            <td></td>
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
