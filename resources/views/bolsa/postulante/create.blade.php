@extends('layouts.bolsa')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 mt-3">
                Registrar datos en la bolsa de trabajo: {{-- <small class="text-primary"> {{ $user->name }} </small> --}}
            </h4>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <form id="myForm" method="POST" action="{{ route('postulante.store') }}" class="mb-3"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre" value="{{ $user->name }}" @if($user->hasRole('alumno') || $user->hasRole('alumnoB')) readonly @endif required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="apellidos" class="form-label">Apellidos:</label>
                            <input type="text"
                                class="form-control form-control-sm @error('apellidos') is-invalid @enderror" id="apellidos"
                                name="apellidos" value="{{ $user->apellidos }}" @if($user->hasRole('alumno') || $user->hasRole('alumnoB')) readonly @endif required>
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                id="dni" name="dni" value="{{ $user->dni }}" @if($user->hasRole('alumno') || $user->hasRole('alumnoB')) readonly @endif required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="email" class="form-label">Correo electrónico: <small class="text-primary">(Distinto al institucional)</small></label>
                            <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- <div class="col-lg-4 mb-3">
                            <label for="programa_id" class="form-label">Programa de la EESP Pukllasunchis:</label>
                            <select name="programa_id" id="programa_id" class="form-control form-control-sm">
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}"
                                        {{ old('programa_id', optional(auth()->user()->programa)->id) == $programa->id ? 'selected' : '' }}>
                                        {{ $programa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('programa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-lg-4 mb-3">
                            <label for="programa_id" class="form-label">Programa de la EESP Pukllasunchis:</label>
                            @if (auth()->user()->programa)
                                <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->programa->nombre }}" disabled>
                            @else
                                <input type="text" class="form-control form-control-sm" value="" disabled>
                            @endif
                            <input type="hidden" name="programa_id" value="{{ auth()->user()->programa_id }}">
                        </div>
                        

                        <div class="col-lg-4 mb-3">
                            <label for="edad" class="form-label">Edad:</label>
                            <input type="number" name="edad" id="edad" class="form-control form-control-sm"
                                value="{{ old('edad') }}">
                            @error('edad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="idioma" class="form-label">Idioma(s):</label>
                            <input type="text" name="idioma" id="idioma" class="form-control form-control-sm"
                                value="{{ old('idioma') }}">
                            @error('idioma')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="numero" class="form-label">Número de teléfono:</label>
                            <input type="text" name="numero" id="numero" class="form-control form-control-sm"
                                value="{{ old('numero') }}">
                            @error('numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="cv" class="form-label">
                                Subir CV: <small class="text-primary">Subir en PDF</small>
                            </label>
                            <input type="file" name="cv" id="cv" class="form-control form-control-sm">
                            @error('cv')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-12 mb-3">
                            <label for="otros_estudios" class="form-label">Otros estudios:</label>
                            <textarea name="otros_estudios" id="otros_estudios" class="ckeditor form-control form-control-sm" rows="4">{{ old('otros_estudios') }}</textarea>
                            @error('otros_estudios')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="img" class="form-label">Subir Foto:</label>
                            <input type="file" name="img" id="img" class="form-control form-control-sm"
                                onchange="previewImage(event)">
                            <div class="mt-2">
                                <img id="preview" src="#" style="max-width: 100%; display: none;">
                            </div>
                            @error('img')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (old('img'))
                                <p class="text-muted mt-2">Archivo seleccionado: {{ old('img') }}</p>
                            @endif
                        </div>
                        <script>
                            function previewImage(event) {
                                var input = event.target;
                                var reader = new FileReader();

                                reader.onload = function() {
                                    var preview = document.getElementById('preview');
                                    preview.src = reader.result;
                                    preview.style.display = 'block';
                                };

                                reader.readAsDataURL(input.files[0]);
                            }
                        </script>
                        <div class="col-lg-4 mb-3">
                            <label for="facebook" class="form-label">Enlace de Facebook: <small
                                    class="text-primary">(Opcional)</small> </label>
                            <input type="text" name="facebook" id="facebook" class="form-control form-control-sm"
                                value="{{ old('facebook') }}">
                            @error('facebook')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="linkedin" class="form-label">Enlace de LinkedIn: <small
                                    class="text-primary">(Opcional)</small></label>
                            <input type="text" name="linkedin" id="linkedin" class="form-control form-control-sm"
                                value="{{ old('linkedin') }}">
                            @error('linkedin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-12 mb-3">
                            <label for="descripcion" class="form-label">Breve descripción: <small
                                    class="text-primary"></small></label>
                            <textarea name="descripcion" id="descripcion" class="form-control form-control-sm" rows="4" maxlength="400">{{ old('descripcion') }}</textarea>
                            <div id="contador-caracteres" class="text-muted mt-2">
                                Caracteres restantes: <span id="caracteres-restantes">400</span>
                            </div>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <script>
                            var textarea = document.getElementById('descripcion');
                            var contadorCaracteres = document.getElementById('caracteres-restantes');
                            textarea.addEventListener('input', function() {
                                var caracteresRestantes = 400 - textarea.value.length;
                                contadorCaracteres.textContent = caracteresRestantes;

                                if (caracteresRestantes < 0) {
                                    textarea.value = textarea.value.slice(0, 400); // Limitar la longitud del texto
                                    contadorCaracteres.textContent = 0;
                                }
                            });
                        </script>
                        <button type="submit" class="ml-3 btn btn-sm btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
