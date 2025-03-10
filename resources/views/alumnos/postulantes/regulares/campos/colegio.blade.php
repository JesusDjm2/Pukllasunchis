<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="colegio">Nombre del Colegio: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('colegio') is-invalid @enderror" id="colegio"
            name="colegio" value="{{ old('colegio') }}" required>
        @error('colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="codigo_colegio">Código Modular del Colegio: <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm @error('codigo_colegio') is-invalid @enderror" id="codigo_colegio"
            name="codigo_colegio" value="{{ old('codigo_colegio') }}" required>
        @error('codigo_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="gestion_colegio">Gestión del Colegio: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('gestion_colegio') is-invalid @enderror" id="gestion_colegio" name="gestion_colegio" required>
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="publico" {{ old('gestion_colegio') == 'publico' ? 'selected' : '' }}>Público</option>
            <option value="privado" {{ old('gestion_colegio') == 'privado' ? 'selected' : '' }}>Privado</option>
        </select>
        @error('gestion_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
</div>
<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="direccion_colegio">Dirección del Colegio:</label>
        <input type="text" class="form-control form-control-sm @error('direccion_colegio') is-invalid @enderror" id="direccion_colegio"
            name="direccion_colegio" value="{{ old('direccion_colegio') }}">
        @error('direccion_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="distrito_colegio">Distrito del Colegio:</label>
        <input type="text" class="form-control form-control-sm @error('distrito_colegio') is-invalid @enderror" id="distrito_colegio"
            name="distrito_colegio" value="{{ old('distrito_colegio') }}">
        @error('distrito_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="provincia_colegio">Provincia del Colegio:</label>
        <input type="text" class="form-control form-control-sm @error('provincia_colegio') is-invalid @enderror" id="provincia_colegio"
            name="provincia_colegio" value="{{ old('provincia_colegio') }}">
        @error('provincia_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="departamento_colegio">Departamento del Colegio:</label>
        <input type="text" class="form-control form-control-sm @error('departamento_colegio') is-invalid @enderror" id="departamento_colegio"
            name="departamento_colegio" value="{{ old('departamento_colegio') }}">
        @error('departamento_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="ano_termino_colegio">Año de Término del Colegio:</label>
        <input type="number" class="form-control form-control-sm @error('ano_termino_colegio') is-invalid @enderror" id="ano_termino_colegio"
            name="ano_termino_colegio" value="{{ old('ano_termino_colegio') }}">
        @error('ano_termino_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="promedio_colegio">Promedio General del Colegio:</label>
        <input type="number" class="form-control form-control-sm @error('promedio_colegio') is-invalid @enderror" id="promedio_colegio"
            name="promedio_colegio" value="{{ old('promedio_colegio') }}">
        @error('promedio_colegio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-lg-6 mb-2">
        <label for="lengua_1">Lengua Materna: <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm @error('lengua_1') is-invalid @enderror" id="lengua_1"
            name="lengua_1" value="{{ old('lengua_1') }}" required>
        @error('lengua_1')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-6 mb-2">
        <label for="lengua_2">Segundo Idioma (opcional):</label>
        <input type="text" class="form-control form-control-sm @error('lengua_2') is-invalid @enderror" id="lengua_2"
            name="lengua_2" value="{{ old('lengua_2') }}">
        @error('lengua_2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-lg-4 mb-2">
        <label for="estado_civil">Estado Civil: <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('estado_civil') is-invalid @enderror" id="estado_civil" name="estado_civil" required>
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="soltero" {{ old('estado_civil') == 'soltero' ? 'selected' : '' }}>Soltero</option>
            <option value="casado" {{ old('estado_civil') == 'casado' ? 'selected' : '' }}>Casado</option>
            <option value="divorciado" {{ old('estado_civil') == 'divorciado' ? 'selected' : '' }}>Divorciado</option>
            <option value="viudo" {{ old('estado_civil') == 'viudo' ? 'selected' : '' }}>Viudo</option>
        </select>
        @error('estado_civil')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-lg-4 mb-2">
        <label for="num_hijos">Número de Hijos:</label>
        <input type="number" class="form-control form-control-sm @error('num_hijos') is-invalid @enderror" id="num_hijos"
            name="num_hijos" value="{{ old('num_hijos') }}">
        @error('num_hijos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-2">
        <label for="trabajas">¿Trabajas actualmente? </label>
        <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror" id="trabajas" name="trabajas">
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="1" {{ old('trabajas') == '1' ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ old('trabajas') == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-lg-6 mb-2">
        <label for="donde_trabajas">¿Dónde trabajas? (opcional):</label>
        <input type="text" class="form-control form-control-sm @error('donde_trabajas') is-invalid @enderror" id="donde_trabajas"
            name="donde_trabajas" value="{{ old('donde_trabajas') }}">
        @error('donde_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-6 mb-2">
        <label for="cargo_trabajas">Cargo en el Trabajo</label>
        <input type="text" class="form-control form-control-sm @error('cargo_trabajas') is-invalid @enderror" id="cargo_trabajas"
            name="cargo_trabajas" value="{{ old('cargo_trabajas') }}">
        @error('cargo_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group col-lg-12 mb-2">
        <label for="describe_eespp">Describe por qué elegiste el EESPP: <span class="text-danger">*</span></label>
        <textarea class="form-control form-control-sm @error('describe_eespp') is-invalid @enderror" id="describe_eespp"
            name="describe_eespp" rows="3" required>{{ old('describe_eespp') }}</textarea>
        @error('describe_eespp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
