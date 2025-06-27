<div class="row">
    <div class="col-lg-12">
        <h4 class="mb-2 mt-4">Aspectos Vivienda</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="tipo_vivienda">Tipo de vivienda:</label>
        <select class="form-control form-control-sm @error('tipo_vivienda') is-invalid @enderror" id="tipo_vivienda"
            name="tipo_vivienda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Casa independiente" {{ old('tipo_vivienda') == 'Casa independiente' ? 'selected' : '' }}>Casa
                independiente</option>
            <option value="Departamento o edificio"
                {{ old('tipo_vivienda') == 'Departamento o edificio' ? 'selected' : '' }}>Departamento o edificio
            </option>
            <option value="Condominio" {{ old('tipo_vivienda') == 'Condominio' ? 'selected' : '' }}>Condominio</option>
            <option value="Casa de vecindad" {{ old('tipo_vivienda') == 'Casa de vecindad' ? 'selected' : '' }}>Casa de
                vecindad</option>
            <option value="Casa de pensión" {{ old('tipo_vivienda') == 'Casa de pensión' ? 'selected' : '' }}>Casa de
                pensión</option>
            <option value="Cuarto alquilado" {{ old('tipo_vivienda') == 'Cuarto alquilado' ? 'selected' : '' }}>Cuarto
                alquilado</option>
            <option value="Otro" {{ old('tipo_vivienda') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('tipo_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="situacion_vivienda">Situación de vivienda:</label>
        <select class="form-control form-control-sm @error('situacion_vivienda') is-invalid @enderror"
            id="situacion_vivienda" name="situacion_vivienda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Propia" {{ old('situacion_vivienda') == 'Propia' ? 'selected' : '' }}>Propia</option>
            <option value="Alquilada" {{ old('situacion_vivienda') == 'Alquilada' ? 'selected' : '' }}>Alquilada
            </option>
            <option value="Alquiler venta" {{ old('situacion_vivienda') == 'Alquiler venta' ? 'selected' : '' }}>
                Alquiler venta</option>
            <option value="Prestada" {{ old('situacion_vivienda') == 'Prestada' ? 'selected' : '' }}>Prestada</option>
            <option value="Otra" {{ old('situacion_vivienda') == 'Otra' ? 'selected' : '' }}>Otra</option>
        </select>
        @error('situacion_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="dormitorios_vivienda">Número de dormitorios de vivienda:</label>
        <input type="number" class="form-control form-control-sm @error('dormitorios_vivienda') is-invalid @enderror"
            id="dormitorios_vivienda" name="dormitorios_vivienda" value="{{ old('dormitorios_vivienda') }}" required>
        @error('dormitorios_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="banos_vivienda">Número de baños de vivienda:</label>
        <input type="number" class="form-control form-control-sm @error('banos_vivienda') is-invalid @enderror"
            id="banos_vivienda" name="banos_vivienda" value="{{ old('banos_vivienda') }}" required>
        @error('banos_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="material_vivienda">Tipo de material de la vivienda:</label>
        <select class="form-control form-control-sm @error('material_vivienda') is-invalid @enderror"
            id="material_vivienda" name="material_vivienda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Cemento/Ladrillo" {{ old('material_vivienda') == 'Cemento/Ladrillo' ? 'selected' : '' }}>
                Cemento/Ladrillo</option>
            <option value="Adobe" {{ old('material_vivienda') == 'Adobe' ? 'selected' : '' }}>Adobe</option>
            <option value="Quincha" {{ old('material_vivienda') == 'Quincha' ? 'selected' : '' }}>Quincha</option>
            <option value="Otro" {{ old('material_vivienda') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('material_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_agua">Horas disponibles de agua al día:</label>
        <input type="number" class="form-control form-control-sm @error('hrs_disponibles_agua') is-invalid @enderror"
            id="hrs_disponibles_agua" name="hrs_disponibles_agua" value="{{ old('hrs_disponibles_agua') }}" required>
        @error('hrs_disponibles_agua')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_desague">Horas disponibles de desagüe al día:</label>
        <input type="number"
            class="form-control form-control-sm @error('hrs_disponibles_desague') is-invalid @enderror"
            id="hrs_disponibles_desague" name="hrs_disponibles_desague" value="{{ old('hrs_disponibles_desague') }}"
            required>
        @error('hrs_disponibles_desague')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_luz">Horas disponibles de luz al día:</label>
        <input type="number" class="form-control form-control-sm @error('hrs_disponibles_luz') is-invalid @enderror"
            id="hrs_disponibles_luz" name="hrs_disponibles_luz" value="{{ old('hrs_disponibles_luz') }}" required>
        @error('hrs_disponibles_luz')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>Bienes en la vivienda:</label><br>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Cocina a gas"
                {{ in_array('Cocina a gas', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Cocina a gas
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Cocina electrica"
                {{ in_array('Cocina electrica', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Cocina eléctrica
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Aspiradora"
                {{ in_array('Aspiradora', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Aspiradora
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Televisor"
                {{ in_array('Televisor', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Televisor
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="DVD"
                {{ in_array('DVD', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            DVD
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Mini componente"
                {{ in_array('Mini componente', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Mini componente
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Cámara de video"
                {{ in_array('Cámara de video', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Cámara de video
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Computadora"
                {{ in_array('Computadora', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Computadora
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Horno microondas"
                {{ in_array('Horno microondas', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Horno microondas
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Lavadora"
                {{ in_array('Lavadora', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Lavadora
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Secadora de ropa"
                {{ in_array('Secadora de ropa', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Secadora de ropa
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Automóvil"
                {{ in_array('Automóvil', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Automóvil
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Bicicleta"
                {{ in_array('Bicicleta', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Bicicleta
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Motocicleta"
                {{ in_array('Motocicleta', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Motocicleta
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Juego de video"
                {{ in_array('Juego de video', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Juego de video
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="Refrigeradora"
                {{ in_array('Refrigeradora', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Refrigeradora
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="bienes_vivienda[]"
                value="Ninguna de las anteriores"
                {{ in_array('Ninguna de las anteriores', old('bienes_vivienda', [])) ? 'checked' : '' }}>
            Ninguna de las anteriores
        </label>
        @error('bienes_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>Otros servicios en la vivienda:</label><br>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="otros_servicios[]" value="Empleado(a) doméstico"
                {{ in_array('Empleado(a) doméstico', old('otros_servicios', [])) ? 'checked' : '' }}>
            Empleado(a) doméstico
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="otros_servicios[]" value="Servicio de teléfono"
                {{ in_array('Servicio de teléfono', old('otros_servicios', [])) ? 'checked' : '' }}>
            Servicio de teléfono
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="otros_servicios[]" value="Servicio de cable"
                {{ in_array('Servicio de cable', old('otros_servicios', [])) ? 'checked' : '' }}>
            Servicio de cable
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="otros_servicios[]" value="Servicio de Internet"
                {{ in_array('Servicio de Internet', old('otros_servicios', [])) ? 'checked' : '' }}>
            Servicio de Internet
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="otros_servicios[]"
                value="Ninguna de las anteriores"
                {{ in_array('Ninguna de las anteriores', old('otros_servicios', [])) ? 'checked' : '' }}>
            Ninguna de las anteriores
        </label>
    </div>
</div>
