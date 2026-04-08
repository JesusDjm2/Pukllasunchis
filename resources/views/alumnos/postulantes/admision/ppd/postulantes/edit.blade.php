@extends('layouts.app')
@section('titulo', 'Editar Inscripción PPD')

@section('content')
<main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card mt-3 mb-3">
                    <h5 class="text-center mt-3 mb-2">
                        Editar Inscripción – Profesionalización Docente 2026
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

                    {{-- Mensaje de éxito --}}
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card-body">
                        <form action="{{ route('postulantes.ppd.update', $postulante) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Programa: <span class="text-danger">*</span><br>
                                        <small>Inscribirse en el programa de misma
                                            mención. No de Inicial a Primaria o de Primaria a Primaria
                                            EIB o viceversa.</small></label>
                                    <select name="programa" class="form-control form-control-sm" required>
                                        <option value="" disabled>-- Seleccione programa de su misma mención --</option>
                                        <option value="Educación Inicial" {{ old('programa', $postulante->programa) == 'Educación Inicial' ? 'selected' : '' }}>
                                            Educación Inicial
                                        </option>
                                        <option value="Educación Primaria" {{ old('programa', $postulante->programa) == 'Educación Primaria' ? 'selected' : '' }}>
                                            Educación Primaria
                                        </option>
                                        <option value="Educación Primaria Intercultural (EIB)" {{ old('programa', $postulante->programa) == 'Educación Primaria Intercultural (EIB)' ? 'selected' : '' }}>
                                            Educación Primaria Intercultural (EIB)
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Veces que postula pukllasunchis: <span class="text-danger">*</span></label>
                                    <input type="text" name="vecesPostulo"
                                        class="form-control form-control-sm @error('vecesPostulo') is-invalid @enderror"
                                        value="{{ old('vecesPostulo', $postulante->vecesPostulo) }}" required>
                                    @error('vecesPostulo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- ================= DATOS PERSONALES ================= --}}
                            <h3 class="mb-3 mt-4"
                                style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                Datos personales
                                <span style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                            </h3>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Email: <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control form-control-sm"
                                        value="{{ old('email', $postulante->email) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="dni" class="form-label">DNI: <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control @error('dni') is-invalid @enderror"
                                            id="dni" name="dni" maxlength="8" placeholder="Ingrese DNI..."
                                            required value="{{ old('dni', $postulante->dni) }}">
                                    </div>
                                    @error('dni')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="nombres" class="form-label">Nombre(s): <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                                        id="nombres" name="nombres" value="{{ old('nombres', $postulante->nombres) }}"
                                        placeholder="Ingrese nombres..." required>
                                    @error('nombres')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="apellidos" class="form-label">Apellidos: <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                                        id="apellidos" name="apellidos" value="{{ old('apellidos', $postulante->apellidos) }}"
                                        placeholder="Ingrese apellidos completos..." required>
                                    @error('apellidos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Inputs ocultos (para almacenar por separado si lo necesitas luego) --}}
                                <input type="hidden" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $postulante->apellido_paterno ?? '') }}">
                                <input type="hidden" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $postulante->apellido_materno ?? '') }}">
                                <input type="hidden" id="dni_verificado" name="dni_verificado" value="1">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Género:</label>
                                    <select name="genero" class="form-control form-control-sm">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Masculino" {{ old('genero', $postulante->genero) == 'Masculino' ? 'selected' : '' }}>
                                            Masculino
                                        </option>
                                        <option value="Femenino" {{ old('genero', $postulante->genero) == 'Femenino' ? 'selected' : '' }}>
                                            Femenino
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Teléfono: <span class="text-danger">*</span></label>
                                    <input type="text" name="telefono" class="form-control form-control-sm"
                                        value="{{ old('telefono', $postulante->telefono) }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label>Domicilio: <small>(Detallar departamento, provincia, distrito)</small></label>
                                    <input type="text" name="domicilio" class="form-control form-control-sm"
                                        value="{{ old('domicilio', $postulante->domicilio) }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Estado civil <span class="text-danger">*</span></label>
                                    <select name="estadoCivil"
                                        class="form-select form-select-sm @error('estadoCivil') is-invalid @enderror"
                                        required>
                                        <option value="">-- Seleccione --</option>
                                        <option value="soltero" {{ old('estadoCivil', $postulante->estadoCivil) == 'soltero' ? 'selected' : '' }}>Soltero</option>
                                        <option value="casado" {{ old('estadoCivil', $postulante->estadoCivil) == 'casado' ? 'selected' : '' }}>Casado</option>
                                        <option value="viudo" {{ old('estadoCivil', $postulante->estadoCivil) == 'viudo' ? 'selected' : '' }}>Viudo</option>
                                        <option value="divorciado" {{ old('estadoCivil', $postulante->estadoCivil) == 'divorciado' ? 'selected' : '' }}>Divorciado</option>
                                        <option value="separado" {{ old('estadoCivil', $postulante->estadoCivil) == 'separado' ? 'selected' : '' }}>Separado</option>
                                    </select>
                                    @error('estadoCivil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_nacimiento"
                                        class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror"
                                        value="{{ old('fecha_nacimiento', $postulante->fecha_nacimiento ? \Carbon\Carbon::parse($postulante->fecha_nacimiento)->format('Y-m-d') : '') }}" required>
                                    @error('fecha_nacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Edad: <span class="text-danger">*</span></label>
                                    <input type="number" name="edad"
                                        class="form-control form-control-sm @error('edad') is-invalid @enderror"
                                        value="{{ old('edad', $postulante->edad) }}" required>
                                    @error('edad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Lengua materna <span class="text-danger">*</span></label>
                                    <select name="lengua_1" id="lengua_1"
                                        class="form-select form-select-sm @error('lengua_1') is-invalid @enderror"
                                        required>
                                        <option value="" disabled>-- Seleccione --</option>
                                        <option value="Castellano" {{ old('lengua_1', $postulante->lengua_1) == 'Castellano' ? 'selected' : '' }}>Castellano</option>
                                        <option value="Quechua" {{ old('lengua_1', $postulante->lengua_1) == 'Quechua' ? 'selected' : '' }}>Quechua</option>
                                        <option value="Aymara" {{ old('lengua_1', $postulante->lengua_1) == 'Aymara' ? 'selected' : '' }}>Aymara</option>
                                        <option value="Otro" {{ old('lengua_1', $postulante->lengua_1) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('lengua_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Segunda lengua</label>
                                    <select name="lengua_2" id="lengua_2"
                                        class="form-select form-select-sm @error('lengua_2') is-invalid @enderror">
                                        <option value="" disabled>-- Seleccione --</option>
                                        <option value="Castellano" {{ old('lengua_2', $postulante->lengua_2) == 'Castellano' ? 'selected' : '' }}>Castellano</option>
                                        <option value="Quechua" {{ old('lengua_2', $postulante->lengua_2) == 'Quechua' ? 'selected' : '' }}>Quechua</option>
                                        <option value="Aymara" {{ old('lengua_2', $postulante->lengua_2) == 'Aymara' ? 'selected' : '' }}>Aymara</option>
                                        <option value="Otro" {{ old('lengua_2', $postulante->lengua_2) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('lengua_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ================= PROCEDENCIA ================= --}}
                                <h3 class="mb-3 mt-4"
                                    style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                    Procedencia
                                    <span style="position: absolute;left: 0.5em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                                </h3>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Lugar de nacimiento:<span class="text-danger">*</span></label>
                                    <input type="text" name="lugar_nacimiento"
                                        class="form-control form-control-sm @error('lugar_nacimiento') is-invalid @enderror"
                                        value="{{ old('lugar_nacimiento', $postulante->lugar_nacimiento) }}" required>
                                    @error('lugar_nacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ================= NACIMIENTO SELECTS ================= --}}
                                <div class="col-lg-4">
                                    <label class="form-label">Departamento de nacimiento:</label>
                                    <select id="departamento_nacimiento_select"
                                        class="form-select form-select-sm @error('departamento_nacimiento') is-invalid @enderror">
                                        <option value="">-- Selecciona --</option>
                                        @foreach ($departamentos as $dep)
                                            <option value="{{ $dep->id }}" {{ old('departamento_nacimiento', $postulante->departamento_nacimiento) == $dep->nombre ? 'selected' : '' }}>
                                                {{ $dep->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="departamento_nacimiento" id="departamento_nacimiento"
                                        value="{{ old('departamento_nacimiento', $postulante->departamento_nacimiento) }}">
                                    @error('departamento_nacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4 mb-2">
                                    <div class="mb-3">
                                        <label class="form-label">Provincia de nacimiento:</label>
                                        <select id="provincia_nacimiento_select"
                                            class="form-select form-select-sm @error('provincia_nacimiento') is-invalid @enderror">
                                            <option value="">-- Selecciona un departamento --</option>
                                        </select>
                                        <input type="hidden" name="provincia_nacimiento" id="provincia_nacimiento"
                                            value="{{ old('provincia_nacimiento', $postulante->provincia_nacimiento) }}">
                                        @error('provincia_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-4 mb-2">
                                    <div class="mb-3">
                                        <label class="form-label">Distrito de nacimiento:</label>
                                        <select id="distrito_nacimiento_select"
                                            class="form-select form-select-sm @error('distrito_nacimiento') is-invalid @enderror">
                                            <option value="">-- Selecciona una provincia --</option>
                                        </select>
                                        <input type="hidden" name="distrito_nacimiento" id="distrito_nacimiento"
                                            value="{{ old('distrito_nacimiento', $postulante->distrito_nacimiento) }}">
                                        @error('distrito_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">¿Trabaja actualmente?</label>
                                    <input type="text" name="trabaja"
                                        class="form-control form-control-sm @error('trabaja') is-invalid @enderror"
                                        value="{{ old('trabaja', $postulante->trabaja) }}">
                                    @error('trabaja')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Lugar de trabajo:</label>
                                    <input type="text" name="lugar_trabajo"
                                        class="form-control form-control-sm @error('lugar_trabajo') is-invalid @enderror"
                                        value="{{ old('lugar_trabajo', $postulante->lugar_trabajo) }}">
                                    @error('lugar_trabajo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Cargo que desempeña:</label>
                                    <input type="text" name="cargo"
                                        class="form-control form-control-sm @error('cargo') is-invalid @enderror"
                                        value="{{ old('cargo', $postulante->cargo) }}">
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Número de hijos:</label>
                                    <input type="number" name="hijos" min="0"
                                        class="form-control form-control-sm @error('hijos') is-invalid @enderror"
                                        value="{{ old('hijos', $postulante->hijos) }}">
                                    @error('hijos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- ================= INSTITUCIÓN DE PROCEDENCIA ================= --}}
                            <h3 class="mb-3 mt-4"
                                style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                Institución de procedencia
                                <span style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                            </h3>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Carrera Profesional de Procedencia: <span class="text-danger">*</span></label>
                                    <input type="text" name="carrera"
                                        class="form-control form-control-sm @error('carrera') is-invalid @enderror"
                                        value="{{ old('carrera', $postulante->carrera) }}" required>
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
                                        class="form-control form-control-sm" value="{{ old('nombre_institucion', $postulante->nombre_institucion) }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gestión:</label>
                                    <select name="gestion_institucion"
                                        class="form-select form-select-sm @error('gestion_institucion') is-invalid @enderror">
                                        <option value="">-- Selecciona --</option>
                                        <option value="Público" {{ old('gestion_institucion', $postulante->gestion_institucion) == 'Público' ? 'selected' : '' }}>
                                            Público
                                        </option>
                                        <option value="Privado" {{ old('gestion_institucion', $postulante->gestion_institucion) == 'Privado' ? 'selected' : '' }}>
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
                                        value="{{ old('direccion_institucion', $postulante->direccion_institucion) }}">
                                </div>

                                <div class="form-group col-lg-4 mb-2">
                                    <div class="mb-3">
                                        <label for="departamento_institucion" class="form-label">Departamento</label>
                                        <select id="departamento_institucion_select"
                                            class="form-select form-select-sm @error('departamento_institucion') is-invalid @enderror">
                                            <option value="">-- Selecciona --</option>
                                            @foreach ($departamentos as $dep)
                                                <option value="{{ $dep->id }}" {{ old('departamento_institucion', $postulante->departamento_institucion) == $dep->nombre ? 'selected' : '' }}>
                                                    {{ $dep->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="departamento_institucion" name="departamento_institucion"
                                            value="{{ old('departamento_institucion', $postulante->departamento_institucion) }}">
                                        @error('departamento_institucion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-lg-4 mb-2">
                                    <div class="mb-3">
                                        <label for="provincia_institucion" class="form-label">Provincia</label>
                                        <select id="provincia_institucion_select" name="provincia_institucion"
                                            class="form-select form-select-sm @error('provincia_institucion') is-invalid @enderror">
                                            <option value="">-- Selecciona un departamento --</option>
                                        </select>
                                        <input type="hidden" id="provincia_institucion" name="provincia_institucion"
                                            value="{{ old('provincia_institucion', $postulante->provincia_institucion) }}">
                                        @error('provincia_institucion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-lg-4 mb-2">
                                    <div class="mb-3">
                                        <label for="distrito_institucion" class="form-label">Distrito</label>
                                        <select id="distrito_institucion_select" name="distrito_institucion"
                                            class="form-select form-select-sm @error('distrito_institucion') is-invalid @enderror">
                                            <option value="">-- Selecciona una provincia --</option>
                                        </select>
                                        <input type="hidden" id="distrito_institucion" name="distrito_institucion"
                                            value="{{ old('distrito_institucion', $postulante->distrito_institucion) }}">
                                        @error('distrito_institucion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Año en el que concluyó carrera de procedencia:</label>
                                    <input type="number" name="anio_conclusion" class="form-control form-control-sm"
                                        min="1950" max="{{ date('Y') }}"
                                        value="{{ old('anio_conclusion', $postulante->anio_conclusion) }}">
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">¿Cómo se enteró del programa?</label>
                                    <select name="medio_conocimiento"
                                        class="form-select form-select-sm @error('medio_conocimiento') is-invalid @enderror">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Facebook" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="Instagram" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="YouTube" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="Página web" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Página web' ? 'selected' : '' }}>Página web</option>
                                        <option value="Amigos o familiares" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Amigos o familiares' ? 'selected' : '' }}>Amigos o familiares</option>
                                        <option value="Radio" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Radio' ? 'selected' : '' }}>Radio</option>
                                        <option value="Otros" {{ old('medio_conocimiento', $postulante->medio_conocimiento) == 'Otros' ? 'selected' : '' }}>Otros</option>
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
                                        placeholder="Ejemplo: Porque quiero contribuir a la formación de las nuevas generaciones.">{{ old('opinionEespp', $postulante->opinionEespp) }}</textarea>
                                    @error('opinionEespp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- ================= DATOS ADJUNTOS ================= --}}
                            <h3 class="mb-3 mt-4"
                                style="color: #c78d40; font-weight: 500;font-size: 24px; position: relative;">
                                Datos adjuntos
                                <span style="position: absolute;left: 0.1em;bottom: -6px;width: 40px;height: 2px;background-color: #c78d40;"></span>
                            </h3>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">DNI <small>(imagen o PDF)</small>: <span class="text-danger">*</span></label>
                                    <input type="file" name="dni_adjunto" accept="image/*,application/pdf"
                                        class="form-control form-control-sm @error('dni_adjunto') is-invalid @enderror">
                                    @if($postulante->dni_adjunto)
                                        <small class="text-muted">
                                            Archivo actual: <a href="{{ asset($postulante->dni_adjunto) }}" target="_blank">Ver DNI</a>
                                        </small>
                                    @endif
                                    @error('dni_adjunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Certificado de estudios <small>(imagen o PDF)</small>:</label>
                                    <input type="file" name="certificado" accept="image/*,application/pdf"
                                        class="form-control form-control-sm @error('certificado') is-invalid @enderror">
                                    @if($postulante->certificado)
                                        <small class="text-muted">
                                            Archivo actual: <a href="{{ asset($postulante->certificado) }}" target="_blank">Ver Certificado</a>
                                        </small>
                                    @endif
                                    @error('certificado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Foto del postulante:<span class="text-danger">*</span></label>
                                    <input type="file" name="foto" accept="image/*"
                                        class="form-control form-control-sm @error('foto') is-invalid @enderror">
                                    @if($postulante->foto)
                                        <small class="text-muted">
                                            Archivo actual: <a href="{{ asset($postulante->foto) }}" target="_blank">Ver Foto</a>
                                        </small>
                                    @endif
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Título (imagen o PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="titulo" accept="image/*,application/pdf"
                                        class="form-control form-control-sm @error('titulo') is-invalid @enderror">
                                    @if($postulante->titulo)
                                        <small class="text-muted">
                                            Archivo actual: <a href="{{ asset($postulante->titulo) }}" target="_blank">Ver Título</a>
                                        </small>
                                    @endif
                                    @error('titulo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Voucher de pago <span class="text-danger">*</span></label>
                                    <input type="file" name="voucher" accept="image/*,application/pdf"
                                        class="form-control form-control-sm @error('voucher') is-invalid @enderror">
                                    @if($postulante->voucher)
                                        <small class="text-muted">
                                            Archivo actual: <a href="{{ asset($postulante->voucher) }}" target="_blank">Ver Voucher</a>
                                        </small>
                                    @endif
                                    @error('voucher')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>                                
                            </div>

                            <div class="text-center">
                                <a href="{{ route('postulantes.ppd.index') }}" class="btn btn-secondary mt-3">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary mt-3">
                                    Actualizar postulación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Scripts necesarios --}}
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

{{-- Script para los selects de ubicación --}}
<script>
    $(document).ready(function() {
        // Función para cargar provincias (nacimiento)
        function cargarProvinciasNacimiento(departamentoId, selectedValue = '') {
            if (!departamentoId) return;

            $.get('/get-provincias/' + departamentoId, function(provincias) {
                let options = '<option value="">-- Selecciona --</option>';
                provincias.forEach(function(prov) {
                    let selected = (prov.nombre == selectedValue) ? 'selected' : '';
                    options += `<option value="${prov.id}" ${selected}>${prov.nombre}</option>`;
                });

                $('#provincia_nacimiento_select')
                    .html(options)
                    .prop('disabled', false);

                // Si hay un valor seleccionado, cargar distritos
                if (selectedValue) {
                    let provinciaId = provincias.find(p => p.nombre == selectedValue)?.id;
                    if (provinciaId) {
                        cargarDistritosNacimiento(provinciaId, $('#distrito_nacimiento').val());
                    }
                }
            });
        }

        // Función para cargar distritos (nacimiento)
        function cargarDistritosNacimiento(provinciaId, selectedValue = '') {
            if (!provinciaId) return;

            $.get('/get-distritos/' + provinciaId, function(distritos) {
                let options = '<option value="">-- Selecciona --</option>';
                distritos.forEach(function(dist) {
                    let selected = (dist.nombre == selectedValue) ? 'selected' : '';
                    options += `<option value="${dist.id}" ${selected}>${dist.nombre}</option>`;
                });

                $('#distrito_nacimiento_select')
                    .html(options)
                    .prop('disabled', false);
            });
        }

        // Función para cargar provincias (institución)
        function cargarProvinciasInstitucion(departamentoId, selectedValue = '') {
            if (!departamentoId) return;

            $.get('/get-provincias/' + departamentoId, function(provincias) {
                let options = '<option value="">-- Selecciona --</option>';
                provincias.forEach(function(prov) {
                    let selected = (prov.nombre == selectedValue) ? 'selected' : '';
                    options += `<option value="${prov.id}" ${selected}>${prov.nombre}</option>`;
                });

                $('#provincia_institucion_select')
                    .html(options)
                    .prop('disabled', false);

                // Si hay un valor seleccionado, cargar distritos
                if (selectedValue) {
                    let provinciaId = provincias.find(p => p.nombre == selectedValue)?.id;
                    if (provinciaId) {
                        cargarDistritosInstitucion(provinciaId, $('#distrito_institucion').val());
                    }
                }
            });
        }

        // Función para cargar distritos (institución)
        function cargarDistritosInstitucion(provinciaId, selectedValue = '') {
            if (!provinciaId) return;

            $.get('/get-distritos/' + provinciaId, function(distritos) {
                let options = '<option value="">-- Selecciona --</option>';
                distritos.forEach(function(dist) {
                    let selected = (dist.nombre == selectedValue) ? 'selected' : '';
                    options += `<option value="${dist.id}" ${selected}>${dist.nombre}</option>`;
                });

                $('#distrito_institucion_select')
                    .html(options)
                    .prop('disabled', false);
            });
        }

        // ===============================
        // NACIMIENTO
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

            if (departamentoId) {
                cargarProvinciasNacimiento(departamentoId);
            }
        });

        $('#provincia_nacimiento_select').on('change', function() {
            const provinciaId = $(this).val();
            const texto = $(this).find('option:selected').text();
            $('#provincia_nacimiento').val(texto);

            $('#distrito_nacimiento_select')
                .prop('disabled', true)
                .html('<option>Cargando...</option>');

            if (provinciaId) {
                cargarDistritosNacimiento(provinciaId);
            }
        });

        $('#distrito_nacimiento_select').on('change', function() {
            const texto = $(this).find('option:selected').text();
            $('#distrito_nacimiento').val(texto);
        });

        // ===============================
        // INSTITUCIÓN
        // ===============================
        $('#departamento_institucion_select').on('change', function() {
            const departamentoId = $(this).val();
            const texto = $(this).find('option:selected').text();
            $('#departamento_institucion').val(texto);

            $('#provincia_institucion_select')
                .prop('disabled', true)
                .html('<option>Cargando...</option>');

            $('#distrito_institucion_select')
                .prop('disabled', true)
                .html('<option>-- Selecciona una provincia --</option>');

            if (departamentoId) {
                cargarProvinciasInstitucion(departamentoId);
            }
        });

        $('#provincia_institucion_select').on('change', function() {
            const provinciaId = $(this).val();
            const texto = $(this).find('option:selected').text();
            $('#provincia_institucion').val(texto);

            $('#distrito_institucion_select')
                .prop('disabled', true)
                .html('<option>Cargando...</option>');

            if (provinciaId) {
                cargarDistritosInstitucion(provinciaId);
            }
        });

        $('#distrito_institucion_select').on('change', function() {
            const texto = $(this).find('option:selected').text();
            $('#distrito_institucion').val(texto);
        });

        // ===============================
        // Cargar valores iniciales
        // ===============================
        @if($postulante->departamento_nacimiento)
            let deptoNacimiento = $('#departamento_nacimiento_select option').filter(function() {
                return $(this).text() == '{{ $postulante->departamento_nacimiento }}';
            }).val();
            if (deptoNacimiento) {
                $('#departamento_nacimiento_select').val(deptoNacimiento).trigger('change');
                setTimeout(function() {
                    $('#provincia_nacimiento_select option').filter(function() {
                        return $(this).text() == '{{ $postulante->provincia_nacimiento }}';
                    }).prop('selected', true).trigger('change');
                    
                    setTimeout(function() {
                        $('#distrito_nacimiento_select option').filter(function() {
                            return $(this).text() == '{{ $postulante->distrito_nacimiento }}';
                        }).prop('selected', true);
                    }, 500);
                }, 500);
            }
        @endif

        @if($postulante->departamento_institucion)
            let deptoInstitucion = $('#departamento_institucion_select option').filter(function() {
                return $(this).text() == '{{ $postulante->departamento_institucion }}';
            }).val();
            if (deptoInstitucion) {
                $('#departamento_institucion_select').val(deptoInstitucion).trigger('change');
                setTimeout(function() {
                    $('#provincia_institucion_select option').filter(function() {
                        return $(this).text() == '{{ $postulante->provincia_institucion }}';
                    }).prop('selected', true).trigger('change');
                    
                    setTimeout(function() {
                        $('#distrito_institucion_select option').filter(function() {
                            return $(this).text() == '{{ $postulante->distrito_institucion }}';
                        }).prop('selected', true);
                    }, 500);
                }, 500);
            }
        @endif
    });
</script>

{{-- Script para lenguas --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lengua1 = document.getElementById('lengua_1');
        const lengua2 = document.getElementById('lengua_2');

        function actualizarLengua2() {
            const valorSeleccionado = lengua1.value;

            Array.from(lengua2.options).forEach(option => {
                option.disabled = false;
            });

            if (valorSeleccionado) {
                const opcion = Array.from(lengua2.options)
                    .find(opt => opt.value === valorSeleccionado);

                if (opcion) {
                    opcion.disabled = true;
                    if (lengua2.value === valorSeleccionado) {
                        lengua2.value = '';
                    }
                }
            }
        }

        lengua1.addEventListener('change', actualizarLengua2);
        actualizarLengua2();
    });
</script>
@endsection