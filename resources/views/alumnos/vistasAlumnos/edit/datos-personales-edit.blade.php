<div class="row">
    <div class="col-lg-12">
        <div class="card p-4 bg-white">

            <div class="row">
                <div class="col-lg-12">
                    <h5 class="mt-2 mb-1 text-info font-weight-bold">Por favor completar estos datos nuevos:</h5>
                </div>
                <div class="col-lg-6 mb-2">
                    <label for="sector_laboral"><strong>¿En qué sector trabajas?</strong> <span
                            class="text-danger">*</span></label>
                    <select class="form-control form-control-sm @error('sector_laboral') is-invalid @enderror"
                        id="sector_laboral_select" onchange="toggleOtroSector()" required>
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
                            old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') !=
                                'Sector educativo' &&
                            old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') != '';
                    @endphp

                    <input type="text"
                        class="form-control form-control-sm mt-2 @error('sector_laboral') is-invalid @enderror"
                        id="sector_laboral_otro" placeholder="Especifica el sector"
                        style="display: {{ $mostrarInput ? 'block' : 'none' }};"
                        value="{{ old('sector_laboral', isset($alumno) && $alumno->sector_laboral != 'Sector educativo' ? $alumno->sector_laboral : '') }}">

                    <input type="hidden" name="sector_laboral" id="sector_laboral_hidden"
                        value="{{ old('sector_laboral', isset($alumno) ? $alumno->sector_laboral : '') }}">

                    @error('sector_laboral')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="lugar_nacimiento"><strong>Lugar de Nacimiento:</strong> <span
                            class="text-danger">*</span>
                        <small>(Detallar: Región, Provincia, Distrito y dirección)</small>
                    </label>
                    <input type="text"
                        class="form-control form-control-sm mb-2 @error('lugar_nacimiento') is-invalid @enderror"
                        name="lugar_nacimiento" value="{{ old('lugar_nacimiento', $alumno->lugar_nacimiento) }}"
                        placeholder="Ejemplo: Cusco, Cusco, Santiago, Urb, BloqueA-2" required>
                    @error('lugar_nacimiento')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label for="permanencia_vivienda"><strong>Permanencia en la vivienda:</strong> <span
                            class="text-danger">*</span>
                        <small>(Selecciona la opción que mejor describa tu situación)</small>
                    </label>
                    <select
                        class="form-control form-control-sm mb-2 @error('permanencia_vivienda') is-invalid @enderror"
                        name="permanencia_vivienda" required>
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="Vivo permanentemente en Cusco ciudad"
                            {{ old('permanencia_vivienda', $alumno->permanencia_vivienda) == 'Vivo permanentemente en Cusco ciudad' ? 'selected' : '' }}>
                            Vivo permanentemente en Cusco ciudad</option>
                        <option value="Vivo en comunidad y me traslado al Cusco todos los días para estudiar"
                            {{ old('permanencia_vivienda', $alumno->permanencia_vivienda) == 'Vivo en comunidad y me traslado al Cusco todos los días para estudiar' ? 'selected' : '' }}>
                            Vivo en comunidad y me traslado al Cusco todos los días para estudiar</option>
                        <option value="Estoy en Cusco de lunes a viernes y los fines de semana en mi comunidad"
                            {{ old('permanencia_vivienda', $alumno->permanencia_vivienda) == 'Estoy en Cusco de lunes a viernes y los fines de semana en mi comunidad' ? 'selected' : '' }}>
                            Estoy en Cusco de lunes a viernes y los fines de semana en mi comunidad</option>
                    </select>
                    @error('permanencia_vivienda')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
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
                            hiddenInput.value = inputOtro.value;
                        } else {
                            inputOtro.style.display = "none";
                            inputOtro.required = false;
                            inputOtro.value = "";
                            hiddenInput.value = select.value;
                        }
                    }

                    document.getElementById("sector_laboral_otro").addEventListener("input", function() {
                        document.getElementById("sector_laboral_hidden").value = this.value;
                    });

                    document.addEventListener("DOMContentLoaded", function() {
                        toggleOtroSector();
                    });
                </script>
                <div class="col-lg-6 mb-2 mt-2">
                    <label for="egreso"><strong>¿Cuál es tu egreso mensual promedio?</strong> <span
                            class="text-danger">*</span></label>
                    <select class="form-control form-control-sm @error('egreso') is-invalid @enderror" id="egreso"
                        name="egreso" required>
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
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                id="email" name="email" value="{{ old('email', $alumno->email) }}" readonly required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="dni">DNI:</label>
        <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror" id="dni"
            name="dni" value="{{ old('dni', $alumno->dni) }}" readonly required>
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="nombres">Nombres:</label>
        <input type="text" class="form-control form-control-sm @error('nombres') is-invalid @enderror" id="nombres"
            name="nombres" value="{{ old('nombres', $alumno->nombres) }}" readonly required>
        @error('nombres')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="apellidos">Apellidos:</label>
        <input type="text" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
            id="apellidos" name="apellidos" value="{{ old('apellidos', $alumno->apellidos) }}" readonly required>
        @error('apellidos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="programa_id">Programa de estudios:</label>
        <select id="programa_id" name="programa_id" class="form-control form-control-sm" required readonly>
            <option value="{{ $alumno->programa->id }}">{{ $alumno->programa->nombre }}</option>
        </select>
    </div>
    <div class="col-lg-6 mb-2">
        <label for="ciclo_id">Ciclo:</label>
        <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm" required readonly>
            <option value="{{ $alumno->ciclo->id }}">{{ $alumno->ciclo->nombre }}</option>
        </select>
    </div>


    <div class="col-lg-6 mb-2">
        <label for="numero">Número:</label>
        <input type="text" class="form-control form-control-sm @error('numero') is-invalid @enderror"
            id="numero" name="numero" value="{{ old('numero', $alumno->numero) }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="numero_referencia">Número de Referencia:</label>
        <input type="text" class="form-control form-control-sm @error('numero_referencia') is-invalid @enderror"
            id="numero_referencia" name="numero_referencia"
            value="{{ old('numero_referencia', $alumno->numero_referencia) }}" required>
        @error('numero_referencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="procedencia_familiar">¿Tu familia o tú proceden de alguna comunidad?:</label>
        <select class="form-control form-control-sm @error('procedencia_familiar') is-invalid @enderror"
            id="procedencia_familiar" name="procedencia_familiar" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="vivo_en_comunidad"
                {{ old('procedencia_familiar', $alumno->procedencia_familiar) == 'vivo_en_comunidad' ? 'selected' : '' }}>
                Sí, yo aún vivo en la comunidad</option>
            <option value="padres_viven_en_comunidad"
                {{ old('procedencia_familiar', $alumno->procedencia_familiar) == 'padres_viven_en_comunidad' ? 'selected' : '' }}>
                Sí, mis padres aún viven en la comunidad</option>
            <option value="abuelos_viven_en_comunidad"
                {{ old('procedencia_familiar', $alumno->procedencia_familiar) == 'abuelos_viven_en_comunidad' ? 'selected' : '' }}>
                Sí, mis abuelos aún viven en la comunidad</option>
            <option value="no_vivimos_en_comunidad"
                {{ old('procedencia_familiar', $alumno->procedencia_familiar) == 'no_vivimos_en_comunidad' ? 'selected' : '' }}>
                Sí, pero ya no vivimos en la comunidad</option>
            <option value="familia_de_zona_urbana"
                {{ old('procedencia_familiar', $alumno->procedencia_familiar) == 'familia_de_zona_urbana' ? 'selected' : '' }}>
                No, mi familia procede de una zona urbana</option>
        </select>
        @error('procedencia_familiar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label for="direccion">Domicilio actual: <small> (Detallar: Región, Provincia, Distrito) y
                dirección,</small></label>
        <input type="text" class="form-control form-control-sm mb-2 @error('direccion') is-invalid @enderror"
            name="direccion" value="{{ old('direccion', $alumno->direccion) }}" required>
        @error('direccion')
            <span class="invalid-feedback" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="te_consideras">Por tus antepasados y de acuerdo a tus costumbres, te consideras:</label>
        <select class="form-control form-control-sm @error('te_consideras') is-invalid @enderror" id="te_consideras"
            name="te_consideras" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="quechua" {{ old('te_consideras', $alumno->te_consideras) == 'quechua' ? 'selected' : '' }}>
                Quechua</option>
            <option value="aymara" {{ old('te_consideras', $alumno->te_consideras) == 'aymara' ? 'selected' : '' }}>
                Aymara</option>
            <option value="nativo_amazonia"
                {{ old('te_consideras', $alumno->te_consideras) == 'nativo_amazonia' ? 'selected' : '' }}>Nativo o
                indígena de la Amazonía</option>
            <option value="negro_moreno_zambo"
                {{ old('te_consideras', $alumno->te_consideras) == 'negro_moreno_zambo' ? 'selected' : '' }}>
                Negro/Moreno/Zambo mulato/Pueblo afroperuano o afrodescendiente</option>
            <option value="blanco" {{ old('te_consideras', $alumno->te_consideras) == 'blanco' ? 'selected' : '' }}>
                Blanco</option>
            <option value="mestizo" {{ old('te_consideras', $alumno->te_consideras) == 'mestizo' ? 'selected' : '' }}>
                Mestizo</option>
            <option value="otro_pueblo_indigena"
                {{ old('te_consideras', $alumno->te_consideras) == 'otro_pueblo_indigena' ? 'selected' : '' }}>
                Perteneciente o parte de otro pueblo indígena u originario</option>
            <option value="no_sabe_no_responde"
                {{ old('te_consideras', $alumno->te_consideras) == 'no_sabe_no_responde' ? 'selected' : '' }}>No
                sabe/No responde</option>
            <option value="otro" {{ old('te_consideras', $alumno->te_consideras) == 'otro' ? 'selected' : '' }}>Otro
            </option>
        </select>
        @error('te_consideras')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="lengua_1">¿Qué lengua hablas?</label>
        {{-- <select class="form-control form-control-sm @error('lengua_1') is-invalid @enderror" id="lengua_1"
            name="lengua_1" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Quechua" {{ $alumno->lengua_1 == 'Quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="Castellano" {{ $alumno->lengua_1 == 'Castellano' ? 'selected' : '' }}>Castellano</option>
            <option value="Otro" {{ $alumno->lengua_1 == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select> --}}
        <select class="form-control form-control-sm @error('lengua_1') is-invalid @enderror" id="lengua_1"
            name="lengua_1" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Castellano" {{ $alumno->lengua_1 == 'Castellano' ? 'selected' : '' }}>Castellano</option>
            <option value="Castellano y Quechua" {{ $alumno->lengua_1 == 'Castellano y Quechua' ? 'selected' : '' }}>
                Castellano y Quechua</option>
        </select>

        @error('lengua_1')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="lengua_2">¿Cuál fue la segunda lengua aprendiste a hablar?:</label>
        <select class="form-control form-control-sm @error('lengua_2') is-invalid @enderror" id="lengua_2"
            name="lengua_2" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Quechua" {{ $alumno->lengua_2 == 'Quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="Castellano" {{ $alumno->lengua_2 == 'Castellano' ? 'selected' : '' }}>Castellano</option>
            <option value="Otro" {{ $alumno->lengua_2 == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('lengua_2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="estado_civil">Estado Civil:</label>
        <select class="form-control form-control-sm mb-2 @error('estado_civil') is-invalid @enderror"
            name="estado_civil" required>
            <option disabled>Seleccionar estado civil</option>
            <option value="Casado"
                {{ old('estado_civil', $alumno->estado_civil ?? '') == 'Casado' ? 'selected' : '' }}>Casado</option>
            <option value="Soltero"
                {{ old('estado_civil', $alumno->estado_civil ?? '') == 'Soltero' ? 'selected' : '' }}>Soltero</option>
            <option value="Viudo"
                {{ old('estado_civil', $alumno->estado_civil ?? '') == 'Viudo' ? 'selected' : '' }}>Viudo</option>
            <option value="Divorciado"
                {{ old('estado_civil', $alumno->estado_civil ?? '') == 'Divorciado' ? 'selected' : '' }}>Divorciado
            </option>
        </select>
        @error('estado_civil')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label class="d-block">¿Eres padre/madre soltero?</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('p_m_soltero') is-invalid @enderror" type="radio"
                name="p_m_soltero" id="soltero_si" value="1"
                {{ old('p_m_soltero', $alumno->p_m_soltero ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="soltero_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('p_m_soltero') is-invalid @enderror" type="radio"
                name="p_m_soltero" id="soltero_no" value="0"
                {{ old('p_m_soltero', $alumno->p_m_soltero ?? '') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="soltero_no">No</label>
        </div>
        @error('p_m_soltero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="num_hijos">Número de Hijos:</label>
        <input type="number" class="form-control form-control-sm @error('num_hijos') is-invalid @enderror"
            id="num_hijos" name="num_hijos" value="{{ old('num_hijos', $alumno->num_hijos ?? '') }}" required>
        @error('num_hijos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="sector_socioeconomico">¿A qué sector socioeconómico perteneces?:</label>
        <select class="form-control form-control-sm @error('sector_socioeconomico') is-invalid @enderror"
            id="sector_socioeconomico" name="sector_socioeconomico" required>
            <option disabled>Selecciona una opción</option>
            <option value="popular"
                {{ old('sector_socioeconomico', $alumno->sector_socioeconomico ?? '') == 'popular' ? 'selected' : '' }}>
                Popular</option>
            <option value="medio"
                {{ old('sector_socioeconomico', $alumno->sector_socioeconomico ?? '') == 'medio' ? 'selected' : '' }}>
                Medio</option>
            <option value="medio_alto"
                {{ old('sector_socioeconomico', $alumno->sector_socioeconomico ?? '') == 'medio_alto' ? 'selected' : '' }}>
                Medio Alto</option>
            <option value="alto"
                {{ old('sector_socioeconomico', $alumno->sector_socioeconomico ?? '') == 'alto' ? 'selected' : '' }}>
                Alto</option>
        </select>
        @error('sector_socioeconomico')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="num_comprobante">Número de boleta electrónica emitida por la EESP:
            <small>
                <span class="text-primary" type="button" data-toggle="modal" data-target="#exampleModal">
                    Ejemplo
                </span>
            </small>
        </label>
        <div class="input-group">
            <input type="text" class="form-control form-control-sm @error('num_comprobante') is-invalid @enderror"
                id="num_comprobante" name="num_comprobante"
                value="{{ stripos($alumno->num_comprobante, 'beca') !== false ? 'Beca' : (old('num_comprobante') ?: '') }}"
                @if (stripos($alumno->num_comprobante, 'beca') !== false) readonly @endif required>
        </div>
        @error('num_comprobante')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <img src="{{ asset('img/novedades/Foto-voucher-2024-II-2.webp') }}" alt="">
            </div>
        </div>
    </div>

</div>
