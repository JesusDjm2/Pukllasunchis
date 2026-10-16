@extends($layout ?? 'layouts.admin')
@section('contenido')
@include('admin.usuarios._form_estilos')

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap:.75rem;">
        <div class="reg-header">
            <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                <i class="fas fa-user-plus mr-2" style="color:#4e73df;"></i>Nuevo Registro de Usuario
            </h4>
            <small class="text-muted">Completa los datos para crear una cuenta en el sistema</small>
        </div>
        <a href="javascript:history.back()"
           class="btn btn-sm btn-outline-secondary" style="border-radius:.5rem;">
            <i class="fas fa-arrow-left fa-xs mr-1"></i> Volver
        </a>
    </div>

    <form id="myForm" method="POST" action="{{ route('adminStore') }}">
    @csrf
    <div class="row">

        {{-- Columna principal --}}
        <div class="col-xl-8 col-lg-7">

            {{-- Información personal --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-id-card"></i> Información personal
                </div>
                <div class="reg-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Nombres <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user fa-xs"></i></span>
                                </div>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Nombres" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user fa-xs"></i></span>
                                </div>
                                <input type="text" name="apellidos" id="apellidos"
                                    class="form-control @error('apellidos') is-invalid @enderror"
                                    value="{{ old('apellidos') }}" placeholder="Apellidos" required>
                                @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">DNI <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-id-badge fa-xs"></i></span>
                                </div>
                                <input type="text" name="dni" id="dni"
                                    class="form-control @error('dni') is-invalid @enderror"
                                    value="{{ old('dni') }}" placeholder="Nº de DNI" required>
                                @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Credenciales --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-lock"></i> Credenciales de acceso
                </div>
                <div class="reg-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope fa-xs"></i></span>
                                </div>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                </div>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mín. 7 caracteres" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Confirmar contraseña <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                </div>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Repite la contraseña" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rol --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-shield-alt"></i> Rol del usuario
                </div>
                <div class="reg-card-body">
                    @include('admin.usuarios._form_roles', ['seleccionados' => old('roles', [])])
                </div>
            </div>

            {{-- Datos académicos (alumno / alumnoB) --}}
            <div id="admin-fields" style="display:none;">
                <div class="reg-reveal mb-4">
                    <div class="sec-title">
                        <i class="fas fa-university"></i> Datos académicos
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Programa</label>
                            <select id="programa_id" name="programa_id" class="form-control reg-input-solo">
                                <option selected disabled>Seleccionar Programa</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Ciclo</label>
                            <select id="ciclo_id" name="ciclo_id" class="form-control reg-input-solo">
                                <option disabled selected>Seleccionar Ciclo</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label">Condición</label>
                            <select name="condicion" id="condicion" class="form-control reg-input-solo @error('condicion') is-invalid @enderror">
                                <option value="" disabled {{ old('condicion') ? '' : 'selected' }}>Seleccionar</option>
                                <option value="Regular"       {{ old('condicion')=='Regular'       ? 'selected':'' }}>Regular</option>
                                <option value="Beca Continua" {{ old('condicion')=='Beca Continua' ? 'selected':'' }}>Beca Continua</option>
                                <option value="Beca 18"       {{ old('condicion')=='Beca 18'       ? 'selected':'' }}>Beca 18</option>
                                <option value="Beca Puklla"   {{ old('condicion')=='Beca Puklla'   ? 'selected':'' }}>Beca Puklla</option>
                                <option value="Reincorporación" {{ old('condicion')=='Reincorporación' ? 'selected':'' }}>Reincorporación</option>
                                <option value="Egresado" {{ old('condicion')=='Egresado' ? 'selected':'' }}>Egresado</option>
                            </select>
                            @error('condicion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="reg-label">
                                Perfil <small class="text-muted font-weight-normal">(bolsa de trabajo)</small>
                            </label>
                            <select name="perfil" id="perfil" class="form-control reg-input-solo @error('perfil') is-invalid @enderror">
                                <option value="" disabled {{ old('perfil') ? '' : 'selected' }}>Seleccionar</option>
                                <option value="Estudiante" {{ old('perfil')=='Estudiante' ? 'selected':'' }}>Estudiante</option>
                                <option value="Bachiller"  {{ old('perfil')=='Bachiller'  ? 'selected':'' }}>Bachiller</option>
                                <option value="Titulado"   {{ old('perfil')=='Titulado'   ? 'selected':'' }}>Titulado</option>
                                <option value="Egresado"   {{ old('perfil')=='Egresado'   ? 'selected':'' }}>Egresado</option>
                            </select>
                            @error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="reg-label">Beca</label>
                            <select name="beca" id="beca" class="form-control reg-input-solo">
                                <option value="1" {{ old('beca')==1 ? 'selected':'' }}>Sí</option>
                                <option value="0" {{ old('beca')==0 ? 'selected':'' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="reg-label">¿Cursos pendientes?</label>
                            <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes" class="form-control reg-input-solo">
                                <option value="1" {{ old('tiene_cursos_pendientes')==1 ? 'selected':'' }}>Sí</option>
                                <option value="0" {{ old('tiene_cursos_pendientes')==0 ? 'selected':'' }} selected>No</option>
                            </select>
                        </div>
                        @include('admin._cursos_pendientes', ['actuales' => [], 'mostrar' => old('tiene_cursos_pendientes') == 1])
                    </div>
                </div>
            </div>

            {{-- Datos Bolsa (alumnoB) --}}
            <div id="admin-bolsa" style="display:none;">
                <div class="reg-reveal reg-reveal-blue mb-4">
                    <div class="sec-title">
                        <i class="fas fa-briefcase"></i> Datos Bolsa de Trabajo
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="reg-label">Programa</label>
                            <select name="programa_id" class="form-control reg-input-solo">
                                <option selected disabled>Seleccionar Programa</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-label">Perfil</label>
                            <select name="condicion" class="form-control reg-input-solo">
                                <option value="" disabled {{ old('condicion') ? '' : 'selected' }}>Seleccionar</option>
                                <option value="PPD"        {{ old('condicion')=='PPD'        ? 'selected':'' }}>Estudiante PPD</option>
                                <option value="Practicante"{{ old('condicion')=='Practicante' ? 'selected':'' }}>Practicante</option>
                                <option value="Egresado"   {{ old('condicion')=='Egresado'   ? 'selected':'' }}>Egresado</option>
                                <option value="Titulado"   {{ old('condicion')=='Titulado'   ? 'selected':'' }}>Titulado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /col-xl-8 --}}

        {{-- Sidebar --}}
        <div class="col-xl-4 col-lg-5">
            <div class="reg-card" style="position:sticky; top:1.25rem;">
                <div class="reg-card-head">
                    <i class="fas fa-paper-plane"></i> Publicar
                </div>
                <div class="reg-card-body">
                    <p class="small text-muted mb-3">
                        Revisa la información antes de crear el usuario. Los campos marcados con
                        <span class="text-danger font-weight-bold">*</span> son obligatorios.
                    </p>
                    <button type="submit" class="reg-submit mb-2">
                        <i class="fas fa-user-plus mr-2"></i> Crear Usuario
                    </button>
                    <a href="javascript:history.back()"
                       class="btn btn-sm btn-light btn-block" style="border-radius:.45rem;">
                        <i class="fas fa-times fa-xs mr-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const adminFields = document.getElementById('admin-fields');
    const adminBolsa  = document.getElementById('admin-bolsa');
    const tieneCursos = document.getElementById('tiene_cursos_pendientes');
    const cursosPend  = document.getElementById('cursos-pendientes');
    const programaSel = document.getElementById('programa_id');
    const cicloSel    = document.getElementById('ciclo_id');

    function getRoles() {
        return Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value);
    }
    function toggleFields() {
        const roles = getRoles();
        adminFields.style.display = (roles.includes('alumno') || roles.includes('alumnoB')) ? 'block' : 'none';
        adminBolsa.style.display  = roles.includes('alumnoB') ? 'block' : 'none';
    }
    document.querySelectorAll('.role-checkbox').forEach(cb => cb.addEventListener('change', toggleFields));
    toggleFields();

    tieneCursos.addEventListener('change', function () {
        cursosPend.style.display = this.value == 1 ? 'block' : 'none';
    });

    if (programaSel && cicloSel) {
        programaSel.addEventListener('change', function () {
            cicloSel.innerHTML = '<option disabled selected>Seleccionar Ciclo</option>';
            fetch('/obtener-ciclos/' + this.value)
                .then(r => r.json())
                .then(data => data.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.id; o.text = c.nombre;
                    cicloSel.appendChild(o);
                }));
        });
    }
});
</script>
@endsection
