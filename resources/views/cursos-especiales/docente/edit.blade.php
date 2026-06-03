@extends('layouts.docente')
@section('titulo', 'Editar — ' . $curso->nombre)

@push('styles')
    <style>
        .ced-form-page {
            max-width: 720px;
            padding-bottom: 2rem;
        }

        .ced-form-header {
            padding: 1.25rem 0 1rem;
            border-bottom: 2px solid #e3e6f0;
            margin-bottom: 1.75rem;
        }

        .ced-form-header h1 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #2d3561;
            margin: 0;
        }

        .ced-form-header a {
            font-size: .82rem;
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
        }

        .ced-form-header a:hover {
            text-decoration: underline;
        }

        .ced-field-group {
            background: #fafbff;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            padding: 1.25rem 1.25rem 1rem;
            margin-bottom: 1.25rem;
        }

        .ced-field-group-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #4e73df;
            margin-bottom: .85rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .ced-img-preview {
            width: 100%;
            height: 160px;
            border-radius: .6rem;
            object-fit: cover;
            display: none;
            margin-bottom: .75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .ced-img-preview.show {
            display: block;
        }

        .ced-img-drop {
            border: 2px dashed #c5cee0;
            border-radius: .6rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            color: #aaa;
            transition: border-color .2s, background .2s;
        }

        .ced-img-drop:hover {
            border-color: #4e73df;
            background: #f0f4ff;
            color: #4e73df;
        }

        .ced-toggle-wrap {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .ced-toggle {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
        }

        .ced-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .ced-slider {
            position: absolute;
            inset: 0;
            background: #ccc;
            border-radius: 99px;
            cursor: pointer;
            transition: .3s;
        }

        .ced-slider::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .3s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        }

        input:checked+.ced-slider {
            background: #1cc88a;
        }

        input:checked+.ced-slider::before {
            transform: translateX(22px);
        }

        .ced-toggle-label {
            font-size: .88rem;
            font-weight: 600;
            color: #2d3561;
        }

        .ced-footer {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .ced-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff;
            border: none;
            border-radius: .6rem;
            padding: .65rem 1.5rem;
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
            transition: opacity .2s;
        }

        .ced-submit-btn:hover {
            opacity: .88;
        }

        .ced-cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #f0f0f0;
            color: #555;
            border: none;
            border-radius: .6rem;
            padding: .65rem 1.2rem;
            font-weight: 600;
            font-size: .9rem;
            text-decoration: none;
        }

        .ced-cancel-btn:hover {
            background: #e0e0e0;
            text-decoration: none;
            color: #333;
        }
    </style>
@endpush

@section('contenido')
    <div class="container-fluid ced-form-page">

        <div class="ced-form-header">
            <div class="mb-2">
                <a href="{{ route('ce.docente.index') }}">
                    <i class="fas fa-arrow-left fa-xs mr-1"></i> Mis cursos
                </a>
                <span class="text-muted mx-1">/</span>
                <a href="{{ route('ce.docente.show', $curso) }}">{{ Str::limit($curso->nombre, 35) }}</a>
            </div>
            <h1><i class="fas fa-edit mr-2" style="color:#f6c23e"></i>Editar información del curso</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <form action="{{ route('ce.docente.update', $curso) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- Información --}}
            <div class="ced-field-group">
                <div class="ced-field-group-title"><i class="fas fa-info-circle"></i> Información del Curso</div>
                <div class="form-group">
                    <label class="font-weight-bold small">Nombre: <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                        class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $curso->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Descripción:</label>
                    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $curso->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Portada --}}
            <div class="ced-field-group">
                <div class="ced-field-group-title"><i class="fas fa-image"></i> Imagen de portada</div>
                @if ($curso->imagen)
                    <img src="{{ $curso->imagen_url }}" alt="Portada actual" class="ced-img-preview show" id="img-preview">
                    <p class="small text-muted mb-2"><i class="fas fa-check-circle text-success mr-1"></i>Imagen actual —
                        sube una nueva para reemplazarla</p>
                @else
                    <img src="" alt="" class="ced-img-preview" id="img-preview">
                @endif
                <div class="ced-img-drop" id="img-drop" onclick="document.getElementById('img-input').click()">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                    <span class="font-weight-600 d-block">Haz clic para seleccionar una imagen</span>
                    <small>JPG, PNG, WEBP — máx. 2MB</small>
                </div>
                <input type="file" name="imagen" id="img-input" accept="image/*" style="display:none">
                @error('imagen')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Configuración --}}
            {{-- <div class="ced-field-group">
                <div class="ced-field-group-title"><i class="fas fa-sliders-h"></i> Configuración</div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small">Orden de aparición</label>
                        <input type="number" name="orden" class="form-control" min="0"
                            value="{{ old('orden', $curso->orden) }}">
                    </div>
                    <div class="col-md-8 d-flex align-items-end pb-3">
                        <div class="ced-toggle-wrap">
                            <label class="ced-toggle">
                                <input type="checkbox" name="activo" value="1"
                                    {{ old('activo', $curso->activo) ? 'checked' : '' }}>
                                <span class="ced-slider"></span>
                            </label>
                            <span class="ced-toggle-label">Activo — visible para los alumnos</span>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="ced-footer">
                <button type="submit" class="ced-submit-btn"><i class="fas fa-save"></i> Guardar cambios</button>
                <a href="{{ route('ce.docente.show', $curso) }}" class="ced-cancel-btn"><i class="fas fa-times"></i>
                    Cancelar</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const input = document.getElementById('img-input');
            const preview = document.getElementById('img-preview');
            const drop = document.getElementById('img-drop');
            if (!input) return;
            input.addEventListener('change', function() {
                const file = input.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.classList.add('show');
                    drop.innerHTML =
                        '<i class="fas fa-check-circle fa-2x mb-2 d-block" style="color:#1cc88a"></i><span class="font-weight-600">' +
                        file.name + '</span>';
                };
                reader.readAsDataURL(file);
            });
        }());
    </script>
@endpush
