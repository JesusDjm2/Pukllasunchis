@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Editar Admin: <span>{{ $admin->name }}</span></h1>
            <a href="{{ route('admin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <p><strong>Programa:</strong>
                    {{ $admin->programa ? $admin->programa->nombre : 'No asignado' }} | <strong>Ciclo:</strong>
                    {{ optional($admin->ciclo)->nombre ?? 'N/A' }} </p>
                <p><strong>Condicion:</strong> {{ $admin->condicion }} | <strong>Perfil: </strong>
                    @if ($admin->perfil)
                        {{ $admin->perfil }}
                    @else
                        Sin perfil
                    @endif
                </p>
                @if ($admin->pendiente)
                    <p>
                        {{ $admin->pendiente }}
                    </p>
                @endif
            </div>
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form id="editForm" method="POST" action="{{ route('adminUpdate', $admin->id) }}" class="mt-3">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ $admin->name }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos:</label>
                            <input type="text"
                                class="form-control form-control-sm @error('apellidos') is-invalid @enderror" id="apellidos"
                                name="apellidos" value="{{ $admin->apellidos }}" required>
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                id="dni" name="dni" value="{{ $admin->dni }}" required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="email" class="form-label">Correo electrónico:</label>
                            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ $admin->email }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-12 mb-3">
                            <label for="role" class="form-label">Asignar Rol:</label>
                            <select id="role" name="role"
                                class="form-control form-control-sm @error('role') is-invalid @enderror">
                                <option disabled>Seleccionar Rol</option>
                                <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Administrador
                                </option>
                                <option value="docente" {{ $currentRole === 'docente' ? 'selected' : '' }}>Docente</option>
                                <option value="alumno" {{ $currentRole === 'alumno' ? 'selected' : '' }}>Alumno</option>
                                <option value="adminB" {{ $currentRole === 'adminB' ? 'selected' : '' }}>Administrador
                                    Bolsa</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-12" id="admin-fields" style="display: none;">
                            <div class="row">
                                <div class="col-lg-3 mb-3">
                                    <label for="programa_id" class="form-label">Seleccionar Programa:</label>
                                    <select id="programa_id" name="programa_id" class="form-control form-control-sm">
                                        <option disabled>Seleccionar Programa</option>
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}"
                                                {{ $programa->id == $currentProgramId ? 'selected' : '' }}>
                                                {{ $programa->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label for="ciclo_id" class="form-label">Seleccionar Ciclo:</label>
                                    <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm">
                                        <option disabled>Seleccionar Ciclo</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}"
                                                {{ $ciclo->id == $currentCicloId ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 mb-2">
                                    <label for="condicion">Condición:</label>
                                    <select class="form-control form-control-sm @error('condicion') is-invalid @enderror"
                                        id="condicion" name="condicion">
                                        <option value="" disabled {{ !$admin->condicion ? 'selected' : '' }}>
                                            Seleccionar Condición</option>
                                        <option value="Regular" {{ $admin->condicion == 'Regular' ? 'selected' : '' }}>
                                            Regular</option>
                                        <option value="Beca Continua"
                                            {{ $admin->condicion == 'Beca Continua' ? 'selected' : '' }}>Beca Continua
                                        </option>
                                        <option value="Beca 18" {{ $admin->condicion == 'Beca 18' ? 'selected' : '' }}>Beca
                                            18</option>
                                        <option value="Beca Puklla"
                                            {{ $admin->condicion == 'Beca Puklla' ? 'selected' : '' }}>Beca Puklla</option>
                                    </select>
                                    @error('condicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-2">
                                    <label for="perfil">Perfil: <small>Este campo se usa para bolsa de
                                            trabajo</small></label>
                                    <select class="form-control form-control-sm @error('perfil') is-invalid @enderror"
                                        id="perfil" name="perfil">
                                        <option value="" disabled {{ !$admin->perfil ? 'selected' : '' }}>Seleccionar
                                            Perfil</option>
                                        <option value="Estudiante" {{ $admin->perfil == 'Estudiante' ? 'selected' : '' }}>
                                            Estudiante</option>
                                        <option value="Bachiller" {{ $admin->perfil == 'Bachiller' ? 'selected' : '' }}>
                                            Bachiller</option>
                                        <option value="Titulado" {{ $admin->perfil == 'Titulado' ? 'selected' : '' }}>
                                            Titulado</option>
                                        <option value="Egresado" {{ $admin->perfil == 'Egresado' ? 'selected' : '' }}>
                                            Egresado</option>
                                    </select>
                                    @error('perfil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-4">
                                    <label for="beca" class="form-label">Beca:</label>
                                    <select id="beca" name="beca" class="form-control form-control-sm">
                                        <option value="1" {{ $admin->beca ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ !$admin->beca ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('beca')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label for="tiene_cursos_pendientes" class="form-label">¿Tiene cursos pendientes?</label>
                                    <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes" class="form-control form-control-sm">
                                        <option value="1" 
                                            {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0" 
                                            {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-lg-12 mb-4" id="cursos-pendientes"
                                    style="{{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 1 ? '' : 'display: none;' }}">
                                    <label for="pendiente" class="form-label">Cursos Pendientes:</label>
                                    <input type="text" id="pendiente" name="pendiente" class="form-control form-control-sm" 
                                           value="{{ old('pendiente', $admin->pendiente ?? '') }}" 
                                           placeholder="Escriba los cursos pendientes separados por comas">
                                    @error('pendiente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tieneCursosPendientes = document.getElementById('tiene_cursos_pendientes');
            var cursosPendientesContainer = document.getElementById('cursos-pendientes');

            tieneCursosPendientes.addEventListener('change', function() {
                if (tieneCursosPendientes.value == 1) {
                    cursosPendientesContainer.style.display = 'block';
                } else {
                    cursosPendientesContainer.style.display = 'none';
                }
            });

            // Ensure the correct display state on page load
            if (tieneCursosPendientes.value == 1 || '{{ old('tiene_cursos_pendientes') }}' == 1) {
                cursosPendientesContainer.style.display = 'block';
            } else {
                cursosPendientesContainer.style.display = 'none';
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-fields');

            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumno' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumno') {
                adminFields.style.display = 'block';
            }
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
