<div class="row">
    <div class="col-lg-12">
        <h4>Aspectos de la Salud</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="problemas_salud">¿Presenta algún problema de salud? Sí su respuesta es Sí, continue con la
            pregunta 2. Caso contrario, continúe con la pregunta 4.:</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('problemas_salud') is-invalid @enderror" type="radio"
                name="problemas_salud" id="problemas_salud_si" value="1"
                {{ old('problemas_salud') == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="problemas_salud_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('problemas_salud') is-invalid @enderror" type="radio"
                name="problemas_salud" id="problemas_salud_no" value="0"
                {{ old('problemas_salud') == 0 ? 'checked' : '' }}>
            <label class="form-check-label" for="problemas_salud_no">No</label>
        </div>
        @error('problemas_salud')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>¿En los últimos 12 meses ha tenido alguna consulta para el cuidado de su salud? Si su respuesta es No,
            pasar a la pregunta 3. Caso contrario, continúe con la pregunta 4.</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('ultima_consulta') is-invalid @enderror" type="radio"
                name="ultima_consulta" id="ultima_consulta_si" value="1"
                {{ old('ultima_consulta') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="ultima_consulta_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('ultima_consulta') is-invalid @enderror" type="radio"
                name="ultima_consulta" id="ultima_consulta_no" value="0"
                {{ old('ultima_consulta') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="ultima_consulta_no">No</label>
        </div>
        @error('ultima_consulta')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="ultima_consulta">¿Por qué motivo no ha tenido consultas médicas en los últimos 12 meses?</label>
        <select class="form-control form-control-sm @error('ultima_consulta') is-invalid @enderror"
            name="ultima_consulta">
            <option selected disabled>Seleccione una opción:</option>
            <option value="1" {{ old('ultima_consulta') == '1' ? 'selected' : '' }}>Falta de recursos económicos
            </option>
            <option value="0" {{ old('ultima_consulta') == '0' ? 'selected' : '' }}>Falta de tiempo</option>
        </select>
        @error('ultima_consulta')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    {{-- <div class="col-lg-6 mb-2">
        <label for="ultima_consulta">¿Por qué motivo no ha tenido consultas médicas en los últimos 12 meses?</label>
        <select class="form-control form-control-sm @error('ultima_consulta') is-invalid @enderror" name="ultima_consulta">
            <option value="" selected disabled>Seleccione una opción:</option>
            <option value="Falta de recursos económicos" {{ old('ultima_consulta') == 'Falta de recursos económicos' ? 'selected' : '' }}>Falta de recursos económicos</option>
            <option value="Falta de tiempo" {{ old('ultima_consulta') == 'Falta de tiempo' ? 'selected' : '' }}>Falta de tiempo</option>
            <option value="No he tenido necesidad" {{ old('ultima_consulta') == 'No he tenido necesidad' ? 'selected' : '' }}>No he tenido necesidad</option>
            <option value="Sí me he atendido" {{ old('ultima_consulta') == 'Sí me he atendido' ? 'selected' : '' }}>Sí me he atendido</option>
        </select>
        @error('ultima_consulta')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>  --}}

    <div class="col-lg-6 mb-2">
        <label for="tipo_seguro">¿Con qué tipo de seguro de salud cuenta?</label>
        <select class="form-control form-control-sm @error('tipo_seguro') is-invalid @enderror" name="tipo_seguro">
            <option selected disabled>Seleccionar</option>
            <option value="EsSalud" {{ old('tipo_seguro') == 'EsSalud' ? 'selected' : '' }}>EsSalud</option>
            <option value="Seguro Ejército" {{ old('tipo_seguro') == 'Seguro Ejército' ? 'selected' : '' }}>Seguro
                Ejército</option>
            <option value="Seguro Integral de Salud (SIS)"
                {{ old('tipo_seguro') == 'Seguro Integral de Salud (SIS)' ? 'selected' : '' }}>Seguro Integral de Salud
                (SIS)</option>
            <option value="Seguro de la Marina de Guerra del Perú"
                {{ old('tipo_seguro') == 'Seguro de la Marina de Guerra del Perú' ? 'selected' : '' }}>Seguro de la
                Marina de Guerra del Perú</option>
            <option value="Seguro PNP" {{ old('tipo_seguro') == 'Seguro PNP' ? 'selected' : '' }}>Seguro PNP</option>
            <option value="Seguro FAP" {{ old('tipo_seguro') == 'Seguro FAP' ? 'selected' : '' }}>Seguro FAP</option>
        </select>
        @error('tipo_seguro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="familiar_salud">¿Algún miembro que conforma su hogar presenta un problema de salud
            grave?</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('familiar_salud') is-invalid @enderror" type="radio"
                name="familiar_salud" id="familiar_salud_si" value="1"
                {{ old('familiar_salud') == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="familiar_salud_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('familiar_salud') is-invalid @enderror" type="radio"
                name="familiar_salud" id="familiar_salud_no" value="0"
                {{ old('familiar_salud') == 0 ? 'checked' : '' }}>
            <label class="form-check-label" for="familiar_salud_no">No</label>
        </div>
        @error('familiar_salud')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-12 mt-4 mb-2">
        <h4>Aspectos Culturales</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="frecuencia_lectura">Sin considerar textos académicos o manuales de estudio, ¿con qué frecuencia
            usted acostumbra a leer revistas/libros?</label>
        <select class="form-control form-control-sm @error('frecuencia_lectura') is-invalid @enderror"
            name="frecuencia_lectura">
            <option value="" selected disabled>Seleccionar</option>
            <option value="Todos los días" {{ old('frecuencia_lectura') == 'Todos los días' ? 'selected' : '' }}>Todos
                los días</option>
            <option value="1 vez por semana" {{ old('frecuencia_lectura') == '1 vez por semana' ? 'selected' : '' }}>1
                vez a la semana</option>
            <option value="Cada 15 días" {{ old('frecuencia_lectura') == 'Cada 15 días' ? 'selected' : '' }}>Cada 15
                días</option>
            <option value="1 vez al mes" {{ old('frecuencia_lectura') == '1 vez al mes' ? 'selected' : '' }}>1 vez al
                mes</option>
            <option value="Más de 1 mes" {{ old('frecuencia_lectura') == 'Más de 1 mes' ? 'selected' : '' }}>Más de 1
                mes</option>
        </select>
        @error('frecuencia_lectura')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="acceso_lectura">¿De qué manera accede a las revistas/libros que lee habitualmente?</label>
        <select class="form-control form-control-sm @error('acceso_lectura') is-invalid @enderror"
            name="acceso_lectura">
            <option value="" selected disabled>Seleccionar</option>
            <option value="Las compra" {{ old('acceso_lectura') == 'Las compra' ? 'selected' : '' }}>Las compra
            </option>
            <option value="Trabajo u oficina" {{ old('acceso_lectura') == 'Trabajo u oficina' ? 'selected' : '' }}>
                Trabajo u oficina</option>
            <option value="Las lee por internet"
                {{ old('acceso_lectura') == 'Las lee por internet' ? 'selected' : '' }}>Las lee por internet</option>
        </select>
        @error('acceso_lectura')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="visitas_museos">¿Ha asistido a museos?</label>
        <select class="form-control form-control-sm @error('visitas_museos') is-invalid @enderror"
            name="visitas_museos">
            <option value="" selected disabled>Seleccionar</option>
            <option value="El último mes" {{ old('visitas_museos') == 'El último mes' ? 'selected' : '' }}>El último
                mes</option>
            <option value="En los últimos 3 meses"
                {{ old('visitas_museos') == 'En los últimos 3 meses' ? 'selected' : '' }}>En los últimos 3 meses
            </option>
            <option value="En los últimos 6 meses"
                {{ old('visitas_museos') == 'En los últimos 6 meses' ? 'selected' : '' }}>En los últimos 6 meses
            </option>
            <option value="En los últimos 12 meses"
                {{ old('visitas_museos') == 'En los últimos 12 meses' ? 'selected' : '' }}>En los últimos 12 meses
            </option>
            <option value="Más de 12 meses" {{ old('visitas_museos') == 'Más de 12 meses' ? 'selected' : '' }}>Más de
                12 meses</option>
        </select>
        @error('visitas_museos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-12 mt-4 mb-2">
        <h4>Información Adicional</h4>
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="actividades_internet">¿Qué actividades son las que más realiza usted a través de internet?</label>
        <select class="form-control form-control-sm @error('actividades_internet') is-invalid @enderror"
            name="actividades_internet">
            <option value="" selected disabled>Seleccionar</option>
            <option value="Enviar/Recibir correos"
                {{ old('actividades_internet') == 'Enviar/Recibir correos' ? 'selected' : '' }}>Enviar/Recibir correos
            </option>
            <option value="Buscar información para aprender algo nuevo"
                {{ old('actividades_internet') == 'Buscar información para aprender algo nuevo' ? 'selected' : '' }}>
                Buscar información para aprender algo nuevo</option>
            <option value="Entretenimiento/Música"
                {{ old('actividades_internet') == 'Entretenimiento/Música' ? 'selected' : '' }}>Entretenimiento/Música
            </option>
            <option value="Informarse sobre actualidad de la prensa/medios"
                {{ old('actividades_internet') == 'Informarse sobre actualidad de la prensa/medios' ? 'selected' : '' }}>
                Informarse sobre actualidad de la prensa/medios</option>
            <option value="Otros" {{ old('actividades_internet') == 'Otros' ? 'selected' : '' }}>Otros</option>
        </select>
        @error('actividades_internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label>¿Ha desarrollado alguna de las siguientes habilidades?</label><br>

        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="habilidades[]"
                value="Música (Instrumentos, canto)"
                {{ in_array('Música (Instrumentos, canto)', old('habilidades', [])) ? 'checked' : '' }}>
            Música (Instrumentos, canto)
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="habilidades[]"
                value="Artes pláticas (Pintura, Escultura, etc)"
                {{ in_array('Artes pláticas (Pintura, Escultura, etc)', old('habilidades', [])) ? 'checked' : '' }}>
            Artes pláticas (Pintura, Escultura, etc)
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="habilidades[]"
                value="Danzas (Danzas folklóricas, Ballet, Etc)"
                {{ in_array('Danzas (Danzas folklóricas, Ballet, Etc)', old('habilidades', [])) ? 'checked' : '' }}>
            Danzas (Danzas folklóricas, Ballet, Etc)
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="habilidades[]"
                value="Literatura (Poesía, Cuentos, etc)"
                {{ in_array('Literatura (Poesía, Cuentos, etc)', old('habilidades', [])) ? 'checked' : '' }}>
            Literatura (Poesía, Cuentos, etc)
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="habilidades[]" value="Otros"
                {{ in_array('Otros', old('habilidades', [])) ? 'checked' : '' }}>
            Otros
        </label>
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="tiempo_libre">¿Usted considera que dispone de tiempo libre para realizar diversas actividades que
            le gusten?</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tiempo_libre" id="tiempo_libre_si" value="1"
                {{ old('tiempo_libre') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="tiempo_libre_si">
                Sí
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tiempo_libre" id="tiempo_libre_no" value="0"
                {{ old('tiempo_libre') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="tiempo_libre_no">
                No
            </label>
        </div>
        @error('tiempo_libre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


</div>
