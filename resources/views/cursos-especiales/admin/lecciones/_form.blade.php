{{-- Partial: formulario multi-contenido de lección --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
<style>
/* ══ Tokens del tema admin ══════════════════════════════════════ */
:root {
    --cl-blue:   #4e73df;
    --cl-teal:   #06d6a0;
    --cl-amber:  #f6c23e;
    --cl-purple: #8338ec;
    --cl-red:    #e74a3b;
    --cl-dark:   #2d3561;
    --cl-muted:  #6c757d;
    --cl-card:   #fff;
    --cl-card-border: rgba(45,53,97,.13);
    --cl-card-bg: #f8f9fc;
}

/* ══ Card base ══════════════════════════════════════════════════ */
.cl-card {
    background: var(--cl-card);
    border: 1.5px solid var(--cl-card-border);
    border-radius: .9rem;
    margin-bottom: 1rem;
    overflow: hidden;
    transition: box-shadow .25s, border-color .25s;
}
.cl-card:focus-within { box-shadow: 0 4px 20px rgba(78,115,223,.12); }

/* ══ Card header ════════════════════════════════════════════════ */
.cl-card-hdr {
    display: flex; align-items: center; gap: .75rem;
    padding: .9rem 1.1rem;
    cursor: pointer; user-select: none;
    border-bottom: 1.5px solid transparent;
    transition: background .2s, border-color .2s;
}
.cl-card-hdr:hover { background: var(--cl-card-bg); }
.cl-card-hdr.active { border-bottom-color: var(--cl-card-border); }

.cl-card-icon {
    width: 34px; height: 34px; border-radius: .55rem; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; opacity: .5;
    transition: opacity .2s, transform .2s;
}
.cl-card-hdr.active .cl-card-icon { opacity: 1; transform: scale(1.05); }

.cl-card-icon.audio  { background: #e0fdf4; color: #059669; }
.cl-card-icon.video  { background: #fde8e8; color: var(--cl-red); }
.cl-card-icon.texto  { background: #e8f0ff; color: var(--cl-blue); }
.cl-card-icon.meta   { background: #f3eeff; color: var(--cl-purple); }
.cl-card-icon.info   { background: #fff8e0; color: var(--cl-amber); }

.cl-card-label {
    flex: 1; min-width: 0;
}
.cl-card-title {
    font-weight: 800; font-size: .88rem; color: var(--cl-dark);
    display: flex; align-items: center; gap: .45rem;
}
.cl-card-subtitle {
    font-size: .73rem; color: var(--cl-muted); margin-top: .05rem;
}
.cl-card-chevron {
    font-size: .72rem; color: var(--cl-muted);
    transition: transform .3s ease; flex-shrink: 0;
}
.cl-card-hdr.active .cl-card-chevron { transform: rotate(180deg); }

/* Status badge */
.cl-badge-active {
    font-size: .65rem; font-weight: 800; letter-spacing: .06em;
    text-transform: uppercase; padding: .18rem .55rem;
    border-radius: 99px; white-space: nowrap;
}
.cl-badge-active.audio  { background: #d1fae5; color: #059669; }
.cl-badge-active.video  { background: #fee2e2; color: #dc2626; }
.cl-badge-active.texto  { background: #dbeafe; color: #1d4ed8; }

/* Toggle switch */
.cl-toggle {
    position: relative; width: 38px; height: 21px; flex-shrink: 0;
}
.cl-toggle input { opacity: 0; width: 0; height: 0; }
.cl-toggle-track {
    position: absolute; inset: 0; border-radius: 99px;
    background: #dee2e6; transition: background .22s;
    cursor: pointer;
}
.cl-toggle-track::before {
    content: ''; position: absolute;
    width: 15px; height: 15px; border-radius: 50%;
    background: #fff; top: 3px; left: 3px;
    transition: transform .22s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.cl-toggle input:checked + .cl-toggle-track { background: var(--clr, #06d6a0); }
.cl-toggle input:checked + .cl-toggle-track::before { transform: translateX(17px); }

/* ══ Card body ══════════════════════════════════════════════════ */
.cl-card-body {
    padding: 1.1rem 1.1rem .9rem;
    background: var(--cl-card-bg);
    overflow: hidden;
}

/* ══ Upload zone ════════════════════════════════════════════════ */
.cl-file-zone {
    border: 2px dashed rgba(6,214,160,.35); border-radius: .75rem;
    padding: 1.25rem 1rem; text-align: center;
    background: linear-gradient(135deg,#f0fdf8,#e8f4ff);
    cursor: pointer; transition: border-color .2s, background .2s;
}
.cl-file-zone:hover { border-color: #06d6a0; background: #d9f9ee; }
.cl-file-zone-icon { font-size: 1.8rem; color: #06d6a0; margin-bottom: .4rem; }
.cl-file-zone-label { font-weight: 700; font-size: .88rem; color: var(--cl-dark); }
.cl-file-zone-hint  { font-size: .75rem; color: #aaa; margin-top: .15rem; }

.cl-audio-preview-box {
    background: #f0fdf8; border: 1px solid rgba(6,214,160,.25);
    border-radius: .65rem; padding: .75rem 1rem;
}
.cl-audio-saved-box {
    background: #ecfdf5; border: 1px solid rgba(6,214,160,.3);
    border-radius: .65rem; padding: .75rem 1rem;
}
.cl-saved-label {
    display: flex; align-items: center; gap: .35rem;
    font-size: .78rem; font-weight: 700; color: #059669; margin-bottom: .55rem;
}
.cl-action-row { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .6rem; }
.cl-btn-upload {
    display: inline-flex; align-items: center; gap: .4rem;
    background: linear-gradient(135deg,#06d6a0,#3a86ff);
    color: #fff; border: none; border-radius: .5rem;
    padding: .42rem 1rem; font-weight: 700; font-size: .8rem; cursor: pointer;
    transition: opacity .15s;
}
.cl-btn-upload:hover { opacity: .88; }
.cl-btn-upload:disabled { opacity: .5; cursor: not-allowed; }
.cl-btn-secondary {
    display: inline-flex; align-items: center; gap: .35rem;
    background: #ededf5; color: #555; border: none; border-radius: .5rem;
    padding: .42rem .9rem; font-weight: 700; font-size: .8rem; cursor: pointer;
}
.cl-btn-secondary:hover { background: #dde; }

/* ══ Summernote overrides ═══════════════════════════════════════ */
.note-editor.note-frame { border-radius: .55rem !important; border-color: #dde2ee !important; }
.note-toolbar { background: #eef0f8 !important; border-radius: .55rem .55rem 0 0 !important; }

/* ══ URL preview ════════════════════════════════════════════════ */
.cl-video-preview {
    background: #000; border-radius: .55rem; overflow: hidden;
    margin-top: .65rem; display: none;
}
.cl-video-preview.show { display: block; }
.cl-video-preview iframe {
    width: 100%; aspect-ratio: 16/9; border: none; display: block;
}

/* ══ Meta grid ══════════════════════════════════════════════════ */
.cl-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
@media (max-width:576px) { .cl-meta-grid { grid-template-columns: 1fr; } }

/* ══ Dark admin adaptation ══════════════════════════════════════ */
.sidebar-dark .cl-card, .wrapper.sidebar-dark .cl-card { box-shadow: 0 1px 6px rgba(0,0,0,.18); }
.sidebar-dark .cl-card-hdr:hover { background: rgba(255,255,255,.03); }
</style>
@endpush

{{-- ── Información básica ────────────────────────────────── --}}
<div class="cl-card">
    <div class="cl-card-hdr active" onclick="clToggle('info')">
        <span class="cl-card-icon info"><i class="fas fa-tag"></i></span>
        <div class="cl-card-label">
            <div class="cl-card-title">Información básica</div>
            <div class="cl-card-subtitle">Nombre, duración y orden de la lección</div>
        </div>
        <i class="fas fa-chevron-down cl-card-chevron active" id="chev-info"></i>
    </div>
    <div class="cl-card-body" id="body-info">
        <div class="form-group mb-3">
            <label class="font-weight-bold small">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre', $leccion->nombre ?? '') }}"
                   placeholder="Ej: Saludos básicos en Quechua" required>
            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="cl-meta-grid">
            <div class="form-group mb-0">
                <label class="font-weight-bold small">Duración estimada (min)</label>
                <input type="number" name="duracion_min" class="form-control" min="1"
                       value="{{ old('duracion_min', $leccion->duracion_min ?? '') }}" placeholder="Ej: 15">
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold small">Orden</label>
                <input type="number" name="orden" class="form-control" min="0"
                       value="{{ old('orden', $leccion->orden ?? 0) }}">
            </div>
        </div>
    </div>
</div>

{{-- ── Audio ─────────────────────────────────────────────── --}}
@php
    $hasAudio   = !empty(old('archivo_url', $leccion->archivo_url ?? ''));
    $savedAudio = old('archivo_url', $leccion->archivo_url ?? '');
    $savedAudioSrc = $savedAudio ? match(true) {
        str_starts_with($savedAudio, 'gdrive:') => '/ce/audio/' . substr($savedAudio, 7),
        str_starts_with($savedAudio, 'http')    => parse_url($savedAudio, PHP_URL_PATH),
        default                                 => '/storage/' . $savedAudio,
    } : '';
@endphp
<div class="cl-card">
    <div class="cl-card-hdr {{ $hasAudio ? 'active' : '' }}" id="hdr-audio" onclick="clToggle('audio')">
        <span class="cl-card-icon audio"><i class="fas fa-headphones"></i></span>
        <div class="cl-card-label">
            <div class="cl-card-title">
                Audio
                @if($hasAudio) <span class="cl-badge-active audio"><i class="fas fa-check mr-1"></i>Tiene audio</span> @endif
            </div>
            <div class="cl-card-subtitle">Archivo de audio subido a Google Drive</div>
        </div>
        <label class="cl-toggle" style="--clr:#06d6a0" onclick="event.stopPropagation()">
            <input type="checkbox" id="toggle-audio" {{ $hasAudio ? 'checked' : '' }}
                   onchange="clToggleBySwitch('audio', this.checked)">
            <span class="cl-toggle-track"></span>
        </label>
        <i class="fas fa-chevron-down cl-card-chevron {{ $hasAudio ? 'active' : '' }}" id="chev-audio"></i>
    </div>
    <div class="cl-card-body" id="body-audio" style="{{ $hasAudio ? '' : 'display:none' }}">
        <input type="hidden" name="archivo_url" id="cl-audio-url" value="{{ $savedAudio }}">
        <input type="file" id="cl-file-input" accept="audio/*,.mp3,.ogg,.wav,.m4a,.webm" style="display:none">

        <div id="cl-s-idle" {{ $hasAudio ? 'style=display:none' : '' }}>
            <div class="cl-file-zone" id="cl-file-zone">
                <div class="cl-file-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="cl-file-zone-label">Clic para seleccionar · o arrastra aquí</div>
                <div class="cl-file-zone-hint">MP3, OGG, WAV, M4A, WEBM · Máx. 20 MB</div>
            </div>
        </div>

        <div id="cl-s-preview" style="display:none">
            <div class="cl-audio-preview-box">
                <p class="small font-weight-bold mb-2" id="cl-file-name" style="color:var(--cl-dark)"></p>
                <audio id="cl-preview-audio" controls style="width:100%;accent-color:#06d6a0;margin-bottom:.55rem;display:block;"></audio>
                <div class="cl-action-row">
                    <button type="button" class="cl-btn-upload" id="cl-btn-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Subir a Drive
                    </button>
                    <button type="button" class="cl-btn-secondary" id="cl-btn-retry">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>

        <div id="cl-s-saved" {{ $hasAudio ? '' : 'style=display:none' }}>
            <div class="cl-audio-saved-box">
                <div class="cl-saved-label"><i class="fas fa-check-circle"></i> Audio guardado en Drive</div>
                <audio id="cl-saved-audio" controls preload="auto"
                       style="width:100%;display:block;accent-color:#059669;margin-bottom:.55rem;">
                    <source id="cl-saved-src" src="{{ $savedAudioSrc }}" type="audio/mpeg">
                </audio>
                <button type="button" class="cl-btn-secondary" id="cl-btn-cambiar">
                    <i class="fas fa-exchange-alt"></i> Cambiar archivo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Video ─────────────────────────────────────────────── --}}
@php $hasVideo = !empty(old('video_url', $leccion->video_url ?? '')); @endphp
<div class="cl-card">
    <div class="cl-card-hdr {{ $hasVideo ? 'active' : '' }}" id="hdr-video" onclick="clToggle('video')">
        <span class="cl-card-icon video"><i class="fas fa-play-circle"></i></span>
        <div class="cl-card-label">
            <div class="cl-card-title">
                Video
                @if($hasVideo) <span class="cl-badge-active video"><i class="fas fa-check mr-1"></i>Tiene video</span> @endif
            </div>
            <div class="cl-card-subtitle">Embed de YouTube, Vimeo u otro proveedor</div>
        </div>
        <label class="cl-toggle" style="--clr:#e74a3b" onclick="event.stopPropagation()">
            <input type="checkbox" id="toggle-video" {{ $hasVideo ? 'checked' : '' }}
                   onchange="clToggleBySwitch('video', this.checked)">
            <span class="cl-toggle-track"></span>
        </label>
        <i class="fas fa-chevron-down cl-card-chevron {{ $hasVideo ? 'active' : '' }}" id="chev-video"></i>
    </div>
    <div class="cl-card-body" id="body-video" style="{{ $hasVideo ? '' : 'display:none' }}">
        <div class="form-group mb-1">
            <label class="font-weight-bold small">URL embed del video</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                </div>
                <input type="text" name="video_url" id="cl-video-url"
                       class="form-control @error('video_url') is-invalid @enderror"
                       value="{{ old('video_url', $leccion->video_url ?? '') }}"
                       placeholder="https://www.youtube.com/embed/VIDEO_ID">
            </div>
            <small class="form-text text-muted">YouTube: <code>https://www.youtube.com/embed/VIDEO_ID</code></small>
        </div>
        <div class="cl-video-preview" id="cl-video-preview">
            <iframe id="cl-video-iframe" src="" allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media"></iframe>
        </div>
    </div>
</div>

{{-- ── Texto ─────────────────────────────────────────────── --}}
@php $hasTexto = !empty(old('contenido_texto', $leccion->contenido_texto ?? '')); @endphp
<div class="cl-card">
    <div class="cl-card-hdr {{ $hasTexto ? 'active' : '' }}" id="hdr-texto" onclick="clToggle('texto')">
        <span class="cl-card-icon texto"><i class="fas fa-align-left"></i></span>
        <div class="cl-card-label">
            <div class="cl-card-title">
                Texto
                @if($hasTexto) <span class="cl-badge-active texto"><i class="fas fa-check mr-1"></i>Tiene texto</span> @endif
            </div>
            <div class="cl-card-subtitle">Contenido enriquecido: explicaciones, vocabulario, notas</div>
        </div>
        <label class="cl-toggle" style="--clr:#4e73df" onclick="event.stopPropagation()">
            <input type="checkbox" id="toggle-texto" {{ $hasTexto ? 'checked' : '' }}
                   onchange="clToggleBySwitch('texto', this.checked)">
            <span class="cl-toggle-track"></span>
        </label>
        <i class="fas fa-chevron-down cl-card-chevron {{ $hasTexto ? 'active' : '' }}" id="chev-texto"></i>
    </div>
    <div class="cl-card-body" id="body-texto" style="{{ $hasTexto ? '' : 'display:none' }}">
        <textarea name="contenido_texto" id="cl-summernote">{{ old('contenido_texto', $leccion->contenido_texto ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-es-ES.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
(function () {
    const UPLOAD_URL = '{{ route($rp . ".lecciones.upload-audio") }}';
    const CSRF       = '{{ csrf_token() }}';

    /* ══ Accordion toggle con GSAP ════════════════════════════════ */
    function clToggle(key) {
        const body  = document.getElementById('body-'  + key);
        const chev  = document.getElementById('chev-'  + key);
        const hdr   = document.getElementById('hdr-'   + key);
        const sw    = document.getElementById('toggle-' + key);
        if (!body) return;

        const isOpen = body.style.display !== 'none';

        if (isOpen) {
            gsap.to(body, {
                height: 0, opacity: 0, duration: .28, ease: 'power2.in',
                onStart:    () => { body.style.overflow = 'hidden'; },
                onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
            });
            if (chev) chev.classList.remove('active');
            if (hdr)  hdr.classList.remove('active');
            if (sw)   sw.checked = false;
        } else {
            body.style.display = 'block';
            body.style.overflow = 'hidden';
            body.style.height   = '0';
            const h = body.scrollHeight;
            gsap.to(body, {
                height: h, opacity: 1, duration: .35, ease: 'power2.out',
                onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
            });
            if (chev) chev.classList.add('active');
            if (hdr)  hdr.classList.add('active');
            if (sw)   sw.checked = true;
        }
    }
    window.clToggle = clToggle;

    function clToggleBySwitch(key, open) {
        const body = document.getElementById('body-' + key);
        const chev = document.getElementById('chev-' + key);
        const hdr  = document.getElementById('hdr-'  + key);
        if (!body) return;
        const isOpen = body.style.display !== 'none';
        if (open === isOpen) return;
        if (open) {
            body.style.display   = 'block';
            body.style.overflow  = 'hidden';
            body.style.height    = '0';
            const h = body.scrollHeight;
            gsap.to(body, {
                height: h, opacity: 1, duration: .35, ease: 'power2.out',
                onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
            });
            if (chev) chev.classList.add('active');
            if (hdr)  hdr.classList.add('active');
        } else {
            gsap.to(body, {
                height: 0, opacity: 0, duration: .28, ease: 'power2.in',
                onStart:    () => { body.style.overflow = 'hidden'; },
                onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
            });
            if (chev) chev.classList.remove('active');
            if (hdr)  hdr.classList.remove('active');
        }
    }
    window.clToggleBySwitch = clToggleBySwitch;

    /* ══ Entrada de cards con GSAP ════════════════════════════════ */
    gsap.from('.cl-card', {
        opacity: 0, y: 18, duration: .45,
        stagger: .09, ease: 'power2.out', delay: .05
    });

    /* ══ Video URL preview ════════════════════════════════════════ */
    let vTimer;
    const videoInput = document.getElementById('cl-video-url');
    const videoPreview = document.getElementById('cl-video-preview');
    const videoIframe  = document.getElementById('cl-video-iframe');

    function refreshVideo() {
        const url = (videoInput?.value || '').trim();
        if (url && document.getElementById('body-video')?.style.display !== 'none') {
            if (videoIframe) videoIframe.src = url;
            videoPreview?.classList.add('show');
        } else {
            if (videoIframe) videoIframe.src = '';
            videoPreview?.classList.remove('show');
        }
    }
    videoInput?.addEventListener('input', () => { clearTimeout(vTimer); vTimer = setTimeout(refreshVideo, 700); });
    refreshVideo();

    /* ══ Audio upload ═════════════════════════════════════════════ */
    const hiddenUrl = document.getElementById('cl-audio-url');
    const sIdle     = document.getElementById('cl-s-idle');
    const sPreview  = document.getElementById('cl-s-preview');
    const sSaved    = document.getElementById('cl-s-saved');
    const fileInput = document.getElementById('cl-file-input');
    const prevAu    = document.getElementById('cl-preview-audio');
    const savedAu   = document.getElementById('cl-saved-audio');

    function showState(el) {
        [sIdle, sPreview, sSaved].forEach(s => { if (s) s.style.display = 'none'; });
        if (el) el.style.display = '';
    }

    document.getElementById('cl-file-zone')?.addEventListener('click', () => fileInput?.click());
    document.getElementById('cl-file-zone')?.addEventListener('dragover',  e => { e.preventDefault(); e.currentTarget.style.borderColor = '#06d6a0'; });
    document.getElementById('cl-file-zone')?.addEventListener('dragleave', e => { e.currentTarget.style.borderColor = ''; });
    document.getElementById('cl-file-zone')?.addEventListener('drop', e => {
        e.preventDefault(); e.currentTarget.style.borderColor = '';
        const f = e.dataTransfer.files[0];
        if (f?.type.startsWith('audio/')) loadFile(f);
        else alert('Selecciona un archivo de audio.');
    });
    fileInput?.addEventListener('change', function () { if (this.files[0]) loadFile(this.files[0]); });

    function loadFile(file) {
        const nm = document.getElementById('cl-file-name');
        if (nm) nm.textContent = file.name + ' — ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
        prevAu.src = URL.createObjectURL(file);
        prevAu.load();
        showState(sPreview);
    }

    document.getElementById('cl-btn-upload')?.addEventListener('click', async function () {
        const file = fileInput?.files[0];
        if (!file) return;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
        const fd = new FormData();
        fd.append('audio', file, file.name);
        try {
            const res = await fetch(UPLOAD_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            hiddenUrl.value = data.path;
            const src = document.getElementById('cl-saved-src');
            if (src) { src.src = data.url; src.type = data.mime || 'audio/mpeg'; }
            savedAu?.load();
            showState(sSaved);
            // Actualizar badge header
            document.querySelector('#hdr-audio .cl-card-title').innerHTML =
                'Audio <span class="cl-badge-active audio"><i class="fas fa-check mr-1"></i>Tiene audio</span>';
        } catch (err) {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Subir a Drive';
            alert('Error al subir: ' + err.message);
        }
    });

    document.getElementById('cl-btn-retry')?.addEventListener('click',   () => { if (fileInput) fileInput.value=''; if (prevAu) prevAu.src=''; showState(sIdle); });
    document.getElementById('cl-btn-cambiar')?.addEventListener('click', () => { if (hiddenUrl) hiddenUrl.value=''; if (fileInput) fileInput.value=''; if (prevAu) prevAu.src=''; showState(sIdle); });

    /* ══ Summernote ═══════════════════════════════════════════════ */
    $(document).ready(function () {
        if (typeof $.fn.summernote === 'undefined') return;
        $('#cl-summernote').summernote({
            lang: 'es-ES',
            height: 300, minHeight: 160,
            placeholder: 'Escribe el contenido de la lección: vocabulario, explicaciones, transcripción...',
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
        });
    });
}());
</script>
@endpush
