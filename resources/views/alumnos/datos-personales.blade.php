<div class="row">
    <div class="col-lg-6 mb-2">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email', optional($user)->email) }}"
                @if (auth()->check()) readonly @endif required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-lg-6 mb-2">
        <label for="dni">DNI:</label>
        <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror" id="dni"
            name="dni" value="{{ old('dni', optional($user)->dni) }}"
            @if (auth()->check()) readonly @endif required>
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if (auth()->check())
        {{-- Con Auth --}}
        <div class="col-lg-6 mb-2">
            <label for="programa_id">Programa:</label>
            <select id="programa_selector" name="programa_id" class="form-control form-control-sm" required readonly>
                <option selected disabled>Elegir Programa:</option>
                @foreach ($programas as $programa)
                    <option value="{{ $programa->id }}"
                        {{ old('programa_id', optional(auth()->user()->programa)->id) == $programa->id ? 'selected' : '' }}>
                        {{ $programa->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 mb-2">
            <label for="ciclo_id">Ciclo: <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm mb-2 @error('ciclo_id') is-invalid @enderror"
                id="ciclo_selector" name="ciclo_id" required readonly>
                <option value="" disabled>Seleccionar Ciclo</option>
                @foreach ($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}"
                        {{ old('ciclo_id', optional(auth()->user()->ciclo)->id) == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->nombre }}
                    </option>
                @endforeach
            </select>
            @error('ciclo_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <script>
            // Desactivar la capacidad de cambio en los selects
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('programa_selector').addEventListener('mousedown', function(event) {
                    event.preventDefault();
                });

                document.getElementById('ciclo_selector').addEventListener('mousedown', function(event) {
                    event.preventDefault();
                });
            });
        </script>
    @else
        {{-- Sin Auth --}}
        <div class="col-lg-6 mb-2">
            <label for="programa_id">Programa:</label>
            <select id="programa_selector" name="programa_id" class="form-control form-control-sm" required>
                <option selected disabled>Elegir Programa:</option>
                @foreach ($programas as $programa)
                    <option value="{{ $programa->id }}" {{ old('programa_id') == $programa->id ? 'selected' : '' }}>
                        {{ $programa->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 mb-2">
            <label>Ciclo: <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm mb-2 @error('ciclo_id') is-invalid @enderror"
                id="ciclo_selector" name="ciclo_id" required>
                <option value="" disabled {{ old('ciclo_id') ? '' : 'selected' }}>Elegir Ciclo</option>
                @foreach ($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->nombre }}
                    </option>
                @endforeach
            </select>
            @error('ciclo_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    @endif


    <div class="col-lg-6 mb-2">
        <label for="nombres">Nombres:</label>
        <input type="text" class="form-control form-control-sm @error('nombres') is-invalid @enderror" id="nombres"
            name="nombres" value="{{ old('nombres', optional($user)->name) }}"
            @if (auth()->check()) readonly @endif required>
        @error('nombres')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="apellidos">Apellidos:</label>
        <input type="text" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
            id="apellidos" name="apellidos" value="{{ old('apellidos', optional($user)->apellidos) }}"
            @if (auth()->check()) readonly @endif required>
        @error('apellidos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="numero">Celular:</label>
        <input type="text" class="form-control form-control-sm @error('numero') is-invalid @enderror" id="numero"
            name="numero" value="{{ old('numero') }}" required>
        @error('numero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="numero_referencia">Celular de contacto de emergencia:</label>
        <input type="text" class="form-control form-control-sm @error('numero_referencia') is-invalid @enderror"
            id="numero_referencia" name="numero_referencia" value="{{ old('numero_referencia') }}" required>
        @error('numero_referencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="procedencia_familiar">¿Tu familia o tú proceden de alguna comunidad?:</label>
        <select class="form-control form-control-sm @error('procedencia_familiar') is-invalid @enderror"
            id="procedencia_familiar" name="procedencia_familiar" required>
            <option value="" disabled {{ old('procedencia_familiar') ? '' : 'selected' }}>Selecciona una opción
            </option>
            <option value="vivo_en_comunidad"
                {{ old('procedencia_familiar') == 'vivo_en_comunidad' ? 'selected' : '' }}>Sí, yo aún vivo en la
                comunidad</option>
            <option value="padres_viven_en_comunidad"
                {{ old('procedencia_familiar') == 'padres_viven_en_comunidad' ? 'selected' : '' }}>Sí, mis padres aún
                viven en la comunidad</option>
            <option value="abuelos_viven_en_comunidad"
                {{ old('procedencia_familiar') == 'abuelos_viven_en_comunidad' ? 'selected' : '' }}>Sí, mis abuelos aún
                viven en la comunidad</option>
            <option value="no_vivimos_en_comunidad"
                {{ old('procedencia_familiar') == 'no_vivimos_en_comunidad' ? 'selected' : '' }}>Sí, pero ya no vivimos
                en la comunidad</option>
            <option value="familia_de_zona_urbana"
                {{ old('procedencia_familiar') == 'familia_de_zona_urbana' ? 'selected' : '' }}>No, mi familia procede
                de una zona urbana</option>
        </select>
        @error('procedencia_familiar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label for="direccion">Domicilio: <small> (Comunidad, distrito y provincia. Detalla:
                calle-número/barrio-lote)</small></label>
        <input type="text" class="form-control form-control-sm mb-2 @error('direccion') is-invalid @enderror"
            name="direccion" value="{{ old('direccion', isset($alumno) ? $alumno->direccion : '') }}" required>
        @error('direccion')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="te_consideras">Por tus antepasados y de acuerdo a tus costumbres, te consideras:</label>
        <select class="form-control form-control-sm @error('te_consideras') is-invalid @enderror" id="te_consideras"
            name="te_consideras" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="quechua" {{ old('te_consideras') == 'quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="aymara" {{ old('te_consideras') == 'aymara' ? 'selected' : '' }}>Aymara</option>
            <option value="nativo_amazonia" {{ old('te_consideras') == 'nativo_amazonia' ? 'selected' : '' }}>Nativo o
                indígena de la Amazonía</option>
            <option value="negro_moreno_zambo" {{ old('te_consideras') == 'negro_moreno_zambo' ? 'selected' : '' }}>
                Negro/Moreno/Zambo mulato/Pueblo afroperuano o afrodescendiente</option>
            <option value="blanco" {{ old('te_consideras') == 'blanco' ? 'selected' : '' }}>Blanco</option>
            <option value="mestizo" {{ old('te_consideras') == 'mestizo' ? 'selected' : '' }}>Mestizo</option>
            <option value="otro_pueblo_indigena"
                {{ old('te_consideras') == 'otro_pueblo_indigena' ? 'selected' : '' }}>Perteneciente o parte de otro
                pueblo indígena u originario</option>
            <option value="no_sabe_no_responde" {{ old('te_consideras') == 'no_sabe_no_responde' ? 'selected' : '' }}>
                No sabe/No responde</option>
            <option value="otro" {{ old('te_consideras') == 'otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('te_consideras')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="lengua_1">Lengua 1:</label>
        <select class="form-control form-control-sm @error('lengua_1') is-invalid @enderror" id="lengua_1"
            name="lengua_1" required>
            <option value="" disabled>Selecciona una opción</option>
            <option value="Quechua" {{ old('lengua_1') == 'Quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="Castellano" {{ old('lengua_1') == 'Castellano' ? 'selected' : '' }}>Castellano</option>
            <option value="Otro" {{ old('lengua_1') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('lengua_1')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>    

    <div class="col-lg-6 mb-2">
        <label for="lengua_2">Lengua 2:</label>
        <select class="form-control form-control-sm @error('lengua_2') is-invalid @enderror" id="lengua_2"
            name="lengua_2" required>
            <option value="" disabled {{ old('lengua_2') ? '' : 'selected' }}>Selecciona una opción</option>
            <option value="Quechua" {{ old('lengua_2') == 'quechua' ? 'selected' : '' }}>Quechua</option>
            <option value="Castellano" {{ old('lengua_2') == 'castellano' ? 'selected' : '' }}>Castellano</option>
            <option value="Otro" {{ old('lengua_2') == 'otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('lengua_2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-2">
        <label for="estado_civil">Estado Civil:</label>
        <select class="form-control form-control-sm mb-2 @error('estado_civil') is-invalid @enderror"
            name="estado_civil" required>
            <option disabled selected>Seleccionar estado civil</option>
            <option value="Casado"
                {{ old('estado_civil', isset($alumno) ? $alumno->estado_civil : '') == 'Casado' ? 'selected' : '' }}>
                Casado</option>
            <option value="Soltero"
                {{ old('estado_civil', isset($alumno) ? $alumno->estado_civil : '') == 'Soltero' ? 'selected' : '' }}>
                Soltero</option>
            <option value="Viudo"
                {{ old('estado_civil', isset($alumno) ? $alumno->estado_civil : '') == 'Viudo' ? 'selected' : '' }}>
                Viudo</option>
            <option value="Divorciado"
                {{ old('estado_civil', isset($alumno) ? $alumno->estado_civil : '') == 'Divorciado' ? 'selected' : '' }}>
                Divorciado</option>
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
                name="p_m_soltero" id="soltero_si" value="1" {{ old('p_m_soltero') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="soltero_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input @error('p_m_soltero') is-invalid @enderror" type="radio"
                name="p_m_soltero" id="soltero_no" value="0" {{ old('p_m_soltero') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="soltero_no">No</label>
        </div>
        @error('p_m_soltero')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="num_hijos">Número de Hijos:</label>
        <input type="number" class="form-control form-control-sm @error('num_hijos') is-invalid @enderror"
            id="num_hijos" name="num_hijos" value="{{ old('num_hijos') }}" required>
        @error('num_hijos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="sector_socioeconomico">¿A qué sector socioeconómico perteneces?:</label>
        <select class="form-control form-control-sm @error('sector_socioeconomico') is-invalid @enderror"
            id="sector_socioeconomico" name="sector_socioeconomico" required>
            <option disabled selected>Selecciona una opción</option>
            <option value="popular" {{ old('sector_socioeconomico') == 'popular' ? 'selected' : '' }}>Popular</option>
            <option value="medio" {{ old('sector_socioeconomico') == 'medio' ? 'selected' : '' }}>Medio</option>
            <option value="medio_alto" {{ old('sector_socioeconomico') == 'medio_alto' ? 'selected' : '' }}>Medio Alto
            </option>
            <option value="alto" {{ old('sector_socioeconomico') == 'alto' ? 'selected' : '' }}>Alto</option>
        </select>
        @error('sector_socioeconomico')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-2">
        <label for="num_comprobante">N° Voucher Caja Cusco:
            <small> <span class="text-primary" type="button" data-toggle="modal" data-target="#exampleModal">
                    Ejemplo
                </span></small>
        </label>
        <div class="input-group">
            <input type="text" class="form-control form-control-sm @error('num_comprobante') is-invalid @enderror"
                id="num_comprobante" name="num_comprobante"
                value="{{ optional($user)->beca == 1 ? 'Beca' : (old('num_comprobante') ?: '') }}"
                @if (auth()->check() && optional($user)->beca == 1) readonly @endif required>
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
               <img src="{{asset('img/novedades/Voucher-cajaCusco.jpg')}}" alt="">
            </div>
        </div>
    </div>

</div>
