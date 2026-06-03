{{-- Partial: formulario de lección con editor de texto enriquecido --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
<style>
/* ── Field groups ── */
.cea-field-group {
    background: #fafbff; border: 1px solid #e9ecef;
    border-radius: .75rem; padding: 1.25rem 1.25rem 1rem;
    margin-bottom: 1.25rem;
}
.cea-field-group-title {
    font-size: .72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .07em; color: #4e73df; margin-bottom: .85rem;
    display: flex; align-items: center; gap: .4rem;
}

/* ── Type selector ── */
.cea-type-tabs { display: flex; gap: .5rem; margin-bottom: 1rem; }
.cea-type-tab {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .55rem .75rem; border-radius: .55rem; border: 2px solid #e9ecef;
    font-size: .82rem; font-weight: 700; cursor: pointer;
    transition: all .2s; background: #fff; color: #aaa;
}
.cea-type-tab input { display: none; }
.cea-type-tab.selected { border-color: #4e73df; background: #e8f0ff; color: #4e73df; }
.cea-type-tab:hover:not(.selected) { border-color: #c5cee0; color: #666; }

/* ── URL preview ── */
.cea-url-preview {
    background: #000; border-radius: .6rem; overflow: hidden;
    margin-top: .75rem; display: none;
}
.cea-url-preview.show { display: block; }
.cea-url-preview iframe {
    width: 100%; aspect-ratio: 16/9; border: none; display: block;
}
.cea-url-preview audio { width: 100%; display: block; background: #1b1b2f; padding: .5rem; }

/* ── Summernote overrides ── */
.note-editor.note-frame { border-radius: .6rem; border-color: #e9ecef; }
.note-toolbar { background: #f8f9fc; border-bottom: 1px solid #e9ecef; border-radius: .6rem .6rem 0 0 !important; }
.note-statusbar { border-radius: 0 0 .6rem .6rem; }

/* ── Meta row ── */
.cea-meta-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 576px) { .cea-meta-row { grid-template-columns: 1fr; } }
</style>
@endpush

{{-- 1. Información básica --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-tag"></i> Información básica</div>

    <div class="form-group mb-3">
        <label class="font-weight-bold small">Nombre de la lección <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $leccion->nombre ?? '') }}"
               placeholder="Ej: Saludos básicos en Quechua" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Type selector --}}
    <label class="font-weight-bold small d-block mb-2">Tipo de lección <span class="text-danger">*</span></label>
    <div class="cea-type-tabs" id="type-tabs">
        @foreach ([
            'texto' => ['fas fa-file-alt', 'Texto'],
            'audio' => ['fas fa-headphones', 'Audio'],
            'video' => ['fas fa-play-circle', 'Video'],
        ] as $val => [$icon, $label])
            @php $selected = old('tipo', $leccion->tipo ?? 'texto') === $val; @endphp
            <label class="cea-type-tab {{ $selected ? 'selected' : '' }}" data-type="{{ $val }}">
                <input type="radio" name="tipo" value="{{ $val }}" {{ $selected ? 'checked' : '' }}>
                <i class="{{ $icon }}"></i> {{ $label }}
            </label>
        @endforeach
    </div>
</div>

{{-- 2. Contenido de texto (Summernote) --}}
<div class="cea-field-group" id="bloque-texto">
    <div class="cea-field-group-title"><i class="fas fa-align-left"></i> Contenido de la lección</div>
    <div class="form-group mb-0">
        <textarea name="contenido_texto" id="summernote-leccion">{{ old('contenido_texto', $leccion->contenido_texto ?? '') }}</textarea>
    </div>
</div>

{{-- 3. Archivo / URL (audio/video) --}}
<div class="cea-field-group" id="bloque-url">
    <div class="cea-field-group-title"><i class="fas fa-link"></i> URL del recurso</div>

    <div class="form-group mb-1">
        <label class="font-weight-bold small">URL del audio/video</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
            </div>
            <input type="text" name="archivo_url" id="input-url"
                   class="form-control @error('archivo_url') is-invalid @enderror"
                   value="{{ old('archivo_url', $leccion->archivo_url ?? '') }}"
                   placeholder="https://www.youtube.com/embed/...">
            @error('archivo_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <small class="form-text text-muted mt-1">
            Para YouTube usa el enlace embed: <code>https://www.youtube.com/embed/VIDEO_ID</code>
        </small>
    </div>

    <div class="cea-url-preview" id="url-preview">
        <iframe id="url-preview-iframe" src="" allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media"></iframe>
    </div>

    {{-- Notas del video (campo texto secundario para video/audio) --}}
    <div class="form-group mt-3 mb-0">
        <label class="font-weight-bold small">Notas complementarias <span class="text-muted">(opcional)</span></label>
        <textarea name="contenido_texto" id="summernote-leccion-notas"
                  placeholder="Texto de apoyo, instrucciones previas al video, etc.">{{ old('contenido_texto', $leccion->contenido_texto ?? '') }}</textarea>
    </div>
</div>

{{-- 4. Metadatos --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-sliders-h"></i> Metadatos</div>
    <div class="cea-meta-row">
        <div class="form-group mb-0">
            <label class="font-weight-bold small">Duración estimada (min)</label>
            <input type="number" name="duracion_min" class="form-control" min="1"
                   value="{{ old('duracion_min', $leccion->duracion_min ?? '') }}"
                   placeholder="Ej: 15">
        </div>
        <div class="form-group mb-0">
            <label class="font-weight-bold small">Orden</label>
            <input type="number" name="orden" class="form-control" min="0"
                   value="{{ old('orden', $leccion->orden ?? 0) }}">
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-es-ES.min.js"></script>
<script>
(function () {
    const SUMMERNOTE_OPTS = {
        lang: 'es-ES',
        height: 320,
        minHeight: 200,
        placeholder: 'Escribe el contenido de la lección aquí...',
        toolbar: [
            ['style',  ['style']],
            ['font',   ['bold','italic','underline','clear']],
            ['color',  ['color']],
            ['para',   ['ul','ol','paragraph']],
            ['table',  ['table']],
            ['insert', ['link','hr']],
            ['view',   ['codeview','fullscreen']],
        ],
        styleTags: ['p','h2','h3','h4','blockquote','pre'],
        callbacks: {
            onChange: function () {
                // sync textarea for form submit
            }
        }
    };

    /* ── Determinar tipo actual ── */
    function getTipo() {
        const checked = document.querySelector('input[name="tipo"]:checked');
        return checked ? checked.value : 'texto';
    }

    /* ── Mostrar/ocultar bloques ── */
    function actualizarUI(tipo) {
        const bloqueTexto = document.getElementById('bloque-texto');
        const bloqueUrl   = document.getElementById('bloque-url');
        if (tipo === 'texto') {
            bloqueTexto.style.display = '';
            bloqueUrl.style.display   = 'none';
        } else {
            bloqueTexto.style.display = 'none';
            bloqueUrl.style.display   = '';
        }
        // Sync the textarea name: only active one should be named "contenido_texto"
        const txtPrincipal = document.getElementById('summernote-leccion');
        const txtNotas     = document.getElementById('summernote-leccion-notas');
        if (txtPrincipal) txtPrincipal.name = tipo === 'texto' ? 'contenido_texto' : '_ignore';
        if (txtNotas)     txtNotas.name     = tipo !== 'texto' ? 'contenido_texto' : '_ignore';
    }

    /* ── Type tabs click ── */
    document.querySelectorAll('.cea-type-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.cea-type-tab').forEach(t => t.classList.remove('selected'));
            tab.classList.add('selected');
            tab.querySelector('input').checked = true;
            actualizarUI(tab.dataset.type);
            renderPreview();
        });
    });

    /* ── URL preview ── */
    let previewTimer;
    function renderPreview() {
        const url = (document.getElementById('input-url').value || '').trim();
        const preview = document.getElementById('url-preview');
        const iframe  = document.getElementById('url-preview-iframe');
        if (url && getTipo() !== 'texto') {
            iframe.src = url;
            preview.classList.add('show');
        } else {
            iframe.src = '';
            preview.classList.remove('show');
        }
    }
    const urlInput = document.getElementById('input-url');
    if (urlInput) {
        urlInput.addEventListener('input', function () {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(renderPreview, 800);
        });
    }

    /* ── Init Summernote ── */
    $(document).ready(function () {
        if (typeof $.fn.summernote === 'undefined') return;
        $('#summernote-leccion').summernote(SUMMERNOTE_OPTS);
        $('#summernote-leccion-notas').summernote(Object.assign({}, SUMMERNOTE_OPTS, {
            height: 180,
            placeholder: 'Notas de apoyo, instrucciones previas al video/audio...'
        }));
        // Init state
        actualizarUI(getTipo());
        renderPreview();
    });
}());
</script>
@endpush
