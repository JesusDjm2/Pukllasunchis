<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="name" class="form-label">Nombre: {{ $admin->perfil }}</label>
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
        <label for="password" class="form-label">Nueva Contraseña: <small class="text-primary">(Dejar en
                blanco para no editar)</small></label>
        <input type="password"
            class="form-control form-control-sm @error('password') is-invalid @enderror" id="password"
            name="password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-3">
        <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña: <small
                class="text-primary">(Dejar en blanco para no editar)</small></label>
        <input type="password" class="form-control form-control-sm" id="password_confirmation"
            name="password_confirmation">
    </div>

    <div class="col-lg-12 mb-3">
        <label for="role" class="form-label">Asignar Rol:</label>
        <select id="role" name="role"
            class="form-control form-control-sm @error('role') is-invalid @enderror">
            <option disabled>Seleccionar Rol</option>
            <option value="adminB"
                {{ old('role', $admin->roles->pluck('name')->first()) == 'adminB' ? 'selected' : '' }}>
                Administrador
            </option>
            <option value="alumno"
                {{ in_array(old('role', $admin->roles->pluck('name')->first()), ['alumno', 'alumnoB']) ? 'selected' : '' }}>
                Postulante
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
                    <option value="" disabled>Seleccionar Programa</option>
                    @foreach ($programas as $programa)
                        <option selected value="{{ $programa->id }}"
                            {{ $programa->id == $admin->programa_id ? 'selected' : '' }}>
                            {{ $programa->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-2">
                <label for="perfil">Perfil:</label>
                <select class="form-control form-control-sm @error('perfil') is-invalid @enderror"
                    id="perfil" name="perfil">
                    <option value="" disabled
                        {{ is_null(old('perfil', $admin->perfil)) ? 'selected' : '' }}>
                        Seleccionar Perfil
                    </option>
                    <option value="Estudiante"
                        {{ old('perfil', $admin->perfil) == 'Estudiante' ? 'selected' : '' }}>
                        Estudiante
                    </option>
                    <option value="Titulado"
                        {{ old('perfil', $admin->perfil) == 'Titulado' ? 'selected' : '' }}>
                        Titulado
                    </option>
                    <option value="Bachiller"
                        {{ old('perfil', $admin->perfil) == 'Bachiller' ? 'selected' : '' }}>
                        Bachiller
                    </option>
                    <option value="Egresado"
                        {{ old('perfil', $admin->perfil) == 'Egresado' ? 'selected' : '' }}>
                        Egresado
                    </option>
                </select>
                @error('perfil')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
</div>