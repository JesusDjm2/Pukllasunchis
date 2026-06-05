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

/* ── Grabador de audio ── */
.cea-rec-btn-start {
    display:inline-flex; align-items:center; gap:.5rem;
    background:linear-gradient(135deg,#8338ec,#3a86ff);
    color:#fff; border:none; border-radius:.6rem;
    padding:.65rem 1.4rem; font-size:.88rem; font-weight:700;
    cursor:pointer; transition:opacity .15s, transform .12s;
}
.cea-rec-btn-start:hover { opacity:.88; transform:scale(1.02); }
.cea-rec-btn-start:disabled { opacity:.55; cursor:not-allowed; transform:none; }
.cea-rec-live {
    display:flex; align-items:center; gap:.85rem;
    background:#fff0f0; border:1.5px solid #fca5a5;
    border-radius:.65rem; padding:.75rem 1rem;
}
.cea-rec-dot {
    width:11px; height:11px; border-radius:50%; background:#e74a3b; flex-shrink:0;
    animation:ceaDotBlink .75s ease infinite;
}
@@keyframes ceaDotBlink { 0%,100%{opacity:1} 50%{opacity:.2} }
.cea-rec-timer { font-size:1rem; font-weight:800; color:#e74a3b; font-variant-numeric:tabular-nums; flex-shrink:0; }
.cea-rec-stop {
    margin-left:auto; display:inline-flex; align-items:center; gap:.4rem;
    background:#e74a3b; color:#fff; border:none; border-radius:.5rem;
    padding:.4rem .9rem; font-weight:700; font-size:.82rem; cursor:pointer; flex-shrink:0;
}
.cea-rec-stop:hover { opacity:.85; }
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

{{-- Audio de pronunciación (grabación directa) --}}
<div class="cea-field-group">
    <div class="cea-field-group-title">
        <i class="fas fa-microphone"></i> Audio de pronunciación
        <span style="font-weight:500;text-transform:none;letter-spacing:0;color:#aaa;font-size:.78rem;">(opcional)</span>
    </div>
    <p class="small text-muted mb-3">Graba la pronunciación directamente desde el navegador. Los alumnos escucharán el audio antes de responder al ejercicio.</p>

    {{-- Campo oculto que guarda la URL final --}}
    <input type="hidden" name="audio_url" id="cea-audio-url" value="{{ old('audio_url', $ejercicio->audio_url ?? '') }}">

    {{-- Estado: listo para grabar --}}
    <div id="cea-s-idle" {{ old('audio_url', $ejercicio->audio_url ?? '') ? 'style=display:none' : '' }}>
        <button type="button" class="cea-rec-btn-start" id="cea-btn-grabar">
            <i class="fas fa-microphone"></i> Iniciar grabación
        </button>
        <p class="small text-muted mt-2 mb-0">Se pedirá permiso para usar el micrófono.</p>
    </div>

    {{-- Estado: grabando --}}
    <div id="cea-s-recording" style="display:none">
        <div class="cea-rec-live">
            <span class="cea-rec-dot"></span>
            <span style="font-size:.85rem;font-weight:700;color:#c0392b;">Grabando...</span>
            <span class="cea-rec-timer" id="cea-timer">0:00</span>
            <button type="button" class="cea-rec-stop" id="cea-btn-stop">
                <i class="fas fa-stop"></i> Detener
            </button>
        </div>
    </div>

    {{-- Estado: grabado, pendiente de subir --}}
    <div id="cea-s-preview" style="display:none">
        <div class="cea-rec-preview-box">
            <audio id="cea-preview-audio" controls
                   style="width:100%;display:block;margin-bottom:.65rem;accent-color:#8338ec;"></audio>
            <div class="cea-rec-preview-actions">
                <button type="button" class="cea-rec-upload-btn" id="cea-btn-upload">
                    <i class="fas fa-cloud-upload-alt"></i> Subir y guardar
                </button>
                <button type="button" class="cea-rec-retry-btn" id="cea-btn-retry">
                    <i class="fas fa-redo"></i> Volver a grabar
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
                    $savedUrl  = old('audio_url', $ejercicio->audio_url ?? '');
                    $savedMime = match(pathinfo($savedUrl, PATHINFO_EXTENSION)) {
                        'm4a', 'mp4' => 'audio/mp4',
                        'ogg'        => 'audio/ogg',
                        default      => 'audio/webm',
                    };
                @endphp
                @if($savedUrl)
                    <source id="cea-saved-src" src="{{ $savedUrl }}" type="{{ $savedMime }}">
                @else
                    <source id="cea-saved-src" src="" type="">
                @endif
            </audio>
            <button type="button" class="cea-rec-retry-btn" id="cea-btn-regrabar">
                <i class="fas fa-redo"></i> Volver a grabar
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

// ── Grabador de audio (MediaRecorder) ─────────────────────────
(function () {
    const UPLOAD_URL = '{{ route($rp . ".ejercicios.upload-audio") }}';
    const CSRF       = '{{ csrf_token() }}';

    const hiddenUrl  = document.getElementById('cea-audio-url');
    const sIdle      = document.getElementById('cea-s-idle');
    const sRecording = document.getElementById('cea-s-recording');
    const sPreview   = document.getElementById('cea-s-preview');
    const sSaved     = document.getElementById('cea-s-saved');
    const timerEl    = document.getElementById('cea-timer');
    const previewAu  = document.getElementById('cea-preview-audio');
    const savedAu    = document.getElementById('cea-saved-audio');

    function show(el) {
        [sIdle, sRecording, sPreview, sSaved].forEach(s => { if (s) s.style.display = 'none'; });
        if (el) el.style.display = '';
    }

    // ── Timer ──
    let timerInterval, seconds = 0;
    function startTimer() {
        seconds = 0;
        timerEl.textContent = '0:00';
        timerInterval = setInterval(() => {
            seconds++;
            timerEl.textContent = Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0');
        }, 1000);
    }
    function stopTimer() { clearInterval(timerInterval); }

    // ── Grabar ──
    let recorder, chunks = [], audioBlob;

    document.getElementById('cea-btn-grabar')?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia) {
            alert('Tu navegador no soporta grabación de audio.');
            return;
        }
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Solicitando permiso...';
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            const mime = ['audio/mp4','audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus']
                             .find(t => MediaRecorder.isTypeSupported(t)) || '';
            recorder = new MediaRecorder(stream, mime ? { mimeType: mime } : {});
            chunks = [];
            recorder.addEventListener('dataavailable', e => { if (e.data.size > 0) chunks.push(e.data); });
            recorder.addEventListener('stop', () => {
                stream.getTracks().forEach(t => t.stop());
                stopTimer();
                const type = recorder.mimeType || 'audio/webm';
                audioBlob  = new Blob(chunks, { type });
                previewAu.src = URL.createObjectURL(audioBlob);
                previewAu.load();
                show(sPreview);
            });
            recorder.start(250);
            startTimer();
            show(sRecording);
        } catch (err) {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-microphone"></i> Iniciar grabación';
            alert('No se pudo acceder al micrófono: ' + err.message);
        }
    });

    // ── Detener ──
    document.getElementById('cea-btn-stop')?.addEventListener('click', () => {
        if (recorder?.state !== 'inactive') recorder.stop();
    });

    // ── Subir ──
    document.getElementById('cea-btn-upload')?.addEventListener('click', async function () {
        if (!audioBlob) return;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
        // mp4 → .m4a (Chrome/Safari), ogg → .ogg (Firefox), webm → .weba
        const type = audioBlob.type;
        const ext  = type.includes('ogg') ? 'ogg'
                   : type.includes('mp4') ? 'm4a'
                   : 'weba';
        const fd  = new FormData();
        fd.append('audio', audioBlob, 'pronunciacion.' + ext);
        try {
            const res = await fetch(UPLOAD_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd
            });

            // Si el servidor responde con error, mostrar el cuerpo para diagnosticar
            if (!res.ok) {
                const body = await res.text();
                throw new Error('HTTP ' + res.status + ' — ' + body.substring(0, 200));
            }

            const data = await res.json();
            hiddenUrl.value = data.url;

            // Actualizar el <source> directamente (no audio.src cuando hay <source> hijos)
            const savedSrc = document.getElementById('cea-saved-src');
            if (savedSrc) {
                const urlExt  = data.url.split('.').pop().toLowerCase();
                const mimeMap = { m4a: 'audio/mp4', ogg: 'audio/ogg' };
                savedSrc.src  = data.url;
                savedSrc.type = mimeMap[urlExt] || 'audio/webm';
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

    // ── Reiniciar ──
    function resetToIdle() {
        hiddenUrl.value = '';
        audioBlob = null;
        if (previewAu) previewAu.src = '';
        const btn = document.getElementById('cea-btn-grabar');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-microphone"></i> Iniciar grabación'; }
        show(sIdle);
    }
    document.getElementById('cea-btn-retry')?.addEventListener('click', resetToIdle);
    document.getElementById('cea-btn-regrabar')?.addEventListener('click', resetToIdle);
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
