@php
    $esAdmin = auth()->user()?->hasAnyRole(['super-admin', 'admin', 'adminB', 'secretaria']);
    $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';
@endphp
@extends($layout)
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">
                <i class="fas {{ $esAdmin ? 'fa-user-edit text-primary' : 'fa-graduation-cap' }} mr-1"></i>
                {{ $esAdmin ? 'Editar datos y matrícula:' : 'Actualizar matrícula:' }}
                <strong>{{ $alumno->apellidos }} {{ $alumno->nombres }}</strong>
            </h4>
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
                @if ($esAdmin)
                    <div class="alert alert-info text-center py-2 mb-3 shadow-sm" role="alert" style="font-size:0.9rem;">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Vista de administración unificada:</strong> Puedes editar datos de la cuenta de usuario (User), información personal de la ficha y número de comprobante de matrícula. Al guardar, todos los registros se actualizan automáticamente.
                    </div>
                @else
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
                @endif
            </div>
            <div class="col-lg-12 card pt-3 pb-3 border border-primary">
                <form action="{{ route('alumnos.update', ['alumno' => $alumno->id]) }}" method="POST" enctype="multipart/form-data" @if ($esAdmin) novalidate @endif>
                    @csrf
                    @method('PUT')
                    @if ($esAdmin)
                        <div class="container-fluid mt-3">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card bg-light border-0 shadow-sm" style="border-left: 4px solid #4e73df !important;">
                                        <div class="card-body p-3">
                                            <h6 class="text-primary font-weight-bold mb-3">
                                                <i class="fas fa-user-circle mr-1"></i> Datos de la cuenta de usuario (User) y Estado Académico
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_nombres" class="small font-weight-bold">Nombres:</label>
                                                    <input type="text" name="nombres" id="admin_nombres"
                                                        class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                                                        value="{{ old('nombres', $alumno->nombres ?? $usuarioEstudiante?->name) }}"
                                                        placeholder="Nombres">
                                                    @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_apellidos" class="small font-weight-bold">Apellidos:</label>
                                                    <input type="text" name="apellidos" id="admin_apellidos"
                                                        class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                                                        value="{{ old('apellidos', $alumno->apellidos ?? $usuarioEstudiante?->apellidos) }}"
                                                        placeholder="Apellidos">
                                                    @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label for="admin_dni" class="small font-weight-bold">DNI:</label>
                                                    <input type="text" name="dni" id="admin_dni"
                                                        class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                                        value="{{ old('dni', $alumno->dni ?? $usuarioEstudiante?->dni) }}"
                                                        placeholder="DNI">
                                                    @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label for="admin_email" class="small font-weight-bold">Correo electrónico:</label>
                                                    <input type="email" name="email" id="admin_email"
                                                        class="form-control form-control-sm @error('email') is-invalid @enderror"
                                                        value="{{ old('email', $usuarioEstudiante?->email ?? $alumno->email) }}"
                                                        placeholder="correo@ejemplo.com">
                                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_condicion" class="small font-weight-bold">Condición:</label>
                                                    <select name="condicion" id="admin_condicion" class="form-control form-control-sm @error('condicion') is-invalid @enderror">
                                                        <option value="">Seleccionar condición</option>
                                                        @foreach ($condiciones ?? ['Regular', 'Beca Continua', 'Beca 18', 'Beca Puklla', 'Reincorporación', 'Egresado'] as $cond)
                                                            <option value="{{ $cond }}" {{ old('condicion', $usuarioEstudiante?->condicion ?? '') === $cond ? 'selected' : '' }}>
                                                                {{ $cond }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('condicion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_beca" class="small font-weight-bold">¿Becado?:</label>
                                                    <select name="beca" id="admin_beca" class="form-control form-control-sm @error('beca') is-invalid @enderror">
                                                        <option value="0" {{ (int) old('beca', $usuarioEstudiante?->beca ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                                        <option value="1" {{ (int) old('beca', $usuarioEstudiante?->beca ?? 0) === 1 ? 'selected' : '' }}>Sí (Beca activa)</option>
                                                    </select>
                                                    @error('beca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_programa_selector" class="small font-weight-bold">Programa:</label>
                                                    <select name="programa_id" id="admin_programa_selector" class="form-control form-control-sm @error('programa_id') is-invalid @enderror">
                                                        <option value="">Seleccionar programa</option>
                                                        @foreach ($programas ?? [] as $prog)
                                                            <option value="{{ $prog->id }}" {{ (int) old('programa_id', $alumno->programa_id ?? $usuarioEstudiante?->programa_id) === (int) $prog->id ? 'selected' : '' }}>
                                                                {{ $prog->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('programa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_ciclo_selector" class="small font-weight-bold">Ciclo:</label>
                                                    <select name="ciclo_id" id="admin_ciclo_selector" class="form-control form-control-sm @error('ciclo_id') is-invalid @enderror">
                                                        <option value="">Seleccionar ciclo</option>
                                                        @php
                                                            $programaIdActual = old('programa_id', $alumno->programa_id ?? $usuarioEstudiante?->programa_id);
                                                            $ciclosDisponibles = ($ciclos ?? $ciclosPorPrograma ?? collect())->filter(fn($c) => (int)$c->programa_id === (int)$programaIdActual || (int)$c->id === (int)old('ciclo_id', $alumno->ciclo_id ?? $usuarioEstudiante?->ciclo_id));
                                                        @endphp
                                                        @foreach ($ciclosDisponibles as $cic)
                                                            <option value="{{ $cic->id }}" {{ (int) old('ciclo_id', $alumno->ciclo_id ?? $usuarioEstudiante?->ciclo_id) === (int) $cic->id ? 'selected' : '' }}>
                                                                {{ $cic->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('ciclo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_password" class="small font-weight-bold">Nueva contraseña:</label>
                                                    <input type="password" name="password" id="admin_password" autocomplete="new-password"
                                                        class="form-control form-control-sm @error('password') is-invalid @enderror"
                                                        placeholder="Dejar vacía para mantener">
                                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="admin_foto" class="small font-weight-bold">Foto del estudiante:</label>
                                                    <input type="file" name="foto" id="admin_foto" accept="image/*"
                                                        class="form-control-file small @error('foto') is-invalid @enderror">
                                                    @if ($usuarioEstudiante?->foto)
                                                        <small class="text-muted d-block mt-1">
                                                            Foto actual: <a href="{{ asset('img/estudiantes/' . $usuarioEstudiante->foto) }}" target="_blank" class="text-primary font-weight-bold"><i class="fas fa-image mr-1"></i> Ver foto</a>
                                                        </small>
                                                    @endif
                                                    @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @unless ($esAdmin)
                        <div class="container-fluid mt-2">
                            <div class="alert alert-light border small mb-0">
                                <strong>Programa y ciclo:</strong>
                                {{ $alumno->programa->nombre ?? '—' }}
                                —
                                {{ $alumno->ciclo->nombre ?? '—' }}
                                <span class="text-muted">(solo lectura; el administrador puede cambiarlos en su panel.)</span>
                            </div>
                        </div>
                    @endunless
                    <div class="container-fluid mt-3">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label for="numero">Número:</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('numero') is-invalid @enderror"
                                    id="numero" name="numero" value="{{ old('numero', $alumno->numero) }}" {{ $esAdmin ? '' : 'required' }}>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="numero_referencia">Número de Referencia:</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('numero_referencia') is-invalid @enderror"
                                    id="numero_referencia" name="numero_referencia"
                                    value="{{ old('numero_referencia', $alumno->numero_referencia) }}" {{ $esAdmin ? '' : 'required' }}>
                                @error('numero_referencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="num_hijos">Número de Hijos:</label>
                                <input type="number"
                                    class="form-control form-control-sm @error('num_hijos') is-invalid @enderror"
                                    id="num_hijos" name="num_hijos" value="{{ old('num_hijos', $alumno->num_hijos ?? '') }}"
                                    {{ $esAdmin ? '' : 'required' }}>
                                @error('num_hijos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="num_comprobante">Número de boleta / Voucher:
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
                                        value="{{ old('num_comprobante', $matriculaActual?->comprobante ?: $alumno->num_comprobante) }}"
                                        {{ $esAdmin ? '' : (stripos($alumno->num_comprobante, 'beca') !== false ? 'readonly' : 'required') }}>
                                </div>
                                @if ($matriculaActual)
                                    <small class="form-text text-muted">
                                        Matrícula: {{ $periodoActual->nombre ?? 'Periodo actual' }}
                                        @if ($matriculaActual->voucher_verificado)
                                            <span class="badge badge-success ml-1"><i class="fas fa-check-circle mr-1"></i> Verificado</span>
                                        @else
                                            <span class="badge badge-warning ml-1"><i class="fas fa-clock mr-1"></i> Por verificar</span>
                                        @endif
                                        @if ($matriculaActual->ficha_enviada_at)
                                            <span class="badge badge-info ml-1"><i class="fas fa-paper-plane mr-1"></i> Ficha enviada</span>
                                        @endif
                                    </small>
                                @endif
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
                                        value="{{ old('fecha_nacimiento', $alumno->fechaNacimientoResueltaParaInput() ?? '') }}"
                                        {{ $esAdmin ? '' : 'required' }}>
                                </div>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="genero">Género:</label>
                                <div class="input-group">
                                    <select class="form-control form-control-sm @error('genero') is-invalid @enderror"
                                        id="genero" name="genero" {{ $esAdmin ? '' : 'required' }}>
                                        <option value="" {{ empty(old('genero', $alumno->genero)) ? 'selected' : '' }}>-- Selecciona --</option>
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
                                    <option value="" {{ $esAdmin ? '' : 'disabled' }}
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
                                <select id="departamento" name="departamento" class="form-control form-control-sm" {{ $esAdmin ? '' : 'required' }}>
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
                                <select id="provincia" name="provincia" class="form-control form-control-sm" {{ $esAdmin ? '' : 'required' }}>
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
                                <select id="distrito" name="distrito" class="form-control form-control-sm" {{ $esAdmin ? '' : 'required' }}>
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
                                    name="direccion" value="{{ old('direccion', $alumno->direccion) }}" {{ $esAdmin ? '' : 'required' }}>
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
                    </div>
                </form>
            </div>
        </div>
    </div>
    @unless ($esAdmin)
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <img src="{{ asset('img/novedades/Foto-voucher-2024-II-2.webp') }}" alt="">
                </div>
            </div>
        </div>
    @endunless
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
    @hasanyrole('super-admin|admin|adminB')
        <script>
            (function() {
                var base = @json(url('/obtener-ciclos'));
                var programa = document.getElementById('admin_programa_selector');
                var ciclo = document.getElementById('admin_ciclo_selector');
                if (!programa || !ciclo) return;

                function cargarCiclos(programaId, selectedCicloId) {
                    if (!programaId) return;
                    ciclo.disabled = true;
                    ciclo.innerHTML = '<option value="">Cargando…</option>';
                    fetch(base + '/' + programaId, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            ciclo.innerHTML = '';
                            if (!data.length) {
                                ciclo.innerHTML = '<option value="">No hay ciclos</option>';
                                ciclo.disabled = true;
                                return;
                            }
                            data.forEach(function(c) {
                                var opt = document.createElement('option');
                                opt.value = c.id;
                                opt.textContent = c.nombre;
                                if (String(c.id) === String(selectedCicloId)) opt.selected = true;
                                ciclo.appendChild(opt);
                            });
                            ciclo.disabled = false;
                        })
                        .catch(function() {
                            ciclo.innerHTML = '<option value="">Error al cargar</option>';
                            ciclo.disabled = true;
                        });
                }

                programa.addEventListener('change', function() {
                    cargarCiclos(programa.value, '');
                });
            })();
        </script>
    @endhasanyrole
@endsection
