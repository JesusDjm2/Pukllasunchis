@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 >Asignar cursos a Docente: <span class="text-primary font-weight-bold">{{ $docente->nombre }}</span></h4>
            <a href="{{ route('admin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Volver
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
        <div class="row">
            <div class="col-lg-12">
                {{-- @if ($docente)
                    <p>{{ $docente->nombre }}</p>
                @else
                    <p class="text-danger">Este perfil no es un docente</p>
                @endif --}}
                <form action="{{ route('cursos.asignar', $docente->id) }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="programa_id">Programa:</label>
                            <select id="programa_id" name="programa_id" class="form-control form-control-sm" required>
                                <option selected disabled>Elegir Programa:</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="ciclo_id">Ciclo:</label>
                            <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm" required>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-3">
                            <label for="curso_id">Curso:</label>
                            <select id="curso_id" name="curso_id" class="form-control form-control-sm" required>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-3">
                            <button type="submit" class="mt-3 mb-3 btn btn-sm btn-primary">Asignar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // JavaScript para cargar dinámicamente los ciclos según el programa seleccionado
        document.getElementById('programa_id').addEventListener('change', function() {
            var programaId = this.value;
            if (programaId) {
                fetch('/obtener-ciclos/' + programaId)
                    .then(response => response.json())
                    .then(data => {
                        var selectCiclo = document.getElementById('ciclo_id');
                        selectCiclo.innerHTML =
                        '<option selected disabled>Elegir Ciclo</option>'; // Limpiar opciones anteriores

                        data.forEach(ciclo => {
                            var option = document.createElement('option');
                            option.value = ciclo.id;
                            option.text = ciclo.nombre;
                            selectCiclo.appendChild(option);
                        });
                    });
            } else {
                document.getElementById('ciclo_id').innerHTML = '<option selected disabled>Elegir Ciclo</option>';
                document.getElementById('curso_id').innerHTML = '<option selected disabled>Elegir Curso</option>';
            }
        });

        // JavaScript para cargar dinámicamente los cursos según el ciclo seleccionado
        document.getElementById('ciclo_id').addEventListener('change', function() {
            var cicloId = this.value;
            if (cicloId) {
                fetch('/obtener-cursos/' + cicloId)
                    .then(response => response.json())
                    .then(data => {
                        var selectCurso = document.getElementById('curso_id');
                        selectCurso.innerHTML =
                        '<option selected disabled>Elegir Curso</option>'; // Limpiar opciones anteriores

                        data.forEach(curso => {
                            var option = document.createElement('option');
                            option.value = curso.id;
                            option.text = curso.nombre;
                            selectCurso.appendChild(option);
                        });
                    });
            } else {
                document.getElementById('curso_id').innerHTML = '<option selected disabled>Elegir Curso</option>';
            }
        });
    </script>
@endsection
