@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h3 class="h3 mb-0 text-gray-800">Crear nuevo Administrador</h3>
            <a href="javascript:history.back()" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <form id="myForm" method="POST" action="{{ route('adminStore') }}" class="mt-3">
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
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador
                                </option>
                                <option value="docente" {{ old('role') === 'docente' ? 'selected' : '' }}>Docente</option>
                                <option value="alumno" {{ old('role') === 'alumno' ? 'selected' : '' }}>Alumno</option>
                                <option value="adminB" {{ old('role') === 'adminB' ? 'selected' : '' }}>Administrador Bolsa
                                </option>
                                <option value="inhabilitado" {{ old('role') === 'inhabilitado' ? 'selected' : '' }}>
                                    Inhabilitado</option>
                                <option value="alumnoB" {{ old('role') === 'alumnoB' ? 'selected' : '' }}>Alumno PPD
                                </option>
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
                                        <option selected disabled>Seleccionar Programa</option>
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label for="ciclo_id" class="form-label">Seleccionar Ciclo:</label>
                                    <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm">
                                        <!-- Opciones de ciclos -->
                                    </select>
                                </div>
                                <div class="col-lg-3 mb-2">
                                    <label for="condicion">Condición:</label>
                                    <select class="form-control form-control-sm @error('condicion') is-invalid @enderror"
                                        id="condicion" name="condicion">
                                        <option value="" disabled {{ old('condicion') ? '' : 'selected' }}>
                                            Seleccionar Condición</option>
                                        <option value="Regular" {{ old('condicion') == 'Regular' ? 'selected' : '' }}>
                                            Regular</option>
                                        <option value="Beca Continua"
                                            {{ old('condicion') == 'Beca Continua' ? 'selected' : '' }}>Beca Continua
                                        </option>
                                        <option value="Beca 18" {{ old('condicion') == 'Beca 18' ? 'selected' : '' }}>Beca
                                            18</option>
                                        <option value="Beca Puklla"
                                            {{ old('condicion') == 'Beca Puklla' ? 'selected' : '' }}>Beca Puklla</option>
                                    </select>
                                    @error('condicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-3 mb-2">
                                    <label for="perfil">Perfil: <small>Este campo se usa para bolsa de trabajo
                                        </small></label>
                                    <select class="form-control form-control-sm @error('perfil') is-invalid @enderror"
                                        id="perfil" name="perfil">
                                        <option value="" disabled {{ old('perfil') ? '' : 'selected' }}>Seleccionar
                                            Perfil</option>
                                        <option value="Estudiante" {{ old('perfil') == 'Estudiante' ? 'selected' : '' }}>
                                            Estudiante</option>
                                        <option value="Bachiller" {{ old('perfil') == 'Bachiller' ? 'selected' : '' }}>
                                            Bachiller</option>
                                        <option value="Titulado" {{ old('perfil') == 'Titulado' ? 'selected' : '' }}>
                                            Titulado</option>
                                        <option value="Egresado" {{ old('perfil') == 'Egresado' ? 'selected' : '' }}>
                                            Egresado</option>
                                    </select>
                                    @error('perfil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-2 mb-4">
                                    <label for="beca" class="form-label">Beca:</label>
                                    <select id="beca" name="beca" class="form-control form-control-sm">
                                        <option value="1" {{ old('beca') == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ old('beca') == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('beca')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-2 mb-3">
                                    <label for="tiene_cursos_pendientes" class="form-label">¿Tiene cursos
                                        pendientes?</label>
                                    <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes"
                                        class="form-control form-control-sm">
                                        <option value="1"
                                            {{ old('tiene_cursos_pendientes') == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0"
                                            {{ old('tiene_cursos_pendientes') == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <div class="col-lg-8 mb-4" id="cursos-pendientes"
                                    style="{{ old('tiene_cursos_pendientes') == 1 ? '' : 'display: none;' }}">
                                    <label for="pendiente" class="form-label">Cursos Pendientes:</label>
                                    <input type="text" id="pendiente" name="pendiente"
                                        class="form-control form-control-sm" value="{{ old('pendiente') }}"
                                        placeholder="Escriba los cursos pendientes separados por comas">
                                    @error('pendiente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                                    <label for="condicion">Perfil:</label>
                                    <select class="form-control form-control-sm @error('condicion') is-invalid @enderror"
                                        id="condicion" name="condicion">
                                        <option value="" disabled {{ old('condicion') ? '' : 'selected' }}>
                                            Seleccionar Condición</option>
                                        <option value="PPD"
                                            {{ old('condicion') == 'PPD' ? 'selected' : '' }}>
                                            Estudiante PPD</option>
                                        <option value="Practicante"
                                            {{ old('condicion') == 'Practicante' ? 'selected' : '' }}>
                                            Practicante</option>
                                        <option value="Egresado" {{ old('condicion') == 'Egresado' ? 'selected' : '' }}>
                                            Egresado
                                        </option>
                                        <option value="Titulado" {{ old('condicion') == 'Titulado' ? 'selected' : '' }}>
                                            Titulado</option>
                                    </select>
                                    @error('condicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener los elementos y el contenedor de cursos pendientes
            var tieneCursosPendientes = document.getElementById('tiene_cursos_pendientes');
            var cursosPendientesContainer = document.getElementById('cursos-pendientes');

            // Mostrar u ocultar el contenedor según la selección
            tieneCursosPendientes.addEventListener('change', function() {
                if (tieneCursosPendientes.value == 1) {
                    cursosPendientesContainer.style.display = 'block';
                } else {
                    cursosPendientesContainer.style.display = 'none';
                }
            });

            // Llamada inicial para establecer la visibilidad según el valor predeterminado
            if (tieneCursosPendientes.value == 1) {
                cursosPendientesContainer.style.display = 'block';
            } else {
                cursosPendientesContainer.style.display = 'none';
            }
        });
    </script>

    {{-- <script>
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
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-bolsa');
            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumnoB' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumnoB') {
                adminFields.style.display = 'block';
            }
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const adminFields = document.getElementById('admin-fields');
            const adminBolsa = document.getElementById('admin-bolsa');
    
            function toggleFields() {
                const role = roleSelect.value;
    
                // Mostrar adminFields si el rol es alumno o alumnoB
                if (role === 'alumno' || role === 'alumnoB') {
                    adminFields.style.display = 'block';
                } else {
                    adminFields.style.display = 'none';
                }
    
                // Mostrar admin-bolsa solo si el rol es alumnoB
                if (role === 'alumnoB') {
                    adminBolsa.style.display = 'block';
                } else {
                    adminBolsa.style.display = 'none';
                }
            }
    
            roleSelect.addEventListener('change', toggleFields);
    
            // Ejecutar al cargar la página
            toggleFields();
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
