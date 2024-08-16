@extends('layouts.bolsa')
@section('titulo')
    <title>Registrar nuevo Usuario</title>
@endsection
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h3 class="h3 mb-0 text-gray-800">Crear nuevo Postulante</h3>
            <a href="javascript:history.go(-1);" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="col-lg-12 mt-2 mb-2">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <form id="myForm" method="POST" action="{{ route('trabajo.store') }}" class="mt-3">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="apellidos" class="form-label">Apellidos:</label>
                            <input type="text"
                                class="form-control form-control-sm @error('apellidos') is-invalid @enderror" id="apellidos"
                                name="apellidos" value="{{ old('apellidos') }}" required>
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                id="dni" name="dni" value="{{ old('dni') }}" required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="email" class="form-label">Correo electrónico:</label>
                            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="password" class="form-label">Contraseña:</label>
                            <input type="password"
                                class="form-control form-control-sm @error('password') is-invalid @enderror" id="password"
                                name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña:</label>
                            <input type="password" class="form-control form-control-sm" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <label for="role" class="form-label">Asignar Rol:</label>
                            <select id="role" name="role"
                                class="form-control form-control-sm @error('role') is-invalid @enderror">
                                <option selected disabled>Seleccionar Rol</option>
                                <option value="adminB" {{ old('role') === 'adminB' ? 'selected' : '' }}>Administrador
                                </option>
                                <option value="alumno" {{ old('role') === 'alumno' ? 'selected' : '' }}>Postulante
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-12" id="admin-bolsa" style="display:none">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="programa_id" class="form-label">Seleccionar Programa:</label>
                                    <select id="programa_id" name="programa_id" class="form-control form-control-sm">
                                        <option selected disabled>Seleccionar Programa</option>
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <label for="perfil">Perfil:</label>
                                    <select class="form-control form-control-sm @error('perfil') is-invalid @enderror"
                                        id="perfil" name="perfil">
                                        <option value="" disabled {{ old('perfil') ? '' : 'selected' }}>
                                            Seleccionar perfil</option>
                                        <option value="Estudiante"
                                            {{ old('perfil') == 'Estudiante' ? 'selected' : '' }}>
                                            Estudiante</option>
                                        <option value="Bachiller" {{ old('perfil') == 'Bachiller' ? 'selected' : '' }}>
                                            Bachiller
                                        </option>
                                        <option value="Titulado" {{ old('perfil') == 'Titulado' ? 'selected' : '' }}>
                                            Titulado</option>
                                        <option value="Egresado" {{ old('perfil') == 'Egresado' ? 'selected' : '' }}>
                                            Egresado</option>
                                    </select>
                                    @error('perfil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary ml-3 btn-sm">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-bolsa');
            
            function toggleAdminFields() {
                adminFields.style.display = roleSelect.value === 'alumno' || roleSelect.value === 'alumnoB' ? 'block' : 'none';
            }
    
            roleSelect.addEventListener('change', toggleAdminFields);
    
            // Llama a toggleAdminFields para verificar el estado inicial
            toggleAdminFields();
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var programaSelector = document.getElementById('programa_id');
            var cicloSelector = document.getElementById('ciclo_id');

            programaSelector.addEventListener('change', function() {
                var programaId = programaSelector.value;
                cicloSelector.innerHTML = '<option disabled selected>Seleccionar Ciclo</option>';
                if (programaId) {
                    fetch('/obtener-ciclos/' + programaId)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(ciclo => {
                                var option = document.createElement('option');
                                option.value = ciclo.id;
                                option.text = ciclo.nombre;
                                cicloSelector.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
@endsection
