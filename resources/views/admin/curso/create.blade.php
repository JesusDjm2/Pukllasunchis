@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h3 class="mb-0 text-primary font-weight-bold">Crear nuevo Curso:</h3>
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
                        <form action="{{ route('curso.store') }}" method="POST" autocomplete="off">
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
                                    <label for="nombre">Nombre del Curso:</label>
                                    <input type="text" name="nombre"
                                        class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                        value="{{ old('nombre') }}" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-12 mb-3">
                                    <label for="sumilla">Sumilla:</label>
                                    <textarea name="sumilla" 
                                        class="form-control form-control-sm @error('sumilla') is-invalid @enderror" 
                                        rows="4">{{ old('sumilla') }}</textarea>
                                    @error('sumilla')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label for="cc">Componente Curricular:</label>
                                    <select name="cc" class="form-control form-control-sm @error('cc') is-invalid @enderror" required>
                                        <option value="" selected disabled>Selecciona una opción</option>
                                        <option value="Formacion General" {{ old('cc') == 'Formacion General' ? 'selected' : '' }}>Formación General</option>
                                        <option value="Formacion Específica" {{ old('cc') == 'Formacion Específica' ? 'selected' : '' }}>Formación Específica</option>
                                        <option value="Formacion Práctica e Investigación" {{ old('cc') == 'Formacion Práctica e Investigación' ? 'selected' : '' }}>Formación Práctica e Investigación</option>
                                        <option value="Electivo" {{ old('cc') == 'Electivo' ? 'selected' : '' }}>Electivo</option>
                                        <option value="Extracurricular" {{ old('cc') == 'Extracurricular' ? 'selected' : '' }}>Extracurricular</option>
                                    </select>
                                    @error('cc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>  
                                <div class="col-lg-4 mb-3">
                                    <label for="horas">Horas:</label>
                                    <input type="text" name="horas"
                                        class="form-control form-control-sm @error('horas') is-invalid @enderror"
                                        value="{{ old('horas') }}" required>
                                    @error('horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-lg-4 mb-3">
                                    <label for="creditos">Créditos:</label>
                                    <input type="text" name="creditos"
                                        class="form-control form-control-sm @error('creditos') is-invalid @enderror"
                                        value="{{ old('creditos') }}" required>
                                    @error('creditos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="competencias">Competencias:</label>
                                <div id="competencias">
                                    @foreach ($competencias as $competencia)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="competencias[]"
                                                value="{{ $competencia->id }}" id="competencia{{ $competencia->id }}">
                                            <label class="form-check-label" for="competencia{{ $competencia->id }}">
                                                {{ $competencia->nombre }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="mt-3 mb-3 btn btn-sm btn-primary">Guardar Curso</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // JavaScript para cargar dinámicamente los ciclos según el programa seleccionado
        document.getElementById('programa_id').addEventListener('change', function() {
            var programaId = this.value;
            // Obtener los ciclos relacionados con el programa seleccionado mediante una petición AJAX
            fetch('/obtener-ciclos/' + programaId)
                .then(response => response.json())
                .then(data => {
                    var selectCiclo = document.getElementById('ciclo_id');
                    selectCiclo.innerHTML = ''; // Limpiar opciones anteriores

                    data.forEach(ciclo => {
                        var option = document.createElement('option');
                        option.value = ciclo.id;
                        option.text = ciclo.nombre;
                        selectCiclo.appendChild(option);
                    });
                });
        });
    </script>
@endsection
