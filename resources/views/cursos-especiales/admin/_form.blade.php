{{-- Partial compartido por create y edit del curso especial --}}
@push('styles')
<style>
.cea-field-group { background:#fafbff; border:1px solid #e9ecef; border-radius:.75rem; padding:1.25rem 1.25rem 1rem; margin-bottom:1.25rem; }
.cea-field-group-title { font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#4e73df; margin-bottom:.85rem; display:flex; align-items:center; gap:.4rem; }
.cea-img-preview { width:100%; height:160px; border-radius:.6rem; object-fit:cover; display:none; margin-bottom:.75rem; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.cea-img-preview.show { display:block; }
.cea-img-drop {
    border:2px dashed #c5cee0; border-radius:.6rem; padding:2rem;
    text-align:center; cursor:pointer; color:#aaa;
    transition:border-color .2s, background .2s;
}
.cea-img-drop:hover { border-color:#4e73df; background:#f0f4ff; color:#4e73df; }
.cea-toggle-wrap { display:flex; align-items:center; gap:.85rem; }
.cea-toggle { position:relative; display:inline-block; width:48px; height:26px; }
.cea-toggle input { opacity:0; width:0; height:0; }
.cea-slider {
    position:absolute; inset:0; background:#ccc; border-radius:99px; cursor:pointer;
    transition:.3s;
}
.cea-slider::before {
    content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,.2);
}
input:checked + .cea-slider { background:#1cc88a; }
input:checked + .cea-slider::before { transform:translateX(22px); }
.cea-toggle-label { font-size:.88rem; font-weight:600; color:#2d3561; }
</style>
@endpush

{{-- Datos principales --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-info-circle"></i> Información del Curso</div>

    <div class="form-group">
        <label class="font-weight-bold small">Nombre del Curso <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control form-control-lg @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $curso->nombre ?? '') }}"
               placeholder="Ej: Idioma Quechua — Qhichwa Simi" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-group mb-0">
        <label class="font-weight-bold small">Descripción</label>
        <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                  rows="3" placeholder="Descripción breve que verá el alumno antes de inscribirse...">{{ old('descripcion', $curso->descripcion ?? '') }}</textarea>
        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Portada --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-image"></i> Imagen de portada</div>

    @if (!empty($curso->imagen))
        <img src="{{ $curso->imagen_url }}" alt="Portada actual" class="cea-img-preview show" id="img-preview">
        <p class="small text-muted mb-2"><i class="fas fa-check-circle text-success mr-1"></i>Imagen actual — sube una nueva para reemplazarla</p>
    @else
        <img src="" alt="" class="cea-img-preview" id="img-preview">
    @endif

    <div class="cea-img-drop" id="img-drop" onclick="document.getElementById('img-input').click()">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
        <span class="font-weight-600 d-block">Haz clic para seleccionar una imagen</span>
        <small>JPG, PNG, WEBP — máx. 2MB</small>
    </div>
    <input type="file" name="imagen" id="img-input" accept="image/*" style="display:none">
    @error('imagen') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

{{-- Docente responsable --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-chalkboard-teacher"></i> Docente responsable</div>
    <div class="form-group mb-0">
        <label class="font-weight-bold small">Asignar docente <span class="text-muted font-weight-normal">(opcional)</span></label>
        <select name="docente_id" class="form-control @error('docente_id') is-invalid @enderror">
            <option value="">— Sin docente asignado —</option>
            @foreach ($docentes as $d)
                <option value="{{ $d->id }}"
                    {{ old('docente_id', $curso->docente_id ?? '') == $d->id ? 'selected' : '' }}>
                    {{ $d->nombre }}
                    @if ($d->email) · {{ $d->email }} @endif
                </option>
            @endforeach
        </select>
        @error('docente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="form-text text-muted">El docente podrá ver y editar el contenido de este curso desde su panel.</small>
    </div>
</div>

{{-- Config --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-sliders-h"></i> Configuración</div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="font-weight-bold small">Orden de aparición</label>
            <input type="number" name="orden" class="form-control" min="0"
                   value="{{ old('orden', $curso->orden ?? 0) }}">
        </div>
        <div class="col-md-8 d-flex align-items-end pb-3">
            <div class="cea-toggle-wrap">
                <label class="cea-toggle">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo', $curso->activo ?? true) ? 'checked' : '' }}>
                    <span class="cea-slider"></span>
                </label>
                <span class="cea-toggle-label">Activo — visible para los alumnos</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const input   = document.getElementById('img-input');
    const preview = document.getElementById('img-preview');
    const drop    = document.getElementById('img-drop');
    if (!input) return;

    input.addEventListener('change', function () {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.add('show');
            drop.innerHTML = '<i class="fas fa-check-circle fa-2x mb-2 d-block" style="color:#1cc88a"></i><span class="font-weight-600">' + file.name + '</span>';
        };
        reader.readAsDataURL(file);
    });
}());
</script>
@endpush
