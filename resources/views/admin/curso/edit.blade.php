@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h5 class="mb-0 text-primary font-weight-bold">Editar Curso: <span
                    class="text-primary font-weight-bold">{{ $curso->nombre }}</span></h5>
            <a href="{{ route('curso.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
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
                <div class="table-responsive">
                    <div class="container">
                        <form action="{{ route('curso.update', $curso->id) }}" method="POST" autocomplete="off">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label for="programa_id">Programa:</label>
                                    
                                    <select id="programa_id" name="programa_id" class="form-control" required>
                                        <option disabled>Elegir Programa:</option>
                                        @foreach ($programas as $prog)
                                            <option value="{{ $prog->id }}"
                                                {{ $programa->id == $prog->id ? 'selected' : '' }}>
                                                {{ $prog->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label for="ciclo_id">Ciclo:</label>
                                    <select id="ciclo_id" name="ciclo_id" class="form-control" required>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}"
                                                {{ $curso->ciclo_id == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-12 mb-3">
                                    <label for="nombre">Nombre del Curso:</label>
                                    <input type="text" name="nombre"
                                        class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                        value="{{ $curso->nombre }}" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label for="cc">Componente Curricular:</label>
                                    <select name="cc"
                                        class="form-control form-control-sm @error('cc') is-invalid @enderror" required>
                                        <option value="">Selecciona una opción</option>
                                        @foreach (['Formacion General', 'Formacion Específica', 'Formacion Práctica e Investigación', 'Electivo', 'Extracurricular'] as $option)
                                            <option value="{{ $option }}"
                                                {{ $curso->cc == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('cc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label for="horas">Horas:</label>
                                    <input type="text" name="horas"
                                        class="form-control form-control-sm @error('horas') is-invalid @enderror"
                                        value="{{ $curso->horas }}" required>
                                    @error('horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label for="creditos">Créditos:</label>
                                    <input type="text" name="creditos"
                                        class="form-control form-control-sm @error('creditos') is-invalid @enderror"
                                        value="{{ $curso->creditos }}" required>
                                    @error('creditos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-12 mb-3">
                                    <label for="competencias">Competencias:</label>
                                    <div id="competencias">
                                        @foreach ($competencias as $competencia)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="competencias[]"
                                                    value="{{ $competencia->id }}" id="competencia{{ $competencia->id }}"
                                                    {{ in_array($competencia->id, $curso->competencias->pluck('id')->toArray()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="competencia{{ $competencia->id }}">
                                                    {{ $competencia->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Actualizar Curso</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var programaSelect = document.getElementById('programa_id');
            var cicloSelect = document.getElementById('ciclo_id');

            // Cargar ciclos cuando el programa cambia
            programaSelect.addEventListener('change', function() {
                fetch('/obtener-ciclos/' + programaSelect.value)
                    .then(response => response.json())
                    .then(data => {
                        cicloSelect.innerHTML = ''; // Limpiar opciones anteriores

                        data.forEach(ciclo => {
                            var option = document.createElement('option');
                            option.value = ciclo.id;
                            option.text = ciclo.nombre;
                            cicloSelect.appendChild(option);
                        });

                        // Si hay un ciclo seleccionado previamente, mantenlo seleccionado
                        var selectedCicloId = '{{ $curso->ciclo_id }}';
                        cicloSelect.value = selectedCicloId;
                    });
            });
        });
    </script>
@endsection
