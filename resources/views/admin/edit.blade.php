@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4"
            style="border-bottom: 1px dashed #4848fc78; padding-bottom:1em">
            <h4 class="font-weight-bold text-primary">Editar: <span>{{ $admin->apellidos }}, {{ $admin->name }}</span></h4>
            <a href="{{ route('admin') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <style>
                .vertical-align {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
            </style>
            <div class="col-lg-2" style="border-bottom: 1px dashed #4848fc78; padding-bottom:1em">
                @if ($admin->foto)
                    <label class="font-weight-bold">Foto actual:</label>
                    <img src="{{ asset('img/estudiantes/' . $admin->foto) }}" alt="Foto del usuario" class="img-fluid">
                @else
                    <div
                        style="width: 100%; height: 150px; background-color: #e0e0e0; display: flex; justify-content: center; align-items: center;">
                        <span class="text-muted">Sin foto asignada</span>
                    </div>
                @endif
            </div>
            <div class="col-lg-10 vertical-align" style="border-bottom: 1px dashed #4848fc78; padding-bottom:1em">
                <div>
                    @if ($admin->alumno)
                        <p><strong>Programa:</strong>
                            {{ $admin->programa ? $admin->programa->nombre : 'No asignado' }} | <strong>Ciclo:</strong>
                            {{ optional($admin->ciclo)->nombre ?? 'N/A' }}
                        </p>
                        <p><strong>Condición:</strong> {{ $admin->condicion }} | <strong>Perfil: </strong>
                            @if ($admin->perfil)
                                {{ $admin->perfil }}
                            @else
                                Sin perfil
                            @endif
                        </p>
                        @if ($admin->pendiente)
                            <p>{{ $admin->pendiente }}</p>
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form id="editForm" method="POST" action="{{ route('adminUpdate', $admin->id) }}" class="mt-3"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Campo para subir la foto -->
                        <div class="col-lg-8 mb-3">
                            <label for="foto" class="form-label">Subir Foto:</label>
                            <input type="file" name="foto" id="foto" accept="image/*"
                                class="form-control form-control-sm @error('foto') is-invalid @enderror"
                                onchange="previewImage(event)">
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-2">
                            <label>Subir/Actualizar:</label>
                            <div>
                                <img id="preview" src="#" alt="Previsualización de la imagen"
                                    style="width: 100%; display: none;">
                            </div>
                        </div>

                        <script>
                            function previewImage(event) {
                                const input = event.target;
                                const reader = new FileReader();

                                reader.onload = function() {
                                    const preview = document.getElementById('preview');
                                    preview.src = reader.result;
                                    preview.style.display = 'block';
                                };

                                if (input.files && input.files[0]) {
                                    reader.readAsDataURL(input.files[0]);
                                }
                            }
                        </script>
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
                        <div class="col-lg-6 mb-3">
                            <label for="password">Nueva Contraseña (opcional):</label>
                            <input type="password" name="password" id="password" class="form-control form-control-sm">
                            @if ($errors->has('password'))
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="password_confirmation">Confirmar Contraseña (opcional):</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control form-control-sm" placeholder="Confirmar contraseña">

                            @if ($errors->has('password_confirmation'))
                                <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                            @endif
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
                                <option value="alumnoB" {{ $currentRole === 'alumnoB' ? 'selected' : '' }}>Alumno PPD
                                </option>
                                <option value="adminB" {{ $currentRole === 'adminB' ? 'selected' : '' }}>Administrador
                                    Bolsa</option>
                                <option value="inhabilitado" {{ $currentRole === 'inhabilitado' ? 'selected' : '' }}>
                                    Inhabilitado</option>
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
                                        <option value="Beca 18" {{ $admin->condicion == 'Beca 18' ? 'selected' : '' }}>
                                            Beca
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
                                        <option value="" disabled {{ !$admin->perfil ? 'selected' : '' }}>
                                            Seleccionar
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
                                    <label for="tiene_cursos_pendientes" class="form-label">¿Tiene cursos
                                        pendientes?</label>
                                    <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes"
                                        class="form-control form-control-sm">
                                        <option value="1"
                                            {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 1 ? 'selected' : '' }}>
                                            Sí</option>
                                        <option value="0"
                                            {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 0 ? 'selected' : '' }}>
                                            No</option>
                                    </select>
                                </div>
                                <div class="col-lg-12 mb-4" id="cursos-pendientes"
                                    style="{{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : null) == 1 ? '' : 'display: none;' }}">
                                    <label for="pendiente" class="form-label">Cursos Pendientes:</label>
                                    <input type="text" id="pendiente" name="pendiente"
                                        class="form-control form-control-sm"
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

            /* roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumno' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumno') {
                adminFields.style.display = 'block';
            } */
            function toggleFields() {
                if (roleSelect.value === 'alumno' || roleSelect.value === 'alumnoB') {
                    adminFields.style.display = 'block';
                } else {
                    adminFields.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', toggleFields);
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
