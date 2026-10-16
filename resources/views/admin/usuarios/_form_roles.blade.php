{{--
    Selector de roles en "píldoras", compartido por crear y editar usuario.
    $seleccionados = array de nombres de rol marcados.
--}}
@php
    $roleDefs = [
        'admin'        => ['Administrador',   'rp-admin',   'fas fa-users-cog'],
        'docente'      => ['Docente',         'rp-docente', 'fas fa-chalkboard-teacher'],
        'tutor'        => ['Tutor',           'rp-tutor',   'fas fa-chalkboard'],
        'investigador' => ['Investigador',    'rp-investigador', 'fas fa-microscope'],
        'gestor-titulacion' => ['Gestor de Titulación', 'rp-titulacion', 'fas fa-user-graduate'],
        'alumno'       => ['Alumno FID',      'rp-alumno',  'fas fa-graduation-cap'],
        'alumnoB'      => ['Alumno PPD',      'rp-alumnoB', 'fas fa-user-graduate'],
        'adminB'       => ['Admin Bolsa',     'rp-adminB',  'fas fa-briefcase'],
        'inhabilitado' => ['Inhabilitado',    'rp-inh',     'fas fa-ban'],
    ];
@endphp
<div class="role-pill-wrap">
    @foreach ($roleDefs as $val => [$lbl, $cls, $ico])
        <div class="role-pill {{ $cls }}">
            <input class="role-checkbox" type="checkbox"
                name="roles[]" value="{{ $val }}" id="role_{{ $val }}"
                {{ in_array($val, $seleccionados, true) ? 'checked' : '' }}>
            <label for="role_{{ $val }}">
                <i class="{{ $ico }} fa-xs"></i> {{ $lbl }}
            </label>
        </div>
    @endforeach
    @hasrole('super-admin')
        <div class="role-pill rp-sa">
            <input class="role-checkbox" type="checkbox"
                name="roles[]" value="super-admin" id="role_superadmin"
                {{ in_array('super-admin', $seleccionados, true) ? 'checked' : '' }}>
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
