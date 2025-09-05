@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">Actualizar matrícula: <strong> {{ $alumno->apellidos }}
                    {{ $alumno->nombres }}</strong></h4>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (stripos($alumno->num_comprobante, 'beca') !== false)
                    <div class="alert alert-info text-center" role="alert">
                        <strong>Llenar los nuevos campos y de no tener ningun dato para actualizar, solo guardar el
                            formulario. Seguidamente strong>NOTIFICAR</strong> que terminaste tu ficha de matrícula.
                    </div></strong>
                @else
                    <div class="alert alert-info text-center" role="alert">
                        Por favor <strong>completar los nuevos campos</strong> y actualizar datos de ser necesario.
                        Seguidamente <strong>NOTIFICAR</strong> que terminaste tu ficha de matrícula.
                    </div>
                @endif
            </div>
            <div class="col-lg-12 card pt-3 pb-3 border border-primary">
                <form action="{{ route('alumnos.update', ['alumno' => $alumno->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="container-fluid mt-3">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label for="numero">Número:</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('numero') is-invalid @enderror"
                                    id="numero" name="numero" value="{{ old('numero', $alumno->numero) }}" required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="numero_referencia">Número de Referencia:</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('numero_referencia') is-invalid @enderror"
                                    id="numero_referencia" name="numero_referencia"
                                    value="{{ old('numero_referencia', $alumno->numero_referencia) }}" required>
                                @error('numero_referencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="num_hijos">Número de Hijos:</label>
                                <input type="number"
                                    class="form-control form-control-sm @error('num_hijos') is-invalid @enderror"
                                    id="num_hijos" name="num_hijos" value="{{ old('num_hijos', $alumno->num_hijos ?? '') }}"
                                    required>
                                @error('num_hijos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="num_comprobante">Número de boleta electrónica emitida por la EESP:
                                    <small>
                                        <span class="text-primary" type="button" data-toggle="modal"
                                            data-target="#exampleModal">
                                            Ejemplo
                                        </span>
                                    </small>
                                </label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control form-control-sm @error('num_comprobante') is-invalid @enderror"
                                        id="num_comprobante" name="num_comprobante"
                                        value="{{ stripos($alumno->num_comprobante, 'beca') !== false ? 'Beca' : (old('num_comprobante') ?: '') }}"
                                        @if (stripos($alumno->num_comprobante, 'beca') !== false) readonly @endif required>
                                </div>
                                @error('num_comprobante')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                                <div class="input-group">
                                    <input type="date"
                                        class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
                                        id="fecha_nacimiento" name="fecha_nacimiento"
                                        value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento) }}" required>
                                </div>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="genero">Género:</label>
                                <div class="input-group">
                                    <select class="form-control form-control-sm @error('genero') is-invalid @enderror"
                                        id="genero" name="genero" required>
                                        <option selected disabled>-- Selecciona --</option>
                                        <option value="Masculino"
                                            {{ old('genero', $alumno->genero) == 'Masculino' ? 'selected' : '' }}>Masculino
                                        </option>
                                        <option value="Femenino"
                                            {{ old('genero', $alumno->genero) == 'Femenino' ? 'selected' : '' }}>Femenino
                                        </option>                                        
                                    </select>
                                </div>
                                @error('genero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-lg-4 mb-3">
                                <label for="trabajas">¿Trabajas actualmente?:</label>
                                <select class="form-control form-control-sm @error('trabajas') is-invalid @enderror"
                                    name="trabajas" id="trabajas">
                                    <option value="" disabled
                                        {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == '' ? 'selected' : '' }}>
                                        Selecciona una opción
                                    </option>
                                    <option value="Sí"
                                        {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'Sí' ? 'selected' : '' }}>
                                        Sí</option>
                                    <option value="No"
                                        {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'No' ? 'selected' : '' }}>
                                        No</option>
                                    <option value="Todavía no"
                                        {{ old('trabajas', isset($alumno) ? $alumno->trabajas : '') == 'Todavía no' ? 'selected' : '' }}>
                                        Todavía
                                        no</option>
                                </select>

                                @error('trabajas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <div class="col-lg-4 mb-3">
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
                            </div> --}}

                            {{-- Separacion de nuevos campos --}}
                            <div class="col-lg-12 mt-3">
                                <h6 class="text-primary font-weight-bold">Ingresar domicilio actual indicando Departamento,
                                    Provincia y Distrito:</h6>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label>Departamento</label>
                                <select id="departamento" name="departamento" class="form-control form-control-sm" required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($departamentosData as $dep => $data)
                                        <option value="{{ $dep }}"
                                            {{ old('departamento', $alumno->departamento) == $dep ? 'selected' : '' }}>
                                            {{ $dep }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label>Provincia</label>
                                <select id="provincia" name="provincia" class="form-control form-control-sm" required>
                                    <option value="">Seleccione...</option>
                                    @if (!empty($provinciasData))
                                        @foreach ($provinciasData as $prov)
                                            <option value="{{ $prov }}"
                                                {{ old('provincia', $alumno->provincia) == $prov ? 'selected' : '' }}>
                                                {{ $prov }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label>Distrito</label>
                                <select id="distrito" name="distrito" class="form-control form-control-sm" required>
                                    <option value="">Seleccione...</option>
                                    @if (!empty($distritosData))
                                        @foreach ($distritosData as $dist)
                                            <option value="{{ $dist }}"
                                                {{ old('distrito', $alumno->distrito) == $dist ? 'selected' : '' }}>
                                                {{ $dist }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label for="direccion">Dirección actual:</label>
                                <input type="text"
                                    class="form-control form-control-sm mb-2 @error('direccion') is-invalid @enderror"
                                    name="direccion" value="{{ old('direccion', $alumno->direccion) }}" required>
                                @error('direccion')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-12 mb-2 text-center">
                                <button type="submit" class="btn btn-primary mt-3">
                                    Actualizar
                                </button>
                            </div>
                        </div>

                        {{-- <script>
                            const departamentosData = @json($departamentosData);

                            const departamentoSelect = document.getElementById('departamento');
                            const provinciaSelect = document.getElementById('provincia');
                            const distritoSelect = document.getElementById('distrito');

                            departamentoSelect.addEventListener('change', function() {
                                const departamento = this.value;
                                provinciaSelect.innerHTML = '<option value="">Seleccione...</option>';
                                distritoSelect.innerHTML = '<option value="">Seleccione...</option>';

                                if (departamento && departamentosData[departamento]) {
                                    Object.keys(departamentosData[departamento].provincia).forEach(prov => {
                                        provinciaSelect.innerHTML += `<option value="${prov}">${prov}</option>`;
                                    });
                                }
                            });

                            provinciaSelect.addEventListener('change', function() {
                                const departamento = departamentoSelect.value;
                                const provincia = this.value;
                                distritoSelect.innerHTML = '<option value="">Seleccione...</option>';

                                if (departamento && provincia && departamentosData[departamento].provincia[provincia]) {
                                    departamentosData[departamento].provincia[provincia].forEach(dis => {
                                        distritoSelect.innerHTML += `<option value="${dis}">${dis}</option>`;
                                    });
                                }
                            });
                        </script> --}}
                        <script>
                            const departamentosData = @json($departamentosData);

                            const departamentoSelect = document.getElementById('departamento');
                            const provinciaSelect = document.getElementById('provincia');
                            const distritoSelect = document.getElementById('distrito');

                            // Eventos para selección manual
                            departamentoSelect.addEventListener('change', function() {
                                const departamento = this.value;
                                provinciaSelect.innerHTML = '<option value="">Seleccione...</option>';
                                distritoSelect.innerHTML = '<option value="">Seleccione...</option>';

                                if (departamento && departamentosData[departamento]) {
                                    Object.keys(departamentosData[departamento].provincia).forEach(prov => {
                                        provinciaSelect.innerHTML += `<option value="${prov}">${prov}</option>`;
                                    });
                                }
                            });

                            provinciaSelect.addEventListener('change', function() {
                                const departamento = departamentoSelect.value;
                                const provincia = this.value;
                                distritoSelect.innerHTML = '<option value="">Seleccione...</option>';

                                if (departamento && provincia && departamentosData[departamento].provincia[provincia]) {
                                    departamentosData[departamento].provincia[provincia].forEach(dis => {
                                        distritoSelect.innerHTML += `<option value="${dis}">${dis}</option>`;
                                    });
                                }
                            });

                            // ---- AUTO-CARGA para edición ----
                            const savedDepartamento = "{{ old('departamento', $alumno->departamento) }}";
                            const savedProvincia = "{{ old('provincia', $alumno->provincia) }}";
                            const savedDistrito = "{{ old('distrito', $alumno->distrito) }}";

                            if (savedDepartamento) {
                                // Seleccionar departamento
                                departamentoSelect.value = savedDepartamento;

                                // Cargar provincias
                                Object.keys(departamentosData[savedDepartamento].provincia).forEach(prov => {
                                    provinciaSelect.innerHTML +=
                                        `<option value="${prov}" ${prov === savedProvincia ? 'selected' : ''}>${prov}</option>`;
                                });

                                // Cargar distritos si hay provincia
                                if (savedProvincia) {
                                    departamentosData[savedDepartamento].provincia[savedProvincia].forEach(dis => {
                                        distritoSelect.innerHTML +=
                                            `<option value="${dis}" ${dis === savedDistrito ? 'selected' : ''}>${dis}</option>`;
                                    });
                                }
                            }
                        </script>

                        {{-- <ul class="nav nav-tabs nav-pills nav-justified flex-column flex-sm-row" id="myTab"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                    aria-controls="home" aria-selected="true">Datos Personales</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile" aria-selected="false">Familiares/Educativos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="socioeconomico-tab" data-toggle="tab" href="#socioeconomico"
                                    role="tab" aria-controls="socioeconomico" aria-selected="false">
                                    Socioeconómicos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                    aria-controls="contact" aria-selected="false">Aspecto Vivienda</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="vivienda-tab" data-toggle="tab" href="#vivienda" role="tab"
                                    aria-controls="vivienda" aria-selected="false">Salud y Adicionales</a>
                            </li>
                        </ul> --}}
                        {{-- <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="home" role="tabpanel"
                                aria-labelledby="home-tab">
                                @include('alumnos.vistasAlumnos.edit.datos-personales-edit')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary mt-4 next-tab"
                                        data-tab="profile">Siguiente</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                @include('alumnos.vistasAlumnos.edit.caracteristicas-familiares-edit')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                        data-tab="home">Anterior</button>
                                    <button type="button" class="btn btn-primary mt-4 next-tab"
                                        data-tab="contact">Siguiente</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="socioeconomico" role="tabpanel"
                                aria-labelledby="socioeconomico-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-socioeconomico-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="profile">Anterior</button>
                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                    data-tab="socioeconomico">Siguiente</button>
                            </div>
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-vivienda-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="contact">Anterior</button>
                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                    data-tab="vivienda">Siguiente</button>
                            </div>

                            <div class="tab-pane fade" id="vivienda" role="tabpanel" aria-labelledby="vivienda-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-salud-culturales-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="socioeconomico">Anterior</button>
                            </div>
                        </div> --}}

                    </div>
                </form>
            </div>
        </div>
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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
    <script>
        $(document).ready(function() {
            // Función para avanzar al siguiente tab
            $('.next-tab').click(function() {
                var currentTab = $(this).closest('.tab-pane').attr('id');
                var nextTab = $(this).data('tab');

                // Validar campos requeridos antes de cambiar de tab
                if (validateTab(currentTab)) {
                    $('#' + currentTab).removeClass('show active');
                    $('#' + nextTab).addClass('show active');
                    $('#myTab a[href="#' + nextTab + '"]').tab('show');
                }
            });

            // Función para retroceder al tab anterior
            $('.prev-tab').click(function() {
                var currentTab = $(this).closest('.tab-pane').attr('id');
                var prevTab = $(this).data('tab');

                $('#' + currentTab).removeClass('show active');
                $('#' + prevTab).addClass('show active');
                $('#myTab a[href="#' + prevTab + '"]').tab('show');
            });

            // Función para validar campos requeridos en el tab actual
            function validateTab(tabId) {
                var isValid = true;

                $('#' + tabId + ' [required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                return isValid;
            }
        });
    </script>
@endsection
