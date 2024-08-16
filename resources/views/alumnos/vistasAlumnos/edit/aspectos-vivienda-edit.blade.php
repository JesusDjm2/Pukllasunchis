<div class="row">
    <div class="col-lg-6 mb-2 mt-2">
        <label for="tipo_vivienda">Tipo de vivienda:</label>
        <select class="form-control form-control-sm @error('tipo_vivienda') is-invalid @enderror" id="tipo_vivienda"
            name="tipo_vivienda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Casa independiente"
                {{ isset($alumno) && $alumno->tipo_vivienda == 'Casa independiente' ? 'selected' : '' }}>Casa
                independiente</option>
            <option value="Departamento o edificio"
                {{ isset($alumno) && $alumno->tipo_vivienda == 'Departamento o edificio' ? 'selected' : '' }}>
                Departamento o edificio
            </option>
            <option value="Condominio" {{ isset($alumno) && $alumno->tipo_vivienda == 'Condominio' ? 'selected' : '' }}>
                Condominio</option>
            <option value="Casa de vecindad"
                {{ isset($alumno) && $alumno->tipo_vivienda == 'Casa de vecindad' ? 'selected' : '' }}>Casa de
                vecindad</option>
            <option value="Casa de pensión"
                {{ isset($alumno) && $alumno->tipo_vivienda == 'Casa de pensión' ? 'selected' : '' }}>Casa de
                pensión</option>
            <option value="Cuarto alquilado"
                {{ isset($alumno) && $alumno->tipo_vivienda == 'Cuarto alquilado' ? 'selected' : '' }}>Cuarto
                alquilado</option>
            <option value="Otro" {{ isset($alumno) && $alumno->tipo_vivienda == 'Otro' ? 'selected' : '' }}>Otro
            </option>
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
            <option value="Propia" {{ isset($alumno) && $alumno->situacion_vivienda == 'Propia' ? 'selected' : '' }}>
                Propia</option>
            <option value="Alquilada"
                {{ isset($alumno) && $alumno->situacion_vivienda == 'Alquilada' ? 'selected' : '' }}>Alquilada
            </option>
            <option value="Alquiler venta"
                {{ isset($alumno) && $alumno->situacion_vivienda == 'Alquiler venta' ? 'selected' : '' }}>
                Alquiler venta</option>
            <option value="Prestada"
                {{ isset($alumno) && $alumno->situacion_vivienda == 'Prestada' ? 'selected' : '' }}>Prestada</option>
            <option value="Otra" {{ isset($alumno) && $alumno->situacion_vivienda == 'Otra' ? 'selected' : '' }}>Otra
            </option>
        </select>
        @error('situacion_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="dormitorios_vivienda">Número de dormitorios de vivienda:</label>
        <input type="text" class="form-control form-control-sm @error('dormitorios_vivienda') is-invalid @enderror"
            id="dormitorios_vivienda" name="dormitorios_vivienda"
            value="{{ isset($alumno) ? $alumno->dormitorios_vivienda : old('dormitorios_vivienda') }}" required>
        @error('dormitorios_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="banos_vivienda">Número de baños de vivienda:</label>
        <input type="text" class="form-control form-control-sm @error('banos_vivienda') is-invalid @enderror"
            id="banos_vivienda" name="banos_vivienda"
            value="{{ isset($alumno) ? $alumno->banos_vivienda : old('banos_vivienda') }}" required>
        @error('banos_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="material_vivienda">Tipo de material de la vivienda:</label>
        <select class="form-control form-control-sm @error('material_vivienda') is-invalid @enderror"
            id="material_vivienda" name="material_vivienda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Cemento/Ladrillo"
                {{ isset($alumno) && $alumno->material_vivienda == 'Cemento/Ladrillo' ? 'selected' : '' }}>
                Cemento/Ladrillo</option>
            <option value="Adobe" {{ isset($alumno) && $alumno->material_vivienda == 'Adobe' ? 'selected' : '' }}>
                Adobe</option>
            <option value="Quincha" {{ isset($alumno) && $alumno->material_vivienda == 'Quincha' ? 'selected' : '' }}>
                Quincha</option>
            <option value="Otro" {{ isset($alumno) && $alumno->material_vivienda == 'Otro' ? 'selected' : '' }}>Otro
            </option>
        </select>
        @error('material_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_agua">Horas disponibles de agua al día:</label>
        <input type="text" class="form-control form-control-sm @error('hrs_disponibles_agua') is-invalid @enderror"
            id="hrs_disponibles_agua" name="hrs_disponibles_agua"
            value="{{ isset($alumno) ? $alumno->hrs_disponibles_agua : old('hrs_disponibles_agua') }}" required>
        @error('hrs_disponibles_agua')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_desague">Horas disponibles de desagüe al día:</label>
        <input type="text"
            class="form-control form-control-sm @error('hrs_disponibles_desague') is-invalid @enderror"
            id="hrs_disponibles_desague" name="hrs_disponibles_desague"
            value="{{ isset($alumno) ? $alumno->hrs_disponibles_desague : old('hrs_disponibles_desague') }}" required>
        @error('hrs_disponibles_desague')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_disponibles_luz">Horas disponibles de luz al día:</label>
        <input type="text" class="form-control form-control-sm @error('hrs_disponibles_luz') is-invalid @enderror"
            id="hrs_disponibles_luz" name="hrs_disponibles_luz"
            value="{{ isset($alumno) ? $alumno->hrs_disponibles_luz : old('hrs_disponibles_luz') }}" required>
        @error('hrs_disponibles_luz')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>Bienes en la vivienda:</label><br>
        @foreach ($opcionesBienesVivienda as $bien)
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="bienes_vivienda[]" value="{{ $bien }}"
                    {{ in_array($bien, $alumno->bienes_vivienda) ? 'checked' : '' }}>
                {{ $bien }}
            </label>
        @endforeach
        @error('bienes_vivienda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>Otros servicios en la vivienda:</label><br>
        @foreach ($opcionesServicios as $servicio)
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="otros_servicios[]" value="{{ $servicio }}"
                    {{ in_array($servicio, $alumno->otros_servicios) ? 'checked' : '' }}>
                {{ $servicio }}
            </label>
        @endforeach
        @error('otros_servicios')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
