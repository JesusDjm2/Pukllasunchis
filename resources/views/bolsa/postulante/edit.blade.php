@extends('layouts.bolsa')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Editar datos de bolsa de trabajo: <small
                    class="text-primary">{{ $postulante->apellidos }}
                    {{ $postulante->name }}</small></h1>
            <a href="{{ url()->previous() }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form id="myForm" method="POST"
                    action="{{ route('postulante.update', ['postulante' => $postulante->id]) }}" class="mb-3"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                @if ($user->hasRole('admin') || $user->hasRole('adminB'))
                                    <input type="text"
                                        class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                        id="nombre" name="nombre" value="{{ $postulante->nombre }}" required>
                                @else
                                    <input type="hidden" name="nombre" value="{{ $postulante->nombre }}">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $postulante->nombre }}" disabled>
                                @endif
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="apellidos" class="form-label">Apellidos:</label>
                                @if ($user->hasRole('admin') || $user->hasRole('adminB'))
                                    <input type="text"
                                        class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                                        id="apellidos" name="apellidos" value="{{ $postulante->apellidos }}" required>
                                @else
                                    <input type="hidden" name="apellidos" value="{{ $postulante->apellidos }}">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $postulante->apellidos }}" disabled>
                                @endif
                                @error('apellidos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="dni" class="form-label">DNI:</label>
                                @if ($user->hasRole('admin') || $user->hasRole('adminB'))
                                    <input type="text"
                                        class="form-control form-control-sm @error('dni') is-invalid @enderror"
                                        id="dni" name="dni" value="{{ $postulante->dni }}" required>
                                @else
                                    <input type="hidden" name="dni" value="{{ $postulante->dni }}">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $postulante->dni }}" disabled>
                                @endif
                                @error('dni')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label for="email" class="form-label">Correo electrónico: <small
                                        class="text-primary">(Distinto al institucional)</small></label>
                                <input type="email"
                                    class="form-control form-control-sm @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ $postulante->email }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <div class="col-lg-4 mb-3">
                                <label for="programa_id" class="form-label">Programa de la EESP Pukllasunchis:</label>
                                <select name="programa_id" id="programa_id" class="form-control form-control-sm">
                                    <option disabled>Seleccionar Programa</option>
                                    @foreach ($programas as $programa)
                                        <option value="{{ $programa->id }}"
                                            {{ $postulante->programa_id == $programa->id ? 'selected' : '' }}>
                                            {{ $programa->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('programa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="col-lg-4 mb-3">
                                <label for="programa_id" class="form-label">Programa de la EESP Pukllasunchis:</label>
                                <input type="hidden" name="programa_id" value="{{ auth()->user()->programa->id }}">
                                <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->programa->nombre }}" disabled>
                            </div>    --}}
                            <div class="col-lg-4 mb-3">
                                <label for="programa_id" class="form-label">Programa de la EESP Pukllasunchis:</label>
                                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('adminB'))
                                    <select name="programa_id" id="programa_id" class="form-control form-control-sm">
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}"
                                                {{ old('programa_id', optional(auth()->user()->programa)->id) == $programa->id ? 'selected' : '' }}>
                                                {{ $programa->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="programa_id" value="{{ auth()->user()->programa->id }}">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ auth()->user()->programa->nombre }}" disabled>
                                @endif
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="edad" class="form-label">Edad:</label>
                                <input type="number" name="edad" id="edad" class="form-control form-control-sm"
                                    value="{{ $postulante->edad }}">
                                @error('edad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="idioma" class="form-label">Idioma:</label>
                                <input type="text" name="idioma" id="idioma" class="form-control form-control-sm"
                                    value="{{ $postulante->idioma }}">
                                @error('idioma')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="numero" class="form-label">Número:</label>
                                <input type="text" name="numero" id="numero" class="form-control form-control-sm"
                                    value="{{ $postulante->numero }}">
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label for="cv" class="form-label">
                                    Subir CV: <small class="text-primary">(Dejar en blanco para no actualizar)</small>
                                </label>
                                <input type="file" name="cv" id="cv"
                                    class="form-control form-control-sm">
                                @error('cv')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-2 mb-3">
                                <label for="img" class="form-label">Actualizar Foto:</label>
                                <input type="file" name="img" id="img" class="form-control form-control-sm"
                                    onchange="previewImage(event)">
                                @if ($postulante->img)
                                    <div class="mt-2">
                                        <img id="preview" src="{{ asset($postulante->img) }}"
                                            style="max-width: 100%;">
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <img id="preview" src="#" style="max-width: 100%; display: none;">
                                    </div>
                                @endif

                                @error('img')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if (old('img'))
                                    <p class="text-muted mt-2">Archivo seleccionado: {{ old('img') }}</p>
                                @endif
                            </div>
                            <script>
                                window.addEventListener('DOMContentLoaded', (event) => {
                                    // Mostrar la imagen existente al cargar la página
                                    const imgElement = document.getElementById('preview');
                                    const existingImgSrc = imgElement.src;

                                    if (existingImgSrc && existingImgSrc !== '#') {
                                        imgElement.style.display = 'block';
                                    }
                                });

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
                            <div class="col-lg-5 mb-3">
                                <label for="facebook" class="form-label">Enlace de Facebook: <small
                                        class="text-primary">(Opcional)</small></label>
                                <input type="text" name="facebook" id="facebook"
                                    class="form-control form-control-sm" value="{{ $postulante->facebook }}">
                                @error('facebook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-5 mb-3">
                                <label for="linkedin" class="form-label">Enlace de LinkedIn: <small
                                        class="text-primary">(Opcional)</small></label>
                                <input type="text" name="linkedin" id="linkedin"
                                    class="form-control form-control-sm" value="{{ $postulante->linkedin }}">
                                @error('linkedin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="otros_estudios" class="form-label">Otros estudios:</label>
                                <textarea name="otros_estudios" id="otros_estudios" class="form-control form-control-sm" rows="4">{{ $postulante->otros_estudios }}</textarea>
                                @error('otros_estudios')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="descripcion" class="form-label">Breve descripción:</label>
                                <textarea name="descripcion" id="descripcion" class="form-control form-control-sm" rows="4" maxlength="400">{{ $postulante->descripcion }}</textarea>
                                <div id="contador-caracteres" class="text-muted mt-2">
                                    Caracteres restantes: <span
                                        id="caracteres-restantes">{{ 400 - strlen($postulante->descripcion) }}</span>
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
                            <button type="submit" class="ml-3 btn btn-primary btn-sm">Actualizar</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-fields');

            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumno' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumno') {
                adminFields.style.display = 'block';
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var programaSelector = document.getElementById('programa_id');
            var cicloSelector = document.getElementById('ciclo_id');
            programaSelector.addEventListener('change', function() {
                var programaId = programaSelector.value;
                cicloSelector.innerHTML = '<option disabled selected>Seleccionar Ciclo</option>';
                if (programaId) {
                    fetch('/obtener-ciclos/' + programaId)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(ciclo => {
                                var option = document.createElement('option');
                                option.value = ciclo.id;
                                option.text = ciclo.nombre;
                                cicloSelector.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-bolsa');
            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumnoB' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumnoB') {
                adminFields.style.display = 'block';
            }
        });
    </script>
@endsection
