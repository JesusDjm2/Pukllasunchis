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
        
        {{-- MOSTRAR ERRORES GENERALES DEL FORMULARIO --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MOSTRAR MENSAJE DE ÉXITO --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

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
                <form id="editForm" method="POST" action="{{ route('adminUpdate', $admin->id) }}" class="mt-3"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Campo para subir la foto -->
                        <div class="col-lg-2 mb-3">
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
                        
                        <div class="col-lg-4 mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <label for="apellidos" class="form-label">Apellidos:</label>
                            <input type="text"
                                class="form-control form-control-sm @error('apellidos') is-invalid @enderror" id="apellidos"
                                name="apellidos" value="{{ old('apellidos', $admin->apellidos) }}" required>
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-2 mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                id="dni" name="dni" value="{{ old('dni', $admin->dni) }}" required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-3 mb-3">
                            <label for="email" class="form-label">Correo electrónico:</label>
                            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-3 mb-3">
                            <label for="password">Nueva Contraseña (opcional):</label>
                            <input type="password" name="password" id="password" class="form-control form-control-sm @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <label for="password_confirmation">Confirmar Contraseña (opcional):</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Confirmar contraseña">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Roles asignados:</label>
                            @php $selectedRoles = old('roles', $currentRoles); @endphp
                            @foreach([
                                'admin'       => 'Administrador',
                                'docente'     => 'Docente',
                                'tutor'       => 'Tutor',
                                'alumno'      => 'Alumno FID',
                                'alumnoB'     => 'Alumno PPD',
                                'adminB'      => 'Administrador Bolsa',
                                'inhabilitado'=> 'Inhabilitado',
                            ] as $val => $label)
                                <div class="form-check">
                                    <input class="form-check-input role-checkbox" type="checkbox"
                                        name="roles[]" value="{{ $val }}" id="role_{{ $val }}"
                                        {{ in_array($val, $selectedRoles) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                            @error('roles')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-6">
                            <div id="motivo-inhabilitado-container" style="display: none;">
                                <label for="perfil_inhabilitado">Motivo de inhabilitación</label>
                                <select name="perfil" id="perfil_inhabilitado"
                                    class="form-control form-control-sm @error('perfil') is-invalid @enderror">
                                    <option value="Deuda" {{ old('perfil', $admin->perfil) == 'Deuda' ? 'selected' : '' }}>Deuda</option>
                                    <option value="Sin matrícula" {{ old('perfil', $admin->perfil) == 'Sin matrícula' ? 'selected' : '' }}>Sin Matrícula</option>
                                    <option value="Licencia" {{ old('perfil', $admin->perfil) == 'Licencia' ? 'selected' : '' }}>Licencia</option>
                                    <option value="Reserva" {{ old('perfil', $admin->perfil) == 'Reserva' ? 'selected' : '' }}>Reserva</option>
                                </select>
                                @error('perfil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-lg-12" id="admin-fields" style="display: none;">
                            <div class="row">
                                <div class="col-lg-3 mb-3">
                                    <label for="programa_id" class="form-label">Seleccionar Programa:</label>
                                    <select id="programa_id" name="programa_id" class="form-control form-control-sm @error('programa_id') is-invalid @enderror">
                                        <option value="">Seleccionar Programa</option>
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}"
                                                {{ old('programa_id', $currentProgramId) == $programa->id ? 'selected' : '' }}>
                                                {{ $programa->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('programa_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label for="ciclo_id" class="form-label">Seleccionar Ciclo:</label>
                                    <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm @error('ciclo_id') is-invalid @enderror">
                                        <option value="">Seleccionar Ciclo</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}"
                                                {{ old('ciclo_id', $currentCicloId) == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ciclo_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-lg-3 mb-2">
                                    <label for="condicion">Condición:</label>
                                    <select class="form-control form-control-sm @error('condicion') is-invalid @enderror"
                                        id="condicion" name="condicion">
                                        <option value="" {{ old('condicion', $admin->condicion) == '' ? 'selected' : '' }}>Seleccionar Condición</option>
                                        <option value="Regular" {{ old('condicion', $admin->condicion) == 'Regular' ? 'selected' : '' }}>Regular</option>
                                        <option value="Beca Continua" {{ old('condicion', $admin->condicion) == 'Beca Continua' ? 'selected' : '' }}>Beca Continua</option>
                                        <option value="Beca 18" {{ old('condicion', $admin->condicion) == 'Beca 18' ? 'selected' : '' }}>Beca 18</option>
                                        <option value="Beca Puklla" {{ old('condicion', $admin->condicion) == 'Beca Puklla' ? 'selected' : '' }}>Beca Puklla</option>
                                    </select>
                                    @error('condicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-2">
                                    <label for="perfil_bolsa">Perfil: <small>Este campo se usa para bolsa de trabajo</small></label>
                                    <select class="form-control form-control-sm @error('perfil') is-invalid @enderror"
                                        id="perfil_bolsa" name="perfil">
                                        <option value="" {{ old('perfil', $admin->perfil) == '' ? 'selected' : '' }}>Seleccionar Perfil</option>
                                        <option value="Estudiante" {{ old('perfil', $admin->perfil) == 'Estudiante' ? 'selected' : '' }}>Estudiante</option>
                                        <option value="Bachiller" {{ old('perfil', $admin->perfil) == 'Bachiller' ? 'selected' : '' }}>Bachiller</option>
                                        <option value="Titulado" {{ old('perfil', $admin->perfil) == 'Titulado' ? 'selected' : '' }}>Titulado</option>
                                        <option value="Egresado" {{ old('perfil', $admin->perfil) == 'Egresado' ? 'selected' : '' }}>Egresado</option>
                                    </select>
                                    @error('perfil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-4">
                                    <label for="beca" class="form-label">Beca:</label>
                                    <select id="beca" name="beca" class="form-control form-control-sm @error('beca') is-invalid @enderror">
                                        <option value="1" {{ old('beca', $admin->beca) == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ old('beca', $admin->beca) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('beca')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label for="tiene_cursos_pendientes" class="form-label">¿Tiene cursos pendientes?</label>
                                    <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes"
                                        class="form-control form-control-sm">
                                        <option value="1" {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : 0) == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : 0) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                
                                <div class="col-lg-12 mb-4" id="cursos-pendientes"
                                    style="{{ old('tiene_cursos_pendientes', isset($admin->pendiente) ? 1 : 0) == 1 ? '' : 'display: none;' }}">
                                    <label for="pendiente" class="form-label">Cursos Pendientes:</label>
                                    <input type="text" id="pendiente" name="pendiente"
                                        class="form-control form-control-sm @error('pendiente') is-invalid @enderror"
                                        value="{{ old('pendiente', $admin->pendiente ?? '') }}"
                                        placeholder="Escriba los cursos pendientes separados por comas">
                                    @error('pendiente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
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
                cursosPendientesContainer.style.display = tieneCursosPendientes.value == 1 ? 'block' : 'none';
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adminFields = document.getElementById('admin-fields');
            const motivoInhabilitadoContainer = document.getElementById('motivo-inhabilitado-container');
            const selectPerfilBolsa = document.getElementById('perfil_bolsa');
            const selectPerfilInhabilitado = document.getElementById('perfil_inhabilitado');

            function getCheckedRoles() {
                return Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value);
            }

            function toggleFields() {
                const roles = getCheckedRoles();

                if (roles.includes('alumno') || roles.includes('alumnoB')) {
                    adminFields.style.display = 'block';
                    motivoInhabilitadoContainer.style.display = 'none';
                    selectPerfilBolsa.name = 'perfil';
                    selectPerfilInhabilitado.removeAttribute('name');
                } else if (roles.includes('inhabilitado')) {
                    adminFields.style.display = 'none';
                    motivoInhabilitadoContainer.style.display = 'block';
                    selectPerfilInhabilitado.name = 'perfil';
                    selectPerfilBolsa.removeAttribute('name');
                } else {
                    adminFields.style.display = 'none';
                    motivoInhabilitadoContainer.style.display = 'none';
                    selectPerfilBolsa.removeAttribute('name');
                    selectPerfilInhabilitado.removeAttribute('name');
                }
            }

            document.querySelectorAll('.role-checkbox').forEach(cb => cb.addEventListener('change', toggleFields));
            toggleFields();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var programaSelector = document.getElementById('programa_id');
            var cicloSelector = document.getElementById('ciclo_id');
            
            programaSelector.addEventListener('change', function() {
                var programaId = programaSelector.value;
                cicloSelector.innerHTML = '<option value="">Seleccionar Ciclo</option>';
                
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