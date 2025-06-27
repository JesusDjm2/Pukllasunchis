<div class="row">
    <div class="col-lg-12">
        <h4 class="mb-2 mt-4">Aspectos Familiares</h4>
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="convivientes">1. ¿Con quién(es) vive(s)?:</label>
        <select class="form-control @error('convivientes') is-invalid @enderror" id="convivientes" name="convivientes">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Solo" {{ old('convivientes') == 'Solo' ? 'selected' : '' }}>Solo</option>
            <option value="Padre" {{ old('convivientes') == 'Padre' ? 'selected' : '' }}>Padre</option>
            <option value="Madre" {{ old('convivientes') == 'Madre' ? 'selected' : '' }}>Madre</option>
            <option value="Padre y Madre" {{ old('convivientes') == 'Padre y Madre' ? 'selected' : '' }}>Padre y Madre
            </option>
            <option value="Esposo/Cónyuge" {{ old('convivientes') == 'Esposo/Cónyuge' ? 'selected' : '' }}>
                Esposo/Cónyuge</option>
            <option value="Otro" {{ old('convivientes') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        <input type="text" class="form-control mt-2 @if (old('convivientes') != 'Otro') d-none @endif"
            id="otro_conviviente" name="convivientes" value="{{ old('otro_conviviente') }}"
            placeholder="Especificar otro">
        @error('convivientes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const convivientesSelect = document.getElementById('convivientes');
            const otroConvivienteInput = document.getElementById('otro_conviviente');

            convivientesSelect.addEventListener('change', function() {
                if (convivientesSelect.value === 'Otro') {
                    otroConvivienteInput.classList.remove('d-none');
                    otroConvivienteInput.setAttribute('required', 'required');
                    otroConvivienteInput.setAttribute('name',
                        'convivientes'); // Establecer el nombre como 'convivientes'
                    otroConvivienteInput.setAttribute('id',
                        'convivientes'); // Establecer el ID como 'convivientes'
                } else {
                    otroConvivienteInput.classList.add('d-none');
                    otroConvivienteInput.removeAttribute('required');
                    otroConvivienteInput.removeAttribute('name');
                    otroConvivienteInput.removeAttribute('id');
                }
            });

            otroConvivienteInput.addEventListener('input', function() {
                if (convivientesSelect.value === 'Otro') {
                    convivientesSelect.value = otroConvivienteInput.value;
                }
            });
        });
    </script>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="quien_mantiene">2. Persona que mantiene el hogar:</label>
        <select class="form-control form-control-sm @error('quien_mantiene') is-invalid @enderror" id="quien_mantiene"
            name="quien_mantiene" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Yo" {{ old('quien_mantiene') == 'Yo' ? 'selected' : '' }}>Yo</option>
            <option value="Padre" {{ old('quien_mantiene') == 'Padre' ? 'selected' : '' }}>Padre</option>
            <option value="Madre" {{ old('quien_mantiene') == 'Madre' ? 'selected' : '' }}>Madre</option>
            <option value="Padre y Madre" {{ old('quien_mantiene') == 'Padre y Madre' ? 'selected' : '' }}>Padre y
                Madre</option>
            <option value="Esposo(a)/Cónyuge" {{ old('quien_mantiene') == 'Esposo(a)/Cónyuge' ? 'selected' : '' }}>
                Esposo(a)/Cónyuge</option>
            <option value="Otro" {{ old('quien_mantiene') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('quien_mantiene')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_child">3. Cantidad de dependientes en el hogar (niños):</label>
        <input type="text"
            class="form-control form-control-sm @error('cant_dependientes_child') is-invalid @enderror"
            id="cant_dependientes_child" name="cant_dependientes_child" value="{{ old('cant_dependientes_child') }}"
            required>
        @error('cant_dependientes_child')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_old">4. Cantidad de dependientes en el hogar (tercera edad):</label>
        <input type="text" class="form-control form-control-sm @error('cant_dependientes_old') is-invalid @enderror"
            id="cant_dependientes_old" name="cant_dependientes_old" value="{{ old('cant_dependientes_old') }}"
            required>
        @error('cant_dependientes_old')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="cant_dependientes_otros">5. Cantidad de dependientes en el hogar (otros):</label>
        <input type="text"
            class="form-control form-control-sm @error('cant_dependientes_otros') is-invalid @enderror"
            id="cant_dependientes_otros" name="cant_dependientes_otros" value="{{ old('cant_dependientes_otros') }}"
            required>
        @error('cant_dependientes_otros')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-12">
        <h4 class="mt-4 mb-2">Aspectos Educativos</h4>
    </div>
    {{-- Nuevos campos PPD --}}
    <div class="col-lg-4 mb-2 mt-2">
        <label for="carrera_procedencia">1. Carrera de Procedencia:</label>
        <input type="text" class="form-control form-control-sm @error('carrera_procedencia') is-invalid @enderror"
            id="carrera_procedencia" name="carrera_procedencia" placeholder="Ingrese la carrera de procedencia"
            value="{{ old('carrera_procedencia') }}">
        @error('carrera_procedencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="ano_culminaste">2. Año en que culminaste:</label>
        <input type="number" class="form-control form-control-sm @error('ano_culminaste') is-invalid @enderror"
            id="ano_culminaste" name="ano_culminaste" placeholder="Ingrese el año que culmino sus estudios superiores" value="{{ old('ano_culminaste') }}">
        @error('ano_culminaste')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="institucion_procedencia">3. Institución Educativa de Procedencia:</label>
        <input type="text"
            class="form-control form-control-sm @error('institucion_procedencia') is-invalid @enderror"
            id="institucion_procedencia" name="institucion_procedencia" placeholder="Ingrese la institución"
            value="{{ old('institucion_procedencia') }}">
        @error('institucion_procedencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="gestion_institucion">4. Gestión de la I.E. de Procedencia:</label>
        <select class="form-control form-control-sm @error('gestion_institucion') is-invalid @enderror"
            id="gestion_institucion" name="gestion_institucion">
            <option value="" disabled selected>Seleccione</option>
            <option value="Público" {{ old('gestion_institucion') == 'Público' ? 'selected' : '' }}>Público</option>
            <option value="Privado" {{ old('gestion_institucion') == 'Privado' ? 'selected' : '' }}>Privado</option>
        </select>
        @error('gestion_institucion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="direccion_institucion">5. Dirección de la I.E. de Procedencia:</label>
        <input type="text"
            class="form-control form-control-sm @error('direccion_institucion') is-invalid @enderror"
            id="direccion_institucion" name="direccion_institucion" placeholder="Ingrese la dirección"
            value="{{ old('direccion_institucion') }}">
        @error('direccion_institucion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="dep_dist_prov_institucion">6. Departamento, Distrito y Prov. de la I.E. de Procedencia:</label>
        <input type="text"
            class="form-control form-control-sm @error('dep_dist_prov_institucion') is-invalid @enderror"
            id="dep_dist_prov_institucion" name="dep_dist_prov_institucion" placeholder="Ejemplo: (Cusco - Cusco - Jan Jerónimo)"
            value="{{ old('dep_dist_prov_institucion') }}">
        @error('dep_dist_prov_institucion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="estudio_beca">7. En la etapa escolar, ¿estudiaste con Beca? </label>
        <select class="form-control form-control-sm @error('estudio_beca') is-invalid @enderror" id="estudio_beca"
            name="estudio_beca" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Si, Beca Parcial" {{ old('estudio_beca') == 'Si, Beca Parcial' ? 'selected' : '' }}>Si,
                Beca
                Parcial</option>
            <option value="Si, Beca Total" {{ old('estudio_beca') == 'Si, Beca Total' ? 'selected' : '' }}>Si, Beca
                Total
            </option>
            <option value="No" {{ old('estudio_beca') == 'No' ? 'selected' : '' }}>No</option>
        </select>
        @error('estudio_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="origen_beca">8. Origen Beca:</label>
        <input type="text" class="form-control form-control-sm @error('origen_beca') is-invalid @enderror"
            id="origen_beca" name="origen_beca" placeholder="Ingrese el origen de la beca (opcional)"
            value="{{ old('origen_beca') }}">
        @error('origen_beca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_eesp">9. Número de veces que postuló a EESP Pukllasunchis:</label><br><br>
        <input type="number" class="form-control form-control-sm @error('postulaciones_eesp') is-invalid @enderror"
            id="postulaciones_eesp" name="postulaciones_eesp" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_eesp') }}">
        @error('postulaciones_eesp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_inst_uni">10. Número de veces que postuló a <strong> educación</strong> en otras
            instituciones: </label>
        <input type="number"
            class="form-control form-control-sm @error('postulaciones_inst_uni') is-invalid @enderror"
            id="postulaciones_inst_uni" name="postulaciones_inst_uni" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_inst_uni') }}">
        @error('postulaciones_inst_uni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="postulaciones_otros">11.Número de veces que postuló a otras carreras en otras Instituciones:</label>
        <input type="number" class="form-control form-control-sm @error('postulaciones_otros') is-invalid @enderror"
            id="postulaciones_otros" name="postulaciones_otros" placeholder="Ingrese la cantidad"
            value="{{ old('postulaciones_otros') }}">
        @error('postulaciones_otros')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="tipo_preparacion">12. Tipo de Preparación para postular a la EESPP Pukllasunchis:</label>
        <select class="form-control form-control-sm @error('tipo_preparacion') is-invalid @enderror"
            id="tipo_preparacion" name="tipo_preparacion" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="Centro Pre" {{ old('tipo_preparacion') == 'Centro Pre' ? 'selected' : '' }}>Centro Pre
            </option>
            <option value="Centro Pre de otro IESP/EESP"
                {{ old('tipo_preparacion') == 'Centro Pre de otro IESP/EESP' ? 'selected' : '' }}>Centro Pre de otro
                IESP/EESP</option>
            <option value="Académia" {{ old('tipo_preparacion') == 'Académia' ? 'selected' : '' }}>Académia</option>
            <option value="Otros" {{ old('tipo_preparacion') == 'Otros' ? 'selected' : '' }}>Otros</option>
            <option value="Ninguno" {{ old('tipo_preparacion') == 'Ninguno' ? 'selected' : '' }}>Ninguno</option>
        </select>
        @error('tipo_preparacion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="motivo_estudio_eesp">13. Motivo por el cual elegiste estudiar en la EESP Pukllasunchis:</label>
        <select class="form-control form-control-sm @error('motivo_estudio_eesp') is-invalid @enderror"
            id="motivo_estudio_eesp" name="motivo_estudio_eesp" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Por su prestigio" {{ old('motivo_estudio_eesp') == 'Por su prestigio' ? 'selected' : '' }}>
                Por su prestigio</option>
            <option value="Tradición Familiar"
                {{ old('motivo_estudio_eesp') == 'Tradición Familiar' ? 'selected' : '' }}>Tradición Familiar</option>
            <option value="Porque allí estudian amigos"
                {{ old('motivo_estudio_eesp') == 'Porque allí estudian amigos' ? 'selected' : '' }}>Porque allí
                estudian amigos</option>
            <option value="Porque es estatal"
                {{ old('motivo_estudio_eesp') == 'Porque es estatal' ? 'selected' : '' }}>Porque es estatal</option>
            <option value="Cercanía a mi domicilio"
                {{ old('motivo_estudio_eesp') == 'Cercanía a mi domicilio' ? 'selected' : '' }}>Cercanía a mi domicilio
            </option>
            <option value="Vocación" {{ old('motivo_estudio_eesp') == 'Vacación' ? 'selected' : '' }}>Vocación
            </option>
            <option value="Presión Familiar" {{ old('motivo_estudio_eesp') == 'Presión Familiar' ? 'selected' : '' }}>
                Presión Familiar</option>
            <option value="Otro" {{ old('motivo_estudio_eesp') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_estudio_eesp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-2 mt-2">
        <label for="motivo_docencia">14. Motivo para seguir estudios de docencia:</label> <br><br>
        <select class="form-control form-control-sm @error('motivo_docencia') is-invalid @enderror"
            id="motivo_docencia" name="motivo_docencia" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Por realización personal"
                {{ old('motivo_docencia') == 'Por realización personal' ? 'selected' : '' }}>Por realización personal
            </option>
            <option value="Por progresar económicamente"
                {{ old('motivo_docencia') == 'Por progresar económicamente' ? 'selected' : '' }}>Por progresar
                económicamente</option>
            <option value="Por progresar socialmente"
                {{ old('motivo_docencia') == 'Por progresar socialmente' ? 'selected' : '' }}>Por progresar socialmente
            </option>
            <option value="Por ayudar a la comunidad"
                {{ old('motivo_docencia') == 'Por ayudar a la comunidad' ? 'selected' : '' }}>Por ayudar a la comunidad
            </option>
            <option value="Otro" {{ old('motivo_docencia') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_docencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="motivo_especialidad"> 15. Motivo por el cual elegiste tu especialidad:</label>
        <select class="form-control form-control-sm @error('motivo_especialidad') is-invalid @enderror"
            id="motivo_especialidad" name="motivo_especialidad" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Vocación" {{ old('motivo_especialidad') == 'Vocación' ? 'selected' : '' }}>Vocación
            </option>
            <option value="Presión Familiar" {{ old('motivo_especialidad') == 'Presión Familiar' ? 'selected' : '' }}>
                Presión Familiar</option>
            <option value="Tradición Familiar"
                {{ old('motivo_especialidad') == 'Tradición Familiar' ? 'selected' : '' }}>Tradición Familiar</option>
            <option value="Test Vocacional" {{ old('motivo_especialidad') == 'Test Vocacional' ? 'selected' : '' }}>
                Test Vocacional</option>
            <option value="Por el puntaje bajo"
                {{ old('motivo_especialidad') == 'Por el puntaje bajo' ? 'selected' : '' }}>Por el puntaje bajo
            </option>
            <option value="Es más rentable" {{ old('motivo_especialidad') == 'Es más rentable' ? 'selected' : '' }}>Es
                más rentable</option>
            <option value="Es la carrera del momento"
                {{ old('motivo_especialidad') == 'Es la carrera del momento' ? 'selected' : '' }}>Es la carrera del
                momento</option>
            <option value="Otro" {{ old('motivo_especialidad') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_especialidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label class="d-block">16. ¿Tienes acceso a internet en casa?</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('internet') is-invalid @enderror" type="radio" name="internet"
                id="internet_si" value="1" {{ old('internet') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="internet_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('internet') is-invalid @enderror" type="radio" name="internet"
                id="internet_no" value="0" {{ old('internet') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="internet_no">No</label>
        </div>
        @error('internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 mb-2 mt-2">
        <label for="internet_lugar">17. En caso la respuesta anterior haya sido No, ¿desde qué lugar se conecta Ud. a
            internet?</label>
        <select class="form-control form-control-sm @error('internet_lugar') is-invalid @enderror"
            id="internet_lugar" name="internet_lugar">
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Trabajo/Oficina" {{ old('internet_lugar') == 'Trabajo/Oficina' ? 'selected' : '' }}>
                Trabajo/Oficina</option>
            <option value="Casa de amigos/Familiares"
                {{ old('internet_lugar') == 'Casa de amigos/Familiares' ? 'selected' : '' }}>Casa de amigos/Familiares
            </option>
            <option value="Otro" {{ old('internet_lugar') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('internet_lugar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="servicio_internet">18. Principal servicio de internet usado:</label>
        <select class="form-control form-control-sm @error('servicio_internet') is-invalid @enderror"
            id="servicio_internet" name="servicio_internet" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Internet fijo" {{ old('servicio_internet') == 'Internet fijo' ? 'selected' : '' }}>Internet
                fijo</option>
            <option value="Internet Satelital"
                {{ old('servicio_internet') == 'Internet Satelital' ? 'selected' : '' }}>Internet Satelital</option>
            <option value="Internet Móvil (Celular, otro inalámbrico)"
                {{ old('servicio_internet') == 'Internet Móvil (Celular, otro inalámbrico)' ? 'selected' : '' }}>
                Internet Móvil (Celular, otro inalámbrico)</option>
        </select>
        @error('servicio_internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2 mt-2">
        <label for="dispositivo_internet">19. Que dispositivo emplea para conectarse al internet para actividades
            académicas?</label>
        <select class="form-control form-control-sm @error('dispositivo_internet') is-invalid @enderror"
            id="dispositivo_internet" name="dispositivo_internet" required>
            <option value="" disabled {{ old('dispositivo_internet') ? '' : 'selected' }}>Selecciona una opción
            </option>
            <option value="Celular" {{ old('dispositivo_internet') == 'Celular' ? 'selected' : '' }}>Celular</option>
            <option value="Tablet" {{ old('dispositivo_internet') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
            <option value="Laptop" {{ old('dispositivo_internet') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
            <option value="Computadora" {{ old('dispositivo_internet') == 'Computadora' ? 'selected' : '' }}>
                Computadora</option>
        </select>
        @error('dispositivo_internet')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label class="d-block">20. ¿Cómo utiliza el dispositivo para conectarse a internet para actividades
            académicas?</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('propio_compartido') is-invalid @enderror" type="radio"
                name="propio_compartido" id="propio_compartido_si" value="1"
                {{ old('propio_compartido') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="propio_compartido_si">Uso propio</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('propio_compartido') is-invalid @enderror" type="radio"
                name="propio_compartido" id="propio_compartido_no" value="0"
                {{ old('propio_compartido') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="propio_compartido_no">Uso compartido</label>
        </div>
        @error('propio_compartido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="correo">21. ¿Utiliza el correo electrónico?</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('correo') is-invalid @enderror" type="radio" name="correo"
                id="correo_si" value="1" {{ old('correo') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="correo_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('correo') is-invalid @enderror" type="radio" name="correo"
                id="correo_no" value="0" {{ old('correo') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="correo_no">No</label>
        </div>
        @error('correo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="num_hrs_estudio">22. Número de horas dedicadas al estudio:</label>
        <input type="number" class="form-control @error('num_hrs_estudio') is-invalid @enderror"
            id="num_hrs_estudio" name="num_hrs_estudio" value="{{ old('num_hrs_estudio') }}" required>
        @error('num_hrs_estudio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2 mt-2">
        <label for="forma_estudio">23. Forma que prefiere estudiar:</label>
        <select class="form-control form-control-sm @error('forma_estudio') is-invalid @enderror" id="forma_estudio"
            name="forma_estudio" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="Grupo" {{ old('forma_estudio') == 'Grupo' ? 'selected' : '' }}>Grupo</option>
            <option value="Solo" {{ old('forma_estudio') == 'Solo' ? 'selected' : '' }}>Solo</option>
            <option value="Profesor Particular"
                {{ old('forma_estudio') == 'Profesor Particular' ? 'selected' : '' }}>
                Profesor Particular</option>
        </select>
        @error('forma_estudio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
