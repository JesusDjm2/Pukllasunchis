<div class="row">
    <div class="col-lg-6 mb-2 mt-2">
        <label for="trabajas">¿Trabajas actualmente?:</label>
        <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror" name="trabajas"
            id="trabajas">
            <option value="" disabled
                {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == '' ? 'selected' : '' }}>
                Selecciona una opción
            </option>
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

    {{-- <div class="col-lg-6 mb-2 mt-2">
        <label for="sector_laboral">¿En qué sector trabajas?:</label>
        <select class="form-control form-control-sm @error('sector_laboral') is-invalid @enderror"
            id="sector_laboral_select" onchange="toggleOtroSector()">
            <option selected disabled>Elegir</option>
            <option value="Sector educativo"
                {{ old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') == 'Sector educativo' ? 'selected' : '' }}>
                Sector educativo
            </option>
            <option value="Otro"
                {{ old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') != 'Sector educativo' && isset($alumno) && $alumno->sector_laboral ? 'selected' : '' }}>
                Otro: (especificar)
            </option>
        </select>
    
        @php
            $mostrarInput =
                old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') != 'Sector educativo' &&
                old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') != '';
        @endphp
    
        <input type="text" class="form-control form-control-sm mt-2 @error('sector_laboral') is-invalid @enderror"
            id="sector_laboral_otro" 
            placeholder="Especifica el sector" 
            style="display: {{ $mostrarInput ? 'block' : 'none' }};"
            value="{{ old('sector_laboral', isset($alumno) && $alumno->sector_laboral != 'Sector educativo' ? $alumno->sector_laboral : '') }}">
    
        <!-- Campo oculto que se enviará en el formulario -->
        <input type="hidden" name="sector_laboral" id="sector_laboral_hidden"
            value="{{ old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') }}">
    
        @error('sector_laboral')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>    
    <script>
        function toggleOtroSector() {
            var select = document.getElementById("sector_laboral_select");
            var inputOtro = document.getElementById("sector_laboral_otro");
            var hiddenInput = document.getElementById("sector_laboral_hidden");
    
            if (select.value === "Otro") {
                inputOtro.style.display = "block";
                inputOtro.required = true;
                hiddenInput.value = inputOtro.value; // Al seleccionar "Otro", se usa el valor del input de texto
            } else {
                inputOtro.style.display = "none";
                inputOtro.required = false;
                inputOtro.value = "";
                hiddenInput.value = select.value; // Si elige otra opción, se guarda directamente el valor del select
            }
        }
    
        // Asegurar que el campo oculto se actualice cuando el usuario escriba en el input de texto
        document.getElementById("sector_laboral_otro").addEventListener("input", function() {
            document.getElementById("sector_laboral_hidden").value = this.value;
        });
    
        // Mantener el estado correcto cuando la página carga
        document.addEventListener("DOMContentLoaded", function() {
            toggleOtroSector();
        });
    </script> --}}







    <div class="col-lg-6 mb-2 mt-2">
        <label for="donde_trabajas">¿Dónde trabajas?:</label>
        <input type="text" class="form-control form-control-sm @error('donde_trabajas') is-invalid @enderror"
            id="donde_trabajas" name="donde_trabajas"
            value="{{ isset($alumno) ? $alumno->donde_trabajas : old('donde_trabajas') }}">
        @error('donde_trabajas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="ingreso_mensual">¿Cuánto es su ingreso mensual promedio?:</label>
        <select class="form-control form-control-sm @error('ingreso_mensual') is-invalid @enderror" id="ingreso_mensual"
            name="ingreso_mensual">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Menos de 500"
                {{ isset($alumno) && $alumno->ingreso_mensual == 'Menos de 500' ? 'selected' : '' }}>Menos de 500
            </option>
            <option value="De s/501 a s/930"
                {{ isset($alumno) && $alumno->ingreso_mensual == 'De s/501 a s/930' ? 'selected' : '' }}>De
                s/501 a s/930</option>
            <option value="De s/931 a s/1200"
                {{ isset($alumno) && $alumno->ingreso_mensual == 'De s/931 a s/1200' ? 'selected' : '' }}>De
                s/931 a s/1200</option>
            <option value="De s/1201 a s/2000"
                {{ isset($alumno) && $alumno->ingreso_mensual == 'De s/1201 a s/2000' ? 'selected' : '' }}>De
                s/1201 a s/2000</option>
            <option value="Más de s/2000"
                {{ isset($alumno) && $alumno->ingreso_mensual == 'Más de s/2000' ? 'selected' : '' }}>Más de
                s/2000</option>
        </select>
        @error('ingreso_mensual')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

   {{--  <div class="col-lg-6 mb-2 mt-2">
        <label for="egreso">¿Cuál es tu egreso mensual promedio?:</label>
        <input type="text" class="form-control form-control-sm @error('egreso') is-invalid @enderror" id="egreso"
            name="egreso" value="{{ isset($alumno) ? $alumno->egreso : old('egreso') }}" required>
        @error('egreso')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    {{-- <div class="col-lg-6 mb-2 mt-2">
        <label for="egreso"><strong>¿Cuál es tu egreso mensual promedio?</strong> <span
                class="text-danger">*</span></label>
        <select class="form-control form-control-sm @error('egreso') is-invalid @enderror" id="egreso" name="egreso"
            required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Menos de 500"
                {{ old('egreso', isset($alumno) ? $alumno->egreso : '') == 'Menos de 500' ? 'selected' : '' }}>
                Menos de 500
            </option>
            <option value="De 501 a 900"
                {{ old('egreso', isset($alumno) ? $alumno->egreso : '') == 'De 501 a 900' ? 'selected' : '' }}>
                De 501 a 900
            </option>
            <option value="De 901 a 1200"
                {{ old('egreso', isset($alumno) ? $alumno->egreso : '') == 'De 901 a 1200' ? 'selected' : '' }}>
                De 901 a 1200
            </option>
            <option value="De 1201 a 1500"
                {{ old('egreso', isset($alumno) ? $alumno->egreso : '') == 'De 1201 a 1500' ? 'selected' : '' }}>
                De 1201 a 1500
            </option>
            <option value="Más de 1500"
                {{ old('egreso', isset($alumno) ? $alumno->egreso : '') == 'Más de 1500' ? 'selected' : '' }}>
                Más de 1500
            </option>
        </select>
        @error('egreso')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}


    <div class="col-lg-6 mb-2 mt-2">
        <label for="hrs_laboradas_sem">¿Número de horas que labora a la semana:</label>
        <input type="number" class="form-control form-control-sm @error('hrs_laboradas_sem') is-invalid @enderror"
            id="hrs_laboradas_sem" name="hrs_laboradas_sem"
            value="{{ isset($alumno) ? $alumno->hrs_laboradas_sem : old('hrs_laboradas_sem') }}" required>
        @error('hrs_laboradas_sem')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>¿Recibe ayuda económica?</label><br>
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
            <option value="Quincenal"
                {{ (isset($alumno) && $alumno->tiempo_ayuda == 'Quincenal' ? 'selected' : old('tiempo_ayuda') == 'Quincenal') ? 'selected' : '' }}>
                Quincenal</option>
            <option value="Mensual"
                {{ (isset($alumno) && $alumno->tiempo_ayuda == 'Mensual' ? 'selected' : old('tiempo_ayuda') == 'Mensual') ? 'selected' : '' }}>
                Mensual</option>
            <option value="1 vez al año"
                {{ (isset($alumno) && $alumno->tiempo_ayuda == '1 vez al año' ? 'selected' : old('tiempo_ayuda') == '1 vez al año') ? 'selected' : '' }}>
                1 vez al año</option>
            <option value="Otro"
                {{ (isset($alumno) && $alumno->tiempo_ayuda == 'Otro' ? 'selected' : old('tiempo_ayuda') == 'Otro') ? 'selected' : '' }}>
                Otro</option>
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
            <option value="Beca parcial"
                {{ (isset($alumno) && $alumno->tipo_apoyo_formacion == 'Beca parcial' ? 'selected' : old('tipo_apoyo_formacion') == 'Beca parcial') ? 'selected' : '' }}>
                Beca parcial</option>
            <option value="Beca integral"
                {{ (isset($alumno) && $alumno->tipo_apoyo_formacion == 'Beca integral' ? 'selected' : old('tipo_apoyo_formacion') == 'Beca integral') ? 'selected' : '' }}>
                Beca integral</option>
            <option value="No recibo ningún beneficio"
                {{ (isset($alumno) && $alumno->tipo_apoyo_formacion == 'No recibo ningún beneficio' ? 'selected' : old('tipo_apoyo_formacion') == 'No recibo ningún beneficio') ? 'selected' : '' }}>
                No recibo ningún beneficio</option>
            <option value="Otro"
                {{ (isset($alumno) && $alumno->tipo_apoyo_formacion == 'Otro' ? 'selected' : old('tipo_apoyo_formacion') == 'Otro') ? 'selected' : '' }}>
                Otro</option>
        </select>
        @error('tipo_apoyo_formacion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
