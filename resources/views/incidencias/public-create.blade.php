@extends('layouts.app')

@section('titulo', 'Reporte de Incidencias — Pukllasunchis')

@section('content')
    <main class="d-flex justify-content-center align-items-start py-5 fondoLogin2" style="min-height:100vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-12 col-xl-10">

                    <div class="card border-0 shadow-lg" style="border-radius:1rem;overflow:hidden;">
                        {{-- Franja superior de color --}}
                        <div style="height:6px;background:linear-gradient(90deg,#4e73df 0%,#1cc88a 100%);"></div>

                        <div class="card-body p-4 p-md-5">

                            {{-- Alertas --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>Por favor corrige los siguientes errores:</strong>
                                    <ul class="mb-0 mt-2 pl-3">
                                        @foreach ($errors->all() as $error)
                                            <li style="font-size:.875rem;">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            <p class="text-muted mb-4" style="font-size:.875rem;">
                                Complete el formulario para reportar una incidencia sobre un estudiante.
                                Los campos marcados con <span class="text-danger font-weight-bold">*</span> son
                                obligatorios.
                            </p>

                            <form action="{{ route('incidencias.public.store') }}" method="POST"
                                enctype="multipart/form-data" id="incPublicForm">
                                @csrf

                                {{-- ── Datos del docente ── --}}
                                <div class="mb-4">
                                    <div class="text-uppercase font-weight-bold text-muted mb-2"
                                        style="font-size:.68rem;letter-spacing:.1em;border-bottom:2px solid #eaecf4;padding-bottom:.35rem;">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i> Datos del docente
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Nombre completo del docente <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="nombre_docente"
                                            class="form-control @error('nombre_docente') is-invalid @enderror"
                                            placeholder="Apellidos y nombres del docente que reporta"
                                            value="{{ old('nombre_docente') }}">
                                        @error('nombre_docente')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ── Selección del alumno ── --}}
                                <div class="mb-4">
                                    <div class="text-uppercase font-weight-bold text-muted mb-3"
                                        style="font-size:.68rem;letter-spacing:.1em;border-bottom:2px solid #eaecf4;padding-bottom:.35rem;">
                                        <i class="fas fa-user-graduate mr-1"></i> Estudiante involucrado
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold small">
                                                    Programa <span class="text-danger">*</span>
                                                </label>
                                                <select name="programa_id" id="selPrograma"
                                                    class="form-control @error('programa_id') is-invalid @enderror">
                                                    <option value="">— Seleccione un programa —</option>
                                                    @foreach ($programas as $p)
                                                        <option value="{{ $p->id }}"
                                                            {{ old('programa_id') == $p->id ? 'selected' : '' }}>
                                                            {{ $p->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('programa_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold small">
                                                    Ciclo <span class="text-danger">*</span>
                                                </label>
                                                <select name="ciclo_id" id="selCiclo"
                                                    class="form-control @error('ciclo_id') is-invalid @enderror" disabled>
                                                    <option value="">— Seleccione primero un programa —</option>
                                                </select>
                                                @error('ciclo_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Alumno <span class="text-danger">*</span>
                                        </label>
                                        <select name="alumno_id" id="selAlumno"
                                            class="form-control @error('alumno_id') is-invalid @enderror" disabled>
                                            <option value="">— Seleccione primero un ciclo —</option>
                                        </select>
                                        @error('alumno_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ── Detalle de la incidencia ── --}}
                                <div class="mb-4">
                                    <div class="text-uppercase font-weight-bold text-muted mb-3"
                                        style="font-size:.68rem;letter-spacing:.1em;border-bottom:2px solid #eaecf4;padding-bottom:.35rem;">
                                        <i class="fas fa-clipboard-list mr-1"></i> Detalle de la incidencia
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Fecha <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="fecha"
                                            class="form-control @error('fecha') is-invalid @enderror"
                                            value="{{ old('fecha', now()->format('Y-m-d')) }}">
                                        @error('fecha')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Descripción de la incidencia <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="reporte" id="reporte" rows="6" class="form-control @error('reporte') is-invalid @enderror"
                                            placeholder="Describa con detalle la situación observada: qué ocurrió, cuándo, dónde y cualquier otra información relevante…">{{ old('reporte') }}</textarea>
                                        @error('reporte')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Mínimo 10 caracteres.</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Imagen adjunta
                                            <span class="text-muted font-weight-normal">(opcional, máx. 3 MB)</span>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" name="imagen" id="incImagen" accept="image/*"
                                                class="custom-file-input @error('imagen') is-invalid @enderror">
                                            <label class="custom-file-label" for="incImagen">
                                                Seleccionar imagen…
                                            </label>
                                            @error('imagen')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div id="imgPreviewWrap" class="mt-2 d-none"
                                            style="position:relative;display:inline-block;">
                                            <img id="imgPreview" src="" alt="Vista previa"
                                                style="max-height:160px;border-radius:.5rem;border:1px solid #dee2e6;">
                                            <button type="button" id="btnRemoveImg"
                                                style="position:absolute;top:4px;right:4px;background:rgba(220,53,69,.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:.75rem;line-height:22px;text-align:center;cursor:pointer;"
                                                aria-label="Quitar imagen">&times;</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Botón enviar --}}
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5"
                                        style="border-radius:2rem;font-weight:600;">
                                        <i class="fas fa-paper-plane mr-2"></i> Enviar reporte
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <p class="text-center text-white-50 mt-3" style="font-size:.78rem;">
                        © {{ date('Y') }} Instituto de Educación Superior Pedagógico Pukllasunchis
                    </p>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function() {
            var urlCiclos = '{{ route('api.ciclos', ':p') }}';
            var urlAlumnos = '{{ route('api.alumnos', ':c') }}';

            var selPrograma = document.getElementById('selPrograma');
            var selCiclo = document.getElementById('selCiclo');
            var selAlumno = document.getElementById('selAlumno');

            function resetSelect(sel, placeholder) {
                sel.innerHTML = '<option value="">' + placeholder + '</option>';
                sel.disabled = true;
            }

            selPrograma.addEventListener('change', function() {
                resetSelect(selCiclo, '— Seleccione primero un programa —');
                resetSelect(selAlumno, '— Seleccione primero un ciclo —');
                if (!this.value) return;

                fetch(urlCiclos.replace(':p', this.value))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(ciclos) {
                        if (ciclos.length === 0) {
                            selCiclo.innerHTML = '<option value="">— Sin ciclos disponibles —</option>';
                            return;
                        }
                        selCiclo.innerHTML = '<option value="">— Seleccione un ciclo —</option>';
                        ciclos.forEach(function(c) {
                            var opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.nombre;
                            selCiclo.appendChild(opt);
                        });
                        selCiclo.disabled = false;
                    });
            });

            selCiclo.addEventListener('change', function() {
                resetSelect(selAlumno, '— Seleccione primero un ciclo —');
                if (!this.value) return;

                fetch(urlAlumnos.replace(':c', this.value))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(alumnos) {
                        if (alumnos.length === 0) {
                            selAlumno.innerHTML = '<option value="">— Sin alumnos en este ciclo —</option>';
                            return;
                        }
                        selAlumno.innerHTML = '<option value="">— Seleccione un alumno —</option>';
                        alumnos.forEach(function(a) {
                            var opt = document.createElement('option');
                            opt.value = a.id;
                            opt.textContent = a.nombre;
                            selAlumno.appendChild(opt);
                        });
                        selAlumno.disabled = false;
                    });
            });

            // Vista previa de imagen
            var inputImg = document.getElementById('incImagen');
            var previewWrap = document.getElementById('imgPreviewWrap');
            var preview = document.getElementById('imgPreview');
            var btnRemove = document.getElementById('btnRemoveImg');
            var fileLabel = document.querySelector('.custom-file-label');

            inputImg.addEventListener('change', function() {
                var file = this.files[0];
                if (!file) return;
                fileLabel.textContent = file.name;
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewWrap.style.display = 'inline-block';
                    previewWrap.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });

            btnRemove.addEventListener('click', function() {
                inputImg.value = '';
                fileLabel.textContent = 'Seleccionar imagen…';
                preview.src = '';
                previewWrap.style.display = 'none';
            });
        })();
    </script>
@endpush
