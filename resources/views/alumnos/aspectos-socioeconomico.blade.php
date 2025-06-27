<div class="row">
    {{-- <div class="col-lg-6 mb-2 mt-2">
        <label for="trabajas">¿Trabajas actualmente?:</label>
        <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror" name="trabajas"
            id="trabajas">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Sí" {{ isset($alumno) && $alumno->trabajas == 'Sí' ? 'selected' : '' }}>Sí</option>
            <option value="No" {{ isset($alumno) && $alumno->trabajas == 'No' ? 'selected' : '' }}>No</option>
            <option value="Todavía no" {{ isset($alumno) && $alumno->trabajas == 'Todavía no' ? 'selected' : '' }}>
                Todavía no</option>
        </select>

        @error('trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    <div class="col-lg-12">
        <h4 class="mb-2 mt-4">Socioeconómicos</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="trabajas">1. ¿Trabajas actualmente?:</label>
        <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror" name="trabajas"
            id="trabajas">
            <option value="" disabled
                {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == '' ? 'selected' : '' }}>Selecciona una
                opción</option>
            <option value="Sí"
                {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'Sí' ? 'selected' : '' }}>Sí</option>
            <option value="No"
                {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'No' ? 'selected' : '' }}>No</option>
            <option value="Todavía no"
                {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'Todavía no' ? 'selected' : '' }}>Todavía
                no</option>
        </select>
        @error('trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- SECTOR LABORAL -->
    <div class="col-lg-6 mb-2">
        <label for="sector_laboral">2. ¿En qué sector trabajas? <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('sector_laboral') is-invalid @enderror"
            id="sector_laboral_select" onchange="toggleOtroSector()" required>
            <option selected disabled>Elegir</option>
            <option value="Sector educativo" {{ old('sector_laboral') == 'Sector educativo' ? 'selected' : '' }}>Sector
                educativo</option>
            <option value="Otro"
                {{ old('sector_laboral') != 'Sector educativo' && old('sector_laboral') ? 'selected' : '' }}>Otro:
                (especificar)</option>
        </select>

        <input type="text" class="form-control form-control-sm mt-2 @error('sector_laboral') is-invalid @enderror"
            id="sector_laboral_otro" placeholder="Especifica el sector" style="display: none;"
            value="{{ old('sector_laboral') }}">

        <input type="hidden" name="sector_laboral" id="sector_laboral_hidden" value="{{ old('sector_laboral') }}">

        @error('sector_laboral')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="donde_trabajas">3. ¿Dónde trabajas?:</label>
        <input type="text" class="form-control form-control-sm @error('donde_trabajas') is-invalid @enderror"
            id="donde_trabajas" name="donde_trabajas" value="{{ old('donde_trabajas') }}">
        @error('donde_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="ingreso_mensual">4. ¿Cuánto es su ingreso mensual promedio?:</label>
        <select class="form-control form-control-sm @error('ingreso_mensual') is-invalid @enderror" id="ingreso_mensual"
            name="ingreso_mensual">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Menos de 500" {{ old('ingreso_mensual') == 'Menos de 500' ? 'selected' : '' }}>Menos de 500
            </option>
            <option value="De s/501 a s/930" {{ old('ingreso_mensual') == 'De s/501 a s/930' ? 'selected' : '' }}>De
                s/501 a s/930</option>
            <option value="De s/931 a s/1200" {{ old('ingreso_mensual') == 'De s/931 a s/1200' ? 'selected' : '' }}>De
                s/931 a s/1200</option>
            <option value="De s/1201 a s/2000" {{ old('ingreso_mensual') == 'De s/1201 a s/2000' ? 'selected' : '' }}>
                De
                s/1201 a s/2000</option>
            <option value="Más de s/2000" {{ old('ingreso_mensual') == 'Más de s/2000' ? 'selected' : '' }}>Más de
                s/2000</option>
        </select>
        @error('ingreso_mensual')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="egreso">5. ¿Cuál es tu egreso mensual promedio?:</label>
        <input type="text" class="form-control  form-control-sm @error('egreso') is-invalid @enderror" id="egreso"
            name="egreso" value="{{ old('egreso') }}" required>
        @error('egreso')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_laboradas_sem">6.Número de horas que labora a la semana:</label>
        <input type="number" class="form-control form-control-sm @error('hrs_laboradas_sem') is-invalid @enderror"
            id="hrs_laboradas_sem" name="hrs_laboradas_sem" value="{{ old('hrs_laboradas_sem') }}" required>
        @error('hrs_laboradas_sem')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>¿Recibe ayuda económica familiar?</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('ayuda_economica') is-invalid @enderror" type="radio"
                name="ayuda_economica" id="ayuda_si" value="1"
                {{ old('ayuda_economica') == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="ayuda_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('ayuda_economica') is-invalid @enderror" type="radio"
                name="ayuda_economica" id="ayuda_no" value="0"
                {{ old('ayuda_economica') == 0 ? 'checked' : 'checked' }}>
            <label class="form-check-label" for="ayuda_no">No</label>
        </div>
        @error('ayuda_economica')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="tiempo_ayuda">¿Cada cuánto tiempo recibe ayuda económica?:</label>
        <select class="form-control form-control-sm @error('tiempo_ayuda') is-invalid @enderror" id="tiempo_ayuda"
            name="tiempo_ayuda" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Quincenal" {{ old('tiempo_ayuda') == 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
            <option value="Mensual" {{ old('tiempo_ayuda') == 'Mensual' ? 'selected' : '' }}>Mensual</option>
            <option value="1 vez al año" {{ old('tiempo_ayuda') == '1 vez al año' ? 'selected' : '' }}>1 vez al año
            </option>
            <option value="Otro" {{ old('tiempo_ayuda') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('tiempo_ayuda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="tipo_apoyo_formacion">Tipo de apoyo que recibe en su formación inicial docente:</label>
        <select class="form-control form-control-sm @error('tipo_apoyo_formacion') is-invalid @enderror"
            id="tipo_apoyo_formacion" name="tipo_apoyo_formacion" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Beca parcial" {{ old('tipo_apoyo_formacion') == 'Beca parcial' ? 'selected' : '' }}>Beca
                parcial</option>
            <option value="Beca integral" {{ old('tipo_apoyo_formacion') == 'Beca integral' ? 'selected' : '' }}>Beca
                integral</option>
            <option value="No recibo ningún beneficio"
                {{ old('tipo_apoyo_formacion') == 'No recibo ningún beneficio' ? 'selected' : '' }}>No recibo ningún
                beneficio</option>
            <option value="Otro" {{ old('tipo_apoyo_formacion') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('tipo_apoyo_formacion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


</div>
