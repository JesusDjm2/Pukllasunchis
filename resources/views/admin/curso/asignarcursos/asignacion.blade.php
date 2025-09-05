@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h5 class="mb-0 text-primary font-weight-bold">Asignar cursos a:
                <span class="text-primary font-weight-bold">{{ $alumno->apellidos }}, {{ $alumno->user->name }} </span>
            </h5>
            <a href="{{ route('admin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>

        <div class="row bg-white">
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="col-lg-12">
                <form action="{{ route('guardar.cursos', $alumno->id) }}" method="POST">
                    @csrf
                    <h6 class="mt-3 font-weight-bold">Cursos del ciclo actual: {{ $alumno->ciclo->programa->nombre }} -
                        {{ $alumno->ciclo->nombre }}</h6>
                    <div class="row">
                        @foreach ($cursosCiclo as $curso)
                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="cursos[]" value="{{ $curso->id }}"
                                        {{ $alumno->cursos->contains($curso->id) || $alumno->cursos->isEmpty() ? 'checked' : '' }}>
                                    {{ $curso->nombre }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <h6 class="mt-4 font-weight-bold">Asignar Cursos</h6>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="programa_id" class="form-label">Programa:</label>
                            <input type="text" class="form-control" value="{{ $alumno->programa->nombre }}" disabled>
                            <input type="hidden" id="programa_id" name="programa_id" value="{{ $alumno->programa_id }}">
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="ciclo_id" class="form-label">Seleccionar Ciclo:</label>
                            <select id="ciclo_id" class="form-control form-control-sm">
                                <option disabled selected>Seleccionar Ciclo</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-3">
                            <label for="curso_id" class="form-label">Seleccionar Cursos:</label>
                            <select id="curso_id" class="form-control form-control-sm" multiple size="6">
                                <option disabled>Seleccionar Cursos</option>
                            </select>
                            <small class="text-muted">Mantén presionado Ctrl (Windows) o Cmd (Mac) para seleccionar
                                varios</small>
                        </div>
                        <div class="col-lg-12 mb-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="agregarCursosBtn">
                                Agregar cursos seleccionados
                            </button>
                        </div>
                        <div id="cursosSeleccionadosContainer" class="mb-3">
                            @foreach ($otrosCursosAsignados as $curso)
                                <div class="mb-1">
                                    <input type="checkbox" name="cursos[]" value="{{ $curso->id }}" checked>
                                    {{ $curso->nombre }} - <strong>Ciclo {{ $curso->ciclo->nombre }}</strong>
                                    <button type="button" class="btn btn-sm btn-link text-danger"
                                        onclick="this.parentElement.remove()">Quitar</button>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="cursos_json" id="cursos_json">
                    </div>
                    <div class="row">
                        <div class="col-lg-12 mb-3 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Guardar</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const programaId = document.getElementById('programa_id').value;
            const cicloSelect = document.getElementById('ciclo_id');
            const cursoSelect = document.getElementById('curso_id');
            const agregarBtn = document.getElementById('agregarCursosBtn');
            const cursosContainer = document.getElementById('cursosSeleccionadosContainer');

            const cursosSeleccionados = new Map(); // curso_id => nombre

            // Cargar ciclos por programa
            if (programaId) {
                fetch('/obtener-ciclos/' + programaId)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(ciclo => {
                            let option = document.createElement('option');
                            option.value = ciclo.id;
                            option.text = ciclo.nombre;
                            cicloSelect.appendChild(option);
                        });
                    });
            }

            // Cargar cursos por ciclo
            cicloSelect.addEventListener('change', () => {
                const cicloId = cicloSelect.value;
                cursoSelect.innerHTML = '<option disabled selected>Seleccionar Curso</option>';

                if (cicloId) {
                    fetch('/get-cursos/' + cicloId)
                        .then(res => res.json())
                        .then(data => {

                            data.forEach(curso => {
                                let option = document.createElement('option');
                                option.value = curso.id;
                                option.text = curso.nombre;
                                option.setAttribute('data-ciclo', curso.ciclo?.nombre ||
                                    '');
                                cursoSelect.appendChild(option);
                            });
                        });
                }
            });

            // Agregar cursos seleccionados

            agregarBtn.addEventListener('click', () => {
                const opciones = Array.from(cursoSelect.selectedOptions);
                opciones.forEach(opcion => {
                    const id = opcion.value;
                    const nombre = opcion.text;
                    const cicloNombre = opcion.getAttribute('data-ciclo');

                    if (!cursosSeleccionados.has(id)) {
                        cursosSeleccionados.set(id, {
                            nombre: nombre,
                            ciclo: cicloNombre
                        });
                    }
                });
                renderizarCursosSeleccionados();
            });

            function renderizarCursosSeleccionados() {
                cursosContainer.innerHTML = '';

                cursosSeleccionados.forEach((info, id) => {
                    const row = document.createElement('div');
                    row.classList.add('mb-1');

                    row.innerHTML = `
                    <input type="checkbox" name="cursos[]" value="${id}" checked>
                    ${info.nombre} - <strong>Ciclo ${info.ciclo}</strong>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="quitarCurso('${id}')">Quitar</button>
                `;

                    cursosContainer.appendChild(row);
                });
            }

            // Quitar curso
            window.quitarCurso = function(id) {
                cursosSeleccionados.delete(id);
                renderizarCursosSeleccionados();
            }
        });
    </script>

@endsection
