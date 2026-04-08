@extends('layouts.app')
@section('titulo', 'Formulario de Inscripción PPD')

@section('content')
    <main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card mt-3 mb-3">
                        <h5 class="text-center mt-3 mb-2">
                            Formulario de Inscripción – Profesionalización Docente 2026
                        </h5>
                        <div class="mx-auto mb-2"
                            style="width: 30px; height: 4px; background-color: #4e73df; border-radius: 5px;"></div>
                        <p class="text-center">
                            Los campos con (<span class="text-danger">*</span>) son obligatorios.
                        </p>
                        {{-- errores --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-body">
                            <form action="{{ route('postulantes.ppd.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Programa: <span class="text-danger">*</span><br>
                                            <small>Inscribirse en el programa de misma
                                                mención. No de Inicial a Primaria o de Primaria a Primaria
                                                EIB o viceversa.</small></label>
                                        <select name="programa" class="form-control form-control-sm" required>
                                            <option value="" selected disabled>-- Seleccione programa de su misma
                                                mención --</option>

                                            <option value="Educación Inicial"
                                                {{ old('programa') == 'Educación Inicial' ? 'selected' : '' }}>
                                                Educación Inicial
                                            </option>

                                            <option value="Educación Primaria"
                                                {{ old('programa') == 'Educación Primaria' ? 'selected' : '' }}>
                                                Educación Primaria
                                            </option>

                                            <option value="Educación Primaria Intercultural (EIB)"
                                                {{ old('programa') == 'Educación Primaria Intercultural (EIB)' ? 'selected' : '' }}>
                                                Educación Primaria Intercultural (EIB)
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Veces que postula pukllasunchis: <span
                                                class="text-danger">*</span></label><br><br>
                                        <input type="text" name="vecesPostulo"
                                            class="form-control form-control-sm @error('vecesPostulo') is-invalid @enderror"
                                            value="{{ old('vecesPostulo') }}" required>
                                        @error('vecesPostulo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ================= DATOS PERSONALES ================= --}}
                                <h3 class="mb-3 mt-4"
                                    style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">Datos
                                    personales<span
                                        style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                                </h3>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email: <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control form-control-sm"
                                            value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="dni" class="form-label">DNI: <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control @error('dni') is-invalid @enderror"
                                                id="dni" name="dni" maxlength="8" placeholder="Ingrese DNI..."
                                                required value="{{ old('dni') }}">
                                            {{-- <button type="button" id="btn-consultar" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Consultar
                                            </button> --}}
                                        </div>
                                        @error('dni')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="nombres" class="form-label">Nombre(s): <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                                            id="nombres" name="nombres" value="{{ old('nombres') }}"
                                            placeholder="Ingrese nombres..." required>
                                        <small class="form-text text-muted">Puede escribir manualmente o usar el botón
                                            Consultar</small>
                                        @error('nombres')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="apellidos" class="form-label">Apellidos: <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                                            id="apellidos" name="apellidos" value="{{ old('apellidos') }}"
                                            placeholder="Ingrese apellidos completos..." required>
                                        <small class="form-text text-muted">Puede escribir manualmente o usar el botón
                                            Consultar</small>
                                        @error('apellidos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- <div class="col-md-4 mb-3">
                                        <label for="nombres" class="form-label">Nombre(s): <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                                            id="nombres" name="nombres" value="{{ old('nombres') }}" readonly
                                            style="background-color: #f8f9fa; cursor: not-allowed;" required>
                                        <small class="form-text text-muted">Se llena automáticamente al consultar
                                            DNI</small>
                                        @error('nombres')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="apellidos" class="form-label">Apellidos: <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                                            id="apellidos" name="apellidos" value="{{ old('apellidos') }}" readonly
                                            style="background-color: #f8f9fa; cursor: not-allowed;" required>
                                        <small class="form-text text-muted">Se llena automáticamente al consultar
                                            DNI</small>
                                        @error('apellidos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    {{-- Inputs ocultos (para almacenar por separado si lo necesitas luego) --}}
                                    <input type="hidden" id="apellido_paterno" name="apellido_paterno">
                                    <input type="hidden" id="apellido_materno" name="apellido_materno">
                                    {{-- Campo oculto para indicar si el DNI fue verificado --}}
                                    <input type="hidden" id="dni_verificado" name="dni_verificado" value="0">

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Género:</label>
                                        <select name="genero" class="form-control form-control-sm">
                                            <option value="">-- Seleccione --</option>
                                            <option value="Masculino"
                                                {{ old('genero') == 'Masculino' ? 'selected' : '' }}>
                                                Masculino
                                            </option>
                                            <option value="Femenino" {{ old('genero') == 'Femenino' ? 'selected' : '' }}>
                                                Femenino
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Teléfono: <span class="text-danger">*</span></label>
                                        <input type="text" name="telefono" class="form-control form-control-sm"
                                            value="{{ old('telefono') }}" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Domicilio: <small>(Detallar departamento, provincia,
                                                distrito)</small></label>
                                        <input type="text" name="domicilio" class="form-control form-control-sm"
                                            value="{{ old('domicilio') }}">
                                    </div>


                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Estado civil <span class="text-danger">*</span></label>
                                        <select name="estadoCivil"
                                            class="form-select form-select-sm @error('estadoCivil') is-invalid @enderror"
                                            required>
                                            <option value="">-- Seleccione --</option>
                                            <option value="soltero"
                                                {{ old('estadoCivil') == 'soltero' ? 'selected' : '' }}>Soltero</option>
                                            <option value="casado" {{ old('estadoCivil') == 'casado' ? 'selected' : '' }}>
                                                Casado</option>
                                            <option value="viudo" {{ old('estadoCivil') == 'viudo' ? 'selected' : '' }}>
                                                Viudo</option>
                                            <option value="divorciado"
                                                {{ old('estadoCivil') == 'divorciado' ? 'selected' : '' }}>Divorciado
                                            </option>
                                            <option value="separado"
                                                {{ old('estadoCivil') == 'separado' ? 'selected' : '' }}>Separado</option>
                                        </select>
                                        @error('estadoCivil')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Fecha de nacimiento <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="fecha_nacimiento"
                                            class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
                                            value="{{ old('fecha_nacimiento') }}" required>
                                        @error('fecha_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Edad: <span class="text-danger">*</span></label>
                                        <input type="number" name="edad"
                                            class="form-control form-control-sm @error('edad') is-invalid @enderror"
                                            value="{{ old('edad') }}" required>
                                        @error('edad')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Lengua materna <span
                                                class="text-danger">*</span></label>
                                        <select name="lengua_1" id="lengua_1"
                                            class="form-select form-select-sm @error('lengua_1') is-invalid @enderror"
                                            required>
                                            <option value="" selected disabled>-- Seleccione --</option>
                                            <option value="Castellano"
                                                {{ old('lengua_1') == 'Castellano' ? 'selected' : '' }}>Castellano</option>
                                            <option value="Quechua" {{ old('lengua_1') == 'Quechua' ? 'selected' : '' }}>
                                                Quechua</option>
                                            <option value="Aymara" {{ old('lengua_1') == 'Aymara' ? 'selected' : '' }}>
                                                Aymara</option>
                                            <option value="Otro" {{ old('lengua_1') == 'Otro' ? 'selected' : '' }}>Otro
                                            </option>
                                        </select>
                                        @error('lengua_1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Segunda lengua</label>
                                        <select name="lengua_2" id="lengua_2"
                                            class="form-select form-select-sm @error('lengua_2') is-invalid @enderror">
                                            <option value="" selected disabled>-- Seleccione --</option>
                                            <option value="Castellano"
                                                {{ old('lengua_2') == 'Castellano' ? 'selected' : '' }}>Castellano</option>
                                            <option value="Quechua" {{ old('lengua_2') == 'Quechua' ? 'selected' : '' }}>
                                                Quechua</option>
                                            <option value="Aymara" {{ old('lengua_2') == 'Aymara' ? 'selected' : '' }}>
                                                Aymara</option>
                                            <option value="Otro" {{ old('lengua_2') == 'Otro' ? 'selected' : '' }}>Otro
                                            </option>
                                        </select>
                                        @error('lengua_2')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- ================= DATOS PERSONALES ================= --}}
                                    <h3 class="mb-3 mt-4"
                                        style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                        Procedencia
                                        <span
                                            style="position: absolute;left: 0.5em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                                    </h3>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Lugar de nacimiento:<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="lugar_nacimiento"
                                            class="form-control form-control-sm @error('lugar_nacimiento') is-invalid @enderror"
                                            value="{{ old('lugar_nacimiento') }}" required>
                                        @error('lugar_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- ================= NACIMIENTO ================= --}}
                                    <div class="col-lg-4">
                                        <label class="form-label">Departamento de nacimiento:</label>
                                        <select id="departamento_nacimiento_select"
                                            class="form-select form-select-sm @error('departamento_nacimiento') is-invalid @enderror">
                                            <option value="">-- Selecciona --</option>
                                            @foreach ($departamentos as $dep)
                                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="departamento_nacimiento" id="departamento_nacimiento"
                                            value="{{ old('departamento_nacimiento') }}">
                                        @error('departamento_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-2">
                                        <div class="mb-3">
                                            <label class="form-label">Provincia de nacimiento:</label>
                                            <select id="provincia_nacimiento_select"
                                                class="form-select form-select-sm @error('provincia_nacimiento') is-invalid @enderror"
                                                disabled>
                                                <option value="">-- Selecciona un departamento --</option>
                                            </select>

                                            <input type="hidden" name="provincia_nacimiento" id="provincia_nacimiento"
                                                value="{{ old('provincia_nacimiento') }}">

                                            @error('provincia_nacimiento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-4 mb-2">
                                        <div class="mb-3">
                                            <label class="form-label">Distrito de nacimiento:</label>
                                            <select id="distrito_nacimiento_select"
                                                class="form-select form-select-sm @error('distrito_nacimiento') is-invalid @enderror"
                                                disabled>
                                                <option value="">-- Selecciona una provincia --</option>
                                            </select>

                                            <input type="hidden" name="distrito_nacimiento" id="distrito_nacimiento"
                                                value="{{ old('distrito_nacimiento') }}">

                                            @error('distrito_nacimiento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">¿Trabaja actualmente?</label>
                                        <input type="text" name="trabaja"
                                            class="form-control form-control-sm @error('trabaja') is-invalid @enderror"
                                            value="{{ old('trabaja') }}">
                                        @error('trabaja')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Lugar de trabajo:</label>
                                        <input type="text" name="lugar_trabajo"
                                            class="form-control form-control-sm @error('lugar_trabajo') is-invalid @enderror"
                                            value="{{ old('lugar_trabajo') }}">
                                        @error('lugar_trabajo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Cargo que desempeña:</label>
                                        <input type="text" name="cargo"
                                            class="form-control form-control-sm @error('cargo') is-invalid @enderror"
                                            value="{{ old('cargo') }}">
                                        @error('cargo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Número de hijos:</label>
                                        <input type="number" name="hijos" min="0"
                                            class="form-control form-control-sm @error('hijos') is-invalid @enderror"
                                            value="{{ old('hijos') }}">
                                        @error('hijos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <h3 class="mb-3 mt-4"
                                    style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                    Institución de procedencia<span
                                        style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                                </h3>

                                <div class="row">

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Carrera Profesional de Procedencia: <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="carrera"
                                            class="form-control form-control-sm @error('carrera') is-invalid @enderror"
                                            value="{{ old('carrera') }}" required>
                                        @error('carrera')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tipo de institución:</label>
                                        <input type="text" name="tipo_institucion"
                                            class="form-control form-control-sm" value="Instituto Pedagógico" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nombre de la institución:</label>
                                        <input type="text" name="nombre_institucion"
                                            class="form-control form-control-sm" value="{{ old('nombre_institucion') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Gestión:</label>
                                        <select name="gestion_institucion"
                                            class="form-select form-select-sm @error('gestion_institucion') is-invalid @enderror">
                                            <option value="">-- Selecciona --</option>
                                            <option value="Público"
                                                {{ old('gestion_institucion') == 'Público' ? 'selected' : '' }}>
                                                Público
                                            </option>
                                            <option value="Privado"
                                                {{ old('gestion_institucion') == 'Privado' ? 'selected' : '' }}>
                                                Privado
                                            </option>
                                        </select>

                                        @error('gestion_institucion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Dirección de Institución:</label>
                                        <input type="text" name="direccion_institucion"
                                            class="form-control form-control-sm"
                                            value="{{ old('direccion_institucion') }}">
                                    </div>
                                    <div class="form-group col-lg-4 mb-2">
                                        <div class="mb-3">
                                            <label for="departamento_institucion" class="form-label">Departamento</label>
                                            <select id="departamento_institucion_select"
                                                class="form-select form-select-sm @error('departamento_institucion') is-invalid @enderror">
                                                <option value="">-- Selecciona --</option>
                                                @foreach ($departamentos as $dep)
                                                    <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" id="departamento_institucion"
                                                name="departamento_institucion"
                                                value="{{ old('departamento_institucion') }}">

                                            @error('departamento_institucion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4 mb-2">
                                        <div class="mb-3">
                                            <label for="provincia_institucion" class="form-label">Provincia</label>
                                            <select id="provincia_institucion_select" name="provincia_institucion"
                                                class="form-select form-select-sm @error('provincia_institucion') is-invalid @enderror"
                                                disabled>
                                                <option value="">-- Selecciona un departamento --</option>
                                            </select>
                                            <input type="hidden" id="provincia_institucion" name="provincia_institucion"
                                                value="{{ old('provincia_institucion') }}">
                                            @error('provincia_institucion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4 mb-2">
                                        <div class="mb-3">
                                            <label for="distrito_institucion" class="form-label">Distrito</label>
                                            <select id="distrito_institucion_select" name="distrito_institucion"
                                                class="form-select form-select-sm @error('distrito_institucion') is-invalid @enderror"
                                                disabled>
                                                <option value="">-- Selecciona una provincia --</option>
                                            </select>
                                            <input type="hidden" id="distrito_institucion" name="distrito_institucion"
                                                value="{{ old('distrito_institucion') }}">
                                            @error('distrito_institucion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Año en el que concluyó carrera de procedencia:</label>
                                        <input type="number" name="anio_conclusion" class="form-control form-control-sm"
                                            min="1950" max="{{ date('Y') }}"
                                            value="{{ old('anio_conclusion') }}">
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">¿Cómo se enteró del programa?</label>
                                        <select name="medio_conocimiento"
                                            class="form-select form-select-sm @error('medio_conocimiento') is-invalid @enderror">
                                            <option value="">-- Seleccione --</option>

                                            <option value="Facebook"
                                                {{ old('medio_conocimiento') == 'Facebook' ? 'selected' : '' }}>
                                                Facebook
                                            </option>
                                            <option value="Instagram"
                                                {{ old('medio_conocimiento') == 'Instagram' ? 'selected' : '' }}>
                                                Instagram
                                            </option>
                                            <option value="YouTube"
                                                {{ old('medio_conocimiento') == 'YouTube' ? 'selected' : '' }}>
                                                YouTube
                                            </option>
                                            <option value="Página web"
                                                {{ old('medio_conocimiento') == 'Página web' ? 'selected' : '' }}>
                                                Página web
                                            </option>
                                            <option value="Amigos o familiares"
                                                {{ old('medio_conocimiento') == 'Amigos o familiares' ? 'selected' : '' }}>
                                                Amigos o familiares
                                            </option>
                                            <option value="Radio"
                                                {{ old('medio_conocimiento') == 'Radio' ? 'selected' : '' }}>
                                                Radio
                                            </option>
                                            <option value="Otros"
                                                {{ old('medio_conocimiento') == 'Otros' ? 'selected' : '' }}>
                                                Otros
                                            </option>
                                        </select>

                                        @error('medio_conocimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">
                                            En una frase, ¿por qué decidiste estudiar Educación?
                                        </label>
                                        <textarea name="opinionEespp" rows="2"
                                            class="form-control form-control-sm @error('opinionEespp') is-invalid @enderror"
                                            placeholder="Ejemplo: Porque quiero contribuir a la formación de las nuevas generaciones.">{{ old('opinionEespp') }}</textarea>

                                        @error('opinionEespp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <h3 class="mb-3 mt-4"
                                    style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                    Datos adjuntos<span
                                        style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                                </h3>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">DNI <small>(imagen o PDF)</small>: <span
                                                class="text-danger">*</span></label>
                                        <input type="file" name="dni_adjunto" required
                                            accept="image/*,application/pdf"
                                            class="form-control form-control-sm @error('dni_adjunto') is-invalid @enderror">
                                        @error('dni_adjunto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Certificado de estudios <small>(imagen o PDF)</small>:
                                        </label>
                                        <input type="file" name="certificado" accept="image/*,application/pdf"
                                            class="form-control form-control-sm @error('certificado') is-invalid @enderror">

                                        @error('certificado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Foto del postulante:<span
                                                class="text-danger">*</span></label>
                                        <input type="file" name="foto" accept="image/*"
                                            class="form-control form-control-sm @error('foto') is-invalid @enderror" required>

                                        @error('foto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Título (imagen o PDF) <span
                                                class="text-danger">*</span></label>
                                        <input type="file" name="titulo" accept="image/*,application/pdf"
                                            class="form-control form-control-sm @error('titulo') is-invalid @enderror"
                                            required>

                                        @error('titulo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Voucher de pago <span
                                                class="text-danger">*</span></label>
                                        <input type="file" name="voucher" required accept="image/*,application/pdf"
                                            class="form-control form-control-sm @error('voucher') is-invalid @enderror">
                                        @error('voucher')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-lg-12 mb-3 mt-2">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input @error('declaracion_veracidad') is-invalid @enderror"
                                                type="checkbox" id="declaracion_veracidad" name="declaracion_veracidad"
                                                value="1" {{ old('declaracion_veracidad') ? 'checked' : '' }}
                                                required>
                                            <label class="form-check-label" for="declaracion_veracidad">
                                                Declaro bajo juramento que la información registrada expresa la verdad.
                                                <span class="text-danger">*</span>
                                            </label>
                                            @error('declaracion_veracidad')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary mt-3">
                                        Registrar postulación
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnConsultar = document.getElementById('btn-consultar');
            const dniInput = document.getElementById('dni');
            const nombresInput = document.getElementById('nombres');
            const apellidosInput = document.getElementById('apellidos');
            const dniVerificadoInput = document.getElementById('dni_verificado');
            const apellidoPaternoInput = document.getElementById('apellido_paterno');
            const apellidoMaternoInput = document.getElementById('apellido_materno');

            // Estado inicial
            let dniVerificado = false;

            // Validación DNI al escribir - solo números
            dniInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');

                // Limitar a 8 dígitos
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }

                // Si cambia el DNI, resetear estado de verificación
                if (dniVerificado) {
                    dniVerificado = false;
                    dniVerificadoInput.value = '0';
                    dniInput.style.borderColor = '';
                    dniInput.style.backgroundColor = '';
                }
            });

            // Evento click en botón consultar
            btnConsultar.addEventListener('click', async () => {
                const dni = dniInput.value.trim();

                if (dni.length !== 8) {
                    alert('El DNI debe tener exactamente 8 dígitos');
                    dniInput.focus();
                    return;
                }

                if (!/^\d{8}$/.test(dni)) {
                    alert('El DNI debe contener solo números');
                    dniInput.focus();
                    return;
                }

                // Mostrar loading en el botón
                const originalHtml = btnConsultar.innerHTML;
                btnConsultar.innerHTML = '<i class="bi bi-hourglass-split"></i> Consultando...';
                btnConsultar.disabled = true;

                try {
                    const response = await fetch('{{ route('consulta.dni') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            dni: dni
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.data) {
                        // Preguntar al usuario si desea sobrescribir los datos actuales
                        const tieneDatosActuales = nombresInput.value.trim() || apellidosInput.value
                            .trim();

                        if (tieneDatosActuales) {
                            const confirmarSobrescritura = confirm(
                                'Ya hay datos ingresados en los campos de nombres y/o apellidos.\n' +
                                '¿Desea sobrescribirlos con los datos obtenidos del DNI?'
                            );

                            if (!confirmarSobrescritura) {
                                // Restaurar botón y salir
                                btnConsultar.innerHTML = originalHtml;
                                btnConsultar.disabled = false;
                                return;
                            }
                        }

                        // Llenar campos
                        const nombres = data.data.nombres ?? '';
                        const apellidoPaterno = data.data.apellido_paterno ?? '';
                        const apellidoMaterno = data.data.apellido_materno ?? '';

                        // Combinar apellidos
                        const apellidosCompletos = [apellidoPaterno, apellidoMaterno]
                            .filter(Boolean)
                            .join(' ');

                        // Asignar valores
                        nombresInput.value = nombres;
                        apellidosInput.value = apellidosCompletos;
                        apellidoPaternoInput.value = apellidoPaterno;
                        apellidoMaternoInput.value = apellidoMaterno;

                        // Marcar como verificado
                        dniVerificado = true;
                        dniVerificadoInput.value = '1';

                        // Cambiar estilo para indicar verificación exitosa
                        dniInput.style.borderColor = '#28a745';
                        dniInput.style.backgroundColor = '#f0fff0';

                        alert('Datos cargados correctamente desde RENIEC');
                    } else {
                        alert(
                            'No se encontró información para el DNI ingresado. Por favor, verifique el número.');
                    }

                } catch (error) {
                    console.error('Error en la consulta:', error);
                    alert('Error al consultar el DNI. Por favor, intente nuevamente.');
                } finally {
                    // Restaurar botón
                    btnConsultar.innerHTML = originalHtml;
                    btnConsultar.disabled = false;
                }
            });

            // Validar formulario antes de enviar
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const nombres = nombresInput.value.trim();
                    const apellidos = apellidosInput.value.trim();
                    const dni = dniInput.value.trim();

                    // Validaciones básicas
                    if (!nombres) {
                        e.preventDefault();
                        alert('El campo Nombres es obligatorio');
                        nombresInput.focus();
                        return false;
                    }

                    if (!apellidos) {
                        e.preventDefault();
                        alert('El campo Apellidos es obligatorio');
                        apellidosInput.focus();
                        return false;
                    }

                    if (dni.length > 0 && dni.length !== 8) {
                        e.preventDefault();
                        alert('El DNI debe tener 8 dígitos');
                        dniInput.focus();
                        return false;
                    }

                    // Si el DNI está completo pero no verificado, mostrar advertencia
                    if (dni.length === 8 && dniVerificadoInput.value === '0') {
                        const confirmar = confirm(
                            'El DNI ingresado no ha sido verificado con RENIEC.\n\n' +
                            '¿Desea continuar con el envío? (Puede hacer clic en "Consultar" para verificar)'
                        );

                        if (!confirmar) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script> --}}
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnConsultar = document.getElementById('btn-consultar');
            const dniInput = document.getElementById('dni');
            const nombresInput = document.getElementById('nombres');
            const apellidosInput = document.getElementById('apellidos');
            const dniVerificadoInput = document.getElementById('dni_verificado');
            const apellidoPaternoInput = document.getElementById('apellido_paterno');
            const apellidoMaternoInput = document.getElementById('apellido_materno');

            // Estado inicial
            let dniVerificado = false;

            // Validación DNI al escribir
            dniInput.addEventListener('input', function() {
                // Solo números
                this.value = this.value.replace(/\D/g, '');

                // Si cambia el DNI, se resetea la verificación
                if (dniVerificado) {
                    resetearCampos();
                }
            });

            // Evento click en botón consultar
            btnConsultar.addEventListener('click', async () => {
                const dni = dniInput.value.trim();

                if (dni.length !== 8) {
                    alert('El DNI debe tener exactamente 8 dígitos');
                    dniInput.focus();
                    return;
                }

                if (!/^\d{8}$/.test(dni)) {
                    alert('El DNI debe contener solo números');
                    dniInput.focus();
                    return;
                }

                // Mostrar loading en el botón
                const originalHtml = btnConsultar.innerHTML;
                btnConsultar.innerHTML = '<i class="bi bi-hourglass-split"></i> Consultando...';
                btnConsultar.disabled = true;

                try {
                    const response = await fetch('{{ route('consulta.dni') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            dni: dni
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.data) {
                        // Llenar campos
                        const nombres = data.data.nombres ?? '';
                        const apellidoPaterno = data.data.apellido_paterno ?? '';
                        const apellidoMaterno = data.data.apellido_materno ?? '';

                        // Combinar apellidos
                        const apellidosCompletos = [apellidoPaterno, apellidoMaterno]
                            .filter(Boolean)
                            .join(' ');

                        // Asignar valores
                        nombresInput.value = nombres;
                        apellidosInput.value = apellidosCompletos;
                        apellidoPaternoInput.value = apellidoPaterno;
                        apellidoMaternoInput.value = apellidoMaterno;

                        // Marcar como verificado
                        dniVerificado = true;
                        dniVerificadoInput.value = '1';

                        // Cambiar estilo para indicar verificación
                        dniInput.style.borderColor = '#28a745';
                        dniInput.style.backgroundColor = '#d4edda';


                    } else {
                        alert(
                            'No se encontró información para el DNI ingresado. Por favor, verifique el número.'
                        );
                        resetearCampos();
                    }

                } catch (error) {
                    console.error('Error en la consulta:', error);
                    alert('Error al consultar el DNI. Intente nuevamente.');
                    resetearCampos();
                } finally {
                    // Restaurar botón
                    btnConsultar.innerHTML = originalHtml;
                    btnConsultar.disabled = false;
                }
            });

            // Función para resetear campos
            function resetearCampos() {
                nombresInput.value = '';
                apellidosInput.value = '';
                apellidoPaternoInput.value = '';
                apellidoMaternoInput.value = '';
                dniVerificado = false;
                dniVerificadoInput.value = '0';

                // Restaurar estilo del DNI
                dniInput.style.borderColor = '';
                dniInput.style.backgroundColor = '';
            }

            // Prevenir edición manual de campos readonly
            [nombresInput, apellidosInput].forEach(input => {
                input.addEventListener('keydown', function(e) {
                    e.preventDefault();
                    alert('Este campo se llena automáticamente al consultar el DNI');
                });

                input.addEventListener('click', function() {
                    if (!dniVerificado) {
                        alert('Por favor, consulte primero el DNI para cargar los datos');
                        dniInput.focus();
                    }
                });
            });

            // Validar formulario antes de enviar
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!dniVerificado) {
                        e.preventDefault();
                        const confirmar = confirm(
                            'El DNI no ha sido verificado. ¿Desea continuar sin verificación?\n\n' +
                            'Recomendación: Haga clic en "Consultar" para verificar el DNI y cargar los datos automáticamente.'
                        );

                        if (confirmar) {
                            // Permitir envío sin verificación
                            dniVerificadoInput.value = '0';
                            this.submit();
                        }
                    }
                });
            }
        });
    </script> --}}

    <script>
        $(document).ready(function() {

            // ===============================
            // CARGAR PROVINCIAS
            // ===============================
            $('#departamento_institucion_select').on('change', function() {

                const departamentoId = $(this).val();
                const departamentoTexto = $(this).find('option:selected').text();

                // Guardar texto en hidden
                $('#departamento_institucion').val(departamentoTexto);

                // Resetear provincia y distrito
                $('#provincia_institucion_select')
                    .prop('disabled', true)
                    .html('<option>Cargando...</option>');

                $('#distrito_institucion_select')
                    .prop('disabled', true)
                    .html('<option>-- Selecciona una provincia --</option>');

                if (!departamentoId) return;

                $.get('/get-provincias/' + departamentoId, function(provincias) {

                    let options = '<option value="">-- Selecciona --</option>';
                    provincias.forEach(function(prov) {
                        options += `<option value="${prov.id}">${prov.nombre}</option>`;
                    });

                    $('#provincia_institucion_select')
                        .html(options)
                        .prop('disabled', false);
                });
            });

            // ===============================
            // CARGAR DISTRITOS
            // ===============================
            $('#provincia_institucion_select').on('change', function() {

                const provinciaId = $(this).val();
                const provinciaTexto = $(this).find('option:selected').text();

                // Guardar texto en hidden
                $('#provincia_institucion').val(provinciaTexto);

                $('#distrito_institucion_select')
                    .prop('disabled', true)
                    .html('<option>Cargando...</option>');

                if (!provinciaId) return;

                $.get('/get-distritos/' + provinciaId, function(distritos) {

                    let options = '<option value="">-- Selecciona --</option>';
                    distritos.forEach(function(dist) {
                        options += `<option value="${dist.id}">${dist.nombre}</option>`;
                    });

                    $('#distrito_institucion_select')
                        .html(options)
                        .prop('disabled', false);
                });
            });

            // ===============================
            // GUARDAR DISTRITO
            // ===============================
            $('#distrito_institucion_select').on('change', function() {
                const texto = $(this).find('option:selected').text();
                $('#distrito_institucion').val(texto);
            });

        });
    </script>
    <script>
        $(document).ready(function() {

            // ===============================
            // NACIMIENTO → PROVINCIAS
            // ===============================
            $('#departamento_nacimiento_select').on('change', function() {

                const departamentoId = $(this).val();
                const texto = $(this).find('option:selected').text();

                $('#departamento_nacimiento').val(texto);

                $('#provincia_nacimiento_select')
                    .prop('disabled', true)
                    .html('<option>Cargando...</option>');

                $('#distrito_nacimiento_select')
                    .prop('disabled', true)
                    .html('<option>-- Selecciona una provincia --</option>');

                if (!departamentoId) return;

                $.get('/get-provincias/' + departamentoId, function(provincias) {

                    let options = '<option value="">-- Selecciona --</option>';
                    provincias.forEach(function(prov) {
                        options += `<option value="${prov.id}">${prov.nombre}</option>`;
                    });

                    $('#provincia_nacimiento_select')
                        .html(options)
                        .prop('disabled', false);
                });
            });

            // ===============================
            // NACIMIENTO → DISTRITOS
            // ===============================
            $('#provincia_nacimiento_select').on('change', function() {

                const provinciaId = $(this).val();
                const texto = $(this).find('option:selected').text();

                $('#provincia_nacimiento').val(texto);

                $('#distrito_nacimiento_select')
                    .prop('disabled', true)
                    .html('<option>Cargando...</option>');

                if (!provinciaId) return;

                $.get('/get-distritos/' + provinciaId, function(distritos) {

                    let options = '<option value="">-- Selecciona --</option>';
                    distritos.forEach(function(dist) {
                        options += `<option value="${dist.id}">${dist.nombre}</option>`;
                    });

                    $('#distrito_nacimiento_select')
                        .html(options)
                        .prop('disabled', false);
                });
            });

            // ===============================
            // GUARDAR DISTRITO NACIMIENTO
            // ===============================
            $('#distrito_nacimiento_select').on('change', function() {
                const texto = $(this).find('option:selected').text();
                $('#distrito_nacimiento').val(texto);
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const lengua1 = document.getElementById('lengua_1');
            const lengua2 = document.getElementById('lengua_2');

            function actualizarLengua2() {
                const valorSeleccionado = lengua1.value;

                // Habilitar todas primero
                Array.from(lengua2.options).forEach(option => {
                    option.disabled = false;
                });

                // Deshabilitar la opción seleccionada en lengua_1
                if (valorSeleccionado) {
                    const opcion = Array.from(lengua2.options)
                        .find(opt => opt.value === valorSeleccionado);

                    if (opcion) {
                        opcion.disabled = true;

                        // Si estaba seleccionada, limpiarla
                        if (lengua2.value === valorSeleccionado) {
                            lengua2.value = '';
                        }
                    }
                }
            }

            // Ejecutar al cambiar
            lengua1.addEventListener('change', actualizarLengua2);

            // Ejecutar al cargar (para old())
            actualizarLengua2();
        });
    </script>



@endsection
