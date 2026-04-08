<div class="row">
    <!-- DATOS PERSONALES -->
    <div class="col-lg-12">
        <h3 style="color: #c78d40; font-weight: 500; font-size: 24px;">Datos Personales:</h3>
    </div>

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
        <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror" id="dni"
            name="dni" value="{{ old('dni', $postulante->dni) }}" maxlength="8" pattern="\d*" required>
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
        <label for="direccion">Dirección: <small>(Detallar departamento, Provincia, Distrito)</small></label>
        <input type="text" class="form-control form-control-sm @error('direccion') is-invalid @enderror"
            id="direccion" name="direccion" value="{{ old('direccion', $postulante->direccion) }}">
        @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="numero">Número de teléfono: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('numero') is-invalid @enderror" id="numero"
            name="numero" value="{{ old('numero', $postulante->numero) }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="fecha_nacimiento">Fecha de Nacimiento: <span class="text-danger">*</span></label>
        <input type="date" class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
            id="fecha_nacimiento" name="fecha_nacimiento"
            value="{{ old('fecha_nacimiento', $postulante->fecha_nacimiento ? \Carbon\Carbon::parse($postulante->fecha_nacimiento)->format('Y-m-d') : '') }}">
        @error('fecha_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="lugar_nacimiento">Lugar de Nacimiento: (Detallar dirección)</label>
        <input type="text" class="form-control form-control-sm @error('lugar_nacimiento') is-invalid @enderror"
            id="lugar_nacimiento" name="lugar_nacimiento"
            value="{{ old('lugar_nacimiento', $postulante->lugar_nacimiento) }}">
        @error('lugar_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- CAMPOS DE UBICACIÓN DE NACIMIENTO (CORREGIDOS) -->
    <!-- DEPARTAMENTO DE NACIMIENTO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="departamento_nacimiento_select" class="form-label">Departamento de nacimiento: <span
                    class="text-danger">*</span></label>
            <select id="departamento_nacimiento_select"
                class="form-select form-select-sm @error('departamento_nacimiento') is-invalid @enderror">
                <option value="">-- Selecciona --</option>
                @foreach ($departamentos as $dep)
                    <option value="{{ $dep->id }}">
                        {{ $dep->nombre }}
                    </option>
                @endforeach
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->departamento_nacimiento)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->departamento_nacimiento }}</strong>
                </small>
            @endif

            <!-- Campo hidden para enviar el valor -->
            <input type="hidden" id="departamento_nacimiento" name="departamento_nacimiento"
                value="{{ old('departamento_nacimiento', $postulante->departamento_nacimiento ?? '') }}">

            @error('departamento_nacimiento')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- PROVINCIA DE NACIMIENTO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="provincia_nacimiento_select" class="form-label">Provincia de nacimiento <span
                    class="text-danger">*</span></label>
            <select id="provincia_nacimiento_select"
                class="form-select form-select-sm @error('provincia_nacimiento') is-invalid @enderror">
                <option value="">-- Selecciona un departamento --</option>
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->provincia_nacimiento)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->provincia_nacimiento }}</strong>
                </small>
            @endif

            <!-- Campo hidden para enviar el valor -->
            <input type="hidden" id="provincia_nacimiento" name="provincia_nacimiento"
                value="{{ old('provincia_nacimiento', $postulante->provincia_nacimiento ?? '') }}">

            @error('provincia_nacimiento')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- DISTRITO DE NACIMIENTO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="distrito_nacimiento_select" class="form-label">Distrito de nacimiento <span
                    class="text-danger">*</span></label>
            <select id="distrito_nacimiento_select"
                class="form-select form-select-sm @error('distrito_nacimiento') is-invalid @enderror">
                <option value="">-- Selecciona una provincia --</option>
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->distrito_nacimiento)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->distrito_nacimiento }}</strong>
                </small>
            @endif

            <!-- Campo hidden para enviar el valor -->
            <input type="hidden" id="distrito_nacimiento" name="distrito_nacimiento"
                value="{{ old('distrito_nacimiento', $postulante->distrito_nacimiento ?? '') }}">

            @error('distrito_nacimiento')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group col-lg-6 mb-2">
        <label for="etnicoidad">Autoidentificación Étnica: <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm @error('etnicoidad') is-invalid @enderror" id="etnicoidad"
            name="etnicoidad" required>
            <option disabled {{ old('etnicoidad', $postulante->etnicoidad ?? '') ? '' : 'selected' }}>Seleccione...
            </option>
            <option value="Blanco"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Blanco' ? 'selected' : '' }}>Blanco</option>
            <option value="Aimara"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Aimara' ? 'selected' : '' }}>Aimara</option>
            <option value="Mestizo"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Mestizo' ? 'selected' : '' }}>Mestizo</option>
            <option value="Quechua"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="Afroperuano"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Afroperuano' ? 'selected' : '' }}>Afroperuano
            </option>
            <option value="Nativo o Indígena amazónico"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Nativo o Indígena amazónico' ? 'selected' : '' }}>
                Nativo o Indígena amazónico
            </option>
            <option value="Otro pueblo originario"
                {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Otro pueblo originario' ? 'selected' : '' }}>
                Otro pueblo originario
            </option>
            <option value="Otro" {{ old('etnicoidad', $postulante->etnicoidad ?? '') == 'Otro' ? 'selected' : '' }}>
                Otro</option>
        </select>

        <!-- Mostrar valor actual debajo del campo -->
        @if (isset($postulante) && $postulante->etnicoidad)
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle"></i> Valor actual: <strong>{{ $postulante->etnicoidad }}</strong>
            </small>
        @endif

        @error('etnicoidad')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>


    <!-- CÓMO SE ENTERÓ (AGREGADO) -->
    <div class="form-group col-lg-6 mb-2">
        <label for="contacto">¿Cómo te enteraste del programa de Formación Docente?</label>
        <select class="form-control form-control-sm @error('contacto') is-invalid @enderror" id="contacto"
            name="contacto">
            <option value="">Seleccione una opción</option>
            <option value="Facebook" {{ old('contacto', $postulante->contacto) == 'Facebook' ? 'selected' : '' }}>
                Facebook</option>
            <option value="Instagram" {{ old('contacto', $postulante->contacto) == 'Instagram' ? 'selected' : '' }}>
                Instagram</option>
            <option value="Radio Intiraymi"
                {{ old('contacto', $postulante->contacto) == 'Radio Intiraymi' ? 'selected' : '' }}>Radio Intiraymi
            </option>
            <option value="Radio Salkantay"
                {{ old('contacto', $postulante->contacto) == 'Radio Salkantay' ? 'selected' : '' }}>Radio Salkantay
            </option>
            <option value="Radio Metropolitana"
                {{ old('contacto', $postulante->contacto) == 'Radio Metropolitana' ? 'selected' : '' }}>Radio
                Metropolitana</option>
            <option value="Amigos" {{ old('contacto', $postulante->contacto) == 'Amigos' ? 'selected' : '' }}>Amigos
            </option>
            <option value="Pronabec" {{ old('contacto', $postulante->contacto) == 'Pronabec' ? 'selected' : '' }}>
                Pronabec (BECA)</option>
            <option value="Otros" {{ old('contacto', $postulante->contacto) == 'Otros' ? 'selected' : '' }}>Otros
            </option>
        </select>
        @error('contacto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- ENTIDAD EDUCATIVA (AGREGADA COMPLETA) -->
<div class="col-lg-12 mt-4">
    <h3 style="color: #c78d40; font-weight: 500; font-size: 24px;">Entidad Educativa:</h3>
</div>
<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="colegio">Nombre del Colegio: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('colegio') is-invalid @enderror"
            id="colegio" name="colegio" value="{{ old('colegio', $postulante->colegio) }}" required>
        @error('colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="codigo_colegio">Código Modular del Colegio: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('codigo_colegio') is-invalid @enderror"
            id="codigo_colegio" name="codigo_colegio"
            value="{{ old('codigo_colegio', $postulante->codigo_colegio) }}" required>
        @error('codigo_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="gestion_colegio">Gestión del Colegio: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('gestion_colegio') is-invalid @enderror"
            id="gestion_colegio" name="gestion_colegio" required>
            <option value="" disabled>Seleccione una opción</option>
            <option value="publico"
                {{ old('gestion_colegio', $postulante->gestion_colegio) == 'publico' ? 'selected' : '' }}>Público
            </option>
            <option value="privado"
                {{ old('gestion_colegio', $postulante->gestion_colegio) == 'privado' ? 'selected' : '' }}>Privado
            </option>
            <option value="publico_privado"
                {{ old('gestion_colegio', $postulante->gestion_colegio) == 'publico_privado' ? 'selected' : '' }}>
                Público Gestión Privada
            </option>
        </select>
        @error('gestion_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="direccion_colegio">Dirección del Colegio:</label>
        <input type="text" class="form-control form-control-sm @error('direccion_colegio') is-invalid @enderror"
            id="direccion_colegio" name="direccion_colegio"
            value="{{ old('direccion_colegio', $postulante->direccion_colegio) }}">
        @error('direccion_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- DEPARTAMENTO DE COLEGIO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="departamento_colegio_select" class="form-label">Departamento del colegio: <span
                    class="text-danger">*</span></label>
            <select id="departamento_colegio_select"
                class="form-select form-select-sm @error('departamento_colegio') is-invalid @enderror">
                <option value="">-- Selecciona --</option>
                @foreach ($departamentos as $dep)
                    <option value="{{ $dep->id }}">
                        {{ $dep->nombre }}
                    </option>
                @endforeach
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->departamento_colegio)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->departamento_colegio }}</strong>
                </small>
            @endif

            <input type="hidden" id="departamento_colegio" name="departamento_colegio"
                value="{{ old('departamento_colegio', $postulante->departamento_colegio ?? '') }}">

            @error('departamento_colegio')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- PROVINCIA DE COLEGIO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="provincia_colegio_select" class="form-label">Provincia del colegio <span
                    class="text-danger">*</span></label>
            <select id="provincia_colegio_select"
                class="form-select form-select-sm @error('provincia_colegio') is-invalid @enderror">
                <option value="">-- Selecciona un departamento --</option>
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->provincia_colegio)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->provincia_colegio }}</strong>
                </small>
            @endif

            <input type="hidden" id="provincia_colegio" name="provincia_colegio"
                value="{{ old('provincia_colegio', $postulante->provincia_colegio ?? '') }}">

            @error('provincia_colegio')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- DISTRITO DE COLEGIO -->
    <div class="form-group col-lg-4 mb-2">
        <div class="mb-3">
            <label for="distrito_colegio_select" class="form-label">Distrito del colegio <span
                    class="text-danger">*</span></label>
            <select id="distrito_colegio_select"
                class="form-select form-select-sm @error('distrito_colegio') is-invalid @enderror">
                <option value="">-- Selecciona una provincia --</option>
            </select>

            <!-- Mostrar valor actual -->
            @if (isset($postulante) && $postulante->distrito_colegio)
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Valor actual:
                    <strong>{{ $postulante->distrito_colegio }}</strong>
                </small>
            @endif

            <input type="hidden" id="distrito_colegio" name="distrito_colegio"
                value="{{ old('distrito_colegio', $postulante->distrito_colegio ?? '') }}">

            @error('distrito_colegio')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="ano_termino_colegio">Año de Término del Colegio:</label>
        <input type="number" class="form-control form-control-sm @error('ano_termino_colegio') is-invalid @enderror"
            id="ano_termino_colegio" name="ano_termino_colegio"
            value="{{ old('ano_termino_colegio', $postulante->ano_termino_colegio) }}">
        @error('ano_termino_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="promedio_colegio">Promedio General del Colegio:</label>
        <input type="number" step="0.01"
            class="form-control form-control-sm @error('promedio_colegio') is-invalid @enderror"
            id="promedio_colegio" name="promedio_colegio"
            value="{{ old('promedio_colegio', $postulante->promedio_colegio) }}">
        @error('promedio_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- LENGUAS (AGREGADO) -->
<div class="row">
    <div class="form-group col-lg-6 mb-2">
        <label for="lengua_1">Lengua Materna: <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm @error('lengua_1') is-invalid @enderror" id="lengua_1"
            name="lengua_1" required>
            <option disabled {{ old('lengua_1', $postulante->lengua_1) ? '' : 'selected' }}>Seleccione...</option>
            <option value="Castellano" {{ old('lengua_1', $postulante->lengua_1) == 'Castellano' ? 'selected' : '' }}>
                Castellano</option>
            <option value="Quechua" {{ old('lengua_1', $postulante->lengua_1) == 'Quechua' ? 'selected' : '' }}>
                Quechua</option>
            <option value="Aimara" {{ old('lengua_1', $postulante->lengua_1) == 'Aimara' ? 'selected' : '' }}>Aimara
            </option>
            <option value="Otra lengua aborigen"
                {{ old('lengua_1', $postulante->lengua_1) == 'Otra lengua aborigen' ? 'selected' : '' }}>Otra lengua
                aborigen</option>
            <option value="Lengua extranjera"
                {{ old('lengua_1', $postulante->lengua_1) == 'Lengua extranjera' ? 'selected' : '' }}>Lengua extranjera
            </option>
        </select>
        @error('lengua_1')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-6 mb-2">
        <label for="lengua_2">Segundo Idioma <span class="text-muted">(opcional)</span>:</label>
        <select class="form-select form-select-sm @error('lengua_2') is-invalid @enderror" id="lengua_2"
            name="lengua_2">
            <option disabled {{ old('lengua_2', $postulante->lengua_2) ? '' : 'selected' }}>Seleccione...</option>
            <option value="Castellano" {{ old('lengua_2', $postulante->lengua_2) == 'Castellano' ? 'selected' : '' }}>
                Castellano</option>
            <option value="Quechua" {{ old('lengua_2', $postulante->lengua_2) == 'Quechua' ? 'selected' : '' }}>
                Quechua</option>
            <option value="Aimara" {{ old('lengua_2', $postulante->lengua_2) == 'Aimara' ? 'selected' : '' }}>Aimara
            </option>
            <option value="Otra lengua aborigen"
                {{ old('lengua_2', $postulante->lengua_2) == 'Otra lengua aborigen' ? 'selected' : '' }}>Otra lengua
                aborigen</option>
            <option value="Lengua extranjera"
                {{ old('lengua_2', $postulante->lengua_2) == 'Lengua extranjera' ? 'selected' : '' }}>Lengua extranjera
            </option>
        </select>
        @error('lengua_2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Nivel Quechua -->
    @php
        $mostrarQuechua =
            old('lengua_1', $postulante->lengua_1) === 'Quechua' ||
            old('lengua_2', $postulante->lengua_2) === 'Quechua';
    @endphp
    <div id="nivel-quechua-container" class="col-12 mt-3"
        style="display: {{ $mostrarQuechua ? 'block' : 'none' }};">
        <div class="row">
            <div class="form-group col-lg-6 mb-2">
                <label for="nivel_quechua_hablado">Nivel de Quechua (Hablado):</label>
                <select class="form-select form-select-sm @error('nivel_quechua_hablado') is-invalid @enderror"
                    id="nivel_quechua_hablado" name="nivel_quechua_hablado">
                    <option disabled
                        {{ old('nivel_quechua_hablado', $postulante->nivel_quechua_hablado) ? '' : 'selected' }}>
                        Seleccione...</option>
                    <option value="Básico"
                        {{ old('nivel_quechua_hablado', $postulante->nivel_quechua_hablado) == 'Básico' ? 'selected' : '' }}>
                        Básico</option>
                    <option value="Intermedio"
                        {{ old('nivel_quechua_hablado', $postulante->nivel_quechua_hablado) == 'Intermedio' ? 'selected' : '' }}>
                        Intermedio</option>
                    <option value="Avanzado"
                        {{ old('nivel_quechua_hablado', $postulante->nivel_quechua_hablado) == 'Avanzado' ? 'selected' : '' }}>
                        Avanzado</option>
                </select>
                @error('nivel_quechua_hablado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group col-lg-6 mb-2">
                <label for="nivel_quechua_escrito">Nivel de Quechua (Escrito):</label>
                <select class="form-select form-select-sm @error('nivel_quechua_escrito') is-invalid @enderror"
                    id="nivel_quechua_escrito" name="nivel_quechua_escrito">
                    <option disabled
                        {{ old('nivel_quechua_escrito', $postulante->nivel_quechua_escrito) ? '' : 'selected' }}>
                        Seleccione...</option>
                    <option value="Básico"
                        {{ old('nivel_quechua_escrito', $postulante->nivel_quechua_escrito) == 'Básico' ? 'selected' : '' }}>
                        Básico</option>
                    <option value="Intermedio"
                        {{ old('nivel_quechua_escrito', $postulante->nivel_quechua_escrito) == 'Intermedio' ? 'selected' : '' }}>
                        Intermedio</option>
                    <option value="Avanzado"
                        {{ old('nivel_quechua_escrito', $postulante->nivel_quechua_escrito) == 'Avanzado' ? 'selected' : '' }}>
                        Avanzado</option>
                </select>
                @error('nivel_quechua_escrito')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<!-- INFORMACIÓN ADICIONAL (AGREGADA) -->
<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="estado_civil">Estado Civil: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('estado_civil') is-invalid @enderror" id="estado_civil"
            name="estado_civil" required>
            <option value="" disabled>Seleccione una opción</option>
            <option value="soltero"
                {{ old('estado_civil', $postulante->estado_civil) == 'soltero' ? 'selected' : '' }}>Soltero</option>
            <option value="casado"
                {{ old('estado_civil', $postulante->estado_civil) == 'casado' ? 'selected' : '' }}>
                Casado</option>
            <option value="divorciado"
                {{ old('estado_civil', $postulante->estado_civil) == 'divorciado' ? 'selected' : '' }}>Divorciado
            </option>
            <option value="viudo" {{ old('estado_civil', $postulante->estado_civil) == 'viudo' ? 'selected' : '' }}>
                Viudo</option>
        </select>
        @error('estado_civil')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="num_hijos">Número de Hijos:</label>
        <input type="number" class="form-control form-control-sm @error('num_hijos') is-invalid @enderror"
            id="num_hijos" name="num_hijos" value="{{ old('num_hijos', $postulante->num_hijos) }}">
        @error('num_hijos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="trabajas">¿Trabajas actualmente? </label>
        <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror" id="trabajas"
            name="trabajas">
            <option value="" disabled>Seleccione una opción</option>
            <option value="1" {{ old('trabajas', $postulante->trabajas) == '1' ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ old('trabajas', $postulante->trabajas) == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-6 mb-2">
        <label for="donde_trabajas">¿Dónde trabajas? (opcional):</label>
        <input type="text" class="form-control form-control-sm @error('donde_trabajas') is-invalid @enderror"
            id="donde_trabajas" name="donde_trabajas"
            value="{{ old('donde_trabajas', $postulante->donde_trabajas) }}">
        @error('donde_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-6 mb-2">
        <label for="cargo_trabajas">Cargo en el Trabajo</label>
        <input type="text" class="form-control form-control-sm @error('cargo_trabajas') is-invalid @enderror"
            id="cargo_trabajas" name="cargo_trabajas"
            value="{{ old('cargo_trabajas', $postulante->cargo_trabajas) }}">
        @error('cargo_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-12 mb-2">
        <label for="describe_eespp">Describe por qué elegiste el EESPP: <span class="text-danger">*</span></label>
        <textarea class="form-control form-control-sm @error('describe_eespp') is-invalid @enderror" id="describe_eespp"
            name="describe_eespp" rows="3" required>{{ old('describe_eespp', $postulante->describe_eespp) }}</textarea>
        @error('describe_eespp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- DOCUMENTOS ADJUNTOS (MODIFICADO - SIN REQUIRED) -->
<div class="col-lg-12 mt-4">
    <h3 style="color: #c78d40; font-weight: 500; font-size: 24px;">Documentos Adjuntos:</h3>
    <small class="text-muted">Solo subir si deseas reemplazar los documentos existentes</small>
</div>
<div class="row">
    <div class="form-group col-lg-4 mb-3">
        <label for="dni_pdf">DNI (PDF):</label>
        <input type="file" class="form-control form-control-sm @error('dni_pdf') is-invalid @enderror"
            id="dni_pdf" name="dni_pdf" accept=".pdf">
        @if ($postulante->dni_pdf)
            <small class="text-success">
                <i class="fas fa-check-circle"></i> Archivo actual:
                <a href="{{ asset($postulante->dni_pdf) }}" target="_blank">Ver DNI</a>
            </small>
        @endif
        @error('dni_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-3">
        <label for="voucher_pago">Voucher de Pago (JPG, PNG o PDF):</label>
        <input type="file" class="form-control form-control-sm @error('voucher_pago') is-invalid @enderror"
            id="voucher_pago" name="voucher_pago" accept=".jpg,.jpeg,.png,.pdf">
        @if ($postulante->voucher_pago)
            <small class="text-success">
                <i class="fas fa-check-circle"></i> Archivo actual:
                <a href="{{ asset($postulante->voucher_pago) }}" target="_blank">Ver voucher</a>
            </small>
        @endif
        @error('voucher_pago')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-3">
        <label for="foto">Foto (JPG o PNG):</label>
        <input type="file" class="form-control form-control-sm @error('foto') is-invalid @enderror"
            id="foto" name="foto" accept=".jpg,.jpeg,.png">
        @if ($postulante->foto)
            <small class="text-success">
                <i class="fas fa-check-circle"></i> Archivo actual:
                <a href="{{ asset($postulante->foto) }}" target="_blank">Ver foto</a>
            </small>
        @endif
        @error('foto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-3">
        <label for="partida_nacimiento_pdf">Partida de Nacimiento (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('partida_nacimiento_pdf') is-invalid @enderror"
            id="partida_nacimiento_pdf" name="partida_nacimiento_pdf" accept=".pdf">
        @if ($postulante->partida_nacimiento_pdf)
            <small class="text-success">
                <i class="fas fa-check-circle"></i> Archivo actual:
                <a href="{{ asset($postulante->partida_nacimiento_pdf) }}" target="_blank">Ver partida</a>
            </small>
        @endif
        @error('partida_nacimiento_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-3">
        <label for="certificado_secundaria_pdf">Certificado de Secundaria (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('certificado_secundaria_pdf') is-invalid @enderror"
            id="certificado_secundaria_pdf" name="certificado_secundaria_pdf" accept=".pdf">
        @if ($postulante->certificado_secundaria_pdf)
            <small class="text-success">
                <i class="fas fa-check-circle"></i> Archivo actual:
                <a href="{{ asset($postulante->certificado_secundaria_pdf) }}" target="_blank">Ver certificado</a>
            </small>
        @endif
        @error('certificado_secundaria_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Script para ubicaciones y quechua -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para toggle Quechua
        function toggleQuechuaFields() {
            const lengua1 = document.getElementById('lengua_1').value;
            const lengua2 = document.getElementById('lengua_2').value;
            const container = document.getElementById('nivel-quechua-container');

            if (lengua1 === 'Quechua' || lengua2 === 'Quechua') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        document.getElementById('lengua_1').addEventListener('change', toggleQuechuaFields);
        document.getElementById('lengua_2').addEventListener('change', toggleQuechuaFields);
    });
</script>
