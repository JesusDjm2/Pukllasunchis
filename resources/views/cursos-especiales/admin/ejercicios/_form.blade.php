{{-- Partial para create y edit de ejercicio --}}
@push('styles')
<style>
.cea-field-group { background:#fafbff; border:1px solid #e9ecef; border-radius:.75rem; padding:1.25rem 1.25rem 1rem; margin-bottom:1.25rem; }
.cea-field-group-title { font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#4e73df; margin-bottom:.85rem; display:flex; align-items:center; gap:.4rem; }
.cea-opciones-container { border:1px solid #e9ecef; border-radius:.6rem; overflow:hidden; margin-bottom:.75rem; }
.cea-opcion-row {
    display:flex; align-items:center; gap:.5rem;
    padding:.5rem .75rem; border-bottom:1px solid #f0f0f0;
    background:#fff;
}
.cea-opcion-row:last-child { border-bottom:none; }
.cea-opcion-num {
    width:24px; height:24px; border-radius:50%;
    background:#e8f0ff; color:#4e73df; font-size:.72rem; font-weight:800;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.cea-opcion-input { border:none; background:transparent; flex:1; font-size:.88rem; outline:none; }
.cea-add-opcion {
    display:inline-flex; align-items:center; gap:.4rem;
    background:#e8f0ff; color:#4e73df; border:none; border-radius:.5rem;
    padding:.35rem .85rem; font-size:.8rem; font-weight:700; cursor:pointer;
    transition:background .15s;
}
.cea-add-opcion:hover { background:#d0e0ff; }
.cea-remove-opcion { background:none; border:none; color:#e74a3b; cursor:pointer; font-size:.8rem; padding:.1rem; }

/* ── Subida de audio desde PC ── */
.cea-file-zone {
    border:2px dashed rgba(131,56,236,.3); border-radius:.75rem;
    padding:1.5rem 1rem; text-align:center;
    background:linear-gradient(135deg,#faf8ff,#f4f0ff);
    cursor:pointer; transition:border-color .2s, background .2s;
}
.cea-file-zone:hover { border-color:#8338ec; background:#f0eeff; }
.cea-file-zone-icon { font-size:2rem; color:#8338ec; margin-bottom:.5rem; }
.cea-file-zone-label { font-weight:700; font-size:.9rem; color:#2d3561; margin-bottom:.2rem; }
.cea-file-zone-hint { font-size:.78rem; color:#aaa; }
.cea-rec-preview-box {
    background:#f0eeff; border:1px solid rgba(131,56,236,.22); border-radius:.65rem; padding:.75rem 1rem;
}
.cea-rec-preview-actions { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.65rem; }
.cea-rec-upload-btn {
    display:inline-flex; align-items:center; gap:.45rem;
    background:linear-gradient(135deg,#8338ec,#3a86ff);
    color:#fff; border:none; border-radius:.55rem;
    padding:.48rem 1.1rem; font-weight:700; font-size:.82rem; cursor:pointer;
}
.cea-rec-upload-btn:hover { opacity:.88; }
.cea-rec-upload-btn:disabled { opacity:.6; cursor:not-allowed; }
.cea-rec-retry-btn {
    display:inline-flex; align-items:center; gap:.4rem;
    background:#ededf5; color:#555; border:none; border-radius:.55rem;
    padding:.48rem 1rem; font-weight:700; font-size:.82rem; cursor:pointer;
}
.cea-rec-retry-btn:hover { background:#dde; }
.cea-rec-saved-box {
    background:#f0fdf8; border:1px solid rgba(28,200,138,.3); border-radius:.65rem; padding:.75rem 1rem;
}
.cea-rec-saved-label { display:flex; align-items:center; gap:.4rem; font-size:.82rem; font-weight:700; color:#059669; margin-bottom:.6rem; }

/* Mini-reproductor personalizado (preview y saved) */
.cea-mini-player {
    display:flex; align-items:center; gap:.55rem;
    background:rgba(0,0,0,.04); border-radius:.5rem; padding:.5rem .7rem;
}
.cea-mini-play {
    width:32px; height:32px; border-radius:50%; flex-shrink:0;
    border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
    font-size:.75rem; transition:transform .15s, opacity .15s;
}
.cea-mini-play:hover { opacity:.85; transform:scale(1.08); }
.cea-mini-play.purple { background:#8338ec; color:#fff; }
.cea-mini-play.teal   { background:#059669; color:#fff; }
.cea-mini-track { flex:1; min-width:0; }
.cea-mini-bar-wrap {
    height:5px; background:rgba(0,0,0,.12); border-radius:99px; cursor:pointer; margin-bottom:.2rem;
}
.cea-mini-bar-fill { height:100%; width:0; border-radius:99px; transition:width .1s linear; }
.cea-mini-bar-fill.purple { background:linear-gradient(90deg,#8338ec,#3a86ff); }
.cea-mini-bar-fill.teal   { background:linear-gradient(90deg,#059669,#34d399); }
.cea-mini-time { font-size:.67rem; font-weight:600; color:#888; display:flex; justify-content:space-between; }
</style>
@endpush

{{-- Tipo --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-list"></i> Tipo de ejercicio</div>
    <div class="form-group mb-0">
        <select name="tipo" id="tipo-ejercicio" class="form-control">
            @foreach (['multiple' => 'Opción múltiple', 'completar' => 'Completar el espacio', 'emparejar' => 'Emparejar'] as $v => $l)
                <option value="{{ $v }}" {{ old('tipo', $ejercicio->tipo ?? 'multiple') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">La corrección automática funciona para todos los tipos.</small>
    </div>
</div>

{{-- Pregunta --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-question-circle"></i> Pregunta</div>
    <div class="form-group mb-0">
        <textarea name="pregunta" class="form-control @error('pregunta') is-invalid @enderror"
                  rows="3" required
                  placeholder="Ej: ¿Cómo se dice 'Buenos días' en Quechua?">{{ old('pregunta', $ejercicio->pregunta ?? '') }}</textarea>
        @error('pregunta') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Opciones (solo múltiple) --}}
<div class="cea-field-group" id="bloque-opciones">
    <div class="cea-field-group-title"><i class="fas fa-list-ul"></i> Opciones de respuesta</div>
    <p class="small text-muted mb-2">Escribe cada opción. La respuesta correcta debe coincidir exactamente con una de estas opciones.</p>
    <div class="cea-opciones-container" id="opciones-container">
        @php
            $opcionesActuales = old('opciones_raw')
                ? explode("\n", old('opciones_raw'))
                : (isset($ejercicio) && $ejercicio->opciones ? $ejercicio->opciones : ['', '', '', '']);
        @endphp
        @foreach ($opcionesActuales as $i => $op)
            <div class="cea-opcion-row">
                <span class="cea-opcion-num">{{ chr(65 + $i) }}</span>
                <input type="text" class="cea-opcion-input opcion-input" value="{{ $op }}" placeholder="Opción {{ chr(65 + $i) }}">
                <button type="button" class="cea-remove-opcion" onclick="removeOpcion(this)" title="Eliminar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>
    <input type="hidden" name="opciones_raw" id="opciones-raw-hidden">
    <button type="button" class="cea-add-opcion" onclick="addOpcion()">
        <i class="fas fa-plus"></i> Agregar opción
    </button>
</div>

{{-- Respuesta correcta --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-check-double"></i> Respuesta correcta</div>
    <div class="form-group mb-0">
        <input type="text" name="respuesta_correcta"
               class="form-control @error('respuesta_correcta') is-invalid @enderror"
               value="{{ old('respuesta_correcta', $ejercicio->respuesta_correcta ?? '') }}"
               placeholder="Escribe la respuesta correcta exactamente como aparece en las opciones" required>
        @error('respuesta_correcta') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="form-text text-muted">La comparación ignora mayúsculas/minúsculas y espacios al inicio/fin.</small>
    </div>
</div>

{{-- Audio de pronunciación --}}
<div class="cea-field-group">
    <div class="cea-field-group-title">
        <i class="fas fa-headphones"></i> Audio de pronunciación
        <span style="font-weight:500;text-transform:none;letter-spacing:0;color:#aaa;font-size:.78rem;">(opcional)</span>
    </div>
    <p class="small text-muted mb-3">
        Sube un archivo de audio desde tu PC. Los alumnos lo escucharán antes de responder.<br>
        <span style="color:#aaa;">Formatos aceptados: MP3, OGG, WAV, M4A, WEBM · Máx. 20 MB</span>
    </p>

    {{-- Campo oculto que guarda el path en BD --}}
    <input type="hidden" name="audio_url" id="cea-audio-url" value="{{ old('audio_url', $ejercicio->audio_url ?? '') }}">
    {{-- Input de archivo (oculto, activado por la zona) --}}
    <input type="file" id="cea-file-input" accept="audio/*,.mp3,.ogg,.wav,.m4a,.webm,.weba" style="display:none">

    {{-- Estado: sin audio --}}
    <div id="cea-s-idle" {{ old('audio_url', $ejercicio->audio_url ?? '') ? 'style=display:none' : '' }}>
        <div class="cea-file-zone" id="cea-file-zone">
            <div class="cea-file-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="cea-file-zone-label">Haz clic para seleccionar un archivo</div>
            <div class="cea-file-zone-hint">o arrastra y suelta aquí</div>
        </div>
    </div>

    {{-- Estado: archivo seleccionado, pendiente de subir --}}
    <div id="cea-s-preview" style="display:none">
        <div class="cea-rec-preview-box">
            <p class="small font-weight-bold mb-2" id="cea-file-name" style="color:#2d3561;"></p>
            <audio id="cea-preview-audio" controls
                   style="width:100%;display:block;margin-bottom:.65rem;accent-color:#8338ec;"></audio>
            <div class="cea-rec-preview-actions">
                <button type="button" class="cea-rec-upload-btn" id="cea-btn-upload">
                    <i class="fas fa-cloud-upload-alt"></i> Subir y guardar
                </button>
                <button type="button" class="cea-rec-retry-btn" id="cea-btn-retry">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    {{-- Estado: guardado --}}
    <div id="cea-s-saved" {{ old('audio_url', $ejercicio->audio_url ?? '') ? '' : 'style=display:none' }}>
        <div class="cea-rec-saved-box">
            <div class="cea-rec-saved-label">
                <i class="fas fa-check-circle"></i> Audio guardado — los alumnos lo escucharán antes de responder
            </div>
            <audio id="cea-saved-audio" controls preload="auto"
                   style="width:100%;display:block;margin-bottom:.6rem;accent-color:#059669;">
                @php
                    $savedPath = old('audio_url', $ejercicio->audio_url ?? '');
                    $savedSrc  = $savedPath ? match(true) {
                        str_starts_with($savedPath, 'gdrive:') => '/ce/audio/' . substr($savedPath, 7),
                        str_starts_with($savedPath, 'http')    => parse_url($savedPath, PHP_URL_PATH),
                        default                                 => '/storage/' . $savedPath,
                    } : '';
                    $savedMime = $savedPath ? match(strtolower(pathinfo($savedPath, PATHINFO_EXTENSION))) {
                        'm4a', 'mp4' => 'audio/mp4',
                        'mp3'        => 'audio/mpeg',
                        'ogg'        => 'audio/ogg',
                        'wav'        => 'audio/wav',
                        default      => 'audio/mpeg',
                    } : '';
                @endphp
                @if($savedSrc)
                    <source id="cea-saved-src" src="{{ $savedSrc }}" type="{{ $savedMime }}">
                @else
                    <source id="cea-saved-src" src="" type="">
                @endif
            </audio>
            <button type="button" class="cea-rec-retry-btn" id="cea-btn-cambiar">
                <i class="fas fa-exchange-alt"></i> Cambiar archivo
            </button>
        </div>
    </div>
</div>

{{-- Metadatos --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-sliders-h"></i> Metadatos</div>
    <div class="row">
        <div class="col-md-4">
            <label class="font-weight-bold small">Puntaje máximo</label>
            <input type="number" name="puntaje_max" class="form-control" min="1"
                   value="{{ old('puntaje_max', $ejercicio->puntaje_max ?? 1) }}">
        </div>
        <div class="col-md-4">
            <label class="font-weight-bold small">Orden</label>
            <input type="number" name="orden" class="form-control" min="0"
                   value="{{ old('orden', $ejercicio->orden ?? 0) }}">
        </div>
    </div>
</div>

@push('scripts')
<script>
function addOpcion() {
    const container = document.getElementById('opciones-container');
    const rows = container.querySelectorAll('.cea-opcion-row');
    const letra = String.fromCharCode(65 + rows.length);
    const div = document.createElement('div');
    div.className = 'cea-opcion-row';
    div.innerHTML = `
        <span class="cea-opcion-num">${letra}</span>
        <input type="text" class="cea-opcion-input opcion-input" placeholder="Opción ${letra}">
        <button type="button" class="cea-remove-opcion" onclick="removeOpcion(this)">
            <i class="fas fa-times"></i>
        </button>`;
    container.appendChild(div);
    renumberOpciones();
}
function removeOpcion(btn) {
    const rows = document.querySelectorAll('.cea-opcion-row');
    if (rows.length <= 2) return;
    btn.closest('.cea-opcion-row').remove();
    renumberOpciones();
}
function renumberOpciones() {
    document.querySelectorAll('.cea-opcion-row').forEach((row, i) => {
        const num = row.querySelector('.cea-opcion-num');
        const inp = row.querySelector('.cea-opcion-input');
        const letra = String.fromCharCode(65 + i);
        if (num) num.textContent = letra;
        if (inp) inp.placeholder = 'Opción ' + letra;
    });
}

// Sync opciones to hidden input before submit
document.addEventListener('submit', function (e) {
    const inputs = document.querySelectorAll('.opcion-input');
    const vals = Array.from(inputs).map(i => i.value.trim()).filter(v => v !== '');
    const hidden = document.getElementById('opciones-raw-hidden');
    if (hidden) hidden.value = vals.join('\n');
}, true);

// ── Subida de audio desde PC ───────────────────────────────────
(function () {
    const UPLOAD_URL = '{{ route($rp . ".ejercicios.upload-audio") }}';
    const CSRF       = '{{ csrf_token() }}';

    const hiddenUrl = document.getElementById('cea-audio-url');
    const sIdle     = document.getElementById('cea-s-idle');
    const sPreview  = document.getElementById('cea-s-preview');
    const sSaved    = document.getElementById('cea-s-saved');
    const fileInput = document.getElementById('cea-file-input');
    const previewAu = document.getElementById('cea-preview-audio');
    const savedAu   = document.getElementById('cea-saved-audio');

    function show(el) {
        [sIdle, sPreview, sSaved].forEach(s => { if (s) s.style.display = 'none'; });
        if (el) el.style.display = '';
    }

    // Abrir selector al hacer clic en la zona o arrastrar
    document.getElementById('cea-file-zone')?.addEventListener('click', () => fileInput?.click());

    document.getElementById('cea-file-zone')?.addEventListener('dragover', e => {
        e.preventDefault();
        e.currentTarget.style.borderColor = '#8338ec';
    });
    document.getElementById('cea-file-zone')?.addEventListener('dragleave', e => {
        e.currentTarget.style.borderColor = '';
    });
    document.getElementById('cea-file-zone')?.addEventListener('drop', e => {
        e.preventDefault();
        e.currentTarget.style.borderColor = '';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('audio/')) loadFile(file);
        else alert('Por favor selecciona un archivo de audio.');
    });

    // Archivo seleccionado desde el input
    fileInput?.addEventListener('change', function () {
        if (this.files[0]) loadFile(this.files[0]);
    });

    function loadFile(file) {
        const nameEl = document.getElementById('cea-file-name');
        if (nameEl) nameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        previewAu.src = URL.createObjectURL(file);
        previewAu.load();
        show(sPreview);
    }

    // Subir al servidor
    document.getElementById('cea-btn-upload')?.addEventListener('click', async function () {
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
            if (!res.ok) {
                const body = await res.text();
                throw new Error('HTTP ' + res.status + ' — ' + body.substring(0, 200));
            }
            const data = await res.json();
            hiddenUrl.value = data.path;

            const savedSrc = document.getElementById('cea-saved-src');
            const mimeMap  = { mp3: 'audio/mpeg', ogg: 'audio/ogg', wav: 'audio/wav', m4a: 'audio/mp4' };
            const urlExt   = data.url.split('.').pop().toLowerCase();
            if (savedSrc) {
                savedSrc.src  = data.url;
                savedSrc.type = data.mime || mimeMap[urlExt] || 'audio/mpeg';
            } else {
                savedAu.src = data.url;
            }
            savedAu.load();
            show(sSaved);
        } catch (err) {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Subir y guardar';
            alert('Error al subir: ' + err.message);
        }
    });

    // Cancelar selección
    document.getElementById('cea-btn-retry')?.addEventListener('click', () => {
        if (fileInput) fileInput.value = '';
        if (previewAu) previewAu.src = '';
        show(sIdle);
    });

    // Cambiar audio guardado
    document.getElementById('cea-btn-cambiar')?.addEventListener('click', () => {
        hiddenUrl.value = '';
        if (fileInput) fileInput.value = '';
        if (previewAu) previewAu.src = '';
        show(sIdle);
    });
}());

// Show/hide opciones for type multiple
const tipoSel = document.getElementById('tipo-ejercicio');
function toggleTipoUI() {
    const bloqueOp = document.getElementById('bloque-opciones');
    if (bloqueOp) bloqueOp.style.display = tipoSel.value === 'multiple' ? '' : 'none';
}
if (tipoSel) {
    tipoSel.addEventListener('change', toggleTipoUI);
    toggleTipoUI();
}
</script>
@endpush
