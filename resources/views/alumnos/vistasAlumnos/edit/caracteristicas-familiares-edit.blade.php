<div class="row">
    <div class="col-lg-12">
        <h4 class="mb-2 mt-4">Aspectos Familiares</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="convivientes">¿Con quién(es) vive(s)?:</label>
        <select class="form-control @error('convivientes') is-invalid @enderror" id="convivientes" name="convivientes"
            required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Solo" {{ old('convivientes', $alumno->convivientes ?? '') == 'Solo' ? 'selected' : '' }}>
                Solo</option>
            <option value="Padre" {{ old('convivientes', $alumno->convivientes ?? '') == 'Padre' ? 'selected' : '' }}>
                Padre</option>
            <option value="Madre" {{ old('convivientes', $alumno->convivientes ?? '') == 'Madre' ? 'selected' : '' }}>
                Madre</option>
            <option value="Padre y Madre"
                {{ old('convivientes', $alumno->convivientes ?? '') == 'Padre y Madre' ? 'selected' : '' }}>Padre y
                Madre</option>
            <option value="Esposo/Cónyuge"
                {{ old('convivientes', $alumno->convivientes ?? '') == 'Esposo/Cónyuge' ? 'selected' : '' }}>
                Esposo/Cónyuge</option>
            <option value="Otro" {{ old('convivientes', $alumno->convivientes ?? '') == 'Otro' ? 'selected' : '' }}>
                Otro</option>
        </select>
        @error('convivientes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="quien_mantiene">Persona que mantiene el hogar:</label>
        <select class="form-control form-control-sm @error('quien_mantiene') is-invalid @enderror" id="quien_mantiene"
            name="quien_mantiene" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Yo" {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Yo' ? 'selected' : '' }}>
                Yo</option>
            <option value="Padre"
                {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Padre' ? 'selected' : '' }}>Padre</option>
            <option value="Madre"
                {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Madre' ? 'selected' : '' }}>Madre</option>
            <option value="Padre y Madre"
                {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Padre y Madre' ? 'selected' : '' }}>Padre y
                Madre</option>
            <option value="Esposo(a)/Cónyuge"
                {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Esposo(a)/Cónyuge' ? 'selected' : '' }}>
                Esposo(a)/Cónyuge</option>
            <option value="Otro"
                {{ old('quien_mantiene', $alumno->quien_mantiene ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('quien_mantiene')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_child">Cantidad de dependientes en el hogar (niños):</label>
        <input type="text"
            class="form-control form-control-sm @error('cant_dependientes_child') is-invalid @enderror"
            id="cant_dependientes_child" name="cant_dependientes_child"
            value="{{ old('cant_dependientes_child', $alumno->cant_dependientes_child ?? '') }}" required>
        @error('cant_dependientes_child')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_old">Cantidad de dependientes en el hogar (tercera edad):</label>
        <input type="text" class="form-control form-control-sm @error('cant_dependientes_old') is-invalid @enderror"
            id="cant_dependientes_old" name="cant_dependientes_old"
            value="{{ old('cant_dependientes_old', $alumno->cant_dependientes_old ?? '') }}" required>
        @error('cant_dependientes_old')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_otros">Cantidad de dependientes en el hogar (otros):</label>
        <input type="text"
            class="form-control form-control-sm @error('cant_dependientes_otros') is-invalid @enderror"
            id="cant_dependientes_otros" name="cant_dependientes_otros"
            value="{{ old('cant_dependientes_otros', $alumno->cant_dependientes_otros ?? '') }}" required>
        @error('cant_dependientes_otros')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-12">
        <h4 class="mt-4 mb-2">Aspectos Educativos</h4>
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="estudio_beca">Estudio Beca:</label>
        <select class="form-control form-control-sm @error('estudio_beca') is-invalid @enderror" id="estudio_beca"
            name="estudio_beca" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Si, Beca Parcial"
                {{ old('estudio_beca', $alumno->estudio_beca ?? '') == 'Si, Beca Parcial' ? 'selected' : '' }}>Si, Beca
                Parcial</option>
            <option value="Si, Beca Total"
                {{ old('estudio_beca', $alumno->estudio_beca ?? '') == 'Si, Beca Total' ? 'selected' : '' }}>Si, Beca
                Total</option>
            <option value="No" {{ old('estudio_beca', $alumno->estudio_beca ?? '') == 'No' ? 'selected' : '' }}>No
            </option>
        </select>
        @error('estudio_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-lg-6 mb-2 mt-2">
        <label for="origen_beca">Origen Beca:</label>
        <input type="text" class="form-control form-control-sm @error('origen_beca') is-invalid @enderror"
            id="origen_beca" name="origen_beca" placeholder="Ingrese el origen de la beca (opcional)"
            value="{{ old('origen_beca', $alumno->origen_beca ?? '') }}">
        @error('origen_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_eesp">Número de veces que postuló a EESP:</label>
        <input type="number" class="form-control form-control-sm @error('postulaciones_eesp') is-invalid @enderror"
            id="postulaciones_eesp" name="postulaciones_eesp" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_eesp', $alumno->postulaciones_eesp ?? '') }}">
        @error('postulaciones_eesp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_inst_uni">Veces que postuló a Instituciones Universitarias:</label>
        <input type="number" class="form-control form-control-sm @error('postulaciones_inst_uni') is-invalid @enderror"
            id="postulaciones_inst_uni" name="postulaciones_inst_uni" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_inst_uni', $alumno->postulaciones_inst_uni ?? '') }}">
        @error('postulaciones_inst_uni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_otros">Veces que postuló a Otros:</label>
        <input type="number" class="form-control form-control-sm @error('postulaciones_otros') is-invalid @enderror"
            id="postulaciones_otros" name="postulaciones_otros" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_otros', $alumno->postulaciones_otros ?? '') }}">
        @error('postulaciones_otros')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="tipo_preparacion">Tipo de Preparación para postular a al EESPP:</label>
        <select class="form-control form-control-sm @error('tipo_preparacion') is-invalid @enderror"
            id="tipo_preparacion" name="tipo_preparacion" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Centro Pre"
                {{ old('tipo_preparacion', $alumno->tipo_preparacion ?? '') == 'Centro Pre' ? 'selected' : '' }}>Centro
                Pre</option>
            <option value="Centro Pre de otro IESP/EESP"
                {{ old('tipo_preparacion', $alumno->tipo_preparacion ?? '') == 'Centro Pre de otro IESP/EESP' ? 'selected' : '' }}>
                Centro Pre de otro IESP/EESP</option>
            <option value="Académia"
                {{ old('tipo_preparacion', $alumno->tipo_preparacion ?? '') == 'Académia' ? 'selected' : '' }}>Académia
            </option>
            <option value="Otros"
                {{ old('tipo_preparacion', $alumno->tipo_preparacion ?? '') == 'Otros' ? 'selected' : '' }}>Otros
            </option>
            <option value="Ninguno"
                {{ old('tipo_preparacion', $alumno->tipo_preparacion ?? '') == 'Ninguno' ? 'selected' : '' }}>Ninguno
            </option>
        </select>
        @error('tipo_preparacion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="motivo_estudio_eesp">Motivo por el cual elegiste estudiar en la EESP Pukllasunchis:</label>
        <select class="form-control form-control-sm @error('motivo_estudio_eesp') is-invalid @enderror"
            id="motivo_estudio_eesp" name="motivo_estudio_eesp" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Por su prestigio" {{ $alumno->motivo_estudio_eesp == 'Por su prestigio' ? 'selected' : '' }}>
                Por su prestigio</option>
            <option value="Tradición Familiar"
                {{ $alumno->motivo_estudio_eesp == 'Tradición Familiar' ? 'selected' : '' }}>Tradición Familiar</option>
            <option value="Porque allí estudian amigos"
                {{ $alumno->motivo_estudio_eesp == 'Porque allí estudian amigos' ? 'selected' : '' }}>Porque allí
                estudian amigos</option>
            <option value="Porque es estatal"
                {{ $alumno->motivo_estudio_eesp == 'Porque es estatal' ? 'selected' : '' }}>Porque es estatal</option>
            <option value="Cercanía a mi domicilio"
                {{ $alumno->motivo_estudio_eesp == 'Cercanía a mi domicilio' ? 'selected' : '' }}>Cercanía a mi domicilio
            </option>
            <option value="Vocación" {{ $alumno->motivo_estudio_eesp == 'Vacación' ? 'selected' : '' }}>Vocación
            </option>
            <option value="Presión Familiar" {{ $alumno->motivo_estudio_eesp == 'Presión Familiar' ? 'selected' : '' }}>
                Presión Familiar</option>
            <option value="Otro" {{ $alumno->motivo_estudio_eesp == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_estudio_eesp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    

    <div class="col-lg-6 mb-2 mt-2">
        <label for="motivo_docencia">Motivo para seguir estudios de docencia:</label>
        <select class="form-control form-control-sm @error('motivo_docencia') is-invalid @enderror"
            id="motivo_docencia" name="motivo_docencia" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Por realización personal"
                {{ old('motivo_docencia', $alumno->motivo_docencia ?? '') == 'Por realización personal' ? 'selected' : '' }}>
                Por realización personal
            </option>
            <option value="Por progresar económicamente"
                {{ old('motivo_docencia', $alumno->motivo_docencia ?? '') == 'Por progresar económicamente' ? 'selected' : '' }}>
                Por progresar
                económicamente</option>
            <option value="Por progresar socialmente"
                {{ old('motivo_docencia', $alumno->motivo_docencia ?? '') == 'Por progresar socialmente' ? 'selected' : '' }}>
                Por progresar socialmente
            </option>
            <option value="Por ayudar a la comunidad"
                {{ old('motivo_docencia', $alumno->motivo_docencia ?? '') == 'Por ayudar a la comunidad' ? 'selected' : '' }}>
                Por ayudar a la comunidad
            </option>
            <option value="Otro"
                {{ old('motivo_docencia', $alumno->motivo_docencia ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_docencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="motivo_especialidad">Motivo por el cual elegiste tu especialidad:</label>
        <select class="form-control form-control-sm @error('motivo_especialidad') is-invalid @enderror"
            id="motivo_especialidad" name="motivo_especialidad" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Vocación"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Vocación' ? 'selected' : '' }}>
                Vocación
            </option>
            <option value="Presión Familiar"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Presión Familiar' ? 'selected' : '' }}>
                Presión Familiar</option>
            <option value="Tradición Familiar"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Tradición Familiar' ? 'selected' : '' }}>
                Tradición Familiar</option>
            <option value="Test Vocacional"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Test Vocacional' ? 'selected' : '' }}>
                Test Vocacional</option>
            <option value="Por el puntaje bajo"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Por el puntaje bajo' ? 'selected' : '' }}>
                Por el puntaje bajo
            </option>
            <option value="Es más rentable"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Es más rentable' ? 'selected' : '' }}>
                Es
                más rentable</option>
            <option value="Es la carrera del momento"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Es la carrera del momento' ? 'selected' : '' }}>
                Es la carrera del
                momento</option>
            <option value="Otro"
                {{ old('motivo_especialidad', $alumno->motivo_especialidad ?? '') == 'Otro' ? 'selected' : '' }}>Otro
            </option>
        </select>
        @error('motivo_especialidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-lg-6 mb-2 mt-2">
        <label class="d-block">¿Tienes acceso a internet en casa?</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('internet') is-invalid @enderror" type="radio" name="internet"
                id="internet_si" value="1"
                {{ old('internet', $alumno->internet ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="internet_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('internet') is-invalid @enderror" type="radio" name="internet"
                id="internet_no" value="0"
                {{ old('internet', $alumno->internet ?? '') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="internet_no">No</label>
        </div>
        @error('internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="internet_lugar">En caso la respuesta anterior haya sido No, ¿desde qué lugar se conecta Ud. a
            internet?</label>
        <select class="form-control form-control-sm @error('internet_lugar') is-invalid @enderror"
            id="internet_lugar" name="internet_lugar">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Trabajo/Oficina"
                {{ old('internet_lugar', $alumno->internet_lugar ?? '') == 'Trabajo/Oficina' ? 'selected' : '' }}>
                Trabajo/Oficina</option>
            <option value="Casa de amigos/Familiares"
                {{ old('internet_lugar', $alumno->internet_lugar ?? '') == 'Casa de amigos/Familiares' ? 'selected' : '' }}>
                Casa de amigos/Familiares
            </option>
            <option value="Otro"
                {{ old('internet_lugar', $alumno->internet_lugar ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('internet_lugar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="servicio_internet">Principal servicio de internet usado:</label>
        <select class="form-control form-control-sm @error('servicio_internet') is-invalid @enderror"
            id="servicio_internet" name="servicio_internet" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Internet fijo"
                {{ old('servicio_internet', $alumno->servicio_internet ?? '') == 'Internet fijo' ? 'selected' : '' }}>
                Internet
                fijo</option>
            <option value="Internet Satelital"
                {{ old('servicio_internet', $alumno->servicio_internet ?? '') == 'Internet Satelital' ? 'selected' : '' }}>
                Internet Satelital</option>
            <option value="Internet Móvil (Celular, otro inalámbrico)"
                {{ old('servicio_internet', $alumno->servicio_internet ?? '') == 'Internet Móvil (Celular, otro inalámbrico)' ? 'selected' : '' }}>
                Internet Móvil (Celular, otro inalámbrico)</option>
        </select>
        @error('servicio_internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="dispositivo_internet">Dispositivo para conectarse a internet en actividades académicas</label>
        <select class="form-control form-control-sm @error('dispositivo_internet') is-invalid @enderror"
            id="dispositivo_internet" name="dispositivo_internet" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Celular" {{ $alumno->dispositivo_internet == 'Celular' ? 'selected' : '' }}>Celular
            </option>
            <option value="Tablet" {{ $alumno->dispositivo_internet == 'Tablet' ? 'selected' : '' }}>Tablet</option>
            <option value="Laptop" {{ $alumno->dispositivo_internet == 'Laptop' ? 'selected' : '' }}>Laptop</option>
            <option value="Computadora" {{ $alumno->dispositivo_internet == 'Computadora' ? 'selected' : '' }}>
                Computadora</option>
        </select>
        @error('dispositivo_internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>



    <div class="col-lg-6 mb-2 mt-2">
        <label class="d-block">¿Cómo utiliza el dispositivo para conectarse a internet para actividades
            académicas?</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('propio_compartido') is-invalid @enderror" type="radio"
                name="propio_compartido" id="propio_compartido_si" value="1"
                {{ old('propio_compartido', $alumno->propio_compartido) == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="propio_compartido_si">Uso propio</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('propio_compartido') is-invalid @enderror" type="radio"
                name="propio_compartido" id="propio_compartido_no" value="0"
                {{ old('propio_compartido', $alumno->propio_compartido) == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="propio_compartido_no">Uso compartido</label>
        </div>
        @error('propio_compartido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="correo">¿Utiliza el correo electrónico?</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('correo') is-invalid @enderror" type="radio" name="correo"
                id="correo_si" value="1" {{ old('correo', $alumno->correo) == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="correo_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('correo') is-invalid @enderror" type="radio" name="correo"
                id="correo_no" value="0" {{ old('correo', $alumno->correo) == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="correo_no">No</label>
        </div>
        @error('correo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>



    <div class="col-lg-6 mb-2 mt-2">
        <label for="num_hrs_estudio">Número de horas dedicadas al estudio:</label>
        <input type="number" class="form-control @error('num_hrs_estudio') is-invalid @enderror"
            id="num_hrs_estudio" name="num_hrs_estudio"
            value="{{ old('num_hrs_estudio', $alumno->num_hrs_estudio) }}" required>
        @error('num_hrs_estudio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="forma_estudio">Forma que prefiere estudiar:</label>
        <select class="form-control form-control-sm @error('forma_estudio') is-invalid @enderror" id="forma_estudio"
            name="forma_estudio" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Grupo" {{ old('forma_estudio', $alumno->forma_estudio) == 'Grupo' ? 'selected' : '' }}>
                Grupo</option>
            <option value="Solo" {{ old('forma_estudio', $alumno->forma_estudio) == 'Solo' ? 'selected' : '' }}>Solo
            </option>
            <option value="Profesor Particular"
                {{ old('forma_estudio', $alumno->forma_estudio) == 'Profesor Particular' ? 'selected' : '' }}>
                Profesor Particular</option>
        </select>
        @error('forma_estudio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
