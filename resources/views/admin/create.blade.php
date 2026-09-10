@extends($layout ?? 'layouts.admin')
@section('contenido')
<style>    
.reg-header { border-left:4px solid #4e73df; padding-left:1rem; }
.reg-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:.75rem;
    margin-bottom:1.25rem; overflow:hidden;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.reg-card-head {
    display:flex; align-items:center; gap:.6rem;
    padding:.6rem 1.25rem; background:#f8fafc;
    border-bottom:1px solid #e2e8f0;
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; color:#64748b;
}
.reg-card-body { padding:1.25rem; }

.reg-label {
    font-size:.78rem; font-weight:600; color:#374151;
    margin-bottom:.3rem; display:block;
}
.reg-input-wrap .input-group-text {
    background:#f8fafc; border-color:#d1d5db; color:#9ca3af;
    border-radius:.45rem 0 0 .45rem; font-size:.8rem;
}
.reg-input-wrap .form-control {
    border-radius:0 .45rem .45rem 0 !important;
    border-color:#d1d5db; font-size:.85rem;
    transition:border-color .2s, box-shadow .2s;
}
.reg-input-wrap .form-control:focus {
    border-color:#4e73df;
    box-shadow:0 0 0 .18rem rgba(78,115,223,.18);
}
.reg-input-solo {
    border-radius:.45rem !important; border-color:#d1d5db !important;
    font-size:.85rem !important; transition:border-color .2s, box-shadow .2s !important;
}
.reg-input-solo:focus {
    border-color:#4e73df !important;
    box-shadow:0 0 0 .18rem rgba(78,115,223,.18) !important;
}

.role-pill-wrap { display:flex; flex-wrap:wrap; gap:.5rem; }
.role-pill { position:relative; display:inline-flex; }
.role-pill input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
.role-pill label {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.35rem .9rem; border-radius:2rem; cursor:pointer;
    font-size:.78rem; font-weight:600;
    border:2px solid #e2e8f0; background:#f8fafc; color:#64748b;
    transition:all .18s; user-select:none;
}
.role-pill label:hover { border-color:#94a3b8; background:#f1f5f9; }
.role-pill input:checked + label {
    border-color:var(--rc); background:var(--rb); color:var(--rt);
    box-shadow:0 0 0 3px rgba(var(--rr),.18);
}
.rp-admin   { --rc:#f59e0b; --rb:#fef3c7; --rt:#92400e; --rr:245,158,11; }
.rp-docente { --rc:#06b6d4; --rb:#cffafe; --rt:#0e7490; --rr:6,182,212; }
.rp-tutor   { --rc:#10b981; --rb:#d1fae5; --rt:#065f46; --rr:16,185,129; }
.rp-alumno  { --rc:#3b82f6; --rb:#dbeafe; --rt:#1d4ed8; --rr:59,130,246; }
.rp-alumnoB { --rc:#6366f1; --rb:#ede9fe; --rt:#4338ca; --rr:99,102,241; }
.rp-adminB  { --rc:#64748b; --rb:#f1f5f9; --rt:#334155; --rr:100,116,139; }
.rp-inh     { --rc:#ef4444; --rb:#fee2e2; --rt:#b91c1c; --rr:239,68,68; }
.rp-sa      { --rc:#7c3aed; --rb:#ede9fe; --rt:#5b21b6; --rr:124,58,237; }

/* Sección académica / bolsa */
.reg-reveal {
    border:1px dashed #c7d2fe; border-radius:.65rem;
    background:#f5f3ff; padding:1.1rem 1.25rem; margin-top:.25rem;
}
.reg-reveal-blue {
    border-color:#a5b4fc; background:#eef2ff;
}
.reg-reveal .sec-title {
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; color:#6d28d9;
    display:flex; align-items:center; gap:.5rem;
    margin-bottom:.9rem; padding-bottom:.5rem;
    border-bottom:1px solid #ddd6fe;
}
.reg-reveal-blue .sec-title { color:#4338ca; border-color:#c7d2fe; }

/* Sidebar CTA */
.reg-submit {
    background:#4e73df; border:none; border-radius:.5rem;
    color:#fff; font-weight:700; font-size:.9rem;
    padding:.65rem 1.5rem; width:100%; cursor:pointer;
    transition:background .2s, transform .1s;
}
.reg-submit:hover { background:#3a5fd4; color:#fff; }
.reg-submit:active { transform:scale(.98); }
</style>

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
                    @php
                        $roleDefs = [
                            'admin'        => ['Administrador',   'rp-admin',   'fas fa-users-cog'],
                            'docente'      => ['Docente',         'rp-docente', 'fas fa-chalkboard-teacher'],
                            'tutor'        => ['Tutor',           'rp-tutor',   'fas fa-chalkboard'],
                            'alumno'       => ['Alumno FID',      'rp-alumno',  'fas fa-graduation-cap'],
                            'alumnoB'      => ['Alumno PPD',      'rp-alumnoB', 'fas fa-user-graduate'],
                            'adminB'       => ['Admin Bolsa',     'rp-adminB',  'fas fa-briefcase'],
                            'inhabilitado' => ['Inhabilitado',    'rp-inh',     'fas fa-ban'],
                        ];
                    @endphp
                    <div class="role-pill-wrap">
                        @foreach($roleDefs as $val => [$lbl, $cls, $ico])
                            <div class="role-pill {{ $cls }}">
                                <input class="role-checkbox" type="checkbox"
                                    name="roles[]" value="{{ $val }}" id="role_{{ $val }}"
                                    {{ in_array($val, old('roles', [])) ? 'checked' : '' }}>
                                <label for="role_{{ $val }}">
                                    <i class="{{ $ico }} fa-xs"></i> {{ $lbl }}
                                </label>
                            </div>
                        @endforeach
                        @hasrole('super-admin')
                            <div class="role-pill rp-sa">
                                <input class="role-checkbox" type="checkbox"
                                    name="roles[]" value="super-admin" id="role_superadmin"
                                    {{ in_array('super-admin', old('roles', [])) ? 'checked' : '' }}>
                                <label for="role_superadmin">
                                    <i class="fas fa-crown fa-xs"></i> Super Admin
                                </label>
                            </div>
                        @endhasrole
                    </div>
                    @error('roles')
                        <div class="text-danger small mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </div>
                    @enderror
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
                        <div class="col-md-4 mb-3" id="cursos-pendientes"
                             style="{{ old('tiene_cursos_pendientes')==1 ? '' : 'display:none;' }}">
                            <label class="reg-label">Lista de cursos pendientes</label>
                            <input type="text" name="pendiente" id="pendiente"
                                class="form-control reg-input-solo @error('pendiente') is-invalid @enderror"
                                value="{{ old('pendiente') }}"
                                placeholder="Separados por comas">
                            @error('pendiente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
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
