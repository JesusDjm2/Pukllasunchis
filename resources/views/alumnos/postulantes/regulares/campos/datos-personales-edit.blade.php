<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="email">Email: <span class="text-danger">*</span></label>
        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" id="email"
            name="email" value="{{ old('email', $postulante->email) }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="programa">Programa: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('programa') is-invalid @enderror" id="programa"
            name="programa" required>
            <option value="" disabled>Seleccione un programa</option>
            <option value="Educación Inicial"
                {{ old('programa', $postulante->programa) == 'Educación Inicial' ? 'selected' : '' }}>Educación Inicial
            </option>
            <option value="Educación Primaria EIB"
                {{ old('programa', $postulante->programa) == 'Educación Primaria EIB' ? 'selected' : '' }}>Educación
                Primaria EIB</option>
        </select>
        @error('programa')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="estudio_beca">¿Eres preseleccionado de BECA 18? <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('estudio_beca') is-invalid @enderror" id="estudio_beca"
            name="estudio_beca" required>
            <option value="" disabled>Seleccione una opción</option>
            <option value="1" {{ old('estudio_beca', $postulante->estudio_beca) == '1' ? 'selected' : '' }}>Sí
            </option>
            <option value="0" {{ old('estudio_beca', $postulante->estudio_beca) == '0' ? 'selected' : '' }}>No
            </option>
        </select>
        @error('estudio_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="apellidos">Apellidos: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
            id="apellidos" name="apellidos" value="{{ old('apellidos', $postulante->apellidos) }}" required>
        @error('apellidos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="nombres">Nombre(s): <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('nombres') is-invalid @enderror" id="nombres"
            name="nombres" value="{{ old('nombres', $postulante->nombres) }}" required>
        @error('nombres')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="dni">DNI: <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm @error('dni') is-invalid @enderror" id="dni"
            name="dni" value="{{ old('dni', $postulante->dni) }}" required>
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="genero">Género: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('genero') is-invalid @enderror" id="genero" name="genero"
            required>
            <option value="" disabled>Seleccione</option>
            <option value="1" {{ old('genero', $postulante->genero) == '1' ? 'selected' : '' }}>Masculino</option>
            <option value="0" {{ old('genero', $postulante->genero) == '0' ? 'selected' : '' }}>Femenino</option>
        </select>
        @error('genero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-8 mb-2">
        <label for="direccion">Dirección:</label>
        <input type="text" class="form-control form-control-sm @error('direccion') is-invalid @enderror"
            id="direccion" name="direccion" value="{{ old('direccion', $postulante->direccion) }}">
        @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="numero">Número de teléfono: <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm @error('numero') is-invalid @enderror" id="numero"
            name="numero" value="{{ old('numero', $postulante->numero) }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="fecha_nacimiento">Fecha de Nacimiento: <span class="text-danger">*</span></label>
        <input type="date" class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
            id="fecha_nacimiento" name="fecha_nacimiento"
            value="{{ old('fecha_nacimiento', \Carbon\Carbon::parse($postulante->fecha_nacimiento)->format('Y-m-d')) }}">
        @error('fecha_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="lugar_nacimiento">Lugar de Nacimiento:</label>
        <input type="text" class="form-control form-control-sm @error('lugar_nacimiento') is-invalid @enderror"
            id="lugar_nacimiento" name="lugar_nacimiento"
            value="{{ old('lugar_nacimiento', $postulante->lugar_nacimiento) }}">
        @error('lugar_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="distrito_nacimiento">Distrito de Nacimiento:</label>
        <input type="text" class="form-control form-control-sm @error('distrito_nacimiento') is-invalid @enderror"
            id="distrito_nacimiento" name="distrito_nacimiento"
            value="{{ old('distrito_nacimiento', $postulante->distrito_nacimiento) }}">
        @error('distrito_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="provincia_nacimiento">Provincia de Nacimiento:</label>
        <input type="text"
            class="form-control form-control-sm @error('provincia_nacimiento') is-invalid @enderror"
            id="provincia_nacimiento" name="provincia_nacimiento"
            value="{{ old('provincia_nacimiento', $postulante->provincia_nacimiento) }}">
        @error('provincia_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="departamento_nacimiento">Departamento de Nacimiento:</label>
        <input type="text"
            class="form-control form-control-sm @error('departamento_nacimiento') is-invalid @enderror"
            id="departamento_nacimiento" name="departamento_nacimiento"
            value="{{ old('departamento_nacimiento', $postulante->departamento_nacimiento) }}">
        @error('departamento_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
