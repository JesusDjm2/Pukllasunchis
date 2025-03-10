<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="email">Email: <span class="text-danger">*</span></label>
        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" id="email"
            name="email" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="programa">Programa: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('programa') is-invalid @enderror" id="programa"
            name="programa" required>
            <option value="" disabled selected>Seleccione un programa</option>
            <option value="Educación Inicial" {{ old('programa') == 'Educación Inicial' ? 'selected' : '' }}>Educación
                Inicial</option>
            <option value="Educación Primaria EIB" {{ old('programa') == 'Educación Primaria EIB' ? 'selected' : '' }}>
                Educación Primaria EIB</option>
        </select>
        @error('programa')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="estudio_beca">¿Eres preseleccionado de BECA 18? <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('estudio_beca') is-invalid @enderror" id="estudio_beca"
            name="estudio_beca" required>
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="1" {{ old('estudio_beca') == '1' ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ old('estudio_beca') == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('estudio_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="apellidos">Apellidos: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
            id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
        @error('apellidos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="nombres">Nombre(s): <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('nombres') is-invalid @enderror" id="nombres"
            name="nombres" value="{{ old('nombres') }}" required>
        @error('nombres')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="dni">DNI: <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm @error('dni') is-invalid @enderror" id="dni"
            name="dni" value="{{ old('dni') }}" required>
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="genero">Género: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('genero') is-invalid @enderror" id="genero" name="genero"
            required>
            <option value="" disabled selected>Seleccione</option>
            <option value="1" {{ old('genero') == '1' ? 'selected' : '' }}>Masculino</option>
            <option value="0" {{ old('genero') == '0' ? 'selected' : '' }}>Femenino</option>
        </select>
        @error('genero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-8 mb-2">
        <label for="direccion">Dirección:</label>
        <input type="text" class="form-control form-control-sm @error('direccion') is-invalid @enderror"
            id="direccion" name="direccion" value="{{ old('direccion') }}">
        @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="numero">Número de teléfono: <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm @error('numero') is-invalid @enderror" id="numero"
            name="numero" value="{{ old('numero') }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="fecha_nacimiento">Fecha de Nacimiento: <span class="text-danger">*</span></label>
        <input type="date" class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
            id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
        @error('fecha_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="lugar_nacimiento">Lugar de Nacimiento: (Detallar dirección)</label>
        <input type="text" class="form-control form-control-sm @error('lugar_nacimiento') is-invalid @enderror"
            id="lugar_nacimiento" name="lugar_nacimiento" value="{{ old('lugar_nacimiento') }}">
        @error('lugar_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{--  <div class="form-group col-lg-4 mb-2">
        <label for="departamento_nacimiento">Departamento de Nacimiento:</label>
        <select 
            class="form-control form-control-sm @error('departamento_nacimiento') is-invalid @enderror"
            id="departamento_nacimiento" name="departamento_nacimiento">
            <option value="">Seleccione un departamento</option>
            @foreach ($departamentos as $departamento)
                <option value="{{ $departamento['id'] }}" 
                    {{ old('departamento_nacimiento') == $departamento['id'] ? 'selected' : '' }}>
                    {{ $departamento['nombre'] }}
                </option>
            @endforeach
        </select>
        @error('departamento_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-lg-4 mb-2">
        <label for="provincia_nacimiento">Provincia de Nacimiento:</label>
        <select 
            class="form-control form-control-sm @error('provincia_nacimiento') is-invalid @enderror"
            id="provincia_nacimiento" name="provincia_nacimiento">
            <option value="">Seleccione una provincia</option>
        </select>
        @error('provincia_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-lg-4 mb-2">
        <label for="distrito_nacimiento">Distrito de Nacimiento:</label>
        <select 
            class="form-control form-control-sm @error('distrito_nacimiento') is-invalid @enderror"
            id="distrito_nacimiento" name="distrito_nacimiento">
            <option value="">Seleccione un distrito</option>
        </select>
        @error('distrito_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    <div class="form-group col-lg-4 mb-2">
        <label for="departamento_nacimiento">Departamento de Nacimiento:</label>
        <select class="form-control form-control-sm @error('departamento_nacimiento') is-invalid @enderror"
            id="departamento_nacimiento" name="departamento_nacimiento">
            <option value="">Seleccione un departamento</option>
            @foreach ($departamentos as $departamento)
                <option value="{{ $departamento['id'] }}"
                    {{ old('departamento_nacimiento') == $departamento['id'] ? 'selected' : '' }}>
                    {{ $departamento['nombre'] }}
                </option>
            @endforeach
        </select>
        @error('departamento_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="provincia_nacimiento">Provincia de Nacimiento:</label>
        <select class="form-control form-control-sm @error('provincia_nacimiento') is-invalid @enderror"
            id="provincia_nacimiento" name="provincia_nacimiento">
            <option value="">Seleccione una provincia</option>
        </select>
        @error('provincia_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-4 mb-2">
        <label for="distrito_nacimiento">Distrito de Nacimiento:</label>
        <select class="form-control form-control-sm @error('distrito_nacimiento') is-invalid @enderror"
            id="distrito_nacimiento" name="distrito_nacimiento">
            <option value="">Seleccione un distrito</option>
        </select>
        @error('distrito_nacimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-lg-12 mb-2">
        <label for="contacto">¿Cómo te enteraste del programa de Formación Docente?</label>
        <select class="form-control form-control-sm @error('contacto') is-invalid @enderror" id="contacto"
            name="contacto">
            <option value="">Seleccione una opción</option>
            <option value="Facebook">Facebook</option>
            <option value="Instagram">Instagram</option>
            <option value="Radio Intiraymi">Radio Intiraymi</option>
            <option value="Radio Salkantay">Radio Salkantay</option>
            <option value="Radio Metropolitana">Radio Metropolitana</option>
            <option value="Amigos">Amigos</option>
            <option value="Pronabec">Pronabec (BECA)</option>
            <option value="Otros">Otros</option>
        </select>
        @error('contacto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <script>
        document.getElementById('departamento_nacimiento').addEventListener('change', function() {
            var departamentoId = Number(this.value); // Convertir a número
            var provincias = @json($provincias);

            var provinciaSelect = document.getElementById('provincia_nacimiento');
            provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';

            var distritoSelect = document.getElementById('distrito_nacimiento');
            distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';

            if (departamentoId && provincias[departamentoId]) {
                provincias[departamentoId].forEach(function(provincia) {
                    var option = document.createElement('option');
                    option.value = provincia.id;
                    option.textContent = provincia.nombre;
                    provinciaSelect.appendChild(option);
                });
            }
        });

        document.getElementById('provincia_nacimiento').addEventListener('change', function() {
            var provinciaId = Number(this.value); // Convertir a número
            var distritos = @json($distritos);

            var distritoSelect = document.getElementById('distrito_nacimiento');
            distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';

            if (provinciaId && distritos[provinciaId]) {
                distritos[provinciaId].forEach(function(distrito) {
                    var option = document.createElement('option');
                    option.value = distrito.id;
                    option.textContent = distrito.nombre;
                    distritoSelect.appendChild(option);
                });
            }
        });

        // Disparar evento para cargar provincias si ya hay un departamento seleccionado
        if (document.getElementById('departamento_nacimiento').value) {
            document.getElementById('departamento_nacimiento').dispatchEvent(new Event('change'));
        }
    </script>

</div>
